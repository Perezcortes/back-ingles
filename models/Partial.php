<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../entities/Partial.php';

use App\Entities\Partial as PartialEntity;

class Partial
{
    private $conn;
    private $table_name = "partial";

    public function __construct()
    {
        $this->conn = Database::getConnection();
    }

    /**
     * Obtiene todos los parciales activos (no eliminados lógicamente).
     * @return array Un array de objetos o un array vacío.
     */
    public function getAll()
    {
        // Consulta para obtener solo los registros donde deleted_at es NULL
        $query = "SELECT * FROM " . $this->table_name . " WHERE " . PartialEntity::DELETED_AT . " IS NULL ORDER BY " . PartialEntity::ID . " ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene un parcial por su ID.
     * @param int $id ID del parcial.
     * @return array|false
     */
    public function getById($id)
    {
        $query = "SELECT * FROM " . $this->table_name . " WHERE " . PartialEntity::ID . " = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Busca un parcial activo por su nombre para verificar unicidad.
     * @param string $partialName Nombre del parcial.
     * @return array|false
     */
    public function findByName($partialName)
    {
        $query = "SELECT * FROM " . $this->table_name . 
                 " WHERE " . PartialEntity::PARTIAL_NAME . " = :partial_name AND " . 
                 PartialEntity::DELETED_AT . " IS NULL LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":partial_name", $partialName);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Crea un nuevo registro en la tabla 'partial'.
     * @param array $data Datos a insertar.
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
     * Actualiza los datos de un parcial existente por su ID.
     * @param int $id ID del parcial a actualizar.
     * @param array $data Los datos a modificar.
     * @return bool True en éxito, false en fallo.
     */
    public function updateById($id, $data)
    {
        $setClauses = [];
        $data[PartialEntity::UPDATED_AT] = date('Y-m-d H:i:s');
        
        foreach ($data as $key => $value) {
            $setClauses[] = "{$key} = :{$key}";
        }

        if (empty($setClauses)) {
            return false;
        }

        $query = "UPDATE " . $this->table_name . " SET " . implode(', ', $setClauses) . " WHERE " . PartialEntity::ID . " = :id";

        $stmt = $this->conn->prepare($query);

        foreach ($data as $key => &$value) {
            $stmt->bindParam(":" . $key, $value);
        }
        $stmt->bindParam(":id", $id);

        return $stmt->execute();
    }

    /**
     * Realiza el borrado lógico (soft delete) de un parcial.
     * @param int $id ID del parcial a eliminar.
     * @return bool True en éxito, false en fallo.
     */
    public function deleteById($id)
    {
        $query = "UPDATE " . $this->table_name . " SET " . PartialEntity::DELETED_AT . " = NOW() WHERE " . PartialEntity::ID . " = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        return $stmt->execute();
    }
}