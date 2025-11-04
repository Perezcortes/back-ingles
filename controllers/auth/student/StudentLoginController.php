<?php

// controllers/auth/student/StudentLoginController.php

require_once $_SERVER['DOCUMENT_ROOT'] . '/models/Student.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/SessionStudent.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/responses/ResponseHandler.php';

require_once $_SERVER['DOCUMENT_ROOT'] . '/entities/Student.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/entities/SessionStudent.php';

use App\Entities\Student as StudentEntity;
use App\Entities\SessionStudent as SessionStudentEntity;

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
        // Validación inicial de campos obligatorios
        if (!isset($data[StudentEntity::EMAIL]) || !isset($data[StudentEntity::PASSWORD])) {
            $this->responseHandler->sendFailure("Los campos 'email' y 'password' son obligatorios", 400);
            return;
        }

        $email = $data[StudentEntity::EMAIL];
        $password = $data[StudentEntity::PASSWORD];

        // Validación del formato del email (seguridad básica)
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->responseHandler->sendFailure("Formato de campo 'email' inválido.", 400);
            return;
        }

        try {
            //Búsqueda del estudiante por email
            $student = $this->studentModel->findStudentByEmail($email);

            //Si el student es false
            if (!$student) {
                $this->responseHandler->sendFailure("Credenciales inválidas.", 401);
                return;
            }

            //Si las contraseñas no coinciden
            if ($password !== $student[StudentEntity::PASSWORD]) {
                $this->responseHandler->sendFailure( "Credenciales inválidas.", 401);
                return;
            }
            
            //Si todo ok
            ini_set('session.gc_maxlifetime', 10800); // 3 horas en el servidor
            session_set_cookie_params(10800); // 3 horas en la cookie del cliente

            //Aseguramos una sesión
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            //Invalida cualquier ID de sesión anterior y emite uno nuevo.
            session_regenerate_id(true);

            //Almacena el ID del estudiante 
            $_SESSION['student_id'] = $student[StudentEntity::ID];

            //Registra la dirección remota desde donde se inició la sesión.
            $ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown IP';

            //Insertamos la ip del estudiante en la DB
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
        } catch (\Exception $e) {
            $this->responseHandler->sendFailure("Error al procesar el login. Revisar Logs del Server.", 500, $e);
        }
    }

    /**
     * Cierra la sesión activa del estudiante.
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

        // Verifica si existe una sesión de estudiante para cerrar.
        if (isset($_SESSION['student_id'])) {
            
            $studentId = $_SESSION['student_id'];
            
            // Destrucción de la Sesión en el Servidor
            session_unset();    
            session_destroy();   
            
            // Eliminación de la Cookie en el Cliente
            setcookie(session_name(), '', time() - 3600, '/');
            
            // Llama al modelo para marcar la sesión como finalizada en la base de datos (soft-delete).
            try {
                $this->sessionStudentModel->deleteSession($studentId);
            } catch (\Exception $e) {
                // Si el log de logout falla, registramos el error, pero permitimos que el logout HTTP continúe.
                error_log("Error al registrar el LOGOUT en DB: " . $e->getMessage());
            }

            $this->responseHandler->sendSuccess(null, "Logout exitoso.");
        } else {
            // Respuesta si el usuario llama a logout sin una sesión activa.
            $this->responseHandler->sendSuccess(null, "No tenías una sesión activa.");
        }
    }
}