<?php
require_once "seguridad.ajax.php";
SeguridadAjax::inicializar();

require_once "../controladores/listas_precio.controlador.php";
require_once "../modelos/listas_precio.modelo.php";

if (isset($_POST["idListaPrecio"])) {
	$item = (int) $_POST["idListaPrecio"];
	$respuesta = ControladorListasPrecio::ctrMostrarPorId($item);
	echo json_encode($respuesta);
	exit;
}
