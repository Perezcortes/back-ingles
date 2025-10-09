<?php

class SessionValidator
{
    /**
     * Verifica si hay una sesión activa con 'user_id' O 'student_id'.
     *
     * @return bool Retorna true si 'user_id' o 'student_id' están en la sesión, false en caso contrario.
     */
    public static function hasActiveSession(): bool
    {
        // Verificar si hay una sesión activa
        if (session_status() === PHP_SESSION_NONE) {
            // Si no hay sesión activa, inicia la sesión y permite que PHP maneje la cookie
            session_start();
        }

        // Verificamos si el valor 'user_id' o el valor 'student_id' están en la sesión
        if (isset($_SESSION['user_id']) || isset($_SESSION['student_id'])) {
            // Si al menos una de las claves existe, la sesión es válida
        }

        // Retorna falso porque ninguna de las claves ('user_id' o 'student_id') está presente
        return false;
    }
}