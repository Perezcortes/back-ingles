<?php
require_once __DIR__ . '/../models/GeneratorOpenClozeQuestionKey.php';

class GeneratorOpenClozeQuestionKeyController
{
    // Listar activos
    public static function getAll()
    {
        try {
            $model = new GeneratorOpenClozeQuestionKey();
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
            $model = new GeneratorOpenClozeQuestionKey();
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

    // Obtener por id_open_cloze_question (activos)
    public static function getByQuestion($id_open_cloze_question)
    {
        if (!is_numeric($id_open_cloze_question)) {
            return self::sendError(400, "El id_open_cloze_question debe ser numérico.");
        }

        try {
            $model = new GeneratorOpenClozeQuestionKey();
            $items = $model->obtenerPorPregunta($id_open_cloze_question);
            http_response_code(200);
            echo json_encode($items);
        } catch (Exception $e) {
            self::sendError(500, "Error al obtener por id_open_cloze_question.", $e);
        }
    }

    // Crear
    public static function create($data)
    {
        if (!isset($data['id_open_cloze_question']) || !is_numeric($data['id_open_cloze_question'])) {
            http_response_code(400);
            echo json_encode(["error" => "El campo 'id_open_cloze_question' es obligatorio y debe ser numérico"]);
            return;
        }

        try {
            $model = new GeneratorOpenClozeQuestionKey();
            $creado = $model->crear([
                'id_open_cloze_question' => (int)$data['id_open_cloze_question'],
            ]);

            if ($creado) {
                http_response_code(201);
                echo json_encode([
                    "message" => "Registro creado exitosamente.",
                    "generator_open_cloze_question_key" => [
                        "id_open_cloze_question" => (int)$data['id_open_cloze_question'],
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

        if (array_key_exists('id_open_cloze_question', $data)) {
            if (!is_numeric($data['id_open_cloze_question'])) {
                return self::sendError(400, "El campo 'id_open_cloze_question' debe ser numérico.");
            }
            $payload['id_open_cloze_question'] = (int)$data['id_open_cloze_question'];
        }

        if (empty($payload)) return self::sendError(400, "No hay campos válidos para actualizar.");

        try {
            $model = new GeneratorOpenClozeQuestionKey();
            $existente = $model->obtenerPorId($id);
            if (!$existente) return self::sendError(404, "No se encontró el registro con id: $id");

            $actualizado = $model->actualizarPorId($id, $payload);

            if ($actualizado) {
                $nuevo = $model->obtenerPorId($id);
                http_response_code(200);
                echo json_encode([
                    "message" => "Registro actualizado exitosamente.",
                    "generator_open_cloze_question_key" => $nuevo
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
            $model = new GeneratorOpenClozeQuestionKey();
            $item = $model->obtenerPorId($id);
            if (!$item) return self::sendError(404, "No se encontró el registro con id: $id");

            $deleted = $model->eliminarPorId($id);
            if ($deleted) {
                http_response_code(200);
                echo json_encode([
                    "message" => "Registro eliminado exitosamente (soft delete).",
                    "generator_open_cloze_question_key" => $item
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
            $model = new GeneratorOpenClozeQuestionKey();
            $restored = $model->restaurarPorId($id);

            if ($restored) {
                http_response_code(200);
                echo json_encode([
                    "message" => "Registro restaurado exitosamente.",
                    "generator_open_cloze_question_key_id" => (int)$id
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
            $model = new GeneratorOpenClozeQuestionKey();
            $deleted = $model->eliminarPermanentePorId($id);

            if ($deleted) {
                http_response_code(200);
                echo json_encode([
                    "message" => "Registro eliminado permanentemente.",
                    "generator_open_cloze_question_key_id" => (int)$id
                ]);
            } else {
                self::sendError(500, "Error interno al intentar eliminar permanentemente.");
            }
        } catch (Exception $e) {
            self::sendError(500, "Error al eliminar permanentemente el registro.", $e);
        }
    }

    //Vaciar tabla
    public static function truncate()
    {
        try {
            $model = new GeneratorOpenClozeQuestionKey();
            $model->vaciarTabla();
            http_response_code(200);
            echo json_encode(["message" => "Tabla generator_open_cloze_question_keys vaciada exitosamente."]);
        } catch (Exception $e) {
            self::sendError(500, "Error al vaciar la tabla.", $e);
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