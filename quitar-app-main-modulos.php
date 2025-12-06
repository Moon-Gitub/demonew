<?php
/**
 * Script para quitar <main class="app-main"> de todos los módulos
 * Ya que ahora se crea en plantilla.php
 */

$modulos = glob('vistas/modulos/*.php');
$actualizados = 0;

foreach ($modulos as $archivo) {
    $contenido = file_get_contents($archivo);
    $original = $contenido;
    
    // Quitar <!--begin::App Main--> y <main class="app-main">
    $contenido = preg_replace('/<!--begin::App Main-->\s*<main class="app-main">\s*/', '', $contenido);
    
    // Quitar </main> y <!--end::App Main-->
    $contenido = preg_replace('/\s*<\/main>\s*<!--end::App Main-->/', '', $contenido);
    
    // Solo guardar si hubo cambios
    if ($contenido !== $original) {
        if (file_put_contents($archivo, $contenido)) {
            $actualizados++;
            echo "✓ Actualizado: " . basename($archivo) . "\n";
        }
    }
}

echo "\n=== Resumen ===\n";
echo "Módulos actualizados: $actualizados\n";
echo "¡Proceso completado!\n";
?>

