<?php
require_once __DIR__ . '/../models/OptionMultipleMatching.php';

class OptionMultipleMatchingController
{
    // Listar activos
    public static function getAll()
    {
        try {
            $model = new OptionMultipleMatching();
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
        if (!is_numeric($id)) return self::sendError(400, "El id debe ser numérico.");

        try {
            $model = new OptionMultipleMatching();
            $item = $model->obtenerPorId($id);

            if ($item) {
                http_response_code(200);
                echo json_encode($item);
            } else {
                self::sendError(404, "Opción no encontrada.");
            }
        } catch (Exception $e) {
            self::sendError(500, "Error al obtener el registro.", $e);
        }
    }

    // Obtener por id_multiple_matching_template
    public static function getByTemplate($id_multiple_matching_template)
    {
        if (!is_numeric($id_multiple_matching_template)) {
            return self::sendError(400, "El id_multiple_matching_template debe ser numérico.");
        }

        try {
            $model = new OptionMultipleMatching();
            $items = $model->obtenerPorTemplate($id_multiple_matching_template);

            http_response_code(200);
            echo json_encode($items);
        } catch (Exception $e) {
            self::sendError(500, "Error al obtener por template.", $e);
        }
    }

    // Crear
    public static function create($data)
    {
        $required = ['title', 'description', 'id_multiple_matching_template'];
        foreach ($required as $field) {
            if (!isset($data[$field])) {
                http_response_code(400);
                echo json_encode(["error" => "El campo '$field' es obligatorio"]);
                return;
            }
        }

        if (trim($data['title']) === '' || trim($data['description']) === '') {
            http_response_code(400);
            echo json_encode(["error" => "Los campos 'title' y 'description' no pueden ser vacíos"]);
            return;
        }

        if (!is_numeric($data['id_multiple_matching_template'])) {
            http_response_code(400);
            echo json_encode(["error" => "El campo 'id_multiple_matching_template' debe ser numérico"]);
            return;
        }

        try {
            $model = new OptionMultipleMatching();
            $creado = $model->crear([
                'title' => trim($data['title']),
                'description' => trim($data['description']),
                'id_multiple_matching_template' => (int)$data['id_multiple_matching_template'],
            ]);

            if ($creado) {
                http_response_code(201);
                echo json_encode([
                    "message" => "Opción creada exitosamente.",
                    "option_multiple_matching" => [
                        "title" => trim($data['title']),
                        "description" => trim($data['description']),
                        "id_multiple_matching_template" => (int)$data['id_multiple_matching_template'],
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
        if (!is_numeric($id)) return self::sendError(400, "El id debe ser numérico.");
        if (empty($data)) return self::sendError(400, "Se requiere al menos un campo para actualizar.");

        $payload = [];

        if (array_key_exists('title', $data)) {
            if (trim((string)$data['title']) === '') return self::sendError(400, "El campo 'title' no puede ser vacío.");
            $payload['title'] = trim($data['title']);
        }

        if (array_key_exists('description', $data)) {
            if (trim((string)$data['description']) === '') return self::sendError(400, "El campo 'description' no puede ser vacío.");
            $payload['description'] = trim($data['description']);
        }

        if (array_key_exists('id_multiple_matching_template', $data)) {
            if (!is_numeric($data['id_multiple_matching_template'])) {
                return self::sendError(400, "El campo 'id_multiple_matching_template' debe ser numérico.");
            }
            $payload['id_multiple_matching_template'] = (int)$data['id_multiple_matching_template'];
        }

        if (empty($payload)) return self::sendError(400, "No hay campos válidos para actualizar.");

        try {
            $model = new OptionMultipleMatching();
            $existente = $model->obtenerPorId($id);
            if (!$existente) return self::sendError(404, "No se encontró la opción con id: $id");

            $actualizado = $model->actualizarPorId($id, $payload);

            if ($actualizado) {
                $nuevo = $model->obtenerPorId($id);
                http_response_code(200);
                echo json_encode([
                    "message" => "Opción actualizada exitosamente.",
                    "option_multiple_matching" => $nuevo
                ]);
            } else {
                self::sendError(500, "Error interno al intentar actualizar.");
            }
        } catch (Exception $e) {
            self::sendError(500, "Error al actualizar la opción.", $e);
        }
    }

    // Soft delete
    public static function deleteOne($id)
    {
        if (!is_numeric($id)) return self::sendError(400, "El id debe ser numérico.");

        try {
            $model = new OptionMultipleMatching();
            $item = $model->obtenerPorId($id);
            if (!$item) return self::sendError(404, "No se encontró la opción con id: $id");

            $deleted = $model->eliminarPorId($id);

            if ($deleted) {
                http_response_code(200);
                echo json_encode([
                    "message" => "Opción eliminada exitosamente.",
                    "option_multiple_matching" => $item
                ]);
            } else {
                self::sendError(500, "Error interno al intentar eliminar.");
            }
        } catch (Exception $e) {
            self::sendError(500, "Error al eliminar la opción.", $e);
        }
    }

    // Restore
    public static function restore($id)
    {
        if (!is_numeric($id)) return self::sendError(400, "El id debe ser numérico.");

        try {
            $model = new OptionMultipleMatching();
            $restored = $model->restaurarPorId($id);

            if ($restored) {
                http_response_code(200);
                echo json_encode([
                    "message" => "Opción restaurada exitosamente.",
                    "option_multiple_matching_id" => (int)$id
                ]);
            } else {
                self::sendError(500, "Error interno al intentar restaurar.");
            }
        } catch (Exception $e) {
            self::sendError(500, "Error al restaurar la opción.", $e);
        }
    }

    // Hard delete
    public static function deletePermanent($id)
    {
        if (!is_numeric($id)) return self::sendError(400, "El id debe ser numérico.");

        try {
            $model = new OptionMultipleMatching();
            $deleted = $model->eliminarPermanentePorId($id);

            if ($deleted) {
                http_response_code(200);
                echo json_encode([
                    "message" => "Opción eliminada permanentemente.",
                    "option_multiple_matching_id" => (int)$id
                ]);
            } else {
                self::sendError(500, "Error interno al intentar eliminar permanentemente.");
            }
        } catch (Exception $e) {
            self::sendError(500, "Error al eliminar permanentemente la opción.", $e);
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
