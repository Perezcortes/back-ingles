<?php

// models/SessionUser.php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../entities/SessionUser.php';

use App\Entities\SessionUser as SessionUserEntity;
use PDO;
use PDOException;

class SessionUser
{
    private $conn;
    private $table_name;

    public function __construct()
    {
        //$database = new Database(); Esto evita el error de intentar acceder a un constructor privado
        $this->conn = Database::getConnection(); //Cambie el constructor para que use el método estático directamente.
        $this->table_name = "sesion_user";
    }

    /**
     * Crea un nuevo registro de sesión de usuario en la base de datos.
     *
     * @param array $data Los datos para crear la sesión (debe contener 'ip' e 'id_user').
     * @return bool Retorna true si la inserción fue exitosa.
     * @throws PDOException Si la inserción falla.
     */
    public function createSession($data)
    {
        try {
            $query = "INSERT INTO " . $this->table_name . " (" . SessionUserEntity::IP . ", " . SessionUserEntity::ID_USER . ") VALUES (:ip, :id_user)";
            $stmt = $this->conn->prepare($query);

            $stmt->bindParam(':ip', $data[SessionUserEntity::IP]);
            $stmt->bindParam(':id_user', $data[SessionUserEntity::ID_USER], PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            // Manejo de errores más específico
            error_log("Error al crear sesión: " . $e->getMessage());
            return false;
        }
    }
}