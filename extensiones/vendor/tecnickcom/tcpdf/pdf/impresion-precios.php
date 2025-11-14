<?php

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
$pdf->Cell(60,-4.5,  "$ ".number_format($producto['precio_venta'],2,".",",") ,1,0,"C");
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

$precios = new imprimirPreciosProductos();
$precios -> lista = $_GET["lista"];
$precios -> traerImpresionPrecios();

?>
