<?php
/**
 * API ENDPOINT - ESTADO DE CUENTA
 * GET: Obtener estado de cuenta de un cliente Moon
 */

// Cargar autoload para Dotenv
require_once "../extensiones/vendor/autoload.php";

// Cargar .env si existe
if (file_exists(__DIR__ . '/../.env') && class_exists('Dotenv\Dotenv')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
    $dotenv->safeLoad();
}

// Este endpoint ya usa id_cliente como parámetro, así que no necesita sesión

require_once "../controladores/sistema_cobro.controlador.php";
require_once "../modelos/sistema_cobro.modelo.php";

header('Content-Type: application/json; charset=utf-8');

try {
    $id_cliente = isset($_GET['id']) ? intval($_GET['id']) : null;
    
    if(!$id_cliente) {
        http_response_code(400);
        echo json_encode(['error' => 'ID de cliente requerido']);
        exit;
    }
    
    // Obtener datos del cliente
    $cliente = ControladorSistemaCobro::ctrMostrarClientesCobro($id_cliente);
    
    if(!$cliente || !is_array($cliente)) {
        http_response_code(404);
        echo json_encode(['error' => 'Cliente no encontrado']);
        exit;
    }
    
    // Obtener saldo de cuenta corriente
    $ctaCte = ControladorSistemaCobro::ctrMostrarSaldoCuentaCorriente($id_cliente);
    
    $resultado = [
        'estado_bloqueo' => intval($cliente['estado_bloqueo'] ?? 0),
        'saldo' => floatval($ctaCte['saldo'] ?? 0.0),
        'ultimo_pago' => isset($cliente['ultimo_pago']) ? $cliente['ultimo_pago'] : null,
        'fecha_vencimiento' => isset($cliente['fecha_vencimiento']) ? $cliente['fecha_vencimiento'] : null
    ];
    
    echo json_encode($resultado);
    
} catch(Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
