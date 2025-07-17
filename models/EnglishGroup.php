<?php

require_once __DIR__ . '/../config/database.php';

class EnglishGroup {
    private $conexion;

    public function __construct() {
        $this->conexion = Database::getConnection();
    }

    public function crear($datos) {
        $campos = array_keys($datos);
        $placeholders = array_fill(0, count($datos), '?');
        $valores = array_values($datos);
    
        $sql = "INSERT INTO english_group (" . implode(',', $campos) . ") VALUES (" . implode(',', $placeholders) . ")";
        $consulta = $this->conexion->prepare($sql);
        return $consulta->execute($valores);
    }

    public function obtenerTodos() {
        $consulta = $this->conexion->query("SELECT * FROM english_group WHERE is_deleted = FALSE ORDER BY id ASC");
        return $consulta->fetchAll();
    }

    public function obtenerPorId($id) {
        $consulta = $this->conexion->prepare("SELECT * FROM english_group WHERE id = ? AND is_deleted = FALSE");
        $consulta->execute([$id]);
        return $consulta->fetch();
    }

    public function obtenerPorIdProfessor($id_professor) {
        $consulta = $this->conexion->prepare("SELECT * FROM english_group WHERE id_professor = ? AND is_deleted = FALSE");
        $consulta->execute([$id_professor]);
        return $consulta->fetchAll();
    }

    public function obtenerPorIdLevel($id_level) {
        $consulta = $this->conexion->prepare("SELECT * FROM english_group WHERE id_level = ? AND is_deleted = FALSE");
        $consulta->execute([$id_level]);
        return $consulta->fetchAll();
    }

    public function actualizarPorId($id, $datos) {
        $campos = [];
        $valores = [];
    
        foreach ($datos as $campo => $valor) {
            $campos[] = "$campo = ?";
            $valores[] = $valor;
        }
    
        $valores[] = $id; 
    
        $sql = "UPDATE english_group SET " . implode(", ", $campos) . " WHERE id = ?";
        $consulta = $this->conexion->prepare($sql);
        return $consulta->execute($valores);
    }

    // UPDATE - Desmarcar un registro como eliminado (restaurar)
    public function restaurarPorId($id) {
        $consulta = $this->conexion->prepare("UPDATE english_group SET is_deleted = FALSE, deleted_at = NULL WHERE id = ?");
        return $consulta->execute([$id]);
    }

    // DELETE - Soft delete (marcar como eliminado)
    public function eliminarPorId($id) {
        $consulta = $this->conexion->prepare("UPDATE english_group SET is_deleted = TRUE, deleted_at = NOW() WHERE id = ?");
        return $consulta->execute([$id]);
    }

    // DELETE - Hard delete (eliminar permanentemente)
    public function eliminarPermanentePorId($id) {
        $consulta = $this->conexion->prepare("DELETE FROM english_group WHERE id = ?");
        return $consulta->execute([$id]);
    }

    // DELETE - Hard delete para vaciar la tabla
    public function vaciarTabla() {
        $consulta = $this->conexion->query("TRUNCATE TABLE english_group");
        return $consulta;
    }
}