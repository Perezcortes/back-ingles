<?php
require_once __DIR__ . '/../models/Major.php';

class MajorController
{
    // Para obtener todos los majors
    public static function getAll()
    {
        try {
            $majorModel = new Major();
            $majors = $majorModel->obtenerTodos();

            http_response_code(200);
            echo json_encode($majors);
        } catch (Exception $e) {
            self::sendError(500, "Error al obtener los registros de la tabla major.", $e);
        }
    }

    // Obtener una carrera por ID
    public static function getOne($id)
    {
        if (!is_numeric($id)) {
            return self::sendError(400, "El id debe ser numérico.");
        }

        try {
            $majorModel = new Major();
            $major = $majorModel->obtenerPorId($id);

            if ($major) {
                http_response_code(200);
                echo json_encode($major);
            } else {
                self::sendError(404, "Major no encontrado");
            }
        } catch (Exception $e) {
            self::sendError(500, "Error al obtener el registro major", $e);
        }
    }

    // Crear unanueva carrera
    public static function create($data)
    {
        if (!isset($data['major_name']) || trim($data['major_name']) === '') {
            http_response_code(400);
            echo json_encode(["error" => "El campo 'major_name' es obligatorio"]);
            return;
        }

        if (!isset($data['description']) || trim($data['description']) === '') {
            http_response_code(400);
            echo json_encode(["error" => "El campo 'description' es obligatorio"]);
            return;
        }
    
        try {
            $majorModel = new Major();
            $creado = $majorModel->crear($data);
    
            if ($creado) {
                http_response_code(201);
                echo json_encode([
                    "message" => "Carrera creada exitosamente.",
                    "major" => $data
                ]);
            } else {
                self::sendError(500, "Error al crear un registro.");
            }
        } catch (Exception $e) {
            self::sendError(500, "Error al crear un registro.", $e);
        }
    }

    // Actualizar una carrera
    public static function update($id, $data)
    {
        if (!is_numeric($id)) {
            return self::sendError(400, "El id debe ser numérico.");
        }

        if (empty($data)) {
            return self::sendError(400, "Se requiere al menos un campo para actualizar la carrera.");
        }

        try {
            $majorModel = new Major();
            $majorExistente = $majorModel->obtenerPorId($id);

            if (!$majorExistente) {
                return self::sendError(404, "No se encontró la carrera con id: $id");
            }

            $actualizado = $majorModel->actualizarPorId($id, $data);

            if ($actualizado) {
                $majorActualizado = $majorModel->obtenerPorId($id);

                http_response_code(200);
                echo json_encode([
                    "message" => "Carrera actualizada exitosamente",
                    "major" => $majorActualizado
                ]);
            } else {
                self::sendError(500, "Error interno al intentar actualizar la carrera.");
            }
        } catch (Exception $e) {
            self::sendError(500, "Error al actualizar la carrera", $e);
        }
    }

    // Eliminar un major (Soft Delete)
    public static function deleteOne($id)
    {
        if (!is_numeric($id)) {
            return self::sendError(400, "El id debe ser numérico.");
        }

        try {
            $majorModel = new Major();

            $major = $majorModel->obtenerPorId($id);
            if (!$major) {
                return self::sendError(404, "No se encontró la carrera con id: $id");
            }

            // eliminarlo (Soft Delete)
            $eliminado = $majorModel->eliminarPorId($id);

            if ($eliminado) {
                http_response_code(200);
                echo json_encode([
                    "message" => "Carrera eliminada exitosamente.",
                    "major" => $major
                ]);
            } else {
                self::sendError(500, "Error interno al intentar eliminar la carrera.");
            }
        } catch (Exception $e) {
            self::sendError(500, "Error al eliminar la carrera.", $e);
        }
    }

    // Restaurar un major (desmarcar como eliminado)
    public static function restore($id)
    {
        if (!is_numeric($id)) {
            return self::sendError(400, "El id debe ser numérico.");
        }

        try {
            $majorModel = new Major();

            //obtener el registro antes de restaurar
            $major = $majorModel->obtenerPorId($id);
            if (!$major) {
                return self::sendError(404, "No se encontró el major con id: $id");
            }

            //restaurarlo
            $restaurado = $majorModel->restaurarPorId($id);

            if ($restaurado) {
                http_response_code(200);
                echo json_encode([
                    "message" => "Carrera restaurada exitosamente.",
                    "major" => $major
                ]);
            } else {
                self::sendError(500, "Error interno al intentar restaurar la carrera.");
            }
        } catch (Exception $e) {
            self::sendError(500, "Error al restaurar el major.", $e);
        }
    }

    // Eliminar un major permanentemente (Hard Delete)
    public static function deletePermanent($id)
    {
        if (!is_numeric($id)) {
            return self::sendError(400, "El id debe ser numérico.");
        }

        try {
            $majorModel = new Major();

            //obtener el registro antes de eliminar permanentemente
            $major = $majorModel->obtenerPorId($id);
            if (!$major) {
                return self::sendError(404, "No se encontró la carrera con id: $id");
            }

            //eliminarlo permanentemente
            $eliminadoPermanente = $majorModel->eliminarPermanentePorId($id);

            if ($eliminadoPermanente) {
                http_response_code(200);
                echo json_encode([
                    "message" => "Carrera eliminada permanentemente.",
                    "major" => $major
                ]);
            } else {
                self::sendError(500, "Error interno al intentar eliminar permanentemente la carrera.");
            }
        } catch (Exception $e) {
            self::sendError(500, "Error al eliminar permanentemente la carrera.", $e);
        }
    }

    // Helper para las respuestas de error
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