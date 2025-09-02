<?php

require_once __DIR__ . '/../config/database.php';

class ReadingTemplate {
    private $conexion;

    public function __construct() {
        $this->conexion = Database::getConnection();
    }

    // CREATE
    public function crear($datos) {
        $sql = "INSERT INTO reading_template 
            (who_created, id_level, title, description, id_exam_type, id_signs_template, id_simple_multiple_template, id_multiple_matching_template, id_reading_comprehension_template, id_open_cloze_template, id_template_multiple_choice_cloze, id_gap_fill_template)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?)";
        $stmt = $this->conexion->prepare($sql);
        return $stmt->execute([
            $datos['who_created'],
            $datos['id_level'],
            $datos['title'],
            $datos['description'],
            $datos['id_exam_type'],
            $datos['id_signs_template'],
            $datos['id_simple_multiple_template'],
            $datos['id_multiple_matching_template'],
            $datos['id_reading_comprehension_template'],
            $datos['id_open_cloze_template'],
            $datos['id_template_multiple_choice_cloze'],
            $datos['id_gap_fill_template'],
        ]);
    }

    // READ - listar activos
    public function obtenerTodos() {
        $stmt = $this->conexion->query("SELECT * FROM reading_template WHERE is_deleted = FALSE ORDER BY id ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // READ - por ID
    public function obtenerPorId($id) {
        $stmt = $this->conexion->prepare("SELECT * FROM reading_template WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // READ - por nivel
    public function obtenerPorNivel($id_level) {
        $stmt = $this->conexion->prepare("SELECT * FROM reading_template WHERE id_level = ? AND is_deleted = FALSE");
        $stmt->execute([$id_level]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // UPDATE - por ID
    public function actualizarPorId($id, $datos) {
        $campos = [];
        $valores = [];

        foreach ($datos as $campo => $valor) {
            $campos[] = "$campo = ?";
            $valores[] = $valor;
        }

        if (empty($campos)) return false;

        $valores[] = $id;
        $sql = "UPDATE reading_template SET " . implode(", ", $campos) . " WHERE id = ?";
        $stmt = $this->conexion->prepare($sql);
        return $stmt->execute($valores);
    }

    // RESTORE - desmarcar eliminado
    public function restaurarPorId($id) {
        $stmt = $this->conexion->prepare("UPDATE reading_template SET is_deleted = FALSE, deleted_at = NULL WHERE id = ?");
        return $stmt->execute([$id]);
    }

    // DELETE - Soft delete
    public function eliminarPorId($id) {
        $stmt = $this->conexion->prepare("UPDATE reading_template SET is_deleted = TRUE, deleted_at = NOW() WHERE id = ?");
        return $stmt->execute([$id]);
    }

    // DELETE - Hard delete
    public function eliminarPermanentePorId($id) {
        $stmt = $this->conexion->prepare("DELETE FROM reading_template WHERE id = ?");
        return $stmt->execute([$id]);
    }

    // TRUNCATE
    public function vaciarTabla() {
        return $this->conexion->query("TRUNCATE TABLE reading_template");
    }
}
