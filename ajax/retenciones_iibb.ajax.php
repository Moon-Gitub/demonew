<?php

require_once "../controladores/retenciones_iibb.controlador.php";
require_once "../modelos/retenciones_iibb.modelo.php";

session_start();

if (!isset($_SESSION["iniciarSesion"]) || $_SESSION["iniciarSesion"] != "ok") {
	http_response_code(403);
	exit;
}

$accion = isset($_REQUEST["accion"]) ? $_REQUEST["accion"] : '';

if ($accion === 'listar') {
	$fechaInicial = isset($_POST["fechaInicial"]) ? $_POST["fechaInicial"] : date('Y-m-01');
	$fechaFinal = isset($_POST["fechaFinal"]) ? $_POST["fechaFinal"] : date('Y-m-d');
	$idProveedor = isset($_POST["idProveedor"]) && $_POST["idProveedor"] !== '' ? (int)$_POST["idProveedor"] : null;
	$lista = ControladorRetencionesIibb::ctrListarRetenciones($fechaInicial, $fechaFinal, $idProveedor);
	echo json_encode(["ok" => true, "data" => $lista]);
	exit;
}

if ($accion === 'exportar_txt' || $accion === 'exportar_zip') {
	$fechaInicial = isset($_GET["fechaInicial"]) ? $_GET["fechaInicial"] : date('Y-m-01');
	$fechaFinal = isset($_GET["fechaFinal"]) ? $_GET["fechaFinal"] : date('Y-m-d');
	$idProveedor = isset($_GET["idProveedor"]) && $_GET["idProveedor"] !== '' ? (int)$_GET["idProveedor"] : null;

	if ($accion === 'exportar_txt') {
		$contenido = ControladorRetencionesIibb::ctrExportarTxt($fechaInicial, $fechaFinal, $idProveedor);
		$nombre = 'retenciones_' . str_replace('-', '', $fechaInicial) . '_' . str_replace('-', '', $fechaFinal) . '.txt';
		header('Content-Type: text/plain; charset=UTF-8');
		header('Content-Disposition: attachment; filename="' . $nombre . '"');
		echo $contenido;
		exit;
	}

	$zipInfo = ControladorRetencionesIibb::ctrExportarZip($fechaInicial, $fechaFinal, $idProveedor);
	if (!$zipInfo || !file_exists($zipInfo['path'])) {
		http_response_code(500);
		echo 'Error al generar ZIP';
		exit;
	}
	header('Content-Type: application/zip');
	header('Content-Disposition: attachment; filename="' . $zipInfo['nombre'] . '"');
	header('Content-Length: ' . filesize($zipInfo['path']));
	readfile($zipInfo['path']);
	@unlink($zipInfo['path']);
	exit;
}

http_response_code(400);
echo json_encode(["ok" => false, "error" => "Acción no válida"]);
