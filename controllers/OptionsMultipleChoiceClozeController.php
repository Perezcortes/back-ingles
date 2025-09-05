<?php
require_once __DIR__ . '/../models/OptionsMultipleChoiceCloze.php';

class OptionsMultipleChoiceClozeController
{
    // Listar
    public static function getAll()
    {
        try {
            $model = new OptionsMultipleChoiceCloze();
            $items = $model->obtenerTodos();
            http_response_code(200);
            echo json_encode($items);
        } catch (Exception $e) {
            self::sendError(500, "Error al obtener los registros de options_multiple_choice_cloze.", $e);
        }
    }

    // Obtener uno por ID
    public static function getOne($id)
    {
        if (!is_numeric($id)) return self::sendError(400, "El id debe ser numérico.");
        try {
            $model = new OptionsMultipleChoiceCloze();
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

    // Obtener por id_question_number
    public static function getByQuestionNumber($id_question_number)
    {
        if (!is_numeric($id_question_number)) {
            return self::sendError(400, "El id_question_number debe ser numérico.");
        }
        try {
            $model = new OptionsMultipleChoiceCloze();
            $items = $model->obtenerPorQuestionNumber($id_question_number);
            http_response_code(200);
            echo json_encode($items);
        } catch (Exception $e) {
            self::sendError(500, "Error al obtener por id_question_number.", $e);
        }
    }

    // Crear
    public static function create($data)
    {
        if (!isset($data['description']) || trim($data['description']) === '') {
            http_response_code(400);
            echo json_encode(["error" => "El campo 'description' es obligatorio"]);
            return;
        }
        if (!isset($data['id_question_number']) || !is_numeric($data['id_question_number'])) {
            http_response_code(400);
            echo json_encode(["error" => "El campo 'id_question_number' es obligatorio y debe ser numérico"]);
            return;
        }

        // correct_answer es opcional; por defecto FALSE si no se envía
        $correct = 0;
        if (array_key_exists('correct_answer', $data)) {
            $norm = self::normalizeBool($data['correct_answer']);
            if ($norm === null) {
                http_response_code(400);
                echo json_encode(["error" => "El campo 'correct_answer' debe ser booleano (true/false, 1/0)"]);
                return;
            }
            $correct = $norm ? 1 : 0;
        }

        try {
            $model = new OptionsMultipleChoiceCloze();
            $creado = $model->crear([
                'description' => trim($data['description']),
                'id_question_number' => (int)$data['id_question_number'],
                'correct_answer' => $correct
            ]);

            if ($creado) {
                http_response_code(201);
                echo json_encode([
                    "message" => "Opción creada exitosamente.",
                    "options_multiple_choice_cloze" => [
                        "description" => trim($data['description']),
                        "id_question_number" => (int)$data['id_question_number'],
                        "correct_answer" => (bool)$correct
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

        if (array_key_exists('description', $data)) {
            $d = trim((string)$data['description']);
            if ($d === '') return self::sendError(400, "El campo 'description' no puede ser vacío.");
            $payload['description'] = $d;
        }

        if (array_key_exists('id_question_number', $data)) {
            if (!is_numeric($data['id_question_number'])) {
                return self::sendError(400, "El campo 'id_question_number' debe ser numérico.");
            }
            $payload['id_question_number'] = (int)$data['id_question_number'];
        }

        if (array_key_exists('correct_answer', $data)) {
            $norm = self::normalizeBool($data['correct_answer']);
            if ($norm === null) {
                return self::sendError(400, "El campo 'correct_answer' debe ser booleano (true/false, 1/0).");
            }
            $payload['correct_answer'] = $norm ? 1 : 0;
        }

        if (empty($payload)) return self::sendError(400, "No hay campos válidos para actualizar.");

        try {
            $model = new OptionsMultipleChoiceCloze();
            $existente = $model->obtenerPorId($id);
            if (!$existente) return self::sendError(404, "No se encontró la opción con id: $id");

            $actualizado = $model->actualizarPorId($id, $payload);
            if ($actualizado) {
                $nuevo = $model->obtenerPorId($id);
                http_response_code(200);
                echo json_encode([
                    "message" => "Opción actualizada exitosamente.",
                    "options_multiple_choice_cloze" => $nuevo
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
            $model = new OptionsMultipleChoiceCloze();
            $item = $model->obtenerPorId($id);
            if (!$item) return self::sendError(404, "No se encontró la opción con id: $id");

            $deleted = $model->eliminarPorId($id);
            if ($deleted) {
                http_response_code(200);
                echo json_encode([
                    "message" => "Opción eliminada exitosamente.",
                    "options_multiple_choice_cloze" => $item
                ]);
            } else {
                self::sendError(500, "Error interno al intentar eliminar.");
            }
        } catch (Exception $e) {
            self::sendError(500, "Error al eliminar el registro.", $e);
        }
    }

    //Vaciar tabla
    public static function truncate()
    {
        try {
            $model = new OptionsMultipleChoiceCloze();
            $model->vaciarTabla();
            http_response_code(200);
            echo json_encode(["message" => "Tabla options_multiple_choice_cloze vaciada exitosamente."]);
        } catch (Exception $e) {
            self::sendError(500, "Error al vaciar la tabla options_multiple_choice_cloze.", $e);
        }
    }

    // Helper de error
    private static function sendError($code, $message, $exception = null)
    {
        http_response_code($code);
        $response = ["error" => $message];
        if (getenv('APP_ENV') === 'development' && $exception) {
            $response["details"] = $exception->getMessage();
        }
        echo json_encode($response);
    }

    // Normalizar booleano desde true/false, 1/0, "true"/"false", "1"/"0"
    private static function normalizeBool($value) : ?bool {
        if (is_bool($value)) return $value;
        if (is_int($value)) return $value === 1 ? true : ($value === 0 ? false : null);
        if (is_string($value)) {
            $v = strtolower(trim($value));
            if ($v === 'true' || $v === '1') return true;
            if ($v === 'false' || $v === '0') return false;
        }
        return null;
    }
}