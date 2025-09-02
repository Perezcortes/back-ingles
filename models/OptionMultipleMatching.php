<?php

require_once __DIR__ . '/../config/database.php';

class OptionMultipleMatching {
    private $conexion;

    public function __construct() {
        $this->conexion = Database::getConnection();
    }

    // CREATE
    public function crear($datos) {
        $sql = "INSERT INTO option_multiple_matching (title, description, id_multiple_matching_template)
                VALUES (?, ?, ?)";
        $stmt = $this->conexion->prepare($sql);
        return $stmt->execute([
            $datos['title'],
            $datos['description'],
            $datos['id_multiple_matching_template'],
        ]);
    }

    // READ - listar activos
    public function obtenerTodos() {
        $stmt = $this->conexion->query(
            "SELECT * FROM option_multiple_matching WHERE is_deleted = FALSE ORDER BY id ASC"
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // READ - por ID (sin filtrar is_deleted, como en tus ejemplos)
    public function obtenerPorId($id) {
        $stmt = $this->conexion->prepare("SELECT * FROM option_multiple_matching WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // READ - por template (activos)
    public function obtenerPorTemplate($id_multiple_matching_template) {
        $stmt = $this->conexion->prepare(
            "SELECT * FROM option_multiple_matching 
             WHERE id_multiple_matching_template = ? AND is_deleted = FALSE
             ORDER BY id ASC"
        );
        $stmt->execute([$id_multiple_matching_template]);
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
        $sql = "UPDATE option_multiple_matching SET " . implode(", ", $campos) . " WHERE id = ?";
        $stmt = $this->conexion->prepare($sql);
        return $stmt->execute($valores);
    }

    // RESTORE - desmarcar eliminado
    public function restaurarPorId($id) {
        $stmt = $this->conexion->prepare(
            "UPDATE option_multiple_matching SET is_deleted = FALSE, deleted_at = NULL WHERE id = ?"
        );
        return $stmt->execute([$id]);
    }

    // DELETE - Soft delete
    public function eliminarPorId($id) {
        $stmt = $this->conexion->prepare(
            "UPDATE option_multiple_matching SET is_deleted = TRUE, deleted_at = NOW() WHERE id = ?"
        );
        return $stmt->execute([$id]);
    }

    // DELETE - Hard delete
    public function eliminarPermanentePorId($id) {
        $stmt = $this->conexion->prepare("DELETE FROM option_multiple_matching WHERE id = ?");
        return $stmt->execute([$id]);
    }

    // TRUNCATE
    public function vaciarTabla() {
        return $this->conexion->query("TRUNCATE TABLE option_multiple_matching");
    }
}
