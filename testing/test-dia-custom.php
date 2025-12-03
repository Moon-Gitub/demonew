<?php
require_once 'simulador-base.php';

// Obtener día desde GET o usar día 15 por defecto
$diaSimulado = isset($_GET['dia']) ? intval($_GET['dia']) : 15;

// Validar que el día esté entre 1 y 31
if ($diaSimulado < 1) $diaSimulado = 1;
if ($diaSimulado > 31) $diaSimulado = 31;

// Obtener datos de ejemplo
$datos = obtenerDatosEjemplo();

// Calcular totales
$subtotalMensuales = array_sum(array_column($datos['servicios_mensuales'], 'importe'));
$subtotalOtros = array_sum(array_column($datos['otros_cargos'], 'importe'));

// Calcular recargos
$resultado = calcularRecargosPorDia($diaSimulado, $subtotalMensuales, $subtotalOtros);

// Mostrar resultado
mostrarResultadoSimulacion($diaSimulado, $datos, $resultado);
?>
