# ⚡ Optimización y Performance

## Mejoras de Rendimiento del Sistema

---

## 1. Implementar Sistema de Caché

### 🔍 Problema

- Consultas repetitivas a la base de datos
- Sin caché para datos que cambian poco
- Rendimiento lento en listados grandes

### ✅ Solución

**Archivo**: `src/Utils/Cache.php`

```php
<?php

namespace App\Utils;

class Cache {
    
    private static $cacheDir = __DIR__ . '/../../storage/cache/';
    private static $defaultTTL = 3600; // 1 hora
    
    /**
     * Obtener valor del caché
     */
    public static function get($key, $default = null) {
        $file = self::getCacheFile($key);
        
        if (!file_exists($file)) {
            return $default;
        }
        
        $data = unserialize(file_get_contents($file));
        
        // Verificar si expiró
        if ($data['expires'] < time()) {
            self::forget($key);
            return $default;
        }
        
        return $data['value'];
    }
    
    /**
     * Guardar en caché
     */
    public static function put($key, $value, $ttl = null) {
        $ttl = $ttl ?? self::$defaultTTL;
        
        $data = [
            'value' => $value,
            'expires' => time() + $ttl
        ];
        
        self::ensureCacheDirectoryExists();
        
        $file = self::getCacheFile($key);
        file_put_contents($file, serialize($data));
        
        return true;
    }
    
    /**
     * Recordar: Obtener o almacenar
     */
    public static function remember($key, $ttl, $callback) {
        $value = self::get($key);
        
        if ($value !== null) {
            return $value;
        }
        
        $value = $callback();
        self::put($key, $value, $ttl);
        
        return $value;
    }
    
    /**
     * Olvidar caché
     */
    public static function forget($key) {
        $file = self::getCacheFile($key);
        
        if (file_exists($file)) {
            unlink($file);
        }
    }
    
    /**
     * Limpiar todo el caché
     */
    public static function flush() {
        $files = glob(self::$cacheDir . '*');
        
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }
    
    /**
     * Obtener ruta del archivo de caché
     */
    private static function getCacheFile($key) {
        return self::$cacheDir . md5($key) . '.cache';
    }
    
    /**
     * Asegurar que existe el directorio
     */
    private static function ensureCacheDirectoryExists() {
        if (!file_exists(self::$cacheDir)) {
            mkdir(self::$cacheDir, 0755, true);
        }
    }
}
```

#### Usar caché en modelos

**Ejemplo**: `src/Models/ProductosModel.php`

```php
<?php

namespace App\Models;

use App\Utils\Cache;
use App\Utils\Conexion;

class ProductosModel {
    
    /**
     * Obtener productos con caché
     */
    public static function obtenerProductos($categoria = null) {
        
        $cacheKey = $categoria ? "productos_categoria_$categoria" : "productos_todos";
        
        return Cache::remember($cacheKey, 1800, function() use ($categoria) {
            $pdo = Conexion::conectar();
            
            if ($categoria) {
                $stmt = $pdo->prepare("
                    SELECT * FROM productos 
                    WHERE id_categoria = :categoria 
                    ORDER BY descripcion
                ");
                $stmt->bindParam(':categoria', $categoria, PDO::PARAM_INT);
            } else {
                $stmt = $pdo->prepare("SELECT * FROM productos ORDER BY descripcion");
            }
            
            $stmt->execute();
            return $stmt->fetchAll();
        });
    }
    
    /**
     * Invalidar caché al actualizar
     */
    public static function actualizarProducto($id, $datos) {
        // Actualizar en BD
        $resultado = /* ... lógica de actualización ... */;
        
        if ($resultado) {
            // Invalidar cachés relacionados
            Cache::forget('productos_todos');
            Cache::forget("productos_categoria_{$datos['id_categoria']}");
            Cache::forget("producto_$id");
        }
        
        return $resultado;
    }
}
```

---

## 2. Optimizar JavaScript

### 🔍 Problema

- `ventas.js` tiene 2396 líneas
- Código sin modularizar
- Sin minificación
- Sin bundling

### ✅ Solución

#### Dividir en módulos

**Archivo**: `vistas/js/modules/ventas/tabla.js`

```javascript
/**
 * Módulo: Tabla de Ventas
 */
const TablaVentas = (function() {
    
    let tablaInstance = null;
    
    function inicializar() {
        tablaInstance = $('#tablaVentas').DataTable({
            "ajax": "ajax/datatable-ventas.ajax.php",
            "deferRender": true,
            "retrieve": true,
            "processing": true,
            "language": GL_DATATABLE_LENGUAJE,
            'columnDefs': [
                {
                    "targets": [3,5],
                    "className": "text-center"
                }
            ]
        });
        
        bindEvents();
    }
    
    function bindEvents() {
        $("#tablaVentas tbody").on("click", "button.agregarProducto", handleAgregarProducto);
        $("#tablaVentas tbody").on("click", "button.editarVenta", handleEditarVenta);
        $("#tablaVentas tbody").on("click", "button.eliminarVenta", handleEliminarVenta);
    }
    
    function handleAgregarProducto() {
        const idProducto = $(this).attr("idProducto");
        VentasProductos.agregar(idProducto);
    }
    
    function handleEditarVenta() {
        const idVenta = $(this).attr("idVenta");
        VentasFormulario.editar(idVenta);
    }
    
    function handleEliminarVenta() {
        const idVenta = $(this).attr("idVenta");
        VentasFormulario.eliminar(idVenta);
    }
    
    function recargar() {
        if (tablaInstance) {
            tablaInstance.ajax.reload(null, false);
        }
    }
    
    return {
        inicializar,
        recargar
    };
    
})();
```

**Archivo**: `vistas/js/modules/ventas/productos.js`

```javascript
/**
 * Módulo: Productos en Venta
 */
const VentasProductos = (function() {
    
    const carrito = [];
    
    function agregar(idProducto, cantidad = 1) {
        const stockSucursal = $("#sucursalVendedor").val();
        const tipoPrecio = $('#radioPrecio').val();
        
        if (!stockSucursal || !tipoPrecio) {
            Utils.mostrarError("Debe definir sucursal y lista de precio");
            return;
        }
        
        obtenerProducto(idProducto)
            .then(producto => {
                agregarAlCarrito(producto, cantidad);
                actualizarVista();
                calcularTotal();
            })
            .catch(error => {
                Utils.mostrarError("Error al agregar producto");
                console.error(error);
            });
    }
    
    function obtenerProducto(idProducto) {
        return new Promise((resolve, reject) => {
            $.ajax({
                url: "ajax/productos.ajax.php",
                method: "POST",
                data: { codigoProducto: idProducto },
                dataType: "json",
                success: resolve,
                error: reject
            });
        });
    }
    
    function agregarAlCarrito(producto, cantidad) {
        const existe = carrito.find(item => item.id === producto.id);
        
        if (existe) {
            existe.cantidad += cantidad;
        } else {
            carrito.push({
                id: producto.id,
                codigo: producto.codigo,
                descripcion: producto.descripcion,
                precio: producto.precio_venta,
                cantidad: cantidad,
                stock: producto.stock
            });
        }
    }
    
    function remover(idProducto) {
        const index = carrito.findIndex(item => item.id === idProducto);
        if (index > -1) {
            carrito.splice(index, 1);
            actualizarVista();
            calcularTotal();
        }
    }
    
    function actualizarCantidad(idProducto, cantidad) {
        const producto = carrito.find(item => item.id === idProducto);
        if (producto) {
            producto.cantidad = cantidad;
            calcularTotal();
        }
    }
    
    function obtenerCarrito() {
        return [...carrito]; // Retornar copia
    }
    
    function limpiar() {
        carrito.length = 0;
        actualizarVista();
    }
    
    function actualizarVista() {
        const html = carrito.map(item => `
            <tr data-id="${item.id}">
                <td>${item.codigo}</td>
                <td>${item.descripcion}</td>
                <td>
                    <input type="number" 
                           class="form-control cantidad-producto" 
                           value="${item.cantidad}" 
                           min="1" 
                           max="${item.stock}"
                           data-id="${item.id}">
                </td>
                <td>$ ${Utils.formatearPrecio(item.precio)}</td>
                <td>$ ${Utils.formatearPrecio(item.precio * item.cantidad)}</td>
                <td>
                    <button class="btn btn-danger btn-sm eliminar-producto" data-id="${item.id}">
                        <i class="fa fa-trash"></i>
                    </button>
                </td>
            </tr>
        `).join('');
        
        $('#tbody-productos-venta').html(html);
        
        // Re-bindear eventos
        $('.cantidad-producto').on('change', function() {
            const id = $(this).data('id');
            const cantidad = parseInt($(this).val());
            actualizarCantidad(id, cantidad);
        });
        
        $('.eliminar-producto').on('click', function() {
            const id = $(this).data('id');
            remover(id);
        });
    }
    
    function calcularTotal() {
        const total = carrito.reduce((sum, item) => {
            return sum + (item.precio * item.cantidad);
        }, 0);
        
        $('#total-venta').text('$ ' + Utils.formatearPrecio(total));
        
        return total;
    }
    
    return {
        agregar,
        remover,
        actualizarCantidad,
        obtenerCarrito,
        limpiar,
        calcularTotal
    };
    
})();
```

**Archivo**: `vistas/js/modules/utils.js`

```javascript
/**
 * Utilidades comunes
 */
const Utils = (function() {
    
    function mostrarError(mensaje) {
        swal({
            type: 'error',
            title: 'Error',
            text: mensaje,
            toast: true,
            timer: 3000,
            position: 'top',
            confirmButtonText: '¡Cerrar!'
        });
    }
    
    function mostrarExito(mensaje) {
        swal({
            type: 'success',
            title: 'Éxito',
            text: mensaje,
            toast: true,
            timer: 3000,
            position: 'top',
            showConfirmButton: false
        });
    }
    
    function confirmar(mensaje) {
        return swal({
            type: 'warning',
            title: '¿Está seguro?',
            text: mensaje,
            showCancelButton: true,
            confirmButtonText: 'Sí, continuar',
            cancelButtonText: 'Cancelar'
        });
    }
    
    function formatearPrecio(numero) {
        return parseFloat(numero).toLocaleString('es-AR', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }
    
    function formatearFecha(fecha) {
        return moment(fecha).format('DD/MM/YYYY');
    }
    
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
    
    return {
        mostrarError,
        mostrarExito,
        confirmar,
        formatearPrecio,
        formatearFecha,
        debounce
    };
    
})();
```

**Archivo**: `vistas/js/ventas.js` - VERSIÓN MODULAR

```javascript
/**
 * Archivo principal de ventas
 * Carga e inicializa módulos
 */

$(document).ready(function() {
    
    // Inicializar módulos
    TablaVentas.inicializar();
    VentasFormulario.inicializar();
    
    // Event listeners globales
    $('#btn-nueva-venta').on('click', VentasFormulario.nueva);
    $('#btn-guardar-venta').on('click', VentasFormulario.guardar);
    
});
```

#### Configurar bundler (opcional pero recomendado)

**Archivo**: `package.json`

```json
{
  "name": "erp-pos-frontend",
  "version": "1.0.0",
  "scripts": {
    "dev": "webpack --mode development --watch",
    "build": "webpack --mode production",
    "minify": "uglifyjs vistas/js/**/*.js -c -m -o vistas/dist/js/app.min.js"
  },
  "devDependencies": {
    "webpack": "^5.88.0",
    "webpack-cli": "^5.1.0",
    "uglify-js": "^3.17.0"
  }
}
```

---

## 3. Optimizar Consultas SQL

### 🔍 Problemas

- Consultas N+1
- Sin índices adecuados
- JOINs innecesarios

### ✅ Solución

#### Agregar índices faltantes

**Archivo**: `mejoras/scripts/optimizar-indices.sql`

```sql
-- Índices para mejorar performance

-- Tabla productos
ALTER TABLE `productos` ADD INDEX `idx_categoria` (`id_categoria`);
ALTER TABLE `productos` ADD INDEX `idx_proveedor` (`id_proveedor`);
ALTER TABLE `productos` ADD INDEX `idx_codigo` (`codigo`);
ALTER TABLE `productos` ADD INDEX `idx_descripcion` (`descripcion`(50));

-- Tabla ventas
ALTER TABLE `ventas` ADD INDEX `idx_cliente` (`id_cliente`);
ALTER TABLE `ventas` ADD INDEX `idx_vendedor` (`id_vendedor`);
ALTER TABLE `ventas` ADD INDEX `idx_fecha` (`fecha`);
ALTER TABLE `ventas` ADD INDEX `idx_estado` (`estado`);
ALTER TABLE `ventas` ADD INDEX `idx_pto_vta_codigo` (`pto_vta`, `codigo`);

-- Tabla compras
ALTER TABLE `compras` ADD INDEX `idx_proveedor` (`id_proveedor`);
ALTER TABLE `compras` ADD INDEX `idx_fecha` (`fecha`);
ALTER TABLE `compras` ADD INDEX `idx_estado` (`estado`);

-- Tabla clientes
ALTER TABLE `clientes` ADD INDEX `idx_documento` (`documento`);
ALTER TABLE `clientes` ADD INDEX `idx_nombre` (`nombre`(50));

-- Tabla proveedores
ALTER TABLE `proveedores` ADD INDEX `idx_cuit` (`cuit`);
ALTER TABLE `proveedores` ADD INDEX `idx_nombre` (`nombre`(50));

-- Tabla cajas
ALTER TABLE `cajas` ADD INDEX `idx_fecha` (`fecha`);
ALTER TABLE `cajas` ADD INDEX `idx_usuario` (`id_usuario`);
ALTER TABLE `cajas` ADD INDEX `idx_punto_venta` (`punto_venta`);
ALTER TABLE `cajas` ADD INDEX `idx_venta` (`id_venta`);

-- Tabla clientes_cuenta_corriente
ALTER TABLE `clientes_cuenta_corriente` ADD INDEX `idx_cliente` (`id_cliente`);
ALTER TABLE `clientes_cuenta_corriente` ADD INDEX `idx_fecha` (`fecha`);
ALTER TABLE `clientes_cuenta_corriente` ADD INDEX `idx_venta` (`id_venta`);

-- Tabla proveedores_cuenta_corriente
ALTER TABLE `proveedores_cuenta_corriente` ADD INDEX `idx_proveedor` (`id_proveedor`);
ALTER TABLE `proveedores_cuenta_corriente` ADD INDEX `idx_fecha` (`fecha_movimiento`);
ALTER TABLE `proveedores_cuenta_corriente` ADD INDEX `idx_compra` (`id_compra`);
```

#### Usar EXPLAIN para analizar queries

```sql
-- Verificar plan de ejecución
EXPLAIN SELECT p.*, c.categoria 
FROM productos p
LEFT JOIN categorias c ON p.id_categoria = c.id
WHERE p.stock < p.stock_bajo;

-- Buscar queries lentas
SET GLOBAL slow_query_log = 'ON';
SET GLOBAL long_query_time = 2; -- Registrar queries > 2 segundos
```

#### Evitar consultas N+1

**❌ MALO - N+1:**
```php
// 1 query para productos
$productos = $pdo->query("SELECT * FROM productos")->fetchAll();

// N queries (una por cada producto)
foreach ($productos as $producto) {
    $categoria = $pdo->query("SELECT * FROM categorias WHERE id = {$producto['id_categoria']}")->fetch();
    $producto['categoria'] = $categoria;
}
```

**✅ BUENO - JOIN:**
```php
// 1 query total
$productos = $pdo->query("
    SELECT p.*, c.categoria as categoria_nombre
    FROM productos p
    LEFT JOIN categorias c ON p.id_categoria = c.id
")->fetchAll();
```

---

## 4. Optimizar DataTables

### 🔍 Problema

DataTables carga todos los datos en memoria

### ✅ Solución: Server-Side Processing

**Archivo**: `ajax/datatable-productos-optimizado.ajax.php`

```php
<?php

require_once "../vendor/autoload.php";
require_once "seguridad.ajax.php";

use App\Models\Conexion;
use App\Utils\ValidadorSQL;

SeguridadAjax::inicializar(false);

// Parámetros de DataTables
$draw = $_GET['draw'] ?? 1;
$start = $_GET['start'] ?? 0;
$length = $_GET['length'] ?? 10;
$searchValue = $_GET['search']['value'] ?? '';
$orderColumn = $_GET['order'][0]['column'] ?? 0;
$orderDir = $_GET['order'][0]['dir'] ?? 'asc';

// Columnas disponibles
$columns = ['id', 'codigo', 'descripcion', 'stock', 'precio_venta', 'categoria'];

$orderColumnName = $columns[$orderColumn] ?? 'id';
$orderDir = strtoupper($orderDir) === 'DESC' ? 'DESC' : 'ASC';

try {
    $pdo = Conexion::conectar();
    
    // Query base
    $baseQuery = "
        FROM productos p
        LEFT JOIN categorias c ON p.id_categoria = c.id
    ";
    
    // Filtro de búsqueda
    $whereClause = "";
    $params = [];
    
    if (!empty($searchValue)) {
        $whereClause = "WHERE (
            p.codigo LIKE :search OR
            p.descripcion LIKE :search OR
            c.categoria LIKE :search
        )";
        $params[':search'] = "%$searchValue%";
    }
    
    // Contar total de registros
    $stmtTotal = $pdo->prepare("SELECT COUNT(*) as total $baseQuery");
    $stmtTotal->execute();
    $totalRecords = $stmtTotal->fetch()['total'];
    
    // Contar registros filtrados
    $stmtFiltered = $pdo->prepare("SELECT COUNT(*) as total $baseQuery $whereClause");
    $stmtFiltered->execute($params);
    $filteredRecords = $stmtFiltered->fetch()['total'];
    
    // Obtener datos
    $query = "
        SELECT 
            p.id,
            p.codigo,
            p.descripcion,
            p.stock,
            p.precio_venta,
            c.categoria as categoria_nombre,
            p.imagen
        $baseQuery
        $whereClause
        ORDER BY $orderColumnName $orderDir
        LIMIT :start, :length
    ";
    
    $stmt = $pdo->prepare($query);
    
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    
    $stmt->bindValue(':start', (int)$start, PDO::PARAM_INT);
    $stmt->bindValue(':length', (int)$length, PDO::PARAM_INT);
    
    $stmt->execute();
    $data = $stmt->fetchAll();
    
    // Formatear respuesta
    $response = [
        "draw" => intval($draw),
        "recordsTotal" => intval($totalRecords),
        "recordsFiltered" => intval($filteredRecords),
        "data" => $data
    ];
    
    header('Content-Type: application/json');
    echo json_encode($response);
    
} catch (Exception $e) {
    error_log("Error en datatable productos: " . $e->getMessage());
    
    header('Content-Type: application/json');
    echo json_encode([
        "draw" => intval($draw),
        "recordsTotal" => 0,
        "recordsFiltered" => 0,
        "data" => [],
        "error" => "Error al cargar datos"
    ]);
}
```

**JavaScript actualizado:**

```javascript
$('#tablaProductos').DataTable({
    "processing": true,
    "serverSide": true,
    "ajax": {
        "url": "ajax/datatable-productos-optimizado.ajax.php",
        "type": "GET"
    },
    "columns": [
        { "data": "codigo" },
        { "data": "descripcion" },
        { "data": "categoria_nombre" },
        { "data": "stock" },
        { 
            "data": "precio_venta",
            "render": function(data) {
                return '$ ' + parseFloat(data).toFixed(2);
            }
        },
        {
            "data": null,
            "orderable": false,
            "render": function(data) {
                return `
                    <button class="btn btn-warning btn-sm editar-producto" data-id="${data.id}">
                        <i class="fa fa-edit"></i>
                    </button>
                    <button class="btn btn-danger btn-sm eliminar-producto" data-id="${data.id}">
                        <i class="fa fa-trash"></i>
                    </button>
                `;
            }
        }
    ],
    "language": GL_DATATABLE_LENGUAJE
});
```

---

## 5. Lazy Loading de Imágenes

**Archivo**: `vistas/js/modules/lazy-load.js`

```javascript
/**
 * Lazy loading de imágenes
 */
const LazyLoad = (function() {
    
    function inicializar() {
        if ('IntersectionObserver' in window) {
            const imageObserver = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        img.src = img.dataset.src;
                        img.classList.remove('lazy');
                        imageObserver.unobserve(img);
                    }
                });
            });
            
            const images = document.querySelectorAll('img.lazy');
            images.forEach(img => imageObserver.observe(img));
            
        } else {
            // Fallback para navegadores antiguos
            const images = document.querySelectorAll('img.lazy');
            images.forEach(img => {
                img.src = img.dataset.src;
                img.classList.remove('lazy');
            });
        }
    }
    
    return { inicializar };
    
})();

// Inicializar al cargar el DOM
document.addEventListener('DOMContentLoaded', LazyLoad.inicializar);
```

**HTML:**
```html
<!-- En lugar de: -->
<img src="vistas/img/productos/producto1.jpg" alt="Producto 1">

<!-- Usar: -->
<img class="lazy" data-src="vistas/img/productos/producto1.jpg" src="vistas/img/placeholder.jpg" alt="Producto 1">
```

---

## ✅ Checklist de Implementación

- [ ] Implementar sistema de caché
- [ ] Usar caché en modelos frecuentes
- [ ] Modularizar JavaScript de ventas
- [ ] Modularizar JavaScript de productos
- [ ] Crear módulos de utilidades comunes
- [ ] Configurar bundler (opcional)
- [ ] Agregar índices a la base de datos
- [ ] Analizar queries con EXPLAIN
- [ ] Refactorizar consultas N+1
- [ ] Implementar server-side en DataTables
- [ ] Implementar lazy loading de imágenes
- [ ] Minificar CSS y JS
- [ ] Configurar compresión gzip
- [ ] Probar performance con herramientas

---

## 🧪 Herramientas de Testing

1. **GTmetrix**: Analizar tiempos de carga
2. **Chrome DevTools**: Network y Performance tabs
3. **MySQL Slow Query Log**: Identificar queries lentas
4. **New Relic / Blackfire**: Profiling de PHP

---

**Tiempo estimado**: 2-3 semanas  
**Prioridad**: 🟡 MEDIA  
**Anterior**: [03-arquitectura.md](03-arquitectura.md)  
**Siguiente**: [05-modernizacion.md](05-modernizacion.md)

