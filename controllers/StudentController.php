<?php

// controllers/StudentController.php

require_once $_SERVER['DOCUMENT_ROOT'] . '/models/Student.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/Major.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/Level.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/EnglishClass.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/entities/Student.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/entities/Major.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/entities/Level.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/entities/EnglishClass.php';

use App\Entities\Student as StudentEntity;
use App\Entities\EnglishClass as EnglishClassEntity;
use Exception;

class StudentController
{
    private $studentModel;
    private $majorModel;
    private $levelModel;
    private $englishClassModel;

    public function __construct()
    {
        $this->studentModel = new Student();
        $this->majorModel = new Major();
        $this->levelModel = new Level();
        $this->englishClassModel = new EnglishClass();
    }

    // Método para obtener todos los alumnos
    public function getAll()
    {
        try {
            $students = $this->studentModel->getAll();

            http_response_code(200);
            echo json_encode([
                "status" => "success",
                "message" => "Alumnos encontrados exitosamente",
                "students" => $students
            ]);
        } catch (Exception $e) {
            $this->sendError(500, "Error al obtener los registros de la tabla.", $e);
        }
    }

    public function getStudentById($id) {
        // Validation: The ID must be a numeric value
        if (!is_numeric($id)) {
            $this->sendError(400, "El ID debe ser numérico.");
            return;
        }

        try {
            $student = $this->studentModel->getById($id);

            if ($student) {
                unset($student['password']); // remover la contraseña antes de enviar la respuesta
                http_response_code(200);
                echo json_encode([
                    "status" => "success",
                    "message" => "Alumno encontrado exitosamente",
                    "student" => $student
                ]);
            } else {
                $this->sendError(404, "Alumno no encontrado");
            }
        } catch (Exception $e) {
            $this->sendError(500, "Error al obtener el registro del alumno.", $e);
        }
    }

    public function getStudentByEmail($data) { // Ahora recibe los datos del cuerpo de la petición
        // Validar la existencia y formato del email
        if (!isset($data[StudentEntity::EMAIL]) || !filter_var($data[StudentEntity::EMAIL], FILTER_VALIDATE_EMAIL)) {
            $this->sendError(400, "El correo electrónico no es válido.");
            return;
        }

        $email = $data[StudentEntity::EMAIL];

        // Buscar el alumno por email
        try {
            $student = $this->studentModel->findStudentByEmail($email);

            if ($student) {
                unset($student['password']); // Excluye la contraseña de la respuesta
                http_response_code(200);
                echo json_encode([
                    "status" => "success",
                    "message" => "Alumno encontrado exitosamente",
                    "student" => $student
                ]);
            } else {
                $this->sendError(404, "Alumno no encontrado");
            }
        } catch (Exception $e) {
            $this->sendError(500, "Error al obtener el registro del alumno.", $e);
        }
    }

    public static function getStudentByMatricula($matricula)
    {
        try {
            $studentModel = new Student();
            $student = $studentModel->obtenerPorMatricula($matricula);

            if ($student) {
                http_response_code(200);
                echo json_encode($student);
            } else {
                self::sendError(404, "Student no encontrado");
            }
        } catch (Exception $e) {
            self::sendError(500, "Error al obtener el registro student", $e);
        }
    }

    // Método CREATE - Actualizado
    public function create($data)
    {
        $requiredVars = [
            StudentEntity::FULL_NAME,
            StudentEntity::ID_MAJOR,
            StudentEntity::ID_LEVEL,
            StudentEntity::MATRICULA,
            StudentEntity::EMAIL,
            StudentEntity::PASSWORD
        ];

        foreach ($requiredVars as $var) {
            if (!isset($data[$var]) || trim($data[$var]) === '') {
                //http_response_code(400);
                $this->sendError(400, "Falta el campo obligatorio: '{$var}'.");
                return;
            }
        }

        // Validación de existencia de IDs relacionados
        if (!$this->majorModel->getById($data[StudentEntity::ID_MAJOR])) {
            $this->sendError(404, "El id_major no existe.");
            return;
        }

        if (!$this->levelModel->getById($data[StudentEntity::ID_LEVEL])) {
            $this->sendError(404, "El id_level no existe.");
            return;
        }

        if (isset($data[StudentEntity::ID_ENGLISH_CLASS]) && $data[StudentEntity::ID_ENGLISH_CLASS] !== null) {
            if (!$this->englishClassModel->getById($data[StudentEntity::ID_ENGLISH_CLASS])) {
                http_response_code(404);
                echo json_encode(["status" => "error", "message" => "El ID de clase de inglés (id_english_class) no existe."]);
                return;
            }
        }

        // Validación de unicidad de email y matrícula
        if ($this->studentModel->findStudentByEmail($data[StudentEntity::EMAIL])) {
            $this->sendError(409, "El email ya está en uso.");
            return;
        }

        if ($this->studentModel->findStudentByMatricula($data[StudentEntity::MATRICULA])) {
            $this->sendError(409, "La matrícula ya está en uso.");
            return;
        }

        // No se hashea la contraseña por decisión de equipo
        $studentData = [
            StudentEntity::FULL_NAME => $data[StudentEntity::FULL_NAME],
            StudentEntity::ID_MAJOR => $data[StudentEntity::ID_MAJOR],
            StudentEntity::ID_LEVEL => $data[StudentEntity::ID_LEVEL],
            StudentEntity::MAJOR_GROUP => $data[StudentEntity::MAJOR_GROUP] ?? null,
            StudentEntity::ID_ENGLISH_CLASS => $data[StudentEntity::ID_ENGLISH_CLASS] ?? null,
            StudentEntity::MATRICULA => $data[StudentEntity::MATRICULA],
            StudentEntity::PERIOD => $data[StudentEntity::PERIOD] ?? null,
            StudentEntity::EMAIL => $data[StudentEntity::EMAIL],
            StudentEntity::PASSWORD => $data[StudentEntity::PASSWORD]
        ];

        try {
            $createdId = $this->studentModel->create($studentData);
            if ($createdId) {
                $newStudent = $this->studentModel->getById($createdId);
                unset($newStudent[StudentEntity::PASSWORD]); // Ocultar la contraseña

                http_response_code(201);
                echo json_encode([
                    "status" => "success",
                    "message" => "Alumno creado exitosamente",
                    "student" => $newStudent
                ]);
            } else {
                $this->sendError(500, "Error al crear el estudiante.");
            }
        } catch (Exception $e) {
            $this->sendError(500, "Error al crear el estudiante.", $e);
        }
    }


    public static function update($id, $data)
    {
        if (!is_numeric($id)) {
            return self::sendError(400, "El id debe ser numérico.");
        }

        if (empty($data)) {
            return self::sendError(400, "Se requiere al menos un campo para actualizar el estudiante.");
        }

        try {
            $studentModel = new Student();
            $studentExistente = $studentModel->obtenerPorId($id);

            if (!$studentExistente) {
                return self::sendError(404, "No se encontró el student con id: $id");
            }

            $actualizado = $studentModel->actualizarPorId($id, $data);

            if ($actualizado) {
                $studentActualizado = $studentModel->obtenerPorId($id);

                http_response_code(200);
                echo json_encode([
                    "message" => "Estudiante actualizado exitosamente",
                    "student" => $studentActualizado
                ]);
            } else {
                self::sendError(500, "Error interno al intentar actualizar el student.");
            }
        } catch (Exception $e) {
            self::sendError(500, "Error al actualizar el student", $e);
        }
    }

    public static function deleteOne($id)
    {
        if (!is_numeric($id)) {
            return self::sendError(400, "El id debe ser numérico.");
        }

        try {
            $studentModel = new Student();

            // 1. Obtener el registro antes de eliminar
            $student = $studentModel->obtenerPorId($id);
            if (!$student) {
                return self::sendError(404, "No se encontró el nivel con id: $id");
            }

            // 2. Eliminarlo
            $eliminado = $studentModel->eliminarPorId($id);

            if ($eliminado) {
                http_response_code(200);
                echo json_encode([
                    "message" => "Student eliminado exitosamente.",
                    "student" => $student
                ]);
            } else {
                self::sendError(500, "Error interno al intentar eliminar el Student.");
            }
        } catch (Exception $e) {
            self::sendError(500, "Error al eliminar el Student.", $e);
        }
    }

    // Helper for error response
    private static function sendError($code, $message, $exception = null)
    {
        http_response_code($code);
        $response = ["error" => $message];

        if (getenv('APP_ENV') === 'development' && $exception) {
            $response["details"] = $exception->getMessage();
        }

        echo json_encode($response);
    }
}
