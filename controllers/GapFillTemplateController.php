<?php
require_once __DIR__ . '/../models/GapFillTemplate.php';

class GapFillTemplateController
{
    // Listar activos
    public static function getAll()
    {
        try {
            $model = new GapFillTemplate();
            $items = $model->obtenerTodos();

            http_response_code(200);
            echo json_encode($items);
        } catch (Exception $e) {
            self::sendError(500, "Error al obtener los registros de gap_fill_template.", $e);
        }
    }

    // Obtener uno por ID
    public static function getOne($id)
    {
        if (!is_numeric($id)) return self::sendError(400, "El id debe ser numérico.");

        try {
            $model = new GapFillTemplate();
            $item = $model->obtenerPorId($id);

            if ($item) {
                http_response_code(200);
                echo json_encode($item);
            } else {
                self::sendError(404, "Plantilla de gap fill no encontrada.");
            }
        } catch (Exception $e) {
            self::sendError(500, "Error al obtener el registro.", $e);
        }
    }

    // Obtener por nivel
    public static function getByLevel($id_level)
    {
        if (!is_numeric($id_level)) {
            return self::sendError(400, "El id_level debe ser numérico.");
        }

        try {
            $model = new GapFillTemplate();
            $items = $model->obtenerPorNivel($id_level);

            http_response_code(200);
            echo json_encode($items);
        } catch (Exception $e) {
            self::sendError(500, "Error al obtener por nivel.", $e);
        }
    }

    // Crear
    public static function create($data)
    {
        $required = ['title','instruction','topic','id_level'];
        foreach ($required as $f) {
            if (!isset($data[$f])) {
                http_response_code(400);
                echo json_encode(["error" => "El campo '$f' es obligatorio"]);
                return;
            }
        }

        $title = trim((string)$data['title']);
        $instruction = trim((string)$data['instruction']);
        $topic = trim((string)$data['topic']);

        if ($title === '' || mb_strlen($title) > 300) {
            http_response_code(400);
            echo json_encode(["error" => "El campo 'title' es obligatorio y máx. 300 caracteres"]);
            return;
        }
        if ($instruction === '') {
            http_response_code(400);
            echo json_encode(["error" => "El campo 'instruction' es obligatorio"]);
            return;
        }
        if ($topic === '' || mb_strlen($topic) > 100) {
            http_response_code(400);
            echo json_encode(["error" => "El campo 'topic' es obligatorio y máx. 100 caracteres"]);
            return;
        }
        if (!is_numeric($data['id_level'])) {
            http_response_code(400);
            echo json_encode(["error" => "El campo 'id_level' debe ser numérico"]);
            return;
        }

        try {
            $model = new GapFillTemplate();
            $creado = $model->crear([
                'title' => $title,
                'instruction' => $instruction,
                'topic' => $topic,
                'id_level' => (int)$data['id_level'],
            ]);

            if ($creado) {
                http_response_code(201);
                echo json_encode([
                    "message" => "Plantilla de gap fill creada exitosamente.",
                    "gap_fill_template" => [
                        "title" => $title,
                        "instruction" => $instruction,
                        "topic" => $topic,
                        "id_level" => (int)$data['id_level'],
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

        if (array_key_exists('title', $data)) {
            $t = trim((string)$data['title']);
            if ($t === '' || mb_strlen($t) > 300) {
                return self::sendError(400, "El campo 'title' no puede ser vacío ni exceder 300 caracteres.");
            }
            $payload['title'] = $t;
        }

        if (array_key_exists('instruction', $data)) {
            $i = trim((string)$data['instruction']);
            if ($i === '') return self::sendError(400, "El campo 'instruction' no puede ser vacío.");
            $payload['instruction'] = $i;
        }

        if (array_key_exists('topic', $data)) {
            $tp = trim((string)$data['topic']);
            if ($tp === '' || mb_strlen($tp) > 100) {
                return self::sendError(400, "El campo 'topic' no puede ser vacío ni exceder 100 caracteres.");
            }
            $payload['topic'] = $tp;
        }

        if (array_key_exists('id_level', $data)) {
            if (!is_numeric($data['id_level'])) {
                return self::sendError(400, "El campo 'id_level' debe ser numérico.");
            }
            $payload['id_level'] = (int)$data['id_level'];
        }

        if (empty($payload)) return self::sendError(400, "No hay campos válidos para actualizar.");

        try {
            $model = new GapFillTemplate();
            $existente = $model->obtenerPorId($id);
            if (!$existente) return self::sendError(404, "No se encontró la plantilla con id: $id");

            $actualizado = $model->actualizarPorId($id, $payload);

            if ($actualizado) {
                $nuevo = $model->obtenerPorId($id);
                http_response_code(200);
                echo json_encode([
                    "message" => "Plantilla de gap fill actualizada exitosamente.",
                    "gap_fill_template" => $nuevo
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
        if (!is_numeric($id)) return self::sendError(400, "El id debe ser numérico.");

        try {
            $model = new GapFillTemplate();
            $item = $model->obtenerPorId($id);
            if (!$item) return self::sendError(404, "No se encontró la plantilla con id: $id");

            $deleted = $model->eliminarPorId($id);

            if ($deleted) {
                http_response_code(200);
                echo json_encode([
                    "message" => "Plantilla eliminada exitosamente (soft delete).",
                    "gap_fill_template" => $item
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
        if (!is_numeric($id)) return self::sendError(400, "El id debe ser numérico.");

        try {
            $model = new GapFillTemplate();
            $restored = $model->restaurarPorId($id);

            if ($restored) {
                http_response_code(200);
                echo json_encode([
                    "message" => "Plantilla restaurada exitosamente.",
                    "gap_fill_template_id" => (int)$id
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
        if (!is_numeric($id)) return self::sendError(400, "El id debe ser numérico.");

        try {
            $model = new GapFillTemplate();
            $deleted = $model->eliminarPermanentePorId($id);

            if ($deleted) {
                http_response_code(200);
                echo json_encode([
                    "message" => "Plantilla eliminada permanentemente.",
                    "gap_fill_template_id" => (int)$id
                ]);
            } else {
                self::sendError(500, "Error interno al intentar eliminar permanentemente.");
            }
        } catch (Exception $e) {
            self::sendError(500, "Error al eliminar permanentemente la plantilla.", $e);
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