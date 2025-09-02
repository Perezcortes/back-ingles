<?php
require_once __DIR__ . '/../models/Signs.php';

class SignsController
{
    // Listar activos
    public static function getAll()
    {
        try {
            $model = new Signs();
            $items = $model->obtenerTodos();

            http_response_code(200);
            echo json_encode($items);
        } catch (Exception $e) {
            self::sendError(500, "Error al obtener los registros de signs.", $e);
        }
    }

    // Obtener uno por ID
    public static function getOne($id)
    {
        if (!is_numeric($id)) {
            return self::sendError(400, "El id debe ser numérico.");
        }

        try {
            $model = new Signs();
            $item = $model->obtenerPorId($id);

            if ($item) {
                http_response_code(200);
                echo json_encode($item);
            } else {
                self::sendError(404, "Registro de signs no encontrado.");
            }
        } catch (Exception $e) {
            self::sendError(500, "Error al obtener el registro de signs.", $e);
        }
    }

    // Obtener por id_sign_template
    public static function getBySignTemplate($id_sign_template)
    {
        if (!is_numeric($id_sign_template)) {
            return self::sendError(400, "El parámetro 'id_sign_template' debe ser numérico.");
        }

        try {
            $model = new Signs();
            $items = $model->obtenerPorIdSignTemplate($id_sign_template);

            http_response_code(200);
            echo json_encode($items);
        } catch (Exception $e) {
            self::sendError(500, "Error al obtener por id_sign_template.", $e);
        }
    }

    // Crear
    public static function create($data)
    {
        if (!isset($data['id_sign_template']) || !is_numeric($data['id_sign_template'])) {
            http_response_code(400);
            echo json_encode(["error" => "El campo 'id_sign_template' es obligatorio y debe ser numérico"]);
            return;
        }

        if (!isset($data['link_img']) || trim($data['link_img']) === '') {
            http_response_code(400);
            echo json_encode(["error" => "El campo 'link_img' es obligatorio"]);
            return;
        }

        try {
            $model = new Signs();
            $creado = $model->crear([
                'id_sign_template' => (int)$data['id_sign_template'],
                'link_img' => trim($data['link_img']),
            ]);

            if ($creado) {
                http_response_code(201);
                echo json_encode([
                    "message" => "Registro de signs creado exitosamente.",
                    "signs" => [
                        "id_sign_template" => (int)$data['id_sign_template'],
                        "link_img" => trim($data['link_img']),
                    ]
                ]);
            } else {
                self::sendError(500, "Error al crear el registro de signs.");
            }
        } catch (Exception $e) {
            self::sendError(500, "Error al crear el registro de signs.", $e);
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

        // Validaciones de campos si llegan
        $payload = [];

        if (array_key_exists('id_sign_template', $data)) {
            if (!is_numeric($data['id_sign_template'])) {
                return self::sendError(400, "El campo 'id_sign_template' debe ser numérico.");
            }
            $payload['id_sign_template'] = (int)$data['id_sign_template'];
        }

        if (array_key_exists('link_img', $data)) {
            if (trim((string)$data['link_img']) === '') {
                return self::sendError(400, "El campo 'link_img' no puede ser vacío.");
            }
            $payload['link_img'] = trim($data['link_img']);
        }

        if (empty($payload)) {
            return self::sendError(400, "No hay campos válidos para actualizar.");
        }

        try {
            $model = new Signs();
            $existente = $model->obtenerPorId($id);
            if (!$existente) {
                return self::sendError(404, "No se encontró el registro de signs con id: $id");
            }

            $actualizado = $model->actualizarPorId($id, $payload);

            if ($actualizado) {
                $actual = $model->obtenerPorId($id);

                http_response_code(200);
                echo json_encode([
                    "message" => "Registro de signs actualizado exitosamente.",
                    "signs" => $actual
                ]);
            } else {
                self::sendError(500, "Error interno al intentar actualizar.");
            }
        } catch (Exception $e) {
            self::sendError(500, "Error al actualizar el registro de signs.", $e);
        }
    }

    // Soft delete
    public static function deleteOne($id)
    {
        if (!is_numeric($id)) {
            return self::sendError(400, "El id debe ser numérico.");
        }

        try {
            $model = new Signs();

            $item = $model->obtenerPorId($id);
            if (!$item) {
                return self::sendError(404, "No se encontró el registro con id: $id");
            }

            $deleted = $model->eliminarPorId($id);

            if ($deleted) {
                http_response_code(200);
                echo json_encode([
                    "message" => "Registro de signs eliminado exitosamente.",
                    "signs" => $item
                ]);
            } else {
                self::sendError(500, "Error interno al intentar eliminar.");
            }
        } catch (Exception $e) {
            self::sendError(500, "Error al eliminar el registro de signs.", $e);
        }
    }

    // Restore
    public static function restore($id)
    {
        if (!is_numeric($id)) {
            return self::sendError(400, "El id debe ser numérico.");
        }

        try {
            $model = new Signs();
            $restored = $model->restaurarPorId($id);

            if ($restored) {
                http_response_code(200);
                echo json_encode([
                    "message" => "Registro de signs restaurado exitosamente.",
                    "signs_id" => (int)$id
                ]);
            } else {
                self::sendError(500, "Error interno al intentar restaurar.");
            }
        } catch (Exception $e) {
            self::sendError(500, "Error al restaurar el registro de signs.", $e);
        }
    }

    // Hard delete
    public static function deletePermanent($id)
    {
        if (!is_numeric($id)) {
            return self::sendError(400, "El id debe ser numérico.");
        }

        try {
            $model = new Signs();
            $deleted = $model->eliminarPermanentePorId($id);

            if ($deleted) {
                http_response_code(200);
                echo json_encode([
                    "message" => "Registro de signs eliminado permanentemente.",
                    "signs_id" => (int)$id
                ]);
            } else {
                self::sendError(500, "Error interno al intentar eliminar permanentemente.");
            }
        } catch (Exception $e) {
            self::sendError(500, "Error al eliminar permanentemente el registro de signs.", $e);
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
