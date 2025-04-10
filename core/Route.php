<?php

class Route {
    private static $routes = [];

    public static function get($uri, $action)    { self::add('GET', $uri, $action); }
    public static function post($uri, $action)   { self::add('POST', $uri, $action); }
    public static function put($uri, $action)    { self::add('PUT', $uri, $action); }
    public static function delete($uri, $action) { self::add('DELETE', $uri, $action); }

    private static function add($method, $uri, $action) {
        // Convertir /level/getOne/{id} → regex para extraer el parámetro
        $pattern = preg_replace('/\{[a-zA-Z_][a-zA-Z0-9_]*\}/', '([^/]+)', $uri);
        $pattern = "#^" . rtrim($pattern, '/') . "$#";

        self::$routes[$method][] = [
            'pattern' => $pattern,
            'action' => $action
        ];
    }

    public static function dispatch($uri, $method) {
        $uri = rtrim($uri, '/');
        $method = strtoupper($method);
        $body = json_decode(file_get_contents('php://input'), true) ?? [];

        if (!isset(self::$routes[$method])) {
            http_response_code(405);
            echo json_encode(["error" => "Método no permitido"]);
            return;
        }

        foreach (self::$routes[$method] as $ruta) {
            if (preg_match($ruta['pattern'], $uri, $coincidencias)) {
                array_shift($coincidencias); // quitamos la coincidencia completa

                [$controlador, $metodoAccion] = $ruta['action'];

                if (!class_exists($controlador) || !method_exists($controlador, $metodoAccion)) {
                    http_response_code(500);
                    echo json_encode(["error" => "No se encontró el controlador o método"]);
                    return;
                }

                // Mandamos los parámetros dinámicos + body si existe
                return call_user_func_array([$controlador, $metodoAccion], array_merge($coincidencias, [$body]));
            }
        }

        http_response_code(404);
        echo json_encode(["error" => "Ruta no encontrada"]);
    }
}
