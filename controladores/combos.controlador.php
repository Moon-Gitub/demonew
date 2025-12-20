<?php

// Los modelos se cargan en index.php, solo verificamos que existan
if(!class_exists('ModeloCombos')){
	// Intentar cargar si no está cargado
	if(file_exists(__DIR__ . "/../modelos/combos.modelo.php")){
		require_once __DIR__ . "/../modelos/combos.modelo.php";
	}
}

class ControladorCombos{

	/*=============================================
	MOSTRAR COMBOS
	=============================================*/
	static public function ctrMostrarCombos($item, $valor){
		$tabla = "combos";
		$respuesta = ModeloCombos::mdlMostrarCombos($item, $valor);
		return $respuesta;
	}

	/*=============================================
	MOSTRAR PRODUCTOS DE UN COMBO
	=============================================*/
	static public function ctrMostrarProductosCombo($idCombo){
		$respuesta = ModeloCombos::mdlMostrarProductosCombo($idCombo);
		return $respuesta;
	}

	/*=============================================
	CREAR COMBO
	=============================================*/
	static public function ctrCrearCombo(){
		if(isset($_POST["nuevoCodigoCombo"])){
			// Validar que se haya seleccionado un producto
			if(empty($_POST["nuevoProductoCombo"])){
				echo'<script>
					swal({
						type: "error",
						title: "Error",
						text: "Debe seleccionar un producto para el combo",
						showConfirmButton: true,
						confirmButtonText: "Cerrar"
					});
				</script>';
				return;
			}

			// Validar que tenga productos componentes
			if(empty($_POST["productosCombo"]) || !is_array(json_decode($_POST["productosCombo"], true))){
				echo'<script>
					swal({
						type: "error",
						title: "Error",
						text: "Debe agregar al menos un producto componente al combo",
						showConfirmButton: true,
						confirmButtonText: "Cerrar"
					});
				</script>';
				return;
			}

			$tabla = "combos";
			
			// Procesar imagen si existe
			$ruta = "vistas/img/combos/default/anonymous.png";
			if(file_exists($_FILES['nuevaImagenCombo']['tmp_name']) || is_uploaded_file($_FILES['nuevaImagenCombo']['tmp_name'])) { 
				list($ancho, $alto) = getimagesize($_FILES["nuevaImagenCombo"]["tmp_name"]);
				$nuevoAncho = 500;
				$nuevoAlto = 500;

				$directorio = "vistas/img/combos/".$_POST["nuevoCodigoCombo"];
				if(!is_dir($directorio)){
					mkdir($directorio, 0755, true);
				}

				if($_FILES["nuevaImagenCombo"]["type"] == "image/jpeg"){
					$aleatorio = mt_rand(100,999);
					$ruta = "vistas/img/combos/".$_POST["nuevoCodigoCombo"]."/".$aleatorio.".jpg";
					$origen = imagecreatefromjpeg($_FILES["nuevaImagenCombo"]["tmp_name"]);						
					$destino = imagecreatetruecolor($nuevoAncho, $nuevoAlto);
					imagecopyresized($destino, $origen, 0, 0, 0, 0, $nuevoAncho, $nuevoAlto, $ancho, $alto);
					imagejpeg($destino, $ruta);
				}

				if($_FILES["nuevaImagenCombo"]["type"] == "image/png"){
					$aleatorio = mt_rand(100,999);
					$ruta = "vistas/img/combos/".$_POST["nuevoCodigoCombo"]."/".$aleatorio.".png";
					$origen = imagecreatefrompng($_FILES["nuevaImagenCombo"]["tmp_name"]);						
					$destino = imagecreatetruecolor($nuevoAncho, $nuevoAlto);
					imagecopyresized($destino, $origen, 0, 0, 0, 0, $nuevoAncho, $nuevoAlto, $ancho, $alto);
					imagepng($destino, $ruta);
				}
			}

			// Procesar productos componentes
			$productosCombo = json_decode($_POST["productosCombo"], true);
			
			$productos = array();
			foreach($productosCombo as $index => $prod){
				$productos[] = array(
					"id_producto" => $prod["id"],
					"cantidad" => $prod["cantidad"],
					"precio_unitario" => isset($prod["precio_unitario"]) ? $prod["precio_unitario"] : null,
					"descuento" => isset($prod["descuento"]) ? $prod["descuento"] : 0,
					"aplicar_descuento" => isset($prod["aplicar_descuento"]) ? $prod["aplicar_descuento"] : 'porcentaje',
					"orden" => $index
				);
			}

			$datos = array(
				"id_producto" => $_POST["nuevoProductoCombo"],
				"codigo" => $_POST["nuevoCodigoCombo"],
				"nombre" => $_POST["nuevoNombreCombo"],
				"descripcion" => isset($_POST["nuevaDescripcionCombo"]) ? $_POST["nuevaDescripcionCombo"] : '',
				"precio_venta" => isset($_POST["nuevoPrecioVentaCombo"]) ? $_POST["nuevoPrecioVentaCombo"] : 0,
				"precio_venta_mayorista" => isset($_POST["nuevoPrecioMayoristaCombo"]) ? $_POST["nuevoPrecioMayoristaCombo"] : null,
				"tipo_iva" => isset($_POST["nuevoIvaCombo"]) ? $_POST["nuevoIvaCombo"] : 21.00,
				"imagen" => $ruta,
				"activo" => isset($_POST["nuevoActivoCombo"]) ? 1 : 1,
				"tipo_descuento" => isset($_POST["nuevoTipoDescuentoCombo"]) ? $_POST["nuevoTipoDescuentoCombo"] : 'ninguno',
				"descuento_global" => isset($_POST["nuevoDescuentoGlobalCombo"]) ? $_POST["nuevoDescuentoGlobalCombo"] : 0,
				"aplicar_descuento_global" => isset($_POST["nuevoAplicarDescuentoGlobalCombo"]) ? $_POST["nuevoAplicarDescuentoGlobalCombo"] : 'porcentaje',
				"nombre_usuario" => $_SESSION['nombre'],
				"productos" => $productos
			);

			$respuesta = ModeloCombos::mdlIngresarCombo($tabla, $datos);

			if($respuesta == "ok"){
				echo'<script>
					swal({
						type: "success",
						title: "Combos",
						text: "El combo ha sido guardado correctamente",
						showConfirmButton: true,
						confirmButtonText: "Cerrar"
					}).then(function(result){
						if (result.value) {
							window.location = "combos";
						}
					});
				</script>';
			}else{
				echo'<script>
					swal({
						type: "error",
						title: "Error",
						text: "Error al guardar el combo",
						showConfirmButton: true,
						confirmButtonText: "Cerrar"
					});
				</script>';
			}
		}
	}

	/*=============================================
	EDITAR COMBO
	=============================================*/
	static public function ctrEditarCombo(){
		if(isset($_POST["editarCodigoCombo"])){
			// Validar que se haya seleccionado un producto
			if(empty($_POST["editarProductoCombo"])){
				echo'<script>
					swal({
						type: "error",
						title: "Error",
						text: "Debe seleccionar un producto para el combo",
						showConfirmButton: true,
						confirmButtonText: "Cerrar"
					});
				</script>';
				return;
			}

			// Validar que tenga productos componentes
			if(empty($_POST["productosComboEditar"]) || !is_array(json_decode($_POST["productosComboEditar"], true))){
				echo'<script>
					swal({
						type: "error",
						title: "Error",
						text: "Debe agregar al menos un producto componente al combo",
						showConfirmButton: true,
						confirmButtonText: "Cerrar"
					});
				</script>';
				return;
			}

			$tabla = "combos";
			
			// Obtener combo actual para mantener imagen si no se cambia
			$comboActual = ModeloCombos::mdlMostrarCombos("id", $_POST["idCombo"]);
			$ruta = $comboActual ? $comboActual["imagen"] : "vistas/img/combos/default/anonymous.png";
			
			// Procesar imagen si existe
			if(file_exists($_FILES['editarImagenCombo']['tmp_name']) || is_uploaded_file($_FILES['editarImagenCombo']['tmp_name'])) { 
				list($ancho, $alto) = getimagesize($_FILES["editarImagenCombo"]["tmp_name"]);
				$nuevoAncho = 500;
				$nuevoAlto = 500;

				$directorio = "vistas/img/combos/".$_POST["editarCodigoCombo"];
				if(!is_dir($directorio)){
					mkdir($directorio, 0755, true);
				}

				if($_FILES["editarImagenCombo"]["type"] == "image/jpeg"){
					$aleatorio = mt_rand(100,999);
					$ruta = "vistas/img/combos/".$_POST["editarCodigoCombo"]."/".$aleatorio.".jpg";
					$origen = imagecreatefromjpeg($_FILES["editarImagenCombo"]["tmp_name"]);						
					$destino = imagecreatetruecolor($nuevoAncho, $nuevoAlto);
					imagecopyresized($destino, $origen, 0, 0, 0, 0, $nuevoAncho, $nuevoAlto, $ancho, $alto);
					imagejpeg($destino, $ruta);
				}

				if($_FILES["editarImagenCombo"]["type"] == "image/png"){
					$aleatorio = mt_rand(100,999);
					$ruta = "vistas/img/combos/".$_POST["editarCodigoCombo"]."/".$aleatorio.".png";
					$origen = imagecreatefrompng($_FILES["editarImagenCombo"]["tmp_name"]);						
					$destino = imagecreatetruecolor($nuevoAncho, $nuevoAlto);
					imagecopyresized($destino, $origen, 0, 0, 0, 0, $nuevoAncho, $nuevoAlto, $ancho, $alto);
					imagepng($destino, $ruta);
				}
			}

			// Procesar productos componentes
			$productosCombo = json_decode($_POST["productosComboEditar"], true);
			
			$productos = array();
			foreach($productosCombo as $index => $prod){
				$productos[] = array(
					"id_producto" => $prod["id"],
					"cantidad" => $prod["cantidad"],
					"precio_unitario" => isset($prod["precio_unitario"]) ? $prod["precio_unitario"] : null,
					"descuento" => isset($prod["descuento"]) ? $prod["descuento"] : 0,
					"aplicar_descuento" => isset($prod["aplicar_descuento"]) ? $prod["aplicar_descuento"] : 'porcentaje',
					"orden" => $index
				);
			}

			$datos = array(
				"id" => $_POST["idCombo"],
				"nombre" => $_POST["editarNombreCombo"],
				"descripcion" => isset($_POST["editarDescripcionCombo"]) ? $_POST["editarDescripcionCombo"] : '',
				"precio_venta" => isset($_POST["editarPrecioVentaCombo"]) ? $_POST["editarPrecioVentaCombo"] : 0,
				"precio_venta_mayorista" => isset($_POST["editarPrecioMayoristaCombo"]) ? $_POST["editarPrecioMayoristaCombo"] : null,
				"tipo_iva" => isset($_POST["editarIvaCombo"]) ? $_POST["editarIvaCombo"] : 21.00,
				"imagen" => $ruta,
				"activo" => isset($_POST["editarActivoCombo"]) ? 1 : 1,
				"tipo_descuento" => isset($_POST["editarTipoDescuentoCombo"]) ? $_POST["editarTipoDescuentoCombo"] : 'ninguno',
				"descuento_global" => isset($_POST["editarDescuentoGlobalCombo"]) ? $_POST["editarDescuentoGlobalCombo"] : 0,
				"aplicar_descuento_global" => isset($_POST["editarAplicarDescuentoGlobalCombo"]) ? $_POST["editarAplicarDescuentoGlobalCombo"] : 'porcentaje',
				"nombre_usuario" => $_SESSION['nombre'],
				"productos" => $productos
			);

			$respuesta = ModeloCombos::mdlEditarCombo($tabla, $datos);

			if($respuesta == "ok"){
				echo'<script>
					swal({
						type: "success",
						title: "Combos",
						text: "El combo ha sido actualizado correctamente",
						showConfirmButton: true,
						confirmButtonText: "Cerrar"
					}).then(function(result){
						if (result.value) {
							window.location = "combos";
						}
					});
				</script>';
			}else{
				echo'<script>
					swal({
						type: "error",
						title: "Error",
						text: "Error al actualizar el combo",
						showConfirmButton: true,
						confirmButtonText: "Cerrar"
					});
				</script>';
			}
		}
	}

	/*=============================================
	BORRAR COMBO
	=============================================*/
	static public function ctrBorrarCombo(){
		if(isset($_GET["idCombo"])){
			$tabla = "combos";
			$datos = $_GET["idCombo"];

			$respuesta = ModeloCombos::mdlBorrarCombo($tabla, $datos);

			if($respuesta == "ok"){
				echo'<script>
					swal({
						type: "success",
						title: "Combos",
						text: "El combo ha sido borrado correctamente",
						showConfirmButton: true,
						confirmButtonText: "Cerrar"
					}).then(function(result){
						if (result.value) {
							window.location = "combos";
						}
					});
				</script>';
			}else{
				echo'<script>
					swal({
						type: "error",
						title: "Error",
						text: "Error al borrar el combo",
						showConfirmButton: true,
						confirmButtonText: "Cerrar"
					});
				</script>';
			}
		}
	}

	/*=============================================
	VERIFICAR SI UN PRODUCTO ES COMBO
	=============================================*/
	static public function ctrEsCombo($idProducto){
		return ModeloCombos::mdlEsCombo($idProducto);
	}

	/*=============================================
	OBTENER PRODUCTOS COMPONENTES DE UN COMBO
	=============================================*/
	static public function ctrObtenerProductosCombo($idCombo){
		return ModeloCombos::mdlObtenerProductosCombo($idCombo);
	}

}
