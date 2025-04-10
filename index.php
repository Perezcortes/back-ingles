<?php
header("Content-Type: application/json");

// Importar clase router
require_once 'core/Route.php';

// Definir rutas
require_once 'routes/web.php';

// Ejecutar router
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

Route::dispatch($uri, $method);
