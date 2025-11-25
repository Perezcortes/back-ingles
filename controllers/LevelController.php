<?php
require_once __DIR__ . '/../models/Level.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../responses/ResponseHandler.php'; 
require_once __DIR__ . '/../entities/Level.php';
require_once __DIR__ . '/../entities/User.php'; 

use App\Entities\Level as LevelEntity;
use App\Entities\User as UserEnt;

class LevelController
{
    private $levelModel;
    private $userModel;
    private $responseHandler;

    public function __construct()
    {
        $this->levelModel = new Level();
        $this->userModel = new User();
        $this->responseHandler = new ResponseHandler();
    }

    /**
     * Crea un nuevo registro de Nivel (Servicio 21).
     * @param array $data Los datos a insertar.
     * @return void
     */
    public function create($data)
    {
        // Validación: Campos obligatorios y no vacíos
        if (!isset($data[LevelEntity::DESCRIPTION]) || trim($data[LevelEntity::DESCRIPTION]) === '') {
            $this->responseHandler->sendFailure("El campo 'description' es obligatorio.", 400);
            return;
        }

        $levelName = $data[LevelEntity::LEVEL_NAME] ?? null;
        $coordinatorId = $data[LevelEntity::ID_LEVEL_COORDINATOR] ?? null;

        try {
            // Validación de Unicidad (si se proporciona el nombre)
            if ($levelName) {
                if ($this->levelModel->findByName($levelName)) {
                    $this->responseHandler->sendFailure("Ya existe un nivel activo con el nombre '{$levelName}'.", 409);
                    return;
                }
            }

            // Validación de Clave Foránea (id_level_coordinator)
            if ($coordinatorId !== null) {
                // Verificar que sea un entero válido
                if (!is_numeric($coordinatorId) || (int)$coordinatorId <= 0) {
                     $this->responseHandler->sendFailure("El ID de coordinador debe ser un número entero positivo.", 400);
                    return;
                }
                
                // Verificar existencia del usuario (el modelo User::getById incluye borrados lógicamente)
                $user = $this->userModel->getById($coordinatorId);
                
                if (!$user) {
                    $this->responseHandler->sendFailure("El ID de coordinador proporcionado ({$coordinatorId}) no existe en el sistema.", 404);
                    return;
                }
            }
            
            // Preparación de datos
            $levelData = [
                LevelEntity::LEVEL_NAME => $levelName,
                LevelEntity::DESCRIPTION => $data[LevelEntity::DESCRIPTION],
                LevelEntity::ID_LEVEL_COORDINATOR => $coordinatorId,
            ];
            
            // Filtrar NULLs (para que el Modelo no intente insertar campos opcionales nulos si no fueron pasados)
            $levelData = array_filter($levelData, function($value) {
                // Permitir 0s y valores válidos, pero filtrar estrictamente el NULL
                return $value !== null;
            });
            
            // Creación
            $createdId = $this->levelModel->create($levelData);

            if (!$createdId) {
                $this->responseHandler->sendFailure("Error al crear el nivel en la base de datos.", 500);
                return;
            }

            // Obtener y responder
            $newLevel = $this->levelModel->getById($createdId);
            
            $this->responseHandler->sendSuccess(["level" => $newLevel], "Nivel creado exitosamente.", 201);

        } catch (Exception $e) {
            $this->responseHandler->sendFailure("Error en el proceso de creación del nivel. Revisar logs.", 500, $e);
        }
    }

    public function getAll()
    {
        try {
            $levels = $this->levelModel->getAll();
            $this->responseHandler->sendSuccess(["levels" => $levels], "Niveles encontrados exitosamente", 200);
        } catch (Exception $e) {
            $this->responseHandler->sendFailure("Error al obtener los niveles. Revisar logs.", 500, $e);
        }
    }

    public function getOneById($id)
    {
        try {
            // Validar que el ID sea un entero positivo
            if (!is_numeric($id) || (int)$id <= 0) {
                $this->responseHandler->sendFailure("El ID proporcionado no es válido.", 400);
                return;
            }

            $level = $this->levelModel->getById((int)$id);

            if (!$level) {
                $this->responseHandler->sendFailure("Nivel con ID {$id} no encontrado.", 404);
                return;
            }

            $this->responseHandler->sendSuccess(["level" => $level], "Nivel encontrado exitosamente.", 200);
        } catch (Exception $e) {
            $this->responseHandler->sendFailure("Error al obtener el nivel. Revisar logs.", 500, $e);
        }
    }

    /**
     * Actualiza los datos de un nivel existente (Servicio 24).
     * @param int $id ID del nivel a actualizar.
     * @param array $data Los datos a modificar.
     * @return void
     */
    public function update($id, $data)
    {
        if (!is_numeric($id) || (int)$id <= 0) {
            $this->responseHandler->sendFailure("El ID debe ser un número entero positivo.", 400);
            return;
        }
        
        if (empty($data)) {
            $this->responseHandler->sendFailure("Se requiere al menos un campo para actualizar el nivel.", 400);
            return;
        }

        $levelName = $data[LevelEntity::LEVEL_NAME] ?? null;
        $coordinatorId = $data[LevelEntity::ID_LEVEL_COORDINATOR] ?? null;
        $description = $data[LevelEntity::DESCRIPTION] ?? null;
        
        try {
            $existingLevel = $this->levelModel->getById($id);
            if (!$existingLevel) {
                $this->responseHandler->sendFailure("Nivel no encontrado con ID: {$id}.", 404);
                return;
            }

            // Solo si el campo está en el payload Y el valor es diferente al actual:
            if (array_key_exists(LevelEntity::LEVEL_NAME, $data) && $levelName !== $existingLevel[LevelEntity::LEVEL_NAME]) {
                // Debe ser null O no debe existir ya
                if ($levelName !== null && $this->levelModel->findByName($levelName)) {
                    $this->responseHandler->sendFailure("Ya existe un nivel activo con el nombre '{$levelName}'.", 409);
                    return;
                }
            }

            // Solo si el campo está presente en el payload, y su valor no es NULL
            if (array_key_exists(LevelEntity::ID_LEVEL_COORDINATOR, $data) && $coordinatorId !== null) {
                if (!is_numeric($coordinatorId) || (int)$coordinatorId <= 0) {
                     $this->responseHandler->sendFailure("El ID de coordinador debe ser un número entero positivo.", 400);
                    return;
                }
                
                $user = $this->userModel->getById($coordinatorId);
                
                if (!$user) {
                    $this->responseHandler->sendFailure("El ID de coordinador proporcionado ({$coordinatorId}) no existe en el sistema.", 404);
                    return;
                }
            }

            $updateData = [];
            
            // Solo incluir campos si están presentes en la solicitud para no sobreescribir con NULL accidentalmente
            if (array_key_exists(LevelEntity::LEVEL_NAME, $data)) {
                $updateData[LevelEntity::LEVEL_NAME] = $levelName;
            }
            if (array_key_exists(LevelEntity::DESCRIPTION, $data)) {
                $updateData[LevelEntity::DESCRIPTION] = $description;
            }
            if (array_key_exists(LevelEntity::ID_LEVEL_COORDINATOR, $data)) {
                $updateData[LevelEntity::ID_LEVEL_COORDINATOR] = $coordinatorId;
            }
            
            if (empty($updateData)) {
                 $this->responseHandler->sendSuccess(["level" => $existingLevel], "Nivel actualizado exitosamente (no se detectaron cambios).");
                 return;
            }

            $updated = $this->levelModel->updateById($id, $updateData);

            $updatedLevel = $this->levelModel->getById($id);
            
            if ($updated) {
                $this->responseHandler->sendSuccess(["level" => $updatedLevel], "Nivel actualizado exitosamente.");
            } else {
                $this->responseHandler->sendSuccess(["level" => $updatedLevel], "Nivel actualizado exitosamente (no se detectaron cambios).");
            }

        } catch (Exception $e) {
            $this->responseHandler->sendFailure("Error al actualizar el nivel. Revisar logs.", 500, $e);
        }
    }

    /**
     * Realiza un borrado lógico (soft delete) de un nivel (Servicio 25).
     * @param int $id ID del nivel a eliminar.
     * @return void
     */
    public function deleteOne($id)
    {
        if (!is_numeric($id) || (int)$id <= 0) {
            $this->responseHandler->sendFailure("El ID debe ser un número entero positivo.", 400);
            return;
        }

        try {
            $existingLevel = $this->levelModel->getById($id);
            if (!$existingLevel) {
                $this->responseHandler->sendFailure("No se encontró el nivel con id: $id", 404);
                return;
            }

            $deleted = $this->levelModel->deleteById($id);

            if ($deleted) {
                // Recuperar el registro actualizado (con deleted_at lleno)
                $deletedLevel = $this->levelModel->getById($id);
                $this->responseHandler->sendSuccess(["level" => $deletedLevel], "Nivel eliminado exitosamente.");
            } else {
                $this->responseHandler->sendFailure("Error interno al intentar eliminar el nivel.", 500);
            }

        } catch (Exception $e) {
            $this->responseHandler->sendFailure("Error al eliminar el nivel. Revisar logs.", 500, $e);
        }
    }
}
