<?php
// models/Student.php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../entities/Student.php';

use App\Entities\Student as StudentEntity;

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

    // Método para actualizar un estudiante por ID
    public function updateById($id, $data) {
        $setClauses = [];
        foreach ($data as $key => $value) {
            $setClauses[] = "{$key} = :{$key}";
        }
        $query = "UPDATE " . $this->table_name . " SET " . implode(', ', $setClauses) . " WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        
        foreach ($data as $key => &$value) {
            $stmt->bindParam(":" . $key, $value);
        }

        return $stmt->execute();
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

    /**
     * Método para encontrar un estudiante activo por su dirección de email.
     * @param string $email El correo electrónico del estudiante a buscar.
     * @return array|false Devuelve el array de estudiante o false si no se encuentra.
     * @throws \PDOException Si ocurre un error durante la ejecución de la consulta SQL.
     */
    public function findStudentByEmail($email)
    {
        // Consulta base
        $query = "SELECT * FROM " . $this->table_name . 
            " WHERE " . StudentEntity::EMAIL . " = :email " .
            " AND " . StudentEntity::DELETED_AT . " IS NULL " . // Aseguramos que retorne un estudiante activo.
            " LIMIT 1";

        // Si prepare() falla (ej. error de sintaxis) lanza PDOException
        $stmt = $this->conn->prepare($query);

        // Vinculación de Parámetros: Enlaza los datos de entrada a los marcadores de posición.
        $stmt->bindParam(':email', $email);

        // Si execute() falla (ej. conexión perdida), lanza PDOException
        $stmt->execute();

        // Devuelve el array de estudiante o 'false' si no se encuentra
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

    // Método para realizar un borrado lógico (soft delete)
    public function deleteById($id) {
        $query = "UPDATE " . $this->table_name . " SET deleted_at = NOW() WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        
        return $stmt->execute();
    }

    // Método para realizar un borrado lógico masivo (soft delete)
    public function deleteAll()
    {
        $query = "UPDATE " . $this->table_name . " SET deleted_at = NOW() WHERE deleted_at IS NULL";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute();
    }

    // Aquí irían el resto de los métodos (getAll, update, delete, etc.)
}