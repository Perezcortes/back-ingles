<?php

require_once __DIR__ . '/../config/database.php';

class OpenClozeTemplate {
    private $conexion;

    public function __construct() {
        $this->conexion = Database::getConnection();
    }

    // CREATE
    public function crear($datos) {
        $sql = "INSERT INTO open_cloze_template (title, instruction, topic, id_level)
                VALUES (?, ?, ?, ?)";
        $stmt = $this->conexion->prepare($sql);

        $stmt->bindValue(1, (string)$datos['title'], PDO::PARAM_STR);
        $stmt->bindValue(2, (string)$datos['instruction'], PDO::PARAM_STR);
        $stmt->bindValue(3, (string)$datos['topic'], PDO::PARAM_STR);
        $stmt->bindValue(4, (int)$datos['id_level'], PDO::PARAM_INT);

        return $stmt->execute();
    }

    // READ - listar activos
    public function obtenerTodos() {
        $stmt = $this->conexion->query(
            "SELECT * FROM open_cloze_template WHERE is_deleted = FALSE ORDER BY id ASC"
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // READ - por ID (sin filtrar is_deleted, como en tus consultas de ejemplo)
    public function obtenerPorId($id) {
        $stmt = $this->conexion->prepare("SELECT * FROM open_cloze_template WHERE id = ?");
        $stmt->execute([(int)$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // READ - por nivel (activos)
    public function obtenerPorNivel($id_level) {
        $stmt = $this->conexion->prepare(
            "SELECT * FROM open_cloze_template WHERE id_level = ? AND is_deleted = FALSE ORDER BY id ASC"
        );
        $stmt->execute([(int)$id_level]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // UPDATE
    public function actualizarPorId($id, $datos) {
        $permitidos = ['title','instruction','topic','id_level'];
        $set = [];
        $vals = [];

        foreach ($datos as $campo => $valor) {
            if (!in_array($campo, $permitidos, true)) continue;
            $set[] = "$campo = ?";
            if ($campo === 'id_level') {
                $vals[] = (int)$valor;
            } else {
                $vals[] = (string)$valor;
            }
        }
        if (empty($set)) return false;

        $sql = "UPDATE open_cloze_template SET " . implode(", ", $set) . " WHERE id = ?";
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
            "UPDATE open_cloze_template SET is_deleted = FALSE, deleted_at = NULL WHERE id = ?"
        );
        return $stmt->execute([(int)$id]);
    }

    // DELETE - Soft
    public function eliminarPorId($id) {
        $stmt = $this->conexion->prepare(
            "UPDATE open_cloze_template SET is_deleted = TRUE, deleted_at = NOW() WHERE id = ?"
        );
        return $stmt->execute([(int)$id]);
    }

    // DELETE - Hard
    public function eliminarPermanentePorId($id) {
        $stmt = $this->conexion->prepare("DELETE FROM open_cloze_template WHERE id = ?");
        return $stmt->execute([(int)$id]);
    }

    // TRUNCATE
    public function vaciarTabla() {
        return $this->conexion->query("TRUNCATE TABLE open_cloze_template");
    }
}