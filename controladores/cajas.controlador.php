<?php

class ControladorCajas{

	/*=============================================
	CREAR CAJA
	=============================================*/
	static public function ctrCrearCaja(){
	    
		if(isset($_POST["ingresoCajaTipo"])){ //0 egreso - 1 ingreso - 2 movimiento interno
			
			// Iniciar sesión si no está iniciada
			if (session_status() === PHP_SESSION_NONE) {
				session_start();
			}
			
			// Generar token CSRF si no existe
			if (!isset($_SESSION['csrf_token'])) {
				$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
			}
			
			// Validar CSRF token
			$token = isset($_POST['csrf_token']) ? $_POST['csrf_token'] : null;
			if (!$token || !isset($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
				echo'<script>
					swal({
						type: "error",
						title: "Acceso denegado",
						text: "Token CSRF inválido. Por favor, recargá la página.",
						showConfirmButton: true,
						confirmButtonText: "Cerrar"
					});
				</script>';
				return;
			}

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

				// Si viene desde la página de cajas, usar toast y recargar página
				if (isset($_POST["ingresoCajaDesde"]) && $_POST["ingresoCajaDesde"] == "cajas") {
					
					echo'<script>
						swal({
							type: "success",
							title: "Caja",
							text: "' . $msjCaja . ' cargado correctamente",
							toast: true,
							timer: 1500,
							position: "top",
							showConfirmButton: false,
							allowOutsideClick: false
						});
						
						// Cerrar el modal y recargar la página después del toast
						$("#modalAgregarMovimientoCaja").modal("hide");
						
						setTimeout(function(){
							window.location.reload();
						}, 1500);
					</script>';

				} else {

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
								window.location = "' . (isset($_POST["ingresoCajaDesde"]) ? $_POST["ingresoCajaDesde"] : "cajas") . '";
							}
						})
					</script>';
				}
			}
		}
	}

	/*=============================================
	CREAR CAJA (AJAX - devuelve JSON)
	=============================================*/
	static public function ctrCrearCajaAjax($datos){
	    
		if(isset($datos["ingresoCajaTipo"])){ //0 egreso - 1 ingreso - 2 movimiento interno
			
			// Iniciar sesión si no está iniciada
			if (session_status() === PHP_SESSION_NONE) {
				session_start();
			}
			
			// Generar token CSRF si no existe
			if (!isset($_SESSION['csrf_token'])) {
				$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
			}
			
			// Validar CSRF token
			$token = isset($datos['csrf_token']) ? $datos['csrf_token'] : null;
			if (!$token || !isset($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
				return array(
					"status" => "error",
					"mensaje" => "Token CSRF inválido. Por favor, recargá la página."
				);
			}

	   		if(isset($datos["ingresoCajaidVenta"])) {
	   			$respuestaVentaEstado = ModeloVentas::mdlActualizarVenta("ventas", "estado", 1, $datos["ingresoCajaidVenta"]);
	   		}

		   	date_default_timezone_set('America/Argentina/Mendoza');

			$fecha = date('Y-m-d');
			$hora = date('H:i:s');
			$fec_hor = $fecha.' '.$hora;
		   	$tabla = "cajas";

			$dineroMedio = (isset($datos["ingresoMedioPago"])) ? $datos["ingresoMedioPago"] : 'Efectivo';

			$codVenta = (isset($datos["ingresoCajaCodVenta"])) ? $datos["ingresoCajaCodVenta"] : "";

			$observa = (isset($datos["ingresoObservacionesCajaCentral"])) ? $datos["ingresoObservacionesCajaCentral"] : "";

	   		$msjCaja = (($datos["ingresoCajaTipo"] == 1) ? "Ingreso" : "Egreso");

	   		$idVenta = (isset($datos["ingresoCajaidVenta"])) ? $datos["ingresoCajaidVenta"] : null;
			
	   		$datosArray = array(
	   				"id_usuario" => $datos["idUsuarioMovimiento"],
	   				"punto_venta" => $datos["puntoVentaMovimiento"],
	   				"tipo" => $datos["ingresoCajaTipo"],
	   				"descripcion" => $datos["ingresoDetalleCajaCentral"],
	   				"monto" => $datos["ingresoMontoCajaCentral"],
	   				"medio_pago" => $dineroMedio,
	   				"codigo_venta" => $codVenta,
	   				"fecha" => $fec_hor,
	   				"id_venta" => $idVenta,
	   				"id_cliente_proveedor" => null, 
	   				"observaciones" => $observa);

	   		$respuesta = ModeloCajas::mdlIngresarCaja($tabla, $datosArray);

		   	if($respuesta == "ok"){
				return array(
					"status" => "ok",
					"mensaje" => $msjCaja . " cargado correctamente"
				);
			} else {
				return array(
					"status" => "error",
					"mensaje" => "Error al guardar el movimiento: " . $respuesta
				);
			}
		}
		
		return array(
			"status" => "error",
			"mensaje" => "Datos inválidos"
		);
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