<?php 
  date_default_timezone_set('America/Argentina/Mendoza'); 
  $cbteDefecto = $objParametros->getCbteDefecto();
  $arrListasPrecio = $objParametros->getListasPrecio();
  $btnPadronAfip = (isset($arrayEmpresa["ws_padron"])) ? '' : 'disabled';
?>
<style>
/* ============================================
   ESTILOS MODERNOS PARA CREAR VENTA CAJA
   Solo cambios visuales - Sin tocar funcionalidad
   Responsive y mejorado
   ============================================ */

/* Mejorar tablas del formulario */
.crear-venta-caja .table {
    border: none !important;
    margin-bottom: 15px;
}

.crear-venta-caja .table td,
.crear-venta-caja .table th {
    border: none !important;
    padding: 12px 8px !important;
    vertical-align: middle;
}

/* Mejorar input-group-addon */
.crear-venta-caja .input-group-addon {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%) !important;
    border: 2px solid #e0e0e0 !important;
    border-right: none !important;
    color: #2c3e50 !important;
    font-weight: 600 !important;
    padding: 12px 15px !important;
    border-radius: 8px 0 0 8px !important;
}

.crear-venta-caja .input-group .form-control {
    border-left: none !important;
    border-radius: 0 8px 8px 0 !important;
}

.crear-venta-caja .input-group .form-control:focus {
    border-left: none !important;
}

/* Estilos visuales para el autocomplete de jQuery UI
   IMPORTANTE: NO forzar display/visibility/opacity para que jQuery UI
   pueda abrir/cerrar el menú correctamente. */
.ui-autocomplete {
    z-index: 99999 !important;
    position: absolute !important;
    max-height: 300px !important;
    overflow-y: auto !important;
    overflow-x: hidden !important;
    background: #ffffff !important;
    border: 2px solid #667eea !important;
    border-radius: 8px !important;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15) !important;
    padding: 5px 0 !important;
}

.ui-autocomplete .ui-menu-item {
    padding: 0 !important;
    margin: 0 !important;
    list-style: none !important;
}

.ui-autocomplete .ui-menu-item-wrapper {
    padding: 12px 15px !important;
    border: none !important;
    border-bottom: 1px solid #e0e0e0 !important;
    color: #2c3e50 !important;
    transition: all 0.2s ease !important;
    cursor: pointer !important;
    display: block !important;
}

.ui-autocomplete .ui-menu-item-wrapper:hover,
.ui-autocomplete .ui-menu-item-wrapper.ui-state-active {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
    color: #ffffff !important;
    border-bottom-color: #667eea !important;
}

/* Asegurar que los contenedores no corten el autocomplete */
.crear-venta-caja .box,
.crear-venta-caja .box-body,
.crear-venta-caja .input-group {
    overflow: visible !important;
}

/* Mejorar la sección superior con inputs y selects */
.crear-venta-caja .table:first-of-type {
    background: #ffffff !important;
    border-radius: 8px !important;
    padding: 10px !important;
    margin-bottom: 15px !important;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05) !important;
}

.crear-venta-caja .table:first-of-type td {
    padding: 10px 8px !important;
    vertical-align: middle !important;
}

/* Mejorar el input de búsqueda de cliente */
.crear-venta-caja #autocompletarClienteCaja {
    border-radius: 8px !important;
    padding: 12px 15px !important;
    font-size: 14px !important;
    min-height: 42px !important;
}

/* Mejorar el input de búsqueda de productos */
.crear-venta-caja #ventaCajaDetalle {
    border-radius: 8px !important;
    padding: 12px 15px !important;
    font-size: 14px !important;
    min-height: 42px !important;
    width: 100% !important;
}

.crear-venta-caja #ventaCajaDetalle:focus {
    border-color: #667eea !important;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1) !important;
}

/* Mejorar inputs y selects */
.crear-venta-caja .form-control.input-sm {
    padding: 12px 15px !important;
    font-size: 14px !important;
    height: auto !important;
    min-height: 42px !important;
}

.crear-venta-caja select.form-control.input-sm {
    min-height: 42px !important;
    height: 42px !important;
    padding: 12px 15px !important;
}

/* Mejorar botones */
.crear-venta-caja .btn {
    border-radius: 8px !important;
    padding: 10px 20px !important;
    font-weight: 600 !important;
    transition: all 0.3s ease !important;
}

.crear-venta-caja .btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
    border: none !important;
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3) !important;
}

.crear-venta-caja .btn-primary:hover {
    transform: translateY(-2px) !important;
    box-shadow: 0 6px 16px rgba(102, 126, 234, 0.4) !important;
}

.crear-venta-caja .btn-default {
    background: #ffffff !important;
    border: 2px solid #e0e0e0 !important;
    color: #2c3e50 !important;
}

.crear-venta-caja .btn-default:hover {
    background: #f8f9fa !important;
    border-color: #667eea !important;
    color: #667eea !important;
}

/* Mejorar labels y headers */
.crear-venta-caja .control-label {
    color: #2c3e50 !important;
    font-weight: 600 !important;
    font-size: 14px !important;
    margin-bottom: 8px !important;
}

.crear-venta-caja center {
    color: #2c3e50 !important;
    font-weight: 600 !important;
    font-size: 14px !important;
}

/* Mejorar separadores HR */
.crear-venta-caja hr {
    border: none !important;
    height: 2px !important;
    background: linear-gradient(90deg, transparent, #667eea, transparent) !important;
    margin: 20px 0 !important;
}

/* Mejorar el input de total grande */
.crear-venta-caja #nuevoPrecioNetoCajaForm {
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%) !important;
    border: 3px solid #667eea !important;
    border-radius: 12px !important;
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.15) !important;
    font-weight: 700 !important;
    color: #2c3e50 !important;
}

/* Mejorar box-footer */
.crear-venta-caja .box-footer {
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%) !important;
    border-top: 2px solid #e0e0e0 !important;
    padding: 20px !important;
    border-radius: 0 0 16px 16px !important;
}

/* Mejorar área de productos */
.crear-venta-caja #nuevoProductoCaja {
    min-height: 200px;
    max-height: 400px;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 8px;
    border: 2px dashed #e0e0e0;
}

.crear-venta-caja #nuevoProductoCaja:empty::before {
    content: "Los productos agregados aparecerán aquí";
    color: #95a5a6;
    font-style: italic;
    display: block;
    text-align: center;
    padding: 50px 20px;
}

/* Responsive - Mobile */
@media (max-width: 991px) {
    .crear-venta-caja .col-lg-7,
    .crear-venta-caja .col-lg-5 {
        width: 100% !important;
        margin-bottom: 20px;
    }
    
    .crear-venta-caja .table {
        display: block;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    
    .crear-venta-caja .table td,
    .crear-venta-caja .table th {
        white-space: nowrap;
        min-width: 120px;
    }
    
    .crear-venta-caja .input-group {
        margin-bottom: 10px;
    }
    
    .crear-venta-caja .col-md-3,
    .crear-venta-caja .col-md-9 {
        width: 100% !important;
        margin-bottom: 10px;
    }
    
    .crear-venta-caja #nuevoPrecioNetoCajaForm {
        font-size: 36px !important;
    }
}

/* Responsive - Tablet */
@media (min-width: 768px) and (max-width: 991px) {
    .crear-venta-caja .col-lg-7 {
        width: 100% !important;
    }
    
    .crear-venta-caja .col-lg-5 {
        width: 100% !important;
    }
}

/* Mejorar modales */
.crear-venta-caja .modal-content {
    border-radius: 16px !important;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2) !important;
    border: none !important;
}

.crear-venta-caja .modal-header {
    border-radius: 16px 16px 0 0 !important;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
    border-bottom: none !important;
    padding: 20px !important;
}

.crear-venta-caja .modal-footer {
    border-top: 2px solid #e0e0e0 !important;
    padding: 20px !important;
    border-radius: 0 0 16px 16px !important;
}

/* Estilos específicos para Modal Cobro de Venta */
#modalCobrarVenta .modal-content {
    border-radius: 16px !important;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2) !important;
    border: none !important;
    overflow: hidden;
}

#modalCobrarVenta .modal-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
    color: #ffffff !important;
    border-bottom: none !important;
    padding: 20px 25px !important;
    border-radius: 16px 16px 0 0 !important;
}

#modalCobrarVenta .modal-header .modal-title {
    font-size: 20px !important;
    font-weight: 700 !important;
    color: #ffffff !important;
}

#modalCobrarVenta .modal-body {
    padding: 25px !important;
    background: #ffffff !important;
}

#modalCobrarVenta .modal-footer {
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%) !important;
    border-top: 2px solid #e0e0e0 !important;
    padding: 20px 25px !important;
    border-radius: 0 0 16px 16px !important;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

/* Mejorar input PAGO */
#modalCobrarVenta #nuevoValorEntrega {
    font-size: 18px !important;
    font-weight: 600 !important;
    text-align: center !important;
    border: 2px solid #667eea !important;
    border-radius: 8px !important;
    padding: 12px 15px !important;
    min-height: 48px !important;
}

#modalCobrarVenta .input-group-addon[style*="background-color: #eee"] {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%) !important;
    border: 2px solid #e0e0e0 !important;
    border-right: none !important;
    font-weight: 700 !important;
    font-size: 14px !important;
    padding: 12px 15px !important;
    color: #2c3e50 !important;
    border-radius: 8px 0 0 8px !important;
}

/* Mejorar botón agregar medio de pago */
#modalCobrarVenta #agregarMedioPago {
    background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%) !important;
    border: none !important;
    border-radius: 8px 0 0 8px !important;
    padding: 12px 18px !important;
    color: #ffffff !important;
    font-weight: 600 !important;
    box-shadow: 0 2px 8px rgba(17, 153, 142, 0.3) !important;
    transition: all 0.3s ease !important;
}

#modalCobrarVenta #agregarMedioPago:hover {
    transform: translateY(-2px) !important;
    box-shadow: 0 4px 12px rgba(17, 153, 142, 0.4) !important;
}

/* Mejorar select medio de pago */
#modalCobrarVenta #nuevoMetodoPagoCaja {
    border-radius: 0 8px 8px 0 !important;
    border-left: none !important;
    padding: 12px 15px !important;
    min-height: 48px !important;
    font-size: 14px !important;
}

/* Mejorar tabla de resumen */
#modalCobrarVenta .table {
    border: none !important;
    margin-bottom: 0 !important;
}

#modalCobrarVenta .table td {
    border: none !important;
    padding: 12px 8px !important;
    vertical-align: middle !important;
}

#modalCobrarVenta .table td:first-child {
    font-weight: 600 !important;
    color: #2c3e50 !important;
    width: 40% !important;
}

/* Mejorar inputs de total, descuento, interés */
#modalCobrarVenta #nuevoPrecioNetoCaja,
#modalCobrarVenta #nuevoTotalVentaCaja {
    font-size: 18px !important;
    font-weight: 700 !important;
    text-align: center !important;
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%) !important;
    border: 2px solid #667eea !important;
    border-radius: 8px !important;
    padding: 12px 15px !important;
    min-height: 48px !important;
}

#modalCobrarVenta #nuevoTotalVentaCaja {
    border: 3px solid #667eea !important;
    background: linear-gradient(135deg, #fff5f5 0%, #ffffff 100%) !important;
    font-size: 20px !important;
    color: #667eea !important;
}

/* Mejorar inputs de descuento e interés */
#modalCobrarVenta .nuevoDescuentoCaja,
#modalCobrarVenta .nuevoInteresCaja {
    border-radius: 8px !important;
    border: 2px solid #e0e0e0 !important;
    padding: 10px 12px !important;
    min-height: 42px !important;
}

#modalCobrarVenta .nuevoDescuentoCaja:focus,
#modalCobrarVenta .nuevoInteresCaja:focus {
    border-color: #667eea !important;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1) !important;
}

/* Mejorar tabla de métodos de pago mixto */
#modalCobrarVenta #listadoMetodosPagoMixto {
    border-radius: 8px !important;
    overflow: hidden !important;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05) !important;
}

#modalCobrarVenta #listadoMetodosPagoMixto thead {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
    color: #ffffff !important;
}

#modalCobrarVenta #listadoMetodosPagoMixto thead th {
    color: #ffffff !important;
    font-weight: 600 !important;
    padding: 12px 15px !important;
    border: none !important;
}

#modalCobrarVenta #listadoMetodosPagoMixto tbody td {
    padding: 10px 15px !important;
    border-bottom: 1px solid #e0e0e0 !important;
}

#modalCobrarVenta #listadoMetodosPagoMixto tfoot {
    background: #f8f9fa !important;
    font-weight: 700 !important;
}

#modalCobrarVenta #nuevoValorSaldo {
    font-size: 20px !important;
    font-weight: 700 !important;
    color: #f5576c !important;
}

/* Mejorar botones del footer */
#modalCobrarVenta .modal-footer .btn {
    padding: 12px 30px !important;
    font-size: 16px !important;
    font-weight: 600 !important;
    border-radius: 8px !important;
    min-width: 180px !important;
    transition: all 0.3s ease !important;
}

#modalCobrarVenta .modal-footer .btn-default {
    background: #ffffff !important;
    border: 2px solid #e0e0e0 !important;
    color: #2c3e50 !important;
}

#modalCobrarVenta .modal-footer .btn-default:hover {
    background: #f8f9fa !important;
    border-color: #667eea !important;
    color: #667eea !important;
    transform: translateY(-2px) !important;
}

#modalCobrarVenta .modal-footer .btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
    border: none !important;
    color: #ffffff !important;
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3) !important;
}

#modalCobrarVenta .modal-footer .btn-primary:hover {
    transform: translateY(-2px) !important;
    box-shadow: 0 6px 16px rgba(102, 126, 234, 0.4) !important;
}

/* Mejorar datos cuenta corriente */
#modalCobrarVenta #datosCuentaCorrienteCliente {
    color: #2c3e50 !important;
    font-weight: 600 !important;
    padding: 10px 15px !important;
    background: #f8f9fa !important;
    border-radius: 8px !important;
    display: inline-block !important;
}

/* Estilos específicos para Modal Ticket */
#modalImprimirTicketCaja .modal-content {
    border-radius: 16px !important;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2) !important;
    border: none !important;
    overflow: hidden;
}

#modalImprimirTicketCaja .modal-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
    color: #ffffff !important;
    border-bottom: none !important;
    padding: 20px 25px !important;
    border-radius: 16px 16px 0 0 !important;
}

#modalImprimirTicketCaja .modal-header .modal-title {
    font-size: 20px !important;
    font-weight: 700 !important;
    color: #ffffff !important;
}

#modalImprimirTicketCaja .modal-body {
    padding: 25px !important;
    background: #ffffff !important;
}

#modalImprimirTicketCaja .modal-footer {
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%) !important;
    border-top: 2px solid #e0e0e0 !important;
    padding: 20px 25px !important;
    border-radius: 0 0 16px 16px !important;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 10px;
}

/* Mejorar contenido del ticket */
#modalImprimirTicketCaja #impTicketCobroCaja {
    font-family: 'Courier New', monospace !important;
    background: #ffffff !important;
    padding: 20px !important;
    border-radius: 8px !important;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05) !important;
    line-height: 1.8 !important;
    color: #2c3e50 !important;
}

#modalImprimirTicketCaja #impTicketCobroCaja b {
    color: #667eea !important;
    font-weight: 700 !important;
}

#modalImprimirTicketCaja #impTicketCobroCaja hr {
    border: none !important;
    height: 2px !important;
    background: linear-gradient(90deg, transparent, #667eea, transparent) !important;
    margin: 15px 0 !important;
}

/* Mejorar tabla de detalle */
#modalImprimirTicketCaja #tckDetalleVentaCaja {
    width: 100% !important;
    border-collapse: collapse !important;
    margin: 15px 0 !important;
    border-radius: 8px !important;
    overflow: hidden !important;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05) !important;
}

#modalImprimirTicketCaja #tckDetalleVentaCaja th {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
    color: #ffffff !important;
    padding: 12px 8px !important;
    font-weight: 600 !important;
    text-align: center !important;
    border: none !important;
}

#modalImprimirTicketCaja #tckDetalleVentaCaja td {
    padding: 10px 8px !important;
    border-bottom: 1px solid #e0e0e0 !important;
    text-align: center !important;
}

#modalImprimirTicketCaja #tckDetalleVentaCaja tr:last-child td {
    border-bottom: none !important;
}

/* Mejorar resumen financiero */
#modalImprimirTicketCaja #tckSubtotalVentaCaja,
#modalImprimirTicketCaja #tckDescuentoVentaCaja,
#modalImprimirTicketCaja #tckTotalVentaCaja {
    font-weight: 600 !important;
    color: #2c3e50 !important;
}

#modalImprimirTicketCaja #tckTotalVentaCaja {
    font-size: 20px !important;
    color: #667eea !important;
    font-weight: 700 !important;
}

#modalImprimirTicketCaja #tckMedioPagoVentaCaja {
    font-weight: 600 !important;
    color: #11998e !important;
}

/* Mejorar datos CAE */
#modalImprimirTicketCaja #tckDatosFacturaCAE {
    background: #f8f9fa !important;
    padding: 15px !important;
    border-radius: 8px !important;
    margin: 15px 0 !important;
    border-left: 4px solid #667eea !important;
}

/* Mejorar disclaimer */
#modalImprimirTicketCaja #impTicketCobroCaja > div:last-child {
    text-align: center !important;
    font-size: 12px !important;
    color: #95a5a6 !important;
    font-style: italic !important;
    margin-top: 20px !important;
    padding: 10px !important;
    background: #f8f9fa !important;
    border-radius: 8px !important;
}

/* Mejorar alert */
#modalImprimirTicketCaja #divEventoObservacionAprobada {
    border-radius: 8px !important;
    margin-bottom: 15px !important;
    padding: 15px !important;
    border-left: 4px solid #f5576c !important;
}

/* Mejorar botones del footer */
#modalImprimirTicketCaja .modal-footer .btn {
    padding: 10px 20px !important;
    font-size: 14px !important;
    font-weight: 600 !important;
    border-radius: 8px !important;
    transition: all 0.3s ease !important;
    min-width: 120px !important;
}

#modalImprimirTicketCaja .modal-footer .btn-default {
    background: #ffffff !important;
    border: 2px solid #e0e0e0 !important;
    color: #2c3e50 !important;
}

#modalImprimirTicketCaja .modal-footer .btn-default:hover {
    background: #f8f9fa !important;
    border-color: #667eea !important;
    color: #667eea !important;
    transform: translateY(-2px) !important;
}

#modalImprimirTicketCaja .modal-footer .btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
    border: none !important;
    color: #ffffff !important;
    box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3) !important;
}

#modalImprimirTicketCaja .modal-footer .btn-primary:hover {
    transform: translateY(-2px) !important;
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4) !important;
}

/* Responsive para modal ticket */
@media (max-width: 991px) {
    #modalImprimirTicketCaja .modal-dialog {
        margin: 10px !important;
        width: calc(100% - 20px) !important;
        max-width: 100% !important;
    }
    
    #modalImprimirTicketCaja #impTicketCobroCaja {
        font-size: 13px !important;
        padding: 15px !important;
    }
    
    #modalImprimirTicketCaja #tckDetalleVentaCaja {
        font-size: 12px !important;
    }
    
    #modalImprimirTicketCaja #tckDetalleVentaCaja th,
    #modalImprimirTicketCaja #tckDetalleVentaCaja td {
        padding: 8px 5px !important;
    }
    
    #modalImprimirTicketCaja .modal-footer {
        flex-direction: column !important;
    }
    
    #modalImprimirTicketCaja .modal-footer .btn {
        width: 100% !important;
        margin: 5px 0 !important;
    }
    
    #modalImprimirTicketCaja #tckTotalVentaCaja {
        font-size: 18px !important;
    }
}

@media (max-width: 480px) {
    #modalImprimirTicketCaja #impTicketCobroCaja {
        font-size: 11px !important;
        padding: 10px !important;
    }
    
    #modalImprimirTicketCaja #tckDetalleVentaCaja {
        display: block;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    
    #modalImprimirTicketCaja #tckDetalleVentaCaja th,
    #modalImprimirTicketCaja #tckDetalleVentaCaja td {
        white-space: nowrap;
        min-width: 80px;
    }
}

/* Responsive para modal cobro de venta */
@media (max-width: 991px) {
    #modalCobrarVenta .modal-dialog {
        margin: 10px !important;
        width: calc(100% - 20px) !important;
    }
    
    #modalCobrarVenta .col-md-3,
    #modalCobrarVenta .col-md-6 {
        width: 100% !important;
        margin-bottom: 15px !important;
    }
    
    #modalCobrarVenta .col-xs-6 {
        width: 100% !important;
        margin-bottom: 10px !important;
    }
    
    #modalCobrarVenta .modal-footer {
        flex-direction: column !important;
        gap: 10px !important;
    }
    
    #modalCobrarVenta .modal-footer .btn {
        width: 100% !important;
        margin: 0 !important;
    }
    
    #modalCobrarVenta .input-group {
        flex-direction: column !important;
    }
    
    #modalCobrarVenta .input-group-addon,
    #modalCobrarVenta .input-group .form-control {
        border-radius: 8px !important;
        border: 2px solid #e0e0e0 !important;
        width: 100% !important;
    }
    
    #modalCobrarVenta #agregarMedioPago {
        border-radius: 8px 8px 0 0 !important;
        width: 100% !important;
    }
    
    #modalCobrarVenta #nuevoMetodoPagoCaja {
        border-radius: 0 0 8px 8px !important;
        border-left: 2px solid #e0e0e0 !important;
        width: 100% !important;
    }
}

/* Mejorar inputs readonly */
.crear-venta-caja input[readonly] {
    background-color: #f8f9fa !important;
    cursor: default !important;
}

/* Mejorar espaciado general */
.crear-venta-caja .box-body {
    padding: 20px !important;
}

.crear-venta-caja .form-group {
    margin-bottom: 15px !important;
}

/* Mejorar el botón de cobrar */
.crear-venta-caja #btnGuardarVentaCaja {
    font-size: 18px !important;
    padding: 15px 40px !important;
    min-width: 200px;
}
</style>
<div class="content-wrapper crear-venta-caja">
  <section class="content">
    <div class="row">

      <!--=====================================
      EL FORMULARIO
      ======================================-->
      <div class="col-lg-7" >
        
				<div class="box box-warning">

          <div class="box-header with-border"></div>
      
            <div class="box-body">
      
		          <table class="table table-bordered table-striped dt-responsive" style="border: 1px solid black;">
					<tr>
						<td>
						 <div class="input-group">
							<span class="input-group-addon" style="background-color: #ddd"><i class="fa fa-calendar"></i> Día</span>
							<input type="text" class="form-control input-sm" id="fechaEmision" name="fechaEmision" value="<?php echo date('d-m-Y') ?>" placeholder="dd-mm-yyyy" readonly style="background-color: #fff; cursor: pointer;">
							</div>
						</td>
						<td>
						 <div class="input-group">
							<span class="input-group-addon" style="background-color: #ddd"><i class="fa fa-clock-o"></i> Hora</span>
							<input type="time" class="form-control input-sm" id="horaEmision" name="horaEmision" value="<?php echo date('H:i') ?>" step="1">
							</div>
						</td>
					<td>
                  <div class="input-group">
                    <span title="Listas de precio" class="input-group-addon" style="background-color: #ddd">Listas $</span>
                      <?php 

                      $arrListasPrecioHabilitadas = explode(',', $_SESSION['listas_precio']);

                      echo '<select class="form-control input-sm" name="radioPrecio" id="radioPrecio">';
                      foreach ($arrListasPrecio as $key => $value) {

                        if (in_array($key, $arrListasPrecioHabilitadas)) {
                          echo '<option value="' . $key . '" selected>' . $value . '</option>';
                        } else {
                          echo '<option value="' . $key . '" disabled>' . $value . '</option>';
                        }

                      }  

                      echo '</select>';

                      ?>
                  </div>
                 </td>
				</tr>
              </table>

              <input type="hidden" id="fechaActual" name="fechaActual" value="<?php echo date("Y-m-d H:i:s");?>">

<script>
// Inicializar datepicker para fecha de emisión
$(document).ready(function() {
	// Configurar datepicker en español
	$("#fechaEmision").datepicker({
		dateFormat: 'dd-mm-yy',
		dayNames: ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'],
		dayNamesMin: ['Do', 'Lu', 'Ma', 'Mi', 'Ju', 'Vi', 'Sa'],
		monthNames: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
		monthNamesShort: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
		changeMonth: true,
		changeYear: true,
		yearRange: '2020:2030',
		onSelect: function(dateText) {
			actualizarFechaActual();
		}
	});

	// Función para actualizar el campo hidden fechaActual cuando cambian fecha o hora
	function actualizarFechaActual() {
		var fecha = $("#fechaEmision").val();
		var hora = $("#horaEmision").val();
		
		if (fecha && hora) {
			// Convertir fecha de dd-mm-yyyy a yyyy-mm-dd
			var partesFecha = fecha.split('-');
			if (partesFecha.length === 3) {
				var fechaFormato = partesFecha[2] + '-' + partesFecha[1] + '-' + partesFecha[0];
				// Combinar fecha y hora
				var fechaHoraCompleta = fechaFormato + ' ' + hora + ':00';
				$("#fechaActual").val(fechaHoraCompleta);
			}
		}
	}

	// Actualizar cuando cambia la hora
	$("#horaEmision").on('change', function() {
		actualizarFechaActual();
	});

	// Inicializar con la fecha y hora actuales
	actualizarFechaActual();
});
</script>

              <input type="hidden" name="idVendedor" id="idVendedor" value="<?php echo $_SESSION["id"]; ?>">
              <input type="hidden" name="idEmpresa" id="idEmpresa" value="<?php echo $_SESSION["empresa"]; ?>">
              <input type="hidden" id="tokenIdTablaVentas">
 
			  <input type="hidden" name="alto" id="alto" value="">
			
              <table class="table table-bordered table-striped dt-responsive" style="border: 1px solid white;">
				<tr>
					<td>
						 <div class="input-group">
               
                  <span title="Tipos de comprobante" class="input-group-addon" style="background-color: #ddd"><i class="fa fa-bullseye"></i></span>
                  <?php

                  $arrCbtes = json_decode($arrayEmpresa['tipos_cbtes'], true);
                  array_unshift($arrCbtes, 
                              array("codigo"=>"0", "descripcion"=>"X"), 
                              array("codigo"=>"999", "descripcion"=>"Devolucion X")
										);

							  echo '<select title="Seleccione el tipo de comprobante" class="form-control input-sm selectTipoCbte" id="nuevotipoCbte" name="nuevotipoCbte" >';
							  echo '<option value="">Seleccione comprobante</option>';
							  //echo '<option value="0" selected>X</option>';
							  //echo '<option value="999" selected>Devolucion X</option>';
							  foreach ($arrCbtes as $key => $value) {

								if($value["codigo"] == $cbteDefecto){
								  echo '<option value="' . $value["codigo"] . '" selected>' . $value["descripcion"] . '</option>';
								} else {
								  echo '<option value="' . $value["codigo"] . '">' . $value["descripcion"] . '</option>';  
								}

							  }

							  echo '</select>';

							  ?>

							</div>
					</td>
					<td>
						<div class="input-group">

					  <span title="Puntos de venta" class="input-group-addon" style="background-color: #ddd"><i class="fa fa-terminal"></i></span>
					  <?php

					  $arrPuntos = json_decode($arrayEmpresa['ptos_venta'], true);
					  if (!is_array($arrPuntos)) {
						$arrPuntos = [];
					  }
					  $arrPuntosHabilitados = explode(',', $_SESSION['puntos_venta']);

					  echo '<select title="Seleccione el punto de venta" class="form-control input-sm" id="nuevaPtoVta" name="nuevaPtoVta">';
					  echo '<option value="0">Seleccione punto de venta</option>';

					  if (is_array($arrPuntos) && !empty($arrPuntos)) {
						foreach ($arrPuntos as $key => $value) {
						  if (isset($value["pto"]) && isset($value["det"])) {
							if (in_array($value["pto"], $arrPuntosHabilitados)) {
							  echo '<option value="' . $value["pto"] . '" selected>' . $value["pto"] . "-" . $value["det"]  . '</option>';
							} else {
							  echo '<option value="' . $value["pto"] . '" disabled>' . $value["pto"] . "-" . $value["det"]  . '</option>';
							}
						  }
						}
					  }

					  echo '</select>';

                  ?>

					</div>
					</td>
					<td>
					<div class="input-group">

					<span title="Concepto" class="input-group-addon" style="background-color: #ddd"><i class="fa fa-circle-o"></i></span>

					  <?php 
					  $arrConceptos = [ 
						"0" => "Seleccionar concepto",
						"1" => "Productos",
						"2" => "Servicios",
						"3" => "Productos y Servicios"
					  ];

					  echo '<select class="form-control input-sm selectConcepto" name="nuevaConcepto" id="nuevaConcepto">';
					  foreach ($arrConceptos as $key => $value) {

						if ($key == $arrayEmpresa['concepto_defecto']) {
						  echo '<option value="' . $key . '" selected>' . $value . '</option>';
						} else {
						  echo '<option value="' . $key . '">' . $value . '</option>';
						}

					  }  

					  echo '</select>';

					  ?>

				  </div>
				  </td>
				</tr>
					
                </table>


            <div class="row lineaServicio" style="padding-top: 10px;"  >

            <table class="table table-bordered table-striped dt-responsive" style="border: 1px solid white;">
				<tr>
					<td>
			             <div class="input-group">
							<span class="input-group-addon" style="background-color: #ddd">Desde</span>
								<input type="text" class="form-control input-sm nuevaFecServicios" id="nuevaFecDesde" name="nuevaFecDesde" placeholder="Ingrese fecha">

						 </div>
					</td>
					<td>					
						<div class="input-group">
							<span class="input-group-addon" style="background-color: #ddd">Hasta</span>
								<input type="text" class="form-control input-sm nuevaFecServicios" id="nuevaFecHasta" name="nuevaFecHasta" placeholder="Ingrese fecha">

							</div>
					</td>
					<td>
						<div class="input-group">
							<span class="input-group-addon" style="background-color: #ddd">Vto.</span>
								<input type="text" class="form-control input-sm nuevaFecServicios" id="nuevaFecVto" name="nuevaFecVto" placeholder="Ingrese fecha">

						</div>
					</td>
					</tr>
				</table>
			</div>

          <!--=====================================
          LINEA COMPROBANTES ASOCIADOS
          ======================================-->
          <div class="row lineaCbteAsociados" style="padding-top: 10px;"  >

           <table class="table table-bordered table-striped dt-responsive" style="border: 1px solid white;">
				<tr>
					<td>
						<div class="input-group">
							<span class="input-group-addon" style="background-color: #eee">Tipo cbte. asoc. </span>
							<?php

							  $arrCbtes = json_decode($arrayEmpresa['tipos_cbtes']);

							  echo '<select title="Seleccione el tipo de comprobante" class="form-control input-sm nuevaCbteAsociado" id="nuevotipoCbteAsociado" name="nuevotipoCbteAsociado" >';
							  echo '<option value="">Seleccione comprobante asociado</option>';

							  foreach ($arrCbtes as $key => $value) {

								if($value->codigo == '1' || $value->codigo == '4' || $value->codigo == '6' || $value->codigo == '9' || $value->codigo == '11' || $value->codigo == '15' || $value->codigo == '201' || $value->codigo == '206' || $value->codigo == '211'){

								  echo '<option value="' . $value->codigo . '">' . $value->descripcion . '</option>';  

								}

							  }

							  echo '</select>';

							  ?>
							</div>
					</td>
					<td>
						<div class="input-group">
							<span class="input-group-addon" style="background-color: #eee">Pto. vta. asoc</span>
						<?php

							  $arrPuntos = json_decode($arrayEmpresa['ptos_venta'], true);
							  if (!is_array($arrPuntos)) {
								$arrPuntos = [];
							  }
							  $arrPuntosHabilitados = explode(',', $_SESSION['puntos_venta']);

							  echo '<select title="Seleccione el punto de venta" class="form-control input-sm nuevaCbteAsociado" id="nuevaPtoVtaAsociado" name="nuevaPtoVtaAsociado">';
							  echo '<option value="0">Seleccione punto de venta asociado</option>';

							  if (is_array($arrPuntos) && !empty($arrPuntos)) {
								foreach ($arrPuntos as $key => $value) {
								  if (isset($value["pto"]) && isset($value["det"])) {
									if (in_array($value["pto"], $arrPuntosHabilitados)) {
									  echo '<option value="' . $value["pto"] . '" selected>' . $value["pto"] . "-" . $value["det"]  . '</option>';
									} else {
									  echo '<option value="' . $value["pto"] . '" disabled>' . $value["pto"] . "-" . $value["det"]  . '</option>';
									}
								  }
								}
							  }

							  echo '</select>';

							  ?>
						</div>
					</td>
					<td>
						<div class="input-group">
							<span class="input-group-addon" style="background-color: #eee">Nro. asoc.</span>

							<input type="text" class="form-control input-sm nuevaCbteAsociado" id="nuevaNroCbteAsociado" name="nuevaNroCbteAsociado" placeholder="Ingrese N° cbte asociado" autocomplete="off">

						</div>
					</td>
					</tr>
					</table>
			</div>

        <!--=====================================
        ENTRADA DEL CLIENTE
        ======================================-->
        <table class="table table-bordered table-striped dt-responsive" style="border: 1px solid white;">
				<tr>
					<th>

						<div class="input-group">

						  <input type="text" class="form-control ui-autocomplete-input input-sm" id="autocompletarClienteCaja" name="autocompletarCliente" placeholder="1-Consumidor Final" autocomplete="off">
						  <input type="hidden" id="seleccionarCliente" name="seleccionarCliente" value="1">
						  <input type="hidden" id="autocompletarClienteCajaMail">

						  <span class="input-group-btn"><button type="button" class="btn btn-default btn-sm" data-toggle="modal" data-target="#modalAgregarCliente" data-dismiss="modal">Agregar cliente</button></span>

						</div>
					</th>
				</tr>
			</table>				

         

            <!--=====================================
            ENTRADA PARA AGREGAR PRODUCTO
            ======================================--> 
                
              <div class="row" style="padding-top: 10px">
                
                <div class="col-xs-2" ><center>Cant.</center></div>
                <div class="col-xs-6" ><center>Artículo</center></div>
        		<div class="col-xs-2" ><center>P. Unitario</center></div>
        		<div class="col-xs-2" ><center>Precio</center></div>

              </div>
              <hr>

              <div class="form-group row nuevoProductoCaja" id="nuevoProductoCaja" style="width:100%; overflow-y:auto; overflow-x: text;"></div>

              <!-- CAMPOS NECESARIOS PARA ENVIAR POR POST PARA GUARDAR LA VENTA -->
              <input type="hidden" id="nuevaVentaCajaForm" name="nuevaVentaCaja">
              
              <input type="hidden" id="listaProductosCaja" name="listaProductosCaja" value="[]">

              <input type="hidden" id="listaDescuentoCaja" name="listaDescuentoCaja">

              <input type="hidden" id="nuevoPrecioImpuestoCaja" name="nuevoPrecioImpuestoCaja"> <!-- No se para que se usa -->

              <input type="hidden" id="listaMetodoPagoCajaForm" name="listaMetodoPagoCaja">

              <input type="hidden" id="nuevoTotalVentaCajaForm" name="nuevoTotalVentaCaja">

              <input type="hidden" id="nuevoInteresPorcentajeCajaForm" name="nuevoInteresPorcentajeCaja">

              <input type="hidden" id="nuevoDescuentoPorcentajeCajaForm" name="nuevoDescuentoPorcentajeCaja">

              <!-- Campos IVA -->
              <!-- <input type="text" id="nuevoVtaCajaIva0" name="nuevoVtaCajaIva0" value="0"> -->
              <input type="hidden" id="nuevoVtaCajaIva2" name="nuevoVtaCajaIva2" value="0">
              <input type="hidden" id="nuevoVtaCajaIva5" name="nuevoVtaCajaIva5" value="0">
              <input type="hidden" id="nuevoVtaCajaIva10" name="nuevoVtaCajaIva10" value="0">
              <input type="hidden" id="nuevoVtaCajaIva21" name="nuevoVtaCajaIva21" value="0">
              <input type="hidden" id="nuevoVtaCajaIva27" name="nuevoVtaCajaIva27" value="0">

              <!-- Campos base imponible -->
              <input type="hidden" id="nuevoVtaCajaBaseImp0" name="nuevoVtaCajaBaseImp0" value="0">
              <input type="hidden" id="nuevoVtaCajaBaseImp2" name="nuevoVtaCajaBaseImp2" value="0">
              <input type="hidden" id="nuevoVtaCajaBaseImp5" name="nuevoVtaCajaBaseImp5" value="0">
              <input type="hidden" id="nuevoVtaCajaBaseImp10" name="nuevoVtaCajaBaseImp10" value="0">
              <input type="hidden" id="nuevoVtaCajaBaseImp21" name="nuevoVtaCajaBaseImp21" value="0">
              <input type="hidden" id="nuevoVtaCajaBaseImp27" name="nuevoVtaCajaBaseImp27" value="0">

              <hr>

          </div> 


        </div>
        </div>     
      
      <!--=====================================
      LA TABLA DE PRODUCTOS
      ======================================-->

      <div class="col-lg-5 text-md text-sm text-xs">

        <div class="box box-warning">

          <div class="box-header with-border"></div>
      
            <div class="box-body">
      
            <table class="table table-bordered table-striped dt-responsive" style="border: 1px solid white;">
				<tr>
					<td>
                        <div class="form-group">
							<div class="input-group">
								<span class="input-group-addon"><i class="fa fa-user"></i></span> 
								<input type="text" class="form-control input-sm" id="nuevoVendedor" value="<?php echo $_SESSION["nombre"]; ?>" readonly>
							</div>

						</div> 
					</td>
					<td>
                    <?php
                     $arrSucursal = [ 
                        'stock' => 'Local',
                        '' => 'SIN SUCURSAL ASIGNADA'
                      ];
					?>
                    <input type="hidden" id="sucursalVendedor" value="<?php echo $_SESSION["sucursal"]; ?>">
                    <div class="form-group">
                         <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-building"></i></span> 
                              <input type="text" class="form-control input-sm" value="Sucursal: <?php echo $arrSucursal[$_SESSION["sucursal"]]; ?>" readonly>
						</div>
                    </div>
				</td>
			  </tr>
             </table> 
          
          
                   <div class="row">
                  <div class="col-xs-3" ><center>Cantidad</center></div>
                  <div class="col-xs-9" ><center>Cod. artículo</center></div>
                </div>
             
				<div class="col-md-3">
                    <div class="input-group">
                        <span class="input-group-addon"  ><b>#</b></span>
                        <input type="number" class="form-control input-sm ventaCajaInputs" onfocus="this.select();" id="ventaCajaCantidad" name="ventaCajaCantidad" style="text-align:center;" value="1">
                    </div>
                </div>
                <div class="col-md-9">
                    <div class="input-group">
                        <span class="input-group-addon"  ><i class="fa fa-product-hunt"></i></span>
                        <input type="text" class="form-control input-sm ventaCajaInputs" id="ventaCajaDetalle" name="ventaCajaDetalle" style="text-align:center;" placeholder="Buscar producto por codigo o descripcion">
                        <input type="hidden" id="ventaCajaDetalleHidden" name="ventaCajaDetalleHidden" >  
                        <input type="hidden" id="seleccionarProducto" name="seleccionarProducto" >
                    </div>
                </div>
        
                <hr>

                <table class="table table-bordered table-striped dt-responsive" style="border: 1px solid white;">
				    <tr>
					    <th>
                            <div class="input-group">
                                <span class="input-group-addon"><i class="ion ion-social-usd"></i></span>
                                <input type="number" step="0.01" min="0" class="form-control input-lg" id="nuevoPrecioNetoCajaForm" name="nuevoPrecioNetoCaja" placeholder="0,00" id="nuevoPrecioNetoCajaForm" readonly style="font-size: 50px;text-align: center;">
			                </div>
			            </th>
		            </tr>
	            </table>
          

          <div class="box-footer">
         
            <center><button type="submit" class="btn btn-primary" id="btnGuardarVentaCaja">Cobrar (F7)</button></center>

          </div>


      </div>
</div>
    </div>

  </section>

</div>

<!--=====================================
MODAL AGREGAR CLIENTE
======================================-->
<div id="modalAgregarCliente" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <input type="hidden" name="agregarClienteDesde" value="crear-venta-caja">
	      <!--=====================================
        CABEZA DEL MODAL
        ======================================-->
        <div class="modal-header" style="background:#3c8dbc; color:white">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Agregar cliente</h4>
        </div>
        <!--=====================================
        CUERPO DEL MODAL
        ======================================-->
        <div class="modal-body">
          <div class="box-body">
            <!-- ENTRADA PARA EL DOCUMENTO ID -->
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-key"></i></span> 
                <input type="number" min="0" step="1" class="form-control " name="nuevoDocumentoId" id="vtanuevoDocumentoId" placeholder="Ingresar documento">
                <span class="input-group-btn"><button type="button" title="Consultar en padrón de AFIP" id="vtabtnNuevoDocumentoId" class="btn btn-default" <?php echo $btnPadronAfip; ?> ><i class="fa fa-search"></i></button></span>
              </div>
            </div>            

            <!-- ENTRADA PARA TIPO DOCUMENTO -->
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-list-ul"></i></span> 
                <select class="form-control " name="nuevoTipoDocumento" id="vtanuevoTipoDocumento">
                  <option value="0">Seleccionar tipo documento</option>
                  <option value="96">DNI</option>
                  <option value="80">CUIT</option>
                  <option value="86">CUIL</option>
                  <!--<option value="87">CDI</option>
                  <option value="89">LE</option>
                  <option value="90">LC</option>
                  <option value="92">En trámite</option>
                  <option value="93">Acta nacimiento</option>
                  <option value="94">Pasaporte</option>
                  <option value="91">CI extranjera</option>-->
                  <option value="99">Otro</option>
                </select>
              </div>
            </div>

            <!-- ENTRADA PARA EL NOMBRE -->
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-user"></i></span> 
                <input type="text" class="form-control " name="nuevoCliente" id="vtanuevoCliente" placeholder="Ingresar nombre o razón social" required>
              </div>
            </div>

            <!-- ENTRADA PARA TIPO DOCUMENTO -->
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-list-ul"></i></span> 
                <select class="form-control " name="nuevoCondicionIva" id="vtanuevoCondicionIva" required>
								 <option value="">Seleccione condicion I.V.A.</option>
								 <option value="1">IVA Responsable Inscripto</option>
								 <option value="6">Responsable Monotributo</option>
								 <option value="5">Consumidor Final</option>
								 <option value="4">IVA Sujeto Exento</option>
								 <option value="7">Sujeto no categorizado</option>
								 <option value="8">Proveedor del exterior</option>
								 <option value="9">Cliente del exterior</option>
								 <option value="10">IVA Liberado - Ley N° 19.640</option>
								 <option value="13">Monotributista Social</option>
								 <option value="15">IVA No Alcanzado</option>
								 <option value="16">Monotributo Trabajador Independiente Promovido</option>
								 </select>

              </div>
            </div>

            <!-- ENTRADA PARA EL EMAIL -->
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-envelope"></i></span> 
                <input type="email" class="form-control " name="nuevoEmail" id="vtanuevoEmail" placeholder="Ingresar email">
              </div>
            </div>

            <!-- ENTRADA PARA EL TELÉFONO -->
            <div class="form-group">
              <div class="input-group">              
                <span class="input-group-addon"><i class="fa fa-phone"></i></span> 
                <input type="text" class="form-control " name="nuevoTelefono" id="vtanuevoTelefono" placeholder="Ingresar teléfono" data-inputmask="'mask':'(999) 999-9999'" data-mask >
              </div>
            </div>

            <!-- ENTRADA PARA LA DIRECCIÓN -->
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-map-marker"></i></span> 
                <input type="text" class="form-control " name="nuevaDireccion" id="vtanuevaDireccion" placeholder="Ingresar dirección" >
              </div>
            </div>

            <!-- ENTRADA PARA LA FECHA DE NACIMIENTO -->            
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-calendar"></i></span> 
                <input type="text" class="form-control " name="nuevaFechaNacimiento" id="vtanuevaFechaNacimiento" placeholder="Ingresar fecha nacimiento" data-inputmask="'alias': 'yyyy/mm/dd'" data-mask>
              </div>
            </div>

            <!-- ENTRADA PARA LAS OBSERVACIONES -->            
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-list"></i></span> 
                <textarea class="form-control " rows="3" name="nuevaObservaciones" id="vtaObservacionesCliente" placeholder="Observaciones"></textarea>
              </div>
            </div>
          </div>
        </div>

        <!--=====================================
        PIE DEL MODAL
        ======================================-->
        <div class="modal-footer">
          <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Salir</button>
          <button id="btnGuardarClienteVenta"  class="btn btn-primary">Guardar cliente</button>
        </div>
    </div>
  </div>
</div>

<!--=====================================
MODAL COBRAR VENTA
======================================-->
<div id="modalCobrarVenta" class="modal fade" role="dialog">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
        <!--=====================================
        CABEZA DEL MODAL
        ======================================-->
        <div class="modal-header" style="background:#3c8dbc; color:white">
          <h4 class="modal-title">Cobro de venta</h4>
        </div>
        <!--=====================================
        CUERPO DEL MODAL
        ======================================-->
        <div class="modal-body">
          <div class="box-body">
            <!-- ENTRADA PARA TIPO (INGRESO / EGRESO)-  -->
            <input type="hidden" name="ingresoCajaTipo" id="ingresoCajaTipo" value="1">
          </div>
          <div class="row" style="padding-bottom:10px">
              <div class="col-md-6"><span id="datosCuentaCorrienteCliente" style="font-size:18px"></span></div>
          </div>
          <div class="row" style="padding-bottom:10px">
          	<div class="col-md-3">
				<div class="input-group">
					<span class="input-group-addon" style="background-color: #eee"><b>PAGO</b></span>
					<input type="text" class="form-control" id="nuevoValorEntrega">
				</div>
          	</div>
          	<!--
          	<div class="col-md-3">
          	    <div class="icheck-primary d-inline ml-2">
                    <input type="checkbox" id="pagoCtaCte" checked>
                    <label for="pagoCtaCte">Cuenta Corriente</label>
                </div>
          	</div>-->
          	
          </div>
          <div class="form-group row">
            <div class="col-md-3">
               <div class="input-group">
                  <span title="Agregar medio de pago" class="input-group-btn"><button id="agregarMedioPago" type="button" class="btn btn-success" ><i class="fa fa-plus"></i></button></span>
	                <select class="form-control" id="nuevoMetodoPagoCaja">
	                  <?php
	                    // Cargar medios de pago dinámicamente desde BD
	                    if (class_exists('ModeloMediosPago')) {
	                        try {
	                            $mediosPago = ModeloMediosPago::mdlMostrarMediosPagoActivos();
	                            echo '<option value="">Medio de pago</option>';
	                            echo '<option value="MPQR">Mercado Pago QR</option>'; // Siempre disponible
	                            if($mediosPago && is_array($mediosPago)) {
	                                foreach($mediosPago as $medio) {
	                                    echo '<option value="' . htmlspecialchars($medio["codigo"]) . '">' . htmlspecialchars($medio["nombre"]) . '</option>';
	                                }
	                            }
	                        } catch (Exception $e) {
	                            // Fallback a valores por defecto si hay error
	                            echo '<option value="">Medio de pago</option>';
	                            echo '<option value="Efectivo">Efectivo</option>';
	                            echo '<option value="MP">Mercado Pago</option>';
	                            echo '<option value="MPQR">Mercado Pago QR</option>';
	                            echo '<option value="TD">Tarjeta Débito</option>';
	                            echo '<option value="TC">Tarjeta Crédito</option>';
	                            echo '<option value="CH">Cheque</option>';
	                            echo '<option value="TR">Transferencia</option>';
	                            echo '<option value="CC">Cuenta Corriente</option>';
	                        }
	                    } else {
	                        // Si el modelo no está cargado, usar valores por defecto
	                        echo '<option value="">Medio de pago</option>';
	                        echo '<option value="Efectivo">Efectivo</option>';
	                        echo '<option value="MP">Mercado Pago</option>';
	                        echo '<option value="MPQR">Mercado Pago QR</option>';
	                        echo '<option value="TD">Tarjeta Débito</option>';
	                        echo '<option value="TC">Tarjeta Crédito</option>';
	                        echo '<option value="CH">Cheque</option>';
	                        echo '<option value="TR">Transferencia</option>';
	                        echo '<option value="CC">Cuenta Corriente</option>';
	                    }
	                  ?>
	                </select>    
              </div>
            </div>
            <div class="cajasMetodoPagoCaja"></div> <!--Aca se cargan los input de codigo tarjeta, select tarjeta, cuotas  -->
          </div>      
					<hr>
          <div class="row">
            <div class="col-md-6">
            	<div class="row" style="display: none;" id="divImportesPagoMixto">
            		<table class="table" id="listadoMetodosPagoMixto">
            			<thead>
            				<!--<tr style="background-color: #eee; text-align: center;"><td colspan="2">Medios Pago</td></tr>-->
            				<tr>
		            			<th><i class="fa fa-minus-square"></i> </th>
		            			<th>Metodo</th>
		            			<th>Importe</th>
	            			</tr>
            			</thead>
            			<tbody>
            				
            			</tbody>
            			<tfoot>
            				<tr>
            					<td></td>
            					<td></td>
            					<td style="font-size: 18px">
            						<b>SALDO: $</b> <span id="nuevoValorSaldo" style="color:red">0</span>
            					</td>
            				</tr>
            			</tfoot>
            		</table>
            	</div>
            	<input type="hidden" id="listaMetodoPagoCaja"> <!--Manda al servidor si se paga en Efectivo, tarjeta debito, tarjeta credito, etc -->
            	<input type="hidden" id="mxMediosPagos"> <!--Array con los medios de pago en pago mixto -->
            </div>
            <div class="col-md-6">
              <table class="table">
								<tr>
								  <td style="vertical-align:middle; border: none;">Total:</td>
								  <td style="border: none;">
								    <div class="input-group">
								      <span class="input-group-addon"><i class="ion ion-social-usd"></i></span>
								      <input type="number" step="0.01" min="0" class="form-control input-sm" id="nuevoPrecioNetoCaja" placeholder="0,00" readonly style="font-size: 18px;">
								    </div>
								  </td>
								</tr>
								<tr id="filaInteresCaja" style="display:none;">
								  <td style="vertical-align:middle; border: none;">Interés:</td>
								  <td style=" border: none;">
										<div class="row">
										  <div class="col-xs-6">
										    <div class="input-group">
										      <span class="input-group-addon"><b>%</b></span>
										      <input type="number" step="0.01" min="0" placeholder="0,00" style="text-align:center; font-size: 18px;" class="form-control input-sm nuevoInteresCaja" id="nuevoInteresPorcentajeCaja">
										    </div>
										  </div>
										  <div class="col-xs-6">
										    <div class="input-group">
										      <span class="input-group-addon"><i class="ion ion-social-usd"></i></span>
										      <input type="number" step="0.01" min="0" placeholder="0,00" style="text-align:center; font-size: 18px;" class="form-control input-sm nuevoInteresCaja" id="nuevoInteresPrecioCaja">
										    </div>
										  </div>
										</div>
								  </td>
								</tr>
								<tr id="filaDescuentoCaja" style="display:none;">
								  <td style="vertical-align:middle; border: none;">Descuento:</td>
								  <td style="border: none;">
								    <div class="row">
								      <div class="col-xs-6">
								        <div class="input-group">
								          <span class="input-group-addon"><b>%</b></span>
								          <input type="number" step="0.01" min="0" placeholder="0,00" style="text-align:center; font-size: 18px;" class="form-control input-sm nuevoDescuentoCaja" id="nuevoDescuentoPorcentajeCaja" >
								        </div>
								      </div>
								      <div class="col-xs-6">
								        <div class="input-group">
								          <span class="input-group-addon"><i class="ion ion-social-usd"></i></span>
								          <input type="number" step="0.01" min="0" style="text-align:center; font-size: 18px;" class="form-control input-sm nuevoDescuentoCaja" id="nuevoDescuentoPrecioCaja" placeholder="0,00" >
								        </div>
								      </div>
								    </div>
								  </td>
								</tr>
								<tr>
									<td style="vertical-align:middle; border: none;"><b>TOTAL:</b></td>
									<td style="border: none;">
										<div class="input-group">
											<span class="input-group-addon"><i class="ion ion-social-usd"></i></span>
											<input type="number" step="0.01" min="0" style="font-size: 18px; font-weight:bold; text-align:center; " class="form-control input-sm" id="nuevoTotalVentaCaja" total="" placeholder="0,00" readonly required>
										</div>
									</td>
								</tr>
              </table>
            </div>
          </div>
        </div>
        <!--=====================================
        PIE DEL MODAL
        ======================================-->
        <div class="modal-footer">
          <button type="button" id="btnSalirMedioPagoCaja" class="btn btn-default pull-left" data-dismiss="modal">Salir (ESC)</button>
          <button type="button" id="btnCobrarMedioPagoCaja" onClick="this.disabled=true;" class="btn btn-primary">Guardar e imprimir (F8)</button>
        </div>
        <!-- Observaciones facturacion -->
        <div class="box-body" style="display: none; background-color: #f5c5ca" id="divVisualizarObservacionesFactura">
          <p>No se pudo autorizar el comprobante</p>
          <span id="impTicketCobroCajaObservacionFact" style="font-size: 12px;">
          </span>
        </div>
    </div>
  </div>
</div>

<!--=====================================
IMPRIMIR TICKET CAJA
======================================-->
<div id="modalImprimirTicketCaja" class="modal fade" role="dialog" style="overflow-y: scroll;">
  <div class="modal-dialog">
    <div class="modal-content">
      <!--CABEZA DEL MODAL-->
      <div class="modal-header" style="background:#3c8dbc; color:white">
        <h4 class="modal-title">Ticket</h4>
      </div>
      <!--CUERPO DEL MODAL-->
      <div class="modal-body">
        <div class="box-body">
    		<div class="alert " id="divEventoObservacionAprobada" style="" role="alert"></div>
            <div id="impTicketCobroCaja" style="font-size: 15px;">
             <br>
             <?php 
                $condIva = array(
                1 => "IVA Responsable Inscripto ",
                2 => "IVA Sujeto Exento ",
                3 => "IVA Responsable no Inscripto ",
                4 => "IVA no Responsable ",
                5 => "Consumidor Final ",
                6 => "Responsable Monotributo ",
                7 => "Sujeto no Categorizado ",
                8 => "Proveedor del Exterior ",
                9 => "Cliente del Exterior ",
                10 => "IVA Liberado – Ley Nº 19.640 ",
                11 => "IVA Responsable Inscripto – Agente de Percepción ",
                12 => "Pequeño Contribuyente Eventual ",
                13 => "Monotributista Social ",
                14 => "Pequeño Contribuyente Eventual Social",
                ''=>"(no definido)"
                );

              echo '<b>'. $arrayEmpresa["razon_social"] . '</b> <br>';
              echo $arrayEmpresa["titular"] . '<br>';
              echo $arrayEmpresa["domicilio"] . '<br>';
              //echo 'Tel.: ' . $arrayEmpresa["telefono"] . '<br>';
              echo 'Localidad: ' . $arrayEmpresa["localidad"] . ' C.P.: ' . $arrayEmpresa["codigo_postal"] . '<br>';
              echo 'CUIT: <span id="cuitEmpresaEmisora">' . $arrayEmpresa["cuit"] . '</span> II.BB.: ' . $arrayEmpresa["numero_iibb"] . '<br>';
              echo 'Cond. I.V.A.: ' . $condIva[$arrayEmpresa["condicion_iva"]] . '<br> ';
              echo 'Defensa del consumidor Mendoza 0800-222-6678 <br>';

              ?>
              <hr>
              <!-- <span id="tckFechaVentaCaja">Fecha:</span> <br>
              <span id="tckTipoCbteVentaCaja">TipoCbte:</span> <br>
              <span id="tckPtoVtaNumVentaCaja">PtoVta Num:</span> <br><br>
              <span id="tckNombreVentaCaja">Nombre:</span><br>
              <span id="tckTipoNumDocVentaCaja">TipoNumDoc:</span><br>
              <span id="tckCondIvaVentaCaja">CondIva:</span><br>
              <span id="tckDomicilioVentaCaja">Domicilio:</span> -->

                <!--FACTURA: DATOS RECEPTOR -->
                <span id="tckDatosFacturaFecha"></span><br>
                <b><span id="tckDatosFacturaTipoCbte"></span></b><br>
                <!-- <span id="tckDatosFacturaPtoNum"></span><br> -->
                <span id="tckDatosFacturaNumCbte"></span><br>
                
                <!-- <span id="tckDatosFacturaTipoDoc"></span><br>
                <span id="tckDatosFacturaNumDoc"></span><br> -->
                <span id="tckDatosFacturaNombreCliente"></span><br>
                <!-- <span id="tckDatosFacturaCondIva"></span> -->
                <hr>

               <center><b>Detalle</b></center>
             <br>
                <table width="100%" id="tckDetalleVentaCaja">
                  
                  <tr>
                    <th width="15%"><center>Cant. * Unit</center></th></center>
                    <th width="55%"><center>Descrip.</center></th>
                    <th width="30%"><center>Total</center></th>
                  </tr>

                </table>
              <br>

              <div>Subtotal: $ <span id="tckSubtotalVentaCaja"></span></div>
              <div><span id="campoDtoTexto">Descuento</span>: $ <span id="tckDescuentoVentaCaja"></span></div>

              <div id="tckDetalleFacturaA"></div>
              <div><b>TOTAL: $ <span id="tckTotalVentaCaja"></span></b></div>
              <div><b>Medio pago: </b><span id="tckMedioPagoVentaCaja"></span></div>
              <br>
              
              <!-- FACTURA: DATOS CAE - VTOCAE -->
              <div id="tckDatosFacturaCAE" style="display: none; font-size: 15px; font-style: italic;">
              <span id="tckDatosFacturaNumCAE"></span> - <span id="tckDatosFacturaVtoCAE"></span>
              <br>
                <div style="padding-top: 10px" id="dibujoCodigoQR"></div>
              </div>
              <div style="text-align: center">Controle su ticket antes de retirarse. No se aceptan devoluciones</div> 
            </div>
        </div>
      </div>
      <!-- PIE DEL MODAL-->
      <div class="modal-footer">
        <button type="button" id="btnSalirTicketControl" class="btn btn-default pull-left" data-dismiss="modal">Salir (ESC)</button>
        <button type="button" id="btnImprimirTicketControl" class="btn btn-primary"><i class="fa fa-ticket" aria-hidden="true"></i> Ticket (F9)</button>
        <button type="button" id="btnImprimirA4Control" class="btn btn-primary"><i class="fa fa-print" aria-hidden="true"></i> A4</button>
        <button type="button" id="btnEnviarMailA4" class="btn btn-primary"><i class="fa fa-envelope" aria-hidden="true"></i> Mail</button>
      </div>
    </div>
  </div>
</div>

<!--=====================================
AGREGAR PRODUCTO
======================================-->
<div id="modalAgregarProductoCaja" class="modal fade" role="dialog" style="overflow-y: scroll;">
  
  <div class="modal-dialog">

    <div class="modal-content">

      <!--=====================================
      CABEZA DEL MODAL
      ======================================-->

      <div class="modal-header" style="background:#3c8dbc; color:white">

        <h4 class="modal-title">Agregar producto</h4>

      </div>

      <!--=====================================
      CUERPO DEL MODAL
      ======================================-->

      <div class="modal-body">

        <div class="box-body">

           <div class="form-group">
              
              <div class="input-group">
              
                <span class="input-group-addon"><i class="fa fa-code"></i></span> 

                <input type="text" class="form-control" id="nuevoCodigoCaja" name="nuevoCodigo" placeholder="Código producto" required>

              </div>

            </div>

            <!-- ENTRADA PARA LA DESCRIPCIÓN -->

             <div class="form-group">
              
              <div class="input-group">
              
                <span class="input-group-addon"><i class="fa fa-product-hunt"></i></span> 

                <input type="text" class="form-control" id="nuevaDescripcionCaja" name="nuevaDescripcionCaja" placeholder="Ingresar descripción">

              </div>

            </div>

            <div class="row" style="padding-bottom: 10px;">
              <div class="col-xs-12" style="border-bottom-style: groove;">Venta</div>
            </div>

            <!-- ENTRADA PARA PRECIO VENTA -->
             <div class="form-group row">

                <!-- ENTRADA PARA PRECIO VENTA -->
                <div class="col-xs-4">        </div>

                <!-- ENTRADA PARA IVA -->

                <div class="col-xs-4">
                
                  <div class="input-group">
                  
                  <span class="input-group-addon"><i class="fa fa-percent"></i></span> 
                    <select name="nuevoIvaVenta" id="nuevoIvaVentaCaja" class="form-control">
                      <option value="">I.V.A.</option>
                      <option value="0.00">0%</option>
                      <option value="2.50">2,5%</option>
                      <option value="5.00">5%</option>
                      <option value="10.50">10,5%</option>
                      <option value="21.00" selected>21%</option>
                      <option value="27.00">27%</option>
                    </select>

                  </div>

                </div>

                <!-- ENTRADA PARA PRECIO COMPRA IVA INCLUIDO -->                

                <div class="col-xs-4">
                
                  <div class="input-group">
                  
                    <span class="input-group-addon"><i class="fa fa-usd"></i></span> 

                    <input type="number" title="Precio de venta (IVA incluido)" class="form-control" id="nuevoPrecioVentaIvaIncluidoCaja" name="nuevoPrecioVentaIvaIncluido" step="any" min="0" placeholder="Precio venta (IVA incluido)">

                  </div>

                </div>

            </div>

        </div>  

      </div>

      <!--=====================================
      PIE DEL MODAL
      ======================================-->

      <div class="modal-footer">

        <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Salir</button>

        <button type="button" id="btnGuardarNuevoProductoCaja" class="btn btn-primary">Crear</button>

      </div>

    </div>

  </div>

</div>

<!--=====================================
MODAL PAGO CON QR MERCADO PAGO
======================================-->
<div id="modalPagoQR" class="modal fade" role="dialog">
  
  <div class="modal-dialog">

    <div class="modal-content">

      <div class="modal-header" style="background:#3c8dbc; color:white">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title"><i class="fa fa-qrcode"></i> Pago con Mercado Pago QR</h4>
      </div>

      <div class="modal-body">
        <div class="box-body text-center">
          <div id="qrLoading" style="display:none;">
            <i class="fa fa-spinner fa-spin fa-3x"></i>
            <p>Generando código QR...</p>
          </div>
          
          <div id="qrContent" style="display:none;">
            <p class="lead"><strong>Monto a pagar: $<span id="qrMonto">0.00</span></strong></p>
            <div id="qrCodeContainer" style="padding:20px;">
              <img id="qrCodeImage" src="" alt="Código QR" style="max-width:300px; border:2px solid #ddd; padding:10px; background:white;">
            </div>
            <p id="qrMensaje" class="text-info" style="margin-top:15px;">
              <i class="fa fa-info-circle"></i> Escanea el código QR con la app de Mercado Pago para pagar
            </p>
            <div id="qrEstado" class="alert" style="display:none; margin-top:15px;"></div>
          </div>

          <div id="qrError" style="display:none;" class="alert alert-danger">
            <i class="fa fa-exclamation-triangle"></i> <span id="qrErrorMensaje"></span>
          </div>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
        <button type="button" id="btnVerificarPagoQR" class="btn btn-primary" style="display:none;">
          <i class="fa fa-refresh"></i> Verificar Pago
        </button>
      </div>

    </div>

  </div>

</div>

<script type="text/javascript">
    
   var Ancho= screen.width;
   var Alto= screen.height;
   if(Ancho < 450){
		document.getElementById("alto").value = 20;
		document.getElementById("nuevoProductoCaja").style.height = "60px";
		document.getElementById("nuevoPrecioNetoCajaForm").style.height = "40px";
	} else {
		document.getElementById("alto").value = 200;
		document.getElementById("nuevoProductoCaja").style.height = "200px";
		document.getElementById("nuevoPrecioNetoCajaForm").style.height = "85px";
	}

	//GENERA IDS UNICOS PARA LA TABLA VENTAS
  function create_UUID(){
      var dt = new Date().getTime();
      var uuid = 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxx'.replace(/[xy]/g, function(c) {
          var r = (dt + Math.random()*16)%16 | 0;
          dt = Math.floor(dt/16);
          return (c=='x' ? r :(r&0x3|0x8)).toString(16);
      });
      //$("#tokenIdTablaVentas").val(uuid)
      return uuid;
  }
  $("#tokenIdTablaVentas").val(create_UUID());

</script>