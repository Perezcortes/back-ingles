<?php
// controllers/StudentController.php

require_once $_SERVER['DOCUMENT_ROOT'] . '/models/Student.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/Major.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/Level.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/EnglishClass.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/responses/ResponseHandler.php';

require_once $_SERVER['DOCUMENT_ROOT'] . '/entities/Student.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/entities/Major.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/entities/Level.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/entities/EnglishClass.php';

use App\Entities\Student as StudentEntity;

class StudentController
{
    private $studentModel;
    private $majorModel;
    private $levelModel;
    private $englishClassModel;
    private $responseHandler;

    public function __construct()
    {
        $this->studentModel = new Student();
        $this->majorModel = new Major();
        $this->levelModel = new Level();
        $this->englishClassModel = new EnglishClass();
        $this->responseHandler = new ResponseHandler();
    }

    /**
     * Obtiene todos los registros de estudiantes.
     * @return void
     */
    public function getAll()
    {
        try {
            $students = $this->studentModel->getAll();
            $this->responseHandler->sendSuccess(["students" => $students], "Estudiantes encontrados exitosamente.");
        } catch (Exception $e) {
            $this->responseHandler->sendFailure("Error al obtener los registros de estudiantes.", 500, $e);
        }
    }

    /**
     * Obtiene un estudiante por su ID.
     * @param int $id El ID del estudiante.
     * @return void
     */
    public function getOne($id)
    {
        if (!is_numeric($id)) {
            $this->responseHandler->sendFailure("El ID debe ser numérico.", 400);
            return;
        }

        try {
            $student = $this->studentModel->getById($id);

            if (!$student) {
                $this->responseHandler->sendFailure("Estudiante no encontrado.", 404);
                return;
            }

            unset($student[StudentEntity::PASSWORD]);
            $this->responseHandler->sendSuccess(["student" => $student], "Estudiante encontrado exitosamente.");
        } catch (Exception $e) {
            $this->responseHandler->sendFailure("Error al obtener el registro del estudiante.", 500, $e);
        }
    }

    /**
     * Obtiene un estudiante por su correo electrónico.
     * @param array $data Contiene el campo 'email' del estudiante.
     * @return void
     */
    public function getByEmail($data)
    {
        if (!isset($data[StudentEntity::EMAIL]) || !filter_var($data[StudentEntity::EMAIL], FILTER_VALIDATE_EMAIL)) {
            $this->responseHandler->sendFailure("El correo electrónico no es válido.", 400);
            return;
        }
        
        $email = $data[StudentEntity::EMAIL];
        
        try {
            $student = $this->studentModel->findStudentByEmail($email);

            if (!$student) {
                $this->responseHandler->sendFailure("Estudiante no encontrado.", 404);
                return;
            }

            unset($student[StudentEntity::PASSWORD]);
            $this->responseHandler->sendSuccess(["student" => $student], "Estudiante encontrado exitosamente.");
        } catch (Exception $e) {
            $this->responseHandler->sendFailure("Error al obtener el registro del estudiante.", 500, $e);
        }
    }

    /**
     * Obtiene un estudiante por su matrícula.
     * @param array $data Contiene el campo 'matricula' del estudiante.
     * @return void
     */
    public function getByMatricula($data)
    {
        if (empty($data[StudentEntity::MATRICULA])) {
            $this->responseHandler->sendFailure("El campo 'matricula' es obligatorio.", 400);
            return;
        }

        $matricula = $data[StudentEntity::MATRICULA];

        try {
            $student = $this->studentModel->findStudentByMatricula($matricula);

            if (!$student) {
                $this->responseHandler->sendFailure("Estudiante no encontrado.", 404);
                return;
            }

            unset($student[StudentEntity::PASSWORD]);
            $this->responseHandler->sendSuccess(["student" => $student], "Estudiante encontrado exitosamente.");
        } catch (Exception $e) {
            $this->responseHandler->sendFailure("Error al obtener el registro del estudiante.", 500, $e);
        }
    }

    /**
     * Crea un nuevo estudiante.
     * @param array $data Los datos del nuevo estudiante.
     * @return void
     */
    public function create($data)
    {
        $requiredFields = [
            StudentEntity::FULL_NAME,
            StudentEntity::ID_MAJOR,
            StudentEntity::ID_LEVEL,
            StudentEntity::MATRICULA,
            StudentEntity::EMAIL,
            StudentEntity::PASSWORD
        ];

        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                $this->responseHandler->sendFailure("El campo '{$field}' es obligatorio.", 400);
                return;
            }
        }

        try {
            // Validación de existencia de IDs relacionados
            if (!$this->majorModel->getById($data[StudentEntity::ID_MAJOR])) {
                $this->responseHandler->sendFailure("La carrera no existe.", 404);
                return;
            }

            if (!$this->levelModel->getById($data[StudentEntity::ID_LEVEL])) {
                $this->responseHandler->sendFailure("El nivel no existe.", 404);
                return;
            }

            // Validación de unicidad
            if ($this->studentModel->findStudentByEmail($data[StudentEntity::EMAIL])) {
                $this->responseHandler->sendFailure("El correo electrónico ya está en uso.", 409);
                return;
            }
            if ($this->studentModel->findStudentByMatricula($data[StudentEntity::MATRICULA])) {
                $this->responseHandler->sendFailure("La matrícula ya está en uso.", 409);
                return;
            }

            $createdId = $this->studentModel->create($data);

            if (!$createdId) {
                $this->responseHandler->sendFailure("Error al crear el estudiante. No se pudo obtener el ID de inserción.", 500);
                return;
            }

            $newStudent = $this->studentModel->getById($createdId);
            unset($newStudent[StudentEntity::PASSWORD]);
            $this->responseHandler->sendSuccess(["student" => $newStudent], "Estudiante creado exitosamente.", 201);
        } catch (Exception $e) {
            $this->responseHandler->sendFailure("Error al crear el estudiante.", 500, $e);
        }
    }

    /**
     * Crea múltiples estudiantes en una única petición, realizando validaciones exhaustivas.
     * Si alguna validación falla para cualquier registro, detiene el proceso y devuelve
     * un error detallado.
     *
     * @param array $dataArray Un array de arrays con los datos de los estudiantes a crear.
     * @return void
     */
    public function createMany($dataArray)
    {
        // 1. Validación inicial del cuerpo de la solicitud
        if (!is_array($dataArray) || empty($dataArray)) {
            $this->responseHandler->sendFailure("El cuerpo de la solicitud debe ser un array no vacío de estudiantes.", 400);
            return;
        }

        $requiredFields = [
            StudentEntity::FULL_NAME,
            StudentEntity::ID_MAJOR,
            StudentEntity::ID_LEVEL,
            StudentEntity::MATRICULA,
            StudentEntity::EMAIL,
            StudentEntity::PASSWORD
        ];

        $emailsInBatch = [];
        $matriculasInBatch = [];
        $validStudentsData = [];
        
        // 2. Validación de todos los registros en el lote 
        foreach ($dataArray as $index => $data) {
            $recordNumber = $index + 1; // Para mensajes de error más amigables
            
            // 2.1 Validación de campos obligatorios
            foreach ($requiredFields as $field) {
                if (empty($data[$field])) {
                    $this->responseHandler->sendFailure("Registro #{$recordNumber}: El campo '{$field}' es obligatorio.", 400);
                    return;
                }
            }
            
            // 2.2 Validación de formato de email
            if (!filter_var($data[StudentEntity::EMAIL], FILTER_VALIDATE_EMAIL)) {
                $this->responseHandler->sendFailure("Registro #{$recordNumber}: Formato de correo electrónico inválido.", 400);
                return;
            }

            // 2.3 Validación de unicidad dentro del lote
            if (in_array($data[StudentEntity::EMAIL], $emailsInBatch)) {
                $this->responseHandler->sendFailure("Registro #{$recordNumber}: El correo electrónico ya está duplicado en este lote.", 409);
                return;
            }
            if (in_array($data[StudentEntity::MATRICULA], $matriculasInBatch)) {
                $this->responseHandler->sendFailure("Registro #{$recordNumber}: La matrícula ya está duplicada en este lote.", 409);
                return;
            }

            // Añadir a las listas de chequeo del lote
            $emailsInBatch[] = $data[StudentEntity::EMAIL];
            $matriculasInBatch[] = $data[StudentEntity::MATRICULA];
            
            // 2.4 Validación de existencia de IDs relacionados y unicidad en la DB
            try {
                if (!$this->majorModel->getById($data[StudentEntity::ID_MAJOR])) {
                    $this->responseHandler->sendFailure("Registro #{$recordNumber}: La carrera (id_major) no existe.", 404);
                    return;
                }

                if (!$this->levelModel->getById($data[StudentEntity::ID_LEVEL])) {
                    $this->responseHandler->sendFailure("Registro #{$recordNumber}: El nivel (id_level) no existe.", 404);
                    return;
                }

                // Validación de clase de inglés opcional
                if (!empty($data[StudentEntity::ID_ENGLISH_CLASS])) {
                    if (!$this->englishClassModel->getById($data[StudentEntity::ID_ENGLISH_CLASS])) {
                        $this->responseHandler->sendFailure("Registro #{$recordNumber}: La clase de inglés (id_english_class) no existe.", 404);
                        return;
                    }
                }
                
                // Validación de unicidad contra la DB
                if ($this->studentModel->findStudentByEmail($data[StudentEntity::EMAIL])) {
                    $this->responseHandler->sendFailure("Registro #{$recordNumber}: El correo electrónico ya está en uso en el sistema.", 409);
                    return;
                }
                if ($this->studentModel->findStudentByMatricula($data[StudentEntity::MATRICULA])) {
                    $this->responseHandler->sendFailure("Registro #{$recordNumber}: La matrícula ya está en uso en el sistema.", 409);
                    return;
                }

                // Si todas las validaciones pasan, se añade a la lista para inserción
                $validStudentsData[] = $data;
                
            } catch (Exception $e) {
                // Captura errores de DB durante la validación (ej. falla de conexión)
                $this->responseHandler->sendFailure("Registro #{$recordNumber}: Error interno de validación. Revisar Logs del Server.", 500, $e);
                return;
            }
        }
        
        // 3. Inserción masiva si todas las validaciones pasaron 
        try {
            $createdIds = $this->studentModel->createMany($validStudentsData);

            if ($createdIds === false) {
                // Falla de transacción capturada en el modelo
                $this->responseHandler->sendFailure("Fallo la creación de estudiantes por un error en la transacción de la base de datos.", 500);
                return;
            }

            // Opcional: obtener los registros completos creados para la respuesta (excluyendo password)
            $createdStudents = [];
            foreach ($createdIds as $id) {
                $student = $this->studentModel->getById($id);
                if ($student) {
                    unset($student[StudentEntity::PASSWORD]);
                    $createdStudents[] = $student;
                }
            }

            // Respuesta de éxito, código 201 (Created)
            $this->responseHandler->sendSuccess(["students" => $createdStudents], "Estudiantes creados exitosamente.", 201);
            
        } catch (Exception $e) {
            $this->responseHandler->sendFailure("Error al procesar la creación masiva de estudiantes.", 500, $e);
        }
    }

    /**
     * Actualiza los datos de un estudiante por su ID.
     * @param int $id El ID del estudiante a actualizar.
     * @param array $data Los datos para la actualización.
     * @return void
     */
    public function update($id, $data)
    {
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
                $this->responseHandler->sendFailure("Estudiante no encontrado.", 404);
                return;
            }

            $updated = $this->studentModel->updateById($id, $data);
            if (!$updated) {
                $this->responseHandler->sendFailure("Error al actualizar el estudiante.", 500);
                return;
            }

            $updatedStudent = $this->studentModel->getById($id);
            unset($updatedStudent[StudentEntity::PASSWORD]);
            $this->responseHandler->sendSuccess(["student" => $updatedStudent], "Estudiante actualizado exitosamente.");
        } catch (Exception $e) {
            $this->responseHandler->sendFailure("Error al actualizar el estudiante.", 500, $e);
        }
    }

    /**
     * Elimina un estudiante de forma lógica (soft-delete) por su ID.
     * @param int $id El ID del estudiante a eliminar.
     * @return void
     */
    public function deleteOne($id)
    {
        if (!is_numeric($id)) {
            $this->responseHandler->sendFailure("El ID debe ser numérico.", 400);
            return;
        }

        try {
            $existingStudent = $this->studentModel->getById($id);
            if (!$existingStudent) {
                $this->responseHandler->sendFailure("Estudiante no encontrado o ya eliminado.", 404);
                return;
            }

            $deleted = $this->studentModel->deleteById($id);
            if (!$deleted) {
                $this->responseHandler->sendFailure("Error al eliminar el estudiante.", 500);
                return;
            }

            unset($existingStudent[StudentEntity::PASSWORD]);
            $this->responseHandler->sendSuccess(["student" => $existingStudent], "Estudiante eliminado exitosamente (soft-delete).");
        } catch (Exception $e) {
            $this->responseHandler->sendFailure("Error al eliminar el estudiante.", 500, $e);
        }
    }

    /**
     * Elimina todos los registros de estudiantes de forma lógica (soft-delete).
     * @return void
     */
    public function deleteAll()
    {
        try {
            $deleted = $this->studentModel->deleteAll();

            if (!$deleted) {
                $this->responseHandler->sendFailure("Error al eliminar todos los estudiantes.", 500);
                return;
            }
            
            $this->responseHandler->sendSuccess(null, "Todos los estudiantes eliminados exitosamente.");
        } catch (Exception $e) {
            $this->responseHandler->sendFailure("Error al eliminar todos los estudiantes.", 500, $e);
        }
    }
}