<?php

class Database {
    private static $instance = null;
    private $conexion;

    private function __construct() {

        $env = $this->loadEnv();
        
        $host = $env['DB_HOST'] ?? 'localhost';
        $port = $env['DB_PORT'] ?? '3306';
        $db   = $env['DB_NAME'] ?? '';
        $user = $env['DB_USER'] ?? '';
        $pass = $env['DB_PASS'] ?? '';

        $dsn = "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4";

        try {
            $this->conexion = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]);
        } catch (PDOException $e) {
            $this->handleError($e->getMessage());
        }
    }

    public static function getConnection() {
        if (!self::$instance) {
            self::$instance = new Database();
        }

        return self::$instance->conexion;
    }

    private function loadEnv() {
        $path = __DIR__ . '/../.env';
        if (!file_exists($path)) {
            $this->handleError(".env no encontrado");
        }
    
        $vars = parse_ini_file($path);
        //var_dump($vars); con este comando vemos en el navegador el valor de las variables
        return $vars;
    }

    private function handleError($message) {
        $env = getenv('APP_ENV') ?: 'development';

        if ($env === 'development') {
            die(json_encode(['error' => $message]));
        } else {
            http_response_code(500);
            die(json_encode(['error' => 'Error interno del servidor']));
        }
    }
}
