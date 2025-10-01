<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/User.php';

class UserLoginController
{
    public static function login($data)
    {

        // Validación básica
        if (empty($data['email']) || empty($data['password'])) {
            echo json_encode([
                "status" => "failure",
                "message" => "Los campos 'email' y 'password' son obligatorios."]);
            return;
        }

        // Validar formato del correo
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            echo json_encode([
                "status" => "failure",
                "message" => "Campo 'email' inválido."]);
            return;
        }

        // Buscar user por email
        $userModel = new User();
        $user = $userModel->obtenerPorEmail($data['email']);

        if (!$user) {
            echo json_encode([
                "status" => "failure",
                "message" => "Credenciales inválidas."]);
            return;
        }

        // Verificar contraseña en texto plano
        if ($data['password'] !== $user['password']) {
            echo json_encode([
                "status" => "failure",
                "message" => "Credenciales inválidas."]);
            return;
        }

        // Configurar duración de la sesión a 8 horas (el parametro es en segundos) 1 minuto = 60 segundos
        ini_set('session.gc_maxlifetime', 28800);
        session_set_cookie_params(28800);
        session_start();

        // Guardar datos de sesión
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['login_time'] = time(); // Hora de inicio

        echo json_encode([
            "status" => "success",
            "message" => "Login exitoso.",
            "user" => [
                "id" => $user['id'],
                "name" => $_SESSION['full_name'],
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

            echo json_encode([
                "status" => "success",
                "message" => "Logout exitoso."]);
        } else {
            echo json_encode([
                "status" => "success",
                "message" => "Ya estabas deslogueado o no tenías una sesión activa."]);
        }
    }

}