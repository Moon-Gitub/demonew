<?php
/**
 * Script de migración de AdminLTE 2.4.0 a AdminLTE 4.0.0-rc4
 * Refactoriza clases y atributos automáticamente
 */

$directorio = 'vistas/modulos';
$archivos = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($directorio),
    RecursiveIteratorIterator::SELF_FIRST
);

$cambios = [
    // Clases principales
    'class="box"' => 'class="card"',
    'class="box ' => 'class="card ',
    'class="box-' => 'class="card-',
    'box-header' => 'card-header',
    'box-body' => 'card-body',
    'box-footer' => 'card-footer',
    'box-title' => 'card-title',
    'box-tools' => 'card-tools',
    'btn-box-tool' => 'btn-tool',
    
    // Estructura
    'content-wrapper' => 'app-content',
    'main-sidebar' => 'app-sidebar',
    'main-header' => 'app-header',
    'sidebar-menu' => 'sidebar-nav',
    'treeview' => 'nav-item nav-treeview',
    'treeview-menu' => 'nav-treeview',
    
    // Data attributes Bootstrap 5
    'data-toggle="dropdown"' => 'data-bs-toggle="dropdown"',
    'data-toggle="modal"' => 'data-bs-toggle="modal"',
    'data-toggle="collapse"' => 'data-bs-toggle="collapse"',
    'data-toggle="tab"' => 'data-bs-toggle="tab"',
    'data-toggle="tooltip"' => 'data-bs-toggle="tooltip"',
    'data-toggle="popover"' => 'data-bs-toggle="popover"',
    'data-target=' => 'data-bs-target=',
    'data-dismiss="modal"' => 'data-bs-dismiss="modal"',
    'data-dismiss="alert"' => 'data-bs-dismiss="alert"',
    'data-widget="collapse"' => 'data-bs-toggle="collapse"',
    'data-widget="remove"' => 'data-bs-dismiss="card"',
    
    // Iconos Font Awesome → Bootstrap Icons (algunos comunes)
    'fa fa-home' => 'bi bi-house',
    'fa fa-dashboard' => 'bi bi-speedometer2',
    'fa fa-building-o' => 'bi bi-building',
    'fa fa-user' => 'bi bi-person',
    'fa fa-users' => 'bi bi-people',
    'fa fa-product-hunt' => 'bi bi-box-seam',
    'fa fa-print' => 'bi bi-printer',
    'fa fa-dollar' => 'bi bi-cash-coin',
    'fa fa-plus' => 'bi bi-plus-circle',
    'fa fa-line-chart' => 'bi bi-graph-up',
    'fa fa-shopping-cart' => 'bi bi-cart',
    'fa fa-calendar' => 'bi bi-calendar',
    'fa fa-times' => 'bi bi-x',
    'fa fa-minus' => 'bi bi-dash',
    'fa fa-angle-left' => 'bi bi-chevron-left',
    'fa fa-angle-right' => 'bi bi-chevron-right',
    'fa fa-circle-o' => 'bi bi-circle',
    
    // Pull right/left (Bootstrap 5 usa float-end/float-start)
    'pull-right' => 'float-end',
    'pull-left' => 'float-start',
    
    // Colores de box
    'box-primary' => 'card-primary',
    'box-success' => 'card-success',
    'box-info' => 'card-info',
    'box-warning' => 'card-warning',
    'box-danger' => 'card-danger',
];

$archivosProcesados = 0;
$cambiosTotales = 0;

foreach ($archivos as $archivo) {
    if ($archivo->isFile() && $archivo->getExtension() === 'php') {
        $ruta = $archivo->getPathname();
        $contenido = file_get_contents($ruta);
        $contenidoOriginal = $contenido;
        $cambiosEnArchivo = 0;
        
        foreach ($cambios as $buscar => $reemplazar) {
            $contenidoNuevo = str_replace($buscar, $reemplazar, $contenido);
            if ($contenidoNuevo !== $contenido) {
                $cambiosEnArchivo += substr_count($contenido, $buscar);
                $contenido = $contenidoNuevo;
            }
        }
        
        if ($contenido !== $contenidoOriginal) {
            file_put_contents($ruta, $contenido);
            $archivosProcesados++;
            $cambiosTotales += $cambiosEnArchivo;
            echo "✅ Procesado: $ruta ($cambiosEnArchivo cambios)\n";
        }
    }
}

echo "\n";
echo "═══════════════════════════════════════\n";
echo "✅ Migración completada\n";
echo "═══════════════════════════════════════\n";
echo "Archivos procesados: $archivosProcesados\n";
echo "Total de cambios: $cambiosTotales\n";
echo "\n";
echo "⚠️  IMPORTANTE: Revisa manualmente los archivos para:\n";
echo "   1. Ajustar estructura del sidebar/menú\n";
echo "   2. Verificar que los iconos sean correctos\n";
echo "   3. Probar funcionalidad de modales y dropdowns\n";
echo "\n";

