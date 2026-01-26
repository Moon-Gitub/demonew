<?php

$autoloadPath = '../../../autoload.php';
if(!file_exists(__DIR__ . '/' . $autoloadPath)) {
    error_log("ERROR: autoload.php no encontrado en ruta relativa");
    die('Error: No se encuentra autoload.php');
}
require_once $autoloadPath;

$rutaBase = dirname(dirname(dirname(dirname(dirname(dirname(__FILE__))))));

$envPath = $rutaBase . '/.env';
if (file_exists($envPath)) {
    if (class_exists('Dotenv\Dotenv')) {
        try {
            $dotenv = Dotenv\Dotenv::createImmutable($rutaBase);
            $dotenv->load();
        } catch (Exception $e) {
            error_log("❌ Error al cargar .env en comprobante.php: " . $e->getMessage());
        }
    } else {

        $envLines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($envLines as $line) {
            if (strpos(trim($line), '#') === 0) continue; // Saltar comentarios
            if (strpos($line, '=') !== false) {
                list($key, $value) = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);
                if (!empty($key)) {
                    $_ENV[$key] = $value;
                    $_SERVER[$key] = $value;
                    putenv("$key=$value");
                }
            }
        }
    }
} else {
    error_log("⚠️ Archivo .env no encontrado en: " . $envPath);
}

$helpersPath = $rutaBase . '/helpers.php';
if (file_exists($helpersPath)) {
    require_once $helpersPath;
} else {
    error_log("⚠️ helpers.php no encontrado en: " . $helpersPath);
}

// Usar rutas relativas como en recibo.php que funciona
$archivos = [
    "../../../../../controladores/empresa.controlador.php",
    "../../../../../modelos/empresa.modelo.php",
    "../../../../../controladores/compras.controlador.php",
    "../../../../../modelos/compras.modelo.php",
    "../../../../../controladores/proveedores.controlador.php",
    "../../../../../modelos/proveedores.modelo.php",
    "../../../../../controladores/usuarios.controlador.php",
    "../../../../../modelos/usuarios.modelo.php",
    "../../../../../controladores/productos.controlador.php",
    "../../../../../modelos/productos.modelo.php",
    '../../../autoload.php'
];

foreach ($archivos as $archivo) {
    $rutaCompleta = __DIR__ . '/' . $archivo;
    if (!file_exists($rutaCompleta)) {
        error_log("❌ Archivo no encontrado: " . $rutaCompleta);
        die('Error: Archivo requerido no encontrado: ' . basename($archivo) . ' en ' . $rutaCompleta);
    }
    require_once $archivo;
}

class imprimirFactura{

public $codigo;

public function traerImpresionFactura(){

//DATOS EMPRESA
$respEmpresa = ControladorEmpresa::ctrMostrarEmpresa('id', 1);

//TRAEMOS LA INFORMACIÓN DE LA COMPRA
// Acepta tanto 'id' como 'codigo' para compatibilidad
$idCompra = isset($_GET['id']) ? $_GET['id'] : (isset($_GET['codigo']) ? $_GET['codigo'] : null);
if(!$idCompra){
	die("Error: No se proporcionó ID de compra");
}
$itemPedido = "id";
$respuestaCompra = ControladorCompras::ctrMostrarCompras($itemPedido, $idCompra);

// Verificar que la compra existe
if(!$respuestaCompra){
	die("Error: No se encontró la compra con ID: " . $idCompra);
}

//TRAEMOS LA INFORMACIÓN DEL PROVEEDOR
$itemProveedor = "id";
$proveedor = ControladorProveedores::ctrMostrarProveedores($itemProveedor, $respuestaCompra["id_proveedor"]);

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
			<td style="width:560px; text-align: center;"> COMPRA CONFIRMADA</td>
		</tr>
	</table>
	<table border="0" >
		<tr style="padding: 0px;">
			<td style="width:260px; text-align: center; border-style:solid; border-width:2px; border-bottom-color:rgb(255,255,255);"> 
				<h2>$respEmpresa[razon_social]</h2>
			</td>
			<td style="width:40px; text-align:center"><span style="font-size:28.5px;">X</span></td>
			<td style="width:260px; text-align: center; border-style:solid; border-width:2px; border-bottom-color:rgb(255,255,255);"> COMPRA </td>
		</tr>
	</table>
	<table border="0" style="padding: 10px">
		<tr>
			<td style="width:280px; font-size:10px; text-align: left;">
				<br>
				<span><b>Direccion:</b> $respEmpresa[domicilio]</span> <br>
				<span><b>Localidad:</b> $respEmpresa[localidad] - C.P.: $respEmpresa[codigo_postal]</span><br>
			</td>
			<td style="width:280px; font-size:10px; text-align: left">
				<div style="padding-top:5px">
					<span><b>N° Cbte:</b> $respuestaCompra[id]</span> <br>
					<span><b>Fecha Emisión:</b> $fecha </span><br>
				</div>
			</td>
		</tr>
	</table>
	
    <table border="1" style="padding: 5px">
		<tr>
			<td style="width:560px; font-size:12px; text-align: left;">
				<b>Datos proveedor: </b>
				<br>
				<b>Nombre / Razón Social :</b> $proveedor[nombre] - <b> $proveedor[cuit] :</b>
				<br>
				<b>Domicilio: </b> $proveedor[direccion]
			</td>
		</tr>
	</table>
EOF;

$pdf->writeHTML($bloque1, false, false, false, false, '');

// ---------------------------------------------------------
$bloque2 = <<<EOF
	<table  style="font-size:10px; padding:5px 10px;">	
		<tr>
			<td style="width:270px;">
				<b>Orden De Compra: $respuestaCompra[usuarioPedido]</b> 
			</td>
			<td style="width:270px;">
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
		<tr><td style="border: 1px solid #666; background-color:white; width:540px; text-align:center"><b>Detalle de productos</b></td>
		</tr>
		<tr>
		<td style="border: 1px solid #666; background-color:white; width:255px; text-align:center"><b>Producto</b></td>
		<td style="border: 1px solid #666; background-color:white; width:60px; text-align:center"><b>Pedido</b></td>
		<td style="border: 1px solid #666; background-color:white; width:65px; text-align:center"><b>Recibido</b></td>
		<td style="border: 1px solid #666; background-color:white; width:80px; text-align:center"><b>$ Unitario</b></td>
		<td style="border: 1px solid #666; background-color:white; width:80px; text-align:center"><b>$ Total</b></td>
		</tr>
	</table>

EOF;

$pdf->writeHTML($bloque3, false, false, false, false, '');

// ---------------------------------------------------------
$precioTotalNeto = 0;
foreach ($productos as $key => $item) {

//$itemProducto = "descripcion";
//$valorProducto = $item["descripcion"];
//$orden = null;
//$respuestaProducto = ControladorProductos::ctrMostrarProductos($itemProducto, $valorProducto, $orden);
$valorUnitario = number_format($item["precioCompra"], 2);
$precioTotal = number_format($item["total"], 2);
$precioTotalNeto += $item["total"];
$bloque4 = <<<EOF
	<table style="font-size:8px; padding:5px 10px;">
		<tr>
			<td style="border: 1px solid #666; color:#333; background-color:white; width:255x; text-align:center"><b>$item[descripcion]</b></td>
			<td style="border: 1px solid #666; color:#333; background-color:white; width:60px; text-align:center"><b>$item[pedidos]</b></td>
			<td style="border: 1px solid #666; color:#333; background-color:white; width:65px; text-align:center"><b>$item[recibidos]</b></td>
			<td style="border: 1px solid #666; color:#333; background-color:white; width:80px; text-align:center"><b>$valorUnitario</b></td>
			<td style="border: 1px solid #666; color:#333; background-color:white; width:80px; text-align:center"><b>$precioTotal</b></td>
		</tr>
	</table>
EOF;
$pdf->writeHTML($bloque4, false, false, false, false, '');

}

$bloque5 = <<<EOF
	<table>
		<tr>
			<td style="border: 1px solid #666; color:#333; background-color:white; width:420px; height:22px; text-align:rigth">
				 <b>Total neto </b>
			</td>
			<td style="border: 1px solid #666; color:#333; background-color:white; width:120px; height:22px; text-align:center">
				 $ $precioTotalNeto
			</td>
		</tr>
		<tr>
			<td style="border: 1px solid #666; color:#333; background-color:white; width:420px; height:22px; text-align:rigth">
				 <b>Descuento </b>
			</td>
			<td style="border: 1px solid #666; color:#333; background-color:white; width:120px; height:22px; text-align:center">
				 $ $respuestaCompra[descuento]
			</td>
		</tr>
		<tr>
			<td style="border: 1px solid #666; color:#333; background-color:white; width:420px; height:22px; text-align:rigth">
				 <b>Importe neto </b>
			</td>
			<td style="border: 1px solid #666; color:#333; background-color:white; width:120px; height:22px; text-align:center">
				 $ $totalNeto
			</td>
		</tr>
	</table>
EOF;
$pdf->writeHTML($bloque5, false, false, false, false, '');
if($respuestaCompra["tipo"]<>0){
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

/*
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
*/

// ---------------------------------------------------------

$bloque111 = <<<EOF
	<table style="font-size:10px; padding:5px 10px;">
		<tr>
			<td style="border: 1px solid #666; color:#333; background-color:white; width:540px; text-align:center">
				<b>Datos Impositivos</b>
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
				<b>$ $total</b>
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
				<b>$ $total</b>
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
				<b>$ $total</b>
			</td>
		</tr>
	</table>
EOF;
$pdf->writeHTML($bloqueRemitoPago, false, false, false, false, '');
}
// ---------------------------------------------------------
//SALIDA DEL ARCHIVO 

//$pdf->Output('factura.pdf', 'D');
$pdf->Output('Compra_'.$idCompra.'.pdf');

}

}

$factura = new imprimirFactura();
$factura -> codigo = isset($_GET['id']) ? $_GET['id'] : (isset($_GET['codigo']) ? $_GET['codigo'] : null);
$factura -> traerImpresionFactura();

?>
