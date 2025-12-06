<?php
/**
 * Script para verificar y corregir el cierre de etiquetas en módulos
 */

$modulos = glob('vistas/modulos/*.php');
$corregidos = 0;
$errores = [];

foreach ($modulos as $archivo) {
    $contenido = file_get_contents($archivo);
    $original = $contenido;
    $cambios = false;
    
    // Verificar si tiene app-content-header
    if (strpos($contenido, 'app-content-header') !== false) {
        // Verificar si cierra correctamente app-content
        if (strpos($contenido, '<!--end::App Content-->') === false) {
            // Buscar el último </div> antes del final del archivo PHP
            // y agregar los cierres necesarios
            $contenido = preg_replace(
                '/(<\/div>\s*)(<\?php|$)/s',
                '$1    </div>
    <!--end::Container-->
  </div>
  <!--end::App Content-->
$2',
                $contenido,
                1
            );
            $cambios = true;
        }
    }
    
    if ($cambios && $contenido !== $original) {
        if (file_put_contents($archivo, $contenido)) {
            $corregidos++;
            echo "✓ Corregido: " . basename($archivo) . "\n";
        } else {
            $errores[] = basename($archivo);
        }
    }
}

echo "\n=== Resumen ===\n";
echo "Módulos corregidos: $corregidos\n";
if (!empty($errores)) {
    echo "Errores: " . implode(", ", $errores) . "\n";
}
echo "¡Proceso completado!\n";
?>

