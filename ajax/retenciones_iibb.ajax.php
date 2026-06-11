<?php

$raiz = dirname(__DIR__);

require_once $raiz . "/extensiones/vendor/autoload.php";

if (file_exists($raiz . "/.env") && class_exists('Dotenv\Dotenv')) {
	Dotenv\Dotenv::createImmutable($raiz)->safeLoad();
}

if (file_exists($raiz . "/helpers.php")) {
	require_once $raiz . "/helpers.php";
}

if (session_status() === PHP_SESSION_NONE) {
	session_start();
}

if (!isset($_SESSION["iniciarSesion"]) || $_SESSION["iniciarSesion"] != "ok") {
	http_response_code(403);
	header('Content-Type: text/plain; charset=UTF-8');
	echo 'No autorizado';
	exit;
}

require_once $raiz . "/controladores/retenciones_iibb.controlador.php";
require_once $raiz . "/modelos/retenciones_iibb.modelo.php";

$accion = isset($_REQUEST["accion"]) ? $_REQUEST["accion"] : '';

try {

	if ($accion === 'listar') {
		$fechaInicial = isset($_POST["fechaInicial"]) ? $_POST["fechaInicial"] : date('Y-m-01');
		$fechaFinal = isset($_POST["fechaFinal"]) ? $_POST["fechaFinal"] : date('Y-m-d');
		$idProveedor = isset($_POST["idProveedor"]) && $_POST["idProveedor"] !== '' ? (int)$_POST["idProveedor"] : null;
		$lista = ControladorRetencionesIibb::ctrListarRetenciones($fechaInicial, $fechaFinal, $idProveedor);
		header('Content-Type: application/json; charset=UTF-8');
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

		if (!class_exists('ZipArchive')) {
			throw new RuntimeException('La extensión ZIP de PHP no está disponible en el servidor.');
		}

		$zipInfo = ControladorRetencionesIibb::ctrExportarZip($fechaInicial, $fechaFinal, $idProveedor);
		if (!$zipInfo || !file_exists($zipInfo['path'])) {
			throw new RuntimeException('No se pudo generar el archivo ZIP.');
		}
		header('Content-Type: application/zip');
		header('Content-Disposition: attachment; filename="' . $zipInfo['nombre'] . '"');
		header('Content-Length: ' . filesize($zipInfo['path']));
		readfile($zipInfo['path']);
		@unlink($zipInfo['path']);
		exit;
	}

	http_response_code(400);
	header('Content-Type: application/json; charset=UTF-8');
	echo json_encode(["ok" => false, "error" => "Acción no válida"]);

} catch (Throwable $e) {
	error_log('retenciones_iibb.ajax.php: ' . $e->getMessage());
	http_response_code(500);
	header('Content-Type: text/plain; charset=UTF-8');
	echo 'Error al exportar retenciones: ' . $e->getMessage();
}
