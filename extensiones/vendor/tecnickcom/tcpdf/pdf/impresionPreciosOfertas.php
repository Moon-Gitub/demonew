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
$pdf->setMargins(0, 0, 0, false);

$pdf->startPageGroup();

$pdf->SetDisplayMode('fullpage', 'SinglePage', 'UseNone');


$pdf->AddPage('L', 'A4');



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
$yDescripcion = 75;
$yPrecio = 107;
$yCodigo = 133;

foreach ($productos as $key => $value) {
    // Validar que el item tenga ID
    if (!isset($value["id"])) {
        continue; // Saltar si no tiene ID
    }

if($enHoja == 1) {
$pdf->AddPage();
$enHoja=0;
$yDescripcion = 75;
$yPrecio = 107;
$yCodigo = 133;

} elseif ($enHoja == 1) {
$yDescripcion = 142;
$yPrecio = 157;
$yCodigo = 212;
}

$producto = ControladorProductos::ctrMostrarProductos('id', $value["id"], 'id');

// Validar que el producto existe
if (!$producto || empty($producto)) {
    error_log("Producto no encontrado con ID: " . $value["id"]);
    continue; // Saltar si no se encuentra el producto
}

// Validar y convertir precios a float
$precioVenta = 0;
if (isset($producto['precio_venta'])) {
    if (is_numeric($producto['precio_venta'])) {
        $precioVenta = floatval($producto['precio_venta']);
    } elseif (is_string($producto['precio_venta']) && $producto['precio_venta'] !== '') {
        $precioVenta = floatval(str_replace(',', '.', $producto['precio_venta']));
    }
}

$precioPromo = 0;
if (isset($producto['precioPromo'])) {
    if (is_numeric($producto['precioPromo'])) {
        $precioPromo = floatval($producto['precioPromo']);
    } elseif (is_string($producto['precioPromo']) && $producto['precioPromo'] !== '') {
        $precioPromo = floatval(str_replace(',', '.', $producto['precioPromo']));
    }
}
	
//
// IMAGEN FONDO
//
$bloque1 = <<<EOF

<table >
		
		<tr>


			<td style="width:100%; height:100%"><img src="images/oferta.jpg"></td>

			

		</tr>

	</table>
EOF;

$pdf->writeHTML($bloque1, false, false, false, false, '');

//
// DESCRIPCION (ENTRAN 32 CARACTERES)
//
//$pdf->addTTFfont('Montserrat-Light.ttf','TrueTypeUnicode','', 32);

$pdf->SetTextColor(0,0,0);
$pdf->SetFont('', 'B', 20);
$pdf->SetXY(10, $yDescripcion);
$pdf->MultiCell(245, 5, $producto["descripcion"], 0, 'C', 0, 0, '', '', true);

//
// PRECIO
//
$pdf->SetTextColor(0,0,0);
$pdf->SetFont('', 'B', 120);
$pdf->SetXY(60, $yPrecio);

if(isset($producto["estadoPromocion"]) && $producto["estadoPromocion"]) {
    $fecha_actual = strtotime(date("Y-m-d H:i:00",time()));
    $fecha_promo = isset($producto["fechaPromo"]) ? strtotime($producto["fechaPromo"]) : 0;
	
    if($fecha_actual > $fecha_promo && $fecha_promo > 0)
    {
        $precioRedondo = number_format($precioVenta, 2, ',','.');
    } else
    {
        $precioRedondo = number_format($precioPromo, 2, ',','.');
    }
} else {
    $precioRedondo = number_format($precioVenta, 2, ',','.');
}
$bloquePrecio = <<<EOF

$ $precioRedondo

EOF;
$pdf->writeHTML($bloquePrecio, false, false, false, false, '');

//
// CODIGO (EAN 13)
//
$pdf->SetFont('', 'B', 17);
$pdf->SetXY(100, $yCodigo);
$pdf->writeHTML("codigo:".$producto["codigo"], 'EAN13', '', '', '', 18, 0.4, $style, 'N');


$pdf->SetFont('', 'B', 35);
$pdf->SetXY(98,137);

//$pdf->writeHTML($respuesta["razon_social"]);




$enHoja++;

}//Fin Foreach

//$pdf->writeHTML($bloqueCodBarra, false, false, false, false, '');

// ---------------------------------------------------------
//SALIDA DEL ARCHIVO 

//$pdf->Output('factura.pdf', 'D');
$pdf->Output('precios-gondola.pdf');

}

}

$precios = new imprimirPreciosProductos();
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
    error_log("Error en impresionPreciosOfertas: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    die('Error al generar el PDF: ' . htmlspecialchars($e->getMessage()));
} catch (Error $e) {
    header('Content-Type: text/html; charset=utf-8');
    error_log("Error fatal en impresionPreciosOfertas: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    die('Error fatal al generar el PDF: ' . htmlspecialchars($e->getMessage()));
}

?>
