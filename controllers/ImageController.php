<?php
require_once __DIR__ . '/../models/Image.php';

class ImageController
{
    // Get all images
    public static function getAll()
    {
        try {
            $model = new Image();
            $items = $model->obtenerTodos();

            http_response_code(200);
            echo json_encode($items);
        } catch (Exception $e) {
            self::sendError(500, "Error al obtener los registros de la tabla image.", $e);
        }
    }

    // Get one by ID
    public static function getOne($id)
    {
        if (!is_numeric($id)) {
            return self::sendError(400, "El id debe ser numérico.");
        }

        try {
            $model = new Image();
            $item = $model->obtenerPorId($id);

            if ($item) {
                http_response_code(200);
                echo json_encode($item);
            } else {
                self::sendError(404, "Imagen no encontrada.");
            }
        } catch (Exception $e) {
            self::sendError(500, "Error al obtener el registro.", $e);
        }
    }

    // Get by link_image
    public static function getByLink($link_image)
    {
        if (!isset($link_image) || trim($link_image) === '') {
            return self::sendError(400, "El parámetro 'link_image' es obligatorio.");
        }

        try {
            $model = new Image();
            $items = $model->obtenerPorLink($link_image);

            http_response_code(200);
            echo json_encode($items);
        } catch (Exception $e) {
            self::sendError(500, "Error al buscar por link_image.", $e);
        }
    }

    // Get by id_writing_template
    public static function getByWritingTemplate($id_writing_template)
    {
        if (!is_numeric($id_writing_template)) {
            return self::sendError(400, "El id_writing_template debe ser numérico.");
        }

        try {
            $model = new Image();
            $items = $model->obtenerPorIdWritingTemplate($id_writing_template);

            http_response_code(200);
            echo json_encode($items);
        } catch (Exception $e) {
            self::sendError(500, "Error al buscar por id_writing_template.", $e);
        }
    }

    // Create
    public static function create($data)
    {
        // Validaciones mínimas
        if (!isset($data['link_image']) || trim($data['link_image']) === '') {
            http_response_code(400);
            echo json_encode(["error" => "El campo 'link_image' es obligatorio"]);
            return;
        }

        if (!isset($data['id_writing_template']) || !is_numeric($data['id_writing_template'])) {
            http_response_code(400);
            echo json_encode(["error" => "El campo 'id_writing_template' es obligatorio y debe ser numérico"]);
            return;
        }

        try {
            $model = new Image();
            $creado = $model->crear([
                'link_image' => trim($data['link_image']),
                'id_writing_template' => (int)$data['id_writing_template'],
            ]);

            if ($creado) {
                http_response_code(201);
                echo json_encode([
                    "message" => "Imagen creada exitosamente.",
                    "image" => [
                        "link_image" => trim($data['link_image']),
                        "id_writing_template" => (int)$data['id_writing_template'],
                    ]
                ]);
            } else {
                self::sendError(500, "Error al crear el registro.");
            }
        } catch (Exception $e) {
            self::sendError(500, "Error al crear el registro.", $e);
        }
    }

    // Update by ID
    public static function update($id, $data)
    {
        if (!is_numeric($id)) {
            return self::sendError(400, "El id debe ser numérico.");
        }

        if (empty($data)) {
            return self::sendError(400, "Se requiere al menos un campo para actualizar la imagen.");
        }

        $payload = [];

        if (array_key_exists('link_image', $data)) {
            if (trim((string)$data['link_image']) === '') {
                return self::sendError(400, "El campo 'link_image' no puede ser vacío.");
            }
            $payload['link_image'] = trim($data['link_image']);
        }

        if (array_key_exists('id_writing_template', $data)) {
            if (!is_numeric($data['id_writing_template'])) {
                return self::sendError(400, "El campo 'id_writing_template' debe ser numérico.");
            }
            $payload['id_writing_template'] = (int)$data['id_writing_template'];
        }

        if (empty($payload)) {
            return self::sendError(400, "No hay campos válidos para actualizar.");
        }

        try {
            $model = new Image();
            $existente = $model->obtenerPorId($id);
            if (!$existente) {
                return self::sendError(404, "No se encontró la imagen con id: $id");
            }

            $actualizado = $model->actualizarPorId($id, $payload);

            if ($actualizado) {
                $actual = $model->obtenerPorId($id);

                http_response_code(200);
                echo json_encode([
                    "message" => "Imagen actualizada exitosamente.",
                    "image" => $actual
                ]);
            } else {
                self::sendError(500, "Error interno al intentar actualizar la imagen.");
            }
        } catch (Exception $e) {
            self::sendError(500, "Error al actualizar la imagen.", $e);
        }
    }

    // Delete (hard delete)
    public static function deleteOne($id)
    {
        if (!is_numeric($id)) {
            return self::sendError(400, "El id debe ser numérico.");
        }

        try {
            $model = new Image();
            $item = $model->obtenerPorId($id);
            if (!$item) {
                return self::sendError(404, "No se encontró la imagen con id: $id");
            }

            $eliminado = $model->eliminarPorId($id);

            if ($eliminado) {
                http_response_code(200);
                echo json_encode([
                    "message" => "Imagen eliminada exitosamente.",
                    "image" => $item
                ]);
            } else {
                self::sendError(500, "Error interno al intentar eliminar la imagen.");
            }
        } catch (Exception $e) {
            self::sendError(500, "Error al eliminar la imagen.", $e);
        }
    }

    // (Opcional) Vaciar tabla
    public static function truncate()
    {
        try {
            $model = new Image();
            $model->vaciarTabla();

            http_response_code(200);
            echo json_encode(["message" => "Tabla image vaciada exitosamente."]);
        } catch (Exception $e) {
            self::sendError(500, "Error al vaciar la tabla image.", $e);
        }
    }

    // Helper para errores
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
