<?php

require_once "conexion.php";

class ModeloPermisosRol {

	/**
	 * Verificar si las tablas pantallas y permisos_rol existen
	 */
	public static function tablasExisten() {
		try {
			$stmt = Conexion::conectar()->query("SHOW TABLES LIKE 'pantallas'");
			if ($stmt->rowCount() === 0) return false;
			$stmt = Conexion::conectar()->query("SHOW TABLES LIKE 'permisos_rol'");
			return $stmt->rowCount() > 0;
		} catch (Exception $e) {
			return false;
		}
	}

	/**
	 * Listar pantallas agrupadas por agrupacion (para el panel de permisos)
	 * @return array [ agrupacion => [ ['id'=>, 'codigo'=>, 'nombre'=>], ... ], ... ]
	 */
	public static function mdlListarPantallasAgrupadas() {
		if (!self::tablasExisten()) return [];
		try {
			$stmt = Conexion::conectar()->prepare(
				"SELECT id, codigo, nombre, agrupacion, orden FROM pantallas WHERE activo = 1 ORDER BY agrupacion, orden, nombre"
			);
			$stmt->execute();
			$filas = $stmt->fetchAll(PDO::FETCH_ASSOC);
			$stmt = null;
			$agrupado = [];
			foreach ($filas as $f) {
				$agrupado[$f['agrupacion']][] = $f;
			}
			return $agrupado;
		} catch (Exception $e) {
			return [];
		}
	}

	/**
	 * Obtener códigos de pantallas permitidas para un rol (para sesión)
	 * @param string $rol
	 * @return array ['crear-venta-caja', 'ventas', ...]
	 */
	public static function mdlCodigosPermitidosPorRol($rol) {
		if (!self::tablasExisten()) return [];
		try {
			$stmt = Conexion::conectar()->prepare(
				"SELECT p.codigo FROM permisos_rol pr
				 INNER JOIN pantallas p ON p.id = pr.id_pantalla AND p.activo = 1
				 WHERE pr.rol = :rol"
			);
			$stmt->bindParam(":rol", $rol, PDO::PARAM_STR);
			$stmt->execute();
			$filas = $stmt->fetchAll(PDO::FETCH_COLUMN);
			$stmt = null;
			return is_array($filas) ? $filas : [];
		} catch (Exception $e) {
			return [];
		}
	}

	/**
	 * Obtener IDs de pantallas permitidas para un rol (para checkboxes)
	 * @param string $rol
	 * @return array [1, 2, 5, ...]
	 */
	public static function mdlIdsPermitidosPorRol($rol) {
		if (!self::tablasExisten()) return [];
		try {
			$stmt = Conexion::conectar()->prepare(
				"SELECT id_pantalla FROM permisos_rol WHERE rol = :rol"
			);
			$stmt->bindParam(":rol", $rol, PDO::PARAM_STR);
			$stmt->execute();
			$filas = $stmt->fetchAll(PDO::FETCH_COLUMN);
			$stmt = null;
			return is_array($filas) ? array_map('intval', $filas) : [];
		} catch (Exception $e) {
			return [];
		}
	}

	/**
	 * Listar roles distintos (desde permisos_rol o fijos)
	 * @return array ['Administrador', 'Vendedor', ...]
	 */
	public static function mdlListarRoles() {
		if (!self::tablasExisten()) return ['Administrador', 'Vendedor'];
		try {
			$stmt = Conexion::conectar()->query("SELECT DISTINCT rol FROM permisos_rol ORDER BY rol");
			$filas = $stmt->fetchAll(PDO::FETCH_COLUMN);
			$stmt = null;
			if (!empty($filas)) return $filas;
			return ['Administrador', 'Vendedor'];
		} catch (Exception $e) {
			return ['Administrador', 'Vendedor'];
		}
	}

	/**
	 * Guardar permisos de un rol (borra los actuales e inserta los seleccionados)
	 * @param string $rol
	 * @param array $idsPantallas [1, 2, 5, ...]
	 * @return string 'ok' | 'error'
	 */
	public static function mdlGuardarPermisosRol($rol, $idsPantallas) {
		if (!self::tablasExisten()) return 'error';
		try {
			$con = Conexion::conectar();
			$con->beginTransaction();
			$stmt = $con->prepare("DELETE FROM permisos_rol WHERE rol = :rol");
			$stmt->bindParam(":rol", $rol, PDO::PARAM_STR);
			$stmt->execute();
			$stmt = null;
			if (!empty($idsPantallas) && is_array($idsPantallas)) {
				$stmt = $con->prepare("INSERT INTO permisos_rol (rol, id_pantalla) VALUES (:rol, :id_pantalla)");
				foreach ($idsPantallas as $id) {
					$id = (int) $id;
					if ($id <= 0) continue;
					$stmt->bindParam(":rol", $rol, PDO::PARAM_STR);
					$stmt->bindParam(":id_pantalla", $id, PDO::PARAM_INT);
					$stmt->execute();
				}
			}
			$con->commit();
			return 'ok';
		} catch (Exception $e) {
			if (isset($con)) $con->rollBack();
			return 'error';
		}
	}
}
