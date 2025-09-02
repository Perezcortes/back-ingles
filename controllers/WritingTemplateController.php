<?php
require_once __DIR__ . '/../models/WritingTemplate.php';

class WritingTemplateController
{
    // Get all writing templates (no eliminados)
    public static function getAll()
    {
        try {
            $model = new WritingTemplate();
            $items = $model->obtenerTodos();

            http_response_code(200);
            echo json_encode($items);
        } catch (Exception $e) {
            self::sendError(500, "Error al obtener los registros de la tabla.", $e);
        }
    }

    // Get one by ID
    public static function getOne($id)
    {
        if (!is_numeric($id)) {
            return self::sendError(400, "El id debe ser numérico.");
        }

        try {
            $model = new WritingTemplate();
            $item = $model->obtenerPorId($id);

            if ($item) {
                http_response_code(200);
                echo json_encode($item);
            } else {
                self::sendError(404, "Plantilla de writing no encontrada");
            }
        } catch (Exception $e) {
            self::sendError(500, "Error al obtener el registro.", $e);
        }
    }

    // Create
    public static function create($data)
    {
        // Validaciones mínimas (campos requeridos)
        $required = ['id_level','id_exam_type','instruction','story_description','who_created','topic'];

        foreach ($required as $field) {
            if (!isset($data[$field])) {
                http_response_code(400);
                echo json_encode(["error" => "El campo '$field' es obligatorio"]);
                return;
            }
        }

        if (!is_numeric($data['id_level']) || !is_numeric($data['id_exam_type']) || !is_numeric($data['who_created'])) {
            http_response_code(400);
            echo json_encode(["error" => "Los campos 'id_level', 'id_exam_type' y 'who_created' deben ser numéricos"]);
            return;
        }

        if (trim($data['instruction']) === '' || trim($data['story_description']) === '' || trim($data['topic']) === '') {
            http_response_code(400);
            echo json_encode(["error" => "Los campos 'instruction', 'story_description' y 'topic' no pueden ser vacíos"]);
            return;
        }

        try {
            $model = new WritingTemplate();
            $creado = $model->crear([
                'id_level' => (int)$data['id_level'],
                'id_exam_type' => (int)$data['id_exam_type'],
                'instruction' => trim($data['instruction']),
                'story_description' => trim($data['story_description']),
                'who_created' => (int)$data['who_created'],
                'topic' => trim($data['topic']),
            ]);

            if ($creado) {
                http_response_code(201);
                echo json_encode([
                    "message" => "Plantilla de writing creada exitosamente.",
                    "writing_template" => [
                        "id_level" => (int)$data['id_level'],
                        "id_exam_type" => (int)$data['id_exam_type'],
                        "instruction" => trim($data['instruction']),
                        "story_description" => trim($data['story_description']),
                        "who_created" => (int)$data['who_created'],
                        "topic" => trim($data['topic']),
                    ]
                ]);
            } else {
                self::sendError(500, "Error al crear un registro.");
            }
        } catch (Exception $e) {
            self::sendError(500, "Error al crear un registro.", $e);
        }
    }

    // Update
    public static function update($id, $data)
    {
        if (!is_numeric($id)) {
            return self::sendError(400, "El id debe ser numérico.");
        }

        if (empty($data)) {
            return self::sendError(400, "Se requiere al menos un campo para actualizar la plantilla de writing.");
        }

        // Validaciones de tipos / contenidos si llegan
        $payload = [];

        if (array_key_exists('id_level', $data)) {
            if (!is_numeric($data['id_level'])) {
                return self::sendError(400, "El campo 'id_level' debe ser numérico.");
            }
            $payload['id_level'] = (int)$data['id_level'];
        }

        if (array_key_exists('id_exam_type', $data)) {
            if (!is_numeric($data['id_exam_type'])) {
                return self::sendError(400, "El campo 'id_exam_type' debe ser numérico.");
            }
            $payload['id_exam_type'] = (int)$data['id_exam_type'];
        }

        if (array_key_exists('who_created', $data)) {
            if (!is_numeric($data['who_created'])) {
                return self::sendError(400, "El campo 'who_created' debe ser numérico.");
            }
            $payload['who_created'] = (int)$data['who_created'];
        }

        if (array_key_exists('instruction', $data)) {
            if (trim((string)$data['instruction']) === '') {
                return self::sendError(400, "El campo 'instruction' no puede ser vacío.");
            }
            $payload['instruction'] = trim($data['instruction']);
        }

        if (array_key_exists('story_description', $data)) {
            if (trim((string)$data['story_description']) === '') {
                return self::sendError(400, "El campo 'story_description' no puede ser vacío.");
            }
            $payload['story_description'] = trim($data['story_description']);
        }

        if (array_key_exists('topic', $data)) {
            if (trim((string)$data['topic']) === '') {
                return self::sendError(400, "El campo 'topic' no puede ser vacío.");
            }
            $payload['topic'] = trim($data['topic']);
        }

        if (empty($payload)) {
            return self::sendError(400, "No hay campos válidos para actualizar.");
        }

        try {
            $model = new WritingTemplate();
            $existente = $model->obtenerPorId($id);
            if (!$existente) {
                return self::sendError(404, "No se encontró la plantilla de writing con id: $id");
            }

            $actualizado = $model->actualizarPorId($id, $payload);

            if ($actualizado) {
                $actual = $model->obtenerPorId($id);

                http_response_code(200);
                echo json_encode([
                    "message" => "Plantilla de writing actualizada exitosamente",
                    "writing_template" => $actual
                ]);
            } else {
                self::sendError(500, "Error interno al intentar actualizar la plantilla.");
            }
        } catch (Exception $e) {
            self::sendError(500, "Error al actualizar la plantilla de writing.", $e);
        }
    }

    // Delete (soft delete)
    public static function deleteOne($id)
    {
        if (!is_numeric($id)) {
            return self::sendError(400, "El id debe ser numérico.");
        }

        try {
            $model = new WritingTemplate();

            // Obtener el registro antes de eliminar
            $item = $model->obtenerPorId($id);
            if (!$item) {
                return self::sendError(404, "No se encontró la plantilla de writing con id: $id");
            }

            // Soft delete
            $eliminado = $model->eliminarPorId($id);

            if ($eliminado) {
                http_response_code(200);
                echo json_encode([
                    "message" => "Plantilla de writing eliminada exitosamente.",
                    "writing_template" => $item
                ]);
            } else {
                self::sendError(500, "Error interno al intentar eliminar la plantilla.");
            }
        } catch (Exception $e) {
            self::sendError(500, "Error al eliminar la plantilla de writing.", $e);
        }
    }

    // Helper para errores
    private static function sendError($code, $message, $exception = null)
    {
        http_response_code($code);
        $response = ["error" => $message];

        if (getenv('APP_ENV') === 'development' && $exception) {
            $response["details"] = $exception->getMessage();
        }

        echo json_encode($response);
    }
}
