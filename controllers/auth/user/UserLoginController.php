<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/User.php';

class UserLoginController
{
    public static function login($data)
    {

        // Validación básica
        if (empty($data['email']) || empty($data['password'])) {
            http_response_code(400);
            echo json_encode(["error" => "'email' y 'password' son obligatorios."]);
            return;
        }

        // Validar formato del correo
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo json_encode(["error" => "Email inválido."]);
            return;
        }

        // Buscar estudiante por email
        $userModel = new User();
        $user = $userModel->obtenerPorEmail($data['email']);

        if (!$user) {
            http_response_code(401);
            echo json_encode(["error" => "Credenciales inválidas."]);
            return;
        }

        // Verificar contraseña en texto plano
        if ($data['password'] !== $user['password']) {
            http_response_code(401);
            echo json_encode(["error" => "Credenciales inválidas."]);
            return;
        }

        // Configurar duración de la sesión a 3 horas (el parametro es en segundos) 1 miuto=60
        ini_set('session.gc_maxlifetime', 10800);
        session_set_cookie_params(10800);
        session_start();

        // Guardar datos de sesión
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['first_names'] . ' ' . $user['last_name'];
        $_SESSION['login_time'] = time(); // Hora de inicio

        http_response_code(200);
        echo json_encode([
            "message" => "Login exitoso.",
            "user" => [
                "id" => $user['id'],
                "name" => $_SESSION['user_name'],
                "email" => $user['email'],
                "is_administrator" => $user['is_administrator'],
                "is_level_coordinator" => $user['is_level_coordinator'],
                "is_professor" => $user['is_professor']
            ]
        ]);
    }

    public static function logout()
    {
        // Si no hay sesion ni cookie.
        if (session_status() === PHP_SESSION_NONE && isset($_COOKIE[session_name()])) {
            session_start();
        }

        if (session_status() === PHP_SESSION_ACTIVE) {
            // Elimina variables de sesión
            session_unset();

            // Destruye la sesión en el servidor
            session_destroy();

            // Borra la cookie de sesión en el navegador
            if (isset($_COOKIE['PHPSESSID'])) {
                setcookie('PHPSESSID', '', time() - 3600, '/');
            }

            http_response_code(200);
            echo json_encode(["message" => "Logout exitoso."]);
        } else {
            http_response_code(200);
            echo json_encode(["message" => "Ya estabas deslogueado o no tenías una sesión activa."]);
        }
    }

}