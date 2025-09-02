<?php

require_once __DIR__ . '/../config/database.php';

class Signs {
    private $conexion;

    public function __construct() {
        $this->conexion = Database::getConnection();
    }

    // CREATE
    public function crear($datos) {
        $sql = "INSERT INTO signs (id_sign_template, link_img) VALUES (?, ?)";
        $stmt = $this->conexion->prepare($sql);
        return $stmt->execute([
            $datos['id_sign_template'],
            $datos['link_img'],
        ]);
    }

    // READ - listar activos (is_deleted = FALSE)
    public function obtenerTodos() {
        $stmt = $this->conexion->query(
            "SELECT * FROM signs WHERE is_deleted = FALSE ORDER BY id ASC"
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // READ - por ID (sin filtrar is_deleted, igual que tu SQL de ejemplo)
    public function obtenerPorId($id) {
        $stmt = $this->conexion->prepare("SELECT * FROM signs WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // READ - por id_sign_template (activos)
    public function obtenerPorIdSignTemplate($id_sign_template) {
        $stmt = $this->conexion->prepare(
            "SELECT * FROM signs WHERE id_sign_template = ? AND is_deleted = FALSE"
        );
        $stmt->execute([$id_sign_template]);
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
        $sql = "UPDATE signs SET " . implode(", ", $campos) . " WHERE id = ?";
        $stmt = $this->conexion->prepare($sql);
        return $stmt->execute($valores);
    }

    // RESTORE - desmarcar eliminado
    public function restaurarPorId($id) {
        $stmt = $this->conexion->prepare(
            "UPDATE signs SET is_deleted = FALSE, deleted_at = NULL WHERE id = ?"
        );
        return $stmt->execute([$id]);
    }

    // DELETE - Soft delete
    public function eliminarPorId($id) {
        $stmt = $this->conexion->prepare(
            "UPDATE signs SET is_deleted = TRUE, deleted_at = NOW() WHERE id = ?"
        );
        return $stmt->execute([$id]);
    }

    // DELETE - Hard delete
    public function eliminarPermanentePorId($id) {
        $stmt = $this->conexion->prepare("DELETE FROM signs WHERE id = ?");
        return $stmt->execute([$id]);
    }

    // TRUNCATE
    public function vaciarTabla() {
        return $this->conexion->query("TRUNCATE TABLE signs");
    }
}
