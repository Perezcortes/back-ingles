<?php

require_once __DIR__ . '/../config/database.php';

class GapFillQuestion {
    private $conexion;

    public function __construct() {
        $this->conexion = Database::getConnection();
    }

    // CREATE
    public function crear($datos) {
        $sql = "INSERT INTO gap_fill_question (texto, counter, id_gap_fill_template)
                VALUES (?, ?, ?)";
        $stmt = $this->conexion->prepare($sql);

        $stmt->bindValue(1, (string)$datos['texto'], PDO::PARAM_STR);
        $stmt->bindValue(2, (int)$datos['counter'], PDO::PARAM_INT);
        $stmt->bindValue(3, (int)$datos['id_gap_fill_template'], PDO::PARAM_INT);

        return $stmt->execute();
    }

    // READ - listar todo
    public function obtenerTodos() {
        $stmt = $this->conexion->query("SELECT * FROM gap_fill_question ORDER BY id ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // READ - por ID
    public function obtenerPorId($id) {
        $stmt = $this->conexion->prepare("SELECT * FROM gap_fill_question WHERE id = ?");
        $stmt->execute([(int)$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // READ - por id_gap_fill_template
    public function obtenerPorTemplate($id_gap_fill_template) {
        $stmt = $this->conexion->prepare(
            "SELECT * FROM gap_fill_question WHERE id_gap_fill_template = ? ORDER BY id ASC"
        );
        $stmt->execute([(int)$id_gap_fill_template]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // UPDATE - solo campos permitidos
    public function actualizarPorId($id, $datos) {
        $permitidos = ['texto','counter','id_gap_fill_template'];
        $set = [];
        $vals = [];

        foreach ($datos as $campo => $valor) {
            if (!in_array($campo, $permitidos, true)) continue;
            $set[] = "$campo = ?";
            if ($campo === 'counter' || $campo === 'id_gap_fill_template') {
                $vals[] = (int)$valor;
            } else { // texto
                $vals[] = (string)$valor;
            }
        }
        if (empty($set)) return false;

        $sql = "UPDATE gap_fill_question SET " . implode(", ", $set) . " WHERE id = ?";
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
        $stmt = $this->conexion->prepare("DELETE FROM gap_fill_question WHERE id = ?");
        return $stmt->execute([(int)$id]);
    }

    // TRUNCATE
    public function vaciarTabla() {
        return $this->conexion->query("TRUNCATE TABLE gap_fill_question");
    }
}