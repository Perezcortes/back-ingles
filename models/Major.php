<?php

require_once __DIR__ . '/../config/database.php';

class Major {
    private $conexion;

    public function __construct() {
        $this->conexion = Database::getConnection();
    }

    public function crear($datos) {
        $campos = array_keys($datos);
        $placeholders = array_fill(0, count($datos), '?');
        $valores = array_values($datos);
    
        $sql = "INSERT INTO major (" . implode(',', $campos) . ") VALUES (" . implode(',', $placeholders) . ")";
        $consulta = $this->conexion->prepare($sql);
        return $consulta->execute($valores);
    }

    public function obtenerTodos() {
        $consulta = $this->conexion->query("SELECT * FROM major WHERE is_deleted = FALSE ORDER BY id ASC");
        return $consulta->fetchAll();
    }

    public function obtenerPorId($id) {
        $consulta = $this->conexion->prepare("SELECT * FROM major WHERE id = ? AND is_deleted = FALSE");
        $consulta->execute([$id]);
        return $consulta->fetch();
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
        $consulta = $this->conexion->prepare($sql);
        return $consulta->execute($valores);
    }
    // RESTORE - Restaurar un registro por ID
    public function restaurarPorId($id) {
        $consulta = $this->conexion->prepare("UPDATE major SET is_deleted = FALSE, deleted_at = NULL WHERE id = ?");
        return $consulta->execute([$id]);
    }

    // DELETE - Parte del soft delete (marcar como eliminado un elemento)
    public function eliminarPorId($id) {
        $consulta = $this->conexion->prepare("UPDATE major SET is_deleted = TRUE, deleted_at = NOW() WHERE id = ?");
        return $consulta->execute([$id]);
    }

    // DELETE - es del Hard delete (eliminar permanentemente un elemento)
    public function eliminarPermanentePorId($id) {
        $consulta = $this->conexion->prepare("DELETE FROM major WHERE id = ?");
        return $consulta->execute([$id]);
    }

    // DELETE - Hard delete para vaciar la tabla
    public function vaciarTabla() {
        $consulta = $this->conexion->query("TRUNCATE TABLE major");
        return $consulta;
    }
}