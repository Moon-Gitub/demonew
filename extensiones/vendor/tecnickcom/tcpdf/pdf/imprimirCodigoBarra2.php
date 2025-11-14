<?php

require_once "../../../controladores/productos.controlador.php";
require_once "../../../modelos/productos.modelo.php";

class imprimirPreciosProductos {

public $lista;

public function traerImpresionPrecios(){

//REQUERIMOS LA CLASE TCPDF
require_once('tcpdf_include.php');

$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

//$pdf->startPageGroup();
$pdf->AddPage();

$pdf->SetXY(3, 3);
$pdf->SetTextColor(0,0,0);
$pdf->SetFont('', '', 8);

//$pdf->Cell(30,-4.5,$respuesta["razon_social"] ,0,0,"C");
//$pdf->SetFont('', '', 8); 
//$pdf->MultiCell(40, 8, $producto["descripcion"], 0, 'C', 0, 1, '', '', true);

//$pdf->write1DBarcode($producto["codigo"], 'C39', '', '', 40, 10, 0.25, $style, 'N');
//$pdf->Cell(60,-4.5,  "$ ".number_format($producto['precio_venta'],2,".",",") ,1,0,"C");

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


//CELL(?, ?, TEXTO, BORDE TEXTO 0 SIN BORDE - 1 CON BORDE, TABULADO? 0 DEFASADO - 1 TABULADO - 2 SIN TABULAR)
// CODE 39 - ANSI MH10.8M-1983 - USD-3 - 3 of 9.
$pdf->Cell(0, 0, 'TEXTO', 0, 2);
$pdf->write1DBarcode('7894561234567', 'C39', '', '', 40, 10, 0.25, $style, 'N');

$pdf->Cell(0, 0, 'TEXT', 0, 2);
$pdf->write1DBarcode('CAT000001', 'C39', '', '', 40, 10, 0.25, $style, 'N');

$pdf->Cell(0, 0, 'TEXTO', 0, 2);
$pdf->write1DBarcode('PALABRALARGADEVARIOSCARACTERES', 'C39', '', '', 40, 10, 0.25, $style, 'N');



//TRAEMOS LOS PRODUCTOS A IMPRIMIR
$productos = json_decode($this->lista, true);
foreach ($productos as $key => $value) {

$producto = ControladorProductos::ctrMostrarProductos('id', $value["id"], 'id');
$pdf->Cell(0, 0, $producto["descripcion"], 0, 2);
//$pdf->write1DBarcode($producto["codigo"], 'C39', '', '', '', 18, 0.4, $style, 'N');
$pdf->write1DBarcode($producto["codigo"], 'C39', '', '', 40, 10, 0.25, $style, 'N');

}

$pdf->Output('Productos-Codigo-Barra.pdf');

}

}

$precios = new imprimirPreciosProductos();
$precios -> lista = $_GET["lista"];
$precios -> traerImpresionPrecios();

?>
