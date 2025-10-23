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
     * Método para encontrar un user activo por su dirección de email.
     * @param string $email El correo electrónico del user a buscar.
     * @return array|false Devuelve el array de user o false si no se encuentra.
     * @throws \PDOException Si ocurre un error durante la ejecución de la consulta SQL.
     */
    public function findUserByEmail($email)
    {
        // Consulta base
        $query = "SELECT * FROM " . $this->table_name .
            " WHERE " . UserEntity::EMAIL . " = :email " .
            " AND " . UserEntity::DELETED_AT . " IS NULL " . // Aseguramos que retorne un estudiante activo.
            " LIMIT 1";
            
        // Si prepare() falla (ej. error de sintaxis) lanza PDOException
        $stmt = $this->conn->prepare($query);

        // Vinculación de Parámetros: Enlaza los datos de entrada a los marcadores de posición.
        $stmt->bindParam(":email", $email);

        // Si execute() falla (ej. conexión perdida), lanza PDOException
        $stmt->execute();

        // Devuelve el array de user o 'false' si no se encuentra
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