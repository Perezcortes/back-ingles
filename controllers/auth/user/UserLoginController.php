<?php

// controllers/auth/user/UserLoginController.php

require_once $_SERVER['DOCUMENT_ROOT'] . '/models/User.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/SessionUser.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/responses/ResponseHandler.php'; 

require_once $_SERVER['DOCUMENT_ROOT'] . '/entities/User.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/entities/SessionUser.php';

use App\Entities\User as UserEntity;
use App\Entities\SessionUser as SessionUserEntity;
use Exception;

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

    public function login()
    {
        $data = json_decode(file_get_contents("php://input"), true);
        
        if (!isset($data[UserEntity::EMAIL]) || !isset($data[UserEntity::PASSWORD])) {
            $this->responseHandler->sendFailure("Los campos email y password son obligatorios", 400);
            return;
        }

        $email = $data[UserEntity::EMAIL];
        $password = $data[UserEntity::PASSWORD];

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->responseHandler->sendFailure("Formato de correo inválido", 400);
            return;
        }

        try {
            $user = $this->userModel->findUserByEmail($email);

            if (!$user) {
                $this->responseHandler->sendFailure("Credenciales inválidas", 401);
                return;
            }

            if ($password !== $user[UserEntity::PASSWORD]) {
                $this->responseHandler->sendFailure("Credenciales inválidas", 401);
                return;
            }

            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user[UserEntity::ID];
            
            $ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown IP';
            $this->sessionUserModel->createSession([
                'ip' => $ip,
                'id_user' => $user[UserEntity::ID]
            ]);

            unset($user[UserEntity::PASSWORD]);

            $this->responseHandler->sendSuccess(
                ["user" => $user],
                "Login exitoso"
            );
        } catch (Exception $e) {
            $this->responseHandler->sendFailure("Error al procesar el login.", 500, $e);
        }
    }

    public function logout()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (isset($_SESSION['user_id'])) {
            session_unset();
            session_destroy();
            setcookie(session_name(), '', time() - 3600, '/');
            $this->responseHandler->sendSuccess(null, "Logout exitoso.");
        } else {
            $this->responseHandler->sendSuccess(null, "No tenías una sesión activa.");
        }
    }
}