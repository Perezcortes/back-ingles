<?php

// controllers/auth/student/StudentLoginController.php

require_once $_SERVER['DOCUMENT_ROOT'] . '/models/Student.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/SessionStudent.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/responses/ResponseHandler.php'; 

require_once $_SERVER['DOCUMENT_ROOT'] . '/entities/Student.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/entities/SessionStudent.php';

use App\Entities\Student as StudentEntity;
use App\Entities\SessionStudent as SessionStudentEntity;
use Exception;

class StudentLoginController
{
    private $studentModel;
    private $sessionStudentModel;
    private $responseHandler; 

    public function __construct()
    {
        $this->studentModel = new Student();
        $this->sessionStudentModel = new SessionStudent();
        $this->responseHandler = new ResponseHandler(); 
    }

    public function login($data)
    {
        if (!isset($data[StudentEntity::EMAIL]) || !isset($data[StudentEntity::PASSWORD])) {
            $this->responseHandler->sendFailure("Email y password son obligatorios.", 400);
            return;
        }

        $email = $data[StudentEntity::EMAIL];
        $password = $data[StudentEntity::PASSWORD];

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->responseHandler->sendFailure("Formato de email inválido.", 400);
            return;
        }

        try {
            $student = $this->studentModel->findStudentByEmail($email);

            if (!$student) {
                $this->responseHandler->sendFailure("Credenciales inválidas.", 401);
                return;
            }

            if ($password !== $student[StudentEntity::PASSWORD]) {
                $this->responseHandler->sendFailure("Credenciales inválidas.", 401);
                return;
            }

            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            session_regenerate_id(true);
            $_SESSION['student_id'] = $student[StudentEntity::ID];
            ini_set('session.gc_maxlifetime', 10800);
            session_set_cookie_params(10800);

            $ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown IP';
            $this->sessionStudentModel->createSession([
                SessionStudentEntity::IP => $ip,
                SessionStudentEntity::ID_STUDENT => $student[StudentEntity::ID]
            ]);

            // Elimina la contraseña de la respuesta
            unset($student[StudentEntity::PASSWORD]);

            $this->responseHandler->sendSuccess(
                ["student" => $student],
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

        if (isset($_SESSION['student_id'])) {
            session_unset();
            session_destroy();
            setcookie(session_name(), '', time() - 3600, '/');
            $this->responseHandler->sendSuccess(null, "Logout exitoso.");
        } else {
            $this->responseHandler->sendSuccess(null, "No tenías una sesión activa.");
        }
    }
}