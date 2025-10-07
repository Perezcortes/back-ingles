<?php
// controllers/StudentController.php

require_once $_SERVER['DOCUMENT_ROOT'] . '/models/Student.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/Major.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/Level.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/EnglishClass.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/User.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/responses/ResponseHandler.php'; // manejador de respuestas

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
    private $userModel;
    private $responseHandler; // Propiedad para el manejador de respuestas

    public function __construct()
    {
        $this->studentModel = new Student();
        $this->majorModel = new Major();
        $this->levelModel = new Level();
        $this->englishClassModel = new EnglishClass();
        $this->userModel = new User();
        $this->responseHandler = new ResponseHandler(); // Instanciado
    }

    public function getAll()
    {
        try {
            $students = $this->studentModel->getAll();
            $this->responseHandler->sendSuccess(["students" => $students], "Alumnos encontrados exitosamente");
        } catch (Exception $e) {
            $this->responseHandler->sendFailure("Error al obtener los registros.", 500, $e);
        }
    }

    public function getStudentById($id) {
        if (!is_numeric($id)) {
            $this->responseHandler->sendFailure("El ID debe ser numérico.", 400);
            return;
        }
        try {
            $student = $this->studentModel->getById($id);
            if ($student) {
                unset($student['password']);
                $this->responseHandler->sendSuccess(["student" => $student], "Alumno encontrado exitosamente");
            } else {
                $this->responseHandler->sendFailure("Alumno no encontrado", 404);
            }
        } catch (Exception $e) {
            $this->responseHandler->sendFailure("Error al obtener el registro del alumno.", 500, $e);
        }
    }

    public function getStudentByEmail($data) {
        if (!isset($data[StudentEntity::EMAIL]) || !filter_var($data[StudentEntity::EMAIL], FILTER_VALIDATE_EMAIL)) {
            $this->responseHandler->sendFailure("El correo electrónico no es válido.", 400);
            return;
        }
        $email = $data[StudentEntity::EMAIL];
        try {
            $student = $this->studentModel->findStudentByEmail($email);
            if ($student) {
                unset($student['password']);
                $this->responseHandler->sendSuccess(["student" => $student], "Alumno encontrado exitosamente");
            } else {
                $this->responseHandler->sendFailure("Alumno no encontrado", 404);
            }
        } catch (Exception $e) {
            $this->responseHandler->sendFailure("Error al obtener el registro del alumno.", 500, $e);
        }
    }

    public function getStudentByMatricula($data) {
        if (!isset($data[StudentEntity::MATRICULA]) || empty($data[StudentEntity::MATRICULA])) {
            $this->responseHandler->sendFailure("El campo 'matricula' es obligatorio.", 400);
            return;
        }
        $matricula = $data[StudentEntity::MATRICULA];
        try {
            $student = $this->studentModel->findStudentByMatricula($matricula);
            if ($student) {
                unset($student['password']);
                $this->responseHandler->sendSuccess(["student" => $student], "Alumno encontrado exitosamente");
            } else {
                $this->responseHandler->sendFailure("Alumno no encontrado", 404);
            }
        } catch (Exception $e) {
            $this->responseHandler->sendFailure("Error al obtener el registro del alumno.", 500, $e);
        }
    }

    public function create($data)
    {
        $requiredVars = [StudentEntity::FULL_NAME, StudentEntity::ID_MAJOR, StudentEntity::ID_LEVEL, StudentEntity::MATRICULA, StudentEntity::EMAIL, StudentEntity::PASSWORD];
        foreach ($requiredVars as $var) {
            if (!isset($data[$var]) || trim($data[$var]) === '') {
                $this->responseHandler->sendFailure("Falta el campo obligatorio: '{$var}'.", 400);
                return;
            }
        }

        if (!$this->majorModel->getById($data[StudentEntity::ID_MAJOR])) {
            $this->responseHandler->sendFailure("El id_major no existe.", 404);
            return;
        }

        if (!$this->levelModel->getById($data[StudentEntity::ID_LEVEL])) {
            $this->responseHandler->sendFailure("El id_level no existe.", 404);
            return;
        }

        if (isset($data[StudentEntity::ID_ENGLISH_CLASS]) && $data[StudentEntity::ID_ENGLISH_CLASS] !== null) {
            if (!$this->englishClassModel->getById($data[StudentEntity::ID_ENGLISH_CLASS])) {
                $this->responseHandler->sendFailure("El ID de clase de inglés (id_english_class) no existe.", 404);
                return;
            }
        }

        if ($this->studentModel->findStudentByEmail($data[StudentEntity::EMAIL])) {
            $this->responseHandler->sendFailure("El email ya está en uso.", 409);
            return;
        }
        if ($this->studentModel->findStudentByMatricula($data[StudentEntity::MATRICULA])) {
            $this->responseHandler->sendFailure("La matrícula ya está en uso.", 409);
            return;
        }

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
                unset($newStudent[StudentEntity::PASSWORD]);
                $this->responseHandler->sendSuccess(["student" => $newStudent], "Alumno creado exitosamente", 201);
            } else {
                $this->responseHandler->sendFailure("Error al crear el estudiante.", 500);
            }
        } catch (Exception $e) {
            $this->responseHandler->sendFailure("Error al crear el estudiante.", 500, $e);
        }
    }

    public function update($id, $data) {
        if (!is_numeric($id)) {
            $this->responseHandler->sendFailure("El ID debe ser numérico.", 400);
            return;
        }
        if (empty($data)) {
            $this->responseHandler->sendFailure("Se requiere al menos un campo para actualizar.", 400);
            return;
        }
        try {
            $existingStudent = $this->studentModel->getById($id);
            if (!$existingStudent) {
                $this->responseHandler->sendFailure("Alumno no encontrado.", 404);
                return;
            }
            $updated = $this->studentModel->updateById($id, $data);
            if ($updated) {
                $updatedStudent = $this->studentModel->getById($id);
                unset($updatedStudent['password']);
                $this->responseHandler->sendSuccess(["student" => $updatedStudent], "Alumno actualizado exitosamente");
            } else {
                $this->responseHandler->sendFailure("Error al actualizar el alumno.", 500);
            }
        } catch (Exception $e) {
            $this->responseHandler->sendFailure("Error al actualizar el alumno.", 500, $e);
        }
    }

    public function deleteOne($data) {
        if (!isset($data['id']) || !is_numeric($data['id'])) {
            $this->responseHandler->sendFailure("Se requiere un ID numérico para eliminar el alumno.", 400);
            return;
        }
        $id = $data['id'];
        try {
            $existingStudent = $this->studentModel->getById($id);
            if (!$existingStudent) {
                $this->responseHandler->sendFailure("Alumno no encontrado o ya eliminado.", 404);
                return;
            }
            $deleted = $this->studentModel->deleteById($id);
            if ($deleted) {
                $this->responseHandler->sendSuccess(["student" => $existingStudent], "Alumno eliminado exitosamente");
            } else {
                $this->responseHandler->sendFailure("Error al eliminar el alumno.", 500);
            }
        } catch (Exception $e) {
            $this->responseHandler->sendFailure("Error al eliminar el alumno.", 500, $e);
        }
    }

    public function deleteAll()
    {
        try {
            $deleted = $this->studentModel->deleteAll();
            if ($deleted) {
                $this->responseHandler->sendSuccess(null, "Alumnos eliminados exitosamente");
            } else {
                $this->responseHandler->sendFailure("Error al eliminar todos los alumnos.", 500);
            }
        } catch (Exception $e) {
            $this->responseHandler->sendFailure("Error al eliminar todos los alumnos.", 500, $e);
        }
    }
}