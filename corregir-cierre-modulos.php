<?php
/**
 * Script para corregir el cierre de todos los módulos
 * Asegura que todos tengan el cierre correcto de app-content y container-fluid
 */

$modulos = glob('vistas/modulos/*.php');
$corregidos = 0;

foreach ($modulos as $archivo) {
    $contenido = file_get_contents($archivo);
    $original = $contenido;
    
    // Solo procesar módulos que tienen app-content-header
    if (strpos($contenido, 'app-content-header') === false) {
        continue;
    }
    
    // Verificar si ya tiene el cierre correcto
    if (strpos($contenido, '<!--end::App Content-->') !== false) {
        continue;
    }
    
    // Buscar el último </div> antes del cierre PHP o final del archivo
    // y agregar los cierres necesarios ANTES del último </div> o antes del ?>
    
    // Patrón 1: Si termina con </div> seguido de PHP
    if (preg_match('/<\/div>\s*<\?php\s*$/', $contenido)) {
        $contenido = preg_replace(
            '/<\/div>\s*<\?php\s*$/',
            '    </div>
    <!--end::Container-->
  </div>
  <!--end::App Content-->
<?php',
            $contenido
        );
    }
    // Patrón 2: Si termina con múltiples </div>
    elseif (preg_match('/<\/div>\s*<\/div>\s*<\/div>\s*$/', $contenido)) {
        // Ya tiene los cierres, solo agregar comentarios si faltan
        if (strpos($contenido, '<!--end::App Content-->') === false) {
            $contenido = preg_replace(
                '/(<\/div>\s*<\/div>\s*<\/div>\s*)$/',
                '    </div>
    <!--end::Container-->
  </div>
  <!--end::App Content-->',
                $contenido
            );
        }
    }
    // Patrón 3: Si termina con </div> simple
    elseif (preg_match('/<\/div>\s*$/', $contenido) && substr_count($contenido, '<div class="app-content">') > 0) {
        // Contar cuántos divs de app-content hay abiertos
        $abiertos = substr_count($contenido, '<div class="app-content">');
        $cerrados = substr_count($contenido, '</div>');
        
        // Si faltan cierres, agregarlos
        if ($cerrados < $abiertos + 2) { // +2 por container-fluid y app-content
            $contenido = rtrim($contenido);
            $contenido .= "\n    </div>\n    <!--end::Container-->\n  </div>\n  <!--end::App Content-->";
        }
    }
    
    if ($contenido !== $original) {
        if (file_put_contents($archivo, $contenido)) {
            $corregidos++;
            echo "✓ Corregido: " . basename($archivo) . "\n";
        }
    }
}

echo "\n=== Resumen ===\n";
echo "Módulos corregidos: $corregidos\n";
echo "¡Proceso completado!\n";
?>

