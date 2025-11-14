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

		$stmt = Conexion::conectar()->prepare("INSERT IGNORE INTO $tabla(uuid, fecha, codigo, cbte_tipo, id_cliente, id_vendedor, productos, impuesto, impuesto_detalle, neto, neto_gravado, base_imponible_0, base_imponible_2, base_imponible_5, base_imponible_10, base_imponible_21, base_imponible_27, iva_2, iva_5, iva_10, iva_21, iva_27, total, metodo_pago, pto_vta, concepto, fec_desde, fec_hasta, fec_vencimiento, asociado_tipo_cbte, asociado_pto_vta, asociado_nro_cbte, estado, observaciones_vta, pedido_afip, respuesta_afip) VALUES (:uuid, :fecha, :codigo, :cbte_tipo, :id_cliente, :id_vendedor, :productos, :impuesto, :impuesto_detalle, :neto, :neto_gravado, :base_imponible_0, :base_imponible_2, :base_imponible_5, :base_imponible_10, :base_imponible_21, :base_imponible_27, :iva_2, :iva_5, :iva_10, :iva_21, :iva_27, :total, :metodo_pago, :pto_vta, :concepto, :fec_desde, :fec_hasta, :fec_vencimiento, :asociado_tipo_cbte, :asociado_pto_vta, :asociado_nro_cbte, :estado, :observaciones_vta, :pedido_afip, :respuesta_afip)");

		$stmt->bindParam(":uuid", $datos["uuid"], PDO::PARAM_STR);
		$stmt->bindParam(":fecha", $datos["fecha"], PDO::PARAM_STR);
		$stmt->bindParam(":codigo", $datos["codigo"], PDO::PARAM_INT);
		$stmt->bindParam(":cbte_tipo", $datos["cbte_tipo"], PDO::PARAM_INT);
		$stmt->bindParam(":id_cliente", $datos["id_cliente"], PDO::PARAM_INT);
		$stmt->bindParam(":id_vendedor", $datos["id_vendedor"], PDO::PARAM_INT);
		$stmt->bindParam(":productos", $datos["productos"], PDO::PARAM_STR);
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

			return "ok";

		}else{

			return $stmt->errorInfo();
		
		}

		$stmt->close();
		$stmt = null;

	}

	/*=============================================
	EDITAR VENTA
	=============================================*/

	static public function mdlEditarVenta($tabla, $datos){

		$stmt = Conexion::conectar()->prepare("UPDATE $tabla SET  id_cliente = :id_cliente, cbte_tipo = :cbte_tipo, id_vendedor = :id_vendedor, productos = :productos, impuesto = :impuesto, neto = :neto, total= :total, metodo_pago = :metodo_pago, pto_vta = :pto_vta, concepto = :concepto, fec_desde = :fec_desde, fec_hasta = :fec_hasta, fec_vencimiento = :fec_vencimiento, observaciones_vta = :observaciones_vta WHERE codigo = :codigo");

		$stmt->bindParam(":codigo", $datos["codigo"], PDO::PARAM_INT);
		$stmt->bindParam(":id_cliente", $datos["id_cliente"], PDO::PARAM_INT);
		$stmt->bindParam(":cbte_tipo", $datos["cbte_tipo"], PDO::PARAM_INT);
		$stmt->bindParam(":id_vendedor", $datos["id_vendedor"], PDO::PARAM_INT);
		$stmt->bindParam(":productos", $datos["productos"], PDO::PARAM_STR);
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

			return "ok";

		}else{

			return $stmt -> errorInfo();
		
		}

		$stmt->close();
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

		$stmt -> close();

		$stmt = null;

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

		$stmt -> close();

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

		$stmt -> close();

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

		$stmt -> close();

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

		$stmt -> close();

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

		$stmt -> close();

		$stmt = null;

	}

	/*=============================================
	DEVOLVER ULTIMO ID
	=============================================*/

	static public function mdlUltimoId($tabla){	

		$stmt = Conexion::conectar()->prepare("SELECT MAX(id) as ultimo FROM $tabla");

		$stmt -> execute();

		return $stmt -> fetch();

		$stmt -> close();

		$stmt = null;

	}

	/*=============================================
	DEVOLVER ULTIMO ID
	=============================================*/

	static public function mdlMostrarUltimoCodigo($tabla){	

		$stmt = Conexion::conectar()->prepare("SELECT MAX(codigo) as ultimo FROM $tabla");

		$stmt -> execute();

		return $stmt -> fetch();

		$stmt -> close();

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
		
		$stmt -> close();

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
    	$stmt -> close();
    	$stmt = null;
    }
}