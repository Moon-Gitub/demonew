<?php
/**
 * VERIFICAR PAGOS QR PENDIENTES
 * 
 * Este endpoint verifica automáticamente si hay pagos QR pendientes
 * que fueron aprobados en MercadoPago pero no se registraron en el sistema
 * 
 * Se puede llamar manualmente o con un cron job cada 5 minutos
 */

header('Content-Type: application/json');

// Cargar dependencias
require_once __DIR__ . '/../extensiones/vendor/autoload.php';

if (file_exists(__DIR__ . '/../.env') && class_exists('Dotenv\Dotenv')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
    $dotenv->load();
}

require_once __DIR__ . '/../controladores/mercadopago.controlador.php';
require_once __DIR__ . '/../controladores/sistema_cobro.controlador.php';
require_once __DIR__ . '/../modelos/mercadopago.modelo.php';
require_once __DIR__ . '/../modelos/conexion.php';

date_default_timezone_set('America/Argentina/Mendoza');

$respuesta = array(
    'error' => false,
    'mensaje' => '',
    'pagos_encontrados' => 0,
    'pagos_registrados' => 0
);

try {
    // Obtener credenciales
    $credenciales = ControladorMercadoPago::ctrObtenerCredenciales();
    
    if (empty($credenciales['access_token'])) {
        throw new Exception('No hay access_token configurado');
    }
    
    // Buscar intentos pendientes recientes (últimos 2 horas)
    $conexion = Conexion::conectarMoon();
    if (!$conexion) {
        throw new Exception('No se pudo conectar a BD Moon');
    }
    
    $stmt = $conexion->prepare("SELECT id, id_cliente_moon, preference_id, monto, fecha_creacion 
        FROM mercadopago_intentos 
        WHERE estado = 'pendiente' 
        AND preference_id IS NOT NULL 
        AND preference_id != ''
        AND fecha_creacion >= DATE_SUB(NOW(), INTERVAL 2 HOUR)
        ORDER BY fecha_creacion DESC");
    $stmt->execute();
    $intentos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($intentos)) {
        $respuesta['mensaje'] = 'No hay intentos pendientes recientes';
        echo json_encode($respuesta);
        exit;
    }
    
    $respuesta['pagos_encontrados'] = count($intentos);
    error_log("🔍 Verificando " . count($intentos) . " intentos pendientes para pagos QR");
    
    // Para cada intento, verificar si la preferencia tiene pagos aprobados
    foreach ($intentos as $intento) {
        $preferenceId = $intento['preference_id'];
        $idCliente = $intento['id_cliente_moon'];
        $monto = $intento['monto'];
        
        error_log("Verificando preference: $preferenceId, Cliente: $idCliente, Monto: $monto");
        
        // Consultar la preferencia en MP para obtener los payments asociados
        $preferenceUrl = "https://api.mercadopago.com/checkout/preferences/$preferenceId";
        $ch = curl_init($preferenceUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Authorization: Bearer ' . $credenciales['access_token'],
            'Content-Type: application/json'
        ));
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        
        $preferenceResponse = curl_exec($ch);
        $preferenceHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($preferenceHttpCode == 200) {
            $preference = json_decode($preferenceResponse, true);
            
            // Buscar payments asociados a esta preferencia
            // Los payments pueden estar en payment_info o necesitamos buscarlos por preference_id
            // Mejor: buscar payments por preference_id en MP
            $paymentsUrl = "https://api.mercadopago.com/v1/payments/search?preference_id=$preferenceId";
            $ch = curl_init($paymentsUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Authorization: Bearer ' . $credenciales['access_token'],
                'Content-Type: application/json'
            ));
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
            
            $paymentsResponse = curl_exec($ch);
            $paymentsHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($paymentsHttpCode == 200) {
                $paymentsData = json_decode($paymentsResponse, true);
                
                if (isset($paymentsData['results']) && is_array($paymentsData['results']) && count($paymentsData['results']) > 0) {
                    foreach ($paymentsData['results'] as $payment) {
                        // Solo procesar pagos aprobados que no estén ya registrados
                        if (isset($payment['status']) && $payment['status'] === 'approved') {
                            $paymentId = isset($payment['id']) ? $payment['id'] : null;
                            
                            if ($paymentId && !ControladorMercadoPago::ctrVerificarPagoProcesado($paymentId)) {
                                error_log("✅✅✅ PAGO QR ENCONTRADO Y NO REGISTRADO ✅✅✅");
                                error_log("   Payment ID: $paymentId");
                                error_log("   Cliente: $idCliente");
                                error_log("   Monto: " . (isset($payment['transaction_amount']) ? $payment['transaction_amount'] : 'N/A'));
                                
                                // Asegurar que el payment tenga external_reference
                                if (!isset($payment['external_reference']) || empty($payment['external_reference'])) {
                                    $payment['external_reference'] = strval($idCliente);
                                }
                                
                                // Registrar el pago
                                $fechaPago = date('Y-m-d H:i:s');
                                if (isset($payment['date_approved']) && !empty($payment['date_approved'])) {
                                    $fechaAprobada = strtotime($payment['date_approved']);
                                    if ($fechaAprobada !== false) {
                                        $fechaPago = date('Y-m-d H:i:s', $fechaAprobada);
                                    }
                                }
                                
                                $datosPago = array(
                                    'id_cliente_moon' => $idCliente,
                                    'payment_id' => $paymentId,
                                    'preference_id' => $preferenceId,
                                    'monto' => isset($payment['transaction_amount']) ? $payment['transaction_amount'] : 0,
                                    'estado' => 'approved',
                                    'fecha_pago' => $fechaPago,
                                    'payment_type' => isset($payment['payment_type_id']) ? $payment['payment_type_id'] : null,
                                    'payment_method_id' => isset($payment['payment_method_id']) ? $payment['payment_method_id'] : 'desconocido',
                                    'datos_json' => json_encode($payment)
                                );
                                
                                $resultadoPago = ControladorMercadoPago::ctrRegistrarPagoConfirmado($datosPago);
                                
                                if ($resultadoPago === "ok") {
                                    error_log("✅ Pago registrado en mercadopago_pagos");
                                    $respuesta['pagos_registrados']++;
                                    
                                    // Registrar en cuenta corriente
                                    if ($idCliente > 0) {
                                        $resultadoCtaCte = ControladorSistemaCobro::ctrRegistrarMovimientoCuentaCorriente(
                                            $idCliente,
                                            $datosPago['monto']
                                        );
                                        
                                        if ($resultadoCtaCte === "ok") {
                                            error_log("✅ Pago registrado en cuenta corriente");
                                            
                                            // Desbloquear cliente si estaba bloqueado
                                            ControladorSistemaCobro::ctrActualizarClientesCobro($idCliente, 0);
                                            error_log("✅ Cliente desbloqueado si estaba bloqueado");
                                        }
                                    }
                                    
                                    // Actualizar estado del intento
                                    ControladorMercadoPago::ctrActualizarEstadoIntento($preferenceId, 'aprobado', null);
                                    error_log("✅ Estado del intento actualizado");
                                } else {
                                    error_log("❌ Error al registrar pago: " . (is_array($resultadoPago) ? json_encode($resultadoPago) : $resultadoPago));
                                }
                            }
                        }
                    }
                }
            }
        }
    }
    
    $respuesta['mensaje'] = "Verificación completada. {$respuesta['pagos_registrados']} pago(s) registrado(s)";
    
} catch (Exception $e) {
    error_log("ERROR en verificar-pagos-qr-pendientes: " . $e->getMessage());
    $respuesta['error'] = true;
    $respuesta['mensaje'] = $e->getMessage();
}

echo json_encode($respuesta);
