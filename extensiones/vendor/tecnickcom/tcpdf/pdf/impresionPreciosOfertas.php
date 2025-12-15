<?php

// Inicializar entorno (.env) para que la conexión a BD funcione
require_once dirname(__DIR__, 3) . "/autoload.php";
if (class_exists('Dotenv\\Dotenv')) {
    $raiz = dirname(__DIR__, 3);
    if (file_exists($raiz . "/.env")) {
        $dotenv = Dotenv\Dotenv::createImmutable($raiz);
        $dotenv->safeLoad();
    }
}

require_once "../../../controladores/productos.controlador.php";
require_once "../../../modelos/productos.modelo.php";

require_once "../../../controladores/empresa.controlador.php";
require_once "../../../modelos/empresa.modelo.php";


class imprimirPreciosProductos {

public $lista;

public function traerImpresionPrecios(){

//REQUERIMOS LA CLASE TCPDF
require_once('tcpdf_include.php');

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
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar que hay productos en sesión
if (!isset($_SESSION['productos_impresion']) || empty($_SESSION['productos_impresion'])) {
    die('Error: No hay productos seleccionados para imprimir.');
}

// Convertir productos de sesión al formato JSON esperado
$productosParaImprimir = [];
foreach ($_SESSION['productos_impresion'] as $item) {
    $productosParaImprimir[] = ['id' => $item['id']];
}

$precios->lista = json_encode($productosParaImprimir);
$precios -> traerImpresionPrecios();

?>
