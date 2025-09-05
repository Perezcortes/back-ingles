<?php

require_once __DIR__ . '/../config/database.php';

class QuestionNumber {
    private $conexion;

    public function __construct() {
        $this->conexion = Database::getConnection();
    }

    // CREATE
    public function crear($datos) {
        $sql = "INSERT INTO question_number (id_multiple_choice_cloze)
                VALUES (?)";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bindValue(1, (int)$datos['id_multiple_choice_cloze'], PDO::PARAM_INT);
        return $stmt->execute();
    }

    // READ - listar activos
    public function obtenerTodos() {
        $stmt = $this->conexion->query(
            "SELECT * FROM question_number WHERE is_deleted = FALSE ORDER BY id ASC"
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // READ - por ID (sin filtrar is_deleted, según tus ejemplos)
    public function obtenerPorId($id) {
        $stmt = $this->conexion->prepare("SELECT * FROM question_number WHERE id = ?");
        $stmt->execute([(int)$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // READ - por id_multiple_choice_cloze (activos)
    public function obtenerPorMultipleChoiceCloze($id_multiple_choice_cloze) {
        $stmt = $this->conexion->prepare(
            "SELECT * FROM question_number 
             WHERE id_multiple_choice_cloze = ? AND is_deleted = FALSE
             ORDER BY id ASC"
        );
        $stmt->execute([(int)$id_multiple_choice_cloze]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // UPDATE - por ID (solo campos permitidos)
    public function actualizarPorId($id, $datos) {
        $permitidos = ['id_multiple_choice_cloze'];
        $set = [];
        $vals = [];

        foreach ($datos as $campo => $valor) {
            if (!in_array($campo, $permitidos, true)) continue;
            $set[] = "$campo = ?";
            $vals[] = (int)$valor;
        }

        if (empty($set)) return false;

        $sql = "UPDATE question_number SET " . implode(", ", $set) . " WHERE id = ?";
        $stmt = $this->conexion->prepare($sql);

        $i = 1;
        foreach ($vals as $v) $stmt->bindValue($i++, $v, PDO::PARAM_INT);
        $stmt->bindValue($i, (int)$id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    // RESTORE - desmarcar eliminado
    public function restaurarPorId($id) {
        $stmt = $this->conexion->prepare(
            "UPDATE question_number SET is_deleted = FALSE, deleted_at = NULL WHERE id = ?"
        );
        return $stmt->execute([(int)$id]);
    }

    // DELETE - Soft delete
    public function eliminarPorId($id) {
        $stmt = $this->conexion->prepare(
            "UPDATE question_number SET is_deleted = TRUE, deleted_at = NOW() WHERE id = ?"
        );
        return $stmt->execute([(int)$id]);
    }

    // DELETE - Hard delete
    public function eliminarPermanentePorId($id) {
        $stmt = $this->conexion->prepare("DELETE FROM question_number WHERE id = ?");
        return $stmt->execute([(int)$id]);
    }

    // TRUNCATE
    public function vaciarTabla() {
        return $this->conexion->query("TRUNCATE TABLE question_number");
    }
}