<?php
require_once __DIR__ . '/../models/User.php';


class UserController 
{
    // Get all users
    public static function getAll()
    {
        try {
            $userModel = new User();
            $users = $userModel->obtenerTodos();

            http_response_code(200);
            echo json_encode($users);
        } catch (Exception $e) {
            self::sendError(500, "Error al obtener los registros de la tabla.", $e);
        }
    }

    // Get user by ID
    public static function getUserById($id)
    {
        if (!is_numeric($id)) {
            return self::sendError(400, "El id debe ser numérico.");
        }

        try {
            $userModel = new User();
            $user = $userModel->obtenerPorId($id);

            if ($user) {
                http_response_code(200);
                echo json_encode($user);
            } else {
                self::sendError(404, "User no encontrado");
            }
        } catch (Exception $e) {
            self::sendError(500, "Error al obtener el registro user", $e);
        }
    }

    public static function getUserByEmail($email)
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return self::sendError(400, "El correo electrónico no es válido.");
        }

        try {
            $userModel = new User();
            $user = $userModel->obtenerPorEmail($email);

            if ($user) {
                http_response_code(200);
                echo json_encode($user);
            } else {
                self::sendError(404, "User no encontrado");
            }
        } catch (Exception $e) {
            self::sendError(500, "Error al obtener el registro user", $e);
        }
    }

    public static function create($data)
    {
        $requiredVars = [
            'first_names',
            'office',
            'last_name',
            'email',
            'password',
            'is_professor',
            'is_level_coordinator',
            'is_administrator',
            'is_active'
        ];

        foreach ($requiredVars as $var) {
            if (!isset($data[$var]) || trim($data[$var]) === '') {
                http_response_code(400);
                echo json_encode(["error" => "El campo '$var' es obligatorio."]);
                return;
            }
        }

        // Validar que id_major e id_class_group_english sean numéricos
        if (!is_numeric($data['office'])) {
            http_response_code(400);
            echo json_encode(["error" => "Los campos 'id_major' deben ser numéricos."]);
            return;
        }

        // Validar formato del correo
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo json_encode(["error" => "El correo electrónico no es válido."]);
            return;
        }

        try {
            $userModel = new User();
            $creado = $userModel->crear($data);
    
            if ($creado) {
                http_response_code(201);
                echo json_encode([
                    "message" => "User creado exitosamente.",
                    "user" => $data
                ]);
            } else {
                self::sendError(500, "Error al crear el user.");
            }
        } catch (Exception $e) {
            self::sendError(500, "Error al crear el user.", $e);
        }
    }

    public static function update($id, $data)
    {
        if (!is_numeric($id)) {
            return self::sendError(400, "El id debe ser numérico.");
        }
    
        if (empty($data)) {
            return self::sendError(400, "Se requiere al menos un campo para actualizar el estudiante.");
        }
    
        try {
            $userModel = new User();
            $userExistente = $userModel->obtenerPorId($id);
    
            if (!$userExistente) {
                return self::sendError(404, "No se encontró el user con id: $id");
            }
    
            $actualizado = $userModel->actualizarPorId($id, $data);
    
            if ($actualizado) {
                $userActualizado = $userModel->obtenerPorId($id);
    
                http_response_code(200);
                echo json_encode([
                    "message" => "Estudiante actualizado exitosamente",
                    "user" => $userActualizado
                ]);
            } else {
                self::sendError(500, "Error interno al intentar actualizar el user.");
            }
        } catch (Exception $e) {
            self::sendError(500, "Error al actualizar el user", $e);
        }
    }

    public static function deleteOne($id)
    {
        if (!is_numeric($id)) {
            return self::sendError(400, "El id debe ser numérico.");
        }

        try {
            $userModel = new User();

            // 1. Obtener el registro antes de eliminar
            $user = $userModel->obtenerPorId($id);
            if (!$user) {
                return self::sendError(404, "No se encontró el nivel con id: $id");
            }

            // 2. Eliminarlo
            $eliminado = $userModel->eliminarPorId($id);

            if ($eliminado) {
                http_response_code(200);
                echo json_encode([
                    "message" => "User eliminado exitosamente.",
                    "user" => $user
                ]);
            } else {
                self::sendError(500, "Error interno al intentar eliminar el User.");
            }
        } catch (Exception $e) {
            self::sendError(500, "Error al eliminar el User.", $e);
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