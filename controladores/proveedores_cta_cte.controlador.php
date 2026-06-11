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

			// Pago mixto: ingresoMedioPagoCtaCteProveedor es JSON [{"tipo":"EF","entrega":"5000"},...]
			$esPagoMixto = (is_string($dineroMedio) && strlen($dineroMedio) > 0 && $dineroMedio[0] === '[');
			$jsonMetodosPago = $esPagoMixto ? json_decode($dineroMedio, true) : null;

			///VEMOS SI TIENE QUE IMPACTAR EN CAJA ( si tipo es 0 - es un pago - va a caja)
			if ($_POST["tipoMovimientoCtaCteProveedor"] == 0) {
				if ($esPagoMixto && is_array($jsonMetodosPago)) {
					foreach ($jsonMetodosPago as $value) {
						if (isset($value["tipo"]) && $value["tipo"] != 'BO' && $value["tipo"] != 'Bonificacion') {
							$datos = array(
								'fecha' => $fec_hor,
								'id_usuario' => $_POST['idUsuarioMovimientoCtaCteProveedor'],
								'punto_venta' => $_POST['puntoVentaMovimientoCtaCteProveedor'],
								'tipo' => 0,
								'monto' => isset($value["entrega"]) ? $value["entrega"] : 0,
								'medio_pago' => $value["tipo"],
								'descripcion' => $_POST['detalleMovimientoCtaCteProveedor'],
								'codigo_venta' => null,
								"id_venta" => null,
								"id_cliente_proveedor" => $_POST["idProveedorMovimientoCtaCteProveedor"],
								'observaciones' => null
							);
							$respuesta = ModeloCajas::mdlIngresarCaja('cajas', $datos);
						}
					}
				} elseif ($dineroMedio != 'Bonificacion' && $dineroMedio != 'BO') {
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

	static private function ctrRegistrarEgresoCaja($post, $fec_hor, $monto, $medioPago, $descripcion) {
		if ($monto <= 0 || $medioPago == 'Bonificacion' || $medioPago == 'BO') {
			return;
		}
		$datos = array(
			'fecha' => $fec_hor,
			'id_usuario' => $post['idUsuarioMovimientoCtaCteProveedor'],
			'punto_venta' => $post['puntoVentaMovimientoCtaCteProveedor'],
			'tipo' => 0,
			'monto' => $monto,
			'medio_pago' => $medioPago,
			'descripcion' => $descripcion,
			'codigo_venta' => null,
			"id_venta" => null,
			"id_cliente_proveedor" => $post["idProveedorMovimientoCtaCteProveedor"],
			'observaciones' => null
		);
		ModeloCajas::mdlIngresarCaja('cajas', $datos);
	}

	static private function ctrRegistrarEgresosCajaPago($post, $fec_hor, $montoNeto, $descripcion) {
		$dineroMedio = isset($post["ingresoMedioPagoCtaCteProveedor"]) ? $post["ingresoMedioPagoCtaCteProveedor"] : 'Efectivo';
		$esPagoMixto = (is_string($dineroMedio) && strlen($dineroMedio) > 0 && $dineroMedio[0] === '[');
		$jsonMetodosPago = $esPagoMixto ? json_decode($dineroMedio, true) : null;

		if ($esPagoMixto && is_array($jsonMetodosPago)) {
			foreach ($jsonMetodosPago as $value) {
				if (isset($value["tipo"]) && $value["tipo"] != 'BO' && $value["tipo"] != 'Bonificacion') {
					self::ctrRegistrarEgresoCaja($post, $fec_hor, isset($value["entrega"]) ? $value["entrega"] : 0, $value["tipo"], $descripcion);
				}
			}
		} else {
			self::ctrRegistrarEgresoCaja($post, $fec_hor, $montoNeto, $dineroMedio, $descripcion);
		}
	}

	static private function ctrRedirigirExitoCtaCte($idProveedor) {
		echo'<script>
		swal({
			type: "success",
			title: "Proveedores",
			text: "El movimiento ha sido cargado exitosamente",
			showConfirmButton: true,
			confirmButtonText: "Cerrar"
		}).then(function(result){
			if (result.value) {
				window.location = "index.php?ruta=proveedores_cuenta&id_proveedor='.$idProveedor.'";
			}
		})
		</script>';
	}

	/*=============================================
	AGREGAR FACTURA EN CTA CTE PROVEEDOR
	=============================================*/
	static public function ctrCrearFacturaProveedor() {
		if (!isset($_POST["accionCtaCteProveedor"]) || $_POST["accionCtaCteProveedor"] !== 'factura') {
			return;
		}

		date_default_timezone_set('America/Argentina/Mendoza');
		$fecha = isset($_POST["fechaFacturaCtaCte"]) ? $_POST["fechaFacturaCtaCte"] : date('Y-m-d');
		$fec_hor = $fecha . ' ' . date('H:i:s');

		$netoPrevio = (float)(isset($_POST["netoPrevioFacturaCtaCte"]) ? $_POST["netoPrevioFacturaCtaCte"] : 0);
		$descuento = (float)(isset($_POST["descuentoFacturaCtaCte"]) ? $_POST["descuentoFacturaCtaCte"] : 0);
		$neto = (float)(isset($_POST["netoFacturaCtaCte"]) ? $_POST["netoFacturaCtaCte"] : ($netoPrevio - $descuento));
		$iva = (float)(isset($_POST["ivaFacturaCtaCte"]) ? $_POST["ivaFacturaCtaCte"] : 0);
		$total = (float)(isset($_POST["totalFacturaCtaCte"]) ? $_POST["totalFacturaCtaCte"] : ($neto + $iva));
		$numFactura = trim(isset($_POST["numeroFacturaCtaCte"]) ? $_POST["numeroFacturaCtaCte"] : '');
		$descripcion = trim(isset($_POST["detalleFacturaCtaCte"]) ? $_POST["detalleFacturaCtaCte"] : '');
		if ($descripcion === '') {
			$descripcion = 'Factura N° ' . $numFactura;
		}

		$datosCtaCte = array(
			'fecha_movimiento' => $fec_hor,
			'id_proveedor' => $_POST["idProveedorMovimientoCtaCteProveedor"],
			'tipo' => 1,
			'descripcion' => $descripcion,
			'id_compra' => null,
			'importe' => $total,
			'metodo_pago' => null,
			'id_usuario' => $_POST["idUsuarioMovimientoCtaCteProveedor"],
			'factura_numero' => $numFactura,
			'factura_neto_previo' => $netoPrevio,
			'factura_descuento' => $descuento,
			'factura_neto' => $neto,
			'factura_iva' => $iva,
			'total' => $total,
			'fecha_retencion' => null,
			'numero_recibo' => null,
			'alicuota_retencion' => null,
			'monto_retencion' => null
		);

		$respuesta = ModeloProveedoresCtaCte::mdlIngresarCtaCteProveedorExtendido('proveedores_cuenta_corriente', $datosCtaCte);
		if ($respuesta == "ok") {
			self::ctrRedirigirExitoCtaCte($_POST["idProveedorMovimientoCtaCteProveedor"]);
		}
	}

	/*=============================================
	AGREGAR PAGO EN CTA CTE PROVEEDOR (con retención opcional)
	=============================================*/
	static public function ctrCrearPagoProveedor() {
		if (!isset($_POST["accionCtaCteProveedor"]) || $_POST["accionCtaCteProveedor"] !== 'pago') {
			return;
		}

		date_default_timezone_set('America/Argentina/Mendoza');
		$fecha = isset($_POST["fechaPagoCtaCte"]) ? $_POST["fechaPagoCtaCte"] : date('Y-m-d');
		$fec_hor = $fecha . ' ' . date('H:i:s');

		$conRetencion = isset($_POST["aplicarRetencionCtaCte"]) && $_POST["aplicarRetencionCtaCte"] == '1';
		$montoNeto = (float)(isset($_POST["montoNetoPagoCtaCte"]) ? $_POST["montoNetoPagoCtaCte"] : $_POST["montoMovimientoCtaCteProveedor"]);
		$montoRetencion = 0;
		$alicuota = null;
		$numRecibo = null;
		$fechaRetencion = null;
		$numFactura = trim(isset($_POST["numeroFacturaPagoCtaCte"]) ? $_POST["numeroFacturaPagoCtaCte"] : '');
		$montoSujeto = (float)(isset($_POST["montoSujetoPagoCtaCte"]) ? $_POST["montoSujetoPagoCtaCte"] : 0);
		$facturaNeto = $montoSujeto;

		if ($conRetencion) {
			$alicuota = (float)(isset($_POST["alicuotaRetencionCtaCte"]) ? $_POST["alicuotaRetencionCtaCte"] : 0);
			$montoRetencion = round($montoSujeto * $alicuota / 100, 2);
			if (isset($_POST["montoRetencionCtaCte"]) && $_POST["montoRetencionCtaCte"] !== '') {
				$montoRetencion = (float)$_POST["montoRetencionCtaCte"];
			}
			$fechaRetencion = isset($_POST["fechaRetencionCtaCte"]) ? $_POST["fechaRetencionCtaCte"] : $fecha;
			$idEmpresa = isset($_SESSION['empresa']) ? (int)$_SESSION['empresa'] : 1;
			$numRecibo = ModeloRetencionesIibb::mdlReservarNumeroRecibo($idEmpresa);
			if (!$montoNeto && $montoSujeto) {
				$montoNeto = max(0, $montoSujeto - $montoRetencion);
			}
		} else {
			if (!$montoNeto) {
				$montoNeto = (float)$_POST["montoMovimientoCtaCteProveedor"];
			}
		}

		$importeCtaCte = $conRetencion ? ($montoNeto + $montoRetencion) : $montoNeto;
		$descripcion = trim(isset($_POST["detalleMovimientoCtaCteProveedor"]) ? $_POST["detalleMovimientoCtaCteProveedor"] : '');
		if ($descripcion === '') {
			$descripcion = 'Pago Cta. Cte. proveedor';
			if ($conRetencion && $numRecibo) {
				$descripcion .= ' - Ret. recibo N° ' . $numRecibo;
			}
		}

		$datosCtaCte = array(
			'fecha_movimiento' => $fec_hor,
			'id_proveedor' => $_POST["idProveedorMovimientoCtaCteProveedor"],
			'tipo' => 0,
			'descripcion' => $descripcion,
			'id_compra' => null,
			'importe' => $importeCtaCte,
			'metodo_pago' => isset($_POST["ingresoMedioPagoCtaCteProveedor"]) ? $_POST["ingresoMedioPagoCtaCteProveedor"] : null,
			'id_usuario' => $_POST["idUsuarioMovimientoCtaCteProveedor"],
			'factura_numero' => $numFactura !== '' ? $numFactura : null,
			'factura_neto_previo' => null,
			'factura_descuento' => null,
			'factura_neto' => $conRetencion ? $facturaNeto : null,
			'factura_iva' => null,
			'total' => null,
			'fecha_retencion' => $fechaRetencion,
			'numero_recibo' => $numRecibo,
			'alicuota_retencion' => $alicuota,
			'monto_retencion' => $conRetencion ? $montoRetencion : null
		);

		$respuesta = ModeloProveedoresCtaCte::mdlIngresarCtaCteProveedorExtendido('proveedores_cuenta_corriente', $datosCtaCte);

		if ($respuesta == "ok") {
			self::ctrRegistrarEgresosCajaPago($_POST, $fec_hor, $montoNeto, $descripcion);
			self::ctrRedirigirExitoCtaCte($_POST["idProveedorMovimientoCtaCteProveedor"]);
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