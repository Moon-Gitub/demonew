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
		
		// Validar que se encontró el cierre (fetch() devuelve false si no encuentra)
		if($cierreCaja === false || !is_array($cierreCaja)) {
			// Devolver estructura con error para que el frontend pueda manejarlo
			return array(
				'ingresos' => array(), 
				'egresos' => array(), 
				'otros' => null,
				'error' => 'Cierre no encontrado'
			);
		}
		
		// Asegurar que $cierreCaja tenga al menos los campos básicos con valores por defecto
		if(!isset($cierreCaja["id"])) {
			$cierreCaja["id"] = $idCierre;
		}
		if(!isset($cierreCaja["punto_venta_cobro"])) {
			$cierreCaja["punto_venta_cobro"] = 1;
		}
		if(!isset($cierreCaja["ultimo_id_caja"])) {
			$cierreCaja["ultimo_id_caja"] = 1;
		}
		if(!isset($cierreCaja["fecha_hora"])) {
			$cierreCaja["fecha_hora"] = "";
		}
		if(!isset($cierreCaja["detalle"])) {
			$cierreCaja["detalle"] = "";
		}
		if(!isset($cierreCaja["total_ingresos"])) {
			$cierreCaja["total_ingresos"] = "0";
		}
		if(!isset($cierreCaja["total_egresos"])) {
			$cierreCaja["total_egresos"] = "0";
		}
		if(!isset($cierreCaja["apertura_siguiente_monto"])) {
			$cierreCaja["apertura_siguiente_monto"] = "0";
		}
		
		// Obtener nombre de usuario
		if(isset($cierreCaja["id_usuario_cierre"]) && $cierreCaja["id_usuario_cierre"]) {
			$usuario = ModeloUsuarios::mdlMostrarUsuariosPorId($cierreCaja["id_usuario_cierre"]);
			$cierreCaja["id_usuario_cierre"] = (is_array($usuario) && isset($usuario["nombre"])) ? $usuario["nombre"] : (isset($cierreCaja["id_usuario_cierre"]) ? $cierreCaja["id_usuario_cierre"] : "");
		} else {
			$cierreCaja["id_usuario_cierre"] = "";
		}
		
		// Obtener cierre anterior
		$puntoVenta = isset($cierreCaja["punto_venta_cobro"]) ? $cierreCaja["punto_venta_cobro"] : 1;
		$cierreCajaAnterior = ModeloCajaCierres::mdlAnteriorSeleccionadoCierreCaja($puntoVenta, $idCierre); //datos del cierre anterior
		
		// Validar cierre anterior, si no existe usar valores por defecto
		if($cierreCajaAnterior === false || !is_array($cierreCajaAnterior) || empty($cierreCajaAnterior)) {
			$cierreCajaAnterior = array("ultimo_id_caja" => 1);
		}
		
		$cierreCajaAnterior["ultimo_id_caja"] = (isset($cierreCajaAnterior["ultimo_id_caja"])) ? $cierreCajaAnterior["ultimo_id_caja"] : 1;
		
		$puntoVenta = isset($cierreCaja["punto_venta_cobro"]) ? $cierreCaja["punto_venta_cobro"] : 1;
		$ultimoIdCaja = isset($cierreCaja["ultimo_id_caja"]) ? $cierreCaja["ultimo_id_caja"] : 1;
		
		$cajas = ModeloCajas::mdlMovimientosCajaSegunCierre($puntoVenta, $cierreCajaAnterior["ultimo_id_caja"], $ultimoIdCaja); //movimientos de caja entre el cierre anterior y el elegido
		
		// Asegurar que $cajas sea un array
		if(!is_array($cajas)) {
			$cajas = array();
		}
		
		$categorias = ModeloCategorias::mdlMostrarCategorias('categorias', null, null); //traigo todas las categorias
		
		// Asegurar que $categorias sea un array
		if(!is_array($categorias)) {
			$categorias = array();
		}
		
		$datos = array('ingresos' => array(), 'egresos' => array(), 'otros' => $cierreCaja); //defino array de datos
		$indexIngresos = 0;
		$indexEgresos = 0;
		
		foreach ($categorias as $key => $valueCat) { //cargo el array de datos con las categorias existentes
			if(isset($valueCat["id"]) && isset($valueCat["categoria"])) {
				$datos["ingresos"] += [$indexIngresos => array('id' => $valueCat["id"],'descripcion' => $valueCat["categoria"], 'monto' => 0, 'tipo' => 'categoria')];
				$indexIngresos++;
			}
		}

		foreach ($cajas as $key => $value) {
			if(!is_array($value) || !isset($value["tipo"])) {
				continue; // Saltar elementos inválidos
			}
			if($value["tipo"] == 0) { //pago o gasto
				if(isset($value["id_cliente_proveedor"]) && $value["id_cliente_proveedor"]) { //es un pago de cta cte proveedor
					$nombreProveedor = ModeloProveedores::mdlMostrarProveedoresPorId($value["id_cliente_proveedor"]);
					if($nombreProveedor && isset($nombreProveedor["nombre"])) {
						$datos["egresos"][$indexEgresos] = array('id' => isset($value["id"]) ? $value["id"] : 0, 'tipo' => 'proveedor', 'descripcion' => $nombreProveedor["nombre"], 'monto' => isset($value["monto"]) ? floatval($value["monto"]) : 0);
						$indexEgresos++;
					}
				} else {
					$descripcion = isset($value["descripcion"]) ? $value["descripcion"] : "";
					$monto = isset($value["monto"]) ? floatval($value["monto"]) : 0;
					if($descripcion || $monto > 0) {
						$datos["egresos"][$indexEgresos] = array('id' => isset($value["id"]) ? $value["id"] : 0, 'tipo' => 'comun', 'descripcion' => $descripcion, 'monto' => $monto);
						$indexEgresos++;
					}
				}

			} else { //ingreso
				if(isset($value["id_venta"]) && $value["id_venta"]){ //es un ingreso por venta 
					$venta = ModeloVentas::mdlMostrarVentaConCliente($value["id_venta"]); //traigo venta
					
					if($venta && isset($venta["productos"])) {
						$separoProd = json_decode($venta["productos"], true); //separo productos
						
						if(is_array($separoProd)) {
							foreach ($separoProd as $keyPro => $valuePro) {
								if(isset($valuePro["id"])) {
									$cate_prod = ModeloProductos::mdlMostrarCategoriaProducto($valuePro["id"]); //consulto categoria producto
									if($cate_prod && isset($cate_prod["id"])) {
										$itemArray = array_search($cate_prod["id"], array_column($datos["ingresos"], 'id'));
										if($itemArray !== false && isset($datos["ingresos"][$itemArray]) && isset($valuePro["total"])) {
											$datos["ingresos"][$itemArray]["monto"] += floatval($valuePro["total"]);
										}
									}
								}
							}
						}
					}

				} elseif(isset($value["id_cliente_proveedor"]) && $value["id_cliente_proveedor"]) { //ingreso por cta cte cliente
					$nombreCliente = ModeloClientes::mdlMostrarClientesPorId($value["id_cliente_proveedor"]);
					if($nombreCliente && isset($nombreCliente["nombre"])) {
						$datos["ingresos"][$indexIngresos] = array('id' => isset($value["id"]) ? $value["id"] : 0, 'tipo' => 'cliente', 'descripcion' => $nombreCliente["nombre"], 'monto' => isset($value["monto"]) ? floatval($value["monto"]) : 0);
						$indexIngresos++;
					}

				} else { //ingreso de otro tipo
					$descripcion = isset($value["descripcion"]) ? $value["descripcion"] : "";
					$monto = isset($value["monto"]) ? floatval($value["monto"]) : 0;
					if($descripcion || $monto > 0) {
						$datos["ingresos"][$indexIngresos] = array('id' => isset($value["id"]) ? $value["id"] : 0, 'tipo' => 'comun', 'descripcion' => $descripcion, 'monto' => $monto);
						$indexIngresos++;
					}

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
	    
	    // Validar que se encontró el cierre
	    if(!$cierreCajaHasta || empty($cierreCajaHasta)) {
	        return array();
	    }
	    
	    $idCierreAnt = $idCierre - 1;
	    $cierreCajaDesde = ModeloCajaCierres::mdlMostrarCierresCaja($idCierreAnt); //datos del cierre anterior (obtengo id desde)
	    
	    // Si no hay cierre anterior, usar desde el inicio (id_caja = 1)
	    if(!$cierreCajaDesde || empty($cierreCajaDesde)) {
	        $idCajaDesde = 1;
	    } else {
	        $idCajaDesde = (isset($cierreCajaDesde["ultimo_id_caja"])) ? $cierreCajaDesde["ultimo_id_caja"] + 1 : 1;
	    }
	    
	    $idCajaHasta = (isset($cierreCajaHasta["ultimo_id_caja"])) ? $cierreCajaHasta["ultimo_id_caja"] : 1;
	    $puntoVenta = (isset($cierreCajaHasta["punto_venta_cobro"])) ? $cierreCajaHasta["punto_venta_cobro"] : 1;
	    
	    $listado = ModeloCajas::mdlRangoIdsCajas('cajas', $idCajaDesde, $idCajaHasta, $puntoVenta);
	    
	    // Asegurar que siempre devolvemos un array
	    return is_array($listado) ? $listado : array();
	    
	}
 }