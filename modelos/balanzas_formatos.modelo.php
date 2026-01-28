<?php

require_once "conexion.php";

class ModeloBalanzasFormatos {

	/**
	 * Listar formatos (por empresa, activos o todos)
	 */
	public static function mdlListar($id_empresa = null, $soloActivos = true) {
		$sql = "SELECT * FROM balanzas_formatos WHERE 1=1";
		$params = [];
		if ($id_empresa !== null) {
			$sql .= " AND id_empresa = :id_empresa";
			$params[':id_empresa'] = (int) $id_empresa;
		}
		if ($soloActivos) {
			$sql .= " AND activo = 1";
		}
		$sql .= " ORDER BY orden ASC, id ASC";

		$stmt = Conexion::conectar()->prepare($sql);
		foreach ($params as $k => $v) {
			$stmt->bindValue($k, $v, PDO::PARAM_INT);
		}
		$stmt->execute();
		$out = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$stmt = null;
		return $out;
	}

	/**
	 * Configuración para el frontend (crear-venta-caja):
	 * devuelve un array de objetos con los campos necesarios
	 * para interpretar los códigos de balanza.
	 */
	public static function mdlConfigParaVenta($id_empresa = null) {
		if ($id_empresa === null && isset($_SESSION['empresa'])) {
			$id_empresa = (int) $_SESSION['empresa'];
		}
		if ($id_empresa === null) {
			$id_empresa = 1;
		}
		$formatos = self::mdlListar($id_empresa, true);
		$config = [];
		foreach ($formatos as $f) {
			$config[] = [
				'id'               => (int) $f['id'],
				'prefijo'          => $f['prefijo'],
				'longitud_min'     => $f['longitud_min'] !== null ? (int) $f['longitud_min'] : null,
				'longitud_max'     => $f['longitud_max'] !== null ? (int) $f['longitud_max'] : null,
				'pos_producto'     => (int) $f['pos_producto'],
				'longitud_producto'=> (int) $f['longitud_producto'],
				'modo_cantidad'    => $f['modo_cantidad'],
				'pos_cantidad'     => $f['pos_cantidad'] !== null ? (int) $f['pos_cantidad'] : null,
				'longitud_cantidad'=> $f['longitud_cantidad'] !== null ? (int) $f['longitud_cantidad'] : null,
				'factor_divisor'   => (float) $f['factor_divisor'],
				'cantidad_fija'    => (float) $f['cantidad_fija'],
			];
		}
		return $config;
	}

	/**
	 * Alta
	 */
	public static function mdlIngresar($datos) {
		$sql = "INSERT INTO balanzas_formatos 
			(id_empresa, nombre, prefijo, longitud_min, longitud_max, pos_producto, longitud_producto,
			 modo_cantidad, pos_cantidad, longitud_cantidad, factor_divisor, cantidad_fija, orden, activo)
			VALUES
			(:id_empresa, :nombre, :prefijo, :longitud_min, :longitud_max, :pos_producto, :longitud_producto,
			 :modo_cantidad, :pos_cantidad, :longitud_cantidad, :factor_divisor, :cantidad_fija, :orden, :activo)";

		$stmt = Conexion::conectar()->prepare($sql);
		$stmt->bindValue(":id_empresa", (int) $datos["id_empresa"], PDO::PARAM_INT);
		$stmt->bindValue(":nombre", $datos["nombre"], PDO::PARAM_STR);
		$stmt->bindValue(":prefijo", $datos["prefijo"], PDO::PARAM_STR);
		$stmt->bindValue(":longitud_min", $datos["longitud_min"] !== '' ? (int) $datos["longitud_min"] : null, $datos["longitud_min"] === '' ? PDO::NULL : PDO::INT);
		$stmt->bindValue(":longitud_max", $datos["longitud_max"] !== '' ? (int) $datos["longitud_max"] : null, $datos["longitud_max"] === '' ? PDO::NULL : PDO::INT);
		$stmt->bindValue(":pos_producto", (int) $datos["pos_producto"], PDO::PARAM_INT);
		$stmt->bindValue(":longitud_producto", (int) $datos["longitud_producto"], PDO::PARAM_INT);
		$stmt->bindValue(":modo_cantidad", $datos["modo_cantidad"], PDO::PARAM_STR);
		$stmt->bindValue(":pos_cantidad", $datos["pos_cantidad"] !== '' ? (int) $datos["pos_cantidad"] : null, $datos["pos_cantidad"] === '' ? PDO::NULL : PDO::INT);
		$stmt->bindValue(":longitud_cantidad", $datos["longitud_cantidad"] !== '' ? (int) $datos["longitud_cantidad"] : null, $datos["longitud_cantidad"] === '' ? PDO::NULL : PDO::INT);
		$stmt->bindValue(":factor_divisor", $datos["factor_divisor"], PDO::PARAM_STR);
		$stmt->bindValue(":cantidad_fija", $datos["cantidad_fija"], PDO::PARAM_STR);
		$stmt->bindValue(":orden", (int) $datos["orden"], PDO::PARAM_INT);
		$stmt->bindValue(":activo", (int) $datos["activo"], PDO::PARAM_INT);

		if ($stmt->execute()) {
			$stmt = null;
			return "ok";
		}
		$err = $stmt->errorInfo();
		$stmt = null;
		return $err;
	}

	/**
	 * Mostrar por id
	 */
	public static function mdlMostrarPorId($id) {
		$stmt = Conexion::conectar()->prepare("SELECT * FROM balanzas_formatos WHERE id = :id");
		$stmt->bindValue(":id", (int) $id, PDO::PARAM_INT);
		$stmt->execute();
		$out = $stmt->fetch(PDO::FETCH_ASSOC);
		$stmt = null;
		return $out;
	}

	/**
	 * Editar
	 */
	public static function mdlEditar($datos) {
		$sql = "UPDATE balanzas_formatos SET
				nombre = :nombre,
				prefijo = :prefijo,
				longitud_min = :longitud_min,
				longitud_max = :longitud_max,
				pos_producto = :pos_producto,
				longitud_producto = :longitud_producto,
				modo_cantidad = :modo_cantidad,
				pos_cantidad = :pos_cantidad,
				longitud_cantidad = :longitud_cantidad,
				factor_divisor = :factor_divisor,
				cantidad_fija = :cantidad_fija,
				orden = :orden,
				activo = :activo
			WHERE id = :id";

		$stmt = Conexion::conectar()->prepare($sql);
		$stmt->bindValue(":id", (int) $datos["id"], PDO::PARAM_INT);
		$stmt->bindValue(":nombre", $datos["nombre"], PDO::PARAM_STR);
		$stmt->bindValue(":prefijo", $datos["prefijo"], PDO::PARAM_STR);
		$stmt->bindValue(":longitud_min", $datos["longitud_min"] !== '' ? (int) $datos["longitud_min"] : null, $datos["longitud_min"] === '' ? PDO::NULL : PDO::INT);
		$stmt->bindValue(":longitud_max", $datos["longitud_max"] !== '' ? (int) $datos["longitud_max"] : null, $datos["longitud_max"] === '' ? PDO::NULL : PDO::INT);
		$stmt->bindValue(":pos_producto", (int) $datos["pos_producto"], PDO::PARAM_INT);
		$stmt->bindValue(":longitud_producto", (int) $datos["longitud_producto"], PDO::PARAM_INT);
		$stmt->bindValue(":modo_cantidad", $datos["modo_cantidad"], PDO::PARAM_STR);
		$stmt->bindValue(":pos_cantidad", $datos["pos_cantidad"] !== '' ? (int) $datos["pos_cantidad"] : null, $datos["pos_cantidad"] === '' ? PDO::NULL : PDO::INT);
		$stmt->bindValue(":longitud_cantidad", $datos["longitud_cantidad"] !== '' ? (int) $datos["longitud_cantidad"] : null, $datos["longitud_cantidad"] === '' ? PDO::NULL : PDO::INT);
		$stmt->bindValue(":factor_divisor", $datos["factor_divisor"], PDO::PARAM_STR);
		$stmt->bindValue(":cantidad_fija", $datos["cantidad_fija"], PDO::PARAM_STR);
		$stmt->bindValue(":orden", (int) $datos["orden"], PDO::PARAM_INT);
		$stmt->bindValue(":activo", (int) $datos["activo"], PDO::PARAM_INT);

		if ($stmt->execute()) {
			$stmt = null;
			return "ok";
		}
		$err = $stmt->errorInfo();
		$stmt = null;
		return $err;
	}

	/**
	 * Baja lógica
	 */
	public static function mdlEliminar($id) {
		$stmt = Conexion::conectar()->prepare("UPDATE balanzas_formatos SET activo = 0 WHERE id = :id");
		$stmt->bindValue(":id", (int) $id, PDO::PARAM_INT);
		if ($stmt->execute()) {
			$stmt = null;
			return "ok";
		}
		$stmt = null;
		return "error";
	}

	/**
	 * Verificar si la tabla existe (para habilitar funciones)
	 */
	public static function tablaExiste() {
		$stmt = Conexion::conectar()->query(
			"SELECT 1 FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'balanzas_formatos' LIMIT 1"
		);
		$existe = $stmt && $stmt->fetch();
		$stmt = null;
		return (bool) $existe;
	}
}

