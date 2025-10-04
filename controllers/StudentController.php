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

    public function getStudentByMatricula($data) { // Ahora recibe los datos del cuerpo de la petición
        // Validar la existencia de la matrícula
        if (!isset($data[StudentEntity::MATRICULA]) || empty($data[StudentEntity::MATRICULA])) {
            $this->sendError(400, "El campo 'matricula' es obligatorio.");
            return;
        }

        $matricula = $data[StudentEntity::MATRICULA];

        // Buscar el alumno por matrícula
        try {
            $student = $this->studentModel->findStudentByMatricula($matricula);

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


    // Método para actualizar un alumno
    public function update($id, $data) {
        // Validar el ID
        if (!is_numeric($id)) {
            $this->sendError(400, "El ID debe ser numérico.");
            return;
        }

        // Validar que al menos un campo para actualizar esté presente
        if (empty($data)) {
            $this->sendError(400, "Se requiere al menos un campo para actualizar.");
            return;
        }

        // Validar que el alumno exista
        try {
            $existingStudent = $this->studentModel->getById($id);
            if (!$existingStudent) {
                $this->sendError(404, "Alumno no encontrado.");
                return;
            }

            // Lógica para manejar la actualización
            $updated = $this->studentModel->updateById($id, $data);

            if ($updated) {
                $updatedStudent = $this->studentModel->getById($id);
                unset($updatedStudent['password']); // Excluye la contraseña de la respuesta

                http_response_code(200);
                echo json_encode([
                    "status" => "success",
                    "message" => "Alumno actualizado exitosamente",
                    "student" => $updatedStudent
                ]);
            } else {
                $this->sendError(500, "Error al actualizar el alumno.");
            }
        } catch (Exception $e) {
            $this->sendError(500, "Error al actualizar el alumno.", $e);
        }
    }

    // Método para eliminar un alumno lógicamente
    public function deleteOne($data) {
        // Validar el ID en el cuerpo de la petición
        if (!isset($data['id']) || !is_numeric($data['id'])) {
            $this->sendError(400, "Se requiere un ID numérico para eliminar el alumno.");
            return;
        }

        $id = $data['id'];

        // Validar que el alumno exista y no esté ya eliminado
        try {
            $existingStudent = $this->studentModel->getById($id);
            if (!$existingStudent) {
                $this->sendError(404, "Alumno no encontrado o ya eliminado.");
                return;
            }

            // Realizar el borrado lógico
            $deleted = $this->studentModel->deleteById($id);

            if ($deleted) {
                http_response_code(200);
                echo json_encode([
                    "status" => "success",
                    "message" => "Alumno eliminado exitosamente",
                    "student" => $existingStudent
                ]);
            } else {
                $this->sendError(500, "Error al eliminar el alumno.");
            }
        } catch (Exception $e) {
            $this->sendError(500, "Error al eliminar el alumno.", $e);
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
