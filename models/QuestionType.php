<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../entities/QuestionType.php';

use App\Entities\QuestionType as QuestionTypeEntity;

class QuestionType
{
    private $conn;
    private $table_name = "question_type";

    public function __construct()
    {
        $this->conn = Database::getConnection();
    }

    /**
     * Obtiene todos los tipos de pregunta activos.
     */
    public function getAll()
    {
        $query = "SELECT * FROM " . $this->table_name . " WHERE " . QuestionTypeEntity::DELETED_AT . " IS NULL ORDER BY " . QuestionTypeEntity::ID . " ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene un tipo de pregunta por su ID.
     */
    public function getById($id)
    {
        $query = "SELECT * FROM " . $this->table_name . " WHERE " . QuestionTypeEntity::ID . " = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Busca por nombre (para validar unicidad).
     */
    public function findByName($questionName)
    {
        $query = "SELECT * FROM " . $this->table_name . 
                 " WHERE " . QuestionTypeEntity::QUESTION_NAME . " = :question_name AND " . 
                 QuestionTypeEntity::DELETED_AT . " IS NULL LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":question_name", $questionName);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Crea un nuevo registro.
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
     * Actualiza un registro existente.
     */
    public function updateById($id, $data)
    {
        $setClauses = [];
        $data[QuestionTypeEntity::UPDATED_AT] = date('Y-m-d H:i:s');
        
        foreach ($data as $key => $value) {
            $setClauses[] = "{$key} = :{$key}";
        }

        if (empty($setClauses)) {
            return false;
        }

        $query = "UPDATE " . $this->table_name . " SET " . implode(', ', $setClauses) . " WHERE " . QuestionTypeEntity::ID . " = :id";

        $stmt = $this->conn->prepare($query);

        foreach ($data as $key => &$value) {
            $stmt->bindParam(":" . $key, $value);
        }
        $stmt->bindParam(":id", $id);

        return $stmt->execute();
    }

    /**
     * Realiza el borrado lógico.
     */
    public function deleteById($id)
    {
        $query = "UPDATE " . $this->table_name . " SET " . QuestionTypeEntity::DELETED_AT . " = NOW() WHERE " . QuestionTypeEntity::ID . " = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        return $stmt->execute();
    }
}