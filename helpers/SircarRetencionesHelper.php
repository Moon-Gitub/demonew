<?php

/**
 * Generador de líneas TXT SIRCAR — Retenciones Diseño N° 1 (11 campos).
 */
class SircarRetencionesHelper {

	public static function formatearRenglon($numero) {
		return str_pad((int)$numero, 5, '0', STR_PAD_LEFT);
	}

	public static function formatearNumeroComprobante($numero) {
		$digits = preg_replace('/\D/', '', (string)$numero);
		if ($digits === '') {
			$digits = '0';
		}
		return str_pad(substr($digits, -12), 12, '0', STR_PAD_LEFT);
	}

	public static function formatearCuit($cuit) {
		$digits = preg_replace('/\D/', '', (string)$cuit);
		return str_pad(substr($digits, -11), 11, '0', STR_PAD_LEFT);
	}

	public static function formatearFecha($fecha) {
		if (empty($fecha)) {
			return '';
		}
		$ts = strtotime($fecha);
		if ($ts === false) {
			return (string)$fecha;
		}
		return date('d/m/Y', $ts);
	}

	public static function formatearDecimal($monto) {
		return number_format((float)$monto, 2, '.', '');
	}

	/**
	 * @param int    $renglon       Secuencial 1..N en el archivo
	 * @param int    $tipo          1=retención, 2=anulación
	 * @param string $numComprobante Nº factura proveedor (12 dígitos)
	 * @param string $cuit
	 * @param string $fechaRetencion Y-m-d o dd/mm/yyyy
	 * @param float  $montoSujeto
	 * @param float  $alicuota      Porcentaje (ej. 1.25)
	 * @param float  $montoRetenido
	 * @param int    $tipoRegimen   ej. 101
	 * @param int    $jurisdiccion  ej. 913 Mendoza
	 */
	public static function generarLinea($renglon, $tipo, $numComprobante, $cuit, $fechaRetencion, $montoSujeto, $alicuota, $montoRetenido, $tipoRegimen, $jurisdiccion) {
		$campos = [
			self::formatearRenglon($renglon),
			'1',
			(string)(int)$tipo,
			self::formatearNumeroComprobante($numComprobante),
			self::formatearCuit($cuit),
			self::formatearFecha($fechaRetencion),
			self::formatearDecimal($montoSujeto),
			self::formatearDecimal($alicuota),
			self::formatearDecimal($montoRetenido),
			(string)(int)$tipoRegimen,
			(string)(int)$jurisdiccion
		];
		return implode(',', $campos);
	}

	public static function generarArchivo(array $retenciones, $tipoRegimen, $jurisdiccion) {
		$lineas = [];
		$renglon = 1;
		foreach ($retenciones as $row) {
			$montoSujeto = isset($row['factura_neto']) && $row['factura_neto'] !== null
				? $row['factura_neto']
				: (isset($row['importe']) ? $row['importe'] : 0);
			$lineas[] = self::generarLinea(
				$renglon++,
				1,
				isset($row['factura_numero']) ? $row['factura_numero'] : '',
				isset($row['cuit']) ? $row['cuit'] : '',
				!empty($row['fecha_retencion']) ? $row['fecha_retencion'] : ($row['fecha_movimiento'] ?? ''),
				$montoSujeto,
				isset($row['alicuota_retencion']) ? $row['alicuota_retencion'] : 0,
				isset($row['monto_retencion']) ? $row['monto_retencion'] : 0,
				$tipoRegimen,
				$jurisdiccion
			);
		}
		return implode("\r\n", $lineas) . (count($lineas) ? "\r\n" : '');
	}
}
