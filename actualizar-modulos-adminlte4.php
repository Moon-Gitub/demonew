<?php
/**
 * Script para actualizar todos los módulos a la estructura correcta de AdminLTE 4
 * Ejecutar desde la raíz del proyecto: php actualizar-modulos-adminlte4.php
 */

$modulos = [
    'ventas.php',
    'productos.php',
    'clientes.php',
    'proveedores.php',
    'usuarios.php',
    'categorias.php',
    'compras.php',
    'cajas.php',
    'cajas-cierre.php',
    'cajas-cajero.php',
    'pedidos-nuevos.php',
    'pedidos-validados.php',
    'pedidos-generar-movimiento.php',
    'crear-venta.php',
    'crear-venta-caja.php',
    'editar-venta.php',
    'crear-compra.php',
    'ingreso.php',
    'editar-ingreso.php',
    'editar-pedido.php',
    'presupuestos.php',
    'presupuesto-venta.php',
    'crear-presupuesto-caja.php',
    'reportes.php',
    'productos-importar-excel.php',
    'productos-importar-excel2.php',
    'productos-stock-bajo.php',
    'productos-stock-medio.php',
    'productos-stock-valorizado.php',
    'productos-historial.php',
    'ventas-productos.php',
    'ventas-rentabilidad.php',
    'ventas-categoria-proveedor-informe.php',
    'libro-iva-ventas.php',
    'clientes-cuenta-deuda.php',
    'clientes-cuenta-saldos.php',
    'clientes_cuenta.php',
    'proveedores-saldo.php',
    'proveedores-pagos.php',
    'proveedores-cuenta-saldos.php',
    'proveedores_cuenta.php',
    'parametros-facturacion.php',
    'factura-manual.php',
    'impresion-precios.php',
    'precios-qr.php',
    '404.php'
];

$baseDir = __DIR__ . '/vistas/modulos/';
$actualizados = 0;
$errores = [];

foreach ($modulos as $modulo) {
    $archivo = $baseDir . $modulo;
    
    if (!file_exists($archivo)) {
        $errores[] = "Archivo no encontrado: $modulo";
        continue;
    }
    
    $contenido = file_get_contents($archivo);
    $original = $contenido;
    
    // Patrón 1: Reemplazar estructura antigua app-content sin app-main
    // <div class="app-content"> ... <section class="content-header"> ... <section class="content">
    if (preg_match('/<div class="app-content">\s*<section class="content-header">/s', $contenido)) {
        $contenido = preg_replace(
            '/<div class="app-content">\s*<section class="content-header">\s*<h1>\s*(.*?)\s*<\/h1>\s*<ol class="breadcrumb">\s*(.*?)\s*<\/ol>\s*<\/section>\s*<section class="content">/s',
            '<!--begin::App Main-->
<main class="app-main">
  <!--begin::App Content Header-->
  <div class="app-content-header">
    <div class="container-fluid">
      <div class="row">
        <div class="col-sm-6">
          <h3 class="mb-0">$1</h3>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-end mb-0">
$2
          </ol>
        </div>
      </div>
    </div>
  </div>
  <!--end::App Content Header-->
  <!--begin::App Content-->
  <div class="app-content">
    <div class="container-fluid">',
            $contenido
        );
        
        // Cerrar correctamente
        $contenido = preg_replace(
            '/<\/section>\s*<\/div>\s*$/s',
            '    </div>
    <!--end::Container-->
  </div>
  <!--end::App Content-->
</main>
<!--end::App Main-->',
            $contenido
        );
    }
    
    // Patrón 2: Reemplazar input-group-addon por input-group-text (Bootstrap 5)
    $contenido = str_replace('input-group-addon', 'input-group-text', $contenido);
    
    // Patrón 3: Reemplazar iconos Font Awesome genéricos por Bootstrap Icons apropiados
    $reemplazosIconos = [
        '<i class="fa fa-th"></i>' => '<i class="bi bi-pencil-square"></i>',
        '<i class="fa fa-building"></i>' => '<i class="bi bi-building"></i>',
        '<i class="fa fa-user"></i>' => '<i class="bi bi-person"></i>',
        '<i class="fa fa-shopping-cart"></i>' => '<i class="bi bi-cart"></i>',
        '<i class="fa fa-dollar"></i>' => '<i class="bi bi-currency-dollar"></i>',
    ];
    
    foreach ($reemplazosIconos as $buscar => $reemplazar) {
        $contenido = str_replace($buscar, $reemplazar, $contenido);
    }
    
    // Solo guardar si hubo cambios
    if ($contenido !== $original) {
        if (file_put_contents($archivo, $contenido)) {
            $actualizados++;
            echo "✓ Actualizado: $modulo\n";
        } else {
            $errores[] = "Error al escribir: $modulo";
        }
    } else {
        echo "- Sin cambios: $modulo\n";
    }
}

echo "\n=== Resumen ===\n";
echo "Módulos actualizados: $actualizados\n";
echo "Errores: " . count($errores) . "\n";

if (!empty($errores)) {
    echo "\nErrores:\n";
    foreach ($errores as $error) {
        echo "  - $error\n";
    }
}

echo "\n¡Proceso completado!\n";
?>

