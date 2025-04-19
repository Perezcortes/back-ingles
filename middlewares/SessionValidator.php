<?php

class SessionValidator
{
    public static function check()
    {
        // Verificar si hay una sesión activa
        if (session_status() === PHP_SESSION_NONE) {
            // Si no hay sesión activa, inicia la sesión y permite que PHP maneje la cookie
            session_start();
        }

        // Verificamos si el valor 'student_id' está en la sesión
        if (isset($_SESSION['student_id'])) {
            // Si hay un student_id válido en la sesión, la sesión está activa
            return true;
        }

        // Retorna falso porque no hay una sesión válida
        return false;
    }


}