<?php

// controllers/auth/user/UserLoginController.php

require_once $_SERVER['DOCUMENT_ROOT'] . '/models/User.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/SessionUser.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/responses/ResponseHandler.php';

require_once $_SERVER['DOCUMENT_ROOT'] . '/entities/User.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/entities/SessionUser.php';

use App\Entities\User as UserEntity;
use App\Entities\SessionUser as SessionUserEntity;

class UserLoginController
{
    private $userModel;
    private $sessionUserModel;
    private $responseHandler;

    public function __construct()
    {
        $this->userModel = new User();
        $this->sessionUserModel = new SessionUser();
        $this->responseHandler = new ResponseHandler();
    }

    public function login($data)
    {
        // Validación inicial de campos obligatorios
        if (!isset($data[UserEntity::EMAIL]) || !isset($data[UserEntity::PASSWORD])) {
            $this->responseHandler->sendFailure("Los campos 'email' y 'password' son obligatorios", 400);
            return;
        }

        $email = $data[UserEntity::EMAIL];
        $password = $data[UserEntity::PASSWORD];

        // Validación del formato del email (seguridad básica)
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->responseHandler->sendFailure("Formato de campo 'email' inválido", 400);
            return;
        }

        try {
            //Búsqueda del user por email
            $user = $this->userModel->findUserByEmail($email);

            //Si el user es false
            if (!$user) {
                $this->responseHandler->sendFailure("Credenciales inválidas", 401);
                return;
            }

            //Si las contraseñas no coinciden
            if ($password !== $user[UserEntity::PASSWORD]) {
                $this->responseHandler->sendFailure("Credenciales inválidas", 401);
                return;
            }

            //Si todo ok
            ini_set('session.gc_maxlifetime', 28800); // 8 horas en el servidor 
            session_set_cookie_params(28800); // 8 horas en la cookie del cliente

            //Aseguramos una sesión
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            //Invalida cualquier ID de sesión anterior y emite uno nuevo.
            session_regenerate_id(true);

            //Almacena el ID del user 
            $_SESSION['user_id'] = $user[UserEntity::ID];

            //Registra la dirección remota desde donde se inició la sesión.
            $ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown IP';

            //Insertamos la ip del user en la DB
            $this->sessionUserModel->createSession([
                SessionUserEntity::IP => $ip,
                SessionUserEntity::ID_USER => $user[UserEntity::ID]
            ]);

            // Elimina la contraseña de la respuesta
            unset($user[UserEntity::PASSWORD]);

            $this->responseHandler->sendSuccess(
                ["user" => $user],
                "Login exitoso"
            );
        } catch (\Exception $e) {
            $this->responseHandler->sendFailure("Error al procesar el login.", 500, $e);
        }
    }

    /**
     * Cierra la sesión activa del user.
     * * Invalida la sesión PHP en el servidor, destruye los datos de la sesión 
     * y elimina la cookie de sesión del navegador.
     * Además, actualiza el registro de sesión en la base de datos (DB).
     */
    public function logout()
    {
        //Asegura que la sesión esté activa para poder manipularla.
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Verifica si existe una sesión de user para cerrar.

        if (isset($_SESSION['user_id'])) {

            $userId = $_SESSION['user_id'];

            // Destrucción de la Sesión en el Servidor
            session_unset();
            session_destroy();

            // Eliminación de la Cookie en el Cliente
            setcookie(session_name(), '', time() - 3600, '/');

            // Llama al modelo para marcar la sesión como finalizada en la base de datos (soft-delete).
            try {
                $this->sessionUserModel->deleteSession($userId);
            } catch (\Exception $e) {
                // Si el log de logout falla, registramos el error, pero permitimos que el logout HTTP continúe.
                error_log("Error al registrar el LOGOUT en DB: " . $e->getMessage());
            }
            $this->responseHandler->sendSuccess(null, "Logout exitoso.");
        } else {
            $this->responseHandler->sendSuccess(null, "No tenías una sesión activa.");
        }
    }
}