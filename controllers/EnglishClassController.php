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
     * Obtiene todas las clases de inglés.
     * @return void
     */
    public function getAll()
    {
        try {
            $classes = $this->englishClassModel->getAll();
            $this->responseHandler->sendSuccess(["classes" => $classes], "Clases de inglés obtenidas exitosamente.");
        } catch (Exception $e) {
            $this->responseHandler->sendFailure("Error al obtener las clases de inglés.", 500, $e);
        }
    }

    /**
     * Obtiene una clase de inglés por su ID.
     * @param int $id El ID de la clase.
     * @return void
     */
    public function getOne($id)
    {
        if (!is_numeric($id)) {
            $this->responseHandler->sendFailure("El ID debe ser numérico.", 400);
            return;
        }

        try {
            $class = $this->englishClassModel->getById($id);

            if (!$class) {
                $this->responseHandler->sendFailure("Clase de inglés no encontrada.", 404);
                return;
            }
            
            $this->responseHandler->sendSuccess(["class" => $class], "Clase de inglés encontrada exitosamente.");
        } catch (Exception $e) {
            $this->responseHandler->sendFailure("Error al obtener la clase de inglés.", 500, $e);
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
     * Crea una nueva clase de inglés.
     * @param array $data Los datos de la nueva clase.
     * @return void
     */
    public function create($data)
    {
        $requiredFields = [
            EnglishClassEntity::NAME_GROUP,
            EnglishClassEntity::ID_PROFESSOR,
            EnglishClassEntity::ID_LEVEL
        ];

        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                $this->responseHandler->sendFailure("El campo '{$field}' es obligatorio.", 400);
                return;
            }
        }

        try {
            // Validación de existencia de IDs relacionados
            if (!$this->userModel->getById($data[EnglishClassEntity::ID_PROFESSOR])) {
                $this->responseHandler->sendFailure("El profesor con ID: {$data[EnglishClassEntity::ID_PROFESSOR]} no existe.", 404);
                return;
            }

            if (!$this->levelModel->getById($data[EnglishClassEntity::ID_LEVEL])) {
                $this->responseHandler->sendFailure("El nivel con ID: {$data[EnglishClassEntity::ID_LEVEL]} no existe.", 404);
                return;
            }

            $newClassId = $this->englishClassModel->create($data);

            if (!$newClassId) {
                $this->responseHandler->sendFailure("Error al crear la clase. No se pudo obtener el ID de inserción.", 500);
                return;
            }

            $newClass = $this->englishClassModel->getById($newClassId);
            $this->responseHandler->sendSuccess(["class" => $newClass], "Clase de inglés creada exitosamente.", 201);
        } catch (Exception $e) {
            $this->responseHandler->sendFailure("Error al crear la clase.", 500, $e);
        }
    }

    /**
     * Actualiza una clase de inglés por su ID.
     * @param int $id El ID de la clase a actualizar.
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
            $existingClass = $this->englishClassModel->getById($id);
            if (!$existingClass) {
                $this->responseHandler->sendFailure("Clase de inglés no encontrada.", 404);
                return;
            }

            $updated = $this->englishClassModel->updateById($id, $data);
            if (!$updated) {
                $this->responseHandler->sendFailure("Error al actualizar la clase.", 500);
                return;
            }

            $updatedClass = $this->englishClassModel->getById($id);
            $this->responseHandler->sendSuccess(["class" => $updatedClass], "Clase de inglés actualizada exitosamente.");
        } catch (Exception $e) {
            $this->responseHandler->sendFailure("Error al actualizar la clase.", 500, $e);
        }
    }

    /**
     * Elimina una clase de inglés de forma lógica (soft-delete) por su ID.
     * @param int $id El ID de la clase a eliminar.
     * @return void
     */
    public function deleteOne($id)
    {
        if (!is_numeric($id)) {
            $this->responseHandler->sendFailure("El ID debe ser numérico.", 400);
            return;
        }

        try {
            $existingClass = $this->englishClassModel->getById($id);
            if (!$existingClass) {
                $this->responseHandler->sendFailure("Clase de inglés no encontrada.", 404);
                return;
            }

            $deleted = $this->englishClassModel->deleteById($id);
            if (!$deleted) {
                $this->responseHandler->sendFailure("Error al eliminar la clase.", 500);
                return;
            }

            $this->responseHandler->sendSuccess(["class" => $existingClass], "Clase de inglés eliminada exitosamente (soft-delete).");
        } catch (Exception $e) {
            $this->responseHandler->sendFailure("Error al eliminar la clase.", 500, $e);
        }
    }

    /**
     * Restaura una clase de inglés (deshace el soft-delete) por su ID.
     * @param int $id El ID de la clase a restaurar.
     * @return void
     */
    public function restore($id)
    {
        if (!is_numeric($id)) {
            $this->responseHandler->sendFailure("El ID debe ser numérico.", 400);
            return;
        }

        try {
            $existingClass = $this->englishClassModel->getById($id);
            if (!$existingClass) {
                $this->responseHandler->sendFailure("Clase de inglés no encontrada.", 404);
                return;
            }

            $restored = $this->englishClassModel->restoreById($id);
            if (!$restored) {
                $this->responseHandler->sendFailure("Error al restaurar la clase.", 500);
                return;
            }

            $this->responseHandler->sendSuccess(["class" => $existingClass], "Clase de inglés restaurada exitosamente.");
        } catch (Exception $e) {
            $this->responseHandler->sendFailure("Error al restaurar la clase.", 500, $e);
        }
    }

    /**
     * Elimina una clase de inglés permanentemente (hard-delete) por su ID.
     * @param int $id El ID de la clase a eliminar permanentemente.
     * @return void
     */
    public function deletePermanent($id)
    {
        if (!is_numeric($id)) {
            $this->responseHandler->sendFailure("El ID debe ser numérico.", 400);
            return;
        }

        try {
            $existingClass = $this->englishClassModel->getById($id);
            if (!$existingClass) {
                $this->responseHandler->sendFailure("Clase de inglés no encontrada.", 404);
                return;
            }

            $deletedPermanent = $this->englishClassModel->deletePermanentById($id);
            if (!$deletedPermanent) {
                $this->responseHandler->sendFailure("Error al eliminar la clase permanentemente.", 500);
                return;
            }

            $this->responseHandler->sendSuccess(["class" => $existingClass], "Clase de inglés eliminada permanentemente.");
        } catch (Exception $e) {
            $this->responseHandler->sendFailure("Error al eliminar permanentemente la clase.", 500, $e);
        }
    }
}