<?php
require_once 'simulador-base.php';
$diaSimulado = 28;
$datos = obtenerDatosEjemplo();
$subtotalMensuales = array_sum(array_column($datos['servicios_mensuales'], 'importe'));
$subtotalOtros = array_sum(array_column($datos['otros_cargos'], 'importe'));
$resultado = calcularRecargosPorDia($diaSimulado, $subtotalMensuales, $subtotalOtros);
mostrarResultadoSimulacion($diaSimulado, $datos, $resultado);
?>
