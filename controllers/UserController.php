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
     * Obtiene todos los usuarios activos (Servicio 16).
     * @return void
     */
    public function getAll()
    {
        try {
            // Llama al método del modelo que filtra por DELETED_AT IS NULL
            $users = $this->userModel->getAll();
            
            // foreach ($users as &$user) {
            //     unset($user[UserEntity::PASSWORD]); 
            // }

            $this->responseHandler->sendSuccess(["users" => $users], "Usuarios encontrados exitosamente.");
        } catch (Exception $e) {
            $this->responseHandler->sendFailure("Error al obtener los registros de usuarios.", 500, $e);
        }
    }
 
    /**
     * Obtiene un usuario por su ID (Servicio 17).
     * @param int $id ID del usuario a obtener.
     * @return void
     */
    public function getuserById($id)
    {
        // Validación de ID numérico
        if (!is_numeric($id)) {
            $this->responseHandler->sendFailure("El ID debe ser numérico.", 400);
            return;
        }

        try {
            $user = $this->userModel->getById($id);

            if (!$user) {
                $this->responseHandler->sendFailure("Usuario no encontrado.", 404);
                return;
            }

            // Quitar la contraseña de la respuesta
            // unset($user[UserEntity::PASSWORD]); 
            
            $this->responseHandler->sendSuccess(["user" => $user], "Usuario encontrado exitosamente.");

        } catch (Exception $e) {
            $this->responseHandler->sendFailure("Error al obtener el registro del usuario.", 500, $e);
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

    /**
     * Actualiza los datos de un usuario existente (Servicio 19).
     * @param int $id ID del usuario a actualizar.
     * @param array $data Los datos a modificar.
     * @return void
     */
    public function update($id, $data)
    {
        //Validación de ID numérico
        if (!is_numeric($id)) {
            $this->responseHandler->sendFailure("El ID debe ser numérico.", 400);
            return;
        }

        // Campos requeridos en la data de entrada
        $requiredFields = [
            UserEntity::EMAIL, 
            UserEntity::PASSWORD, 
            UserEntity::IS_PROFESSOR, 
            UserEntity::IS_LEVEL_COORDINATOR, 
            UserEntity::IS_ADMINISTRATOR
        ];
        
        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                $this->responseHandler->sendFailure("El campo '{$field}' es obligatorio para la actualización.", 400);
                return;
            }
        }

        try {
            // Verificar si el usuario existe y obtener sus datos actuales
            $existingUser = $this->userModel->getById($id);

            if (!$existingUser) {
                $this->responseHandler->sendFailure("Usuario no encontrado.", 404);
                return;
            }

            // Validación de unicidad de email (si el email ha cambiado)
            $newEmail = $data[UserEntity::EMAIL];
            if ($existingUser[UserEntity::EMAIL] !== $newEmail) {
                $userWithSameEmail = $this->userModel->findUserByEmail($newEmail);
                if ($userWithSameEmail && (int)$userWithSameEmail[UserEntity::ID] !== (int)$id) {
                    $this->responseHandler->sendFailure("El correo electrónico ya está registrado por otro usuario.", 409);
                    return;
                }
            }
            
            // Preparación de datos para la actualización
            $updateData = [
                // Los campos opcionales toman el valor enviado o el valor existente
                UserEntity::FULL_NAME => $data[UserEntity::FULL_NAME] ?? $existingUser[UserEntity::FULL_NAME],
                UserEntity::OFFICE => $data[UserEntity::OFFICE] ?? $existingUser[UserEntity::OFFICE],
                UserEntity::EMAIL => $newEmail,
                UserEntity::PASSWORD => $data[UserEntity::PASSWORD],
                // Conversión de booleano a entero (1 o 0)
                UserEntity::IS_PROFESSOR => $data[UserEntity::IS_PROFESSOR] ? 1 : 0,
                UserEntity::IS_LEVEL_COORDINATOR => $data[UserEntity::IS_LEVEL_COORDINATOR] ? 1 : 0,
                UserEntity::IS_ADMINISTRATOR => $data[UserEntity::IS_ADMINISTRATOR] ? 1 : 0,
            ];
            
            // Lógica de negocio (Anulación de FKs si se desactiva un rol)
            
            $oldIsCoordinator = (int)$existingUser[UserEntity::IS_LEVEL_COORDINATOR];
            $newIsCoordinator = $updateData[UserEntity::IS_LEVEL_COORDINATOR];

            $oldIsProfessor = (int)$existingUser[UserEntity::IS_PROFESSOR];
            $newIsProfessor = $updateData[UserEntity::IS_PROFESSOR];

            // Si antes era coordinador (1) y ahora no (0), se anula el FK en la tabla 'level'
            if ($oldIsCoordinator === 1 && $newIsCoordinator === 0) {
                $this->userModel->nullifyLevelCoordinator($id);
            }
            
            // Si antes era profesor (1) y ahora no (0), se anula el FK en la tabla 'english_class'
            if ($oldIsProfessor === 1 && $newIsProfessor === 0) {
                $this->userModel->nullifyEnglishClassProfessor($id);
            }

            // Ejecutar la actualización
            $this->userModel->updateById($id, $updateData);
            
            // Obtener y responder con el usuario actualizado
            $updatedUser = $this->userModel->getById($id);
            unset($updatedUser[UserEntity::PASSWORD]); // Quitar contraseña de la respuesta

            $this->responseHandler->sendSuccess(["user" => $updatedUser], "Usuario actualizado exitosamente.");

        } catch (Exception $e) {
            $this->responseHandler->sendFailure("Error al actualizar el usuario. Revisar logs.", 500, $e);
        }
    }

    /**
     * Realiza el borrado lógico (soft delete) de un usuario (Servicio 20).
     * @param int $id ID del usuario a eliminar.
     * @return void
     */
    public function deleteOne($id)
    {
        // Validación de ID numérico
        if (!is_numeric($id)) {
            $this->responseHandler->sendFailure("El ID debe ser numérico.", 400);
            return;
        }

        try {
            // Obtener el registro antes de eliminar 
            $user = $this->userModel->getById($id);
            if (!$user) {
                $this->responseHandler->sendFailure("No se encontró el usuario con id: $id", 404);
                return;
            }

            // Anular FKs en tablas dependientes si el usuario tenía roles
            $isCoordinator = (int)$user[UserEntity::IS_LEVEL_COORDINATOR];
            $isProfessor = (int)$user[UserEntity::IS_PROFESSOR];
            
            // Si era coordinador, anular la referencia en la tabla 'level'
            if ($isCoordinator === 1) {
                $this->userModel->nullifyLevelCoordinator($id);
            }
            
            // Si era profesor, anular la referencia en la tabla 'english_class'
            if ($isProfessor === 1) {
                $this->userModel->nullifyEnglishClassProfessor($id);
            }

            // Ejecutar el borrado lógico (soft delete)
            $eliminado = $this->userModel->deleteById($id);

            if ($eliminado) {
                // Preparamos la respuesta (obtenemos el usuario actualizado para incluir el campo deleted_at)
                $deletedUser = $this->userModel->getById($id);
                unset($deletedUser[UserEntity::PASSWORD]);
                
                $this->responseHandler->sendSuccess(["user" => $deletedUser], "Usuario eliminado exitosamente.");
            } else {
                $this->responseHandler->sendFailure("Error interno al intentar eliminar el usuario.", 500);
            }
        } catch (Exception $e) {
            $this->responseHandler->sendFailure("Error al eliminar el usuario.", 500, $e);
        }
    }

    /**
     * Elimina lógicamente (soft delete) a todos los usuarios activos. (Servicio 21)
     * @return void
     */
    public function deleteAll()
    {
        try {
            // El modelo User::deleteAll() maneja la lógica de negocio (anular FKs)
            // y la eliminación lógica masiva de todos los usuarios activos.
            $success = $this->userModel->deleteAll();

            if ($success) {
                $this->responseHandler->sendSuccess(null, "Todos los usuarios eliminados exitosamente.");
            } else {
                $this->responseHandler->sendFailure("Error al realizar el borrado lógico masivo de usuarios.", 500);
            }
        } catch (Exception $e) {
            $this->responseHandler->sendFailure("Error en el proceso de borrado masivo de usuarios. Revisar logs.", 500, $e);
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