<?php
require_once "seguridad.ajax.php";
SeguridadAjax::inicializar();

require_once "../controladores/balanzas_formatos.controlador.php";
require_once "../modelos/balanzas_formatos.modelo.php";

if (isset($_POST["idBalanzaFormato"])) {
	$id = (int) $_POST["idBalanzaFormato"];
	$respuesta = ControladorBalanzasFormatos::ctrMostrarPorId($id);
	echo json_encode($respuesta);
	exit;
}

