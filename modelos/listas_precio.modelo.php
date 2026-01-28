<?php

require_once "conexion.php";

class ModeloListasPrecio {

	/**
	 * Listar listas de precio (por empresa o todas)
	 */
	static public function mdlListar($id_empresa = null, $soloActivas = true) {
		$sql = "SELECT * FROM listas_precio WHERE 1=1";
		$params = [];
		if ($id_empresa !== null) {
			$sql .= " AND id_empresa = :id_empresa";
			$params[':id_empresa'] = $id_empresa;
		}
		if ($soloActivas) {
			$sql .= " AND activo = 1";
		}
		$sql .= " ORDER BY orden ASC, nombre ASC";

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
	 * Obtener una lista por id
	 */
	static public function mdlMostrarPorId($id) {
		$stmt = Conexion::conectar()->prepare("SELECT * FROM listas_precio WHERE id = :id");
		$stmt->bindParam(":id", $id, PDO::PARAM_INT);
		$stmt->execute();
		$out = $stmt->fetch(PDO::FETCH_ASSOC);
		$stmt = null;
		return $out;
	}

	/**
	 * Obtener una lista por código y empresa
	 */
	static public function mdlMostrarPorCodigo($codigo, $id_empresa = 1) {
		$stmt = Conexion::conectar()->prepare("SELECT * FROM listas_precio WHERE codigo = :codigo AND id_empresa = :id_empresa LIMIT 1");
		$stmt->bindParam(":codigo", $codigo, PDO::PARAM_STR);
		$stmt->bindParam(":id_empresa", $id_empresa, PDO::PARAM_INT);
		$stmt->execute();
		$out = $stmt->fetch(PDO::FETCH_ASSOC);
		$stmt = null;
		return $out;
	}

	/**
	 * Para vistas de venta: array codigo => nombre (solo activas, por empresa)
	 */
	static public function mdlListarParaVenta($id_empresa = null) {
		if ($id_empresa === null && isset($_SESSION['empresa'])) {
			$id_empresa = (int) $_SESSION['empresa'];
		}
		if ($id_empresa === null) {
			$id_empresa = 1;
		}
		$listas = self::mdlListar($id_empresa, true);
		$arr = [];
		foreach ($listas as $row) {
			$arr[$row['codigo']] = $row['nombre'];
		}
		return $arr;
	}

	/**
	 * Para JS: configuración de listas (base_precio, tipo_descuento, valor_descuento) por codigo
	 * Solo listas activas de la empresa.
	 */
	static public function mdlConfigPorCodigos($codigos, $id_empresa = null) {
		if ($id_empresa === null && isset($_SESSION['empresa'])) {
			$id_empresa = (int) $_SESSION['empresa'];
		}
		if ($id_empresa === null) {
			$id_empresa = 1;
		}
		if (is_string($codigos)) {
			$codigos = array_filter(array_map('trim', explode(',', $codigos)));
		}
		if (empty($codigos)) {
			return [];
		}
		$placeholders = implode(',', array_fill(0, count($codigos), '?'));
		$sql = "SELECT codigo, base_precio, tipo_descuento, valor_descuento FROM listas_precio 
		        WHERE id_empresa = ? AND activo = 1 AND codigo IN ($placeholders) ORDER BY orden";
		$stmt = Conexion::conectar()->prepare($sql);
		$stmt->execute(array_merge([$id_empresa], $codigos));
		$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$stmt = null;
		$config = [];
		foreach ($rows as $r) {
			$config[$r['codigo']] = [
				'base_precio'    => $r['base_precio'],
				'tipo_descuento' => $r['tipo_descuento'],
				'valor_descuento'=> (float) $r['valor_descuento']
			];
		}
		return $config;
	}

	/**
	 * Alta
	 */
	static public function mdlIngresar($datos) {
		$stmt = Conexion::conectar()->prepare(
			"INSERT INTO listas_precio (id_empresa, codigo, nombre, base_precio, tipo_descuento, valor_descuento, orden, activo) 
			 VALUES (:id_empresa, :codigo, :nombre, :base_precio, :tipo_descuento, :valor_descuento, :orden, :activo)"
		);
		$stmt->bindParam(":id_empresa", $datos["id_empresa"], PDO::PARAM_INT);
		$stmt->bindParam(":codigo", $datos["codigo"], PDO::PARAM_STR);
		$stmt->bindParam(":nombre", $datos["nombre"], PDO::PARAM_STR);
		$stmt->bindParam(":base_precio", $datos["base_precio"], PDO::PARAM_STR);
		$stmt->bindParam(":tipo_descuento", $datos["tipo_descuento"], PDO::PARAM_STR);
		$stmt->bindParam(":valor_descuento", $datos["valor_descuento"], PDO::PARAM_STR);
		$stmt->bindParam(":orden", $datos["orden"], PDO::PARAM_INT);
		$stmt->bindParam(":activo", $datos["activo"], PDO::PARAM_INT);
		if ($stmt->execute()) {
			$stmt = null;
			return "ok";
		}
		$err = $stmt->errorInfo();
		$stmt = null;
		return $err;
	}

	/**
	 * Editar
	 */
	static public function mdlEditar($datos) {
		$stmt = Conexion::conectar()->prepare(
			"UPDATE listas_precio SET codigo = :codigo, nombre = :nombre, base_precio = :base_precio, 
			 tipo_descuento = :tipo_descuento, valor_descuento = :valor_descuento, orden = :orden, activo = :activo 
			 WHERE id = :id"
		);
		$stmt->bindParam(":codigo", $datos["codigo"], PDO::PARAM_STR);
		$stmt->bindParam(":nombre", $datos["nombre"], PDO::PARAM_STR);
		$stmt->bindParam(":base_precio", $datos["base_precio"], PDO::PARAM_STR);
		$stmt->bindParam(":tipo_descuento", $datos["tipo_descuento"], PDO::PARAM_STR);
		$stmt->bindParam(":valor_descuento", $datos["valor_descuento"], PDO::PARAM_STR);
		$stmt->bindParam(":orden", $datos["orden"], PDO::PARAM_INT);
		$stmt->bindParam(":activo", $datos["activo"], PDO::PARAM_INT);
		$stmt->bindParam(":id", $datos["id"], PDO::PARAM_INT);
		if ($stmt->execute()) {
			$stmt = null;
			return "ok";
		}
		$err = $stmt->errorInfo();
		$stmt = null;
		return $err;
	}

	/**
	 * Baja lógica (activo = 0)
	 */
	static public function mdlEliminar($id) {
		$stmt = Conexion::conectar()->prepare("UPDATE listas_precio SET activo = 0 WHERE id = :id");
		$stmt->bindParam(":id", $id, PDO::PARAM_INT);
		if ($stmt->execute()) {
			$stmt = null;
			return "ok";
		}
		$stmt = null;
		return "error";
	}

	/**
	 * Verificar si la tabla existe (para fallback parametros)
	 */
	static public function tablaExiste() {
		$stmt = Conexion::conectar()->query(
			"SELECT 1 FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'listas_precio' LIMIT 1"
		);
		$existe = $stmt && $stmt->fetch();
		$stmt = null;
		return (bool) $existe;
	}
}
