<?php
require_once __DIR__ . '/../models/SignOptions.php';

class SignOptionsController
{
    // Listar
    public static function getAll()
    {
        try {
            $model = new SignOptions();
            $items = $model->obtenerTodos();

            http_response_code(200);
            echo json_encode($items);
        } catch (Exception $e) {
            self::sendError(500, "Error al obtener los registros de sign_options.", $e);
        }
    }

    // Obtener por ID
    public static function getOne($id)
    {
        if (!is_numeric($id)) {
            return self::sendError(400, "El id debe ser numérico.");
        }

        try {
            $model = new SignOptions();
            $item = $model->obtenerPorId($id);

            if ($item) {
                http_response_code(200);
                echo json_encode($item);
            } else {
                self::sendError(404, "Opción no encontrada.");
            }
        } catch (Exception $e) {
            self::sendError(500, "Error al obtener el registro de sign_options.", $e);
        }
    }

    // Obtener por id_sign
    public static function getBySign($id_sign)
    {
        if (!is_numeric($id_sign)) {
            return self::sendError(400, "El parámetro 'id_sign' debe ser numérico.");
        }

        try {
            $model = new SignOptions();
            $items = $model->obtenerPorIdSign($id_sign);

            http_response_code(200);
            echo json_encode($items);
        } catch (Exception $e) {
            self::sendError(500, "Error al obtener opciones por id_sign.", $e);
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
        if (!isset($data['id_sign']) || !is_numeric($data['id_sign'])) {
            http_response_code(400);
            echo json_encode(["error" => "El campo 'id_sign' es obligatorio y debe ser numérico"]);
            return;
        }
        if (!isset($data['correct_answer'])) {
            http_response_code(400);
            echo json_encode(["error" => "El campo 'correct_answer' es obligatorio"]);
            return;
        }

        // Normalizar boolean (aceptar true/false, 'true'/'false', 1/0)
        $correct = filter_var($data['correct_answer'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        if ($correct === null) {
            http_response_code(400);
            echo json_encode(["error" => "El campo 'correct_answer' debe ser booleano (true/false)."]);
            return;
        }

        try {
            $model = new SignOptions();
            $creado = $model->crear([
                'description' => trim($data['description']),
                'id_sign' => (int)$data['id_sign'],
                'correct_answer' => $correct,
            ]);

            if ($creado) {
                http_response_code(201);
                echo json_encode([
                    "message" => "Opción creada exitosamente.",
                    "sign_option" => [
                        "description" => trim($data['description']),
                        "id_sign" => (int)$data['id_sign'],
                        "correct_answer" => (bool)$correct,
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
        if (!is_numeric($id)) {
            return self::sendError(400, "El id debe ser numérico.");
        }
        if (empty($data)) {
            return self::sendError(400, "Se requiere al menos un campo para actualizar.");
        }

        $payload = [];

        if (array_key_exists('description', $data)) {
            if (trim((string)$data['description']) === '') {
                return self::sendError(400, "El campo 'description' no puede ser vacío.");
            }
            $payload['description'] = trim($data['description']);
        }

        if (array_key_exists('id_sign', $data)) {
            if (!is_numeric($data['id_sign'])) {
                return self::sendError(400, "El campo 'id_sign' debe ser numérico.");
            }
            $payload['id_sign'] = (int)$data['id_sign'];
        }

        if (array_key_exists('correct_answer', $data)) {
            $correct = filter_var($data['correct_answer'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($correct === null) {
                return self::sendError(400, "El campo 'correct_answer' debe ser booleano (true/false).");
            }
            $payload['correct_answer'] = $correct;
        }

        if (empty($payload)) {
            return self::sendError(400, "No hay campos válidos para actualizar.");
        }

        try {
            $model = new SignOptions();
            $existente = $model->obtenerPorId($id);
            if (!$existente) {
                return self::sendError(404, "No se encontró la opción con id: $id");
            }

            $actualizado = $model->actualizarPorId($id, $payload);

            if ($actualizado) {
                $actual = $model->obtenerPorId($id);

                http_response_code(200);
                echo json_encode([
                    "message" => "Opción actualizada exitosamente.",
                    "sign_option" => $actual
                ]);
            } else {
                self::sendError(500, "Error interno al intentar actualizar la opción.");
            }
        } catch (Exception $e) {
            self::sendError(500, "Error al actualizar la opción.", $e);
        }
    }

    // Delete (hard delete)
    public static function deleteOne($id)
    {
        if (!is_numeric($id)) {
            return self::sendError(400, "El id debe ser numérico.");
        }

        try {
            $model = new SignOptions();
            $item = $model->obtenerPorId($id);
            if (!$item) {
                return self::sendError(404, "No se encontró la opción con id: $id");
            }

            $deleted = $model->eliminarPorId($id);

            if ($deleted) {
                http_response_code(200);
                echo json_encode([
                    "message" => "Opción eliminada exitosamente.",
                    "sign_option" => $item
                ]);
            } else {
                self::sendError(500, "Error interno al intentar eliminar la opción.");
            }
        } catch (Exception $e) {
            self::sendError(500, "Error al eliminar la opción.", $e);
        }
    }

    // (Opcional) Vaciar tabla
    public static function truncate()
    {
        try {
            $model = new SignOptions();
            $model->vaciarTabla();

            http_response_code(200);
            echo json_encode(["message" => "Tabla sign_options vaciada exitosamente."]);
        } catch (Exception $e) {
            self::sendError(500, "Error al vaciar la tabla sign_options.", $e);
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
