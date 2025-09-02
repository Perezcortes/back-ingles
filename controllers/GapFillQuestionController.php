<?php
require_once __DIR__ . '/../models/GapFillQuestion.php';

class GapFillQuestionController
{
    // Listar todo
    public static function getAll()
    {
        try {
            $model = new GapFillQuestion();
            $items = $model->obtenerTodos();
            http_response_code(200);
            echo json_encode($items);
        } catch (Exception $e) {
            self::sendError(500, "Error al obtener los registros de gap_fill_question.", $e);
        }
    }

    // Obtener uno por ID
    public static function getOne($id)
    {
        if (!is_numeric($id)) return self::sendError(400, "El id debe ser numérico.");

        try {
            $model = new GapFillQuestion();
            $item = $model->obtenerPorId($id);

            if ($item) {
                http_response_code(200);
                echo json_encode($item);
            } else {
                self::sendError(404, "Pregunta de gap fill no encontrada.");
            }
        } catch (Exception $e) {
            self::sendError(500, "Error al obtener el registro.", $e);
        }
    }

    // Obtener por id_gap_fill_template
    public static function getByTemplate($id_gap_fill_template)
    {
        if (!is_numeric($id_gap_fill_template)) {
            return self::sendError(400, "El id_gap_fill_template debe ser numérico.");
        }

        try {
            $model = new GapFillQuestion();
            $items = $model->obtenerPorTemplate($id_gap_fill_template);
            http_response_code(200);
            echo json_encode($items);
        } catch (Exception $e) {
            self::sendError(500, "Error al obtener por template.", $e);
        }
    }

    // Crear
    public static function create($data)
    {
        // Validaciones
        if (!isset($data['texto']) || trim($data['texto']) === '') {
            http_response_code(400);
            echo json_encode(["error" => "El campo 'texto' es obligatorio"]);
            return;
        }
        if (!isset($data['counter']) || !is_numeric($data['counter'])) {
            http_response_code(400);
            echo json_encode(["error" => "El campo 'counter' es obligatorio y debe ser numérico"]);
            return;
        }
        if (!isset($data['id_gap_fill_template']) || !is_numeric($data['id_gap_fill_template'])) {
            http_response_code(400);
            echo json_encode(["error" => "El campo 'id_gap_fill_template' es obligatorio y debe ser numérico"]);
            return;
        }

        try {
            $model = new GapFillQuestion();
            $creado = $model->crear([
                'texto' => trim($data['texto']),
                'counter' => (int)$data['counter'],
                'id_gap_fill_template' => (int)$data['id_gap_fill_template'],
            ]);

            if ($creado) {
                http_response_code(201);
                echo json_encode([
                    "message" => "Pregunta de gap fill creada exitosamente.",
                    "gap_fill_question" => [
                        "texto" => trim($data['texto']),
                        "counter" => (int)$data['counter'],
                        "id_gap_fill_template" => (int)$data['id_gap_fill_template'],
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

        if (array_key_exists('texto', $data)) {
            $t = trim((string)$data['texto']);
            if ($t === '') return self::sendError(400, "El campo 'texto' no puede ser vacío.");
            $payload['texto'] = $t;
        }

        if (array_key_exists('counter', $data)) {
            if (!is_numeric($data['counter'])) {
                return self::sendError(400, "El campo 'counter' debe ser numérico.");
            }
            $payload['counter'] = (int)$data['counter'];
        }

        if (array_key_exists('id_gap_fill_template', $data)) {
            if (!is_numeric($data['id_gap_fill_template'])) {
                return self::sendError(400, "El campo 'id_gap_fill_template' debe ser numérico.");
            }
            $payload['id_gap_fill_template'] = (int)$data['id_gap_fill_template'];
        }

        if (empty($payload)) return self::sendError(400, "No hay campos válidos para actualizar.");

        try {
            $model = new GapFillQuestion();
            $existente = $model->obtenerPorId($id);
            if (!$existente) return self::sendError(404, "No se encontró la pregunta con id: $id");

            $actualizado = $model->actualizarPorId($id, $payload);

            if ($actualizado) {
                $nuevo = $model->obtenerPorId($id);
                http_response_code(200);
                echo json_encode([
                    "message" => "Pregunta actualizada exitosamente.",
                    "gap_fill_question" => $nuevo
                ]);
            } else {
                self::sendError(500, "Error interno al intentar actualizar.");
            }
        } catch (Exception $e) {
            self::sendError(500, "Error al actualizar el registro.", $e);
        }
    }

    // Delete (hard delete)
    public static function deleteOne($id)
    {
        if (!is_numeric($id)) return self::sendError(400, "El id debe ser numérico.");

        try {
            $model = new GapFillQuestion();
            $item = $model->obtenerPorId($id);
            if (!$item) return self::sendError(404, "No se encontró la pregunta con id: $id");

            $deleted = $model->eliminarPorId($id);

            if ($deleted) {
                http_response_code(200);
                echo json_encode([
                    "message" => "Pregunta eliminada exitosamente.",
                    "gap_fill_question" => $item
                ]);
            } else {
                self::sendError(500, "Error interno al intentar eliminar.");
            }
        } catch (Exception $e) {
            self::sendError(500, "Error al eliminar el registro.", $e);
        }
    }

    // (Opcional) Vaciar tabla
    public static function truncate()
    {
        try {
            $model = new GapFillQuestion();
            $model->vaciarTabla();
            http_response_code(200);
            echo json_encode(["message" => "Tabla gap_fill_question vaciada exitosamente."]);
        } catch (Exception $e) {
            self::sendError(500, "Error al vaciar la tabla gap_fill_question.", $e);
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