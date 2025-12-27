<?php
// Habilitar reporte de errores para debugging
error_reporting(E_ALL);
ini_set('display_errors', 1); // Mostrar errores temporalmente para debugging
ini_set('log_errors', 1);

// Crear log específico para este archivo
$logFile = dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/error_log_presupuesto.txt';
ini_set('error_log', $logFile);

error_log("==========================================");
error_log("INICIO presupuesto.php - " . date('Y-m-d H:i:s'));
error_log("ID Presupuesto recibido: " . (isset($_GET['idPresupuesto']) ? $_GET['idPresupuesto'] : 'NO DEFINIDO'));
error_log("==========================================");

// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Validar que se haya proporcionado el idPresupuesto
if(!isset($_GET['idPresupuesto']) || empty($_GET['idPresupuesto'])) {
    error_log("Error presupuesto.php: No se proporcionó el idPresupuesto");
    http_response_code(400);
    die('Error: No se proporcionó el ID del presupuesto');
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
$rutaBase = dirname(dirname(dirname(dirname(dirname(dirname(__FILE__))))));
error_log("Ruta base calculada: " . $rutaBase);
error_log("Ruta base existe: " . (is_dir($rutaBase) ? 'SÍ' : 'NO'));

// Cargar variables de entorno desde .env PRIMERO
$envPath = $rutaBase . '/.env';
if (file_exists($envPath)) {
    if (class_exists('Dotenv\Dotenv')) {
        try {
            $dotenv = Dotenv\Dotenv::createImmutable($rutaBase);
            $dotenv->load();
            error_log("✅ .env cargado correctamente desde: " . $envPath);
        } catch (Exception $e) {
            error_log("❌ Error al cargar .env en presupuesto.php: " . $e->getMessage());
        }
    } else {
        error_log("⚠️ Dotenv no está disponible, intentando leer .env manualmente");
        $envLines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($envLines as $line) {
            if (strpos(trim($line), '#') === 0) continue;
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

// Cargar helpers
$helpersPath = $rutaBase . '/helpers.php';
if (file_exists($helpersPath)) {
    require_once $helpersPath;
} else {
    error_log("⚠️ helpers.php no encontrado en: " . $helpersPath);
}

// Usar rutas relativas
$archivos = [
    "../../../../../controladores/empresa.controlador.php",
    "../../../../../modelos/empresa.modelo.php",
    "../../../../../controladores/presupuestos.controlador.php",
    "../../../../../modelos/presupuestos.modelo.php",
    "../../../../../controladores/clientes.controlador.php",
    "../../../../../modelos/clientes.modelo.php",
    "../../../../../controladores/usuarios.controlador.php",
    "../../../../../modelos/usuarios.modelo.php",
    "../../../../../controladores/productos.controlador.php",
    "../../../../../modelos/productos.modelo.php"
];

foreach ($archivos as $archivo) {
    $rutaCompleta = __DIR__ . '/' . $archivo;
    if (file_exists($rutaCompleta)) {
        require_once $archivo;
        error_log("✅ Cargado: " . basename($archivo));
    } else {
        error_log("❌ No encontrado: " . $rutaCompleta);
        http_response_code(500);
        die('Error: No se encuentra un archivo necesario: ' . basename($archivo));
    }
}

class imprimirComprobante{

public $codigo;

public function traerImpresionComprobante(){

try {
    error_log("Obteniendo datos de empresa...");
    $respEmpresa = ModeloEmpresa::mdlMostrarEmpresa('empresa', 'id', 1);
    
    if(!$respEmpresa || !is_array($respEmpresa)) {
        error_log("ERROR: No se pudo obtener datos de la empresa");
        http_response_code(500);
        die('Error: No se pudieron obtener los datos de la empresa');
    }
    error_log("✅ Datos de empresa obtenidos");

$tiposCbtes = array(
0 => 'X',
1 => 'Factura A',
6 => 'Factura B', 
11 => 'Factura C',
//'Factura E' => 0, 
51 => 'Factura M',
2 => 'Nota Débito A',
7 => 'Nota Débito B',
12 => 'Nota Débito C',
//'Nota Débito E' => 0, 
52 => 'Nota Débito M',
3 => 'Nota Crédito A',
8 => 'Nota Crédito B',
13 => 'Nota Crédito C',
//'Nota Crédito E' => 0,
53 => 'Nota Crédito M',
4 => 'Recibo A',
9 => 'Recibo B',
15 => 'Recibo C',
//'Recibo E' => 0, 
54 => 'Recibo M',
'' => 'no definido'
);

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
'' => 'X'
);


    //TRAEMOS LA INFORMACIÓN DEL PRESUPUESTO
    error_log("Obteniendo datos del presupuesto ID: " . $_GET['idPresupuesto']);
    $itemPedido = "id";
    $respuestaVenta = ControladorPresupuestos::ctrMostrarPresupuestos($itemPedido, $_GET['idPresupuesto']);
    
    if(!$respuestaVenta || !is_array($respuestaVenta) || empty($respuestaVenta)) {
        error_log("ERROR: No se pudo obtener el presupuesto con ID: " . $_GET['idPresupuesto']);
        http_response_code(404);
        die('Error: Presupuesto no encontrado');
    }
    error_log("✅ Presupuesto obtenido correctamente");
    
    // Validar que tenga los campos necesarios
    if(!isset($respuestaVenta["id_cliente"]) || empty($respuestaVenta["id_cliente"])) {
        error_log("ERROR: El presupuesto no tiene id_cliente");
        http_response_code(500);
        die('Error: El presupuesto no tiene cliente asociado');
    }
    
    $facturada = false;
    
    error_log("Obteniendo datos del cliente ID: " . $respuestaVenta["id_cliente"]);
    $respuestaCliente = ControladorClientes::ctrMostrarClientes('id', $respuestaVenta["id_cliente"]);
    
    if(!$respuestaCliente || !is_array($respuestaCliente) || empty($respuestaCliente)) {
        error_log("ERROR: No se pudo obtener el cliente con ID: " . $respuestaVenta["id_cliente"]);
        http_response_code(500);
        die('Error: Cliente no encontrado');
    }
    error_log("✅ Cliente obtenido correctamente");

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


$tipoDocumento = $arrTipoDocumento[0];

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

$tipoIva = $condIva[$respEmpresa["condicion_iva"]];
$tipoIvaCliente = $condIva[$respuestaCliente["condicion_iva"]];

    // Validar y procesar datos del presupuesto
    if(!isset($respuestaVenta["fecha"]) || empty($respuestaVenta["fecha"])) {
        error_log("ERROR: El presupuesto no tiene fecha");
        http_response_code(500);
        die('Error: El presupuesto no tiene fecha');
    }
    
    $fecha = substr($respuestaVenta["fecha"],0,-8);
    $fecha = date("d-m-Y",strtotime($fecha));
    
    // Los presupuestos almacenan productos en JSON, no en tabla relacional
    $productos = array();
    if(isset($respuestaVenta["productos"]) && !empty($respuestaVenta["productos"])) {
        $productosJson = $respuestaVenta["productos"];
        if ($productosJson !== '[]' && $productosJson !== 'null' && $productosJson !== '' && json_decode($productosJson) !== null) {
            $productos = json_decode($productosJson, true);
            if (!is_array($productos)) {
                $productos = array();
            }
        }
    }
    
    if(empty($productos)) {
        error_log("ERROR: El presupuesto no tiene productos válidos");
        error_log("Productos JSON: " . ($respuestaVenta["productos"] ?? "NO DEFINIDO"));
        http_response_code(500);
        die('Error: El presupuesto no tiene productos válidos');
    }
    error_log("✅ Productos obtenidos desde JSON: " . count($productos) . " items");
    
    $total = isset($respuestaVenta["total"]) ? number_format($respuestaVenta["total"],2, ',', '.') : '0,00';
    $observaciones = isset($respuestaVenta["observaciones"]) ? $respuestaVenta["observaciones"] : '';
    
    $subTotal = isset($respuestaVenta["neto"]) ? number_format($respuestaVenta["neto"],2, ',', '.') : '0,00';
    $neto_grav = isset($respuestaVenta["neto_gravado"]) ? number_format($respuestaVenta["neto_gravado"],2, ',', '.') : '0,00';
    $jsnPago = isset($respuestaVenta["metodo_pago"]) ? json_decode($respuestaVenta["metodo_pago"], true) : array();

//$descuentos = $jsnPago[0]["descuento"] * $respuestaVenta["neto"] / 100;
// $descuentos = $respuestaVenta["descuento"] * $respuestaVenta["neto"] / 100;
$descuentos = number_format(0, 2, ',','.');

// $totalVenta = number_format($respuestaVenta["total"], 2, ',','.');
// $netoVenta = number_format($respuestaVenta["neto"], 2, ',','.');

$barra = "";

$tipoVtaLetra = "X";
$tipoCodigo = "";
$tipoVta = "<h3>Documento no valido como factura</h3>";
$datosPrecios = '<td style="background-color:white; width:80px; text-align:center">Precio Unit.</td>
		<td style="background-color:white; width:80px; text-align:center">Total</td>';
$numCte = str_pad($respuestaVenta["id"], 8, "0", STR_PAD_LEFT);

$vtoCae ="-";
$cae ="-";

$ptoVta = '0';

$fecEmi = date('d/m/Y', strtotime($respuestaVenta["fecha"]));

//REQUERIMOS LA CLASE TCPDF
require_once('tcpdf_include.php');

$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

$pdf->AddPage('P', 'A4');

//$pdf->SetLineStyle(array('width' => 0.5, 'cap' => 'butt', 'join' => 'miter', 'dash' => 4, 'color' => array(255, 0, 0)));

//--------------------ORIGINAL
$bloque0 = <<<EOF
	<table border="1">
		<tr>
			<td style="width:560px; text-align: center;"> ORIGINAL</td>
		</tr>
	</table>
EOF;

$pdf->writeHTML($bloque0, false, false, false, false, '');

//--------------------datos cabecera
$bloqueCab = <<<EOF
	<table border="1" >
		
		<tr style="padding: 0px;">
			
			<td style="width:260px; text-align: center; border-style:solid; border-width:2px; border-bottom-color:rgb(255,255,255);"> 
				<h2>$respEmpresa[razon_social]</h2>
			</td>

			<td style="width:40px; text-align:center">

				<div>
					<span style="font-size:28.5px;">$tipoVtaLetra</span>
				
					<span style="font-size:10px;">$tipoCodigo</span>
				</div>
				
			</td>

			<td style="width:260px; text-align: center; border-style:solid; border-width:2px; border-bottom-color:rgb(255,255,255);"> 
				$tipoVta
			</td>
		</tr>
	</table>
EOF;

$pdf->writeHTML($bloqueCab, false, false, false, false, '');


//--------------------Datos comprobante
$bloqueDatosCbte = <<<EOF

	<table border="1" style="padding: 10px">
		
		<tr>
			
			<td style="width:280px; font-size:10px; text-align: left;">
				<br>
				<span>Direccion: $respEmpresa[domicilio]</span> <br>
				<span>Telefono: $respEmpresa[telefono]</span> <br>
				<span>Localidad: $respEmpresa[localidad] - C.P.: $respEmpresa[codigo_postal]</span><br>
				<span>Cond. I.V.A.: $tipoIva </span>

			</td>

			<td style="width:280px; font-size:10px; text-align: left">
				<div style="padding-top:5px">
					<span><b>N° Cbte:</b> $ptoVta - $numCte</span> <br>
					<span><b>Fecha Emisión:</b> $fecEmi </span><br>
					<span><b>CUIT:</b> $respEmpresa[cuit] </span><br>
					<span><b>II.BB.:</b> $respEmpresa[numero_iibb] </span><br>
				</div>

			</td>
		</tr>

	</table>

EOF;

$pdf->writeHTML($bloqueDatosCbte, false, false, false, false, '');

//--------------------Fechas servicio

if (isset($respuestaVenta["fec_desde"]) && $respuestaVenta["fec_desde"] != "") {

$bloqueFecServicio = <<<EOF

	<table border="1" >
		
		<tr>
			
			<td style="width:186px; font-size:10px; text-align: left;">
				<br>
				<span><b>Fecha Desde:</b> $respuestaVenta[fec_desde]</span> <br>
			</td>

			<td style="width:187px; font-size:10px; text-align: left;">
				<br>
				<span><b>Fecha Hasta:</b> $respuestaVenta[fec_hasta]</span> <br>
			</td>

			<td style="width:187px; font-size:10px; text-align: left;">
				<br>
				<span><b>Fecha Vto.:</b> $respuestaVenta[fec_vencimiento]</span> <br>
			</td>			
		</tr>

	</table>

EOF;

$pdf->writeHTML($bloqueFecServicio, false, false, false, false, '');

}

$bloqueCliente = <<<EOF

	<table border="1" style="padding: 5px">
		
		<tr >
			
			<td style="width:560px; font-size:10px; text-align: left;">
				<br>
				<span><b>Tipo Doc.: $tipoDocumento :</b> $respuestaCliente[documento] </span> - <span> <b>Nombre / Razón Social :</b> $respuestaCliente[nombre] </span> 
				<br>
				<span><b>Domicilio: </b> $respuestaCliente[direccion] </span> - <span> <b>Condición I.V.A.:</b> $tipoIvaCliente </span> 
			</td>

		</tr>

	</table>

EOF;

$pdf->writeHTML($bloqueCliente, false, false, false, false, '');

//DISEÑO DETALLE DEPENDIENDO DEL TIPO DE COMPROBANTE (A DISCRIMINA IVA)

//---------------------Cabecera Detalle B o C
$bloqueDetalleCab = <<<EOF

	<table border="1" style="padding: 5px">
		
		<tr style="background-color: #f4f4f4">
			
			<td style="width:50px; font-size:8px; text-align: center;">
				<span><b>Cant.</b></span> 
			</td>			
			<td style="width:430px; font-size:8px; text-align: center;">
				<span><b>Detalle</b></span> 
			</td>
			<td style="width:80px; font-size:8px; text-align: center; background-color">
				<span><b>Total</b></span> 
			</td>

		</tr>

	</table>

EOF;

$pdf->writeHTML($bloqueDetalleCab, false, false, false, false, '');

//--------------------- Detalle B o C
$datosFact = [];
$tamanioProd = count($productos);
for ($i = 0; $i <= 19; $i++) {

	if($i < $tamanioProd){
		$prod = $productos[$i];
		$datosFact[$i]["cantidad"] = isset($prod["cantidad"]) ? $prod["cantidad"] : "0";
		$datosFact[$i]["descripcion"] = isset($prod["descripcion"]) ? htmlspecialchars($prod["descripcion"]) : "";
		// El total puede venir como "total" o calcularse desde precio y cantidad
		$total = 0;
		if (isset($prod["total"])) {
			$total = floatval($prod["total"]);
		} elseif (isset($prod["precio"]) && isset($prod["cantidad"])) {
			$total = floatval($prod["precio"]) * floatval($prod["cantidad"]);
		} elseif (isset($prod["precio_venta"]) && isset($prod["cantidad"])) {
			$total = floatval($prod["precio_venta"]) * floatval($prod["cantidad"]);
		}
		$datosFact[$i]["total"] = '$ ' . number_format($total, 2, ',', '.');
	} else {

		$datosFact[$i]["cantidad"] = "";
		$datosFact[$i]["descripcion"] = "";
		$datosFact[$i]["total"] = "";
	}
}

foreach ($datosFact as $key => $value) {

$bloqueDetalle = <<<EOF

	<table style=" padding: 5px; ">
		
		<tr>
			
			<td style="width:50px; font-size:8px; text-align: center;">
				<span>$value[cantidad]</span> 
			</td>			
			<td style="width:430px; font-size:8px; text-align: left;">
				<span>$value[descripcion]</span> 
			</td>
			<td style="width:80px; font-size:8px; text-align: left;">
				<span>$value[total]</span> 
			</td>

		</tr>

	</table>

EOF;

$pdf->writeHTML($bloqueDetalle, false, false, false, false, '');

}

//---------------------Datos Factura neto, totales, iva, descuento
$bloqueDatosFact = '
<table>
	<tr>
		<td width="360px" style="border-color: #000;">';

if ($facturada) {
$bloqueDatosFact .=	'<table width="100%">
				<tr>
					<td style="text-align: left;  border-color: #000; padding-bottom:0px ">
						<div>
							<img src="images/logo_afip.png" width="80"> Comprobante Autorizado
						</div>';
$style = array(
    'position' => '',
    'align' => 'C',
    'stretch' => false,
    'fitwidth' => true,
    'cellfitalign' => '',
    'border' => false,
    'fgcolor' => array(0,0,0),
    'bgcolor' => false, //array(255,255,255),
    'text' => true,
    'font' => 'helvetica',
    'fontsize' => 8,
    'stretchtext' => 4
);

$params = $pdf->serializeTCPDFtagParameters(array($barra, 'I25+', '', '', '', 13, 0.3, $style, 'N'));

$bloqueDatosFact .= '<tcpdf method="write1DBarcode" params="'.$params.'" />

					</td>
				</tr>
				<tr>
					<td style="font-size:8px; font-style: italic; text-align: left; ">
						<b>CAE: </b>'; 
						$bloqueDatosFact .= $cae;
						$bloqueDatosFact .= ' <b>Vto. CAE: </b>'; 
						$bloqueDatosFact .= $vtoCae;
$bloqueDatosFact .= '</td>
				</tr>
			</table>';
}

$bloqueDatosFact .=	'</td>
		<td width="200px">
			<table style="padding: 5px;" width="100%" >
				<tr>
					<td style="width:80px; font-size:8px; text-align: rigth; border-color: #000;  background-color: #f4f4f4">
						Subtotal: $
					</td>
					<td style="width:120px; font-size:8px; text-align: rigth; border-color: #000;  background-color: #f4f4f4">';
$bloqueDatosFact .= $subTotal;
$bloqueDatosFact .= '</td>
				</tr>
				<tr>
					<td style="width:80px; font-size:8px; text-align: rigth;  background-color: #f4f4f4">
						Descuento: $
					</td>
					<td style="width:120px; font-size:8px; text-align: rigth;  background-color: #f4f4f4">';
$bloqueDatosFact .= $descuentos;
$bloqueDatosFact .= '</td>
				</tr>

				<tr>
					<td style="width:80px; font-size:8px; text-align: rigth;  background-color: #f4f4f4">
					TOTAL: $
					</td>
					<td style="width:120px; font-size:10px; text-align: rigth;  background-color: #f4f4f4">';
$bloqueDatosFact .= $total;
$bloqueDatosFact .= '</td>
				</tr>

			</table>
		</td>
	</tr>
</table>
';

$pdf->writeHTML($bloqueDatosFact, false, false, false, false, '');

    //SALIDA DEL ARCHIVO
    error_log("Generando PDF del presupuesto...");
    //$pdf->Output('factura.pdf', 'D');
    $pdf->Output('Presupuesto.pdf');
    error_log("✅ PDF generado exitosamente");

} catch (Exception $e) {
    error_log("❌ EXCEPCIÓN en presupuesto.php: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    http_response_code(500);
    die('Error al generar el presupuesto: ' . $e->getMessage());
} catch (Error $e) {
    error_log("❌ ERROR FATAL en presupuesto.php: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    http_response_code(500);
    die('Error fatal al generar el presupuesto: ' . $e->getMessage());
}

}

try {
    error_log("Inicializando clase imprimirComprobante...");
    $comprobante = new imprimirComprobante();
    $comprobante -> codigo = $_GET["idPresupuesto"];
    $comprobante -> traerImpresionComprobante();
    error_log("✅ Proceso completado exitosamente");
} catch (Exception $e) {
    error_log("❌ EXCEPCIÓN al inicializar: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    http_response_code(500);
    die('Error al generar el presupuesto: ' . $e->getMessage());
} catch (Error $e) {
    error_log("❌ ERROR FATAL al inicializar: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    http_response_code(500);
    die('Error fatal al generar el presupuesto: ' . $e->getMessage());
}

?>