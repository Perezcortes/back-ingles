<?php
// controllers/EnglishClassController.php

require_once __DIR__ . '/../models/EnglishClass.php';
require_once __DIR__ . '/../models/User.php'; // Requerimos el modelo User para la validación
require_once __DIR__ . '/../models/Level.php'; // Requerimos el modelo Level para la validación
require_once __DIR__ . '/../responses/ResponseHandler.php'; // <-- Nueva dependencia
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
    
    // Obtener todos los grupos de inglés (método de instancia)
    public function getAll()
    {
        try {
            $groups = $this->englishClassModel->getAll();
            $this->responseHandler->sendSuccess(["groups" => $groups], "Clases de inglés encontradas exitosamente.");
        } catch (Exception $e) {
            $this->responseHandler->sendFailure("Error al obtener los registros de la tabla.", 500, $e);
        }
    }

    // Obtener un grupo de inglés por ID (método de instancia)
    public function getOne($id)
    {
        if (!is_numeric($id)) {
            $this->responseHandler->sendFailure("El id debe ser numérico.", 400);
            return;
        }

        try {
            $group = $this->englishClassModel->getById($id);

            if ($group) {
                $this->responseHandler->sendSuccess(["class" => $group], "Clase de inglés encontrada exitosamente.");
            } else {
                $this->responseHandler->sendFailure("Clase de inglés no encontrada", 404);
            }
        } catch (Exception $e) {
            $this->responseHandler->sendFailure("Error al obtener el registro del grupo", 500, $e);
        }
    }

    // Obtener grupos de inglés por profesor (método de instancia)
    public function getByProfessor($id_professor)
    {
        if (!is_numeric($id_professor)) {
            $this->responseHandler->sendFailure("El id_professor debe ser numérico.", 400);
            return;
        }

        try {
            $groups = $this->englishClassModel->getByProfessorId($id_professor);
            $this->responseHandler->sendSuccess(["groups" => $groups], "Clases encontradas por profesor exitosamente.");
        } catch (Exception $e) {
            $this->responseHandler->sendFailure("Error al obtener los grupos por profesor", 500, $e);
        }
    }

    // Obtener grupos de inglés por nivel (método de instancia)
    public function getByLevel($id_level)
    {
        if (!is_numeric($id_level)) {
            $this->responseHandler->sendFailure("El id_level debe ser numérico.", 400);
            return;
        }

        try {
            $groups = $this->englishClassModel->getByLevelId($id_level);
            $this->responseHandler->sendSuccess(["groups" => $groups], "Clases encontradas por nivel exitosamente.");
        } catch (Exception $e) {
            $this->responseHandler->sendFailure("Error al obtener los grupos por nivel", 500, $e);
        }
    }

    // Crear un nuevo grupo de inglés (método de instancia)
    public function create($data)
    {
        $requiredVars = [
            EnglishClassEntity::NAME_GROUP,
            EnglishClassEntity::ID_PROFESSOR,
            EnglishClassEntity::ID_LEVEL
        ];

        foreach ($requiredVars as $var) {
            if (!isset($data[$var]) || trim($data[$var]) === '') {
                $this->responseHandler->sendFailure("El campo '{$var}' es obligatorio", 400);
                return;
            }
        }
        
        // Asumiendo que los modelos 'User' y 'Level' existen y tienen el método getById()
        // Validación de existencia de IDs relacionados
        if (!$this->userModel->getById($data[EnglishClassEntity::ID_PROFESSOR])) {
            $this->responseHandler->sendFailure("El profesor con id: {$data[EnglishClassEntity::ID_PROFESSOR]} no existe.", 404);
            return;
        }

        if (!$this->levelModel->getById($data[EnglishClassEntity::ID_LEVEL])) {
            $this->responseHandler->sendFailure("El nivel con id: {$data[EnglishClassEntity::ID_LEVEL]} no existe.", 404);
            return;
        }
        
        try {
            $created = $this->englishClassModel->create($data);

            if ($created) {
                // Obtenemos el ID del registro creado
                $newClassId = $this->englishClassModel->lastInsertId(); // Asegúrate de tener este método en tu modelo
                $newClass = $this->englishClassModel->getById($newClassId);
                
                $this->responseHandler->sendSuccess(["class" => $newClass], "Clase de inglés creada exitosamente.", 201);
            } else {
                $this->responseHandler->sendFailure("Error al crear la clase.", 500);
            }
        } catch (Exception $e) {
            $this->responseHandler->sendFailure("Error al crear la clase.", 500, $e);
        }
    }

    // Actualizar un grupo de inglés (método de instancia)
    public function update($id, $data)
    {
        if (!is_numeric($id)) {
            $this->responseHandler->sendFailure("El id debe ser numérico.", 400);
            return;
        }

        if (empty($data)) {
            $this->responseHandler->sendFailure("Se requiere al menos un campo para actualizar la clase.", 400);
            return;
        }

        try {
            $groupExistente = $this->englishClassModel->getById($id);

            if (!$groupExistente) {
                $this->responseHandler->sendFailure("No se encontró la clase con id: $id", 404);
                return;
            }

            $updated = $this->englishClassModel->updateById($id, $data);

            if ($updated) {
                $updatedGroup = $this->englishClassModel->getById($id);

                $this->responseHandler->sendSuccess(["class" => $updatedGroup], "Clase de inglés actualizada exitosamente.");
            } else {
                $this->responseHandler->sendFailure("Error interno al intentar actualizar la clase.", 500);
            }
        } catch (Exception $e) {
            $this->responseHandler->sendFailure("Error al actualizar la clase", 500, $e);
        }
    }

    // Eliminar un grupo de inglés (Soft Delete) (método de instancia)
    public function deleteOne($id)
    {
        if (!is_numeric($id)) {
            $this->responseHandler->sendFailure("El id debe ser numérico.", 400);
            return;
        }

        try {
            $group = $this->englishClassModel->getById($id);
            if (!$group) {
                $this->responseHandler->sendFailure("No se encontró la clase con id: $id", 404);
                return;
            }

            $deleted = $this->englishClassModel->deleteById($id);

            if ($deleted) {
                $this->responseHandler->sendSuccess(["class" => $group], "Clase de inglés eliminada exitosamente.");
            } else {
                $this->responseHandler->sendFailure("Error interno al intentar eliminar la clase.", 500);
            }
        } catch (Exception $e) {
            $this->responseHandler->sendFailure("Error al eliminar la clase.", 500, $e);
        }
    }

    // Restaurar un grupo de inglés (desmarcar como eliminado) (método de instancia)
    public function restore($id)
    {
        if (!is_numeric($id)) {
            $this->responseHandler->sendFailure("El id debe ser numérico.", 400);
            return;
        }

        try {
            $group = $this->englishClassModel->getById($id);
            if (!$group) {
                $this->responseHandler->sendFailure("No se encontró la clase con id: $id", 404);
                return;
            }

            $restored = $this->englishClassModel->restoreById($id);

            if ($restored) {
                $this->responseHandler->sendSuccess(["class" => $group], "Clase de inglés restaurada exitosamente.");
            } else {
                $this->responseHandler->sendFailure("Error interno al intentar restaurar la clase.", 500);
            }
        } catch (Exception $e) {
            $this->responseHandler->sendFailure("Error al restaurar la clase.", 500, $e);
        }
    }

    // Eliminar un grupo de inglés permanentemente (hard delete) (método de instancia)
    public function deletePermanent($id)
    {
        if (!is_numeric($id)) {
            $this->responseHandler->sendFailure("El id debe ser numérico.", 400);
            return;
        }

        try {
            $group = $this->englishClassModel->getById($id);
            if (!$group) {
                $this->responseHandler->sendFailure("No se encontró la clase con id: $id", 404);
                return;
            }

            $deletedPermanent = $this->englishClassModel->deletePermanentById($id);

            if ($deletedPermanent) {
                $this->responseHandler->sendSuccess(["class" => $group], "Clase de inglés eliminada permanentemente.");
            } else {
                $this->responseHandler->sendFailure("Error interno al intentar eliminar permanentemente la clase.", 500);
            }
        } catch (Exception $e) {
            $this->responseHandler->sendFailure("Error al eliminar permanentemente la clase.", 500, $e);
        }
    }
}