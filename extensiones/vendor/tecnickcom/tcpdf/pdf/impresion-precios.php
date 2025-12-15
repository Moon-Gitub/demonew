<?php

// Habilitar reporte de errores para debugging
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Inicializar entorno (.env) para que la conexión a BD funcione
$autoloadPath = dirname(__DIR__, 3) . "/autoload.php";
$autoloadPathAlt = __DIR__ . "/../../../autoload.php";

if (file_exists($autoloadPath)) {
    require_once $autoloadPath;
} elseif (file_exists($autoloadPathAlt)) {
    require_once $autoloadPathAlt;
} else {
    error_log("Error: No se encuentra autoload.php");
    header('Content-Type: text/html; charset=utf-8');
    die('Error de configuración: No se encuentra autoload.php');
}

// Cargar .env desde la raíz del proyecto
if (class_exists('Dotenv\\Dotenv')) {
    $raiz1 = dirname(__DIR__, 5);
    $raiz2 = __DIR__ . "/../../../../../";
    $raiz3 = dirname(__DIR__, 3);
    
    $raiz = null;
    $envPath = null;
    
    foreach ([$raiz1, $raiz2, $raiz3] as $ruta) {
        $rutaReal = realpath($ruta);
        if ($rutaReal && file_exists($rutaReal . "/.env")) {
            $raiz = $rutaReal;
            $envPath = $rutaReal . "/.env";
            break;
        }
    }
    
    if ($raiz && $envPath) {
        try {
            $dotenv = Dotenv\Dotenv::createImmutable($raiz);
            $dotenv->safeLoad();
        } catch (Exception $e) {
            error_log("Error al cargar .env: " . $e->getMessage());
        }
    }
}

require_once "../../../../../controladores/productos.controlador.php";
require_once "../../../../../modelos/productos.modelo.php";

require_once "../../../../../controladores/empresa.controlador.php";
require_once "../../../../../modelos/empresa.modelo.php";

class imprimirPreciosProductos {

public $lista;

public function traerImpresionPrecios(){

//REQUERIMOS LA CLASE TCPDF
$tcpdfIncludePath = __DIR__ . '/tcpdf_include.php';
if (file_exists($tcpdfIncludePath)) {
    require_once($tcpdfIncludePath);
} else {
    // Fallback: cargar TCPDF directamente
    define('K_PATH_MAIN', dirname(__DIR__) . '/');
    define('K_PATH_FONTS', K_PATH_MAIN . 'fonts/');
    define('K_PATH_IMAGES', __DIR__ . '/images/');
    require_once(K_PATH_MAIN . 'tcpdf.php');
}

$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

$pdf->startPageGroup();

$pdf->AddPage();

// define barcode style
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

// ---------------------------------------------------------
$respuesta = ModeloEmpresa::mdlMostrarEmpresa('empresa', 'id', 1);
//TRAEMOS LOS PRODUCTOS A IMPRIMIR
$productos = json_decode($this->lista, true);

$enHoja=0;
$enRama=0;
$yRazon = 11;
$yDescripcion = 18;
$yPrecio = 25;
$yCodigo = 40.5;
$x1= 10;
$x2= 20;
$x3= 30;
foreach ($productos as $key => $value) {
    // Validar que el item tenga ID
    if (!isset($value["id"])) {
        continue; // Saltar si no tiene ID
    }

if($enHoja == 8) {
$pdf->AddPage();
$enHoja=0;
$enRama=0;
$yRazon = 11;
$yDescripcion = 18;
$yPrecio = 25;
$yCodigo = 40.5;
$x1= 10;
$x2= 20;
$x3= 30;

} if ($enHoja == 1) {
	if($enRama == 1){
		$x1= 75;
		$x2= 85;
		$x3= 90;
		$yRazon = 11;
		$yDescripcion = 18;
		$yPrecio = 25;
		$yCodigo = 40.5;
		$enHoja--;
	
	}
	else{
		$x1= 140;
		$x2= 150;
		$x3= 155;
		$yRazon = 11;
		$yDescripcion = 18;
		$yPrecio = 25;
		$yCodigo = 40.5;
		$enRama = 0;
		//$enHoja++;
		$enRama=0;
	}
}
if ($enHoja == 2) {
	if($enRama == 1){
		$x1= 10;
		$x2= 20;
		$x3= 30;
		$yRazon = 47;
		$yDescripcion = 54;
		$yPrecio = 61;
		$yCodigo = 76.5;
		$enHoja--;
	
	}else if($enRama == 2){
		$x1= 75;
		$x2= 80;
		$x3= 90;
		$yRazon = 47;
		$yDescripcion = 54;
		$yPrecio = 61;
		$yCodigo = 76.5;
		$enHoja--;
	
	}
	else{
		$x1= 140;
		$x2= 150;
		$x3= 155;
		$yRazon = 47;
		$yDescripcion = 54;
		$yPrecio = 61;
		$yCodigo = 76.5;
		$enRama = 0;
		//$enHoja++;
		$enRama=0;
	}
}

if ($enHoja == 3) {
	if($enRama == 1){
		$x1= 10;
		$x2= 20;
		$x3= 25;
		$yRazon = 83;
		$yDescripcion = 90;
		$yPrecio = 97;
		$yCodigo = 112.5;
		$enHoja--;
	
	}else if($enRama == 2){
		$x1= 75;
		$x2= 80;
		$x3= 90;
		$yRazon = 83;
		$yDescripcion = 90;
		$yPrecio = 97;
		$yCodigo = 112.5;
		$enHoja--;
	
	}
	else{
		$x1= 140;
		$x2= 150;
		$x3= 155;
		$yRazon = 83;
		$yDescripcion = 90;
		$yPrecio = 97;
		$yCodigo = 112.5;
		$enRama = 0;
		//$enHoja++;
		$enRama=0;
	}
}
if ($enHoja == 4) {
	if($enRama == 1){
		$x1= 10;
		$x2= 20;
		$x3= 30;
		$yRazon = 119;
		$yDescripcion = 126;
		$yPrecio = 133;
		$yCodigo = 148.5;
		$enHoja--;
	
	}else if($enRama == 2){
		$x1= 75;
		$x2= 80;
		$x3= 90;
		$yRazon = 119;
		$yDescripcion = 126;
		$yPrecio = 133;
		$yCodigo = 148.5;
		$enHoja--;
	
	}
	else{
		$x1= 140;
		$x2= 150;
		$x3= 155;
		$yRazon = 119;
		$yDescripcion = 126;
		$yPrecio = 133;
		$yCodigo = 148.5;
		$enRama = 0;
		//$enHoja++;
		$enRama=0;
	}
}
if ($enHoja == 5) {
	if($enRama == 1){
		$x1= 10;
		$x2= 20;
		$x3= 25;
		$yRazon = 155;
		$yDescripcion = 162;
		$yPrecio = 169;
		$yCodigo = 184.5;
		$enHoja--;
	
	}else if($enRama == 2){
		$x1= 75;
		$x2= 85;
		$x3= 90;
		$yRazon = 155;
		$yDescripcion = 162;
		$yPrecio = 169;
		$yCodigo = 184.5;
		$enHoja--;
	
	}
	else{
		$x1= 140;
		$x2= 150;
		$x3= 155;
		$yRazon = 155;
		$yDescripcion = 162;
		$yPrecio = 169;
		$yCodigo = 184.5;
		$enRama = 0;
		//$enHoja++;
		$enRama=0;
	}
}

if ($enHoja == 6) {
	if($enRama == 1){
		$x1= 10;
		$x2= 20;
		$x3= 25;
		$yRazon = 191;
		$yDescripcion = 198;
		$yPrecio = 205;
		$yCodigo = 220.5;
		$enHoja--;
	
	}else if($enRama == 2){
		$x1= 75;
		$x2= 85;
		$x3= 90;
			$yRazon = 191;
		$yDescripcion = 198;
		$yPrecio = 205;
		$yCodigo = 220.5;
		$enHoja--;
	
	}
	else{
		$x1= 140;
		$x2= 150;
		$x3= 155;
		$yRazon = 191;
		$yDescripcion = 198;
		$yPrecio = 205;
		$yCodigo = 220.5;
		$enRama = 0;
		//$enHoja++;
		$enRama=0;
	}
}

if ($enHoja == 7) {
	if($enRama == 1){
		$x1= 10;
		$x2= 20;
		$x3= 25;
		$yRazon = 227;
		$yDescripcion = 234;
		$yPrecio = 241;
		$yCodigo = 256.5;
		$enHoja--;
	
	}else if($enRama == 2){
		$x1= 75;
		$x2= 85;
		$x3= 90;
		$yRazon = 227;
		$yDescripcion = 234;
		$yPrecio = 241;
		$yCodigo = 256.5;
		$enHoja--;
	
	}
	else{
		$x1= 140;
		$x2= 150;
		$x3= 155;
		$yRazon = 227;
		$yDescripcion = 234;
		$yPrecio = 241;
		$yCodigo = 256.5;
		$enRama = 0;
		//$enHoja++;
		$enRama=0;
	}
}
$producto = ControladorProductos::ctrMostrarProductos('id', $value["id"], 'id');

// Validar que el producto existe
if (!$producto || empty($producto)) {
    error_log("Producto no encontrado con ID: " . $value["id"]);
    continue; // Saltar si no se encuentra el producto
}

// Validar y convertir precio_venta a float
$precioVenta = 0;
if (isset($producto['precio_venta'])) {
    if (is_numeric($producto['precio_venta'])) {
        $precioVenta = floatval($producto['precio_venta']);
    } elseif (is_string($producto['precio_venta']) && $producto['precio_venta'] !== '') {
        $precioVenta = floatval(str_replace(',', '.', $producto['precio_venta']));
    }
}
	
$bloque1 = <<<EOF

<table>
		
		<tr style="text-align: center;">

			<td style="width:5%"></td>

			<td style="width:225px"></td>

			<td style="width:5%"></td>

		</tr>

	</table>
EOF;

$pdf->writeHTML($bloque1, false, false, false, false, '');

$pdf->SetTextColor(0,0,0);
$pdf->SetFont('', 'B', 15);
$pdf->SetXY($x1, $yRazon);

$pdf->Cell(60,-4.5,$respuesta["razon_social"] ,1,0,"C");
$pdf->SetFont('', '', 8); 
$pdf->SetXY($x1, $yDescripcion);
$pdf->MultiCell(60, 7, $producto["descripcion"], 1, 'C', 0, 1, '', '', true);

$pdf->SetTextColor(0,0,0);
$pdf->SetFont('', 'B', 25);
$pdf->SetXY($x1, $yPrecio);
$pdf->Cell(60,-4.5,  "$ ".number_format($precioVenta, 2, ".", ",") ,1,0,"C");
$pdf->SetFont('', 'B', 10);
$pdf->SetXY($x1, $yCodigo);
$pdf->Cell(60,-4.5, "codigo:".$producto["codigo"] ,1,0,"C");

$enHoja++;
$enRama++;

$bloqueEspacio = <<<EOF

	<br>
	<br>
	<br>

EOF;
$pdf->writeHTML($bloqueEspacio, false, false, false, false, '');

}

$pdf->Output('precios-gondola.pdf');

}

}

// Inicializar sesión para leer productos seleccionados
// IMPORTANTE: session_id() debe llamarse ANTES de session_start()
if (isset($_GET['PHPSESSID']) && !empty($_GET['PHPSESSID'])) {
    session_id($_GET['PHPSESSID']);
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Intentar obtener productos de sesión o de parámetro GET (backup)
$productosParaImprimir = [];

// Primero intentar desde sesión
if (isset($_SESSION['productos_impresion']) && !empty($_SESSION['productos_impresion'])) {
    foreach ($_SESSION['productos_impresion'] as $item) {
        if (isset($item['id'])) {
            $productosParaImprimir[] = ['id' => intval($item['id'])];
        }
    }
}

// Si no hay en sesión, intentar desde parámetro GET (backup)
if (empty($productosParaImprimir) && isset($_GET['ids']) && !empty($_GET['ids'])) {
    $idsJson = json_decode(urldecode($_GET['ids']), true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        $idsJson = json_decode($_GET['ids'], true);
    }
    
    if (is_array($idsJson) && !empty($idsJson)) {
        foreach ($idsJson as $item) {
            if (isset($item['id'])) {
                $productosParaImprimir[] = ['id' => intval($item['id'])];
            }
        }
    }
}

// Verificar que hay productos
if (empty($productosParaImprimir)) {
    header('Content-Type: text/html; charset=utf-8');
    die('Error: No hay productos seleccionados para imprimir. Por favor, seleccioná productos desde la página de impresión.');
}

$precios = new imprimirPreciosProductos();
$precios->lista = json_encode($productosParaImprimir);

try {
    $precios->traerImpresionPrecios();
} catch (Exception $e) {
    header('Content-Type: text/html; charset=utf-8');
    error_log("Error en impresion-precios: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    die('Error al generar el PDF: ' . htmlspecialchars($e->getMessage()));
} catch (Error $e) {
    header('Content-Type: text/html; charset=utf-8');
    error_log("Error fatal en impresion-precios: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    die('Error fatal al generar el PDF: ' . htmlspecialchars($e->getMessage()));
}

?>
