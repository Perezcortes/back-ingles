<?php

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
