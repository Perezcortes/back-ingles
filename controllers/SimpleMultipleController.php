<?php
require_once __DIR__ . '/../models/SimpleMultiple.php';

class SimpleMultipleController
{
    // Listar activos
    public static function getAll()
    {
        try {
            $model = new SimpleMultiple();
            $items = $model->obtenerTodos();

            http_response_code(200);
            echo json_encode($items);
        } catch (Exception $e) {
            self::sendError(500, "Error al obtener los registros de simple_multiple.", $e);
        }
    }

    // Obtener uno por ID
    public static function getOne($id)
    {
        if (!is_numeric($id)) {
            return self::sendError(400, "El id debe ser numérico.");
        }

        try {
            $model = new SimpleMultiple();
            $item = $model->obtenerPorId($id);

            if ($item) {
                http_response_code(200);
                echo json_encode($item);
            } else {
                self::sendError(404, "Registro simple_multiple no encontrado.");
            }
        } catch (Exception $e) {
            self::sendError(500, "Error al obtener el registro.", $e);
        }
    }

    // Obtener por id_simple_multiple_template
    public static function getByTemplate($id_simple_multiple_template)
    {
        if (!is_numeric($id_simple_multiple_template)) {
            return self::sendError(400, "El id_simple_multiple_template debe ser numérico.");
        }

        try {
            $model = new SimpleMultiple();
            $items = $model->obtenerPorIdTemplate($id_simple_multiple_template);

            http_response_code(200);
            echo json_encode($items);
        } catch (Exception $e) {
            self::sendError(500, "Error al obtener por id_simple_multiple_template.", $e);
        }
    }

    // Crear
    public static function create($data)
    {
        if (!isset($data['id_simple_multiple_template']) || !is_numeric($data['id_simple_multiple_template'])) {
            http_response_code(400);
            echo json_encode(["error" => "El campo 'id_simple_multiple_template' es obligatorio y debe ser numérico"]);
            return;
        }
        if (!isset($data['sentence']) || trim($data['sentence']) === '') {
            http_response_code(400);
            echo json_encode(["error" => "El campo 'sentence' es obligatorio"]);
            return;
        }

        try {
            $model = new SimpleMultiple();
            $creado = $model->crear([
                'id_simple_multiple_template' => (int)$data['id_simple_multiple_template'],
                'sentence' => trim($data['sentence']),
            ]);

            if ($creado) {
                http_response_code(201);
                echo json_encode([
                    "message" => "Registro simple_multiple creado exitosamente.",
                    "simple_multiple" => [
                        "id_simple_multiple_template" => (int)$data['id_simple_multiple_template'],
                        "sentence" => trim($data['sentence']),
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
        if (!is_numeric($id)) {
            return self::sendError(400, "El id debe ser numérico.");
        }
        if (empty($data)) {
            return self::sendError(400, "Se requiere al menos un campo para actualizar.");
        }

        $payload = [];

        if (array_key_exists('id_simple_multiple_template', $data)) {
            if (!is_numeric($data['id_simple_multiple_template'])) {
                return self::sendError(400, "El campo 'id_simple_multiple_template' debe ser numérico.");
            }
            $payload['id_simple_multiple_template'] = (int)$data['id_simple_multiple_template'];
        }

        if (array_key_exists('sentence', $data)) {
            if (trim((string)$data['sentence']) === '') {
                return self::sendError(400, "El campo 'sentence' no puede ser vacío.");
            }
            $payload['sentence'] = trim($data['sentence']);
        }

        if (empty($payload)) {
            return self::sendError(400, "No hay campos válidos para actualizar.");
        }

        try {
            $model = new SimpleMultiple();
            $existente = $model->obtenerPorId($id);
            if (!$existente) {
                return self::sendError(404, "No se encontró el registro con id: $id");
            }

            $actualizado = $model->actualizarPorId($id, $payload);

            if ($actualizado) {
                $actual = $model->obtenerPorId($id);

                http_response_code(200);
                echo json_encode([
                    "message" => "Registro simple_multiple actualizado exitosamente.",
                    "simple_multiple" => $actual
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
        if (!is_numeric($id)) {
            return self::sendError(400, "El id debe ser numérico.");
        }

        try {
            $model = new SimpleMultiple();

            $item = $model->obtenerPorId($id);
            if (!$item) {
                return self::sendError(404, "No se encontró el registro con id: $id");
            }

            $deleted = $model->eliminarPorId($id);

            if ($deleted) {
                http_response_code(200);
                echo json_encode([
                    "message" => "Registro simple_multiple eliminado exitosamente.",
                    "simple_multiple" => $item
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
        if (!is_numeric($id)) {
            return self::sendError(400, "El id debe ser numérico.");
        }

        try {
            $model = new SimpleMultiple();
            $restored = $model->restaurarPorId($id);

            if ($restored) {
                http_response_code(200);
                echo json_encode([
                    "message" => "Registro restaurado exitosamente.",
                    "simple_multiple_id" => (int)$id
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
        if (!is_numeric($id)) {
            return self::sendError(400, "El id debe ser numérico.");
        }

        try {
            $model = new SimpleMultiple();
            $deleted = $model->eliminarPermanentePorId($id);

            if ($deleted) {
                http_response_code(200);
                echo json_encode([
                    "message" => "Registro eliminado permanentemente.",
                    "simple_multiple_id" => (int)$id
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
            $response["details"] = $exception->getMessage();
        }

        echo json_encode($response);
    }
}
