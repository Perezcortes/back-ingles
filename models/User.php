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

    /**
     * Actualiza un registro de usuario por su ID.
     * @param int $id El ID del usuario a actualizar.
     * @param array $data Los datos a actualizar.
     * @return bool True si la actualización fue exitosa, false en caso contrario.
     */
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

    /**
     * Anula el ID de un coordinador de nivel en la tabla 'level' si deja de ser coordinador.
     * @param int $coordinatorId El ID del coordinador a anular.
     * @return bool True en éxito, false en fallo.
     */
    public function nullifyLevelCoordinator($coordinatorId)
    {
        // tabla 'level' y columna 'id_level_coordinator'
        $query = "UPDATE level SET id_level_coordinator = NULL WHERE id_level_coordinator = :coordinatorId";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':coordinatorId', $coordinatorId, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * Anula el ID de un profesor en la tabla 'english_class' si deja de ser profesor.
     * @param int $professorId El ID del profesor a anular.
     * @return bool True en éxito, false en fallo.
     */
    public function nullifyEnglishClassProfessor($professorId)
    {
        // tabla 'english_class' y columna 'id_professor'
        $query = "UPDATE english_class SET id_professor = NULL WHERE id_professor = :professorId";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':professorId', $professorId, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * Realiza el borrado lógico (soft delete) de un usuario.
     * @param int $id ID del usuario a eliminar.
     * @return bool True en éxito, false en fallo.
     */
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