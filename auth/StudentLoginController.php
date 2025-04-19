<?php
require_once __DIR__ . '/../models/Student.php';

class StudentLoginController 
{
    public static function login($email, $password)
    {
        // Validación básica
        if (empty($email) || empty($password)) {
            http_response_code(400);
            echo json_encode(["error" => "Email y contraseña son obligatorios."]);
            return;
        }

        // Buscar estudiante por email
        $studentModel = new Student();
        $student = $studentModel->obtenerPorEmail($email);

        if (!$student) {
            http_response_code(401);
            echo json_encode(["error" => "Credenciales inválidas."]);
            return;
        }

        // Verificar contraseña en texto plano
        if ($password !== $student['password']) {
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

        // Respuesta de éxito
        http_response_code(200);
        echo json_encode(["message" => "Logout exitoso."]);
    }

}