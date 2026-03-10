-- ================================================================
-- MODELO DESTINO - Estructura completa para migraciones
-- ================================================================
-- Usar este archivo como DESTINO en db_alter_generator.py
-- para que las migraciones siempre converjan a esta estructura.
-- Base: kioscoelfacu_kioscoelfacu_db (phpMyAdmin 5.2.2, MariaDB 10.11.16)
-- ================================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

-- --------------------------------------------------------
-- balanzas_formatos
-- --------------------------------------------------------
CREATE TABLE `balanzas_formatos` (
  `id` int(11) NOT NULL,
  `id_empresa` int(11) NOT NULL DEFAULT 1,
  `nombre` varchar(128) NOT NULL,
  `prefijo` varchar(32) NOT NULL COMMENT 'Prefijo que debe tener el código (ej: 20, 21, 20000)',
  `longitud_min` int(11) DEFAULT NULL,
  `longitud_max` int(11) DEFAULT NULL,
  `pos_producto` int(11) NOT NULL DEFAULT 0 COMMENT 'Posición inicial (0-based) del id de producto en el código',
  `longitud_producto` int(11) NOT NULL DEFAULT 0 COMMENT 'Cantidad de caracteres del id de producto',
  `modo_cantidad` varchar(16) NOT NULL DEFAULT 'ninguno' COMMENT 'peso | unidad | ninguno',
  `pos_cantidad` int(11) DEFAULT NULL COMMENT 'Posición inicial (0-based) del campo cantidad/peso',
  `longitud_cantidad` int(11) DEFAULT NULL COMMENT 'Cantidad de caracteres del campo cantidad/peso',
  `factor_divisor` decimal(10,4) NOT NULL DEFAULT 1.0000 COMMENT 'Divisor para pasar gramos a kg, etc.',
  `cantidad_fija` decimal(10,3) NOT NULL DEFAULT 1.000 COMMENT 'Cantidad fija cuando modo_cantidad = unidad',
  `orden` int(11) NOT NULL DEFAULT 0,
  `activo` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- cajas
-- --------------------------------------------------------
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
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------
-- caja_cierres
-- --------------------------------------------------------
CREATE TABLE `caja_cierres` (
  `id` int(11) NOT NULL,
  `fecha_hora` datetime DEFAULT NULL,
  `punto_venta_cobro` int(11) DEFAULT NULL,
  `ultimo_id_caja` int(11) DEFAULT NULL,
  `total_ingresos` decimal(11,2) DEFAULT NULL,
  `total_egresos` decimal(11,2) DEFAULT NULL,
  `detalle_ingresos` text DEFAULT NULL,
  `detalle_egresos` text DEFAULT NULL,
  `apertura_siguiente_monto` decimal(11,2) DEFAULT NULL,
  `id_usuario_cierre` int(11) DEFAULT NULL,
  `detalle` varchar(255) DEFAULT NULL,
  `detalle_ingresos_manual` text DEFAULT NULL,
  `detalle_egresos_manual` text DEFAULT NULL,
  `diferencias` text DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------
-- categorias
-- --------------------------------------------------------
CREATE TABLE `categorias` (
  `id` int(11) NOT NULL,
  `categoria` text NOT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_spanish_ci;

-- --------------------------------------------------------
-- clientes
-- --------------------------------------------------------
CREATE TABLE `clientes` (
  `id` int(11) NOT NULL,
  `nombre` text NOT NULL,
  `tipo_documento` int(11) DEFAULT NULL,
  `documento` varchar(100) DEFAULT NULL,
  `condicion_iva` int(11) DEFAULT NULL,
  `email` text DEFAULT NULL,
  `telefono` text DEFAULT NULL,
  `direccion` text DEFAULT NULL,
  `fecha_nacimiento` date DEFAULT NULL,
  `compras` int(11) DEFAULT NULL,
  `ultima_compra` datetime DEFAULT NULL,
  `fecha` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `observaciones` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_spanish_ci;

-- --------------------------------------------------------
-- clientes_cuenta_corriente
-- --------------------------------------------------------
CREATE TABLE `clientes_cuenta_corriente` (
  `id` bigint(20) NOT NULL,
  `fecha` datetime DEFAULT NULL,
  `id_cliente` bigint(20) DEFAULT NULL,
  `tipo` int(11) DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `id_venta` bigint(20) DEFAULT NULL,
  `importe` decimal(11,2) DEFAULT 0.00,
  `metodo_pago` text DEFAULT NULL,
  `numero_recibo` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------
-- combos
-- --------------------------------------------------------
CREATE TABLE `combos` (
  `id` int(11) NOT NULL,
  `id_producto` int(11) NOT NULL COMMENT 'ID del producto combo (referencia a productos.id)',
  `codigo` varchar(255) NOT NULL COMMENT 'Código único del combo',
  `nombre` varchar(255) NOT NULL COMMENT 'Nombre del combo',
  `descripcion` text DEFAULT NULL COMMENT 'Descripción del combo',
  `precio_venta` decimal(11,2) DEFAULT 0.00 COMMENT 'Precio de venta del combo',
  `precio_venta_mayorista` decimal(11,2) DEFAULT NULL COMMENT 'Precio mayorista del combo',
  `tipo_iva` decimal(11,2) DEFAULT 21.00 COMMENT 'Tipo de IVA del combo',
  `imagen` text DEFAULT NULL COMMENT 'Ruta de la imagen del combo',
  `activo` tinyint(1) DEFAULT 1 COMMENT '1=Activo, 0=Inactivo',
  `tipo_descuento` enum('ninguno','global','por_producto','mixto') DEFAULT 'ninguno' COMMENT 'Tipo de descuento aplicable',
  `descuento_global` decimal(11,2) DEFAULT 0.00 COMMENT 'Descuento global en porcentaje o monto fijo',
  `aplicar_descuento_global` enum('porcentaje','monto_fijo') DEFAULT 'porcentaje' COMMENT 'Cómo aplicar el descuento global',
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_modificacion` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `nombre_usuario` varchar(50) DEFAULT NULL COMMENT 'Usuario que creó/modificó'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_spanish_ci COMMENT='Tabla principal de combos de productos';

-- --------------------------------------------------------
-- combos_productos
-- --------------------------------------------------------
CREATE TABLE `combos_productos` (
  `id` int(11) NOT NULL,
  `id_combo` int(11) NOT NULL COMMENT 'ID del combo',
  `id_producto` int(11) NOT NULL COMMENT 'ID del producto componente',
  `cantidad` decimal(11,2) NOT NULL DEFAULT 1.00 COMMENT 'Cantidad del producto en el combo',
  `precio_unitario` decimal(11,2) DEFAULT NULL COMMENT 'Precio unitario del producto en el combo (opcional, para override)',
  `descuento` decimal(11,2) DEFAULT 0.00 COMMENT 'Descuento específico para este producto en el combo',
  `aplicar_descuento` enum('porcentaje','monto_fijo') DEFAULT 'porcentaje' COMMENT 'Cómo aplicar el descuento',
  `orden` int(11) DEFAULT 0 COMMENT 'Orden de visualización',
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_spanish_ci COMMENT='Relación entre combos y productos componentes';

-- --------------------------------------------------------
-- compras
-- --------------------------------------------------------
CREATE TABLE `compras` (
  `id` int(11) NOT NULL,
  `codigo` int(11) DEFAULT NULL,
  `id_proveedor` int(11) DEFAULT NULL,
  `usuarioPedido` varchar(250) DEFAULT NULL,
  `sucursalDestino` varchar(255) DEFAULT NULL,
  `usuarioConfirma` varchar(250) DEFAULT NULL,
  `productos` text NOT NULL,
  `totalNeto` float DEFAULT NULL,
  `iva` float DEFAULT NULL,
  `precepcionesIngresosBrutos` float DEFAULT NULL,
  `precepcionesIva` float DEFAULT NULL,
  `precepcionesGanancias` float DEFAULT NULL,
  `impuestoInterno` float DEFAULT NULL,
  `total` varchar(255) DEFAULT NULL,
  `estado` int(11) DEFAULT NULL,
  `descuento` decimal(10,2) DEFAULT NULL,
  `fecha` datetime NOT NULL DEFAULT current_timestamp(),
  `fechaEntrega` varchar(255) DEFAULT NULL,
  `fechaPago` varchar(255) DEFAULT NULL,
  `medioPago` int(11) DEFAULT NULL,
  `observacion` varchar(255) DEFAULT NULL,
  `tipo` varchar(255) DEFAULT NULL,
  `remitoNumero` varchar(255) DEFAULT NULL,
  `numeroFactura` varchar(255) DEFAULT NULL,
  `fechaEmision` varchar(255) DEFAULT NULL,
  `observacionFactura` varchar(255) DEFAULT NULL,
  `fechaIngreso` varchar(255) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------
-- empresa
-- --------------------------------------------------------
CREATE TABLE `empresa` (
  `id` int(11) NOT NULL,
  `razon_social` varchar(255) DEFAULT NULL,
  `titular` varchar(255) DEFAULT NULL,
  `cuit` varchar(20) DEFAULT NULL,
  `domicilio` varchar(255) DEFAULT NULL,
  `localidad` varchar(255) DEFAULT NULL,
  `codigo_postal` varchar(255) DEFAULT NULL,
  `mail` varchar(255) DEFAULT NULL,
  `telefono` varchar(255) DEFAULT NULL,
  `ptos_venta` text DEFAULT NULL,
  `pto_venta_defecto` char(100) DEFAULT NULL,
  `almacenes` text DEFAULT NULL,
  `listas_precio` text DEFAULT NULL,
  `condicion_iva` char(1) DEFAULT NULL,
  `condicion_iibb` char(1) DEFAULT NULL,
  `numero_iibb` varchar(50) DEFAULT NULL,
  `numero_establecimiento` varchar(100) DEFAULT NULL,
  `cbu` varchar(255) DEFAULT NULL,
  `cbu_alias` varchar(255) DEFAULT NULL,
  `inicio_actividades` varchar(50) NOT NULL,
  `concepto_defecto` char(1) DEFAULT '0',
  `tipos_cbtes` text DEFAULT NULL,
  `entorno_facturacion` varchar(50) DEFAULT NULL,
  `ws_padron` varchar(50) DEFAULT NULL,
  `csr` varchar(255) DEFAULT NULL,
  `passphrase` varchar(255) DEFAULT NULL,
  `pem` varchar(255) DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `login_fondo` varchar(255) DEFAULT NULL COMMENT 'Fondo de la página de login (color o imagen)',
  `login_logo` varchar(255) DEFAULT NULL COMMENT 'Ruta del logo del login',
  `login_fondo_form` varchar(255) DEFAULT NULL COMMENT 'Fondo del formulario de login',
  `login_color_boton` varchar(50) DEFAULT '#52658d' COMMENT 'Color del botón de ingresar',
  `login_fuente` varchar(100) DEFAULT 'Segoe UI, Tahoma, Geneva, Verdana, sans-serif' COMMENT 'Fuente del login',
  `login_color_texto_titulo` varchar(50) DEFAULT '#ffffff' COMMENT 'Color del texto del título "Ingresar al sistema"',
  `mp_public_key` varchar(255) DEFAULT NULL COMMENT 'Public Key de Mercado Pago',
  `mp_access_token` varchar(255) DEFAULT NULL COMMENT 'Access Token de Mercado Pago',
  `mp_pos_id` varchar(255) DEFAULT NULL COMMENT 'POS ID de Mercado Pago para QR estático',
  `mp_pos_external_id` varchar(255) DEFAULT NULL COMMENT 'External ID del POS de Mercado Pago (se puede obtener desde la app de MP)'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------
-- integraciones
-- --------------------------------------------------------
CREATE TABLE `integraciones` (
  `id` int(11) NOT NULL,
  `nombre` varchar(255) NOT NULL COMMENT 'Nombre de la integración',
  `tipo` varchar(50) NOT NULL COMMENT 'Tipo: n8n, api, webhook, etc.',
  `webhook_url` varchar(500) DEFAULT NULL COMMENT 'URL del webhook',
  `api_key` varchar(255) DEFAULT NULL COMMENT 'API Key si es necesario',
  `descripcion` text DEFAULT NULL COMMENT 'Descripción de la integración',
  `activo` tinyint(1) DEFAULT 1 COMMENT '1=activo, 0=inactivo',
  `fecha_creacion` datetime DEFAULT current_timestamp(),
  `fecha_actualizacion` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabla de integraciones con servicios externos';

-- --------------------------------------------------------
-- listas_precio
-- --------------------------------------------------------
CREATE TABLE `listas_precio` (
  `id` int(11) NOT NULL,
  `id_empresa` int(11) NOT NULL DEFAULT 1,
  `codigo` varchar(64) NOT NULL COMMENT 'Clave única: precio_venta, empleados, etc.',
  `nombre` varchar(128) NOT NULL COMMENT 'Nombre visible en ventas',
  `base_precio` varchar(64) NOT NULL DEFAULT 'precio_venta' COMMENT 'Columna producto: precio_venta, precio_compra',
  `tipo_descuento` varchar(32) NOT NULL DEFAULT 'ninguno' COMMENT 'ninguno, porcentaje',
  `valor_descuento` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Porcentaje 0-100 si tipo_descuento=porcentaje',
  `orden` int(11) NOT NULL DEFAULT 0,
  `activo` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- medios_pago
-- --------------------------------------------------------
CREATE TABLE `medios_pago` (
  `id` int(11) NOT NULL,
  `codigo` varchar(10) NOT NULL COMMENT 'Código único del medio (ej: EF, TD, TC)',
  `nombre` varchar(100) NOT NULL COMMENT 'Nombre descriptivo (ej: Efectivo, Tarjeta Débito)',
  `descripcion` text DEFAULT NULL COMMENT 'Descripción opcional',
  `activo` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1=activo, 0=inactivo',
  `requiere_codigo` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Si requiere código de transacción',
  `requiere_banco` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Si requiere banco',
  `requiere_numero` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Si requiere número de referencia',
  `requiere_fecha` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Si requiere fecha de vencimiento',
  `orden` int(11) NOT NULL DEFAULT 0 COMMENT 'Orden de visualización',
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_spanish_ci;

-- --------------------------------------------------------
-- pantallas
-- --------------------------------------------------------
CREATE TABLE `pantallas` (
  `id` int(11) NOT NULL,
  `codigo` varchar(128) NOT NULL COMMENT 'Ruta del sistema (ej: crear-venta-caja)',
  `nombre` varchar(128) NOT NULL COMMENT 'Nombre para mostrar en el panel',
  `agrupacion` varchar(64) NOT NULL DEFAULT 'General' COMMENT 'Grupo en menú: Empresa, Ventas, etc.',
  `orden` int(11) NOT NULL DEFAULT 0,
  `activo` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- pedidos
-- --------------------------------------------------------
CREATE TABLE `pedidos` (
  `id` int(11) NOT NULL,
  `codigo` int(11) NOT NULL,
  `id_vendedor` varchar(255) NOT NULL,
  `productos` mediumtext NOT NULL,
  `origen` varchar(255) NOT NULL,
  `destino` varchar(255) NOT NULL,
  `estado` int(11) NOT NULL,
  `usuarioConfirma` varchar(255) NOT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------
-- permisos_rol
-- --------------------------------------------------------
CREATE TABLE `permisos_rol` (
  `rol` varchar(64) NOT NULL,
  `id_pantalla` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- presupuestos
-- --------------------------------------------------------
CREATE TABLE `presupuestos` (
  `id` int(11) NOT NULL,
  `id_cliente` int(11) NOT NULL,
  `id_vendedor` int(11) NOT NULL,
  `productos` text NOT NULL,
  `neto` decimal(10,2) NOT NULL DEFAULT 0.00,
  `neto_gravado` decimal(11,2) DEFAULT NULL COMMENT 'Es el neto mas los intereses de tarjeta y menos los descuentos',
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
  `observaciones` text DEFAULT NULL,
  `fecha` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_spanish_ci ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------
-- productos - ORDEN: stock, stock2, stock3, stock_medio, stock_bajo...
-- --------------------------------------------------------
CREATE TABLE `productos` (
  `id` int(11) NOT NULL,
  `id_categoria` int(11) DEFAULT NULL,
  `codigo` varchar(255) NOT NULL DEFAULT '',
  `id_proveedor` int(11) DEFAULT NULL,
  `descripcion` varchar(255) NOT NULL DEFAULT '',
  `imagen` text DEFAULT NULL,
  `stock` decimal(11,2) DEFAULT 0.00,
  `stock2` decimal(11,2) DEFAULT 0.00,
  `stock3` decimal(11,2) DEFAULT 0.00,
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
  `cambio_desde` varchar(50) NOT NULL,
  `es_combo` tinyint(1) DEFAULT 0 COMMENT '1=Es combo, 0=Producto normal',
  `activo` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1=Activo, 0=Inactivo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_spanish_ci;

-- --------------------------------------------------------
-- productos_historial
-- --------------------------------------------------------
CREATE TABLE `productos_historial` (
  `accion` varchar(9) DEFAULT 'insertar',
  `revision` int(11) NOT NULL,
  `fecha_hora` datetime NOT NULL DEFAULT current_timestamp(),
  `id` int(11) NOT NULL,
  `stock` float DEFAULT NULL,
  `precio_compra` float DEFAULT NULL,
  `precio_venta` float DEFAULT NULL,
  `precio_venta_mayorista` float DEFAULT NULL,
  `nombre_usuario` varchar(255) DEFAULT NULL,
  `cambio_desde` varchar(255) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_spanish_ci ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------
-- productos_venta
-- --------------------------------------------------------
CREATE TABLE `productos_venta` (
  `id` int(11) NOT NULL,
  `id_venta` int(11) NOT NULL,
  `id_producto` int(11) NOT NULL,
  `cantidad` decimal(10,2) NOT NULL DEFAULT 0.00,
  `precio_compra` decimal(10,2) NOT NULL DEFAULT 0.00,
  `precio_venta` decimal(10,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------
-- proveedores
-- --------------------------------------------------------
CREATE TABLE `proveedores` (
  `id` int(11) NOT NULL,
  `nombre` varchar(255) DEFAULT NULL,
  `tipo_documento` int(11) DEFAULT NULL,
  `cuit` varchar(255) DEFAULT NULL,
  `localidad` varchar(255) DEFAULT NULL,
  `direccion` varchar(255) DEFAULT NULL,
  `telefono` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `inicio_actividades` varchar(255) DEFAULT NULL,
  `ingresos_brutos` varchar(255) DEFAULT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp(),
  `observaciones` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------
-- proveedores_cuenta_corriente
-- --------------------------------------------------------
CREATE TABLE `proveedores_cuenta_corriente` (
  `id` int(11) NOT NULL,
  `id_compra` int(11) DEFAULT NULL,
  `id_proveedor` int(11) NOT NULL,
  `total_compra` double NOT NULL,
  `fecha_movimiento` date NOT NULL,
  `importe` double NOT NULL,
  `tipo` int(11) NOT NULL,
  `estado` int(11) NOT NULL,
  `metodo_pago` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `id_usuario` varchar(250) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------
-- usuarios
-- --------------------------------------------------------
CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nombre` varchar(255) NOT NULL DEFAULT '',
  `usuario` varchar(255) NOT NULL DEFAULT '',
  `password` varchar(255) NOT NULL DEFAULT '',
  `perfil` varchar(255) NOT NULL DEFAULT '',
  `sucursal` varchar(200) DEFAULT NULL,
  `puntos_venta` varchar(100) DEFAULT NULL,
  `listas_precio` varchar(200) DEFAULT NULL,
  `foto` varchar(255) NOT NULL DEFAULT '',
  `estado` int(11) NOT NULL,
  `ultimo_login` datetime NOT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `empresa` tinyint(4) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_spanish_ci;

-- --------------------------------------------------------
-- ventas
-- --------------------------------------------------------
CREATE TABLE `ventas` (
  `id` int(11) NOT NULL,
  `uuid` varchar(34) NOT NULL,
  `id_empresa` tinyint(4) NOT NULL DEFAULT 1,
  `codigo` int(11) NOT NULL,
  `cbte_tipo` int(11) DEFAULT NULL,
  `id_cliente` int(11) NOT NULL,
  `id_vendedor` int(11) NOT NULL,
  `productos` text NOT NULL,
  `sucursal` varchar(50) DEFAULT 'stock',
  `neto` decimal(10,2) NOT NULL DEFAULT 0.00,
  `neto_gravado` decimal(11,2) DEFAULT NULL COMMENT 'Es el neto mas los intereses de tarjeta y menos los descuentos',
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
  `pto_vta` int(11) DEFAULT NULL,
  `fec_desde` varchar(20) DEFAULT NULL,
  `fec_hasta` varchar(20) DEFAULT NULL,
  `fec_vencimiento` varchar(20) DEFAULT NULL,
  `asociado_tipo_cbte` int(11) DEFAULT NULL,
  `asociado_pto_vta` int(11) DEFAULT NULL,
  `asociado_nro_cbte` int(11) DEFAULT NULL,
  `pedido_afip` text DEFAULT NULL,
  `respuesta_afip` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_spanish_ci;

-- --------------------------------------------------------
-- ventas_factura
-- --------------------------------------------------------
CREATE TABLE `ventas_factura` (
  `id` int(11) NOT NULL,
  `id_venta` int(11) DEFAULT NULL,
  `fec_factura` varchar(15) DEFAULT NULL,
  `nro_cbte` bigint(20) DEFAULT NULL,
  `cae` varchar(100) DEFAULT NULL,
  `fec_vto_cae` varchar(10) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Índices (para que db_alter_generator los detecte al usar como DESTINO)
--
ALTER TABLE `balanzas_formatos` ADD PRIMARY KEY (`id`), ADD KEY `idx_balanzas_empresa_activo` (`id_empresa`,`activo`,`orden`);
ALTER TABLE `cajas` ADD PRIMARY KEY (`id`), ADD KEY `idx_cajas_fecha` (`fecha`);
ALTER TABLE `caja_cierres` ADD PRIMARY KEY (`id`) USING BTREE;
ALTER TABLE `categorias` ADD PRIMARY KEY (`id`);
ALTER TABLE `clientes` ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `documento` (`documento`);
ALTER TABLE `clientes_cuenta_corriente` ADD PRIMARY KEY (`id`);
ALTER TABLE `combos` ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `codigo` (`codigo`), ADD UNIQUE KEY `id_producto` (`id_producto`), ADD KEY `activo` (`activo`);
ALTER TABLE `combos_productos` ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `combo_producto` (`id_combo`,`id_producto`), ADD KEY `id_combo` (`id_combo`), ADD KEY `id_producto` (`id_producto`);
ALTER TABLE `compras` ADD PRIMARY KEY (`id`);
ALTER TABLE `empresa` ADD PRIMARY KEY (`id`);
ALTER TABLE `integraciones` ADD PRIMARY KEY (`id`);
ALTER TABLE `listas_precio` ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `uq_listas_precio_empresa_codigo` (`id_empresa`,`codigo`), ADD KEY `idx_listas_precio_empresa_activo` (`id_empresa`,`activo`);
ALTER TABLE `medios_pago` ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `codigo` (`codigo`);
ALTER TABLE `pantallas` ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `uq_pantallas_codigo` (`codigo`), ADD KEY `idx_pantallas_agrupacion_orden` (`agrupacion`,`orden`);
ALTER TABLE `pedidos` ADD PRIMARY KEY (`id`);
ALTER TABLE `permisos_rol` ADD PRIMARY KEY (`rol`,`id_pantalla`), ADD KEY `fk_permisos_rol_pantalla` (`id_pantalla`);
ALTER TABLE `presupuestos` ADD PRIMARY KEY (`id`);
ALTER TABLE `productos` ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `codigo` (`codigo`), ADD KEY `es_combo` (`es_combo`);
ALTER TABLE `productos_historial` ADD PRIMARY KEY (`id`,`revision`);
ALTER TABLE `productos_venta` ADD PRIMARY KEY (`id`), ADD KEY `idx_venta` (`id_venta`), ADD KEY `idx_producto` (`id_producto`);
ALTER TABLE `proveedores` ADD PRIMARY KEY (`id`);
ALTER TABLE `proveedores_cuenta_corriente` ADD PRIMARY KEY (`id`);
ALTER TABLE `usuarios` ADD PRIMARY KEY (`id`);
ALTER TABLE `ventas` ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `uuid` (`uuid`), ADD KEY `idx_fecha_cbte_tipo` (`fecha`,`cbte_tipo`);
ALTER TABLE `ventas_factura` ADD PRIMARY KEY (`id`);
