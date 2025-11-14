<?php

require_once "../../../../../controladores/compras.controlador.php";
require_once "../../../../../modelos/compras.modelo.php";
require_once "../../../../../controladores/productos.controlador.php";
require_once "../../../../../modelos/productos.modelo.php";
require_once "../../../../../controladores/empresa.controlador.php";
require_once "../../../../../modelos/empresa.modelo.php";

require_once '../../../autoload.php';

class imprimirFactura{

public $codigo;

public function traerImpresionFactura(){

//DATOS EMPRESA
$respEmpresa = ControladorEmpresa::ctrMostrarEmpresa('id', 1);

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
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
// Configuración del documento
$pdf->SetCreator('Posmoon');
$pdf->SetTitle($respEmpresa["razon_social"]);
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

$pdf->AddPage('P', 'A4');

//---------------------------------------------------------
$bloque1 = <<<EOF
	<table border="1">
		<tr>
			<td style="width:560px; text-align: center;"> COMPRA INGRESADA</td>
		</tr>
	</table>
	<table border="1" >
		<tr style="padding: 0px;">
			<td style="width:260px; text-align: center; border-style:solid; border-width:2px; border-bottom-color:rgb(255,255,255);"> 
				<h2>$respEmpresa[razon_social]</h2>
			</td>
			<td style="width:40px; text-align:center">
			<div><span style="font-size:28.5px;">X</span></div>	
			</td>
			<td style="width:260px; text-align: center; border-style:solid; border-width:2px; border-bottom-color:rgb(255,255,255);"> 
				Orden de compra
			</td>
		</tr>
	</table>
	<table border="1" style="padding: 10px">
		<tr>
			<td style="width:280px; font-size:10px; text-align: left;">
				<br>
				<span><b>Direccion:</b> $respEmpresa[domicilio]</span> <br>
				<span><b>Telefono:</b> $respEmpresa[telefono]</span> <br>
				<span><b>Localidad:</b> $respEmpresa[localidad] - C.P.: $respEmpresa[codigo_postal]</span><br>
				<span><b>Defensa al Consumidor Mza. 08002226678</b></span> 
			</td>
			<td style="width:280px; font-size:10px; text-align: left">
				<div style="padding-top:5px">
					<span><b>N° Cbte:</b> $respuestaCompra[id]</span> <br>
					<span><b>Fecha Emisión:</b> $fecha </span><br>
					<span><b>CUIT:</b> $respEmpresa[cuit] </span><br>
					<span><b>II.BB.:</b> $respEmpresa[numero_iibb] </span><br>
					<span><b>Inic. Actividad:</b> $respEmpresa[inicio_actividades] </span>
				</div>
			</td>
		</tr>
	</table>
	
    <table style="padding: 5px">
		<tr>
			<td style="width:560px; font-size:12px; text-align: left;">
				<br>
				<span>PROVEEDOR: <b>Nombre / Razón Social :</b> $proveedor[nombre] </span> - <span> <b> $proveedor[cuit] :</b> </span>  
				<br>
				<span><b>Domicilio: </b> $proveedor[direccion] </span>  
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
		
			<td style="border: 1px solid #666; background-color:white; width:270px">

				<b>Orden De Compra: $respuestaCompra[usuarioPedido]</b> 

			</td>

			<td style="border: 1px solid #666; background-color:white; width:270px; text-align:left">
			
				<b>Validado De Compra: $respuestaCompra[usuarioConfirma]</b>

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
if($respuestaCompra["tipo"]=="factura"){
$bloque10 = <<<EOF

	<table style="font-size:10px; padding:5px 10px;">
		<tr><td style="border: 1px solid #666; background-color:white; width:540px; text-align:center"><b>Detalle Factura Numero</b></td>
		</tr>

		<tr>
		
		<td style="border: 1px solid #666; background-color:white; width:270px; text-align:center"><b>Numero Factura $respuestaCompra[numeroFactura]</b></td>
		<td style="border: 1px solid #666; background-color:white; width:270px; text-align:center"><b>Fecha De Emision $respuestaCompra[fechaEmision]</b></td>

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
$diferencia = number_format($item["precioCompra"] - $item["precioCompraPedido"], 2);
$totalDiferencia = $diferencia * $item["recibidos"];

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
$bloque112 = <<<EOF

	<table style="font-size:10px; padding:5px 10px;">
		
		<tr>
			
			<td style="border: 1px solid #666; color:#333; background-color:white; width:540px; text-align:center">
				<b>TOTAL A PAGAR</b>
			</td>
		</tr>	
		<tr>
			<td style="border: 1px solid #666; color:#333; background-color:white; width:540px; text-align:center">
				<b>$ $totalPagar</b>
			</td>
		</tr>

	</table>


EOF;

$pdf->writeHTML($bloque112, false, false, false, false, '');
}
}else{
$bloqueRemito = <<<EOF

	<table style="font-size:10px; padding:5px 10px;">
		<tr><td style="border: 1px solid #666; background-color:white; width:540px; text-align:center"><b>Detalle Remito Compra</b></td>
		</tr>

		<tr>
		
		<td style="border: 1px solid #666; background-color:white; width:270px; text-align:center"><b>Numero De Remito</b></td>
		<td style="border: 1px solid #666; background-color:white; width:270px; text-align:center"><b>$respuestaCompra[remitoNumero]</b></td>

		</tr>

	</table>

EOF;

$pdf->writeHTML($bloqueRemito, false, false, false, false, '');	
$bloqueRemitoPago = <<<EOF

	<table style="font-size:10px; padding:5px 10px;">
		
		<tr>
			
			<td style="border: 1px solid #666; color:#333; background-color:white; width:540px; text-align:center">
				<b>TOTAL A PAGAR</b>
			</td>
		</tr>	
		<tr>
			<td style="border: 1px solid #666; color:#333; background-color:white; width:540px; text-align:center">
				<b>$ $respuestaCompra[total]</b>
			</td>
		</tr>

	</table>


EOF;

$pdf->writeHTML($bloqueRemitoPago, false, false, false, false, '');
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
