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
				$modificoStock = $traerProducto["stock"] + $value["recibidos"];
				$nuevoStockDestino = ModeloProductos::mdlActualizarProducto('productos', 'stock', $modificoStock, $valor, 'Ingreso stock (Cbte: '.$traerCompra["id"].')');
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
			$tablaCtaCte = "proveedores_cuenta_corriente";
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