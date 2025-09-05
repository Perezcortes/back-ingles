<?php

require_once __DIR__ . '/../config/database.php';

class OpenClozeOption {
    private $conexion;

    public function __construct() {
        $this->conexion = Database::getConnection();
    }

    // CREATE
    public function crear($datos) {
        $sql = "INSERT INTO open_cloze_option (id_generator_open_cloze_question_keys, description)
                VALUES (?, ?)";
        $stmt = $this->conexion->prepare($sql);

        $stmt->bindValue(1, (int)$datos['id_generator_open_cloze_question_keys'], PDO::PARAM_INT);
        $stmt->bindValue(2, (string)$datos['description'], PDO::PARAM_STR);

        return $stmt->execute();
    }

    // READ - listar todo
    public function obtenerTodos() {
        $stmt = $this->conexion->query("SELECT * FROM open_cloze_option ORDER BY id ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // READ - por ID
    public function obtenerPorId($id) {
        $stmt = $this->conexion->prepare("SELECT * FROM open_cloze_option WHERE id = ?");
        $stmt->execute([(int)$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // READ - por id_generator_open_cloze_question_keys
    public function obtenerPorKey($id_generator_key) {
        $stmt = $this->conexion->prepare(
            "SELECT * FROM open_cloze_option WHERE id_generator_open_cloze_question_keys = ? ORDER BY id ASC"
        );
        $stmt->execute([(int)$id_generator_key]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // UPDATE - por ID (solo campos permitidos)
    public function actualizarPorId($id, $datos) {
        $permitidos = ['id_generator_open_cloze_question_keys', 'description'];
        $set = [];
        $vals = [];

        foreach ($datos as $campo => $valor) {
            if (!in_array($campo, $permitidos, true)) continue;
            $set[] = "$campo = ?";
            if ($campo === 'id_generator_open_cloze_question_keys') {
                $vals[] = (int)$valor;
            } else { // description
                $vals[] = (string)$valor;
            }
        }

        if (empty($set)) return false;

        $sql = "UPDATE open_cloze_option SET " . implode(", ", $set) . " WHERE id = ?";
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
        $stmt = $this->conexion->prepare("DELETE FROM open_cloze_option WHERE id = ?");
        return $stmt->execute([(int)$id]);
    }

    // TRUNCATE
    public function vaciarTabla() {
        return $this->conexion->query("TRUNCATE TABLE open_cloze_option");
    }
}