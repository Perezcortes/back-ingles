<?php

// models/EnglishClass.php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../entities/EnglishClass.php';

use App\Entities\EnglishClass as EnglishClassEntity;
use PDO;

class EnglishClass
{
    private $conn;
    private $table_name = "english_class";

    public function __construct()
    {
        $this->conn = Database::getConnection();
    }

    public function create($data)
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = ":" . implode(', :', array_keys($data));
        $query = "INSERT INTO " . $this->table_name . " ({$columns}) VALUES ({$placeholders})";
        
        $stmt = $this->conn->prepare($query);
        
        foreach ($data as $key => &$value) {
            $stmt->bindParam(":" . $key, $value);
        }
        
        return $stmt->execute();
    }

    public function getAll()
    {
        $query = "SELECT * FROM " . $this->table_name . " WHERE " . EnglishClassEntity::DELETED_AT . " IS NULL ORDER BY " . EnglishClassEntity::ID . " ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id)
    {
        $query = "SELECT * FROM " . $this->table_name . " WHERE " . EnglishClassEntity::ID . " = :id AND " . EnglishClassEntity::DELETED_AT . " IS NULL LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getByProfessorId($id_professor)
    {
        $query = "SELECT * FROM " . $this->table_name . " WHERE " . EnglishClassEntity::ID_PROFESSOR . " = :id_professor AND " . EnglishClassEntity::DELETED_AT . " IS NULL";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_professor', $id_professor, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getByLevelId($id_level)
    {
        $query = "SELECT * FROM " . $this->table_name . " WHERE " . EnglishClassEntity::ID_LEVEL . " = :id_level AND " . EnglishClassEntity::DELETED_AT . " IS NULL";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_level', $id_level, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateById($id, $data)
    {
        $setClauses = [];
        $data[EnglishClassEntity::UPDATED_AT] = date('Y-m-d H:i:s');
        foreach ($data as $key => $value) {
            $setClauses[] = "{$key} = :{$key}";
        }
        $query = "UPDATE " . $this->table_name . " SET " . implode(', ', $setClauses) . " WHERE " . EnglishClassEntity::ID . " = :id";
        
        $stmt = $this->conn->prepare($query);
        
        foreach ($data as $key => &$value) {
            $stmt->bindParam(":" . $key, $value);
        }
        $stmt->bindParam(":id", $id);
        
        return $stmt->execute();
    }

    public function restoreById($id)
    {
        $query = "UPDATE " . $this->table_name . " SET " . EnglishClassEntity::DELETED_AT . " = NULL WHERE " . EnglishClassEntity::ID . " = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function deleteById($id)
    {
        $query = "UPDATE " . $this->table_name . " SET " . EnglishClassEntity::DELETED_AT . " = NOW() WHERE " . EnglishClassEntity::ID . " = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function deletePermanentById($id)
    {
        $query = "DELETE FROM " . $this->table_name . " WHERE " . EnglishClassEntity::ID . " = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function truncateTable()
    {
        $query = "TRUNCATE TABLE " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        return $stmt->execute();
    }
}