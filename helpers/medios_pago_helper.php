<?php
/**
 * Helper para generar opciones de medios de pago dinámicamente
 * 
 * Uso:
 * require_once "helpers/medios_pago_helper.php";
 * echo generarOpcionesMediosPago();
 */

function generarOpcionesMediosPago($incluirMPQR = true) {
    $html = '<option value="">Medio de pago</option>';
    
    // MercadoPago QR es FIJO (siempre disponible)
    if($incluirMPQR) {
        $html .= '<option value="MPQR">Mercado Pago QR</option>';
    }
    
    // Cargar medios de pago activos desde la BD
    $mediosPago = ModeloMediosPago::mdlMostrarMediosPagoActivos();
    
    if($mediosPago) {
        foreach($mediosPago as $medio) {
            $html .= '<option value="' . htmlspecialchars($medio["codigo"]) . '">' . htmlspecialchars($medio["nombre"]) . '</option>';
        }
    }
    
    return $html;
}
