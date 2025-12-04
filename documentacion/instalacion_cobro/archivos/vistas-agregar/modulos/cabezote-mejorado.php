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
            CABEZA DEL MODAL - SIMPLE Y LIMPIO
            ======================================-->
            <div class="modal-header" style="background: white; border-bottom: 1px solid #e0e0e0; padding: 20px 25px;">
                <button type="button" class="close" data-dismiss="modal" style="color: #6c757d; opacity: 0.8; font-size: 28px;">&times;</button>
                <h4 style="margin: 0; color: #2c3e50; font-weight: 600; font-size: 20px;">
                    <i class="fa fa-credit-card" style="color: #667eea; margin-right: 8px;"></i>
                    Estado de Cuenta
                </h4>
            </div>

            <!--=====================================
            CUERPO DEL MODAL - DISEÑO TIPO MOBILE
            ======================================-->
            <div class="modal-body" style="padding: 20px; background: #f5f7fa;">

                <!-- Card: Resumen de Pago -->
                <div style="background: white; border-radius: 10px; padding: 20px; margin-bottom: 15px; box-shadow: 0 2px 8px rgba(0,0,0,0.06);">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; padding-bottom: 15px; border-bottom: 2px solid #f0f0f0;">
                        <span style="color: #6c757d; font-size: 13px; font-weight: 600;">TOTAL A PAGAR</span>
                        <div style="text-align: right;">
                            <div style="font-size: 24px; font-weight: 700; color: #667eea;">
                                $<?php echo number_format($abonoMensual, 2, ',', '.'); ?>
                            </div>
                            <div style="font-size: 11px; color: #6c757d;">
                                <i class="fa fa-calendar"></i> <?php echo date('F Y'); ?>
                            </div>
                        </div>
                    </div>
                    
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                        <span style="color: #6c757d; font-size: 13px; font-weight: 600;">CLIENTE</span>
                        <span style="color: #2c3e50; font-size: 14px; font-weight: 500;">
                            <?php echo isset($clienteMoon["nombre"]) ? $clienteMoon["nombre"] : 'Cliente'; ?>
                        </span>
                    </div>

                    <!-- Detalles colapsables -->
                    <div style="border-top: 1px solid #f0f0f0; padding-top: 12px;">
                        <a href="#" onclick="document.getElementById('detallesCargos').style.display = document.getElementById('detallesCargos').style.display === 'none' ? 'block' : 'none'; return false;" style="color: #667eea; font-size: 13px; font-weight: 600; text-decoration: none;">
                            <i class="fa fa-list"></i> DETALLE DE CARGOS
                            <i class="fa fa-chevron-down" style="font-size: 10px; margin-left: 5px;"></i>
                        </a>
                        <div id="detallesCargos" style="display: none; margin-top: 12px; padding-top: 12px; border-top: 1px dashed #e0e0e0;">
                            <?php
                            // Mostrar servicios mensuales
                            if (count($serviciosMensuales) > 0) {
                                foreach ($serviciosMensuales as $mov) {
                                    echo '<div style="display: flex; justify-content: space-between; padding: 6px 0; font-size: 12px;">';
                                    echo '<span style="color: #6c757d;">' . $mov['descripcion'] . '</span>';
                                    echo '<span style="font-weight: 600; color: #dc3545;">$' . number_format($mov['importe'], 2, ',', '.') . '</span>';
                                    echo '</div>';
                                }
                            }

                            // Mostrar otros cargos
                            if (count($otrosCargos) > 0) {
                                foreach ($otrosCargos as $mov) {
                                    echo '<div style="display: flex; justify-content: space-between; padding: 6px 0; font-size: 12px;">';
                                    echo '<span style="color: #6c757d;">' . $mov['descripcion'] . '</span>';
                                    echo '<span style="font-weight: 600; color: #dc3545;">$' . number_format($mov['importe'], 2, ',', '.') . '</span>';
                                    echo '</div>';
                                }
                            }

                            // Mostrar recargo si aplica
                            if($tieneRecargo && $subtotalMensuales > 0) {
                                $montoRecargoReal = $subtotalMensuales * ($porcentajeRecargo / 100);
                                echo '<div style="display: flex; justify-content: space-between; padding: 6px 0; font-size: 12px; background: #fff3cd; margin: 6px -10px; padding-left: 10px; padding-right: 10px; border-radius: 4px;">';
                                echo '<span style="color: #856404;"><i class="fa fa-exclamation-triangle"></i> Recargo (' . $porcentajeRecargo . '%)</span>';
                                echo '<span style="font-weight: 600; color: #856404;">$' . number_format($montoRecargoReal, 2, ',', '.') . '</span>';
                                echo '</div>';
                            }
                            ?>
                        </div>
                    </div>
                </div>

                <!-- Card: Cómo Pagar -->
                <div style="background: white; border-radius: 10px; padding: 20px; margin-bottom: 15px; box-shadow: 0 2px 8px rgba(0,0,0,0.06);">
                    <div style="display: flex; align-items: flex-start; margin-bottom: 12px;">
                        <div style="width: 40px; height: 40px; background: #e3f2fd; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 15px; flex-shrink: 0;">
                            <i class="fa fa-question-circle" style="color: #2196f3; font-size: 20px;"></i>
                        </div>
                        <div>
                            <div style="font-size: 15px; font-weight: 700; color: #2c3e50; margin-bottom: 8px;">CÓMO PAGAR</div>
                            <div style="font-size: 13px; color: #6c757d; line-height: 1.6;">
                                Haz clic en el botón de Mercado Pago que está abajo para completar tu pago de forma segura.
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card: Paga a Tiempo -->
                <?php if($tieneRecargo) { ?>
                <div style="background: white; border-radius: 10px; padding: 20px; margin-bottom: 15px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); border-left: 4px solid #ff9800;">
                    <div style="display: flex; align-items: flex-start;">
                        <div style="width: 40px; height: 40px; background: #fff3e0; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 15px; flex-shrink: 0;">
                            <i class="fa fa-exclamation-triangle" style="color: #ff9800; font-size: 20px;"></i>
                        </div>
                        <div>
                            <div style="font-size: 15px; font-weight: 700; color: #2c3e50; margin-bottom: 8px;">RECARGO APLICADO</div>
                            <div style="font-size: 13px; color: #6c757d; line-height: 1.6;">
                                Este mes incluye un recargo del <strong><?php echo $porcentajeRecargo; ?>%</strong>. 
                                Paga antes del día 10 para evitar recargos futuros.
                            </div>
                        </div>
                    </div>
                </div>
                <?php } else { ?>
                <div style="background: white; border-radius: 10px; padding: 20px; margin-bottom: 15px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); border-left: 4px solid #ff9800;">
                    <div style="display: flex; align-items: flex-start;">
                        <div style="width: 40px; height: 40px; background: #fff3e0; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 15px; flex-shrink: 0;">
                            <i class="fa fa-exclamation-triangle" style="color: #ff9800; font-size: 20px;"></i>
                        </div>
                        <div>
                            <div style="font-size: 15px; font-weight: 700; color: #2c3e50; margin-bottom: 8px;">PAGA A TIEMPO</div>
                            <div style="font-size: 13px; color: #6c757d; line-height: 1.6;">
                                Recuerda que los pagos realizados después del día 10 pueden generar recargos del 10% al 30%.
                            </div>
                        </div>
                    </div>
                </div>
                <?php } ?>

                <!-- Card: Métodos Disponibles -->
                <div style="background: white; border-radius: 10px; padding: 20px; margin-bottom: 15px; box-shadow: 0 2px 8px rgba(0,0,0,0.06);">
                    <div style="font-size: 15px; font-weight: 700; color: #2c3e50; margin-bottom: 15px;">MÉTODOS DISPONIBLES</div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                        <div style="background: #f5f7fa; padding: 16px; border-radius: 8px; text-align: center;">
                            <i class="fa fa-credit-card" style="font-size: 28px; color: #667eea; display: block; margin-bottom: 8px;"></i>
                            <span style="font-size: 12px; color: #6c757d; font-weight: 500;">Tarjetas</span>
                        </div>
                        <div style="background: #f5f7fa; padding: 16px; border-radius: 8px; text-align: center;">
                            <i class="fa fa-university" style="font-size: 28px; color: #17a2b8; display: block; margin-bottom: 8px;"></i>
                            <span style="font-size: 12px; color: #6c757d; font-weight: 500;">Transferencia</span>
                        </div>
                        <div style="background: #f5f7fa; padding: 16px; border-radius: 8px; text-align: center;">
                            <i class="fa fa-money" style="font-size: 28px; color: #28a745; display: block; margin-bottom: 8px;"></i>
                            <span style="font-size: 12px; color: #6c757d; font-weight: 500;">Efectivo</span>
                        </div>
                        <div style="background: #f5f7fa; padding: 16px; border-radius: 8px; text-align: center;">
                            <i class="fa fa-qrcode" style="font-size: 28px; color: #764ba2; display: block; margin-bottom: 8px;"></i>
                            <span style="font-size: 12px; color: #6c757d; font-weight: 500;">QR/Débito</span>
                        </div>
                    </div>
                </div>

                <!-- Seguridad -->
                <div style="text-align: center; margin-bottom: 20px; padding: 12px;">
                    <i class="fa fa-lock" style="color: #28a745; font-size: 16px; margin-right: 6px;"></i>
                    <span style="color: #6c757d; font-size: 13px; font-weight: 500;">Pago 100% seguro con encriptación SSL</span>
                </div>

                <?php if($muestroModal && isset($preference)) { ?>
                <!-- Botón de Pago Grande -->
                <div class="checkout-btn" style="margin-bottom: 15px;"></div>

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
                    /* Botón de Mercado Pago - Estilo simple y grande */
                    .checkout-btn button {
                        background: #009ee3 !important;
                        border-radius: 8px !important;
                        font-size: 17px !important;
                        font-weight: 600 !important;
                        padding: 18px 30px !important;
                        border: none !important;
                        box-shadow: 0 3px 12px rgba(0, 158, 227, 0.25) !important;
                        transition: all 0.2s ease !important;
                        width: 100% !important;
                        cursor: pointer !important;
                        color: white !important;
                        display: flex !important;
                        align-items: center !important;
                        justify-content: center !important;
                        gap: 10px !important;
                    }

                    .checkout-btn button:hover {
                        background: #0084c5 !important;
                        transform: translateY(-2px) !important;
                        box-shadow: 0 5px 18px rgba(0, 158, 227, 0.35) !important;
                    }

                    /* Responsive */
                    @media (max-width: 768px) {
                        .checkout-btn button {
                            font-size: 16px !important;
                            padding: 16px 25px !important;
                        }
                    }
                </style>
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
