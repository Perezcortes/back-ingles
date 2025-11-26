<?php

require_once __DIR__ . '/../models/Major.php';
require_once __DIR__ . '/../responses/ResponseHandler.php'; 
require_once __DIR__ . '/../entities/Major.php';

use App\Entities\Major as MajorEntity;

class MajorController
{
    private $majorModel;
    private $responseHandler;

    public function __construct()
    {
        $this->majorModel = new Major();
        $this->responseHandler = new ResponseHandler();
    }

    /**
     * Crea una nueva carrera (Servicio 26).
     * @param array $data Los datos a insertar.
     * @return void
     */
    public function create($data)
    {
        if (!isset($data[MajorEntity::MAJOR_NAME]) || trim($data[MajorEntity::MAJOR_NAME]) === '') {
            $this->responseHandler->sendFailure("El campo 'major_name' es obligatorio.", 400);
            return;
        }

        $majorName = trim($data[MajorEntity::MAJOR_NAME]);
        $description = isset($data[MajorEntity::DESCRIPTION]) ? trim($data[MajorEntity::DESCRIPTION]) : null;

        try {
            if ($this->majorModel->findByName($majorName)) {
                $this->responseHandler->sendFailure("Ya existe una carrera activa con el nombre '{$majorName}'.", 409);
                return;
            }

            $majorData = [
                MajorEntity::MAJOR_NAME => $majorName,
                MajorEntity::DESCRIPTION => $description,
            ];
            
            $createdId = $this->majorModel->create($majorData);

            if (!$createdId) {
                $this->responseHandler->sendFailure("Error al crear la carrera en la base de datos.", 500);
                return;
            }

            $newMajor = $this->majorModel->getById($createdId);
            
            $this->responseHandler->sendSuccess(["major" => $newMajor], "Carrera creada exitosamente.", 201);

        } catch (Exception $e) {
            $this->responseHandler->sendFailure("Error en el proceso de creación de la carrera. Revisar logs.", 500, $e);
        }
    }

    /**
     * Obtiene todas las carreras activas (Servicio 27).
     * @return void
     */
    public function getAll()
    {
        try {
            $majors = $this->majorModel->getAll();
            
            $this->responseHandler->sendSuccess(["majors" => $majors], "Carreras encontradas exitosamente.");

        } catch (Exception $e) {
            $this->responseHandler->sendFailure("Error al obtener las carreras. Revisar logs.", 500, $e);
        }
    }

    /**
     * Obtiene una carrera por su ID (Servicio 28).
     * @param int $id ID de la carrera.
     * @return void
     */
    public function getOneById($id)
    {
        if (!is_numeric($id) || (int)$id <= 0) {
            $this->responseHandler->sendFailure("El ID debe ser un número entero positivo.", 400);
            return;
        }

        try {
            $major = $this->majorModel->getById($id);

            if (!$major || $major[MajorEntity::DELETED_AT] !== null) {
                $this->responseHandler->sendFailure("Carrera no encontrada.", 404);
                return;
            }

            $this->responseHandler->sendSuccess(["major" => $major], "Carrera encontrada exitosamente.");

        } catch (Exception $e) {
            $this->responseHandler->sendFailure("Error al obtener la carrera. Revisar logs.", 500, $e);
        }
    }

    /**
     * Actualiza los datos de una carrera existente (Servicio 29).
     * @param int $id ID de la carrera a actualizar.
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
            $this->responseHandler->sendFailure("Se requiere al menos un campo para actualizar la carrera.", 400);
            return;
        }

        try {
            $existingMajor = $this->majorModel->getById($id);
            if (!$existingMajor || $existingMajor[MajorEntity::DELETED_AT] !== null) {
                $this->responseHandler->sendFailure("Carrera no encontrada.", 404);
                return;
            }

            $majorName = isset($data[MajorEntity::MAJOR_NAME]) ? trim($data[MajorEntity::MAJOR_NAME]) : null;
            $description = isset($data[MajorEntity::DESCRIPTION]) ? trim($data[MajorEntity::DESCRIPTION]) : null;

            if (array_key_exists(MajorEntity::MAJOR_NAME, $data) && $majorName !== $existingMajor[MajorEntity::MAJOR_NAME]) {
                if ($majorName !== null && $majorName !== '' && $this->majorModel->findByName($majorName)) {
                    $this->responseHandler->sendFailure("Ya existe una carrera activa con el nombre '{$majorName}'.", 409);
                    return;
                }
            }

            $updateData = [];
            if (array_key_exists(MajorEntity::MAJOR_NAME, $data)) {
                $updateData[MajorEntity::MAJOR_NAME] = $majorName;
            }
            if (array_key_exists(MajorEntity::DESCRIPTION, $data)) {
                $updateData[MajorEntity::DESCRIPTION] = $description;
            }

            if (empty($updateData)) {
                 $this->responseHandler->sendSuccess(["major" => $existingMajor], "Carrera actualizada exitosamente (no se detectaron cambios).");
                 return;
            }

            $this->majorModel->updateById($id, $updateData);
            
            $updatedMajor = $this->majorModel->getById($id);
            
            $this->responseHandler->sendSuccess(["major" => $updatedMajor], "Carrera actualizada exitosamente.");

        } catch (Exception $e) {
            $this->responseHandler->sendFailure("Error al actualizar la carrera. Revisar logs.", 500, $e);
        }
    }

    /**
     * Realiza un borrado lógico (soft delete) de una carrera (Servicio 30).
     * @param int $id ID de la carrera a eliminar.
     * @return void
     */
    public function deleteOne($id)
    {
        if (!is_numeric($id) || (int)$id <= 0) {
            $this->responseHandler->sendFailure("El ID debe ser un número entero positivo.", 400);
            return;
        }

        try {
            $existingMajor = $this->majorModel->getById($id);
            if (!$existingMajor) {
                $this->responseHandler->sendFailure("No se encontró la carrera con id: $id", 404);
                return;
            }

            $deleted = $this->majorModel->deleteById($id);

            if ($deleted) {
                $deletedMajor = $this->majorModel->getById($id);
                $this->responseHandler->sendSuccess(["major" => $deletedMajor], "Carrera eliminada exitosamente.");
            } else {
                $this->responseHandler->sendFailure("Error interno al intentar eliminar la carrera.", 500);
            }

        } catch (Exception $e) {
            $this->responseHandler->sendFailure("Error al eliminar la carrera. Revisar logs.", 500, $e);
        }
    }
}