<?php
/**
 * Script para analizar el rendimiento de la aplicación
 * 
 * Uso: Acceder desde el navegador o ejecutar desde línea de comandos
 * Ejemplo: php analizar-rendimiento.php
 */

// Iniciar medición de tiempo total
$tiempoInicio = microtime(true);
$memoriaInicio = memory_get_usage();

// Configurar zona horaria
date_default_timezone_set('America/Argentina/Mendoza');

// Cargar configuración
require_once "modelos/conexion.php";
require_once "controladores/ventas.controlador.php";
require_once "controladores/empresa.controlador.php";
require_once "controladores/clientes.controlador.php";

$resultados = [];

// ============================================
// 1. ANÁLISIS DE CONSULTAS A BASE DE DATOS
// ============================================
echo "=== ANÁLISIS DE RENDIMIENTO ===\n\n";

// Activar logging de consultas SQL
$db = new Conexion;
$con = $db->getDatosConexion();

// Simular carga de página de ventas
echo "1. ANALIZANDO CONSULTAS DE VENTAS...\n";
echo str_repeat("-", 50) . "\n";

$tiempoVentas = microtime(true);
$fechaInicial = date('Y-m-d') . ' 00:00';
$fechaFinal = date('Y-m-d') . ' 23:59';

// Consulta principal de ventas
$tiempoConsulta1 = microtime(true);
$ventas = ControladorVentas::ctrRangoFechasVentas($fechaInicial, $fechaFinal);
$tiempoConsulta1 = microtime(true) - $tiempoConsulta1;

echo "   - Consulta principal de ventas: " . number_format($tiempoConsulta1 * 1000, 2) . " ms\n";
echo "   - Registros obtenidos: " . count($ventas) . "\n";

// Simular consultas N+1 (como en el código actual)
$tiempoN1 = microtime(true);
$consultasN1 = 0;
$tiempoTotalN1 = 0;

if (is_array($ventas) && count($ventas) > 0) {
    // Simular las primeras 10 para no demorar mucho
    $muestra = array_slice($ventas, 0, min(10, count($ventas)));
    
    foreach ($muestra as $venta) {
        // Consulta de facturación (N+1)
        $tiempoInicioConsulta = microtime(true);
        $facturada = ControladorVentas::ctrVentaFacturadaDatos($venta['id']);
        $tiempoConsulta = microtime(true) - $tiempoInicioConsulta;
        $tiempoTotalN1 += $tiempoConsulta;
        $consultasN1++;
        
        // Consulta de empresa (N+1)
        $tiempoInicioConsulta = microtime(true);
        $empresa = ControladorEmpresa::ctrMostrarempresa('id', $venta['id_empresa']);
        $tiempoConsulta = microtime(true) - $tiempoInicioConsulta;
        $tiempoTotalN1 += $tiempoConsulta;
        $consultasN1++;
        
        // Consulta de cliente (N+1)
        $tiempoInicioConsulta = microtime(true);
        $cliente = ControladorClientes::ctrMostrarClientes('id', $venta['id_cliente']);
        $tiempoConsulta = microtime(true) - $tiempoInicioConsulta;
        $tiempoTotalN1 += $tiempoConsulta;
        $consultasN1++;
    }
    
    // Proyectar para todas las ventas
    $factor = count($ventas) / count($muestra);
    $tiempoProyectadoN1 = $tiempoTotalN1 * $factor;
    $consultasProyectadasN1 = $consultasN1 * $factor;
}

$tiempoN1 = microtime(true) - $tiempoN1;

echo "\n2. PROBLEMA N+1 DETECTADO:\n";
echo str_repeat("-", 50) . "\n";
echo "   - Consultas N+1 en muestra (10 ventas): " . $consultasN1 . " consultas\n";
echo "   - Tiempo total N+1 (muestra): " . number_format($tiempoTotalN1 * 1000, 2) . " ms\n";
if (count($ventas) > 10) {
    echo "   - PROYECCIÓN para " . count($ventas) . " ventas:\n";
    echo "     * Consultas estimadas: " . number_format($consultasProyectadasN1, 0) . "\n";
    echo "     * Tiempo estimado: " . number_format($tiempoProyectadoN1 * 1000, 2) . " ms (" . number_format($tiempoProyectadoN1, 2) . " segundos)\n";
}

// ============================================
// 3. ANÁLISIS DE MEMORIA
// ============================================
$memoriaFin = memory_get_usage();
$memoriaUsada = $memoriaFin - $memoriaInicio;
$memoriaPeak = memory_get_peak_usage();

echo "\n3. USO DE MEMORIA:\n";
echo str_repeat("-", 50) . "\n";
echo "   - Memoria usada: " . number_format($memoriaUsada / 1024 / 1024, 2) . " MB\n";
echo "   - Memoria pico: " . number_format($memoriaPeak / 1024 / 1024, 2) . " MB\n";

// ============================================
// 4. TIEMPO TOTAL
// ============================================
$tiempoTotal = microtime(true) - $tiempoInicio;

echo "\n4. TIEMPO TOTAL DE EJECUCIÓN:\n";
echo str_repeat("-", 50) . "\n";
echo "   - Tiempo total: " . number_format($tiempoTotal * 1000, 2) . " ms (" . number_format($tiempoTotal, 2) . " segundos)\n";

// ============================================
// 5. RECOMENDACIONES
// ============================================
echo "\n5. RECOMENDACIONES:\n";
echo str_repeat("-", 50) . "\n";

if ($tiempoTotal > 2) {
    echo "   ⚠️  La página tarda más de 2 segundos en cargar\n";
}

if (isset($tiempoProyectadoN1) && $tiempoProyectadoN1 > 1) {
    echo "   ⚠️  PROBLEMA CRÍTICO: Consultas N+1 detectadas\n";
    echo "      Solución: Usar JOINs en la consulta principal\n";
    echo "      Ejemplo: SELECT v.*, e.titular, c.nombre, vf.nro_cbte\n";
    echo "               FROM ventas v\n";
    echo "               LEFT JOIN empresa e ON v.id_empresa = e.id\n";
    echo "               LEFT JOIN clientes c ON v.id_cliente = c.id\n";
    echo "               LEFT JOIN ventas_factura vf ON v.id = vf.id_venta\n";
}

if ($memoriaUsada > 50 * 1024 * 1024) {
    echo "   ⚠️  Uso de memoria alto (>50MB)\n";
    echo "      Considera limitar la cantidad de registros cargados\n";
}

if (count($ventas) > 1000) {
    echo "   ⚠️  Muchos registros cargados (" . count($ventas) . ")\n";
    echo "      Considera implementar paginación del lado del servidor\n";
}

echo "\n" . str_repeat("=", 50) . "\n";
echo "Análisis completado\n";
