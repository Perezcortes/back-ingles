<?php

// models/Level.php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../entities/Level.php';

use App\Entities\Level as LevelEntity;
use PDO;

class Level
{
    private $conn;
    private $table_name = "level";

    public function __construct()
    {
        $this->conn = Database::getConnection();
    }

    /**
     * Crea un nuevo registro en la tabla `level`.
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
        $query = "SELECT * FROM " . $this->table_name . " WHERE " . LevelEntity::DELETED_AT . " IS NULL ORDER BY " . LevelEntity::ID . " ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene un nivel por su ID.
     * @param int $id ID del nivel.
     * @return array|false
     */
    public function getById($id)
    {
        $query = "SELECT * FROM " . $this->table_name . " WHERE " . LevelEntity::ID . " = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Busca un nivel activo por su nombre para verificar unicidad.
     * @param string $levelName Nombre del nivel.
     * @return array|false
     */
    public function findByName($levelName)
    {
        $query = "SELECT * FROM " . $this->table_name . 
                 " WHERE " . LevelEntity::LEVEL_NAME . " = :level_name AND " . 
                 LevelEntity::DELETED_AT . " IS NULL LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":level_name", $levelName);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Actualiza los datos de un nivel existente por su ID.
     * @param int $id ID del nivel a actualizar.
     * @param array $data Los datos a modificar.
     * @return bool True en éxito, false en fallo.
     */
    public function updateById($id, $data)
    {
        $setClauses = [];
        // Añadir la marca de tiempo de actualización
        $data[LevelEntity::UPDATED_AT] = date('Y-m-d H:i:s'); 
        
        foreach ($data as $key => $value) {
            // Asegura que los valores nulos se mapeen correctamente a la cláusula SET
            $setClauses[] = "{$key} = :{$key}";
        }

        if (empty($setClauses)) {
            return false; // No hay nada que actualizar
        }

        $query = "UPDATE " . $this->table_name . " SET " . implode(', ', $setClauses) . " WHERE " . LevelEntity::ID . " = :id";

        $stmt = $this->conn->prepare($query);

        foreach ($data as $key => &$value) {
            // Bindear todos los parámetros, incluyendo el de la marca de tiempo
            $stmt->bindParam(":" . $key, $value);
        }
        $stmt->bindParam(":id", $id);

        return $stmt->execute();
    }
    
    /**
     * Realiza el borrado lógico (soft delete) de un nivel.
     * @param int $id ID del nivel a eliminar.
     * @return bool True en éxito, false en fallo.
     */
    public function deleteById($id)
    {
        $query = "UPDATE " . $this->table_name . " SET " . LevelEntity::DELETED_AT . " = NOW() WHERE " . LevelEntity::ID . " = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        return $stmt->execute();
    }

    /**
     * Restaura un registro por ID (soft delete).
     * @param int $id El ID del registro a restaurar.
     * @return bool True si la restauración fue exitosa, false de lo contrario.
     */
    public function restoreById($id)
    {
        $query = "UPDATE " . $this->table_name . " SET " . LevelEntity::DELETED_AT . " = NULL WHERE " . LevelEntity::ID . " = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
    
    /**
     * Elimina un registro permanentemente.
     * @param int $id El ID del registro a eliminar permanentemente.
     * @return bool True si la eliminación fue exitosa, false de lo contrario.
     */
    public function deletePermanentById($id)
    {
        $query = "DELETE FROM " . $this->table_name . " WHERE " . LevelEntity::ID . " = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
    
    /**
     * Vacía la tabla.
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