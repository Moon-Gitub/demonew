<?php

require_once "conexion.php";

/**
 * Modelo para el informe Dashboard Ejecutivo Diario.
 * Métricas: ventas del día, comparativas, top productos, medios de pago, saldo caja.
 */
class ModeloReporteDashboardEjecutivo {

	/** Excluir notas de crédito y anulaciones */
	const CBTE_TIPO_EXCLUIDOS = '3, 8, 13, 203, 208, 213, 999';

	/**
	 * Resumen del día: ventas totales, transacciones, ticket promedio, clientes atendidos.
	 * Usa rango de fechas para poder usar índice en ventas.fecha.
	 * @param string $fecha Fecha en Y-m-d
	 * @return array|null
	 */
	static public function mdlResumenDia($fecha) {
		$stmt = Conexion::conectar()->prepare("
			SELECT
			  :fecha AS fecha,
			  COALESCE(SUM(v.total), 0) AS ventas_totales,
			  COUNT(*) AS cantidad_transacciones,
			  COALESCE(AVG(v.total), 0) AS ticket_promedio,
			  COUNT(DISTINCT v.id_cliente) AS clientes_atendidos
			FROM ventas v
			WHERE v.fecha >= :fecha
			  AND v.fecha < :fecha_fin
			  AND v.cbte_tipo NOT IN (" . self::CBTE_TIPO_EXCLUIDOS . ")
		");
		$fechaFin = date('Y-m-d', strtotime($fecha . ' +1 day'));
		$stmt->bindParam(":fecha", $fecha, PDO::PARAM_STR);
		$stmt->bindParam(":fecha_fin", $fechaFin, PDO::PARAM_STR);
		$stmt->execute();
		$res = $stmt->fetch(PDO::FETCH_ASSOC);
		$stmt->closeCursor();
		$stmt = null;
		return $res;
	}

	/**
	 * Ventas del día anterior (para comparativa %). Rango de fechas para índice.
	 * @param string $fechaAyer Fecha en Y-m-d
	 * @return float
	 */
	static public function mdlVentasDiaAnterior($fechaAyer) {
		$stmt = Conexion::conectar()->prepare("
			SELECT COALESCE(SUM(total), 0) AS total
			FROM ventas
			WHERE fecha >= :fecha
			  AND fecha < :fecha_fin
			  AND cbte_tipo NOT IN (" . self::CBTE_TIPO_EXCLUIDOS . ")
		");
		$fechaFin = date('Y-m-d', strtotime($fechaAyer . ' +1 day'));
		$stmt->bindParam(":fecha", $fechaAyer, PDO::PARAM_STR);
		$stmt->bindParam(":fecha_fin", $fechaFin, PDO::PARAM_STR);
		$stmt->execute();
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		$stmt->closeCursor();
		$stmt = null;
		return $row ? (float) $row['total'] : 0;
	}

	/**
	 * Ventas mismo día del mes anterior (para comparativa %).
	 * @param string $fecha Fecha en Y-m-d (ej. primer día del mes actual para "mismo día mes anterior")
	 * @return float
	 */
	static public function mdlVentasMismoDiaMesAnterior($fecha) {
		$stmt = Conexion::conectar()->prepare("
			SELECT COALESCE(SUM(total), 0) AS total
			FROM ventas
			WHERE fecha >= DATE_SUB(:fecha, INTERVAL 1 MONTH)
			  AND fecha < DATE_ADD(DATE_SUB(:fecha, INTERVAL 1 MONTH), INTERVAL 1 DAY)
			  AND cbte_tipo NOT IN (" . self::CBTE_TIPO_EXCLUIDOS . ")
		");
		$stmt->bindParam(":fecha", $fecha, PDO::PARAM_STR);
		$stmt->execute();
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		$stmt->closeCursor();
		$stmt = null;
		return $row ? (float) $row['total'] : 0;
	}

	/**
	 * Top 10 productos más vendidos del día. Parte de ventas en rango (índice).
	 * @param string $fecha Y-m-d
	 * @return array
	 */
	static public function mdlTopProductosDia($fecha) {
		$stmt = Conexion::conectar()->prepare("
			SELECT
			  p.descripcion AS nombre,
			  SUM(pv.cantidad) AS cantidad_vendida,
			  SUM(pv.cantidad * COALESCE(pv.precio_venta, pv.precio_unitario, 0)) AS monto_total
			FROM ventas v
			INNER JOIN productos_venta pv ON pv.id_venta = v.id
			INNER JOIN productos p ON pv.id_producto = p.id
			WHERE v.fecha >= :fecha
			  AND v.fecha < :fecha_fin
			  AND v.cbte_tipo NOT IN (" . self::CBTE_TIPO_EXCLUIDOS . ")
			GROUP BY p.id, p.descripcion
			ORDER BY cantidad_vendida DESC
			LIMIT 10
		");
		$fechaFin = date('Y-m-d', strtotime($fecha . ' +1 day'));
		$stmt->bindParam(":fecha", $fecha, PDO::PARAM_STR);
		$stmt->bindParam(":fecha_fin", $fechaFin, PDO::PARAM_STR);
		$stmt->execute();
		$res = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$stmt->closeCursor();
		$stmt = null;
		return $res ?: [];
	}

	/**
	 * Distribución por medio de pago del día. Rango de fechas para índice.
	 * @param string $fecha Y-m-d
	 * @return array
	 */
	static public function mdlMediosPagoDia($fecha) {
		$stmt = Conexion::conectar()->prepare("
			SELECT
			  v.metodo_pago AS nombre,
			  COUNT(*) AS cantidad,
			  COALESCE(SUM(v.total), 0) AS monto_total
			FROM ventas v
			WHERE v.fecha >= :fecha
			  AND v.fecha < :fecha_fin
			  AND v.cbte_tipo NOT IN (" . self::CBTE_TIPO_EXCLUIDOS . ")
			GROUP BY v.metodo_pago
			ORDER BY monto_total DESC
		");
		$fechaFin = date('Y-m-d', strtotime($fecha . ' +1 day'));
		$stmt->bindParam(":fecha", $fecha, PDO::PARAM_STR);
		$stmt->bindParam(":fecha_fin", $fechaFin, PDO::PARAM_STR);
		$stmt->execute();
		$res = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$stmt->closeCursor();
		$stmt = null;
		return $res ?: [];
	}

	/**
	 * Saldo de caja acumulado hasta la fecha (ingresos tipo=1 menos egresos tipo=0).
	 * Usa fecha < :fecha_fin para poder usar índice en cajas.fecha.
	 * @param string $fecha Y-m-d
	 * @return float
	 */
	static public function mdlSaldoCajaAl($fecha) {
		$stmt = Conexion::conectar()->prepare("
			SELECT
			  COALESCE(SUM(CASE WHEN tipo = 1 THEN monto ELSE 0 END), 0) -
			  COALESCE(SUM(CASE WHEN tipo = 0 THEN monto ELSE 0 END), 0) AS saldo_caja
			FROM cajas
			WHERE fecha < :fecha_fin
		");
		$fechaFin = date('Y-m-d', strtotime($fecha . ' +1 day'));
		$stmt->bindParam(":fecha_fin", $fechaFin, PDO::PARAM_STR);
		$stmt->execute();
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		$stmt->closeCursor();
		$stmt = null;
		return $row ? (float) $row['saldo_caja'] : 0;
	}
}
