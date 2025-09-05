<?php

require_once __DIR__ . '/../config/database.php';

class MultipleChoiceCloze {
    private $conexion;

    public function __construct() {
        $this->conexion = Database::getConnection();
    }

    // CREATE
    public function crear($datos) {
        $sql = "INSERT INTO multiple_choice_cloze (counter, texto, id_template_multiple_choice_cloze)
                VALUES (?, ?, ?)";
        $stmt = $this->conexion->prepare($sql);

        $stmt->bindValue(1, (int)$datos['counter'], PDO::PARAM_INT);
        $stmt->bindValue(2, (string)$datos['texto'], PDO::PARAM_STR);
        $stmt->bindValue(3, (int)$datos['id_template_multiple_choice_cloze'], PDO::PARAM_INT);

        return $stmt->execute();
    }

    // READ - listar activos
    public function obtenerTodos() {
        $stmt = $this->conexion->query(
            "SELECT * FROM multiple_choice_cloze WHERE is_deleted = FALSE ORDER BY id ASC"
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // READ - por ID (sin filtrar is_deleted, igual a tus ejemplos)
    public function obtenerPorId($id) {
        $stmt = $this->conexion->prepare("SELECT * FROM multiple_choice_cloze WHERE id = ?");
        $stmt->execute([(int)$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // READ - por plantilla (activos)
    public function obtenerPorTemplate($id_template_multiple_choice_cloze) {
        $stmt = $this->conexion->prepare(
            "SELECT * FROM multiple_choice_cloze 
             WHERE id_template_multiple_choice_cloze = ? AND is_deleted = FALSE
             ORDER BY id ASC"
        );
        $stmt->execute([(int)$id_template_multiple_choice_cloze]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // UPDATE - por ID (solo campos permitidos)
    public function actualizarPorId($id, $datos) {
        $permitidos = ['counter','texto','id_template_multiple_choice_cloze'];
        $set = [];
        $vals = [];

        foreach ($datos as $campo => $valor) {
            if (!in_array($campo, $permitidos, true)) continue;

            $set[] = "$campo = ?";
            if ($campo === 'counter' || $campo === 'id_template_multiple_choice_cloze') {
                $vals[] = (int)$valor;
            } else { // texto
                $vals[] = (string)$valor;
            }
        }

        if (empty($set)) return false;

        $sql = "UPDATE multiple_choice_cloze SET " . implode(", ", $set) . " WHERE id = ?";
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
            "UPDATE multiple_choice_cloze SET is_deleted = FALSE, deleted_at = NULL WHERE id = ?"
        );
        return $stmt->execute([(int)$id]);
    }

    // DELETE - Soft delete
    public function eliminarPorId($id) {
        $stmt = $this->conexion->prepare(
            "UPDATE multiple_choice_cloze SET is_deleted = TRUE, deleted_at = NOW() WHERE id = ?"
        );
        return $stmt->execute([(int)$id]);
    }

    // DELETE - Hard delete
    public function eliminarPermanentePorId($id) {
        $stmt = $this->conexion->prepare("DELETE FROM multiple_choice_cloze WHERE id = ?");
        return $stmt->execute([(int)$id]);
    }

    // TRUNCATE
    public function vaciarTabla() {
        return $this->conexion->query("TRUNCATE TABLE multiple_choice_cloze");
    }
}