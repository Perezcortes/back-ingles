<?php
require_once __DIR__ . '/../models/Level.php';

class LevelController {

    // Get all levels
    public static function getAll() {
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
    public static function getOne($id) {
        if (!is_numeric($id)) {
            return self::sendError(400, "El Id debe ser numérico.");
        }

        try {
            $levelModel = new Level();
            $level = $levelModel->obtenerPorId($id);

            if ($level) {
                http_response_code(200);
                echo json_encode($level);
            } else {
                self::sendError(404, "Level no encontrado");
            }
        } catch (Exception $e) {
            self::sendError(500, "Error al obtener el registro Level", $e);
        }
    }

    // Create a new level
    public static function create($data) {

        try {
            $levelModel = new Level();
            $created = $levelModel->crear($data['level_name']);

            if ($created) {
                http_response_code(201);
                echo json_encode(["message" => "Level creado exitosamente"]);
            } else {
                self::sendError(500, "Error al crear un registro Level");
            }
        } catch (Exception $e) {
            self::sendError(500, "Error al crear un registro Level", $e);
        }
    }

    // Update a level
    public static function update($id, $data) {
        if (!is_numeric($id)) {
            return self::sendError(400, "El Id debe ser numérico");
        }

        try {
            $levelModel = new Level();
            $updated = $levelModel->actualizar($id, $data['level_name']);

            if ($updated) {
                http_response_code(200);
                echo json_encode(["message" => "Level actualizado exitosamente"]);
            } else {
                self::sendError(404, "Level no encontrado");
            }
        } catch (Exception $e) {
            self::sendError(500, "Error al actualizar Level", $e);
        }
    }

    // Delete a level
    public static function deleteOne($id) {
        if (!is_numeric($id)) {
            return self::sendError(400, "El Id debe ser numérico");
        }

        try {
            $levelModel = new Level();
            $deleted = $levelModel->eliminar($id);

            if ($deleted) {
                http_response_code(200);
                echo json_encode(["message" => "Level eliminado exitosamente"]);
            } else {
                self::sendError(404, "Level no encontrado");
            }
        } catch (Exception $e) {
            self::sendError(500, "Error al eliminar el Level", $e);
        }
    }

    // Helper for error response
    private static function sendError($code, $message, $exception = null) {
        http_response_code($code);
        $response = ["error" => $message];

        if (getenv('APP_ENV') === 'development' && $exception) {
            $response["details"] = $exception->getMessage();
        }

        echo json_encode($response);
    }
}
