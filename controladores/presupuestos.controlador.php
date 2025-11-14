<?php

class ControladorPresupuestos{

	/*=============================================
	RANGO FECHAS
	=============================================*/	
	static public function ctrRangoFechasPresupuestos($fechaInicial, $fechaFinal){

		$tabla = "presupuestos";

		$respuesta = ModeloPresupuestos::mdlRangoFechasPresupuestos($tabla, $fechaInicial, $fechaFinal);

		return $respuesta;
		
	}	

	/*=============================================
	MOSTRAR PRESUPUESTOS
	=============================================*/
	static public function ctrMostrarPresupuestos($item, $valor){

		$tabla = "presupuestos";

		$respuesta = ModeloPresupuestos::mdlMostrarPresupuestos($tabla, $item, $valor);

		return $respuesta;

	}

	/*=============================================
	CREAR PRESUPUESTO CAJA
	=============================================*/
	static public function ctrCrearPresupuestoCaja($postPresupuestoCaja){

		if(isset($postPresupuestoCaja["nuevoTotalVentaCaja"])) {

			/*=============================================
			ACTUALIZAR LAS COMPRAS DEL CLIENTE Y REDUCIR EL STOCK Y AUMENTAR LAS VENTAS DE LOS PRODUCTOS
			=============================================*/
			if($postPresupuestoCaja["listaProductosCaja"] == "" && $postPresupuestoCaja["listaDescuentoCaja"] == "") {

				return "La venta no se ha ejecuta si no hay productos";

			}

			date_default_timezone_set('America/Argentina/Mendoza');
			$fecha = date('Y-m-d');
			$hora = date('H:i:s');
			$fec_hor = $fecha.' '.$hora;

			//Si hay descuento a la venta tengo que aplicarlo a cada iva y cada base imponible
			$descGeneral = (isset($postPresupuestoCaja["nuevoDescuentoPorcentajeCaja"])) ? floatval($postPresupuestoCaja["nuevoDescuentoPorcentajeCaja"]) : 0;

			//Si hay interes a la venta tengo que aplicarlo a cada iva y cada base imponible
			//$intGeneral = (isset($postPresupuestoCaja["nuevoInteresPorcentajeCaja"])) ? floatval($postPresupuestoCaja["nuevoInteresPorcentajeCaja"]) : 0;
			
			//Acumulador de los diferentes IVA
			$impuesto = 0;
			
			//Acumulador de neto que va a grabarse en afip
			$netoGravado = 0;
		
			//Valores Base imponibles
			$bimp0 = floatval($postPresupuestoCaja["nuevoVtaCajaBaseImp0"]);
			$bimp2 = floatval($postPresupuestoCaja["nuevoVtaCajaBaseImp2"]);
			$bimp5 = floatval($postPresupuestoCaja["nuevoVtaCajaBaseImp5"]);
			$bimp10 = floatval($postPresupuestoCaja["nuevoVtaCajaBaseImp10"]);
			$bimp21 = floatval($postPresupuestoCaja["nuevoVtaCajaBaseImp21"]);
			$bimp27 = floatval($postPresupuestoCaja["nuevoVtaCajaBaseImp27"]);

			//Valores IVA
			$iva2 = floatval($postPresupuestoCaja["nuevoVtaCajaIva2"]);
			$iva5 = floatval($postPresupuestoCaja["nuevoVtaCajaIva5"]);
			$iva10 = floatval($postPresupuestoCaja["nuevoVtaCajaIva10"]);
			$iva21 = floatval($postPresupuestoCaja["nuevoVtaCajaIva21"]);
			$iva27 = floatval($postPresupuestoCaja["nuevoVtaCajaIva27"]);

			//Armo array impuesto_detalle y acumulado de impuesto
			$impuestoDetalle = '[';

			if($bimp0 > 0){ //Hay productos con IVA 0% 
				$bimp0 = $bimp0 - ($bimp0 * $descGeneral / 100);
				$bimp0 = round($bimp0,2);
				$netoGravado = $netoGravado + $bimp0;
				$impuestoDetalle = $impuestoDetalle . '{"id":3,"descripcion":"IVA 0%","baseImponible":"'.$bimp0.'","iva":"0"},';
			}

			if($bimp2 > 0){ //Hay productos con IVA 2,5% 
				$bimp2 = $bimp2 - ($bimp2 * $descGeneral / 100);
				$bimp2 = round($bimp2,2);
				$iva2 = $iva2 - ($iva2 * $descGeneral / 100);
				$iva2 = round($iva2,2);
				$netoGravado = $netoGravado + $bimp2;
				$impuestoDetalle = $impuestoDetalle . '{"id":9,"descripcion":"IVA 2,5%","baseImponible":"'.$bimp2.'","iva":"'.$iva2.'"},';
				$impuesto = $impuesto + $iva2;
			}

			if($bimp5 > 0){ //Hay productos con IVA 5% 
				$bimp5 = $bimp5 - ($bimp5 * $descGeneral / 100);
				$bimp5 = round($bimp5,2);
				$iva5 = $iva5 - ($iva5 * $descGeneral / 100);
				$iva5 = round($iva5,2);
				$netoGravado = $netoGravado + $bimp5;
				$impuestoDetalle = $impuestoDetalle . '{"id":8,"descripcion":"IVA 5%","baseImponible":"'.$bimp5.'","iva":"'.$iva5.'"},';
				$impuesto = $impuesto + $iva5;
			}

			if($bimp10 > 0){ //Hay productos con IVA 10,5% 
				$bimp10 = $bimp10 - ($bimp10 * $descGeneral / 100);
				$bimp10 = round($bimp10,2);
				$iva10 = $iva10 - ($iva10 * $descGeneral / 100);
				$iva10 = round($iva10,2);
				$netoGravado = $netoGravado + $bimp10;
				$impuestoDetalle = $impuestoDetalle . '{"id":4,"descripcion":"IVA 10,5%","baseImponible":"'.$bimp10.'","iva":"'.$iva10.'"},';
				$impuesto = $impuesto + $iva10;
			}

			if($bimp21 > 0){ //Hay productos con IVA 21% 
				$bimp21 = $bimp21 - ($bimp21 * $descGeneral / 100);
				$bimp21 = round($bimp21,2);
				$iva21 = $iva21 - ($iva21 * $descGeneral / 100);
				$iva21 = round($iva21,2);
				$netoGravado = $netoGravado + $bimp21;
				$impuestoDetalle = $impuestoDetalle . '{"id":5,"descripcion":"IVA 21%","baseImponible":"'.$bimp21.'","iva":"'.$iva21.'"},';
				$impuesto = $impuesto + $iva21;
			}

			if($bimp27 > 0){ //Hay productos con IVA 27% 
				$bimp27 = $bimp27 - ($bimp27 * $descGeneral / 100);
				$bimp27 = round($bimp27,2);
				$iva27 = $iva27 - ($iva27 * $descGeneral / 100);
				$iva27 = round($iva27,2);
				$netoGravado = $netoGravado + $bimp27;
				$impuestoDetalle = $impuestoDetalle . '{"id":6,"descripcion":"IVA 27%","baseImponible":"'.$bimp27.'","iva":"'.$iva27.'"},';
				$impuesto = $impuesto + $iva27;
			}

			$netoGravado = round($netoGravado,2);
			$impuesto = round($impuesto,2);
			$impuestoDetalle = substr($impuestoDetalle, 0, -1);
			$impuestoDetalle = $impuestoDetalle . ']';

			$tipoCbte = (int)$postPresupuestoCaja["nuevotipoCbte"];

			$pedidoAfip = null;
			$respuestaAfip = null;

			/*=============================================
			INGRESAR REGISTROS EN LA TABLA PRESUPUESTOS
			=============================================*/
			$listaProductos = json_decode($postPresupuestoCaja["listaProductosCaja"], true);

			$estado = 1;

			if($postPresupuestoCaja["listaMetodoPagoCaja"] == "Mixto") {

				$lstMetodoPago = json_decode($postPresupuestoCaja["mxMediosPagos"]);

			} else {

				//Inserto la entrega inicial / pago
				// $importePagado = ($postPresupuestoCaja["listaMetodoPagoCaja"] == "CC") ? $postPresupuestoCaja["ccEntregaInicial"] : $postPresupuestoCaja["nuevoTotalVentaCaja"];
				$importePagado = $postPresupuestoCaja["nuevoTotalVentaCaja"];

				$estado = ($postPresupuestoCaja["listaMetodoPagoCaja"] == "CC") ? 2 : $estado;

				//Inserto cantidad de cuotas (sino hay pongo 0). Esta es para cuenta corriente
				$cantidadCuotas = 0;//($postPresupuestoCaja["ccCantidadCuotas"] != "") ? $postPresupuestoCaja["ccCantidadCuotas"] : 0;

				$lstMetodoPago = array (

									array(

										"tipo" => $postPresupuestoCaja["listaMetodoPagoCaja"],

										"interes" => $postPresupuestoCaja["nuevoInteresPorcentajeCaja"],

										"descuento" => $postPresupuestoCaja["nuevoDescuentoPorcentajeCaja"], 

										"entrega" => $importePagado, 

										"cuotas" => $cantidadCuotas
									)
								);
			}

			/*=============================================
			GUARDAR PRESUPUESTO
			=============================================*/	
			$tabla = "presupuestos";
			$datos = array(
							"id_vendedor"=>$postPresupuestoCaja["idVendedor"],
						   	"id_cliente"=>$postPresupuestoCaja["seleccionarCliente"],
						   	"productos"=>$postPresupuestoCaja["listaProductosCaja"],
						   	"neto"=>$postPresupuestoCaja["nuevoPrecioNetoCaja"],
						   	"neto_gravado"=>$netoGravado,
							"base_imponible_0"=>$bimp0,
							"base_imponible_2"=>$bimp2,
							"base_imponible_5"=>$bimp5,
							"base_imponible_10"=>$bimp10,
							"base_imponible_21"=>$bimp21,
							"base_imponible_27"=>$bimp27,
							"iva_2"=>$iva2,
							"iva_5"=>$iva5,
							"iva_10"=>$iva10,
							"iva_21"=>$iva21,
							"iva_27"=>$iva27,
							"impuesto"=>$impuesto,
							"impuesto_detalle"=>$impuestoDetalle,
						   	"total"=>$postPresupuestoCaja["nuevoTotalVentaCaja"],
						   	"metodo_pago"=> json_encode($lstMetodoPago),
						   	"estado" => $estado,
						   	"fecha" => $postPresupuestoCaja["fechaActual"], 
							);

			$respuesta = ModeloPresupuestos::mdlIngresarPresupuesto($tabla, $datos);

			/*=============================================
			EVALUACION DE RESPUESTAS DE VENTA, CAJA Y FACTURACION
			=============================================*/
		   	if ($respuesta == "ok") {

		   		$devuelvo = array('estado' => 'ok',
		   						  'datos' => ''  );

		   	} else {

				$devuelvo = array('estado' => 'error',
							   'modeloPresupuesto' => json_encode($respuesta));

		   	}

		 	return $devuelvo;

		}

	}

	/*=============================================
	ELIMINAR PRESUPUESTO
	=============================================*/
	static public function ctrEliminarPresupuesto(){

		if(isset($_GET["idPresupuesto"])){

			$respuesta = ModeloPresupuestos::mdlEliminarPresupuesto('presupuestos', $_GET["idPresupuesto"]);

			if($respuesta == "ok"){

				echo'<script>

				swal({
					  type: "success",
					  title: "Presupuestos",
					  text: "El presupuesto ha sido borrado correctamente",					  
					  showConfirmButton: true,
					  confirmButtonText: "Cerrar"
					  }).then(function(result){
								if (result.value) {

								window.location = "presupuestos";

								}
							})

				</script>';

			}		
		}

	}	

/*=============================================
	--------------------------------------------------------------------------------------------------------------------------
	=============================================*/	

	/*=============================================
	SUMA TOTAL PRESUPUESTOS
	=============================================*/

	static public function ctrSumaTotalPresupuestos(){

		$tabla = "ventas";

		$respuesta = ModeloPresupuestos::mdlSumaTotalPresupuestos($tabla);

		return $respuesta;

	}

	/*=============================================
	CONSULTAR POR PRESUPUESTO FACTURADA
	=============================================*/

	static public function ctrPresupuestoFacturada($id){

		$respuesta = ModeloPresupuestos::mdlPresupuestoFacturada($id);

		return $respuesta;

	}

	/*=============================================
	CONSULTAR POR PRESUPUESTO FACTURADA
	=============================================*/

	static public function ctrPresupuestoFacturadaDatos($id){

		$respuesta = ModeloPresupuestos::mdlPresupuestoFacturadaDatos($id);

		return $respuesta;

	}

	/*=============================================
	CONSULTAR POR PRESUPUESTO FACTURADA (RESP AFIP)
	=============================================*/
	static public function ctrPresupuestoFacturadaDatosAfip($id){

	//ARMAR

		// $respuesta = ModeloPresupuestos::mdlPresupuestoFacturadaDatos($id);

		// return $respuesta;

	}
	/*=============================================
	MOSTRAR ULTIMO ID
	=============================================*/

	static public function ctrUltimoId(){

		$tabla = "ventas";

		$respuesta = ModeloPresupuestos::mdlUltimoId($tabla);

		return $respuesta;

	}

	/*=============================================
	AUTORIZAR COMPROBANTE (desde ventas.php)
	=============================================*/
	static public function ctrAutorizarCbte(){

		if(isset($_POST["autorizarCbteIdPresupuesto"])) {

			var_dump($_POST);

			if(ModeloPresupuestos::mdlPresupuestoFacturada($_POST["autorizarCbteIdPresupuesto"])){
				echo'<script>

				swal({
					  type: "error",
					  title: "Presupuestos",
					  text: "El comprobante ya se encuentra autorizado",
					  showConfirmButton: true,
					  confirmButtonText: "Cerrar"
					  }).then(function(result){
						if (result.value) {

							window.location = "ventas";

						}
					})

				</script>';

				return;
			}

			$idPresupuesto = $_POST["autorizarCbteIdPresupuesto"];
			$tipoCbte = $_POST["autorizarCbteTipoCbte"] + 0;
			$idCliente = (isset($_POST["autorizarCbteCliente"])) ? $_POST["autorizarCbteCliente"] : 1;

			//Primero actualizo el tipo comprobante, si no se llega a aprobar va a salir en ventas.php con un signo de exclamación
			$actualizarVta = ModeloPresupuestos::mdlActualizarPresupuesto('ventas', 'cbte_tipo', $tipoCbte, $idPresupuesto);
			//Actualizo el cliente
			$actualizarVtaCli = ModeloPresupuestos::mdlActualizarPresupuesto('ventas', 'id_cliente', $idCliente, $idPresupuesto);

			$respuesta = self::ctrFacturarPresupuesto($idPresupuesto, $tipoCbte);

			if($respuesta){

				echo'<script>

				swal({
					  type: "success",
					  title: "Presupuestos",
					  text: "El comprobante ha sido autorizado correctamente",
					  showConfirmButton: true,
					  confirmButtonText: "Cerrar"
					  }).then(function(result){
						if (result.value) {

							window.location = "ventas";

						}
					})

				</script>';
			} else {

				var_dump($respuesta);

			}

		}

	}

	/*===========================================
	AUTORIZAR COMPROBANTE (usado desde venta.php)
	=============================================*/
	static public function ctrFacturarPresupuesto($idPresupuesto, $tipo_comprobante){

		$msjAfip = array();

		$arrEmpresa = ModeloEmpresa::mdlMostrarEmpresa("empresa", "id", 1);

		$venta = ModeloPresupuestos::mdlMostrarPresupuestos("ventas", "id", $idPresupuesto);

		date_default_timezone_set('America/Argentina/Mendoza');
		$fecha = date('Y-m-d');
		$hora = date('H:i:s');
		$fec_hor = $fecha.' '.$hora;

		/*=============================================
				FACTURACION ELECTRONICA
		=============================================*/
		$datosFactura = array('factura' => 'no');

		$pedidoAfip = null;

		if($tipo_comprobante <> 0) {

			$msjAfip = array();

			$datosFactura = array(
				"fec_factura" => $fec_hor,
				"pto_vta" => ($venta["pto_vta"] == '') ? 0 : (int)$venta["pto_vta"],
				"cbte_tipo"=> (int)$tipo_comprobante,
				"concepto" => (int)$venta["concepto"],
				"fec_desde" =>  $venta["fec_desde"],
				"fec_hasta" => $venta["fec_hasta"],
				"fec_vencimiento" => $venta["fec_vencimiento"],
				"total"=>$venta["total"],
			   );

			$datosFactura += ["neto_gravado" => $venta["neto_gravado"]];
			$datosFactura += ["impuesto" => $venta["impuesto"]];
			$datosFactura += ["impuesto_detalle" => $venta["impuesto_detalle"]];

			if(!is_null($datosFactura['concepto']) || !is_null($datosFactura["pto_vta"]) || $datosFactura["pto_vta"] <> 0 || !is_null($datosFactura['cbte_tipo'])) {

				$wsfe = new WSFE($arrEmpresa);
				$wsfe->openTA();

				//Consulto el ultimo numero de comprobante para el punto de venta y tipo de comprobante
				$ultComp = $wsfe->UltimoAutorizado($datosFactura['pto_vta'], $datosFactura["cbte_tipo"]);

				$cliente = ModeloClientes::mdlMostrarClientes("clientes", "id", $venta['id_cliente']);

				// file_put_contents('cliente', json_encode($cliente));

				//Armo array para impactar en AFIP
				$datosFacturacion = array(
		          'FeCAEReq' => array
		          (
		            'FeCabReq' => array
		            (
		              'CantReg' => 1,
		              'PtoVta' => (int)$datosFactura["pto_vta"],
					'CbteTipo' => (int)$datosFactura["cbte_tipo"]
		            ),
		            'FeDetReq' => array
		            (
		              'FECAEDetRequest' => array
		              (
		                'Concepto' => (int)$datosFactura['concepto'], 
						'DocTipo' => (int)$cliente['tipo_documento'], 
						'DocNro' => (float)$cliente['documento'], //pongo float porque con int se rompe con los cuit
						'CbteDesde' => $ultComp + 1,
						'CbteHasta' => $ultComp + 1, 
						'CbteFch' => date('Ymd', strtotime($fec_hor)),
						'ImpTotal' => (double)$datosFactura["total"],
						'ImpTotConc' => 0,
						'ImpNeto' => (double)$datosFactura["neto_gravado"],
						'ImpOpEx' => 0,
						'ImpTrib' => 0,
						'ImpIVA' => (double)$datosFactura["impuesto"],
						'MonId' => 'PES',
		                'MonCotiz' => 1
						)
		          	 )
		           )
		      	);

    			//Si el concepto tiene servicio hay que agregar al array fechas
    			if((int)$datosFactura['concepto'] <> 1){
						
					$datosFacturacion["FeCAEReq"]["FeDetReq"]["FECAEDetRequest"] += ["FchServDesde" => date('Ymd', strtotime($datosFactura["fec_desde"]))];
					$datosFacturacion["FeCAEReq"]["FeDetReq"]["FECAEDetRequest"] += ["FchServHasta" => date('Ymd', strtotime($datosFactura["fec_hasta"]))];
					$datosFacturacion["FeCAEReq"]["FeDetReq"]["FECAEDetRequest"] += ["FchVtoPago" => date('Ymd', strtotime($datosFactura["fec_vencimiento"]))];
				}

				//tipos de comprobantes que discriminan IVA (A y B)
				$discriminarIVA = array(
					1, 
					2, 
					3,
					4,
					6,
					7,
					8,
					9
				);

				//Agrego al array los detalles de iva (si son cbtes tipo A o B)
				if(in_array($datosFactura["cbte_tipo"], $discriminarIVA)) {
					$arrDetImpuestos = json_decode($venta["impuesto_detalle"], true);
					$datosFacturacion["FeCAEReq"]["FeDetReq"]["FECAEDetRequest"] += ["Iva" => array('AlicIva' => array())];
					$indice = 0;
					foreach ($arrDetImpuestos as $key => $value) {

							$datosFacturacion["FeCAEReq"]["FeDetReq"]["FECAEDetRequest"]["Iva"]["AlicIva"] += array($indice => array(
								'Id' => (int)$value["id"],
								'BaseImp' => $value["baseImponible"],
								'Importe' => $value["iva"]));

							$indice++;
						}
					}

				$pedidoAfip = json_encode($datosFacturacion);

				//Guardo el Array como se lo pido a la afip
				ModeloPresupuestos::mdlPedidoAfipPresupuesto("ventas", $pedidoAfip, $idPresupuesto);

				//Aca sucede la magia.... o no
				$respAfip = $wsfe->CAESolicitar($datosFactura['pto_vta'], $datosFactura['cbte_tipo'], $datosFacturacion);

				//guardo la respuesta completa como la devuelve afip
				ModeloPresupuestos::mdlRespuestaAfipPresupuesto("ventas", json_encode($respAfip), $idPresupuesto);

				//Procesamiento de respuesta AFIP
				if (property_exists ($respAfip->FECAESolicitarResult, 'FeCabResp')) {

					if ($respAfip->FECAESolicitarResult->FeCabResp->Resultado == "A") { //Factura Aprobada

						//Guardo en venta_factura los datos del comprobante aprobado
						$datosFactura = array(
							"id_venta"=> $idPresupuesto,
							"fec_factura" => $respAfip->FECAESolicitarResult->FeDetResp->FECAEDetResponse->CbteFch,
							"nro_cbte" => $respAfip->FECAESolicitarResult->FeDetResp->FECAEDetResponse->CbteDesde,
							"cae" => $respAfip->FECAESolicitarResult->FeDetResp->FECAEDetResponse->CAE,
							"fec_vto_cae" => $respAfip->FECAESolicitarResult->FeDetResp->FECAEDetResponse->CAEFchVto
						   );

						$respuestaFact = ModeloPresupuestos::mdlFacturarPresupuesto("ventas_factura", $datosFactura);

						echo '<script>

							swal({
								  type: "success",
								  title: "Presupuestos",
								  text: "Comprobante autorizado",
								  showConfirmButton: true,
								  confirmButtonText: "Cerrar"
								  }).then(function(result){
									if (result.value) {

										window.location = "ventas";

									}
								})

						</script>';

					} else { //Factura rechazada con observaciones

						//Existen Observaciones en la facturacion
						if (property_exists ($respAfip->FECAESolicitarResult, 'FeDetResp')){

						    if (property_exists($respAfip->FECAESolicitarResult->FeDetResp->FECAEDetResponse, 'Observaciones')){

						      $i=0;
						      $msjAfip['observaciones'] = "";
						      $arrObserva =$respAfip->FECAESolicitarResult->FeDetResp->FECAEDetResponse->Observaciones->Obs;
						      
						      if (is_array($arrObserva)){ //Si es un array lo recorro con un foreach (cuando hay mas de una observacion)

						        foreach ($arrObserva as $obser) {

						          $msjAfip['observaciones'] = $msjAfip['observaciones'] . $i .') '. $obser->Code . ": " . $obser->Msg . ' ';
						          $i++;

						        }

						      } else { //Si solo es una observacion, viene como un objeto stdClass, con get_obt... lo paso a array
						         $obser = get_object_vars($respAfip->FECAESolicitarResult->FeDetResp->FECAEDetResponse->Observaciones->Obs);
						         $msjAfip['observaciones'] = $msjAfip['observaciones'] . '0) '. $obser["Code"] . ": " . $obser["Msg"] . ' ';
						      }

						    }
						  
						  }

						  ModeloPresupuestos::mdlObservacionesPresupuesto('ventas', json_encode($msjAfip), $idPresupuesto);
					
						echo'<script>
						swal({
							  type: "error",
							  title: "Presupuestos",
							  html: "<p>Error al intentar autorizar. <a href=\"index.php?ruta=editar-venta&idPresupuesto='.$idPresupuesto.'\"> Ver observaciones en venta</a></p>",
							  showConfirmButton: true,
							  confirmButtonText: "Cerrar"
							  }).then(function(result){
								if (result.value) {

									window.location = "ventas";

								}
							})

							</script>';

					}

				} else { //factura rechaza con errores o eventos

					$i = 0;
					//Existen eventos en la facturacion
					if (property_exists($respAfip->FECAESolicitarResult, 'Events')) {

						$msjAfip['eventos'] = "";
						if (is_array($respAfip->FECAESolicitarResult->Events->Evt)) {

							for ($i = 0; $i <= count($respAfip->FECAESolicitarResult->Events->Evt)-1; $i++) {
								$msjAfip['eventos'] = $msjAfip['eventos'] . $i . ')' . $respAfip->FECAESolicitarResult->Events->Evt[$i]->Code . ": " . $respAfip->FECAESolicitarResult->Events->Evt[$i]->Msg;
							}

						} else {

							$msjAfip['eventos'] = $msjAfip['eventos'] . $i . ')' . $respAfip->FECAESolicitarResult->Events->Evt->Code . ": " . $respAfip->FECAESolicitarResult->Events->Evt->Msg;

						}

					}

					//Existen errores en la facturacion
					if (property_exists($respAfip->FECAESolicitarResult, 'Errors')) {

						$msjAfip['errores'] = "";
						$i=0;
						if (is_array($respAfip->FECAESolicitarResult->Errors->Err)) {

							for ($i = 0; $i <= count($respAfip->FECAESolicitarResult->Errors->Err)-1; $i++) {
								$msjAfip['errores'] = $msjAfip['errores'] . $i . ') ' . $respAfip->FECAESolicitarResult->Errors->Err[$i]->Code . ": " . $respAfip->FECAESolicitarResult->Errors->Err[$i]->Msg;
							}

						} else {

							$msjAfip['errores'] = $msjAfip['errores'] . $i . ') ' . $respAfip->FECAESolicitarResult->Errors->Err->Code . ": " . $respAfip->FECAESolicitarResult->Errors->Err->Msg;

						}

					}
					
					ModeloPresupuestos::mdlObservacionesPresupuesto('ventas', json_encode($msjAfip), $idPresupuesto);
					//OCURRIO ERROR AL INTENTAR FACTURAR ($respAFIP viene sin FeCabResp)
					echo'<script>
						swal({
								  type: "error",
								  title: "Presupuestos",
								  html: "<p>Error al intentar autorizar. <a href=\"index.php?ruta=editar-venta&idPresupuesto='.$idPresupuesto.'\"> Ver errores/eventos en venta</a></p>",
								  showConfirmButton: true,
								  confirmButtonText: "Cerrar"
								  }).then(function(result){
									if (result.value) {

										window.location = "ventas";

									}
								})

							</script>';

				}

			} else {

					echo'<script>
					swal({
						  type: "error",
						  title: "Presupuestos",
						  text: "Concepto, punto de venta y tipo de comprobante deben ser seleccionados para solicitar autorización",
						  showConfirmButton: true,
						  confirmButtonText: "Cerrar"
						  }).then(function(result){
							if (result.value) {

								window.location = "ventas";

							}
						})

					</script>';

			}

		} else {

			echo'<script>

			swal({
				  type: "error",
				  title: "Presupuestos",
				  text: "No se puede autorizar comprobante X",
				  showConfirmButton: true,
				  confirmButtonText: "Cerrar"
				  }).then(function(result){
					if (result.value) {

						window.location = "ventas";

					}
				})

			</script>';
		
		}

	}	

	/*=============================================
	TOTAL PRESUPUESTOS POR RANGO FECHA
	=============================================*/	
	static public function ctrRangoFechasTotalPresupuestos($fechaInicial, $fechaFinal){

		$respuesta = ModeloPresupuestos::mdlRangoFechasTotalPresupuestos($fechaInicial, $fechaFinal);

		return $respuesta;
		
	}	

	/*=============================================
	MOSTRAR PRESUPUESTO CON CLIENTE
	=============================================*/	
	static public function ctrMostrarPresupuestoConCliente($idPresupuesto){

		$respuesta = ModeloPresupuestos::mdlMostrarPresupuestoConCliente($idPresupuesto);

		return $respuesta;
		
	}	

	/*=============================================
	LIBRO IVA PRESUPUESTOS
	=============================================*/	
	static public function ctrLibroIvaPresupuestos($fechaInicial, $fechaFinal) {

		$respuesta = ModeloPresupuestos::mdlLibroIvaPresupuestos($fechaInicial, $fechaFinal);

		return $respuesta;
		
	}
}