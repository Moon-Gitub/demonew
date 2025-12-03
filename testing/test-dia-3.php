<?php
require_once 'simulador-base.php';

// Simulando día 3 del mes (Sin recargo - Inicio de mes)
$diaSimulado = 3;

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
