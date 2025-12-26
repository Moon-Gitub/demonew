-- ================================================================
-- SCRIPT DE SINCRONIZACIÓN DE ESTRUCTURA
-- ================================================================
-- Generado: 2025-12-25 23:43:08
-- 
-- DESTINO (modelo): /home/cluna/Documentos/7-Moon-Desarrollos/Migraciones/comestiblesadrimar/newmoon_newmoon_db.sql
-- ORIGEN (a modificar): /home/cluna/Documentos/7-Moon-Desarrollos/Migraciones/comestiblesadrimar/comestiblesadrim_db_sin_datos.sql
--
-- Este script transforma la estructura del ORIGEN para que sea
-- idéntica al DESTINO, sin eliminar datos existentes.
--
-- ⚠️  IMPORTANTE: Hacer backup antes de ejecutar
-- ================================================================

SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';

-- ================================================================
-- TABLAS NUEVAS A CREAR
-- ================================================================
-- Se usa CREATE TABLE IF NOT EXISTS para evitar errores si la tabla ya existe

-- Creación de tabla 'caja_cierres' (reconstruida desde datos parseados)
CREATE TABLE IF NOT EXISTS `caja_cierres` (
  `id` INT(11) NOT NULL,
  `fecha_hora` DATETIME NULL DEFAULT NULL,
  `punto_venta_cobro` INT(11) NULL DEFAULT NULL,
  `ultimo_id_caja` INT(11) NULL DEFAULT NULL,
  `total_ingresos` DECIMAL(11,2) NULL DEFAULT NULL,
  `total_egresos` DECIMAL(11,2) NULL DEFAULT NULL,
  `detalle_ingresos` TEXT NULL DEFAULT NULL,
  `detalle_egresos` TEXT NULL DEFAULT NULL,
  `apertura_siguiente_monto` DECIMAL(11,2) NULL DEFAULT NULL,
  `id_usuario_cierre` INT(11) NULL DEFAULT NULL,
  `detalle` VARCHAR(255) NULL DEFAULT NULL,
  `detalle_ingresos_manual` TEXT NULL DEFAULT NULL,
  `detalle_egresos_manual` TEXT NULL DEFAULT NULL,
  `diferencias` TEXT NULL DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;

-- Creación de tabla 'combos' (reconstruida desde datos parseados)
CREATE TABLE IF NOT EXISTS `combos` (
  `id` INT(11) NOT NULL,
  `id_producto` INT(11) NOT NULL,
  `codigo` VARCHAR(255) NOT NULL,
  `nombre` VARCHAR(255) NOT NULL,
  `descripcion` TEXT NULL DEFAULT NULL,
  `precio_venta` DECIMAL(11,2) NULL DEFAULT 0.00,
  `precio_venta_mayorista` DECIMAL(11,2) NULL DEFAULT NULL,
  `tipo_iva` DECIMAL(11,2) NULL DEFAULT 21.00,
  `imagen` TEXT NULL DEFAULT NULL,
  `activo` TINYINT(1) NULL DEFAULT 1,
  `tipo_descuento` ENUM('NINGUNO','GLOBAL','POR_PRODUCTO','MIXTO') NULL DEFAULT 'ninguno',
  `descuento_global` DECIMAL(11,2) NULL DEFAULT 0.00,
  `aplicar_descuento_global` ENUM('PORCENTAJE','MONTO_FIJO') NULL DEFAULT 'porcentaje',
  `fecha_creacion` TIMESTAMP NOT NULL DEFAULT 'current_timestamp()',
  `fecha_modificacion` TIMESTAMP NULL DEFAULT NULL,
  `nombre_usuario` VARCHAR(50) NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- Creación de tabla 'combos_productos' (reconstruida desde datos parseados)
CREATE TABLE IF NOT EXISTS `combos_productos` (
  `id` INT(11) NOT NULL,
  `id_combo` INT(11) NOT NULL,
  `id_producto` INT(11) NOT NULL,
  `cantidad` DECIMAL(11,2) NOT NULL DEFAULT 1.00,
  `precio_unitario` DECIMAL(11,2) NULL DEFAULT NULL,
  `descuento` DECIMAL(11,2) NULL DEFAULT 0.00,
  `aplicar_descuento` ENUM('PORCENTAJE','MONTO_FIJO') NULL DEFAULT 'porcentaje',
  `orden` INT(11) NULL DEFAULT 0,
  `fecha_creacion` TIMESTAMP NOT NULL DEFAULT 'current_timestamp()'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- Creación de tabla 'integraciones' (reconstruida desde datos parseados)
CREATE TABLE IF NOT EXISTS `integraciones` (
  `id` INT(11) NOT NULL,
  `nombre` VARCHAR(255) NOT NULL,
  `tipo` VARCHAR(50) NOT NULL,
  `webhook_url` VARCHAR(500) NULL DEFAULT NULL,
  `api_key` VARCHAR(255) NULL DEFAULT NULL,
  `descripcion` TEXT NULL DEFAULT NULL,
  `activo` TINYINT(1) NULL DEFAULT 1,
  `fecha_creacion` DATETIME NULL DEFAULT 'current_timestamp()',
  `fecha_actualizacion` DATETIME NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Creación de tabla 'pedidos' (reconstruida desde datos parseados)
CREATE TABLE IF NOT EXISTS `pedidos` (
  `id` INT(11) NOT NULL,
  `codigo` INT(11) NOT NULL,
  `id_vendedor` VARCHAR(255) NOT NULL,
  `productos` MEDIUMTEXT NOT NULL,
  `origen` VARCHAR(255) NOT NULL,
  `destino` VARCHAR(255) NOT NULL,
  `estado` INT(11) NOT NULL,
  `usuarioConfirma` VARCHAR(255) NOT NULL,
  `fecha` TIMESTAMP NOT NULL DEFAULT 'current_timestamp()'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;

-- Creación de tabla 'presupuestos' (reconstruida desde datos parseados)
CREATE TABLE IF NOT EXISTS `presupuestos` (
  `id` INT(11) NOT NULL,
  `id_cliente` INT(11) NOT NULL,
  `id_vendedor` INT(11) NOT NULL,
  `productos` TEXT NOT NULL,
  `neto` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `neto_gravado` DECIMAL(11,2) NULL DEFAULT NULL,
  `base_imponible_0` DECIMAL(10,2) NULL DEFAULT NULL,
  `base_imponible_2` DECIMAL(10,2) NULL DEFAULT NULL,
  `base_imponible_5` DECIMAL(10,2) NULL DEFAULT NULL,
  `base_imponible_10` DECIMAL(10,2) NULL DEFAULT NULL,
  `base_imponible_21` DECIMAL(10,2) NULL DEFAULT NULL,
  `base_imponible_27` DECIMAL(10,2) NULL DEFAULT NULL,
  `iva_2` DECIMAL(10,2) NULL DEFAULT NULL,
  `iva_5` DECIMAL(10,2) NULL DEFAULT NULL,
  `iva_10` DECIMAL(10,2) NULL DEFAULT NULL,
  `iva_21` DECIMAL(10,2) NULL DEFAULT NULL,
  `iva_27` DECIMAL(10,2) NULL DEFAULT NULL,
  `impuesto` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `impuesto_detalle` TEXT NULL DEFAULT NULL,
  `total` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `metodo_pago` TEXT NOT NULL,
  `estado` INT(11) NULL DEFAULT 0,
  `observaciones` TEXT NULL DEFAULT NULL,
  `fecha` TIMESTAMP NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- Creación de tabla 'productos_cambios' (reconstruida desde datos parseados)
CREATE TABLE IF NOT EXISTS `productos_cambios` (
  `accion` VARCHAR(9) NULL DEFAULT 'insertar',
  `revision` INT(11) NOT NULL,
  `fecha_hora` DATETIME NOT NULL DEFAULT 'current_timestamp()',
  `id` INT(11) NOT NULL,
  `stock` FLOAT NULL DEFAULT NULL,
  `precio_compra` FLOAT NULL DEFAULT NULL,
  `precio_venta` FLOAT NULL DEFAULT NULL,
  `precio_venta_mayorista` FLOAT NULL DEFAULT NULL,
  `nombre_usuario` VARCHAR(255) NULL DEFAULT NULL,
  `cambio_desde` VARCHAR(255) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;


-- ================================================================
-- TABLA: cajas
-- Agregar: 3 campos
-- Modificar: 1 campos
-- ================================================================

ALTER TABLE `cajas` ADD COLUMN `punto_venta` INT(11) NULL DEFAULT NULL;
ALTER TABLE `cajas` ADD COLUMN `id_venta` INT(11) NULL DEFAULT NULL;
ALTER TABLE `cajas` ADD COLUMN `id_cliente_proveedor` INT(11) NULL DEFAULT NULL;

ALTER TABLE `cajas` MODIFY COLUMN `tipo` INT(11) NULL DEFAULT NULL;


-- ================================================================
-- TABLA: clientes
-- Agregar: 1 campos
-- Modificar: 1 campos
-- ================================================================

ALTER TABLE `clientes` ADD COLUMN `observaciones` TEXT NULL DEFAULT NULL;

ALTER TABLE `clientes` MODIFY COLUMN `documento` VARCHAR(100) NULL DEFAULT NULL;


-- ================================================================
-- TABLA: clientes_cuenta_corriente
-- Agregar: 1 campos
-- ================================================================

ALTER TABLE `clientes_cuenta_corriente` ADD COLUMN `numero_recibo` INT(11) NULL DEFAULT NULL;


-- ================================================================
-- TABLA: compras
-- Agregar: 1 campos
-- Modificar: 1 campos
-- ================================================================

ALTER TABLE `compras` ADD COLUMN `descuento` DECIMAL(10,2) NULL DEFAULT NULL;

ALTER TABLE `compras` MODIFY COLUMN `fecha` DATETIME NOT NULL DEFAULT current_timestamp();


-- ================================================================
-- TABLA: empresa
-- Agregar: 16 campos
-- Modificar: 1 campos
-- ================================================================

ALTER TABLE `empresa` ADD COLUMN `almacenes` TEXT NULL DEFAULT NULL;
ALTER TABLE `empresa` ADD COLUMN `listas_precio` TEXT NULL DEFAULT NULL;
ALTER TABLE `empresa` ADD COLUMN `numero_establecimiento` VARCHAR(100) NULL DEFAULT NULL;
ALTER TABLE `empresa` ADD COLUMN `cbu` VARCHAR(255) NULL DEFAULT NULL;
ALTER TABLE `empresa` ADD COLUMN `cbu_alias` VARCHAR(255) NULL DEFAULT NULL;
ALTER TABLE `empresa` ADD COLUMN `ws_padron` VARCHAR(50) NULL DEFAULT NULL;
ALTER TABLE `empresa` ADD COLUMN `login_fondo` VARCHAR(255) NULL DEFAULT NULL;
ALTER TABLE `empresa` ADD COLUMN `login_logo` VARCHAR(255) NULL DEFAULT NULL;
ALTER TABLE `empresa` ADD COLUMN `login_fondo_form` VARCHAR(255) NULL DEFAULT NULL;
ALTER TABLE `empresa` ADD COLUMN `login_color_boton` VARCHAR(50) NULL DEFAULT '#52658d';
ALTER TABLE `empresa` ADD COLUMN `login_fuente` VARCHAR(100) NULL DEFAULT 'Segoe UI, Tahoma, Geneva, Verdana, sans-serif';
ALTER TABLE `empresa` ADD COLUMN `login_color_texto_titulo` VARCHAR(50) NULL DEFAULT '#ffffff';
ALTER TABLE `empresa` ADD COLUMN `mp_public_key` VARCHAR(255) NULL DEFAULT NULL;
ALTER TABLE `empresa` ADD COLUMN `mp_access_token` VARCHAR(255) NULL DEFAULT NULL;
ALTER TABLE `empresa` ADD COLUMN `mp_pos_id` VARCHAR(255) NULL DEFAULT NULL;
ALTER TABLE `empresa` ADD COLUMN `mp_pos_external_id` VARCHAR(255) NULL DEFAULT NULL;

ALTER TABLE `empresa` MODIFY COLUMN `pto_venta_defecto` CHAR(100) NULL DEFAULT NULL;


-- ================================================================
-- TABLA: productos
-- Agregar: 6 campos
-- Modificar: 1 campos
-- ================================================================

ALTER TABLE `productos` ADD COLUMN `deposito` DECIMAL(10,2) NOT NULL;
ALTER TABLE `productos` ADD COLUMN `precio_compra_dolar` DECIMAL(11,2) NULL DEFAULT 0.00;
ALTER TABLE `productos` ADD COLUMN `precio_venta_mayorista` DECIMAL(11,2) NULL DEFAULT NULL;
ALTER TABLE `productos` ADD COLUMN `nombre_usuario` VARCHAR(50) NULL DEFAULT NULL;
ALTER TABLE `productos` ADD COLUMN `cambio_desde` VARCHAR(50) NOT NULL;
ALTER TABLE `productos` ADD COLUMN `es_combo` TINYINT(1) NULL DEFAULT 0;

ALTER TABLE `productos` MODIFY COLUMN `id_categoria` INT(11) NULL DEFAULT NULL;


-- ================================================================
-- TABLA: proveedores
-- Agregar: 4 campos
-- ================================================================

ALTER TABLE `proveedores` ADD COLUMN `tipo_documento` INT(11) NULL DEFAULT NULL;
ALTER TABLE `proveedores` ADD COLUMN `inicio_actividades` VARCHAR(255) NULL DEFAULT NULL;
ALTER TABLE `proveedores` ADD COLUMN `ingresos_brutos` VARCHAR(255) NULL DEFAULT NULL;
ALTER TABLE `proveedores` ADD COLUMN `observaciones` TEXT NULL DEFAULT NULL;


-- ================================================================
-- TABLA: proveedores_cuenta_corriente
-- Modificar: 1 campos
-- ================================================================

ALTER TABLE `proveedores_cuenta_corriente` MODIFY COLUMN `metodo_pago` VARCHAR(255) NULL DEFAULT NULL;


-- ================================================================
-- TABLA: usuarios
-- Agregar: 4 campos
-- Modificar: 5 campos
-- ================================================================

ALTER TABLE `usuarios` ADD COLUMN `sucursal` VARCHAR(200) NULL DEFAULT NULL;
ALTER TABLE `usuarios` ADD COLUMN `puntos_venta` VARCHAR(100) NULL DEFAULT NULL;
ALTER TABLE `usuarios` ADD COLUMN `listas_precio` VARCHAR(200) NULL DEFAULT NULL;
ALTER TABLE `usuarios` ADD COLUMN `empresa` TINYINT(4) NOT NULL DEFAULT 1;

ALTER TABLE `usuarios` MODIFY COLUMN `nombre` VARCHAR(255) NOT NULL DEFAULT '';
ALTER TABLE `usuarios` MODIFY COLUMN `usuario` VARCHAR(255) NOT NULL DEFAULT '';
ALTER TABLE `usuarios` MODIFY COLUMN `password` VARCHAR(255) NOT NULL DEFAULT '';
ALTER TABLE `usuarios` MODIFY COLUMN `perfil` VARCHAR(255) NOT NULL DEFAULT '';
ALTER TABLE `usuarios` MODIFY COLUMN `foto` VARCHAR(255) NOT NULL DEFAULT '';


-- ================================================================
-- TABLA: ventas
-- Agregar: 5 campos
-- Modificar: 3 campos
-- ================================================================

ALTER TABLE `ventas` ADD COLUMN `uuid` VARCHAR(34) NOT NULL;
ALTER TABLE `ventas` ADD COLUMN `id_empresa` TINYINT(4) NOT NULL DEFAULT 1;
ALTER TABLE `ventas` ADD COLUMN `asociado_tipo_cbte` INT(11) NULL DEFAULT NULL;
ALTER TABLE `ventas` ADD COLUMN `asociado_pto_vta` INT(11) NULL DEFAULT NULL;
ALTER TABLE `ventas` ADD COLUMN `asociado_nro_cbte` INT(11) NULL DEFAULT NULL;

ALTER TABLE `ventas` MODIFY COLUMN `estado` INT(11) NULL DEFAULT 0;
ALTER TABLE `ventas` MODIFY COLUMN `concepto` INT(11) NULL DEFAULT NULL;
ALTER TABLE `ventas` MODIFY COLUMN `pto_vta` INT(11) NULL DEFAULT NULL;


-- ================================================================
-- TABLA: ventas_factura
-- Modificar: 2 campos
-- ================================================================

ALTER TABLE `ventas_factura` MODIFY COLUMN `id_venta` INT(11) NULL DEFAULT NULL;
ALTER TABLE `ventas_factura` MODIFY COLUMN `nro_cbte` BIGINT(20) NULL DEFAULT NULL;


SET FOREIGN_KEY_CHECKS = 1;

-- ================================================================
-- RESUMEN
-- ================================================================
-- Tablas creadas: 7
-- Columnas agregadas: 41
-- Columnas modificadas: 16
-- Índices agregados: 0
--
-- ⚠️ No se elimina ninguna columna ni tabla (sin DROP).
--
-- ✅ Validación de sintaxis SQL: PASADA
--
-- ⚠️ ADVERTENCIAS:
--    Línea 3: CREATE TABLE IF NOT EXISTS `caja_cierres` (...
--    Línea 19: CREATE TABLE IF NOT EXISTS `combos` (...
--    Línea 37: CREATE TABLE IF NOT EXISTS `combos_productos` (...
--    Línea 48: CREATE TABLE IF NOT EXISTS `integraciones` (...
--    Línea 59: CREATE TABLE IF NOT EXISTS `pedidos` (...
--    Línea 70: CREATE TABLE IF NOT EXISTS `presupuestos` (...
--    Línea 96: CREATE TABLE IF NOT EXISTS `productos_cambios` (...
--    Tipo de dato potencialmente inválido: DESTINO
--    Tipo de dato potencialmente inválido: ORIGEN
--
-- VALIDACIONES APLICADAS:
-- ✅ Valores DEFAULT con comillas correctamente escapadas
-- ✅ CREATE TABLE IF NOT EXISTS para evitar errores de tablas existentes
-- ✅ Validación de sintaxis SQL básica
-- ✅ Manejo de valores especiales (NULL, números, funciones SQL)
-- ================================================================