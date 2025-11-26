<?php
// controllers/EnglishClassController.php

require_once __DIR__ . '/../models/EnglishClass.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Level.php';
require_once __DIR__ . '/../responses/ResponseHandler.php';
require_once __DIR__ . '/../entities/EnglishClass.php';
require_once __DIR__ . '/../entities/User.php';
require_once __DIR__ . '/../entities/Level.php';

use App\Entities\EnglishClass as EnglishClassEntity;
use App\Entities\User as UserEntity;
use App\Entities\Level as LevelEntity;
use Exception;

class EnglishClassController
{
    private $englishClassModel;
    private $userModel;
    private $levelModel;
    private $responseHandler;

    public function __construct()
    {
        $this->englishClassModel = new EnglishClass();
        $this->userModel = new User();
        $this->levelModel = new Level();
        $this->responseHandler = new ResponseHandler();
    }

    /**
     * Obtiene todas las clases activas (Servicio 37).
     * @return void
     */
    public function getAll()
    {
        try {
            $classes = $this->englishClassModel->getAll();
            
            $this->responseHandler->sendSuccess(["english_classes" => $classes], "Clases encontradas exitosamente.");

        } catch (Exception $e) {
            $this->responseHandler->sendFailure("Error al obtener las clases. Revisar logs.", 500, $e);
        }
    }

    /**
     * Obtiene una clase por su ID (Servicio 38).
     * @param int $id ID de la clase.
     * @return void
     */
    public function getOneById($id)
    {
        if (!is_numeric($id) || (int)$id <= 0) {
            $this->responseHandler->sendFailure("El ID debe ser un número entero positivo.", 400);
            return;
        }

        try {
            $class = $this->englishClassModel->getById($id);

            if (!$class || $class[EnglishClassEntity::DELETED_AT] !== null) {
                $this->responseHandler->sendFailure("Clase no encontrada.", 404);
                return;
            }

            $this->responseHandler->sendSuccess(["english_class" => $class], "Clase encontrada exitosamente.");

        } catch (Exception $e) {
            $this->responseHandler->sendFailure("Error al obtener la clase. Revisar logs.", 500, $e);
        }
    }

    /**
     * Obtiene las clases de inglés por el ID del profesor.
     * @param int $id_professor El ID del profesor.
     * @return void
     */
    public function getByProfessor($id_professor)
    {
        if (!is_numeric($id_professor)) {
            $this->responseHandler->sendFailure("El ID del profesor debe ser numérico.", 400);
            return;
        }

        try {
            $classes = $this->englishClassModel->getByProfessorId($id_professor);
            $this->responseHandler->sendSuccess(["classes" => $classes], "Clases encontradas por profesor exitosamente.");
        } catch (Exception $e) {
            $this->responseHandler->sendFailure("Error al obtener las clases por profesor.", 500, $e);
        }
    }

    /**
     * Obtiene las clases de inglés por el ID del nivel.
     * @param int $id_level El ID del nivel.
     * @return void
     */
    public function getByLevel($id_level)
    {
        if (!is_numeric($id_level)) {
            $this->responseHandler->sendFailure("El ID del nivel debe ser numérico.", 400);
            return;
        }

        try {
            $classes = $this->englishClassModel->getByLevelId($id_level);
            $this->responseHandler->sendSuccess(["classes" => $classes], "Clases encontradas por nivel exitosamente.");
        } catch (Exception $e) {
            $this->responseHandler->sendFailure("Error al obtener las clases por nivel.", 500, $e);
        }
    }

    /**
     * Crea una nueva clase de inglés (Servicio 36).
     * @param array $data Los datos a insertar.
     * @return void
     */
    public function create($data)
    {
        if (!isset($data[EnglishClassEntity::NAME_GROUP]) || trim($data[EnglishClassEntity::NAME_GROUP]) === '') {
            $this->responseHandler->sendFailure("El campo 'name_group' es obligatorio.", 400);
            return;
        }
        
        if (!isset($data[EnglishClassEntity::ID_LEVEL])) {
            $this->responseHandler->sendFailure("El campo 'id_level' es obligatorio.", 400);
            return;
        }

        $nameGroup = trim($data[EnglishClassEntity::NAME_GROUP]);
        $idLevel = $data[EnglishClassEntity::ID_LEVEL];
        $idProfessor = isset($data[EnglishClassEntity::ID_PROFESSOR]) ? $data[EnglishClassEntity::ID_PROFESSOR] : null;

        try {
            // Validación de Unicidad del nombre del grupo
            if ($this->englishClassModel->findByName($nameGroup)) {
                $this->responseHandler->sendFailure("Ya existe una clase con el nombre de grupo '{$nameGroup}'.", 409);
                return;
            }

            // Validación de Existencia del Nivel (FK)
            if (!is_numeric($idLevel) || (int)$idLevel <= 0) {
                $this->responseHandler->sendFailure("El 'id_level' debe ser un número válido.", 400);
                return;
            }
            $level = $this->levelModel->getById($idLevel);
            if (!$level) {
                $this->responseHandler->sendFailure("El nivel proporcionado ({$idLevel}) no existe.", 404);
                return;
            }

            // Validación de Existencia del Profesor (FK) - Opcional
            if ($idProfessor !== null) {
                if (!is_numeric($idProfessor) || (int)$idProfessor <= 0) {
                    $this->responseHandler->sendFailure("El 'id_professor' debe ser un número válido.", 400);
                    return;
                }
                $professor = $this->userModel->getById($idProfessor);
                if (!$professor) {
                    $this->responseHandler->sendFailure("El profesor proporcionado ({$idProfessor}) no existe.", 404);
                    return;
                }
                // Opcional: Validar si el usuario realmente tiene el rol de profesor
                // if ($professor['is_professor'] != 1) { ... }
            }

            $classData = [
                EnglishClassEntity::NAME_GROUP => $nameGroup,
                EnglishClassEntity::ID_LEVEL => $idLevel,
                EnglishClassEntity::ID_PROFESSOR => $idProfessor,
            ];
            
            $createdId = $this->englishClassModel->create($classData);

            if (!$createdId) {
                $this->responseHandler->sendFailure("Error al crear la clase en la base de datos.", 500);
                return;
            }

            $newClass = $this->englishClassModel->getById($createdId);
            
            $this->responseHandler->sendSuccess(["english_class" => $newClass], "Clase creada exitosamente.", 201);

        } catch (Exception $e) {
            $this->responseHandler->sendFailure("Error en el proceso de creación de la clase. Revisar logs.", 500, $e);
        }
    }

    /**
     * Actualiza una clase existente (Servicio 39).
     * @param int $id ID de la clase a actualizar.
     * @param array $data Datos a actualizar.
     * @return void
     */
    public function update($id, $data)
    {
        if (!is_numeric($id) || (int)$id <= 0) {
            $this->responseHandler->sendFailure("El ID debe ser un número entero positivo.", 400);
            return;
        }

        if (empty($data)) {
            $this->responseHandler->sendFailure("Se requiere al menos un campo para actualizar la clase.", 400);
            return;
        }

        try {
            // Verificar existencia de la clase
            $existingClass = $this->englishClassModel->getById($id);
            if (!$existingClass || $existingClass[EnglishClassEntity::DELETED_AT] !== null) {
                $this->responseHandler->sendFailure("Clase no encontrada.", 404);
                return;
            }

            $nameGroup = isset($data[EnglishClassEntity::NAME_GROUP]) ? trim($data[EnglishClassEntity::NAME_GROUP]) : null;
            $idLevel = isset($data[EnglishClassEntity::ID_LEVEL]) ? $data[EnglishClassEntity::ID_LEVEL] : null;
            // Para id_professor, usamos array_key_exists para permitir enviar NULL y borrar el profesor
            $idProfessor = array_key_exists(EnglishClassEntity::ID_PROFESSOR, $data) ? $data[EnglishClassEntity::ID_PROFESSOR] : 'no_update';

            // Validación de Unicidad (si el nombre cambia)
            if ($nameGroup && $nameGroup !== $existingClass[EnglishClassEntity::NAME_GROUP]) {
                if ($this->englishClassModel->findByName($nameGroup)) {
                    $this->responseHandler->sendFailure("Ya existe una clase con el nombre de grupo '{$nameGroup}'.", 409);
                    return;
                }
            }

            // Validación de Existencia de Nivel (si se envía)
            if ($idLevel !== null) {
                 if (!is_numeric($idLevel) || (int)$idLevel <= 0) {
                    $this->responseHandler->sendFailure("El 'id_level' debe ser un número válido.", 400);
                    return;
                }
                if (!$this->levelModel->getById($idLevel)) {
                    $this->responseHandler->sendFailure("El nivel proporcionado ({$idLevel}) no existe.", 404);
                    return;
                }
            }

            // Validación de Existencia de Profesor (si se envía y no es null)
            if ($idProfessor !== 'no_update' && $idProfessor !== null) {
                 if (!is_numeric($idProfessor) || (int)$idProfessor <= 0) {
                    $this->responseHandler->sendFailure("El 'id_professor' debe ser un número válido.", 400);
                    return;
                }
                if (!$this->userModel->getById($idProfessor)) {
                    $this->responseHandler->sendFailure("El profesor proporcionado ({$idProfessor}) no existe.", 404);
                    return;
                }
            }

            $updateData = [];
            if ($nameGroup !== null) $updateData[EnglishClassEntity::NAME_GROUP] = $nameGroup;
            if ($idLevel !== null) $updateData[EnglishClassEntity::ID_LEVEL] = $idLevel;
            if ($idProfessor !== 'no_update') $updateData[EnglishClassEntity::ID_PROFESSOR] = $idProfessor;

            if (empty($updateData)) {
                 $this->responseHandler->sendSuccess(["english_class" => $existingClass], "Clase actualizada exitosamente (no se detectaron cambios).");
                 return;
            }

            $this->englishClassModel->updateById($id, $updateData);
            $updatedClass = $this->englishClassModel->getById($id);
            
            $this->responseHandler->sendSuccess(["english_class" => $updatedClass], "Clase actualizada exitosamente.");

        } catch (Exception $e) {
            $this->responseHandler->sendFailure("Error al actualizar la clase. Revisar logs.", 500, $e);
        }
    }

    /**
     * Realiza un borrado lógico (soft delete) de una clase (Servicio 40).
     * @param int $id ID de la clase a eliminar.
     * @return void
     */
    public function deleteOne($id)
    {
        if (!is_numeric($id) || (int)$id <= 0) {
            $this->responseHandler->sendFailure("El ID debe ser un número entero positivo.", 400);
            return;
        }

        try {
            $existingClass = $this->englishClassModel->getById($id);
            
            if (!$existingClass) {
                $this->responseHandler->sendFailure("No se encontró la clase con id: $id", 404);
                return;
            }

            $deleted = $this->englishClassModel->deleteById($id);

            if ($deleted) {
                $deletedClass = $this->englishClassModel->getById($id);
                $this->responseHandler->sendSuccess(["english_class" => $deletedClass], "Clase eliminada exitosamente.");
            } else {
                $this->responseHandler->sendFailure("Error interno al intentar eliminar la clase.", 500);
            }

        } catch (Exception $e) {
            $this->responseHandler->sendFailure("Error al eliminar la clase. Revisar logs.", 500, $e);
        }
    }
}