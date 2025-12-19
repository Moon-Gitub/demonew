<?php
/**
 * API ENDPOINT - LISTAR USUARIOS
 * Para sincronización con sistema offline
 * 
 * NOTA: Este endpoint permite sincronización sin sesión activa
 * pero requiere un token de API o ID de cliente para seguridad básica
 */

// Cargar autoload para Dotenv
require_once "../extensiones/vendor/autoload.php";

// Cargar .env si existe
if (file_exists(__DIR__ . '/../.env') && class_exists('Dotenv\Dotenv')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
    $dotenv->safeLoad();
}

// Verificación básica: requerir ID de cliente Moon como parámetro
$id_cliente = isset($_GET['id_cliente']) ? intval($_GET['id_cliente']) : null;

// Si no se proporciona ID, intentar verificar sesión (para compatibilidad)
if (!$id_cliente) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (!isset($_SESSION["iniciarSesion"]) || $_SESSION["iniciarSesion"] != "ok") {
        http_response_code(401);
        echo json_encode(['error' => 'Se requiere id_cliente como parámetro o sesión activa']);
        exit;
    }
}

require_once "../controladores/usuarios.controlador.php";
require_once "../modelos/usuarios.modelo.php";

header('Content-Type: application/json; charset=utf-8');

try {
    $usuarios = ModeloUsuarios::mdlMostrarUsuarios("usuarios", null, null);
    $resultado = [];
    
    if($usuarios && is_array($usuarios)) {
        foreach($usuarios as $user) {
            $resultado[] = [
                'id' => $user['id'],
                'usuario' => $user['usuario'],
                'password' => $user['password'], // Hash
                'nombre' => $user['nombre'],
                'perfil' => $user['perfil'],
                'sucursal' => $user['sucursal'],
                'estado' => $user['estado']
            ];
        }
    }
    
    echo json_encode($resultado);
    
} catch(Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
