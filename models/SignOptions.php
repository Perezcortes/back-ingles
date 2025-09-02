<?php

require_once __DIR__ . '/../config/database.php';

class SignOptions {
    private $conexion;

    public function __construct() {
        $this->conexion = Database::getConnection();
    }

    // CREATE
    public function crear($datos) {
        $sql = "INSERT INTO sign_options (description, id_sign, correct_answer) VALUES (?, ?, ?)";
        $stmt = $this->conexion->prepare($sql);
        return $stmt->execute([
            $datos['description'],
            $datos['id_sign'],
            $datos['correct_answer'], // Debe mapear a boolean en DB
        ]);
    }

    // READ - listar todo
    public function obtenerTodos() {
        $stmt = $this->conexion->query("SELECT * FROM sign_options ORDER BY id ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // READ - por ID
    public function obtenerPorId($id) {
        $stmt = $this->conexion->prepare("SELECT * FROM sign_options WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // READ - por id_sign (todas las opciones de una pregunta)
    public function obtenerPorIdSign($id_sign) {
        $stmt = $this->conexion->prepare("SELECT * FROM sign_options WHERE id_sign = ? ORDER BY id ASC");
        $stmt->execute([$id_sign]);
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
        $sql = "UPDATE sign_options SET " . implode(", ", $campos) . " WHERE id = ?";
        $stmt = $this->conexion->prepare($sql);
        return $stmt->execute($valores);
    }

    // DELETE - hard delete
    public function eliminarPorId($id) {
        $stmt = $this->conexion->prepare("DELETE FROM sign_options WHERE id = ?");
        return $stmt->execute([$id]);
    }

    // TRUNCATE
    public function vaciarTabla() {
        return $this->conexion->query("TRUNCATE TABLE sign_options");
    }
}
