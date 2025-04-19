<?php

class AuthenticationMiddleware
{
    public static function check()
    {
        // Iniciar la sesión si no está iniciada
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        // Verificar si el usuario tiene una sesión activa (por ejemplo, si está logueado)
        if (isset($_SESSION['student_id'])) {
            // Si está autenticado, retornar true
            return true;
        }

        // Si no está autenticado, retornar false
        return false;
    }
}