<?php

require_once __DIR__ . '/../config/database.php';

class SimpleMultiple {
    private $conexion;

    public function __construct() {
        $this->conexion = Database::getConnection();
    }

    // CREATE
    public function crear($datos) {
        $sql = "INSERT INTO simple_multiple (id_simple_multiple_template, sentence)
                VALUES (?, ?)";
        $stmt = $this->conexion->prepare($sql);
        return $stmt->execute([
            $datos['id_simple_multiple_template'],
            $datos['sentence'],
        ]);
    }

    // READ - listar activos
    public function obtenerTodos() {
        $stmt = $this->conexion->query(
            "SELECT * FROM simple_multiple WHERE is_deleted = FALSE ORDER BY id ASC"
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // READ - por ID (sin filtrar is_deleted, como en tu SQL)
    public function obtenerPorId($id) {
        $stmt = $this->conexion->prepare("SELECT * FROM simple_multiple WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // READ - por id_simple_multiple_template (activos)
    public function obtenerPorIdTemplate($id_simple_multiple_template) {
        $stmt = $this->conexion->prepare(
            "SELECT * FROM simple_multiple WHERE id_simple_multiple_template = ? AND is_deleted = FALSE ORDER BY id ASC"
        );
        $stmt->execute([$id_simple_multiple_template]);
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
        $sql = "UPDATE simple_multiple SET " . implode(", ", $campos) . " WHERE id = ?";
        $stmt = $this->conexion->prepare($sql);
        return $stmt->execute($valores);
    }

    // RESTORE - desmarcar eliminado
    public function restaurarPorId($id) {
        $stmt = $this->conexion->prepare(
            "UPDATE simple_multiple SET is_deleted = FALSE, deleted_at = NULL WHERE id = ?"
        );
        return $stmt->execute([$id]);
    }

    // DELETE - Soft delete
    public function eliminarPorId($id) {
        $stmt = $this->conexion->prepare(
            "UPDATE simple_multiple SET is_deleted = TRUE, deleted_at = NOW() WHERE id = ?"
        );
        return $stmt->execute([$id]);
    }

    // DELETE - Hard delete
    public function eliminarPermanentePorId($id) {
        $stmt = $this->conexion->prepare("DELETE FROM simple_multiple WHERE id = ?");
        return $stmt->execute([$id]);
    }

    // TRUNCATE
    public function vaciarTabla() {
        return $this->conexion->query("TRUNCATE TABLE simple_multiple");
    }
}
