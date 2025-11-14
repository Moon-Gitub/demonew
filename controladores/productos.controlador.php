<?php

//require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

class ControladorProductos{

	/*=============================================
	MOSTRAR PRODUCTOS
	=============================================*/
	static public function ctrMostrarProductos($item, $valor, $orden){
		$tabla = "productos";
		$respuesta = ModeloProductos::mdlMostrarProductos($tabla, $item, $valor, $orden);
		return $respuesta;
	}
	
	/*=============================================
	MOSTRAR PRODUCTOS PAGINADOS
	=============================================*/
	static public function ctrMostrarProductosPaginados($desde, $limite){
		$respuesta = ModeloProductos::mdlMostrarProductosPaginados($desde, $limite);
		return $respuesta;
	}

	/*=============================================
	MOSTRAR PRODUCTO X ID
	=============================================*/
	static public function ctrMostrarProductoXId($idProducto){
		$respuesta = ModeloProductos::mdlMostrarProductoXId($idProducto);
		return $respuesta;
	}
	
	/*=============================================
	MOSTRAR PRODUCTOS POR PROVEEDOR
	=============================================*/
	static public function ctrMostrarProductosLector($valor){
		$respuesta = ModeloProductos::mdlMostrarProductosLector($valor);
		return $respuesta;
	}
	
	/*=============================================
	CREAR PRODUCTO
	=============================================*/
	static public function ctrCrearProducto(){
		if(isset($_POST["nuevaDescripcion"])){
			//VALIDAR IMAGEN
		   	$ruta = "vistas/img/productos/default/anonymous.png";

		   	//if(isset($_FILES["nuevaImagen"]["tmp_name"])){
		   	if(file_exists($_FILES['nuevaImagen']['tmp_name']) || is_uploaded_file($_FILES['nuevaImagen']['tmp_name'])) { 
				list($ancho, $alto) = getimagesize($_FILES["nuevaImagen"]["tmp_name"]);
				$nuevoAncho = 500;
				$nuevoAlto = 500;

				//CREAMOS EL DIRECTORIO DONDE VAMOS A GUARDAR LA FOTO DEL USUARIO
				$directorio = "vistas/img/productos/".$_POST["nuevoCodigo"];
				mkdir($directorio, 0755);

				//DE ACUERDO AL TIPO DE IMAGEN APLICAMOS LAS FUNCIONES POR DEFECTO DE PHP
				if($_FILES["nuevaImagen"]["type"] == "image/jpeg"){
					//GUARDAMOS LA IMAGEN EN EL DIRECTORIO
					$aleatorio = mt_rand(100,999);
					$ruta = "vistas/img/productos/".$_POST["nuevoCodigo"]."/".$aleatorio.".jpg";
					$origen = imagecreatefromjpeg($_FILES["nuevaImagen"]["tmp_name"]);						
					$destino = imagecreatetruecolor($nuevoAncho, $nuevoAlto);
					imagecopyresized($destino, $origen, 0, 0, 0, 0, $nuevoAncho, $nuevoAlto, $ancho, $alto);
					imagejpeg($destino, $ruta);
				}

				if($_FILES["nuevaImagen"]["type"] == "image/png"){
					//GUARDAMOS LA IMAGEN EN EL DIRECTORIO
					$aleatorio = mt_rand(100,999);
					$ruta = "vistas/img/productos/".$_POST["nuevoCodigo"]."/".$aleatorio.".png";
					$origen = imagecreatefrompng($_FILES["nuevaImagen"]["tmp_name"]);						
					$destino = imagecreatetruecolor($nuevoAncho, $nuevoAlto);
					imagecopyresized($destino, $origen, 0, 0, 0, 0, $nuevoAncho, $nuevoAlto, $ancho, $alto);
					imagepng($destino, $ruta);
				}

			}

			$tabla = "productos";
			$_POST["nuevoCodigo"] = str_replace(array("\r\n", "\r", "\n", "\t"), " ", $_POST["nuevoCodigo"]);
			$_POST["nuevaDescripcion"] = str_replace(array("\r\n", "\r", "\n", "\t"), " ", $_POST["nuevaDescripcion"]);
			$_POST["nuevaDescripcion"] = str_replace(array('"'), "''", $_POST["nuevaDescripcion"]);

			$datos = array(
				"id_categoria" => $_POST["nuevaCategoria"],
				"codigo" => $_POST["nuevoCodigo"],
				"id_proveedor" => $_POST["nuevoProveedor"],
			    "descripcion" => $_POST["nuevaDescripcion"],
				"stock" => $_POST["nuevoStock"],
				"stock_medio" => $_POST["nuevoStockMedio"],
				"stock_bajo" => $_POST["nuevoStockBajo"],
				"precio_compra" => $_POST["nuevoPrecioCompraNeto"],
				"precio_compra_dolar" => $_POST["nuevoPrecioCompraNetoDolar"],
				"margen_ganancia" => $_POST["nuevoPorcentajeText"],
				"tipo_iva" => $_POST["nuevoIvaVenta"],
				"precio_venta" => $_POST["nuevoPrecioVentaIvaIncluido"],
				"imagen" => $ruta);
			$respuesta = ModeloProductos::mdlIngresarProducto($tabla, $datos);
            
            if($respuesta == "ok"){
				echo'<script>
					localStorage.setItem("msjProductoCorrecto", true);
				</script>';
			} else {
				$respError = (isset($respuesta[2])) ? $respuesta[2] : "Error desconocido";
					echo '<script>
					swal({
						  type: "error",
						  title: "Productos",
						  text: "'.$respError.'",
						  showConfirmButton: true,
						  confirmButtonText: "Cerrar"
						  }).then(function(result){
							if (result.value) {
								window.location = "productos";
							}
						})
					</script>';
			}
		}
	}
    
    
	/*=============================================
	EDITAR PRODUCTO
	=============================================*/
	static public function ctrEditarProducto(){
		if(isset($_POST["editarDescripcion"])){
			// if(preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ ]+$/', $_POST["editarDescripcion"]) &&
			//    preg_match('/^[0-9]+$/', $_POST["editarStock"]) &&	
			//    preg_match('/^[0-9.]+$/', $_POST["editarPrecioCompra"]) &&
			//    preg_match('/^[0-9.]+$/', $_POST["editarPrecioVenta"])){

			//VALIDAR IMAGEN
		   	$ruta = $_POST["imagenActual"];
		   	if(isset($_FILES["editarImagen"]["tmp_name"]) && !empty($_FILES["editarImagen"]["tmp_name"])){
				list($ancho, $alto) = getimagesize($_FILES["editarImagen"]["tmp_name"]);
				$nuevoAncho = 500;
				$nuevoAlto = 500;

				/*=============================================
				CREAMOS EL DIRECTORIO DONDE VAMOS A GUARDAR LA FOTO DEL USUARIO
				=============================================*/
				$directorio = "vistas/img/productos/".$_POST["editarCodigo"];

				/*=============================================
				PRIMERO PREGUNTAMOS SI EXISTE OTRA IMAGEN EN LA BD
				=============================================*/
				if(!empty($_POST["imagenActual"]) && $_POST["imagenActual"] != "vistas/img/productos/default/anonymous.png"){
					unlink($_POST["imagenActual"]);
				}else{
					mkdir($directorio, 0755);	
				}
				
				/*=============================================
				DE ACUERDO AL TIPO DE IMAGEN APLICAMOS LAS FUNCIONES POR DEFECTO DE PHP
				=============================================*/
				if($_FILES["editarImagen"]["type"] == "image/jpeg"){
					/*=============================================
					GUARDAMOS LA IMAGEN EN EL DIRECTORIO
					=============================================*/
					$aleatorio = mt_rand(100,999);
					$ruta = "vistas/img/productos/".$_POST["editarCodigo"]."/".$aleatorio.".jpg";
					$origen = imagecreatefromjpeg($_FILES["editarImagen"]["tmp_name"]);						
					$destino = imagecreatetruecolor($nuevoAncho, $nuevoAlto);
					imagecopyresized($destino, $origen, 0, 0, 0, 0, $nuevoAncho, $nuevoAlto, $ancho, $alto);
					imagejpeg($destino, $ruta);
				}

				if($_FILES["editarImagen"]["type"] == "image/png"){
					/*=============================================
					GUARDAMOS LA IMAGEN EN EL DIRECTORIO
					=============================================*/
					$aleatorio = mt_rand(100,999);
					$ruta = "vistas/img/productos/".$_POST["editarCodigo"]."/".$aleatorio.".png";
					$origen = imagecreatefrompng($_FILES["editarImagen"]["tmp_name"]);						
					$destino = imagecreatetruecolor($nuevoAncho, $nuevoAlto);
					imagecopyresized($destino, $origen, 0, 0, 0, 0, $nuevoAncho, $nuevoAlto, $ancho, $alto);
					imagepng($destino, $ruta);
				}
			}

			$tabla = "productos";
			$_POST["editarCodigo"] = str_replace(array("\r\n", "\r", "\n", "\t"), " ", $_POST["editarCodigo"]);
			$_POST["editarDescripcion"] = str_replace(array("\r\n", "\r", "\n", "\t"), " ", $_POST["editarDescripcion"]);
			$_POST["editarDescripcion"] = str_replace(array('"'), "''", $_POST["editarDescripcion"]);
			
			//si son productos VARIOS no actualizo todos los valores, solo algunos (tipo iva, descripcion)
			$_POST["editarCategoria"] = ($_POST["editarId"] > 9) ? $_POST["editarCategoria"] : 1;
			$_POST["editarProveedor"] = ($_POST["editarId"] > 9) ? $_POST["editarProveedor"] : 1;
			$_POST["editarStock"] = ($_POST["editarId"] > 9) ? $_POST["editarStock"] : 0;
			$_POST["editarStockMedio"] = ($_POST["editarId"] > 9) ? $_POST["editarStockMedio"] : 0;
			$_POST["editarStockBajo"] = ($_POST["editarId"] > 9) ? $_POST["editarStockBajo"] : 0;
			$_POST["editarPrecioCompraNeto"] = ($_POST["editarId"] > 9) ? $_POST["editarPrecioCompraNeto"] : 0;
			$_POST["editarPrecioCompraNetoDolar"] = ($_POST["editarId"] > 9) ? $_POST["editarPrecioCompraNetoDolar"] : 0;
			$_POST["editarPorcentajeText"] = ($_POST["editarId"] > 9) ? $_POST["editarPorcentajeText"] : 0;
			$_POST["editarPrecioVenta"] = ($_POST["editarId"] > 9) ? $_POST["editarPrecioVenta"] : 0;
			$_POST["editarPrecioVentaIvaIncluido"] = ($_POST["editarId"] > 9) ? $_POST["editarPrecioVentaIvaIncluido"] : 0;

			$datos = array(
			            "id" => $_POST["editarId"],
						"id_categoria" => $_POST["editarCategoria"],
						"codigo" => $_POST["editarCodigo"],
						"id_proveedor" => $_POST["editarProveedor"],
						"descripcion" => $_POST["editarDescripcion"],
						"stock" => $_POST["editarStock"],
						"stock_medio" => $_POST["editarStockMedio"],
						"stock_bajo" => $_POST["editarStockBajo"],
						"precio_compra" => $_POST["editarPrecioCompraNeto"],
						"precio_compra_dolar" => $_POST["editarPrecioCompraNetoDolar"],
						"margen_ganancia" => $_POST["editarPorcentajeText"],
						"tipo_iva" => $_POST["editarIvaVenta"],
						"precio_venta" => $_POST["editarPrecioVentaIvaIncluido"],
						"imagen" => $ruta);
			$respuesta = ModeloProductos::mdlEditarProducto($tabla, $datos);
            
			if($respuesta == "ok"){
				echo'<script>
					localStorage.setItem("msjProductoCorrecto", true);
					window.location = "productos";
				</script>';
			} else {
				$respError = (isset($respuesta[2])) ? $respuesta[2] : "Error desconocido";
				echo '<script>
				swal({
					  type: "error",
					  title: "Productos",
					  text: "'.$respError.'",
					  showConfirmButton: true,
					  confirmButtonText: "Cerrar"
					  }).then(function(result){
						if (result.value) {
							window.location = "productos";
						}
					})
				</script>';
			}
		}
	}

	/*=============================================
	BORRAR PRODUCTO
	=============================================*/
    static public function ctrEliminarProducto(){
		if(isset($_GET["idProducto"])){
			$tabla ="productos";
			$datos = $_GET["idProducto"];
			
			if($_GET["imagen"] != "" && $_GET["imagen"] != "vistas/img/productos/default/anonymous.png"){
				unlink($_GET["imagen"]);
				rmdir('vistas/img/productos/'.$_GET["codigo"]);
			}
			
			$respuesta = ModeloProductos::mdlEliminarProducto($tabla, $datos);
			
			if($respuesta == "ok"){
				echo'<script> 
					swal({ 
						type: "success", 
						title: "Productos", 
						text: "El producto ha sido borrado correctamente", 
						showConfirmButton: true, 
						confirmButtonText: "Cerrar" 
					}).then(function(result){ 
						if (result.value) { 
							window.location = "productos"; 
						} 
					}) 
				</script>';
			}
		}

	}

	/*=============================================
	LISTAR PRODUCTOS CON STOCK MEDIO
	=============================================*/
	static public function ctrMostrarStockMedio(){
		$respuesta = ModeloProductos::mdlMostrarStockMedio();
		return $respuesta;
	}	

	/*=============================================
	LISTAR PRODUCTOS CON STOCK BAJO
	=============================================*/
	static public function ctrMostrarStockBajo(){
		$respuesta = ModeloProductos::mdlMostrarStockBajo();
		return $respuesta;
	}	

	/*=============================================
	LISTAR PRODUCTOS STOCK VALORIZADO
	=============================================*/
	static public function ctrMostrarStockValorizado(){
		$respuesta = ModeloProductos::mdlMostrarStockValorizado();
		return $respuesta;
	}

	/*=============================================
	LISTAR PRODUCTOS STOCK VALORIZADO TOTALES
	=============================================*/
	static public function ctrMostrarStockValorizadoTotales(){
		$respuesta = ModeloProductos::mdlMostrarStockValorizadoTotales();
		return $respuesta;
	}	

	/*=============================================
	AGERGAR PRODCUTOS DESDE VENTA CAJA
	=============================================*/
	static public function ctrAgregarProductoVentaCaja($datosProducto){
		$respuesta = ModeloProductos::mdlAgregarProductoVentaCaja($datosProducto);
		return $respuesta;
	}	

	/*=============================================
	ACTUALIZO EL PRECIO DE VENTA CUANDO ES 0 (CODIGOS DEL 1 AL 10 DESCARTADOS)
	=============================================*/
	static public function ctrActualizarPrecioVenta($datosProducto){
		$respuesta = ModeloProductos::mdlActualizarPrecioVenta($datosProducto);
		return $respuesta;
	}	

	/*=============================================
	MOFIDICAR PRECIOS PRODUCTOS POR CATEGORIA
	=============================================*/
	static public function ctrModificarPrecioCategoria() {
		if(isset($_POST["nuevoModificacionPrecio"])){
			$tabla = 'productos';
			$id_categoria = $_POST["idCategoriaNuevoPrecio"];
			$porcentaje = $_POST["nuevoModificacionPrecio"];
			$respuesta = ModeloProductos::mdlModificarPrecioCategoria($tabla, $id_categoria, $porcentaje);
			if($respuesta == "ok"){
				echo'<script>

					swal({
						  type: "success",
						  title: "Productos",
						  text: "Los productos de la categoría seleccionada, han sido modificados",
						  showConfirmButton: true,
						  confirmButtonText: "Cerrar"
						  }).then(function(result){
								if (result.value) {
									window.location = "categorias";
									}
								})
					</script>';
			}
		}
	}

	/*=============================================
	MOFIDICAR PRECIOS PRODUCTOS POR PROVEEDOR
	=============================================*/
	static public function ctrModificarPrecioProveedor() {
		if(isset($_POST["nuevoModificacionPrecio"])){
			$tabla = 'productos';
			$id_proveedor = $_POST["idProveedorNuevoPrecio"];
			$porcentaje = $_POST["nuevoModificacionPrecio"];
			$respuesta = ModeloProductos::mdlModificarPrecioProveedor($tabla, $id_proveedor, $porcentaje);
			if($respuesta == "ok"){
				echo'<script>
					swal({
						  type: "success",
						  title: "Productos",
						  text: "Los productos del proveedor seleccionada, han sido modificados",
						  showConfirmButton: true,
						  confirmButtonText: "Cerrar"
						  }).then(function(result){
								if (result.value) {
									window.location = "productos";
								}
							})
					</script>';
			}
		}
	}	

	/*=============================================
	LISTAR PRODUCTOS FILTRADOS (AUTOCOMPLETE PRODUCTOS)
	=============================================*/
	static public function ctrMostrarProductosFiltrados($filtro){
		$tabla = "productos";
		$respuesta = ModeloProductos::mdlMostrarProductosFiltrados($tabla, $filtro);
		return $respuesta;
	}

	/*=============================================
	IMPORTAR ARCHIVO EXCEL (VERSION 1)
	=============================================*/
	static public function ctrImportarExcel() {
		
		if(isset($_POST["seleccionarProveedor"])) {
			
			$nombreProveedor = ModeloProveedores::mdlMostrarProveedores("proveedores", "id", $_POST["seleccionarProveedor"]);
		 	$origen = $_FILES["nuevaExcel"]["tmp_name"];
			$directorio = "vistas/dist/xlsx/";
			date_default_timezone_set('America/Argentina/Mendoza');
			$fecha = date('Y-m-d');
			$hora = date('H-i-s');
			$destino = $directorio.$fecha."_".$hora."_".$nombreProveedor["nombre"].".xlsx";
			if (!copy($origen, $destino)) {
				echo'<script>
					swal({
						  type: "error",
						  title: "Productos",
						  text: "Error al subir el archivo",
						  showConfirmButton: true,
						  confirmButtonText: "Cerrar"
						  })
					</script>';
				return false;

			} else {
				

				// Cargo la hoja de cálculo
				$objPHPExcel = IOFactory::load($destino);

				//Asigno la primer hoja de calculo (si quisiera la activa debería usar getSheet() )
				$hoja = $objPHPExcel->getSheet(0);

				//$valor = $hoja->getCell('B2')->getValue(); // Columna B, fila 2
				//$valor = $hoja->getCellByColumnAndRow(2, 2)->getValue(); // 2 = Columna B, fila 2
				//$ultimaFila = $hoja->getHighestRow(); // Última fila con datos
				//$ultimaColumnaLetra = $hoja->getHighestColumn(); // Última columna como letra (ej. "C")

				$insertarProducto = 0;
				$actualizarProducto = 0;

				file_put_contents($directorio."detalle_datos_importados", "<p>Proveedor: ".$nombreProveedor["nombre"]."</p>");
				file_put_contents($directorio."detalle_datos_importados", "<p>Archivo original: ".$_FILES['nuevaExcel']['name']."</p>", FILE_APPEND);
				file_put_contents($directorio."detalle_datos_importados", "<p>Archivo renombrado: ".$fecha."_".$hora."_".$nombreProveedor["nombre"].".xlsx</p>", FILE_APPEND);

				$tablaTxt = "";

				//Recorremos las filas del archivo excel. 
				foreach ($hoja->getRowIterator() as $fila) {

					$tablaTxt .= "<tr>";

					$numeroFila = $fila->getRowIndex();

    				if ($numeroFila === 1) continue; // Saltar encabezado
					
					//$codigoBarra = $hoja->getCellByColumnAndRow(1, $numeroFila)->getValue();	
					$codigoBarra = $hoja->getCell('A'.$numeroFila)->getValue();
					$descripcion = mb_convert_encoding($hoja->getCell('B'.$numeroFila)->getValue(), 'UTF-8');

					$precioCompra = $hoja->getCell('C'.$numeroFila)->getValue();
					$precioCompra = (isset($precioCompra)) ?  $precioCompra : 0;

					$iva = $hoja->getCell('D'.$numeroFila)->getValue();
					//$iva = (isset($iva)) ?  $iva : 21;

					$precioVentaPublico = $hoja->getCell('E'.$numeroFila)->getValue();
					$precioVentaPublico = (isset($precioVentaPublico)) ?  $precioVentaPublico : 0;

					//$stock = $objPHPExcel->getActiveSheet()->getCell('F'.$fila)->getCalculatedValue();
					
					if($codigoBarra != "") {

						$tablaTxt .= "
						<td>".$codigoBarra."</td>
						<td>".$descripcion."</td>
						<td>".$precioCompra."</td>
						<td>".$iva."</td>
						<td>".$precioVentaPublico."</td>

						</tr>";

						$datosConsulta = array (
							"id_proveedor" => $_POST["seleccionarProveedor"],
							"codigo" => $codigoBarra);

						$producto = ModeloProductos::mdlMostrarProductosExcel("productos", $datosConsulta);

						$codigoBarra = str_replace(array("\r\n", "\r", "\n", "\t"), " ", $codigoBarra);
						$descripcion = str_replace(array("\r\n", "\r", "\n", "\t"), " ", $descripcion);
						$descripcion = str_replace(array('"'), "''", $descripcion);

						if(!empty($producto)) {

							$datos = array(
									"codigo" => $codigoBarra,
									"descripcion" => $descripcion,
									"precio_compra" => $precioCompra, 
									"tipo_iva" => $iva,
									"precio_venta" => $precioVentaPublico
									);

							$resp = ModeloProductos::mdlEditarProductoExcel("productos", $datos);

							if($resp =="ok" ) {

								$actualizarProducto++;	

							} else {

								$mensaje = ((isset($resp[2])) ? $resp[2] : "Sin detalle");

								echo'<script>

								swal({
									  type: "error",
									  title: "Productos",
									  text: "Error al actualizar producto. Detalle: '.$mensaje.'",
									  showConfirmButton: true,
									  confirmButtonText: "Cerrar"
									  })
								</script>';

								return false;

							}

						} else {

							$datos = array(
									"id_proveedor" => $_POST["seleccionarProveedor"],
									"codigo" => $codigoBarra,
									"id_categoria" => 1,
									"descripcion" => $descripcion,
									"precio_compra" => $precioCompra, 
									"precio_compra_dolar" => 0, 
									"tipo_iva" => $iva,
									"precio_venta" => $precioVentaPublico
									);

							$resp = ModeloProductos::mdlIngresarProductoExcel("productos", $datos);	

							if($resp =="ok" ) {

								$insertarProducto++;	

							} else {

								$mensaje = ((isset($resp[2])) ? $resp[2] : "Sin detalle");

								echo'<script>

								swal({
									  type: "error",
									  title: "Productos",
									  text: "Error al ingresar producto. Detalle: '.$mensaje.'",
									  showConfirmButton: true,
									  confirmButtonText: "Cerrar"
									  })
								</script>';

								return false;

							}

						}

					}
			
				}

				file_put_contents($directorio."detalle_datos_importados", "<p>Cantidad de productos insertados: ".$insertarProducto."</p>", FILE_APPEND);
				file_put_contents($directorio."detalle_datos_importados", "<p>Cantidad de productos actualizados: ".$actualizarProducto."<p>", FILE_APPEND);
				file_put_contents($directorio."detalle_datos_importados", "<p>Total de filas importadas: ".strval($insertarProducto + $actualizarProducto)."</p>", FILE_APPEND);
				file_put_contents($directorio."tabla_datos_importados", $tablaTxt);

				echo'<script>
					swal({
						  type: "success",
						  title: "Productos",
						  text: "El archivo se ha importado correctamente. Productos ingresados: '.$insertarProducto.' - Productos actualizados: '.$actualizarProducto.'",
						  showConfirmButton: true,
						  confirmButtonText: "Cerrar"
						  })
					</script>';

			}

		}

	}

	/*=============================================
	LISTAR HISTORIAL CAMBIOS POR PRODUCTOS
	=============================================*/
	static public function ctrMostrarProductosHistorial($idProducto){
		$respuesta = ModeloProductos::mdlMostrarProductosHistorial($idProducto);
		return $respuesta;
	}

	/*=============================================
	PRODUCTOS MAS VENDIDOS
	=============================================*/
	static public function ctrMostrarProductosMasVendidos($fechaInicial, $fechaFinal){

		$respuesta = ModeloProductos::mdlMostrarProductosMasVendidos($fechaInicial, $fechaFinal);

		$productosVendidos = array();
		foreach ($respuesta as $key => $value) {

			$separoProd = explode(",", $value["productosA"]);
			$separoCant = explode(",", $value["cantidadesA"]);
			$separoDesc = explode(",", $value["descripcionA"]);

			$indiceArray = 0;
			for ($i = 0; $i < count($separoProd); $i++){

				if(array_key_exists($separoProd[$i], $productosVendidos)){

					$valorNuevo = $productosVendidos[$separoProd[$i]]["cantidad"] + $separoCant[$i];
					$productosVendidos[$separoProd[$i]]["cantidad"] = $valorNuevo;

				} else {

					$productosVendidos[$separoProd[$i]] = array('cantidad' => floatval($separoCant[$i]), 'descripcion' => $separoDesc[$i]);

				}

			}

		}

		arsort($productosVendidos); //ordeno array(clave=>valor) por VALOR Descendente
		return array_slice($productosVendidos, 0, 10, true); //Devulevo array con los primeros 10 elementos del array

	}
	
 	/*=============================================
	REGISTRO DE AJUSTE DE STOCK DE PRODUCTO
	=============================================*/
	static public function ctrIngresarAjusteStockProducto(){

		if(isset($_POST["editarIdAjusteStock"])){

			//REGISTRO EL CAMBIO GENERADO
			$datos = array(
				"id" => $_POST["editarIdAjusteStock"],
				"stock_anterior" => $_POST["editarStockAnterior"],
				"stock_actual" => $_POST["editarStockAjuste"],
				"almacen" => $_POST["editarAjusteStockAlmacen"]
			);

			$respuesta = ModeloProductos::mdlIngresarAjusteStockProducto($datos);
			
			if($respuesta == "ok"){

				echo'<script>

					localStorage.setItem("msjProductoCorrecto", true);

				</script>';

			}

		}

	}
    
   	/*=============================================
	LISTAR CAMPOS DE LA TABLA PRODUCTOS
	=============================================*/
	static public function ctrEstructuraTablaProductos(){
		$respuesta = ModeloProductos::mdlEstructuraTablaProductos($idProducto);
		return $respuesta;
	}

	/*=============================================
	IMPORTAR ARCHIVO EXCEL (Version 2 - Paso1)
	=============================================*/
	static public function ctrObtenerColumnasExcel() {
		if(isset($_FILES["nuevaExcel"])) {
		 	$origen = $_FILES["nuevaExcel"]["tmp_name"];
			$directorio = "vistas/dist/xlsx/";
			date_default_timezone_set('America/Argentina/Mendoza');
			$fecha = date('Y-m-d');
			$hora = date('H-i-s');
	        $nombreArchivo = time();
			$destino = $directorio.$fecha."_".$hora."_".$nombreArchivo.".xlsx";
			if (!copy($origen, $destino)) {
				echo'<script>
					swal({
						  type: "error",
						  title: "Productos",
						  text: "Error al subir el archivo",
						  showConfirmButton: true,
						  confirmButtonText: "Cerrar"
						  })
					</script>';
				return false;
			} else {
	            $columnas= array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');

				// Cargo la hoja de cálculo
				$objPHPExcel = IOFactory::load($destino);

				//Asigno la primer hoja de calculo (si quisiera la activa debería usar getSheet() )
				$hoja = $objPHPExcel->getSheet(0);

				//Obtengo el numero de filas del archivo
				$numRows = $hoja->getHighestRow();
				//$numColumns = $objPHPExcel->getActiveSheet()->getHighestDataColumn();

				//Obtengo el numero de columnas del archivo (devuelve valor en letras)
				$numColumns = $hoja->getHighestColumn();
				
				$cabeceras = array(0 => 'Ninguna');
				$columnNum = array_search($numColumns, $columnas);
				$cabeceraExcel = "";
				$rangoExcel = '';
				$index=0;

				for ($i=0; $i <= $columnNum; $i++){
					$rangoExcel = $columnas[$i] . '1';
					$cabeceraExcel = $hoja->getCell($rangoExcel)->getValue();
					$cabeceras += [$i+1 => $cabeceraExcel];
				}

				$columnNum++;
	            echo'<script>
					swal({
						  type: "success",
						  title: "Productos",
						  text: "Excel subido! - Filas: '.$numRows.' - Columnas: '.$columnNum.'",
						  showConfirmButton: true,
						  confirmButtonText: "Cerrar"
						  })
					</script>';

				return array($cabeceras, $destino); 
			}
		}
	}
 
	/*=============================================
	IMPORTAR PRODUCTOS DESDE ARCHIVO EXCEL (VERSION 2 - 2Paso)
	=============================================*/
	static public function ctrImportarProductosExcel() {

		if(isset($_POST["ubicacionArchivoExcel"])) {
		    
		 	$directorio = "vistas/dist/xlsx/";
			$destino = $_POST['ubicacionArchivoExcel']; //$directorio.$_POST['ubicacionArchivoExcel'];

			if (!file_exists($destino)) {
				
				echo'<script>

					swal({
						  type: "error",
						  title: "Productos",
						  text: "Error al reabrir el archivo",
						  showConfirmButton: true,
						  confirmButtonText: "Cerrar"
						  })
					</script>';

				return false;

			} else {

				$columnas= array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');

				// Cargo la hoja de cálculo
				$objPHPExcel = IOFactory::load($destino);

				//Asigno la hoja de calculo activa
				$hoja = $objPHPExcel->getSheet(0);

				//Obtengo el numero de filas del archivo
				$numRows = $hoja->getHighestRow();

				//Obtengo el numero de columnas del archivo (devuelve valor en letras)
				$numColumns = $hoja->getHighestColumn();

				//Numero de columnas
				$columnNum = array_search($numColumns, $columnas);

				$insertarProducto = 0;
				$actualizarProducto = 0;
				$creoCategorias = 0;
				$creoProveedores = 0;
				$tablaTxt = "";
				$estaCodigo = 0;
				$colCodigo = 0;
				
				//ARMO ARRAY DATOS QUE PASA AL MODELO
				$datosModelo = array();
				for($indice = 0; $indice <= $columnNum; $indice++){

				    $campoBD = 'campoBaseDatos'.$indice;
					$campoXLSX = 'campoExcel'.$indice;
					
					if( $_POST[$campoBD] != '0' && $_POST[$campoXLSX] != '0' ) {
                        $valorXLSX = $_POST[$campoXLSX] - 1; //resto uno para que coincida con la columna de excel
                        $datosModelo += [$_POST[$campoBD] => $valorXLSX];
                        
                        if($_POST[$campoBD] == "codigo"){ //Es obligatorio que uno de los campos seleccionado sea codigo
					        $estaCodigo++;
					    }
					}

				}
			    
				if($estaCodigo <> 1){
			    	echo'<script>
    					swal({
    						  type: "error",
    						  title: "Productos",
    						  text: "Error! el campo \"Codigo\" es obiglatorio",
    						  showConfirmButton: true,
    						  confirmButtonText: "Cerrar"
    						  })
    					</script>';

					return false;
				}

				//Recorremos las filas del archivo excel. Restamos 1 porque empezamos de la fila 2 (sino restamos carga filas vacias)
				for ($fila = 2; $fila <= $numRows; $fila++) {

				    $actualizoProducto = false;
                    $updateQuery = "";
                    $insertQueryK = "";
                    $insertQueryV = "";

					//RECORRO COLUMNAS
					foreach ($datosModelo as $key => $value) {

					    $letraExcel = $columnas[$value];
					    $valorCelda = $hoja->getCell($letraExcel.$fila)->getValue();
    					$valorCelda = str_replace(array("\r\n", "\r", "\n", "\t"), "", $valorCelda);
    					$valorCelda = str_replace(array('"'), "''", $valorCelda);
    					$valorCelda = trim($valorCelda);
					    if($key == "codigo"){ //si la columna que estoy recorriendo es la del codigo

					        $producto = ModeloProductos::mdlMostrarProductos('productos', 'codigo', $valorCelda, null);
					        if($producto){
					            $actualizoProducto = true;
					        }
					        
					    } elseif($key == "id_categoria"){ //si la columna que estoy recorriendo es la de categorias
					    
					        if($valorCelda == ""){
					            $valorCelda = 1;
					        } elseif(!is_numeric($valorCelda)){ //si NO es un id de categoria
					            $categoria = ModeloCategorias::mdlMostrarCategorias('categorias', 'categoria', $valorCelda);
					            if($categoria){ //si la categoria existe guardo el id
					                $valorCelda = $categoria["id"];
					            } else { //si la categoria no existe la creo
					                $creoCat = ModeloCategorias::mdlIngresarCategoriaExcel($valorCelda);
					                $creoCategorias++;
					                $valorCelda = (is_numeric($creoCat)) ? $creoCat : 1; //Si hay error al crear la categoria se agrega como categoria GENERAL
					            }
					        }
					    } elseif($key == "id_proveedor"){ //si la columna que estoy recorriendo es la de proveedores
					    
					        if($valorCelda == ""){
					            $valorCelda = 1;
					        } elseif(!is_numeric($valorCelda)){ //si NO es un id de proveedor
					            $proveedor = ModeloProveedores::mdlMostrarProveedores('proveedores', 'nombre', $valorCelda);
					            if($proveedor){ //si el proveedor existe guardo el id
					                $valorCelda = $proveedor["id"];
					            } else { //si el proveedor no existe lo creo
					                $creoProv = ModeloProveedores::mdlIngresarProveedorExcel($valorCelda);
					                $creoProveedores++;
					                $valorCelda = (is_numeric($creoProv)) ? $creoProv : 1; //Si hay error al crear la proveedor se agrega como proveedor VARIOS
					            }
					        }
					    }
					    
					    $updateQuery .= $key . '="' . $valorCelda . '",';
					    $insertQueryK .= $key . ',';
					    $insertQueryV .= '"' . $valorCelda . '",';
					    
					}
					
					if($actualizoProducto){
					    $updateQuery .= 'nombre_usuario="'.$_SESSION["nombre"].'",cambio_desde="Importar Excel v2"';
					     
					    $sqlQuery = "UPDATE productos SET " . $updateQuery . " WHERE id=" . $producto["id"] . ";";
                        $actualizarProducto++;
                        
					} else {
                        $insertQueryK .= 'nombre_usuario,cambio_desde';//substr($insertQueryK, 0, -1);
                        $insertQueryV .= '"'.$_SESSION["nombre"].'","Importar Excel v2"'; //substr($insertQueryV, 0, -1);
                        $sqlQuery = "INSERT INTO productos (" . $insertQueryK . ") VALUES (" . $insertQueryV . ")" . ";";
                        $insertarProducto++;

					}
					
					$resp = ModeloProductos::mdlEjecutarQueryProductos($sqlQuery);
					
					if($resp !="ok" ) {

						$mensaje = ((isset($resp[2])) ? $resp[2] : "Sin detalle");

						echo'<script>

						swal({
							  type: "error",
							  title: "Productos",
							  text: "Error al actualizar/insertar producto. Detalle: '.$mensaje.'",
							  showConfirmButton: true,
							  confirmButtonText: "Cerrar"
							  })
						</script>';

						return false;

					}
			
				}

                //Borro archivo de excel subido
                //fclose($destino);
                unlink($destino);
                
				echo'<script>
					swal({
						  type: "success",
						  title: "Productos",
						  text: "El archivo se ha importado correctamente. Productos ingresados: '.$insertarProducto.' - Productos actualizados: '.$actualizarProducto.' - Nuevas Categorias: '.$creoCategorias.' - Nuevos Proveedores: '.$creoProveedores.'",
						  showConfirmButton: true,
						  confirmButtonText: "Cerrar"
						  })
					</script>';

			}

		}

	}

	/*=============================================
	MOSTRAR ULTIMA FECHA ACTUALIZACION
	=============================================*/
	static public function ctrFechaActualizacion(){
		$respuesta = ModeloProductos::mdlFechaActualizacion();
		return $respuesta;
	}
	
	/*=============================================
	BORRAR PRODUCTOS MASIVO
	=============================================*/
	static public function ctrBorrarProductosMasivo($idProducto){    
        $tabla = "productos";
		$respuesta = ModeloProductos::mdlBorrarProductosMasivo($tabla, $idProducto);
		return $respuesta;
	}
}