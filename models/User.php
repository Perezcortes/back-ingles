<?php

// models/User.php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../entities/User.php';

use App\Entities\User as UserEntity;

class User
{
    private $conn;
    private $table_name = "user";

    public function __construct()
    {
        $this->conn = Database::getConnection();
    }

    /**
     * Obtiene todos los registros de usuarios activos (no eliminados lógicamente).
     * @return array Un array de objetos o un array vacío.
     */
    public function getAll()
    {
        $query = "SELECT * FROM " . $this->table_name . " WHERE " . UserEntity::DELETED_AT . " IS NULL ORDER BY " . UserEntity::ID . " ASC";
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

    /**
     * Encuentra un usuario activo por su dirección de email.
     * @param string $email El correo electrónico del usuario a buscar.
     * @return array|false Devuelve el array de usuario o false si no se encuentra.
     */
    public function findUserByEmail($email)
    {
        // Consulta base: busca por email y verifica que no esté eliminado
        $query = "SELECT * FROM " . $this->table_name .
            " WHERE " . UserEntity::EMAIL . " = :email " .
            " AND " . UserEntity::DELETED_AT . " IS NULL " . 
            " LIMIT 1";
            
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":email", $email);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Crea un nuevo registro en la tabla `user`.
     * @param array $data Los datos a insertar.
     * @return int|bool El ID del nuevo registro o false si falla.
     */
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
            return $this->conn->lastInsertId(); // Devolvemos el ID
        }

        return false;
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
    public function deletePermanent($id)
    {
        $query = "DELETE FROM " . $this->table_name . " WHERE " . UserEntity::ID . " = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    // Método para restaurar un usuario (soft delete)
    public function restore($id)
    {
        $query = "UPDATE " . $this->table_name . " SET " . UserEntity::DELETED_AT . " = NULL WHERE " . UserEntity::ID . " = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}