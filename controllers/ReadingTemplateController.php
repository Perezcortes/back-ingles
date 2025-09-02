<?php
require_once __DIR__ . '/../models/ReadingTemplate.php';

class ReadingTemplateController
{
    // Get all
    public static function getAll()
    {
        try {
            $model = new ReadingTemplate();
            $items = $model->obtenerTodos();

            http_response_code(200);
            echo json_encode($items);
        } catch (Exception $e) {
            self::sendError(500, "Error al obtener los registros.", $e);
        }
    }

    // Get one by ID
    public static function getOne($id)
    {
        if (!is_numeric($id)) {
            return self::sendError(400, "El id debe ser numérico.");
        }

        try {
            $model = new ReadingTemplate();
            $item = $model->obtenerPorId($id);

            if ($item) {
                http_response_code(200);
                echo json_encode($item);
            } else {
                self::sendError(404, "Plantilla de reading no encontrada.");
            }
        } catch (Exception $e) {
            self::sendError(500, "Error al obtener el registro.", $e);
        }
    }

    // Get by level
    public static function getByLevel($id_level)
    {
        if (!is_numeric($id_level)) {
            return self::sendError(400, "El id_level debe ser numérico.");
        }

        try {
            $model = new ReadingTemplate();
            $items = $model->obtenerPorNivel($id_level);

            http_response_code(200);
            echo json_encode($items);
        } catch (Exception $e) {
            self::sendError(500, "Error al obtener por nivel.", $e);
        }
    }

    // Create
    public static function create($data)
    {
        $required = [
            'who_created','id_level','title','description','id_exam_type',
            'id_signs_template','id_simple_multiple_template','id_multiple_matching_template',
            'id_reading_comprehension_template','id_open_cloze_template',
            'id_template_multiple_choice_cloze','id_gap_fill_template'
        ];

        foreach ($required as $field) {
            if (!isset($data[$field])) {
                http_response_code(400);
                echo json_encode(["error" => "El campo '$field' es obligatorio"]);
                return;
            }
        }

        try {
            $model = new ReadingTemplate();
            $creado = $model->crear($data);

            if ($creado) {
                http_response_code(201);
                echo json_encode([
                    "message" => "Plantilla de reading creada exitosamente.",
                    "reading_template" => $data
                ]);
            } else {
                self::sendError(500, "Error al crear un registro.");
            }
        } catch (Exception $e) {
            self::sendError(500, "Error al crear un registro.", $e);
        }
    }

    // Update
    public static function update($id, $data)
    {
        if (!is_numeric($id)) {
            return self::sendError(400, "El id debe ser numérico.");
        }

        if (empty($data)) {
            return self::sendError(400, "Se requiere al menos un campo para actualizar.");
        }

        try {
            $model = new ReadingTemplate();
            $existente = $model->obtenerPorId($id);

            if (!$existente) {
                return self::sendError(404, "No se encontró la plantilla con id: $id");
            }

            $actualizado = $model->actualizarPorId($id, $data);

            if ($actualizado) {
                $nuevo = $model->obtenerPorId($id);
                http_response_code(200);
                echo json_encode([
                    "message" => "Plantilla de reading actualizada exitosamente.",
                    "reading_template" => $nuevo
                ]);
            } else {
                self::sendError(500, "Error interno al intentar actualizar.");
            }
        } catch (Exception $e) {
            self::sendError(500, "Error al actualizar la plantilla.", $e);
        }
    }

    // Soft delete
    public static function deleteOne($id)
    {
        if (!is_numeric($id)) {
            return self::sendError(400, "El id debe ser numérico.");
        }

        try {
            $model = new ReadingTemplate();
            $item = $model->obtenerPorId($id);

            if (!$item) {
                return self::sendError(404, "No se encontró la plantilla con id: $id");
            }

            $deleted = $model->eliminarPorId($id);

            if ($deleted) {
                http_response_code(200);
                echo json_encode([
                    "message" => "Plantilla de reading eliminada exitosamente.",
                    "reading_template" => $item
                ]);
            } else {
                self::sendError(500, "Error interno al intentar eliminar.");
            }
        } catch (Exception $e) {
            self::sendError(500, "Error al eliminar la plantilla.", $e);
        }
    }

    // Restore
    public static function restore($id)
    {
        if (!is_numeric($id)) {
            return self::sendError(400, "El id debe ser numérico.");
        }

        try {
            $model = new ReadingTemplate();
            $restored = $model->restaurarPorId($id);

            if ($restored) {
                http_response_code(200);
                echo json_encode([
                    "message" => "Plantilla restaurada exitosamente.",
                    "reading_template_id" => (int)$id
                ]);
            } else {
                self::sendError(500, "Error interno al intentar restaurar.");
            }
        } catch (Exception $e) {
            self::sendError(500, "Error al restaurar la plantilla.", $e);
        }
    }

    // Hard delete
    public static function deletePermanent($id)
    {
        if (!is_numeric($id)) {
            return self::sendError(400, "El id debe ser numérico.");
        }

        try {
            $model = new ReadingTemplate();
            $deleted = $model->eliminarPermanentePorId($id);

            if ($deleted) {
                http_response_code(200);
                echo json_encode([
                    "message" => "Plantilla eliminada permanentemente.",
                    "reading_template_id" => (int)$id
                ]);
            } else {
                self::sendError(500, "Error interno al intentar eliminar permanentemente.");
            }
        } catch (Exception $e) {
            self::sendError(500, "Error al eliminar permanentemente.", $e);
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
