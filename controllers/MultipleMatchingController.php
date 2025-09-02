<?php
require_once __DIR__ . '/../models/MultipleMatching.php';

class MultipleMatchingController
{
    // Listar todo
    public static function getAll()
    {
        try {
            $model = new MultipleMatching();
            $items = $model->obtenerTodos();

            http_response_code(200);
            echo json_encode($items);
        } catch (Exception $e) {
            self::sendError(500, "Error al obtener los registros de multiple_matching.", $e);
        }
    }

    // Obtener uno por ID
    public static function getOne($id)
    {
        if (!is_numeric($id)) return self::sendError(400, "El id debe ser numérico.");

        try {
            $model = new MultipleMatching();
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

    // Obtener por id_multiple_matching_template
    public static function getByTemplate($id_multiple_matching_template)
    {
        if (!is_numeric($id_multiple_matching_template)) {
            return self::sendError(400, "El id_multiple_matching_template debe ser numérico.");
        }

        try {
            $model = new MultipleMatching();
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
        // description requerido
        if (!isset($data['description']) || trim($data['description']) === '') {
            http_response_code(400);
            echo json_encode(["error" => "El campo 'description' es obligatorio"]);
            return;
        }
        // id_multiple_matching_template requerido numérico
        if (!isset($data['id_multiple_matching_template']) || !is_numeric($data['id_multiple_matching_template'])) {
            http_response_code(400);
            echo json_encode(["error" => "El campo 'id_multiple_matching_template' es obligatorio y debe ser numérico"]);
            return;
        }
        // id_correct_answer opcional, puede ser null o numérico
        $idCorrect = null;
        if (array_key_exists('id_correct_answer', $data)) {
            if ($data['id_correct_answer'] === null || $data['id_correct_answer'] === 'null') {
                $idCorrect = null;
            } elseif (is_numeric($data['id_correct_answer'])) {
                $idCorrect = (int)$data['id_correct_answer'];
            } else {
                http_response_code(400);
                echo json_encode(["error" => "El campo 'id_correct_answer' debe ser numérico o null"]);
                return;
            }
        }

        try {
            $model = new MultipleMatching();
            $creado = $model->crear([
                'description' => trim($data['description']),
                'id_correct_answer' => $idCorrect,
                'id_multiple_matching_template' => (int)$data['id_multiple_matching_template'],
            ]);

            if ($creado) {
                http_response_code(201);
                echo json_encode([
                    "message" => "Registro multiple_matching creado exitosamente.",
                    "multiple_matching" => [
                        "description" => trim($data['description']),
                        "id_correct_answer" => $idCorrect,
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

        if (array_key_exists('description', $data)) {
            if (trim((string)$data['description']) === '') {
                return self::sendError(400, "El campo 'description' no puede ser vacío.");
            }
            $payload['description'] = trim($data['description']);
        }

        if (array_key_exists('id_correct_answer', $data)) {
            if ($data['id_correct_answer'] === null || $data['id_correct_answer'] === 'null') {
                $payload['id_correct_answer'] = null; // limpiar FK
            } elseif (is_numeric($data['id_correct_answer'])) {
                $payload['id_correct_answer'] = (int)$data['id_correct_answer'];
            } else {
                return self::sendError(400, "El campo 'id_correct_answer' debe ser numérico o null.");
            }
        }

        if (array_key_exists('id_multiple_matching_template', $data)) {
            if (!is_numeric($data['id_multiple_matching_template'])) {
                return self::sendError(400, "El campo 'id_multiple_matching_template' debe ser numérico.");
            }
            $payload['id_multiple_matching_template'] = (int)$data['id_multiple_matching_template'];
        }

        if (empty($payload)) return self::sendError(400, "No hay campos válidos para actualizar.");

        try {
            $model = new MultipleMatching();
            $existente = $model->obtenerPorId($id);
            if (!$existente) return self::sendError(404, "No se encontró el registro con id: $id");

            $actualizado = $model->actualizarPorId($id, $payload);

            if ($actualizado) {
                $nuevo = $model->obtenerPorId($id);
                http_response_code(200);
                echo json_encode([
                    "message" => "Registro multiple_matching actualizado exitosamente.",
                    "multiple_matching" => $nuevo
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
            $model = new MultipleMatching();
            $item = $model->obtenerPorId($id);
            if (!$item) return self::sendError(404, "No se encontró el registro con id: $id");

            $deleted = $model->eliminarPorId($id);

            if ($deleted) {
                http_response_code(200);
                echo json_encode([
                    "message" => "Registro multiple_matching eliminado exitosamente.",
                    "multiple_matching" => $item
                ]);
            } else {
                self::sendError(500, "Error interno al intentar eliminar.");
            }
        } catch (Exception $e) {
            self::sendError(500, "Error al eliminar el registro.", $e);
        }
    }

    // (Opcional) TRUNCATE
    public static function truncate()
    {
        try {
            $model = new MultipleMatching();
            $model->vaciarTabla();

            http_response_code(200);
            echo json_encode(["message" => "Tabla multiple_matching vaciada exitosamente."]);
        } catch (Exception $e) {
            self::sendError(500, "Error al vaciar la tabla multiple_matching.", $e);
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
