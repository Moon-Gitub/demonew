<?php
/**
 * Middleware de seguridad para archivos AJAX
 */

require_once dirname(__DIR__) . "/extensiones/vendor/autoload.php";

class SeguridadAjax {

    private static $envInicializado = false;

    /**
     * Cargar variables de entorno sin duplicar trabajo
     */
    private static function inicializarEntorno() {
        if (self::$envInicializado) {
            return;
        }

        $raiz = dirname(__DIR__);
        if (file_exists($raiz . "/.env")) {
            $dotenv = Dotenv\Dotenv::createImmutable($raiz);
            $dotenv->safeLoad();
        }

        self::$envInicializado = true;
    }
    
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
                'mensaje' => 'No autorizado'
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
                'mensaje' => 'Token CSRF inválido'
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
                'mensaje' => 'Petición inválida'
            ]);
            exit;
        }
    }
    
    /**
     * Inicialización completa
     */
    static public function inicializar($verificarCSRF = true) {
        self::inicializarEntorno();
        self::verificarSesion();
        self::verificarAjax();
        
        if ($verificarCSRF) {
            self::verificarCSRF();
        }
    }
}
