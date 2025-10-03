<?php

require_once 'config/database.php';
require_once 'entities/Student.php';
use App\Entities\Student as StudentEntity;
use PDO;

class Student
{
    private $conn;
    private $table_name = "student";

    public function __construct()
    {
        $this->conn = Database::getConnection();
    }

    public function findStudentByEmail($email)
    {
        $query = "SELECT * FROM " . $this->table_name . " WHERE " . StudentEntity::EMAIL . " = :email LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":email", $email);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // Aquí irían otros métodos del CRUD, como create, getById, etc.
}