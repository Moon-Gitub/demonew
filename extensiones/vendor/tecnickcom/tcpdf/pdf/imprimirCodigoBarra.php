<?php
// Habilitar reporte de errores para debugging (solo en desarrollo)
error_reporting(E_ALL);
ini_set('display_errors', 0); // No mostrar en pantalla, solo en logs
ini_set('log_errors', 1);

// Inicializar entorno (.env) para que la conexión a BD funcione
// Desde esta ruta: extensiones/vendor/tecnickcom/tcpdf/pdf
// Necesitamos llegar a: extensiones/vendor/autoload.php
// __DIR__ = extensiones/vendor/tecnickcom/tcpdf/pdf
// dirname(__DIR__) = extensiones/vendor/tecnickcom/tcpdf
// dirname(__DIR__, 2) = extensiones/vendor/tecnickcom
// dirname(__DIR__, 3) = extensiones/vendor
$autoloadPath = dirname(__DIR__, 3) . "/autoload.php";
$autoloadPathAlt = __DIR__ . "/../../../autoload.php"; // Ruta alternativa

if (file_exists($autoloadPath)) {
    require_once $autoloadPath;
} elseif (file_exists($autoloadPathAlt)) {
    require_once $autoloadPathAlt;
} else {
    error_log("Error: No se encuentra autoload.php. Intentado: " . $autoloadPath . " y " . $autoloadPathAlt);
    header('Content-Type: text/html; charset=utf-8');
    die('Error de configuración: No se encuentra autoload.php. Revisa los logs del servidor.');
}

if (class_exists('Dotenv\\Dotenv')) {
    $raiz = dirname(__DIR__, 3);
    if (file_exists($raiz . "/.env")) {
        $dotenv = Dotenv\Dotenv::createImmutable($raiz);
        $dotenv->safeLoad();
    }
}

// Cargar modelos y controladores
// Desde: extensiones/vendor/tecnickcom/tcpdf/pdf/
// Hacia: controladores/ (en la raíz del proyecto)
// Necesitamos subir 5 niveles: ../../../../../controladores/
$controladorPath = __DIR__ . "/../../../../../controladores/productos.controlador.php";
$modeloPath = __DIR__ . "/../../../../../modelos/productos.modelo.php";

if (!file_exists($controladorPath)) {
    error_log("Error: No se encuentra productos.controlador.php en: " . $controladorPath);
    header('Content-Type: text/html; charset=utf-8');
    die('Error: No se encuentra productos.controlador.php');
}

if (!file_exists($modeloPath)) {
    error_log("Error: No se encuentra productos.modelo.php en: " . $modeloPath);
    header('Content-Type: text/html; charset=utf-8');
    die('Error: No se encuentra productos.modelo.php');
}

require_once $controladorPath;
require_once $modeloPath;

class imprimirPreciosProductos {

public $lista;

public function traerImpresionPrecios(){

//REQUERIMOS LA CLASE TCPDF
// tcpdf_include.php debe estar en el mismo directorio (pdf/)
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

// define barcode style
$style = array(
    'align' => 'C',
    'stretch' => false,
    'fitwidth' => false,
    'border' => false,
    'fgcolor' => array(0,0,0),
    'bgcolor' => false, 
    'text' => true,
    //'font' => 'courier',
    //'fontsize' => 8,
    'stretchtext' => 3
);

// dfino fuente monoespaciada
$pdf->SetFont('courier', '', 10);

//TRAEMOS LOS PRODUCTOS A IMPRIMIR
$productos = json_decode($this->lista, true);

$nuevaPagina = true;
$enLinea = 1;
$Xinicio = $Xactual = 5;
$Yinicio = $Yactual = 5;

foreach ($productos as $key => $value) {
    if (!isset($value["id"])) {
        continue; // Saltar si no tiene ID
    }
    
    $producto = ControladorProductos::ctrMostrarProductos('id', $value["id"], 'id');
    
    if (!$producto || empty($producto)) {
        error_log("Producto no encontrado con ID: " . $value["id"]);
        continue; // Saltar si no se encuentra el producto
    }
	
if($nuevaPagina){
$pdf->AddPage();
$Xactual = $Xinicio;
$Yactual = $Yinicio;
$nuevaPagina = false;
}

$descripcion = substr($producto["descripcion"],0,23); 
$codigo = $producto["codigo"]; 

$pdf->SetXY($Xactual, $Yactual);
$pdf->Cell(45, 0, $descripcion, 0, 1, 'C', 0, '', 0);
$pdf->write1DBarcode($codigo, 'C39', $Xactual, $Yactual + 5, 45, 10, 0.15, $style, 'N');

if($enLinea < 4) {
    $enLinea++;
    $Xactual += 50;
} elseif($enLinea == 4){
    $enLinea = 1;
    $Xactual = $Xinicio;
    $Yactual += 20;
}

if($Yactual > 260){
    $nuevaPagina = true;
}

}

$pdf->Output('Productos-Codigo-Barra.pdf');

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

// Si no hay en sesión, intentar desde parámetro GET (backup para cuando sesión no funciona)
if (empty($productosParaImprimir) && isset($_GET['ids']) && !empty($_GET['ids'])) {
    $idsJson = json_decode(urldecode($_GET['ids']), true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log("Error al decodificar JSON de ids: " . json_last_error_msg());
        // Intentar sin urldecode por si ya viene decodificado
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

// Validar que tenemos productos antes de continuar
if (empty($productosParaImprimir)) {
    header('Content-Type: text/html; charset=utf-8');
    error_log("Error: No se pudieron obtener productos de sesión ni de parámetro GET");
    die('Error: No hay productos seleccionados para imprimir. Por favor, seleccioná productos desde la página de impresión.');
}

error_log("Productos para imprimir: " . count($productosParaImprimir) . " productos");

$precios = new imprimirPreciosProductos();
$precios->lista = json_encode($productosParaImprimir);

try {
    $precios->traerImpresionPrecios();
} catch (Exception $e) {
    header('Content-Type: text/html; charset=utf-8');
    error_log("Error en imprimirCodigoBarra: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    die('Error al generar el PDF: ' . htmlspecialchars($e->getMessage()));
} catch (Error $e) {
    header('Content-Type: text/html; charset=utf-8');
    error_log("Error fatal en imprimirCodigoBarra: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    die('Error fatal al generar el PDF: ' . htmlspecialchars($e->getMessage()));
}

?>