<?php
require_once __DIR__ . '/../models/MultipleMatchingTemplate.php';

class MultipleMatchingTemplateController
{
    // Listar activos
    public static function getAll()
    {
        try {
            $model = new MultipleMatchingTemplate();
            $items = $model->obtenerTodos();

            http_response_code(200);
            echo json_encode($items);
        } catch (Exception $e) {
            self::sendError(500, "Error al obtener los registros.", $e);
        }
    }

    // Obtener uno por ID
    public static function getOne($id)
    {
        if (!is_numeric($id)) {
            return self::sendError(400, "El id debe ser numérico.");
        }

        try {
            $model = new MultipleMatchingTemplate();
            $item = $model->obtenerPorId($id);

            if ($item) {
                http_response_code(200);
                echo json_encode($item);
            } else {
                self::sendError(404, "Plantilla de multiple matching no encontrada.");
            }
        } catch (Exception $e) {
            self::sendError(500, "Error al obtener el registro.", $e);
        }
    }

    // Obtener por nivel
    public static function getByLevel($id_level)
    {
        if (!is_numeric($id_level)) {
            return self::sendError(400, "El id_level debe ser numérico.");
        }

        try {
            $model = new MultipleMatchingTemplate();
            $items = $model->obtenerPorNivel($id_level);

            http_response_code(200);
            echo json_encode($items);
        } catch (Exception $e) {
            self::sendError(500, "Error al obtener por nivel.", $e);
        }
    }

    // Crear
    public static function create($data)
    {
        $required = ['title', 'instruction', 'question_number', 'topic', 'id_level'];
        foreach ($required as $field) {
            if (!isset($data[$field])) {
                http_response_code(400);
                echo json_encode(["error" => "El campo '$field' es obligatorio"]);
                return;
            }
        }

        if (trim($data['title']) === '' || trim($data['instruction']) === '' || trim($data['topic']) === '') {
            http_response_code(400);
            echo json_encode(["error" => "Los campos 'title', 'instruction' y 'topic' no pueden ser vacíos"]);
            return;
        }

        if (!is_numeric($data['question_number']) || !is_numeric($data['id_level'])) {
            http_response_code(400);
            echo json_encode(["error" => "Los campos 'question_number' e 'id_level' deben ser numéricos"]);
            return;
        }

        try {
            $model = new MultipleMatchingTemplate();
            $creado = $model->crear([
                'title' => trim($data['title']),
                'instruction' => trim($data['instruction']),
                'question_number' => (int)$data['question_number'],
                'topic' => trim($data['topic']),
                'id_level' => (int)$data['id_level'],
            ]);

            if ($creado) {
                http_response_code(201);
                echo json_encode([
                    "message" => "Plantilla de multiple matching creada exitosamente.",
                    "multiple_matching_template" => [
                        "title" => trim($data['title']),
                        "instruction" => trim($data['instruction']),
                        "question_number" => (int)$data['question_number'],
                        "topic" => trim($data['topic']),
                        "id_level" => (int)$data['id_level'],
                    ]
                ]);
            } else {
                self::sendError(500, "Error al crear un registro.");
            }
        } catch (Exception $e) {
            self::sendError(500, "Error al crear un registro.", $e);
        }
    }

    // Actualizar por ID
    public static function update($id, $data)
    {
        if (!is_numeric($id)) {
            return self::sendError(400, "El id debe ser numérico.");
        }

        if (empty($data)) {
            return self::sendError(400, "Se requiere al menos un campo para actualizar.");
        }

        $payload = [];

        if (array_key_exists('title', $data)) {
            if (trim((string)$data['title']) === '') {
                return self::sendError(400, "El campo 'title' no puede ser vacío.");
            }
            $payload['title'] = trim($data['title']);
        }

        if (array_key_exists('instruction', $data)) {
            if (trim((string)$data['instruction']) === '') {
                return self::sendError(400, "El campo 'instruction' no puede ser vacío.");
            }
            $payload['instruction'] = trim($data['instruction']);
        }

        if (array_key_exists('question_number', $data)) {
            if (!is_numeric($data['question_number'])) {
                return self::sendError(400, "El campo 'question_number' debe ser numérico.");
            }
            $payload['question_number'] = (int)$data['question_number'];
        }

        if (array_key_exists('topic', $data)) {
            if (trim((string)$data['topic']) === '') {
                return self::sendError(400, "El campo 'topic' no puede ser vacío.");
            }
            $payload['topic'] = trim($data['topic']);
        }

        if (array_key_exists('id_level', $data)) {
            if (!is_numeric($data['id_level'])) {
                return self::sendError(400, "El campo 'id_level' debe ser numérico.");
            }
            $payload['id_level'] = (int)$data['id_level'];
        }

        if (empty($payload)) {
            return self::sendError(400, "No hay campos válidos para actualizar.");
        }

        try {
            $model = new MultipleMatchingTemplate();
            $existente = $model->obtenerPorId($id);

            if (!$existente) {
                return self::sendError(404, "No se encontró la plantilla con id: $id");
            }

            $actualizado = $model->actualizarPorId($id, $payload);

            if ($actualizado) {
                $nuevo = $model->obtenerPorId($id);
                http_response_code(200);
                echo json_encode([
                    "message" => "Plantilla de multiple matching actualizada exitosamente.",
                    "multiple_matching_template" => $nuevo
                ]);
            } else {
                self::sendError(500, "Error interno al intentar actualizar.");
            }
        } catch (Exception $e) {
            self::sendError(500, "Error al actualizar la plantilla.", $e);
        }
    }

    // Soft delete
    public static function deleteOne($id)
    {
        if (!is_numeric($id)) {
            return self::sendError(400, "El id debe ser numérico.");
        }

        try {
            $model = new MultipleMatchingTemplate();
            $item = $model->obtenerPorId($id);

            if (!$item) {
                return self::sendError(404, "No se encontró la plantilla con id: $id");
            }

            $deleted = $model->eliminarPorId($id);

            if ($deleted) {
                http_response_code(200);
                echo json_encode([
                    "message" => "Plantilla de multiple matching eliminada exitosamente.",
                    "multiple_matching_template" => $item
                ]);
            } else {
                self::sendError(500, "Error interno al intentar eliminar.");
            }
        } catch (Exception $e) {
            self::sendError(500, "Error al eliminar la plantilla.", $e);
        }
    }

    // Restore
    public static function restore($id)
    {
        if (!is_numeric($id)) {
            return self::sendError(400, "El id debe ser numérico.");
        }

        try {
            $model = new MultipleMatchingTemplate();
            $restored = $model->restaurarPorId($id);

            if ($restored) {
                http_response_code(200);
                echo json_encode([
                    "message" => "Plantilla restaurada exitosamente.",
                    "multiple_matching_template_id" => (int)$id
                ]);
            } else {
                self::sendError(500, "Error interno al intentar restaurar.");
            }
        } catch (Exception $e) {
            self::sendError(500, "Error al restaurar la plantilla.", $e);
        }
    }

    // Hard delete
    public static function deletePermanent($id)
    {
        if (!is_numeric($id)) {
            return self::sendError(400, "El id debe ser numérico.");
        }

        try {
            $model = new MultipleMatchingTemplate();
            $deleted = $model->eliminarPermanentePorId($id);

            if ($deleted) {
                http_response_code(200);
                echo json_encode([
                    "message" => "Plantilla eliminada permanentemente.",
                    "multiple_matching_template_id" => (int)$id
                ]);
            } else {
                self::sendError(500, "Error interno al intentar eliminar permanentemente.");
            }
        } catch (Exception $e) {
            self::sendError(500, "Error al eliminar permanentemente la plantilla.", $e);
        }
    }

    // Helper
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
