<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../entities/Major.php';

use App\Entities\Major as MajorEntity;
use PDO;

class Major {
    private $conn;
    private $table_name = "major"; // <-- Propiedad 'table_name' añadida

    public function __construct() {
        $this->conn = Database::getConnection();
    }

    public function crear($datos) {
        $campos = array_keys($datos);
        $placeholders = array_fill(0, count($datos), '?');
        $valores = array_values($datos);
    
        $sql = "INSERT INTO major (" . implode(',', $campos) . ") VALUES (" . implode(',', $placeholders) . ")";
        $consulta = $this->conn->prepare($sql);
        return $consulta->execute($valores);
    }

    public function obtenerTodos() {
        $consulta = $this->conn->query("SELECT * FROM major WHERE is_deleted = FALSE ORDER BY id ASC");
        return $consulta->fetchAll();
    }

    public function getById($id) { // <-- Nombre del método corregido
        $query = "SELECT * FROM " . $this->table_name . " WHERE " . MajorEntity::ID . " = :id AND " . MajorEntity::DELETED_AT . " IS NULL LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function actualizarPorId($id, $datos) {
        $campos = [];
        $valores = [];
    
        foreach ($datos as $campo => $valor) {
            $campos[] = "$campo = ?";
            $valores[] = $valor;
        }
    
        $valores[] = $id; 
    
        $sql = "UPDATE major SET " . implode(", ", $campos) . " WHERE id = ?";
        $consulta = $this->conn->prepare($sql);
        return $consulta->execute($valores);
    }
    // RESTORE - Restaurar un registro por ID
    public function restaurarPorId($id) {
        $consulta = $this->conn->prepare("UPDATE major SET is_deleted = FALSE, deleted_at = NULL WHERE id = ?");
        return $consulta->execute([$id]);
    }

    // DELETE - Parte del soft delete (marcar como eliminado un elemento)
    public function eliminarPorId($id) {
        $consulta = $this->conn->prepare("UPDATE major SET is_deleted = TRUE, deleted_at = NOW() WHERE id = ?");
        return $consulta->execute([$id]);
    }

    // DELETE - es del Hard delete (eliminar permanentemente un elemento)
    public function eliminarPermanentePorId($id) {
        $consulta = $this->conn->prepare("DELETE FROM major WHERE id = ?");
        return $consulta->execute([$id]);
    }

    // DELETE - Hard delete para vaciar la tabla
    public function vaciarTabla() {
        $consulta = $this->conn->query("TRUNCATE TABLE major");
        return $consulta;
    }
}