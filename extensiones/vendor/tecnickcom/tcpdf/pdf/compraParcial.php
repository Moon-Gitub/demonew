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

//TRAEMOS LA INFORMACIÓN DE LA VENTA
$itemPedido = "id";
$respuestaCompra = ControladorCompras::ctrMostrarCompras($itemPedido, $_GET['codigo']);

$fecha = substr($respuestaCompra["fecha"],0,-8);
$fecha=date("d-m-Y",strtotime($fecha));
$productos = json_decode($respuestaCompra["productos"], true);
$destino = $respuestaCompra["sucursalDestino"];
$total = number_format(round($respuestaCompra["total"],2),2);

$proveedor = ControladorProveedores::ctrMostrarProveedores('id', $respuestaCompra["id_proveedor"]);

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
	<table border="0" >
		<tr style="padding: 0px;">
			<td style="width:260px; text-align: center; border-style:solid; border-width:2px; border-bottom-color:rgb(255,255,255);"> 
				<h2>$respEmpresa[razon_social]</h2>
			</td>
			<td style="width:40px; text-align:center"><span style="font-size:28.5px;">X</span></td>
			<td style="width:260px; text-align: center; border-style:solid; border-width:2px; border-bottom-color:rgb(255,255,255);">ORDEN DE COMPRA</td>
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
// ---------------------------------------------------------

$bloque3 = <<<EOF
	<table style="font-size:9px; padding:5px 10px;">
		<tr>
			<td style="border: 1px solid #666; background-color:white; width:540px; text-align:center"><b>Productos pedidos</b></td>
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
$pdf->Output('Orden_compra_'.$_GET['codigo'].'.pdf');

}

}

$factura = new imprimirFactura();
$factura -> codigo = $_GET["codigo"];
$factura -> traerImpresionFactura();

?>
