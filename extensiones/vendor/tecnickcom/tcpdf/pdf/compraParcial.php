<?php

require_once "../../../controladores/compras.controlador.php";
require_once "../../../modelos/compras.modelo.php";

require_once "../../../controladores/productos.controlador.php";
require_once "../../../modelos/productos.modelo.php";

require_once "../../../controladores/proveedores.controlador.php";
require_once "../../../modelos/proveedores.modelo.php";

require_once "../../../controladores/empresa.controlador.php";
require_once "../../../modelos/empresa.modelo.php";

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
$total = number_format(round($respuestaCompra["total"],2),2);

$proveedor = ControladorProveedores::ctrMostrarProveedores('id', $respuestaCompra["id_proveedor"])["nombre"];
//REQUERIMOS LA CLASE TCPDF

require_once('tcpdf_include.php');

$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

$pdf->AddPage('P', 'A4');

//---------------------------------------------------------
$bloque1 = <<<EOF
	<table border="1">
		<tr>
			<td style="width:560px; text-align: center;"> ORDEN DE COMPRA</td>
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
//SALIDA DEL ARCHIVO 

//$pdf->Output('factura.pdf', 'D');
$pdf->Output('factura.pdf');

}

}

$factura = new imprimirFactura();
$factura -> codigo = $_GET["codigo"];
$factura -> traerImpresionFactura();

?>
