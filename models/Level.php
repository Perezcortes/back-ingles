<?php
require_once __DIR__ . '/../config/database.php';

class Level {
    private $conexion;

    public function __construct() {
        $this->conexion = Database::getConnection();
    }

    public function obtenerTodos() {
        $consulta = $this->conexion->query("SELECT * FROM level ORDER BY id ASC");
        return $consulta->fetchAll();
    }

    public function obtenerPorId($id) {
        $consulta = $this->conexion->prepare("SELECT * FROM level WHERE id = ?");
        $consulta->execute([$id]);
        return $consulta->fetch();
    }

    public function crear($datos) {
        $campos = array_keys($datos);
        $placeholders = array_fill(0, count($datos), '?');
        $valores = array_values($datos);
    
        $sql = "INSERT INTO level (" . implode(',', $campos) . ") VALUES (" . implode(',', $placeholders) . ")";
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
    
        $sql = "UPDATE level SET " . implode(", ", $campos) . " WHERE id = ?";
        $consulta = $this->conexion->prepare($sql);
        return $consulta->execute($valores);
    }
    

    public function eliminarPorId($id) {
        $consulta = $this->conexion->prepare("DELETE FROM level WHERE id = ?");
        return $consulta->execute([$id]);
    }
}
