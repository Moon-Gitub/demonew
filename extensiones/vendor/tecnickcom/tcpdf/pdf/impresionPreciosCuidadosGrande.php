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

$pdf = new TCPDF("L", PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

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
    //'fgcolor' => array(30,186,237),
    'fgcolor' => array(0,0,0),
    'bgcolor' => false, //array(255,255,255),
    'text' => true,
    'font' => 'helvetica',
    'fontsize' => 8,
    'stretchtext' => 4
);

// ---------------------------------------------------------

//TRAEMOS LOS PRODUCTOS A IMPRIMIR
$productos = json_decode($this->lista, true);

$enHoja=0;
$yDescripcion = 10;
$yPrecio = 27;
$yCodigo = 51;

foreach ($productos as $key => $value) {

if ($enHoja == 2) {
$pdf->AddPage();
$enHoja=0;
$yDescripcion = 10;
$yPrecio = 20;
$yCodigo = 51;
}elseif ($enHoja == 1) {
$yDescripcion = 93;
$yPrecio = 110;
$yCodigo = 136;
}

$producto = ControladorProductos::ctrMostrarProductos('id', $value["id"], 'id');
	
//
// IMAGEN FONDO
//
$bloque1 = <<<EOF

<table>
		
		<tr style="text-align: center;">

		

			<td style="width:811px"><img src="images/preciosCuidados.jpg"></td>

			

		</tr>

	</table>
EOF;

$pdf->writeHTML($bloque1, false, false, false, false, '');

//
// DESCRIPCION (ENTRAN 32 CARACTERES)
//

//$pdf->SetTextColor(238,57,138);
$pdf->SetTextColor(0,0,0);
$pdf->SetFont('', 'B', 14);
$pdf->SetXY(10, $yDescripcion);

$pdf->MultiCell(150, 5, $producto[descripcion], 0, 'C', 0, 0, '', '', true);

//
// PRECIO
//
//$pdf->SetTextColor(238,57,138);
$pdf->SetTextColor(0,0,0);
$pdf->SetFont('', 'B', 65);
$pdf->SetXY(86, $yPrecio);
if($producto["estadoPromocion"]) {
$fecha_actual = strtotime(date("Y-m-d H:i:00",time()));
$fecha_promo = strtotime($producto["fechaPromo"]);
	
if($fecha_actual > $fecha_promo)
	{
	$precioRedondo = number_format($producto["precio_venta"], 2, ',','.');
	}else
	{
	$precioRedondo = number_format($producto["precioPromo"], 2, ',','.');
	}
}else{
$precioRedondo = number_format($producto["precio_venta"], 2, ',','.');
}
$bloquePrecio = <<<EOF

$ $precioRedondo

EOF;
$pdf->writeHTML($bloquePrecio, false, false, false, false, '');

//
// CODIGO (EAN 13)
//
$pdf->SetFont('', 'B', 14);
$pdf->SetXY(106, $yCodigo);
$pdf->writeHTML("codigo:".$producto["codigo"], 'ARIAL', '', '', '', 22, 0.4, $style, 'N');

$enHoja++;

$bloqueEspacio = <<<EOF

	<br>
	<br>
	<br>

EOF;
$pdf->writeHTML($bloqueEspacio, false, false, false, false, '');

}

//$pdf->writeHTML($bloqueCodBarra, false, false, false, false, '');

// ---------------------------------------------------------
//SALIDA DEL ARCHIVO 

//$pdf->Output('factura.pdf', 'D');
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

// Si aún no hay productos, intentar desde lista antigua (compatibilidad)
if (empty($productosParaImprimir) && isset($_GET["lista"]) && !empty($_GET["lista"])) {
    $listaJson = json_decode($_GET["lista"], true);
    if (is_array($listaJson)) {
        $productosParaImprimir = $listaJson;
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
    error_log("Error en impresionPreciosCuidadosGrande: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    die('Error al generar el PDF: ' . htmlspecialchars($e->getMessage()));
} catch (Error $e) {
    header('Content-Type: text/html; charset=utf-8');
    error_log("Error fatal en impresionPreciosCuidadosGrande: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    die('Error fatal al generar el PDF: ' . htmlspecialchars($e->getMessage()));
}

?>
