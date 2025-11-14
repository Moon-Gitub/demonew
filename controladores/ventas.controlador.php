<?php

class ControladorVentas{

	/*=============================================
	MOSTRAR VENTAS
	=============================================*/
	static public function ctrMostrarVentas($item, $valor){
		$tabla = "ventas";
		$respuesta = ModeloVentas::mdlMostrarVentas($tabla, $item, $valor);
		return $respuesta;
	}
    
	/*=============================================
	CREAR VENTA
	=============================================*/
	static public function ctrCrearVenta(){
		
		if(!ModeloVentas::mdlBuscarIdentificadorVenta($_POST["tokenIdTablaVentas"])){
		    if(isset($_POST["listaProductos"]) && $_POST["listaProductos"] != "" ){
    			date_default_timezone_set('America/Argentina/Mendoza');
    			//Codigo de VENTA
                $codigoNuevaVenta = ModeloVentas::mdlMostrarVentas('ventas', null, null);
                if(!$codigoNuevaVenta) {
    				$codigo ="10001";
                } else {
    				foreach ($codigoNuevaVenta as $key => $value) { }
    				$codigo = $value["codigo"] + 1;
    			}
    
    			$listaProductos = json_decode($_POST["listaProductos"], true);
    
    			$totalProductosComprados = array();
    
    			foreach ($listaProductos as $key => $value) {
    
    			   array_push($totalProductosComprados, $value["cantidad"]);
    				
    			   $tablaProductos = "productos";
    
    			    $item = "id";
    			    $valor = $value["id"];
    			    $orden = "id";
    			    $traerProducto = ModeloProductos::mdlMostrarProductos($tablaProductos, $item, $valor, $orden);
    
    				//Suma la cantidad actual vendida + las ventas totales del producto
    				//$item1a = "ventas";
    				//$valor1a = $value["cantidad"] + $traerProducto["ventas"]; 
    			    //$nuevasVentas = ModeloProductos::mdlActualizarProducto($tablaProductos, $item1a, $valor1a, $valor, 'Crear venta (' . $codigo. ')');
    
    				//Establece el nuevo stock disponible del producto - FORMA VIEJA
    				$item1b = "stock";
    				// $valor1b = $value["stock"]; 
    				//Establece el nuevo stock disponible del producto - FORMA NUEVA
    				//traigo stock actual
    				$stkActual = $traerProducto["stock"];
    				$valor1b = $stkActual - $value["cantidad"]; 
    				$nuevoStock = ModeloProductos::mdlActualizarProducto($tablaProductos, $item1b, $valor1b, $valor, 'Crear venta (' . $codigo. ')');
    
    			}
    
    			$tablaClientes = "clientes";
    
    			$item = "id";
    			$valor = $_POST["seleccionarCliente"];
    
    			$traerCliente = ModeloClientes::mdlMostrarClientes($tablaClientes, $item, $valor);
    
    			$item1a = "compras";
    				
    			$valor1a = array_sum($totalProductosComprados) + $traerCliente["compras"];
    
    			$comprasCliente = ModeloClientes::mdlActualizarCliente($tablaClientes, $item1a, $valor1a, $valor);
    
    			$item1b = "ultima_compra";
    
    			$fecha = date('Y-m-d');
    			$hora = date('H:i:s');
    			$valor1b = $fecha.' '.$hora;
    
    			$fechaCliente = ModeloClientes::mdlActualizarCliente($tablaClientes, $item1b, $valor1b, $valor);
              
    			//MEDIO PAGO
    
    			$lstMetodoPago = array (
    
    						array(
    
    							"tipo" => $_POST["listaMetodoPago"],
    							"interes" => $_POST["nuevoInteresPorcentaje"],
    							"descuento" => $_POST["nuevoDescuentoPorcentaje"], 
    							"entrega" => $_POST["totalVenta"], 
    							"cuotas" => 0
    						)
    					);
    			/*=============================================
    			GUARDAR LA VENTA
    			=============================================*/	
    
    			$fecha = date('Y-m-d');
    			$hora = date('H:i:s');
    			$fec_hora = $fecha.' '.$hora;
    
    			$tabla = "ventas";
    
    			$datos = array(
    			    "uuid" => $_POST["tokenIdTablaVentas"],
    			    "id_vendedor"=>$_POST["idVendedor"],
    				"id_cliente"=>$_POST["seleccionarCliente"],
    				"codigo"=>$codigo,
    				"cbte_tipo" => $_POST["nuevotipoCbte"],
    				"productos"=>$_POST["listaProductos"],
    				"impuesto"=>$_POST["nuevoPrecioImpuesto"],
    				"neto"=>$_POST["nuevoPrecioNeto"],
    				"total"=>$_POST["totalVenta"],
    				"metodo_pago" => json_encode($lstMetodoPago),
    				"pto_vta" => ($_POST["nuevaPtoVta"] == '') ? 0 : $_POST["nuevaPtoVta"],
    				"concepto" => $_POST["nuevaConcepto"],
    				"fec_desde" =>  $_POST["nuevaFecDesde"],
    				"fec_hasta" => $_POST["nuevaFecHasta"],
    				"fec_vencimiento" => $_POST["nuevaFecVto"],
    				"estado" => 0, 
    				"observaciones_vta" => $_POST["nuevaObservacionVenta"],
    				"fecha" => $fec_hora
    			);
    
    			$respuesta = ModeloVentas::mdlIngresarVenta($tabla, $datos);
				
							
    			/************************************
    			*	FACTURAR
    			*************************************/
    			
    			$respuestaFac = false;
    
    			if($_POST["nuevotipoCbte"] <> "0" ){
    
    				$respuestaFac = self::ctrFacturarVenta(self::ctrUltimoId()['ultimo']);
    
    			}
    
    			if($respuesta == "ok"){
    
    				echo'<script>
    
    				localStorage.removeItem("rango");
    
    				swal({
    					  type: "success",
    					  title: "Ventas",
    					  text: "La venta ha sido guardada correctamente",
    					  showConfirmButton: true,
    					  confirmButtonText: "Cerrar"
    					  }).then(function(result){
    						if (result.value) {
    
    							window.location = "ventas";
    
    						}
    					})
    
    				</script>';
    
    			} else {
    
    				$respError = (isset($respuesta[2])) ? $respuesta[2] : "Error desconocido";
    
    				echo '<script>
    
    				swal({
    					  type: "error",
    					  title: "Ventas",
    					  text: "'.$respError.'",
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
		}

	}

	/*=============================================
	CREAR VENTA CAJA
	=============================================*/
	static public function ctrCrearVentaCaja($postVentaCaja){

		if(!ModeloVentas::mdlBuscarIdentificadorVenta($postVentaCaja["tokenIdTablaVentas"])){
		    if(isset($postVentaCaja["nuevoTotalVentaCaja"])) {

    			//devuelvo error si el usuario no tiene sucursal asignada
    			if(!isset($postVentaCaja["sucursalVendedor"]) || $postVentaCaja["sucursalVendedor"] == "") {
    				return  array('estado' => 'error',
    							   'modeloVentas' => 'SIN SUCURSAL ASIGNADA',
    							   'modeloCaja' => null,
    							   'modeloVentFac' => null);
    			}
    
    			/*=============================================
    			ACTUALIZAR LAS COMPRAS DEL CLIENTE Y REDUCIR EL STOCK Y AUMENTAR LAS VENTAS DE LOS PRODUCTOS
    			=============================================*/
    			if($postVentaCaja["listaProductosCaja"] == "" && $postVentaCaja["listaDescuentoCaja"] == "") {
    
    				return "La venta no se ha ejecuta si no hay productos";
    
    			}
    
    			date_default_timezone_set('America/Argentina/Mendoza');
    			$fecha = date('Y-m-d');
    			$hora = date('H:i:s');
    			$fec_hor = $fecha.' '.$hora;
    
    			/*=============================================
    					FACTURACION ELECTRONICA
    			=============================================*/
    			$facturar = false;
    			$datosFactura = array('factura' => 'no');
    
    			//Si hay descuento a la venta tengo que aplicarlo a cada iva y cada base imponible
    			$descGeneral = (isset($postVentaCaja["nuevoDescuentoPorcentajeCaja"])) ? floatval($postVentaCaja["nuevoDescuentoPorcentajeCaja"]) : 0;
    
    			//Si hay interes a la venta tengo que aplicarlo a cada iva y cada base imponible
    			//$intGeneral = (isset($postVentaCaja["nuevoInteresPorcentajeCaja"])) ? floatval($postVentaCaja["nuevoInteresPorcentajeCaja"]) : 0;
    			
    			//Acumulador de los diferentes IVA
    			$impuesto = 0;
    			
    			//Acumulador de neto que va a grabarse en afip
    			$netoGravado = 0;
    		
    			//Valores Base imponibles
    			$bimp0 = floatval($postVentaCaja["nuevoVtaCajaBaseImp0"]);
    			$bimp2 = floatval($postVentaCaja["nuevoVtaCajaBaseImp2"]);
    			$bimp5 = floatval($postVentaCaja["nuevoVtaCajaBaseImp5"]);
    			$bimp10 = floatval($postVentaCaja["nuevoVtaCajaBaseImp10"]);
    			$bimp21 = floatval($postVentaCaja["nuevoVtaCajaBaseImp21"]);
    			$bimp27 = floatval($postVentaCaja["nuevoVtaCajaBaseImp27"]);
    
    			//VAlores IVA
    			$iva2 = floatval($postVentaCaja["nuevoVtaCajaIva2"]);
    			$iva5 = floatval($postVentaCaja["nuevoVtaCajaIva5"]);
    			$iva10 = floatval($postVentaCaja["nuevoVtaCajaIva10"]);
    			$iva21 = floatval($postVentaCaja["nuevoVtaCajaIva21"]);
    			$iva27 = floatval($postVentaCaja["nuevoVtaCajaIva27"]);
    
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
    			$impuestoDetalle = $impuestoDetalle = (strlen($impuestoDetalle)>1) ? substr($impuestoDetalle, 0, -1) : $impuestoDetalle;
    			$impuestoDetalle = $impuestoDetalle . ']';
    
    			$tipoCbte = (int)$postVentaCaja["nuevotipoCbte"];
    
    			$pedidoAfip = null;
    			$respuestaAfip = null;
    
                $msjAfip = null;//array();
                
    			if($tipoCbte <> 0 && $tipoCbte <> 999) { // 0: Cbte X | 999: devolucion X | NUM: autorizo a afip 
    
    				$arrEmpresa = ModeloEmpresa::mdlMostrarEmpresa("empresa", "id", 1);
    
    				$datosFactura = array(
    								"fec_factura" => $fec_hor,
    								"pto_vta" => ($postVentaCaja["nuevaPtoVta"] == '') ? 0 : (int)$postVentaCaja["nuevaPtoVta"],
    							    "cbte_tipo"=> (int)$postVentaCaja["nuevotipoCbte"],
    							    "concepto" => (int)$postVentaCaja["nuevaConcepto"],
    							    "fec_desde" =>  $postVentaCaja["nuevaFecDesde"],
    					 		    "fec_hasta" => $postVentaCaja["nuevaFecHasta"],
    							    "fec_vencimiento" => $postVentaCaja["nuevaFecVto"],
    							   	"total"=>$postVentaCaja["nuevoTotalVentaCaja"],
    							   );
    
    				$datosFactura += ["neto_gravado" => $netoGravado];
    				$datosFactura += ["impuesto" => $impuesto];
    				$datosFactura += ["impuesto_detalle" => $impuestoDetalle];
    
    				if(!is_null($datosFactura['concepto']) || !is_null($datosFactura["pto_vta"]) || $datosFactura["pto_vta"] <> 0 || !is_null($datosFactura['cbte_tipo'])) {
    
    					try {
    
    						// $wsaa = new WSAA($arrEmpresa);
    						// if (date('Y-m-d H:i:s', strtotime($wsaa->get_expiration())) < date('Y-m-d H:i:s')) {
    						// 	$wsaa->generar_TA();
    						// }
    
    						$wsfe = new WSFE($arrEmpresa);
    						$wsfe->openTA();
    
    						//Consulto el ultimo numero de comprobante para el punto de venta y tipo de comprobante
    						$ultComp = $wsfe->UltimoAutorizado($datosFactura['pto_vta'], $datosFactura["cbte_tipo"]);
    
    						$cliente = ModeloClientes::mdlMostrarClientes("clientes", "id", $postVentaCaja['seleccionarCliente']);
    
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
    				                'MonCotiz' => 1,
    				                'CondicionIVAReceptorId' => (int)$cliente["condicion_iva"]
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
    
    						//tipos de comprobantes que deben informar comprobante asociado
    						$cbtesAsociados = array(
    							2, 
    							7,
    							12,
    							3, 
    							8, 
    							13,
    							202,
    							207,
    							212,
    							203,
    							208,
    							213
    						);
    						//COMPROBANTES ASOCIADOS
    		    			if(in_array($datosFactura["cbte_tipo"], $cbtesAsociados)) {
    							
    							$datosFacturacion["FeCAEReq"]["FeDetReq"]["FECAEDetRequest"] += ["CbtesAsoc" => array(0 => array(
    								'Tipo' => (int)$postVentaCaja["nuevotipoCbteAsociado"],
    								'PtoVta' => $postVentaCaja["nuevaPtoVtaAsociado"],
    								'Nro' => $postVentaCaja["nuevaNroCbteAsociado"]
    								)
    							)];
    
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
    
    							$arrDetImpuestos = json_decode($impuestoDetalle, true);
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
    
    						//file_put_contents('AAA_ultimo_pedido_afip', $pedidoAfip);
    
    						//Guardo el Array como se lo pido a la afip
    						//ModeloVentas::mdlPedidoAfipVenta("ventas_factura", json_encode($datosFactura), $idVenta);
    
    						//Aca sucede la magia.... o no
    						$respAfip = $wsfe->CAESolicitar($datosFactura['pto_vta'], $datosFactura['cbte_tipo'], $datosFacturacion);
    
    						$respuestaAfip  = json_encode($respAfip);
    
    						//file_put_contents('AAA_ultima_respuesta_afip', json_encode($respAfip));
    						
    						//guardo la respuesta completa como la devuelve afip
    						//ModeloVentas::mdlRespuestaAfipVenta("ventas", json_encode($respAfip), $idVenta);
    
    						//Procesamiento de respuesta AFIP
    						//Existen eventos en la facturacion
    						if (property_exists($respAfip->FECAESolicitarResult, 'Events')) {
    
    							$msjAfip['eventos'] = "";
    							$i = 1;
    							if (is_array($respAfip->FECAESolicitarResult->Events->Evt)) {
    
    								for ($i = 0; $i <= count($respAfip->FECAESolicitarResult->Events->Evt)-1; $i++) {
    									$msjAfip['eventos'] = $msjAfip['eventos'] . $i . ')' . $respAfip->FECAESolicitarResult->Events->Evt[$i]->Code . ": " . $respAfip->FECAESolicitarResult->Events->Evt[$i]->Msg;
    								}
    
    							} 
    							else {
    
    								$msjAfip['eventos'] = $msjAfip['eventos'] . '1)' . $respAfip->FECAESolicitarResult->Events->Evt->Code . ": " . $respAfip->FECAESolicitarResult->Events->Evt->Msg;
    
    							}
    
    						}
    
    						//Existen errores en la facturacion
    						if (property_exists($respAfip->FECAESolicitarResult, 'Errors')) {
    
    							$msjAfip['errores'] = "";
    							$i = 1;
    							if (is_array($respAfip->FECAESolicitarResult->Errors->Err)) {
    
    								for ($i = 0; $i <= count($respAfip->FECAESolicitarResult->Errors->Err)-1; $i++) {
    									$msjAfip['errores'] = $msjAfip['errores'] . $i . ') ' . $respAfip->FECAESolicitarResult->Errors->Err[$i]->Code . ": " . $respAfip->FECAESolicitarResult->Errors->Err[$i]->Msg;
    								}
    
    							} 
    							else {
    
    								$msjAfip['errores'] = $msjAfip['errores'] . '1) ' . $respAfip->FECAESolicitarResult->Errors->Err->Code . ": " . $respAfip->FECAESolicitarResult->Errors->Err->Msg;
    
    							}
    
    						}
    
    						//Existen Observaciones en la facturacion
    						if (property_exists ($respAfip->FECAESolicitarResult, 'FeDetResp')){
    
    							if (property_exists($respAfip->FECAESolicitarResult->FeDetResp->FECAEDetResponse, 'Observaciones')){
    
    							  $msjAfip['observaciones'] = "";
    							  $arrObserva =$respAfip->FECAESolicitarResult->FeDetResp->FECAEDetResponse->Observaciones->Obs;
    							  $i = 1;
    							  if (is_array($arrObserva)){ //Si es un array lo recorro con un foreach (cuando hay mas de una observacion)
    
    								foreach ($arrObserva as $obser) {
    
    								  $msjAfip['observaciones'] = $msjAfip['observaciones'] . $i .') '. $obser->Code . ": " . $obser->Msg . ' ';
    								  $i++;
    
    								}
    
    							  } 
    							  else { //Si solo es una observacion, viene como un objeto stdClass, con get_obt... lo paso a array
    								 $obser = get_object_vars($respAfip->FECAESolicitarResult->FeDetResp->FECAEDetResponse->Observaciones->Obs);
    								 $msjAfip['observaciones'] = $msjAfip['observaciones'] . '1) '. $obser["Code"] . ": " . $obser["Msg"] . ' ';
    							  }
    
    							}
    						  
    						}
    
    						if ($respAfip->FECAESolicitarResult->FeCabResp->Resultado == "A") { //Factura Aprobada
    
    							$facturar = true; //Aca aviso que se pudo autorizar el comprobante
    
    						} else { //Factura rechazada 
    
    							//Existen Observaciones en la facturacion
    							/*if (property_exists ($respAfip->FECAESolicitarResult, 'FeDetResp')){
    
    								if (property_exists($respAfip->FECAESolicitarResult->FeDetResp->FECAEDetResponse, 'Observaciones')){
    
    								  $msjAfip['observaciones'] = "";
    								  $arrObserva =$respAfip->FECAESolicitarResult->FeDetResp->FECAEDetResponse->Observaciones->Obs;
    								  $i = 1;
    								  if (is_array($arrObserva)){ //Si es un array lo recorro con un foreach (cuando hay mas de una observacion)
    
    									foreach ($arrObserva as $obser) {
    
    									  $msjAfip['observaciones'] = $msjAfip['observaciones'] . $i .') '. $obser->Code . ": " . $obser->Msg . ' ';
    									  $i++;
    
    									}
    
    								  } 
    								  else { //Si solo es una observacion, viene como un objeto stdClass, con get_obt... lo paso a array
    									 $obser = get_object_vars($respAfip->FECAESolicitarResult->FeDetResp->FECAEDetResponse->Observaciones->Obs);
    									 $msjAfip['observaciones'] = $msjAfip['observaciones'] . '1) '. $obser["Code"] . ": " . $obser["Msg"] . ' ';
    								  }
    
    								}
    							  
    							  }*/
    							  
    							 return $devuelvo = array('estado' => 'error',
    											 		  'factura' => $msjAfip);
    
    						}
    
    					} catch (Exception $e) {
    
    						file_put_contents('exeption', $e);
    
    						return $devuelvo = array('estado' => 'error',
    												'factura' => $e);
    					}
    				
    				} else {
    
    					return $devuelvo = array('estado' => 'error',
    											'factura' => "Concepto, punto de venta y tipo de comprobante deben ser seleccionados para solicitar autorización");
    
    				}
    
    			}
    
    			/*=============================================
    			INGRESAR REGISTROS EN LA TABLA VENTAS
    			=============================================*/
    			/*=============================================
    			GENERO EL CODIGO DE VENTA
    			=============================================*/	
    			$codigoVenta = ModeloVentas::mdlMostrarUltimoCodigo('ventas');
    
    			if(!$codigoVenta){
    
    				$codigoSiguiente = '10001';
    
    			}else{
    
    				$codigoSiguiente = $codigoVenta["ultimo"] + 1;
    
    			}
    
    			/*=============================================
    			ACTUALIZAR LAS COMPRAS DEL CLIENTE Y REDUCIR EL STOCK Y AUMENTAR LAS VENTAS DE LOS PRODUCTOS
    			=============================================*/
    			//tipos de comprobantes que suman stock (notas credito, devoluciones, etc )
    			$tipoCbteDevuelveStock = array(
    				3,		//Nota credito A 
    				8,		//Nota credito B
    				13,		//Nota Credito C
    				203,	//NOTA DE CREDITO ELECTRÓNICA MiPyMEs (FCE) A
    				208,	//NOTA DE CREDITO ELECTRÓNICA MiPyMEs (FCE) B
    				213,	//NOTA DE CREDITO ELECTRÓNICA MiPyMEs (FCE) C
    				999		//DEVOLUCION X
    			);
    
    			$listaProductos = json_decode($postVentaCaja["listaProductosCaja"], true);
    
    			$totalProductosComprados = array();
    			$tablaProductos = "productos";
    			
    			$sacoStockDe = $postVentaCaja["sucursalVendedor"]; //sucursal de donde muevo stock
    
    			foreach ($listaProductos as $key => $value) {
    
    				array_push($totalProductosComprados, $value["cantidad"]);
    				$valor = $value["id"];
    				$traerProducto = ModeloProductos::mdlMostrarProductos($tablaProductos, 'id', $valor, 'codigo');
    				$stockOriginal = $traerProducto[$sacoStockDe];
    
    				//SELECCIONO SI LA OPERACION VA A SUMAR O RESTAR STOCK
    				if($value["id"]>9){ //productos del 1 al 10 no hace movimientos en stock
    					if(in_array($tipoCbte, $tipoCbteDevuelveStock)) {//si tipocbte esta en array, devuelvo stock
    						$valorVentas = $value["cantidad"] - $traerProducto["ventas"];
    						$valorStock = $stockOriginal + $value["cantidad"];
    					} else {
    						$valorVentas = $value["cantidad"] + $traerProducto["ventas"];
    						$valorStock = $stockOriginal - $value["cantidad"];
    					}
    
    				
    				} else {
    					$valorVentas = 0;
    				}
    
    				//Contabilizo la cantidad de ventas
    				//$nuevasVentas = ModeloProductos::mdlActualizarProducto($tablaProductos, 'ventas', $valorVentas, $valor, 'Nueva venta  (' . $codigoSiguiente. ')');
    
    			}
    
    			$tablaClientes = "clientes";
    			$valor = $postVentaCaja["seleccionarCliente"];
    			$traerCliente = ModeloClientes::mdlMostrarClientes($tablaClientes, 'id', $valor);
    
    			$valor1a = array_sum($totalProductosComprados) + $traerCliente["compras"];
    			//$comprasCliente = ModeloClientes::mdlActualizarCliente($tablaClientes, 'compras', $valor1a, $valor);
    
    			$fechaCliente = ModeloClientes::mdlActualizarCliente($tablaClientes, 'ultima_compra', $fec_hor, $valor);
    
    			$estado = 1; //ESTADO VENTA: 1 PAGADO | 0 ADEUDADO | 2 CTA CTE 
    
    			/*if($postVentaCaja["listaMetodoPagoCaja"] == "Mixto") {
    
    				$lstMetodoPago = json_decode($postVentaCaja["mxMediosPagos"]);
    
    			} else {
    
    				//Inserto la entrega inicial / pago
    				//$importePagado = ($postVentaCaja["listaMetodoPagoCaja"] == "CC") ? $postVentaCaja["ccEntregaInicial"] : $postVentaCaja["nuevoTotalVentaCaja"];
    				$importePagado = $postVentaCaja["nuevoTotalVentaCaja"];
    
    				$estado = ($postVentaCaja["listaMetodoPagoCaja"] == "CC") ? 2 : $estado;
    
    				//Inserto cantidad de cuotas (sino hay pongo 0). Esta es para cuenta corriente
    				//$cantidadCuotas = 0;//($postVentaCaja["ccCantidadCuotas"] != "") ? $postVentaCaja["ccCantidadCuotas"] : 0;
    
    				$lstMetodoPago = array (
    
    									array(
    										"tipo" => $postVentaCaja["listaMetodoPagoCaja"],
    										"entrega" => $importePagado, 
    									)
    								);
    			}*/
    
    			if(isset($postVentaCaja["mxMediosPagos"]) && $postVentaCaja["mxMediosPagos"] != '[]' && $postVentaCaja["mxMediosPagos"] != "") {
    				//vienen varios metodos de pago
    				$lstMetodoPago = json_decode($postVentaCaja["mxMediosPagos"], true);
    
    			} else {
    				//un solo medio de pago
    				//Inserto la entrega inicial / pago
    				$importePagado = $postVentaCaja["nuevoTotalVentaCaja"];
    				$estado = ($postVentaCaja["listaMetodoPagoCaja"] == "CC") ? 2 : $estado;
    				$lstMetodoPago = array (
    									array(
    										"tipo" => $postVentaCaja["listaMetodoPagoCaja"],
    										"entrega" => $importePagado, 
    									)
    								);
    			}
    
    			/*=============================================
    			GUARDAR LA VENTA
    			=============================================*/	
    			$tabla = "ventas";
    			$datos = array(
    			    "uuid" => $postVentaCaja["tokenIdTablaVentas"],
    				"id_vendedor"=>$postVentaCaja["idVendedor"],
    			   	"id_cliente"=>$postVentaCaja["seleccionarCliente"],
    			   	"codigo"=>$codigoSiguiente, //$postVentaCaja["nuevaVentaCaja"],
    			   	"productos"=>$postVentaCaja["listaProductosCaja"],
    			   	"neto"=>$postVentaCaja["nuevoPrecioNetoCaja"],
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
    			   	// "impuesto"=>$postVentaCaja["nuevoPrecioImpuestoCaja"],
    			   	"total"=>$postVentaCaja["nuevoTotalVentaCaja"],
    			   	"metodo_pago"=> json_encode($lstMetodoPago),
    			   	"estado" => $estado,
    			   	"fecha" => $postVentaCaja["fechaActual"], 
    		   		"cbte_tipo" => $postVentaCaja["nuevotipoCbte"] + 0,
    				"pto_vta" => ($postVentaCaja["nuevaPtoVta"] == '') ? 1 : $postVentaCaja["nuevaPtoVta"] + 0,
    				"concepto" => $postVentaCaja["nuevaConcepto"],
    				"fec_desde" => $postVentaCaja["nuevaFecDesde"],
    				"fec_hasta" => $postVentaCaja["nuevaFecHasta"],
    				"fec_vencimiento" => $postVentaCaja["nuevaFecVto"],
    				"observaciones_vta" => "",
    				"asociado_tipo_cbte" => $postVentaCaja["nuevotipoCbteAsociado"],
    				"asociado_pto_vta" => $postVentaCaja["nuevaPtoVtaAsociado"],
    				"asociado_nro_cbte" => $postVentaCaja["nuevaNroCbteAsociado"],
    				"pedido_afip" => $pedidoAfip,
    				"respuesta_afip" => $respuestaAfip
    				);
    
    			$respuesta = ModeloVentas::mdlIngresarVenta($tabla, $datos);
				//ultimo id de venta
    			$ultimoidVta = ModeloVentas::mdlUltimoId($tabla);

    			/********************************************
    			*	INGRESO REGISTRO A CAJA O CUENTA CORRIENTE
    			********************************************/
    			$ultimocodigo = ModeloVentas::mdlMostrarUltimoCodigo($tabla);
    
    			//SELECCIONO SI LA OPERACION VA A SUMAR O RESTAR CAJA / CTA. CTE 
    			if(in_array($tipoCbte, $tipoCbteDevuelveStock)) {
    				$tipoCtaCte = 1;
    				$tipoCaja = 0;
    				$descripcionCtaCte = 'Devolucion - Cbte. N°: ' . $codigoSiguiente;
    				$descripcionCaja = 'Egreso por devolucion - N° ' . $ultimocodigo["ultimo"];
    			} else {
    				$tipoCtaCte = 0;
    				$tipoCaja = 1;
    				$descripcionCtaCte = 'Venta - Cbte. N°: ' . $codigoSiguiente;
    				$descripcionCaja = "Ingreso por venta - N° ".$ultimocodigo["ultimo"];
    			}
    
    			if($estado == 2) { //si es cuenta corriente
    
    				$tablaCtaCte = "clientes_cuenta_corriente";
    				$datosCtaCte = array(
    						'fecha' => $fec_hor,
    						'id_cliente' => $postVentaCaja["seleccionarCliente"],
    						'tipo' => $tipoCtaCte,
    						'descripcion' => $descripcionCtaCte, 
    						'id_venta' => $ultimoidVta["ultimo"], 
    						'importe' => $postVentaCaja["nuevoTotalVentaCaja"],
    						'metodo_pago' => json_encode($lstMetodoPago));
    
    				$respuestaDos = ModeloClientesCtaCte::mdlIngresarCtaCte($tablaCtaCte, $datosCtaCte);
    
    			} else { //sino es cta cte, inserto en caja
    
    				//$medioP = (isset($postVentaCaja["listaMetodoPagoCaja"])) ? $postVentaCaja["listaMetodoPagoCaja"] : 'Efectivo';
    
    			    $tablaCaja = "cajas";
    
    			   	foreach($lstMetodoPago as $llave => $valor) {
    				   	$datosCaja = array(
    				   					"tipo" => $tipoCaja, //ingreso 1 - egreso 0
    				   					"id_usuario" => $postVentaCaja["idVendedor"],
    				   					"punto_venta" => ($postVentaCaja["nuevaPtoVta"] == '') ? 1 : $postVentaCaja["nuevaPtoVta"] + 0,
    					            	"monto" => $valor["entrega"],
    					            	"medio_pago" => $valor["tipo"],
    					                "descripcion"=>$descripcionCaja,
    					                "codigo_venta"=>$ultimocodigo["ultimo"],
    					   				"id_venta" => $ultimoidVta["ultimo"],
    				   					"id_cliente_proveedor" => $postVentaCaja["seleccionarCliente"],
    					                "fecha"=>$fec_hor);
    
    				   	$respuestaDos = ModeloCajas::mdlIngresarCaja($tablaCaja, $datosCaja);
    
    			   }
    
    			}
    
    			/*=============================================
    					INSERTO REGISTRO EN VENTA FACTURA
    			=============================================*/
    			if($facturar){
    
    				$datosFactura = array(
    					"id_venta"=> $ultimoidVta["ultimo"],
    					"fec_factura" => $respAfip->FECAESolicitarResult->FeDetResp->FECAEDetResponse->CbteFch,
    					"nro_cbte" => $respAfip->FECAESolicitarResult->FeDetResp->FECAEDetResponse->CbteDesde,
    					"pto_vta" => ($postVentaCaja["nuevaPtoVta"] == '') ? 0 : $postVentaCaja["nuevaPtoVta"] + 0,
    				   	"neto_gravado"=>$netoGravado,
    				 	"impuesto_detalle"=>$impuestoDetalle,
    				 	"total" => $postVentaCaja["nuevoTotalVentaCaja"],
    				 	"impuesto" => $impuesto,
    					"cae" => $respAfip->FECAESolicitarResult->FeDetResp->FECAEDetResponse->CAE,
    					"fec_vto_cae" => $respAfip->FECAESolicitarResult->FeDetResp->FECAEDetResponse->CAEFchVto
    				   );
    
    				$respuestaFact = ModeloVentas::mdlFacturarVenta("ventas_factura", $datosFactura);
    			}
    
    			/*=============================================
    			EVALUACION DE RESPUESTAS DE VENTA, CAJA Y FACTURACION
    			=============================================*/
    		   	if ($respuesta == "ok" && $respuestaDos == "ok") {
    		   		$devuelvo = array('estado' => 'ok',
    		   						  'codigoVta' => $codigoSiguiente,
    		   						  'factura' => $datosFactura,
    		   						  'msjAfip' => $msjAfip, 
    		   						  'datosFacturacion' => (isset($datosFacturacion)) ? $datosFacturacion : false );
    
    		   	} else {
    				
    				//return "error";
    				$devuelvo = array('estado' => 'error',
    							   'modeloVentas' => $respuesta,
    							   'modeloCaja' => $respuestaDos,
    							   'modeloVentFac' => $respuestaFact);
    
    		   	}
    
    		 	return $devuelvo;
    
    		}
		}
	}

	/*=============================================
	ACTUALIZAR VENTAS
	=============================================*/
	static public function ctrActualizarVenta($item, $valor, $id){
		$tabla = "ventas";
		$respuesta = ModeloVentas::mdlActualizarVenta($tabla, $item, $valor, $id);
		return $respuesta;
	}

	/*=============================================
	EDITAR VENTA
	=============================================*/
	static public function ctrEditarVenta(){
		if(isset($_POST["editarVenta"])){

			/*=============================================
			FORMATEAR TABLA DE PRODUCTOS Y LA DE CLIENTES
			=============================================*/
			$tabla = "ventas";

			$item = "codigo";
			$valor = $_POST["editarVenta"];

			$traerVenta = ModeloVentas::mdlMostrarVentas($tabla, $item, $valor);

			/*=============================================
			REVISAR SI VIENE PRODUCTOS EDITADOS
			=============================================*/

			if($_POST["listaProductos"] == ""){

				$listaProductos = $traerVenta["productos"];
				$cambioProducto = false;


			}else{

				$listaProductos = $_POST["listaProductos"];
				$cambioProducto = true;
			}

			if($cambioProducto){

				$productos =  json_decode($traerVenta["productos"], true);

				$totalProductosComprados = array();
				foreach ($productos as $key => $value) {

					array_push($totalProductosComprados, $value["cantidad"]);
					$tablaProductos = "productos";

					$item = "id";
					$valor = $value["id"];
					$orden = "id";

					$traerProducto = ModeloProductos::mdlMostrarProductos($tablaProductos, $item, $valor, $orden);

					$item1a = "ventas";
					$valor1a = $traerProducto["ventas"] - $value["cantidad"];

					$nuevasVentas = ModeloProductos::mdlActualizarProducto($tablaProductos, $item1a, $valor1a, $valor, 'Editar venta  (' . $traerVenta["codigo"]. ') S.A.');

					$item1b = "stock";
					$valor1b = $value["cantidad"] + $traerProducto["stock"];

					$nuevoStock = ModeloProductos::mdlActualizarProducto($tablaProductos, $item1b, $valor1b, $valor, 'Editar venta  (' . $traerVenta["codigo"]. ') S.A.');

				}

				$tablaClientes = "clientes";

				$itemCliente = "id";
				$valorCliente = $_POST["seleccionarCliente"];

				$traerCliente = ModeloClientes::mdlMostrarClientes($tablaClientes, $itemCliente, $valorCliente);

				$item1a = "compras";
				$valor1a = $traerCliente["compras"] - array_sum($totalProductosComprados);		

				$comprasCliente = ModeloClientes::mdlActualizarCliente($tablaClientes, $item1a, $valor1a, $valorCliente);

				/*=============================================
				ACTUALIZAR LAS COMPRAS DEL CLIENTE Y REDUCIR EL STOCK Y AUMENTAR LAS VENTAS DE LOS PRODUCTOS
				=============================================*/

				$listaProductos_2 = json_decode($listaProductos, true);

				$totalProductosComprados_2 = array();

				foreach ($listaProductos_2 as $key => $value) {

					array_push($totalProductosComprados_2, $value["cantidad"]);
					
					$tablaProductos_2 = "productos";

					$item_2 = "id";
					$valor_2 = $value["id"];
					$orden = "id";

					$traerProducto_2 = ModeloProductos::mdlMostrarProductos($tablaProductos_2, $item_2, $valor_2, $orden);

					$item1a_2 = "ventas";
					$valor1a_2 = $value["cantidad"] + $traerProducto_2["ventas"];

					$nuevasVentas_2 = ModeloProductos::mdlActualizarProducto($tablaProductos_2, $item1a_2, $valor1a_2, $valor_2, 'Editar venta  (' . $traerVenta["codigo"]. ') S.N.');

					$item1b_2 = "stock";
					$stkActual = $traerProducto_2["stock"];
					$valor1b_2 = $stkActual - $value["cantidad"];
					$nuevoStock_2 = ModeloProductos::mdlActualizarProducto($tablaProductos_2, $item1b_2, $valor1b_2, $valor_2, 'Editar venta  (' . $traerVenta["codigo"]. ') S.N.');

				}

				$tablaClientes_2 = "clientes";

				$item_2 = "id";
				$valor_2 = $_POST["seleccionarCliente"];

				$traerCliente_2 = ModeloClientes::mdlMostrarClientes($tablaClientes_2, $item_2, $valor_2);

				$item1a_2 = "compras";

				$valor1a_2 = array_sum($totalProductosComprados_2) + $traerCliente_2["compras"];

				$comprasCliente_2 = ModeloClientes::mdlActualizarCliente($tablaClientes_2, $item1a_2, $valor1a_2, $valor_2);

				$item1b_2 = "ultima_compra";

				date_default_timezone_set('America/Argentina/Mendoza');

				$fecha = date('Y-m-d');
				$hora = date('H:i:s');
				$valor1b_2 = $fecha.' '.$hora;

				$fechaCliente_2 = ModeloClientes::mdlActualizarCliente($tablaClientes_2, $item1b_2, $valor1b_2, $valor_2);

			}

			/*=============================================
			MEDIOS DE PAGO
			=============================================*/	
			$lstMetodoPago = array (
									array(
										"tipo" => $_POST["listaMetodoPago"],
 										"interes" => $_POST["nuevoInteresPorcentaje"],
										"descuento" => $_POST["nuevoDescuentoPorcentaje"], 
										"entrega" => $_POST["totalVenta"], 
										"cuotas" => 0
									)
								);

			/*=============================================
			GUARDAR CAMBIOS DE LA VENTA
			=============================================*/	
			$datos = array("id_vendedor"=>$_POST["idVendedor"],
						   "id_cliente"=>$_POST["seleccionarCliente"],
						   "codigo"=>$_POST["editarVenta"],
						   "cbte_tipo" => $_POST["editartipoCbte"],
						   "productos"=>$listaProductos,
						   "impuesto"=>$_POST["nuevoPrecioImpuesto"],
						   "neto"=>$_POST["nuevoPrecioNeto"],
						   "total"=>$_POST["totalVenta"],
						   "metodo_pago" => json_encode($lstMetodoPago),
						   "pto_vta" => ($_POST["editarPtoVta"] == '') ? 0 : $_POST["editarPtoVta"],
						   "concepto" => $_POST["editarConcepto"],
						   "fec_desde" =>  $_POST["editarFecDesde"],
				 		   "fec_hasta" => $_POST["editarFecHasta"],
						   "fec_vencimiento" => $_POST["editarFecVto"],
						   // "estado" => 0, 
						   "observaciones_vta" => $_POST["editarObservacionVenta"]);

			$respuesta = ModeloVentas::mdlEditarVenta($tabla, $datos);

			/************************************
			*	FACTURAR
			*************************************/
			$respuestaFac = false;
			if($_POST["nuevotipoCbte"] <> "0" ){
				$respuestaFac = self::ctrFacturarVenta(self::ctrUltimoId()['ultimo']);
			}			

			if($respuesta == "ok"){
				echo'<script>
				localStorage.removeItem("rango");
				swal({
					  type: "success",
					  title: "Ventas",
					  text: "La venta ha sido editada correctamente",
					  showConfirmButton: true,
					  confirmButtonText: "Cerrar"
					  }).then((result) => {
								if (result.value) {

								window.location = "ventas";

								}
							})

				</script>';
			}
		}
	}

	/*=============================================
	ELIMINAR VENTA
	=============================================*/
	static public function ctrEliminarVenta(){
		if(isset($_GET["idVenta"])){
			$tabla = "ventas";
			$item = "id";
			$valor = $_GET["idVenta"];
			$traerVenta = ModeloVentas::mdlMostrarVentas($tabla, $item, $valor);

			/*=============================================
			ACTUALIZAR FECHA ÚLTIMA COMPRA
			=============================================
			$tablaClientes = "clientes";
			$itemVentas = null;
			$valorVentas = null;
			$traerVentas = ModeloVentas::mdlMostrarVentas($tabla, $itemVentas, $valorVentas);
			$guardarFechas = array();
			foreach ($traerVentas as $key => $value) {
    			if($value["id_cliente"] == $traerVenta["id_cliente"]){
					array_push($guardarFechas, $value["fecha"]);
				}
			}

			if(count($guardarFechas) > 1){
				if($traerVenta["fecha"] > $guardarFechas[count($guardarFechas)-2]){
					$item = "ultima_compra";
					$valor = $guardarFechas[count($guardarFechas)-2];
					$valorIdCliente = $traerVenta["id_cliente"];
					$comprasCliente = ModeloClientes::mdlActualizarCliente($tablaClientes, $item, $valor, $valorIdCliente);
				}else{
					$item = "ultima_compra";
					$valor = $guardarFechas[count($guardarFechas)-1];
					$valorIdCliente = $traerVenta["id_cliente"];
					$comprasCliente = ModeloClientes::mdlActualizarCliente($tablaClientes, $item, $valor, $valorIdCliente);
				}
			}else{
				$item = "ultima_compra";
				$valor = "0000-00-00 00:00:00";
				$valorIdCliente = $traerVenta["id_cliente"];
				$comprasCliente = ModeloClientes::mdlActualizarCliente($tablaClientes, $item, $valor, $valorIdCliente);
			}*/

			/*=============================================
			FORMATEAR TABLA DE PRODUCTOS Y LA DE CLIENTES
			=============================================*/
			$productos =  json_decode($traerVenta["productos"], true);
			$totalProductosComprados = array();
			foreach ($productos as $key => $value) {
				array_push($totalProductosComprados, $value["cantidad"]);
				$traerProducto = ModeloProductos::mdlMostrarProductos("productos", "id", $value["id"], "id");
				$valor1a = $traerProducto["ventas"] - $value["cantidad"];
				$nuevasVentas = ModeloProductos::mdlActualizarProducto("productos", "ventas", $valor1a, $value["id"], 'Eliminar venta  (' . $traerVenta["codigo"]. ')');
				$valor1b = $value["cantidad"] + $traerProducto["stock"];
				$nuevoStock = ModeloProductos::mdlActualizarProducto("productos", "stock", $valor1b, $value["id"], 'Eliminar venta  (' . $traerVenta["codigo"]. ')');
			}

			/*
			$tablaClientes = "clientes";
			$itemCliente = "id";
			$valorCliente = $traerVenta["id_cliente"];
			$traerCliente = ModeloClientes::mdlMostrarClientes($tablaClientes, $itemCliente, $valorCliente);
			$item1a = "compras";
			$valor1a = $traerCliente["compras"] - array_sum($totalProductosComprados);
			$comprasCliente = ModeloClientes::mdlActualizarCliente($tablaClientes, $item1a, $valor1a, $valorCliente);
            */
            
            
            //SI ESTADO ES 1 (PAGADA) AGREGAR REGISTRO EN CAJA PARA DEVOLUCION DE DINERO
            date_default_timezone_set('America/Argentina/Mendoza');
			$fecha = date('Y-m-d');
			$hora = date('H:i:s');
			$fec_hor = $fecha.' '.$hora;
            if ($traerVenta["estado"] == 1) {

            	$metodoPago = json_decode($traerVenta["metodo_pago"], true);
            	foreach ($metodoPago as $key => $value) {
            		// code...
	        		$datosCaja = array(
						"tipo" => 0, //ingreso 1 - egreso 0
						"id_usuario" => $_SESSION['id'],
						"punto_venta" => $traerVenta["pto_vta"],
						"monto" => $value["entrega"],
						"medio_pago" => $value["tipo"],
						"descripcion"=> 'Egreso por venta anulada ('.$traerVenta["codigo"].')',
						"id_venta"=>$traerVenta["id"],
						"codigo_venta"=>$traerVenta["codigo"],
						"id_cliente_proveedor" => $traerVenta["id_cliente"],
						"fecha"=>$fec_hor);

						$respuestaDos = ModeloCajas::mdlIngresarCaja('cajas', $datosCaja);
				}
            //SI ESTADO ES 2 (CTA CTE) AGREGAR REGISTRO DE CREDITO EN ID CLIENTE
            } elseif ($traerVenta["estado"] == 2) {
            	$tablaCtaCte = "clientes_cuenta_corriente";
				$datosCtaCte = array(
						'fecha' => $fec_hor,
						'id_cliente' => $traerVenta["id_cliente"],
						'tipo' => 1,
						'descripcion' => 'Anulación venta - Cbte N°: '.$traerVenta["codigo"],
						'id_venta' => $traerVenta["id"],
						'importe' => $traerVenta["total"],
						'metodo_pago' => null);

    			$respuestaDos = ModeloClientesCtaCte::mdlIngresarCtaCte($tablaCtaCte, $datosCtaCte);
            
            }

  			/*=============================================
			ELIMINAR VENTA
    		=============================================*/
			$respuesta = ModeloVentas::mdlEliminarVenta($tabla, $_GET["idVenta"]);
			if($respuesta == "ok"){
    			echo'<script>
    				swal({
					  type: "success",
					  title: "Ventas",
					  text: "La venta ha sido borrada correctamente",					  
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
	}

	/*=============================================
	RANGO FECHAS
	=============================================*/	
	static public function ctrRangoFechasVentas($fechaInicial, $fechaFinal){
		$tabla = "ventas";
		$respuesta = ModeloVentas::mdlRangoFechasVentas($tabla, $fechaInicial, $fechaFinal);
		return $respuesta;
	}

	/*=============================================
	SUMA TOTAL VENTAS
	=============================================*/
	static public function ctrSumaTotalVentas(){
		$tabla = "ventas";
		$respuesta = ModeloVentas::mdlSumaTotalVentas($tabla);
		return $respuesta;
	}

	/*=============================================
	CONSULTAR POR VENTA FACTURADA
    =============================================*/
	static public function ctrVentaFacturada($id){
		$respuesta = ModeloVentas::mdlVentaFacturada($id);
		return $respuesta;
	}

	/*=============================================
	CONSULTAR POR VENTA FACTURADA
	=============================================*/
	static public function ctrVentaFacturadaDatos($id){
		$respuesta = ModeloVentas::mdlVentaFacturadaDatos($id);
		return $respuesta;
	}

	/*=============================================
	CONSULTAR POR VENTA FACTURADA (RESP AFIP)
	=============================================*/
	static public function ctrVentaFacturadaDatosAfip($id){

	//ARMAR

		// $respuesta = ModeloVentas::mdlVentaFacturadaDatos($id);

		// return $respuesta;

	}
	/*=============================================
	MOSTRAR ULTIMO ID
	=============================================*/
	static public function ctrUltimoId(){
		$tabla = "ventas";
		$respuesta = ModeloVentas::mdlUltimoId($tabla);
		return $respuesta;
	}

	/*=============================================
	AUTORIZAR COMPROBANTE (desde ventas.php)
	=============================================*/
	static public function ctrAutorizarCbte(){

		if(isset($_POST["autorizarCbteIdVenta"])) {

			if(ModeloVentas::mdlVentaFacturada($_POST["autorizarCbteIdVenta"])){
				echo'<script>

				swal({
					  type: "error",
					  title: "Ventas",
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

			$idVenta = $_POST["autorizarCbteIdVenta"];
			$tipoCbte = $_POST["autorizarCbteTipoCbte"] + 0;
			$idCliente = (isset($_POST["autorizarCbteCliente"])) ? $_POST["autorizarCbteCliente"] : 1;

			//Primero actualizo el tipo comprobante, si no se llega a aprobar va a salir en ventas.php con un signo de exclamación
			$actualizarVta = ModeloVentas::mdlActualizarVenta('ventas', 'cbte_tipo', $tipoCbte, $idVenta);
			//Actualizo el cliente
			$actualizarVtaCli = ModeloVentas::mdlActualizarVenta('ventas', 'id_cliente', $idCliente, $idVenta);

			$respuesta = self::ctrFacturarVenta($idVenta, $tipoCbte);

			if($respuesta){

				echo'<script>
				swal({
					  type: "success",
					  title: "Ventas",
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
	static public function ctrFacturarVenta($idVenta, $tipo_comprobante){

		$msjAfip = array();

		$arrEmpresa = ModeloEmpresa::mdlMostrarEmpresa("empresa", "id", 1);

		$venta = ModeloVentas::mdlMostrarVentas("ventas", "id", $idVenta);

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
		                'MonCotiz' => 1,
		                'CondicionIVAReceptorId' => (int)$cliente["condicion_iva"]
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
				ModeloVentas::mdlPedidoAfipVenta("ventas", $pedidoAfip, $idVenta);

				//Aca sucede la magia.... o no
				$respAfip = $wsfe->CAESolicitar($datosFactura['pto_vta'], $datosFactura['cbte_tipo'], $datosFacturacion);

				//guardo la respuesta completa como la devuelve afip
				ModeloVentas::mdlRespuestaAfipVenta("ventas", json_encode($respAfip), $idVenta);

				//Procesamiento de respuesta AFIP
				if (property_exists ($respAfip->FECAESolicitarResult, 'FeCabResp')) {

					if ($respAfip->FECAESolicitarResult->FeCabResp->Resultado == "A") { //Factura Aprobada

						//Guardo en venta_factura los datos del comprobante aprobado
						$datosFactura = array(
							"id_venta"=> $idVenta,
							"fec_factura" => $respAfip->FECAESolicitarResult->FeDetResp->FECAEDetResponse->CbteFch,
							"nro_cbte" => $respAfip->FECAESolicitarResult->FeDetResp->FECAEDetResponse->CbteDesde,
							"cae" => $respAfip->FECAESolicitarResult->FeDetResp->FECAEDetResponse->CAE,
							"fec_vto_cae" => $respAfip->FECAESolicitarResult->FeDetResp->FECAEDetResponse->CAEFchVto
						   );

						$respuestaFact = ModeloVentas::mdlFacturarVenta("ventas_factura", $datosFactura);

						echo '<script>

							swal({
								  type: "success",
								  title: "Ventas",
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

						  ModeloVentas::mdlObservacionesVenta('ventas', json_encode($msjAfip), $idVenta);
					
						echo'<script>
						swal({
							  type: "error",
							  title: "Ventas",
							  html: "<p>Error al intentar autorizar. <a href=\"index.php?ruta=editar-venta&idVenta='.$idVenta.'\"> Ver observaciones en venta</a></p>",
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
					
					ModeloVentas::mdlObservacionesVenta('ventas', json_encode($msjAfip), $idVenta);
					//OCURRIO ERROR AL INTENTAR FACTURAR ($respAFIP viene sin FeCabResp)
					echo'<script>
						swal({
								  type: "error",
								  title: "Ventas",
								  html: "<p>Error al intentar autorizar. <a href=\"index.php?ruta=editar-venta&idVenta='.$idVenta.'\"> Ver errores/eventos en venta</a></p>",
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
						  title: "Ventas",
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
				  title: "Ventas",
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
	TOTAL VENTAS POR RANGO FECHA
	=============================================*/	
	static public function ctrRangoFechasTotalVentas($fechaInicial, $fechaFinal){

		$respuesta = ModeloVentas::mdlRangoFechasTotalVentas($fechaInicial, $fechaFinal);

		return $respuesta;
		
	}	

	/*=============================================
	MOSTRAR VENTA CON CLIENTE
	=============================================*/	
	static public function ctrMostrarVentaConCliente($idVenta){

		$respuesta = ModeloVentas::mdlMostrarVentaConCliente($idVenta);

		return $respuesta;
		
	}	

	/*=============================================
	LIBRO IVA VENTAS
	=============================================*/	
	static public function ctrLibroIvaVentas($fechaInicial, $fechaFinal) {
		$respuesta = ModeloVentas::mdlLibroIvaVentas($fechaInicial, $fechaFinal);
		return $respuesta;
	}
	
	/*=============================================
	RANGO FECHAS SOLO VENTAS (EL OTRO RANGO FECHAS TRAE TODOS LOS REGISTROS DE LA TABLA VENTA)
	=============================================*/	
	static public function ctrRangoFechasSoloVentas($fechaInicial, $fechaFinal){
		$respuesta = ModeloVentas::mdlRangoFechasSoloVentas($fechaInicial, $fechaFinal);
		return $respuesta;
	}

	/*=============================================
	RANGO FECHAS SOLO VENTAS (EL OTRO RANGO FECHAS TRAE TODOS LOS REGISTROS DE LA TABLA VENTA)
	=============================================*/	
	static public function ctrRangoVentasPorMesAnio($fechaInicial, $fechaFinal){
		$respuesta = ModeloVentas::mdlRangoVentasPorMesAnio($fechaInicial, $fechaFinal);
		return $respuesta;
	}

}