<?php

require_once "../../../controladores/compras.controlador.php";
require_once "../../../modelos/compras.modelo.php";

require_once "../../../controladores/productos.controlador.php";
require_once "../../../modelos/productos.modelo.php";

class imprimirFactura{

public $codigo;

public function traerImpresionFactura(){

//TRAEMOS LA INFORMACIÓN DE LA VENTA

$itemPedido = "id";
$respuestaCompra = ControladorCompras::ctrMostrarCompras($itemPedido, $_GET['codigo']);

$fecha = substr($respuestaCompra["fecha"],0,-8);
$fecha=date("d-m-Y",strtotime($fecha));
$productos = json_decode($respuestaCompra["productos"], true);
$destino = json_decode($respuestaCompra["destino"],true);
$total = number_format(round($respuestaCompra["total"],2),2);
$obs = $respuestaCompra["observacion"];
//REQUERIMOS LA CLASE TCPDF
//$proveedor = ControladorProveedores::ctrMostrarProveedores('id', $respuestaCompra["id_proveedor"])["nombre"];
require_once('tcpdf_include.php');

$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

$pdf->AddPage('P', 'A4');

//---------------------------------------------------------

$bloque1 = <<<EOF

	<table>
		
		<tr>
			
			<td style="width:250px; padding:45px;"><img src="images/logo_moon.png"></td>

			<td style="background-color:white; width:100px">
				
				<div style="font-size:8.5px; text-align:right; line-height:15px;">

				</div>

			</td>

		</tr>

		<tr>
			
			<td style="width:250px">
			CODIGO POSTAL: 5600
			<br>
			DIRECCION: Ballofet 2800
			<br>
			LOCALIDAD: San Rafael
			<br>
			PROVINCIA: Mendoza

			</td>
			<td style="background-color:white; width:40px">

				<div style="font-size:28.5px; text-align:right; line-height:25px;">
				<br>	
			
				</div>
				
			</td>

			
			<td style="background-color:white; width:350px; text-align:center;">
			
			  COMPRA Nº $respuestaCompra[id]
			<br>
			  FECHA: $fecha
			<br>
			</td>
		</tr>

	</table>

EOF;

$pdf->writeHTML($bloque1, false, false, false, false, '');

// ---------------------------------------------------------

$bloque2 = <<<EOF

	<table>
		
		<tr>
			
			<td style="width:540px"><img src="images/back.jpg"></td>
		
		</tr>

	</table>

	<table style="font-size:10px; padding:5px 10px;">
		<tr>
		
			<td style="border: 1px solid #666; background-color:white; width:540px">

				Proveedor: $proveedor

			</td>

		</tr>
		<tr>
		
			<td style="border: 1px solid #666; background-color:white; width:540px">

				Pedido: $respuestaCompra[usuarioPedido] 

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
		<tr><td style="border: 1px solid #666; background-color:white; width:540px; text-align:center"><b>Detalle de los productos comprados cantidades</b></td>
		</tr>

		<tr>
		
		<td style="border: 1px solid #666; background-color:white; width:260px; text-align:center">Producto</td>
		<td style="border: 1px solid #666; background-color:white; width:80px; text-align:center">Codigo</td>
		<td style="border: 1px solid #666; background-color:white; width:60px; text-align:center">Pedido</td>
		<td style="border: 1px solid #666; background-color:white; width:70px; text-align:center">Recibido</td>
		<td style="border: 1px solid #666; background-color:white; width:70px; text-align:center">Precio</td>
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
$precioCompra = number_format($item["precioCompra"], 2);
//$precioTotal = number_format($item["total"], 2);

$bloque4 = <<<EOF

	<table style="font-size:8px; padding:5px 10px;">

		<tr>
			
			<td style="border: 1px solid #666; color:#333; background-color:white; width:260px; text-align:center">
				$item[descripcion]
			</td>
			<td style="border: 1px solid #666; color:#333; background-color:white; width:80px; text-align:center">
				$respuestaProducto[codigo] 
			</td>
			<td style="border: 1px solid #666; color:#333; background-color:white; width:60px; text-align:center">
				$item[pedidos] 
			</td>
			<td style="border: 1px solid #666; color:#333; background-color:white; width:70px; text-align:center"> 
				$item[recibidos] 
			</td>
			<td style="border: 1px solid #666; color:#333; background-color:white; width:70px; text-align:center">
				 $ $precioCompra 
			</td>
		</tr>

	</table>

EOF;

$pdf->writeHTML($bloque4, false, false, false, false, '');

}
// ---------------------------------------------------------

$bloque5 = <<<EOF

	<table>
		
		<tr>
			<td style="border: 1px solid #666; color:#333; background-color:white; width:340px; height:22px; text-align:center">
				 TOTAL COMPRA 
			</td>
			<td style="border: 1px solid #666; color:#333; background-color:white; width:200px; height:22px; text-align:center">
				 $ $total 
			</td>
		</tr>

	</table>

EOF;
$pdf->writeHTML($bloque5, false, false, false, false, '');

// ---------------------------------------------------------

$bloque6 = <<<EOF

	<table>
		
		<tr>
			
			<td style="width:540px"><img src="images/back.jpg"></td>
		
		</tr>

	</table>

	<table style="font-size:10px; padding:5px 10px;">
	
		

	</table>

EOF;
$pdf->writeHTML($bloque6, false, false, false, false, '');

// ---------------------------------------------------------

$bloque7 = <<<EOF

	<table style="font-size:10px; padding:5px 10px;">

		<tr>
			
			<td style="border: 1px solid #666; color:#333; background-color:white; width:540px; text-align:center">
				OBSERVACIONES
			</td>
		</tr>
		<tr>
			<td style="border: 1px solid #666; color:#333; background-color:white; width:540px; text-align:center">
			$obs
			</td>			
		</tr>

	</table>


EOF;

$pdf->writeHTML($bloque7, false, false, false, false, '');

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
