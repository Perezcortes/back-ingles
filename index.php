<?php
// =================== CONFIGURACIÓN CORS ===================
$allowedOrigins = ['http://localhost:8094'];
//agregar mas fuentes de origen si es necesario
$allowedOrigins = ['http://localhost:8095'];

if (isset($_SERVER['HTTP_ORIGIN']) && in_array($_SERVER['HTTP_ORIGIN'], $allowedOrigins)) {
    header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
    header("Access-Control-Allow-Headers: Content-Type");
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
}

// Manejo de preflight request (OPTIONS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}
// ==========================================================

//Le indicamos al cliente que lo que le vamos a proporcionar es JSON.
header("Content-Type: application/json");


// Importar clase Route, require_once asegura que el archivo se incluya una sola vez,
// evitando errores por múltiples inclusiones.
require_once 'router/Route.php';

// Definir rutas
require_once 'routes/web.php';

// Extrae la ruta de la URL solicitada.
// http://localhost:8093/tu/ruta/de/consulta
// $uri = /tu/ruta/de/consulta
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Obtenemos el método de la petición por ejemplo, 'GET', 'HEAD', 'POST', 'PUT'.
$method = $_SERVER['REQUEST_METHOD'];

//Con este codigo vemos los valores de las variables en el navegador.
//var_dump($uri);

//Finalmente ocupamos el Route, para dirigir al endpoint con su método.
//Con el operador de resolución de ámbito, no es necesario instanciar la clase Route.
//Es suficiente con acceder al método estático dispatch con el operador ::
Route::dispatch($uri, $method);
