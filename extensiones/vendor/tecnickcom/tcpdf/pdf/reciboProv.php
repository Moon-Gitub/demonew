<?php

require_once "../../../controladores/proveedores.controlador.php";
require_once "../../../modelos/proveedores.modelo.php";

require_once "../../../controladores/proveedores_cta_cte.controlador.php";
require_once "../../../modelos/proveedores_cta_cte.modelo.php";

require_once "../../../controladores/usuarios.controlador.php";
require_once "../../../modelos/usuarios.modelo.php";

require_once "../../../controladores/empresa.controlador.php";
require_once "../../../modelos/empresa.modelo.php";

class imprimirFactura{

public $id_registro;

public function traerImpresionFactura(){

//DATOS EMPRESA
$respEmpresa = ControladorEmpresa::ctrMostrarEmpresa('id', 1);

//TRAEMOS LA INFORMACION REGISTRO
$item = "id";
$valor = $this->id_registro;
$respuestaRegistro = ControladorProveedoresCtaCte::ctrMostrarRegistroCtaCteProveedor($item, $valor);

$fecha = substr($respuestaRegistro["fecha"],0,-8);
$descripcion = $respuestaRegistro["descripcion"];
$total = number_format($respuestaRegistro["importe"],2);
$metPago = (isset($respuestaRegistro["metodo_pago"])) ? "Medio de pago: " . $respuestaRegistro["metodo_pago"] : "";

//TRAEMOS LA INFORMACIÓN DEL CLIENTE
$itemCliente = "id";
$valorCliente = $respuestaRegistro["id_proveedor"];
$respuestaCliente = ControladorProveedores::ctrMostrarProveedores($itemCliente, $valorCliente);

//REQUERIMOS LA CLASE TCPDF
require_once('tcpdf_include.php');

$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

$pdf->startPageGroup();

$pdf->AddPage('P', 'A4');

// -------------------ORIGINAL--------------------------------------

$bloque1 = <<<EOF
	<table>
		<tr>
			<td style="width:150px;color:#00a65a; font-size:18px">$respEmpresa[razon_social]</td>
			<td style="width:200px; text-align:rigth; ">RECIBO - ORIGINAL</td>
		</tr>
	</table>
EOF;
$pdf->writeHTML($bloque1, false, false, false, false, '');
// ---------------------------------------------------------
$bloque2 = <<<EOF
	<table border="0" style="font-size:10px; padding:5px 10px;">
		<tr>
		
			<td style="background-color:white; width:390px">

				Proveedor: $respuestaCliente[nombre]

			</td>

			<td style="background-color:white; width:150px; text-align:right">
			
				Fecha: $fecha

			</td>

		</tr>
			<tr>
		
			<td style="background-color:white; width:390px">

				Direccion: $respuestaCliente[direccion]

			</td>

			<td style="background-color:white; width:150px; text-align:right">

			</td>

		</tr>

		<tr>
		
		<td style="background-color:white; width:540px"></td>

		</tr>

	</table>

EOF;

$pdf->writeHTML($bloque2, false, false, false, false, '');

// ---------------------------------------------------------

$bloque3 = <<<EOF

	<table style="font-size:15px; padding:5px 10px;">

		<tr>
		
		<td><p>RECIBIMOS de $respuestaCliente[nombre] la suma de pesos: $ $total, en concepto de: $respuestaRegistro[descripcion].</p></td>

		</tr>
		<tr>
		
		<td><p>$metPago</p></td>

		</tr>

	</table>

EOF;

$pdf->writeHTML($bloque3, false, false, false, false, '');

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

	<table style="font-size:10px; padding:5px 10px;">

		<tr>

			<td style="text-align:center; width:390px; ">
			</td>

			<td style="border-top: 1px solid #666; color:#333; background-color:white; width:150px; text-align:center">
			Firma y aclaración
			</td>
		</tr>

	</table>

EOF;

$pdf->writeHTML($bloqueFondo, false, false, false, false, '');	
// ---------------------------------------------------------

$bloqueObs = <<<EOF

	<table>
		
		<tr>
			
			<td style="width:540px; "><img src="images/back.jpg"></td>
		
		</tr>
		<tr>
			
			<td style="width:540px; "><img src="images/back.jpg"></td>
		
		</tr>
		<tr>
			
			<td style="width:540px;  border-bottom: 1px solid #666; "><img src="images/back.jpg"></td>
		
		</tr>

	</table>

EOF;

$pdf->writeHTML($bloqueObs, false, false, false, false, '');	

//-------------------DUPLICADO--------------------------------------

$bloque1 = <<<EOF

	<table>
		
		<tr>
			
			<td style="width:150px;color:#00a65a; font-size:18px">$respEmpresa[razon_social]</td>

			<td style="width:200px; text-align:rigth; ">RECIBO - DUPLICADO</td>

		</tr>

	</table>

EOF;

$pdf->writeHTML($bloque1, false, false, false, false, '');

// ---------------------------------------------------------

$bloque2 = <<<EOF

	<table border="0" style="font-size:10px; padding:5px 10px;">
	
		<tr>
		
			<td style="background-color:white; width:390px">

				Proveedor: $respuestaCliente[nombre]

			</td>

			<td style="background-color:white; width:150px; text-align:right">
			
				Fecha: $fecha

			</td>

		</tr>
			<tr>
		
			<td style="background-color:white; width:390px">

				Direccion: $respuestaCliente[direccion]

			</td>

			<td style="background-color:white; width:150px; text-align:right">

			</td>

		</tr>

		<tr>
		
		<td style="background-color:white; width:540px"></td>

		</tr>

	</table>

EOF;

$pdf->writeHTML($bloque2, false, false, false, false, '');

// ---------------------------------------------------------

$bloque3 = <<<EOF

	<table style="font-size:15px; padding:5px 10px;">

		<tr>
		
		<td><p>RECIBIMOS de $respuestaCliente[nombre] la suma de pesos: $ $total, en concepto de: $respuestaRegistro[descripcion].</p></td>

		</tr>
		<tr>
		
		<td><p>$metPago</p></td>

		</tr>

	</table>

EOF;

$pdf->writeHTML($bloque3, false, false, false, false, '');

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

	<table style="font-size:10px; padding:5px 10px;">

		<tr>

			<td style="text-align:center; width:390px; ">
			</td>

			<td style="border-top: 1px solid #666; color:#333; background-color:white; width:150px; text-align:center">
			Firma y aclaración
			</td>
		</tr>

	</table>

EOF;

$pdf->writeHTML($bloqueFondo, false, false, false, false, '');	
// ---------------------------------------------------------
//SALIDA DEL ARCHIVO 

//$pdf->Output('factura.pdf', 'D');
$pdf->Output('factura.pdf');

}

}

$factura = new imprimirFactura();
$factura -> id_registro = $_GET["idRegistro"];
$factura -> traerImpresionFactura();

?>