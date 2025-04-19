<?php
require_once __DIR__ . '/../models/Student.php';
require_once __DIR__ . '/../middlewares/AuthenticationMiddleware.php';

class StudentLoginController
{
    public static function login($data)
    {

        // Validación básica
        if (empty($data['email']) || empty($data['password'])) {
            http_response_code(400);
            echo json_encode(["error" => "'email' y 'password' son obligatorios."]);
            return;
        }

        // Buscar estudiante por email
        $studentModel = new Student();
        $student = $studentModel->obtenerPorEmail($data['email']);

        if (!$student) {
            http_response_code(401);
            echo json_encode(["error" => "Credenciales inválidas."]);
            return;
        }

        // Verificar contraseña en texto plano
        if ($data['password'] !== $student['password']) {
            http_response_code(401);
            echo json_encode(["error" => "Credenciales inválidas."]);
            return;
        }

        // Configurar duración de la sesión a 3 horas
        ini_set('session.gc_maxlifetime', 10800);
        session_set_cookie_params(10800);
        session_start();

        // Guardar datos de sesión
        $_SESSION['student_id'] = $student['id'];
        $_SESSION['student_name'] = $student['first_names'] . ' ' . $student['last_name'];
        $_SESSION['login_time'] = time(); // Hora de inicio

        http_response_code(200);
        echo json_encode([
            "message" => "Login exitoso.",
            "student" => [
                "id" => $student['id'],
                "name" => $_SESSION['student_name'],
                "email" => $student['email'],
                "matricula" => $student["matricula"],
            ]
        ]);
    }

    public static function logout()
    {
        // Inicia la sesión si aún no está iniciada
        session_start();

        // Elimina todas las variables de sesión
        session_unset();

        // Destruye la sesión
        session_destroy();

        // Elimina la cookie de la sesión en el navegador
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/'); // Eliminar la cookie con un tiempo pasado
        }

        // Respuesta de éxito
        http_response_code(200);
        echo json_encode(["message" => "Logout exitoso."]);
    }

}