<?php
//========================
// VALIDACIONES PREVIAS PARA COMPATIBILIDAD
//========================
// Validar si existe $arrayEmpresa (sistema con AFIP)
$tieneAfip = isset($arrayEmpresa) && is_array($arrayEmpresa) && isset($arrayEmpresa["entorno_facturacion"]);

// Validar si existe $objParametros (sistema con cotización dólar)
$tieneCotizacion = isset($objParametros) && is_object($objParametros);

//========================
// CONEXION AFIP (OPCIONAL)
//========================
$conAfip = false;
$msjError="";
$wsfe = null;
$wsaa = null;

if($tieneAfip && $arrayEmpresa["entorno_facturacion"]){
 try {
   $wsaa = new WSAA($arrayEmpresa);

   if (date('Y-m-d H:i:s', strtotime($wsaa->get_expiration())) < date('Y-m-d H:i:s')) {
     $wsaa->generar_TA();
   }

   $wsfe = new WSFE($arrayEmpresa);
   $test = $wsfe->openTA();

   if (isset($test)){
       $conAfip = true;
   } else {
     $conAfip = false;
   }
 } catch (Exception $e) {
   $conAfip = false;
   $msjError = $e->getMessage();
 }
}

//========================
// ARCHIVO COTIZACION (OPCIONAL)
//========================
$result=[];
$archivoCotizacionExiste = file_exists("cotizacion");

if ($archivoCotizacionExiste && $file = fopen("cotizacion", "r")) {
    $i = 0;
    while(!feof($file)) {
        $line = fgets($file);
        $result[$i] = $line;
        $i++;
    }
    fclose($file);
} else {
    $result[0]="No disponible";
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
    if ($clienteMoon === false || !is_array($clienteMoon) || $ctaCteCliente === false) {
        error_log("ERROR COBRO: Cliente ID $idCliente - Consultas fallaron");
        throw new Exception("No se pudieron obtener datos del cliente ID $idCliente");
    }

    // Obtener el nombre del cliente directamente de la BD Moon
    $nombreCliente = 'Cliente';
    try {
        $conexionMoon = Conexion::conectarMoon();
        $stmtNombre = $conexionMoon->prepare("SELECT nombre, email, dominio FROM clientes WHERE id = :id");
        $stmtNombre->bindParam(":id", $idCliente, PDO::PARAM_INT);
        $stmtNombre->execute();
        $datosCliente = $stmtNombre->fetch(PDO::FETCH_ASSOC);
        
        if ($datosCliente && isset($datosCliente['nombre']) && !empty(trim($datosCliente['nombre']))) {
            $nombreCliente = trim($datosCliente['nombre']);
        } elseif ($datosCliente && isset($datosCliente['email']) && !empty(trim($datosCliente['email']))) {
            $nombreCliente = trim($datosCliente['email']);
        } elseif ($datosCliente && isset($datosCliente['dominio']) && !empty(trim($datosCliente['dominio']))) {
            $nombreCliente = trim($datosCliente['dominio']);
        }
    } catch (Exception $e) {
        error_log("Error obteniendo nombre del cliente: " . $e->getMessage());
    }

    // Obtener el saldo pendiente actual
    $saldoPendiente = floatval($ctaCteCliente["saldo"]);

    // Obtener solo los cargos que generan la deuda actual
    $serviciosMensuales = [];
    $otrosCargos = [];
    $subtotalMensuales = 0;
    $subtotalOtros = 0;
    
    if ($saldoPendiente > 0) {
        // Obtener cargos más recientes ordenados por fecha descendente
        $conexionMoon = Conexion::conectarMoon();
        $stmtCargos = $conexionMoon->prepare("
            SELECT descripcion, importe, fecha 
            FROM clientes_cuenta_corriente 
            WHERE id_cliente = :id AND tipo = 0
            ORDER BY fecha DESC
        ");
        $stmtCargos->bindParam(":id", $idCliente, PDO::PARAM_INT);
        $stmtCargos->execute();
        $todosLosCargos = $stmtCargos->fetchAll(PDO::FETCH_ASSOC);
        
        // Tomar solo los cargos necesarios hasta alcanzar el saldo pendiente
        $sumaAcumulada = 0;
        foreach ($todosLosCargos as $cargo) {
            $importe = floatval($cargo['importe']);
            
            // Si aún no llegamos al saldo pendiente, agregar este cargo
            if ($sumaAcumulada < $saldoPendiente) {
                $importePendiente = $importe;
                
                // Si este cargo excede el saldo pendiente, ajustar el importe
                if ($sumaAcumulada + $importe > $saldoPendiente) {
                    $importePendiente = $saldoPendiente - $sumaAcumulada;
                    $descripcion = 'Saldo pendiente de: ' . $cargo['descripcion'];
                } else {
                    $descripcion = $cargo['descripcion'];
                }
                
                // Determinar si es servicio mensual
                if (stripos($cargo['descripcion'], 'Servicio POS') !== false) {
                    $serviciosMensuales[] = array(
                        'descripcion' => $descripcion,
                        'importe' => $importePendiente,
                        'fecha' => $cargo['fecha']
                    );
                    $subtotalMensuales += $importePendiente;
                } else {
                    $otrosCargos[] = array(
                        'descripcion' => $descripcion,
                        'importe' => $importePendiente,
                        'fecha' => $cargo['fecha']
                    );
                    $subtotalOtros += $importePendiente;
                }
                
                $sumaAcumulada += $importePendiente;
                
                // Si ya alcanzamos el saldo pendiente, detener
                if ($sumaAcumulada >= $saldoPendiente) {
                    break;
                }
            }
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
            // Bloquear cliente automáticamente si pasa del día 26
            $resultadoBloqueo = ControladorSistemaCobro::ctrActualizarClientesCobro($idCliente, 1);
            if ($resultadoBloqueo !== false) {
                error_log("✅ Cliente $idCliente bloqueado automáticamente (día $diaActual > 26)");
            } else {
                error_log("⚠️ No se pudo bloquear cliente $idCliente (día $diaActual > 26)");
            }
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

    // **PREVENCIÓN ABSOLUTA DE DUPLICADOS**
    // NO crear preferencia si no es absolutamente necesario
    $preference = null;
    $usarPreferenciaExistente = false;
    
    // Log de entrada
    error_log("=== INICIO VERIFICACIÓN PREFERENCIA ===");
    error_log("Cliente: $idCliente | Monto: $abonoMensual");
    
    if(!isset($_GET["preference_id"])) {
        try {
            // PASO 1: SIEMPRE verificar si existe un intento pendiente reciente ANTES de crear
            $intentoExistente = ControladorMercadoPago::ctrObtenerIntentoPendienteReciente($idCliente, $abonoMensual);
            
            if ($intentoExistente && isset($intentoExistente['preference_id']) && !empty($intentoExistente['preference_id'])) {
                error_log("🔍 INTENTO EXISTENTE ENCONTRADO:");
                error_log("   - ID Intento: " . $intentoExistente['id']);
                error_log("   - Preference ID: " . $intentoExistente['preference_id']);
                error_log("   - Fecha creación: " . $intentoExistente['fecha_creacion']);
                error_log("   ➡️ REUTILIZANDO preferencia existente, NO se creará nueva");
                
                // CRÍTICO: Si hay un intento pendiente, usar su preference_id aunque no se pueda validar en MP
                // Esto evita crear duplicados. Si la preferencia expiró, el usuario puede crear una nueva manualmente
                $usarPreferenciaExistente = true;
                // Crear objeto preference básico con el ID que tenemos en BD
                $preference = (object)['id' => $intentoExistente['preference_id']];
                error_log("✅ Usando preference_id de BD: " . $intentoExistente['preference_id']);
                
                // Intentar validar en MP (opcional, no crítico)
                try {
                    require_once 'extensiones/vendor/autoload.php';
                    \MercadoPago\MercadoPagoConfig::setAccessToken($accesTokenMercadoPago);
                    
                    $client = new \MercadoPago\Client\Preference\PreferenceClient();
                    $preferenceValidada = $client->get($intentoExistente['preference_id']);
                    
                    if ($preferenceValidada && isset($preferenceValidada->id)) {
                        $preference = $preferenceValidada;
                        error_log("✅ Preferencia validada exitosamente en MP: " . $preference->id);
                    } else {
                        error_log("⚠️ Preferencia no válida en MP, pero usando la de BD para evitar duplicados");
                    }
                } catch (Exception $e) {
                    error_log("⚠️ No se pudo validar preferencia en MP: " . $e->getMessage() . " - Usando la de BD");
                }
            } else {
                error_log("ℹ️ No hay intentos pendientes recientes para este cliente/monto");
            }
            
            // PASO 2: Solo crear nueva preferencia si NO existe una pendiente (CRÍTICO: no crear si hay intento)
            if (!$usarPreferenciaExistente && !$intentoExistente) {
                error_log("📝 Creando NUEVA preferencia...");
                
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

                // PASO 3: Registrar intento SOLO si se creó nueva preferencia
                if ($preference && isset($preference->id)) {
                    error_log("✅ Nueva preferencia creada: " . $preference->id);
                    
                    $datosIntento = array(
                        'id_cliente_moon' => $idCliente,
                        'preference_id' => $preference->id,
                        'monto' => $abonoMensual,
                        'descripcion' => 'Pago mensual - ' . date('m/Y'),
                        'fecha_creacion' => date('Y-m-d H:i:s'),
                        'estado' => 'pendiente'
                    );
                    
                    $resultadoRegistro = ControladorMercadoPago::ctrRegistrarIntentoPago($datosIntento);
                    
                    if ($resultadoRegistro === "ok") {
                        error_log("✅ Intento registrado correctamente");
                    } else {
                        error_log("⚠️ No se registró el intento: " . (is_array($resultadoRegistro) ? json_encode($resultadoRegistro) : $resultadoRegistro));
                    }
                } else {
                    error_log("❌ No se pudo crear la preferencia");
                }
            } else {
                error_log("✅ Usando preferencia existente - NO se registra nuevo intento");
            }
            
            error_log("=== FIN VERIFICACIÓN PREFERENCIA ===");

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

                                if ( $tieneAfip && $conAfip && isset($wsfe) && isset($arrayEmpresa) ){

                                  $fecform = date_create($wsfe->datosTA()["Exp"]);
                                  echo '<i class="fa fa-check-circle-o fa-2x" style="color: green"></i></p>';

                                  echo '<p>CUIT: '. $arrayEmpresa['cuit'] . '</p>
                                  <p>Ticket acceso valido hasta: <br/>' . $fecform->format('d/m/Y - H:i:s') .' </p>';

                                  echo '<p>Entorno: ' .$arrayEmpresa['entorno_facturacion'] . '</p>';

                                } else {

                                    echo '<i class="fa fa-times-circle-o fa-2x" style="color: red"></i></p>';

                                    if($tieneAfip && !empty($msjError)) {
                                        echo $msjError;
                                    } else {
                                        echo 'AFIP no configurado';
                                    }

                                }
                                ?>
                            <li class="footer">
                                <!-- Punto de venta? -->
                            </li>
                            </ul>
                        </li>
                    </ul>
                </li>

                <?php if($tieneCotizacion && $objParametros->getPrecioDolar()) { ?>
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
    .modal-header h4 {
        font-size: 18px !important;
    }
}

/* Estilos globales del modal - FUENTES MÁS GRANDES */
#modalCobro .modal-body {
    font-size: 16px !important;
}

/* Tablet y desktop */
@media (min-width: 769px) {
    #modalCobro .card-title {
        font-size: 20px !important;
    }
    #modalCobro .card-text {
        font-size: 17px !important;
    }
    #modalCobro .total-amount {
        font-size: 38px !important;
    }
}

/* Móvil - FUENTES AÚN MÁS GRANDES */
@media (max-width: 768px) {
    #modalCobro .card-title {
        font-size: 18px !important;
    }
    #modalCobro .card-text {
        font-size: 16px !important;
    }
    #modalCobro .total-amount {
        font-size: 32px !important;
    }
    #modalCobro .label-text {
        font-size: 16px !important;
    }
    #modalCobro .value-text {
        font-size: 17px !important;
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
                        <span style="color: #6c757d; font-size: 15px; font-weight: 600;">TOTAL A PAGAR</span>
                        <div style="text-align: right;">
                            <div style="font-size: 32px; font-weight: 700; color: #667eea;">
                                $<?php echo number_format($abonoMensual, 2, ',', '.'); ?>
                            </div>
                            <div style="font-size: 13px; color: #6c757d;">
                                <i class="fa fa-calendar"></i> <?php echo date('F Y'); ?>
                            </div>
                        </div>
                    </div>
                    
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                        <span style="color: #6c757d; font-size: 15px; font-weight: 600;">CLIENTE</span>
                        <span style="color: #2c3e50; font-size: 16px; font-weight: 500;">
                            <?php echo $nombreCliente; ?>
                        </span>
                    </div>

                    <!-- Detalles colapsables -->
                    <div style="border-top: 1px solid #f0f0f0; padding-top: 12px;">
                        <a href="#" onclick="document.getElementById('detallesCargos').style.display = document.getElementById('detallesCargos').style.display === 'none' ? 'block' : 'none'; return false;" style="color: #667eea; font-size: 15px; font-weight: 600; text-decoration: none;">
                            <i class="fa fa-list"></i> DETALLE DE CARGOS
                            <i class="fa fa-chevron-down" style="font-size: 12px; margin-left: 5px;"></i>
                        </a>
                        <div id="detallesCargos" style="display: none; margin-top: 12px; padding-top: 12px; border-top: 1px dashed #e0e0e0;">
                            <?php
                            // Mostrar servicios mensuales
                            if (count($serviciosMensuales) > 0) {
                                foreach ($serviciosMensuales as $mov) {
                                    echo '<div style="display: flex; justify-content: space-between; padding: 8px 0; font-size: 14px;">';
                                    echo '<span style="color: #6c757d;">' . $mov['descripcion'] . '</span>';
                                    echo '<span style="font-weight: 600; color: #dc3545;">$' . number_format($mov['importe'], 2, ',', '.') . '</span>';
                                    echo '</div>';
                                }
                            }

                            // Mostrar otros cargos
                            if (count($otrosCargos) > 0) {
                                foreach ($otrosCargos as $mov) {
                                    echo '<div style="display: flex; justify-content: space-between; padding: 8px 0; font-size: 14px;">';
                                    echo '<span style="color: #6c757d;">' . $mov['descripcion'] . '</span>';
                                    echo '<span style="font-weight: 600; color: #dc3545;">$' . number_format($mov['importe'], 2, ',', '.') . '</span>';
                                    echo '</div>';
                                }
                            }

                            // Mostrar recargo si aplica
                            if($tieneRecargo && $subtotalMensuales > 0) {
                                $montoRecargoReal = $subtotalMensuales * ($porcentajeRecargo / 100);
                                echo '<div style="display: flex; justify-content: space-between; padding: 8px 0; font-size: 14px; background: #fff3cd; margin: 6px -10px; padding-left: 10px; padding-right: 10px; border-radius: 4px;">';
                                echo '<span style="color: #856404;"><i class="fa fa-exclamation-triangle"></i> Recargo (' . $porcentajeRecargo . '%)</span>';
                                echo '<span style="font-weight: 600; color: #856404;">$' . number_format($montoRecargoReal, 2, ',', '.') . '</span>';
                                echo '</div>';
                            }
                            ?>
                        </div>
                    </div>
                </div>

                <!-- Card: Cómo Pagar -->
                <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 10px; padding: 20px; margin-bottom: 15px; box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);">
                    <div style="display: flex; align-items: flex-start; margin-bottom: 12px;">
                        <div style="width: 50px; height: 50px; background: rgba(255, 255, 255, 0.2); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 15px; flex-shrink: 0;">
                            <i class="fa fa-exclamation-circle" style="color: white; font-size: 24px;"></i>
                        </div>
                        <div>
                            <div style="font-size: 18px; font-weight: 700; color: white; margin-bottom: 8px;">⚠️ IMPORTANTE: FORMA DE PAGO</div>
                            <div style="font-size: 16px; color: rgba(255, 255, 255, 0.95); line-height: 1.7; font-weight: 500;">
                                <strong>La única forma de que se compute tu pago es haciendo clic en el botón de Mercado Pago.</strong><br>
                                <span style="font-size: 14px; margin-top: 8px; display: block;">❌ NO se deben hacer transferencias bancarias<br>
                                ❌ NO se computan pagos fuera del botón</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card: Paga a Tiempo -->
                <?php if($tieneRecargo) { ?>
                <div style="background: white; border-radius: 10px; padding: 20px; margin-bottom: 15px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); border-left: 4px solid #ff9800;">
                    <div style="display: flex; align-items: flex-start;">
                        <div style="width: 45px; height: 45px; background: #fff3e0; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 15px; flex-shrink: 0;">
                            <i class="fa fa-exclamation-triangle" style="color: #ff9800; font-size: 22px;"></i>
                        </div>
                        <div>
                            <div style="font-size: 17px; font-weight: 700; color: #2c3e50; margin-bottom: 8px;">RECARGO APLICADO</div>
                            <div style="font-size: 15px; color: #6c757d; line-height: 1.6;">
                                Este mes incluye un recargo del <strong><?php echo $porcentajeRecargo; ?>%</strong>. 
                                Paga antes del día 10 para evitar recargos futuros.
                            </div>
                        </div>
                    </div>
                </div>
                <?php } else { ?>
                <div style="background: white; border-radius: 10px; padding: 20px; margin-bottom: 15px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); border-left: 4px solid #ff9800;">
                    <div style="display: flex; align-items: flex-start;">
                        <div style="width: 45px; height: 45px; background: #fff3e0; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 15px; flex-shrink: 0;">
                            <i class="fa fa-exclamation-triangle" style="color: #ff9800; font-size: 22px;"></i>
                        </div>
                        <div>
                            <div style="font-size: 17px; font-weight: 700; color: #2c3e50; margin-bottom: 8px;">PAGA A TIEMPO</div>
                            <div style="font-size: 15px; color: #6c757d; line-height: 1.7;">
                                <strong>Recargos por pagos fuera de término:</strong><br>
                                • Del 1 al 10: Sin recargo<br>
                                • Del 10 al 20: <strong>10% de recargo</strong><br>
                                • Del 20 al 25: <strong>15% de recargo</strong><br>
                                • Después del 25: <strong>30% de recargo</strong><br>
                                • Después del 26: <strong>Suspensión del sistema</strong>
                            </div>
                        </div>
                    </div>
                </div>
                <?php } ?>

                <!-- Card: Información de Pago -->
                <div style="background: white; border-radius: 10px; padding: 20px; margin-bottom: 15px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); border-left: 4px solid #009ee3;">
                    <div style="font-size: 17px; font-weight: 700; color: #2c3e50; margin-bottom: 12px;">
                        <i class="fa fa-info-circle" style="color: #009ee3; margin-right: 8px;"></i>
                        INFORMACIÓN IMPORTANTE
                    </div>
                    <div style="font-size: 15px; color: #6c757d; line-height: 1.7;">
                        <strong>El pago se procesa únicamente a través del botón de Mercado Pago.</strong><br>
                        Una vez que hagas clic en el botón, podrás elegir el método de pago que prefieras (tarjeta, transferencia, efectivo, etc.) dentro de la plataforma de Mercado Pago.
                    </div>
                </div>

                <!-- Seguridad -->
                <div style="text-align: center; margin-bottom: 20px; padding: 12px;">
                    <i class="fa fa-lock" style="color: #28a745; font-size: 18px; margin-right: 6px;"></i>
                    <span style="color: #6c757d; font-size: 15px; font-weight: 500;">Pago 100% seguro con encriptación SSL</span>
                </div>

                <?php if($muestroModal && isset($preference)) { ?>
                <!-- Botones de Pago -->
                <div style="margin-bottom: 20px;">
                    <!-- Mensaje destacado sobre el botón -->
                    <div style="background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin-bottom: 20px; border-radius: 6px;">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <i class="fa fa-hand-pointer-o" style="font-size: 24px; color: #ffc107;"></i>
                            <div>
                                <strong style="color: #856404; font-size: 16px;">¡HAZ CLIC EN EL BOTÓN DE ABAJO PARA PAGAR!</strong>
                                <div style="color: #856404; font-size: 14px; margin-top: 5px;">
                                    Este es el único método que computa tu pago correctamente.
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Botón Mercado Pago -->
                    <div class="checkout-btn" style="margin-bottom: 15px;"></div>
                    
                    <?php
                    /* ============================================
                       CÓDIGO QR - COMENTADO (NO ELIMINADO)
                       ============================================
                       Este código genera y muestra un código QR para pagos.
                       Actualmente está oculto pero se mantiene comentado
                       por si se necesita reactivar en el futuro.
                    */
                    /*
                    <!-- Divisor -->
                    <div style="text-align: center; margin: 20px 0; color: #6c757d; font-size: 14px; position: relative;">
                        <span style="background: #f5f7fa; padding: 0 15px; position: relative; z-index: 1;">O ESCANEA EL QR</span>
                        <div style="position: absolute; top: 50%; left: 0; right: 0; height: 1px; background: #e0e0e0; z-index: 0;"></div>
                    </div>
                    
                    <!-- Card QR -->
                    <div style="background: white; border-radius: 10px; padding: 25px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); text-align: center;">
                        <div style="font-size: 16px; font-weight: 600; color: #2c3e50; margin-bottom: 15px;">
                            <i class="fa fa-qrcode" style="color: #667eea; margin-right: 8px;"></i>
                            Pagar con código QR
                        </div>
                        <div style="font-size: 14px; color: #6c757d; margin-bottom: 20px;">
                            Escanea con tu celular para pagar
                        </div>
                        
                        <!-- QR Code generado localmente con PHP -->
                        <div id="qr-container" style="display: inline-block; padding: 15px; background: white; border: 2px solid #e0e0e0; border-radius: 8px;">
                            <?php 
                            $qrUrl = "generar-qr.php?url=" . urlencode($preference->init_point);
                            ?>
                            <img src="<?php echo $qrUrl; ?>" 
                                 alt="Código QR para pagar" 
                                 style="width: 250px; height: 250px; display: block;"
                                 onerror="this.parentElement.innerHTML='<div style=\'padding:50px;color:#dc3545;\'>Error generando QR</div>'">
                        </div>
                        
                        <div style="margin-top: 15px; font-size: 13px; color: #6c757d;">
                            <i class="fa fa-info-circle"></i> Abre la cámara de tu celular y apunta al código
                        </div>
                    </div>
                    */
                    ?>
                </div>

                <script src="https://sdk.mercadopago.com/js/v2"></script>
                <script type="text/javascript">
                    var clavePublicaMP = document.getElementById('hiddenClavePublicaMP').value;
                    const mp = new MercadoPago(clavePublicaMP, {locale: "es-AR"});
                    var preferenceIdActual = '<?php echo $preference->id; ?>';

                    mp.checkout({
                        preference: {
                            id: preferenceIdActual,
                        },
                        render: {
                            container: '.checkout-btn',
                            label: 'Pagar con Mercado Pago',
                        },
                    });
                    
                    // VERIFICACIÓN AUTOMÁTICA DE PAGOS QR PENDIENTES (solo cuando se abre el modal)
                    // Esto es un respaldo al webhook que puede no estar recibiendo notificaciones
                    $('#modalCobro').on('shown.bs.modal', function() {
                        // Verificar una vez si hay pagos QR pendientes que no se registraron
                        setTimeout(function() {
                            $.ajax({
                                url: 'ajax/verificar-pagos-qr-pendientes.ajax.php',
                                method: 'GET',
                                dataType: 'json',
                                success: function(resp) {
                                    if (resp.pagos_registrados > 0) {
                                        console.log('Pagos QR encontrados y registrados:', resp);
                                        // Recargar para mostrar el saldo actualizado
                                        location.reload();
                                    }
                                },
                                error: function() {
                                    // Error silencioso
                                }
                            });
                        }, 3000); // Esperar 3 segundos después de abrir el modal
                    });
                    
                </script>

                <style>
                    /* Botón de Mercado Pago - MUY DESTACADO Y VISIBLE */
                    .checkout-btn {
                        position: relative;
                        margin: 20px 0 !important;
                    }
                    
                    .checkout-btn button {
                        background: linear-gradient(135deg, #009ee3 0%, #0077b6 100%) !important;
                        border-radius: 12px !important;
                        font-size: 22px !important;
                        font-weight: 700 !important;
                        padding: 25px 40px !important;
                        border: 3px solid #0077b6 !important;
                        box-shadow: 0 8px 25px rgba(0, 158, 227, 0.4), 0 0 20px rgba(0, 158, 227, 0.2) !important;
                        transition: all 0.3s ease !important;
                        width: 100% !important;
                        cursor: pointer !important;
                        color: white !important;
                        display: flex !important;
                        align-items: center !important;
                        justify-content: center !important;
                        gap: 12px !important;
                        text-transform: uppercase !important;
                        letter-spacing: 1px !important;
                        position: relative !important;
                        overflow: hidden !important;
                        animation: pulse-button 2s infinite !important;
                    }
                    
                    /* Animación de pulso para llamar la atención */
                    @keyframes pulse-button {
                        0%, 100% {
                            box-shadow: 0 8px 25px rgba(0, 158, 227, 0.4), 0 0 20px rgba(0, 158, 227, 0.2);
                        }
                        50% {
                            box-shadow: 0 8px 35px rgba(0, 158, 227, 0.6), 0 0 30px rgba(0, 158, 227, 0.4);
                            transform: scale(1.02);
                        }
                    }
                    
                    /* Efecto de brillo al pasar el mouse */
                    .checkout-btn button::before {
                        content: '';
                        position: absolute;
                        top: 0;
                        left: -100%;
                        width: 100%;
                        height: 100%;
                        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
                        transition: left 0.5s;
                    }
                    
                    .checkout-btn button:hover::before {
                        left: 100%;
                    }

                    .checkout-btn button:hover {
                        background: linear-gradient(135deg, #0077b6 0%, #005a8a 100%) !important;
                        transform: translateY(-3px) scale(1.02) !important;
                        box-shadow: 0 12px 35px rgba(0, 158, 227, 0.5), 0 0 40px rgba(0, 158, 227, 0.3) !important;
                        border-color: #005a8a !important;
                    }
                    
                    .checkout-btn button:active {
                        transform: translateY(-1px) scale(0.98) !important;
                    }
                    
                    /* Icono dentro del botón más grande */
                    .checkout-btn button i {
                        font-size: 24px !important;
                    }

                    /* Responsive */
                    @media (max-width: 768px) {
                        .checkout-btn button {
                            font-size: 18px !important;
                            padding: 22px 30px !important;
                        }
                        
                        .checkout-btn button i {
                            font-size: 20px !important;
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
        // Solo procesar si existe el controlador de cotización
        if (class_exists('ControladorCotizacion')) {
          $nuevaCotizacion = new ControladorCotizacion();
          $nuevaCotizacion -> ctrNuevaCotizacion();
        }
        ?>

      </form>
    </div>
  </div>
</div>

<script type="text/javascript">
// ============================================
// CONTROL DE APARICIONES DEL MODAL DE COBRO
// Máximo 3 veces por sesión de login (incluso si está bloqueado)
// ============================================
console.log('🔍 Script de control de modal iniciado');

$(document).ready(function(){
    console.log('📄 Documento listo, verificando modal...');
    
    <?php if($muestroModal) { ?>
        // Control de apariciones para TODOS los modales (fijo o normal)
        // Máximo 3 veces por sesión, incluso si el cliente está bloqueado
        console.log('<?php echo $fijoModal ? "🔒 Modal FIJO" : "🔓 Modal NORMAL"; ?>: verificando si se debe mostrar...');
        
        try {
            // Inicializar contador si no existe
            var cantidadMostrado = parseInt(sessionStorage.getItem('modalCobroMostrado')) || 0;
            console.log('📊 Contador actual:', cantidadMostrado, '/3');
            
            // Obtener URL actual (sin parámetros GET)
            var urlActual = window.location.pathname;
            var ultimaUrl = sessionStorage.getItem('modalCobroUltimaUrl') || '';
            console.log('🌐 URL actual:', urlActual);
            console.log('🌐 Última URL:', ultimaUrl);
            
            // Verificar si ya se mostró en esta página
            var yaMostradoEnEstaPagina = (ultimaUrl === urlActual);
            console.log('✅ Ya mostrado en esta página?', yaMostradoEnEstaPagina);
            
            // Verificar si el modal ya está abierto
            var modalYaAbierto = $("#modalCobro").hasClass('in') || $("#modalCobro").hasClass('show') || $("#modalCobro").is(':visible');
            console.log('👁️ Modal ya abierto?', modalYaAbierto);
            
            // DECISIÓN: Solo mostrar si:
            // 1. No se alcanzó el límite de 3 veces
            // 2. No se mostró ya en esta página (evita recargas)
            // 3. El modal no está ya abierto
            if (cantidadMostrado < 3 && !yaMostradoEnEstaPagina && !modalYaAbierto) {
                console.log('✅ CONDICIONES CUMPLIDAS: Se mostrará el modal');
                
                // Incrementar contador INMEDIATAMENTE
                cantidadMostrado = cantidadMostrado + 1;
                sessionStorage.setItem('modalCobroMostrado', cantidadMostrado);
                sessionStorage.setItem('modalCobroUltimaUrl', urlActual);
                
                console.log('📈 Contador actualizado a:', cantidadMostrado, '/3');
                console.log('💾 Guardado en sessionStorage');
                
                // Mostrar el modal (fijo o normal según corresponda)
                setTimeout(function() {
                    console.log('🚀 Abriendo modal...');
                    <?php if($fijoModal) { ?>
                        // Modal fijo: no se puede cerrar
                        $("#modalCobro").modal({backdrop: 'static', keyboard: false});
                    <?php } else { ?>
                        // Modal normal: se puede cerrar
                        $("#modalCobro").modal();
                    <?php } ?>
                }, 300);
                
            } else {
                if (cantidadMostrado >= 3) {
                    console.log('⛔ NO SE MUESTRA: Límite de 3 veces alcanzado');
                } else if (yaMostradoEnEstaPagina) {
                    console.log('⛔ NO SE MUESTRA: Ya se mostró en esta página');
                } else if (modalYaAbierto) {
                    console.log('⛔ NO SE MUESTRA: Modal ya está abierto');
                }
            }
        } catch (error) {
            console.error('❌ ERROR en control de modal:', error);
            // En caso de error, no mostrar el modal para evitar spam
        }
    
    <?php } else { ?>
        console.log('ℹ️ Modal no debe mostrarse (cliente al día o sin deuda)');
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
