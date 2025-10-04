<?php
// models/Student.php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../entities/Student.php';

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
    
    // Método para crear un nuevo estudiante
    public function create($data)
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = ":" . implode(', :', array_keys($data));
        $query = "INSERT INTO " . $this->table_name . " ({$columns}) VALUES ({$placeholders})";
        
        $stmt = $this->conn->prepare($query);
        
        foreach ($data as $key => &$value) {
            $stmt->bindParam(":" . $key, $value);
        }

        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        
        return false;
    }

    public function getAll()
    {
        $query = "SELECT * FROM " . $this->table_name . " WHERE deleted_at IS NULL ORDER BY id ASC"; // Asegurarse de no incluir registros eliminados
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Método para encontrar un estudiante por su ID
    public function getById($id) // <-- Nombre del método corregido
    {
        $query = "SELECT * FROM " . $this->table_name . " WHERE " . StudentEntity::ID . " = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Método para encontrar un estudiante por su email
    public function findStudentByEmail($email)
    {
        $query = "SELECT * FROM " . $this->table_name . " WHERE " . StudentEntity::EMAIL . " = :email LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // Método para encontrar un estudiante por su matrícula
    public function findStudentByMatricula($matricula)
    {
        $query = "SELECT * FROM " . $this->table_name . " WHERE " . StudentEntity::MATRICULA . " = :matricula LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':matricula', $matricula);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Aquí irían el resto de los métodos (getAll, update, delete, etc.)
}