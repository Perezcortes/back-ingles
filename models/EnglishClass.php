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
    
    /**
     * Crea un nuevo registro en la tabla 'english_class'.
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
     * Obtiene todas las clases de inglés activas (no eliminadas lógicamente).
     * @return array Un array de objetos o un array vacío.
     */
    public function getAll()
    {
        $query = "SELECT * FROM " . $this->table_name . " WHERE " . EnglishClassEntity::DELETED_AT . " IS NULL ORDER BY " . EnglishClassEntity::ID . " ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene una clase por su ID.
     * @param int $id ID de la clase.
     * @return array|false
     */
    public function getById($id)
    {
        $query = "SELECT * FROM " . $this->table_name . " WHERE " . EnglishClassEntity::ID . " = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Busca una clase activa por su nombre de grupo (para evitar duplicados).
     * @param string $nameGroup Nombre del grupo.
     * @return array|false
     */
    public function findByName($nameGroup)
    {
        $query = "SELECT * FROM " . $this->table_name . 
                 " WHERE " . EnglishClassEntity::NAME_GROUP . " = :name_group AND " . 
                 EnglishClassEntity::DELETED_AT . " IS NULL LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":name_group", $nameGroup);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtiene registros por ID de profesor.
     */
    public function getByProfessorId($id_professor)
    {
        $query = "SELECT * FROM " . $this->table_name . " WHERE " . EnglishClassEntity::ID_PROFESSOR . " = :id_professor AND " . EnglishClassEntity::DELETED_AT . " IS NULL";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_professor', $id_professor, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene registros por ID de nivel.
     */
    public function getByLevelId($id_level)
    {
        $query = "SELECT * FROM " . $this->table_name . " WHERE " . EnglishClassEntity::ID_LEVEL . " = :id_level AND " . EnglishClassEntity::DELETED_AT . " IS NULL";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_level', $id_level, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Actualiza los datos de una clase existente por su ID.
     * @param int $id ID de la clase a actualizar.
     * @param array $data Los datos a modificar.
     * @return bool True en éxito, false en fallo.
     */
    public function updateById($id, $data)
    {
        $setClauses = [];
        // Añadir la marca de tiempo de actualización
        $data[EnglishClassEntity::UPDATED_AT] = date('Y-m-d H:i:s');
        
        foreach ($data as $key => $value) {
            // Construye la parte SET de la consulta (ej: name_group = :name_group)
            $setClauses[] = "{$key} = :{$key}";
        }

        if (empty($setClauses)) {
            return false; // No hay nada que actualizar
        }

        // Construye la consulta SQL completa
        $query = "UPDATE " . $this->table_name . " SET " . implode(', ', $setClauses) . " WHERE " . EnglishClassEntity::ID . " = :id";

        $stmt = $this->conn->prepare($query);

        // Asigna los valores a los parámetros
        foreach ($data as $key => &$value) {
            $stmt->bindParam(":" . $key, $value);
        }
        // Asigna el ID
        $stmt->bindParam(":id", $id);

        return $stmt->execute();
    }

    
    /**
     * Realiza el borrado lógico (soft delete) de una clase.
     * @param int $id ID de la clase a eliminar.
     * @return bool True en éxito, false en fallo.
     */
    public function deleteById($id)
    {
        $query = "UPDATE " . $this->table_name . " SET " . EnglishClassEntity::DELETED_AT . " = NOW() WHERE " . EnglishClassEntity::ID . " = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        return $stmt->execute();
    }
    
    /**
     * Obtiene el ID del último registro insertado.
     * Es útil para el controlador.
     */
    public function lastInsertId() {
        return $this->conn->lastInsertId();
    }
}