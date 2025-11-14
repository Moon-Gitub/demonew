<?php

class ControladorCajas{

	/*=============================================
	CREAR CAJA
	=============================================*/
	static public function ctrCrearCaja(){
	    
		if(isset($_POST["ingresoCajaTipo"])){ //0 egreso - 1 ingreso - 2 movimiento interno

	   		if(isset($_POST["ingresoCajaidVenta"])) {
	   			$respuestaVentaEstado = ModeloVentas::mdlActualizarVenta("ventas", "estado", 1, $_POST["ingresoCajaidVenta"]);
	   		}

		   	date_default_timezone_set('America/Argentina/Mendoza');

			$fecha = date('Y-m-d');
			$hora = date('H:i:s');
			$fec_hor = $fecha.' '.$hora;
		   	$tabla = "cajas";

			$dineroMedio = (isset($_POST["ingresoMedioPago"])) ? $_POST["ingresoMedioPago"] : 'Efectivo';

			$codVenta = (isset($_POST["ingresoCajaCodVenta"])) ? $_POST["ingresoCajaCodVenta"] : "";

			$observa = (isset($_POST["ingresoObservacionesCajaCentral"])) ? $_POST["ingresoObservacionesCajaCentral"] : "";

	   		$msjCaja = (($_POST["ingresoCajaTipo"] == 1) ? "Ingreso" : "Egreso");

	   		$idVenta = (isset($_POST["ingresoCajaidVenta"])) ? $_POST["ingresoCajaidVenta"] : null;
			
	   		$datos = array(
	   				"id_usuario" => $_POST["idUsuarioMovimiento"],
	   				"punto_venta" => $_POST["puntoVentaMovimiento"],
	   				"tipo" => $_POST["ingresoCajaTipo"],
	   				"descripcion" => $_POST["ingresoDetalleCajaCentral"],
	   				"monto" => $_POST["ingresoMontoCajaCentral"],
	   				"medio_pago" => $dineroMedio,
	   				"codigo_venta" => $codVenta,
	   				"fecha" => $fec_hor,
	   				"id_venta" => $idVenta,
	   				"id_cliente_proveedor" => null, 
	   				"observaciones" => $observa);

	   		$respuesta = ModeloCajas::mdlIngresarCaja($tabla, $datos);

		   	if($respuesta == "ok"){

		  //  		if ($_POST["ingresoCajaDesde"] == "cajas") {
		   			
		  //  			echo'<script>

				// 	swal({
				// 	  type: "success",
				// 	  title: "' . $msjCaja . ' cargado correctamente",
				// 	  toast: true,
				// 	  timer: 3000,
				// 	  position: "top",
				// 		confirmButtonText: "¡Cerrar!"

				// 	  });
					  
				// 	$("#tablaCajaCentral").DataTable().ajax.reload();

				// 	</script>';

				// } else {

					echo'<script>

					swal({
					  type: "success",
					  title: "Caja",
					  text: "' . $msjCaja . ' cargado correctamente",
					  showConfirmButton: true,
					  confirmButtonText: "Cerrar",
					  allowOutsideClick: false
					  }).then(function(result){
								if (result.value) {

								window.location = "' . $_POST["ingresoCajaDesde"] . '";

								}
							})

					</script>';
		   			
				// }
			}
		}
	}

	/*=============================================
	RANGO FECHAS
	=============================================*/	
	static public function ctrRangoFechasCajas($fechaInicial, $fechaFinal, $numCaja){
		$tabla = "cajas";
		$respuesta = ModeloCajas::mdlRangoFechasCajas($tabla, $fechaInicial, $fechaFinal, $numCaja);
		return $respuesta;
	}
	
	/*=============================================
	RANGO IDS
	=============================================*/	
	static public function ctrRangoIdsCajas($idInicial, $idFinal, $numCaja){
		$tabla = "cajas";
		$respuesta = ModeloCajas::mdlRangoIdsCajas($tabla, $idInicial, $idFinal, $numCaja);
		return $respuesta;
	}

	/*=============================================
	SUMA TOTAL CAJAS
	=============================================*/
	static public function ctrSumaTotalCajas(){
		$tabla = "cajas";
		$respuesta = ModeloCajas::mdlSumaTotalCajas($tabla);
		return $respuesta;
	}

	/*=============================================
	MOSTRAR CAJAS
	=============================================*/
	static public function ctrMostrarCajas($item, $valor){
		$tabla = "cajas";
		$respuesta = ModeloCajas::mdlMostrarCajas($tabla, $item, $valor);
		return $respuesta;
	}

	/*=============================================
	SALDO DE CAJA CENTRAL A FECHA XX
	=============================================*/
	static public function ctrSaldoCajaAl($fecha, $numCaja){
		$respuesta = ModeloCajas::mdlSaldoCajaAl($fecha, $numCaja);
		return $respuesta;
	}	

	/*=============================================
	TEXTO DESCRIPCION
	=============================================*/
	static public function ctrMostrarDescripcion($txt){
		$respuesta = ModeloCajas::mdlMostrarDescripcion($txt);
		return $respuesta;
	}

	/*=============================================
	TOTALES GASTOS RANGO FECHA
	=============================================*/
	static public function ctrRangoTotalesGastos($fechaInicial, $fechaFinal){
		$respuesta = ModeloCajas::mdlRangoTotalesGastos($fechaInicial, $fechaFinal);
		return $respuesta;
	}

	/*=============================================
	TOTALES RETIROS MM
	=============================================*/
	static public function ctrRangoTotalesRetirosMM($fechaInicial, $fechaFinal){
		$respuesta = ModeloCajas::mdlRangoTotalesRetirosMM($fechaInicial, $fechaFinal);
		return $respuesta;
	}

	/*=============================================
	TOTALES CONSUMICIONES MM
	=============================================*/
	static public function ctrRangoTotalesConsumicionesMM($fechaInicial, $fechaFinal){
		$respuesta = ModeloCajas::mdlRangoTotalesConsumicionesMM($fechaInicial, $fechaFinal);
		return $respuesta;
	}

	/*=============================================
	Movimientos desde ultimo cierre
	=============================================*/	
	static public function ctrMovimientosCajaDesdeUltimoCierre($ultimoCierre){
		$respuesta = ModeloCajas::mdlMovimientosCajaDesdeUltimoCierre($ultimoCierre);
		return $respuesta;
	}

	/*=============================================
	Medios de pago usados
	=============================================*/	
	static public function ctrMediosPagosUsados(){
		$respuesta = ModeloCajas::mdlMediosPagosUsados();
		return $respuesta;
	}

	/*=============================================
	Medios de pago usados egresos
	=============================================*/	
	static public function ctrSumatoriaMedios($tipo, $medio, $desdeFecha, $hastaFecha, $numCaja){
		$respuesta = ModeloCajas::mdlSumatoriaMedios($tipo, $medio, $desdeFecha, $hastaFecha, $numCaja);
		return $respuesta;
	}	

	/*=============================================
	RANGO FECHAS DESDE ULTIMO CIERRE
	=============================================*/	
	static public function ctrRangoFechasCajasUltimoCierre($ultimoIdCaja, $numCaja){
		$respuesta = ModeloCajas::mldRangoFechasCajasUltimoCierre($ultimoIdCaja, $numCaja);
		return $respuesta;
	}

 }