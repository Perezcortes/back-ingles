<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../entities/Level.php'; // Incluir la entidad

use App\Entities\Level as LevelEntity; 
use PDO;

class Level {
    private $conn;
    private $table_name = "level";

    public function __construct() {
        $this->conn = Database::getConnection();
    }

    public function obtenerTodos() {
        $consulta = $this->conn->query("SELECT * FROM level ORDER BY id ASC");
        return $consulta->fetchAll();
    }

    public function getById($id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE " . LevelEntity::ID . " = :id AND " . LevelEntity::DELETED_AT . " IS NULL LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function crear($datos) {
        $campos = array_keys($datos);
        $placeholders = array_fill(0, count($datos), '?');
        $valores = array_values($datos);
    
        $sql = "INSERT INTO level (" . implode(',', $campos) . ") VALUES (" . implode(',', $placeholders) . ")";
        $consulta = $this->conn->prepare($sql);
        return $consulta->execute($valores);
    }

    public function actualizarPorId($id, $datos) {
        $campos = [];
        $valores = [];
    
        foreach ($datos as $campo => $valor) {
            $campos[] = "$campo = ?";
            $valores[] = $valor;
        }
    
        $valores[] = $id; // El ID va al final para el WHERE
    
        $sql = "UPDATE level SET " . implode(", ", $campos) . " WHERE id = ?";
        $consulta = $this->conn->prepare($sql);
        return $consulta->execute($valores);
    }
    

    public function eliminarPorId($id) {
        $consulta = $this->conn->prepare("DELETE FROM level WHERE id = ?");
        return $consulta->execute([$id]);
    }
}
