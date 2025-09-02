<?php

require_once __DIR__ . '/../config/database.php';

class ExamType {
    private $conexion;

    public function __construct() {
        $this->conexion = Database::getConnection();
    }

    // CREATE
    public function crear($datos) {
        // Solo se requiere exam_name
        $sql = "INSERT INTO exam_type (exam_name) VALUES (?)";
        $consulta = $this->conexion->prepare($sql);
        return $consulta->execute([$datos['exam_name']]);
    }

    // READ - listar activos (no eliminados)
    public function obtenerTodos() {
        $consulta = $this->conexion->query("SELECT * FROM exam_type WHERE is_deleted = FALSE ORDER BY id ASC");
        return $consulta->fetchAll(PDO::FETCH_ASSOC);
    }

    // READ - por ID (si está activo)
    public function obtenerPorId($id) {
        $consulta = $this->conexion->prepare("SELECT * FROM exam_type WHERE id = ? AND is_deleted = FALSE");
        $consulta->execute([$id]);
        return $consulta->fetch(PDO::FETCH_ASSOC);
    }

    // UPDATE - por ID (solo campos enviados)
    public function actualizarPorId($id, $datos) {
        $campos = [];
        $valores = [];

        foreach ($datos as $campo => $valor) {
            $campos[] = "$campo = ?";
            $valores[] = $valor;
        }

        if (empty($campos)) return false;

        $valores[] = $id;
        $sql = "UPDATE exam_type SET " . implode(", ", $campos) . " WHERE id = ?";
        $consulta = $this->conexion->prepare($sql);
        return $consulta->execute($valores);
    }

    // RESTORE - desmarcar eliminado
    public function restaurarPorId($id) {
        $consulta = $this->conexion->prepare("UPDATE exam_type SET is_deleted = FALSE, deleted_at = NULL WHERE id = ?");
        return $consulta->execute([$id]);
    }

    // SOFT DELETE
    public function eliminarPorId($id) {
        $consulta = $this->conexion->prepare("UPDATE exam_type SET is_deleted = TRUE, deleted_at = NOW() WHERE id = ?");
        return $consulta->execute([$id]);
    }

    // HARD DELETE
    public function eliminarPermanentePorId($id) {
        $consulta = $this->conexion->prepare("DELETE FROM exam_type WHERE id = ?");
        return $consulta->execute([$id]);
    }

    // TRUNCATE
    public function vaciarTabla() {
        return $this->conexion->query("TRUNCATE TABLE exam_type");
    }
}