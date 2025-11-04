<?php

// models/SessionUser.php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../entities/SessionUser.php';

use App\Entities\SessionUser as SessionUserEntity;

class SessionUser
{
    private $conn;
    private $table_name = 'sesion_user';

    public function __construct()
    {
        $this->conn = Database::getConnection();
    }

    /**
     * Crea un registro de sesión de auditoría en la base de datos.
     * @param array $data Contiene los valores para la inserción: [SessionUserEntity::IP, SessionUserEntity::ID_USER]
     * @return bool Retorna true si la inserción fue exitosa.
     * @throws PDOException Si ocurre un error de base de datos durante la preparación o ejecución de la consulta.
     */
    public function createSession($data)
    {
        // Consulta Base: Usa marcadores de posición.
        $query = "INSERT INTO " . $this->table_name . " (" . SessionUserEntity::IP . ", " . SessionUserEntity::ID_USER . ") VALUES (:ip, :id_user)";

        // Si prepare() falla se lanza PDOException
        $stmt = $this->conn->prepare($query);

        // Vinculación de Parámetros: Enlaza los datos de entrada a los marcadores de posición.
        $stmt->bindParam(':ip', $data[SessionUserEntity::IP]);
        $stmt->bindParam(':id_user', $data[SessionUserEntity::ID_USER], PDO::PARAM_INT);

        // Si execute() falla se lanza PDOException
        return $stmt->execute();
    }


    /**
     * Marca la sesión como terminada (Soft Delete) en el registro de auditoría.
     * Se llama al cerrar la sesión (Logout).
     * @param int $id_user El ID del usuario cuya sesión debe ser marcada como finalizada.
     * @return bool Devuelve true si la actualización de la fila fue exitosa.
     * @throws PDOException Si ocurre un error de base de datos durante la preparación o ejecución de la consulta.
     */
    public function deleteSession($id_user)
    {
        // Consulta Base: Usa marcadores de posición.
        $query = "UPDATE " . $this->table_name .
            " SET " . SessionUserEntity::DELETED_AT . " = NOW() " .
            " WHERE " . SessionUserEntity::ID_USER . " = :id_user " .
            " AND " . SessionUserEntity::DELETED_AT . " IS NULL "; // Solo finaliza las sesiones activas

        // Si prepare() falla se lanza PDOException
        $stmt = $this->conn->prepare($query);

        // Vinculación de Parámetros: Enlaza los datos de entrada a los marcadores de posición.
        $stmt->bindParam(':id_user', $id_user, PDO::PARAM_INT);

        // Si execute() falla se lanza PDOException
        return $stmt->execute();
    }
}