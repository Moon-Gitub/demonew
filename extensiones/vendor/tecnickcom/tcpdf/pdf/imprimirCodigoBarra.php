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

$producto = ControladorProductos::ctrMostrarProductos('id', $value["id"], 'id');
	
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

$precios = new imprimirPreciosProductos();
$precios -> lista = $_GET["lista"];
$precios -> traerImpresionPrecios();

?>