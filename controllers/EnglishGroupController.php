<?php
require_once __DIR__ . '/../models/EnglishGroup.php';

class EnglishGroupController
{
    // Obtener todos los grupos de inglés
    public static function getAll()
    {
        try {
            $englishGroupModel = new EnglishGroup();
            $groups = $englishGroupModel->obtenerTodos();

            http_response_code(200);
            echo json_encode($groups);
        } catch (Exception $e) {
            self::sendError(500, "Error al obtener los registros de la tabla english_group.", $e);
        }
    }

    // Obtener un grupo de inglés por ID
    public static function getOne($id)
    {
        if (!is_numeric($id)) {
            return self::sendError(400, "El id debe ser numérico.");
        }

        try {
            $englishGroupModel = new EnglishGroup();
            $group = $englishGroupModel->obtenerPorId($id);

            if ($group) {
                http_response_code(200);
                echo json_encode($group);
            } else {
                self::sendError(404, "Grupo no encontrado");
            }
        } catch (Exception $e) {
            self::sendError(500, "Error al obtener el registro del grupo", $e);
        }
    }

    // Obtener grupos de inglés por profesor
    public static function getByProfessor($id_professor)
    {
        if (!is_numeric($id_professor)) {
            return self::sendError(400, "El id_professor debe ser numérico.");
        }

        try {
            $englishGroupModel = new EnglishGroup();
            $groups = $englishGroupModel->obtenerPorIdProfessor($id_professor);

            http_response_code(200);
            echo json_encode($groups);
        } catch (Exception $e) {
            self::sendError(500, "Error al obtener los grupos por profesor", $e);
        }
    }

    // Obtener grupos de inglés por nivel
    public static function getByLevel($id_level)
    {
        if (!is_numeric($id_level)) {
            return self::sendError(400, "El id_level debe ser numérico.");
        }

        try {
            $englishGroupModel = new EnglishGroup();
            $groups = $englishGroupModel->obtenerPorIdLevel($id_level);

            http_response_code(200);
            echo json_encode($groups);
        } catch (Exception $e) {
            self::sendError(500, "Error al obtener los grupos por nivel", $e);
        }
    }

    // Crear un nuevo grupo de inglés
    public static function create($data)
    {
        if (!isset($data['name_group']) || trim($data['name_group']) === '') {
            http_response_code(400);
            echo json_encode(["error" => "El campo 'name_group' es obligatorio"]);
            return;
        }

        if (!isset($data['id_professor']) || !is_numeric($data['id_professor'])) {
            http_response_code(400);
            echo json_encode(["error" => "El campo 'id_professor' es obligatorio y debe ser numérico"]);
            return;
        }

        if (!isset($data['id_level']) || !is_numeric($data['id_level'])) {
            http_response_code(400);
            echo json_encode(["error" => "El campo 'id_level' es obligatorio y debe ser numérico"]);
            return;
        }

        try {
            $englishGroupModel = new EnglishGroup();
            $created = $englishGroupModel->crear($data);

            if ($created) {
                http_response_code(201);
                echo json_encode([
                    "message" => "Grupo de inglés creado exitosamente.",
                    "group" => $data
                ]);
            } else {
                self::sendError(500, "Error al crear el grupo.");
            }
        } catch (Exception $e) {
            self::sendError(500, "Error al crear el grupo.", $e);
        }
    }

    // Actualizar un grupo de inglés
    public static function update($id, $data)
    {
        if (!is_numeric($id)) {
            return self::sendError(400, "El id debe ser numérico.");
        }

        if (empty($data)) {
            return self::sendError(400, "Se requiere al menos un campo para actualizar el grupo.");
        }

        try {
            $englishGroupModel = new EnglishGroup();
            $groupExistente = $englishGroupModel->obtenerPorId($id);

            if (!$groupExistente) {
                return self::sendError(404, "No se encontró el grupo con id: $id");
            }

            $updated = $englishGroupModel->actualizarPorId($id, $data);

            if ($updated) {
                $updatedGroup = $englishGroupModel->obtenerPorId($id);

                http_response_code(200);
                echo json_encode([
                    "message" => "Grupo de inglés actualizado exitosamente",
                    "group" => $updatedGroup
                ]);
            } else {
                self::sendError(500, "Error interno al intentar actualizar el grupo.");
            }
        } catch (Exception $e) {
            self::sendError(500, "Error al actualizar el grupo", $e);
        }
    }

    // Eliminar un grupo de inglés (Soft Delete)
    public static function deleteOne($id)
    {
        if (!is_numeric($id)) {
            return self::sendError(400, "El id debe ser numérico.");
        }

        try {
            $englishGroupModel = new EnglishGroup();

            $group = $englishGroupModel->obtenerPorId($id);
            if (!$group) {
                return self::sendError(404, "No se encontró el grupo con id: $id");
            }

            $deleted = $englishGroupModel->eliminarPorId($id);

            if ($deleted) {
                http_response_code(200);
                echo json_encode([
                    "message" => "Grupo de inglés eliminado exitosamente.",
                    "group" => $group
                ]);
            } else {
                self::sendError(500, "Error interno al intentar eliminar el grupo.");
            }
        } catch (Exception $e) {
            self::sendError(500, "Error al eliminar el grupo.", $e);
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

    // Restaurar un grupo de inglés (desmarcar como eliminado)
    public static function restore($id)
    {
        if (!is_numeric($id)) {
            return self::sendError(400, "El id debe ser numérico.");
        }

        try {
            $englishGroupModel = new EnglishGroup();

            // Obtener el registro antes de restaurar
            $group = $englishGroupModel->obtenerPorId($id);
            if (!$group) {
                return self::sendError(404, "No se encontró el grupo con id: $id");
            }

            // Restaurarlo
            $restored = $englishGroupModel->restaurarPorId($id);

            if ($restored) {
                http_response_code(200);
                echo json_encode([
                    "message" => "Grupo de inglés restaurado exitosamente.",
                    "group" => $group
                ]);
            } else {
                self::sendError(500, "Error interno al intentar restaurar el grupo.");
            }
        } catch (Exception $e) {
            self::sendError(500, "Error al restaurar el grupo.", $e);
        }
    }

    // Eliminar un grupo de inglés permanentemente (hard delete)
    public static function deletePermanent($id)
    {
        if (!is_numeric($id)) {
            return self::sendError(400, "El id debe ser numérico.");
        }

        try {
            $englishGroupModel = new EnglishGroup();

            // Obtener el registro antes de eliminarlo permanentemente
            $group = $englishGroupModel->obtenerPorId($id);
            if (!$group) {
                return self::sendError(404, "No se encontró el grupo con id: $id");
            }

            // Eliminarlo permanentemente
            $deletedPermanent = $englishGroupModel->eliminarPermanentePorId($id);

            if ($deletedPermanent) {
                http_response_code(200);
                echo json_encode([
                    "message" => "Grupo de inglés eliminado permanentemente.",
                    "group" => $group
                ]);
            } else {
                self::sendError(500, "Error interno al intentar eliminar permanentemente el grupo.");
            }
        } catch (Exception $e) {
            self::sendError(500, "Error al eliminar permanentemente el grupo.", $e);
        }
    }

}