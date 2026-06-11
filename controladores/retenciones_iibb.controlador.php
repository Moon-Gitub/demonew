<?php

require_once __DIR__ . '/../helpers/SircarRetencionesHelper.php';

class ControladorRetencionesIibb {

	static public function ctrListarRetenciones($fechaInicial, $fechaFinal, $idProveedor = null) {
		return ModeloRetencionesIibb::mdlListarRetenciones($fechaInicial, $fechaFinal, $idProveedor);
	}

	static public function ctrExportarTxt($fechaInicial, $fechaFinal, $idProveedor = null) {
		$retenciones = self::ctrListarRetenciones($fechaInicial, $fechaFinal, $idProveedor);
		$idEmpresa = isset($_SESSION['empresa']) ? (int)$_SESSION['empresa'] : 1;
		$config = ModeloRetencionesIibb::mdlObtenerConfigEmpresa($idEmpresa);
		$tipoRegimen = isset($config['tipo_regimen_retencion_default']) ? (int)$config['tipo_regimen_retencion_default'] : 101;
		$jurisdiccion = isset($config['codigo_jurisdiccion_iibb']) ? (int)$config['codigo_jurisdiccion_iibb'] : 913;
		return SircarRetencionesHelper::generarArchivo($retenciones, $tipoRegimen, $jurisdiccion);
	}

	static public function ctrExportarZip($fechaInicial, $fechaFinal, $idProveedor = null) {
		$contenido = self::ctrExportarTxt($fechaInicial, $fechaFinal, $idProveedor);
		$zip = new ZipArchive();
		$tmp = tempnam(sys_get_temp_dir(), 'sircar_');
		$zipPath = $tmp . '.zip';
		@unlink($tmp);
		if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
			return null;
		}
		$nombreTxt = 'retenciones_' . str_replace('-', '', $fechaInicial) . '_' . str_replace('-', '', $fechaFinal) . '.txt';
		$zip->addFromString($nombreTxt, $contenido);
		$zip->close();
		return ['path' => $zipPath, 'nombre' => str_replace('.txt', '.zip', $nombreTxt)];
	}
}
