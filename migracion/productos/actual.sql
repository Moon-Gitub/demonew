-- Estructura destino de la tabla productos (referencia)
-- Columnas: id, id_categoria, codigo, id_proveedor, descripcion, imagen, stock, stock2, stock3, stock_medio, stock_bajo, precio_compra, precio_compra_dolar, margen_ganancia, precio_venta_neto, tipo_iva, precio_venta, precio_venta_mayorista, ventas, fecha, nombre_usuario, cambio_desde, es_combo, activo

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
  `es_combo` tinyint(1) DEFAULT 0,
  `activo` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_spanish_ci;
