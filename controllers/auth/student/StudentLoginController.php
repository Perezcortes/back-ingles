<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/models/Student.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/SessionStudent.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/entities/Student.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/entities/SessionStudent.php';

use App\Entities\Student as StudentEntity;
use App\Entities\SessionStudent as SessionStudentEntity;

class StudentLoginController
{
    private $studentModel;
    private $sessionStudentModel;

    public function __construct()
    {
        $this->studentModel = new Student();
        $this->sessionStudentModel = new SessionStudent();
    }

    public function login($data)
    {
        if (!isset($data[StudentEntity::EMAIL]) || !isset($data[StudentEntity::PASSWORD])) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Email y password son obligatorios.']);
            return;
        }

        $email = $data[StudentEntity::EMAIL];
        $password = $data[StudentEntity::PASSWORD];
        
        // Verifica que el email tenga un formato válido
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Formato de email inválido.']);
            return;
        }

        $student = $this->studentModel->findStudentByEmail($email);

        if (!$student) {
            http_response_code(401);
            echo json_encode(['status' => 'error', 'message' => 'Credenciales inválidas.']);
            return;
        }

        // Validación de contraseña en texto plano 
        if ($password !== $student[StudentEntity::PASSWORD]) {
            http_response_code(401);
            echo json_encode(['status' => 'error', 'message' => 'Credenciales inválidas.']);
            return;
        }

        // Inicia la sesión
        session_start();
        session_regenerate_id(true);

        $_SESSION['student_id'] = $student[StudentEntity::ID];

        // Establece la duración de la cookie de sesión en 3 horas (3 * 60 * 60 segundos)
        ini_set('session.gc_maxlifetime', 10800);
        session_set_cookie_params(10800);

        // Registra la sesión en la base de datos
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown IP';
        $this->sessionStudentModel->createSession([
            SessionStudentEntity::IP => $ip,
            SessionStudentEntity::ID_STUDENT => $student[StudentEntity::ID]
        ]);

        http_response_code(200);
        echo json_encode([
            "status" => "success",
            "message" => "Login exitoso",
            "student" => [
                "id" => (int)$student[StudentEntity::ID],
                "full_name" => $student[StudentEntity::FULL_NAME],
                "id_major" => (int)$student[StudentEntity::ID_MAJOR],
                "id_level" => (int)$student[StudentEntity::ID_LEVEL],
                "major_group" => $student[StudentEntity::MAJOR_GROUP],
                "id_english_class" => (int)$student[StudentEntity::ID_ENGLISH_CLASS],
                "matricula" => $student[StudentEntity::MATRICULA],
                "period" => $student[StudentEntity::PERIOD],
                "email" => $student[StudentEntity::EMAIL]
            ]
        ]);
    }
    
    public function logout()
    {
        // Lógica de logout
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (isset($_SESSION['student_id'])) {
            // Opcional: Eliminar la sesión de la tabla sesion_student
            // $this->sessionStudentModel->deleteSession($_SESSION['student_id']);

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