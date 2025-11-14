<?php

require_once "../controladores/ventas.controlador.php";
require_once "../modelos/ventas.modelo.php";

class AjaxVentas{

	/*=============================================
	COBRAR VENTA
	=============================================*/	

	public $codigo;
	
	public function ajaxVerVentas(){

		$item = "codigo";
		$valor = $this->codigo;

		$respuesta = ControladorVentas::ctrRangoFechasVentasProducto($item, $valor);

		echo json_encode($respuesta);

	}
	
	public $fechaInicio;
	public $fechaFin;
	
	public function ajaxVerVentasConsulta(){

		$item = "descripcion";
		$valor = $this->productoContulta;
		$item2 = "fechaInicio";
		$valor2 = $this->fechaInicio;
		$item3 = "fechaFin";
		$valor3 = $this->fechaFin;

		$respuesta = ControladorVentas::ctrRangoFechasProductoConsulta($item, $valor, $item2, $valor2, $item3, $valor3);

		echo json_encode($respuesta);

	}

	public $desde;
	public $hasta;

	public function ajaxVerVentasTotalesConsulta(){

		$item = "fechaInicio";
		$valor = $this->desde;
		$item2 = "fechaFin";
		$valor2 = $this->hasta;

		$respuesta = ControladorVentas::ctrRangoFechasProductoSumasConsulta($item, $valor, $item2, $valor2);

		echo json_encode($respuesta);

	}
	
	public $desdeDos;
	public $hastaDos;

	public function ajaxVerVentasCategoriasConsulta(){
		
		$valor = $this->idCategoria;
		$valor2 = $this->desdeDos;
		$valor3 = $this->hastaDos;

		$respuesta = ControladorVentas::ctrVentasCategoriasConsulta($valor, $valor2, $valor3);

		echo json_encode($respuesta);

	}

}

/*=============================================
INSERTAR VENTA CAJA
=============================================*/	

if(isset($_POST["producto"])){

	$venta = new AjaxVentas();
	$venta -> codigo = $_POST["producto"];
	$venta -> ajaxVerVentas();

}

/*=============================================
INSERTAR VENTA CAJA
=============================================*/	

if(isset($_POST["productoContulta"])){

	$venta = new AjaxVentas();
	$venta -> productoContulta = $_POST["productoContulta"];
	$venta -> fechaInicio = $_POST["fechaInicio"];
	$venta -> fechaFin = $_POST["fechaFin"];
	$venta -> ajaxVerVentasConsulta();

}

/*=============================================
INSERTAR VENTA CAJA
=============================================*/	

if(isset($_POST["desde"])){

	$venta = new AjaxVentas();
	$venta -> desde = $_POST["desde"];
	$venta -> hasta = $_POST["hasta"];
	$venta -> ajaxVerVentasTotalesConsulta();

}
