<?php
require_once __DIR__ . '/../models/OptionReadingComprehension.php';

class OptionReadingComprehensionController
{
    // Listar
    public static function getAll()
    {
        try {
            $model = new OptionReadingComprehension();
            $items = $model->obtenerTodos();
            http_response_code(200);
            echo json_encode($items);
        } catch (Exception $e) {
            self::sendError(500, "Error al obtener los registros de option_reading_comprehension.", $e);
        }
    }

    // Obtener uno por ID
    public static function getOne($id)
    {
        if (!is_numeric($id)) return self::sendError(400, "El id debe ser numérico.");

        try {
            $model = new OptionReadingComprehension();
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

    // Obtener por id_reading_comprehension
    public static function getByReading($id_reading_comprehension)
    {
        if (!is_numeric($id_reading_comprehension)) {
            return self::sendError(400, "El id_reading_comprehension debe ser numérico.");
        }

        try {
            $model = new OptionReadingComprehension();
            $items = $model->obtenerPorReading($id_reading_comprehension);
            http_response_code(200);
            echo json_encode($items);
        } catch (Exception $e) {
            self::sendError(500, "Error al obtener opciones por id_reading_comprehension.", $e);
        }
    }

    // Crear
    public static function create($data)
    {
        // description requerido + max 300
        if (!isset($data['description']) || trim($data['description']) === '' || mb_strlen($data['description']) > 300) {
            http_response_code(400);
            echo json_encode(["error" => "El campo 'description' es obligatorio y máx. 300 caracteres"]);
            return;
        }
        // id_reading_comprehension requerido numérico
        if (!isset($data['id_reading_comprehension']) || !is_numeric($data['id_reading_comprehension'])) {
            http_response_code(400);
            echo json_encode(["error" => "El campo 'id_reading_comprehension' es obligatorio y debe ser numérico"]);
            return;
        }
        // correct_answer opcional (default false)
        $correct = false;
        if (array_key_exists('correct_answer', $data)) {
            $bool = filter_var($data['correct_answer'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($bool === null) {
                http_response_code(400);
                echo json_encode(["error" => "El campo 'correct_answer' debe ser booleano (true/false)."]);
                return;
            }
            $correct = $bool;
        }

        try {
            $model = new OptionReadingComprehension();
            $creado = $model->crear([
                'description' => trim($data['description']),
                'correct_answer' => $correct,
                'id_reading_comprehension' => (int)$data['id_reading_comprehension'],
            ]);

            if ($creado) {
                http_response_code(201);
                echo json_encode([
                    "message" => "Opción creada exitosamente.",
                    "option_reading_comprehension" => [
                        "description" => trim($data['description']),
                        "correct_answer" => (bool)$correct,
                        "id_reading_comprehension" => (int)$data['id_reading_comprehension'],
                    ]
                ]);
            } else {
                self::sendError(500, "Error al crear la opción.");
            }
        } catch (Exception $e) {
            self::sendError(500, "Error al crear la opción.", $e);
        }
    }

    // Actualizar por ID
    public static function update($id, $data)
    {
        if (!is_numeric($id)) return self::sendError(400, "El id debe ser numérico.");
        if (empty($data)) return self::sendError(400, "Se requiere al menos un campo para actualizar.");

        $payload = [];

        if (array_key_exists('description', $data)) {
            $d = trim((string)$data['description']);
            if ($d === '' || mb_strlen($d) > 300) {
                return self::sendError(400, "El campo 'description' no puede ser vacío ni exceder 300 caracteres.");
            }
            $payload['description'] = $d;
        }

        if (array_key_exists('correct_answer', $data)) {
            $bool = filter_var($data['correct_answer'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($bool === null) {
                return self::sendError(400, "El campo 'correct_answer' debe ser booleano (true/false).");
            }
            $payload['correct_answer'] = $bool;
        }

        if (array_key_exists('id_reading_comprehension', $data)) {
            if (!is_numeric($data['id_reading_comprehension'])) {
                return self::sendError(400, "El campo 'id_reading_comprehension' debe ser numérico.");
            }
            $payload['id_reading_comprehension'] = (int)$data['id_reading_comprehension'];
        }

        if (empty($payload)) return self::sendError(400, "No hay campos válidos para actualizar.");

        try {
            $model = new OptionReadingComprehension();
            $existente = $model->obtenerPorId($id);
            if (!$existente) return self::sendError(404, "No se encontró la opción con id: $id");

            $actualizado = $model->actualizarPorId($id, $payload);

            if ($actualizado) {
                $nuevo = $model->obtenerPorId($id);
                http_response_code(200);
                echo json_encode([
                    "message" => "Opción actualizada exitosamente.",
                    "option_reading_comprehension" => $nuevo
                ]);
            } else {
                self::sendError(500, "Error interno al intentar actualizar.");
            }
        } catch (Exception $e) {
            self::sendError(500, "Error al actualizar la opción.", $e);
        }
    }

    // Delete (hard delete)
    public static function deleteOne($id)
    {
        if (!is_numeric($id)) return self::sendError(400, "El id debe ser numérico.");

        try {
            $model = new OptionReadingComprehension();
            $item = $model->obtenerPorId($id);
            if (!$item) return self::sendError(404, "No se encontró la opción con id: $id");

            $deleted = $model->eliminarPorId($id);

            if ($deleted) {
                http_response_code(200);
                echo json_encode([
                    "message" => "Opción eliminada exitosamente.",
                    "option_reading_comprehension" => $item
                ]);
            } else {
                self::sendError(500, "Error interno al intentar eliminar.");
            }
        } catch (Exception $e) {
            self::sendError(500, "Error al eliminar la opción.", $e);
        }
    }

    // (Opcional) Vaciar tabla
    public static function truncate()
    {
        try {
            $model = new OptionReadingComprehension();
            $model->vaciarTabla();
            http_response_code(200);
            echo json_encode(["message" => "Tabla option_reading_comprehension vaciada exitosamente."]);
        } catch (Exception $e) {
            self::sendError(500, "Error al vaciar la tabla option_reading_comprehension.", $e);
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