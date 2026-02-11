<?php

require_once "conexion.php";

class ModeloVentas{

	/*=============================================
	MOSTRAR VENTAS
	=============================================*/
	static public function mdlMostrarVentas($tabla, $item, $valor){
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

	/*=============================================
	REGISTRO DE VENTA
	=============================================*/
	static public function mdlIngresarVenta($tabla, $datos){

		// Guardar productos JSON vacío (compatibilidad con estructura, pero no se usa)
		$productosJson = '[]';

		$conexion = Conexion::conectar();
		$stmt = $conexion->prepare("INSERT IGNORE INTO $tabla(uuid, id_empresa, fecha, codigo, cbte_tipo, id_cliente, id_vendedor, productos, impuesto, impuesto_detalle, neto, neto_gravado, base_imponible_0, base_imponible_2, base_imponible_5, base_imponible_10, base_imponible_21, base_imponible_27, iva_2, iva_5, iva_10, iva_21, iva_27, total, metodo_pago, pto_vta, concepto, fec_desde, fec_hasta, fec_vencimiento, asociado_tipo_cbte, asociado_pto_vta, asociado_nro_cbte, estado, observaciones_vta, pedido_afip, respuesta_afip) VALUES (:uuid, :id_empresa, :fecha, :codigo, :cbte_tipo, :id_cliente, :id_vendedor, :productos, :impuesto, :impuesto_detalle, :neto, :neto_gravado, :base_imponible_0, :base_imponible_2, :base_imponible_5, :base_imponible_10, :base_imponible_21, :base_imponible_27, :iva_2, :iva_5, :iva_10, :iva_21, :iva_27, :total, :metodo_pago, :pto_vta, :concepto, :fec_desde, :fec_hasta, :fec_vencimiento, :asociado_tipo_cbte, :asociado_pto_vta, :asociado_nro_cbte, :estado, :observaciones_vta, :pedido_afip, :respuesta_afip)");

		$stmt->bindParam(":uuid", $datos["uuid"], PDO::PARAM_STR);
		$stmt->bindParam(":id_empresa", $datos["id_empresa"], PDO::PARAM_INT);
		$stmt->bindParam(":fecha", $datos["fecha"], PDO::PARAM_STR);
		$stmt->bindParam(":codigo", $datos["codigo"], PDO::PARAM_INT);
		$stmt->bindParam(":cbte_tipo", $datos["cbte_tipo"], PDO::PARAM_INT);
		$stmt->bindParam(":id_cliente", $datos["id_cliente"], PDO::PARAM_INT);
		$stmt->bindParam(":id_vendedor", $datos["id_vendedor"], PDO::PARAM_INT);
		$stmt->bindParam(":productos", $productosJson, PDO::PARAM_STR);
		$stmt->bindParam(":impuesto", $datos["impuesto"], PDO::PARAM_STR);
		$stmt->bindParam(":impuesto_detalle", $datos["impuesto_detalle"], PDO::PARAM_STR);
		$stmt->bindParam(":neto", $datos["neto"], PDO::PARAM_STR);
	   	$stmt->bindParam(":neto_gravado", $datos["neto_gravado"], PDO::PARAM_STR);
		$stmt->bindParam(":base_imponible_0", $datos["base_imponible_0"], PDO::PARAM_STR);
		$stmt->bindParam(":base_imponible_2", $datos["base_imponible_2"], PDO::PARAM_STR);
		$stmt->bindParam(":base_imponible_5", $datos["base_imponible_5"], PDO::PARAM_STR);
		$stmt->bindParam(":base_imponible_10", $datos["base_imponible_10"], PDO::PARAM_STR);
		$stmt->bindParam(":base_imponible_21", $datos["base_imponible_21"], PDO::PARAM_STR);
		$stmt->bindParam(":base_imponible_27", $datos["base_imponible_27"], PDO::PARAM_STR);
		$stmt->bindParam(":iva_2", $datos["iva_2"], PDO::PARAM_STR);
		$stmt->bindParam(":iva_5", $datos["iva_5"], PDO::PARAM_STR);
		$stmt->bindParam(":iva_10", $datos["iva_10"], PDO::PARAM_STR);
		$stmt->bindParam(":iva_21", $datos["iva_21"], PDO::PARAM_STR);
		$stmt->bindParam(":iva_27", $datos["iva_27"], PDO::PARAM_STR);
		$stmt->bindParam(":total", $datos["total"], PDO::PARAM_STR);
		$stmt->bindParam(":metodo_pago", $datos["metodo_pago"], PDO::PARAM_STR);
		$stmt->bindParam(":pto_vta", $datos["pto_vta"], PDO::PARAM_INT);
		$stmt->bindParam(":concepto", $datos["concepto"], PDO::PARAM_INT);
		$stmt->bindParam(":fec_desde", $datos["fec_desde"], PDO::PARAM_STR);
		$stmt->bindParam(":fec_hasta", $datos["fec_hasta"], PDO::PARAM_STR);
		$stmt->bindParam(":fec_vencimiento", $datos["fec_vencimiento"], PDO::PARAM_STR);
		$stmt->bindParam(":asociado_tipo_cbte", $datos["asociado_tipo_cbte"], PDO::PARAM_INT);
		$stmt->bindParam(":asociado_pto_vta", $datos["asociado_pto_vta"], PDO::PARAM_INT);
		$stmt->bindParam(":asociado_nro_cbte", $datos["asociado_nro_cbte"], PDO::PARAM_INT);
		$stmt->bindParam(":estado", $datos["estado"], PDO::PARAM_INT);
		$stmt->bindParam(":observaciones_vta", $datos["observaciones_vta"], PDO::PARAM_STR);
		$stmt->bindParam(":pedido_afip", $datos["pedido_afip"], PDO::PARAM_STR);
		$stmt->bindParam(":respuesta_afip", $datos["respuesta_afip"], PDO::PARAM_STR);

		if($stmt->execute()){

			// Obtener el ID de la venta insertada
			// Con INSERT IGNORE, lastInsertId puede retornar 0 si la venta ya existe
			// Por eso obtenemos el ID usando el UUID o el código
			$idVenta = $conexion->lastInsertId();
			
			// Si lastInsertId retorna 0, obtener el ID por UUID o código
			if ($idVenta == 0 || empty($idVenta)) {
				if (isset($datos["uuid"]) && !empty($datos["uuid"])) {
					$stmtId = $conexion->prepare("SELECT id FROM $tabla WHERE uuid = :uuid LIMIT 1");
					$stmtId->bindParam(":uuid", $datos["uuid"], PDO::PARAM_STR);
					$stmtId->execute();
					$venta = $stmtId->fetch();
					$idVenta = $venta ? intval($venta["id"]) : 0;
					$stmtId->closeCursor();
				} elseif (isset($datos["codigo"]) && !empty($datos["codigo"])) {
					$stmtId = $conexion->prepare("SELECT id FROM $tabla WHERE codigo = :codigo LIMIT 1");
					$stmtId->bindParam(":codigo", $datos["codigo"], PDO::PARAM_INT);
					$stmtId->execute();
					$venta = $stmtId->fetch();
					$idVenta = $venta ? intval($venta["id"]) : 0;
					$stmtId->closeCursor();
				}
			}
			
			// Si se pasó productos y tenemos un ID válido, insertarlos en productos_venta
			if ($idVenta > 0 && isset($datos["productos"]) && !empty($datos["productos"])) {
				$resultadoProductos = self::mdlIngresarProductosVenta($idVenta, $datos["productos"]);
				if ($resultadoProductos != "ok") {
					// Log error pero no fallar la inserción de venta
					error_log("Error al insertar productos_venta para venta $idVenta: $resultadoProductos");
				} else {
					error_log("Productos insertados correctamente en productos_venta para venta $idVenta");
				}
			} else {
				if ($idVenta == 0) {
					error_log("Error: No se pudo obtener el ID de la venta insertada. UUID: " . ($datos["uuid"] ?? "N/A") . ", Código: " . ($datos["codigo"] ?? "N/A"));
				}
				if (!isset($datos["productos"]) || empty($datos["productos"])) {
					error_log("Advertencia: No se pasaron productos para la venta. ID: $idVenta");
				}
			}

			// Retornar array con estado y el ID de la venta insertada
			return array("estado" => "ok", "id_venta" => $idVenta, "codigo" => isset($datos["codigo"]) ? $datos["codigo"] : null);

		}else{

			return array("estado" => "error", "error" => $stmt->errorInfo(), "id_venta" => 0);
		
		}

		$stmt->closeCursor();
		$stmt = null;

	}

	/*=============================================
	EDITAR VENTA
	=============================================*/

	static public function mdlEditarVenta($tabla, $datos){

		// Primero obtener el id de la venta desde el codigo
		$stmtId = Conexion::conectar()->prepare("SELECT id FROM $tabla WHERE codigo = :codigo");
		$stmtId->bindParam(":codigo", $datos["codigo"], PDO::PARAM_INT);
		$stmtId->execute();
		$venta = $stmtId->fetch();
		$idVenta = $venta ? $venta["id"] : null;
		$stmtId->closeCursor();

		// Guardar productos JSON vacío (compatibilidad con estructura, pero no se usa)
		$productosJson = '[]';

		$stmt = Conexion::conectar()->prepare("UPDATE $tabla SET  id_cliente = :id_cliente, cbte_tipo = :cbte_tipo, id_vendedor = :id_vendedor, productos = :productos, impuesto = :impuesto, neto = :neto, total= :total, metodo_pago = :metodo_pago, pto_vta = :pto_vta, concepto = :concepto, fec_desde = :fec_desde, fec_hasta = :fec_hasta, fec_vencimiento = :fec_vencimiento, observaciones_vta = :observaciones_vta WHERE codigo = :codigo");

		$stmt->bindParam(":codigo", $datos["codigo"], PDO::PARAM_INT);
		$stmt->bindParam(":id_cliente", $datos["id_cliente"], PDO::PARAM_INT);
		$stmt->bindParam(":cbte_tipo", $datos["cbte_tipo"], PDO::PARAM_INT);
		$stmt->bindParam(":id_vendedor", $datos["id_vendedor"], PDO::PARAM_INT);
		$stmt->bindParam(":productos", $productosJson, PDO::PARAM_STR);
		$stmt->bindParam(":impuesto", $datos["impuesto"], PDO::PARAM_STR);
		$stmt->bindParam(":neto", $datos["neto"], PDO::PARAM_STR);
		$stmt->bindParam(":total", $datos["total"], PDO::PARAM_STR);
		$stmt->bindParam(":metodo_pago", $datos["metodo_pago"], PDO::PARAM_STR);
		$stmt->bindParam(":pto_vta", $datos["pto_vta"], PDO::PARAM_INT);
		$stmt->bindParam(":concepto", $datos["concepto"], PDO::PARAM_INT);
		$stmt->bindParam(":fec_desde", $datos["fec_desde"], PDO::PARAM_STR);
		$stmt->bindParam(":fec_hasta", $datos["fec_hasta"], PDO::PARAM_STR);
		$stmt->bindParam(":fec_vencimiento", $datos["fec_vencimiento"], PDO::PARAM_STR);		
		$stmt->bindParam(":observaciones_vta", $datos["observaciones_vta"], PDO::PARAM_STR);

		if($stmt->execute()){

			// Si se pasó productos y tenemos id_venta, actualizar productos_venta
			if ($idVenta && isset($datos["productos"]) && !empty($datos["productos"])) {
				// Eliminar productos existentes
				self::mdlEliminarProductosVenta($idVenta);
				// Insertar nuevos productos
				$resultadoProductos = self::mdlIngresarProductosVenta($idVenta, $datos["productos"]);
				if ($resultadoProductos != "ok") {
					error_log("Error al actualizar productos_venta para venta $idVenta: $resultadoProductos");
				}
			}

			return "ok";

		}else{

			return $stmt -> errorInfo();
		
		}

		$stmt->closeCursor();
		$stmt = null;

	}

	/*=============================================
	ELIMINAR VENTA
	=============================================*/
	static public function mdlEliminarVenta($tabla, $datos){
		//$stmt = Conexion::conectar()->prepare("DELETE FROM $tabla WHERE id = :id");
		$stmt = Conexion::conectar()->prepare("UPDATE $tabla SET cbte_tipo = 999 WHERE id = :id");
		$stmt -> bindParam(":id", $datos, PDO::PARAM_INT);
		if($stmt -> execute()){
			return "ok";
		}else{
			return $stmt -> errorInfo();	
		}
		$stmt -> close();
		$stmt = null;
	}

	/*=============================================
	RANGO FECHAS
	=============================================*/	
	static public function mdlRangoFechasVentas($tabla, $fechaInicial, $fechaFinal){
		if($fechaInicial == null){
			$stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla ORDER BY id DESC");
			$stmt -> execute();
			return $stmt -> fetchAll();	
		}else if($fechaInicial == $fechaFinal){
			$stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla WHERE fecha like '%$fechaFinal%' ORDER BY id DESC");
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
				$stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla WHERE fecha BETWEEN '$fechaInicial' AND '$fechaFinalMasUno' ORDER BY id DESC");
			}else{
				$stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla WHERE fecha BETWEEN '$fechaInicial' AND '$fechaFinal' ORDER BY id DESC");
			}
			$stmt -> execute();
			return $stmt -> fetchAll();
		}
	}

	/*=============================================
	SUMAR EL TOTAL DE VENTAS
	=============================================*/

	static public function mdlSumaTotalVentas($tabla){	

		$stmt = Conexion::conectar()->prepare("SELECT SUM(total) as total FROM $tabla ");

		$stmt -> execute();

		return $stmt -> fetch();

		$stmt -> closeCursor();

		$stmt = null;

	}

	/*=============================================
	SUMAR VENTAS POR RANGO DE FECHAS (OPTIMIZADO)
	=============================================*/
	static public function mdlSumaVentasPorRango($fechaInicial, $fechaFinal){

		if($fechaInicial == null){
			$stmt = Conexion::conectar()->prepare("SELECT SUM(total) as total FROM ventas WHERE cbte_tipo NOT IN (3, 8, 13, 203, 208, 213, 999)");
		}else if($fechaInicial == $fechaFinal){
			$stmt = Conexion::conectar()->prepare("SELECT SUM(total) as total FROM ventas WHERE cbte_tipo NOT IN (3, 8, 13, 203, 208, 213, 999) AND fecha LIKE '%$fechaFinal%'");
		}else{
			$fechaActual = new DateTime();
			$fechaActual ->add(new DateInterval("P1D"));
			$fechaActualMasUno = $fechaActual->format("Y-m-d");

			$fechaFinal2 = new DateTime($fechaFinal);
			$fechaFinal2 ->add(new DateInterval("P1D"));
			$fechaFinalMasUno = $fechaFinal2->format("Y-m-d");

			if($fechaFinalMasUno == $fechaActualMasUno){
				$stmt = Conexion::conectar()->prepare("SELECT SUM(total) as total FROM ventas WHERE cbte_tipo NOT IN (3, 8, 13, 203, 208, 213, 999) AND fecha BETWEEN '$fechaInicial' AND '$fechaFinalMasUno'");
			}else{
				$stmt = Conexion::conectar()->prepare("SELECT SUM(total) as total FROM ventas WHERE cbte_tipo NOT IN (3, 8, 13, 203, 208, 213, 999) AND fecha BETWEEN '$fechaInicial' AND '$fechaFinal'");
			}
		}
		
		$stmt -> execute();
		$resultado = $stmt -> fetch();
		
		$stmt -> closeCursor();
		$stmt = null;

		return $resultado ? ($resultado["total"] ? $resultado["total"] : 0) : 0;
	}

	/*=============================================
	ACTUALIZAR VENTA
	=============================================*/

	static public function mdlActualizarVenta($tabla, $item1, $valor1, $id){

		$stmt = Conexion::conectar()->prepare("UPDATE $tabla SET $item1 = $valor1 WHERE id = $id");

		if($stmt -> execute()){

			return "ok";
		
		}else{

			return $stmt -> errorInfo();	

		}

		$stmt -> closeCursor();

		$stmt = null;

	}

	/*=============================================
	ACTUALIZAR VENTA
	=============================================*/

	static public function mdlPedidoAfipVenta($tabla, $pedido_afip, $id){

		$stmt = Conexion::conectar()->prepare("UPDATE $tabla SET pedido_afip = :pedido_afip WHERE id = $id");

		$stmt -> bindParam(":pedido_afip", $pedido_afip, PDO::PARAM_STR);

		if($stmt -> execute()){

			return "ok";
		
		}else{

			return $stmt -> errorInfo();	

		}

		$stmt -> closeCursor();

		$stmt = null;

	}

		/*=============================================
	ACTUALIZAR VENTA
	=============================================*/

	static public function mdlRespuestaAfipVenta($tabla, $respAfip, $id){

		$stmt = Conexion::conectar()->prepare("UPDATE $tabla SET respuesta_afip = :respuesta_afip WHERE id = $id");

		$stmt -> bindParam(":respuesta_afip", $respAfip, PDO::PARAM_STR);

		if($stmt -> execute()){

			return "ok";
		
		}else{

			return $stmt -> errorInfo();	

		}

		$stmt -> closeCursor();

		$stmt = null;

	}


	/*=============================================
	CONSULTAR POR VENTA FACTURADA - TRUE SI ESTA FACTURA FALSE SI NO
	=============================================*/

	static public function mdlVentaFacturada($id){	

		$stmt = Conexion::conectar()->prepare("SELECT ventas.id FROM ventas INNER JOIN ventas_factura ON ventas.id = ventas_factura.id_venta WHERE ventas.id = ?");

		$stmt->bindParam(1, $id, PDO::PARAM_INT);
		$stmt->execute();

		$row = $stmt->fetch(PDO::FETCH_ASSOC);

		if( ! $row) {

			return false;

		} else {

			return true;

		}

		$stmt -> closeCursor();

		$stmt = null;

	}

	/*=============================================
	CONSULTAR POR DATOS DE VENTA FACTURADA
	=============================================*/

	static public function mdlVentaFacturadaDatos($id){	

		$stmt = Conexion::conectar()->prepare("SELECT * FROM ventas INNER JOIN ventas_factura ON ventas.id = ventas_factura.id_venta WHERE ventas.id = ?");

		$stmt->bindParam(1, $id, PDO::PARAM_INT);

		$stmt -> execute();

		return $stmt -> fetch();

		$stmt -> closeCursor();

		$stmt = null;

	}

	/*=============================================
	DEVOLVER ULTIMO ID
	=============================================*/

	static public function mdlUltimoId($tabla){	

		$stmt = Conexion::conectar()->prepare("SELECT MAX(id) as ultimo FROM $tabla");

		$stmt -> execute();

		return $stmt -> fetch();

		$stmt -> closeCursor();

		$stmt = null;

	}

	/*=============================================
	DEVOLVER ULTIMO ID
	=============================================*/

	static public function mdlMostrarUltimoCodigo($tabla){	

		$stmt = Conexion::conectar()->prepare("SELECT MAX(codigo) as ultimo FROM $tabla");

		$stmt -> execute();

		return $stmt -> fetch();

		$stmt -> closeCursor();

		$stmt = null;

	}

	/*=============================================
	FACTURAR DE VENTA
	=============================================*/

	static public function mdlFacturarVenta($tabla, $datos){

		$stmt = Conexion::conectar()->prepare("INSERT INTO $tabla (id_venta, nro_cbte, fec_factura, cae, fec_vto_cae) VALUES (:id_venta, :nro_cbte, :fec_factura, :cae, :fec_vto_cae)");

		$stmt->bindParam(":id_venta", $datos["id_venta"], PDO::PARAM_INT);
		// $stmt->bindParam(":concepto", $datos["concepto"], PDO::PARAM_INT);
		// $stmt->bindParam(":pto_vta", $datos["pto_vta"], PDO::PARAM_INT);
		// $stmt->bindParam(":cbte_tipo", $datos["cbte_tipo"], PDO::PARAM_INT);
		$stmt->bindParam(":nro_cbte", $datos["nro_cbte"], PDO::PARAM_INT);
		$stmt->bindParam(":fec_factura", $datos["fec_factura"], PDO::PARAM_STR);
		$stmt->bindParam(":cae", $datos["cae"], PDO::PARAM_STR);
		$stmt->bindParam(":fec_vto_cae", $datos["fec_vto_cae"], PDO::PARAM_STR);

		if($stmt->execute()){

			return true;

		}else{

			return false;

		}

		$stmt->close();
		$stmt = null;

	}	

	/*=============================================
	INSERTAR ERRORES FACTURACION
	=============================================*/

	static public function mdlObservacionesVenta($tabla, $datos, $id){

		$stmt = Conexion::conectar()->prepare("UPDATE $tabla SET  observaciones = :observaciones WHERE id = :id");

		$stmt->bindParam(":id", $id, PDO::PARAM_INT);
		$stmt->bindParam(":observaciones", $datos, PDO::PARAM_STR);

		if($stmt->execute()){

			return "ok";

		}else{

			return "error";

		}

		$stmt->close();
		$stmt = null;

	}

	/*=============================================
	TOTAL VENTAS RANGO FECHAS
	=============================================*/	
	static public function mdlRangoFechasTotalVentas($fechaInicial, $fechaFinal){

		if($fechaInicial == null){

			$stmt = Conexion::conectar()->prepare("SELECT SUM(total) as total FROM ventas");

		}else if($fechaInicial == $fechaFinal){

			$stmt = Conexion::conectar()->prepare("SELECT SUM(total) as total FROM ventas WHERE fecha like '%$fechaFinal%'");

		}else{

			$fechaActual = new DateTime();
			$fechaActual ->add(new DateInterval("P1D"));
			$fechaActualMasUno = $fechaActual->format("Y-m-d");

			$fechaFinal2 = new DateTime($fechaFinal);
			$fechaFinal2 ->add(new DateInterval("P1D"));
			$fechaFinalMasUno = $fechaFinal2->format("Y-m-d");

			if($fechaFinalMasUno == $fechaActualMasUno){

				$stmt = Conexion::conectar()->prepare("SELECT  SUM(total) as total FROM ventas WHERE fecha BETWEEN '$fechaInicial' AND '$fechaFinalMasUno'");

			}else{

				$stmt = Conexion::conectar()->prepare("SELECT  SUM(total) as total FROM ventas WHERE fecha BETWEEN '$fechaInicial' AND '$fechaFinal'");

			}

		}

		$stmt -> execute();

		return $stmt -> fetch();

	}

	/*=============================================
	MOSTRAR VENTA CON CLIENTE
	=============================================*/
	static public function mdlMostrarVentaConCliente($idVenta){

		$stmt = Conexion::conectar()->prepare("SELECT v.*, c.id as idCliente, c.nombre, c.tipo_documento, c.documento, vf.nro_cbte, vf.cae, vf.fec_vto_cae FROM ventas v LEFT JOIN clientes c ON v.id_cliente = c.id LEFT JOIN ventas_factura vf ON vf.id_venta = v.id WHERE v.id = :id");

		$stmt -> bindParam(":id", $idVenta, PDO::PARAM_INT);

		$stmt -> execute();

		return $stmt -> fetch();
		
		$stmt -> closeCursor();

		$stmt = null;

	}

	/*=============================================
	OBTENER PRODUCTOS DE UNA VENTA (TABLA RELACIONAL)
	=============================================*/
	static public function mdlObtenerProductosVenta($idVenta){

		$stmt = Conexion::conectar()->prepare("SELECT 
			pv.id,
			pv.id_venta,
			pv.id_producto,
			pv.cantidad,
			pv.precio_compra,
			pv.precio_venta,
			p.id as producto_id,
			p.descripcion,
			p.codigo,
			p.id_categoria,
			c.categoria,
			(pv.cantidad * pv.precio_venta) as total
		FROM productos_venta pv
		LEFT JOIN productos p ON pv.id_producto = p.id
		LEFT JOIN categorias c ON p.id_categoria = c.id
		WHERE pv.id_venta = :id_venta
		ORDER BY pv.id ASC");

		$stmt -> bindParam(":id_venta", $idVenta, PDO::PARAM_INT);

		$stmt -> execute();

		return $stmt -> fetchAll();
		
		$stmt -> closeCursor();

		$stmt = null;

	}

	/*=============================================
	LISTAR PRODUCTOS VENTA POR RANGO DE FECHAS (para informes con combos expandidos)
	=============================================*/
	static public function mdlListarProductosVentaPorRango($fechaInicial, $fechaFinal){
		$stmt = Conexion::conectar()->prepare("SELECT 
			pv.id_producto,
			p.descripcion,
			p.codigo,
			c.categoria,
			pv.cantidad,
			pv.precio_compra,
			pv.precio_venta,
			(pv.cantidad * pv.precio_venta) as total
		FROM ventas v
		INNER JOIN productos_venta pv ON v.id = pv.id_venta
		LEFT JOIN productos p ON pv.id_producto = p.id
		LEFT JOIN categorias c ON p.id_categoria = c.id
		WHERE v.fecha BETWEEN :fechaInicial AND :fechaFinal
		AND v.cbte_tipo NOT IN (3, 8, 13, 203, 208, 213, 999)
		ORDER BY v.fecha ASC, pv.id ASC");
		$stmt->bindParam(":fechaInicial", $fechaInicial, PDO::PARAM_STR);
		$stmt->bindParam(":fechaFinal", $fechaFinal, PDO::PARAM_STR);
		$stmt->execute();
		$resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$stmt->closeCursor();
		$stmt = null;
		return $resultado;
	}

	/*=============================================
	INSERTAR / ACTUALIZAR PRODUCTOS DE VENTA (TABLA RELACIONAL, IDMPOTENTE)
	=============================================*/
	static public function mdlIngresarProductosVenta($idVenta, $productos){

		// Si productos viene como JSON string, decodificarlo
		if (is_string($productos)) {
			$productos = json_decode($productos, true);
		}

		if (!is_array($productos) || empty($productos)) {
			return "ok"; // No hay productos, no es error
		}

		$conexion = Conexion::conectar();
		$conexion->beginTransaction();

		try {
			foreach ($productos as $producto) {
				$idProducto = isset($producto["id"]) ? intval($producto["id"]) : 0;
				$cantidad = isset($producto["cantidad"]) ? floatval($producto["cantidad"]) : 0;
				$precioCompra = isset($producto["precio_compra"]) ? floatval($producto["precio_compra"]) : 0;
				$precioVenta = isset($producto["precio"]) ? floatval($producto["precio"]) : (isset($producto["precio_venta"]) ? floatval($producto["precio_venta"]) : 0);

				if ($idProducto > 0 && $cantidad > 0) {
					// Usar INSERT ... ON DUPLICATE KEY UPDATE para evitar duplicados y hacer la operación idempotente.
					// Requiere índice único en la BD: UNIQUE KEY uq_venta_producto (id_venta, id_producto)
					$stmt = $conexion->prepare("
						INSERT INTO productos_venta (id_venta, id_producto, cantidad, precio_compra, precio_venta) 
						VALUES (:id_venta, :id_producto, :cantidad, :precio_compra, :precio_venta)
						ON DUPLICATE KEY UPDATE
							cantidad      = VALUES(cantidad),
							precio_compra = VALUES(precio_compra),
							precio_venta  = VALUES(precio_venta)
					");

					$stmt->bindParam(":id_venta", $idVenta, PDO::PARAM_INT);
					$stmt->bindParam(":id_producto", $idProducto, PDO::PARAM_INT);
					$stmt->bindParam(":cantidad", $cantidad, PDO::PARAM_STR);
					$stmt->bindParam(":precio_compra", $precioCompra, PDO::PARAM_STR);
					$stmt->bindParam(":precio_venta", $precioVenta, PDO::PARAM_STR);

					$stmt->execute();
					$stmt->closeCursor();
				}
			}

			$conexion->commit();
			return "ok";

		} catch (Exception $e) {
			$conexion->rollBack();
			return "error: " . $e->getMessage();
		}

		$stmt = null;
		$conexion = null;

	}

	/*=============================================
	ELIMINAR PRODUCTOS DE VENTA (TABLA RELACIONAL)
	=============================================*/
	static public function mdlEliminarProductosVenta($idVenta){

		$stmt = Conexion::conectar()->prepare("DELETE FROM productos_venta WHERE id_venta = :id_venta");

		$stmt -> bindParam(":id_venta", $idVenta, PDO::PARAM_INT);

		if($stmt -> execute()){

			return "ok";
		
		}else{

			return $stmt->errorInfo();	
		
		}

		$stmt -> closeCursor();

		$stmt = null;

	}

	/*=============================================
	LIBRO IVA VENTAS
	=============================================*/
	static public function mdlLibroIvaVentas($fechaInicial, $fechaFinal){

		if($fechaInicial == null){

			$stmt = Conexion::conectar()->prepare("SELECT v.fecha, vf.fec_factura as fechavf, v.concepto, v.cbte_tipo, v.pto_vta, vf.nro_cbte, c.tipo_documento, c.documento, c.nombre, v.base_imponible_0, v.base_imponible_2, v.base_imponible_5, v.base_imponible_10, v.base_imponible_21, v.base_imponible_27, v.neto_gravado as total_neto, v.iva_2, v.iva_5, v.iva_10, v.iva_21, v.iva_27, v.impuesto as total_impuesto, v.total, vf.cae, vf.fec_vto_cae
				FROM ventas v INNER JOIN ventas_factura vf ON v.id = vf.id_venta
				INNER JOIN clientes c ON v.id_cliente = c.id
				ORDER BY vf.id ASC;");

		}else if($fechaInicial == $fechaFinal){

			$stmt = Conexion::conectar()->prepare("SELECT v.fecha, vf.fec_factura as fechavf, v.concepto, v.cbte_tipo, v.pto_vta, vf.nro_cbte, c.tipo_documento, c.documento, c.nombre, v.base_imponible_0, v.base_imponible_2, v.base_imponible_5, v.base_imponible_10, v.base_imponible_21, v.base_imponible_27, v.neto_gravado as total_neto, v.iva_2, v.iva_5, v.iva_10, v.iva_21, v.iva_27, v.impuesto as total_impuesto, v.total, vf.cae, vf.fec_vto_cae
				FROM ventas v INNER JOIN ventas_factura vf ON v.id = vf.id_venta
				INNER JOIN clientes c ON v.id_cliente = c.id
				WHERE v.fecha like '%$fechaFinal%'
				ORDER BY vf.id ASC;");

		}else{

			$fechaActual = new DateTime();
			$fechaActual ->add(new DateInterval("P1D"));
			$fechaActualMasUno = $fechaActual->format("Y-m-d");

			$fechaFinal2 = new DateTime($fechaFinal);
			$fechaFinal2 ->add(new DateInterval("P1D"));
			$fechaFinalMasUno = $fechaFinal2->format("Y-m-d");

			if($fechaFinalMasUno == $fechaActualMasUno){

				$stmt = Conexion::conectar()->prepare("SELECT v.fecha, vf.fec_factura as fechavf, v.concepto, v.cbte_tipo, v.pto_vta, vf.nro_cbte, c.tipo_documento, c.documento, c.nombre, v.base_imponible_0, v.base_imponible_2, v.base_imponible_5, v.base_imponible_10, v.base_imponible_21, v.base_imponible_27, v.neto_gravado as total_neto, v.iva_2, v.iva_5, v.iva_10, v.iva_21, v.iva_27, v.impuesto as total_impuesto, v.total, vf.cae, vf.fec_vto_cae
				FROM ventas v INNER JOIN ventas_factura vf ON v.id = vf.id_venta
				INNER JOIN clientes c ON v.id_cliente = c.id
				WHERE v.fecha BETWEEN '$fechaInicial%' AND '$fechaFinalMasUno%'
				ORDER BY vf.id ASC;");

			}else{

				$stmt = Conexion::conectar()->prepare("SELECT v.fecha, vf.fec_factura as fechavf, v.concepto, v.cbte_tipo, v.pto_vta, vf.nro_cbte, c.tipo_documento, c.documento, c.nombre, v.base_imponible_0, v.base_imponible_2, v.base_imponible_5, v.base_imponible_10, v.base_imponible_21, v.base_imponible_27, v.neto_gravado as total_neto, v.iva_2, v.iva_5, v.iva_10, v.iva_21, v.iva_27, v.impuesto as total_impuesto, v.total, vf.cae, vf.fec_vto_cae
				FROM ventas v INNER JOIN ventas_factura vf ON v.id = vf.id_venta
				INNER JOIN clientes c ON v.id_cliente = c.id
				WHERE v.fecha BETWEEN '$fechaInicial%' AND '$fechaFinal%'
				ORDER BY vf.id ASC;");				

			}

		}

		$stmt -> execute();

		return $stmt -> fetchAll();
	}

	/*=============================================
	RANGO FECHAS SOLO VENTAS (EL OTRO RANGO FECHAS TRAE TODOS LOS REGISTROS DE LA TABLA VENTA)
	=============================================*/	
	static public function mdlRangoFechasSoloVentas($fechaInicial, $fechaFinal){

		if($fechaInicial == null){
			$stmt = Conexion::conectar()->prepare("SELECT * FROM ventas WHERE cbte_tipo NOT IN (3, 8, 13, 203, 208, 213, 999) ORDER BY id DESC");

		}else if($fechaInicial == $fechaFinal){
			$stmt = Conexion::conectar()->prepare("SELECT * FROM ventas WHERE cbte_tipo NOT IN (3, 8, 13, 203, 208, 213, 999) AND fecha like '%$fechaFinal%' ORDER BY id DESC");
		
		}else{

			$fechaActual = new DateTime();
			$fechaActual ->add(new DateInterval("P1D"));
			$fechaActualMasUno = $fechaActual->format("Y-m-d");

			$fechaFinal2 = new DateTime($fechaFinal);
			$fechaFinal2 ->add(new DateInterval("P1D"));
			$fechaFinalMasUno = $fechaFinal2->format("Y-m-d");

			if($fechaFinalMasUno == $fechaActualMasUno){
				$stmt = Conexion::conectar()->prepare("SELECT * FROM ventas WHERE cbte_tipo NOT IN (3, 8, 13, 203, 208, 213, 999) AND fecha BETWEEN '$fechaInicial' AND '$fechaFinalMasUno' ORDER BY id DESC");

			}else{
				$stmt = Conexion::conectar()->prepare("SELECT * FROM ventas WHERE cbte_tipo NOT IN (3, 8, 13, 203, 208, 213, 999) AND fecha BETWEEN '$fechaInicial' AND '$fechaFinal' ORDER BY id DESC");

			}

		}
		
		$stmt -> execute();

		return $stmt -> fetchAll();

	}

	/*=============================================
	RANGO FECHAS SOLO VENTAS POR MES/AÑO (EL OTRO RANGO FECHAS TRAE TODOS LOS REGISTROS DE LA TABLA VENTA)
	=============================================*/	
	static public function mdlRangoVentasPorMesAnio($fechaInicial, $fechaFinal){

		if($fechaInicial == null){

			$stmt = Conexion::conectar()->prepare("SELECT DISTINCT(DATE_FORMAT(fecha, '%Y-%m')) AS fecha, COUNT(id) AS cantidad, SUM(total) AS total FROM ventas WHERE cbte_tipo NOT IN (3, 8, 13, 203, 208, 213, 999) GROUP BY DATE_FORMAT(fecha, '%Y-%m') ORDER BY fecha ASC");

		}else if($fechaInicial == $fechaFinal){

			$stmt = Conexion::conectar()->prepare("SELECT DISTINCT(DATE_FORMAT(fecha, '%Y-%m')) AS fecha, COUNT(id) AS cantidad, SUM(total) AS total FROM ventas WHERE cbte_tipo NOT IN (3, 8, 13, 203, 208, 213, 999) GROUP BY DATE_FORMAT(fecha, '%Y-%m')  AND fecha like '%$fechaFinal%' ORDER BY fecha ASC");

		}else{

			$fechaActual = new DateTime();
			$fechaActual ->add(new DateInterval("P1D"));
			$fechaActualMasUno = $fechaActual->format("Y-m-d");

			$fechaFinal2 = new DateTime($fechaFinal);
			$fechaFinal2 ->add(new DateInterval("P1D"));
			$fechaFinalMasUno = $fechaFinal2->format("Y-m-d");

			if($fechaFinalMasUno == $fechaActualMasUno){

				$stmt = Conexion::conectar()->prepare("SELECT DISTINCT(DATE_FORMAT(fecha, '%Y-%m')) AS fecha, COUNT(id) AS cantidad, SUM(total) AS total FROM ventas WHERE cbte_tipo NOT IN (3, 8, 13, 203, 208, 213, 999) GROUP BY DATE_FORMAT(fecha, '%Y-%m')  AND fecha BETWEEN '$fechaInicial' AND '$fechaFinalMasUno' ORDER BY fecha ASC");

			}else{

				$stmt = Conexion::conectar()->prepare("SELECT DISTINCT(DATE_FORMAT(fecha, '%Y-%m')) AS fecha, COUNT(id) AS cantidad, SUM(total) AS total FROM ventas WHERE cbte_tipo NOT IN (3, 8, 13, 203, 208, 213, 999) GROUP BY DATE_FORMAT(fecha, '%Y-%m')  AND fecha BETWEEN '$fechaInicial' AND '$fechaFinal' ORDER BY fecha ASC");

			}

		}
		
		$stmt -> execute();

		return $stmt -> fetchAll();

	}

    /*=============================================
    BUSCAR IDENTIFICADOR UNICO DE VENTA
    =============================================*/
    static public function mdlBuscarIdentificadorVenta($id){
    	$stmt = Conexion::conectar()->prepare("SELECT id FROM ventas WHERE uuid = :uuid");
    	$stmt->bindParam(":uuid", $id, PDO::PARAM_STR);
    	$stmt -> execute();
    	return $stmt -> fetch();
    	$stmt -> closeCursor();
    	$stmt = null;
    }

	/*=============================================
	OBTENER ESTADÍSTICAS DEL DASHBOARD EN UNA SOLA CONSULTA (ULTRA OPTIMIZADO)
	=============================================*/
	static public function mdlEstadisticasDashboard($fechaHoy, $fechaSemanaInicio, $fechaSemanaFin, $fechaMesInicio, $fechaMesFin, $fechaMesAnteriorInicio, $fechaMesAnteriorFin){
		
		// OPTIMIZADO: Una sola consulta con UNION para todas las estadísticas
		// Ventas de hoy: usar DATE() para que coincida con todo el día (fecha puede ser TIMESTAMP/DATETIME)
		$stmt = Conexion::conectar()->prepare("
			SELECT 'hoy' as periodo, COALESCE(SUM(total), 0) as total 
			FROM ventas 
			WHERE cbte_tipo NOT IN (3, 8, 13, 203, 208, 213, 999) 
			AND DATE(fecha) = :fechaHoy
			
			UNION ALL
			
			SELECT 'semana' as periodo, COALESCE(SUM(total), 0) as total 
			FROM ventas 
			WHERE cbte_tipo NOT IN (3, 8, 13, 203, 208, 213, 999) 
			AND fecha BETWEEN :fechaSemanaInicio AND :fechaSemanaFin
			
			UNION ALL
			
			SELECT 'mes_actual' as periodo, COALESCE(SUM(total), 0) as total 
			FROM ventas 
			WHERE cbte_tipo NOT IN (3, 8, 13, 203, 208, 213, 999) 
			AND fecha BETWEEN :fechaMesInicio AND :fechaMesFin
			
			UNION ALL
			
			SELECT 'mes_anterior' as periodo, COALESCE(SUM(total), 0) as total 
			FROM ventas 
			WHERE cbte_tipo NOT IN (3, 8, 13, 203, 208, 213, 999) 
			AND fecha BETWEEN :fechaMesAnteriorInicio AND :fechaMesAnteriorFin
		");
		
		$stmt->bindParam(":fechaHoy", $fechaHoy, PDO::PARAM_STR);
		$stmt->bindParam(":fechaSemanaInicio", $fechaSemanaInicio, PDO::PARAM_STR);
		$stmt->bindParam(":fechaSemanaFin", $fechaSemanaFin, PDO::PARAM_STR);
		$stmt->bindParam(":fechaMesInicio", $fechaMesInicio, PDO::PARAM_STR);
		$stmt->bindParam(":fechaMesFin", $fechaMesFin, PDO::PARAM_STR);
		$stmt->bindParam(":fechaMesAnteriorInicio", $fechaMesAnteriorInicio, PDO::PARAM_STR);
		$stmt->bindParam(":fechaMesAnteriorFin", $fechaMesAnteriorFin, PDO::PARAM_STR);
		
		$stmt->execute();
		$resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
		
		$stmt->closeCursor();
		$stmt = null;
		
		// Convertir a array asociativo más fácil de usar
		$estadisticas = array();
		foreach ($resultados as $row) {
			$estadisticas[$row['periodo']] = floatval($row['total']);
		}
		
		return $estadisticas;
	}
}