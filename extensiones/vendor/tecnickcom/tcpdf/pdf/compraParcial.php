<?php
// Habilitar reporte de errores para debugging
error_reporting(E_ALL);
ini_set('display_errors', 1); // Mostrar errores temporalmente para debugging
ini_set('log_errors', 1);

// Crear log específico para este archivo
$logFile = dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/error_log_compraParcial.txt';
ini_set('error_log', $logFile);

error_log("==========================================");
error_log("INICIO compraParcial.php - " . date('Y-m-d H:i:s'));
error_log("Código recibido: " . (isset($_GET['codigo']) ? $_GET['codigo'] : 'NO DEFINIDO'));
error_log("==========================================");

// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Validar que se haya proporcionado el código
if(!isset($_GET['codigo']) || empty($_GET['codigo'])) {
    error_log("Error compraParcial.php: No se proporcionó el código");
    http_response_code(400);
    die('Error: No se proporcionó el código de la compra');
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
            error_log("❌ Error al cargar .env en compraParcial.php: " . $e->getMessage());
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
    "../../../../../controladores/compras.controlador.php",
    "../../../../../modelos/compras.modelo.php",
    "../../../../../controladores/productos.controlador.php",
    "../../../../../modelos/productos.modelo.php",
    "../../../../../controladores/proveedores.controlador.php",
    "../../../../../modelos/proveedores.modelo.php",
    "../../../../../controladores/empresa.controlador.php",
    "../../../../../modelos/empresa.modelo.php"
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

class imprimirFactura{

public $codigo;

public function traerImpresionFactura(){

try {
    //DATOS EMPRESA
    error_log("Obteniendo datos de empresa...");
    $respEmpresa = ControladorEmpresa::ctrMostrarEmpresa('id', 1);
    
    if(!$respEmpresa || !is_array($respEmpresa)) {
        error_log("ERROR: No se pudo obtener datos de la empresa");
        http_response_code(500);
        die('Error: No se pudieron obtener los datos de la empresa');
    }
    error_log("✅ Datos de empresa obtenidos");
    
    //TRAEMOS LA INFORMACIÓN DE LA COMPRA
    error_log("Obteniendo datos de la compra ID: " . $_GET['codigo']);
    $itemPedido = "id";
    $respuestaCompra = ControladorCompras::ctrMostrarCompras($itemPedido, $_GET['codigo']);
    
    if(!$respuestaCompra || !is_array($respuestaCompra) || empty($respuestaCompra)) {
        error_log("ERROR: No se pudo obtener la compra con ID: " . $_GET['codigo']);
        http_response_code(404);
        die('Error: Compra no encontrada');
    }
    error_log("✅ Compra obtenida correctamente");
    
    // Validar campos necesarios
    if(!isset($respuestaCompra["fecha"]) || empty($respuestaCompra["fecha"])) {
        error_log("ERROR: La compra no tiene fecha");
        http_response_code(500);
        die('Error: La compra no tiene fecha');
    }
    
    if(!isset($respuestaCompra["productos"]) || empty($respuestaCompra["productos"])) {
        error_log("ERROR: La compra no tiene productos");
        http_response_code(500);
        die('Error: La compra no tiene productos');
    }
    
    if(!isset($respuestaCompra["id_proveedor"]) || empty($respuestaCompra["id_proveedor"])) {
        error_log("ERROR: La compra no tiene proveedor");
        http_response_code(500);
        die('Error: La compra no tiene proveedor asociado');
    }
    
    $fecha = substr($respuestaCompra["fecha"],0,-8);
    $fecha=date("d-m-Y",strtotime($fecha));
    
    $productos = json_decode($respuestaCompra["productos"], true);
    if(!is_array($productos) || empty($productos)) {
        error_log("ERROR: Los productos de la compra no son válidos");
        http_response_code(500);
        die('Error: Los productos de la compra no son válidos');
    }
    error_log("✅ Productos obtenidos: " . count($productos) . " items");
    
    $destino = isset($respuestaCompra["destino"]) ? json_decode($respuestaCompra["destino"],true) : array();
    $total = isset($respuestaCompra["total"]) ? number_format(round($respuestaCompra["total"],2),2) : '0.00';
    
    error_log("Obteniendo datos del proveedor ID: " . $respuestaCompra["id_proveedor"]);
    $proveedorData = ControladorProveedores::ctrMostrarProveedores('id', $respuestaCompra["id_proveedor"]);
    
    if(!$proveedorData || !is_array($proveedorData) || empty($proveedorData)) {
        error_log("ERROR: No se pudo obtener el proveedor con ID: " . $respuestaCompra["id_proveedor"]);
        http_response_code(500);
        die('Error: Proveedor no encontrado');
    }
    
    if(!isset($proveedorData["nombre"]) || empty($proveedorData["nombre"])) {
        error_log("ERROR: El proveedor no tiene nombre");
        http_response_code(500);
        die('Error: El proveedor no tiene nombre');
    }
    
    $proveedor = $proveedorData;
    error_log("✅ Proveedor obtenido correctamente");
//REQUERIMOS LA CLASE TCPDF

require_once('tcpdf_include.php');

$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

$pdf->AddPage('P', 'A4');

//---------------------------------------------------------
$bloque1 = <<<EOF
	<table border="1">
		<tr>
			<td style="width:560px; text-align: center;"> ORDEN DE COMPRA</td>
		</tr>
	</table>
	<table border="1" >
		<tr style="padding: 0px;">
			<td style="width:260px; text-align: center; border-style:solid; border-width:2px; border-bottom-color:rgb(255,255,255);"> 
				<h2>$respEmpresa[razon_social]</h2>
			</td>
			<td style="width:40px; text-align:center">
			<div><span style="font-size:28.5px;">X</span></div>	
			</td>
			<td style="width:260px; text-align: center; border-style:solid; border-width:2px; border-bottom-color:rgb(255,255,255);"> 
				Orden de compra
			</td>
		</tr>
	</table>
	<table border="1" style="padding: 10px">
		<tr>
			<td style="width:280px; font-size:10px; text-align: left;">
				<br>
				<span><b>Direccion:</b> $respEmpresa[domicilio]</span> <br>
				<span><b>Telefono:</b> $respEmpresa[telefono]</span> <br>
				<span><b>Localidad:</b> $respEmpresa[localidad] - C.P.: $respEmpresa[codigo_postal]</span><br>
				<span><b>Defensa al Consumidor Mza. 08002226678</b></span> 
			</td>
			<td style="width:280px; font-size:10px; text-align: left">
				<div style="padding-top:5px">
					<span><b>N° Cbte:</b> $respuestaCompra[id]</span> <br>
					<span><b>Fecha Emisión:</b> $fecha </span><br>
					<span><b>CUIT:</b> $respEmpresa[cuit] </span><br>
					<span><b>II.BB.:</b> $respEmpresa[numero_iibb] </span><br>
					<span><b>Inic. Actividad:</b> $respEmpresa[inicio_actividades] </span>
				</div>
			</td>
		</tr>
	</table>
EOF;
    
    // Preparar datos del proveedor antes del bloque heredoc
    $proveedorNombre = isset($proveedor["nombre"]) ? $proveedor["nombre"] : "N/A";
    $proveedorCuit = isset($proveedor["cuit"]) ? $proveedor["cuit"] : "N/A";
    $proveedorDireccion = isset($proveedor["direccion"]) ? $proveedor["direccion"] : "N/A";
    
    $bloqueProveedor = <<<EOF
    <table style="padding: 5px">
		<tr>
			<td style="width:560px; font-size:12px; text-align: left;">
				<br>
				<span>PROVEEDOR: <b>Nombre / Razón Social :</b> $proveedorNombre </span> - <span> <b> CUIT:</b> $proveedorCuit </span>  
				<br>
				<span><b>Domicilio: </b> $proveedorDireccion </span>  
			</td>
		</tr>
	</table>
EOF;

$pdf->writeHTML($bloque1, false, false, false, false, '');
$pdf->writeHTML($bloqueProveedor, false, false, false, false, '');

// ---------------------------------------------------------
// ---------------------------------------------------------

$bloque3 = <<<EOF

	<table style="font-size:10px; padding:5px 10px;">
		<tr><td style="border: 1px solid #666; background-color:white; width:540px; text-align:center"><b>Detalle de los productos comprados cantidades</b></td>
		</tr>

		<tr>
		
		<td style="border: 1px solid #666; background-color:white; width:260px; text-align:center">Producto</td>
		<td style="border: 1px solid #666; background-color:white; width:80px; text-align:center">Codigo</td>
		<td style="border: 1px solid #666; background-color:white; width:60px; text-align:center">Pedido</td>
		<td style="border: 1px solid #666; background-color:white; width:70px; text-align:center">Recibido</td>
		<td style="border: 1px solid #666; background-color:white; width:70px; text-align:center">Precio</td>

		</tr>

	</table>

EOF;

$pdf->writeHTML($bloque3, false, false, false, false, '');

// ---------------------------------------------------------

    foreach ($productos as $key => $item) {
        
        if(!isset($item["descripcion"]) || empty($item["descripcion"])) {
            error_log("⚠️ Producto en índice $key no tiene descripción, omitiendo");
            continue;
        }
        
        $itemProducto = "descripcion";
        $valorProducto = $item["descripcion"];
        $orden = null;
        
        $respuestaProducto = ControladorProductos::ctrMostrarProductos($itemProducto, $valorProducto, $orden);
        
        if(!$respuestaProducto || !is_array($respuestaProducto) || empty($respuestaProducto)) {
            error_log("⚠️ No se encontró producto con descripción: " . $item["descripcion"]);
            // Usar valores por defecto si no se encuentra el producto
            $respuestaProducto = array(
                "codigo" => isset($item["codigo"]) ? $item["codigo"] : "N/A",
                "precio_venta" => 0
            );
        } else {
            // Si es un array, tomar el primer elemento
            if(isset($respuestaProducto[0])) {
                $respuestaProducto = $respuestaProducto[0];
            }
        }
        
        $valorUnitario = isset($respuestaProducto["precio_venta"]) ? number_format($respuestaProducto["precio_venta"], 2) : '0.00';
        $precioCompra = isset($item["precioCompra"]) ? number_format($item["precioCompra"], 2) : '0.00';
//$precioTotal = number_format($item["total"], 2);

$bloque4 = <<<EOF

	<table style="font-size:8px; padding:5px 10px;">

		<tr>
			
			<td style="border: 1px solid #666; color:#333; background-color:white; width:260px; text-align:center">
				$item[descripcion]
			</td>
			<td style="border: 1px solid #666; color:#333; background-color:white; width:80px; text-align:center">
				$respuestaProducto[codigo] 
			</td>	
			<td style="border: 1px solid #666; color:#333; background-color:white; width:60px; text-align:center">
				$item[pedidos] 
			</td>
			<td style="border: 1px solid #666; color:#333; background-color:white; width:70px; text-align:center"> 
			</td>
			<td style="border: 1px solid #666; color:#333; background-color:white; width:70px; text-align:center">
				 $ $precioCompra 
			</td>
		</tr>

	</table>

EOF;

$pdf->writeHTML($bloque4, false, false, false, false, '');

}
// ---------------------------------------------------------

$bloque5 = <<<EOF

	<table>
		
		<tr>
			<td style="border: 1px solid #666; color:#333; background-color:white; width:340px; height:22px; text-align:center">
				 TOTAL COMPRA 
			</td>
			<td style="border: 1px solid #666; color:#333; background-color:white; width:200px; height:22px; text-align:center">
				 $ $total 
			</td>
		</tr>

	</table>

EOF;
$pdf->writeHTML($bloque5, false, false, false, false, '');

    // ---------------------------------------------------------
    //SALIDA DEL ARCHIVO 
    error_log("Generando PDF de la compra...");
    //$pdf->Output('factura.pdf', 'D');
    $pdf->Output('CompraParcial.pdf');
    error_log("✅ PDF generado exitosamente");

} catch (Exception $e) {
    error_log("❌ EXCEPCIÓN en compraParcial.php: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    http_response_code(500);
    die('Error al generar la compra: ' . $e->getMessage());
} catch (Error $e) {
    error_log("❌ ERROR FATAL en compraParcial.php: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    http_response_code(500);
    die('Error fatal al generar la compra: ' . $e->getMessage());
}

}

try {
    error_log("Inicializando clase imprimirFactura...");
    $factura = new imprimirFactura();
    $factura -> codigo = $_GET["codigo"];
    $factura -> traerImpresionFactura();
    error_log("✅ Proceso completado exitosamente");
} catch (Exception $e) {
    error_log("❌ EXCEPCIÓN al inicializar: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    http_response_code(500);
    die('Error al generar la compra: ' . $e->getMessage());
} catch (Error $e) {
    error_log("❌ ERROR FATAL al inicializar: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    http_response_code(500);
    die('Error fatal al generar la compra: ' . $e->getMessage());
}

?>
