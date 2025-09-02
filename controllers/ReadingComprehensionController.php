<?php
require_once __DIR__ . '/../models/ReadingComprehension.php';

class ReadingComprehensionController
{
    // Listar activos
    public static function getAll()
    {
        try {
            $model = new ReadingComprehension();
            $items = $model->obtenerTodos();
            http_response_code(200);
            echo json_encode($items);
        } catch (Exception $e) {
            self::sendError(500, "Error al obtener los registros de reading_comprehension.", $e);
        }
    }

    // Obtener uno por ID
    public static function getOne($id)
    {
        if (!is_numeric($id)) return self::sendError(400, "El id debe ser numérico.");

        try {
            $model = new ReadingComprehension();
            $item = $model->obtenerPorId($id);

            if ($item) {
                http_response_code(200);
                echo json_encode($item);
            } else {
                self::sendError(404, "Registro de reading_comprehension no encontrado.");
            }
        } catch (Exception $e) {
            self::sendError(500, "Error al obtener el registro.", $e);
        }
    }

    // Obtener por id_reading_comprehension_template (activos)
    public static function getByTemplate($id_reading_comprehension_template)
    {
        if (!is_numeric($id_reading_comprehension_template)) {
            return self::sendError(400, "El id_reading_comprehension_template debe ser numérico.");
        }

        try {
            $model = new ReadingComprehension();
            $items = $model->obtenerPorTemplate($id_reading_comprehension_template);
            http_response_code(200);
            echo json_encode($items);
        } catch (Exception $e) {
            self::sendError(500, "Error al obtener por template.", $e);
        }
    }

    // Crear
    public static function create($data)
    {
        if (!isset($data['sentence']) || trim($data['sentence']) === '') {
            http_response_code(400);
            echo json_encode(["error" => "El campo 'sentence' es obligatorio"]);
            return;
        }
        if (mb_strlen($data['sentence']) > 300) {
            http_response_code(400);
            echo json_encode(["error" => "El campo 'sentence' no debe exceder 300 caracteres"]);
            return;
        }
        if (!isset($data['id_reading_comprehension_template']) || !is_numeric($data['id_reading_comprehension_template'])) {
            http_response_code(400);
            echo json_encode(["error" => "El campo 'id_reading_comprehension_template' es obligatorio y debe ser numérico"]);
            return;
        }

        try {
            $model = new ReadingComprehension();
            $creado = $model->crear([
                'sentence' => trim($data['sentence']),
                'id_reading_comprehension_template' => (int)$data['id_reading_comprehension_template'],
            ]);

            if ($creado) {
                http_response_code(201);
                echo json_encode([
                    "message" => "Registro reading_comprehension creado exitosamente.",
                    "reading_comprehension" => [
                        "sentence" => trim($data['sentence']),
                        "id_reading_comprehension_template" => (int)$data['id_reading_comprehension_template'],
                    ]
                ]);
            } else {
                self::sendError(500, "Error al crear el registro.");
            }
        } catch (Exception $e) {
            self::sendError(500, "Error al crear el registro.", $e);
        }
    }

    // Actualizar por ID
    public static function update($id, $data)
    {
        if (!is_numeric($id)) return self::sendError(400, "El id debe ser numérico.");
        if (empty($data)) return self::sendError(400, "Se requiere al menos un campo para actualizar.");

        $payload = [];

        if (array_key_exists('sentence', $data)) {
            $s = trim((string)$data['sentence']);
            if ($s === '' || mb_strlen($s) > 300) {
                return self::sendError(400, "El campo 'sentence' no puede ser vacío ni exceder 300 caracteres.");
            }
            $payload['sentence'] = $s;
        }

        if (array_key_exists('id_reading_comprehension_template', $data)) {
            if (!is_numeric($data['id_reading_comprehension_template'])) {
                return self::sendError(400, "El campo 'id_reading_comprehension_template' debe ser numérico.");
            }
            $payload['id_reading_comprehension_template'] = (int)$data['id_reading_comprehension_template'];
        }

        if (empty($payload)) return self::sendError(400, "No hay campos válidos para actualizar.");

        try {
            $model = new ReadingComprehension();
            $existente = $model->obtenerPorId($id);
            if (!$existente) return self::sendError(404, "No se encontró el registro con id: $id");

            $actualizado = $model->actualizarPorId($id, $payload);

            if ($actualizado) {
                $nuevo = $model->obtenerPorId($id);
                http_response_code(200);
                echo json_encode([
                    "message" => "Registro actualizado exitosamente.",
                    "reading_comprehension" => $nuevo
                ]);
            } else {
                self::sendError(500, "Error interno al intentar actualizar.");
            }
        } catch (Exception $e) {
            self::sendError(500, "Error al actualizar el registro.", $e);
        }
    }

    // Soft delete
    public static function deleteOne($id)
    {
        if (!is_numeric($id)) return self::sendError(400, "El id debe ser numérico.");

        try {
            $model = new ReadingComprehension();
            $item = $model->obtenerPorId($id);
            if (!$item) return self::sendError(404, "No se encontró el registro con id: $id");

            $deleted = $model->eliminarPorId($id);

            if ($deleted) {
                http_response_code(200);
                echo json_encode([
                    "message" => "Registro eliminado exitosamente (soft delete).",
                    "reading_comprehension" => $item
                ]);
            } else {
                self::sendError(500, "Error interno al intentar eliminar.");
            }
        } catch (Exception $e) {
            self::sendError(500, "Error al eliminar el registro.", $e);
        }
    }

    // Restore
    public static function restore($id)
    {
        if (!is_numeric($id)) return self::sendError(400, "El id debe ser numérico.");

        try {
            $model = new ReadingComprehension();
            $restored = $model->restaurarPorId($id);

            if ($restored) {
                http_response_code(200);
                echo json_encode([
                    "message" => "Registro restaurado exitosamente.",
                    "reading_comprehension_id" => (int)$id
                ]);
            } else {
                self::sendError(500, "Error interno al intentar restaurar.");
            }
        } catch (Exception $e) {
            self::sendError(500, "Error al restaurar el registro.", $e);
        }
    }

    // Hard delete
    public static function deletePermanent($id)
    {
        if (!is_numeric($id)) return self::sendError(400, "El id debe ser numérico.");

        try {
            $model = new ReadingComprehension();
            $deleted = $model->eliminarPermanentePorId($id);

            if ($deleted) {
                http_response_code(200);
                echo json_encode([
                    "message" => "Registro eliminado permanentemente.",
                    "reading_comprehension_id" => (int)$id
                ]);
            } else {
                self::sendError(500, "Error interno al intentar eliminar permanentemente.");
            }
        } catch (Exception $e) {
            self::sendError(500, "Error al eliminar permanentemente el registro.", $e);
        }
    }

    // Helper
    private static function sendError($code, $message, $exception = null)
    {
        http_response_code($code);
        $response = ["error" => $message];
        if (getenv('APP_ENV') === 'development' && $exception) {
            $response["details"] = $exception->getMessage()];
        }
        echo json_encode($response);
    }
}
