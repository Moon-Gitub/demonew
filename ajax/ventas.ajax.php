<?php
// ✅ Seguridad AJAX
require_once "seguridad.ajax.php";
SeguridadAjax::inicializar();

require_once "../controladores/ventas.controlador.php";
require_once "../modelos/ventas.modelo.php";

require_once "../controladores/productos.controlador.php";
require_once "../modelos/productos.modelo.php";

require_once "../controladores/clientes.controlador.php";
require_once "../modelos/clientes.modelo.php";

require_once "../controladores/clientes_cta_cte.controlador.php";
require_once "../modelos/clientes_cta_cte.modelo.php";

require_once "../controladores/cajas.controlador.php";
require_once "../modelos/cajas.modelo.php";

require_once "../controladores/empresa.controlador.php";
require_once "../modelos/empresa.modelo.php";

require_once "../controladores/facturacion/wsaa.class.php";
require_once "../controladores/facturacion/wsfe.class.php";

class AjaxVentas{

	/*=============================================
	COBRAR VENTA
	=============================================*/	

	public $idVenta;
	public $postVentaCaja;	

	public function ajaxEditarVenta(){

		$item = "id";
		$valor = $this->idVenta;

		$respuesta = ControladorVentas::ctrMostrarVentas($item, $valor);
		
		// Agregar datos del cliente si existe
		if ($respuesta && isset($respuesta["id_cliente"])) {
			require_once "../controladores/clientes.controlador.php";
			require_once "../modelos/clientes.modelo.php";
			
			$itemCliente = "id";
			$valorCliente = $respuesta["id_cliente"];
			$cliente = ControladorClientes::ctrMostrarClientes($itemCliente, $valorCliente);
			
			if ($cliente) {
				$respuesta["cliente_nombre"] = $cliente["nombre"] ?? "Consumidor Final";
				$respuesta["cliente_documento"] = $cliente["documento"] ?? "";
			} else {
				$respuesta["cliente_nombre"] = $respuesta["id_cliente"] == 1 ? "Consumidor Final" : "Cliente #" . $respuesta["id_cliente"];
			}
		}
		
		// Agregar datos del vendedor si existe
		if ($respuesta && isset($respuesta["id_vendedor"])) {
			require_once "../controladores/usuarios.controlador.php";
			require_once "../modelos/usuarios.modelo.php";
			
			$itemUsuario = "id";
			$valorUsuario = $respuesta["id_vendedor"];
			$vendedor = ControladorUsuarios::ctrMostrarUsuarios($itemUsuario, $valorUsuario);
			
			if ($vendedor) {
				$respuesta["vendedor_nombre"] = $vendedor["nombre"] ?? "N/A";
			} else {
				$respuesta["vendedor_nombre"] = "N/A";
			}
		}

		// Reemplazar productos JSON con productos desde tabla relacional (formato compatible)
		if ($respuesta && isset($respuesta["id"])) {
			$productosLegacy = ControladorVentas::ctrObtenerProductosVentaLegacy($respuesta["id"]);
			$respuesta["productos"] = json_encode($productosLegacy);
		}

		echo json_encode($respuesta);

	}

	public function ajaxInsertarVenta(){

		$respuesta = ControladorVentas::ctrCrearVentaCaja($this->postVentaCaja);

		echo json_encode($respuesta);

	}

	public function ajaxMostrarVentaConCliente($idVenta){

		$respuesta = ControladorVentas::ctrMostrarVentaConCliente($idVenta);

		echo json_encode($respuesta);

	}
}

/*=============================================
COBRAR VENTA
=============================================*/	

if(isset($_POST["idVenta"])){

	$venta = new AjaxVentas();
	$venta -> idVenta = $_POST["idVenta"];
	$venta -> ajaxEditarVenta();

}

/*=============================================
INSERTAR VENTA CAJA
=============================================*/	

if(isset($_POST["nuevaVentaCaja"])){

	$venta = new AjaxVentas();
	$venta -> postVentaCaja = $_POST;
	$venta -> ajaxInsertarVenta();

}

/*=============================================
COBRAR VENTA
=============================================*/	

if(isset($_POST["idVentaConCliente"])){

	$venta = new AjaxVentas();
	$venta -> ajaxMostrarVentaConCliente($_POST["idVentaConCliente"]);

}
