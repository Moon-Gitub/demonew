<?php

require_once "conexion.php";

class ModeloRetencionesIibb {

	static public function mdlListarRetenciones($fechaInicial, $fechaFinal, $idProveedor = null) {

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
		$stmt = Conexion::conectar()->prepare(
			"SELECT agente_retencion_iibb, codigo_jurisdiccion_iibb, tipo_regimen_retencion_default, proximo_numero_recibo
			FROM empresa WHERE id = :id LIMIT 1"
		);
		$stmt->bindParam(":id", $idEmpresa, PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetch();
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
