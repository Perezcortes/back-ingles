<?php
require_once __DIR__ . '/../models/QuestionType.php';
require_once __DIR__ . '/../responses/ResponseHandler.php'; 
require_once __DIR__ . '/../entities/QuestionType.php';

use App\Entities\QuestionType as QuestionTypeEntity;

class QuestionTypeController 
{
    private $questionTypeModel;
    private $responseHandler;

    public function __construct()
    {
        $this->questionTypeModel = new QuestionType();
        $this->responseHandler = new ResponseHandler();
    }

    /**
     * Servicio 41: Crear un tipo de pregunta.
     */
    public function create($data)
    {
        if (!isset($data[QuestionTypeEntity::QUESTION_NAME]) || trim($data[QuestionTypeEntity::QUESTION_NAME]) === '') {
            $this->responseHandler->sendFailure("El campo 'question_name' es obligatorio.", 400);
            return;
        }

        $questionName = trim($data[QuestionTypeEntity::QUESTION_NAME]);

        try {
            if ($this->questionTypeModel->findByName($questionName)) {
                $this->responseHandler->sendFailure("Ya existe un tipo de pregunta con el nombre '{$questionName}'.", 409);
                return;
            }

            $newData = [QuestionTypeEntity::QUESTION_NAME => $questionName];
            $createdId = $this->questionTypeModel->create($newData);

            if (!$createdId) {
                $this->responseHandler->sendFailure("Error al crear el tipo de pregunta.", 500);
                return;
            }

            $createdType = $this->questionTypeModel->getById($createdId);
            $this->responseHandler->sendSuccess(["question_type" => $createdType], "Tipo de pregunta creado exitosamente.", 201);

        } catch (Exception $e) {
            $this->responseHandler->sendFailure("Error interno al crear.", 500, $e);
        }
    }

    /**
     * Servicio 42: Obtener Todos los tipos de Pregunta.
     */
    public function getAll()
    {
        try {
            $types = $this->questionTypeModel->getAll();
            $this->responseHandler->sendSuccess(["question_types" => $types], "Tipos de pregunta encontrados exitosamente.");
        } catch (Exception $e) {
            $this->responseHandler->sendFailure("Error al obtener los tipos de pregunta.", 500, $e);
        }
    }

    /**
     * Servicio 43: Obtener un Tipo de pregunta por ID.
     */
    public function getOneById($id)
    {
        if (!is_numeric($id) || (int)$id <= 0) {
            $this->responseHandler->sendFailure("El ID debe ser numérico.", 400);
            return;
        }

        try {
            $type = $this->questionTypeModel->getById($id);

            if (!$type || $type[QuestionTypeEntity::DELETED_AT] !== null) {
                $this->responseHandler->sendFailure("Tipo de pregunta no encontrado.", 404);
                return;
            }

            $this->responseHandler->sendSuccess(["question_type" => $type], "Tipo de pregunta encontrado exitosamente.");

        } catch (Exception $e) {
            $this->responseHandler->sendFailure("Error al obtener el registro.", 500, $e);
        }
    }

    /**
     * Servicio 44: Actualizar un Tipo de pregunta.
     */
    public function update($id, $data)
    {
        if (!is_numeric($id) || (int)$id <= 0) {
            $this->responseHandler->sendFailure("El ID debe ser numérico.", 400);
            return;
        }

        if (empty($data)) {
            $this->responseHandler->sendFailure("Se requieren datos para actualizar.", 400);
            return;
        }

        try {
            // Verificar existencia
            $existingType = $this->questionTypeModel->getById($id);
            if (!$existingType || $existingType[QuestionTypeEntity::DELETED_AT] !== null) {
                $this->responseHandler->sendFailure("Tipo de pregunta no encontrado.", 404);
                return;
            }

            $questionName = isset($data[QuestionTypeEntity::QUESTION_NAME]) ? trim($data[QuestionTypeEntity::QUESTION_NAME]) : null;

            // Validar unicidad si cambia el nombre
            if ($questionName && $questionName !== $existingType[QuestionTypeEntity::QUESTION_NAME]) {
                if ($this->questionTypeModel->findByName($questionName)) {
                    $this->responseHandler->sendFailure("Ya existe un tipo de pregunta con el nombre '{$questionName}'.", 409);
                    return;
                }
            }

            // Preparar datos
            $updateData = [];
            if ($questionName) $updateData[QuestionTypeEntity::QUESTION_NAME] = $questionName;

            if (empty($updateData)) {
                 $this->responseHandler->sendSuccess(["question_type" => $existingType], "Tipo de pregunta actualizado exitosamente (sin cambios).");
                 return;
            }

            // Actualizar
            $this->questionTypeModel->updateById($id, $updateData);
            $updatedType = $this->questionTypeModel->getById($id);
            
            $this->responseHandler->sendSuccess(["question_type" => $updatedType], "Tipo de pregunta actualizado exitosamente.");

        } catch (Exception $e) {
            $this->responseHandler->sendFailure("Error al actualizar.", 500, $e);
        }
    }

    /**
     * Servicio 45: Eliminar un Tipo de pregunta.
     */
    public function deleteOne($id)
    {
        if (!is_numeric($id) || (int)$id <= 0) {
            $this->responseHandler->sendFailure("El ID debe ser numérico.", 400);
            return;
        }

        try {
            $existingType = $this->questionTypeModel->getById($id);
            
            if (!$existingType) {
                $this->responseHandler->sendFailure("Tipo de pregunta no encontrado.", 404);
                return;
            }

            $deleted = $this->questionTypeModel->deleteById($id);

            if ($deleted) {
                $deletedType = $this->questionTypeModel->getById($id);
                $this->responseHandler->sendSuccess(["question_type" => $deletedType], "Tipo de pregunta eliminada exitosamente.");
            } else {
                $this->responseHandler->sendFailure("Error al eliminar el registro.", 500);
            }

        } catch (Exception $e) {
            $this->responseHandler->sendFailure("Error al eliminar.", 500, $e);
        }
    }
}