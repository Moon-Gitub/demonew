<?php
//DEMO
class ControladorCajaCierres{

	/*=============================================
	CREAR CAJA
	=============================================*/
	static public function ctrCrearCierreCaja(){

		if(isset($_POST["aperturaSiguienteMonto"])){

		   	date_default_timezone_set('America/Argentina/Buenos_Aires');
			$fecha = date('Y-m-d');
			$hora = date('H:i:s');
			$fec_hor = $fecha.' '.$hora;
	
	   		$datos = array("fecha_hora"=>$fec_hor,
			           "ultimo_id_caja"=> $_POST["ultimoIdCajaCierre"],
			           "punto_venta_cobro" => $_POST['puntoVentaCierre'],
			           "total_ingresos"=>$_POST["totalIngresosCierre"],
			           "total_egresos"=>$_POST["totalEgresosCierre"],
			           "detalle_ingresos"=>$_POST["detalleIngresosCierre"],
			           "detalle_egresos"=>$_POST["detalleEgresosCierre"],
			           "apertura_siguiente_monto"=>$_POST["aperturaSiguienteMonto"],
			           "id_usuario_cierre" => $_POST["idUsuarioCierre"],
			       	   "detalle" => $_POST["cierreCajaDetalle"], 
			       	   "detalle_ingresos_manual" => (isset($_POST["totalIngresosCierreManual"])) ? $_POST["totalIngresosCierreManual"] : null,
			       	   "detalle_egresos_manual" => (isset($_POST["totalEgresosCierreManual"])) ? $_POST["totalEgresosCierreManual"] : null,
			       	   "diferencias" => (isset($_POST["totalDiferenciasCierre"])) ? $_POST["totalDiferenciasCierre"] : null,

			       	);

	   		$respuesta = ModeloCajaCierres::mdlIngresarCierreCaja($datos);

		   	if($respuesta == "ok"){

	   			echo'<script>

					swal({
					  type: "success",
					  title: "Caja",
					  text: "Cierre caja cargado correctamente",
					  showConfirmButton: true,
					  confirmButtonText: "Cerrar"
					  }).then(function(result){
							if (result.value) {

								window.location = "cajas";

							}
						})
	
					</script>';


			} else {

				echo'<script>

					swal({
					  type: "error",
					  title: "Caja",
					  text: "' .json_encode($respuesta) . '",
					  showConfirmButton: true,
					  confirmButtonText: "Cerrar"
					  }).then(function(result){
							if (result.value) {

								window.location = "cajas";

							}
						})

					</script>';

			}
			
		}

	}
	
	/*=============================================
	CREAR CAJA CAJERO
	=============================================*/
	static public function ctrCierreCajaCajero($datosPost){

		if(isset($datosPost["apertura_siguiente_monto"])){

			date_default_timezone_set('America/Argentina/Buenos_Aires');
			
	   		$datos = array(
				"fecha_hora"=>$datosPost["fecha_hora"] . ' ' . date('H:i:s'),
				"ultimo_id_caja"=> $datosPost["ultimo_id_caja"],
				"punto_venta_cobro" => $datosPost['punto_venta_cobro'],
				"total_ingresos"=>$datosPost["total_ingresos"],
				"total_egresos"=>$datosPost["total_egresos"],
				"detalle_ingresos"=>$datosPost["detalle_ingresos"],
				"detalle_egresos"=>$datosPost["detalle_egresos"],
				"apertura_siguiente_monto"=>$datosPost["apertura_siguiente_monto"],
				"id_usuario_cierre" => $datosPost["id_usuario_cierre"],
				"detalle" => $datosPost["detalle"], 
				"detalle_ingresos_manual" => $datosPost["detalle_ingresos_manual"],
				"detalle_egresos_manual" => $datosPost["detalle_egresos_manual"],
				"diferencias" => $datosPost["diferencias"]
			);

	   		$respuesta = ModeloCajaCierres::mdlIngresarCierreCaja($datos);
		   	return $respuesta;
		}
	}

	/*=============================================
	MOSTRAR CIERRES DE CAJA
	=============================================*/	
	static public function ctrRangoFechasCajaCierres($fechaInicial, $fechaFinal){
		$respuesta = ModeloCajaCierres::mdlRangoFechasCajaCierres($fechaInicial, $fechaFinal);
		return $respuesta;
	}

	/*=============================================
	MOSTRAR CIERRES DE CAJA
	=============================================*/	
	static public function ctrMostrarCierresCaja($idCierre){
		$respuesta = ModeloCajaCierres::mdlMostrarCierresCaja($idCierre);
		return $respuesta;
	}

	/*=============================================
	ULTIMO CIERRE CAJA
	=============================================*/	
	static public function ctrUltimoCierreCaja($numCaja){
		$respuesta = ModeloCajaCierres::mdlUltimoCierreCaja($numCaja);
		return $respuesta;
	}

	/*============================================
	INFORME CIERRE CAJAS
	=============================================*/
	static public function ctrInformeCierreCajas($idCierre){
		$cierreCaja = ModeloCajaCierres::mdlMostrarCierresCaja($idCierre); //datos del cierre
		$cierreCaja["id_usuario_cierre"] = ModeloUsuarios::mdlMostrarUsuariosPorId($cierreCaja["id_usuario_cierre"])["nombre"];
		$cierreCajaAnterior = ModeloCajaCierres::mdlAnteriorSeleccionadoCierreCaja($cierreCaja["punto_venta_cobro"], $idCierre); //datos del cierre anterior
		$cierreCajaAnterior["ultimo_id_caja"] = (isset($cierreCajaAnterior["ultimo_id_caja"])) ? $cierreCajaAnterior["ultimo_id_caja"] : 1;
		$cajas = ModeloCajas::mdlMovimientosCajaSegunCierre($cierreCaja["punto_venta_cobro"], $cierreCajaAnterior["ultimo_id_caja"], $cierreCaja["ultimo_id_caja"]); //movimientos de caja entre el cierre anterior y el elegido
		$categorias = ModeloCategorias::mdlMostrarCategorias('categorias', null, null); //traigo todas las categorias
		$datos = array('ingresos' => array(), 'egresos' => array(), 'otros' => $cierreCaja); //defino array de datos
		$indexIngresos = 0;
		$indexEgresos = 0;
		
		foreach ($categorias as $key => $valueCat) { //cargo el array de datos con las categorias existentes
			$datos["ingresos"] += [$indexIngresos => array('id' => $valueCat["id"],'descripcion' => $valueCat["categoria"], 'monto' => 0, 'tipo' => 'categoria')];
			$indexIngresos++;
		}

		foreach ($cajas as $key => $value) {
			if($value["tipo"] == 0) { //pago o gasto
				if(isset($value["id_cliente_proveedor"])) { //es un pago de cta cte proveedor
					$nombreProveedor = ModeloProveedores::mdlMostrarProveedoresPorId($value["id_cliente_proveedor"])["nombre"];
					$datos["egresos"][$indexEgresos] = array('id' => $value["id"], 'tipo' => 'proveedor', 'descripcion' => $nombreProveedor, 'monto' => $value["monto"]);
				} else {
					$datos["egresos"][$indexEgresos] = array('id' => $value["id"], 'tipo' => 'comun', 'descripcion' => $value["descripcion"], 'monto' => $value["monto"]);
				}
				$indexEgresos++;

			} else { //ingreso
				if(isset($value["id_venta"])){ //es un ingreso por venta 
					$venta = ModeloVentas::mdlMostrarVentaConCliente($value["id_venta"]); //traigo venta
					$separoProd = json_decode($venta["productos"], true); //separo productos
					
					foreach ($separoProd as $keyPro => $valuePro) {
						$cate_prod = ModeloProductos::mdlMostrarCategoriaProducto($valuePro["id"]); //consulto categoria producto
						$itemArray = array_search($cate_prod["id"], array_column($datos["ingresos"], 'id'));
    					$datos["ingresos"][$itemArray]["monto"] += $valuePro["total"];

					}

				} elseif(isset($value["id_cliente_proveedor"])) { //ingreso por cta cte cliente
					$nombreCliente = ModeloClientes::mdlMostrarClientesPorId($value["id_cliente_proveedor"]);
					$datos["ingresos"][$indexIngresos] = array('id' => $value["id"], 'tipo' => 'cliente', 'descripcion' => $nombreCliente["nombre"], 'monto' => $value["monto"]);
					$indexIngresos++;

				} else { //ingreso de otro tipo
					$datos["ingresos"][$indexIngresos] = array('id' => $value["id"], 'tipo' => 'comun', 'descripcion' => $value["descripcion"], 'monto' => $value["monto"]);
					$indexIngresos++;

				}
			}
		}
		return $datos;
	}

	/*============================================
    MOVIMIENTOS DE CAJA ENTRE CIERRES
	=============================================*/
	static public function ctrMovimientosCierreCajas($idCierre){
	    
	    $cierreCajaHasta = ModeloCajaCierres::mdlMostrarCierresCaja($idCierre); //datos del cierre
	    
	    $idCierreAnt = $idCierre - 1;
	    $cierreCajaDesde = ModeloCajaCierres::mdlMostrarCierresCaja($idCierreAnt); //datos del cierre anterior (obtengo id desde)
	    
	    $idCajaDesde = $cierreCajaDesde["ultimo_id_caja"] + 1;
	    $idCajaHasta = $cierreCajaHasta["ultimo_id_caja"];
	    $listado = ModeloCajas::mdlRangoIdsCajas('cajas', $idCajaDesde, $idCajaHasta, $cierreCajaHasta["punto_venta_cobro"]);
	    
	    return $listado;
	    
	}
 }