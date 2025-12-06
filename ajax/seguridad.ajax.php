<?php
/**
 * MIDDLEWARE DE SEGURIDAD PARA ARCHIVOS AJAX
 * 
 * Proporciona validación de sesión, CSRF y verificación AJAX
 * para todos los endpoints AJAX del sistema
 */

class SeguridadAjax {
    
    /**
     * Verificar que la sesión está activa
     */
    static public function verificarSesion() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION["iniciarSesion"]) || $_SESSION["iniciarSesion"] != "ok") {
            http_response_code(401);
            echo json_encode([
                'error' => true,
                'mensaje' => 'No autorizado. Por favor, inicia sesión.'
            ]);
            exit;
        }
    }
    
    /**
     * Verificar token CSRF
     */
    static public function verificarCSRF() {
        $token = $_POST['csrf_token'] ?? $_GET['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
        
        if (!$token || !isset($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
            http_response_code(403);
            echo json_encode([
                'error' => true,
                'mensaje' => 'Token CSRF inválido. Por favor, recarga la página.'
            ]);
            exit;
        }
    }
    
    /**
     * Verificar que la petición es AJAX
     */
    static public function verificarAjax() {
        if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || 
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
            http_response_code(403);
            echo json_encode([
                'error' => true,
                'mensaje' => 'Solo se permiten peticiones AJAX'
            ]);
            exit;
        }
    }
    
    /**
     * Inicialización completa de seguridad
     * 
     * @param bool $verificarCSRF Si debe verificar CSRF (default: true)
     */
    static public function inicializar($verificarCSRF = true) {
        self::verificarSesion();
        self::verificarAjax();
        
        if ($verificarCSRF) {
            self::verificarCSRF();
        }
    }
}

