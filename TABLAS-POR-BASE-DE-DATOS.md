# 📊 DISTRIBUCIÓN DE TABLAS POR BASE DE DATOS

**Fecha:** 20 Noviembre 2025

---

## 🗄️ BASE DE DATOS 1: DEMO_DB (LOCAL - Sistema POS)

**Servidor:** localhost
**Nombre:** demo_db
**Usuario:** demo_user
**Uso:** Sistema POS (ventas, productos, inventario, usuarios)

### Tablas del Sistema POS:

```sql
-- ===================================
-- EJECUTAR EN: demo_db (localhost)
-- ===================================

1.  cajas
2.  caja_cierres
3.  categorias
4.  clientes (clientes del POS local)
5.  clientes_cta_cte (cuenta corriente local)
6.  compras
7.  empresa
8.  ip_bloqueadas
9.  login_intentos
10. pedidos
11. presupuestos
12. productos
13. productos_historial
14. proveedores
15. proveedores_cta_cte
16. usuarios
17. ventas
18. ventas_factura

TOTAL: ~18 tablas
```

### ⚠️ IMPORTANTE: Estas tablas YA EXISTEN
No hay que crear nada nuevo en demo_db, ya están todas las tablas del sistema POS.

---

## 🌙 BASE DE DATOS 2: MOONDESA_MOON (REMOTA - Sistema de Cobro)

**Servidor:** 107.161.23.241
**Nombre:** moondesa_moon
**Usuario:** moondesa_moon
**Uso:** Sistema de cobro de Moon Desarrollos + MercadoPago

### Tablas Existentes (Sistema Cobro):

```sql
-- ===================================
-- YA EXISTEN EN: moondesa_moon
-- ===================================

1. clientes (clientes de Moon Desarrollos)
2. clientes_cuenta_corriente (cuenta corriente de Moon)
```

### ⚠️ Tablas NUEVAS a Crear (MercadoPago):

```sql
-- ===================================
-- CREAR EN: moondesa_moon
-- Archivo: db/crear-tablas-mercadopago.sql
-- ===================================

3. mercadopago_intentos
4. mercadopago_pagos
5. mercadopago_webhooks

-- Vistas (se crean automáticamente con el script):
- v_mercadopago_resumen
- v_mercadopago_pendientes
```

---

## 📋 SCRIPT SQL PARA BD MOON

### Comando para ejecutar:

```bash
# Opción 1: Desde línea de comandos
mysql -h 107.161.23.241 -u moondesa_moon -p moondesa_moon < db/crear-tablas-mercadopago.sql

# Opción 2: Desde phpMyAdmin
# 1. Conectar a servidor 107.161.23.241
# 2. Seleccionar base de datos: moondesa_moon
# 3. Ir a pestaña "SQL"
# 4. Copiar y pegar contenido de: db/crear-tablas-mercadopago.sql
# 5. Ejecutar
```

### Contenido del script (db/crear-tablas-mercadopago.sql):

```sql
-- ===============================================
-- TABLAS PARA SISTEMA DE MERCADOPAGO MEJORADO
-- ===============================================
-- ⚠️ EJECUTAR EN: moondesa_moon (107.161.23.241)
-- ⚠️ NO ejecutar en demo_db

-- Tabla 1: Intentos de pago
CREATE TABLE IF NOT EXISTS `mercadopago_intentos` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `id_cliente_moon` INT(11) NOT NULL,
  `preference_id` VARCHAR(255) NOT NULL,
  `monto` DECIMAL(11,2) NOT NULL,
  `descripcion` VARCHAR(255) DEFAULT NULL,
  `fecha_creacion` DATETIME NOT NULL,
  `fecha_actualizacion` DATETIME DEFAULT NULL,
  `estado` VARCHAR(50) DEFAULT 'pendiente',
  PRIMARY KEY (`id`),
  INDEX `idx_cliente` (`id_cliente_moon`),
  INDEX `idx_preference` (`preference_id`),
  INDEX `idx_fecha` (`fecha_creacion`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla 2: Pagos confirmados
CREATE TABLE IF NOT EXISTS `mercadopago_pagos` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `id_cliente_moon` INT(11) NOT NULL,
  `payment_id` VARCHAR(255) NOT NULL,
  `preference_id` VARCHAR(255) DEFAULT NULL,
  `monto` DECIMAL(11,2) NOT NULL,
  `estado` VARCHAR(50) NOT NULL,
  `fecha_pago` DATETIME NOT NULL,
  `payment_type` VARCHAR(50) DEFAULT NULL,
  `payment_method_id` VARCHAR(50) DEFAULT NULL,
  `datos_json` TEXT DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_payment_id` (`payment_id`),
  INDEX `idx_cliente` (`id_cliente_moon`),
  INDEX `idx_fecha` (`fecha_pago`),
  INDEX `idx_estado` (`estado`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla 3: Webhooks recibidos
CREATE TABLE IF NOT EXISTS `mercadopago_webhooks` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `topic` VARCHAR(50) NOT NULL,
  `resource_id` VARCHAR(255) NOT NULL,
  `datos_json` TEXT DEFAULT NULL,
  `fecha_recibido` DATETIME NOT NULL,
  `fecha_procesado` DATETIME DEFAULT NULL,
  `procesado` TINYINT(1) DEFAULT 0,
  PRIMARY KEY (`id`),
  INDEX `idx_topic` (`topic`),
  INDEX `idx_resource` (`resource_id`),
  INDEX `idx_fecha` (`fecha_recibido`),
  INDEX `idx_procesado` (`procesado`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Vistas útiles
CREATE OR REPLACE VIEW v_mercadopago_resumen AS
SELECT
    id_cliente_moon,
    COUNT(*) as total_pagos,
    SUM(monto) as total_monto,
    MAX(fecha_pago) as ultimo_pago,
    MIN(fecha_pago) as primer_pago
FROM mercadopago_pagos
WHERE estado = 'approved'
GROUP BY id_cliente_moon;

CREATE OR REPLACE VIEW v_mercadopago_pendientes AS
SELECT
    i.*,
    DATEDIFF(NOW(), i.fecha_creacion) as dias_pendiente
FROM mercadopago_intentos i
LEFT JOIN mercadopago_pagos p ON i.preference_id = p.preference_id
WHERE p.id IS NULL
AND i.estado = 'pendiente'
ORDER BY i.fecha_creacion DESC;
```

---

## 🔍 VERIFICAR QUE LAS TABLAS SE CREARON

### En BD Local (demo_db):

```sql
-- Ver todas las tablas
SHOW TABLES;

-- Debe mostrar:
-- cajas, categorias, clientes, productos, ventas, etc.
```

### En BD Moon (moondesa_moon):

```sql
-- Conectar a la BD Moon
mysql -h 107.161.23.241 -u moondesa_moon -p

-- Seleccionar base de datos
USE moondesa_moon;

-- Ver tablas de MercadoPago
SHOW TABLES LIKE 'mercadopago%';

-- Debe mostrar:
-- mercadopago_intentos
-- mercadopago_pagos
-- mercadopago_webhooks

-- Ver estructura de las tablas
DESCRIBE mercadopago_intentos;
DESCRIBE mercadopago_pagos;
DESCRIBE mercadopago_webhooks;

-- Ver las vistas
SHOW FULL TABLES WHERE TABLE_TYPE = 'VIEW';

-- Debe mostrar:
-- v_mercadopago_resumen
-- v_mercadopago_pendientes
```

---

## 📊 RESUMEN VISUAL

```
┌─────────────────────────────────────────────┐
│  BD LOCAL: demo_db (localhost)              │
├─────────────────────────────────────────────┤
│  Sistema POS                                │
│  ✅ YA EXISTEN todas las tablas             │
│  • cajas                                    │
│  • categorias                               │
│  • clientes (locales)                       │
│  • productos                                │
│  • ventas                                   │
│  • usuarios                                 │
│  • proveedores                              │
│  • compras                                  │
│  • etc... (~18 tablas)                      │
│                                             │
│  ❌ NO crear tablas de MercadoPago aquí    │
└─────────────────────────────────────────────┘

┌─────────────────────────────────────────────┐
│  BD MOON: moondesa_moon (107.161.23.241)    │
├─────────────────────────────────────────────┤
│  Sistema de Cobro Moon                      │
│  ✅ YA EXISTEN:                             │
│  • clientes (de Moon)                       │
│  • clientes_cuenta_corriente                │
│                                             │
│  ⚠️ CREAR (con el script SQL):              │
│  • mercadopago_intentos        [NUEVO]      │
│  • mercadopago_pagos           [NUEVO]      │
│  • mercadopago_webhooks        [NUEVO]      │
│  • v_mercadopago_resumen       [NUEVO]      │
│  • v_mercadopago_pendientes    [NUEVO]      │
└─────────────────────────────────────────────┘
```

---

## ✅ CHECKLIST DE CREACIÓN DE TABLAS

### BD Local (demo_db):
- [x] Tablas del POS ya existen
- [ ] No hacer nada, ya está todo

### BD Moon (moondesa_moon):
- [ ] Conectar a 107.161.23.241
- [ ] Ejecutar script: db/crear-tablas-mercadopago.sql
- [ ] Verificar que se crearon las 3 tablas
- [ ] Verificar que se crearon las 2 vistas
- [ ] Hacer backup de la BD Moon

---

## 🚨 ERRORES COMUNES

### ❌ Error: "Table already exists"
**Causa:** Las tablas ya fueron creadas anteriormente
**Solución:** Está bien, el script usa `IF NOT EXISTS`, no hace nada

### ❌ Error: "Access denied"
**Causa:** Las credenciales de la BD Moon son incorrectas
**Solución:** Verificar en .env las credenciales MOON_DB_*

### ❌ Error: Tablas creadas en demo_db en lugar de moondesa_moon
**Causa:** Se ejecutó el script en la BD incorrecta
**Solución:**
```sql
-- Eliminar de demo_db
DROP TABLE IF EXISTS mercadopago_intentos;
DROP TABLE IF EXISTS mercadopago_pagos;
DROP TABLE IF EXISTS mercadopago_webhooks;

-- Crear en moondesa_moon
mysql -h 107.161.23.241 -u moondesa_moon -p moondesa_moon < db/crear-tablas-mercadopago.sql
```

---

## 📞 COMANDO RÁPIDO (COPY-PASTE)

```bash
# Crear tablas de MercadoPago en BD Moon
mysql -h 107.161.23.241 -u moondesa_moon -p moondesa_moon < db/crear-tablas-mercadopago.sql

# Verificar que se crearon
mysql -h 107.161.23.241 -u moondesa_moon -p -e "USE moondesa_moon; SHOW TABLES LIKE 'mercadopago%';"
```

**Listo!** Con esto ya tienes todas las tablas necesarias en las bases de datos correctas.

---

**Desarrollado por:** Claude AI
**Sprint:** 1 - Sistema de Cobro MercadoPago
**Estado:** ✅ Documentación completa
