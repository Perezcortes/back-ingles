<?php
// controllers/EnglishClassController.php

require_once __DIR__ . '/../models/EnglishClass.php';
require_once __DIR__ . '/../entities/EnglishClass.php';

use App\Entities\EnglishClass as EnglishClassEntity;
use Exception;

class EnglishClassController
{
    private $englishClassModel;

    public function __construct()
    {
        $this->englishClassModel = new EnglishClass();
    }
    
    // Obtener todos los grupos de inglés (método de instancia)
    public function getAll()
    {
        try {
            $groups = $this->englishClassModel->getAll();

            http_response_code(200);
            echo json_encode($groups);
        } catch (Exception $e) {
            $this->sendError(500, "Error al obtener los registros de la tabla.", $e);
        }
    }

    // Obtener un grupo de inglés por ID (método de instancia)
    public function getOne($id)
    {
        if (!is_numeric($id)) {
            $this->sendError(400, "El id debe ser numérico.");
            return;
        }

        try {
            $group = $this->englishClassModel->getById($id);

            if ($group) {
                http_response_code(200);
                echo json_encode($group);
            } else {
                $this->sendError(404, "Grupo no encontrado");
            }
        } catch (Exception $e) {
            $this->sendError(500, "Error al obtener el registro del grupo", $e);
        }
    }

    // Obtener grupos de inglés por profesor (método de instancia)
    public function getByProfessor($id_professor)
    {
        if (!is_numeric($id_professor)) {
            $this->sendError(400, "El id_professor debe ser numérico.");
            return;
        }

        try {
            $groups = $this->englishClassModel->getByProfessorId($id_professor);

            http_response_code(200);
            echo json_encode($groups);
        } catch (Exception $e) {
            $this->sendError(500, "Error al obtener los grupos por profesor", $e);
        }
    }

    // Obtener grupos de inglés por nivel (método de instancia)
    public function getByLevel($id_level)
    {
        if (!is_numeric($id_level)) {
            $this->sendError(400, "El id_level debe ser numérico.");
            return;
        }

        try {
            $groups = $this->englishClassModel->getByLevelId($id_level);

            http_response_code(200);
            echo json_encode($groups);
        } catch (Exception $e) {
            $this->sendError(500, "Error al obtener los grupos por nivel", $e);
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
                $this->sendError(400, "El campo '{$var}' es obligatorio");
                return;
            }
        }
        
        // Asumiendo que los modelos 'User' y 'Level' existen y tienen el método getById()
        // Validación de existencia de IDs relacionados
        $userModel = new User();
        $levelModel = new Level();

        if (!$userModel->getById($data[EnglishClassEntity::ID_PROFESSOR])) {
            $this->sendError(404, "El profesor con id: {$data[EnglishClassEntity::ID_PROFESSOR]} no existe.");
            return;
        }

        if (!$levelModel->getById($data[EnglishClassEntity::ID_LEVEL])) {
            $this->sendError(404, "El nivel con id: {$data[EnglishClassEntity::ID_LEVEL]} no existe.");
            return;
        }
        
        try {
            $created = $this->englishClassModel->create($data);

            if ($created) {
                http_response_code(201);
                echo json_encode([
                    "message" => "Clase de inglés creada exitosamente.",
                    "class" => $data
                ]);
            } else {
                $this->sendError(500, "Error al crear la clase.");
            }
        } catch (Exception $e) {
            $this->sendError(500, "Error al crear la clase.", $e);
        }
    }

    // Actualizar un grupo de inglés (método de instancia)
    public function update($id, $data)
    {
        if (!is_numeric($id)) {
            $this->sendError(400, "El id debe ser numérico.");
            return;
        }

        if (empty($data)) {
            $this->sendError(400, "Se requiere al menos un campo para actualizar la clase.");
            return;
        }

        try {
            $groupExistente = $this->englishClassModel->getById($id);

            if (!$groupExistente) {
                $this->sendError(404, "No se encontró la clase con id: $id");
                return;
            }

            $updated = $this->englishClassModel->updateById($id, $data);

            if ($updated) {
                $updatedGroup = $this->englishClassModel->getById($id);

                http_response_code(200);
                echo json_encode([
                    "message" => "Clase de inglés actualizada exitosamente",
                    "class" => $updatedGroup
                ]);
            } else {
                $this->sendError(500, "Error interno al intentar actualizar la clase.");
            }
        } catch (Exception $e) {
            $this->sendError(500, "Error al actualizar la clase", $e);
        }
    }

    // Eliminar un grupo de inglés (Soft Delete) (método de instancia)
    public function deleteOne($id)
    {
        if (!is_numeric($id)) {
            $this->sendError(400, "El id debe ser numérico.");
            return;
        }

        try {
            $group = $this->englishClassModel->getById($id);
            if (!$group) {
                $this->sendError(404, "No se encontró la clase con id: $id");
                return;
            }

            $deleted = $this->englishClassModel->deleteById($id);

            if ($deleted) {
                http_response_code(200);
                echo json_encode([
                    "message" => "Clase de inglés eliminada exitosamente.",
                    "class" => $group
                ]);
            } else {
                $this->sendError(500, "Error interno al intentar eliminar la clase.");
            }
        } catch (Exception $e) {
            $this->sendError(500, "Error al eliminar la clase.", $e);
        }
    }

    // Restaurar un grupo de inglés (desmarcar como eliminado) (método de instancia)
    public function restore($id)
    {
        if (!is_numeric($id)) {
            $this->sendError(400, "El id debe ser numérico.");
            return;
        }

        try {
            $group = $this->englishClassModel->getById($id);
            if (!$group) {
                $this->sendError(404, "No se encontró la clase con id: $id");
                return;
            }

            $restored = $this->englishClassModel->restoreById($id);

            if ($restored) {
                http_response_code(200);
                echo json_encode([
                    "message" => "Clase de inglés restaurada exitosamente.",
                    "class" => $group
                ]);
            } else {
                $this->sendError(500, "Error interno al intentar restaurar la clase.");
            }
        } catch (Exception $e) {
            $this->sendError(500, "Error al restaurar la clase.", $e);
        }
    }

    // Eliminar un grupo de inglés permanentemente (hard delete) (método de instancia)
    public function deletePermanent($id)
    {
        if (!is_numeric($id)) {
            $this->sendError(400, "El id debe ser numérico.");
            return;
        }

        try {
            $group = $this->englishClassModel->getById($id);
            if (!$group) {
                $this->sendError(404, "No se encontró la clase con id: $id");
                return;
            }

            $deletedPermanent = $this->englishClassModel->deletePermanentById($id);

            if ($deletedPermanent) {
                http_response_code(200);
                echo json_encode([
                    "message" => "Clase de inglés eliminada permanentemente.",
                    "class" => $group
                ]);
            } else {
                $this->sendError(500, "Error interno al intentar eliminar permanentemente la clase.");
            }
        } catch (Exception $e) {
            $this->sendError(500, "Error al eliminar permanentemente la clase.", $e);
        }
    }
    
    // Método helper para las respuestas de error (método de instancia)
    private function sendError($code, $message, $exception = null)
    {
        http_response_code($code);
        $response = ["error" => $message];

        if (getenv('APP_ENV') === 'development' && $exception) {
            $response["details"] = $exception->getMessage();
        }

        echo json_encode($response);
    }
}