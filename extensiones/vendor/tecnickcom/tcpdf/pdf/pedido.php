<?php

require_once "../../../controladores/pedidos.controlador.php";
require_once "../../../modelos/pedidos.modelo.php";

require_once "../../../controladores/productos.controlador.php";
require_once "../../../modelos/productos.modelo.php";

class imprimirFactura{

public $codigo;

public function traerImpresionFactura(){

//TRAEMOS LA INFORMACIÓN DE LA VENTA

$itemPedido = "id";
$respuestaPedido = ControladorPedidos::ctrMostrarPedidos($itemPedido, $_GET['codigo']);

$fecha = substr($respuestaPedido["fecha"],0,-8);
$fecha=date("d-m-Y",strtotime($fecha));
$productos = json_decode($respuestaPedido["productos"], true);
$origen = json_decode($respuestaPedido["origen"],true);
$destino = json_decode($respuestaPedido["destino"],true);

//REQUERIMOS LA CLASE TCPDF

require_once('tcpdf_include.php');

$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

$pdf->AddPage('P', 'A4');


// ---------------------------------------------------------

$bloque1 = <<<EOF

	<table>
		
		<tr>
			
			<td style="width:150px; padding:45px;"><img src="images/logo_moon.png"></td>

			<td style="background-color:white; width:100px">
				
				<div style="font-size:8.5px; text-align:right; line-height:15px;">
					
					

				</div>

			</td>

			<td style="background-color:white; width:40px">

				<div style="border: 1px solid #666;font-size:28.5px; text-align:right; line-height:25px;">
				<br>	
				R	

				</div>
				
			</td>

			<td style="background-color:white; width:250px; text-align:center; color:red">
			<br>
			<br>
			 DOCUMENTO NO VALIDO COMO FACTURA
			</td>
		</tr>

	</table>

EOF;

$pdf->writeHTML($bloque1, false, false, false, false, '');

// ---------------------------------------------------------

$bloque2 = <<<EOF
	
	<table style="font-size:10px; padding:5px 10px;">
	
		<tr>
					
			<td style="border: 1px solid #666; background-color:white; width:270px; text-align:left">
			
				PEDIDO Nº $_GET[codigo]

			</td>
			<td style="border: 1px solid #666; background-color:white; width:270px; text-align:left">
			
				FECHA: $fecha<br>

			</td>
		</tr>

	</table>
	
	<table style="font-size:10px; padding:5px 10px;">
	
		<tr>
		
			<td style="border: 1px solid #666; background-color:white; width:270px">

				Usuario que hizo el pedido: $respuestaPedido[id_vendedor] 

			</td>

			<td style="border: 1px solid #666; background-color:white; width:270px; text-align:left">
			
				Usuario que valido el pedido: $respuestaPedido[usuarioConfirma]

			</td>

		</tr>

		<tr>
		
			<td style="border: 1px solid #666; background-color:white; width:270px">

				Sucursal Origen: $respuestaPedido[origen]

			</td>

			<td style="border: 1px solid #666; background-color:white; width:270px; text-align:left">
			
				Sucursal Destino: $respuestaPedido[destino]

			</td>

		</tr>

		<tr>
		
		<td style="border-bottom: 1px solid #666; background-color:white; width:540px"></td>

		</tr>

	</table>

EOF;

$pdf->writeHTML($bloque2, false, false, false, false, '');

// ---------------------------------------------------------

$bloque3 = <<<EOF

	<table style="font-size:10px; padding:5px 10px;">
		<tr><td style="border: 1px solid #666; background-color:white; width:540px; text-align:center"><b>Detalle de los productos pedidos</b></td>
		</tr>

		<tr>
		
		<td style="border: 1px solid #666; background-color:white; width:300px; text-align:center">Producto</td>
		<td style="border: 1px solid #666; background-color:white; width:100px; text-align:center">Codigo</td>
		<td style="border: 1px solid #666; background-color:white; width:70px; text-align:center">Pedido</td>
		<td style="border: 1px solid #666; background-color:white; width:70px; text-align:center">Recibido</td>
		

		</tr>

	</table>

EOF;

$pdf->writeHTML($bloque3, false, false, false, false, '');

// ---------------------------------------------------------

foreach ($productos as $key => $item) {

$itemProducto = "descripcion";
$valorProducto = $item["descripcion"];
$orden = null;

$respuestaProducto = ControladorProductos::ctrMostrarProductos($itemProducto, $valorProducto, $orden);

$valorUnitario = number_format($respuestaProducto["precio_venta"], 2);

$precioTotal = number_format($item["total"], 2);

$bloque4 = <<<EOF

	<table style="font-size:10px; padding:5px 10px;">

		<tr>
			
			<td style="border: 1px solid #666; color:#333; background-color:white; width:300px; text-align:center">
				$item[descripcion]
			</td>
			<td style="border: 1px solid #666; color:#333; background-color:white; width:100px; text-align:center">
				$respuestaProducto[codigo]
			</td>
			<td style="border: 1px solid #666; color:#333; background-color:white; width:70px; text-align:center">
				$item[cantidad]
			</td>

			<td style="border: 1px solid #666; color:#333; background-color:white; width:70px; text-align:center">$item[recibida]
			</td>

			


		</tr>

	</table>


EOF;

$pdf->writeHTML($bloque4, false, false, false, false, '');

}

// ---------------------------------------------------------





// ---------------------------------------------------------
//SALIDA DEL ARCHIVO 

//$pdf->Output('factura.pdf', 'D');
$pdf->Output('factura.pdf');

}

}

$factura = new imprimirFactura();
$factura -> codigo = $_GET["codigo"];
$factura -> traerImpresionFactura();

?>
