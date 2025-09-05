<?php
require_once __DIR__ . '/../models/MultipleChoiceCloze.php';

class MultipleChoiceClozeController
{
    // Listar activos
    public static function getAll()
    {
        try {
            $model = new MultipleChoiceCloze();
            $items = $model->obtenerTodos();
            http_response_code(200);
            echo json_encode($items);
        } catch (Exception $e) {
            self::sendError(500, "Error al obtener los registros de multiple_choice_cloze.", $e);
        }
    }

    // Obtener uno por ID
    public static function getOne($id)
    {
        if (!is_numeric($id)) return self::sendError(400, "El id debe ser numérico.");

        try {
            $model = new MultipleChoiceCloze();
            $item = $model->obtenerPorId($id);

            if ($item) {
                http_response_code(200);
                echo json_encode($item);
            } else {
                self::sendError(404, "Registro no encontrado.");
            }
        } catch (Exception $e) {
            self::sendError(500, "Error al obtener el registro.", $e);
        }
    }

    // Obtener por id_template_multiple_choice_cloze (activos)
    public static function getByTemplate($id_template_multiple_choice_cloze)
    {
        if (!is_numeric($id_template_multiple_choice_cloze)) {
            return self::sendError(400, "El id_template_multiple_choice_cloze debe ser numérico.");
        }

        try {
            $model = new MultipleChoiceCloze();
            $items = $model->obtenerPorTemplate($id_template_multiple_choice_cloze);
            http_response_code(200);
            echo json_encode($items);
        } catch (Exception $e) {
            self::sendError(500, "Error al obtener por plantilla.", $e);
        }
    }

    // Crear
    public static function create($data)
    {
        // Validaciones
        if (!isset($data['counter']) || !is_numeric($data['counter'])) {
            http_response_code(400);
            echo json_encode(["error" => "El campo 'counter' es obligatorio y debe ser numérico"]);
            return;
        }
        if (!isset($data['texto']) || trim($data['texto']) === '') {
            http_response_code(400);
            echo json_encode(["error" => "El campo 'texto' es obligatorio"]);
            return;
        }
        if (!isset($data['id_template_multiple_choice_cloze']) || !is_numeric($data['id_template_multiple_choice_cloze'])) {
            http_response_code(400);
            echo json_encode(["error" => "El campo 'id_template_multiple_choice_cloze' es obligatorio y debe ser numérico"]);
            return;
        }

        try {
            $model = new MultipleChoiceCloze();
            $creado = $model->crear([
                'counter' => (int)$data['counter'],
                'texto' => trim($data['texto']),
                'id_template_multiple_choice_cloze' => (int)$data['id_template_multiple_choice_cloze'],
            ]);

            if ($creado) {
                http_response_code(201);
                echo json_encode([
                    "message" => "Registro creado exitosamente.",
                    "multiple_choice_cloze" => [
                        "counter" => (int)$data['counter'],
                        "texto" => trim($data['texto']),
                        "id_template_multiple_choice_cloze" => (int)$data['id_template_multiple_choice_cloze'],
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

        if (array_key_exists('counter', $data)) {
            if (!is_numeric($data['counter'])) {
                return self::sendError(400, "El campo 'counter' debe ser numérico.");
            }
            $payload['counter'] = (int)$data['counter'];
        }

        if (array_key_exists('texto', $data)) {
            $t = trim((string)$data['texto']);
            if ($t === '') return self::sendError(400, "El campo 'texto' no puede ser vacío.");
            $payload['texto'] = $t;
        }

        if (array_key_exists('id_template_multiple_choice_cloze', $data)) {
            if (!is_numeric($data['id_template_multiple_choice_cloze'])) {
                return self::sendError(400, "El campo 'id_template_multiple_choice_cloze' debe ser numérico.");
            }
            $payload['id_template_multiple_choice_cloze'] = (int)$data['id_template_multiple_choice_cloze'];
        }

        if (empty($payload)) return self::sendError(400, "No hay campos válidos para actualizar.");

        try {
            $model = new MultipleChoiceCloze();
            $existente = $model->obtenerPorId($id);
            if (!$existente) return self::sendError(404, "No se encontró el registro con id: $id");

            $actualizado = $model->actualizarPorId($id, $payload);

            if ($actualizado) {
                $nuevo = $model->obtenerPorId($id);
                http_response_code(200);
                echo json_encode([
                    "message" => "Registro actualizado exitosamente.",
                    "multiple_choice_cloze" => $nuevo
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
            $model = new MultipleChoiceCloze();
            $item = $model->obtenerPorId($id);
            if (!$item) return self::sendError(404, "No se encontró el registro con id: $id");

            $deleted = $model->eliminarPorId($id);
            if ($deleted) {
                http_response_code(200);
                echo json_encode([
                    "message" => "Registro eliminado (soft delete) exitosamente.",
                    "multiple_choice_cloze" => $item
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
            $model = new MultipleChoiceCloze();
            $restored = $model->restaurarPorId($id);

            if ($restored) {
                http_response_code(200);
                echo json_encode([
                    "message" => "Registro restaurado exitosamente.",
                    "multiple_choice_cloze_id" => (int)$id
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
            $model = new MultipleChoiceCloze();
            $deleted = $model->eliminarPermanentePorId($id);

            if ($deleted) {
                http_response_code(200);
                echo json_encode([
                    "message" => "Registro eliminado permanentemente.",
                    "multiple_choice_cloze_id" => (int)$id
                ]);
            } else {
                self::sendError(500, "Error interno al intentar eliminar permanentemente.");
            }
        } catch (Exception $e) {
            self::sendError(500, "Error al eliminar permanentemente el registro.", $e);
        }
    }

    // Vaciar tabla
    public static function truncate()
    {
        try {
            $model = new MultipleChoiceCloze();
            $model->vaciarTabla();
            http_response_code(200);
            echo json_encode(["message" => "Tabla multiple_choice_cloze vaciada exitosamente."]);
        } catch (Exception $e) {
            self::sendError(500, "Error al vaciar la tabla multiple_choice_cloze.", $e);
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