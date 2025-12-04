<?php
/**
 * FUNCIONES HELPER DEL SISTEMA
 * 
 * Funciones globales útiles para todo el sistema
 */

if (!function_exists('env')) {
    /**
     * Obtener una variable de entorno
     * 
     * Esta función intenta obtener variables de entorno en el siguiente orden:
     * 1. $_ENV (lo que usa Dotenv)
     * 2. $_SERVER (fallback)
     * 3. getenv() (fallback adicional)
     * 4. Valor por defecto
     * 
     * @param string $key Nombre de la variable
     * @param mixed $default Valor por defecto si no existe
     * @return mixed
     */
    function env($key, $default = null) {
        // Prioridad 1: $_ENV (donde Dotenv carga las variables)
        if (isset($_ENV[$key])) {
            return $_ENV[$key];
        }
        
        // Prioridad 2: $_SERVER
        if (isset($_SERVER[$key])) {
            return $_SERVER[$key];
        }
        
        // Prioridad 3: getenv()
        $value = getenv($key);
        if ($value !== false) {
            return $value;
        }
        
        // Si no se encuentra, devolver el valor por defecto
        return $default;
    }
}

