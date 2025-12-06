<?php
/**
 * Script para corregir módulos sin cierre de app-content
 */

$modulosSinCierre = [
    'cajas-cierre.php',
    'categorias.php',
    'clientes-cuenta-saldos.php',
    'clientes_cuenta.php',
    'crear-compra.php',
    'editar-pedido.php',
    'impresion-precios.php',
    'pedidos-nuevos.php',
    'productos-importar-excel.php',
    'productos-importar-excel2.php',
    'proveedores.php',
    'usuarios.php',
    'ventas-productos.php'
];

$baseDir = __DIR__ . '/vistas/modulos/';
$corregidos = 0;

foreach ($modulosSinCierre as $modulo) {
    $archivo = $baseDir . $modulo;
    
    if (!file_exists($archivo)) {
        echo "⚠️  No existe: $modulo\n";
        continue;
    }
    
    $contenido = file_get_contents($archivo);
    $original = $contenido;
    
    // Si ya tiene el cierre, saltar
    if (strpos($contenido, '<!--end::App Content-->') !== false) {
        echo "- Ya tiene cierre: $modulo\n";
        continue;
    }
    
    // Buscar el final del archivo y agregar cierres antes del último PHP o al final
    // Patrón 1: Termina con ?>
    if (preg_match('/\?>\s*$/', $contenido)) {
        $contenido = preg_replace(
            '/\?>\s*$/',
            '    </div>
    <!--end::Container-->
  </div>
  <!--end::App Content-->
?>',
            $contenido
        );
    }
    // Patrón 2: Termina con </div> seguido de espacios
    elseif (preg_match('/<\/div>\s*$/', $contenido)) {
        // Contar divs abiertos vs cerrados
        $abiertos = substr_count($contenido, '<div');
        $cerrados = substr_count($contenido, '</div>');
        
        // Si faltan cierres (debería haber al menos 2 más: container-fluid y app-content)
        if ($cerrados < $abiertos) {
            $contenido = rtrim($contenido);
            // Agregar los cierres necesarios
            $faltantes = ($abiertos - $cerrados) + 1; // +1 para asegurar
            for ($i = 0; $i < min($faltantes, 3); $i++) {
                if ($i == 0) {
                    $contenido .= "\n    </div>";
                } elseif ($i == 1) {
                    $contenido .= "\n    <!--end::Container-->";
                } elseif ($i == 2) {
                    $contenido .= "\n  </div>";
                    $contenido .= "\n  <!--end::App Content-->";
                }
            }
        }
    }
    // Patrón 3: Termina con múltiples </div>
    else {
        // Agregar al final
        $contenido = rtrim($contenido);
        if (!strpos($contenido, '<!--end::App Content-->')) {
            $contenido .= "\n    </div>\n    <!--end::Container-->\n  </div>\n  <!--end::App Content-->";
        }
    }
    
    if ($contenido !== $original) {
        if (file_put_contents($archivo, $contenido)) {
            $corregidos++;
            echo "✓ Corregido: $modulo\n";
        } else {
            echo "✗ Error al escribir: $modulo\n";
        }
    } else {
        echo "- Sin cambios necesarios: $modulo\n";
    }
}

echo "\n=== Resumen ===\n";
echo "Módulos corregidos: $corregidos\n";
echo "¡Proceso completado!\n";
?>

