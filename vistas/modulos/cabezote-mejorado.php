<?php
//========================
// CONEXION AFIP
//========================
$conAfip = false;
$msjError="";

if($arrayEmpresa["entorno_facturacion"]){

 try {

   $wsaa = new WSAA($arrayEmpresa);

   if (date('Y-m-d H:i:s', strtotime($wsaa->get_expiration())) < date('Y-m-d H:i:s')) {

     $wsaa->generar_TA();

   }

   $wsfe = new WSFE($arrayEmpresa);
   $test = $wsfe->openTA();

  // $test = $wsfe->PruebaConexion();

   if (isset($test)){
     //if ($test->FEDummyResult->AppServer == 'OK' && $test->FEDummyResult->DbServer == 'OK' && $test->FEDummyResult->AuthServer == 'OK' ){

       $conAfip = true;

     //}
   } else {

     $conAfip = false;

   }

 } catch (Exception $e) {

   $conAfip = false;
   $msjError = $e->getMessage();
 }

}

//========================
// ARCHIVO COTIZACION
//========================
$result=[];
if ($file = fopen("cotizacion", "r")) {
    $i = 0;

    while(!feof($file)) {
        $line = fgets($file);
        $result[$i] = $line;
        $i++;

    }
    fclose($file);
} else {
    $result[0]="No se pudo cargar la ultima cotización";
    $result[1]="0,00";
}

//==================================
//      SISTEMA DE COBRO MEJORADO
//==================================

// Verificar si el sistema de cobro está disponible
try {
    // Verificar conexión a BD Moon
    $testConexion = Conexion::conectarMoon();
    if (!$testConexion) {
        throw new Exception("BD Moon no disponible");
    }

    // ID del cliente (leer de $_ENV o $_SERVER)
    $idCliente = isset($_ENV['MOON_CLIENTE_ID']) ? intval($_ENV['MOON_CLIENTE_ID']) : (isset($_SERVER['MOON_CLIENTE_ID']) ? intval($_SERVER['MOON_CLIENTE_ID']) : 14);

    // Obtener credenciales desde .env o usar por defecto
    $credencialesMP = ControladorMercadoPago::ctrObtenerCredenciales();
    $clavePublicaMercadoPago = $credencialesMP['public_key'];
    $accesTokenMercadoPago = $credencialesMP['access_token'];

    // URL de respuesta
    if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on')
        $rutaRespuesta = "https://";
    else
        $rutaRespuesta = "http://";

    $rutaRespuesta .= $_SERVER['HTTP_HOST'];
    $rutaRespuesta .= "/index.php?ruta=procesar-pago";

    // Obtener datos del cliente
    $clienteMoon = ControladorSistemaCobro::ctrMostrarClientesCobro($idCliente);
    $ctaCteCliente = ControladorSistemaCobro::ctrMostrarSaldoCuentaCorriente($idCliente);
    $ctaCteMov = ControladorSistemaCobro::ctrMostrarMovimientoCuentaCorriente($idCliente);

    // Verificar que las consultas funcionaron (usar !== false porque ID puede ser 0)
    if ($clienteMoon === false || $ctaCteCliente === false) {
        error_log("ERROR COBRO: Cliente ID $idCliente - Consultas fallaron");
        throw new Exception("No se pudieron obtener datos del cliente ID $idCliente");
    }

    // Obtener el saldo pendiente actual
    $saldoPendiente = floatval($ctaCteCliente["saldo"]);

    // IMPORTANTE: Usar directamente el saldo pendiente
    // No intentar reconstruir cargos específicos porque puede haber pagos parciales
    
    // Si el saldo es pequeño, probablemente es un resto de pago parcial
    // Mostrarlo como "Saldo pendiente" sin desglose detallado
    $serviciosMensuales = [];
    $otrosCargos = [];
    $subtotalMensuales = 0;
    $subtotalOtros = 0;
    
    if ($saldoPendiente > 0) {
        // Obtener último movimiento para descripción
        if ($ctaCteMov && isset($ctaCteMov['descripcion'])) {
            $descripcion = $ctaCteMov['descripcion'];
            
            // Determinar si es servicio mensual o no
            if (stripos($descripcion, 'Servicio POS') !== false) {
                $serviciosMensuales[] = array(
                    'descripcion' => 'Saldo pendiente (resto de: ' . $descripcion . ')',
                    'importe' => $saldoPendiente
                );
                $subtotalMensuales = $saldoPendiente;
            } else {
                $otrosCargos[] = array(
                    'descripcion' => 'Saldo pendiente (resto de: ' . $descripcion . ')',
                    'importe' => $saldoPendiente
                );
                $subtotalOtros = $saldoPendiente;
            }
        } else {
            // Si no hay último movimiento, mostrar como saldo general
            $otrosCargos[] = array(
                'descripcion' => 'Saldo pendiente de cuenta corriente',
                'importe' => $saldoPendiente
            );
            $subtotalOtros = $saldoPendiente;
        }
    }

    // Calcular monto con recargos usando el controlador del sistema de cobro
    // El controlador verifica automáticamente el campo aplicar_recargos del cliente
    // y aplica recargos SOLO sobre servicios mensuales si corresponde
    $datosCobro = ControladorMercadoPago::ctrCalcularMontoCobro($clienteMoon, $ctaCteCliente, $subtotalMensuales, $subtotalOtros);

    $abonoMensual = $datosCobro['monto'];
    $mensajeCliente = $datosCobro['mensaje'];
    $tieneRecargo = $datosCobro['tiene_recargo'];
    $porcentajeRecargo = $datosCobro['porcentaje_recargo'];
    $aplicarRecargos = $datosCobro['aplicar_recargos'];

$muestroModal = false;
$fijoModal = false;
$estadoClienteBarra = '';
$badgeNavbar = '';
$dropdownContent = '';

// Determinar estado del cliente
if($ctaCteCliente["saldo"] <= 0) {
    // Cliente al día
    ControladorSistemaCobro::ctrActualizarClientesCobro($idCliente, 0);

    $dropdownContent = '
        <div style="text-align: center; padding: 20px;">
            <i class="fa fa-check-circle" style="font-size: 48px; color: #28a745;"></i>
            <h4 style="margin-top: 10px; color: #28a745;">¡Cuenta al día!</h4>
            <p style="color: #6c757d;">No hay pagos pendientes</p>
        </div>
    ';

} else {
    // Cliente con deuda
    $diaActual = intval(date('d'));
    $diasCorte = 26 - $diaActual;

    // Verificar estado_bloqueo (usar isset para evitar errores si el campo no existe)
    $estadoBloqueo = isset($clienteMoon["estado_bloqueo"]) ? $clienteMoon["estado_bloqueo"] : 0;
    
    if ($estadoBloqueo == "1") {
        // Cliente bloqueado
        $estadoClienteBarra = 'style="background-color: #dc3545;"';
        $muestroModal = true;
        $fijoModal = true;
        $badgeNavbar = '<span class="label label-danger">' . number_format($abonoMensual, 0) . '</span>';

    } else {
        if ($diaActual >= 1 && $diaActual <= 4) {
            // Días 1-4: Mostrar modal pero sin recargos aún
            $muestroModal = true;
            $badgeNavbar = '<span class="label label-success">' . number_format($abonoMensual, 0) . '</span>';

        } elseif ($diaActual > 4 && $diaActual <= 9) {
            $muestroModal = true;
            $badgeNavbar = '<span class="label label-info">' . number_format($abonoMensual, 0) . '</span>';

        } elseif ($diaActual > 10 && $diaActual <= 21) {
            $muestroModal = true;
            $badgeNavbar = '<span class="label label-warning">' . number_format($abonoMensual, 0) . '</span>';

        } elseif ($diaActual > 21 && $diaActual <= 26) {
            $estadoClienteBarra = 'style="background-color: #ffc107;"';
            $muestroModal = true;
            $badgeNavbar = '<span class="label label-warning">' . number_format($abonoMensual, 0) . '</span>';

        } elseif ($diaActual > 26) {
            ControladorSistemaCobro::ctrActualizarClientesCobro($idCliente, 1);
            $estadoClienteBarra = 'style="background-color: #dc3545;"';
            $muestroModal = true;
            $fijoModal = true;
            $badgeNavbar = '<span class="label label-danger">' . number_format($abonoMensual, 0) . '</span>';
        }
    }

    // Contenido del dropdown
    $dropdownContent = '
        <div style="padding: 15px; min-width: 250px;">
            <div style="text-align: center; padding: 15px; background: #f8f9fa; border-radius: 6px; margin-bottom: 10px;">
                <div style="font-size: 13px; color: #6c757d;">Saldo Pendiente</div>
                <div style="font-size: 28px; font-weight: 700; color: #dc3545;">$' . number_format($abonoMensual, 2, ',', '.') . '</div>
            </div>';

    if ($tieneRecargo) {
        $dropdownContent .= '
            <div style="background: #fff3cd; padding: 10px; border-radius: 4px; margin-bottom: 10px; border-left: 3px solid #ffc107;">
                <i class="fa fa-exclamation-triangle" style="color: #ffc107;"></i>
                <strong>Recargo:</strong> ' . $porcentajeRecargo . '%
            </div>';
    }

    $dropdownContent .= '
            <button class="btn btn-primary btn-block" data-toggle="modal" data-target="#modalCobro" style="background: #009ee3 !important; border: none;">
                <i class="fa fa-credit-card"></i> Pagar Ahora
            </button>
        </div>';

    // Crear preferencia de MercadoPago (con manejo de errores independiente)
    $preference = null;
    
    if(!isset($_GET["preference_id"])) {
        try {
            require_once 'extensiones/vendor/autoload.php';

            // SDK de MercadoPago v3.x (usando nombres completos de clase)
            \MercadoPago\MercadoPagoConfig::setAccessToken($accesTokenMercadoPago);

            // Construir items dinámicamente
            $items = [];

            // Agregar servicios mensuales
            foreach ($serviciosMensuales as $servicio) {
                $items[] = [
                    "title" => isset($servicio['descripcion']) ? $servicio['descripcion'] : 'Servicio Mensual',
                    "quantity" => 1,
                    "unit_price" => floatval($servicio['importe'])
                ];
            }

            // Agregar otros cargos
            foreach ($otrosCargos as $cargo) {
                $items[] = [
                    "title" => isset($cargo['descripcion']) ? $cargo['descripcion'] : 'Otro Cargo',
                    "quantity" => 1,
                    "unit_price" => floatval($cargo['importe'])
                ];
            }

            // Agregar recargo como item separado si aplica
            if ($tieneRecargo && $subtotalMensuales > 0) {
                $montoRecargoItems = $subtotalMensuales * ($porcentajeRecargo / 100);
                $items[] = [
                    "title" => "Recargo por mora sobre servicios mensuales (" . $porcentajeRecargo . "%)",
                    "quantity" => 1,
                    "unit_price" => $montoRecargoItems
                ];
            }

            $client = new \MercadoPago\Client\Preference\PreferenceClient();
            $preference = $client->create([
                "items" => $items,
                "external_reference" => strval($idCliente),
                "back_urls" => [
                    "success" => $rutaRespuesta,
                    "failure" => $rutaRespuesta
                ],
                "auto_return" => "approved",
                "binary_mode" => true
            ]);

            // Registrar intento de pago
            if ($preference && isset($preference->id)) {
                $datosIntento = array(
                    'id_cliente_moon' => $idCliente,
                    'preference_id' => $preference->id,
                    'monto' => $abonoMensual,
                    'descripcion' => 'Pago mensual - ' . date('m/Y'),
                    'fecha_creacion' => date('Y-m-d H:i:s'),
                    'estado' => 'pendiente'
                );
                ControladorMercadoPago::ctrRegistrarIntentoPago($datosIntento);
            }

        } catch (Exception $e) {
            // Error creando preferencia - no romper el cabezote
            error_log("ERROR creando preferencia MP: " . $e->getMessage());
            $preference = null;
            // El cabezote se mostrará pero sin botón de pago
        }
    }
}
?>

<header class="main-header">

    <!--=====================================
    LOGOTIPO
    ======================================-->
    <a href="inicio" class="logo">
        <span class="logo-mini">
            <i class="fa fa-moon-o fa-2x"></i>
        </span>
        <span class="logo-lg">
            <i class="fa fa-moon-o fa-2x"></i>
            POS | Moon
        </span>
    </a>

    <!--=====================================
    BARRA DE NAVEGACIÓN
    ======================================-->
    <nav class="navbar navbar-static-top" role="navigation">

        <a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button">
            <span class="sr-only">Toggle navigation</span>
        </a>

        <div class="navbar-custom-menu">
            <ul class="nav navbar-nav">
            
                <!-- Alerta de tiempo de sesión -->
                <li class="dropdown tasks-menu" style="display: none" id="alertaTiempoSesionRestanteLi">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                      <i class="fa fa-clock-o"></i>
                      <span title="Tiempo restante de sesión" class="label label-danger" id="alertaTiempoSesionRestante"></span>
                    </a>
                </li>
                
                <!-- Dropdown AFIP -->
                <li class="dropdown tasks-menu">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                      <img src="vistas/img/plantilla/afipicon.ico" >
                    </a>
                    <ul class="dropdown-menu">
                        <li class="header" style="background-color: #000"><img src="vistas/img/plantilla/AFIPlogoChico.png" width="30%"></li>
                        <li>
                            <ul class="menu" style="background-color: #eee;">
                                <?php 
                                echo '<p>Conexion con servidor de AFIP ';

                                if ( $conAfip ){

                                  $fecform = date_create($wsfe->datosTA()["Exp"]);
                                  echo '<i class="fa fa-check-circle-o fa-2x" style="color: green"></i></p>';

                                  echo '<p>CUIT: '. $arrayEmpresa['cuit'] . '</p>
                                  <p>Ticket acceso valido hasta: <br/>' . $fecform->format('d/m/Y - H:i:s') .' </p>';

                                  echo '<p>Entorno: ' .$arrayEmpresa['entorno_facturacion'] . '</p>';

                                } else {

                                    echo '<i class="fa fa-times-circle-o fa-2x" style="color: red"></i></p>';

                                    echo $msjError;

                                }
                                ?>
                            <li class="footer">
                                <!-- Punto de venta? -->
                            </li>
                            </ul>
                        </li>
                    </ul>
                </li>

                <?php if($objParametros->getPrecioDolar()) { ?>
                <!-- Dropdown Cotización Dólar -->
                <li class="dropdown tasks-menu">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                      <i class="fa fa-money"></i>
                    </a>
                    <ul class="dropdown-menu">
                        <li class="header" style="background-color: #000; color: #fff">Ultima actualizacion dolar</li>
                        <li>
                            <ul class="menu" style="background-color: #eee;">
                                <?php
                                 echo '<li>
                                      <h4>
                                        Fecha: <span>'.$result[0].'</span>
                                      </h4>
                                       <h4>
                                        Valor: $ <span id="cabezoteCotizacionPesos">'. $result[1] .'</span>
                                      </h4>
                                  </li>';
                              ?>
                                  <li class="footer">
                                    <center>
                                        <button class="btn btn-primary" data-toggle="modal" data-target="#modalNuevaCotizacion">
                                      Nueva Cotización
                                        </button>
                                    </center>
                                  </li>
                        </ul>
                      </li>
                    </ul>
                  </li>
            <?php } ?>

                <?php if($_SESSION["perfil"] == "Administrador") { ?>

                <!-- Sistema de Cobro Moon -->
                <li class="dropdown tasks-menu">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" title="Estado de Cuenta - Sistema de Cobro">
                        <i class="fa fa-credit-card" style="font-size: 18px;"></i>
                        <span class="hidden-xs" style="margin-left: 5px;">Estado Cuenta</span>
                        <?php echo isset($badgeNavbar) ? $badgeNavbar : ''; ?>
                    </a>
                    <ul class="dropdown-menu">
                        <li class="header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #fff; padding: 10px;">
                            <i class="fa fa-moon-o"></i> Moon Desarrollos
                        </li>
                        <li>
                            <input type="hidden" id="hiddenClavePublicaMP" value="<?php echo isset($clavePublicaMercadoPago) ? $clavePublicaMercadoPago : ''; ?>">
                            <ul class="menu">
                                <?php echo isset($dropdownContent) ? $dropdownContent : '<p style="padding: 15px;">Sistema de cobro no disponible</p>'; ?>
                            </ul>
                        </li>
                    </ul>
                </li>

                <?php } ?>

                <!-- Usuario -->
                <!-- Usuario -->
                <li class="dropdown user user-menu">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                        <?php
                        if($_SESSION["foto"] != ""){
                            echo '<img src="'.$_SESSION["foto"].'" class="user-image">';
                        }else{
                            echo '<img src="vistas/img/usuarios/default/anonymous.png" class="user-image">';
                        }
                        ?>
                        <span class="hidden-xs"><?php echo $_SESSION["nombre"]; ?></span>
                    </a>
                    
                    <ul class="dropdown-menu">
                        <li class="header" style="background-color: #000; color: #fff; padding: 5px">Datos usuario</li>
                        <li>
                            <ul class="menu" style="background-color: #eee;">
                                <p>Nombre: <?php echo $_SESSION["nombre"]; ?></p>
                                <p>Usuario: <?php echo $_SESSION["usuario"]; ?></p>
                                <p>Perfil: <?php echo $_SESSION["perfil"]; ?></p>
                                <center>
                                    <a href="salir" class="btn btn-primary ">Salir</a>
                                </center>
                            </ul>
                        </li>
                    </ul>
                </li>

            </ul>
        </div>

    </nav>

</header>

<!--=====================================
ESTILOS RESPONSIVE PARA MÓVIL
======================================-->
<style>
/* Responsive para el navbar */
@media (max-width: 768px) {
    .navbar-nav > li > a {
        padding: 10px !important;
    }
    .navbar-nav > li > a > span {
        display: none !important;
    }
    .navbar-nav > li > a > i {
        font-size: 20px !important;
    }
}

/* Responsive para el modal */
@media (max-width: 768px) {
    .modal-dialog.modal-lg {
        margin: 10px !important;
        width: calc(100% - 20px) !important;
    }
    .modal-body {
        padding: 15px !important;
    }
    .modal-header h3 {
        font-size: 20px !important;
    }
    .modal-header i {
        font-size: 32px !important;
    }
}
</style>

<!--=====================================
MODAL COBRO MEJORADO
======================================-->
<div id="modalCobro" class="modal fade" role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="border-radius: 8px; overflow: hidden;">

            <!--=====================================
            CABEZA DEL MODAL - DISEÑO MODERNO
            ======================================-->
            <div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; padding: 25px; text-align: center; position: relative;">
                <button type="button" class="close" data-dismiss="modal" style="position: absolute; right: 15px; top: 15px; color: white; opacity: 0.8; font-size: 28px;">&times;</button>
                <i class="fa fa-credit-card" style="font-size: 40px; margin-bottom: 10px;"></i>
                <h3 style="margin: 5px 0 0 0; font-weight: 400; font-size: 24px;">Estado de Cuenta</h3>
            </div>

            <!--=====================================
            CUERPO DEL MODAL - DISEÑO LIMPIO
            ======================================-->
            <div class="modal-body" style="padding: 0; background: #f5f6fa;">

                <!-- Banner de instrucciones -->
                <div style="background: linear-gradient(to right, #009ee3, #0084c5); color: white; padding: 20px; text-align: center;">
                    <div style="font-size: 18px; font-weight: 500; margin-bottom: 5px;">
                        <i class="fa fa-hand-o-right"></i> Haz clic en el botón "Pagar con Mercado Pago"
                    </div>
                    <div style="font-size: 14px; opacity: 0.9;">
                        Podrás pagar con tarjeta de crédito, débito, transferencia o efectivo
                    </div>
                </div>

                <!-- Contenedor principal -->
                <div style="padding: 25px;">

                <!-- Total a pagar - DESTACADO -->
                <div style="background: white; border-radius: 12px; padding: 30px; text-align: center; box-shadow: 0 2px 10px rgba(0,0,0,0.08); margin-bottom: 20px;">
                    <div style="color: #6c757d; font-size: 14px; font-weight: 500; margin-bottom: 8px;">TOTAL A PAGAR</div>
                    <div style="font-size: 48px; font-weight: 700; color: #667eea; margin: 10px 0;">
                        $<?php echo number_format($abonoMensual, 2, ',', '.'); ?>
                    </div>
                    <div style="color: #6c757d; font-size: 13px;">
                        <i class="fa fa-calendar"></i> <?php echo strftime('%B %Y'); ?>
                    </div>
                </div>

                <div class="row">
                    <!-- Columna izquierda: Detalles -->
                    <div class="col-sm-6">
                        <!-- Cliente -->
                        <div style="background: white; padding: 18px; border-radius: 8px; margin-bottom: 15px; box-shadow: 0 1px 3px rgba(0,0,0,0.06);">
                            <div style="font-size: 12px; color: #6c757d; font-weight: 600; margin-bottom: 8px;">
                                <i class="fa fa-user"></i> CLIENTE
                            </div>
                            <div style="font-size: 16px; color: #2c3e50; font-weight: 500;">
                                <?php echo isset($clienteMoon["nombre"]) ? $clienteMoon["nombre"] : 'Cliente'; ?>
                            </div>
                        </div>

                        <!-- Desglose de Cargos -->
                        <div style="background: white; padding: 18px; border-radius: 8px; margin-bottom: 15px; box-shadow: 0 1px 3px rgba(0,0,0,0.06);">
                            <div style="font-size: 12px; color: #6c757d; font-weight: 600; margin-bottom: 12px;">
                                <i class="fa fa-file-text-o"></i> DETALLE DE CARGOS
                            </div>
                            <div style="font-size: 14px;">
                                <?php
                                // Mostrar servicios mensuales
                                if (count($serviciosMensuales) > 0) {
                                    foreach ($serviciosMensuales as $mov) {
                                        echo '<div style="padding: 8px 0; border-bottom: 1px dashed #e0e0e0; display: flex; justify-content: space-between;">';
                                        echo '<span style="color: #495057;">' . $mov['descripcion'] . '</span>';
                                        echo '<span style="font-weight: 600; color: #dc3545;">$' . number_format($mov['importe'], 2, ',', '.') . '</span>';
                                        echo '</div>';
                                    }
                                }

                                // Mostrar otros cargos
                                if (count($otrosCargos) > 0) {
                                    foreach ($otrosCargos as $mov) {
                                        echo '<div style="padding: 8px 0; border-bottom: 1px dashed #e0e0e0; display: flex; justify-content: space-between;">';
                                        echo '<span style="color: #495057;">' . $mov['descripcion'] . '</span>';
                                        echo '<span style="font-weight: 600; color: #dc3545;">$' . number_format($mov['importe'], 2, ',', '.') . '</span>';
                                        echo '</div>';
                                    }
                                }

                                // Mostrar recargo si aplica
                                if($tieneRecargo && $subtotalMensuales > 0) {
                                    $montoRecargoReal = $subtotalMensuales * ($porcentajeRecargo / 100);
                                    echo '<div style="padding: 8px 0; border-bottom: 1px dashed #e0e0e0; display: flex; justify-content: space-between; background: #fff3cd; margin: 8px -8px; padding-left: 8px; padding-right: 8px;">';
                                    echo '<span style="color: #856404; font-size: 12px;"><i class="fa fa-exclamation-triangle"></i> Recargo (' . $porcentajeRecargo . '%)</span>';
                                    echo '<span style="font-weight: 600; color: #856404;">$' . number_format($montoRecargoReal, 2, ',', '.') . '</span>';
                                    echo '</div>';
                                }
                                ?>
                            </div>
                        </div>
                    </div>

                    <!-- Columna derecha: Instrucciones y métodos -->
                    <div class="col-sm-6">
                        <!-- Instrucciones de pago -->
                        <div style="background: white; padding: 18px; border-radius: 8px; margin-bottom: 15px; box-shadow: 0 1px 3px rgba(0,0,0,0.06);">
                            <div style="font-size: 12px; color: #6c757d; font-weight: 600; margin-bottom: 12px;">
                                <i class="fa fa-info-circle"></i> CÓMO PAGAR
                            </div>
                            <ol style="margin: 0; padding-left: 20px; color: #495057; font-size: 13px; line-height: 1.8;">
                                <li>Haz clic en <strong>"Pagar con Mercado Pago"</strong></li>
                                <li>Elige tu método de pago preferido</li>
                                <li>Completa los datos y confirma</li>
                                <li>¡Listo! Tu cuenta quedará al día</li>
                            </ol>
                        </div>

                        <!-- Información de plazos -->
                        <?php if($tieneRecargo) { ?>
                        <div style="background: #fff3cd; padding: 15px; border-radius: 8px; margin-bottom: 15px; border-left: 4px solid #ffc107;">
                            <div style="font-size: 11px; color: #856404; font-weight: 600; margin-bottom: 8px;">
                                <i class="fa fa-exclamation-triangle"></i> RECARGO POR MORA APLICADO
                            </div>
                            <div style="font-size: 12px; color: #856404; line-height: 1.5;">
                                Este mes incluye un recargo del <strong><?php echo $porcentajeRecargo; ?>%</strong>. 
                                Paga antes del día 10 para evitar recargos.
                            </div>
                        </div>
                        <?php } else { ?>
                        <div style="background: #e8f5e9; padding: 15px; border-radius: 8px; margin-bottom: 15px; border-left: 4px solid #4caf50;">
                            <div style="font-size: 11px; color: #2e7d32; font-weight: 600; margin-bottom: 8px;">
                                <i class="fa fa-check-circle"></i> PAGA A TIEMPO
                            </div>
                            <div style="font-size: 12px; color: #2e7d32; line-height: 1.5;">
                                Realiza tu pago <strong>antes del día 10</strong> para evitar recargos del 10% al 30%.
                            </div>
                        </div>
                        <?php } ?>

                        <!-- Métodos de Pago disponibles -->
                        <div style="background: white; padding: 18px; border-radius: 8px; margin-bottom: 15px; box-shadow: 0 1px 3px rgba(0,0,0,0.06);">
                            <div style="font-size: 12px; color: #6c757d; font-weight: 600; margin-bottom: 12px;">
                                <i class="fa fa-credit-card"></i> MÉTODOS DISPONIBLES
                            </div>
                            <div style="display: flex; flex-wrap: wrap; gap: 10px; margin-top: 10px;">
                                <div style="flex: 1; min-width: 45%; background: #f8f9fa; padding: 10px; border-radius: 6px; text-align: center; font-size: 11px; color: #495057;">
                                    <i class="fa fa-credit-card" style="font-size: 20px; color: #667eea; display: block; margin-bottom: 5px;"></i>
                                    Tarjetas
                                </div>
                                <div style="flex: 1; min-width: 45%; background: #f8f9fa; padding: 10px; border-radius: 6px; text-align: center; font-size: 11px; color: #495057;">
                                    <i class="fa fa-university" style="font-size: 20px; color: #17a2b8; display: block; margin-bottom: 5px;"></i>
                                    Transferencia
                                </div>
                                <div style="flex: 1; min-width: 45%; background: #f8f9fa; padding: 10px; border-radius: 6px; text-align: center; font-size: 11px; color: #495057;">
                                    <i class="fa fa-money" style="font-size: 20px; color: #28a745; display: block; margin-bottom: 5px;"></i>
                                    Efectivo
                                </div>
                                <div style="flex: 1; min-width: 45%; background: #f8f9fa; padding: 10px; border-radius: 6px; text-align: center; font-size: 11px; color: #495057;">
                                    <i class="fa fa-qrcode" style="font-size: 20px; color: #764ba2; display: block; margin-bottom: 5px;"></i>
                                    QR/Débito
                                </div>
                            </div>
                        </div>

                        <!-- Seguridad -->
                        <div style="background: #d4edda; padding: 12px; border-radius: 8px; text-align: center; border: 1px solid #c3e6cb;">
                            <i class="fa fa-shield" style="color: #155724; font-size: 18px; margin-right: 5px;"></i>
                            <span style="color: #155724; font-size: 12px; font-weight: 500;">Pago 100% seguro con encriptación SSL</span>
                        </div>
                    </div>
                </div>

                <?php if($muestroModal && isset($preference)) { ?>
                <!-- Botón de Pago Destacado -->
                <div style="background: white; padding: 25px; border-radius: 10px; box-shadow: 0 2px 12px rgba(0,0,0,0.1); margin-top: 20px;">
                    <!-- Instrucción destacada -->
                    <div style="text-align: center; margin-bottom: 20px;">
                        <div style="display: inline-block; background: linear-gradient(135deg, #e8f5e9, #c8e6c9); padding: 12px 25px; border-radius: 30px; font-size: 14px; font-weight: 600; margin-bottom: 15px; box-shadow: 0 2px 8px rgba(76, 175, 80, 0.2);">
                            <i class="fa fa-hand-pointer-o" style="color: #2e7d32; font-size: 16px; margin-right: 8px;"></i>
                            <span style="color: #2e7d32;">HAZ CLIC AQUÍ ABAJO PARA PAGAR</span>
                        </div>
                    </div>

                    <!-- Botón de Mercado Pago -->
                    <div class="checkout-btn" style="text-align: center; margin: 0;"></div>

                    <!-- Marcas aceptadas -->
                    <div style="text-align: center; margin-top: 20px; padding-top: 18px; border-top: 2px dashed #e0e0e0;">
                        <div style="color: #6c757d; font-size: 11px; font-weight: 600; margin-bottom: 12px; letter-spacing: 0.5px;">MEDIOS DE PAGO ACEPTADOS</div>
                        <div style="display: flex; justify-content: center; align-items: center; flex-wrap: wrap; gap: 10px;">
                            <img src="https://http2.mlstatic.com/storage/logos-api-admin/51b446b0-571c-11e8-9a2d-4b2bd7b1bf77-m.svg" alt="VISA" style="height: 26px; opacity: 0.8;">
                            <img src="https://http2.mlstatic.com/storage/logos-api-admin/aa2b8f70-5c85-11ec-ae75-df2bef173be2-m.svg" alt="Mastercard" style="height: 26px; opacity: 0.8;">
                            <img src="https://http2.mlstatic.com/storage/logos-api-admin/fb0fde10-a7e1-11e9-8dc2-8f4c274d34c5-m.svg" alt="American Express" style="height: 26px; opacity: 0.8;">
                            <img src="https://http2.mlstatic.com/storage/logos-api-admin/d11d7300-a8bf-11ed-92ca-b730e93b1da8-m.svg" alt="Diners Club" style="height: 26px; opacity: 0.8;">
                        </div>
                        <div style="margin-top: 12px; color: #28a745; font-size: 12px; font-weight: 500;">
                            <i class="fa fa-shield"></i> Transacción 100% segura
                        </div>
                    </div>
                </div>

                </div> <!-- Cierre padding principal -->

                <script src="https://sdk.mercadopago.com/js/v2"></script>
                <script type="text/javascript">
                    var clavePublicaMP = document.getElementById('hiddenClavePublicaMP').value;
                    const mp = new MercadoPago(clavePublicaMP, {locale: "es-AR"});

                    mp.checkout({
                        preference: {
                            id: '<?php echo $preference->id; ?>',
                        },
                        render: {
                            container: '.checkout-btn',
                            label: 'Pagar con Mercado Pago',
                        },
                    });
                </script>

                <style>
                    /* Botón de Mercado Pago mejorado */
                    .checkout-btn button {
                        background: linear-gradient(135deg, #009ee3, #0084c5) !important;
                        border-radius: 12px !important;
                        font-size: 19px !important;
                        font-weight: 700 !important;
                        padding: 20px 60px !important;
                        border: none !important;
                        box-shadow: 0 6px 20px rgba(0, 158, 227, 0.35) !important;
                        transition: all 0.3s ease !important;
                        text-transform: uppercase !important;
                        letter-spacing: 1px !important;
                        width: 100% !important;
                        max-width: 450px !important;
                        cursor: pointer !important;
                    }

                    .checkout-btn button:hover {
                        background: linear-gradient(135deg, #0084c5, #006fa5) !important;
                        transform: translateY(-3px) scale(1.03) !important;
                        box-shadow: 0 8px 30px rgba(0, 158, 227, 0.5) !important;
                    }

                    /* Animación de pulso suave */
                    @keyframes pulse-soft {
                        0%, 100% { 
                            box-shadow: 0 6px 20px rgba(0, 158, 227, 0.35);
                            transform: scale(1);
                        }
                        50% { 
                            box-shadow: 0 6px 30px rgba(0, 158, 227, 0.5);
                            transform: scale(1.01);
                        }
                    }

                    .checkout-btn button {
                        animation: pulse-soft 2.5s ease-in-out infinite;
                    }

                    .checkout-btn button:hover {
                        animation: none;
                    }

                    /* Responsive */
                    @media (max-width: 768px) {
                        .checkout-btn button {
                            font-size: 16px !important;
                            padding: 16px 40px !important;
                        }
                    }
                </style>
                <?php } else { ?>
                </div> <!-- Cierre padding principal si no hay botón -->
                <?php } ?>

            </div>

        </div>
    </div>
</div>

<!--=====================================
MODAL NUEVA COTIZACION
======================================-->
<div id="modalNuevaCotizacion" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <form role="form" method="post">

        <!--=====================================
        CABEZA DEL MODAL
        ======================================-->
        <div class="modal-header" style="background:#3c8dbc; color:white">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Nueva Cotización</h4>
        </div>

        <!--=====================================
        CUERPO DEL MODAL
        ======================================-->
        <div class="modal-body">
          <div class="box-body">

            <!-- ENTRADA PARA LA FECHA -->
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-calendar"></i></span> 
                <?php
                    date_default_timezone_set('America/Argentina/Buenos_Aires');
                    $fecha = date('d-m-Y');
                ?>
                <input type="text" readonly class="form-control input-lg" id="nuevaCotizacionFecha" name="nuevaCotizacionFecha" value="<?php echo $fecha; ?> ">
              </div>
            </div>
  
            <!-- ENTRADA PARA LA COTIZACION -->
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-th"></i></span> 
                <input type="number" step="0.01" min="0" class="form-control input-lg" name="nuevaCotizacionPesos" placeholder="Ingresar cotización" required>
              </div>
            </div>
  
          </div>
        </div>

        <!--=====================================
        PIE DEL MODAL
        ======================================-->
        <div class="modal-footer">
          <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Salir</button>
          <button type="submit" class="btn btn-primary">Guardar cotización</button>
        </div>

        <?php

          $nuevaCotizacion = new ControladorCotizacion();
          $nuevaCotizacion -> ctrNuevaCotizacion();

        ?>

      </form>
    </div>
  </div>
</div>

<script type="text/javascript">
$(function(){
    <?php if($muestroModal && $fijoModal) { ?>
        // Modal fijo (no se puede cerrar)
        $("#modalCobro").modal({backdrop: 'static', keyboard: false});

    <?php } elseif ($muestroModal) { ?>
        // Modal normal (mostrar una vez por día, máximo 5 veces)
        var diaDeHoyModal = new Date();
        var dateCformat = [diaDeHoyModal.getDate(), (diaDeHoyModal.getMonth()+1), diaDeHoyModal.getFullYear()].join('/');
        var diaAnterior = localStorage.getItem('diaMostrandoModal');

        if(dateCformat != diaAnterior){
            var cantidadMostrado = Number(localStorage.getItem('modalCobroMostrado'));
            if(!cantidadMostrado){
                localStorage.setItem('modalCobroMostrado', 0);
            }
            if(cantidadMostrado != 5) {
                $("#modalCobro").modal();
                cantidadMostrado = cantidadMostrado + 1;
                localStorage.setItem('modalCobroMostrado', cantidadMostrado);
            } else if (cantidadMostrado == 5) {
                localStorage.setItem('diaMostrandoModal', dateCformat);
                localStorage.setItem('modalCobroMostrado', 0);
            }
        }
    <?php } ?>
});
</script>

<?php
} catch (Exception $e) {
    // Si falla el sistema de cobro, cargar cabezote normal
    error_log("=== SISTEMA DE COBRO NO DISPONIBLE ===");
    error_log("Error: " . $e->getMessage());
    error_log("Archivo: " . $e->getFile());
    error_log("Línea: " . $e->getLine());
    error_log("Trace: " . $e->getTraceAsString());
    error_log("=== CARGANDO CABEZOTE NORMAL ===");
    include "cabezote.php";
}
?>
