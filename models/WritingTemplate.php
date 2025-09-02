<?php

require_once __DIR__ . '/../config/database.php';

class WritingTemplate {
    private $conexion;

    public function __construct() {
        $this->conexion = Database::getConnection();
    }

    // CREATE
    public function crear($datos) {
        $sql = "INSERT INTO writing_template
            (id_level, id_exam_type, instruction, story_description, who_created, topic)
            VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->conexion->prepare($sql);
        return $stmt->execute([
            $datos['id_level'],
            $datos['id_exam_type'],
            $datos['instruction'],
            $datos['story_description'],
            $datos['who_created'],
            $datos['topic'],
        ]);
    }

    // READ - listar activos (no eliminados)
    public function obtenerTodos() {
        $stmt = $this->conexion->query(
            "SELECT * FROM writing_template WHERE is_deleted = FALSE ORDER BY id ASC"
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // READ - por ID (activo)
    public function obtenerPorId($id) {
        $stmt = $this->conexion->prepare(
            "SELECT * FROM writing_template WHERE id = ? AND is_deleted = FALSE"
        );
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
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
        $sql = "UPDATE writing_template SET " . implode(", ", $campos) . " WHERE id = ?";
        $stmt = $this->conexion->prepare($sql);
        return $stmt->execute($valores);
    }

    // DELETE - Soft delete
    public function eliminarPorId($id) {
        $stmt = $this->conexion->prepare(
            "UPDATE writing_template SET is_deleted = TRUE, deleted_at = NOW() WHERE id = ?"
        );
        return $stmt->execute([$id]);
    }
}
