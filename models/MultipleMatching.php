<?php

require_once __DIR__ . '/../config/database.php';

class MultipleMatching {
    private $conexion;

    public function __construct() {
        $this->conexion = Database::getConnection();
    }

    // CREATE
    public function crear($datos) {
        $sql = "INSERT INTO multiple_matching (description, id_correct_answer, id_multiple_matching_template)
                VALUES (?, ?, ?)";
        $stmt = $this->conexion->prepare($sql);

        // id_correct_answer puede ser NULL
        $idCorrect = array_key_exists('id_correct_answer', $datos) && $datos['id_correct_answer'] !== null
            ? (int)$datos['id_correct_answer']
            : null;

        $stmt->bindValue(1, (string)$datos['description'], PDO::PARAM_STR);
        if ($idCorrect === null) {
            $stmt->bindValue(2, null, PDO::PARAM_NULL);
        } else {
            $stmt->bindValue(2, $idCorrect, PDO::PARAM_INT);
        }
        $stmt->bindValue(3, (int)$datos['id_multiple_matching_template'], PDO::PARAM_INT);

        return $stmt->execute();
    }

    // READ - listar todo
    public function obtenerTodos() {
        $stmt = $this->conexion->query("SELECT * FROM multiple_matching ORDER BY id ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // READ - por ID
    public function obtenerPorId($id) {
        $stmt = $this->conexion->prepare("SELECT * FROM multiple_matching WHERE id = ?");
        $stmt->execute([(int)$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // READ - por id_multiple_matching_template
    public function obtenerPorTemplate($id_multiple_matching_template) {
        $stmt = $this->conexion->prepare(
            "SELECT * FROM multiple_matching WHERE id_multiple_matching_template = ? ORDER BY id ASC"
        );
        $stmt->execute([(int)$id_multiple_matching_template]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // UPDATE - por ID (solo campos permitidos)
    public function actualizarPorId($id, $datos) {
        $permitidos = ['description','id_correct_answer','id_multiple_matching_template'];
        $set = [];
        $vals = [];

        foreach ($datos as $campo => $valor) {
            if (!in_array($campo, $permitidos, true)) continue;
            $set[] = "$campo = ?";
            if ($campo === 'id_multiple_matching_template') {
                $vals[] = (int)$valor;
            } elseif ($campo === 'id_correct_answer') {
                // permitir NULL explícito
                $vals[] = ($valor === null ? null : (int)$valor);
            } else {
                $vals[] = (string)$valor; // description
            }
        }

        if (empty($set)) return false;

        $sql = "UPDATE multiple_matching SET " . implode(", ", $set) . " WHERE id = ?";
        $stmt = $this->conexion->prepare($sql);

        // bind tipado
        $i = 1;
        foreach ($vals as $v) {
            if ($v === null) {
                $stmt->bindValue($i++, null, PDO::PARAM_NULL);
            } elseif (is_int($v)) {
                $stmt->bindValue($i++, $v, PDO::PARAM_INT);
            } else {
                $stmt->bindValue($i++, $v, PDO::PARAM_STR);
            }
        }
        $stmt->bindValue($i, (int)$id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    // DELETE - hard delete
    public function eliminarPorId($id) {
        $stmt = $this->conexion->prepare("DELETE FROM multiple_matching WHERE id = ?");
        return $stmt->execute([(int)$id]);
    }

    // TRUNCATE - vaciar tabla
    public function vaciarTabla() {
        return $this->conexion->query("TRUNCATE TABLE multiple_matching");
    }
}
