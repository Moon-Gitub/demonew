<?php

require_once __DIR__ . "/conexion.php";

class ModeloRetencionesIibb {

	static public function mdlListarRetenciones($fechaInicial, $fechaFinal, $idProveedor = null) {

		try {
			$pdo = Conexion::conectar();
			$check = $pdo->query("SHOW COLUMNS FROM proveedores_cuenta_corriente LIKE 'monto_retencion'");
			if (!$check || !$check->fetch()) {
				throw new RuntimeException(
					'Faltan columnas de retenciones en la base de datos. Ejecute migracion/retenciones_iibb.sql'
				);
			}
		} catch (RuntimeException $e) {
			throw $e;
		} catch (Throwable $e) {
			throw new RuntimeException('Error de conexión al listar retenciones: ' . $e->getMessage(), 0, $e);
		}

		$sql = "SELECT cc.*, p.nombre AS proveedor_nombre, p.cuit
			FROM proveedores_cuenta_corriente cc
			INNER JOIN proveedores p ON p.id = cc.id_proveedor
			WHERE cc.tipo = 0
			AND cc.monto_retencion IS NOT NULL
			AND cc.monto_retencion > 0
			AND COALESCE(cc.fecha_retencion, DATE(cc.fecha_movimiento)) >= :fecha_inicial
			AND COALESCE(cc.fecha_retencion, DATE(cc.fecha_movimiento)) <= :fecha_final";

		if ($idProveedor !== null && $idProveedor !== '') {
			$sql .= " AND cc.id_proveedor = :id_proveedor";
		}

		$sql .= " ORDER BY COALESCE(cc.fecha_retencion, DATE(cc.fecha_movimiento)) ASC, cc.id ASC";

		$stmt = Conexion::conectar()->prepare($sql);
		$stmt->bindParam(":fecha_inicial", $fechaInicial, PDO::PARAM_STR);
		$stmt->bindParam(":fecha_final", $fechaFinal, PDO::PARAM_STR);
		if ($idProveedor !== null && $idProveedor !== '') {
			$stmt->bindParam(":id_proveedor", $idProveedor, PDO::PARAM_INT);
		}
		$stmt->execute();
		return $stmt->fetchAll();
	}

	static public function mdlObtenerConfigEmpresa($idEmpresa = 1) {
		$defaults = [
			'agente_retencion_iibb' => 0,
			'codigo_jurisdiccion_iibb' => 913,
			'tipo_regimen_retencion_default' => 101,
			'proximo_numero_recibo' => 1,
		];

		try {
			$pdo = Conexion::conectar();
			$check = $pdo->query("SHOW COLUMNS FROM empresa LIKE 'codigo_jurisdiccion_iibb'");
			if (!$check || !$check->fetch()) {
				return $defaults;
			}

			$stmt = $pdo->prepare(
				"SELECT agente_retencion_iibb, codigo_jurisdiccion_iibb, tipo_regimen_retencion_default, proximo_numero_recibo
				FROM empresa WHERE id = :id LIMIT 1"
			);
			$stmt->bindParam(":id", $idEmpresa, PDO::PARAM_INT);
			$stmt->execute();
			$row = $stmt->fetch();
			if (!$row || !is_array($row)) {
				return $defaults;
			}
			return array_merge($defaults, $row);
		} catch (Throwable $e) {
			error_log('mdlObtenerConfigEmpresa retenciones: ' . $e->getMessage());
			return $defaults;
		}
	}

	static public function mdlReservarNumeroRecibo($idEmpresa = 1) {
		$pdo = Conexion::conectar();
		$pdo->beginTransaction();
		try {
			$stmt = $pdo->prepare("SELECT proximo_numero_recibo FROM empresa WHERE id = :id FOR UPDATE");
			$stmt->bindParam(":id", $idEmpresa, PDO::PARAM_INT);
			$stmt->execute();
			$row = $stmt->fetch();
			$numero = isset($row['proximo_numero_recibo']) ? (int)$row['proximo_numero_recibo'] : 1;
			$siguiente = $numero + 1;
			$upd = $pdo->prepare("UPDATE empresa SET proximo_numero_recibo = :siguiente WHERE id = :id");
			$upd->bindParam(":siguiente", $siguiente, PDO::PARAM_INT);
			$upd->bindParam(":id", $idEmpresa, PDO::PARAM_INT);
			$upd->execute();
			$pdo->commit();
			return $numero;
		} catch (Exception $e) {
			$pdo->rollBack();
			return null;
		}
	}
}
