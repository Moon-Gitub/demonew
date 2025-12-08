# 🛠️ Consultas SQL para Herramientas del Asistente Virtual

Este documento contiene todas las consultas SQL utilizadas por las herramientas del asistente virtual.

## 📊 Tabla: ventas

### Estructura Principal
```sql
CREATE TABLE `ventas` (
  `id` int(11) NOT NULL,
  `uuid` varchar(34) NOT NULL,
  `codigo` int(11) NOT NULL,
  `cbte_tipo` int(11) DEFAULT NULL,
  `id_cliente` int(11) NOT NULL,
  `id_vendedor` int(11) NOT NULL,
  `productos` text NOT NULL,
  `neto` decimal(10,2) NOT NULL DEFAULT 0.00,
  `neto_gravado` decimal(11,2) DEFAULT NULL,
  `base_imponible_0` decimal(10,2) DEFAULT NULL,
  `base_imponible_2` decimal(10,2) DEFAULT NULL,
  `base_imponible_5` decimal(10,2) DEFAULT NULL,
  `base_imponible_10` decimal(10,2) DEFAULT NULL,
  `base_imponible_21` decimal(10,2) DEFAULT NULL,
  `base_imponible_27` decimal(10,2) DEFAULT NULL,
  `iva_2` decimal(10,2) DEFAULT NULL,
  `iva_5` decimal(10,2) DEFAULT NULL,
  `iva_10` decimal(10,2) DEFAULT NULL,
  `iva_21` decimal(10,2) DEFAULT NULL,
  `iva_27` decimal(10,2) DEFAULT NULL,
  `impuesto` decimal(10,2) NOT NULL DEFAULT 0.00,
  `impuesto_detalle` text DEFAULT NULL,
  `total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `metodo_pago` text NOT NULL,
  `estado` int(11) DEFAULT 0,
  `observaciones_vta` text DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `fecha` timestamp NULL DEFAULT NULL,
  `concepto` int(11) DEFAULT NULL,
  `pto_vta` int(11) DEFAULT NULL
);
```

**Nota**: Los tipos de comprobante 3, 8, 13, 203, 208, 213, 999 son anulaciones o notas de crédito. Se excluyen de las consultas de ventas.

## 📦 Tabla: productos

### Estructura Principal
```sql
CREATE TABLE `productos` (
  `id` int(11) NOT NULL,
  `id_categoria` int(11) DEFAULT NULL,
  `codigo` varchar(255) NOT NULL DEFAULT '',
  `id_proveedor` int(11) DEFAULT NULL,
  `descripcion` varchar(255) NOT NULL DEFAULT '',
  `imagen` text DEFAULT NULL,
  `stock` decimal(11,2) DEFAULT 0.00,
  `deposito` decimal(10,2) NOT NULL,
  `stock_medio` decimal(11,2) DEFAULT 0.00,
  `stock_bajo` decimal(11,2) DEFAULT 0.00,
  `precio_compra` decimal(11,2) DEFAULT 0.00,
  `precio_compra_dolar` decimal(11,2) DEFAULT 0.00,
  `margen_ganancia` decimal(11,2) DEFAULT 0.00,
  `precio_venta_neto` decimal(11,2) DEFAULT 0.00,
  `tipo_iva` decimal(11,2) DEFAULT 0.00,
  `precio_venta` decimal(11,2) DEFAULT 0.00,
  `precio_venta_mayorista` decimal(11,2) DEFAULT NULL,
  `ventas` int(11) DEFAULT 0,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `nombre_usuario` varchar(50) DEFAULT NULL,
  `cambio_desde` varchar(50) NOT NULL
);
```

## 👥 Tabla: clientes

### Estructura Principal
```sql
CREATE TABLE `clientes` (
  `id` int(11) NOT NULL,
  `nombre` text NOT NULL,
  `dominio` varchar(100) DEFAULT NULL,
  `tipo_documento` int(11) DEFAULT NULL,
  `documento` varchar(255) DEFAULT NULL,
  `condicion_iva` int(11) DEFAULT NULL,
  `email` text DEFAULT NULL,
  `telefono` text DEFAULT NULL,
  `direccion` text DEFAULT NULL,
  `fecha_nacimiento` date DEFAULT NULL,
  `compras` int(11) DEFAULT NULL,
  `ultima_compra` datetime DEFAULT NULL,
  `mensual` int(12) NOT NULL,
  `fecha` timestamp NULL DEFAULT current_timestamp(),
  `observaciones` text DEFAULT NULL,
  `estado_cuenta` int(10) NOT NULL,
  `estado_bloqueo` int(10) NOT NULL
);
```

## 🏢 Tabla: proveedores

### Estructura Principal
```sql
CREATE TABLE `proveedores` (
  `id` int(11) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `inicio_actividades` date DEFAULT NULL,
  `tipo_documento` int(11) DEFAULT NULL,
  `cuit` varchar(255) DEFAULT NULL,
  `ingresos_brutos` varchar(255) DEFAULT NULL,
  `localidad` varchar(255) DEFAULT NULL,
  `telefono` varchar(255) DEFAULT NULL,
  `direccion` text DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `observaciones` text DEFAULT NULL
);
```

## 💰 Tabla: cajas

### Estructura Principal
```sql
CREATE TABLE `cajas` (
  `id` int(11) NOT NULL,
  `fecha` datetime DEFAULT NULL,
  `id_usuario` int(11) DEFAULT NULL,
  `punto_venta` int(11) DEFAULT NULL,
  `tipo` int(11) DEFAULT NULL,
  `monto` decimal(10,2) DEFAULT 0.00,
  `medio_pago` varchar(255) DEFAULT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `codigo_venta` varchar(255) DEFAULT NULL,
  `id_venta` int(11) DEFAULT NULL,
  `id_cliente_proveedor` int(11) DEFAULT NULL,
  `observaciones` text DEFAULT NULL
);
```

## 📝 Consultas SQL por Herramienta

### 1. Consultar Ventas - Día Específico
```sql
SELECT 
  COUNT(*) as cantidad_ventas,
  SUM(total) as total_ventas,
  SUM(neto) as total_neto,
  SUM(impuesto) as total_impuestos,
  AVG(total) as promedio_venta
FROM ventas 
WHERE DATE(fecha) = ? 
  AND cbte_tipo NOT IN (3, 8, 13, 203, 208, 213, 999)
```

### 2. Consultar Ventas - Rango de Fechas
```sql
SELECT 
  COUNT(*) as cantidad_ventas,
  SUM(total) as total_ventas,
  SUM(neto) as total_neto,
  SUM(impuesto) as total_impuestos,
  AVG(total) as promedio_venta,
  MIN(fecha) as fecha_inicio,
  MAX(fecha) as fecha_fin
FROM ventas 
WHERE fecha BETWEEN ? AND ? 
  AND cbte_tipo NOT IN (3, 8, 13, 203, 208, 213, 999)
```

### 3. Consultar Ventas - Hoy
```sql
SELECT 
  COUNT(*) as cantidad_ventas,
  SUM(total) as total_ventas,
  SUM(neto) as total_neto,
  SUM(impuesto) as total_impuestos,
  AVG(total) as promedio_venta
FROM ventas 
WHERE DATE(fecha) = CURDATE() 
  AND cbte_tipo NOT IN (3, 8, 13, 203, 208, 213, 999)
```

### 4. Consultar Ventas - Por Cliente
```sql
SELECT 
  c.nombre as cliente,
  COUNT(v.id) as cantidad_ventas,
  SUM(v.total) as total_ventas
FROM ventas v
LEFT JOIN clientes c ON v.id_cliente = c.id
WHERE v.cbte_tipo NOT IN (3, 8, 13, 203, 208, 213, 999)
GROUP BY v.id_cliente, c.nombre
ORDER BY total_ventas DESC
LIMIT 10
```

### 5. Consultar Productos - Por Código
```sql
SELECT 
  id, codigo, descripcion, stock, precio_compra, precio_venta,
  stock_medio, stock_bajo, id_categoria, id_proveedor
FROM productos 
WHERE codigo = ?
```

### 6. Consultar Productos - Por Descripción
```sql
SELECT 
  id, codigo, descripcion, stock, precio_compra, precio_venta,
  stock_medio, stock_bajo, id_categoria, id_proveedor
FROM productos 
WHERE descripcion LIKE ?
LIMIT 20
```

### 7. Consultar Stock - Bajo
```sql
SELECT 
  id, codigo, descripcion, stock, stock_medio, stock_bajo,
  precio_compra, precio_venta,
  ROUND((IF(stock<0,0,stock)) * precio_compra, 2) as valor_inventario
FROM productos 
WHERE (IF(stock<0,0,stock)) <= stock_bajo
ORDER BY stock ASC
```

### 8. Consultar Stock - Medio
```sql
SELECT 
  id, codigo, descripcion, stock, stock_medio, stock_bajo,
  precio_compra, precio_venta
FROM productos 
WHERE (IF(stock<0,0,stock)) <= stock_medio 
  AND (IF(stock<0,0,stock)) > stock_bajo
ORDER BY stock ASC
```

### 9. Sugerencias de Compras
```sql
SELECT 
  p.id,
  p.codigo,
  p.descripcion,
  p.stock,
  p.stock_medio,
  p.stock_bajo,
  p.precio_compra,
  pr.nombre as proveedor,
  CASE 
    WHEN (IF(p.stock<0,0,p.stock)) <= p.stock_bajo THEN 'URGENTE'
    WHEN (IF(p.stock<0,0,p.stock)) <= p.stock_medio THEN 'RECOMENDADO'
    ELSE 'NORMAL'
  END as prioridad,
  GREATEST(p.stock_medio - IF(p.stock<0,0,p.stock), 0) as cantidad_sugerida
FROM productos p
LEFT JOIN proveedores pr ON p.id_proveedor = pr.id
WHERE (IF(p.stock<0,0,p.stock)) <= p.stock_medio
ORDER BY 
  CASE 
    WHEN (IF(p.stock<0,0,p.stock)) <= p.stock_bajo THEN 1
    WHEN (IF(p.stock<0,0,p.stock)) <= p.stock_medio THEN 2
    ELSE 3
  END,
  p.stock ASC
LIMIT 50
```

### 10. Consultar Clientes - Por Nombre
```sql
SELECT 
  id, nombre, documento, email, telefono, direccion,
  compras, ultima_compra, estado_cuenta
FROM clientes 
WHERE nombre LIKE ?
LIMIT 20
```

### 11. Consultar Clientes - Por Documento
```sql
SELECT 
  id, nombre, documento, email, telefono, direccion,
  compras, ultima_compra, estado_cuenta
FROM clientes 
WHERE documento = ?
```

### 12. Estadísticas - Ventas del Día
```sql
SELECT 
  COUNT(*) as total_ventas,
  SUM(total) as total_facturado,
  AVG(total) as promedio_venta,
  MIN(total) as venta_minima,
  MAX(total) as venta_maxima,
  COUNT(DISTINCT id_cliente) as clientes_unicos
FROM ventas 
WHERE DATE(fecha) = CURDATE() 
  AND cbte_tipo NOT IN (3, 8, 13, 203, 208, 213, 999)
```

### 13. Estadísticas - Ventas del Mes
```sql
SELECT 
  COUNT(*) as total_ventas,
  SUM(total) as total_facturado,
  AVG(total) as promedio_venta,
  COUNT(DISTINCT id_cliente) as clientes_unicos
FROM ventas 
WHERE MONTH(fecha) = MONTH(CURDATE()) 
  AND YEAR(fecha) = YEAR(CURDATE())
  AND cbte_tipo NOT IN (3, 8, 13, 203, 208, 213, 999)
```

### 14. Estadísticas - Productos
```sql
SELECT 
  COUNT(*) as total_productos,
  COUNT(CASE WHEN (IF(stock<0,0,stock)) <= stock_bajo THEN 1 END) as productos_stock_bajo,
  COUNT(CASE WHEN (IF(stock<0,0,stock)) <= stock_medio AND (IF(stock<0,0,stock)) > stock_bajo THEN 1 END) as productos_stock_medio,
  SUM(CASE WHEN stock > 0 THEN stock * precio_compra ELSE 0 END) as valor_inventario,
  SUM(CASE WHEN stock > 0 THEN stock * precio_venta ELSE 0 END) as valor_venta_potencial
FROM productos
```

### 15. Estadísticas - Clientes
```sql
SELECT 
  COUNT(*) as total_clientes,
  COUNT(CASE WHEN compras > 0 THEN 1 END) as clientes_activos,
  COUNT(CASE WHEN estado_cuenta != 0 THEN 1 END) as clientes_con_deuda
FROM clientes
```

## 🔍 Notas Importantes

1. **Stock Negativo**: Se usa `IF(stock<0,0,stock)` para tratar stock negativo como 0
2. **Tipos de Comprobante**: Se excluyen anulaciones (3, 8, 13, 203, 208, 213, 999) de las consultas de ventas
3. **Límites**: Las consultas de listado tienen límites (LIMIT) para evitar respuestas muy grandes
4. **Formato de Fechas**: Usar formato YYYY-MM-DD para fechas en parámetros
5. **Seguridad**: Solo se permiten consultas SELECT. No se permiten INSERT, UPDATE, DELETE

## 🎯 Optimizaciones

- Agregar índices en `ventas.fecha` para consultas por fecha
- Agregar índices en `productos.codigo` y `productos.descripcion` para búsquedas
- Agregar índices en `clientes.nombre` y `clientes.documento` para búsquedas
- Considerar caché para consultas frecuentes

