<?php

// Incluir los modelos y la entidad necesaria
require_once 'models/User.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/SessionUser.php';
require_once 'entities/User.php';

use App\Entities\User as UserEntity;

class UserLoginController
{
    private $userModel;
    private $sessionUserModel;

    public function __construct()
    {
        $this->userModel = new User();
        $this->sessionUserModel = new SessionUser();
    }

    public function login()
    {
        // Obtener datos del cuerpo de la solicitud
        $data = json_decode(file_get_contents("php://input"), true);
        
        // Validar la existencia de los campos
        if (!isset($data[UserEntity::EMAIL]) || !isset($data[UserEntity::PASSWORD])) {
            http_response_code(400); // Bad Request
            echo json_encode(['status' => 'error', 'message' => 'Los campos email y password son obligatorios']);
            return;
        }

        $email = $data[UserEntity::EMAIL];
        $password = $data[UserEntity::PASSWORD];

        // Validar el formato del correo
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Formato de correo invalido']);
            return;
        }

        // Buscar el usuario en la base de datos
        $user = $this->userModel->findUserByEmail($email);

        if (!$user) {
            http_response_code(401); // Unauthorized
            echo json_encode(['status' => 'error', 'message' => 'Credenciales invalidas']);
            return;
        }

        // Verificar la contraseña usando password_verify()
        //if (!password_verify($password, $user[UserEntity::PASSWORD])) {
        if ($password !== $user[UserEntity::PASSWORD]) {
            http_response_code(401);
            echo json_encode(['status' => 'error', 'message' => 'Credenciales invalidas']);
            return;
        }

        // Configurar sesión
        session_start();
        session_regenerate_id(true);

        $_SESSION['user_id'] = $user[UserEntity::ID];
        
        // Registrar la sesión en la tabla `sesion_user`
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown IP';
        $this->sessionUserModel->createSession([
            'ip' => $ip,
            'id_user' => $user[UserEntity::ID]
        ]);

        // Preparar y devolver la respuesta con los datos requeridos
        http_response_code(200);
        echo json_encode([
            "status" => "success",
            "message" => "Login exitoso",
            "user" => [
                "id" => (int)$user[UserEntity::ID],
                "full_name" => $user[UserEntity::FULL_NAME],
                "office" => $user[UserEntity::OFFICE],
                "email" => $user[UserEntity::EMAIL],
                "is_professor" => (int)$user[UserEntity::IS_PROFESSOR],
                "is_level_coordinator" => (int)$user[UserEntity::IS_LEVEL_COORDINATOR],
                "is_administrator" => (int)$user[UserEntity::IS_ADMINISTRATOR]
            ]
        ]);
    }

    public function logout()
    {
        // Lógica de logout 
        session_start();
        
        if (isset($_SESSION['user_id'])) {
            // Eliminar la sesión de la base de datos (opcional, pero buena práctica)
            // $this->sessionUserModel->deleteSession($_SESSION['user_id']); dejamos pendiente

            session_unset();
            session_destroy();
            setcookie(session_name(), '', time() - 3600, '/');

            http_response_code(200);
            echo json_encode([
                "status" => "success",
                "message" => "Logout exitoso."
            ]);
        } else {
            http_response_code(200);
            echo json_encode([
                "status" => "success",
                "message" => "No tenías una sesión activa."
            ]);
        }
    }
}