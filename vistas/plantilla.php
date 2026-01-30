<?php

  session_start();

  setcookie(session_name(), session_id(), 0, "/");

  // Generar token CSRF si no existe (para sesiones existentes que no tienen token)
  if (!isset($_SESSION['csrf_token']) && isset($_SESSION["iniciarSesion"]) && $_SESSION["iniciarSesion"] == "ok") {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
  }

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
    
    <title> <?php echo (isset($arrayEmpresa) && is_array($arrayEmpresa) && isset($arrayEmpresa['razon_social'])) ? $arrayEmpresa['razon_social'] : 'Sistema POS'; ?></title>

    <!-- Tell the browser to be responsive to screen width -->
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">

    <link rel="icon" href="vistas/img/plantilla/icono-negro.png">

    <!--=====================================
    PLUGINS DE CSS
    ======================================-->

    <!-- Bootstrap 3.3.7 -->
    <link rel="stylesheet" href="vistas/bower_components/bootstrap/dist/css/bootstrap.min.css">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="vistas/bower_components/font-awesome/css/font-awesome.min.css">
    <!-- Ionicons -->
    <link rel="stylesheet" href="vistas/bower_components/Ionicons/css/ionicons.min.css">

    <!-- Theme style -->
    <link rel="stylesheet" href="vistas/dist/css/AdminLTE.css">
    
    <!-- AdminLTE Skins -->
    <link rel="stylesheet" href="vistas/dist/css/skins/_all-skins.min.css">

    <!-- Google Font -->
    <link rel="stylesheet" href="vistas/dist/css/fonts_googleapis.css">

    <!-- Estilos modernos globales para todo el sistema -->
    <link rel="stylesheet" href="vistas/dist/css/sistema-moderno.css">
    
    <!-- Estilos modernos para el cabezote -->
    <link rel="stylesheet" href="vistas/dist/css/cabezote-moderno.css">

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
    
    <!-- Bootstrap 3.3.7 -->
    <script src="vistas/bower_components/bootstrap/dist/js/bootstrap.min.js"></script>

    <!-- FastClick -->
    <script src="vistas/bower_components/fastclick/lib/fastclick.js"></script>
    
    <!-- AdminLTE App -->
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

  <body class="hold-transition skin-blue sidebar-mini sidebar-collapse">
   
    <?php

    if(isset($_SESSION["iniciarSesion"]) && $_SESSION["iniciarSesion"] == "ok"){

     echo '<div class="wrapper">';

     echo '<input type="hidden" id="tiempoMaximoSesion" value="'.ini_get("session.gc_maxlifetime").'">';

      //CABEZOTE CON SISTEMA DE COBRO MERCADOPAGO
      include "modulos/cabezote-mejorado.php";

      //MENU
      include "modulos/menu.php";
      
      //CONTENIDO (ruta desde directorio de plantilla para evitar 404)
      $dirModulos = __DIR__ . "/modulos";
      if(isset($_GET["ruta"])){
        $ruta = $_GET["ruta"];
        $archivo = $dirModulos . "/" . $ruta . ".php";
        $permiso = true;
        if (isset($_SESSION["permisos_pantallas"]) && is_array($_SESSION["permisos_pantallas"])) {
          $permiso = in_array($ruta, $_SESSION["permisos_pantallas"], true);
        } else {
          $permiso = ($ruta == "inicio" || $ruta == "usuarios" || $ruta == "categorias" || $ruta == "productos" || $ruta == "combos" || $ruta == "productos-stock-medio" || $ruta == "productos-stock-bajo" || $ruta == "productos-stock-valorizado" || $ruta == "productos-historial" || $ruta == "productos-importar-excel" || $ruta == "productos-importar-excel2" || $ruta == "pedidos-validados" || $ruta == "pedidos-nuevos" || $ruta == "crear-venta-caja-impresion" || $ruta == "info" || $ruta == "pedidos-generar-movimiento" || $ruta == "impresion-precios" || $ruta == "impresionPreciosCuidados" || $ruta == "impresionPreciosOfertas" || $ruta == "impresionPreciosCuidadosGrande" || $ruta == "proveedores" || $ruta == "proveedores-cuenta-saldos" || $ruta == "proveedores_cuenta" || $ruta == "proveedores-saldo" || $ruta == "proveedores-pagos" || $ruta == "integraciones" || $ruta == "chat" || $ruta == "clientes" || $ruta == "editar-pedido" || $ruta == "clientes-cuenta-saldos" || $ruta == "clientes-cuenta-deuda" || $ruta == "clientes_cuenta" || $ruta == "ventas" || $ruta == "ventas-categoria-proveedor-informe" || $ruta == "ventas-productos" || $ruta == "presupuestos" || $ruta == "presupuesto-venta" || $ruta == "crear-presupuesto-caja" || $ruta == "crear-presupuesto-caja2" || $ruta == "libro-iva-ventas" || $ruta == "ventas-rentabilidad" || $ruta == "crear-venta" || $ruta == "crear-venta-caja" || $ruta == "editar-venta" || $ruta == "crear-compra" || $ruta == "ingreso" || $ruta == "editar-ingreso" || $ruta == "compras" || $ruta == "reportes" || $ruta == "empresa" || $ruta == "listas-precio" || $ruta == "balanzas-formatos" || $ruta == "permisos-rol" || $ruta == "medios-pago" || $ruta == "cajas" || $ruta == "cajas-cajero" || $ruta == "cajas-cierre" || $ruta == "parametros-facturacion" || $ruta == "factura-manual" || $ruta == "procesar-pago" || $ruta == "salir");
        }
        if ($permiso && file_exists($archivo)) {
          include $archivo;
        } else {
          include $dirModulos . "/404.php";
        }
      } else {
        include $dirModulos . "/inicio.php";
      }

      /*=============================================
      FOOTER
      =============================================*/
      include "modulos/footer.php";

      echo '</div>';

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
  <script src="vistas/js/combos.js"></script>
  <script src="vistas/js/compras.js"></script>
  <script src="vistas/js/proveedores.js"></script>
  <script src="vistas/js/clientes.js"></script>
  <script src="vistas/js/ventas.js?v=1"></script>
  <script src="vistas/js/venta-caja.js?v=1"></script>
  <script src="vistas/js/reportes.js"></script>
  <script src="vistas/js/cajas.js"></script>
  <script src="vistas/js/pedidos.js"></script>
  <script src="vistas/js/integraciones.js"></script>
  <script src="vistas/js/chat.js"></script>

  </body>
</html>