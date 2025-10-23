<?php
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../responses/ResponseHandler.php'; 
require_once __DIR__ . '/../entities/User.php';

use App\Entities\User as UserEntity;

class UserController 
{
    private $userModel;
    private $responseHandler;

    public function __construct()
    {
        $this->userModel = new User();
        $this->responseHandler = new ResponseHandler();
    }

    /**
     * Crea un nuevo usuario en el sistema.
     * @param array $data Los datos del nuevo usuario.
     * @return void
     */
    public function create($data)
    {
        // Campos obligatorios
        $requiredFields = [
            UserEntity::EMAIL, 
            UserEntity::PASSWORD, 
            UserEntity::IS_PROFESSOR, 
            UserEntity::IS_LEVEL_COORDINATOR, 
            UserEntity::IS_ADMINISTRATOR
        ];

        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || (is_string($data[$field]) && trim($data[$field]) === '')) {
                $this->responseHandler->sendFailure("El campo '{$field}' es obligatorio.", 400);
                return;
            }
        }

        // Validación de formato de email
        $email = $data[UserEntity::EMAIL];
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->responseHandler->sendFailure("El formato del correo electrónico es inválido.", 400);
            return;
        }

        try {
            // Validación de unicidad de email
            if ($this->userModel->findUserByEmail($email)) {
                $this->responseHandler->sendFailure("El correo electrónico ya está registrado.", 409);
                return;
            }

            // Preparación de datos (usando valores por defecto y opcionales) Usamos el operador ternario para asegurar que los booleanos se conviertan a INT (1 o 0)
            $userData = [
                UserEntity::FULL_NAME => $data[UserEntity::FULL_NAME] ?? null,
                UserEntity::OFFICE => $data[UserEntity::OFFICE] ?? null,
                UserEntity::EMAIL => $email,
                UserEntity::PASSWORD => $data[UserEntity::PASSWORD],
                UserEntity::IS_PROFESSOR => $data[UserEntity::IS_PROFESSOR] ? 1 : 0,
                UserEntity::IS_LEVEL_COORDINATOR => $data[UserEntity::IS_LEVEL_COORDINATOR] ? 1 : 0,
                UserEntity::IS_ADMINISTRATOR => $data[UserEntity::IS_ADMINISTRATOR] ? 1 : 0,
            ];

            // Creación
            $createdId = $this->userModel->create($userData);

            if (!$createdId) {
                $this->responseHandler->sendFailure("Error al crear el usuario. No se pudo obtener el ID de inserción.", 500);
                return;
            }

            // Obtener y responder
            $newUser = $this->userModel->getById($createdId);
            unset($newUser[UserEntity::PASSWORD]); // Eliminar la contraseña de la respuesta

            $this->responseHandler->sendSuccess(["user" => $newUser], "Usuario creado exitosamente.", 201);

        } catch (Exception $e) {
            $this->responseHandler->sendFailure("Error al crear el usuario. Revisar logs.", 500, $e);
        }
    }

    /**
     * Obtiene todos los usuarios activos.
     * Este es el Servicio 16: Obtener Todos los Usuarios.
     * @return void
     */
    public function getAll()
    {
        try {
            // Llama al método del modelo que filtra por DELETED_AT IS NULL
            $users = $this->userModel->getAll();
            
            // Eliminar la contraseña de todos los usuarios
            foreach ($users as &$user) {
                unset($user[UserEntity::PASSWORD]);
            }

            $this->responseHandler->sendSuccess(["users" => $users], "Usuarios encontrados exitosamente.");
        } catch (Exception $e) {
            $this->responseHandler->sendFailure("Error al obtener los registros de usuarios.", 500, $e);
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

    /**
     * Obtiene un usuario activo por su email.
     * Este método recibe datos por POST (body) o GET (query params).
     * @param array $data Contiene el campo 'email' del usuario.
     * @return void
     */
    public function getUserByEmail($data)
    {
        if (!isset($data[UserEntity::EMAIL]) || !filter_var($data[UserEntity::EMAIL], FILTER_VALIDATE_EMAIL)) {
            $this->responseHandler->sendFailure("El correo electrónico no es válido.", 400);
            return;
        }
        
        $email = $data[UserEntity::EMAIL];

        try {
            // findUserByEmail solo devuelve usuarios activos (deleted_at IS NULL)
            $user = $this->userModel->findUserByEmail($email);

            if (!$user) {
                $this->responseHandler->sendFailure("Usuario no encontrado.", 404);
                return;
            }

            unset($user[UserEntity::PASSWORD]);
            $this->responseHandler->sendSuccess(["user" => $user], "Usuario encontrado exitosamente.");
        } catch (Exception $e) {
            $this->responseHandler->sendFailure("Error al obtener el registro del usuario.", 500, $e);
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