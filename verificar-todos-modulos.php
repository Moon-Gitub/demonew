<?php
/**
 * Script para verificar y corregir TODOS los módulos
 */

$modulos = glob('vistas/modulos/*.php');
$sinCierre = [];
$conCierre = [];

foreach ($modulos as $archivo) {
    $contenido = file_get_contents($archivo);
    $nombre = basename($archivo);
    
    // Solo verificar módulos que tienen app-content-header
    if (strpos($contenido, 'app-content-header') === false) {
        continue;
    }
    
    // Verificar si tiene el cierre correcto
    if (strpos($contenido, '<!--end::App Content-->') !== false) {
        $conCierre[] = $nombre;
    } else {
        $sinCierre[] = $nombre;
    }
}

echo "=== Módulos CON cierre correcto: " . count($conCierre) . " ===\n";
foreach ($conCierre as $mod) {
    echo "✓ $mod\n";
}

echo "\n=== Módulos SIN cierre (necesitan corrección): " . count($sinCierre) . " ===\n";
foreach ($sinCierre as $mod) {
    echo "✗ $mod\n";
}

if (!empty($sinCierre)) {
    echo "\n⚠️  Hay módulos que necesitan corrección manual\n";
}
?>

