<?php
require_once __DIR__ . '/../models/ExamType.php';

class ExamTypeController
{
    // Get all exam types (no eliminados)
    public static function getAll()
    {
        try {
            $model = new ExamType();
            $items = $model->obtenerTodos();

            http_response_code(200);
            echo json_encode($items);
        } catch (Exception $e) {
            self::sendError(500, "Error al obtener los registros de la tabla.", $e);
        }
    }

    // Get one by ID
    public static function getOne($id)
    {
        if (!is_numeric($id)) {
            return self::sendError(400, "El id debe ser numérico.");
        }

        try {
            $model = new ExamType();
            $item = $model->obtenerPorId($id);

            if ($item) {
                http_response_code(200);
                echo json_encode($item);
            } else {
                self::sendError(404, "Tipo de examen no encontrado");
            }
        } catch (Exception $e) {
            self::sendError(500, "Error al obtener el registro.", $e);
        }
    }

    // Create
    public static function create($data)
    {
        if (!isset($data['exam_name']) || trim($data['exam_name']) === '') {
            http_response_code(400);
            echo json_encode(["error" => "El campo 'exam_name' es obligatorio"]);
            return;
        }

        try {
            $model = new ExamType();
            $creado = $model->crear([
                'exam_name' => trim($data['exam_name']),
            ]);

            if ($creado) {
                http_response_code(201);
                echo json_encode([
                    "message" => "Tipo de examen creado exitosamente.",
                    "exam_type" => ["exam_name" => trim($data['exam_name'])]
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
            return self::sendError(400, "Se requiere al menos un campo para actualizar el tipo de examen.");
        }

        // Solo permitimos actualizar exam_name si viene
        $payload = [];
        if (array_key_exists('exam_name', $data)) {
            if (trim((string)$data['exam_name']) === '') {
                return self::sendError(400, "El campo 'exam_name' no puede ser vacío.");
            }
            $payload['exam_name'] = trim($data['exam_name']);
        }

        if (empty($payload)) {
            return self::sendError(400, "No hay campos válidos para actualizar.");
        }

        try {
            $model = new ExamType();
            $existente = $model->obtenerPorId($id);

            if (!$existente) {
                return self::sendError(404, "No se encontró el tipo de examen con id: $id");
            }

            $actualizado = $model->actualizarPorId($id, $payload);

            if ($actualizado) {
                $actual = $model->obtenerPorId($id);

                http_response_code(200);
                echo json_encode([
                    "message" => "Tipo de examen actualizado exitosamente",
                    "exam_type" => $actual
                ]);
            } else {
                self::sendError(500, "Error interno al intentar actualizar el tipo de examen.");
            }
        } catch (Exception $e) {
            self::sendError(500, "Error al actualizar el tipo de examen.", $e);
        }
    }

    // Delete (soft delete)
    public static function deleteOne($id)
    {
        if (!is_numeric($id)) {
            return self::sendError(400, "El id debe ser numérico.");
        }

        try {
            $model = new ExamType();

            // Obtener el registro antes de eliminar
            $item = $model->obtenerPorId($id);
            if (!$item) {
                return self::sendError(404, "No se encontró el tipo de examen con id: $id");
            }

            // Eliminar (soft)
            $eliminado = $model->eliminarPorId($id);

            if ($eliminado) {
                http_response_code(200);
                echo json_encode([
                    "message" => "Tipo de examen eliminado exitosamente.",
                    "exam_type" => $item
                ]);
            } else {
                self::sendError(500, "Error interno al intentar eliminar el tipo de examen.");
            }
        } catch (Exception $e) {
            self::sendError(500, "Error al eliminar el tipo de examen.", $e);
        }
    }

    // Restore (opcional, si lo usas en rutas)
    public static function restore($id)
    {
        if (!is_numeric($id)) {
            return self::sendError(400, "El id debe ser numérico.");
        }

        try {
            $model = new ExamType();
            $restaurado = $model->restaurarPorId($id);

            if ($restaurado) {
                http_response_code(200);
                echo json_encode([
                    "message" => "Tipo de examen restaurado exitosamente.",
                    "exam_type_id" => (int)$id
                ]);
            } else {
                self::sendError(500, "Error interno al intentar restaurar el tipo de examen.");
            }
        } catch (Exception $e) {
            self::sendError(500, "Error al restaurar el tipo de examen.", $e);
        }
    }

    // Hard delete (opcional, si lo usas en rutas)
    public static function deletePermanent($id)
    {
        if (!is_numeric($id)) {
            return self::sendError(400, "El id debe ser numérico.");
        }

        try {
            $model = new ExamType();
            $eliminado = $model->eliminarPermanentePorId($id);

            if ($eliminado) {
                http_response_code(200);
                echo json_encode([
                    "message" => "Tipo de examen eliminado permanentemente.",
                    "exam_type_id" => (int)$id
                ]);
            } else {
                self::sendError(500, "Error interno al intentar eliminar permanentemente el tipo de examen.");
            }
        } catch (Exception $e) {
            self::sendError(500, "Error al eliminar permanentemente el tipo de examen.", $e);
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
