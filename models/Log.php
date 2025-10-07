<?php
// models/Log.php

require_once __DIR__ . '/../config/database.php';

class Log
{
    private $conn;
    private $table_name = "logs";

    public function __construct()
    {
        $this->conn = Database::getConnection();
    }

    /**
     * Crea un nuevo registro de log en la base de datos.
     * * @param string $message El mensaje detallado del evento.
     * @return bool Devuelve true si la inserción fue exitosa, false en caso contrario.
     * @throws \PDOException Si ocurre un error durante la ejecución de la consulta SQL.
     */
    public function createLog($message)
    {
        //Consulta base
        $query = "INSERT INTO " . $this->table_name . " (message) VALUES (:message)";

        // Lanza un PDOException si hay error de sintaxis
        $stmt = $this->conn->prepare($query);

        // Vincula el valor del mensaje al marcador de posición
        $stmt->bindParam(':message', $message);

        return $stmt->execute();
    }
}