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

if($enHoja == 10) {
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

} 

if ($enHoja == 1) {
	if($enRama == 1){
		$x1= 55;
		$x2= 65;
		$x3= 70;
		$yRazon = 11;
		$yDescripcion = 18;
		$yPrecio = 25;
		$yCodigo = 40.5;
		$enHoja--;
	
	}
	if($enRama == 2){
		$x1= 100;
		$x2= 110;
		$x3= 115;
		$yRazon = 11;
		$yDescripcion = 18;
		$yPrecio = 25;
		$yCodigo = 40.5;
		//$enRama = 0;
		//$enHoja++;
		$enHoja--;
	}
	
	if($enRama == 3){
		$x1= 145;
		$x2= 155;
		$x3= 160;
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
		$yRazon = 11;
		$yDescripcion = 44;
		$yPrecio = 51;
		$yCodigo = 66.5;
		$enHoja--;
	
	}
	if($enRama == 2){
		$x1= 55;
		$x2= 65;
		$x3= 70;
		$yRazon = 11;
		$yDescripcion = 44;
		$yPrecio = 51;
		$yCodigo = 66.5;
		//$enRama = 0;
		//$enHoja++;
		$enHoja--;
	}
	
	if($enRama == 3){
		$x1= 100;
		$x2= 110;
		$x3= 115;
		$yRazon = 11;
		$yDescripcion = 44;
		$yPrecio = 51;
		$yCodigo = 66.5;
		//$enRama = 0;
		//$enHoja++;
		$enHoja--;
	}
	if($enRama == 4){
		$x1= 145;
		$x2= 155;
		$x3= 160;
		$yRazon = 11;
		$yDescripcion = 44;
		$yPrecio = 51;
		$yCodigo = 66.5;
		//$enHoja++;
		$enRama=0;
	}
}

if ($enHoja == 3) {
	if($enRama == 1){
		$x1= 10;
		$x2= 20;
		$x3= 30;
		$yRazon = 11;
		$yDescripcion = 70;
		$yPrecio = 77;
		$yCodigo = 112.5;
		$enHoja--;
	
	}
	if($enRama == 2){
		$x1= 55;
		$x2= 65;
		$x3= 70;
		$yRazon = 11;
		$yDescripcion = 70;
		$yPrecio = 77;
		$yCodigo = 112.5;
		//$enRama = 0;
		//$enHoja++;
		$enHoja--;
	}
	
	if($enRama == 3){
		$x1= 100;
		$x2= 110;
		$x3= 115;
		$yRazon = 11;
		$yDescripcion = 70;
		$yPrecio = 77;
		$yCodigo = 112.5;
		//$enRama = 0;
		//$enHoja++;
		$enHoja--;
	}
	if($enRama == 4){
		$x1= 145;
		$x2= 155;
		$x3= 160;
		$yRazon = 11;
		$yDescripcion = 70;
		$yPrecio = 77;
		$yCodigo = 112.5;
		//$enRama = 0;
		//$enHoja++;
		$enRama=0;
	}
}
if ($enHoja == 4) {
	if($enRama == 1){
		$x1= 10;
		$x2= 20;
		$x3= 30;
		$yRazon = 11;
		$yDescripcion = 96;
		$yPrecio = 103;
		$yCodigo = 118.5;
		$enHoja--;
	
	}
	if($enRama == 2){
		$x1= 55;
		$x2= 65;
		$x3= 70;
		$yRazon = 11;
		$yDescripcion = 96;
		$yPrecio = 103;
		$yCodigo = 118.5;
		//$enRama = 0;
		//$enHoja++;
		$enHoja--;
	}
	
	if($enRama == 3){
		$x1= 100;
		$x2= 110;
		$x3= 115;
		$yRazon = 11;
		$yDescripcion = 96;
		$yPrecio = 103;
		$yCodigo = 118.5;
		//$enRama = 0;
		//$enHoja++;
		$enHoja--;
	}
	if($enRama == 4){
		$x1= 145;
		$x2= 155;
		$x3= 160;
		$yRazon = 11;
		$yDescripcion = 96;
		$yPrecio = 103;
		$yCodigo = 118.5;
		//$enRama = 0;
		//$enHoja++;
		$enRama=0;
	}
}
if ($enHoja == 5) {
	if($enRama == 1){
		$x1= 10;
		$x2= 20;
		$x3= 30;
		$yRazon = 11;
		$yDescripcion = 122;
		$yPrecio = 129;
		$yCodigo = 144.5;
		$enHoja--;
	
	}
	if($enRama == 2){
		$x1= 55;
		$x2= 65;
		$x3= 70;
		$yRazon = 11;
		$yDescripcion = 122;
		$yPrecio = 129;
		$yCodigo = 144.5;
		//$enRama = 0;
		//$enHoja++;
		$enHoja--;
	}
	
	if($enRama == 3){
		$x1= 100;
		$x2= 110;
		$x3= 115;
		$yRazon = 11;
		$yDescripcion = 122;
		$yPrecio = 129;
		$yCodigo = 144.5;
		//$enRama = 0;
		//$enHoja++;
		$enHoja--;
	}
	if($enRama == 4){
		$x1= 145;
		$x2= 155;
		$x3= 160;
		$yRazon = 11;
		$yDescripcion = 122;
		$yPrecio = 129;
		$yCodigo = 144.5;
		//$enRama = 0;
		//$enHoja++;
		$enRama=0;
	}
}
if ($enHoja == 6) {
	if($enRama == 1){
		$x1= 10;
		$x2= 20;
		$x3= 30;
		$yRazon = 11;
		$yDescripcion = 148;
		$yPrecio = 155;
		$yCodigo = 160.5;
		$enHoja--;
	
	}
	if($enRama == 2){
		$x1= 55;
		$x2= 65;
		$x3= 70;
		$yRazon = 11;
		$yDescripcion = 148;
		$yPrecio = 155;
		$yCodigo = 160.5;
		//$enRama = 0;
		//$enHoja++;
		$enHoja--;
	}
	
	if($enRama == 3){
		$x1= 100;
		$x2= 110;
		$x3= 115;
		$yRazon = 11;
		$yDescripcion = 148;
		$yPrecio = 155;
		$yCodigo = 160.5;
		//$enRama = 0;
		//$enHoja++;
		$enHoja--;
	}
	if($enRama == 4){
		$x1= 145;
		$x2= 155;
		$x3= 160;
		$yRazon = 11;
		$yDescripcion = 148;
		$yPrecio = 155;
		$yCodigo = 160.5;
		$enRama=0;
	}
}

if ($enHoja == 7) {
	if($enRama == 1){
		$x1= 10;
		$x2= 20;
		$x3= 30;
		$yRazon = 11;
		$yDescripcion = 174;
		$yPrecio = 181;
		$yCodigo = 196.5;
		$enHoja--;
	
	}
	if($enRama == 2){
		$x1= 55;
		$x2= 65;
		$x3= 70;
		$yRazon = 11;
		$yDescripcion = 174;
		$yPrecio = 181;
		$yCodigo = 196.5;
		//$enRama = 0;
		//$enHoja++;
		$enHoja--;
	}
	
	if($enRama == 3){
		$x1= 100;
		$x2= 110;
		$x3= 115;
		$yRazon = 11;
		$yDescripcion = 174;
		$yPrecio = 181;
		$yCodigo = 196.5;
		//$enRama = 0;
		//$enHoja++;
		$enHoja--;
	}
	if($enRama == 4){
		$x1= 145;
		$x2= 155;
		$x3= 160;
		$yRazon = 11;
		$yDescripcion = 174;
		$yPrecio = 181;
		$yCodigo = 196.5;
		//$enRama = 0;
		//$enHoja++;
		$enRama=0;
	}
}
if ($enHoja == 8) {
	if($enRama == 1){
		$x1= 10;
		$x2= 20;
		$x3= 30;
		$yRazon = 11;
		$yDescripcion = 200;
		$yPrecio = 207;
		$yCodigo = 222.5;
		$enHoja--;
	
	}
	if($enRama == 2){
		$x1= 55;
		$x2= 65;
		$x3= 70;
		$yRazon = 11;
		$yDescripcion = 200;
		$yPrecio = 207;
		$yCodigo = 222.5;
		//$enRama = 0;
		//$enHoja++;
		$enHoja--;
	}
	
	if($enRama == 3){
		$x1= 100;
		$x2= 110;
		$x3= 115;
		$yRazon = 11;
		$yDescripcion = 200;
		$yPrecio = 207;
		$yCodigo = 222.5;
		//$enRama = 0;
		//$enHoja++;
		$enHoja--;
	}
	if($enRama == 4){
		$x1= 145;
		$x2= 155;
		$x3= 160;
		$yRazon = 11;
		$yDescripcion = 200;
		$yPrecio = 207;
		$yCodigo = 222.5;
		//$enRama = 0;
		//$enHoja++;
		$enRama=0;
	}

}
if ($enHoja == 9) {
	if($enRama == 1){
		$x1= 10;
		$x2= 20;
		$x3= 30;
		$yRazon = 11;
		$yDescripcion = 226;
		$yPrecio = 233;
		$yCodigo = 248.5;
		$enHoja--;
	
	}
	if($enRama == 2){
		$x1= 55;
		$x2= 65;
		$x3= 70;
		$yRazon = 11;
		$yDescripcion = 226;
		$yPrecio = 233;
		$yCodigo = 248.5;
		//$enRama = 0;
		//$enHoja++;
		$enHoja--;
	}
	
	if($enRama == 3){
		$x1= 100;
		$x2= 110;
		$x3= 115;
		$yRazon = 11;
		$yDescripcion = 226;
		$yPrecio = 233;
		$yCodigo = 248.5;
		//$enRama = 0;
		//$enHoja++;
		$enHoja--;
	}
	if($enRama == 4){
		$x1= 145;
		$x2= 155;
		$x3= 160;
		$yRazon = 11;
		$yDescripcion = 226;
		$yPrecio = 233;
		$yCodigo = 248.5;
		//$enRama = 0;
		//$enHoja++;
		$enRama=0;
	}
}
$producto = ControladorProductos::ctrMostrarProductos('id', $value["id"], 'id');
	
$bloque1 = <<<EOF

<table border=1>
		
		<tr style="text-align: center;">

			<td style="width:5%"></td>

			<td style="width:225px"></td>

			<td style="width:5%"></td>

		</tr>

	</table>
EOF;

//$pdf->writeHTML($bloque1, false, false, false, false, '');

$pdf->SetTextColor(0,0,0);
$pdf->SetFont('', 'B', 15);
//$pdf->SetXY($x1, $yRazon);

//$pdf->Cell(30,-4.5,$respuesta["razon_social"] ,0,0,"C");
$pdf->SetFont('', '', 8); 
$pdf->SetXY($x1, $yDescripcion);
$pdf->MultiCell(40, 8, $producto["descripcion"], 0, 'C', 0, 1, '', '', true);

$pdf->SetTextColor(0,0,0);
$pdf->SetFont('', 'B', 35);
$pdf->SetXY($x1, $yPrecio);
$pdf->write1DBarcode($producto["codigo"], 'C39', '', '', 40, 10, 0.25, $style, 'N');
//$pdf->Cell(60,-4.5,  "$ ".number_format($producto['precio_venta'],2,".",",") ,1,0,"C");
$pdf->SetFont('', 'B', 10);
//$pdf->SetXY($x1, $yCodigo);
//$pdf->Cell(60,-4.5, "codigo:".$producto["codigo"] ,1,0,"C");

$enHoja++;
$enRama++;

$bloqueEspacio = <<<EOF

	<br>
	<br>
	<br>

EOF;
$pdf->writeHTML($bloqueEspacio, false, false, false, false, '');

}

$pdf->Output('Productos-Codigo-Barra.pdf');

}

}

$precios = new imprimirPreciosProductos();
$precios -> lista = $_GET["lista"];
$precios -> traerImpresionPrecios();

?>
