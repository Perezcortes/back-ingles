<?php
require_once __DIR__ . '/../models/OpenClozeOption.php';

class OpenClozeOptionController
{
    // Listar
    public static function getAll()
    {
        try {
            $model = new OpenClozeOption();
            $items = $model->obtenerTodos();
            http_response_code(200);
            echo json_encode($items);
        } catch (Exception $e) {
            self::sendError(500, "Error al obtener los registros de open_cloze_option.", $e);
        }
    }

    // Obtener uno por ID
    public static function getOne($id)
    {
        if (!is_numeric($id)) return self::sendError(400, "El id debe ser numérico.");

        try {
            $model = new OpenClozeOption();
            $item = $model->obtenerPorId($id);

            if ($item) {
                http_response_code(200);
                echo json_encode($item);
            } else {
                self::sendError(404, "Opción de open cloze no encontrada.");
            }
        } catch (Exception $e) {
            self::sendError(500, "Error al obtener el registro.", $e);
        }
    }

    // Obtener por id_generator_open_cloze_question_keys
    public static function getByGeneratorKey($id_generator_key)
    {
        if (!is_numeric($id_generator_key)) {
            return self::sendError(400, "El id_generator_open_cloze_question_keys debe ser numérico.");
        }

        try {
            $model = new OpenClozeOption();
            $items = $model->obtenerPorKey($id_generator_key);
            http_response_code(200);
            echo json_encode($items);
        } catch (Exception $e) {
            self::sendError(500, "Error al obtener por id_generator_open_cloze_question_keys.", $e);
        }
    }

    // Crear
    public static function create($data)
    {
        if (!isset($data['id_generator_open_cloze_question_keys']) || !is_numeric($data['id_generator_open_cloze_question_keys'])) {
            http_response_code(400);
            echo json_encode(["error" => "El campo 'id_generator_open_cloze_question_keys' es obligatorio y debe ser numérico"]);
            return;
        }
        if (!isset($data['description']) || trim($data['description']) === '') {
            http_response_code(400);
            echo json_encode(["error" => "El campo 'description' es obligatorio"]);
            return;
        }

        try {
            $model = new OpenClozeOption();
            $creado = $model->crear([
                'id_generator_open_cloze_question_keys' => (int)$data['id_generator_open_cloze_question_keys'],
                'description' => trim($data['description']),
            ]);

            if ($creado) {
                http_response_code(201);
                echo json_encode([
                    "message" => "Opción creada exitosamente.",
                    "open_cloze_option" => [
                        "id_generator_open_cloze_question_keys" => (int)$data['id_generator_open_cloze_question_keys'],
                        "description" => trim($data['description']),
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

        if (array_key_exists('id_generator_open_cloze_question_keys', $data)) {
            if (!is_numeric($data['id_generator_open_cloze_question_keys'])) {
                return self::sendError(400, "El campo 'id_generator_open_cloze_question_keys' debe ser numérico.");
            }
            $payload['id_generator_open_cloze_question_keys'] = (int)$data['id_generator_open_cloze_question_keys'];
        }

        if (array_key_exists('description', $data)) {
            $d = trim((string)$data['description']);
            if ($d === '') {
                return self::sendError(400, "El campo 'description' no puede ser vacío.");
            }
            $payload['description'] = $d;
        }

        if (empty($payload)) return self::sendError(400, "No hay campos válidos para actualizar.");

        try {
            $model = new OpenClozeOption();
            $existente = $model->obtenerPorId($id);
            if (!$existente) return self::sendError(404, "No se encontró la opción con id: $id");

            $actualizado = $model->actualizarPorId($id, $payload);

            if ($actualizado) {
                $nuevo = $model->obtenerPorId($id);
                http_response_code(200);
                echo json_encode([
                    "message" => "Opción actualizada exitosamente.",
                    "open_cloze_option" => $nuevo
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
            $model = new OpenClozeOption();
            $item = $model->obtenerPorId($id);
            if (!$item) return self::sendError(404, "No se encontró la opción con id: $id");

            $deleted = $model->eliminarPorId($id);

            if ($deleted) {
                http_response_code(200);
                echo json_encode([
                    "message" => "Opción eliminada exitosamente.",
                    "open_cloze_option" => $item
                ]);
            } else {
                self::sendError(500, "Error interno al intentar eliminar.");
            }
        } catch (Exception $e) {
            self::sendError(500, "Error al eliminar la opción.", $e);
        }
    }

    //Vaciar tabla
    public static function truncate()
    {
        try {
            $model = new OpenClozeOption();
            $model->vaciarTabla();
            http_response_code(200);
            echo json_encode(["message" => "Tabla open_cloze_option vaciada exitosamente."]);
        } catch (Exception $e) {
            self::sendError(500, "Error al vaciar la tabla open_cloze_option.", $e);
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