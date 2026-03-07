<?php

/**
 * Helper para construcción del request AFIP (FeCAEReq).
 * El IVA usado en facturación debe ser siempre el persistido en la venta; esta clase
 * no recalcula IVA, solo arma el array a partir de los datos de la venta.
 */
class FacturacionAfipHelper {

	/** Condiciones IVA consideradas Responsable Inscripto (discriminan IVA en AFIP) */
	const CONDICION_IVA_RI = [1, 11];

	/** Tipos de comprobante que llevan detalle de alícuotas IVA (A y B) */
	const CBTES_DISCRIMINAN_IVA = [1, 2, 3, 4, 6, 7, 8, 9];

	/**
	 * Indica si la empresa es Monotributista según condicion_iva.
	 * Monotributista: no se envía AlicIva y ImpIVA = 0.
	 *
	 * @param int|string $condicionIva valor de empresa.condicion_iva
	 * @return bool
	 */
	public static function esMonotributista($condicionIva) {
		$condicionIva = (int) $condicionIva;
		return !in_array($condicionIva, self::CONDICION_IVA_RI, true);
	}

	/**
	 * Construye el array FeCAEReq listo para CAESolicitar.
	 * Usa únicamente datos de la venta persistida (impuesto_detalle, impuesto, neto_gravado, total).
	 * No recalcula IVA.
	 *
	 * @param array $datosFactura con pto_vta, cbte_tipo, concepto, total, neto_gravado, impuesto, impuesto_detalle; opc. fec_desde, fec_hasta, fec_vencimiento
	 * @param array $cliente con tipo_documento, documento, condicion_iva
	 * @param int $condicionIvaEmisor empresa.condicion_iva (1/11 = RI, 6/13/16 = Monotributista)
	 * @param int $ultComp último número autorizado para ese pto_vta y cbte_tipo
	 * @param string $cbteFchYmd fecha del comprobante en formato Ymd
	 * @param array|null $cbtesAsoc opcional [['Tipo'=>int,'PtoVta'=>int,'Nro'=>int]]
	 * @return array estructura FeCAEReq para pasar a WSFE::CAESolicitar
	 */
	/**
	 * Construye solo el array FECAEDetRequest para un comprobante (para uso en lote).
	 * @param array $datosFactura
	 * @param array $cliente
	 * @param int $condicionIvaEmisor
	 * @param int $cbteDesde número desde (ej. ultComp+1)
	 * @param int $cbteHasta número hasta (ej. ultComp+1 para uno)
	 * @param string $cbteFchYmd
	 * @param array|null $cbtesAsoc
	 * @return array FECAEDetRequest
	 */
	public static function buildFECAEDetRequest(array $datosFactura, array $cliente, $condicionIvaEmisor, $cbteDesde, $cbteHasta, $cbteFchYmd, array $cbtesAsoc = null) {
		$impIVA = 0.0;
		$impTotal = round((float) ($datosFactura['total'] ?? 0), 2);
		if (self::esMonotributista($condicionIvaEmisor)) {
			$impNeto = $impTotal;
		} else {
			$impNeto = round((float) ($datosFactura['neto_gravado'] ?? 0), 2);
			$impIVA = round((float) ($datosFactura['impuesto'] ?? 0), 2);
		}

		$det = [
			'Concepto' => (int) ($datosFactura['concepto'] ?? 1),
			'DocTipo' => (int) $cliente['tipo_documento'],
			'DocNro' => (float) $cliente['documento'],
			'CbteDesde' => (int) $cbteDesde,
			'CbteHasta' => (int) $cbteHasta,
			'CbteFch' => $cbteFchYmd,
			'ImpTotal' => $impTotal,
			'ImpTotConc' => 0,
			'ImpNeto' => $impNeto,
			'ImpOpEx' => 0,
			'ImpTrib' => 0,
			'ImpIVA' => $impIVA,
			'MonId' => 'PES',
			'MonCotiz' => 1,
			'CondicionIVAReceptorId' => (int) $cliente['condicion_iva'],
		];

		if ((int) ($datosFactura['concepto'] ?? 1) !== 1) {
			$fecDesde = $datosFactura['fec_desde'] ?? null;
			$fecHasta = $datosFactura['fec_hasta'] ?? null;
			$fecVto = $datosFactura['fec_vencimiento'] ?? null;
			if ($fecDesde) $det['FchServDesde'] = is_numeric($fecDesde) ? date('Ymd', strtotime($fecDesde)) : $fecDesde;
			if ($fecHasta) $det['FchServHasta'] = is_numeric($fecHasta) ? date('Ymd', strtotime($fecHasta)) : $fecHasta;
			if ($fecVto)  $det['FchVtoPago']  = is_numeric($fecVto)  ? date('Ymd', strtotime($fecVto))  : $fecVto;
		}

		if (is_array($cbtesAsoc) && !empty($cbtesAsoc)) {
			$det['CbtesAsoc'] = $cbtesAsoc;
		}

		if (!self::esMonotributista($condicionIvaEmisor) && in_array((int) ($datosFactura['cbte_tipo'] ?? 0), self::CBTES_DISCRIMINAN_IVA, true)) {
			$impuestoDetalle = $datosFactura['impuesto_detalle'] ?? '';
			if (is_string($impuestoDetalle)) {
				$arrDet = json_decode($impuestoDetalle, true);
			} else {
				$arrDet = $impuestoDetalle;
			}
			if (is_array($arrDet) && count($arrDet) > 0) {
				$det['Iva'] = ['AlicIva' => []];
				foreach ($arrDet as $idx => $value) {
					$det['Iva']['AlicIva'][$idx] = [
						'Id' => (int) ($value['id'] ?? 0),
						'BaseImp' => round((float) ($value['baseImponible'] ?? 0), 2),
						'Importe' => round((float) ($value['iva'] ?? 0), 2),
					];
				}
			}
		}

		return $det;
	}

	public static function buildFeCAEReq(array $datosFactura, array $cliente, $condicionIvaEmisor, $ultComp, $cbteFchYmd, array $cbtesAsoc = null) {
		$cbte = $ultComp + 1;
		$det = self::buildFECAEDetRequest($datosFactura, $cliente, $condicionIvaEmisor, $cbte, $cbte, $cbteFchYmd, $cbtesAsoc);
		return [
			'FeCAEReq' => [
				'FeCabReq' => [
					'CantReg' => 1,
					'PtoVta' => (int) ($datosFactura['pto_vta'] ?? 0),
					'CbteTipo' => (int) ($datosFactura['cbte_tipo'] ?? 0),
				],
				'FeDetReq' => [
					'FECAEDetRequest' => $det,
				],
			],
		];
	}

	/**
	 * Construye FeCAEReq para facturación por lote (varios comprobantes en una sola llamada).
	 * Todas las ventas deben ser mismo pto_vta y cbte_tipo.
	 *
	 * @param array $items cada elemento: ['datosFactura' => array, 'cliente' => array, 'cbtesAsoc' => array|null]
	 * @param int $condicionIvaEmisor empresa.condicion_iva
	 * @param int $pto_vta
	 * @param int $cbte_tipo
	 * @param int $ultComp último número autorizado (AFIP)
	 * @return array FeCAEReq listo para CAESolicitar
	 */
	public static function buildFeCAEReqLote(array $items, $condicionIvaEmisor, $pto_vta, $cbte_tipo, $ultComp) {
		$dets = [];
		$n = count($items);
		for ($i = 0; $i < $n; $i++) {
			$cbteNro = $ultComp + 1 + $i;
			$item = $items[$i];
			$datosFactura = $item['datosFactura'];
			$cliente = $item['cliente'];
			$cbtesAsoc = isset($item['cbtesAsoc']) ? $item['cbtesAsoc'] : null;
			$fch = isset($datosFactura['fecha']) ? date('Ymd', strtotime($datosFactura['fecha'])) : date('Ymd');
			$dets[] = self::buildFECAEDetRequest($datosFactura, $cliente, $condicionIvaEmisor, $cbteNro, $cbteNro, $fch, $cbtesAsoc);
		}
		return [
			'FeCAEReq' => [
				'FeCabReq' => [
					'CantReg' => $n,
					'PtoVta' => (int) $pto_vta,
					'CbteTipo' => (int) $cbte_tipo,
				],
				'FeDetReq' => [
					'FECAEDetRequest' => $dets,
				],
			],
		];
	}
}
