<?php
class Route
{
    // Arreglo estático que guarda todas las rutas registradas, agrupadas por método HTTP
    private static $routes = [];

    /**
     * Registra una ruta GET
     * $uri - Ruta del endpoint
     * $action - Controlador y método estático asociado al endpoint
     */
    public static function get($uri, $action, $middleware = null)
    {
        self::add('GET', $uri, $action, $middleware);
    }


    /**
     * Registra una ruta POST
     * $uri - Ruta del endpoint
     * $action - Controlador y método estático asociado al endpoint
     */
    public static function post($uri, $action, $middleware = null)
    {
        self::add('POST', $uri, $action, $middleware);
    }


    /**
     * Registra una ruta PUT
     * $uri - Ruta del endpoint
     * $action - Controlador y método estático asociado al endpoint
     */
    public static function put($uri, $action, $middleware = null)
    {
        self::add('PUT', $uri, $action, $middleware);
    }


    /**
     * Registra una ruta DELETE
     * $uri - Ruta del endpoint
     * $action - Controlador y método estático asociado al endpoint
     */
    public static function delete($uri, $action, $middleware = null)
    {
        self::add('DELETE', $uri, $action, $middleware);
    }


    private static function add($method, $uri, $action, $middleware = null)
    {
        // Convierte parámetros dinámicos como {id} en expresiones regulares que capturan valores
        // Explicación del patrón:
        // \{         → busca una llave de apertura literal "{"
        // [a-zA-Z_]  → el primer carácter del nombre del parámetro debe ser letra o guion bajo
        // [a-zA-Z0-9_]* → el resto pueden ser letras, números o guiones bajos (0 o más veces)
        // \}         → busca una llave de cierre literal "}"
        //
        // Lo anterior se reemplaza por: ([^/]+)
        // /level/{id} -> /level/{id}/  -> /level/([^/]+)
        $endpoint = preg_replace('/\{[a-zA-Z_][a-zA-Z0-9_]*\}/', '([^/]+)', $uri);

        // Se agregan delimitadores y anclajes:
        // ^ indica el inicio de la cadena, $ el final, así nos aseguramos de que la coincidencia sea exacta
        // rtrim(...) elimina una barra final si existe para que /user y /user/ no se traten diferente
        // /level/{id} -> /level/{id}/  -> /level/([^/]+) -> #^/level/([^/]+)$#
        $endpoint = "#^" . rtrim($endpoint, '/') . "$#";

        // Guarda la ruta convertida junto con su acción asociada, organizada por método HTTP
        self::$routes[$method][] = [
            'endpoint' => $endpoint,
            'action' => $action,
            'middleware' =>$middleware,
        ];
    }

    public static function dispatch($uri, $method)
    {
        // Elimina el último slash
        $uri = rtrim($uri, '/');

        // Convierte el método a mayúsculas
        $method = strtoupper($method);

        // Determina el tipo de contenido enviado por el cliente
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';

        // Según el Content-Type, obtén el cuerpo de la petición de forma adecuada
        if (stripos($contentType, 'application/json') !== false) {
            // JSON crudo (POST, PUT)
            $body = json_decode(file_get_contents('php://input'), true) ?? [];
        } elseif (stripos($contentType, 'multipart/form-data') !== false || stripos($contentType, 'application/x-www-form-urlencoded') !== false) {
            // Datos de formulario (POST con o sin archivos)
            $body = array_merge($_POST, $_FILES);
        } else {
            // Otro tipo (texto plano, etc.)
            $body = [];
        }

        //Si no existe el método http en nuestro arreglo de metodos, se cumple la condición.
        if (!isset(self::$routes[$method])) {
            http_response_code(405);
            echo json_encode(["error" => "Método no permitido"]);
            return;
        }

        // Recorremos las rutas registradas para el método HTTP recibido (GET, POST, etc.)
        foreach (self::$routes[$method] as $ruta) {

            // Verificamos si la URI solicitada coincide con el patrón de la ruta
            // Si hay coincidencia, $coincidencias incluirá los valores dinámicos extraídos (ej: {id})

            if (preg_match($ruta['endpoint'], $uri, $coincidencias)) {

                // Quitamos la coincidencia completa (posición 0), dejamos solo los parámetros
                array_shift($coincidencias); 

                // Extraemos el nombre del controlador y el método que debe ejecutarse
                [$controlador, $metodoAccion] = $ruta['action'];

                //Si hay un middleware, entonces lo ejecutamos primero
                if (isset($ruta['middleware']) && is_callable($ruta['middleware'])) {
                    $autorizado = call_user_func($ruta['middleware']);
                    if (!$autorizado) {
                        http_response_code(401);
                        echo json_encode(["error" => "No autorizado. Debes iniciar sesión."]);
                        return;
                    }
                }

                // Validamos que el controlador y el método realmente existan
                if (!class_exists($controlador) || !method_exists($controlador, $metodoAccion)) {
                    http_response_code(500);
                    echo json_encode(["error" => "No se encontró el controlador o método"]);
                    return;
                }

                // Ejecutamos el método del controlador, pasando los parámetros dinámicos de la URL y el cuerpo (body)
                return call_user_func_array([$controlador, $metodoAccion], array_merge($coincidencias, [$body]));
            }
        }

        http_response_code(404);
        echo json_encode(["error" => "Ruta no encontrada"]);
    }
}
