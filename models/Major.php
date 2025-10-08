<?php
// models/Major.php

/**
 * @package App\Models
 *
 * Modelo para gestionar la tabla `major` (carreras) de la base de datos.
 * Incluye operaciones CRUD con soporte para borrado lógico (soft delete).
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../entities/Major.php';

use App\Entities\Major as MajorEntity;
use PDO;

class Major
{
    private $conn;
    private $table_name = "major";

    public function __construct()
    {
        $this->conn = Database::getConnection();
    }

    /**
     * Crea un nuevo registro en la tabla `major`.
     * @param array $data Los datos a insertar.
     * @return int|bool El ID del nuevo registro o false si falla.
     */
    public function create($data)
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = ":" . implode(', :', array_keys($data));
        $query = "INSERT INTO " . $this->table_name . " ({$columns}) VALUES ({$placeholders})";
        
        $stmt = $this->conn->prepare($query);
        
        // Uso de bindParam para enlazar variables de forma segura
        foreach ($data as $key => &$value) {
            $stmt->bindParam(":" . $key, $value);
        }
        
        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        
        return false;
    }

    /**
     * Obtiene todos los registros no eliminados lógicamente.
     * @return array Un array de objetos o un array vacío.
     */
    public function getAll()
    {
        // Se verifica que DELETED_AT sea NULL para excluir los soft deleted
        $query = "SELECT * FROM " . $this->table_name . " WHERE " . MajorEntity::DELETED_AT . " IS NULL ORDER BY " . MajorEntity::ID . " ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene un registro por ID.
     * @param int $id El ID del registro.
     * @return array|bool Un array asociativo del registro o false si no se encuentra.
     */
    public function getById($id)
    {
        $query = "SELECT * FROM " . $this->table_name . " WHERE " . MajorEntity::ID . " = :id AND " . MajorEntity::DELETED_AT . " IS NULL LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Actualiza un registro por ID.
     * @param int $id El ID del registro a actualizar.
     * @param array $data Los datos a actualizar.
     * @return bool True si la actualización fue exitosa, false de lo contrario.
     */
    public function updateById($id, $data)
    {
        $setClauses = [];
        // Se añade la marca de tiempo de actualización
        $data[MajorEntity::UPDATED_AT] = date('Y-m-d H:i:s');
        
        foreach ($data as $key => $value) {
            $setClauses[] = "{$key} = :{$key}";
        }
        $query = "UPDATE " . $this->table_name . " SET " . implode(', ', $setClauses) . " WHERE " . MajorEntity::ID . " = :id";
        
        $stmt = $this->conn->prepare($query);
        
        // Uso de bindParam
        foreach ($data as $key => &$value) {
            $stmt->bindParam(":" . $key, $value);
        }
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        
        return $stmt->execute();
    }
    
    /**
     * Restaura un registro por ID (quita la marca de soft delete).
     * @param int $id El ID del registro a restaurar.
     * @return bool True si la restauración fue exitosa, false de lo contrario.
     */
    public function restoreById($id)
    {
        $query = "UPDATE " . $this->table_name . " SET " . MajorEntity::DELETED_AT . " = NULL WHERE " . MajorEntity::ID . " = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * Realiza un borrado lógico de un registro (soft delete).
     * @param int $id El ID del registro a eliminar.
     * @return bool True si la eliminación fue exitosa, false de lo contrario.
     */
    public function deleteById($id)
    {
        $query = "UPDATE " . $this->table_name . " SET " . MajorEntity::DELETED_AT . " = NOW() WHERE " . MajorEntity::ID . " = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * Elimina un registro permanentemente (hard delete).
     * @param int $id El ID del registro a eliminar permanentemente.
     * @return bool True si la eliminación fue exitosa, false de lo contrario.
     */
    public function deletePermanentById($id)
    {
        $query = "DELETE FROM " . $this->table_name . " WHERE " . MajorEntity::ID . " = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * Vacía la tabla permanentemente.
     * @return bool True si la tabla fue vaciada, false de lo contrario.
     */
    public function truncateTable()
    {
        $query = "TRUNCATE TABLE " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        return $stmt->execute();
    }
    
    /**
     * Obtiene el ID del último registro insertado.
     * @return int El ID del último registro.
     */
    public function lastInsertId() {
        return $this->conn->lastInsertId();
    }
}
