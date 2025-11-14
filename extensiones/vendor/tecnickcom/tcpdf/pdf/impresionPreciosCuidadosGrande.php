<?php

require_once "../../../controladores/productos.controlador.php";
require_once "../../../modelos/productos.modelo.php";

class imprimirPreciosProductos {

public $lista;

public function traerImpresionPrecios(){

//REQUERIMOS LA CLASE TCPDF
require_once('tcpdf_include.php');

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

$precios = new imprimirPreciosProductos();
$precios -> lista = $_GET["lista"];
$precios -> traerImpresionPrecios();

?>
