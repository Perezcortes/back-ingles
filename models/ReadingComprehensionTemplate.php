<?php

require_once __DIR__ . '/../config/database.php';

class ReadingComprehensionTemplate {
    private $conexion;

    public function __construct() {
        $this->conexion = Database::getConnection();
    }

    // CREATE
    public function crear($datos) {
        $sql = "INSERT INTO reading_comprehension_template
                (title, instruction, question_number, topic, description, id_level)
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->conexion->prepare($sql);

        $stmt->bindValue(1, (string)$datos['title'], PDO::PARAM_STR);
        $stmt->bindValue(2, (string)$datos['instruction'], PDO::PARAM_STR);
        $stmt->bindValue(3, (int)$datos['question_number'], PDO::PARAM_INT);
        $stmt->bindValue(4, (string)$datos['topic'], PDO::PARAM_STR);
        $stmt->bindValue(5, (string)$datos['description'], PDO::PARAM_STR);
        $stmt->bindValue(6, (int)$datos['id_level'], PDO::PARAM_INT);

        return $stmt->execute();
    }

    // READ - listar activos
    public function obtenerTodos() {
        $stmt = $this->conexion->query(
            "SELECT * FROM reading_comprehension_template WHERE is_deleted = FALSE ORDER BY id ASC"
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // READ - por ID (sin filtrar is_deleted, como en tu SQL de ejemplo)
    public function obtenerPorId($id) {
        $stmt = $this->conexion->prepare("SELECT * FROM reading_comprehension_template WHERE id = ?");
        $stmt->execute([(int)$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // READ - por nivel (activos)
    public function obtenerPorNivel($id_level) {
        $stmt = $this->conexion->prepare(
            "SELECT * FROM reading_comprehension_template WHERE id_level = ? AND is_deleted = FALSE ORDER BY id ASC"
        );
        $stmt->execute([(int)$id_level]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // UPDATE - por ID (solo campos permitidos)
    public function actualizarPorId($id, $datos) {
        $permitidos = ['title','instruction','question_number','topic','description','id_level'];
        $set = [];
        $vals = [];

        foreach ($datos as $campo => $valor) {
            if (!in_array($campo, $permitidos, true)) continue;
            $set[] = "$campo = ?";
            if (in_array($campo, ['question_number','id_level'], true)) {
                $vals[] = (int)$valor;
            } else {
                $vals[] = (string)$valor;
            }
        }
        if (empty($set)) return false;

        $sql = "UPDATE reading_comprehension_template SET " . implode(", ", $set) . " WHERE id = ?";
        $stmt = $this->conexion->prepare($sql);

        $i = 1;
        foreach ($vals as $v) {
            $stmt->bindValue($i++, $v, is_int($v) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $stmt->bindValue($i, (int)$id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    // RESTORE - desmarcar eliminado
    public function restaurarPorId($id) {
        $stmt = $this->conexion->prepare(
            "UPDATE reading_comprehension_template SET is_deleted = FALSE, deleted_at = NULL WHERE id = ?"
        );
        return $stmt->execute([(int)$id]);
    }

    // DELETE - Soft delete
    public function eliminarPorId($id) {
        $stmt = $this->conexion->prepare(
            "UPDATE reading_comprehension_template SET is_deleted = TRUE, deleted_at = NOW() WHERE id = ?"
        );
        return $stmt->execute([(int)$id]);
    }

    // DELETE - Hard delete
    public function eliminarPermanentePorId($id) {
        $stmt = $this->conexion->prepare("DELETE FROM reading_comprehension_template WHERE id = ?");
        return $stmt->execute([(int)$id]);
    }

    // TRUNCATE
    public function vaciarTabla() {
        return $this->conexion->query("TRUNCATE TABLE reading_comprehension_template");
    }
}