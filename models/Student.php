<?php

require_once __DIR__ . '/../config/database.php';

class Student{

    private $conexion;

    public function __construct() {
        $this->conexion = Database::getConnection();
    }

    public function obtenerTodos() {
        $consulta = $this->conexion->query("SELECT * FROM student ORDER BY id ASC");
        return $consulta->fetchAll();
    }

    public function obtenerPorId($id) {
        $consulta = $this->conexion->prepare("SELECT * FROM student WHERE id = ?");
        $consulta->execute([$id]);
        return $consulta->fetch();
    }

    public function obtenerPorEmail($email) {
        $consulta = $this->conexion->prepare("SELECT * FROM student WHERE email = ?");
        $consulta->execute([$email]);
        return $consulta->fetch();
    }

    public function obtenerPorMatricula($matricula) {
        $consulta = $this->conexion->prepare("SELECT * FROM student WHERE matricula = ?");
        $consulta->execute([$matricula]);
        return $consulta->fetch();
    }


    public function crear($datos) {
        $campos = array_keys($datos);
        $placeholders = array_fill(0, count($datos), '?');
        $valores = array_values($datos);
    
        $sql = "INSERT INTO student (" . implode(',', $campos) . ") VALUES (" . implode(',', $placeholders) . ")";
        $consulta = $this->conexion->prepare($sql);
        return $consulta->execute($valores);
    }

    public function actualizarPorId($id, $datos) {
        $campos = [];
        $valores = [];
    
        foreach ($datos as $campo => $valor) {
            $campos[] = "$campo = ?";
            $valores[] = $valor;
        }
    
        $valores[] = $id; // El ID va al final para el WHERE
    
        $sql = "UPDATE student SET " . implode(", ", $campos) . " WHERE id = ?";
        $consulta = $this->conexion->prepare($sql);
        return $consulta->execute($valores);
    }

    public function eliminarPorId($id) {
        $consulta = $this->conexion->prepare("DELETE FROM student WHERE id = ?");
        return $consulta->execute([$id]);
    }
}