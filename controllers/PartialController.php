<?php
require_once __DIR__ . '/../models/Partial.php';
require_once __DIR__ . '/../responses/ResponseHandler.php'; 
require_once __DIR__ . '/../entities/Partial.php';

use App\Entities\Partial as PartialEntity;

class PartialController 
{
    private $partialModel;
    private $responseHandler;

    public function __construct()
    {
        $this->partialModel = new Partial();
        $this->responseHandler = new ResponseHandler();
    }

    /**
     * Crea un nuevo parcial (Servicio 31).
     * @param array $data Los datos a insertar.
     * @return void
     */
    public function create($data)
    {
        // Nota: Según la especificación, partial_name es opcional.
        // Sin embargo, validamos que si viene, no sea una cadena vacía.
        $partialName = isset($data[PartialEntity::PARTIAL_NAME]) ? trim($data[PartialEntity::PARTIAL_NAME]) : null;

        if (array_key_exists(PartialEntity::PARTIAL_NAME, $data) && $partialName === '') {
             $this->responseHandler->sendFailure("El campo 'partial_name' no puede estar vacío.", 400);
             return;
        }

        try {
            if ($partialName) {
                if ($this->partialModel->findByName($partialName)) {
                    $this->responseHandler->sendFailure("Ya existe un parcial activo con el nombre '{$partialName}'.", 409);
                    return;
                }
            }

            $partialData = [];
            if ($partialName !== null) {
                $partialData[PartialEntity::PARTIAL_NAME] = $partialName;
            }
            
            $createdId = $this->partialModel->create($partialData);

            if (!$createdId) {
                $this->responseHandler->sendFailure("Error al crear el parcial en la base de datos.", 500);
                return;
            }

            $newPartial = $this->partialModel->getById($createdId);
            
            $this->responseHandler->sendSuccess(["partial" => $newPartial], "Parcial creado exitosamente.", 201);

        } catch (Exception $e) {
            $this->responseHandler->sendFailure("Error en el proceso de creación del parcial. Revisar logs.", 500, $e);
        }
    }

    /**
     * Obtiene todos los parciales activos (Servicio 32).
     * @return void
     */
    public function getAll()
    {
        try {
            $partials = $this->partialModel->getAll();
            
            $this->responseHandler->sendSuccess(["partials" => $partials], "Parciales encontrados exitosamente.");

        } catch (Exception $e) {
            $this->responseHandler->sendFailure("Error al obtener los parciales. Revisar logs.", 500, $e);
        }
    }

    /**
     * Obtiene un parcial por su ID (Servicio 33).
     * @param int $id ID del parcial.
     * @return void
     */
    public function getOneById($id)
    {
        try {
            $partial = $this->partialModel->getById($id);

            if (!$partial || $partial[PartialEntity::DELETED_AT] !== null) {
                $this->responseHandler->sendFailure("Parcial no encontrado.", 404);
                return;
            }

            $this->responseHandler->sendSuccess(["partial" => $partial], "Parcial encontrado exitosamente.");

        } catch (Exception $e) {
            $this->responseHandler->sendFailure("Error al obtener el parcial. Revisar logs.", 500, $e);
        }
    }

    /**
     * Actualiza los datos de un parcial existente (Servicio 34).
     * @param int $id ID del parcial a actualizar.
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
            $this->responseHandler->sendFailure("Se requiere al menos un campo para actualizar el parcial.", 400);
            return;
        }

        try {
            $existingPartial = $this->partialModel->getById($id);
            if (!$existingPartial || $existingPartial[PartialEntity::DELETED_AT] !== null) {
                $this->responseHandler->sendFailure("Parcial no encontrado.", 404);
                return;
            }

            $partialName = isset($data[PartialEntity::PARTIAL_NAME]) ? trim($data[PartialEntity::PARTIAL_NAME]) : null;

            if (array_key_exists(PartialEntity::PARTIAL_NAME, $data) && $partialName !== $existingPartial[PartialEntity::PARTIAL_NAME]) {
                if ($partialName !== null && $partialName !== '' && $this->partialModel->findByName($partialName)) {
                    $this->responseHandler->sendFailure("Ya existe un parcial activo con el nombre '{$partialName}'.", 409);
                    return;
                }
            }

            $updateData = [];
            if (array_key_exists(PartialEntity::PARTIAL_NAME, $data)) {
                $updateData[PartialEntity::PARTIAL_NAME] = $partialName;
            }

            if (empty($updateData)) {
                 $this->responseHandler->sendSuccess(["partial" => $existingPartial], "Parcial actualizado exitosamente (no se detectaron cambios).");
                 return;
            }

            $this->partialModel->updateById($id, $updateData);
            
            $updatedPartial = $this->partialModel->getById($id);
            
            $this->responseHandler->sendSuccess(["partial" => $updatedPartial], "Parcial actualizado exitosamente.");

        } catch (Exception $e) {
            $this->responseHandler->sendFailure("Error al actualizar el parcial. Revisar logs.", 500, $e);
        }
    }

    /**
     * Realiza un borrado lógico (soft delete) de un parcial (Servicio 35).
     * @param int $id ID del parcial a eliminar.
     * @return void
     */
    public function deleteOne($id)
    {
        if (!is_numeric($id) || (int)$id <= 0) {
            $this->responseHandler->sendFailure("El ID debe ser un número entero positivo.", 400);
            return;
        }

        try {
            $existingPartial = $this->partialModel->getById($id);
            
            if (!$existingPartial) {
                $this->responseHandler->sendFailure("No se encontró el parcial con id: $id", 404);
                return;
            }

            $deleted = $this->partialModel->deleteById($id);

            if ($deleted) {
                $deletedPartial = $this->partialModel->getById($id);
                $this->responseHandler->sendSuccess(["partial" => $deletedPartial], "Parcial eliminado exitosamente.");
            } else {
                $this->responseHandler->sendFailure("Error interno al intentar eliminar el parcial.", 500);
            }

        } catch (Exception $e) {
            $this->responseHandler->sendFailure("Error al eliminar el parcial. Revisar logs.", 500, $e);
        }
    }
}