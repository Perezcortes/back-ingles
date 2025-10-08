<?php
// models/Student.php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../entities/Student.php';

use App\Entities\Student as StudentEntity;
use PDO;

class Student
{
    private $conn;
    private $table_name = "student";

    public function __construct()
    {
        $this->conn = Database::getConnection();
    }
    
    /**
     * Crea un nuevo registro en la tabla `student`.
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
     * Actualiza un estudiante por ID.
     * @param int $id El ID del estudiante a actualizar.
     * @param array $data Los datos a actualizar.
     * @return bool True si la actualización fue exitosa, false de lo contrario.
     */
    public function updateById($id, $data)
    {
        $setClauses = [];
        // Se añade la marca de tiempo de actualización
        $data[StudentEntity::UPDATED_AT] = date('Y-m-d H:i:s'); 
        
        foreach ($data as $key => $value) {
            $setClauses[] = "{$key} = :{$key}";
        }
        $query = "UPDATE " . $this->table_name . " SET " . implode(', ', $setClauses) . " WHERE " . StudentEntity::ID . " = :id";
        
        $stmt = $this->conn->prepare($query);
        
        foreach ($data as $key => &$value) {
            $stmt->bindParam(":" . $key, $value);
        }
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        
        return $stmt->execute();
    }

    /**
     * Obtiene todos los registros de estudiantes no eliminados lógicamente.
     * @return array Un array de objetos o un array vacío.
     */
    public function getAll()
    {
        $query = "SELECT * FROM " . $this->table_name . " WHERE " . StudentEntity::DELETED_AT . " IS NULL ORDER BY " . StudentEntity::ID . " ASC"; 
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtiene un estudiante por su ID, excluyendo los eliminados lógicamente.
     * @param int $id El ID del estudiante.
     * @return array|false Un array asociativo del estudiante o false si no se encuentra.
     */
    public function getById($id)
    {
        $query = "SELECT * FROM " . $this->table_name . 
            " WHERE " . StudentEntity::ID . " = :id" .
            " AND " . StudentEntity::DELETED_AT . " IS NULL LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Encuentra un estudiante activo por su dirección de email.
     * @param string $email El correo electrónico del estudiante a buscar.
     * @return array|false Devuelve el array de estudiante o false si no se encuentra.
     */
    public function findStudentByEmail($email)
    {
        $query = "SELECT * FROM " . $this->table_name . 
            " WHERE " . StudentEntity::EMAIL . " = :email " .
            " AND " . StudentEntity::DELETED_AT . " IS NULL " . 
            " LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Encuentra un estudiante activo por su matrícula.
     * @param string $matricula La matrícula del estudiante a buscar.
     * @return array|false Devuelve el array de estudiante o false si no se encuentra.
     */
    public function findStudentByMatricula($matricula)
    {
        $query = "SELECT * FROM " . $this->table_name . 
            " WHERE " . StudentEntity::MATRICULA . " = :matricula" .
            " AND " . StudentEntity::DELETED_AT . " IS NULL LIMIT 1";
            
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':matricula', $matricula);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Realiza un borrado lógico de un registro (soft delete).
     * @param int $id El ID del estudiante a eliminar.
     * @return bool True si la eliminación fue exitosa, false de lo contrario.
     */
    public function deleteById($id)
    {
        $query = "UPDATE " . $this->table_name . " SET " . StudentEntity::DELETED_AT . " = NOW() WHERE " . StudentEntity::ID . " = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        
        return $stmt->execute();
    }

    /**
     * Restaura un registro por ID (quita la marca de soft delete).
     * @param int $id El ID del registro a restaurar.
     * @return bool True si la restauración fue exitosa, false de lo contrario.
     */
    public function restoreById($id)
    {
        $query = "UPDATE " . $this->table_name . " SET " . StudentEntity::DELETED_AT . " = NULL WHERE " . StudentEntity::ID . " = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * Realiza un borrado lógico masivo (soft delete) para todos los estudiantes activos.
     * @return bool True si la eliminación fue exitosa, false de lo contrario.
     */
    public function deleteAll()
    {
        $query = "UPDATE " . $this->table_name . " SET " . StudentEntity::DELETED_AT . " = NOW() WHERE " . StudentEntity::DELETED_AT . " IS NULL";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute();
    }
    
    /**
     * Elimina un registro permanentemente (hard delete).
     * @param int $id El ID del registro a eliminar permanentemente.
     * @return bool True si la eliminación fue exitosa, false de lo contrario.
     */
    public function deletePermanentById($id)
    {
        $query = "DELETE FROM " . $this->table_name . " WHERE " . StudentEntity::ID . " = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
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
