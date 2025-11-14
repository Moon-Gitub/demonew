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

class imprimirComprobante{

public $codigo;
public $email;

public function traerImpresionComprobante(){

$respEmpresa = ModeloEmpresa::mdlMostrarEmpresa('empresa', 'id', 1);

$tiposCbtes = array(
0 => 'X',
1 => 'Factura A',
6 => 'Factura B', 
11 => 'Factura C',
51 => 'Factura M',
2 => 'Nota Débito A',
7 => 'Nota Débito B',
12 => 'Nota Débito C',
52 => 'Nota Débito M',
3 => 'Nota Crédito A',
8 => 'Nota Crédito B',
13 => 'Nota Crédito C',
53 => 'Nota Crédito M',
4 => 'Recibo A',
9 => 'Recibo B',
15 => 'Recibo C',
54 => 'Recibo M',
'' => 'no definido');

$tiposCbtesLetras = array(
0 => 'X',
1 => 'A',
6 => 'B', 
11 => 'C',
51 => 'M',
2 => 'A',
7 => 'B',
12 => 'C',
52 => 'M',
3 => 'A',
8 => 'B',
13 => 'C',
53 => 'M',
4 => 'A',
9 => 'B',
15 => 'C',
54 => 'M',
'' => 'X');

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
0 => "Consumidor Final",
1 => "IVA Responsable Inscripto",
4 => "IVA Sujeto Exento",
5 => "Consumidor Final",
6 => "Responsable Monotributo",
7 => "Sujeto no Categorizado",
8 => "Proveedor del Exterior",
9 => "Cliente del Exterior",
10 => "IVA Liberado – Ley Nº 19.640",
13 => "Monotributista Social",
15 => "IVA no alcanzado",
16 => "Monotributo Trabajador Independiente Promovido",
''=>"(no definido)");

//TRAEMOS LA INFORMACIÓN DE LA VENTA
$respuestaVenta = ControladorVentas::ctrMostrarVentas('codigo', $_GET['codigo']);
$facturada = ControladorVentas::ctrVentaFacturada($respuestaVenta["id"]);
$respuestaCliente = ControladorClientes::ctrMostrarClientes('id', $respuestaVenta["id_cliente"]);

if($_GET["email"] == "") {

	if($respuestaCliente["email"] == ""){
	    echo 'ERROR. El cliente no tiene una cuenta de correo electronico agregada.';
	    return false;

	} else {
		$direccionMail = $respuestaCliente["email"];

	}

} else {
	$direccionMail = $_GET['email'];
}

$tipoDocumento = $arrTipoDocumento[$respuestaCliente["tipo_documento"]];
$tipoIva = $condIva[$respEmpresa["condicion_iva"]];
$tipoIvaCliente = $condIva[$respuestaCliente["condicion_iva"]];
$fecha = substr($respuestaVenta["fecha"],0,-8);
$fecha = date("d-m-Y",strtotime($fecha));
$productos = json_decode($respuestaVenta["productos"], true);
$tamanioProd = count($productos);
$total = number_format($respuestaVenta["total"],2, ',', '.');
$observaciones = $respuestaVenta["observaciones"];
$subTotal = number_format($respuestaVenta["neto"],2, ',', '.');
$neto_grav = number_format($respuestaVenta["neto_gravado"],2, ',', '.');
$jsnPago = json_decode($respuestaVenta["metodo_pago"], true);

//$descuentos = $jsnPago[0]["descuento"] * $respuestaVenta["neto"] / 100;
//$descuentos = $respuestaVenta["descuento"] * $respuestaVenta["neto"] / 100;
$descuentos = number_format(0, 2, ',','.');

if($respuestaVenta["cbte_tipo"] == "0") {

$tipoVtaLetra = "X";
$tipoCodigo = "";
$tipoVta = "<h3>Documento no valido como factura</h3>";
$numCte = str_pad($respuestaVenta["codigo"], 8, "0", STR_PAD_LEFT);
$vtoCae ="-";
$cae ="-";

} else {

$factura = ControladorVentas::ctrVentaFacturadaDatos($respuestaVenta["id"]);
$jsonQR = '{"ver":1,"fecha":"'.date('Y-m-d', strtotime($respuestaVenta["fecha"])).'","cuit":'.$respEmpresa["cuit"].',"ptoVta":'.$respuestaVenta["pto_vta"].',"tipoCmp":'.$respuestaVenta["cbte_tipo"].',"nroCmp":'.$factura["nro_cbte"].',"importe":'.$respuestaVenta["total"].',"moneda":"PES","ctz":1,"tipoDocRec":'.$respuestaCliente["tipo_documento"].',"nroDocRec":'.$respuestaCliente["documento"].',"tipoCodAut":"E","codAut":'.$factura["cae"].'}';
$jsonQRBase64 = 'https://www.afip.gob.ar/fe/qr/?p=' . base64_encode($jsonQR);
$tipoVta = "<h2>" . $tiposCbtes[$respuestaVenta["cbte_tipo"]] ."</h2>";
$tipoCodigo = "Cod. ". $respuestaVenta["cbte_tipo"];
$tipoVtaLetra = $tiposCbtesLetras[$respuestaVenta["cbte_tipo"]];
$numCte = str_pad($factura["nro_cbte"], 8, "0", STR_PAD_LEFT);
$cuit = $respEmpresa["cuit"];
$tipoComprobante = str_pad($respuestaVenta["cbte_tipo"], 3, "0", STR_PAD_LEFT);
$cae = $factura["cae"];
$vtoCae = $factura["fec_vto_cae"];

}

$ptoVta = str_pad($respuestaVenta["pto_vta"], 5, "0", STR_PAD_LEFT);
$fecEmi = date('d/m/Y', strtotime($respuestaVenta["fecha"]));

//REQUERIMOS LA CLASE TCPDF
require_once('tcpdf_include.php');
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

$ubicacionCabecera  = 7;
$ubicacionDetalle   = 80;
$ubicacionFooter    = 250;
$datosFact = []; //Array de datos a imprimir
$detalleEnTabla = ""; //filas en tabla para armar detalle
$subTotalPorPagina = 0;
$transportePorPagina = 0;
$valorY = 0;
$nuevaPagina = true;
$imprimoCabeceraDetalle = true;
$numPaginaActual = 0;
$ultimoProducto = count($productos);

$bloqueCabeceraOriginal = <<<EOF
	<table border="1">
		<tr>
			<td style="width:560px; text-align: center;"> ORIGINAL</td>
		</tr>
	</table>
	<table border="1" >
		<tr style="padding: 0px;">
			<td style="width:260px; text-align: center; border-style:solid; border-width:2px; border-bottom-color:rgb(255,255,255);"> 
				<h2>$respEmpresa[razon_social]</h2>
			</td>
			<td style="width:40px; text-align:center">
				<div>
					<span style="font-size:28.5px;">$tipoVtaLetra</span>
					<span style="font-size:10px;">$tipoCodigo</span>
				</div>
			</td>
			<td style="width:260px; text-align: center; border-style:solid; border-width:2px; border-bottom-color:rgb(255,255,255);"> 
				$tipoVta
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
				<span><b>Cond. I.V.A.:</b> $tipoIva </span><br>
				<span><b>Defensa al Consumidor Mza. 08002226678</b></span> 
			</td>
			<td style="width:280px; font-size:10px; text-align: left">
				<div style="padding-top:5px">
					<span><b>N° Cbte:</b> $ptoVta - $numCte</span> <br>
					<span><b>Fecha Emisión:</b> $fecEmi </span><br>
					<span><b>CUIT:</b> $respEmpresa[cuit] </span><br>
					<span><b>II.BB.:</b> $respEmpresa[numero_iibb] </span><br>
					<span><b>Inic. Actividad:</b> $respEmpresa[inicio_actividades] </span>
				</div>
			</td>
		</tr>
	</table>
	<table border="1" style="padding-left: 5px">
		<tr>
			<td style="width:186px; font-size:8px; text-align: left;">
				<br>
				<span><b>Fecha Desde:</b> $respuestaVenta[fec_desde]</span> <br>
			</td>
			<td style="width:187px; font-size:8px; text-align: left;">
				<br>
				<span><b>Fecha Hasta:</b> $respuestaVenta[fec_hasta]</span> <br>
			</td>
			<td style="width:187px; font-size:8px; text-align: left;">
				<br>
				<span><b>Fecha Vto.:</b> $respuestaVenta[fec_vencimiento]</span> <br>
			</td>			
		</tr>
	</table>
    <table border="1" style="padding: 5px">
		<tr>
			<td style="width:560px; font-size:8px; text-align: left;">
				<br>
				<span><b>Tipo Doc.: $tipoDocumento :</b> $respuestaCliente[documento] </span> - <span> <b>Nombre / Razón Social :</b> $respuestaCliente[nombre] </span> 
				<br>
				<span><b>Domicilio: </b> $respuestaCliente[direccion] </span> - <span> <b>Condición I.V.A.:</b> $tipoIvaCliente </span> 
			</td>
		</tr>
	</table>
EOF;

//RECORRO TODOS LOS PRODUCTOS PARA ARMAR DETALLE
foreach ($productos as $key => $value) {

if($nuevaPagina){
$pdf->AddPage('P', 'A4');
$numPaginaActual++;
$pdf->SetY($ubicacionDetalle);
$nuevaPagina = false;
if($transportePorPagina != 0){
$bloqueTransporte = <<<EOF
    <table>
		<tr style="font-weight: bold">
			<td style="width:380px;">
			</td>
			<td style="width:90px; font-size:10px; text-align: rigth;">
				TRANSPORTE: $
			</td>
			<td style="width:90px; font-size:10px; text-align: left;">
				$transportePorPagina
			</td>
		</tr>
	</table>
EOF;
$pdf->writeHTML($bloqueTransporte, false, false, false, false, '');
$transportePorPagina = 0;
$pdf->SetY($ubicacionDetalle + 7);
    
}
$imprimoCabeceraDetalle = true;
}

///////////////DETALLES
$getProducto        = ControladorProductos::ctrMostrarProductoXId($value["id"]);
$formatCantidad     = number_format($value["cantidad"],2,',','.');
$formatPrecioUnit   = '$ ' . number_format($value["precio"],2,',','.');
$formatTotal        = '$ ' . number_format($value["total"],2,',','.');
$subTotalPorPagina += $value["total"];

//DISEÑO DETALLE DEPENDIENDO DEL TIPO DE COMPROBANTE
if ($respuestaVenta["cbte_tipo"] == 1 || $respuestaVenta["cbte_tipo"] == 2 || $respuestaVenta["cbte_tipo"] == 3 || $respuestaVenta["cbte_tipo"] == 4) {

if($imprimoCabeceraDetalle){
//---------------------CABECERA DETALLE A
$bloqueDetalleCab = <<<EOF
	<table border="1" style="padding: 5px">
		<tr style="background-color: #f4f4f4">
			<td style="width:40px; font-size:8px; text-align: center;">
				<span><b>Cant.</b></span> 
			</td>
			<td style="width:310px; font-size:8px; text-align: center;">
				<span><b>Detalle</b></span> 
			</td>
			<td style="width:80px; font-size:8px; text-align: center;">
				<span><b>Unit.</b></span> 
			</td>
			<td style="width:50px; font-size:8px; text-align: center;">
				<span><b>IVA %</b></span> 
			</td>
			<td style="width:80px; font-size:8px; text-align: center; background-color">
				<span><b>Total</b></span> 
			</td>
		</tr>
	</table>
EOF;
$pdf->writeHTML($bloqueDetalleCab, false, false, false, false, '');
$imprimoCabeceraDetalle = false;
}

//--------------------- DETALLE COMPROBANTE A
$bloqueDetalle = <<<EOF
	<table style=" padding: 2px; ">
	    <tr>
			<td style="width:40px; font-size:8px; text-align: center;">
				<span>$formatCantidad</span> 
			</td>			
			<td style="width:310px; font-size:8px; text-align: left;">
				<span>$value[descripcion]</span> 
			</td>
			<td style="width:80px; font-size:8px; text-align: left;">
				<span>$formatPrecioUnit</span> 
			</td>
			<td style="width:50px; font-size:8px; text-align: left;">
				<span>$getProducto[tipo_iva]</span> 
			</td>
			<td style="width:80px; font-size:8px; text-align: left;">
				<span>$formatTotal</span> 
			</td>
		</tr>
	</table>
EOF;
$pdf->writeHTML($bloqueDetalle, false, false, false, false, '');

} else {

if($imprimoCabeceraDetalle){
//--------------------- CABECERA DETALLE B | C | X
$bloqueDetalleCab = <<<EOF
	<table border="1" style="padding: 5px">
		<tr style="background-color: #f4f4f4">
			<td style="width:50px; font-size:8px; text-align: center;">
				<span><b>Cant.</b></span> 
			</td>			
			<td style="width:350px; font-size:8px; text-align: center;">
				<span><b>Detalle</b></span> 
			</td>
			<td style="width:80px; font-size:8px; text-align: center;">
				<span><b>Unit.</b></span> 
			</td>
			<td style="width:80px; font-size:8px; text-align: center; background-color">
				<span><b>Total</b></span> 
			</td>
		</tr>
	</table>
EOF;
$pdf->writeHTML($bloqueDetalleCab, false, false, false, false, '');
$imprimoCabeceraDetalle = false;

}

$bloqueDetalle = <<<EOF
	<table style=" padding: 2px; ">
	    <tr>
			<td style="width:50px; font-size:8px; text-align: center;">
				<span>$formatCantidad</span> 
			</td>			
			<td style="width:350px; font-size:8px; text-align: left;">
				<span>$value[descripcion]</span> 
			</td>
			<td style="width:80px; font-size:8px; text-align: left;">
				<span>$formatPrecioUnit</span> 
			</td>
			<td style="width:80px; font-size:8px; text-align: left;">
				<span>$formatTotal</span> 
			</td>
		</tr>
	</table>
EOF;
$pdf->writeHTML($bloqueDetalle, false, false, false, false, '');

}

$valorY = $pdf->GetY();

if($valorY < ($ubicacionFooter - 15) && ($key+1) != $ultimoProducto){
//Todavia tengo lugar para incluir productos
} else {

if(isset($productos[$key+1])) {
$subTotalPorPagina = number_format($subTotalPorPagina,2,',','.');
$transportePorPagina = $subTotalPorPagina;
$bloqueSubtotal = <<<EOF
	<table>
		<tr style="font-weight: bold">
			<td style="width:380px;">
			</td>
			<td style="width:90px; font-size:10px; text-align: rigth;">
				SUBTOTAL: $
			</td>
			<td style="width:90px; font-size:10px; text-align: left;">
				$subTotalPorPagina
			</td>
		</tr>
	</table>
EOF;
$pdf->writeHTML($bloqueSubtotal, false, false, false, false, '');
$subTotalPorPagina = 0;
}

//INCLUYO CABECERA
$pdf->SetY($ubicacionCabecera);
$pdf->writeHTML($bloqueCabeceraOriginal, false, false, false, false, '');

//$pdf->SetFont('helvetica', '', 8);
//$pdf->Text(50, 273, 'Pagina 1/2');

//INCLUYO FOOTER
$pdf->SetY($ubicacionFooter);
//---------------------Datos Factura neto, totales, iva, descuento
if ($respuestaVenta["cbte_tipo"] == 1 || $respuestaVenta["cbte_tipo"] == 2 || $respuestaVenta["cbte_tipo"] == 3 || $respuestaVenta["cbte_tipo"] == 4) {
$ivas = json_decode($respuestaVenta["impuesto_detalle"], true);
$style = array(
    'border' => false,
    'fgcolor' => array(0,0,0),
    'bgcolor' => false, //array(255,255,255)
);
$pdf->write2DBarcode($jsonQRBase64, 'QRCODE,L', '', '', 20, 20, $style, 'N');
$pdf->SetY($ubicacionFooter);
$ivasDiscriminadosNombre = "";
$ivasDiscriminadosValor = "";
foreach ($ivas as $key => $value) {
	$ivasDiscriminadosNombre .= $value["descripcion"] . ': $<br>';
	$ivasDiscriminadosValor .= '<b>' . number_format($value["iva"],2, ',', '.') . '</b><br>';
}
$bloqueDatosFact = <<<EOF
	<table>
		<tr>
			<td style="width:80px;">
				 <!--ACA VA CODIGO QR -->
			</td>
			<td style="width:300px; font-size:8px; text-align: left;  border-color: #000; padding-bottom:0px ">
				<div>
					<img src="images/logo_afip.png" width="60"> Comprobante Autorizado
				</div> <br>
				<b>CAE: </b> $cae -	<b>Vto. CAE: </b> $vtoCae <br><br>
				<span style="font-size: 6.5px; font-style:italic">Esta Administración Federal no se responsabiliza por los datos ingresados en el detalle de la operación</span><br>

				<span style="font-size: 10px; font-style:italic; text-align:right">PAGINA $numPaginaActual</span>
			</td>
			<td style="width:90px; font-size:8px; text-align: rigth; border-color: #000;  background-color: #f4f4f4;">
				SUBTOTAL: $<br>
				DESCUENTO: $<br>
				NETO GRAVADO: $<br>
				<!--IVA 21: $<br>
				IVA 21: $<br>
				IVA 21: $<br>
				IVA 21: $<br>
				IVA 21: $<br> -->
				$ivasDiscriminadosNombre
                TOTAL: $<br>
			</td>
			<td style="width:90px; font-size:8px; text-align: rigth; border-color: #000;  background-color: #f4f4f4;">
				<b>$subTotal</b><br>
				<b>$descuentos</b><br>
				<b>$neto_grav</b><br>
				<!--<b>2.5</b><br>
				<b>5</b><br>
				<b>10.5</b><br>
				<b>21</b><br>
				<b>27</b><br>-->
				$ivasDiscriminadosValor
                <b>$total</b><br>
			</td>
		</tr>
	</table>
EOF;

} else {

$cbteBoCAutorizado = "";
if ($facturada) {
$style = array(
    'border' => false,
    'fgcolor' => array(0,0,0),
    'bgcolor' => false, //array(255,255,255)
);
$pdf->write2DBarcode($jsonQRBase64, 'QRCODE,L', '', '', 20, 20, $style, 'N');
$pdf->SetY($ubicacionFooter);
$cbteBoCAutorizado = '<div>
						<img src="images/logo_afip.png" width="60"> Comprobante Autorizado
					 </div> <br>
					<b>CAE: </b> ' . $cae . ' -	<b>Vto. CAE: </b> ' . $vtoCae . ' <br><br>
					<span style="font-size: 6.5px; font-style:italic">Esta Administración Federal no se responsabiliza por los datos ingresados en el detalle de la operación</span>';
}
$bloqueDatosFact = <<<EOF
	<table>
		<tr>
			<td style="width:80px;border-color: #000;">
				 <!--ACA VA CODIGO QR -->
			</td>
			<td style="width:300px; font-size:8px; text-align: left;  border-color: #000; padding-bottom:0px ">
				$cbteBoCAutorizado <br>
				<span style="font-size: 10px; font-style:italic; text-align:right">PAGINA $numPaginaActual</span>
			</td>
			<td style="width:90px; font-size:8px; text-align: rigth; border-color: #000;  background-color: #f4f4f4;">
				SUBTOTAL: $<br>
				DESCUENTO: $<br>
                TOTAL: $<br>
			</td>
			<td style="width:90px; font-size:8px; text-align: rigth; border-color: #000;  background-color: #f4f4f4;">
				<b>$subTotal</b><br>
				<b>$descuentos</b><br>
                <b>$total</b><br>
			</td>
		</tr>
	</table>
EOF;

}
$pdf->writeHTML($bloqueDatosFact, false, false, false, false, '');

$nuevaPagina = true;

}

}


/*=============================================================================
-----------------------------------DUPLICADO----------------------------------
==============================================================================*/
$ubicacionCabecera  = 7;
$ubicacionDetalle   = 80;
$ubicacionFooter    = 250;
$datosFact = []; //Array de datos a imprimir
$detalleEnTabla = ""; //filas en tabla para armar detalle
$subTotalPorPagina = 0;
$transportePorPagina = 0;
$valorY = 0;
$nuevaPagina = true;
$imprimoCabeceraDetalle = true;
$numPaginaActual = 0;
$ultimoProducto = count($productos);

$bloqueCabeceraDuplicado = <<<EOF
	<table border="1">
		<tr>
			<td style="width:560px; text-align: center;"> DUPLICADO</td>
		</tr>
	</table>
	<table border="1" >
		<tr style="padding: 0px;">
			<td style="width:260px; text-align: center; border-style:solid; border-width:2px; border-bottom-color:rgb(255,255,255);"> 
				<h2>$respEmpresa[razon_social]</h2>
			</td>
			<td style="width:40px; text-align:center">
				<div>
					<span style="font-size:28.5px;">$tipoVtaLetra</span>
					<span style="font-size:10px;">$tipoCodigo</span>
				</div>
			</td>
			<td style="width:260px; text-align: center; border-style:solid; border-width:2px; border-bottom-color:rgb(255,255,255);"> 
				$tipoVta
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
				<span><b>Cond. I.V.A.:</b> $tipoIva </span><br>
				<span><b>Defensa al Consumidor Mza. 08002226678</b></span> 
			</td>
			<td style="width:280px; font-size:10px; text-align: left">
				<div style="padding-top:5px">
					<span><b>N° Cbte:</b> $ptoVta - $numCte</span> <br>
					<span><b>Fecha Emisión:</b> $fecEmi </span><br>
					<span><b>CUIT:</b> $respEmpresa[cuit] </span><br>
					<span><b>II.BB.:</b> $respEmpresa[numero_iibb] </span><br>
					<span><b>Inic. Actividad:</b> $respEmpresa[inicio_actividades] </span>
				</div>
			</td>
		</tr>
	</table>
	<table border="1" style="padding-left: 5px">
	<tr>
			<td style="width:186px; font-size:8px; text-align: left;">
				<br>
				<span><b>Fecha Desde:</b> $respuestaVenta[fec_desde]</span> <br>
			</td>
			<td style="width:187px; font-size:8px; text-align: left;">
				<br>
				<span><b>Fecha Hasta:</b> $respuestaVenta[fec_hasta]</span> <br>
			</td>
			<td style="width:187px; font-size:8px; text-align: left;">
				<br>
				<span><b>Fecha Vto.:</b> $respuestaVenta[fec_vencimiento]</span> <br>
			</td>			
		</tr>
	</table>
    <table border="1" style="padding: 5px">
		<tr>
			<td style="width:560px; font-size:8px; text-align: left;">
				<br>
				<span><b>Tipo Doc.: $tipoDocumento :</b> $respuestaCliente[documento] </span> - <span> <b>Nombre / Razón Social :</b> $respuestaCliente[nombre] </span> 
				<br>
				<span><b>Domicilio: </b> $respuestaCliente[direccion] </span> - <span> <b>Condición I.V.A.:</b> $tipoIvaCliente </span> 
			</td>
		</tr>
	</table>
EOF;

//RECORRO TODOS LOS PRODUCTOS PARA ARMAR DETALLE
foreach ($productos as $key => $value) {

if($nuevaPagina){
$pdf->AddPage('P', 'A4');
$numPaginaActual++;
$pdf->SetY($ubicacionDetalle);
$nuevaPagina = false;
if($transportePorPagina != 0){
$bloqueTransporte = <<<EOF
    <table>
		<tr style="font-weight: bold">
			<td style="width:380px;">
			</td>
			<td style="width:90px; font-size:10px; text-align: rigth;">
				TRANSPORTE: $
			</td>
			<td style="width:90px; font-size:10px; text-align: left;">
				$transportePorPagina
			</td>
		</tr>
	</table>
EOF;
$pdf->writeHTML($bloqueTransporte, false, false, false, false, '');
$transportePorPagina = 0;
$pdf->SetY($ubicacionDetalle + 7);
    
}
$imprimoCabeceraDetalle = true;
}

///////////////DETALLES
$getProducto        = ControladorProductos::ctrMostrarProductoXId($value["id"]);
$formatCantidad     = number_format($value["cantidad"],2,',','.');
$formatPrecioUnit   = '$ ' . number_format($value["precio"],2,',','.');
$formatTotal        = '$ ' . number_format($value["total"],2,',','.');
$subTotalPorPagina += $value["total"];

//DISEÑO DETALLE DEPENDIENDO DEL TIPO DE COMPROBANTE
if ($respuestaVenta["cbte_tipo"] == 1 || $respuestaVenta["cbte_tipo"] == 2 || $respuestaVenta["cbte_tipo"] == 3 || $respuestaVenta["cbte_tipo"] == 4) {

if($imprimoCabeceraDetalle){
//---------------------CABECERA DETALLE A
$bloqueDetalleCab = <<<EOF
	<table border="1" style="padding: 5px">
		<tr style="background-color: #f4f4f4">
			<td style="width:40px; font-size:8px; text-align: center;">
				<span><b>Cant.</b></span> 
			</td>
			<td style="width:310px; font-size:8px; text-align: center;">
				<span><b>Detalle</b></span> 
			</td>
			<td style="width:80px; font-size:8px; text-align: center;">
				<span><b>Unit.</b></span> 
			</td>
			<td style="width:50px; font-size:8px; text-align: center;">
				<span><b>IVA %</b></span> 
			</td>
			<td style="width:80px; font-size:8px; text-align: center; background-color">
				<span><b>Total</b></span> 
			</td>
		</tr>
	</table>
EOF;
$pdf->writeHTML($bloqueDetalleCab, false, false, false, false, '');
$imprimoCabeceraDetalle = false;
}

//--------------------- DETALLE COMPROBANTE A
$bloqueDetalle = <<<EOF
	<table style=" padding: 2px; ">
	    <tr>
			<td style="width:40px; font-size:8px; text-align: center;">
				<span>$formatCantidad</span> 
			</td>			
			<td style="width:310px; font-size:8px; text-align: left;">
				<span>$value[descripcion]</span> 
			</td>
			<td style="width:80px; font-size:8px; text-align: left;">
				<span>$formatPrecioUnit</span> 
			</td>
			<td style="width:50px; font-size:8px; text-align: left;">
				<span>$getProducto[tipo_iva]</span> 
			</td>
			<td style="width:80px; font-size:8px; text-align: left;">
				<span>$formatTotal</span> 
			</td>
		</tr>
	</table>
EOF;
$pdf->writeHTML($bloqueDetalle, false, false, false, false, '');

} else {

if($imprimoCabeceraDetalle){
//--------------------- CABECERA DETALLE B | C | X
$bloqueDetalleCab = <<<EOF
	<table border="1" style="padding: 5px">
		<tr style="background-color: #f4f4f4">
			<td style="width:50px; font-size:8px; text-align: center;">
				<span><b>Cant.</b></span> 
			</td>			
			<td style="width:350px; font-size:8px; text-align: center;">
				<span><b>Detalle</b></span> 
			</td>
			<td style="width:80px; font-size:8px; text-align: center;">
				<span><b>Unit.</b></span> 
			</td>
			<td style="width:80px; font-size:8px; text-align: center; background-color">
				<span><b>Total</b></span> 
			</td>
		</tr>
	</table>
EOF;
$pdf->writeHTML($bloqueDetalleCab, false, false, false, false, '');
$imprimoCabeceraDetalle = false;

}

$bloqueDetalle = <<<EOF
	<table style=" padding: 2px; ">
	    <tr>
			<td style="width:50px; font-size:8px; text-align: center;">
				<span>$formatCantidad</span> 
			</td>			
			<td style="width:350px; font-size:8px; text-align: left;">
				<span>$value[descripcion]</span> 
			</td>
			<td style="width:80px; font-size:8px; text-align: left;">
				<span>$formatPrecioUnit</span> 
			</td>
			<td style="width:80px; font-size:8px; text-align: left;">
				<span>$formatTotal</span> 
			</td>
		</tr>
	</table>
EOF;
$pdf->writeHTML($bloqueDetalle, false, false, false, false, '');

}

$valorY = $pdf->GetY();

if($valorY < ($ubicacionFooter - 15) && ($key+1) != $ultimoProducto){
//Todavia tengo lugar para incluir productos
} else {

if(isset($productos[$key+1])) {
$subTotalPorPagina = number_format($subTotalPorPagina,2,',','.');
$transportePorPagina = $subTotalPorPagina;
$bloqueSubtotal = <<<EOF
	<table>
		<tr style="font-weight: bold">
			<td style="width:380px;">
			</td>
			<td style="width:90px; font-size:10px; text-align: rigth;">
				SUBTOTAL: $
			</td>
			<td style="width:90px; font-size:10px; text-align: left;">
				$subTotalPorPagina
			</td>
		</tr>
	</table>
EOF;
$pdf->writeHTML($bloqueSubtotal, false, false, false, false, '');
$subTotalPorPagina = 0;
}

//INCLUYO CABECERA
$pdf->SetY($ubicacionCabecera);
$pdf->writeHTML($bloqueCabeceraDuplicado, false, false, false, false, '');

//$pdf->SetFont('helvetica', '', 8);
//$pdf->Text(50, 273, 'Pagina 1/2');

//INCLUYO FOOTER
$pdf->SetY($ubicacionFooter);
//---------------------Datos Factura neto, totales, iva, descuento
if ($respuestaVenta["cbte_tipo"] == 1 || $respuestaVenta["cbte_tipo"] == 2 || $respuestaVenta["cbte_tipo"] == 3 || $respuestaVenta["cbte_tipo"] == 4) {
$ivas = json_decode($respuestaVenta["impuesto_detalle"], true);
$style = array(
    'border' => false,
    'fgcolor' => array(0,0,0),
    'bgcolor' => false, //array(255,255,255)
);
$pdf->write2DBarcode($jsonQRBase64, 'QRCODE,L', '', '', 20, 20, $style, 'N');
$pdf->SetY($ubicacionFooter);
$ivasDiscriminadosNombre = "";
$ivasDiscriminadosValor = "";
foreach ($ivas as $key => $value) {
	$ivasDiscriminadosNombre .= $value["descripcion"] . ': $<br>';
	$ivasDiscriminadosValor .= '<b>' . number_format($value["iva"],2, ',', '.') . '</b><br>';
}
$bloqueDatosFact = <<<EOF
	<table>
		<tr>
			<td style="width:80px;">
				 <!--ACA VA CODIGO QR -->
			</td>
			<td style="width:300px; font-size:8px; text-align: left;  border-color: #000; padding-bottom:0px ">
				<div>
					<img src="images/logo_afip.png" width="60"> Comprobante Autorizado
				</div> <br>
				<b>CAE: </b> $cae -	<b>Vto. CAE: </b> $vtoCae <br><br>
				<span style="font-size: 6.5px; font-style:italic">Esta Administración Federal no se responsabiliza por los datos ingresados en el detalle de la operación</span><br>

				<span style="font-size: 10px; font-style:italic; text-align:right">PAGINA $numPaginaActual</span>
			</td>
			<td style="width:90px; font-size:8px; text-align: rigth; border-color: #000;  background-color: #f4f4f4;">
				SUBTOTAL: $<br>
				DESCUENTO: $<br>
				NETO GRAVADO: $<br>
				<!--IVA 21: $<br>
				IVA 21: $<br>
				IVA 21: $<br>
				IVA 21: $<br>
				IVA 21: $<br> -->
				$ivasDiscriminadosNombre
                TOTAL: $<br>
			</td>
			<td style="width:90px; font-size:8px; text-align: rigth; border-color: #000;  background-color: #f4f4f4;">
				<b>$subTotal</b><br>
				<b>$descuentos</b><br>
				<b>$neto_grav</b><br>
				<!--<b>2.5</b><br>
				<b>5</b><br>
				<b>10.5</b><br>
				<b>21</b><br>
				<b>27</b><br>-->
				$ivasDiscriminadosValor
                <b>$total</b><br>
			</td>
		</tr>
	</table>
EOF;

} else {

$cbteBoCAutorizado = "";
if ($facturada) {
$style = array(
    'border' => false,
    'fgcolor' => array(0,0,0),
    'bgcolor' => false, //array(255,255,255)
);
$pdf->write2DBarcode($jsonQRBase64, 'QRCODE,L', '', '', 20, 20, $style, 'N');
$pdf->SetY($ubicacionFooter);
$cbteBoCAutorizado = '<div>
						<img src="images/logo_afip.png" width="60"> Comprobante Autorizado
					 </div> <br>
					<b>CAE: </b> ' . $cae . ' -	<b>Vto. CAE: </b> ' . $vtoCae . ' <br><br>
					<span style="font-size: 6.5px; font-style:italic">Esta Administración Federal no se responsabiliza por los datos ingresados en el detalle de la operación</span>';
}
$bloqueDatosFact = <<<EOF
	<table>
		<tr>
			<td style="width:80px;border-color: #000;">
				 <!--ACA VA CODIGO QR -->
			</td>
			<td style="width:300px; font-size:8px; text-align: left;  border-color: #000; padding-bottom:0px ">
				$cbteBoCAutorizado <br>
				<span style="font-size: 10px; font-style:italic; text-align:right">PAGINA $numPaginaActual</span>
			</td>
			<td style="width:90px; font-size:8px; text-align: rigth; border-color: #000;  background-color: #f4f4f4;">
				SUBTOTAL: $<br>
				DESCUENTO: $<br>
                TOTAL: $<br>
			</td>
			<td style="width:90px; font-size:8px; text-align: rigth; border-color: #000;  background-color: #f4f4f4;">
				<b>$subTotal</b><br>
				<b>$descuentos</b><br>
                <b>$total</b><br>
			</td>
		</tr>
	</table>
EOF;

}
$pdf->writeHTML($bloqueDatosFact, false, false, false, false, '');

$nuevaPagina = true;

}

}

//SALIDA DEL ARCHIVO
$pathArchivo = $_SERVER['DOCUMENT_ROOT'] . 'extensiones/vendor/tecnickcom/tcpdf/pdf/comprobantes/CBTE-' . $tipoVtaLetra.'_'.$ptoVta.'-'.$numCte.'.pdf';
$nomArchivo = 'CBTE-' . $tipoVtaLetra.'_'.$ptoVta.'-'.$numCte.'.pdf';

$pdfdoc = $pdf->Output($pathArchivo, 'F');

$to = $direccionMail;
$from = $respEmpresa["mail"];
$fromName = $respEmpresa["razon_social"];
$subject = 'Comprobante';

$message = "<p>Muchas gracias por su compra. Se ha adjuntado el comprobante en el presente email.</p><br>";
$message .= "<p><b>".$respEmpresa["razon_social"]."</b><br>";
$message .= "Domicilio: ".$respEmpresa["domicilio"]."<br>";
$message .= "Telefono: ".$respEmpresa["telefono"]."<br>";
$message .= "Email: ".$respEmpresa["mail"]."</p>";

$separator = md5(time());
$eol = PHP_EOL;
$filename = $nomArchivo;

$fp =    fopen($pathArchivo,"rb");
$data =  fread($fp,filesize($pathArchivo));

$attachment = chunk_split(base64_encode($data));

$headers = "From: " . $fromName . ' <'.$from.'>' . $eol;
$headers .= "MIME-Version: 1.0" . $eol;
$headers .= "Content-Type: multipart/mixed; boundary=\"" . $separator . "\"" . $eol . $eol;

$body = '';
$body .= "Content-Transfer-Encoding: 7bit" . $eol;
$body .= "This is a MIME encoded message." . $eol; //had one more .$eol
$body .= "--" . $separator . $eol;
$body .= "Content-Type: text/html; charset=\"iso-8859-1\"" . $eol;
$body .= "Content-Transfer-Encoding: 8bit" . $eol . $eol;
$body .= $message . $eol;
$body .= "--" . $separator . $eol;
$body .= "Content-Type: application/octet-stream; name=\"" . $filename . "\"" . $eol;
$body .= "Content-Transfer-Encoding: base64" . $eol;
$body .= "Content-Disposition: attachment" . $eol . $eol;
$body .= $attachment . $eol;
$body .= "--" . $separator . "--";

if (!mail($to, $subject, $body, $headers)) {
	echo 'ERROR. Email no ha sido enviado';
} else {
    echo '<h3>Email enviado correctamente!</h3><br><p>Ya puede cerrar esta ventana</p>';
}

if (!unlink($pathArchivo)) {
    echo ("ERROR. $pathArchivo no ha podido borrarse debido a un error");
} 

}

}

$comprobante = new imprimirComprobante();
$comprobante -> codigo = $_GET["codigo"];
$comprobante -> email = $_GET["email"];
$comprobante -> traerImpresionComprobante();

?>