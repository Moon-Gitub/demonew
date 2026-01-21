<?php
/**
 * VERIFICAR Y REGISTRAR PAGO POR PREFERENCE_ID
 * 
 * Este endpoint verifica si un pago fue aprobado mediante su preference_id
 * y lo registra automáticamente si no está registrado
 * 
 * Funciona como RESPALDO cuando el webhook no llega
 */

require_once "../controladores/mercadopago.controlador.php";
require_once "../controladores/sistema_cobro.controlador.php";

header('Content-Type: application/json');

try {
    $preferenceId = isset($_GET['preference_id']) ? $_GET['preference_id'] : null;
    
    if (!$preferenceId) {
        echo json_encode(['error' => true, 'mensaje' => 'Preference ID requerido']);
        exit;
    }
    
    error_log("=== VERIFICAR PAGO DESDE FRONTEND ===");
    error_log("Preference ID: $preferenceId");
    
    // Obtener credenciales
    $credenciales = ControladorMercadoPago::ctrObtenerCredenciales();
    
    // Buscar pagos con este preference_id en la API de MercadoPago
    $url = "https://api.mercadopago.com/v1/payments/search?preference_id=$preferenceId";
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Authorization: Bearer ' . $credenciales['access_token'],
        'Content-Type: application/json'
    ));
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode == 200) {
        $data = json_decode($response, true);
        
        // Buscar un pago aprobado
        $pagoAprobado = null;
        if (isset($data['results']) && is_array($data['results'])) {
            foreach ($data['results'] as $payment) {
                if (isset($payment['status']) && $payment['status'] === 'approved') {
                    $pagoAprobado = $payment;
                    break;
                }
            }
        }
        
        if ($pagoAprobado) {
            error_log("✅ PAGO APROBADO ENCONTRADO");
            error_log("Payment ID: " . $pagoAprobado['id']);
            error_log("Monto: " . $pagoAprobado['transaction_amount']);
            
            // Verificar si ya fue registrado
            if (ControladorMercadoPago::ctrVerificarPagoProcesado($pagoAprobado['id'])) {
                error_log("ℹ️ Pago ya procesado anteriormente");
                echo json_encode([
                    'error' => false,
                    'aprobado' => true,
                    'ya_procesado' => true,
                    'payment_id' => $pagoAprobado['id']
                ]);
                exit;
            }
            
            // Obtener id_cliente_moon
            $idClienteMoon = null;
            if (isset($pagoAprobado['external_reference']) && is_numeric($pagoAprobado['external_reference'])) {
                $idClienteMoon = intval($pagoAprobado['external_reference']);
            }
            
            if (!$idClienteMoon || $idClienteMoon <= 0) {
                error_log("❌ No se pudo obtener id_cliente_moon");
                echo json_encode([
                    'error' => true,
                    'mensaje' => 'No se pudo identificar el cliente'
                ]);
                exit;
            }
            
            // Preparar datos del pago
            $fechaPago = date('Y-m-d H:i:s');
            if (isset($pagoAprobado['date_approved']) && !empty($pagoAprobado['date_approved'])) {
                $fechaAprobada = strtotime($pagoAprobado['date_approved']);
                if ($fechaAprobada !== false) {
                    $fechaPago = date('Y-m-d H:i:s', $fechaAprobada);
                }
            }
            
            $datosPago = array(
                'id_cliente_moon' => $idClienteMoon,
                'payment_id' => $pagoAprobado['id'],
                'preference_id' => $preferenceId,
                'monto' => $pagoAprobado['transaction_amount'],
                'estado' => 'approved',
                'fecha_pago' => $fechaPago,
                'payment_type' => isset($pagoAprobado['payment_type_id']) ? $pagoAprobado['payment_type_id'] : null,
                'payment_method_id' => isset($pagoAprobado['payment_method_id']) ? $pagoAprobado['payment_method_id'] : null,
                'datos_json' => json_encode($pagoAprobado)
            );
            
            // REGISTRAR PAGO
            error_log("📝 Registrando pago en mercadopago_pagos...");
            $resultadoPago = ControladorMercadoPago::ctrRegistrarPagoConfirmado($datosPago);
            
            if ($resultadoPago === "ok") {
                error_log("✅ Pago registrado en mercadopago_pagos");
                
                // ACTUALIZAR CUENTA CORRIENTE
                error_log("📝 Actualizando cuenta corriente...");
                $resultadoCtaCte = ControladorSistemaCobro::ctrRegistrarMovimientoCuentaCorriente(
                    $idClienteMoon,
                    $pagoAprobado['transaction_amount']
                );
                
                if ($resultadoCtaCte === "ok") {
                    error_log("✅ Cuenta corriente actualizada");
                    
                    // DESBLOQUEAR CLIENTE
                    ControladorSistemaCobro::ctrActualizarClientesCobro($idClienteMoon, 0);
                    error_log("✅ Cliente desbloqueado");
                    
                    // ACTUALIZAR ESTADO DEL INTENTO
                    require_once "../modelos/mercadopago.modelo.php";
                    ModeloMercadoPago::mdlActualizarEstadoIntento($preferenceId, 'aprobado');
                    error_log("✅ Intento actualizado");
                    
                    echo json_encode([
                        'error' => false,
                        'aprobado' => true,
                        'payment_id' => $pagoAprobado['id'],
                        'id_cliente_moon' => $idClienteMoon,
                        'monto' => $pagoAprobado['transaction_amount'],
                        'registrado' => true
                    ]);
                } else {
                    error_log("❌ Error al actualizar cuenta corriente");
                    echo json_encode([
                        'error' => false,
                        'aprobado' => true,
                        'payment_id' => $pagoAprobado['id'],
                        'registrado_pago' => true,
                        'error_cuenta_corriente' => true
                    ]);
                }
            } else {
                error_log("❌ Error al registrar pago: " . (is_array($resultadoPago) ? json_encode($resultadoPago) : $resultadoPago));
                echo json_encode([
                    'error' => true,
                    'mensaje' => 'Error al registrar el pago'
                ]);
            }
        } else {
            // No hay pago aprobado aún
            echo json_encode([
                'error' => false,
                'aprobado' => false,
                'mensaje' => 'Pago pendiente o no encontrado'
            ]);
        }
    } else {
        error_log("❌ Error al buscar pagos (HTTP $httpCode)");
        echo json_encode([
            'error' => true,
            'mensaje' => 'Error al consultar MercadoPago'
        ]);
    }
    
} catch (Exception $e) {
    error_log("❌ Excepción verificando pago: " . $e->getMessage());
    echo json_encode([
        'error' => true,
        'mensaje' => $e->getMessage()
    ]);
}
?>
