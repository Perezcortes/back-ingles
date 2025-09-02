<?php
require_once __DIR__ . '/../models/Exam.php';

class ExamController
{
    // Listar activos
    public static function getAll()
    {
        try {
            $model = new Exam();
            $items = $model->obtenerTodos();

            http_response_code(200);
            echo json_encode($items);
        } catch (Exception $e) {
            self::sendError(500, "Error al obtener los registros de exam.", $e);
        }
    }

    // Obtener uno por ID
    public static function getOne($id)
    {
        if (!is_numeric($id)) {
            return self::sendError(400, "El id debe ser numérico.");
        }

        try {
            $model = new Exam();
            $item = $model->obtenerPorId($id);

            if ($item) {
                http_response_code(200);
                echo json_encode($item);
            } else {
                self::sendError(404, "Examen no encontrado.");
            }
        } catch (Exception $e) {
            self::sendError(500, "Error al obtener el registro de exam.", $e);
        }
    }

    // Obtener por who_created
    public static function getByCreator($who_created)
    {
        if (!is_numeric($who_created)) {
            return self::sendError(400, "El parámetro 'who_created' debe ser numérico.");
        }

        try {
            $model = new Exam();
            $items = $model->obtenerPorCreador($who_created);

            http_response_code(200);
            echo json_encode($items);
        } catch (Exception $e) {
            self::sendError(500, "Error al obtener por who_created.", $e);
        }
    }

    // Crear
    public static function create($data)
    {
        $required = ['id_level','id_writing_template','id_reading_template','duration_time','who_created'];
        foreach ($required as $field) {
            if (!isset($data[$field])) {
                http_response_code(400);
                echo json_encode(["error" => "El campo '$field' es obligatorio"]);
                return;
            }
        }

        // Validaciones básicas
        if (!is_numeric($data['id_level'])
            || !is_numeric($data['id_writing_template'])
            || !is_numeric($data['id_reading_template'])
            || !is_numeric($data['duration_time'])
            || !is_numeric($data['who_created'])) {
            http_response_code(400);
            echo json_encode(["error" => "Todos los campos numéricos deben ser válidos."]);
            return;
        }

        try {
            $model = new Exam();
            $creado = $model->crear([
                'id_level' => (int)$data['id_level'],
                'id_writing_template' => (int)$data['id_writing_template'],
                'id_reading_template' => (int)$data['id_reading_template'],
                'duration_time' => (int)$data['duration_time'],
                'who_created' => (int)$data['who_created'],
            ]);

            if ($creado) {
                http_response_code(201);
                echo json_encode([
                    "message" => "Examen creado exitosamente.",
                    "exam" => [
                        "id_level" => (int)$data['id_level'],
                        "id_writing_template" => (int)$data['id_writing_template'],
                        "id_reading_template" => (int)$data['id_reading_template'],
                        "duration_time" => (int)$data['duration_time'],
                        "who_created" => (int)$data['who_created'],
                    ]
                ]);
            } else {
                self::sendError(500, "Error al crear el examen.");
            }
        } catch (Exception $e) {
            self::sendError(500, "Error al crear el examen.", $e);
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

        if (array_key_exists('id_level', $data)) {
            if (!is_numeric($data['id_level'])) return self::sendError(400, "El campo 'id_level' debe ser numérico.");
            $payload['id_level'] = (int)$data['id_level'];
        }

        if (array_key_exists('id_writing_template', $data)) {
            if (!is_numeric($data['id_writing_template'])) return self::sendError(400, "El campo 'id_writing_template' debe ser numérico.");
            $payload['id_writing_template'] = (int)$data['id_writing_template'];
        }

        if (array_key_exists('id_reading_template', $data)) {
            if (!is_numeric($data['id_reading_template'])) return self::sendError(400, "El campo 'id_reading_template' debe ser numérico.");
            $payload['id_reading_template'] = (int)$data['id_reading_template'];
        }

        if (array_key_exists('duration_time', $data)) {
            if (!is_numeric($data['duration_time'])) return self::sendError(400, "El campo 'duration_time' debe ser numérico.");
            $payload['duration_time'] = (int)$data['duration_time'];
        }

        if (array_key_exists('who_created', $data)) {
            if (!is_numeric($data['who_created'])) return self::sendError(400, "El campo 'who_created' debe ser numérico.");
            $payload['who_created'] = (int)$data['who_created'];
        }

        if (empty($payload)) {
            return self::sendError(400, "No hay campos válidos para actualizar.");
        }

        try {
            $model = new Exam();
            $existente = $model->obtenerPorId($id);
            if (!$existente) {
                return self::sendError(404, "No se encontró el examen con id: $id");
            }

            $actualizado = $model->actualizarPorId($id, $payload);

            if ($actualizado) {
                $actual = $model->obtenerPorId($id);

                http_response_code(200);
                echo json_encode([
                    "message" => "Examen actualizado exitosamente.",
                    "exam" => $actual
                ]);
            } else {
                self::sendError(500, "Error interno al intentar actualizar el examen.");
            }
        } catch (Exception $e) {
            self::sendError(500, "Error al actualizar el examen.", $e);
        }
    }

    // Soft delete
    public static function deleteOne($id)
    {
        if (!is_numeric($id)) {
            return self::sendError(400, "El id debe ser numérico.");
        }

        try {
            $model = new Exam();

            $item = $model->obtenerPorId($id);
            if (!$item) {
                return self::sendError(404, "No se encontró el examen con id: $id");
            }

            $deleted = $model->eliminarPorId($id);

            if ($deleted) {
                http_response_code(200);
                echo json_encode([
                    "message" => "Examen eliminado exitosamente.",
                    "exam" => $item
                ]);
            } else {
                self::sendError(500, "Error interno al intentar eliminar el examen.");
            }
        } catch (Exception $e) {
            self::sendError(500, "Error al eliminar el examen.", $e);
        }
    }

    // Restore (desmarcar eliminado)
    public static function restore($id)
    {
        if (!is_numeric($id)) {
            return self::sendError(400, "El id debe ser numérico.");
        }

        try {
            $model = new Exam();
            $restored = $model->restaurarPorId($id);

            if ($restored) {
                http_response_code(200);
                echo json_encode([
                    "message" => "Examen restaurado exitosamente.",
                    "exam_id" => (int)$id
                ]);
            } else {
                self::sendError(500, "Error interno al intentar restaurar el examen.");
            }
        } catch (Exception $e) {
            self::sendError(500, "Error al restaurar el examen.", $e);
        }
    }

    // Hard delete
    public static function deletePermanent($id)
    {
        if (!is_numeric($id)) {
            return self::sendError(400, "El id debe ser numérico.");
        }

        try {
            $model = new Exam();
            $deleted = $model->eliminarPermanentePorId($id);

            if ($deleted) {
                http_response_code(200);
                echo json_encode([
                    "message" => "Examen eliminado permanentemente.",
                    "exam_id" => (int)$id
                ]);
            } else {
                self::sendError(500, "Error interno al intentar eliminar permanentemente el examen.");
            }
        } catch (Exception $e) {
            self::sendError(500, "Error al eliminar permanentemente el examen.", $e);
        }
    }

    // Helper de errores
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
