<?php

require_once __DIR__ . '/../config/database.php';

class Exam {
    private $conexion;

    public function __construct() {
        $this->conexion = Database::getConnection();
    }

    // CREATE
    public function crear($datos) {
        $sql = "INSERT INTO exam
            (id_level, id_writing_template, id_reading_template, duration_time, who_created)
            VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->conexion->prepare($sql);
        return $stmt->execute([
            $datos['id_level'],
            $datos['id_writing_template'],
            $datos['id_reading_template'],
            $datos['duration_time'],
            $datos['who_created'],
        ]);
    }

    // READ - listar activos
    public function obtenerTodos() {
        $stmt = $this->conexion->query(
            "SELECT * FROM exam WHERE is_deleted = FALSE ORDER BY id ASC"
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // READ - por ID (sin filtrar is_deleted, como en tu ejemplo SQL)
    public function obtenerPorId($id) {
        $stmt = $this->conexion->prepare("SELECT * FROM exam WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // READ - por who_created (sin filtrar is_deleted, acorde a tu ejemplo)
    public function obtenerPorCreador($who_created) {
        $stmt = $this->conexion->prepare("SELECT * FROM exam WHERE who_created = ?");
        $stmt->execute([$who_created]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
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
        $sql = "UPDATE exam SET " . implode(", ", $campos) . " WHERE id = ?";
        $stmt = $this->conexion->prepare($sql);
        return $stmt->execute($valores);
    }

    // RESTORE - desmarcar eliminado
    public function restaurarPorId($id) {
        $stmt = $this->conexion->prepare(
            "UPDATE exam SET is_deleted = FALSE, deleted_at = NULL WHERE id = ?"
        );
        return $stmt->execute([$id]);
    }

    // DELETE - Soft delete
    public function eliminarPorId($id) {
        $stmt = $this->conexion->prepare(
            "UPDATE exam SET is_deleted = TRUE, deleted_at = NOW() WHERE id = ?"
        );
        return $stmt->execute([$id]);
    }

    // DELETE - Hard delete
    public function eliminarPermanentePorId($id) {
        $stmt = $this->conexion->prepare("DELETE FROM exam WHERE id = ?");
        return $stmt->execute([$id]);
    }

    // TRUNCATE - vaciar tabla
    public function vaciarTabla() {
        return $this->conexion->query("TRUNCATE TABLE exam");
    }
}
