<?php

require_once __DIR__ . '/../config/database.php';

class OptionsMultipleChoiceCloze {
    private $conexion;

    public function __construct() {
        $this->conexion = Database::getConnection();
    }

    // CREATE
    public function crear($datos) {
        $sql = "INSERT INTO options_multiple_choice_cloze (description, id_question_number, correct_answer)
                VALUES (?, ?, ?)";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bindValue(1, (string)$datos['description'], PDO::PARAM_STR);
        $stmt->bindValue(2, (int)$datos['id_question_number'], PDO::PARAM_INT);
        // correct_answer: 0/1
        $stmt->bindValue(3, (int)$datos['correct_answer'], PDO::PARAM_INT);
        return $stmt->execute();
    }

    // READ - listar todo
    public function obtenerTodos() {
        $stmt = $this->conexion->query(
            "SELECT * FROM options_multiple_choice_cloze ORDER BY id ASC"
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // READ - por ID
    public function obtenerPorId($id) {
        $stmt = $this->conexion->prepare(
            "SELECT * FROM options_multiple_choice_cloze WHERE id = ?"
        );
        $stmt->execute([(int)$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // READ - por id_question_number
    public function obtenerPorQuestionNumber($id_question_number) {
        $stmt = $this->conexion->prepare(
            "SELECT * FROM options_multiple_choice_cloze
             WHERE id_question_number = ?
             ORDER BY id ASC"
        );
        $stmt->execute([(int)$id_question_number]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // UPDATE - por ID (solo campos enviados)
    public function actualizarPorId($id, $datos) {
        $permitidos = ['description','id_question_number','correct_answer'];
        $set = [];
        $vals = [];

        foreach ($datos as $campo => $valor) {
            if (!in_array($campo, $permitidos, true)) continue;
            $set[] = "$campo = ?";
            if ($campo === 'id_question_number' || $campo === 'correct_answer') {
                $vals[] = (int)$valor;
            } else { // description
                $vals[] = (string)$valor;
            }
        }
        if (empty($set)) return false;

        $sql = "UPDATE options_multiple_choice_cloze SET " . implode(", ", $set) . " WHERE id = ?";
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
        $stmt = $this->conexion->prepare(
            "DELETE FROM options_multiple_choice_cloze WHERE id = ?"
        );
        return $stmt->execute([(int)$id]);
    }

    // TRUNCATE
    public function vaciarTabla() {
        return $this->conexion->query("TRUNCATE TABLE options_multiple_choice_cloze");
    }
}