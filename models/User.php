<?php

// models/User.php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../entities/User.php';

use App\Entities\User as UserEntity;
use PDO;

class User
{
    private $conn;
    private $table_name = "user";

    public function __construct()
    {
        //$database = new Database(); Esto evita el error de intentar acceder a un constructor privado
        $this->conn = Database::getConnection(); //Cambie el constructor para que use el método estático directamente.
    }

    public function getAll()
    {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY " . UserEntity::ID . " ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id)
    {
        $query = "SELECT * FROM " . $this->table_name . " WHERE " . UserEntity::ID . " = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // Método actualizado para el login de usuarios
    public function findUserByEmail($email)
    {
        $query = "SELECT * FROM " . $this->table_name . " WHERE " . UserEntity::EMAIL . " = :email LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":email", $email);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
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

    public function updateById($id, $data)
    {
        $setClauses = [];
        $data[UserEntity::UPDATED_AT] = date('Y-m-d H:i:s');
        foreach ($data as $key => $value) {
            $setClauses[] = "{$key} = :{$key}";
        }
        
        $query = "UPDATE " . $this->table_name . " SET " . implode(', ', $setClauses) . " WHERE " . UserEntity::ID . " = :id";
        
        $stmt = $this->conn->prepare($query);
        
        foreach ($data as $key => &$value) {
            $stmt->bindParam(":" . $key, $value);
        }
        $stmt->bindParam(":id", $id);

        return $stmt->execute();
    }

    public function deleteById($id)
    {
        $query = "UPDATE " . $this->table_name . " SET " . UserEntity::DELETED_AT . " = NOW() WHERE " . UserEntity::ID . " = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        return $stmt->execute();
    }
    
    // Método para eliminar de forma permanente
    public function deletePermanent($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE " . UserEntity::ID . " = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
    
    // Método para restaurar un usuario (soft delete)
    public function restore($id) {
        $query = "UPDATE " . $this->table_name . " SET " . UserEntity::DELETED_AT . " = NULL WHERE " . UserEntity::ID . " = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}