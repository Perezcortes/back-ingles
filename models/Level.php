<?php
require_once __DIR__ . '/../config/database.php';

class Level {
    private $conexion;

    public function __construct() {
        $this->conexion = Database::getConnection();
    }

    public function obtenerTodos() {
        $consulta = $this->conexion->query("SELECT * FROM levels ORDER BY id ASC");
        return $consulta->fetchAll();
    }

    public function obtenerPorId($id) {
        $consulta = $this->conexion->prepare("SELECT * FROM levels WHERE id = ?");
        $consulta->execute([$id]);
        return $consulta->fetch();
    }

    public function crear($datos) {
        $campos = array_keys($datos);
        $placeholders = array_fill(0, count($datos), '?');
        $valores = array_values($datos);
    
        $sql = "INSERT INTO levels (" . implode(',', $campos) . ") VALUES (" . implode(',', $placeholders) . ")";
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
    
        $sql = "UPDATE levels SET " . implode(", ", $campos) . " WHERE id = ?";
        $consulta = $this->conexion->prepare($sql);
        return $consulta->execute($valores);
    }
    

    public function eliminar($id) {
        $consulta = $this->conexion->prepare("DELETE FROM levels WHERE id = ?");
        return $consulta->execute([$id]);
    }
}
