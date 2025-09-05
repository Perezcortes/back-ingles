<?php

require_once __DIR__ . '/../config/database.php';

class OpenClozeQuestion {
    private $conexion;

    public function __construct() {
        $this->conexion = Database::getConnection();
    }

    // CREATE
    public function crear($datos) {
        $sql = "INSERT INTO open_cloze_question (texto, counter, id_open_cloze_template)
                VALUES (?, ?, ?)";
        $stmt = $this->conexion->prepare($sql);

        $stmt->bindValue(1, (string)$datos['texto'], PDO::PARAM_STR);
        $stmt->bindValue(2, (int)$datos['counter'], PDO::PARAM_INT);
        $stmt->bindValue(3, (int)$datos['id_open_cloze_template'], PDO::PARAM_INT);

        return $stmt->execute();
    }

    // READ - listar activos
    public function obtenerTodos() {
        $stmt = $this->conexion->query(
            "SELECT * FROM open_cloze_question WHERE is_deleted = FALSE ORDER BY id ASC"
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // READ - por ID (sin filtrar is_deleted, como en tus consultas de ejemplo)
    public function obtenerPorId($id) {
        $stmt = $this->conexion->prepare("SELECT * FROM open_cloze_question WHERE id = ?");
        $stmt->execute([(int)$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // READ - por template (activos)
    public function obtenerPorTemplate($id_open_cloze_template) {
        $stmt = $this->conexion->prepare(
            "SELECT * FROM open_cloze_question 
             WHERE id_open_cloze_template = ? AND is_deleted = FALSE
             ORDER BY id ASC"
        );
        $stmt->execute([(int)$id_open_cloze_template]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // UPDATE - por ID (solo campos permitidos)
    public function actualizarPorId($id, $datos) {
        $permitidos = ['texto','counter','id_open_cloze_template'];
        $set = [];
        $vals = [];

        foreach ($datos as $campo => $valor) {
            if (!in_array($campo, $permitidos, true)) continue;
            $set[] = "$campo = ?";
            if ($campo === 'counter' || $campo === 'id_open_cloze_template') {
                $vals[] = (int)$valor;
            } else { // texto
                $vals[] = (string)$valor;
            }
        }
        if (empty($set)) return false;

        $sql = "UPDATE open_cloze_question SET " . implode(", ", $set) . " WHERE id = ?";
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
            "UPDATE open_cloze_question SET is_deleted = FALSE, deleted_at = NULL WHERE id = ?"
        );
        return $stmt->execute([(int)$id]);
    }

    // DELETE - Soft delete
    public function eliminarPorId($id) {
        $stmt = $this->conexion->prepare(
            "UPDATE open_cloze_question SET is_deleted = TRUE, deleted_at = NOW() WHERE id = ?"
        );
        return $stmt->execute([(int)$id]);
    }

    // DELETE - Hard delete
    public function eliminarPermanentePorId($id) {
        $stmt = $this->conexion->prepare("DELETE FROM open_cloze_question WHERE id = ?");
        return $stmt->execute([(int)$id]);
    }

    // TRUNCATE
    public function vaciarTabla() {
        return $this->conexion->query("TRUNCATE TABLE open_cloze_question");
    }
}