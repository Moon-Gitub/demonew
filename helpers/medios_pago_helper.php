<?php
/**
 * Helper para generar opciones de medios de pago dinámicamente
 * 
 * Uso:
 * require_once "helpers/medios_pago_helper.php";
 * echo generarOpcionesMediosPago();
 */

// Cargar modelo si no está cargado
if(!class_exists('ModeloMediosPago')) {
    require_once __DIR__ . '/../modelos/medios_pago.modelo.php';
}

function generarOpcionesMediosPago($incluirMPQR = true) {
    $html = '<option value="">Medio de pago</option>';
    
    // MercadoPago QR es FIJO (siempre disponible)
    if($incluirMPQR) {
        $html .= '<option value="MPQR">Mercado Pago QR</option>';
    }
    
    // Cargar medios de pago activos desde la BD
    try {
        $mediosPago = ModeloMediosPago::mdlMostrarMediosPagoActivos();
        
        if($mediosPago && is_array($mediosPago)) {
            foreach($mediosPago as $medio) {
                $html .= '<option value="' . htmlspecialchars($medio["codigo"]) . '">' . htmlspecialchars($medio["nombre"]) . '</option>';
            }
        }
    } catch (Exception $e) {
        // Si hay error, usar valores por defecto
        $html .= '<option value="EF">Efectivo</option>';
        $html .= '<option value="TD">Tarjeta Débito</option>';
        $html .= '<option value="TC">Tarjeta Crédito</option>';
        $html .= '<option value="CH">Cheque</option>';
        $html .= '<option value="TR">Transferencia</option>';
        $html .= '<option value="CC">Cuenta Corriente</option>';
    }
    
    return $html;
}
