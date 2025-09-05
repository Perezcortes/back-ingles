<?php

require_once __DIR__ . '/../config/database.php';

class GeneratorOpenClozeQuestionKey {
    private $conexion;

    public function __construct() {
        $this->conexion = Database::getConnection();
    }

    // CREATE
    public function crear($datos) {
        $sql = "INSERT INTO generator_open_cloze_question_keys (id_open_cloze_question)
                VALUES (?)";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bindValue(1, (int)$datos['id_open_cloze_question'], PDO::PARAM_INT);
        return $stmt->execute();
    }

    // READ - listar activos
    public function obtenerTodos() {
        $stmt = $this->conexion->query(
            "SELECT * FROM generator_open_cloze_question_keys WHERE is_deleted = FALSE ORDER BY id ASC"
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // READ - por ID (sin filtrar is_deleted, como en tus ejemplos)
    public function obtenerPorId($id) {
        $stmt = $this->conexion->prepare(
            "SELECT * FROM generator_open_cloze_question_keys WHERE id = ?"
        );
        $stmt->execute([(int)$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // READ - por id_open_cloze_question (activos)
    public function obtenerPorPregunta($id_open_cloze_question) {
        $stmt = $this->conexion->prepare(
            "SELECT * FROM generator_open_cloze_question_keys
             WHERE id_open_cloze_question = ? AND is_deleted = FALSE
             ORDER BY id ASC"
        );
        $stmt->execute([(int)$id_open_cloze_question]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // UPDATE - por ID (solo campo permitido)
    public function actualizarPorId($id, $datos) {
        $permitidos = ['id_open_cloze_question'];
        $set = [];
        $vals = [];

        foreach ($datos as $campo => $valor) {
            if (!in_array($campo, $permitidos, true)) continue;
            $set[] = "$campo = ?";
            $vals[] = (int)$valor;
        }

        if (empty($set)) return false;

        $sql = "UPDATE generator_open_cloze_question_keys SET " . implode(", ", $set) . " WHERE id = ?";
        $stmt = $this->conexion->prepare($sql);

        $i = 1;
        foreach ($vals as $v) $stmt->bindValue($i++, $v, PDO::PARAM_INT);
        $stmt->bindValue($i, (int)$id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    // RESTORE - desmarcar eliminado
    public function restaurarPorId($id) {
        $stmt = $this->conexion->prepare(
            "UPDATE generator_open_cloze_question_keys
             SET is_deleted = FALSE, deleted_at = NULL
             WHERE id = ?"
        );
        return $stmt->execute([(int)$id]);
    }

    // DELETE - Soft delete
    public function eliminarPorId($id) {
        $stmt = $this->conexion->prepare(
            "UPDATE generator_open_cloze_question_keys
             SET is_deleted = TRUE, deleted_at = NOW()
             WHERE id = ?"
        );
        return $stmt->execute([(int)$id]);
    }

    // DELETE - Hard delete
    public function eliminarPermanentePorId($id) {
        $stmt = $this->conexion->prepare(
            "DELETE FROM generator_open_cloze_question_keys WHERE id = ?"
        );
        return $stmt->execute([(int)$id]);
    }

    // TRUNCATE
    public function vaciarTabla() {
        return $this->conexion->query("TRUNCATE TABLE generator_open_cloze_question_keys");
    }
}