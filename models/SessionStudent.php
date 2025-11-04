<?php

require_once 'config/database.php';
require_once 'entities/SessionStudent.php';

use App\Entities\SessionStudent as SessionStudentEntity;

class SessionStudent
{
    private $conn;
    private $table_name = "sesion_student";

    public function __construct()
    {
        $this->conn = Database::getConnection();
    }

    /**
     * Crea un registro de sesión de auditoría en la base de datos.
     * * @param array $data Contiene los valores para la inserción: [SessionStudentEntity::IP, SessionStudentEntity::ID_STUDENT].
     * @return bool Devuelve true si la inserción de la fila fue exitosa.
     * @throws \PDOException Si ocurre un error de base de datos durante la preparación o ejecución de la consulta.
     */
    public function createSession($data)
    {
        // Consulta Base: Usa marcadores de posición.
        $query = "INSERT INTO " . $this->table_name . " (" . SessionStudentEntity::IP . ", " . SessionStudentEntity::ID_STUDENT . ") VALUES (:ip, :id_student)";

        // Si prepare() falla se lanza PDOException
        $stmt = $this->conn->prepare($query);

        // Vinculación de Parámetros: Enlaza los datos de entrada a los marcadores de posición.
        $stmt->bindParam(':ip', $data[SessionStudentEntity::IP]);
        $stmt->bindParam(':id_student', $data[SessionStudentEntity::ID_STUDENT], PDO::PARAM_INT);

        // Si execute() falla se lanza PDOException
        return $stmt->execute();
    }

    /**
     * Marca la sesión como terminada (Soft Delete) en el registro de auditoría.
     * Se llama al cerrar la sesión (Logout).
     * * @param int $id_student El ID del estudiante cuya sesión debe ser marcada como finalizada.
     * @return bool Devuelve true si la actualización de la fila fue exitosa.
     * @throws \PDOException Si ocurre un error de base de datos durante la preparación o ejecución de la consulta.
     */
    public function deleteSession($id_student)
    {
        // Consulta Base: Usa marcadores de posición.
        $query = "UPDATE " . $this->table_name .
            " SET " . SessionStudentEntity::DELETED_AT . " = NOW() " .
            " WHERE " . SessionStudentEntity::ID_STUDENT . " = :id_student " .
            " AND " . SessionStudentEntity::DELETED_AT . " IS NULL "; // Solo finaliza las sesiones activas

        // Si prepare() falla se lanza PDOException
        $stmt = $this->conn->prepare($query);

        // Vinculación de Parámetros: Enlaza los datos de entrada a los marcadores de posición.
        $stmt->bindParam(':id_student', $id_student, PDO::PARAM_INT);

        // Si execute() falla se lanza PDOException
        return $stmt->execute();
    }
}