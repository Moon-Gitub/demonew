<?php

require_once "conexion.php";

class ModeloCompras{

	//MOSTRAR COMPRAS
	static public function mdlMostrarCompras($tabla, $item, $valor){
		if($item != null){ 
			$stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla WHERE $item = :$item ORDER BY id ASC");
			$stmt -> bindParam(":".$item, $valor, PDO::PARAM_STR);
			$stmt -> execute();
			return $stmt -> fetch();

		}else{
			$stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla ORDER BY id ASC");
			$stmt -> execute();
			return $stmt -> fetchAll();
		}
		
		$stmt -> close();
		$stmt = null;

	}

	//REGISTRO DE COMPRA
	static public function mdlCargarNota($tabla, $idCompra, $productos){

		$stmt = Conexion::conectar()->prepare("INSERT INTO $tabla(id_compra, productos) VALUES (:id_compra, :productos)");
	
		$stmt->bindParam(":id_compra", $idCompra, PDO::PARAM_INT);
		$stmt->bindParam(":productos", $productos, PDO::PARAM_STR);
		
		if($stmt->execute()){

			return "ok";

		}else{

			return $stmt->errorInfo();
		
		}

		$stmt->close();
		$stmt = null;

	}
	
	/*=============================================
	REGISTRO DE COMPRA NOTA DEBITO
	=============================================*/

	static public function mdlCargarNotaDebito($tabla, $idCompra, $productos){

		$stmt = Conexion::conectar()->prepare("INSERT INTO $tabla(id_compra, productos) VALUES (:id_compra, :productos)");
	
		$stmt->bindParam(":id_compra", $idCompra, PDO::PARAM_INT);
		$stmt->bindParam(":productos", $productos, PDO::PARAM_STR);
		
		if($stmt->execute()){

			return "ok";

		}else{

			return "error";
		
		}

		$stmt->close();
		$stmt = null;

	}
	
	/*=============================================
	REGISTRO DE COMPRA
	=============================================*/
	static public function mdlIngresarCompra($tabla, $datos){

		$stmt = Conexion::conectar()->prepare("INSERT INTO $tabla(codigo, id_proveedor, usuarioPedido, usuarioConfirma, sucursalDestino, fechaEntrega, fechaPago, productos, estado, fecha, total) VALUES (:codigo, :id_proveedor, :usuarioPedido, :usuarioConfirma, :sucursalDestino, :fechaEntrega, :fechaPago, :productos, :estado, :fecha, :total)");

		$stmt->bindParam(":fecha", $datos["fecha"], PDO::PARAM_STR);
		$stmt->bindParam(":usuarioPedido", $datos["usuarioPedido"], PDO::PARAM_STR);
		$stmt->bindParam(":usuarioConfirma", $datos["usuarioConfirma"], PDO::PARAM_STR);
		$stmt->bindParam(":sucursalDestino", $datos["sucursalDestino"], PDO::PARAM_STR);
		$stmt->bindParam(":codigo", $datos["codigo"], PDO::PARAM_INT);
		$stmt->bindParam(":id_proveedor", $datos["id_proveedor"], PDO::PARAM_INT);
		$stmt->bindParam(":fechaEntrega", $datos["fechaEntrega"], PDO::PARAM_STR);
		$stmt->bindParam(":fechaPago", $datos["fechaPago"], PDO::PARAM_STR);
		$stmt->bindParam(":productos", $datos["productos"], PDO::PARAM_STR);
		$stmt->bindParam(":total", $datos["total"], PDO::PARAM_STR);
		$stmt->bindParam(":estado", $datos["estado"], PDO::PARAM_STR);
		
		if($stmt->execute()){

			return "ok";

		}else{

			return $stmt -> errorInfo();
		
		}

		$stmt->close();
		$stmt = null;

	}

	/*=============================================
	INGRESAR COMPRA DIRECTA (FACTURA SIN ORDEN)
	=============================================*/
	static public function mdlIngresarCompraDirecta($tabla, $datos){
		$stmt = Conexion::conectar()->prepare("INSERT INTO $tabla(codigo, id_proveedor, usuarioPedido, usuarioConfirma, sucursalDestino, fechaEntrega, fechaPago, productos, estado, fecha, total, tipo, remitoNumero, numeroFactura, fechaEmision, descuento, totalNeto, iva, precepcionesIngresosBrutos, precepcionesIva, precepcionesGanancias, impuestoInterno, observacionFactura, fechaIngreso) VALUES (:codigo, :id_proveedor, :usuarioPedido, :usuarioConfirma, :sucursalDestino, :fechaEntrega, :fechaPago, :productos, :estado, :fecha, :total, :tipo, :remitoNumero, :numeroFactura, :fechaEmision, :descuento, :totalNeto, :iva, :precepcionesIngresosBrutos, :precepcionesIva, :precepcionesGanancias, :impuestoInterno, :observacionFactura, :fechaIngreso)");

		$stmt->bindParam(":fecha", $datos["fecha"], PDO::PARAM_STR);
		$stmt->bindParam(":usuarioPedido", $datos["usuarioPedido"], PDO::PARAM_STR);
		$stmt->bindParam(":usuarioConfirma", $datos["usuarioConfirma"], PDO::PARAM_STR);
		$stmt->bindParam(":sucursalDestino", $datos["sucursalDestino"], PDO::PARAM_STR);
		$stmt->bindParam(":codigo", $datos["codigo"], PDO::PARAM_INT);
		$stmt->bindParam(":id_proveedor", $datos["id_proveedor"], PDO::PARAM_INT);
		$stmt->bindParam(":fechaEntrega", $datos["fechaEntrega"], PDO::PARAM_STR);
		$stmt->bindParam(":fechaPago", $datos["fechaPago"], PDO::PARAM_STR);
		$stmt->bindParam(":productos", $datos["productos"], PDO::PARAM_STR);
		$stmt->bindParam(":total", $datos["total"], PDO::PARAM_STR);
		$stmt->bindParam(":estado", $datos["estado"], PDO::PARAM_STR);
		$stmt->bindParam(":tipo", $datos["tipo"], PDO::PARAM_STR);
		$stmt->bindParam(":remitoNumero", $datos["remitoNumero"], PDO::PARAM_STR);
		$stmt->bindParam(":numeroFactura", $datos["numeroFactura"], PDO::PARAM_STR);
		$stmt->bindParam(":fechaEmision", $datos["fechaEmision"], PDO::PARAM_STR);
		$stmt->bindParam(":descuento", $datos["descuento"], PDO::PARAM_STR);
		$stmt->bindParam(":totalNeto", $datos["totalNeto"], PDO::PARAM_STR);
		$stmt->bindParam(":iva", $datos["iva"], PDO::PARAM_STR);
		$stmt->bindParam(":precepcionesIngresosBrutos", $datos["precepcionesIngresosBrutos"], PDO::PARAM_STR);
		$stmt->bindParam(":precepcionesIva", $datos["precepcionesIva"], PDO::PARAM_STR);
		$stmt->bindParam(":precepcionesGanancias", $datos["precepcionesGanancias"], PDO::PARAM_STR);
		$stmt->bindParam(":impuestoInterno", $datos["impuestoInterno"], PDO::PARAM_STR);
		$stmt->bindParam(":observacionFactura", $datos["observacionFactura"], PDO::PARAM_STR);
		$stmt->bindParam(":fechaIngreso", $datos["fechaIngreso"], PDO::PARAM_STR);

		if($stmt->execute()){
			return "ok";
		}else{
			return $stmt -> errorInfo();
		}

		$stmt->close();
		$stmt = null;
	}

	/*=============================================
	OBTENER ÚLTIMA COMPRA CREADA
	=============================================*/
	static public function mdlObtenerUltimaCompra(){
		$stmt = Conexion::conectar()->prepare("SELECT id FROM compras ORDER BY id DESC LIMIT 1");
		$stmt -> execute();
		$resultado = $stmt -> fetch();
		$stmt -> close();
		$stmt = null;
		return $resultado ? $resultado["id"] : null;
	}

	/*=============================================
	EDITAR INGRESO
	=============================================*/

	static public function mdlEditarIngreso($tabla, $datos){

		$stmt = Conexion::conectar()->prepare("UPDATE $tabla SET usuarioPedido = :usuarioPedido, id_proveedor= :id_proveedor, usuarioConfirma = :usuarioConfirma, remitoNumero = :remitoNumero, numeroFactura = :numeroFactura, fechaEmision = :fechaEmision, observacionFactura = :observacionFactura, estado = :estado, descuento = :descuento, totalNeto= :totalNeto, tipo = :tipo, iva= :iva, precepcionesIngresosBrutos= :precepcionesIngresosBrutos,  precepcionesIva= :precepcionesIva, precepcionesGanancias= :precepcionesGanancias, impuestoInterno= :impuestoInterno, fechaIngreso = :fechaIngreso, productos = :productos, total= :total WHERE id = :id");

		$stmt->bindParam(":usuarioPedido", $datos["usuarioPedido"], PDO::PARAM_STR);
		$stmt->bindParam(":id_proveedor", $datos["id_proveedor"], PDO::PARAM_STR);
		$stmt->bindParam(":usuarioConfirma", $datos["usuarioConfirma"], PDO::PARAM_STR);
		$stmt->bindParam(":remitoNumero", $datos["remitoNumero"], PDO::PARAM_STR);
		$stmt->bindParam(":numeroFactura", $datos["numeroFactura"], PDO::PARAM_STR);
		$stmt->bindParam(":fechaEmision", $datos["fechaEmision"], PDO::PARAM_STR);
		$stmt->bindParam(":observacionFactura", $datos["observacionFactura"], PDO::PARAM_STR);
		$stmt->bindParam(":estado", $datos["estado"], PDO::PARAM_STR);
		$stmt->bindParam(":totalNeto", $datos["totalNeto"], PDO::PARAM_STR);
		$stmt->bindParam(":descuento", $datos["descuento"], PDO::PARAM_STR);
		$stmt->bindParam(":tipo", $datos["tipo"], PDO::PARAM_STR);
		$stmt->bindParam(":iva", $datos["iva"], PDO::PARAM_STR);
		$stmt->bindParam(":precepcionesIngresosBrutos", $datos["precepcionesIngresosBrutos"], PDO::PARAM_STR);
		$stmt->bindParam(":precepcionesIva", $datos["precepcionesIva"], PDO::PARAM_STR);
		$stmt->bindParam(":precepcionesGanancias", $datos["precepcionesGanancias"], PDO::PARAM_STR);
		$stmt->bindParam(":impuestoInterno", $datos["impuestoInterno"], PDO::PARAM_STR);
		$stmt->bindParam(":fechaIngreso", $datos["fechaIngreso"], PDO::PARAM_STR);
		$stmt->bindParam(":productos", $datos["productos"], PDO::PARAM_STR);
		$stmt->bindParam(":total", $datos["total"], PDO::PARAM_STR);
		$stmt->bindParam(":id", $datos["id"], PDO::PARAM_STR);

		if($stmt->execute()){

			return "ok";

		}else{

			return $stmt->errorInfo();
		
		}

		$stmt->close();
		$stmt = null;

	}
	
	/*=============================================
	ELIMINAR COMPRA
	=============================================*/

	static public function mdlEliminarCompra($tabla, $datos){
		$idCompra = (int)$datos;
		if($idCompra <= 0){
			return "error";
		}

		$conexion = Conexion::conectar();
		$usuario = isset($_SESSION["nombre"]) ? (string)$_SESSION["nombre"] : (isset($_SESSION["usuario"]) ? (string)$_SESSION["usuario"] : "(sin especificar)");

		try {
			$conexion->beginTransaction();

			// Traer compra para validar reversión de stock.
			$stmtCompra = $conexion->prepare("SELECT id, estado, sucursalDestino, productos FROM $tabla WHERE id = :id");
			$stmtCompra->bindParam(":id", $idCompra, PDO::PARAM_INT);
			$stmtCompra->execute();
			$compra = $stmtCompra->fetch();

			if(!$compra){
				$conexion->rollBack();
				return "error";
			}

			$estado = (int)($compra["estado"] ?? 0);
			$sucursalDestinoOriginal = isset($compra["sucursalDestino"]) ? trim((string)$compra["sucursalDestino"]) : "";
			$productosJson = $compra["productos"] ?? "[]";
			$listaProductos = json_decode($productosJson, true);
			if(!is_array($listaProductos)){
				$listaProductos = [];
			}

			// Mapa sucursalDestino -> columna real de stock (compatibilidad entre esquemas).
			$mapearColumnaStock = function(string $sucursal, array $cols) : ?string {
				$sucursal = trim($sucursal);
				if($sucursal === '') return null;
				if($sucursal === 'stock1') $sucursal = 'stock';
				if(in_array($sucursal, $cols, true)) return $sucursal;

				if($sucursal === 'stock2' && in_array('deposito', $cols, true)) return 'deposito';
				if($sucursal === 'deposito' && in_array('stock2', $cols, true)) return 'stock2';

				if($sucursal === 'stock3'){
					if(in_array('deposito2', $cols, true)) return 'deposito2';
					if(in_array('ameghino', $cols, true)) return 'ameghino';
					if(in_array('stock3', $cols, true)) return 'stock3';
				}

				if($sucursal === 'deposito2' && in_array('stock3', $cols, true)) return 'stock3';
				if($sucursal === 'ameghino' && in_array('stock3', $cols, true)) return 'stock3';

				return null;
			};

			// Detección simple de servicio (para no impactar stock).
			$esServicioLinea = function(array $linea) : bool {
				$idProd = isset($linea["id"]) ? (int)$linea["id"] : 0;
				if($idProd <= 0) return true;
				$descripcion = isset($linea["descripcion"]) ? (string)$linea["descripcion"] : "";
				if($descripcion === '') return false;

				$keywords = ["SERVICIO", "EDEMSA", "LUZ", "AGUA", "GAS", "INTERNET", "TELEFONIA"];
				foreach($keywords as $k){
					if(stripos($descripcion, $k) !== false){
						return true;
					}
				}
				return false;
			};

			// 1) Restar stock (solo si ya impactó: estado 1 y 2).
			if($estado === 1 || $estado === 2){
				$cols = $conexion->query("SHOW COLUMNS FROM productos")->fetchAll(PDO::FETCH_COLUMN);

				// Mantener consistencia con el comportamiento actual del sistema.
				$sucursalDestino = $sucursalDestinoOriginal !== '' ? $sucursalDestinoOriginal : 'stock';
				$colDestino = $mapearColumnaStock($sucursalDestino, $cols);
				if(empty($colDestino)){
					throw new Exception("No se pudo mapear sucursalDestino='$sucursalDestino' a una columna válida.");
				}

				// Consolidar cantidades a revertir por producto.
				$cantPorProducto = [];
				foreach($listaProductos as $linea){
					if(!is_array($linea)) continue;
					if($esServicioLinea($linea)) continue;

					$idProd = isset($linea["id"]) ? (int)$linea["id"] : 0;
					if($idProd <= 0) continue;

					$qty = 0.0;
					if(isset($linea["recibidos"]) && floatval($linea["recibidos"]) > 0){
						$qty = floatval($linea["recibidos"]);
					}else if(isset($linea["pedidos"]) && floatval($linea["pedidos"]) > 0){
						$qty = floatval($linea["pedidos"]);
					}else if(isset($linea["cantidad"]) && floatval($linea["cantidad"]) > 0){
						$qty = floatval($linea["cantidad"]);
					}
					if($qty <= 0) continue;

					if(!isset($cantPorProducto[$idProd])){
						$cantPorProducto[$idProd] = 0.0;
					}
					$cantPorProducto[$idProd] += $qty;
				}

				if(!empty($cantPorProducto)){
					$ids = array_map('intval', array_keys($cantPorProducto));
					$placeholders = implode(',', array_fill(0, count($ids), '?'));

					$stmtStock = $conexion->prepare("SELECT id, $colDestino AS stock_actual FROM productos WHERE id IN ($placeholders)");
					$stmtStock->execute($ids);

					$stockMap = [];
					while($row = $stmtStock->fetch()){
						$stockMap[(int)$row["id"]] = floatval($row["stock_actual"]);
					}

					// Validar stock no negativo.
					foreach($cantPorProducto as $idProd => $qty){
						if(!array_key_exists($idProd, $stockMap)){
							throw new Exception("Producto $idProd no encontrado en productos para validar stock.");
						}
						$nuevoStock = $stockMap[$idProd] - $qty;
						if($nuevoStock < 0){
							throw new Exception("No se puede eliminar: stock negativo en producto=$idProd, columna=$colDestino.");
						}
					}

					// Aplicar reversión.
					$stmtUpdate = $conexion->prepare("UPDATE productos SET $colDestino = :nuevo_stock, nombre_usuario = :nombre_usuario, cambio_desde = :cambio_desde WHERE id = :id");
					$cambioDesde = "Borrado compra (ID: $idCompra)";
					foreach($cantPorProducto as $idProd => $qty){
						$nuevoStock = $stockMap[$idProd] - $qty;
						$stmtUpdate->execute([
							":nuevo_stock" => $nuevoStock,
							":nombre_usuario" => $usuario,
							":cambio_desde" => $cambioDesde,
							":id" => $idProd
						]);
					}
				}
			}

			// 2) Eliminar cta cte vinculada a la compra (si existe).
			$stmtCta = $conexion->prepare("DELETE FROM proveedores_cuenta_corriente WHERE id_compra = :id_compra");
			$stmtCta->execute([":id_compra" => $idCompra]);

			// 3) Eliminar la compra.
			$stmtDel = $conexion->prepare("DELETE FROM $tabla WHERE id = :id");
			$stmtDel->execute([":id" => $idCompra]);

			$conexion->commit();

			error_log("Compra eliminada correctamente. id_compra=$idCompra usuario=$usuario fecha=" . date('Y-m-d H:i:s'));
			return "ok";
		} catch(Exception $e){
			try {
				if($conexion->inTransaction()){
					$conexion->rollBack();
				}
			} catch(Exception $e2){
				// Ignorar rollback fallido.
			}
			error_log("Error al eliminar compra id_compra=$idCompra usuario=$usuario: " . $e->getMessage());
			return "error";
		}

	}

	/*=============================================
	RANGO FECHAS
	=============================================*/	
	static public function mdlRangoFechasCompras($tabla, $fechaInicial, $fechaFinal){

		if($fechaInicial == null){
			$stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla WHERE estado = 0 ORDER BY id DESC");
			$stmt -> execute();
			return $stmt -> fetchAll();	

		}else if($fechaInicial == $fechaFinal){
			// Filtrar solo compras pendientes (estado = 0) para validar ingreso
			$stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla WHERE estado = 0 AND fecha like '%$fechaFinal%' ORDER BY codigo DESC");
			$stmt -> bindParam(":fecha", $fechaFinal, PDO::PARAM_STR);
			$stmt -> execute();
			return $stmt -> fetchAll();

		}else{

			$fechaActual = new DateTime();
			$fechaActual ->add(new DateInterval("P1D"));
			$fechaActualMasUno = $fechaActual->format("Y-m-d");

			$fechaFinal2 = new DateTime($fechaFinal);
			$fechaFinal2 ->add(new DateInterval("P1D"));
			$fechaFinalMasUno = $fechaFinal2->format("Y-m-d");

			if($fechaFinalMasUno == $fechaActualMasUno){
				// Filtrar solo compras pendientes (estado = 0) para validar ingreso
				$stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla WHERE estado = 0 AND fecha BETWEEN '$fechaInicial' AND '$fechaFinalMasUno' ORDER BY id DESC");

			}else{
				// Filtrar solo compras pendientes (estado = 0) para validar ingreso
				$stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla WHERE estado = 0 AND fecha BETWEEN '$fechaInicial' AND '$fechaFinal' ORDER BY id DESC");

			}
		
			$stmt -> execute();

			return $stmt -> fetchAll();

		}

	}

	/*=============================================
	RANGO FECHAS
	=============================================*/	
	static public function mdlRangoFechasComprasIngresadas($tabla, $fechaInicial, $fechaFinal){

		if($fechaInicial == null){
			$stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla WHERE estado = 1 ORDER BY id DESC");
			$stmt -> execute();
			return $stmt -> fetchAll();	

		} else if($fechaInicial == $fechaFinal){

			$stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla WHERE estado = 1 AND fecha like '%$fechaFinal%' ORDER BY codigo DESC");

			$stmt -> bindParam(":fecha", $fechaFinal, PDO::PARAM_STR);

			$stmt -> execute();

			return $stmt -> fetchAll();

		}else{

			$fechaActual = new DateTime();
			$fechaActual ->add(new DateInterval("P1D"));
			$fechaActualMasUno = $fechaActual->format("Y-m-d");

			$fechaFinal2 = new DateTime($fechaFinal);
			$fechaFinal2 ->add(new DateInterval("P1D"));
			$fechaFinalMasUno = $fechaFinal2->format("Y-m-d");

			if($fechaFinalMasUno == $fechaActualMasUno){

				$stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla WHERE estado = 1 AND fecha BETWEEN '$fechaInicial' AND '$fechaFinalMasUno'");

			}else{

				$stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla WHERE estado = 1 AND fecha BETWEEN '$fechaInicial' AND '$fechaFinal'");

			}
		
			$stmt -> execute();

			return $stmt -> fetchAll();

		}

	}

	/*=============================================
	RANGO FECHAS
	=============================================*/	

	static public function mdlRangoFechasComprasValidadas($tabla, $fechaInicial, $fechaFinal){

		if($fechaInicial == null){
			$stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla WHERE estado = 2 ORDER BY id DESC");
			$stmt -> execute();
			return $stmt -> fetchAll();	

		}else if($fechaInicial == $fechaFinal){

			$stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla WHERE estado = 2 AND fecha like '%$fechaFinal%' ORDER BY codigo DESC");
			$stmt -> bindParam(":fecha", $fechaFinal, PDO::PARAM_STR);
			$stmt -> execute();
			return $stmt -> fetchAll();

		}else{

			$fechaActual = new DateTime();
			$fechaActual ->add(new DateInterval("P1D"));
			$fechaActualMasUno = $fechaActual->format("Y-m-d");

			$fechaFinal2 = new DateTime($fechaFinal);
			$fechaFinal2 ->add(new DateInterval("P1D"));
			$fechaFinalMasUno = $fechaFinal2->format("Y-m-d");

			if($fechaFinalMasUno == $fechaActualMasUno){

				$stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla WHERE estado = 2 AND fecha BETWEEN '$fechaInicial' AND '$fechaFinalMasUno'");

			}else{

				$stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla WHERE estado = 2 AND fecha BETWEEN '$fechaInicial' AND '$fechaFinal'");

			}
		
			$stmt -> execute();
			return $stmt -> fetchAll();
		}
	}
	
	/*=============================================
	SUMAR EL TOTAL DE COMPRAS
	=============================================*/
	public function mdlSumaTotalCompras($tabla){	
		$stmt = Conexion::conectar()->prepare("SELECT SUM(neto) as total FROM $tabla");
		$stmt -> execute();
		return $stmt -> fetch();
		$stmt -> close();
		$stmt = null;

	}

	/*=============================================
	RANGO FECHAS
	=============================================*/	
	static public function mdlMostrarProveedoresInforme($tabla, $fechaInicial, $fechaFinal){
		if($fechaInicial == null){
			$stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla ORDER BY id DESC");
			$stmt -> execute();
			return $stmt -> fetchAll();	

		}else{
			$stmt = Conexion::conectar()->prepare("SELECT id_proveedor, sum(total) as total, count(id) as compras FROM $tabla WHERE fecha>='$fechaInicial' AND fecha<='$fechaFinal' group by id_proveedor order by total DESC" );
	
		}
		$stmt -> execute();
		return $stmt -> fetchAll();

	}

	/*=============================================
	ULTIMO ID / CODIGO COMPRAS
	=============================================*/
	static public function mdlUltimoIdCodigoCompras($item){	
		$stmt = Conexion::conectar()->prepare("SELECT MAX($item) as ultimo FROM compras");
		$stmt -> execute();
		return $stmt -> fetch();
		$stmt -> close();
		$stmt = null;
	}

}