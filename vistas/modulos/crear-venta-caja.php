<?php 
  date_default_timezone_set('America/Argentina/Mendoza'); 
  $cbteDefecto = $objParametros->getCbteDefecto();
  // Listas de precio: desde BD si existe tabla, sino desde parametros
  if (class_exists('ModeloListasPrecio') && ModeloListasPrecio::tablaExiste()) {
    $arrListasPrecio = ModeloListasPrecio::mdlListarParaVenta();
  } else {
    $arrListasPrecio = $objParametros->getListasPrecio();
  }
  $listasPrecioConfig = [];
  if (class_exists('ModeloListasPrecio') && ModeloListasPrecio::tablaExiste() && !empty($_SESSION['listas_precio'])) {
    $listasPrecioConfig = ModeloListasPrecio::mdlConfigPorCodigos($_SESSION['listas_precio']);
  }
  // Formatos de balanza: configuración para interpretación de códigos
  $balanzasFormatosConfig = [];
  if (class_exists('ModeloBalanzasFormatos') && ModeloBalanzasFormatos::tablaExiste()) {
    $balanzasFormatosConfig = ModeloBalanzasFormatos::mdlConfigParaVenta();
  }
  // Medios de pago: desde BD (tabla medios_pago)
  $listaMediosPago = [];
  if (class_exists('ModeloMediosPago')) {
    $listaMediosPago = ModeloMediosPago::mdlMostrarMediosPagoActivos();
    if (!is_array($listaMediosPago)) {
      $listaMediosPago = [];
    }
  }
  $btnPadronAfip = (isset($arrayEmpresa["ws_padron"])) ? '' : 'disabled';
?>
<style>
/* ============================================
   LAYOUT CREAR VENTA: derecha siempre visible, izquierda se achica si hace falta
   ============================================ */
.crear-venta-caja .content .row.crear-venta-caja-fila {
    display: flex !important;
    flex-wrap: nowrap !important;
    margin-left: 0;
    margin-right: 0;
}
.crear-venta-caja .content .row.crear-venta-caja-fila > .col-lg-7 {
    flex: 1 1 55% !important;
    max-width: 58% !important;
    min-width: 0 !important;
}
.crear-venta-caja .content .row.crear-venta-caja-fila > .col-lg-5 {
    flex: 0 0 42% !important;
    min-width: 360px !important;
    max-width: 42% !important;
    display: flex !important;
    flex-direction: column !important;
}
@media (min-width: 992px) {
    .crear-venta-caja .content .row.crear-venta-caja-fila > .col-lg-5 {
        min-height: calc(100vh - 130px) !important;
    }
    .crear-venta-caja .content .row.crear-venta-caja-fila > .col-lg-5 > .box {
        flex: 1 !important;
        display: flex !important;
        flex-direction: column !important;
        min-height: 0 !important;
    }
    .crear-venta-caja .content .row.crear-venta-caja-fila > .col-lg-5 #seccionCobroVenta {
        flex: 1 !important;
        display: flex !important;
        flex-direction: column !important;
        min-height: 0 !important;
    }
    .crear-venta-caja .crear-venta-caja-espacio-fondo-derecha {
        flex: 1 1 auto !important;
        min-height: 40px !important;
    }
}
@media (max-width: 1200px) {
    .crear-venta-caja .content .row.crear-venta-caja-fila > .col-lg-7 { flex: 1 1 50% !important; max-width: 55% !important; }
    .crear-venta-caja .content .row.crear-venta-caja-fila > .col-lg-5 { min-width: 320px !important; }
}
@media (max-width: 991px) {
    .crear-venta-caja .content .row.crear-venta-caja-fila { flex-wrap: wrap !important; }
    .crear-venta-caja .content .row.crear-venta-caja-fila > .col-lg-7,
    .crear-venta-caja .content .row.crear-venta-caja-fila > .col-lg-5 { flex: none !important; max-width: 100% !important; min-width: 0 !important; }
}

/* ============================================
   RESPONSIVE MÓVIL (celu / tablet chica)
   ============================================ */
.crear-venta-caja .content {
    padding-left: 8px !important;
    padding-right: 8px !important;
}
@media (max-width: 767px) {
    .crear-venta-caja .content { padding: 8px 6px !important; }
    .crear-venta-caja .content .row.crear-venta-caja-fila > .col-lg-7,
    .crear-venta-caja .content .row.crear-venta-caja-fila > .col-lg-5 {
        width: 100% !important;
        padding-left: 6px !important;
        padding-right: 6px !important;
    }
    .crear-venta-caja .box { margin-bottom: 12px !important; }
    .crear-venta-caja .table td, .crear-venta-caja .table th { padding: 8px 6px !important; font-size: 14px !important; }
    .crear-venta-caja .input-group-addon,
    .crear-venta-caja .form-control,
    .crear-venta-caja input[type="text"],
    .crear-venta-caja input[type="number"],
    .crear-venta-caja select.form-control {
        min-height: 44px !important;
        padding: 10px 12px !important;
        font-size: 16px !important; /* evita zoom en iOS al enfocar */
    }
    .crear-venta-caja .btn { min-height: 44px !important; padding: 12px 16px !important; font-size: 16px !important; }
    .crear-venta-caja #nuevoPrecioNetoCajaForm { font-size: 1.75rem !important; min-height: 48px !important; }
    .crear-venta-caja .contenido-cobro-inline .form-group-row-unificado [class*="col-"] { width: 100% !important; max-width: 100% !important; }
    .crear-venta-caja .box-footer-unificado .btn { width: 48% !important; margin: 2px 0 !important; }
    .crear-venta-caja .nuevoProductoCaja .row [class*="col-"] { flex: 0 0 100% !important; max-width: 100% !important; }
}
@media (max-width: 576px) {
    .crear-venta-caja .content { padding: 6px 4px !important; }
    .crear-venta-caja .table { font-size: 13px !important; }
    .crear-venta-caja .table td, .crear-venta-caja .table th { padding: 6px 4px !important; white-space: normal !important; word-break: break-word; }
    .crear-venta-caja .input-group { flex-wrap: wrap !important; }
    .crear-venta-caja .input-group .form-control { border-radius: 8px !important; border-left: 2px solid #e0e0e0 !important; }
    .crear-venta-caja .input-group-addon { border-radius: 8px !important; margin-bottom: 4px; width: 100% !important; text-align: left !important; }
    .crear-venta-caja .col-md-3, .crear-venta-caja .col-md-6, .crear-venta-caja .col-md-9,
    .crear-venta-caja .col-xs-3, .crear-venta-caja .col-xs-6, .crear-venta-caja .col-xs-9 { width: 100% !important; max-width: 100% !important; }
    .crear-venta-caja #nuevoPrecioNetoCajaForm { font-size: 1.5rem !important; }
    .crear-venta-caja .box-footer-unificado .btn { width: 100% !important; display: block !important; margin: 6px 0 !important; }
}
/* Touch: evitar hover que quede “pegado” en táctiles */
@media (hover: none) {
    .crear-venta-caja .btn:active { opacity: 0.9; }
}

/* Evitar scroll horizontal en móvil */
.crear-venta-caja.content-wrapper { overflow-x: hidden !important; }
.crear-venta-caja .content { overflow-x: hidden !important; max-width: 100vw; }
.crear-venta-caja .row.crear-venta-caja-fila { overflow-x: hidden !important; }

/* Safe area para móviles con muesca */
@supports (padding: max(0px)) {
    @media (max-width: 767px) {
        .crear-venta-caja .content { padding-left: max(8px, env(safe-area-inset-left)) !important; padding-right: max(8px, env(safe-area-inset-right)) !important; padding-bottom: max(12px, env(safe-area-inset-bottom)) !important; }
    }
}

/* Móvil: lista productos y cobro adaptados */
@media (max-width: 767px) {
    .crear-venta-caja #nuevoProductoCaja { min-height: 120px !important; overflow: visible !important; }
    .crear-venta-caja .contenido-cobro-inline .form-group-row-unificado > [class*="col-md-"] { width: 100% !important; max-width: 100% !important; flex: 0 0 100% !important; }
    .crear-venta-caja .box-footer-unificado { display: flex !important; flex-wrap: wrap !important; gap: 8px !important; justify-content: space-between !important; }
    .crear-venta-caja .box-footer-unificado .btn { min-width: 44px !important; }
}

/* ============================================
   COLUMNA DERECHA UNIFICADA: todo en una sola sección, bien alineado
   ============================================ */
.crear-venta-caja .hr-unificado { margin: 12px 0 !important; border-color: #e0e0e0 !important; }
.crear-venta-caja .contenido-cobro-inline { margin-top: 0 !important; }
.crear-venta-caja .contenido-cobro-inline .form-group-row-unificado { margin-bottom: 10px !important; }
.crear-venta-caja .contenido-cobro-inline .input-group-unificado .form-control,
.crear-venta-caja .contenido-cobro-inline .input-group-unificado .input-group-addon { min-height: 40px !important; padding: 8px 12px !important; font-size: 15px !important; }
.crear-venta-caja .contenido-cobro-inline .input-group-unificado .btn { min-height: 40px !important; padding: 8px 12px !important; }
.crear-venta-caja .contenido-cobro-inline .table-totales-inline td { padding: 4px 8px !important; border: none !important; vertical-align: middle !important; }
.crear-venta-caja .contenido-cobro-inline .table-totales-inline .input-group-sm .form-control { min-height: 34px !important; font-size: 14px !important; }
/* Alinear filas Descuento e Interés: label e inputs % y $ en la misma línea base */
.crear-venta-caja .contenido-cobro-inline .table-totales-inline #filaDescuentoCaja td,
.crear-venta-caja .contenido-cobro-inline .table-totales-inline #filaInteresCaja td { vertical-align: middle !important; }
.crear-venta-caja .contenido-cobro-inline .table-totales-inline #filaDescuentoCaja .row,
.crear-venta-caja .contenido-cobro-inline .table-totales-inline #filaInteresCaja .row { display: flex !important; align-items: stretch !important; margin-left: -5px !important; margin-right: -5px !important; }
.crear-venta-caja .contenido-cobro-inline .table-totales-inline #filaDescuentoCaja .row > [class*="col-"],
.crear-venta-caja .contenido-cobro-inline .table-totales-inline #filaInteresCaja .row > [class*="col-"] { display: flex !important; padding-left: 5px !important; padding-right: 5px !important; }
.crear-venta-caja .contenido-cobro-inline .table-totales-inline #filaDescuentoCaja .input-group-sm,
.crear-venta-caja .contenido-cobro-inline .table-totales-inline #filaInteresCaja .input-group-sm { min-height: 38px !important; display: flex !important; align-items: stretch !important; width: 100% !important; }
.crear-venta-caja .contenido-cobro-inline .table-totales-inline #filaDescuentoCaja .input-group-addon,
.crear-venta-caja .contenido-cobro-inline .table-totales-inline #filaDescuentoCaja .input-group-sm .form-control,
.crear-venta-caja .contenido-cobro-inline .table-totales-inline #filaInteresCaja .input-group-addon,
.crear-venta-caja .contenido-cobro-inline .table-totales-inline #filaInteresCaja .input-group-sm .form-control { min-height: 38px !important; padding: 8px 10px !important; font-size: 14px !important; }
.crear-venta-caja .contenido-cobro-inline .cajasMetodoPagoCaja { min-height: 0 !important; }
/* Alinear en todos los medios de pago: misma altura y línea base (Entrega/Vuelto, Cheque, etc.) */
.crear-venta-caja .contenido-cobro-inline .row.form-group-row-unificado {
    display: flex !important;
    flex-wrap: wrap !important;
    align-items: flex-end !important;
}
.crear-venta-caja .contenido-cobro-inline .cajasMetodoPagoCaja {
    display: flex !important;
    flex-wrap: wrap !important;
    align-items: flex-end !important;
    gap: 8px 12px !important;
}
.crear-venta-caja .contenido-cobro-inline .cajasMetodoPagoCaja .input-group,
.crear-venta-caja .contenido-cobro-inline .cajasMetodoPagoCaja .input-group-addon,
.crear-venta-caja .contenido-cobro-inline .cajasMetodoPagoCaja .form-control {
    min-height: 40px !important;
    padding: 8px 12px !important;
    font-size: 15px !important;
}
.crear-venta-caja .contenido-cobro-inline .cajasMetodoPagoCaja > [class*="col-"] {
    flex: 0 0 auto !important;
    max-width: none !important;
}
.crear-venta-caja .box-footer-unificado { padding: 10px 15px !important; margin-top: 4px !important; border-top: 1px solid #e0e0e0 !important; }
.crear-venta-caja .box-footer-unificado .btn { font-size: 16px !important; padding: 12px 20px !important; min-height: 48px !important; }
/* Enlace Atajos de teclado (visible, centrado, accesible por teclado) */
.crear-venta-caja .link-atajos-teclado {
    display: inline-block;
    padding: 8px 20px;
    background: rgba(236, 112, 151, 0.25);
    border: 2px solid rgba(236, 112, 151, 0.6);
    border-radius: 8px;
    color: #c2185b;
    font-weight: 600;
    text-decoration: none;
    cursor: pointer;
    outline: none;
}
.crear-venta-caja .link-atajos-teclado:hover,
.crear-venta-caja .link-atajos-teclado:focus {
    background: rgba(236, 112, 151, 0.35);
    border-color: #c2185b;
    color: #ad1457;
}
.crear-venta-caja .link-atajos-teclado:focus { box-shadow: 0 0 0 3px rgba(194, 24, 91, 0.3); }

/* Ocultar total duplicado: solo se usa el total de arriba */
.crear-venta-caja .total-cobro-oculto { display: none !important; }

/* Total único (arriba): lo más grande posible */
.crear-venta-caja .total-unico-grande,
.crear-venta-caja #nuevoPrecioNetoCajaForm {
    font-size: min(4.5rem, 12vw) !important;
    font-weight: 700 !important;
    text-align: center !important;
    min-height: 72px !important;
    padding: 16px 20px !important;
    width: 100% !important;
    border-width: 3px !important;
}
.crear-venta-caja .col-lg-5 .table.table-bordered th { padding: 12px !important; }
.crear-venta-caja .col-lg-5 .table.table-bordered th .input-group { margin-bottom: 0 !important; width: 100% !important; }
.crear-venta-caja .col-lg-5 .table.table-bordered th .input-group-addon { font-size: 1.5rem !important; padding: 16px 20px !important; min-height: 72px !important; }
.crear-venta-caja .col-lg-5 .table.table-bordered th .form-control { min-height: 72px !important; padding: 16px 20px !important; }
@media (max-width: 480px) {
    .crear-venta-caja .content { padding: 6px 4px !important; }
    .crear-venta-caja .contenido-cobro-inline .row.form-group-row-unificado > [class*="col-"] { width: 100% !important; max-width: 100% !important; margin-bottom: 8px !important; }
    .crear-venta-caja .box-footer-unificado .btn { width: 100% !important; }
}

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

/* Columna izquierda: que llegue hasta el fondo de la página (sin cortar a la mitad) */
@media (min-width: 992px) {
    .crear-venta-caja .content .row.crear-venta-caja-fila > .col-lg-7 .box {
        display: flex !important;
        flex-direction: column !important;
        min-height: calc(100vh - 130px) !important;
    }
}
.crear-venta-caja .content .row.crear-venta-caja-fila > .col-lg-7 .box {
    display: flex !important;
    flex-direction: column !important;
}
.crear-venta-caja .content .row.crear-venta-caja-fila > .col-lg-7 .box .box-body {
    flex: 1 !important;
    display: flex !important;
    flex-direction: column !important;
    min-height: 0 !important;
}
.crear-venta-caja .crear-venta-caja-espacio-fondo {
    flex: 1 1 auto !important;
    min-height: 80px !important;
}
/* Área productos: crece con el contenido, scroll solo al final (scroll de página) */
.crear-venta-caja #nuevoProductoCaja {
    min-height: 200px;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 8px;
    border: 2px dashed #e0e0e0;
    overflow: visible !important;
}

.crear-venta-caja #nuevoProductoCaja:empty::before {
    content: "Los productos agregados aparecerán aquí";
    color: #95a5a6;
    font-style: italic;
    display: block;
    text-align: center;
    padding: 50px 20px;
}

/* Responsive - Tablet y móvil (columnas apiladas) */
@media (max-width: 991px) {
    .crear-venta-caja .col-lg-7,
    .crear-venta-caja .col-lg-5 {
        width: 100% !important;
        margin-bottom: 16px;
    }
    .crear-venta-caja .table {
        display: block;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        max-width: 100%;
    }
    .crear-venta-caja .table td,
    .crear-venta-caja .table th {
        min-width: 80px;
    }
    .crear-venta-caja .input-group { margin-bottom: 10px; }
    .crear-venta-caja .col-md-3,
    .crear-venta-caja .col-md-9 { margin-bottom: 10px; }
    .crear-venta-caja #nuevoPrecioNetoCajaForm { font-size: 2rem !important; }
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
    <div class="row crear-venta-caja-fila">

      <!--=====================================
      EL FORMULARIO
      ======================================-->
      <div class="col-lg-7">
        <p class="text-center" style="margin-bottom: 10px;">
          <a href="javascript:void(0);" id="linkAtajosTecladoForm" class="link-atajos-teclado" tabindex="0" role="button" title="Ver atajos de teclado (F1 dos veces)"><i class="fa fa-keyboard-o"></i> Atajos de teclado</a>
        </p>
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

                      $arrListasPrecioHabilitadas = !empty($_SESSION['listas_precio']) ? array_map('trim', explode(',', $_SESSION['listas_precio'])) : [];

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

<script type="text/javascript">
// Configuración de listas de precio para cálculo (base_precio, tipo_descuento, valor_descuento)
var listasPrecioConfig = <?php echo json_encode($listasPrecioConfig); ?>;
// Configuración de formatos de balanza (prefijo, posiciones, etc.)
var balanzasFormatosConfig = <?php echo json_encode($balanzasFormatosConfig); ?>;
</script>
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

							  $arrCbtes = json_decode($arrayEmpresa['tipos_cbtes'], true);

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

              <div class="form-group row nuevoProductoCaja" id="nuevoProductoCaja" style="width:100%;"></div>

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
              <!-- Espaciador: lleva el contenido hasta el fondo de la página (scroll solo al final) -->
              <div class="crear-venta-caja-espacio-fondo" aria-hidden="true"></div>

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
                   
                    <div class="form-group">
                         <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-building"></i></span> 
                                <?php 

                                $arrSucursal = json_decode($arrayEmpresa['almacenes'], true);
                                foreach ($arrSucursal as $valueSuc) {
                                    if ($_SESSION["sucursal"] === $valueSuc["stkProd"]) {
                                        echo '<input type="text" class="form-control input-sm" value="Sucursal: '.$valueSuc["det"].'" readonly>';
                                        echo '<input type="hidden" id="sucursalVendedor" value="'.$valueSuc["stkProd"].'">';
                                    }
                                }

                                ?>

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
                                <input type="number" step="0.01" min="0" class="form-control input-lg total-unico-grande" id="nuevoPrecioNetoCajaForm" name="nuevoPrecioNetoCaja" placeholder="0,00" readonly>
			                </div>
			            </th>
		            </tr>
	            </table>

            <hr class="hr-unificado">
            <input type="hidden" name="ingresoCajaTipo" id="ingresoCajaTipo" value="1">
            <div id="seccionCobroVenta" class="contenido-cobro-inline">
              <div class="row form-group-row-unificado">
                <div class="col-md-12"><span id="datosCuentaCorrienteCliente" style="font-size:1rem"></span></div>
              </div>
              <div class="row form-group-row-unificado">
                <div class="col-md-5 col-sm-6">
                  <div class="input-group input-group-unificado">
                    <span class="input-group-addon"><b>PAGO</b></span>
                    <input type="text" class="form-control" id="nuevoValorEntrega">
                  </div>
                </div>
                <div class="col-md-7 col-sm-6">
                  <div class="input-group input-group-unificado">
                    <span class="input-group-btn"><button id="agregarMedioPago" type="button" class="btn btn-success btn-sm"><i class="fa fa-plus"></i></button></span>
                    <select class="form-control" id="nuevoMetodoPagoCaja" required>
                      <?php
                      $idx = 0;
                      $idxEf = -1;
                      if (!empty($listaMediosPago)) {
                        foreach ($listaMediosPago as $mp) {
                          $cod = htmlspecialchars($mp['codigo'] ?? '');
                          if ($cod === 'EF') $idxEf = $idx;
                          $idx++;
                        }
                        $selIdx = ($idxEf >= 0) ? $idxEf : 0;
                        $i = 0;
                        foreach ($listaMediosPago as $mp) {
                          $cod = htmlspecialchars($mp['codigo'] ?? '');
                          $nom = htmlspecialchars($mp['nombre'] ?? $cod);
                          $rc = (int)($mp['requiere_codigo'] ?? 0);
                          $rb = (int)($mp['requiere_banco'] ?? 0);
                          $rn = (int)($mp['requiere_numero'] ?? 0);
                          $rf = (int)($mp['requiere_fecha'] ?? 0);
                          $sel = ($i === $selIdx) ? ' selected' : '';
                          echo '<option value="' . $cod . '"' . $sel . ' data-requiere-codigo="' . $rc . '" data-requiere-banco="' . $rb . '" data-requiere-numero="' . $rn . '" data-requiere-fecha="' . $rf . '">' . $nom . '</option>';
                          $i++;
                        }
                      }
                      ?>
                    </select>
                  </div>
                </div>
                <div class="cajasMetodoPagoCaja"></div>
              </div>
              <div class="row form-group-row-unificado">
                <div class="col-md-6">
                  <div class="row" style="display: none;" id="divImportesPagoMixto">
                    <table class="table" id="listadoMetodosPagoMixto">
                      <thead><tr><th><i class="fa fa-minus-square"></i></th><th>Metodo</th><th>Importe</th></tr></thead>
                      <tbody></tbody>
                      <tfoot><tr><td></td><td></td><td><b>SALDO: $</b> <span id="nuevoValorSaldo" style="color:red">0</span></td></tr></tfoot>
                    </table>
                  </div>
                  <input type="hidden" id="listaMetodoPagoCaja">
                  <input type="hidden" id="mxMediosPagos">
                </div>
                <div class="col-md-6">
                  <table class="table table-condensed table-totales-inline">
                    <tr class="total-cobro-oculto"><td style="border:none;">Total:</td><td style="border:none;"><div class="input-group input-group-sm"><span class="input-group-addon">$</span><input type="number" step="0.01" class="form-control" id="nuevoPrecioNetoCaja" placeholder="0,00" readonly></div></td></tr>
                    <tr id="filaInteresCaja" style="display:none;"><td style="border:none;">Interés:</td><td style="border:none;"><div class="row"><div class="col-xs-6"><div class="input-group input-group-sm"><span class="input-group-addon">%</span><input type="number" step="0.01" class="form-control nuevoInteresCaja" id="nuevoInteresPorcentajeCaja"></div></div><div class="col-xs-6"><div class="input-group input-group-sm"><span class="input-group-addon">$</span><input type="number" step="0.01" class="form-control nuevoInteresCaja" id="nuevoInteresPrecioCaja"></div></div></div></td></tr>
                    <tr id="filaDescuentoCaja" style="display:none;"><td style="border:none;">Descuento:</td><td style="border:none;"><div class="row"><div class="col-xs-6"><div class="input-group input-group-sm"><span class="input-group-addon">%</span><input type="number" step="0.01" class="form-control nuevoDescuentoCaja" id="nuevoDescuentoPorcentajeCaja"></div></div><div class="col-xs-6"><div class="input-group input-group-sm"><span class="input-group-addon">$</span><input type="number" step="0.01" class="form-control nuevoDescuentoCaja" id="nuevoDescuentoPrecioCaja" placeholder="0,00"></div></div></div></td></tr>
                    <tr class="total-cobro-oculto"><td style="border:none;"><b>TOTAL:</b></td><td style="border:none;"><div class="input-group input-group-sm"><span class="input-group-addon">$</span><input type="number" step="0.01" class="form-control" id="nuevoTotalVentaCaja" total="" placeholder="0,00" readonly required></div></td></tr>
                  </table>
                </div>
              </div>
            </div>
            <div class="crear-venta-caja-espacio-fondo-derecha" aria-hidden="true"></div>
            </div>
            <div class="box-footer box-footer-unificado">
              <button type="button" id="btnCobrarMedioPagoCaja" onClick="this.disabled=true;" class="btn btn-primary btn-block">Guardar e imprimir (F8)</button>
            </div>

      </div>
</div>
    </div>

  </section>

  <!-- Oculto: mensaje comprobante no aprobado se muestra por swal, no en la pantalla de cobro -->
  <div id="divVisualizarObservacionesFactura" style="display:none !important; position:absolute; left:-9999px;"><span id="impTicketCobroCajaObservacionFact"></span></div>

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
MODAL COBRAR VENTA (no se usa; el cobro va en #seccionCobroVenta)
======================================-->
<div id="modalCobrarVenta" class="modal fade" role="dialog" style="display:none;">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header"><h4 class="modal-title">Cobro de venta</h4></div>
      <div class="modal-body">
        <p class="text-muted">El cobro se realiza en la sección inferior de la pantalla.</p>
        <h5><strong>Atajos de teclado</strong></h5>
        <ul class="list-unstyled">
          <li><kbd>F1</kbd> Búsqueda de producto</li>
          <li><kbd>F2</kbd> Cantidad</li>
          <li><kbd>F7</kbd> Ir a sección de cobro / Pago</li>
          <li><kbd>F8</kbd> Guardar e imprimir</li>
          <li><kbd>Esc</kbd> Salir de cobro (volver a productos)</li>
          <li><kbd>Alt+D</kbd> Día · <kbd>Alt+H</kbd> Hora · <kbd>Alt+L</kbd> Listas · <kbd>Alt+P</kbd> Pto. venta · <kbd>Alt+A</kbd> Cliente · <kbd>Alt+E</kbd> Entrega</li>
          <li><kbd>↑</kbd><kbd>↓</kbd> Lista productos · <kbd>Ctrl+Q</kbd> Cantidad del ítem · <kbd>Ctrl+Del</kbd> Quitar ítem</li>
          <li><kbd>F1</kbd> dos veces: ver todos los atajos</li>
        </ul>
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
<div id="modalPagoQR" class="modal fade" role="dialog" data-backdrop="static" data-keyboard="false">
  
  <div class="modal-dialog">

    <div class="modal-content">

      <div class="modal-header" style="background:#3c8dbc; color:white">
        <button type="button" class="close" id="btnCerrarModalQR" data-dismiss="modal" style="display:none;">&times;</button>
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
        <button type="button" class="btn btn-default" id="btnCerrarModalQRFooter" data-dismiss="modal" style="display:none;">Cerrar</button>
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