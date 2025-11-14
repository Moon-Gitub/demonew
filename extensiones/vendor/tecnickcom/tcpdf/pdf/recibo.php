<?php

require_once "../../../../../controladores/clientes.controlador.php";
require_once "../../../../../modelos/clientes.modelo.php";
require_once "../../../../../controladores/clientes_cta_cte.controlador.php";
require_once "../../../../../modelos/clientes_cta_cte.modelo.php";
require_once "../../../../../controladores/usuarios.controlador.php";
require_once "../../../../../modelos/usuarios.modelo.php";
require_once "../../../../../controladores/empresa.controlador.php";
require_once "../../../../../modelos/empresa.modelo.php";

require_once '../../../autoload.php';

class imprimirFactura{

public $id_registro;

public function traerImpresionFactura(){

//TRAEMOS LA INFORMACION REGISTRO
$item = "id";
$valor = $this->id_registro;
$respuestaRegistro = ControladorClientesCtaCte::ctrMostrarCtaCteClienteId($item, $valor);

$fecha = date('d//m/Y', strtotime($respuestaRegistro["fecha"]));
$descripcion = $respuestaRegistro["descripcion"];
$total = number_format($respuestaRegistro["importe"],2);
$metPago = (isset($respuestaRegistro["metodo_pago"])) ? "Medio de pago: " . $respuestaRegistro["metodo_pago"] : "";

//TRAEMOS LA INFORMACIÓN DEL CLIENTE
$itemCliente = "id";
$valorCliente = $respuestaRegistro["id_cliente"];
$respuestaCliente = ControladorClientes::ctrMostrarClientes($itemCliente, $valorCliente);

//TRAEMOS LA INFO DE EMPRESA
$respEmpresa = ControladorEmpresa::ctrMostrarempresa('id', 1);

//REQUERIMOS LA CLASE TCPDF
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
// Configuración del documento
$pdf->SetCreator('Posmoon');
$pdf->SetTitle($respEmpresa["razon_social"]);
$pdf->AddPage('P', 'A4');

$bloqueCabeceraOriginal = <<<EOF
	<table border="1">
		<tr>
			<td style="width:560px; text-align: center;"> ORIGINAL</td>
		</tr>
	</table>
EOF;

$bloqueCabeceraDuplicado = <<<EOF
	<table border="1">
		<tr>
			<td style="width:560px; text-align: center;"> DUPLICADO</td>
		</tr>
	</table>
EOF;

$bloqueCabecera = <<<EOF
	<table border="1" >
		<tr style="padding: 0px;">
			<td style="width:260px; text-align: center; border-style:solid; border-width:2px; border-bottom-color:rgb(255,255,255);"> 
				<h2>$respEmpresa[razon_social]</h2>
			</td>
			<td style="width:40px; text-align:center">
				<div>
					<span style="font-size:28.5px;">X</span>
				</div>
			</td>
			<td style="width:260px; text-align: center; border-style:solid; border-width:2px; border-bottom-color:rgb(255,255,255);"> 
				<h2>RECIBO</h2>
			</td>
		</tr>
	</table>
	<table border="1" style="padding: 5px">
		<tr>
			<td style="width:280px; font-size:10px; text-align: left;">
				<br>
				<span><b>Direccion:</b> $respEmpresa[domicilio]</span> <br>
				<span><b>Telefono:</b> $respEmpresa[telefono]</span> <br>
				<span><b>Localidad:</b> $respEmpresa[localidad] - C.P.: $respEmpresa[codigo_postal]</span><br>
				<span><b>Cond. I.V.A.:</b> I.V.A. Responsable Inscripto </span><br>
			</td>
			<td style="width:280px; font-size:10px; text-align: left">
				<div style="padding-top:5px">
					<span><b>N° Cbte:</b> $respuestaRegistro[numero_recibo] </span> <br>
					<span><b>Fecha Emisión:</b> $fecha </span><br>
					<span><b>CUIT:</b> $respEmpresa[cuit] </span><br>
					<span><b>II.BB.:</b> $respEmpresa[numero_iibb] </span> - <span><b>Inic. Actividad:</b> $respEmpresa[inicio_actividades] </span>
				</div>
			</td>
		</tr>
	</table>
EOF;

// ---------------------------------------------------------
$bloqueDetalle = <<<EOF
	<table style="font-size:15px; padding:5px 10px;">
		<tr>
			<td><p style="line-height: 1.5">RECIBIMOS de $respuestaCliente[nombre] ( Documento/CUIT/CUIL.: $respuestaCliente[documento] ) la suma de pesos: $ $total, en concepto de: $respuestaRegistro[descripcion].</p></td>
		</tr>
		<tr>
			<td><p>$metPago</p></td>
		</tr>
	</table>
EOF;

// ---------------------------------------------------------
$bloqueFondo = <<<EOF
	<table>
		<tr>
			<td style="width:540px"><img src="images/back.jpg"></td>
		</tr>
	</table>
	<table>
		<tr>
			<td style="width:540px"><img src="images/back.jpg"></td>
		</tr>
	</table>
	<table style="font-size:10px; padding:5px 10px; padding-bottom: 15px">
		<tr>
			<td style="text-align:center; width:390px; "></td>
			<td style="border-top: 1px solid #666; color:#333; background-color:white; width:150px; text-align:center">
				Firma y aclaración
			</td>
		</tr>
	</table>
EOF;

//-------------------ORIGINAL---------------------------------------
$pdf->writeHTML($bloqueCabeceraOriginal, false, false, false, false, '');
$pdf->writeHTML($bloqueCabecera, false, false, false, false, '');
$pdf->writeHTML($bloqueDetalle, false, false, false, false, '');
$pdf->writeHTML($bloqueFondo, false, false, false, false, '');

//-------------------DUPLICADO--------------------------------------
$pdf->writeHTML($bloqueCabeceraDuplicado, false, false, false, false, '');
$pdf->writeHTML($bloqueCabecera, false, false, false, false, '');
$pdf->writeHTML($bloqueDetalle, false, false, false, false, '');
$pdf->writeHTML($bloqueFondo, false, false, false, false, '');	

//SALIDA DEL ARCHIVO 
$pdf->Output('factura.pdf');

}

}

$factura = new imprimirFactura();
$factura -> id_registro = $_GET["idRegistro"];
$factura -> traerImpresionFactura();

?>