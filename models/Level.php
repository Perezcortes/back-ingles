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

    public function crear($nombreNivel) {
        $consulta = $this->conexion->prepare("INSERT INTO levels (level_name) VALUES (?)");
        return $consulta->execute([$nombreNivel]);
    }

    public function actualizar($id, $nombreNivel) {
        $consulta = $this->conexion->prepare("UPDATE levels SET level_name = ? WHERE id = ?");
        return $consulta->execute([$nombreNivel, $id]);
    }

    public function eliminar($id) {
        $consulta = $this->conexion->prepare("DELETE FROM levels WHERE id = ?");
        return $consulta->execute([$id]);
    }
}
