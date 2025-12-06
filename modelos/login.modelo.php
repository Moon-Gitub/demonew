<?php

/**
 * MODELO DE LOGIN Y PROTECCIÓN CONTRA FUERZA BRUTA
 * 
 * Funciones para prevenir ataques de fuerza bruta en el login
 */

class ModeloLogin {
    
    const MAX_INTENTOS = 5;
    const TIEMPO_BLOQUEO = 900; // 15 minutos en segundos
    
    /**
     * Registrar intento fallido de login
     * 
     * @param string $usuario Nombre de usuario
     */
    static public function registrarIntentoFallido($usuario) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['intentos_login'])) {
            $_SESSION['intentos_login'] = [];
        }
        
        $intentosActuales = $_SESSION['intentos_login'][$usuario]['intentos'] ?? 0;
        
        $_SESSION['intentos_login'][$usuario] = [
            'intentos' => $intentosActuales + 1,
            'ultimo_intento' => time()
        ];
    }
    
    /**
     * Verificar si el usuario está bloqueado por intentos fallidos
     * 
     * @param string $usuario Nombre de usuario
     * @return bool True si está bloqueado
     */
    static public function estaBloqueado($usuario) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['intentos_login'][$usuario])) {
            return false;
        }
        
        $datos = $_SESSION['intentos_login'][$usuario];
        $tiempoTranscurrido = time() - $datos['ultimo_intento'];
        
        // Si pasó el tiempo de bloqueo, resetear
        if ($tiempoTranscurrido > self::TIEMPO_BLOQUEO) {
            unset($_SESSION['intentos_login'][$usuario]);
            return false;
        }
        
        return $datos['intentos'] >= self::MAX_INTENTOS;
    }
    
    /**
     * Resetear intentos después de login exitoso
     * 
     * @param string $usuario Nombre de usuario
     */
    static public function resetearIntentos($usuario) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (isset($_SESSION['intentos_login'][$usuario])) {
            unset($_SESSION['intentos_login'][$usuario]);
        }
    }
    
    /**
     * Obtener tiempo restante de bloqueo en minutos
     * 
     * @param string $usuario Nombre de usuario
     * @return int Minutos restantes (0 si no está bloqueado)
     */
    static public function tiempoRestanteBloqueo($usuario) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['intentos_login'][$usuario])) {
            return 0;
        }
        
        $datos = $_SESSION['intentos_login'][$usuario];
        $tiempoTranscurrido = time() - $datos['ultimo_intento'];
        $tiempoRestante = self::TIEMPO_BLOQUEO - $tiempoTranscurrido;
        
        return max(0, ceil($tiempoRestante / 60)); // Convertir a minutos
    }
    
    /**
     * Obtener número de intentos restantes
     * 
     * @param string $usuario Nombre de usuario
     * @return int Intentos restantes antes del bloqueo
     */
    static public function intentosRestantes($usuario) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['intentos_login'][$usuario])) {
            return self::MAX_INTENTOS;
        }
        
        $intentos = $_SESSION['intentos_login'][$usuario]['intentos'] ?? 0;
        return max(0, self::MAX_INTENTOS - $intentos);
    }
}

