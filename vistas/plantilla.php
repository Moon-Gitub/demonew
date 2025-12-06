<?php

  session_start();

  setcookie(session_name(), session_id(), 0, "/");

  //$arrayEmpresa y $objParametros LO DEFINIMOS UNA SOLA VEZ Y ES USADO EN TODAS LAS VISTAS DEL SISTEMA
  $idEmpresaPorSesion = isset($_SESSION["empresa"]) ? $_SESSION["empresa"] : 1;
  $arrayEmpresa = ModeloEmpresa::mdlMostrarEmpresa('empresa', 'id', $idEmpresaPorSesion);
  $objParametros = new ClaseParametros();

?>

<!DOCTYPE html>
<html>
  <head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="robots" content="noindex, nofollow" />
    <!-- ✅ Token CSRF para protección -->
    <meta name="csrf-token" content="<?php echo isset($_SESSION['csrf_token']) ? $_SESSION['csrf_token'] : ''; ?>">
    <!-- ✅ Content Security Policy: Permite MercadoPago y recursos necesarios -->
    <meta http-equiv="Content-Security-Policy" content="default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval' https://sdk.mercadopago.com https://cdn.jsdelivr.net https://www.mercadolibre.com https://www.mercadopago.com; style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://fonts.googleapis.com; font-src 'self' data: https://fonts.gstatic.com https://cdn.jsdelivr.net; img-src 'self' data: https: http:; frame-src 'self' https://www.mercadolibre.com https://www.mercadopago.com https://sdk.mercadopago.com; connect-src 'self' https://api.mercadopago.com https://api.mercadolibre.com https://www.mercadolibre.com https://www.mercadopago.com https://cdn.jsdelivr.net;">
    
    <title> <?php echo (isset($arrayEmpresa) && is_array($arrayEmpresa) && isset($arrayEmpresa['razon_social'])) ? $arrayEmpresa['razon_social'] : 'Sistema POS'; ?></title>

    <!-- Tell the browser to be responsive to screen width -->
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">

    <link rel="icon" href="vistas/img/plantilla/icono-negro.png">

    <!--=====================================
    PLUGINS DE CSS
    ======================================-->

    <!-- Bootstrap 5.3.2 (Compatible con AdminLTE 4.0.0-rc4) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    
    <!-- Font Awesome (mantener para compatibilidad) -->
    <link rel="stylesheet" href="vistas/bower_components/font-awesome/css/font-awesome.min.css">
    
    <!-- Bootstrap Icons (AdminLTE 4 usa Bootstrap Icons) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" crossorigin="anonymous">
    
    <!-- OverlayScrollbars (requerido por AdminLTE 4) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/styles/overlayscrollbars.min.css" crossorigin="anonymous">
    
    <!-- AdminLTE 4 CSS -->
    <link rel="stylesheet" href="vistas/dist/css/adminlte.min.css">
    
    <!-- CSS Personalizado - Diseño Moderno -->
    <link rel="stylesheet" href="vistas/css/adminlte-custom.css">

     <!-- DataTables -->
    <link rel="stylesheet" href="vistas/bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css">
    <link rel="stylesheet" href="vistas/bower_components/datatables.net-bs/css/responsive.bootstrap.min.css">
    <link rel="stylesheet" href="vistas/bower_components/datatables.net-bs/css/buttons.dataTables.min.css">
    <link rel="stylesheet" href="vistas/bower_components/datatables.net-bs/css/select.dataTables.min.css">

    <!-- iCheck for checkboxes and radio inputs -->
    <link rel="stylesheet" href="vistas/plugins/iCheck/all.css">

     <!-- Daterange picker -->
    <link rel="stylesheet" href="vistas/bower_components/bootstrap-daterangepicker/daterangepicker.css">

    <!-- Morris chart -->
    <link rel="stylesheet" href="vistas/bower_components/morris.js/morris.css">

    <!-- Jquery UI -->
    <link rel="stylesheet" href="vistas/bower_components/jquery-ui/jquery-ui.structure.min.css">
    <link rel="stylesheet" href="vistas/bower_components/jquery-ui/jquery-ui.theme.min.css">

    <!--=====================================
    PLUGINS DE JAVASCRIPT
    ======================================-->

    <!-- jQuery 3 -->
    <script src="vistas/bower_components/jquery/dist/jquery.min.js"></script>
    
    <!-- Bootstrap 5.3.2 JS Bundle (Compatible con AdminLTE 4) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
    
    <!-- OverlayScrollbars JS (requerido por AdminLTE 4) -->
    <script src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/browser/overlayscrollbars.browser.es6.min.js" crossorigin="anonymous"></script>
    
    <!-- AdminLTE 4 JS -->
    <script src="vistas/dist/js/adminlte.min.js"></script>

    <!-- DataTables -->
    <script src="vistas/bower_components/datatables.net/js/jquery.dataTables.min.js"></script>
    <script src="vistas/bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js"></script>
    <script src="vistas/bower_components/datatables.net-bs/js/dataTables.responsive.min.js"></script>
    <script src="vistas/bower_components/datatables.net-bs/js/responsive.bootstrap.min.js"></script>
    
    <script src="vistas/bower_components/datatables.net-bs/js/dataTables.buttons.min.js"></script>
    <script src="vistas/bower_components/datatables.net-bs/js/buttons.flash.min.js"></script>
    <script src="vistas/bower_components/datatables.net-bs/js/jszip.min.js"></script>
    <script src="vistas/bower_components/datatables.net-bs/js/pdfmake.min.js"></script>
    <script src="vistas/bower_components/datatables.net-bs/js/vfs_fonts.js"></script>
    <script src="vistas/bower_components/datatables.net-bs/js/buttons.html5.min.js"></script>
    <script src="vistas/bower_components/datatables.net-bs/js/buttons.print.min.js"></script>
    <script src="vistas/bower_components/datatables.net-bs/js/dataTables.select.min.js"></script>

    <!-- SweetAlert 2 -->
    <script src="vistas/plugins/sweetalert2/sweetalert2.all.js"></script>
     <!-- By default SweetAlert2 doesn't support IE. To enable IE 11 support, include Promise polyfill:-->
    <script src="vistas/bower_components/core/core.js"></script>

    <!-- iCheck 1.0.1 -->
    <script src="vistas/plugins/iCheck/icheck.min.js"></script>

    <!-- InputMask -->
    <script src="vistas/plugins/input-mask/jquery.inputmask.js"></script>
    <script src="vistas/plugins/input-mask/jquery.inputmask.date.extensions.js"></script>
    <script src="vistas/plugins/input-mask/jquery.inputmask.extensions.js"></script>

    <!-- jQuery Number -->
    <script src="vistas/plugins/jqueryNumber/jquerynumber.min.js"></script>

    <!-- daterangepicker http://www.daterangepicker.com/-->
    <script src="vistas/bower_components/moment/min/moment.min.js"></script>
    <script src="vistas/bower_components/bootstrap-daterangepicker/daterangepicker.js"></script>

    <!-- Morris.js charts http://morrisjs.github.io/morris.js/-->
    <script src="vistas/bower_components/raphael/raphael.min.js"></script>
    <script src="vistas/bower_components/morris.js/morris.min.js"></script>

    <!-- ChartJS http://www.chartjs.org/-->
    <script src="vistas/bower_components/Chart.js/Chart.js"></script>

    <!-- jQuery UI -->
    <script src="vistas/bower_components/jquery-ui/jquery-ui.min.js"></script>

    <!-- GENERAR QR EN TICKET -->
    <script type="text/javascript" src="vistas/plugins/qrcodejs/qrcode.min.js"></script>

  </head>

  <!--=====================================
  CUERPO DOCUMENTO
  ======================================-->
  <?php

   if(isset($_GET["ruta"]) && $_GET["ruta"] == "productos-precios"){
      include "modulos/productos-precios.php";

    } elseif(isset($_GET["ruta"]) && $_GET["ruta"] == "precios-qr"){
  	  include "modulos/precios-qr.php";

    }	else { 

  ?>

  <body class="layout-fixed sidebar-expand-lg sidebar-open bg-body-tertiary">
   
    <?php

    if(isset($_SESSION["iniciarSesion"]) && $_SESSION["iniciarSesion"] == "ok"){

     echo '<div class="app-wrapper">';

     echo '<input type="hidden" id="tiempoMaximoSesion" value="'.ini_get("session.gc_maxlifetime").'">';

      //CABEZOTE CON SISTEMA DE COBRO MERCADOPAGO
      include "modulos/cabezote-mejorado.php";

      //MENU
      include "modulos/menu.php";
      
      //CONTENIDO - Abrir app-main
      echo '<!--begin::App Main-->';
      echo '<main class="app-main">';
      
      if(isset($_GET["ruta"])){
        
        if($_GET["ruta"] == "inicio" ||
        $_GET["ruta"] == "usuarios" ||
        $_GET["ruta"] == "categorias" ||
        $_GET["ruta"] == "productos" ||
        $_GET["ruta"] == "productos-stock-medio" ||
        $_GET["ruta"] == "productos-stock-bajo" ||
        $_GET["ruta"] == "productos-stock-valorizado" ||
        $_GET["ruta"] == "productos-historial" ||
        $_GET["ruta"] == "productos-importar-excel" ||
        $_GET["ruta"] == "productos-importar-excel2" ||
        $_GET["ruta"] == "pedidos-validados" ||
        $_GET["ruta"] == "pedidos-nuevos" ||
        $_GET["ruta"] == "crear-venta-caja-impresion" ||
        $_GET["ruta"] == "info" ||
        $_GET["ruta"] == "pedidos-generar-movimiento" ||
        $_GET["ruta"] == "impresion-precios" || 
        $_GET["ruta"] == "impresionPreciosCuidados" ||
        $_GET["ruta"] == "impresionPreciosOfertas" ||
        $_GET["ruta"] == "impresionPreciosCuidadosGrande" || 
        $_GET["ruta"] == "proveedores" ||
        $_GET["ruta"] == "proveedores-cuenta-saldos" ||
        $_GET["ruta"] == "proveedores_cuenta" ||
        $_GET["ruta"] == "proveedores-saldo" ||
        $_GET["ruta"] == "proveedores-pagos" ||
        $_GET["ruta"] == "clientes" ||
        $_GET["ruta"] == "editar-pedido" ||
        $_GET["ruta"] == "clientes-cuenta-saldos" ||
        $_GET["ruta"] == "clientes-cuenta-deuda" ||
        $_GET["ruta"] == "clientes_cuenta" ||
        $_GET["ruta"] == "ventas" ||
        $_GET["ruta"] == "ventas-categoria-proveedor-informe" ||
        $_GET["ruta"] == "ventas-productos" ||
        $_GET["ruta"] == "presupuestos" ||
        $_GET["ruta"] == "presupuesto-venta" ||
        $_GET["ruta"] == "crear-presupuesto-caja" ||
        $_GET["ruta"] == "crear-presupuesto-caja2" ||
        $_GET["ruta"] == "libro-iva-ventas" ||
        $_GET["ruta"] == "ventas-rentabilidad" ||
        $_GET["ruta"] == "crear-venta" ||
        $_GET["ruta"] == "crear-venta-caja" ||
        $_GET["ruta"] == "editar-venta" ||
        $_GET["ruta"] == "crear-compra" ||
        $_GET["ruta"] == "ingreso" ||
        $_GET["ruta"] == "editar-ingreso" ||
        $_GET["ruta"] == "compras" ||
        $_GET["ruta"] == "reportes" ||
        $_GET["ruta"] == "empresa" ||         
        $_GET["ruta"] == "cajas" ||
        $_GET["ruta"] == "cajas-cajero" ||
        $_GET["ruta"] == "cajas-cierre" ||
        $_GET["ruta"] == "parametros-facturacion" ||
        $_GET["ruta"] == "factura-manual" ||
        $_GET["ruta"] == "procesar-pago" ||
        $_GET["ruta"] == "salir"){

          include "modulos/".$_GET["ruta"].".php";

        } else {
          include "modulos/404.php";

        }

      } else {
        include "modulos/inicio.php";

      }

      //Cerrar app-main
      echo '</main>';
      echo '<!--end::App Main-->';
      
      /*=============================================
      FOOTER
      =============================================*/
      include "modulos/footer.php";

      echo '</div>';
      echo '<!--end::App Wrapper-->';

    } else {
      include "modulos/login.php";

    }

  }

?>

  <div id="loader" style="position: fixed;
    left: 0px;
    top: 0px;
    width: 100%;
    height: 100%;
    z-index: 9999;
    background: url('vistas/img/pageLoader.gif') 50% 50% no-repeat rgb(255,255,255);
    opacity: .8;">
  </div>

  <script src="vistas/js/plantilla.js"></script>
  <script src="vistas/js/usuarios.js"></script>
  <script src="vistas/js/empresa.js"></script>
  <script src="vistas/js/categorias.js"></script>
  <script src="vistas/js/productos.js"></script>
  <script src="vistas/js/compras.js"></script>
  <script src="vistas/js/proveedores.js"></script>
  <script src="vistas/js/clientes.js"></script>
  <script src="vistas/js/ventas.js?v=1"></script>
  <script src="vistas/js/venta-caja.js?v=1"></script>
  <script src="vistas/js/reportes.js"></script>
  <script src="vistas/js/cajas.js"></script>
  <script src="vistas/js/pedidos.js"></script>

  </body>
</html>