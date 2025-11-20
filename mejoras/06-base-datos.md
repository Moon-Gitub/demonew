# 🗄️ Análisis y Optimización de Base de Datos

## Revisión de Estructura y Recomendaciones

---

## 1. Resumen de Tablas

### Tablas Principales

| Tabla | Registros Estimados | Motor | Observaciones |
|-------|-------------------|-------|---------------|
| usuarios | < 100 | InnoDB | ✅ Bien estructurada |
| productos | 1,000 - 50,000 | InnoDB | ⚠️ Necesita índices |
| productos_historial | 50,000+ | MyISAM | ⚠️ Cambiar a InnoDB |
| categorias | < 100 | InnoDB | ✅ OK |
| clientes | 500 - 10,000 | InnoDB | ✅ OK |
| proveedores | < 500 | InnoDB | ⚠️ Charset mixto |
| ventas | 10,000 - 500,000 | InnoDB | ⚠️ JSON en texto |
| compras | 5,000 - 100,000 | MyISAM | ❌ Cambiar a InnoDB |
| cajas | 20,000 - 500,000 | MyISAM | ❌ Cambiar a InnoDB |
| caja_cierres | < 5,000 | MyISAM | ❌ Cambiar a InnoDB |
| presupuestos | 1,000 - 50,000 | InnoDB | ✅ OK |
| pedidos | 1,000 - 50,000 | MyISAM | ❌ Cambiar a InnoDB |

---

## 2. Problemas Detectados

### 🔴 Críticos

#### 2.1. Uso de MyISAM

**Tablas afectadas:**
- compras
- pedidos
- cajas
- caja_cierres
- productos_historial
- proveedores_cuenta_corriente
- ventas_factura

**Problemas:**
- No soporta transacciones
- No tiene integridad referencial
- Bloqueos a nivel de tabla (peor concurrencia)
- Mayor riesgo de corrupción

**Solución:**

```sql
-- Script de migración a InnoDB
-- Archivo: mejoras/scripts/migrar-a-innodb.sql

-- Respaldar antes de ejecutar!

ALTER TABLE compras ENGINE=InnoDB;
ALTER TABLE pedidos ENGINE=InnoDB;
ALTER TABLE cajas ENGINE=InnoDB;
ALTER TABLE caja_cierres ENGINE=InnoDB;
ALTER TABLE productos_historial ENGINE=InnoDB;
ALTER TABLE proveedores_cuenta_corriente ENGINE=InnoDB;
ALTER TABLE ventas_factura ENGINE=InnoDB;

-- Verificar
SELECT TABLE_NAME, ENGINE 
FROM information_schema.TABLES 
WHERE TABLE_SCHEMA = 'demo_db' 
AND ENGINE = 'MyISAM';
```

#### 2.2. Charset Inconsistente

**Problema detectado:**
- Algunas tablas usan `utf8mb3` (deprecated)
- Otras usan `latin1`
- Mezcla de collations

**Solución:**

```sql
-- Archivo: mejoras/scripts/normalizar-charset.sql

-- Cambiar charset de la base de datos
ALTER DATABASE demo_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Cambiar todas las tablas a utf8mb4
ALTER TABLE cajas CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE caja_cierres CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE categorias CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE clientes CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE clientes_cuenta_corriente CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE compras CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE empresa CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE pedidos CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE presupuestos CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE productos CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE productos_historial CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE proveedores CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE proveedores_cuenta_corriente CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE usuarios CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE ventas CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE ventas_factura CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Verificar
SELECT TABLE_NAME, TABLE_COLLATION 
FROM information_schema.TABLES 
WHERE TABLE_SCHEMA = 'demo_db'
ORDER BY TABLE_NAME;
```

### 🟠 Alta Prioridad

#### 2.3. Falta de Relaciones Foráneas

**Problema:**
No hay `FOREIGN KEYS` definidas, lo que permite:
- Datos huérfanos
- Referencias a registros inexistentes
- Dificulta mantener integridad

**Solución:**

```sql
-- Archivo: mejoras/scripts/agregar-foreign-keys.sql

-- IMPORTANTE: Limpiar datos huérfanos ANTES de crear las FK

-- Productos
ALTER TABLE productos
    ADD CONSTRAINT fk_productos_categoria 
    FOREIGN KEY (id_categoria) REFERENCES categorias(id) 
    ON DELETE SET NULL ON UPDATE CASCADE,
    
    ADD CONSTRAINT fk_productos_proveedor 
    FOREIGN KEY (id_proveedor) REFERENCES proveedores(id) 
    ON DELETE SET NULL ON UPDATE CASCADE;

-- Ventas
ALTER TABLE ventas
    ADD CONSTRAINT fk_ventas_cliente 
    FOREIGN KEY (id_cliente) REFERENCES clientes(id) 
    ON DELETE RESTRICT ON UPDATE CASCADE,
    
    ADD CONSTRAINT fk_ventas_vendedor 
    FOREIGN KEY (id_vendedor) REFERENCES usuarios(id) 
    ON DELETE RESTRICT ON UPDATE CASCADE;

-- Compras
ALTER TABLE compras
    ADD CONSTRAINT fk_compras_proveedor 
    FOREIGN KEY (id_proveedor) REFERENCES proveedores(id) 
    ON DELETE RESTRICT ON UPDATE CASCADE;

-- Cajas
ALTER TABLE cajas
    ADD CONSTRAINT fk_cajas_usuario 
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id) 
    ON DELETE RESTRICT ON UPDATE CASCADE,
    
    ADD CONSTRAINT fk_cajas_venta 
    FOREIGN KEY (id_venta) REFERENCES ventas(id) 
    ON DELETE SET NULL ON UPDATE CASCADE;

-- Presupuestos
ALTER TABLE presupuestos
    ADD CONSTRAINT fk_presupuestos_cliente 
    FOREIGN KEY (id_cliente) REFERENCES clientes(id) 
    ON DELETE RESTRICT ON UPDATE CASCADE,
    
    ADD CONSTRAINT fk_presupuestos_vendedor 
    FOREIGN KEY (id_vendedor) REFERENCES usuarios(id) 
    ON DELETE RESTRICT ON UPDATE CASCADE;

-- Clientes Cuenta Corriente
ALTER TABLE clientes_cuenta_corriente
    ADD CONSTRAINT fk_ccc_cliente 
    FOREIGN KEY (id_cliente) REFERENCES clientes(id) 
    ON DELETE CASCADE ON UPDATE CASCADE,
    
    ADD CONSTRAINT fk_ccc_venta 
    FOREIGN KEY (id_venta) REFERENCES ventas(id) 
    ON DELETE SET NULL ON UPDATE CASCADE;

-- Proveedores Cuenta Corriente
ALTER TABLE proveedores_cuenta_corriente
    ADD CONSTRAINT fk_pcc_proveedor 
    FOREIGN KEY (id_proveedor) REFERENCES proveedores(id) 
    ON DELETE CASCADE ON UPDATE CASCADE,
    
    ADD CONSTRAINT fk_pcc_compra 
    FOREIGN KEY (id_compra) REFERENCES compras(id) 
    ON DELETE SET NULL ON UPDATE CASCADE;

-- Productos Historial
ALTER TABLE productos_historial
    ADD CONSTRAINT fk_historial_producto 
    FOREIGN KEY (id) REFERENCES productos(id) 
    ON DELETE CASCADE ON UPDATE CASCADE;

-- Ventas Factura
ALTER TABLE ventas_factura
    ADD CONSTRAINT fk_venta_factura 
    FOREIGN KEY (id_venta) REFERENCES ventas(id) 
    ON DELETE CASCADE ON UPDATE CASCADE;
```

**Script para encontrar datos huérfanos:**

```sql
-- Archivo: mejoras/scripts/encontrar-huerfanos.sql

-- Productos sin categoría válida
SELECT p.id, p.codigo, p.descripcion, p.id_categoria
FROM productos p
LEFT JOIN categorias c ON p.id_categoria = c.id
WHERE p.id_categoria IS NOT NULL AND c.id IS NULL;

-- Productos sin proveedor válido
SELECT p.id, p.codigo, p.descripcion, p.id_proveedor
FROM productos p
LEFT JOIN proveedores pr ON p.id_proveedor = pr.id
WHERE p.id_proveedor IS NOT NULL AND pr.id IS NULL;

-- Ventas sin cliente válido
SELECT v.id, v.codigo, v.id_cliente
FROM ventas v
LEFT JOIN clientes c ON v.id_cliente = c.id
WHERE c.id IS NULL;

-- Ventas sin vendedor válido
SELECT v.id, v.codigo, v.id_vendedor
FROM ventas v
LEFT JOIN usuarios u ON v.id_vendedor = u.id
WHERE u.id IS NULL;

-- Compras sin proveedor válido
SELECT co.id, co.codigo, co.id_proveedor
FROM compras co
LEFT JOIN proveedores p ON co.id_proveedor = p.id
WHERE co.id_proveedor IS NOT NULL AND p.id IS NULL;
```

#### 2.4. Campos JSON Almacenados como TEXT

**Problema:**
- `productos` en ventas, compras, presupuestos
- `pedido_afip`, `respuesta_afip` en ventas
- `detalle_ingresos`, `detalle_egresos` en caja_cierres

**Impacto:**
- No se puede buscar/filtrar eficientemente
- No hay validación de estructura
- Espacio desperdiciado

**Solución (requiere MySQL 5.7+):**

```sql
-- Archivo: mejoras/scripts/migrar-json-fields.sql

-- Respaldar datos primero!

-- Ventas
ALTER TABLE ventas 
    MODIFY COLUMN productos JSON,
    MODIFY COLUMN pedido_afip JSON,
    MODIFY COLUMN respuesta_afip JSON;

-- Compras
ALTER TABLE compras 
    MODIFY COLUMN productos JSON;

-- Presupuestos
ALTER TABLE presupuestos 
    MODIFY COLUMN productos JSON;

-- Caja Cierres
ALTER TABLE caja_cierres 
    MODIFY COLUMN detalle_ingresos JSON,
    MODIFY COLUMN detalle_egresos JSON,
    MODIFY COLUMN detalle_ingresos_manual JSON,
    MODIFY COLUMN detalle_egresos_manual JSON,
    MODIFY COLUMN diferencias JSON;

-- Ahora se pueden hacer queries como:
-- SELECT * FROM ventas WHERE JSON_EXTRACT(productos, '$[0].id') = 123;
-- SELECT JSON_EXTRACT(productos, '$[*].cantidad') FROM ventas WHERE id = 1;
```

### 🟡 Media Prioridad

#### 2.5. Índices Faltantes

**Archivo**: `mejoras/scripts/agregar-indices.sql`

```sql
-- Productos (tabla más consultada)
CREATE INDEX idx_productos_categoria ON productos(id_categoria);
CREATE INDEX idx_productos_proveedor ON productos(id_proveedor);
CREATE INDEX idx_productos_codigo ON productos(codigo);
CREATE INDEX idx_productos_descripcion ON productos(descripcion(100));
CREATE INDEX idx_productos_stock ON productos(stock);
CREATE INDEX idx_productos_precio ON productos(precio_venta);
CREATE FULLTEXT INDEX ft_productos_descripcion ON productos(descripcion);

-- Ventas
CREATE INDEX idx_ventas_cliente ON ventas(id_cliente);
CREATE INDEX idx_ventas_vendedor ON ventas(id_vendedor);
CREATE INDEX idx_ventas_fecha ON ventas(fecha);
CREATE INDEX idx_ventas_estado ON ventas(estado);
CREATE INDEX idx_ventas_pto_vta_codigo ON ventas(pto_vta, codigo);
CREATE INDEX idx_ventas_cbte_tipo ON ventas(cbte_tipo);
CREATE INDEX idx_ventas_total ON ventas(total);

-- Compras
CREATE INDEX idx_compras_proveedor ON compras(id_proveedor);
CREATE INDEX idx_compras_fecha ON compras(fecha);
CREATE INDEX idx_compras_estado ON compras(estado);
CREATE INDEX idx_compras_codigo ON compras(codigo);

-- Clientes
CREATE INDEX idx_clientes_documento ON clientes(documento);
CREATE INDEX idx_clientes_nombre ON clientes(nombre(100));
CREATE INDEX idx_clientes_email ON clientes(email(100));

-- Proveedores
CREATE INDEX idx_proveedores_cuit ON proveedores(cuit);
CREATE INDEX idx_proveedores_nombre ON proveedores(nombre(100));

-- Cajas
CREATE INDEX idx_cajas_fecha ON cajas(fecha);
CREATE INDEX idx_cajas_usuario ON cajas(id_usuario);
CREATE INDEX idx_cajas_punto_venta ON cajas(punto_venta);
CREATE INDEX idx_cajas_tipo ON cajas(tipo);
CREATE INDEX idx_cajas_venta ON cajas(id_venta);

-- Clientes Cuenta Corriente
CREATE INDEX idx_ccc_cliente ON clientes_cuenta_corriente(id_cliente);
CREATE INDEX idx_ccc_fecha ON clientes_cuenta_corriente(fecha);
CREATE INDEX idx_ccc_tipo ON clientes_cuenta_corriente(tipo);
CREATE INDEX idx_ccc_venta ON clientes_cuenta_corriente(id_venta);

-- Proveedores Cuenta Corriente
CREATE INDEX idx_pcc_proveedor ON proveedores_cuenta_corriente(id_proveedor);
CREATE INDEX idx_pcc_fecha ON proveedores_cuenta_corriente(fecha_movimiento);
CREATE INDEX idx_pcc_compra ON proveedores_cuenta_corriente(id_compra);

-- Presupuestos
CREATE INDEX idx_presupuestos_cliente ON presupuestos(id_cliente);
CREATE INDEX idx_presupuestos_vendedor ON presupuestos(id_vendedor);
CREATE INDEX idx_presupuestos_fecha ON presupuestos(fecha);
CREATE INDEX idx_presupuestos_estado ON presupuestos(estado);

-- Pedidos
CREATE INDEX idx_pedidos_vendedor ON pedidos(id_vendedor);
CREATE INDEX idx_pedidos_codigo ON pedidos(codigo);
CREATE INDEX idx_pedidos_estado ON pedidos(estado);
CREATE INDEX idx_pedidos_fecha ON pedidos(fecha);

-- Productos Historial
CREATE INDEX idx_historial_id ON productos_historial(id);
CREATE INDEX idx_historial_fecha ON productos_historial(fecha_hora);
CREATE INDEX idx_historial_accion ON productos_historial(accion);
CREATE INDEX idx_historial_revision ON productos_historial(id, revision);
```

---

## 3. Normalización

### Problema: Datos Serializados

**Tablas afectadas:**
- `productos` en ventas/compras (detalles de productos)
- `ptos_venta` en empresa (lista de puntos de venta)
- `almacenes` en empresa
- `listas_precio` en empresa

**Recomendación:** Crear tablas relacionales

```sql
-- Archivo: mejoras/scripts/normalizacion.sql

-- Tabla para detalles de venta (en lugar de JSON)
CREATE TABLE IF NOT EXISTS ventas_detalle (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    id_venta INT NOT NULL,
    id_producto INT NOT NULL,
    cantidad DECIMAL(11,2) NOT NULL,
    precio_unitario DECIMAL(11,2) NOT NULL,
    descuento DECIMAL(11,2) DEFAULT 0.00,
    subtotal DECIMAL(11,2) NOT NULL,
    tipo_iva DECIMAL(5,2) NOT NULL,
    iva_importe DECIMAL(11,2) NOT NULL,
    
    FOREIGN KEY (id_venta) REFERENCES ventas(id) ON DELETE CASCADE,
    FOREIGN KEY (id_producto) REFERENCES productos(id) ON DELETE RESTRICT,
    
    INDEX idx_venta (id_venta),
    INDEX idx_producto (id_producto)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla para detalles de compra
CREATE TABLE IF NOT EXISTS compras_detalle (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    id_compra INT NOT NULL,
    id_producto INT NOT NULL,
    cantidad DECIMAL(11,2) NOT NULL,
    precio_unitario DECIMAL(11,2) NOT NULL,
    subtotal DECIMAL(11,2) NOT NULL,
    
    FOREIGN KEY (id_compra) REFERENCES compras(id) ON DELETE CASCADE,
    FOREIGN KEY (id_producto) REFERENCES productos(id) ON DELETE RESTRICT,
    
    INDEX idx_compra (id_compra),
    INDEX idx_producto (id_producto)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla para puntos de venta
CREATE TABLE IF NOT EXISTS puntos_venta (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    numero INT NOT NULL,
    activo TINYINT(1) DEFAULT 1,
    
    UNIQUE KEY uk_numero (numero)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla para almacenes/sucursales
CREATE TABLE IF NOT EXISTS almacenes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    direccion VARCHAR(255),
    activo TINYINT(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla para listas de precio
CREATE TABLE IF NOT EXISTS listas_precio (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    activa TINYINT(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla pivot para precios por lista
CREATE TABLE IF NOT EXISTS productos_precios (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    id_producto INT NOT NULL,
    id_lista_precio INT NOT NULL,
    precio DECIMAL(11,2) NOT NULL,
    fecha_desde DATE,
    fecha_hasta DATE,
    
    FOREIGN KEY (id_producto) REFERENCES productos(id) ON DELETE CASCADE,
    FOREIGN KEY (id_lista_precio) REFERENCES listas_precio(id) ON DELETE CASCADE,
    
    UNIQUE KEY uk_producto_lista (id_producto, id_lista_precio),
    INDEX idx_lista (id_lista_precio)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

## 4. Campos Recomendados para Agregar

```sql
-- Archivo: mejoras/scripts/campos-auditoria.sql

-- Agregar campos de auditoría a tablas principales

ALTER TABLE productos
    ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    ADD COLUMN created_by INT,
    ADD COLUMN updated_by INT,
    ADD FOREIGN KEY (created_by) REFERENCES usuarios(id),
    ADD FOREIGN KEY (updated_by) REFERENCES usuarios(id);

ALTER TABLE ventas
    ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

ALTER TABLE compras
    ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

ALTER TABLE clientes
    ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

ALTER TABLE proveedores
    ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

-- Soft deletes (borrado lógico)
ALTER TABLE productos ADD COLUMN deleted_at TIMESTAMP NULL;
ALTER TABLE clientes ADD COLUMN deleted_at TIMESTAMP NULL;
ALTER TABLE proveedores ADD COLUMN deleted_at TIMESTAMP NULL;
```

---

## 5. Vistas Útiles

```sql
-- Archivo: mejoras/scripts/vistas-utiles.sql

-- Vista de productos con stock bajo
CREATE OR REPLACE VIEW v_productos_stock_bajo AS
SELECT 
    p.id,
    p.codigo,
    p.descripcion,
    p.stock,
    p.stock_bajo,
    p.precio_venta,
    c.categoria,
    pr.nombre as proveedor
FROM productos p
LEFT JOIN categorias c ON p.id_categoria = c.id
LEFT JOIN proveedores pr ON p.id_proveedor = pr.id
WHERE p.stock <= p.stock_bajo
ORDER BY p.stock ASC;

-- Vista de ventas del día
CREATE OR REPLACE VIEW v_ventas_hoy AS
SELECT 
    v.id,
    v.codigo,
    v.fecha,
    c.nombre as cliente,
    u.nombre as vendedor,
    v.total,
    v.estado
FROM ventas v
LEFT JOIN clientes c ON v.id_cliente = c.id
LEFT JOIN usuarios u ON v.id_vendedor = u.id
WHERE DATE(v.fecha) = CURDATE();

-- Vista de cuenta corriente clientes
CREATE OR REPLACE VIEW v_clientes_saldo AS
SELECT 
    c.id,
    c.nombre,
    c.documento,
    COALESCE(SUM(CASE WHEN ccc.tipo = 1 THEN ccc.importe ELSE 0 END), 0) as debe,
    COALESCE(SUM(CASE WHEN ccc.tipo = 2 THEN ccc.importe ELSE 0 END), 0) as haber,
    COALESCE(SUM(CASE WHEN ccc.tipo = 1 THEN ccc.importe ELSE -ccc.importe END), 0) as saldo
FROM clientes c
LEFT JOIN clientes_cuenta_corriente ccc ON c.id = ccc.id_cliente
GROUP BY c.id, c.nombre, c.documento
HAVING saldo != 0
ORDER BY saldo DESC;

-- Vista de productos más vendidos
CREATE OR REPLACE VIEW v_productos_mas_vendidos AS
SELECT 
    p.id,
    p.codigo,
    p.descripcion,
    p.ventas as cantidad_vendida,
    p.precio_venta,
    (p.ventas * p.precio_venta) as total_vendido,
    c.categoria
FROM productos p
LEFT JOIN categorias c ON p.id_categoria = c.id
ORDER BY p.ventas DESC
LIMIT 50;
```

---

## 6. Procedimientos Almacenados Útiles

```sql
-- Archivo: mejoras/scripts/stored-procedures.sql

DELIMITER $$

-- Actualizar stock de producto
CREATE PROCEDURE sp_actualizar_stock(
    IN p_id_producto INT,
    IN p_cantidad DECIMAL(11,2),
    IN p_tipo VARCHAR(10), -- 'entrada' o 'salida'
    IN p_id_usuario INT,
    IN p_motivo VARCHAR(255)
)
BEGIN
    DECLARE v_stock_actual DECIMAL(11,2);
    
    -- Obtener stock actual
    SELECT stock INTO v_stock_actual FROM productos WHERE id = p_id_producto;
    
    -- Actualizar según tipo
    IF p_tipo = 'entrada' THEN
        UPDATE productos SET stock = stock + p_cantidad WHERE id = p_id_producto;
    ELSEIF p_tipo = 'salida' THEN
        IF v_stock_actual >= p_cantidad THEN
            UPDATE productos SET stock = stock - p_cantidad WHERE id = p_id_producto;
        ELSE
            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Stock insuficiente';
        END IF;
    END IF;
    
    -- Registrar en historial
    -- (implementar según necesidad)
END$$

-- Calcular saldo cliente
CREATE FUNCTION fn_saldo_cliente(p_id_cliente INT)
RETURNS DECIMAL(11,2)
DETERMINISTIC
BEGIN
    DECLARE v_saldo DECIMAL(11,2);
    
    SELECT COALESCE(SUM(
        CASE 
            WHEN tipo = 1 THEN importe 
            ELSE -importe 
        END
    ), 0) INTO v_saldo
    FROM clientes_cuenta_corriente
    WHERE id_cliente = p_id_cliente;
    
    RETURN v_saldo;
END$$

DELIMITER ;

-- Uso:
-- SELECT fn_saldo_cliente(1);
-- CALL sp_actualizar_stock(10, 50.00, 'entrada', 1, 'Compra');
```

---

## 7. Backup y Mantenimiento

**Script de backup diario:**

```bash
#!/bin/bash
# Archivo: mejoras/scripts/backup-db.sh

# Variables
DB_NAME="demo_db"
DB_USER="demo_user"
DB_PASS="aK4UWccl2ceg"
BACKUP_DIR="/home/cluna/backups/db"
DATE=$(date +"%Y%m%d_%H%M%S")
FILENAME="$BACKUP_DIR/${DB_NAME}_${DATE}.sql.gz"

# Crear directorio si no existe
mkdir -p $BACKUP_DIR

# Backup
mysqldump -u $DB_USER -p$DB_PASS $DB_NAME | gzip > $FILENAME

# Eliminar backups antiguos (mantener 30 días)
find $BACKUP_DIR -name "*.sql.gz" -mtime +30 -delete

echo "Backup completado: $FILENAME"
```

**Agregar a crontab:**

```bash
# Backup diario a las 2 AM
0 2 * * * /home/cluna/Documentos/Moon-Desarrollos/public_html/mejoras/scripts/backup-db.sh
```

---

## ✅ Checklist de Implementación

- [ ] Respaldar base de datos completa
- [ ] Migrar tablas de MyISAM a InnoDB
- [ ] Normalizar charset a utf8mb4
- [ ] Encontrar y corregir datos huérfanos
- [ ] Agregar foreign keys
- [ ] Crear índices faltantes
- [ ] Migrar campos TEXT a JSON
- [ ] Crear tablas normalizadas (opcional)
- [ ] Agregar campos de auditoría
- [ ] Crear vistas útiles
- [ ] Crear stored procedures
- [ ] Configurar backup automático
- [ ] Documentar cambios
- [ ] Probar aplicación después de cambios

---

**Tiempo estimado**: 1-2 semanas  
**Prioridad**: 🟠 ALTA  
**Anterior**: [05-modernizacion.md](05-modernizacion.md)  
**Siguiente**: [07-plan-implementacion.md](07-plan-implementacion.md)

