<?php

require_once __DIR__ . '/../config/database.php';

class OptionReadingComprehension {
    private $conexion;

    public function __construct() {
        $this->conexion = Database::getConnection();
    }

    // CREATE
    public function crear($datos) {
        $sql = "INSERT INTO option_reading_comprehension (description, correct_answer, id_reading_comprehension)
                VALUES (?, ?, ?)";
        $stmt = $this->conexion->prepare($sql);

        $desc = (string)$datos['description'];
        $correct = !empty($datos['correct_answer']) ? 1 : 0; // 0/1 para MySQL
        $idRC = (int)$datos['id_reading_comprehension'];

        $stmt->bindValue(1, $desc, PDO::PARAM_STR);
        $stmt->bindValue(2, $correct, PDO::PARAM_INT);
        $stmt->bindValue(3, $idRC, PDO::PARAM_INT);

        return $stmt->execute();
    }

    // READ - listar todo
    public function obtenerTodos() {
        $stmt = $this->conexion->query("SELECT * FROM option_reading_comprehension ORDER BY id ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // READ - por ID
    public function obtenerPorId($id) {
        $stmt = $this->conexion->prepare("SELECT * FROM option_reading_comprehension WHERE id = ?");
        $stmt->execute([(int)$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // READ - por id_reading_comprehension
    public function obtenerPorReading($id_reading_comprehension) {
        $stmt = $this->conexion->prepare(
            "SELECT * FROM option_reading_comprehension WHERE id_reading_comprehension = ? ORDER BY id ASC"
        );
        $stmt->execute([(int)$id_reading_comprehension]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // UPDATE - por ID (solo campos permitidos)
    public function actualizarPorId($id, $datos) {
        $permitidos = ['description','correct_answer','id_reading_comprehension'];
        $set = [];
        $vals = [];

        foreach ($datos as $campo => $valor) {
            if (!in_array($campo, $permitidos, true)) continue;
            $set[] = "$campo = ?";
            if ($campo === 'id_reading_comprehension') {
                $vals[] = (int)$valor;
            } elseif ($campo === 'correct_answer') {
                $vals[] = !empty($valor) ? 1 : 0;
            } else { // description
                $vals[] = (string)$valor;
            }
        }
        if (empty($set)) return false;

        $sql = "UPDATE option_reading_comprehension SET " . implode(", ", $set) . " WHERE id = ?";
        $stmt = $this->conexion->prepare($sql);

        $i = 1;
        foreach ($vals as $v) {
            $stmt->bindValue($i++, $v, is_int($v) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $stmt->bindValue($i, (int)$id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    // DELETE - hard delete
    public function eliminarPorId($id) {
        $stmt = $this->conexion->prepare("DELETE FROM option_reading_comprehension WHERE id = ?");
        return $stmt->execute([(int)$id]);
    }

    // TRUNCATE
    public function vaciarTabla() {
        return $this->conexion->query("TRUNCATE TABLE option_reading_comprehension");
    }
}