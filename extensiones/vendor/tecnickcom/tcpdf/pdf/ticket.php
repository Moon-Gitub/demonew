<?php

require_once "../../../controladores/empresa.controlador.php";
require_once "../../../modelos/empresa.modelo.php";

require_once "../../../controladores/ventas.controlador.php";
require_once "../../../modelos/ventas.modelo.php";

require_once "../../../controladores/clientes.controlador.php";
require_once "../../../modelos/clientes.modelo.php";

require_once "../../../controladores/usuarios.controlador.php";
require_once "../../../modelos/usuarios.modelo.php";

require_once "../../../controladores/productos.controlador.php";
require_once "../../../modelos/productos.modelo.php";

class imprimirFactura{

public $item;
public $codigo;

public function traerImpresionFactura(){

$tiposCbtes = array(
0 => 'X',
1 => 'Factura A',
6 => 'Factura B', 
11 => 'Factura C',
//'Factura E' => 0, 
51 => 'Factura M',
2 => 'Nota Débito A',
7 => 'Nota Débito B',
12 => 'Nota Débito C',
//'Nota Débito E' => 0, 
52 => 'Nota Débito M',
3 => 'Nota Crédito A',
8 => 'Nota Crédito B',
13 => 'Nota Crédito C',
//'Nota Crédito E' => 0,
53 => 'Nota Crédito M',
4 => 'Recibo A',
9 => 'Recibo B',
15 => 'Recibo C',
//'Recibo E' => 0, 
54 => 'Recibo M',
'' => 'no definido'
);

$arrTipoDocumento = array(
96 => "DNI",
80 => "CUIT",
86 => "CUIL",
87 => "CDI",
89 => "LE",
90 => "LC",
92 => "En trámite",
93 => "Acta nacimiento",
94 => "Pasaporte",
91 => "CI extranjera",
99 => "Otro",
0 => "(no definido)");

$condIva = array(
1 => "IVA Responsable Inscripto ",
2 => "IVA Sujeto Exento ",
3 => "IVA Responsable no Inscripto ",
4 => "IVA no Responsable ",
5 => "Consumidor Final ",
6 => "Responsable Monotributo ",
7 => "Sujeto no Categorizado ",
8 => "Proveedor del Exterior ",
9 => "Cliente del Exterior ",
10 => "IVA Liberado – Ley Nº 19.640 ",
11 => "IVA Responsable Inscripto – Agente de Percepción ",
12 => "Pequeño Contribuyente Eventual ",
13 => "Monotributista Social ",
14 => "Pequeño Contribuyente Eventual Social",
''=>"(no definido)"
);

//TRAEMOS LA INFORMACIÓN DE LA EMPRESA
$respEmpresa = ModeloEmpresa::mdlMostrarEmpresa('empresa', 'id', 1);
$tipoIva = $condIva[$respEmpresa["condicion_iva"]];

//TRAEMOS LA INFORMACIÓN DE LA VENTA
$itemVenta = $this->item;
$codigoVenta = $this->codigo;
$respuestaVenta = ControladorVentas::ctrMostrarVentas($itemVenta, $codigoVenta);
$fecha = substr($respuestaVenta["fecha"],0,-8);
$productos = json_decode($respuestaVenta["productos"], true);
$neto = number_format($respuestaVenta["neto"],2);
$impuesto = number_format($respuestaVenta["impuesto"],2);
$total = number_format($respuestaVenta["total"],2);

//TRAEMOS LA INFORMACIÓN DEL CLIENTE
$respuestaCliente = ControladorClientes::ctrMostrarClientes('id', $respuestaVenta["id_cliente"]);
$tipoDocumento = $arrTipoDocumento[$respuestaCliente["tipo_documento"]];
$tipoIvaCliente = $condIva[$respuestaCliente["condicion_iva"]];

if($respuestaVenta["cbte_tipo"] == "0") {
	$tipoVtaLetra = "X";
	$tipoCodigo = "";
	$tipoVta = "Documento no valido como factura";
	$datosPrecios = '<td style="background-color:white; width:80px; text-align:center">Precio Unit.</td>
			<td style="background-color:white; width:80px; text-align:center">Total</td>';
	$numCte = str_pad($respuestaVenta["codigo"], 8, "0", STR_PAD_LEFT);

	$vtoCae ="-";
	$cae ="-";
	$barra = "";

} else {

	$tipoVta = $tiposCbtes[$respuestaVenta["cbte_tipo"]];
	$tipoCodigo = "Cod. ". $respuestaVenta["cbte_tipo"];
	$tipoVtaLetra = "C";
	$datosPrecios = '<td style="background-color:white; width:80px; text-align:center">Precio Unit.</td>
			<td style="background-color:white; width:80px; text-align:center">Total</td>';
	$factura = ControladorVentas::ctrVentaFacturadaDatos($respuestaVenta["id"]);
	$numCte = str_pad($factura["nro_cbte"], 8, "0", STR_PAD_LEFT);

	$cuit = $respEmpresa["cuit"];
	$tipoComprobante = str_pad($respuestaVenta["cbte_tipo"], 3, "0", STR_PAD_LEFT);
	$cae = 'CAE: ' . $factura["cae"];
	$vtoCae = 'Vto. CAE: ' .  $factura["fec_vto_cae"];
	$ptoVta = str_pad($respuestaVenta["pto_vta"], 5, "0", STR_PAD_LEFT);
	$barra = $cuit . $tipoComprobante . $ptoVta . $cae . $vtoCae;

}

$ptoVta = str_pad($respuestaVenta["pto_vta"], 5, "0", STR_PAD_LEFT);

//REQUERIMOS LA CLASE TCPDF

require_once('tcpdf_include.php');

$tamanoCantProd = 5 * count($productos);

$tamanoAltura = 100 + $tamanoCantProd;

//$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
$pdf = new TCPDF('P', 'mm', array(80, $tamanoAltura), true, 'UTF-8', false);

$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

$pdf->AddPage();

//---------------------------------------------------------

$bloque1 = <<<EOF

	<div style="font-size:9px; padding:0px;">

		<div style="text-align: center;"><b> $respEmpresa[razon_social]</b></div> <br>
		<span>$respEmpresa[domicilio]</span> <br>
		<span>Tel.: $respEmpresa[telefono]</span> <br>
		<span>Localidad: $respEmpresa[localidad] - C.P.: $respEmpresa[codigo_postal]</span><br>
		<span>CUIT: $respEmpresa[cuit]</span> -	<span>II.BB.: $respEmpresa[numero_iibb] </span><br>
		<span>Cond. I.V.A.: $tipoIva </span><br>
		<br>
		<span>Fecha: $fecha</span> <br>
		<span>$tipoVta</span> <br>
		<span><b>Pto. Vta.:</b> $ptoVta <b>N° Cbte:</b> $numCte</span> <br><br>
		<span><b>Tipo Doc.: $tipoDocumento :</b> $respuestaCliente[documento] </span> <br>
		<span>$respuestaCliente[nombre] </span> 
		<br>
		<span><b>Domicilio: </b> $respuestaCliente[direccion] </span> - <span> <b>Condición I.V.A.:</b> $tipoIvaCliente </span> 
		
		<div style="text-align: center"><b>Detalle:</b></div>
		
		<table style="font-size:8px;">
		<tr>
			<td style="width:25px; text-align:center;">Cant.</td>
			<td style="width:100px;">Descrip.</td>
			<td style="width:45px; text-align:left;">Total</td>
		</tr>

		</table>
EOF;

$pdf->writeHTML($bloque1, false, false, false, false, '');

// ---------------------------------------------------------

foreach ($productos as $key => $item) {

// $valorUnitario = number_format($item["precioVenta"], 2);

$precioTotal = number_format($item["total"], 2);

$bloque2 = <<<EOF

<table style="font-size:8px;">

	<tr>
		<td style="width:25px; text-align:center;"> $item[cantidad] </td>
		<td style="width:100px;">$item[descripcion] </td>
		<td style="width:45px; text-align:left;">$ $precioTotal</td>

	</tr>

</table>

EOF;

$pdf->writeHTML($bloque2, false, false, false, false, '');

}

// ---------------------------------------------------------

$bloque3 = <<<EOF

<table style="font-size:9px; text-align:right">

	<tr>
		<td style="width:165px;">
			 -------------
		</td>

	</tr>

	<tr>
	
		<td style="width:80px;">
			 TOTAL: 
		</td>

		<td style="width:80px;">
			$ $total
		</td>

	</tr>
</table>

EOF;

$pdf->writeHTML($bloque3, false, false, false, false, '');


$bloqueCAE = <<<EOF

<table style="font-size:8px;">
	<tr>
		<td style="font-style: italic">
			$cae		
		</td>
	</tr>
	<tr>
		<td style="font-style: italic">
			$vtoCae
		</td>
	</tr>
</table>

EOF;

$pdf->writeHTML($bloqueCAE, false, false, false, false, '');


// ---------------------------------------------------------
//SALIDA DEL ARCHIVO 

//$pdf->Output('factura.pdf', 'D');
$pdf->Output('factura.pdf');

}

}

//Vengo desde ventas
if(isset($_GET["codigo"])){
$factura = new imprimirFactura();
$factura -> item = "codigo";
$factura -> codigo = $_GET["codigo"];
$factura -> traerImpresionFactura();
}

//Vengo desde mesas
if(isset($_GET["idVenta"])){
$factura = new imprimirFactura();
$factura -> item = "id";
$factura -> codigo = $_GET["idVenta"];
$factura -> traerImpresionFactura();
}


?>