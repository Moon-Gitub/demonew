<?php

// Habilitar reporte de errores para debugging
error_reporting(E_ALL);
ini_set('display_errors', 1); // Mostrar errores temporalmente para debugging
ini_set('log_errors', 1);

// Crear log específico para este archivo
$logFile = dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/error_log_comprobante.txt';
ini_set('error_log', $logFile);

// Registrar inicio de ejecución
error_log("==========================================");
error_log("INICIO comprobante.php - " . date('Y-m-d H:i:s'));
error_log("Código recibido: " . (isset($_GET['codigo']) ? $_GET['codigo'] : 'NO DEFINIDO'));
error_log("==========================================");

// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Validar que se haya proporcionado el código
if(!isset($_GET['codigo']) || empty($_GET['codigo'])) {
    error_log("Error comprobante.php: No se proporcionó el código");
    http_response_code(400);
    die('Error: No se proporcionó el código del comprobante');
}

// Cargar autoload primero (usar ruta relativa como en recibo.php)
$autoloadPath = '../../../autoload.php';
error_log("Buscando autoload en: " . __DIR__ . '/' . $autoloadPath);
if(!file_exists(__DIR__ . '/' . $autoloadPath)) {
    error_log("ERROR: autoload.php no encontrado en ruta relativa");
    die('Error: No se encuentra autoload.php');
}
error_log("✅ autoload.php encontrado, cargando...");
require_once $autoloadPath;
error_log("✅ autoload.php cargado");

// Obtener la ruta base del proyecto (raíz donde está .env)
// Desde: extensiones/vendor/tecnickcom/tcpdf/pdf/comprobante.php
// Hacia: raíz del proyecto (6 niveles arriba)
$rutaBase = dirname(dirname(dirname(dirname(dirname(dirname(__FILE__))))));
error_log("Ruta base calculada: " . $rutaBase);
error_log("Ruta base existe: " . (is_dir($rutaBase) ? 'SÍ' : 'NO'));

// Cargar variables de entorno desde .env PRIMERO (si existe y si Dotenv está instalado)
// IMPORTANTE: Se carga antes de los modelos para que .env esté disponible
$envPath = $rutaBase . '/.env';
if (file_exists($envPath)) {
    if (class_exists('Dotenv\Dotenv')) {
        try {
            $dotenv = Dotenv\Dotenv::createImmutable($rutaBase);
            $dotenv->load();
            error_log("✅ .env cargado correctamente desde: " . $envPath);
        } catch (Exception $e) {
            error_log("❌ Error al cargar .env en comprobante.php: " . $e->getMessage());
            // Continuar aunque falle el .env, puede que las variables estén en otro lugar
        }
    } else {
        error_log("⚠️ Dotenv no está disponible, intentando leer .env manualmente");
        // Leer .env manualmente si Dotenv no está disponible
        $envLines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($envLines as $line) {
            if (strpos(trim($line), '#') === 0) continue; // Saltar comentarios
            if (strpos($line, '=') !== false) {
                list($key, $value) = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);
                if (!empty($key)) {
                    $_ENV[$key] = $value;
                    $_SERVER[$key] = $value;
                    putenv("$key=$value");
                }
            }
        }
    }
} else {
    error_log("⚠️ Archivo .env no encontrado en: " . $envPath);
}

// Cargar helpers (incluye función env() para leer variables)
$helpersPath = $rutaBase . '/helpers.php';
if (file_exists($helpersPath)) {
    require_once $helpersPath;
} else {
    error_log("⚠️ helpers.php no encontrado en: " . $helpersPath);
}

// Usar rutas relativas como en recibo.php que funciona
$archivos = [
    "../../../../../controladores/empresa.controlador.php",
    "../../../../../modelos/empresa.modelo.php",
    "../../../../../controladores/ventas.controlador.php",
    "../../../../../modelos/ventas.modelo.php",
    "../../../../../controladores/clientes.controlador.php",
    "../../../../../modelos/clientes.modelo.php",
    "../../../../../controladores/usuarios.controlador.php",
    "../../../../../modelos/usuarios.modelo.php",
    "../../../../../controladores/productos.controlador.php",
    "../../../../../modelos/productos.modelo.php",
    '../../../autoload.php'
];

foreach ($archivos as $archivo) {
    $rutaCompleta = __DIR__ . '/' . $archivo;
    error_log("Verificando archivo: " . basename($archivo) . " en " . $rutaCompleta);
    if (!file_exists($rutaCompleta)) {
        error_log("❌ Archivo no encontrado: " . $rutaCompleta);
        die('Error: Archivo requerido no encontrado: ' . basename($archivo) . ' en ' . $rutaCompleta);
    }
    error_log("✅ Cargando: " . basename($archivo));
    require_once $archivo;
    error_log("✅ Cargado: " . basename($archivo));
}
error_log("✅ Todos los archivos requeridos cargados correctamente");

class imprimirComprobante{

public function traerImpresionComprobante(){

error_log("Iniciando traerImpresionComprobante()");

$tiposCbtes = array(
0 => 'X',
1 => 'Factura A',
6 => 'Factura B', 
11 => 'Factura C',
51 => 'Factura M',
2 => 'Nota Débito A',
7 => 'Nota Débito B',
12 => 'Nota Débito C',
52 => 'Nota Débito M',
3 => 'Nota Crédito A',
8 => 'Nota Crédito B',
13 => 'Nota Crédito C',
53 => 'Nota Crédito M',
4 => 'Recibo A',
9 => 'Recibo B',
15 => 'Recibo C',
54 => 'Recibo M',
'' => 'no definido');

$tiposCbtesLetras = array(
0 => 'X',
1 => 'A',
6 => 'B', 
11 => 'C',
51 => 'M',
2 => 'A',
7 => 'B',
12 => 'C',
52 => 'M',
3 => 'A',
8 => 'B',
13 => 'C',
53 => 'M',
4 => 'A',
9 => 'B',
15 => 'C',
54 => 'M',
'' => 'X');

$arrTipoDocumento = array(
96 => "DNI",
80 => "CUIT",
86 => "CUIL",
87 => "CDI",
89 => "LE",
90 => "LC",
92 => "En trámite",
93 => "Acta nacimiento",
94 => "Pasaporte",
91 => "CI extranjera",
99 => "Otro",
0 => "(no definido)");

$condIva = array(
0 => "Consumidor Final",
1 => "IVA Responsable Inscripto",
4 => "IVA Sujeto Exento",
5 => "Consumidor Final",
6 => "Responsable Monotributo",
7 => "Sujeto no Categorizado",
8 => "Proveedor del Exterior",
9 => "Cliente del Exterior",
10 => "IVA Liberado – Ley Nº 19.640",
13 => "Monotributista Social",
15 => "IVA no alcanzado",
16 => "Monotributo Trabajador Independiente Promovido",
''=>"(no definido)");

//TRAEMOS LA INFORMACIÓN DE LA VENTA
try {
    $codigoVenta = intval($_GET['codigo']);
    
    if($codigoVenta <= 0) {
        error_log("Error comprobante.php: Código de venta inválido: " . $_GET['codigo']);
        http_response_code(400);
        die('Error: Código de venta inválido');
    }
    
    $respuestaVenta = ControladorVentas::ctrMostrarVentas('codigo', $codigoVenta);
    
    // Validar que se obtuvo la venta
    if(!$respuestaVenta || empty($respuestaVenta) || !isset($respuestaVenta["id"])) {
        error_log("Error comprobante.php: No se encontró la venta con código " . $codigoVenta);
        http_response_code(404);
        die('Error: No se encontró la venta con código ' . $codigoVenta);
    }
    
    $facturada = ControladorVentas::ctrVentaFacturada($respuestaVenta["id"]);
    
    if(!isset($respuestaVenta["id_cliente"]) || empty($respuestaVenta["id_cliente"])) {
        error_log("Error comprobante.php: La venta no tiene cliente asociado");
        http_response_code(500);
        die('Error: La venta no tiene cliente asociado');
    }
    
    $respuestaCliente = ControladorClientes::ctrMostrarClientes('id', $respuestaVenta["id_cliente"]);
    
    // Validar que se obtuvo el cliente
    if(!$respuestaCliente || empty($respuestaCliente)) {
        error_log("Error comprobante.php: No se encontró el cliente con ID " . $respuestaVenta["id_cliente"]);
        http_response_code(404);
        die('Error: No se encontró el cliente de la venta');
    }
} catch(Exception $e) {
    error_log("Error comprobante.php al obtener datos de venta: " . $e->getMessage());
    http_response_code(500);
    die('Error al obtener los datos de la venta: ' . $e->getMessage());
}





try {

    error_log("Obteniendo datos de empresa...");

    $respEmpresa = ModeloEmpresa::mdlMostrarEmpresa('empresa', 'id', $respuestaVenta["id_empresa"]);

    error_log("Datos de empresa obtenidos: " . (is_array($respEmpresa) ? 'SÍ' : 'NO'));

    

    // Validar que se obtuvo la empresa

    if(!$respEmpresa || empty($respEmpresa)) {

        error_log("Error comprobante.php: No se pudo obtener la información de la empresa");

        http_response_code(500);

        die('Error: No se pudo obtener la información de la empresa');

    }

    

    //REQUERIMOS LA CLASE TCPDF

    if(!class_exists('TCPDF')) {

        error_log("Error comprobante.php: La clase TCPDF no está disponible");

        http_response_code(500);

        die('Error: La clase TCPDF no está disponible');

    }

    

    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    
    // Configuración del documento
    $pdf->SetCreator('Posmoon');
    $pdf->SetTitle($respEmpresa["razon_social"]);
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    
    // Agregar primera página
    $pdf->AddPage('P', 'A4');
    
    // Configurar márgenes normales
    $pdf->SetMargins(10, 10, 10);
    $pdf->SetAutoPageBreak(true, 10);

} catch(Exception $e) {

    error_log("Error comprobante.php en inicialización: " . $e->getMessage());

    http_response_code(500);

    die('Error al inicializar el PDF: ' . $e->getMessage());

}



$tipoDocumento = isset($arrTipoDocumento[$respuestaCliente["tipo_documento"]]) ? $arrTipoDocumento[$respuestaCliente["tipo_documento"]] : "(no definido)";

$tipoIva = isset($condIva[$respEmpresa["condicion_iva"]]) ? $condIva[$respEmpresa["condicion_iva"]] : "(no definido)";

$tipoIvaCliente = isset($condIva[$respuestaCliente["condicion_iva"]]) ? $condIva[$respuestaCliente["condicion_iva"]] : "(no definido)";

try {
    $fecha = isset($respuestaVenta["fecha"]) ? substr($respuestaVenta["fecha"],0,-8) : date("Y-m-d");
    $fecha = date("d-m-Y",strtotime($fecha));
    
    // Obtener productos desde tabla relacional
    // El controlador ya está cargado arriba, solo necesitamos obtener los productos
    $productos = ControladorVentas::ctrObtenerProductosVentaLegacy($respuestaVenta["id"]);
    
    // Validar que se obtuvieron los productos
    if(!is_array($productos) || empty($productos)) {
        error_log("Error comprobante.php: No se pudieron obtener los productos de la venta ID: " . $respuestaVenta["id"]);
        error_log("Código de venta: " . $codigoVenta);
        error_log("Respuesta de ctrObtenerProductosVentaLegacy: " . (is_array($productos) ? 'Array con ' . count($productos) . ' elementos' : gettype($productos)));
        error_log("IMPORTANTE: Esta venta necesita ser migrada. Ejecutar: db/migrar-venta-especifica.sql con id_venta = " . $respuestaVenta["id"]);
        http_response_code(500);
        die('Error: No se encontraron productos en la venta. ID venta: ' . $respuestaVenta["id"] . ', Código: ' . $codigoVenta . '. Esta venta necesita ser migrada a la tabla productos_venta.');
    }
    
    $tamanioProd = count($productos);
} catch(Exception $e) {
    error_log("Error comprobante.php al procesar productos: " . $e->getMessage());
    http_response_code(500);
    die('Error al procesar los productos: ' . $e->getMessage());
}
$totalNumero = isset($respuestaVenta["total"]) ? floatval($respuestaVenta["total"]) : 0;
$total = '$ ' . number_format($totalNumero, 2, ',', '.');
$observaciones = isset($respuestaVenta["observaciones"]) ? htmlspecialchars($respuestaVenta["observaciones"], ENT_QUOTES, 'UTF-8') : '';
$subTotalNumero = isset($respuestaVenta["neto"]) ? floatval($respuestaVenta["neto"]) : 0;
$subTotal = '$ ' . number_format($subTotalNumero, 2, ',', '.');
$netoGravNumero = isset($respuestaVenta["neto_gravado"]) ? floatval($respuestaVenta["neto_gravado"]) : 0;
$neto_grav = '$ ' . number_format($netoGravNumero, 2, ',', '.');
$jsnPago = json_decode($respuestaVenta["metodo_pago"], true);
if(!is_array($jsnPago)) {
    $jsnPago = array();
}

//$descuentos = $jsnPago[0]["descuento"] * $respuestaVenta["neto"] / 100;
//$descuentos = $respuestaVenta["descuento"] * $respuestaVenta["neto"] / 100;
$descuentosNumero = isset($respuestaVenta["descuento"]) ? floatval($respuestaVenta["descuento"]) : 0;
$descuentos = '$ ' . number_format($descuentosNumero, 2, ',','.');

if($respuestaVenta["cbte_tipo"] == "0") {

$tipoVtaLetra = "X";
$tipoCodigo = "";
$tipoVta = "Documento no válido como factura";
$numCte = str_pad($respuestaVenta["codigo"], 8, "0", STR_PAD_LEFT);
$vtoCae ="-";
$cae ="-";

} else {

$factura = ControladorVentas::ctrVentaFacturadaDatos($respuestaVenta["id"]);
$nroCbte = isset($factura["nro_cbte"]) ? $factura["nro_cbte"] : 0;
$caeFactura = isset($factura["cae"]) ? $factura["cae"] : '';
$fecVtoCae = isset($factura["fec_vto_cae"]) ? $factura["fec_vto_cae"] : '';
$jsonQR = '{"ver":1,"fecha":"'.date('Y-m-d', strtotime($respuestaVenta["fecha"])).'","cuit":'.$respEmpresa["cuit"].',"ptoVta":'.$respuestaVenta["pto_vta"].',"tipoCmp":'.$respuestaVenta["cbte_tipo"].',"nroCmp":'.$nroCbte.',"importe":'.$respuestaVenta["total"].',"moneda":"PES","ctz":1,"tipoDocRec":'.$respuestaCliente["tipo_documento"].',"nroDocRec":'.$respuestaCliente["documento"].',"tipoCodAut":"E","codAut":'.$caeFactura.'}';
$jsonQRBase64 = 'https://www.afip.gob.ar/fe/qr/?p=' . base64_encode($jsonQR);
$tipoVta = isset($tiposCbtes[$respuestaVenta["cbte_tipo"]]) ? $tiposCbtes[$respuestaVenta["cbte_tipo"]] : 'No definido';
$tipoCodigo = "Cod. ". $respuestaVenta["cbte_tipo"];
$tipoVtaLetra = isset($tiposCbtesLetras[$respuestaVenta["cbte_tipo"]]) ? $tiposCbtesLetras[$respuestaVenta["cbte_tipo"]] : 'X';
$numCte = str_pad($nroCbte, 8, "0", STR_PAD_LEFT);
$cuit = isset($respEmpresa["cuit"]) ? $respEmpresa["cuit"] : '';
$tipoComprobante = str_pad($respuestaVenta["cbte_tipo"], 3, "0", STR_PAD_LEFT);
$cae = $caeFactura;
$vtoCae = $fecVtoCae;

}

$ptoVta = str_pad(isset($respuestaVenta["pto_vta"]) ? $respuestaVenta["pto_vta"] : 0, 5, "0", STR_PAD_LEFT);
$fecEmi = date('d/m/Y', strtotime(isset($respuestaVenta["fecha"]) ? $respuestaVenta["fecha"] : date('Y-m-d')));

// Asegurar que todas las variables usadas en HTML estén inicializadas
$razonSocial = isset($respEmpresa["razon_social"]) ? htmlspecialchars($respEmpresa["razon_social"], ENT_QUOTES, 'UTF-8') : '';
$domicilio = isset($respEmpresa["domicilio"]) ? htmlspecialchars($respEmpresa["domicilio"], ENT_QUOTES, 'UTF-8') : '';
$telefono = isset($respEmpresa["telefono"]) ? htmlspecialchars($respEmpresa["telefono"], ENT_QUOTES, 'UTF-8') : '';
$localidad = isset($respEmpresa["localidad"]) ? htmlspecialchars($respEmpresa["localidad"], ENT_QUOTES, 'UTF-8') : '';
$codigoPostal = isset($respEmpresa["codigo_postal"]) ? htmlspecialchars($respEmpresa["codigo_postal"], ENT_QUOTES, 'UTF-8') : '';
$cuit = isset($respEmpresa["cuit"]) ? htmlspecialchars($respEmpresa["cuit"], ENT_QUOTES, 'UTF-8') : '';
$numeroIibb = isset($respEmpresa["numero_iibb"]) ? htmlspecialchars($respEmpresa["numero_iibb"], ENT_QUOTES, 'UTF-8') : '';
$inicioActividades = isset($respEmpresa["inicio_actividades"]) ? htmlspecialchars($respEmpresa["inicio_actividades"], ENT_QUOTES, 'UTF-8') : '';
$nombreCliente = isset($respuestaCliente["nombre"]) ? htmlspecialchars($respuestaCliente["nombre"], ENT_QUOTES, 'UTF-8') : '';
$documentoCliente = isset($respuestaCliente["documento"]) ? htmlspecialchars($respuestaCliente["documento"], ENT_QUOTES, 'UTF-8') : '';
$direccionCliente = isset($respuestaCliente["direccion"]) ? htmlspecialchars($respuestaCliente["direccion"], ENT_QUOTES, 'UTF-8') : '';
$tieneServicio = isset($tieneServicio) ? $tieneServicio : '';

/*
if(isset($respEmpresa["logo"]) && $respEmpresa["logo"] != ""){
	$razonSocial = '<td style="width:250px; padding:45px;"><img src="../../../vistas/img/plantilla/logo_impreso.png"></td>';
} else {
	$razonSocial = $respEmpresa["razon_social"];
}*/

$ubicacionCabecera  = 5;
$ubicacionDetalle   = 0; // Se calculará dinámicamente
$ubicacionFooter    = 0; // Se calculará dinámicamente
$datosFact = []; //Array de datos a imprimir
$detalleEnTabla = ""; //filas en tabla para armar detalle
$subTotalPorPagina = 0;
$transportePorPagina = 0;
$transporteAcumulado = 0; // Suma acumulada de totales de páginas anteriores
$totalPorPagina = []; // Array para almacenar totales por página
$valorY = 0;
$nuevaPagina = true;
$imprimoCabeceraDetalle = true;
$numPaginaActual = 0;
$ultimoProducto = count($productos);

$tieneServicio = '';
if($respuestaVenta["concepto"] != 1){
$tieneServicio = <<<EOF
            <table border="1" style="padding-left: 5px">
		<tr>
			<td style="width:186px; font-size:8px; text-align: left;">
				<br>
				<span><b>Fecha Desde:</b> $respuestaVenta[fec_desde]</span> <br>
			</td>
			<td style="width:187px; font-size:8px; text-align: left;">
				<br>
				<span><b>Fecha Hasta:</b> $respuestaVenta[fec_hasta]</span> <br>
			</td>
			<td style="width:187px; font-size:8px; text-align: left;">
				<br>
				<span><b>Fecha Vto.:</b> $respuestaVenta[fec_vencimiento]</span> <br>
			</td>			
		</tr>
	</table>
EOF;
}

$condicionVenta = '';
if($respuestaVenta["estado"] == 2){
    $condicionVenta = 'Cuenta Corriente';
} else {
    //[{"tipo":"Efectivo","entrega":"1000"},{"tipo":"MP-","entrega":"300"}]
    if(is_array($jsnPago) && !empty($jsnPago)) {
        foreach ($jsnPago as $clave => $valor) {
            if(isset($valor["tipo"])) {
                $condicionVenta .= $valor["tipo"] . ' | ';
            }
        }
        if(!empty($condicionVenta)) {
            $condicionVenta = substr($condicionVenta, 0, -2);
        }
    }
    if(empty($condicionVenta)) {
        $condicionVenta = 'Efectivo';
    }
}


// Determinar estilo del tipo de venta
$tipoVtaStyle = '';
if($respuestaVenta["cbte_tipo"] == "0") {
    $tipoVtaStyle = 'style="font-size: 11px; font-weight: bold; color: #dc3545; text-align: center;"';
} else {
    $tipoVtaStyle = 'style="font-size: 14px; font-weight: bold; text-align: center;"';
}

$bloqueCabeceraOriginal = <<<EOF
	<table border="1" cellpadding="5" cellspacing="0" style="width:100%;">
		<tr>
			<td colspan="3" style="text-align: center; padding: 10px; font-size: 16px; font-weight: bold; background-color: #e9ecef;">
				ORIGINAL
			</td>
		</tr>
		<tr>
			<td style="width:45%; text-align: center; padding: 15px; vertical-align: middle;">
				<div style="font-size: 18px; font-weight: bold;">$razonSocial</div>
			</td>
			<td style="width:10%; text-align: center; padding: 12px; vertical-align: middle; background-color: #f8f9fa;">
				<div style="font-size: 42px; font-weight: bold; line-height: 1;">$tipoVtaLetra</div>
				<div style="font-size: 8px; margin-top: 3px; color: #6c757d;">$tipoCodigo</div>
			</td>
			<td style="width:45%; text-align: center; padding: 15px; vertical-align: middle;">
				<div $tipoVtaStyle>$tipoVta</div>
			</td>
		</tr>
		<tr>
			<td style="width:50%; font-size: 10px; padding: 12px; vertical-align: top; border-right: 1px solid #000;">
				<div style="line-height: 1.8;">
					<div style="margin-bottom: 4px;"><b>Dirección:</b> $domicilio</div>
					<div style="margin-bottom: 4px;"><b>Teléfono:</b> $telefono</div>
					<div style="margin-bottom: 4px;"><b>Localidad:</b> $localidad - C.P.: $codigoPostal</div>
					<div style="margin-bottom: 4px;"><b>Cond. I.V.A.:</b> $tipoIva</div>
					<div style="margin-top: 8px; padding-top: 6px; border-top: 1px solid #dee2e6; font-size: 9px;"><b>Defensa al Consumidor Mza. 08002226678</b></div>
				</div>
			</td>
			<td colspan="2" style="width:50%; font-size: 10px; padding: 12px; vertical-align: top;">
				<div style="line-height: 1.8;">
					<div style="margin-bottom: 4px;"><b>N° Cbte:</b> <span style="font-size: 11px; font-weight: bold;">$ptoVta - $numCte</span></div>
					<div style="margin-bottom: 4px;"><b>Fecha Emisión:</b> $fecEmi</div>
					<div style="margin-bottom: 4px;"><b>CUIT:</b> $cuit</div>
					<div style="margin-bottom: 4px;"><b>II.BB.:</b> $numeroIibb</div>
					<div style="margin-bottom: 4px;"><b>Inic. Actividad:</b> $inicioActividades</div>
				</div>
			</td>
		</tr>
	</table>
	
	$tieneServicio
    
	<table border="1" cellpadding="5" cellspacing="0" style="width:100%; margin-top: 5px;">
		<tr>
			<td style="font-size: 10px; padding: 12px; line-height: 1.8; background-color: #f8f9fa;">
				<div style="margin-bottom: 5px;"><b>Tipo Doc.:</b> $tipoDocumento: <b>$documentoCliente</b> | <b>Nombre / Razón Social:</b> $nombreCliente</div>
				<div style="margin-bottom: 5px;"><b>Domicilio:</b> $direccionCliente | <b>Condición I.V.A.:</b> $tipoIvaCliente</div>
				<div><b>Condición de Venta:</b> <span style="font-weight: bold;">$condicionVenta</span></div>
			</td>
		</tr>
	</table>
EOF;

// Crear bloque cabecera duplicado (igual que original pero con "DUPLICADO")
$bloqueCabeceraDuplicado = str_replace('ORIGINAL', 'DUPLICADO', $bloqueCabeceraOriginal);

// CONSTRUIR TABLA DE PRODUCTOS COMPLETA
$tablaProductos = '';
$filasProductos = '';

// Determinar columnas según tipo de comprobante
$esTipoA = ($respuestaVenta["cbte_tipo"] == 1 || $respuestaVenta["cbte_tipo"] == 2 || $respuestaVenta["cbte_tipo"] == 3 || $respuestaVenta["cbte_tipo"] == 4);

// Construir encabezado de tabla (alineado con cabecera - ancho total 100%)
if($esTipoA) {
    $tablaProductos = '<table border="1" cellpadding="4" cellspacing="0" style="width:100%; margin-top: 10px;">
        <tr style="background-color: #343a40; color: #ffffff;">
            <td style="width:8%; font-size: 10px; text-align: center; padding: 8px; font-weight: bold;">Cant.</td>
            <td style="width:50%; font-size: 10px; text-align: center; padding: 8px; font-weight: bold;">Detalle</td>
            <td style="width:14%; font-size: 10px; text-align: center; padding: 8px; font-weight: bold;">Subtotal</td>
            <td style="width:8%; font-size: 10px; text-align: center; padding: 8px; font-weight: bold;">IVA %</td>
            <td style="width:20%; font-size: 10px; text-align: center; padding: 8px; font-weight: bold;">Total</td>
        </tr>';
} else {
    $tablaProductos = '<table border="1" cellpadding="4" cellspacing="0" style="width:100%; margin-top: 10px;">
        <tr style="background-color: #343a40; color: #ffffff;">
            <td style="width:10%; font-size: 10px; text-align: center; padding: 8px; font-weight: bold;">Cant.</td>
            <td style="width:60%; font-size: 10px; text-align: center; padding: 8px; font-weight: bold;">Detalle</td>
            <td style="width:15%; font-size: 10px; text-align: center; padding: 8px; font-weight: bold;">Unit.</td>
            <td style="width:15%; font-size: 10px; text-align: center; padding: 8px; font-weight: bold;">Total</td>
        </tr>';
}

// Construir filas de productos
foreach ($productos as $key => $value) {
    if(!isset($value["id"]) || !isset($value["cantidad"]) || !isset($value["total"])) {
        continue; // Saltar productos con datos incompletos
    }
    
    $getProducto = ControladorProductos::ctrMostrarProductoXId($value["id"]);
    if(!$getProducto || !is_array($getProducto)) {
        continue; // Saltar si no se puede obtener el producto
    }
    
    $formatCantidad = number_format($value["cantidad"], 2, ',', '.');
    $formatTotal = '$ ' . number_format($value["total"], 2, ',', '.');
    $precioProducto = isset($value["precio"]) ? floatval($value["precio"]) : (isset($value["precio_venta"]) ? floatval($value["precio_venta"]) : 0);
    $descripcion = isset($value["descripcion"]) ? htmlspecialchars($value["descripcion"], ENT_QUOTES, 'UTF-8') : '';
    $tipoIvaProducto = isset($getProducto["tipo_iva"]) ? floatval($getProducto["tipo_iva"]) : 0;
    
    if($esTipoA) {
        $formatPrecioUnit = $precioProducto / (1 + ($tipoIvaProducto / 100));
        $formatSubtotal = $formatPrecioUnit * floatval($value["cantidad"]);
        $formatPrecioUnit = '$ ' . number_format($formatPrecioUnit, 2, ',', '.');
        $formatSubtotal = '$ ' . number_format($formatSubtotal, 2, ',', '.');
        
        $filasProductos .= '<tr>
            <td style="width:8%; font-size: 9px; text-align: center; padding: 6px; vertical-align: middle;">' . $formatCantidad . '</td>
            <td style="width:50%; font-size: 9px; text-align: left; padding: 6px; vertical-align: middle;">' . $descripcion . '</td>
            <td style="width:14%; font-size: 9px; text-align: right; padding: 6px; vertical-align: middle;">' . $formatSubtotal . '</td>
            <td style="width:8%; font-size: 9px; text-align: center; padding: 6px; vertical-align: middle;">' . number_format($tipoIvaProducto, 0, ',', '.') . '</td>
            <td style="width:20%; font-size: 9px; text-align: right; padding: 6px; vertical-align: middle; font-weight: bold;">' . $formatTotal . '</td>
        </tr>';
    } else {
        $formatPrecioUnit = '$ ' . number_format($precioProducto, 2, ',', '.');
        
        $filasProductos .= '<tr>
            <td style="width:10%; font-size: 9px; text-align: center; padding: 6px; vertical-align: middle;">' . $formatCantidad . '</td>
            <td style="width:60%; font-size: 9px; text-align: left; padding: 6px; vertical-align: middle;">' . $descripcion . '</td>
            <td style="width:15%; font-size: 9px; text-align: right; padding: 6px; vertical-align: middle;">' . $formatPrecioUnit . '</td>
            <td style="width:15%; font-size: 9px; text-align: right; padding: 6px; vertical-align: middle; font-weight: bold;">' . $formatTotal . '</td>
        </tr>';
    }
}

// Cerrar tabla de productos
$tablaProductos .= $filasProductos . '</table>';

// INCLUIR CABECERA
$pdf->SetY($ubicacionCabecera);
$pdf->writeHTML($bloqueCabeceraOriginal, false, false, false, false, '');

// Obtener posición Y después de la cabecera para calcular posición de tabla
$yDespuesCabecera = $pdf->GetY();
$espacioEntreCabeceraYTabla = 10; // Espacio entre cabecera y tabla
$ubicacionDetalle = $yDespuesCabecera + $espacioEntreCabeceraYTabla;

// Obtener número de página actual
$numPaginaActual = $pdf->getPage();

// Mostrar transporte acumulado solo desde página 2 en adelante
if($numPaginaActual > 1) {
    // Calcular transporte acumulado (suma de totales de páginas anteriores)
    $transporteAcumulado = 0;
    foreach($totalPorPagina as $pagNum => $totalPag) {
        if($pagNum < $numPaginaActual) {
            $transporteAcumulado += $totalPag;
        }
    }
    
    if($transporteAcumulado > 0) {
        $transporteFormateado = '$ ' . number_format($transporteAcumulado, 2, ',', '.');
        $bloqueTransporte = '<div style="font-size: 12px; font-weight: bold; text-align: right; margin-bottom: 10px; color: #000;">
            Transporte: ' . $transporteFormateado . '
        </div>';
        $pdf->SetY($ubicacionDetalle);
        $pdf->writeHTML($bloqueTransporte, false, false, false, false, '');
        $ubicacionDetalle = $pdf->GetY() + 5; // Ajustar posición después del transporte
    }
}

// INCLUIR TABLA DE PRODUCTOS (separada claramente)
$pdf->SetY($ubicacionDetalle);
$pdf->writeHTML($tablaProductos, false, false, false, false, '');

// Obtener posición Y después de la tabla de productos
$currentY = $pdf->GetY();

// Calcular posición del footer: después de la tabla + espacio adicional
$espacioEntreTablaYFooter = 20; // Espacio adicional entre tabla y footer
$ubicacionFooterCalculada = $currentY + $espacioEntreTablaYFooter;

// Verificar que no se haya excedido el límite de la página antes de agregar footer
// Mover footer al fondo de la página (cerca del final)
$alturaMaximaPagina = 287; // Altura máxima de página A4 en mm
$alturaFooter = 30; // Altura estimada del footer
$ubicacionFooterFinal = $alturaMaximaPagina - $alturaFooter - 10; // 10mm de margen inferior

if($ubicacionFooterCalculada > $ubicacionFooterFinal) {
    $pdf->AddPage('P', 'A4');
    $ubicacionFooter = $alturaMaximaPagina - $alturaFooter - 10;
} else {
    // Usar posición calculada pero asegurar que esté cerca del fondo
    $ubicacionFooter = max($ubicacionFooterCalculada, $ubicacionFooterFinal);
}

// INCLUIR FOOTER
$pdf->SetY($ubicacionFooter);

$ivas = json_decode($respuestaVenta["impuesto_detalle"], true);
$ivasDiscriminadosNombre = "";
$ivasDiscriminadosValor = "";
$ivasAcumuladosB = 0;
if(is_array($ivas) && !empty($ivas)) {
    foreach ($ivas as $key => $value) {
        if(isset($value["descripcion"]) && isset($value["iva"])) {
            $ivasDiscriminadosNombre .= $value["descripcion"] . ': $<br>';
            $ivasDiscriminadosValor .= '<b>' . number_format($value["iva"],2, ',', '.') . '</b><br>';
        }
    }
}

//---------------------Datos Factura neto, totales, iva, descuento (ORIGINAL)
if ($respuestaVenta["cbte_tipo"] == 1 || $respuestaVenta["cbte_tipo"] == 2 || $respuestaVenta["cbte_tipo"] == 3 || $respuestaVenta["cbte_tipo"] == 4) {
if(isset($jsonQRBase64) && !empty($jsonQRBase64)) {
    $style = array('border' => false, 'fgcolor' => array(0,0,0), 'bgcolor' => false);
    $pdf->write2DBarcode($jsonQRBase64, 'QRCODE,L', '', '', 25, 25, $style, 'N');
}
$pdf->SetY($ubicacionFooter);
$numPaginaFooter = $pdf->getPage(); // Calcular número de página antes del heredoc
$bloqueDatosFact = <<<EOF
	<table border="1" cellpadding="5" cellspacing="0" style="width:100%;">
		<tr>
			<td style="width:15%; vertical-align: top;">
				 <!--ACA VA CODIGO QR -->
			</td>
			<td style="width:40%; font-size:9px; text-align: left; padding: 8px; vertical-align: top;">
				<div style="color: #242C4F; font-size:11px; font-weight: bold;">
					ARCA - Comprobante autorizado
				</div>
				<div style="font-size:8px; margin-top: 3px;">
					<b>CAE:</b> $cae - <b>Vto. CAE:</b> $vtoCae
				</div>
				<div style="font-size: 7px; font-style:italic; margin-top: 5px;">
					Esta Administración Federal no se responsabiliza por los datos ingresados en el detalle de la operación
				</div>
				<div style="font-size: 9px; font-style:italic; text-align:right; margin-top: 5px;">
					PAGINA $numPaginaFooter
				</div>
			</td>
			<td style="width:22%; font-size:9px; text-align: right; padding: 8px; background-color: #f4f4f4; vertical-align: top;">
				SUBTOTAL: $<br>
				DESCUENTO: $<br>
				NETO GRAVADO: $<br>
				$ivasDiscriminadosNombre
                TOTAL: $<br>
			</td>
			<td style="width:23%; font-size:9px; text-align: right; padding: 8px; background-color: #f4f4f4; vertical-align: top; font-weight: bold;">
				' . $subTotal . '<br>
				' . $descuentos . '<br>
				' . $neto_grav . '<br>
				' . $ivasDiscriminadosValor . '
                ' . $total . '<br>
			</td>
		</tr>
	</table>
EOF;

} else {

$cbteBoCAutorizado = "";
$leyendaArcaB = "";
if ($facturada && isset($jsonQRBase64) && !empty($jsonQRBase64)) {
    $style = array('border' => false, 'fgcolor' => array(0,0,0), 'bgcolor' => false);
    $pdf->write2DBarcode($jsonQRBase64, 'QRCODE,L', '', '', 25, 25, $style, 'N');
    $pdf->SetY($ubicacionFooter);
    $cbteBoCAutorizado = '<div style="color: #242C4F; font-size:11px; font-weight: bold;">
						ARCA - Comprobante Autorizado
					 </div>
					<div style="font-size:8px; margin-top: 3px;">
						<b>CAE:</b> ' . $cae . ' - <b>Vto. CAE:</b> ' . $vtoCae . '
					</div>
					<div style="font-size: 7px; font-style:italic; margin-top: 5px;">
						Esta Administración Federal no se responsabiliza por los datos ingresados en el detalle de la operación
					</div>';
    $leyendaArcaB = ($respuestaVenta["cbte_tipo"] == 6 || $respuestaVenta["cbte_tipo"] == 7 || $respuestaVenta["cbte_tipo"] == 8 || $respuestaVenta["cbte_tipo"] == 9) ? "IVA contenido (Ley 27.743) $<br><br>" : "";
    $ivasAcumuladosB = ($respuestaVenta["cbte_tipo"] == 6 || $respuestaVenta["cbte_tipo"] == 7 || $respuestaVenta["cbte_tipo"] == 8 || $respuestaVenta["cbte_tipo"] == 9) ? "<b>" . $respuestaVenta["impuesto"] . "</b><br><br>" : "";
}
$numPaginaFooterB = $pdf->getPage(); // Calcular número de página antes del heredoc
$bloqueDatosFact = <<<EOF
	<table border="1" cellpadding="5" cellspacing="0" style="width:100%;">
		<tr>
			<td style="width:15%; vertical-align: top;">
				 <!--ACA VA CODIGO QR -->
			</td>
			<td style="width:40%; font-size:9px; text-align: left; padding: 8px; vertical-align: top;">
				$cbteBoCAutorizado
				<div style="font-size: 9px; font-style:italic; text-align:right; margin-top: 5px;">
					PAGINA $numPaginaFooterB
				</div>
			</td>
			<td style="width:22%; font-size:9px; text-align: right; padding: 8px; background-color: #f4f4f4; vertical-align: top;">
				$leyendaArcaB
				SUBTOTAL: $<br>
				DESCUENTO: $<br>
                TOTAL: $<br>
			</td>
			<td style="width:23%; font-size:9px; text-align: right; padding: 8px; background-color: #f4f4f4; vertical-align: top; font-weight: bold;">
				' . $ivasAcumuladosB . '
				' . $subTotal . '<br>
				' . $descuentos . '<br>
                ' . $total . '<br>
			</td>
		</tr>
	</table>
EOF;

}
$pdf->writeHTML($bloqueDatosFact, false, false, false, false, '');


/*=============================================================================
-----------------------------------DUPLICADO----------------------------------
==============================================================================*/
// Agregar nueva página para duplicado
$pdf->AddPage('P', 'A4');
$ubicacionCabecera = 5;
$ubicacionDetalle = 0; // Se calculará dinámicamente
$ubicacionFooter = 0; // Se calculará dinámicamente

// INCLUIR CABECERA DUPLICADO
$pdf->SetY($ubicacionCabecera);
$pdf->writeHTML($bloqueCabeceraDuplicado, false, false, false, false, '');

// Obtener posición Y después de la cabecera para calcular posición de tabla
$yDespuesCabecera = $pdf->GetY();
$espacioEntreCabeceraYTabla = 10; // Espacio entre cabecera y tabla
$ubicacionDetalle = $yDespuesCabecera + $espacioEntreCabeceraYTabla;

// Obtener número de página actual
$numPaginaActual = $pdf->getPage();

// Mostrar transporte acumulado solo desde página 2 en adelante
if($numPaginaActual > 1) {
    // Calcular transporte acumulado (suma de totales de páginas anteriores)
    $transporteAcumulado = 0;
    foreach($totalPorPagina as $pagNum => $totalPag) {
        if($pagNum < $numPaginaActual) {
            $transporteAcumulado += $totalPag;
        }
    }
    
    if($transporteAcumulado > 0) {
        $transporteFormateado = '$ ' . number_format($transporteAcumulado, 2, ',', '.');
        $bloqueTransporte = '<div style="font-size: 12px; font-weight: bold; text-align: right; margin-bottom: 10px; color: #000;">
            Transporte: ' . $transporteFormateado . '
        </div>';
        $pdf->SetY($ubicacionDetalle);
        $pdf->writeHTML($bloqueTransporte, false, false, false, false, '');
        $ubicacionDetalle = $pdf->GetY() + 5; // Ajustar posición después del transporte
    }
}

// INCLUIR TABLA DE PRODUCTOS (reutilizar la misma tabla construida)
$pdf->SetY($ubicacionDetalle);
$pdf->writeHTML($tablaProductos, false, false, false, false, '');

// Obtener posición Y después de la tabla de productos
$currentY = $pdf->GetY();

// Calcular posición del footer: después de la tabla + espacio adicional
$espacioEntreTablaYFooter = 20; // Espacio adicional entre tabla y footer
$ubicacionFooterCalculada = $currentY + $espacioEntreTablaYFooter;

// Verificar que no se haya excedido el límite de la página antes de agregar footer
// Mover footer al fondo de la página (cerca del final)
$alturaMaximaPagina = 287; // Altura máxima de página A4 en mm
$alturaFooter = 30; // Altura estimada del footer
$ubicacionFooterFinal = $alturaMaximaPagina - $alturaFooter - 10; // 10mm de margen inferior

// Guardar total de esta página (número sin formato) antes de verificar si necesita nueva página
$totalPorPagina[$pdf->getPage()] = $totalNumero;

if($ubicacionFooterCalculada > $ubicacionFooterFinal) {
    $pdf->AddPage('P', 'A4');
    $ubicacionFooter = $alturaMaximaPagina - $alturaFooter - 10;
} else {
    // Usar posición calculada pero asegurar que esté cerca del fondo
    $ubicacionFooter = max($ubicacionFooterCalculada, $ubicacionFooterFinal);
}

// INCLUIR FOOTER DUPLICADO (mismo formato que original)
$pdf->SetY($ubicacionFooter);

$ivas = json_decode($respuestaVenta["impuesto_detalle"], true);
$ivasDiscriminadosNombre = "";
$ivasDiscriminadosValor = "";
$ivasAcumuladosB = 0;
if(is_array($ivas)) {
    foreach ($ivas as $key => $value) {
        $ivasDiscriminadosNombre .= $value["descripcion"] . ': $<br>';
        $ivasDiscriminadosValor .= '<b>' . number_format($value["iva"],2, ',', '.') . '</b><br>';
    }
}

//---------------------Datos Factura neto, totales, iva, descuento (DUPLICADO)
if ($respuestaVenta["cbte_tipo"] == 1 || $respuestaVenta["cbte_tipo"] == 2 || $respuestaVenta["cbte_tipo"] == 3 || $respuestaVenta["cbte_tipo"] == 4) {
if(isset($jsonQRBase64) && !empty($jsonQRBase64)) {
    $style = array('border' => false, 'fgcolor' => array(0,0,0), 'bgcolor' => false);
    $pdf->write2DBarcode($jsonQRBase64, 'QRCODE,L', '', '', 25, 25, $style, 'N');
}
$pdf->SetY($ubicacionFooter);
$numPaginaFooterDuplicado = $pdf->getPage(); // Calcular número de página antes del heredoc
$bloqueDatosFact = <<<EOF
	<table border="1" cellpadding="5" cellspacing="0" style="width:100%;">
		<tr>
			<td style="width:15%; vertical-align: top;">
				 <!--ACA VA CODIGO QR -->
			</td>
			<td style="width:40%; font-size:9px; text-align: left; padding: 8px; vertical-align: top;">
				<div style="color: #242C4F; font-size:11px; font-weight: bold;">
					ARCA - Comprobante autorizado
				</div>
				<div style="font-size:8px; margin-top: 3px;">
					<b>CAE:</b> $cae - <b>Vto. CAE:</b> $vtoCae
				</div>
				<div style="font-size: 7px; font-style:italic; margin-top: 5px;">
					Esta Administración Federal no se responsabiliza por los datos ingresados en el detalle de la operación
				</div>
				<div style="font-size: 9px; font-style:italic; text-align:right; margin-top: 5px;">
					PAGINA $numPaginaFooterDuplicado
				</div>
			</td>
			<td style="width:22%; font-size:9px; text-align: right; padding: 8px; background-color: #f4f4f4; vertical-align: top;">
				SUBTOTAL: $<br>
				DESCUENTO: $<br>
				NETO GRAVADO: $<br>
				$ivasDiscriminadosNombre
                TOTAL: $<br>
			</td>
			<td style="width:23%; font-size:9px; text-align: right; padding: 8px; background-color: #f4f4f4; vertical-align: top; font-weight: bold;">
				' . $subTotal . '<br>
				' . $descuentos . '<br>
				' . $neto_grav . '<br>
				' . $ivasDiscriminadosValor . '
                ' . $total . '<br>
			</td>
		</tr>
	</table>
EOF;

} else {

$cbteBoCAutorizado = "";
$leyendaArcaB = "";
if ($facturada && isset($jsonQRBase64) && !empty($jsonQRBase64)) {
    $style = array('border' => false, 'fgcolor' => array(0,0,0), 'bgcolor' => false);
    $pdf->write2DBarcode($jsonQRBase64, 'QRCODE,L', '', '', 25, 25, $style, 'N');
    $pdf->SetY($ubicacionFooter);
    $cbteBoCAutorizado = '<div style="color: #242C4F; font-size:11px; font-weight: bold;">
						ARCA - Comprobante Autorizado
					 </div>
					<div style="font-size:8px; margin-top: 3px;">
						<b>CAE:</b> ' . $cae . ' - <b>Vto. CAE:</b> ' . $vtoCae . '
					</div>
					<div style="font-size: 7px; font-style:italic; margin-top: 5px;">
						Esta Administración Federal no se responsabiliza por los datos ingresados en el detalle de la operación
					</div>';
    $leyendaArcaB = ($respuestaVenta["cbte_tipo"] == 6 || $respuestaVenta["cbte_tipo"] == 7 || $respuestaVenta["cbte_tipo"] == 8 || $respuestaVenta["cbte_tipo"] == 9) ? "IVA contenido (Ley 27.743) $<br><br>" : "";
    $ivasAcumuladosB = ($respuestaVenta["cbte_tipo"] == 6 || $respuestaVenta["cbte_tipo"] == 7 || $respuestaVenta["cbte_tipo"] == 8 || $respuestaVenta["cbte_tipo"] == 9) ? "<b>" . $respuestaVenta["impuesto"] . "</b><br><br>" : "";
}
$numPaginaFooterB = $pdf->getPage(); // Calcular número de página antes del heredoc
$bloqueDatosFact = <<<EOF
	<table border="1" cellpadding="5" cellspacing="0" style="width:100%;">
		<tr>
			<td style="width:15%; vertical-align: top;">
				 <!--ACA VA CODIGO QR -->
			</td>
			<td style="width:40%; font-size:9px; text-align: left; padding: 8px; vertical-align: top;">
				$cbteBoCAutorizado
				<div style="font-size: 9px; font-style:italic; text-align:right; margin-top: 5px;">
					PAGINA $numPaginaFooterB
				</div>
			</td>
			<td style="width:22%; font-size:9px; text-align: right; padding: 8px; background-color: #f4f4f4; vertical-align: top;">
				$leyendaArcaB
				SUBTOTAL: $<br>
				DESCUENTO: $<br>
                TOTAL: $<br>
			</td>
			<td style="width:23%; font-size:9px; text-align: right; padding: 8px; background-color: #f4f4f4; vertical-align: top; font-weight: bold;">
				' . $ivasAcumuladosB . '
				' . $subTotal . '<br>
				' . $descuentos . '<br>
                ' . $total . '<br>
			</td>
		</tr>
	</table>
EOF;

}
$pdf->writeHTML($bloqueDatosFact, false, false, false, false, '');

//SALIDA DEL ARCHIVO
$nomArchivo = 'CBTE_'.$tipoVtaLetra.'_'.$ptoVta.'-'.$numCte.'.pdf';
if(isset($_GET["descargarFactura"])){
$pdf->Output($nomArchivo, 'D');
} else {
$pdf->Output($nomArchivo);
}

}

}

error_log("Creando instancia de imprimirComprobante...");
try {
    $comprobante = new imprimirComprobante();
    error_log("Instancia creada, llamando traerImpresionComprobante()...");
    $comprobante -> traerImpresionComprobante();
    error_log("✅ PDF generado exitosamente");
} catch (Exception $e) {
    error_log("❌ ERROR FATAL en comprobante.php: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    http_response_code(500);
    die('Error al generar el comprobante: ' . $e->getMessage());
} catch (Error $e) {
    error_log("❌ ERROR FATAL (Error) en comprobante.php: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    http_response_code(500);
    die('Error fatal al generar el comprobante: ' . $e->getMessage());
}

?>