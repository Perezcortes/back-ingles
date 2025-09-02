<?php

require_once __DIR__ . '/../config/database.php';

class SignsTemplate {
    private $conexion;

    public function __construct() {
        $this->conexion = Database::getConnection();
    }

    // CREATE
    public function crear($datos) {
        $sql = "INSERT INTO signs_template (title, instruction, question_number, id_level) VALUES (?, ?, ?, ?)";
        $stmt = $this->conexion->prepare($sql);
        return $stmt->execute([
            $datos['title'],
            $datos['instruction'],
            $datos['question_number'],
            $datos['id_level']
        ]);
    }

    // READ - listar activos
    public function obtenerTodos() {
        $stmt = $this->conexion->query("SELECT * FROM signs_template WHERE is_deleted = FALSE ORDER BY id ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // READ - por ID
    public function obtenerPorId($id) {
        $stmt = $this->conexion->prepare("SELECT * FROM signs_template WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // READ - por id_level
    public function obtenerPorNivel($id_level) {
        $stmt = $this->conexion->prepare("SELECT * FROM signs_template WHERE id_level = ? AND is_deleted = FALSE");
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
        $sql = "UPDATE signs_template SET " . implode(", ", $campos) . " WHERE id = ?";
        $stmt = $this->conexion->prepare($sql);
        return $stmt->execute($valores);
    }

    // RESTORE - desmarcar eliminado
    public function restaurarPorId($id) {
        $stmt = $this->conexion->prepare("UPDATE signs_template SET is_deleted = FALSE, deleted_at = NULL WHERE id = ?");
        return $stmt->execute([$id]);
    }

    // DELETE - Soft delete
    public function eliminarPorId($id) {
        $stmt = $this->conexion->prepare("UPDATE signs_template SET is_deleted = TRUE, deleted_at = NOW() WHERE id = ?");
        return $stmt->execute([$id]);
    }

    // DELETE - Hard delete
    public function eliminarPermanentePorId($id) {
        $stmt = $this->conexion->prepare("DELETE FROM signs_template WHERE id = ?");
        return $stmt->execute([$id]);
    }

    // TRUNCATE
    public function vaciarTabla() {
        return $this->conexion->query("TRUNCATE TABLE signs_template");
    }
}
