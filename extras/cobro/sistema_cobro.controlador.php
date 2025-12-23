<?php

class ControladorSistemaCobro{

	/*=============================================
	MOSTRAR CLIENTES COBRO
	=============================================*/
	static public function ctrMostrarClientesCobro($idCliente){

		$respuesta = ModeloSistemaCobro::mdlMostrarClientesCobro($idCliente);

		return $respuesta;
	}

	/*=============================================
	MOSTRAR SALDO CUENTA CORRIENTE
	=============================================*/
	static public function ctrMostrarSaldoCuentaCorriente($idCliente) {

		$respuesta = ModeloSistemaCobro::mdlMostrarSaldoCuentaCorriente($idCliente);

		return $respuesta;
	}

	/*=============================================
	ACTUALIZAR ESTADO CLIENTES
	=============================================*/
	static public function ctrActualizarClientesCobro($idCliente, $estado) {

		$respuesta = ModeloSistemaCobro::mdlActualizarClientesCobro($idCliente, $estado);

		return $respuesta;
	}

	/*=============================================
	ACTUALIZAR ESTADO CLIENTES
	=============================================*/
	static public function ctrMostrarMovimientoCuentaCorriente($idCliente) {

		$respuesta = ModeloSistemaCobro::mdlMostrarMovimientoCuentaCorriente($idCliente);

		return $respuesta;
	}

	/*=============================================
	ACTUALIZAR ESTADO CLIENTES
	=============================================*/
	static public function ctrRegistrarMovimientoCuentaCorriente($idCliente, $importe) {

		$respuesta = ModeloSistemaCobro::mdlRegistrarMovimientoCuentaCorriente($idCliente, $importe);

		return $respuesta;
	}
	
	/*=============================================
	ACTUALIZAR ESTADO CLIENTES
	=============================================*/
	static public function ctrRegistrarInteresCuentaCorriente($idCliente, $importe) {

		$respuesta = ModeloSistemaCobro::mdlRegistrarInteresCuentaCorriente($idCliente, $importe);

		return $respuesta;
	}

}
