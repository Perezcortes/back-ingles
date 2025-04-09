<?php

$request = $_SERVER['REQUEST_URI'];
$method = $_SERVER['REQUEST_METHOD'];

require_once 'config/database.php';

if ($request === '/conexion' && $method === 'GET') {
    $pdo = Database::getConnection();
    $stmt = $pdo->query("SELECT NOW() AS fecha");
    $data = $stmt->fetch();

    echo json_encode([
        "conexion" => "ok",
        "fecha" => $data['fecha']
    ]);
    exit;
}
