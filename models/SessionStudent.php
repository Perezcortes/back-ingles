<?php

require_once 'config/database.php';
require_once 'entities/SessionStudent.php';
use App\Entities\SessionStudent as SessionStudentEntity;
use PDO;
use PDOException;

class SessionStudent
{
    private $conn;
    private $table_name;

    public function __construct()
    {
        $this->conn = Database::getConnection();
        $this->table_name = "sesion_student";
    }

    public function createSession($data)
    {
        try {
            $query = "INSERT INTO " . $this->table_name . " (" . SessionStudentEntity::IP . ", " . SessionStudentEntity::ID_STUDENT . ") VALUES (:ip, :id_student)";
            $stmt = $this->conn->prepare($query);

            $stmt->bindParam(':ip', $data[SessionStudentEntity::IP]);
            $stmt->bindParam(':id_student', $data[SessionStudentEntity::ID_STUDENT], PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error al crear sesión de estudiante: " . $e->getMessage());
            return false;
        }
    }
    
    // Opcional: método para eliminar sesiones al hacer logout
    public function deleteSession($id_student)
    {
        $query = "UPDATE " . $this->table_name . " SET " . SessionStudentEntity::DELETED_AT . " = NOW() WHERE " . SessionStudentEntity::ID_STUDENT . " = :id_student";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_student', $id_student, PDO::PARAM_INT);
        return $stmt->execute();
    }
}