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
$total = round($respuestaCompra["total"],2);
$totalNeto = round($respuestaCompra["totalNeto"],2);
$iva = round($respuestaCompra["iva"],2);
$precepcionesIngresosBrutos = round($respuestaCompra["precepcionesIngresosBrutos"],2);
$precepcionesIva = round($respuestaCompra["precepcionesIva"],2);
$precepcionesGanancias = round($respuestaCompra["precepcionesGanancias"],2);
$impuestoInterno = round($respuestaCompra["impuestoInterno"],2);
$diferenciaPago = round($total - $totalNeto,2);

//REQUERIMOS LA CLASE TCPDF

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
		
			<td style="border: 1px solid #666; background-color:white; width:180px">

				<b>Pedido: $respuestaCompra[usuarioPedido]</b> 

			</td>

			<td style="border: 1px solid #666; background-color:white; width:180px; text-align:left">
			
				<b>Ingreso: $respuestaCompra[usuarioDeposito]</b>

			</td>

			<td style="border: 1px solid #666; background-color:white; width:180px; text-align:left">
			
				<b>Validar: $respuestaCompra[usuarioConfirma]</b>

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
		
		<td style="border: 1px solid #666; background-color:white; width:235px; text-align:center"><b>Producto</b></td>
		<td style="border: 1px solid #666; background-color:white; width:60px; text-align:center"><b>Pedido</b></td>
		<td style="border: 1px solid #666; background-color:white; width:65px; text-align:center"><b>Recibido</b></td>
		<td style="border: 1px solid #666; background-color:white; width:60px; text-align:center"><b>Factura</b></td>	
		<td style="border: 1px solid #666; background-color:white; width:60px; text-align:center"><b>P. Compra</b></td>
		<td style="border: 1px solid #666; background-color:white; width:60px; text-align:center"><b>P. Factura</b></td>
		
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

	<table style="font-size:8px; padding:5px 10px;">

		<tr>
			<td style="border: 1px solid #666; color:#333; background-color:white; width:235x; text-align:center"><b>$item[descripcion]</b></td>
			<td style="border: 1px solid #666; color:#333; background-color:white; width:60px; text-align:center"><b>$item[pedidos]</b></td>
			<td style="border: 1px solid #666; color:#333; background-color:white; width:65px; text-align:center"><b>$item[recibidos]</b></td>
			<td style="border: 1px solid #666; color:#333; background-color:white; width:60px; text-align:center"><b>$item[articulosFactura]</b></td>
			<td style="border: 1px solid #666; color:#333; background-color:white; width:60px; text-align:center"><b>$item[precioCompra]</b></td>
			<td style="border: 1px solid #666; color:#333; background-color:white; width:60px; text-align:center"><b>$item[precioCompraPedido]</b></td>
		</tr>

	</table>


EOF;

$pdf->writeHTML($bloque4, false, false, false, false, '');

}

$bloque5 = <<<EOF

	<table>
		
		<tr>
			<td style="border: 1px solid #666; color:#333; background-color:white; width:420px; height:22px; text-align:center">
				 TOTAL COMPRA 
			</td>
			<td style="border: 1px solid #666; color:#333; background-color:white; width:120px; height:22px; text-align:center">
				 $ $total
			</td>
		</tr>

	</table>

EOF;
$pdf->writeHTML($bloque5, false, false, false, false, '');

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

$bloque7 = <<<EOF

	<table style="font-size:10px; padding:5px 10px;">
		<tr><td style="border: 1px solid #666; background-color:white; width:540px; text-align:center"><b>Detalle Transporte</b></td>
		</tr>

		<tr>
		
		<td style="border: 1px solid #666; background-color:white; width:270px; text-align:center"><b>Empresa Nombre</b></td>
		<td style="border: 1px solid #666; background-color:white; width:270px; text-align:center"><b>Pedido Numero</b></td>

		</tr>
		<tr>
		
<td style="border: 1px solid #666; background-color:white; width:270px; text-align:center"><b>$respuestaCompra[fleteEmpresa]</b></td>
		<td style="border: 1px solid #666; background-color:white; width:270px; text-align:center"><b>$respuestaCompra[fleteNumero]</b></td>

		</tr>

	</table>

EOF;

$pdf->writeHTML($bloque7, false, false, false, false, '');

$bloque8 = <<<EOF

	<table style="font-size:10px; padding:5px 10px;">
		<tr><td style="border: 1px solid #666; background-color:white; width:540px; text-align:center"><b>Ingreso de mercaderia observacion: $respuestaCompra[observacion]</b></td>
		</tr>

	</table>

EOF;

$pdf->writeHTML($bloque8, false, false, false, false, '');

// ---------------------------------------------------------

$bloque9 = <<<EOF

	<table>
		
		<tr>
			
			<td style="width:540px"><img src="images/back.jpg"></td>
		
		</tr>

	</table>

	<table style="font-size:10px; padding:5px 10px;">
	
		

	</table>

EOF;
$pdf->writeHTML($bloque9, false, false, false, false, '');

$bloque10 = <<<EOF

	<table style="font-size:10px; padding:5px 10px;">
		<tr><td style="border: 1px solid #666; background-color:white; width:540px; text-align:center"><b>Detalle Factura Numero</b></td>
		</tr>

		<tr>
		
		<td style="border: 1px solid #666; background-color:white; width:270px; text-align:center"><b>$respuestaCompra[numeroFactura]</b></td>
		<td style="border: 1px solid #666; background-color:white; width:270px; text-align:center"><b>$respuestaCompra[fechaEmision]</b></td>

		</tr>

	</table>

EOF;

$pdf->writeHTML($bloque10, false, false, false, false, '');
$diferenciaCantidad = 0;
foreach ($productos as $key => $item) {

$itemProducto = "descripcion";
$valorProducto = $item["descripcion"];
$orden = null;

$respuestaProducto = ControladorProductos::ctrMostrarProductos($itemProducto, $valorProducto, $orden);

$valorUnitario = number_format($respuestaProducto["precio_venta"], 2);
$precioPedido = number_format($item["precioCompraPedido"], 2);
$precioCompra = number_format($item["precioCompra"], 2);

$diferenciaCantidad =  ($diferenciaCantidad + (($item["articulosFactura"] - $item["recibidos"])*$item["precioCompra"]));
$diferencia = $item["precioCompra"] - $item["precioCompraPedido"];
$totalDiferencia = $totalDiferencia + $diferencia * $item["recibidos"];

$totalDiferenciaCalculada = $diferenciaCantidad + $totalDiferencia;

}
$totalPagar = round(($total ),2);
// ---------------------------------------------------------


$bloque111 = <<<EOF

		<table style="font-size:10px; padding:5px 10px;">
		<tr>
			
			<td style="border: 1px solid #666; color:#333; background-color:white; width:540px; text-align:center">
				<b>DETALLE ECONOMICO</b>
			</td>
		</tr>	
		<tr>
			<td style="border: 1px solid #666; color:#333; background-color:white; width:77px; text-align:center">
				<b>Neto</b>
			</td>
			<td style="border: 1px solid #666; color:#333; background-color:white; width:77px; text-align:center">
				<b>IVA</b>
			</td>
			<td style="border: 1px solid #666; color:#333; background-color:white; width:77px; text-align:center">
				<b>Percepciones Ingresos Brutos</b>
			</td>
			<td style="border: 1px solid #666; color:#333; background-color:white; width:77px; text-align:center">
				<b>Percepciones IVA</b>
			</td>
			<td style="border: 1px solid #666; color:#333; background-color:white; width:77px; text-align:center">
				<b>Percepciones Ganancias</b>
			</td>
			<td style="border: 1px solid #666; color:#333; background-color:white; width:77px; text-align:center">
				<b>Impuestos Internos</b>
			</td>
			<td style="border: 1px solid #666; color:#333; background-color:white; width:78px; text-align:center">
				<b>Total</b>
			</td>
		</tr>
		<tr>
			<td style="border: 1px solid #666; color:#333; background-color:white; width:77px; text-align:center">
				<b>$ $totalNeto</b>
			</td>
			<td style="border: 1px solid #666; color:#333; background-color:white; width:77px; text-align:center">
				<b>$ $iva</b>
			</td>
			<td style="border: 1px solid #666; color:#333; background-color:white; width:77px; text-align:center">
				<b>$ $precepcionesIngresosBrutos</b>
			</td>
			<td style="border: 1px solid #666; color:#333; background-color:white; width:77px; text-align:center">
				<b>$ $precepcionesIva</b>
			</td>
			<td style="border: 1px solid #666; color:#333; background-color:white; width:77px; text-align:center">
				<b>$ $precepcionesGanancias</b>
			</td>
			<td style="border: 1px solid #666; color:#333; background-color:white; width:77px; text-align:center">
				<b>$ $impuestoInterno</b>
			</td>
			<td style="border: 1px solid #666; color:#333; background-color:white; width:78px; text-align:center">
				<b>$ $totalPagar</b>
			</td>			
		</tr>

	</table>


EOF;

$pdf->writeHTML($bloque111, false, false, false, false, '');
if($totalDiferenciaCalculada!=0){
$totalaPagar = number_format(round(($totalPagar-$totalDiferenciaCalculada),2),2);
$bloque112 = <<<EOF

	<table style="font-size:10px; padding:5px 10px;">
		<tr>
			
			<td style="border: 1px solid #666; color:#333; background-color:white; width:540px; text-align:center">
				<b>DETALLE NOTA DE CREDITO</b>
			</td>
		</tr>	
		<tr>
			<td style="border: 1px solid #666; color:#333; background-color:white; width:540px; text-align:center">
				<b>$ $totalDiferenciaCalculada</b>
			</td>
		</tr>
		<tr>
			
			<td style="border: 1px solid #666; color:#333; background-color:white; width:540px; text-align:center">
				<b>TOTAL A PAGAR</b>
			</td>
		</tr>	
		<tr>
			<td style="border: 1px solid #666; color:#333; background-color:white; width:540px; text-align:center">
				<b>$ $totalaPagar</b>
			</td>
		</tr>

	</table>


EOF;

$pdf->writeHTML($bloque112, false, false, false, false, '');
}
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
