<?php

require_once __DIR__ . '/../config/database.php';

class OptionSimpleMultiple {
    private $conexion;

    public function __construct() {
        $this->conexion = Database::getConnection();
    }

    // CREATE
    public function crear($datos) {
        $sql = "INSERT INTO option_simple_multiple (description, id_simple_multiple, correct_answer)
                VALUES (?, ?, ?)";
        $stmt = $this->conexion->prepare($sql);
        return $stmt->execute([
            $datos['description'],
            $datos['id_simple_multiple'],
            $datos['correct_answer'], // boolean/bit en DB
        ]);
    }

    // READ - listar todo
    public function obtenerTodos() {
        $stmt = $this->conexion->query("SELECT * FROM option_simple_multiple ORDER BY id ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // READ - por ID
    public function obtenerPorId($id) {
        $stmt = $this->conexion->prepare("SELECT * FROM option_simple_multiple WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // READ - por id_simple_multiple (todas las opciones de una pregunta)
    public function obtenerPorIdSimpleMultiple($id_simple_multiple) {
        $stmt = $this->conexion->prepare(
            "SELECT * FROM option_simple_multiple WHERE id_simple_multiple = ? ORDER BY id ASC"
        );
        $stmt->execute([$id_simple_multiple]);
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
        $sql = "UPDATE option_simple_multiple SET " . implode(", ", $campos) . " WHERE id = ?";
        $stmt = $this->conexion->prepare($sql);
        return $stmt->execute($valores);
    }

    // DELETE - hard delete
    public function eliminarPorId($id) {
        $stmt = $this->conexion->prepare("DELETE FROM option_simple_multiple WHERE id = ?");
        return $stmt->execute([$id]);
    }

    // TRUNCATE
    public function vaciarTabla() {
        return $this->conexion->query("TRUNCATE TABLE option_simple_multiple");
    }
}
