<?php
require_once __DIR__ . '/../models/Level.php';

class LevelController
{

    // Get all levels
    public static function getAll()
    {
        try {
            $levelModel = new Level();
            $levels = $levelModel->obtenerTodos();

            http_response_code(200);
            echo json_encode($levels);
        } catch (Exception $e) {
            self::sendError(500, "Error al obtener los registros de la tabla.", $e);
        }
    }

    // Get a level by ID
    public static function getOne($id)
    {
        if (!is_numeric($id)) {
            return self::sendError(400, "El id debe ser numérico.");
        }

        try {
            $levelModel = new Level();
            $level = $levelModel->obtenerPorId($id);

            if ($level) {
                http_response_code(200);
                echo json_encode($level);
            } else {
                self::sendError(404, "Nivel no encontrado");
            }
        } catch (Exception $e) {
            self::sendError(500, "Error al obtener el registro nivel", $e);
        }
    }

    // Create a new level
    public static function create($data)
    {
        if (!isset($data['level_name']) || trim($data['level_name']) === '') {
            http_response_code(400);
            echo json_encode(["error" => "El campo 'level_name' es obligatorio"]);
            return;
        }
    
        try {
            $levelModel = new Level();
            $creado = $levelModel->crear($data);
    
            if ($creado) {
                http_response_code(201);
                echo json_encode([
                    "message" => "Nivel creado exitosamente.",
                    "nivel" => $data
                ]);
            } else {
                self::sendError(500, "Error al crear un registro.");
            }
        } catch (Exception $e) {
            self::sendError(500, "Error al crear un registro.", $e);
        }
    }
    


    // Update a level
    public static function update($id, $data)
    {
        if (!is_numeric($id)) {
            return self::sendError(400, "El id debe ser numérico.");
        }

        if (empty($data)) {
            return self::sendError(400, "Se requiere al menos un campo para actualizar el nivel.");
        }

        try {
            $levelModel = new Level();
            $nivelExistente = $levelModel->obtenerPorId($id);

            if (!$nivelExistente) {
                return self::sendError(404, "No se encontró el Nivel con id: $id");
            }

            $actualizado = $levelModel->actualizar($id, $data);

            if ($actualizado) {
                $nivelActualizado = $levelModel->obtenerPorId($id);

                http_response_code(200);
                echo json_encode([
                    "message" => "Nivel actualizado exitosamente",
                    "level" => $nivelActualizado
                ]);
            } else {
                self::sendError(500, "Error interno al intentar actualizar el nivel.");
            }
        } catch (Exception $e) {
            self::sendError(500, "Error al actualizar nivel", $e);
        }
    }


    // Delete a level
    public static function deleteOne($id)
    {
        if (!is_numeric($id)) {
            return self::sendError(400, "El id debe ser numérico.");
        }

        try {
            $levelModel = new Level();

            // 1. Obtener el registro antes de eliminar
            $nivel = $levelModel->obtenerPorId($id);
            if (!$nivel) {
                return self::sendError(404, "No se encontró el nivel con id: $id");
            }

            // 2. Eliminarlo
            $eliminado = $levelModel->eliminar($id);

            if ($eliminado) {
                http_response_code(200);
                echo json_encode([
                    "message" => "Nivel eliminado exitosamente.",
                    "level" => $nivel
                ]);
            } else {
                self::sendError(500, "Error interno al intentar eliminar el nivel.");
            }
        } catch (Exception $e) {
            self::sendError(500, "Error al eliminar el nivel.", $e);
        }
    }



    // Helper for error response
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
