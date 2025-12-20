<?php

require_once "conexion.php";

class ModeloCombos{

	/*=============================================
	MOSTRAR COMBOS
	=============================================*/
	static public function mdlMostrarCombos($item, $valor){
		try {
			if($item != null){
				$stmt = Conexion::conectar()->prepare("SELECT c.*, p.descripcion as producto_descripcion, p.codigo as producto_codigo 
					FROM combos c 
					LEFT JOIN productos p ON c.id_producto = p.id 
					WHERE c.$item = :$item");
				$stmt -> bindParam(":".$item, $valor, PDO::PARAM_STR);
				$stmt -> execute();
				return $stmt -> fetch();
			}else{
				$stmt = Conexion::conectar()->prepare("SELECT c.*, p.descripcion as producto_descripcion, p.codigo as producto_codigo 
					FROM combos c 
					LEFT JOIN productos p ON c.id_producto = p.id 
					ORDER BY c.id DESC");
				$stmt -> execute();
				return $stmt -> fetchAll();
			}
			$stmt -> close();
			$stmt = null;
		} catch(PDOException $e){
			// Si la tabla no existe, retornar array vacío
			if(strpos($e->getMessage(), "doesn't exist") !== false || strpos($e->getMessage(), "no existe") !== false){
				error_log("Tabla combos no existe. Ejecute el script SQL: db/crear-tablas-combos.sql");
				return ($item != null) ? false : array();
			}
			// Re-lanzar otros errores
			throw $e;
		}
	}

	/*=============================================
	MOSTRAR PRODUCTOS DE UN COMBO
	=============================================*/
	static public function mdlMostrarProductosCombo($idCombo){
		try {
			$stmt = Conexion::conectar()->prepare("SELECT cp.*, p.descripcion, p.codigo, p.precio_venta, p.stock, p.tipo_iva 
				FROM combos_productos cp 
				LEFT JOIN productos p ON cp.id_producto = p.id 
				WHERE cp.id_combo = :id_combo 
				ORDER BY cp.orden ASC, cp.id ASC");
			$stmt -> bindParam(":id_combo", $idCombo, PDO::PARAM_INT);
			$stmt -> execute();
			return $stmt -> fetchAll();
			$stmt -> close();
			$stmt = null;
		} catch(PDOException $e){
			// Si la tabla no existe, retornar array vacío
			if(strpos($e->getMessage(), "doesn't exist") !== false || strpos($e->getMessage(), "no existe") !== false){
				error_log("Tabla combos_productos no existe. Ejecute el script SQL: db/crear-tablas-combos.sql");
				return array();
			}
			// Re-lanzar otros errores
			throw $e;
		}
	}

	/*=============================================
	CREAR COMBO
	=============================================*/
	static public function mdlIngresarCombo($tabla, $datos){
		$pdo = Conexion::conectar();
		
		try {
			$pdo->beginTransaction();
			
			// Insertar combo principal
			$stmt = $pdo->prepare("INSERT INTO $tabla(id_producto, codigo, nombre, descripcion, precio_venta, precio_venta_mayorista, tipo_iva, imagen, activo, tipo_descuento, descuento_global, aplicar_descuento_global, nombre_usuario) 
				VALUES (:id_producto, :codigo, :nombre, :descripcion, :precio_venta, :precio_venta_mayorista, :tipo_iva, :imagen, :activo, :tipo_descuento, :descuento_global, :aplicar_descuento_global, :nombre_usuario)");
			
			$stmt->bindParam(":id_producto", $datos["id_producto"], PDO::PARAM_INT);
			$stmt->bindParam(":codigo", $datos["codigo"], PDO::PARAM_STR);
			$stmt->bindParam(":nombre", $datos["nombre"], PDO::PARAM_STR);
			$stmt->bindParam(":descripcion", $datos["descripcion"], PDO::PARAM_STR);
			$stmt->bindParam(":precio_venta", $datos["precio_venta"], PDO::PARAM_STR);
			$stmt->bindParam(":precio_venta_mayorista", $datos["precio_venta_mayorista"], PDO::PARAM_STR);
			$stmt->bindParam(":tipo_iva", $datos["tipo_iva"], PDO::PARAM_STR);
			$stmt->bindParam(":imagen", $datos["imagen"], PDO::PARAM_STR);
			$stmt->bindParam(":activo", $datos["activo"], PDO::PARAM_INT);
			$stmt->bindParam(":tipo_descuento", $datos["tipo_descuento"], PDO::PARAM_STR);
			$stmt->bindParam(":descuento_global", $datos["descuento_global"], PDO::PARAM_STR);
			$stmt->bindParam(":aplicar_descuento_global", $datos["aplicar_descuento_global"], PDO::PARAM_STR);
			$stmt->bindParam(":nombre_usuario", $datos["nombre_usuario"], PDO::PARAM_STR);
			
			if($stmt->execute()){
				$idCombo = $pdo->lastInsertId();
				
				// Insertar productos componentes
				if(isset($datos["productos"]) && is_array($datos["productos"])){
					$stmtProductos = $pdo->prepare("INSERT INTO combos_productos(id_combo, id_producto, cantidad, precio_unitario, descuento, aplicar_descuento, orden) 
						VALUES (:id_combo, :id_producto, :cantidad, :precio_unitario, :descuento, :aplicar_descuento, :orden)");
					
					foreach($datos["productos"] as $index => $producto){
						$stmtProductos->bindParam(":id_combo", $idCombo, PDO::PARAM_INT);
						$stmtProductos->bindParam(":id_producto", $producto["id_producto"], PDO::PARAM_INT);
						$stmtProductos->bindParam(":cantidad", $producto["cantidad"], PDO::PARAM_STR);
						$precioUnitario = isset($producto["precio_unitario"]) ? $producto["precio_unitario"] : null;
						$stmtProductos->bindParam(":precio_unitario", $precioUnitario, PDO::PARAM_STR);
						$descuento = isset($producto["descuento"]) ? $producto["descuento"] : 0;
						$stmtProductos->bindParam(":descuento", $descuento, PDO::PARAM_STR);
						$aplicarDescuento = isset($producto["aplicar_descuento"]) ? $producto["aplicar_descuento"] : 'porcentaje';
						$stmtProductos->bindParam(":aplicar_descuento", $aplicarDescuento, PDO::PARAM_STR);
						$orden = isset($producto["orden"]) ? $producto["orden"] : $index;
						$stmtProductos->bindParam(":orden", $orden, PDO::PARAM_INT);
						
						if(!$stmtProductos->execute()){
							throw new Exception("Error al insertar producto componente");
						}
					}
				}
				
				// Marcar producto como combo
				$stmtUpdate = $pdo->prepare("UPDATE productos SET es_combo = 1 WHERE id = :id_producto");
				$stmtUpdate->bindParam(":id_producto", $datos["id_producto"], PDO::PARAM_INT);
				$stmtUpdate->execute();
				
				$pdo->commit();
				return "ok";
			}else{
				$pdo->rollBack();
				return "error";
			}
		} catch(Exception $e){
			$pdo->rollBack();
			error_log("Error al crear combo: " . $e->getMessage());
			return "error";
		}
		
		$stmt -> close();
		$stmt = null;
	}

	/*=============================================
	EDITAR COMBO
	=============================================*/
	static public function mdlEditarCombo($tabla, $datos){
		$pdo = Conexion::conectar();
		
		try {
			$pdo->beginTransaction();
			
			// Actualizar combo principal
			$stmt = $pdo->prepare("UPDATE $tabla SET 
				nombre = :nombre, 
				descripcion = :descripcion, 
				precio_venta = :precio_venta, 
				precio_venta_mayorista = :precio_venta_mayorista, 
				tipo_iva = :tipo_iva, 
				imagen = :imagen, 
				activo = :activo, 
				tipo_descuento = :tipo_descuento, 
				descuento_global = :descuento_global, 
				aplicar_descuento_global = :aplicar_descuento_global,
				nombre_usuario = :nombre_usuario
				WHERE id = :id");
			
			$stmt->bindParam(":nombre", $datos["nombre"], PDO::PARAM_STR);
			$stmt->bindParam(":descripcion", $datos["descripcion"], PDO::PARAM_STR);
			$stmt->bindParam(":precio_venta", $datos["precio_venta"], PDO::PARAM_STR);
			$stmt->bindParam(":precio_venta_mayorista", $datos["precio_venta_mayorista"], PDO::PARAM_STR);
			$stmt->bindParam(":tipo_iva", $datos["tipo_iva"], PDO::PARAM_STR);
			$stmt->bindParam(":imagen", $datos["imagen"], PDO::PARAM_STR);
			$stmt->bindParam(":activo", $datos["activo"], PDO::PARAM_INT);
			$stmt->bindParam(":tipo_descuento", $datos["tipo_descuento"], PDO::PARAM_STR);
			$stmt->bindParam(":descuento_global", $datos["descuento_global"], PDO::PARAM_STR);
			$stmt->bindParam(":aplicar_descuento_global", $datos["aplicar_descuento_global"], PDO::PARAM_STR);
			$stmt->bindParam(":nombre_usuario", $datos["nombre_usuario"], PDO::PARAM_STR);
			$stmt->bindParam(":id", $datos["id"], PDO::PARAM_INT);
			
			if($stmt->execute()){
				// Eliminar productos componentes existentes
				$stmtDelete = $pdo->prepare("DELETE FROM combos_productos WHERE id_combo = :id_combo");
				$stmtDelete->bindParam(":id_combo", $datos["id"], PDO::PARAM_INT);
				$stmtDelete->execute();
				
				// Insertar nuevos productos componentes
				if(isset($datos["productos"]) && is_array($datos["productos"])){
					$stmtProductos = $pdo->prepare("INSERT INTO combos_productos(id_combo, id_producto, cantidad, precio_unitario, descuento, aplicar_descuento, orden) 
						VALUES (:id_combo, :id_producto, :cantidad, :precio_unitario, :descuento, :aplicar_descuento, :orden)");
					
					foreach($datos["productos"] as $index => $producto){
						$stmtProductos->bindParam(":id_combo", $datos["id"], PDO::PARAM_INT);
						$stmtProductos->bindParam(":id_producto", $producto["id_producto"], PDO::PARAM_INT);
						$stmtProductos->bindParam(":cantidad", $producto["cantidad"], PDO::PARAM_STR);
						$precioUnitario = isset($producto["precio_unitario"]) ? $producto["precio_unitario"] : null;
						$stmtProductos->bindParam(":precio_unitario", $precioUnitario, PDO::PARAM_STR);
						$descuento = isset($producto["descuento"]) ? $producto["descuento"] : 0;
						$stmtProductos->bindParam(":descuento", $descuento, PDO::PARAM_STR);
						$aplicarDescuento = isset($producto["aplicar_descuento"]) ? $producto["aplicar_descuento"] : 'porcentaje';
						$stmtProductos->bindParam(":aplicar_descuento", $aplicarDescuento, PDO::PARAM_STR);
						$orden = isset($producto["orden"]) ? $producto["orden"] : $index;
						$stmtProductos->bindParam(":orden", $orden, PDO::PARAM_INT);
						
						if(!$stmtProductos->execute()){
							throw new Exception("Error al insertar producto componente");
						}
					}
				}
				
				$pdo->commit();
				return "ok";
			}else{
				$pdo->rollBack();
				return "error";
			}
		} catch(Exception $e){
			$pdo->rollBack();
			error_log("Error al editar combo: " . $e->getMessage());
			return "error";
		}
		
		$stmt -> close();
		$stmt = null;
	}

	/*=============================================
	BORRAR COMBO
	=============================================*/
	static public function mdlBorrarCombo($tabla, $datos){
		$pdo = Conexion::conectar();
		
		try {
			$pdo->beginTransaction();
			
			// Obtener id_producto antes de eliminar
			$stmtCombo = $pdo->prepare("SELECT id_producto FROM $tabla WHERE id = :id");
			$stmtCombo->bindParam(":id", $datos, PDO::PARAM_INT);
			$stmtCombo->execute();
			$combo = $stmtCombo->fetch();
			
			if($combo){
				// Eliminar productos componentes (CASCADE debería hacerlo automáticamente)
				$stmtDelete = $pdo->prepare("DELETE FROM combos_productos WHERE id_combo = :id_combo");
				$stmtDelete->bindParam(":id_combo", $datos, PDO::PARAM_INT);
				$stmtDelete->execute();
				
				// Eliminar combo
				$stmt = $pdo->prepare("DELETE FROM $tabla WHERE id = :id");
				$stmt->bindParam(":id", $datos, PDO::PARAM_INT);
				
				if($stmt->execute()){
					// Desmarcar producto como combo
					$stmtUpdate = $pdo->prepare("UPDATE productos SET es_combo = 0 WHERE id = :id_producto");
					$stmtUpdate->bindParam(":id_producto", $combo["id_producto"], PDO::PARAM_INT);
					$stmtUpdate->execute();
					
					$pdo->commit();
					return "ok";
				}else{
					$pdo->rollBack();
					return "error";
				}
			}else{
				$pdo->rollBack();
				return "error";
			}
		} catch(Exception $e){
			$pdo->rollBack();
			error_log("Error al borrar combo: " . $e->getMessage());
			return "error";
		}
		
		$stmt -> close();
		$stmt = null;
	}

	/*=============================================
	OBTENER PRODUCTOS COMPONENTES DE UN COMBO
	=============================================*/
	static public function mdlObtenerProductosCombo($idCombo){
		return self::mdlMostrarProductosCombo($idCombo);
	}

	/*=============================================
	VERIFICAR SI UN PRODUCTO ES COMBO
	=============================================*/
	static public function mdlEsCombo($idProducto){
		try {
			$stmt = Conexion::conectar()->prepare("SELECT c.* FROM combos c WHERE c.id_producto = :id_producto AND c.activo = 1 LIMIT 1");
			$stmt -> bindParam(":id_producto", $idProducto, PDO::PARAM_INT);
			$stmt -> execute();
			$resultado = $stmt -> fetch();
			$stmt -> close();
			$stmt = null;
			return $resultado ? $resultado : false;
		} catch(PDOException $e){
			// Si la tabla no existe, retornar false
			if(strpos($e->getMessage(), "doesn't exist") !== false || strpos($e->getMessage(), "no existe") !== false){
				return false;
			}
			// Re-lanzar otros errores
			throw $e;
		}
	}

}
