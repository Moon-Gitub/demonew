<?php

// Habilitar reporte de errores para debugging
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Validar que se haya proporcionado el idRegistro
if(!isset($_GET['idRegistro']) || empty($_GET['idRegistro'])) {
    error_log("Error recibo.php: No se proporcionó el idRegistro");
    http_response_code(400);
    die('Error: No se proporcionó el ID del registro');
}

// Cargar autoload primero (usar ruta relativa)
$autoloadPath = '../../../autoload.php';
if(!file_exists(__DIR__ . '/' . $autoloadPath)) {
    error_log("ERROR: autoload.php no encontrado en: " . __DIR__ . '/' . $autoloadPath);
    die('Error: No se encuentra autoload.php');
}
require_once $autoloadPath;

// Obtener la ruta base del proyecto (raíz donde está .env)
$rutaBase = dirname(dirname(dirname(dirname(dirname(dirname(__FILE__))))));

// Cargar variables de entorno desde .env PRIMERO (si existe y si Dotenv está instalado)
$envPath = $rutaBase . '/.env';
if (file_exists($envPath)) {
    if (class_exists('Dotenv\Dotenv')) {
        try {
            $dotenv = Dotenv\Dotenv::createImmutable($rutaBase);
            $dotenv->load();
        } catch (Exception $e) {
            error_log("Error al cargar .env en recibo.php: " . $e->getMessage());
        }
    } else {
        // Leer .env manualmente si Dotenv no está disponible
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
}

// Cargar helpers (incluye función env() para leer variables)
if (file_exists($rutaBase . '/helpers.php')) {
    require_once $rutaBase . '/helpers.php';
}

require_once "../../../../../controladores/clientes.controlador.php";
require_once "../../../../../modelos/clientes.modelo.php";
require_once "../../../../../controladores/clientes_cta_cte.controlador.php";
require_once "../../../../../modelos/clientes_cta_cte.modelo.php";
require_once "../../../../../controladores/usuarios.controlador.php";
require_once "../../../../../modelos/usuarios.modelo.php";
require_once "../../../../../controladores/empresa.controlador.php";
require_once "../../../../../modelos/empresa.modelo.php";

class imprimirFactura{

public $id_registro;

public function traerImpresionFactura(){

try {
    //TRAEMOS LA INFORMACION REGISTRO
    $item = "id";
    $valor = $this->id_registro;
    
    if(empty($valor)) {
        throw new Exception("ID de registro no válido");
    }
    
    $respuestaRegistro = ControladorClientesCtaCte::ctrMostrarCtaCteClienteId($item, $valor);
    
    // Validar que se obtuvo el registro
    if(!$respuestaRegistro || empty($respuestaRegistro)) {
        throw new Exception("No se encontró el registro con ID: " . $valor);
    }
    
    $fecha = isset($respuestaRegistro["fecha"]) ? date('d/m/Y', strtotime($respuestaRegistro["fecha"])) : date('d/m/Y');
    $descripcion = isset($respuestaRegistro["descripcion"]) ? $respuestaRegistro["descripcion"] : "";
    $total = isset($respuestaRegistro["importe"]) ? number_format($respuestaRegistro["importe"], 2, ',', '.') : "0,00";
    $metPago = (isset($respuestaRegistro["metodo_pago"]) && !empty($respuestaRegistro["metodo_pago"])) ? "Medio de pago: " . $respuestaRegistro["metodo_pago"] : "";
    
    //TRAEMOS LA INFORMACIÓN DEL CLIENTE
    if(!isset($respuestaRegistro["id_cliente"]) || empty($respuestaRegistro["id_cliente"])) {
        throw new Exception("El registro no tiene cliente asociado");
    }
    
    $itemCliente = "id";
    $valorCliente = $respuestaRegistro["id_cliente"];
    $respuestaCliente = ControladorClientes::ctrMostrarClientes($itemCliente, $valorCliente);
    
    // Validar que se obtuvo el cliente
    if(!$respuestaCliente || empty($respuestaCliente)) {
        throw new Exception("No se encontró el cliente con ID: " . $valorCliente);
    }
    
    //TRAEMOS LA INFO DE EMPRESA
    $respEmpresa = ControladorEmpresa::ctrMostrarempresa('id', 1);
    
    // Validar que se obtuvo la empresa
    if(!$respEmpresa || empty($respEmpresa)) {
        throw new Exception("No se pudo obtener la información de la empresa");
    }
    
    //REQUERIMOS LA CLASE TCPDF
    if(!class_exists('TCPDF')) {
        throw new Exception("La clase TCPDF no está disponible");
    }
    
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
} catch(Exception $e) {
    error_log("Error recibo.php: " . $e->getMessage());
    http_response_code(500);
    die('Error al generar el recibo: ' . $e->getMessage());
}
// Configuración del documento
$pdf->SetCreator('Posmoon');
$pdf->SetTitle($respEmpresa["razon_social"]);
$pdf->AddPage('P', 'A4');

$bloqueCabeceraOriginal = <<<EOF
	<table border="1">
		<tr>
			<td style="width:560px; text-align: center;"> ORIGINAL</td>
		</tr>
	</table>
EOF;

$bloqueCabeceraDuplicado = <<<EOF
	<table border="1">
		<tr>
			<td style="width:560px; text-align: center;"> DUPLICADO</td>
		</tr>
	</table>
EOF;

$bloqueCabecera = <<<EOF
	<table border="1" >
		<tr style="padding: 0px;">
			<td style="width:260px; text-align: center; border-style:solid; border-width:2px; border-bottom-color:rgb(255,255,255);"> 
				<h2>$respEmpresa[razon_social]</h2>
			</td>
			<td style="width:40px; text-align:center">
				<div>
					<span style="font-size:28.5px;">X</span>
				</div>
			</td>
			<td style="width:260px; text-align: center; border-style:solid; border-width:2px; border-bottom-color:rgb(255,255,255);"> 
				<h2>RECIBO</h2>
			</td>
		</tr>
	</table>
	<table border="1" style="padding: 5px">
		<tr>
			<td style="width:280px; font-size:10px; text-align: left;">
				<br>
				<span><b>Direccion:</b> $respEmpresa[domicilio]</span> <br>
				<span><b>Telefono:</b> $respEmpresa[telefono]</span> <br>
				<span><b>Localidad:</b> $respEmpresa[localidad] - C.P.: $respEmpresa[codigo_postal]</span><br>
				<span><b>Cond. I.V.A.:</b> I.V.A. Responsable Inscripto </span><br>
			</td>
			<td style="width:280px; font-size:10px; text-align: left">
				<div style="padding-top:5px">
					<span><b>N° Cbte:</b> $respuestaRegistro[numero_recibo] </span> <br>
					<span><b>Fecha Emisión:</b> $fecha </span><br>
					<span><b>CUIT:</b> $respEmpresa[cuit] </span><br>
					<span><b>II.BB.:</b> $respEmpresa[numero_iibb] </span> - <span><b>Inic. Actividad:</b> $respEmpresa[inicio_actividades] </span>
				</div>
			</td>
		</tr>
	</table>
EOF;

// ---------------------------------------------------------
$bloqueDetalle = <<<EOF
	<table style="font-size:15px; padding:5px 10px;">
		<tr>
			<td><p style="line-height: 1.5">RECIBIMOS de $respuestaCliente[nombre] ( Documento/CUIT/CUIL.: $respuestaCliente[documento] ) la suma de pesos: $ $total, en concepto de: $respuestaRegistro[descripcion].</p></td>
		</tr>
		<tr>
			<td><p>$metPago</p></td>
		</tr>
	</table>
EOF;

// ---------------------------------------------------------
$bloqueFondo = <<<EOF
	<table>
		<tr>
			<td style="width:540px"><img src="images/back.jpg"></td>
		</tr>
	</table>
	<table>
		<tr>
			<td style="width:540px"><img src="images/back.jpg"></td>
		</tr>
	</table>
	<table style="font-size:10px; padding:5px 10px; padding-bottom: 15px">
		<tr>
			<td style="text-align:center; width:390px; "></td>
			<td style="border-top: 1px solid #666; color:#333; background-color:white; width:150px; text-align:center">
				Firma y aclaración
			</td>
		</tr>
	</table>
EOF;

//-------------------ORIGINAL---------------------------------------
$pdf->writeHTML($bloqueCabeceraOriginal, false, false, false, false, '');
$pdf->writeHTML($bloqueCabecera, false, false, false, false, '');
$pdf->writeHTML($bloqueDetalle, false, false, false, false, '');
$pdf->writeHTML($bloqueFondo, false, false, false, false, '');

//-------------------DUPLICADO--------------------------------------
$pdf->writeHTML($bloqueCabeceraDuplicado, false, false, false, false, '');
$pdf->writeHTML($bloqueCabecera, false, false, false, false, '');
$pdf->writeHTML($bloqueDetalle, false, false, false, false, '');
$pdf->writeHTML($bloqueFondo, false, false, false, false, '');	

//SALIDA DEL ARCHIVO 
$pdf->Output('factura.pdf');

}

}

try {
    $factura = new imprimirFactura();
    $factura -> id_registro = intval($_GET["idRegistro"]);
    $factura -> traerImpresionFactura();
} catch (Exception $e) {
    error_log("ERROR FATAL en recibo.php: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    http_response_code(500);
    die('Error al generar el recibo: ' . $e->getMessage());
} catch (Error $e) {
    error_log("ERROR FATAL (Error) en recibo.php: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    http_response_code(500);
    die('Error fatal al generar el recibo: ' . $e->getMessage());
}

?>