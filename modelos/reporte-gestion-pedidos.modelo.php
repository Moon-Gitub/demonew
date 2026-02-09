<?php

require_once "conexion.php";

/**
 * Informe Gestión Inteligente de Pedidos.
 * Responde: ¿Qué debo pedir, a quién y cuánto?
 * Basado en: velocidad de venta, días de cobertura, ROI, productos críticos.
 */
class ModeloReporteGestionPedidos {

	const CBTE_TIPO_EXCLUIDOS = '3, 8, 13, 203, 208, 213, 999';

	/**
	 * Productos con ventas en el período: stock, ventas 7/30 días, días cobertura, cantidad sugerida, inversión.
	 * @param int $diasAnalisis Ej. 30
	 * @param int $diasCoberturaDeseado Ej. 30 (días de stock objetivo)
	 * @return array Cada ítem: id, codigo, descripcion, stock_actual, stock_minimo, precio_compra, precio_venta, proveedor, ventas_7_dias, ventas_30_dias, promedio_venta_diaria, dias_cobertura, cantidad_sugerida, inversion_necesaria, ganancia_esperada, roi, estado_urgencia
	 */
	static public function mdlProductosCriticos($diasAnalisis = 30, $diasCoberturaDeseado = 30) {
		$fechaDesde = date('Y-m-d', strtotime("-$diasAnalisis days"));
		$fechaDesde7 = date('Y-m-d', strtotime('-7 days'));

		// Optimizado: partir de ventas en el rango de fechas (usa índice en ventas.fecha), agregar por id_producto, luego unir a productos/proveedores.
		$stmt = Conexion::conectar()->prepare("
			SELECT
			  p.id,
			  p.codigo,
			  p.descripcion,
			  IF(p.stock < 0, 0, COALESCE(p.stock, 0)) AS stock_actual,
			  COALESCE(p.stock_bajo, 0) AS stock_minimo,
			  COALESCE(p.precio_compra, 0) AS precio_compra,
			  COALESCE(p.precio_venta, 0) AS precio_venta,
			  prov.nombre AS proveedor,
			  p.id_proveedor,
			  COALESCE(agg.ventas_7_dias, 0) AS ventas_7_dias,
			  COALESCE(agg.ventas_30_dias, 0) AS ventas_30_dias
			FROM (
			  SELECT
			    pv.id_producto,
			    SUM(CASE WHEN v.fecha >= :fecha_7 THEN pv.cantidad ELSE 0 END) AS ventas_7_dias,
			    SUM(pv.cantidad) AS ventas_30_dias
			  FROM ventas v
			  INNER JOIN productos_venta pv ON pv.id_venta = v.id
			  WHERE v.fecha >= :fecha_desde
			    AND v.cbte_tipo NOT IN (" . self::CBTE_TIPO_EXCLUIDOS . ")
			  GROUP BY pv.id_producto
			  HAVING ventas_30_dias > 0
			) agg
			INNER JOIN productos p ON p.id = agg.id_producto
			LEFT JOIN proveedores prov ON p.id_proveedor = prov.id
		");
		$stmt->bindParam(":fecha_desde", $fechaDesde, PDO::PARAM_STR);
		$stmt->bindParam(":fecha_7", $fechaDesde7, PDO::PARAM_STR);
		$stmt->execute();
		$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$stmt->closeCursor();
		$stmt = null;

		$result = [];
		foreach ($rows as $r) {
			$ventas30 = (float) $r['ventas_30_dias'];
			$promedioDiario = $diasAnalisis > 0 ? $ventas30 / $diasAnalisis : 0;
			$stock = (float) $r['stock_actual'];
			$diasCobertura = $promedioDiario > 0 ? round($stock / $promedioDiario, 1) : 999;
			$necesario = max(0, ($promedioDiario * $diasCoberturaDeseado) - $stock);
			$cantidadSugerida = round($necesario, 2);
			$precioCompra = (float) $r['precio_compra'];
			$precioVenta = (float) $r['precio_venta'];
			$inversion = $cantidadSugerida * $precioCompra;
			$ganancia = $cantidadSugerida * ($precioVenta - $precioCompra);
			$roi = $inversion > 0 ? round(($ganancia / $inversion) * 100, 1) : 0;

			if ($diasCobertura <= 3) {
				$estado = 'critico';
			} elseif ($diasCobertura <= 7) {
				$estado = 'urgente';
			} else {
				$estado = 'normal';
			}

			$result[] = array_merge($r, [
				'promedio_venta_diaria' => round($promedioDiario, 2),
				'dias_cobertura'       => $diasCobertura,
				'cantidad_sugerida'     => $cantidadSugerida,
				'inversion_necesaria'   => round($inversion, 2),
				'ganancia_esperada'     => round($ganancia, 2),
				'roi'                  => $roi,
				'estado_urgencia'      => $estado,
			]);
		}

		usort($result, function ($a, $b) {
			return ($a['dias_cobertura'] <=> $b['dias_cobertura']);
		});
		return $result;
	}

	/**
	 * Resumen: inversión total, solo críticos, ganancia esperada, cantidad productos.
	 */
	static public function mdlResumenInversion($diasAnalisis = 30, $diasCoberturaDeseado = 30) {
		$lista = self::mdlProductosCriticos($diasAnalisis, $diasCoberturaDeseado);
		$inversionTotal = 0;
		$inversionCriticos = 0;
		$gananciaTotal = 0;
		foreach ($lista as $r) {
			$inversionTotal += $r['inversion_necesaria'];
			$gananciaTotal += $r['ganancia_esperada'];
			if ($r['estado_urgencia'] === 'critico') {
				$inversionCriticos += $r['inversion_necesaria'];
			}
		}
		return [
			'inversion_total'      => round($inversionTotal, 2),
			'inversion_criticos'   => round($inversionCriticos, 2),
			'ganancia_esperada'    => round($gananciaTotal, 2),
			'cantidad_productos'  => count($lista),
			'criticos_count'      => count(array_filter($lista, function ($x) { return $x['estado_urgencia'] === 'critico'; })),
			'urgentes_count'      => count(array_filter($lista, function ($x) { return $x['estado_urgencia'] === 'urgente'; })),
		];
	}

	/**
	 * Agrupado por proveedor: lista de productos a pedir y total por proveedor.
	 */
	static public function mdlPedidoPorProveedor($diasAnalisis = 30, $diasCoberturaDeseado = 30) {
		$lista = self::mdlProductosCriticos($diasAnalisis, $diasCoberturaDeseado);
		$porProveedor = [];
		foreach ($lista as $r) {
			$nombre = $r['proveedor'] ?: 'Sin proveedor';
			if (!isset($porProveedor[$nombre])) {
				$porProveedor[$nombre] = ['proveedor' => $nombre, 'productos' => [], 'total_inversion' => 0];
			}
			$porProveedor[$nombre]['productos'][] = $r;
			$porProveedor[$nombre]['total_inversion'] += $r['inversion_necesaria'];
		}
		foreach ($porProveedor as $k => $v) {
			$porProveedor[$k]['total_inversion'] = round($v['total_inversion'], 2);
		}
		return array_values($porProveedor);
	}

	/**
	 * Productos de baja rotación: con stock pero pocas o ninguna venta en los últimos 90 días.
	 */
	static public function mdlBajaRotacion($dias = 90) {
		$fechaDesde = date('Y-m-d', strtotime("-$dias days"));
		// Optimizado: subconsulta agrega ventas por producto solo en el rango de fechas; luego JOIN a productos con stock.
		$stmt = Conexion::conectar()->prepare("
			SELECT
			  p.id,
			  p.codigo,
			  p.descripcion,
			  IF(p.stock < 0, 0, COALESCE(p.stock, 0)) AS stock_actual,
			  COALESCE(p.precio_compra, 0) AS precio_compra,
			  (IF(p.stock < 0, 0, COALESCE(p.stock, 0)) * COALESCE(p.precio_compra, 0)) AS valorizado
			FROM productos p
			LEFT JOIN (
			  SELECT pv.id_producto, SUM(pv.cantidad) AS ventas_periodo
			  FROM ventas v
			  INNER JOIN productos_venta pv ON pv.id_venta = v.id
			  WHERE v.fecha >= :fecha_desde
			    AND v.cbte_tipo NOT IN (" . self::CBTE_TIPO_EXCLUIDOS . ")
			  GROUP BY pv.id_producto
			) ventas_per ON ventas_per.id_producto = p.id
			WHERE IF(p.stock < 0, 0, COALESCE(p.stock, 0)) > 0
			  AND (ventas_per.ventas_periodo IS NULL OR ventas_per.ventas_periodo < 1)
			ORDER BY valorizado DESC
			LIMIT 50
		");
		$stmt->bindParam(":fecha_desde", $fechaDesde, PDO::PARAM_STR);
		$stmt->execute();
		$res = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$stmt->closeCursor();
		$stmt = null;
		return $res ?: [];
	}
}
