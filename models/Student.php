<?php

require_once __DIR__ . '/../config/database.php';

class Student{

    private $conexion;

    public function __construct() {
        $this->conexion = Database::getConnection();
    }

    public function obtenerTodos() {
        $consulta = $this->conexion->query("SELECT * FROM students ORDER BY id ASC");
        return $consulta->fetchAll();
    }

    public function obtenerPorId($id) {
        $consulta = $this->conexion->prepare("SELECT * FROM students WHERE id = ?");
        $consulta->execute([$id]);
        return $consulta->fetch();
    }

    public function obtenerPorEmail($id) {
        $consulta = $this->conexion->prepare("SELECT * FROM students WHERE email = ?");
        $consulta->execute([$id]);
        return $consulta->fetch();
    }

    public function obtenerPorMatricula($id) {
        $consulta = $this->conexion->prepare("SELECT * FROM students WHERE matricula = ?");
        $consulta->execute([$id]);
        return $consulta->fetch();
    }


    public function crear($datos) {
        $campos = array_keys($datos);
        $placeholders = array_fill(0, count($datos), '?');
        $valores = array_values($datos);
    
        $sql = "INSERT INTO students (" . implode(',', $campos) . ") VALUES (" . implode(',', $placeholders) . ")";
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
    
        $sql = "UPDATE students SET " . implode(", ", $campos) . " WHERE id = ?";
        $consulta = $this->conexion->prepare($sql);
        return $consulta->execute($valores);
    }
    public function eliminar($id) {
        $consulta = $this->conexion->prepare("DELETE FROM students WHERE id = ?");
        return $consulta->execute([$id]);
    }
}