<?php

class ControladorCompras{

	//MOSTRAR COMPRAS
	static public function ctrMostrarCompras($item, $valor){
		$tabla = "compras";
		$respuesta = ModeloCompras::mdlMostrarCompras($tabla, $item, $valor);
		return $respuesta;
	}
	
	//MOSTRAR COMPRAS VALIDADAS
	static public function ctrMostrarComprasValidados($item, $valor){
		$tabla = "compras";
		$respuesta = ModeloCompras::mdlMostrarComprasValidados($tabla, $item, $valor);
		return $respuesta;
	}

	//CREAR VENTA
	static public function ctrCrearCompra(){
		if(isset($_POST["seleccionarProveedor"])){
			// Validar token CSRF
			if(!isset($_POST["csrf_token"]) || !isset($_SESSION['csrf_token']) || $_POST["csrf_token"] !== $_SESSION['csrf_token']){
				echo'<script>
				swal({
					  type: "error",
					  title: "Error de seguridad",
					  text: "Token CSRF inválido",
					  showConfirmButton: true,
					  confirmButtonText: "Cerrar"
					  })
				</script>';
				return;
			}
			
			if($_POST["listaProductosCompras"] == ""){
				echo'<script>
				swal({
					  type: "error",
					  title: "Compras",
					  text: "La compra no se ha ejecuta si no hay productos",
					  showConfirmButton: true,
					  confirmButtonText: "Cerrar"
					  })
				</script>';
				return;
			}

			date_default_timezone_set('America/Argentina/Mendoza');
			
			$ultimCodigo = ModeloCompras::mdlUltimoIdCodigoCompras('codigo');
			$codigo = $ultimCodigo["ultimo"] + 1;
			$listaProductosCompras = json_decode($_POST["listaProductosCompras"], true);
			$tablaProductos = "productos";
			$item = "id";
			$orden = "id";
			foreach ($listaProductosCompras as $key => $value) {
			    $valor = $value["id"];
			    $traerProducto = ModeloProductos::mdlMostrarProductos($tablaProductos, $item, $valor, $orden);
				$precioCompra = $value["precioCompra"];
				$ganancia = $value["ganancia"];
				$precioVenta = $value["precioVenta"];
				$iva = 1 + ($value["tipo_iva"] / 100);
				
				$respAct = ModeloProductos::mdlActualizarProductoCompraIngreso($precioCompra, $ganancia, $precioVenta, $valor, 'Crear orden compra ('.$codigo.')');
			}
			
			$fecha = date('Y-m-d');
			$hora = date('H:i:s');
			$fec_hora = $fecha.' '.$hora;

			//GUARDAR LA COMPRA
			$tabla = "compras";
			$datos = array(
				"fecha" => $fec_hora,
				"usuarioPedido"=>$_POST["usuarioPedidoOculto"],
                "usuarioConfirma"=>0,
                "id_proveedor"=>$_POST["seleccionarProveedor"],
                "fechaEntrega"=>$_POST["fechaEntrega"],
                "fechaPago"=>$_POST["fechaPago"],
                "codigo"=>$codigo,
                "productos"=>$_POST["listaProductosCompras"],
                "estado"=>0,
                "total"=>$_POST["totalCompra"]);

			$respuesta = ModeloCompras::mdlIngresarCompra($tabla, $datos);

			if($respuesta == "ok"){
				echo'<script>
				localStorage.removeItem("rango");
				swal({
					  type: "success",
					  title: "Compras",
					  text: "La compra ha sido guardada correctamente",
					  showConfirmButton: true,
					  confirmButtonText: "Cerrar"
					  }).then(function(result){
						if (result.value) {
							window.location = "ingreso";
						}
					})
				</script>';

			} else {
				echo'<script>
				localStorage.removeItem("rango");
				swal({
					  type: "error",
					  title: "Ocurri�� un error al guardar la compra",
					  showConfirmButton: true,
					  confirmButtonText: "Cerrar"
					  }).then(function(result){
						if (result.value) {
							window.location = "ingreso";
						}
					})
				</script>';
			}
		}
	}

	/*=============================================
	CREAR FACTURA DIRECTA (SIN ORDEN PREVIA)
	=============================================*/
	static public function ctrCrearFacturaDirecta(){
		if(isset($_POST["crearFacturaDirecta"])){
			// Validar token CSRF
			if(!isset($_POST["csrf_token"]) || !isset($_SESSION['csrf_token']) || $_POST["csrf_token"] !== $_SESSION['csrf_token']){
				echo'<script>
				swal({
					  type: "error",
					  title: "Error de seguridad",
					  text: "Token CSRF inválido",
					  showConfirmButton: true,
					  confirmButtonText: "Cerrar"
					  })
				</script>';
				return;
			}
			
			// Permitir facturas de servicios sin productos físicos
			// Si listaProductosCompras está vacío pero es factura directa, permitir continuar
			// (útil para facturas de servicios como EDEMSA)
			if($_POST["listaProductosCompras"] == ""){
				// Verificar si es una factura de servicio (sin productos físicos)
				// En este caso, se puede crear una factura con monto pero sin productos
				if(isset($_POST["nuevoTotalFactura"]) && floatval($_POST["nuevoTotalFactura"]) > 0){
					// Es una factura de servicio, crear un producto virtual
					$productoServicio = array(
						array(
							"id" => 0, // ID 0 indica producto servicio
							"descripcion" => "SERVICIO - " . ($_POST["observacionFactura"] ?? "Factura de servicio"),
							"pedidos" => 1,
							"recibidos" => 0,
							"precioCompra" => floatval($_POST["nuevoTotalFactura"]),
							"precioCompraOriginal" => floatval($_POST["nuevoTotalFactura"]),
							"ganancia" => 0,
							"tipo_iva" => 0,
							"precioVenta" => floatval($_POST["nuevoTotalFactura"]),
							"total" => floatval($_POST["nuevoTotalFactura"])
						)
					);
					$_POST["listaProductosCompras"] = json_encode($productoServicio);
				} else {
					echo'<script>
					swal({
						  type: "error",
						  title: "Compras",
						  text: "La factura no se puede crear si no hay productos o monto",
						  showConfirmButton: true,
						  confirmButtonText: "Cerrar"
						  })
					</script>';
					return;
				}
			}

			date_default_timezone_set('America/Argentina/Mendoza');
			
			$ultimCodigo = ModeloCompras::mdlUltimoIdCodigoCompras('codigo');
			$codigo = $ultimCodigo["ultimo"] + 1;
			$listaProductosCompras = json_decode($_POST["listaProductosCompras"], true);
			$tablaProductos = "productos";
			$item = "id";
			$orden = "id";
			
			// Procesar productos (actualizar precios, pero NO stock si es servicio)
			foreach ($listaProductosCompras as $key => $value) {
			    $valor = $value["id"];
			    
			    // Si el ID es 0, es un producto servicio virtual (no existe en BD)
			    if($valor == 0 || $valor == "0"){
			    	// Es un servicio virtual, no actualizar precios ni stock
			    	continue;
			    }
			    
			    $traerProducto = ModeloProductos::mdlMostrarProductos($tablaProductos, $item, $valor, $orden);
			    
			    // Si el producto no existe, es un servicio virtual
			    if(!$traerProducto){
			    	continue;
			    }
			    
				$precioCompra = $value["precioCompra"];
				$ganancia = $value["ganancia"];
				$precioVenta = $value["precioVenta"];
				
				// Actualizar precios del producto
				$respAct = ModeloProductos::mdlActualizarProductoCompraIngreso($precioCompra, $ganancia, $precioVenta, $valor, 'Factura directa ('.$codigo.')');
				
				// Actualizar stock SOLO si NO es servicio
				// Un producto es servicio si su descripción contiene "SERVICIO" o si tiene un ID especial
				$esServicio = false;
				if(isset($value["descripcion"]) && stripos($value["descripcion"], "SERVICIO") !== false){
					$esServicio = true;
				}
				// También verificar si el producto tiene stock = 0 y descripción que sugiere servicio
				if(!$esServicio && isset($traerProducto["stock"]) && floatval($traerProducto["stock"]) == 0 && 
				   isset($traerProducto["descripcion"]) && (stripos($traerProducto["descripcion"], "SERVICIO") !== false || 
				   stripos($traerProducto["descripcion"], "EDEMSA") !== false || 
				   stripos($traerProducto["descripcion"], "LUZ") !== false ||
				   stripos($traerProducto["descripcion"], "AGUA") !== false)){
					$esServicio = true;
				}
				
				// Si NO es servicio, actualizar stock
				// En factura directa, usar "pedidos" o "cantidad" como cantidad recibida
				$cantidadRecibida = isset($value["recibidos"]) ? floatval($value["recibidos"]) : 
				                   (isset($value["pedidos"]) ? floatval($value["pedidos"]) : 
				                   (isset($value["cantidad"]) ? floatval($value["cantidad"]) : 0));
				
				if(!$esServicio && $cantidadRecibida > 0){
					$modificoStock = floatval($traerProducto["stock"]) + $cantidadRecibida;
					ModeloProductos::mdlActualizarProducto('productos', 'stock', $modificoStock, $valor, 'Ingreso stock (Factura directa: '.$codigo.')');
				}
			}
			
			$fecha = date('Y-m-d');
			$hora = date('H:i:s');
			$fec_hora = $fecha.' '.$hora;
			$nroFactura = str_pad($_POST["puntoVenta"], 5, "0", STR_PAD_LEFT) . ' - ' .str_pad($_POST["numeroFactura"], 8, "0", STR_PAD_LEFT);

			//GUARDAR LA COMPRA CON ESTADO=1 (INGRESADA DIRECTAMENTE)
			$tabla = "compras";
			$datos = array(
				"fecha" => $fec_hora,
				"usuarioPedido"=>$_POST["usuarioPedidoOculto"],
                "usuarioConfirma"=>$_POST["usuarioConfirma"] ?? $_SESSION["nombre"],
                "id_proveedor"=>$_POST["seleccionarProveedor"],
                "fechaEntrega"=>$_POST["fechaEntrega"] ?? $fecha,
                "fechaPago"=>$_POST["fechaPago"] ?? $fecha,
                "codigo"=>$codigo,
                "productos"=>$_POST["listaProductosCompras"],
                "estado"=>1, // Estado 1 = ingresada directamente
                "total"=>$_POST["nuevoTotalFactura"] ?? $_POST["totalCompra"],
                "tipo"=>$_POST["tipoFactura"] ?? "",
                "remitoNumero"=>$_POST["remitoNumero"] ?? "",
                "numeroFactura"=>$nroFactura,
                "fechaEmision"=>$_POST["fechaEmision"] ?? $fecha,
                "descuento"=>$_POST["descuentoCompraOrden"] ?? 0,
                "totalNeto"=>$_POST["nuevoTotalCompra"] ?? $_POST["totalCompra"],
                "iva"=>$_POST["totalIVA"] ?? 0,
                "precepcionesIngresosBrutos"=>$_POST["precepcionesIngresosBrutos"] ?? 0,
                "precepcionesIva"=>$_POST["precepcionesIva"] ?? 0,
                "precepcionesGanancias"=>$_POST["precepcionesGanancias"] ?? 0,
                "impuestoInterno"=>$_POST["impuestoInterno"] ?? 0,
                "observacionFactura"=>$_POST["observacionFactura"] ?? "",
                "fechaIngreso"=>$fec_hora
			);

			$respuesta = ModeloCompras::mdlIngresarCompraDirecta($tabla, $datos);

			if($respuesta == "ok"){
				// Obtener el ID de la compra recién creada
				$idCompra = ModeloCompras::mdlObtenerUltimaCompra();
				
				// Registrar en cuenta corriente del proveedor
				$tablaCtaCte = "proveedores_cuenta_corriente";
				$datos_vta = array(
					'fecha_movimiento' => $fecha,
					'id_proveedor' => $_POST["seleccionarProveedor"],
					'tipo' => 1, // Tipo 1 = compra
					'descripcion'=>"Compra Nro. Int. " . $idCompra,
					'id_compra' => $idCompra,
					'importe' => $_POST["nuevoTotalFactura"] ?? $_POST["totalCompra"],
					'metodo_pago' => null,
					'estado' => 0,
					'id_usuario' => $_SESSION["id"]
				);
				$compraInsertada = ModeloProveedoresCtaCte::mdlIngresarCtaCteProveedor($tablaCtaCte, $datos_vta);
				
				echo'<script>
				localStorage.removeItem("rango");
				swal({
					  type: "success",
					  title: "Factura cargada correctamente",
					  text: "La factura ha sido registrada y validada exitosamente",
					  showConfirmButton: true,
					  confirmButtonText: "Cerrar"
					  }).then(function(result){
						if (result.value) {
							window.location = "compras";
						}
					})
				</script>';

			} else {
				echo'<script>
				localStorage.removeItem("rango");
				swal({
					  type: "error",
					  title: "Ocurrió un error al guardar la factura",
					  showConfirmButton: true,
					  confirmButtonText: "Cerrar"
					  }).then(function(result){
						if (result.value) {
							window.location = "crear-compra";
						}
					})
				</script>';
			}
		}
	}

	/*=============================================
	EDITAR INGRESO
	=============================================*/
	static public function ctrEditarCompra(){
		if(isset($_POST["editarIngreso"])){
			/*=============================================
			FORMATEAR TABLA DE PRODUCTOS Y LA DE CLIENTES
			=============================================*/
			$tabla = "compras";
			$item = "id";
			$valor = $_POST["editarIngreso"];
			$traerCompra = ModeloCompras::mdlMostrarCompras($tabla, $item, $valor);
			/*=============================================
			REVISAR SI VIENE PRODUCTOS EDITADOS
			=============================================*/
			if($_POST["listaProductosValidarCompra"] == ""){
				$listaProductosValidarCompra = $traerCompra["productos"];
				$cambioProducto = false;
			}else{
				$listaProductosValidarCompra = $_POST["listaProductosValidarCompra"];
				$cambioProducto = true;
			}

			$productos = json_decode($listaProductosValidarCompra, true);
			$totalProductosComprados = array();
            $item = "id";
            $orden = "id";
			foreach ($productos as $key => $value) {
				$valor = $value["id"];
				$traerProducto = ModeloProductos::mdlMostrarProductos('productos', $item, $valor, $orden);
				
				// Verificar si es servicio (no actualizar stock)
				$esServicio = false;
				if(isset($value["descripcion"]) && stripos($value["descripcion"], "SERVICIO") !== false){
					$esServicio = true;
				}
				// También verificar si el producto tiene características de servicio
				if(!$esServicio && isset($traerProducto["stock"]) && floatval($traerProducto["stock"]) == 0 && 
				   isset($traerProducto["descripcion"]) && (stripos($traerProducto["descripcion"], "SERVICIO") !== false || 
				   stripos($traerProducto["descripcion"], "EDEMSA") !== false || 
				   stripos($traerProducto["descripcion"], "LUZ") !== false ||
				   stripos($traerProducto["descripcion"], "AGUA") !== false)){
					$esServicio = true;
				}
				
				// Actualizar stock SOLO si NO es servicio
				if(!$esServicio && isset($value["recibidos"]) && floatval($value["recibidos"]) > 0){
					$modificoStock = $traerProducto["stock"] + $value["recibidos"];
					$nuevoStockDestino = ModeloProductos::mdlActualizarProducto('productos', 'stock', $modificoStock, $valor, 'Ingreso stock (Cbte: '.$traerCompra["id"].')');
				}
			}
			$nroFactura = str_pad($_POST["puntoVenta"], 5, "0", STR_PAD_LEFT) . ' - ' .str_pad($_POST["numeroFactura"], 8, "0", STR_PAD_LEFT);

			/*=============================================
			GUARDAR CAMBIOS DE LA COMPRA
			=============================================*/	
			$datos = array("id"=>$_POST["editarIngreso"],
						   "usuarioPedido"=>$_POST["usuarioPedido"],
						   "usuarioConfirma"=>$_POST["usuarioConfirma"],
						   "id_proveedor"=>$_POST["editarProveedor"],
						   "productos"=>$listaProductosValidarCompra,
						   "fechaIngreso"=>date("Y-m-d H:i:s"),
						   //"sucursalDestino"=>@$_POST["editarDestino"],
						   "estado"=>1,
						   "tipo"=>$_POST["tipoFactura"],
						   "remitoNumero"=>$_POST["remitoNumero"],
						   "numeroFactura"=>$nroFactura,
						   "fechaEmision"=>$_POST["fechaEmision"],
						   "descuento" => $_POST["descuentoCompraOrden"],
						   "totalNeto"=>$_POST["nuevoTotalCompra"],
						   "iva"=>$_POST["totalIVA"],
						   "precepcionesIngresosBrutos"=>$_POST["precepcionesIngresosBrutos"],
						   "precepcionesIva"=>$_POST["precepcionesIva"],
						   "precepcionesGanancias"=>$_POST["precepcionesGanancias"],
						   "impuestoInterno"=>$_POST["impuestoInterno"],
						   "observacionFactura"=>$_POST["observacionFactura"],
						   "total"=>$_POST["nuevoTotalFactura"]);
		   	$respuesta = ModeloCompras::mdlEditarIngreso($tabla, $datos);
		   	
		   	// Verificar que no exista ya un registro en cuenta corriente para esta compra
		   	// (prevenir duplicados si se valida dos veces)
		   	$tablaCtaCte = "proveedores_cuenta_corriente";
		   	$existeRegistro = ModeloProveedoresCtaCte::mdlMostrarCtaCteProveedor($tablaCtaCte, "id_compra", $_POST["editarIngreso"]);
		   	
		   	if(!$existeRegistro || empty($existeRegistro)){
				$datos_vta = array('fecha_movimiento' => date('Y-m-d'),
								'id_proveedor' =>$_POST["editarProveedor"],
								'tipo' => 1,
								'descripcion'=>"Compra Nro. Int. " . $_POST["editarIngreso"],
								'id_compra' =>$_POST["editarIngreso"],
								'importe' => $_POST["nuevoTotalFactura"],
								'metodo_pago' => null,
								'estado' => 0,
								'id_usuario' => $_SESSION["id"]
							);
				$compraInsertada = ModeloProveedoresCtaCte::mdlIngresarCtaCteProveedor($tablaCtaCte, $datos_vta);
			}
			if($respuesta == "ok"){
				echo'<script>
				localStorage.removeItem("rango");
				swal({
					  type: "success",
					  title: "La Compra Ha Sido Cargada Exitosamente",
					  showConfirmButton: true,
					  confirmButtonText: "Cerrar"
					  }).then((result) => {
							if (result.value) {
								window.location = "compras";
							}
						})
				</script>';
			} else {
				$msjError = (isset($respuesta[2])) ? $respuesta[2] : "Error desconocido";
				echo'<script>
				localStorage.removeItem("rango");
				swal({
					  type: "error",
					  title: "'.$msjError.'",
					  showConfirmButton: true,
					  confirmButtonText: "Cerrar"
					  }).then((result) => {
							if (result.value) {
								window.location = "compras";
							}
						})
				</script>';
			}
		}
	}

	/*=============================================
	ELIMINAR VENTA
	=============================================*/
	static public function ctrEliminarCompra(){
		if(isset($_GET["idCompra"])){
			$tabla = "compras";
			$item = "id";
			$valor = $_GET["idCompra"];
			$respuesta = ModeloCompras::mdlEliminarCompra($tabla, $_GET["idCompra"]);
			if($respuesta == "ok"){
				echo'<script>
				swal({
					  type: "success",
					  title: "La compra ha sido borrada correctamente",
					  showConfirmButton: true,
					  confirmButtonText: "Cerrar"
					  }).then(function(result){
							if (result.value) {
								window.location = "compras";
							}
						})
				</script>';
			}		
		}
	}

	/*=============================================
	RANGO FECHAS
	=============================================*/	
	static public function ctrRangoFechasCompras($fechaInicial, $fechaFinal){
		$tabla = "compras";
		$respuesta = ModeloCompras::mdlRangoFechasCompras($tabla, $fechaInicial, $fechaFinal);
		return $respuesta;
	}

	/*=============================================
	RANGO FECHAS
	=============================================*/	
	static public function ctrRangoFechasComprasIngresadas($fechaInicial, $fechaFinal){
		$tabla = "compras";
		$respuesta = ModeloCompras::mdlRangoFechasComprasIngresadas($tabla, $fechaInicial, $fechaFinal);
		return $respuesta;
	}
	
	/*=============================================
	RANGO FECHAS
	=============================================*/	
	static public function ctrRangoFechasComprasValidadas($fechaInicial, $fechaFinal){
		$tabla = "compras";
		$respuesta = ModeloCompras::mdlRangoFechasComprasValidadas($tabla, $fechaInicial, $fechaFinal);
		return $respuesta;
	}
	
	/*=============================================
	SUMA TOTAL VENTAS
	=============================================*/
	public function ctrSumaTotalCompras(){
		$tabla = "compras";
		$respuesta = ModeloCompras::mdlSumaTotalCompras($tabla);
		return $respuesta;
	}
	
	/*=============================================
	MOSTRAR COMPRAS VALIDADAS
	=============================================*/
	static public function ctrMostrarProveedoresInforme($fechaDesde, $fechaHasta){
		$tabla = "compras";
		$respuesta = ModeloCompras::mdlMostrarProveedoresInforme($tabla, $fechaDesde, $fechaHasta);
		return $respuesta;
	}
}