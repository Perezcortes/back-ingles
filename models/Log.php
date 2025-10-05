<?php
// models/Log.php

require_once __DIR__ . '/../config/database.php';

class Log {
    private $conn;
    private $table_name = "logs";

    public function __construct() {
        $this->conn = Database::getConnection();
    }

    public function createLog($message) {
        $query = "INSERT INTO " . $this->table_name . " (message) VALUES (:message)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':message', $message);
        
        return $stmt->execute();
    }
}