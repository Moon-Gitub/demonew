<?php
/**
 * Script PHP para migrar ventas pendientes a productos_venta
 * 
 * Este script migra SOLO las ventas que:
 *   1. Tienen productos en JSON (campo productos)
 *   2. NO tienen productos en la tabla productos_venta
 * 
 * USO:
 *   php migrar-ventas-pendientes.php
 *   O ejecutar desde navegador: migrar-ventas-pendientes.php
 */

// Cargar autoload
require_once dirname(__DIR__) . '/../extensiones/vendor/autoload.php';

// Cargar .env si existe
$rutaBase = dirname(dirname(__DIR__));
$envPath = $rutaBase . '/.env';
if (file_exists($envPath) && class_exists('Dotenv\Dotenv')) {
    try {
        $dotenv = Dotenv\Dotenv::createImmutable($rutaBase);
        $dotenv->load();
    } catch (Exception $e) {
        echo "Advertencia: No se pudo cargar .env\n";
    }
}

// Cargar conexión
require_once $rutaBase . '/modelos/conexion.php';

// Configurar para ejecución desde navegador o CLI
$isCli = php_sapi_name() === 'cli';

if (!$isCli) {
    header('Content-Type: text/html; charset=utf-8');
    echo "<pre>";
}

echo "========================================\n";
echo "MIGRACIÓN DE VENTAS PENDIENTES\n";
echo "========================================\n\n";

try {
    $conexion = Conexion::conectar();
    
    // 1. Identificar ventas pendientes
    echo "1. Identificando ventas pendientes...\n";
    $sql = "SELECT COUNT(*) as total
            FROM ventas v
            WHERE v.productos IS NOT NULL 
            AND v.productos != '' 
            AND v.productos != '[]'
            AND JSON_VALID(v.productos) = 1
            AND NOT EXISTS (
                SELECT 1 FROM productos_venta pv WHERE pv.id_venta = v.id
            )";
    
    $stmt = $conexion->prepare($sql);
    $stmt->execute();
    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
    $ventasPendientes = $resultado['total'];
    
    echo "   Ventas pendientes: {$ventasPendientes}\n\n";
    
    if ($ventasPendientes == 0) {
        echo "✅ No hay ventas pendientes de migrar.\n";
        exit(0);
    }
    
    // 2. Obtener lista de ventas pendientes
    echo "2. Obteniendo lista de ventas...\n";
    $sql = "SELECT v.id, v.codigo, v.productos
            FROM ventas v
            WHERE v.productos IS NOT NULL 
            AND v.productos != '' 
            AND v.productos != '[]'
            AND JSON_VALID(v.productos) = 1
            AND NOT EXISTS (
                SELECT 1 FROM productos_venta pv WHERE pv.id_venta = v.id
            )
            ORDER BY v.id";
    
    $stmt = $conexion->prepare($sql);
    $stmt->execute();
    $ventas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "   Ventas a migrar: " . count($ventas) . "\n\n";
    
    // 3. Iniciar transacción
    $conexion->beginTransaction();
    
    $ventasMigradas = 0;
    $productosMigrados = 0;
    $errores = 0;
    $reporte = [];
    
    echo "3. Iniciando migración...\n";
    
    foreach ($ventas as $venta) {
        $idVenta = $venta['id'];
        $codigoVenta = $venta['codigo'];
        $productosJson = $venta['productos'];
        
        try {
            $productos = json_decode($productosJson, true);
            
            if (!is_array($productos) || empty($productos)) {
                $reporte[] = [
                    'id_venta' => $idVenta,
                    'codigo' => $codigoVenta,
                    'estado' => 'ERROR: JSON inválido o vacío',
                    'productos_migrados' => 0
                ];
                $errores++;
                continue;
            }
            
            $productosMigradosVenta = 0;
            
            foreach ($productos as $producto) {
                $idProducto = isset($producto['id']) ? intval($producto['id']) : 0;
                $cantidad = isset($producto['cantidad']) ? floatval($producto['cantidad']) : 0;
                $precioCompra = isset($producto['precio_compra']) ? floatval($producto['precio_compra']) : 0;
                
                // Obtener precio_venta (puede venir como "precio" o "precio_venta")
                $precioVenta = isset($producto['precio']) ? floatval($producto['precio']) : 0;
                if ($precioVenta == 0 && isset($producto['precio_venta'])) {
                    $precioVenta = floatval($producto['precio_venta']);
                }
                
                // Validar datos mínimos
                if ($idProducto > 0 && $cantidad > 0) {
                    // Verificar que el producto existe
                    $sqlCheck = "SELECT COUNT(*) as existe FROM productos WHERE id = :id_producto";
                    $stmtCheck = $conexion->prepare($sqlCheck);
                    $stmtCheck->bindParam(':id_producto', $idProducto, PDO::PARAM_INT);
                    $stmtCheck->execute();
                    $existe = $stmtCheck->fetch(PDO::FETCH_ASSOC)['existe'];
                    
                    if ($existe > 0) {
                        // Insertar en productos_venta
                        $sqlInsert = "INSERT INTO productos_venta (id_venta, id_producto, cantidad, precio_compra, precio_venta)
                                      VALUES (:id_venta, :id_producto, :cantidad, :precio_compra, :precio_venta)";
                        $stmtInsert = $conexion->prepare($sqlInsert);
                        $stmtInsert->bindParam(':id_venta', $idVenta, PDO::PARAM_INT);
                        $stmtInsert->bindParam(':id_producto', $idProducto, PDO::PARAM_INT);
                        $stmtInsert->bindParam(':cantidad', $cantidad, PDO::PARAM_STR);
                        $stmtInsert->bindParam(':precio_compra', $precioCompra, PDO::PARAM_STR);
                        $stmtInsert->bindParam(':precio_venta', $precioVenta, PDO::PARAM_STR);
                        $stmtInsert->execute();
                        
                        $productosMigradosVenta++;
                    }
                }
            }
            
            $reporte[] = [
                'id_venta' => $idVenta,
                'codigo' => $codigoVenta,
                'estado' => $productosMigradosVenta == count($productos) ? 'OK' : 'PARCIAL',
                'productos_json' => count($productos),
                'productos_migrados' => $productosMigradosVenta
            ];
            
            $ventasMigradas++;
            $productosMigrados += $productosMigradosVenta;
            
            if ($ventasMigradas % 100 == 0) {
                echo "   Procesadas: {$ventasMigradas} ventas...\n";
            }
            
        } catch (Exception $e) {
            $reporte[] = [
                'id_venta' => $idVenta,
                'codigo' => $codigoVenta,
                'estado' => 'ERROR: ' . $e->getMessage(),
                'productos_migrados' => 0
            ];
            $errores++;
        }
    }
    
    // 4. Confirmar transacción
    $conexion->commit();
    
    echo "\n4. Migración completada\n";
    echo "   Ventas migradas: {$ventasMigradas}\n";
    echo "   Productos migrados: {$productosMigrados}\n";
    echo "   Errores: {$errores}\n\n";
    
    // 5. Mostrar reporte de errores
    if ($errores > 0) {
        echo "5. Ventas con problemas:\n";
        foreach ($reporte as $item) {
            if (strpos($item['estado'], 'ERROR') !== false || strpos($item['estado'], 'PARCIAL') !== false) {
                echo "   Venta #{$item['codigo']} (ID: {$item['id_venta']}): {$item['estado']}\n";
            }
        }
        echo "\n";
    }
    
    // 6. Verificación final
    echo "6. Verificación final...\n";
    $sql = "SELECT COUNT(*) as total
            FROM ventas v
            WHERE v.productos IS NOT NULL 
            AND v.productos != '' 
            AND v.productos != '[]'
            AND JSON_VALID(v.productos) = 1
            AND NOT EXISTS (
                SELECT 1 FROM productos_venta pv WHERE pv.id_venta = v.id
            )";
    
    $stmt = $conexion->prepare($sql);
    $stmt->execute();
    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
    $ventasPendientesFinal = $resultado['total'];
    
    if ($ventasPendientesFinal == 0) {
        echo "   ✅ Todas las ventas han sido migradas correctamente.\n";
    } else {
        echo "   ⚠️  Aún quedan {$ventasPendientesFinal} ventas pendientes.\n";
    }
    
    echo "\n========================================\n";
    echo "MIGRACIÓN FINALIZADA\n";
    echo "========================================\n";
    
} catch (Exception $e) {
    if (isset($conexion) && $conexion->inTransaction()) {
        $conexion->rollBack();
    }
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
    exit(1);
}

if (!$isCli) {
    echo "</pre>";
}
