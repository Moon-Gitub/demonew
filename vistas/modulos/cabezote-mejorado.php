<?php
//==================================
//      SISTEMA DE COBRO MEJORADO
//==================================

// ID del cliente (configurar según tu sistema)
$idCliente = 7;

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

// Calcular monto con recargos usando el controlador mejorado
$datosCobro = ControladorMercadoPago::ctrCalcularMontoCobro($clienteMoon, $ctaCteCliente);

$abonoMensual = $datosCobro['monto'];
$mensajeCliente = $datosCobro['mensaje'];
$tieneRecargo = $datosCobro['tiene_recargo'];
$porcentajeRecargo = $datosCobro['porcentaje_recargo'];

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

    if ($clienteMoon["estado_bloqueo"] == "1") {
        // Cliente bloqueado
        $estadoClienteBarra = 'style="background-color: #dc3545;"';
        $muestroModal = true;
        $fijoModal = true;
        $badgeNavbar = '<span class="label label-danger">' . number_format($abonoMensual, 0) . '</span>';

    } else {
        if ($diaActual > 4 && $diaActual <= 9) {
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

    // Crear preferencia de MercadoPago
    if(!isset($_GET["preference_id"])) {
        require_once 'extensiones/vendor/autoload.php';

        MercadoPago\SDK::setAccessToken($accesTokenMercadoPago);
        $preference = new MercadoPago\Preference();

        $item = new MercadoPago\Item();
        $item->title = "Mensual-POS Moon Desarrollos";
        $item->quantity = 1;
        $item->unit_price = $abonoMensual;

        $preference->items = array($item);
        $preference->external_reference = strval($idCliente);
        $preference->back_urls = array(
            "success" => $rutaRespuesta,
            "failure" => $rutaRespuesta
        );
        $preference->auto_return = "approved";
        $preference->binary_mode = true;
        $preference->save();

        // Registrar intento de pago
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

                <?php if($_SESSION["perfil"] == "Administrador") { ?>

                <!-- Sistema de Cobro Moon -->
                <li class="dropdown tasks-menu">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                        <i class="fa fa-moon-o" style="font-size: 20px;"></i>
                        <?php echo $badgeNavbar; ?>
                    </a>
                    <ul class="dropdown-menu">
                        <li class="header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #fff; padding: 10px;">
                            <i class="fa fa-moon-o"></i> Moon Desarrollos
                        </li>
                        <li>
                            <input type="hidden" id="hiddenClavePublicaMP" value="<?php echo $clavePublicaMercadoPago; ?>">
                            <ul class="menu">
                                <?php echo $dropdownContent; ?>
                            </ul>
                        </li>
                    </ul>
                </li>

                <?php } ?>

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
                        <li class="user-body">
                            <div class="pull-right">
                                <a href="salir" class="btn btn-default btn-flat">Salir</a>
                            </div>
                        </li>
                    </ul>
                </li>

            </ul>
        </div>

    </nav>

</header>

<!--=====================================
MODAL COBRO MEJORADO
======================================-->
<div id="modalCobro" class="modal fade" role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="border-radius: 8px; overflow: hidden;">

            <!--=====================================
            CABEZA DEL MODAL - DISEÑO MEJORADO
            ======================================-->
            <div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; padding: 30px; text-align: center;">
                <h3 style="margin: 0; font-weight: 300;">
                    <i class="fa fa-moon-o" style="font-size: 48px; display: block; margin-bottom: 10px;"></i>
                    Sistema de Cobro Moon POS
                </h3>
                <p style="margin: 10px 0 0 0; font-size: 16px; opacity: 0.9;">Servicio Mensual</p>
            </div>

            <!--=====================================
            CUERPO DEL MODAL
            ======================================-->
            <div class="modal-body" style="padding: 30px;">

                <!-- Alerta Informativa -->
                <div style="background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; border-radius: 4px; margin-bottom: 25px;">
                    <h4 style="margin-top: 0; color: #856404;">
                        <i class="fa fa-exclamation-triangle"></i> Información Importante
                    </h4>
                    <p style="margin: 10px 0; color: #856404; line-height: 1.6;">
                        Los pagos del servicio mensual deberán realizarse <strong>antes del día 10</strong> de cada mes:
                    </p>
                    <ul style="margin: 10px 0; color: #856404;">
                        <li>Del 10 al 20: Se aplicará un <strong>10% de recargo</strong></li>
                        <li>Del 20 al 25: Se aplicará un <strong>15% de recargo</strong></li>
                        <li>Después del 25: Se aplicará un <strong>30% de recargo</strong></li>
                        <li>Después del 26: <strong>El sistema será suspendido</strong> hasta regularizar la situación</li>
                    </ul>
                </div>

                <div class="row">
                    <!-- Información del Cliente -->
                    <div class="col-sm-6">
                        <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
                            <h4 style="margin-top: 0; color: #495057; border-bottom: 2px solid #dee2e6; padding-bottom: 10px;">
                                <i class="fa fa-user"></i> Detalle del Servicio
                            </h4>
                            <div style="margin: 15px 0;">
                                <strong style="color: #6c757d; display: block; margin-bottom: 5px;">CLIENTE</strong>
                                <p style="font-size: 16px; margin: 0;"><?php echo $clienteMoon["nombre"]; ?></p>
                            </div>
                            <div style="margin: 15px 0;">
                                <strong style="color: #6c757d; display: block; margin-bottom: 5px;">SERVICIO</strong>
                                <p style="font-size: 16px; margin: 0;">
                                    <i class="fa fa-desktop" style="color: #667eea;"></i>
                                    <?php echo $ctaCteMov["descripcion"] ? $ctaCteMov["descripcion"] : "Mensual-POS"; ?>
                                </p>
                            </div>

                            <?php if($tieneRecargo) { ?>
                            <div style="background: #fff3cd; padding: 12px; border-radius: 6px; margin-top: 15px; border-left: 3px solid #ffc107;">
                                <strong style="color: #856404;">
                                    <i class="fa fa-exclamation-triangle"></i>
                                    Recargo aplicado: <?php echo $porcentajeRecargo; ?>%
                                </strong>
                                <p style="margin: 5px 0 0 0; font-size: 13px; color: #856404;">
                                    Por pago fuera de término
                                </p>
                            </div>
                            <?php } ?>
                        </div>
                    </div>

                    <!-- Total a Pagar -->
                    <div class="col-sm-6">
                        <div class="total-cobro-box" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 30px; border-radius: 8px; text-align: center; color: white; box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3); margin-bottom: 20px;">
                            <div style="font-size: 16px; opacity: 0.9; margin-bottom: 10px;">TOTAL A PAGAR</div>
                            <div class="monto-total" style="font-size: 42px; font-weight: 700; margin: 15px 0;">
                                $<?php echo number_format($abonoMensual, 2, ',', '.'); ?>
                            </div>
                            <div style="font-size: 14px; opacity: 0.8;">
                                <i class="fa fa-calendar"></i>
                                <?php echo date('F Y'); ?>
                            </div>
                        </div>

                        <!-- Métodos de Pago -->
                        <div style="text-align: center; margin: 20px 0;">
                            <p style="color: #6c757d; font-size: 13px; margin-bottom: 10px;">Métodos de pago disponibles</p>
                            <div style="font-size: 32px; margin: 10px 0;">
                                <i class="fa fa-credit-card" style="color: #667eea; margin: 0 5px;"></i>
                                <i class="fa fa-credit-card-alt" style="color: #764ba2; margin: 0 5px;"></i>
                                <i class="fa fa-money" style="color: #28a745; margin: 0 5px;"></i>
                                <i class="fa fa-university" style="color: #17a2b8; margin: 0 5px;"></i>
                            </div>
                            <p style="color: #28a745; font-size: 13px; margin-top: 10px;">
                                <i class="fa fa-check-circle"></i> Pago 100% seguro
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Botón de Pago -->
                <?php if($muestroModal && isset($preference)) { ?>
                <div class="checkout-btn" style="text-align: center; margin: 30px 0 20px 0;"></div>

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
                            label: 'Pagar con MercadoPago',
                        },
                    });
                </script>

                <style>
                    .checkout-btn button {
                        background: #009ee3 !important;
                        padding: 15px 50px !important;
                        font-size: 18px !important;
                        border-radius: 50px !important;
                        border: none !important;
                        box-shadow: 0 4px 15px rgba(0, 158, 227, 0.3) !important;
                        transition: all 0.3s ease !important;
                        cursor: pointer !important;
                    }
                    .checkout-btn button:hover {
                        transform: translateY(-2px) !important;
                        box-shadow: 0 6px 20px rgba(0, 158, 227, 0.4) !important;
                    }
                </style>
                <?php } ?>

                <!-- Footer de Seguridad -->
                <div style="text-align: center; padding: 20px; background: #f8f9fa; border-radius: 6px; margin-top: 20px;">
                    <img src="https://imgmp.mlstatic.com/org-img/banners/ar/medios/online/468X60.jpg"
                         alt="MercadoPago"
                         style="max-width: 100%; height: auto; margin-bottom: 10px;">
                    <p style="margin: 10px 0 0 0; color: #6c757d; font-size: 13px;">
                        <i class="fa fa-lock" style="color: #28a745;"></i>
                        Tus datos están protegidos con encriptación SSL
                    </p>
                </div>

            </div>

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
