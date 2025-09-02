<?php

require_once __DIR__ . '/../config/database.php';

class Image {
    private $conexion;

    public function __construct() {
        $this->conexion = Database::getConnection();
    }

    // CREATE
    public function crear($datos) {
        $sql = "INSERT INTO image (link_image, id_writing_template) VALUES (?, ?)";
        $stmt = $this->conexion->prepare($sql);
        return $stmt->execute([
            $datos['link_image'],
            $datos['id_writing_template'],
        ]);
    }

    // READ - listar todo
    public function obtenerTodos() {
        $stmt = $this->conexion->query("SELECT * FROM image ORDER BY id ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // READ - por ID
    public function obtenerPorId($id) {
        $stmt = $this->conexion->prepare("SELECT * FROM image WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // READ - por link_image
    public function obtenerPorLink($link_image) {
        $stmt = $this->conexion->prepare("SELECT * FROM image WHERE link_image = ?");
        $stmt->execute([$link_image]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // READ - por id_writing_template
    public function obtenerPorIdWritingTemplate($id_writing_template) {
        $stmt = $this->conexion->prepare("SELECT * FROM image WHERE id_writing_template = ?");
        $stmt->execute([$id_writing_template]);
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
        $sql = "UPDATE image SET " . implode(", ", $campos) . " WHERE id = ?";
        $stmt = $this->conexion->prepare($sql);
        return $stmt->execute($valores);
    }

    // DELETE - hard delete por ID
    public function eliminarPorId($id) {
        $stmt = $this->conexion->prepare("DELETE FROM image WHERE id = ?");
        return $stmt->execute([$id]);
    }

    // TRUNCATE - vaciar tabla
    public function vaciarTabla() {
        return $this->conexion->query("TRUNCATE TABLE image");
    }
}
