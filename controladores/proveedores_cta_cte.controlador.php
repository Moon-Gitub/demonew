<?php

class ControladorProveedoresCtaCte{

	/*=============================================
	MOSTRAR CTA CTE Proveedores
	=============================================*/
	static public function ctrMostrarCtaCteProveedores($item, $valor){

		$tabla = "proveedores_cuenta_corriente";

		$respuesta = ModeloProveedoresCtaCte::mdlMostrarCtaCteProveedor($tabla, $item, $valor);

		return $respuesta;

	}
		
	/*=============================================
	MOSTRAR CTA CTE Proveedores
	=============================================*/
	static public function ctrMostrarCtaCteProveedor($valor){

		$tablaCtaCte = "proveedores_cuenta_corriente";

		$respuesta = ModeloProveedoresCtaCte::mdlMostrarCtaCteProveedorDos($tablaCtaCte, $valor);

		return $respuesta;

	}

	/*=============================================
	MOSTRAR CTA CTE Proveedores
	=============================================*/
	static public function ctrSumarCompras($valor){

		$tablaCtaCte = "proveedores_cuenta_corriente";

		$respuesta = ModeloProveedoresCtaCte::mdlSumarCompras($tablaCtaCte, $valor);

		return $respuesta;

	}

	/*=============================================
	MOSTRAR CTA CTE Proveedores
	=============================================*/
	static public function ctrSumarComprasListado($valor, $fecha){

		$tablaCtaCte = "proveedores_cuenta_corriente";

		$respuesta = ModeloProveedoresCtaCte::mdlSumarComprasListado($tablaCtaCte, $valor, $fecha);

		return $respuesta;

	}
	
	/*=============================================
	MOSTRAR CTA CTE Proveedores
	=============================================*/
	static public function ctrSumarRemitos($valor){

		$tablaCtaCte = "proveedores_cuenta_corriente";

		$respuesta = ModeloProveedoresCtaCte::mdlSumarRemitos($tablaCtaCte, $valor);

		return $respuesta;

	}

	/*=============================================
	MOSTRAR CTA CTE Proveedores
	=============================================*/
	static public function ctrSumarRemitosListado($valor, $fecha){

		$tablaCtaCte = "proveedores_cuenta_corriente";

		$respuesta = ModeloProveedoresCtaCte::mdlSumarRemitosListado($tablaCtaCte, $valor, $fecha);

		return $respuesta;

	}
	
	/*=============================================
	MOSTRAR CTA CTE Proveedores
	=============================================*/
	static public function ctrSumarPagos($valor){

		$tablaCtaCte = "proveedores_cuenta_corriente";

		$respuesta = ModeloProveedoresCtaCte::mdlSumarPagos($tablaCtaCte, $valor);

		return $respuesta;

	}

	/*=============================================
	MOSTRAR CTA CTE Proveedores
	=============================================*/
	static public function ctrSumarPagosListado($valor, $fecha){

		$tablaCtaCte = "proveedores_cuenta_corriente";

		$respuesta = ModeloProveedoresCtaCte::mdlSumarPagosListado($tablaCtaCte, $valor, $fecha);

		return $respuesta;

	}
	
	/*=============================================
	MOSTRAR CTA CTE Proveedores
	=============================================*/
	static public function ctrNotasCreditos($valor){

		$tablaCtaCte = "proveedores_cuenta_corriente";

		$respuesta = ModeloProveedoresCtaCte::mdlCuentasPagos($tablaCtaCte, $valor);

		return $respuesta;

	}

	/*=============================================
	MOSTRAR CTA CTE Proveedores
	=============================================*/
	static public function ctrNotasCreditosListado($valor, $fecha){

		$tablaCtaCte = "proveedores_cuenta_corriente";

		$respuesta = ModeloProveedoresCtaCte::mdlCuentasPagosListado($tablaCtaCte, $valor, $fecha);

		return $respuesta;

	}

	/*=============================================
	ELIMINAR REGISTRO CTA CTE Proveedores
	=============================================*/
	static public function ctrEliminarCtaCteProveedores(){
		if(isset($_GET["idMovimiento"])){

			$tabla ="proveedores_cuenta_corriente";
			$datos = $_GET["idMovimiento"];
			$proveedor = $_GET["id_proveedor"];

			$respuesta = ModeloProveedoresCtaCte::mdlEliminarCtaCteProveedores($tabla, $datos);

			if($respuesta == "ok"){
			
			$direccion = "index.php?ruta=proveedores_cuenta&id_proveedor=".''.$proveedor;
				echo'<script>

				swal({
					  type: "success",
					  title: "El Movimiento ha sido borrado correctamente",
					  showConfirmButton: true,
					  confirmButtonText: "Cerrar",
					  closeOnConfirm: false
					  }).then(function(result){
								if (result.value) {

								window.location = "' . $direccion . '";

								}
							})

				</script>';

			}		

		}
	}

	/*=============================================
	AGREGAR REGISTRO CTA CTE PROVEEDORES
	=============================================*/
	static public function ctrCrearRegistroProveedores(){

		if(isset($_POST["tipoMovimientoCtaCteProveedor"])){ //0 PAGO AL PROVEEDOR - 1 COMPRO AL PROVEEDOR

			date_default_timezone_set('America/Argentina/Mendoza');

			$fecha = date('Y-m-d');
			$hora = date('H:i:s');
			$fec_hor = $fecha.' '.$hora;

			$tabla = "proveedores_cuenta_corriente";

			$msjCaja = (($_POST["tipoMovimientoCtaCteProveedor"] == 1) ? "Débito" : "Pago");

			$datosCtaCte = array(
					'fecha_movimiento' => $fec_hor,
					'id_proveedor' => $_POST["idProveedorMovimientoCtaCteProveedor"],
					'tipo' => $_POST["tipoMovimientoCtaCteProveedor"],
					'descripcion' => $_POST["detalleMovimientoCtaCteProveedor"], 
					'id_compra' => null, 
					'importe' => $_POST["montoMovimientoCtaCteProveedor"], 
					'metodo_pago' => $_POST["ingresoMedioPagoCtaCteProveedor"],
					'id_usuario' => $_POST["idUsuarioMovimientoCtaCteProveedor"]);

			$respuesta = ModeloProveedoresCtaCte::mdlIngresarCtaCteProveedor($tabla, $datosCtaCte);

			$dineroMedio = (isset($_POST["ingresoMedioPagoCtaCteProveedor"])) ? $_POST["ingresoMedioPagoCtaCteProveedor"] : 'Efectivo';

			///VEMOS SI TIENE QUE IMPACTAR EN CAJA ( si tipo es 0 - es un pago - va a caja)
			if($_POST["tipoMovimientoCtaCteProveedor"] == 0 && $dineroMedio != 'Bonificacion') {

		   		//INGRESO DATOS A CAJA
		   		$datos = array(
		   				'fecha' => $fec_hor,
		   				'id_usuario' => $_POST['idUsuarioMovimientoCtaCteProveedor'],
		   				'punto_venta' => $_POST['puntoVentaMovimientoCtaCteProveedor'],
		   				'tipo' => 0,
		   				'monto' => $_POST['montoMovimientoCtaCteProveedor'],
		   				'medio_pago' => $dineroMedio,
		   				'descripcion' => $_POST['detalleMovimientoCtaCteProveedor'],
		   				'codigo_venta' => null,
		   				"id_venta" => null,
	   					"id_cliente_proveedor" => $_POST["idProveedorMovimientoCtaCteProveedor"],
		   				'observaciones' => null
		   				);

		   		$respuesta = ModeloCajas::mdlIngresarCaja('cajas', $datos);

	   		}

			if($respuesta == "ok"){

				echo'<script>

				swal({
					  type: "success",
					  title: "Proveedores",
					  text: "El movimiento ha sido cargado exitosamente",
					  showConfirmButton: true,
					  confirmButtonText: "Cerrar"
					  }).then(function(result){
						if (result.value) {
							window.location = "index.php?ruta=proveedores_cuenta&id_proveedor='.$_POST["idProveedorMovimientoCtaCteProveedor"].'";
						}
					})

				</script>';

			}

		}

	}
	
	/*=============================================
	MOSTRAR REGISTRO DE CUENTA CORRIENTE PROVEEDOR
	=============================================*/
	static public function ctrMostrarRegistroCtaCteProveedor($idReg){
	    
	    $respuesta = ModeloProveedoresCtaCte::mdlMostrarRegistroCtaCteProveedor('proveedores_cuenta_corriente', $idReg);
	    
	    return $respuesta;

	}

	/*=============================================
	LISTADO DE PROVEEDORES CON SALDO EN CUENTA CORRIENTE
	Esta consulta trae los proveedores donde total de compras - total de pagos es distindo de 0
	Usada en proveedoress.php
	=============================================*/
	static public function ctrMostrarSaldos(){

		$respuesta = ModeloProveedoresCtaCte::mdlMostrarSaldos();

		return $respuesta;

	}

	/*=============================================
	SALDO TOTAL EN CUENTA CORRIENTE
	Usada en proveedores-cuenta-saldos y en inicio
	=============================================*/
	static public function ctrMostrarSaldoTotal(){

		$respuesta = ModeloProveedoresCtaCte::mdlMostrarSaldoTotal();
		return $respuesta;
	}


}