-- =====================================================
-- SCRIPT DE DATOS DE PRUEBA - CASOS DE PRUEBA
-- =====================================================
-- Inserta: 20 productos, 10 clientes, 10 proveedores,
--          10 ventas, 10 compras
-- =====================================================
-- Uso: Ejecutar sobre una BD con la estructura base
--      (categorias 1-8, empresa, usuarios deben existir)
-- =====================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";
START TRANSACTION;

-- =====================================================
-- 1. PROVEEDORES (10)
-- =====================================================
INSERT INTO `proveedores` (`id`, `nombre`, `tipo_documento`, `cuit`, `localidad`, `direccion`, `telefono`, `email`, `inicio_actividades`, `ingresos_brutos`, `fecha`, `observaciones`) VALUES
(1000, 'PROVEEDOR PRUEBA 1 S.A.', 80, '30111222330', 'San Rafael', 'Calle Falsa 100', '(260) 444-1001', 'proveedor1@test.com', '2020-01-01', '1', NOW(), 'Proveedor de prueba'),
(1001, 'PROVEEDOR PRUEBA 2 S.R.L.', 80, '30111222331', 'Mendoza', 'Av. Libertad 200', '(261) 444-1002', 'proveedor2@test.com', '2020-01-01', '1', NOW(), NULL),
(1002, 'PROVEEDOR PRUEBA 3', 96, '30111222332', 'Godoy Cruz', 'Belgrano 300', '(261) 444-1003', 'proveedor3@test.com', '2020-01-01', '1', NOW(), NULL),
(1003, 'PROVEEDOR PRUEBA 4 S.A.', 80, '30111222333', 'San Rafael', 'San Martín 400', '(260) 444-1004', 'proveedor4@test.com', '2020-01-01', '1', NOW(), NULL),
(1004, 'PROVEEDOR PRUEBA 5', 96, '30111222334', 'Las Heras', 'España 500', '(261) 444-1005', 'proveedor5@test.com', '2020-01-01', '1', NOW(), NULL),
(1005, 'PROVEEDOR PRUEBA 6 S.R.L.', 80, '30111222335', 'Mendoza', 'Colón 600', '(261) 444-1006', 'proveedor6@test.com', '2020-01-01', '1', NOW(), NULL),
(1006, 'PROVEEDOR PRUEBA 7', 96, '30111222336', 'San Rafael', 'Rivadavia 700', '(260) 444-1007', 'proveedor7@test.com', '2020-01-01', '1', NOW(), NULL),
(1007, 'PROVEEDOR PRUEBA 8 S.A.', 80, '30111222337', 'Godoy Cruz', 'Chile 800', '(261) 444-1008', 'proveedor8@test.com', '2020-01-01', '1', NOW(), NULL),
(1008, 'PROVEEDOR PRUEBA 9', 96, '30111222338', 'Las Heras', 'San Juan 900', '(261) 444-1009', 'proveedor9@test.com', '2020-01-01', '1', NOW(), NULL),
(1009, 'PROVEEDOR PRUEBA 10 S.R.L.', 80, '30111222339', 'Mendoza', 'Perú 1000', '(261) 444-1010', 'proveedor10@test.com', '2020-01-01', '1', NOW(), NULL);

-- =====================================================
-- 2. CLIENTES (10)
-- =====================================================
INSERT INTO `clientes` (`id`, `nombre`, `tipo_documento`, `documento`, `condicion_iva`, `email`, `telefono`, `direccion`, `fecha_nacimiento`, `compras`, `ultima_compra`, `fecha`, `observaciones`) VALUES
(1000, 'CLIENTE PRUEBA 1', 80, '27111222330', 1, 'cliente1@test.com', '(260) 555-1001', 'Av. Test 101', NULL, 0, NULL, NOW(), 'Cliente de prueba'),
(1001, 'CLIENTE PRUEBA 2', 80, '27111222331', 1, 'cliente2@test.com', '(260) 555-1002', 'Av. Test 102', NULL, 0, NULL, NOW(), NULL),
(1002, 'CLIENTE PRUEBA 3', 96, '27111222332', 1, 'cliente3@test.com', '(260) 555-1003', 'Av. Test 103', NULL, 0, NULL, NOW(), NULL),
(1003, 'CLIENTE PRUEBA 4', 80, '27111222333', 1, 'cliente4@test.com', '(260) 555-1004', 'Av. Test 104', NULL, 0, NULL, NOW(), NULL),
(1004, 'CLIENTE PRUEBA 5', 96, '27111222334', 1, 'cliente5@test.com', '(260) 555-1005', 'Av. Test 105', NULL, 0, NULL, NOW(), NULL),
(1005, 'CLIENTE PRUEBA 6', 80, '27111222335', 1, 'cliente6@test.com', '(260) 555-1006', 'Av. Test 106', NULL, 0, NULL, NOW(), NULL),
(1006, 'CLIENTE PRUEBA 7', 96, '27111222336', 1, 'cliente7@test.com', '(260) 555-1007', 'Av. Test 107', NULL, 0, NULL, NOW(), NULL),
(1007, 'CLIENTE PRUEBA 8', 80, '27111222337', 1, 'cliente8@test.com', '(260) 555-1008', 'Av. Test 108', NULL, 0, NULL, NOW(), NULL),
(1008, 'CLIENTE PRUEBA 9', 96, '27111222338', 1, 'cliente9@test.com', '(260) 555-1009', 'Av. Test 109', NULL, 0, NULL, NOW(), NULL),
(1009, 'CLIENTE PRUEBA 10', 80, '27111222339', 1, 'cliente10@test.com', '(260) 555-1010', 'Av. Test 110', NULL, 0, NULL, NOW(), NULL);

-- =====================================================
-- 3. PRODUCTOS (20)
-- =====================================================
-- Nota: Usa stock, stock2, stock3 (schema multi-sucursal).
--       Si tu BD tiene "deposito" en vez de stock2, cambia stock2 por deposito.
INSERT INTO `productos` (`id`, `id_categoria`, `codigo`, `id_proveedor`, `descripcion`, `imagen`, `stock`, `stock2`, `stock3`, `stock_medio`, `stock_bajo`, `precio_compra`, `precio_compra_dolar`, `margen_ganancia`, `precio_venta_neto`, `tipo_iva`, `precio_venta`, `precio_venta_mayorista`, `ventas`, `fecha`, `nombre_usuario`, `cambio_desde`) VALUES
(1000, 1, 'PRUEBA001', 1000, 'Producto Prueba 1 - Arroz', NULL, 100.00, 100.00, 0.00, 80.00, 40.00, 500.00, 0.00, 50.00, 750.00, 21.00, 907.50, 850.00, 0, NOW(), 'sistema', 'sistema'),
(1001, 1, 'PRUEBA002', 1000, 'Producto Prueba 2 - Fideos', NULL, 120.00, 120.00, 0.00, 90.00, 45.00, 350.00, 0.00, 55.00, 542.50, 21.00, 656.43, 600.00, 0, NOW(), 'sistema', 'sistema'),
(1002, 1, 'PRUEBA003', 1001, 'Producto Prueba 3 - Aceite', NULL, 80.00, 80.00, 0.00, 60.00, 30.00, 600.00, 0.00, 60.00, 960.00, 21.00, 1161.60, 1050.00, 0, NOW(), 'sistema', 'sistema'),
(1003, 2, 'PRUEBA004', 1001, 'Producto Prueba 4 - Agua', NULL, 200.00, 200.00, 0.00, 150.00, 75.00, 180.00, 0.00, 40.00, 252.00, 21.00, 304.92, 280.00, 0, NOW(), 'sistema', 'sistema'),
(1004, 2, 'PRUEBA005', 1002, 'Producto Prueba 5 - Gaseosa', NULL, 150.00, 150.00, 0.00, 100.00, 50.00, 320.00, 0.00, 50.00, 480.00, 21.00, 580.80, 540.00, 0, NOW(), 'sistema', 'sistema'),
(1005, 2, 'PRUEBA006', 1002, 'Producto Prueba 6 - Jugo', NULL, 90.00, 90.00, 0.00, 60.00, 30.00, 250.00, 0.00, 45.00, 362.50, 21.00, 438.63, 400.00, 0, NOW(), 'sistema', 'sistema'),
(1006, 3, 'PRUEBA007', 1003, 'Producto Prueba 7 - Leche', NULL, 180.00, 180.00, 0.00, 120.00, 60.00, 160.00, 0.00, 35.00, 216.00, 10.50, 238.68, 220.00, 0, NOW(), 'sistema', 'sistema'),
(1007, 3, 'PRUEBA008', 1003, 'Producto Prueba 8 - Yogur', NULL, 110.00, 110.00, 0.00, 80.00, 40.00, 280.00, 0.00, 50.00, 420.00, 21.00, 508.20, 470.00, 0, NOW(), 'sistema', 'sistema'),
(1008, 3, 'PRUEBA009', 1004, 'Producto Prueba 9 - Queso', NULL, 70.00, 70.00, 0.00, 50.00, 25.00, 800.00, 0.00, 55.00, 1240.00, 21.00, 1500.40, 1380.00, 0, NOW(), 'sistema', 'sistema'),
(1009, 4, 'PRUEBA010', 1004, 'Producto Prueba 10 - Carne', NULL, 45.00, 45.00, 0.00, 30.00, 15.00, 2400.00, 0.00, 40.00, 3360.00, 21.00, 4065.60, 3750.00, 0, NOW(), 'sistema', 'sistema'),
(1010, 4, 'PRUEBA011', 1005, 'Producto Prueba 11 - Pollo', NULL, 35.00, 35.00, 0.00, 25.00, 12.00, 1700.00, 0.00, 45.00, 2465.00, 21.00, 2982.65, 2750.00, 0, NOW(), 'sistema', 'sistema'),
(1011, 5, 'PRUEBA012', 1005, 'Producto Prueba 12 - Tomate', NULL, 160.00, 160.00, 0.00, 120.00, 60.00, 280.00, 0.00, 50.00, 420.00, 0.00, 420.00, 390.00, 0, NOW(), 'sistema', 'sistema'),
(1012, 5, 'PRUEBA013', 1006, 'Producto Prueba 13 - Cebolla', NULL, 140.00, 140.00, 0.00, 100.00, 50.00, 220.00, 0.00, 48.00, 325.60, 0.00, 325.60, 300.00, 0, NOW(), 'sistema', 'sistema'),
(1013, 6, 'PRUEBA014', 1006, 'Producto Prueba 14 - Detergente', NULL, 85.00, 85.00, 0.00, 60.00, 30.00, 400.00, 0.00, 52.00, 608.00, 21.00, 735.68, 680.00, 0, NOW(), 'sistema', 'sistema'),
(1014, 6, 'PRUEBA015', 1007, 'Producto Prueba 15 - Lavandina', NULL, 95.00, 95.00, 0.00, 65.00, 32.00, 260.00, 0.00, 50.00, 390.00, 21.00, 471.90, 440.00, 0, NOW(), 'sistema', 'sistema'),
(1015, 7, 'PRUEBA016', 1007, 'Producto Prueba 16 - Pan', NULL, 100.00, 100.00, 0.00, 70.00, 35.00, 350.00, 0.00, 45.00, 507.50, 10.50, 560.79, 520.00, 0, NOW(), 'sistema', 'sistema'),
(1016, 7, 'PRUEBA017', 1008, 'Producto Prueba 17 - Facturas', NULL, 65.00, 65.00, 0.00, 45.00, 22.00, 420.00, 0.00, 50.00, 630.00, 21.00, 762.30, 700.00, 0, NOW(), 'sistema', 'sistema'),
(1017, 8, 'PRUEBA018', 1008, 'Producto Prueba 18 - Chocolate', NULL, 170.00, 170.00, 0.00, 120.00, 60.00, 165.00, 0.00, 60.00, 264.00, 21.00, 319.44, 295.00, 0, NOW(), 'sistema', 'sistema'),
(1018, 8, 'PRUEBA019', 1009, 'Producto Prueba 19 - Caramelos', NULL, 130.00, 130.00, 0.00, 90.00, 45.00, 140.00, 0.00, 55.00, 217.00, 21.00, 262.57, 240.00, 0, NOW(), 'sistema', 'sistema'),
(1019, 8, 'PRUEBA020', 1009, 'Producto Prueba 20 - Galletas', NULL, 155.00, 155.00, 0.00, 110.00, 55.00, 300.00, 0.00, 50.00, 450.00, 21.00, 544.50, 500.00, 0, NOW(), 'sistema', 'sistema');

-- =====================================================
-- 4. VENTAS (10)
-- =====================================================
-- Incluye id_empresa y sucursal (si tu BD no las tiene, quita esas columnas del INSERT)
INSERT INTO `ventas` (`id`, `uuid`, `id_empresa`, `codigo`, `cbte_tipo`, `id_cliente`, `id_vendedor`, `productos`, `sucursal`, `neto`, `neto_gravado`, `base_imponible_0`, `base_imponible_2`, `base_imponible_5`, `base_imponible_10`, `base_imponible_21`, `base_imponible_27`, `iva_2`, `iva_5`, `iva_10`, `iva_21`, `iva_27`, `impuesto`, `impuesto_detalle`, `total`, `metodo_pago`, `estado`, `observaciones_vta`, `observaciones`, `fecha`, `concepto`, `pto_vta`, `fec_desde`, `fec_hasta`, `fec_vencimiento`, `asociado_tipo_cbte`, `asociado_pto_vta`, `asociado_nro_cbte`, `pedido_afip`, `respuesta_afip`) VALUES
(10000, 'a1b2c3d4-e5f6-4a7b-8c9d-0e1f2a3b4c5d', 1, 10000, 1, 1000, 1, '[{"id":"1000","descripcion":"Producto Prueba 1 - Arroz","cantidad":"5","categoria":"1","stock":"0","precio_compra":"500.00","precio":"907.50","total":"4537.50"}]', 'stock', 4537.50, 4537.50, 0.00, 0.00, 0.00, 0.00, 4537.50, 0.00, 0.00, 0.00, 0.00, 952.88, 0.00, 952.88, '[{"id":5,"descripcion":"IVA 21%","baseImponible":"4537.50","iva":"952.88"}]', 5490.38, '[{"tipo":"Efectivo","entrega":"5490.38"}]', 1, '', NULL, '2025-02-01 10:00:00', 1, 1, '', '', '', 0, 0, 0, NULL, NULL),
(10001, 'a1b2c3d4-e5f6-4a7b-8c9d-0e1f2a3b4c5e', 1, 10001, 1, 1001, 1, '[{"id":"1001","descripcion":"Producto Prueba 2 - Fideos","cantidad":"8","categoria":"1","stock":"0","precio_compra":"350.00","precio":"656.43","total":"5251.44"}]', 'stock', 5251.44, 5251.44, 0.00, 0.00, 0.00, 0.00, 5251.44, 0.00, 0.00, 0.00, 0.00, 1102.80, 0.00, 1102.80, '[{"id":5,"descripcion":"IVA 21%","baseImponible":"5251.44","iva":"1102.80"}]', 6354.24, '[{"tipo":"TD-","entrega":"6354.24"}]', 1, '', NULL, '2025-02-02 11:30:00', 1, 1, '', '', '', 0, 0, 0, NULL, NULL),
(10002, 'a1b2c3d4-e5f6-4a7b-8c9d-0e1f2a3b4c5f', 1, 10002, 1, 1002, 1, '[{"id":"1003","descripcion":"Producto Prueba 4 - Agua","cantidad":"15","categoria":"2","stock":"0","precio_compra":"180.00","precio":"304.92","total":"4573.80"}]', 'stock', 4573.80, 4573.80, 0.00, 0.00, 0.00, 0.00, 4573.80, 0.00, 0.00, 0.00, 0.00, 960.50, 0.00, 960.50, '[{"id":5,"descripcion":"IVA 21%","baseImponible":"4573.80","iva":"960.50"}]', 5534.30, '[{"tipo":"Efectivo","entrega":"5534.30"}]', 1, '', NULL, '2025-02-03 09:15:00', 1, 1, '', '', '', 0, 0, 0, NULL, NULL),
(10003, 'a1b2c3d4-e5f6-4a7b-8c9d-0e1f2a3b4c60', 1, 10003, 1, 1003, 1, '[{"id":"1006","descripcion":"Producto Prueba 7 - Leche","cantidad":"10","categoria":"3","stock":"0","precio_compra":"160.00","precio":"238.68","total":"2386.80"}]', 'stock', 2386.80, 2386.80, 0.00, 0.00, 0.00, 2386.80, 0.00, 0.00, 0.00, 250.61, 0.00, 250.61, '[{"id":4,"descripcion":"IVA 10.5%","baseImponible":"2386.80","iva":"250.61"}]', 2637.41, '[{"tipo":"TR--","entrega":"2637.41"}]', 1, '', NULL, '2025-02-04 14:20:00', 1, 1, '', '', '', 0, 0, 0, NULL, NULL),
(10004, 'a1b2c3d4-e5f6-4a7b-8c9d-0e1f2a3b4c61', 1, 10004, 1, 1004, 1, '[{"id":"1009","descripcion":"Producto Prueba 10 - Carne","cantidad":"3","categoria":"4","stock":"0","precio_compra":"2400.00","precio":"4065.60","total":"12196.80"}]', 'stock', 12196.80, 12196.80, 0.00, 0.00, 0.00, 0.00, 12196.80, 0.00, 0.00, 0.00, 0.00, 2561.33, 0.00, 2561.33, '[{"id":5,"descripcion":"IVA 21%","baseImponible":"12196.80","iva":"2561.33"}]', 14758.13, '[{"tipo":"TC-","entrega":"14758.13"}]', 1, '', NULL, '2025-02-05 16:45:00', 1, 1, '', '', '', 0, 0, 0, NULL, NULL),
(10005, 'a1b2c3d4-e5f6-4a7b-8c9d-0e1f2a3b4c62', 1, 10005, 1, 1005, 1, '[{"id":"1000","descripcion":"Producto Prueba 1 - Arroz","cantidad":"4","categoria":"1","stock":"0","precio_compra":"500.00","precio":"907.50","total":"3630"},{"id":"1004","descripcion":"Producto Prueba 5 - Gaseosa","cantidad":"6","categoria":"2","stock":"0","precio_compra":"320.00","precio":"580.80","total":"3484.80"}]', 'stock', 7114.80, 7114.80, 0.00, 0.00, 0.00, 0.00, 7114.80, 0.00, 0.00, 0.00, 0.00, 1494.11, 0.00, 1494.11, '[{"id":5,"descripcion":"IVA 21%","baseImponible":"7114.80","iva":"1494.11"}]', 8608.91, '[{"tipo":"Efectivo","entrega":"8608.91"}]', 1, '', NULL, '2025-02-06 10:30:00', 1, 1, '', '', '', 0, 0, 0, NULL, NULL),
(10006, 'a1b2c3d4-e5f6-4a7b-8c9d-0e1f2a3b4c63', 1, 10006, 1, 1006, 1, '[{"id":"1011","descripcion":"Producto Prueba 12 - Tomate","cantidad":"20","categoria":"5","stock":"0","precio_compra":"280.00","precio":"420","total":"8400"}]', 'stock', 8400.00, 8400.00, 8400.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, '[{"id":3,"descripcion":"IVA 0%","baseImponible":"8400","iva":"0"}]', 8400.00, '[{"tipo":"Efectivo","entrega":"5000"},{"tipo":"TD-","entrega":"3400"}]', 1, '', NULL, '2025-02-07 13:00:00', 1, 1, '', '', '', 0, 0, 0, NULL, NULL),
(10007, 'a1b2c3d4-e5f6-4a7b-8c9d-0e1f2a3b4c64', 1, 10007, 1, 1007, 1, '[{"id":"1017","descripcion":"Producto Prueba 18 - Chocolate","cantidad":"12","categoria":"8","stock":"0","precio_compra":"165.00","precio":"319.44","total":"3833.28"}]', 'stock', 3833.28, 3833.28, 0.00, 0.00, 0.00, 0.00, 3833.28, 0.00, 0.00, 0.00, 0.00, 804.99, 0.00, 804.99, '[{"id":5,"descripcion":"IVA 21%","baseImponible":"3833.28","iva":"804.99"}]', 4638.27, '[{"tipo":"Efectivo","entrega":"4638.27"}]', 1, '', NULL, '2025-02-08 15:20:00', 1, 1, '', '', '', 0, 0, 0, NULL, NULL),
(10008, 'a1b2c3d4-e5f6-4a7b-8c9d-0e1f2a3b4c65', 1, 10008, 1, 1008, 1, '[{"id":"1007","descripcion":"Producto Prueba 8 - Yogur","cantidad":"7","categoria":"3","stock":"0","precio_compra":"280.00","precio":"508.20","total":"3557.40"}]', 'stock', 3557.40, 3557.40, 0.00, 0.00, 0.00, 0.00, 3557.40, 0.00, 0.00, 0.00, 0.00, 747.05, 0.00, 747.05, '[{"id":5,"descripcion":"IVA 21%","baseImponible":"3557.40","iva":"747.05"}]', 4304.45, '[{"tipo":"CC","entrega":"4304.45"}]', 2, '', NULL, '2025-02-09 11:00:00', 1, 1, '', '', '', 0, 0, 0, NULL, NULL),
(10009, 'a1b2c3d4-e5f6-4a7b-8c9d-0e1f2a3b4c66', 1, 10009, 1, 1009, 1, '[{"id":"1019","descripcion":"Producto Prueba 20 - Galletas","cantidad":"9","categoria":"8","stock":"0","precio_compra":"300.00","precio":"544.50","total":"4900.50"}]', 'stock', 4900.50, 4900.50, 0.00, 0.00, 0.00, 0.00, 4900.50, 0.00, 0.00, 0.00, 0.00, 1029.11, 0.00, 1029.11, '[{"id":5,"descripcion":"IVA 21%","baseImponible":"4900.50","iva":"1029.11"}]', 5929.61, '[{"tipo":"TC-","entrega":"5929.61"}]', 1, '', NULL, '2025-02-10 09:45:00', 1, 1, '', '', '', 0, 0, 0, NULL, NULL);

-- =====================================================
-- 5. COMPRAS (10)
-- =====================================================
INSERT INTO `compras` (`id`, `codigo`, `id_proveedor`, `usuarioPedido`, `sucursalDestino`, `usuarioConfirma`, `productos`, `totalNeto`, `iva`, `precepcionesIngresosBrutos`, `precepcionesIva`, `precepcionesGanancias`, `impuestoInterno`, `total`, `estado`, `descuento`, `fecha`, `fechaEntrega`, `fechaPago`, `medioPago`, `observacion`, `tipo`, `remitoNumero`, `numeroFactura`, `fechaEmision`, `observacionFactura`, `fechaIngreso`) VALUES
(1000, 1000, 1000, 'moondesa', 'stock', 'moondesa', '[{"id":"1000","descripcion":"Producto Prueba 1 - Arroz","cantidad":"50","precio":"500","total":"25000"},{"id":"1001","descripcion":"Producto Prueba 2 - Fideos","cantidad":"60","precio":"350","total":"21000"}]', 46000.00, 9660.00, 0.00, 0.00, 0.00, 0.00, '55660', 1, 0.00, '2025-02-01 08:00:00', '2025-02-05', '2025-02-10', 1, 'Compra inicial proveedor prueba 1', 'compra', 'RE-P001', 'FC-P001', '2025-02-01', 'Factura A', '2025-02-05'),
(1001, 1001, 1001, 'moondesa', 'stock', 'moondesa', '[{"id":"1002","descripcion":"Producto Prueba 3 - Aceite","cantidad":"40","precio":"600","total":"24000"},{"id":"1003","descripcion":"Producto Prueba 4 - Agua","cantidad":"100","precio":"180","total":"18000"}]', 42000.00, 8820.00, 0.00, 0.00, 0.00, 0.00, '50820', 1, 0.00, '2025-02-02 09:00:00', '2025-02-06', '2025-02-12', 2, 'Compra proveedor prueba 2', 'compra', 'RE-P002', 'FC-P002', '2025-02-02', 'Factura A', '2025-02-06'),
(1002, 1002, 1002, 'moondesa', 'stock', 'moondesa', '[{"id":"1004","descripcion":"Producto Prueba 5 - Gaseosa","cantidad":"80","precio":"320","total":"25600"},{"id":"1005","descripcion":"Producto Prueba 6 - Jugo","cantidad":"50","precio":"250","total":"12500"}]', 38100.00, 8001.00, 0.00, 0.00, 0.00, 0.00, '46101', 1, 0.00, '2025-02-03 10:00:00', '2025-02-07', '2025-02-14', 1, 'Compra bebidas', 'compra', 'RE-P003', 'FC-P003', '2025-02-03', 'Factura A', '2025-02-07'),
(1003, 1003, 1003, 'moondesa', 'stock', 'moondesa', '[{"id":"1006","descripcion":"Producto Prueba 7 - Leche","cantidad":"120","precio":"160","total":"19200"},{"id":"1007","descripcion":"Producto Prueba 8 - Yogur","cantidad":"70","precio":"280","total":"19600"}]', 38800.00, 4074.00, 0.00, 0.00, 0.00, 0.00, '42874', 1, 0.00, '2025-02-04 11:00:00', '2025-02-08', '2025-02-16', 2, 'Compra lácteos', 'compra', 'RE-P004', 'FC-P004', '2025-02-04', 'Factura A', '2025-02-08'),
(1004, 1004, 1004, 'moondesa', 'stock', 'moondesa', '[{"id":"1008","descripcion":"Producto Prueba 9 - Queso","cantidad":"35","precio":"800","total":"28000"},{"id":"1009","descripcion":"Producto Prueba 10 - Carne","cantidad":"25","precio":"2400","total":"60000"}]', 88000.00, 18480.00, 0.00, 0.00, 0.00, 0.00, '106480', 1, 0.00, '2025-02-05 08:30:00', '2025-02-10', '2025-02-18', 3, 'Compra carnes y lácteos', 'compra', 'RE-P005', 'FC-P005', '2025-02-05', 'Factura A', '2025-02-10'),
(1005, 1005, 1005, 'moondesa', 'stock', 'moondesa', '[{"id":"1010","descripcion":"Producto Prueba 11 - Pollo","cantidad":"30","precio":"1700","total":"51000"},{"id":"1011","descripcion":"Producto Prueba 12 - Tomate","cantidad":"80","precio":"280","total":"22400"}]', 73400.00, 4704.00, 0.00, 0.00, 0.00, 0.00, '78104', 1, 0.00, '2025-02-06 09:30:00', '2025-02-11', '2025-02-20', 1, 'Compra carnes y verduras', 'compra', 'RE-P006', 'FC-P006', '2025-02-06', 'Factura A', '2025-02-11'),
(1006, 1006, 1006, 'moondesa', 'stock', 'moondesa', '[{"id":"1012","descripcion":"Producto Prueba 13 - Cebolla","cantidad":"70","precio":"220","total":"15400"},{"id":"1013","descripcion":"Producto Prueba 14 - Detergente","cantidad":"45","precio":"400","total":"18000"}]', 33400.00, 7014.00, 0.00, 0.00, 0.00, 0.00, '40414', 1, 0.00, '2025-02-07 10:30:00', '2025-02-12', '2025-02-22', 2, 'Compra verduras y limpieza', 'compra', 'RE-P007', 'FC-P007', '2025-02-07', 'Factura A', '2025-02-12'),
(1007, 1007, 1007, 'moondesa', 'stock', 'moondesa', '[{"id":"1014","descripcion":"Producto Prueba 15 - Lavandina","cantidad":"55","precio":"260","total":"14300"},{"id":"1015","descripcion":"Producto Prueba 16 - Pan","cantidad":"60","precio":"350","total":"21000"}]', 35300.00, 3706.50, 0.00, 0.00, 0.00, 0.00, '39006.50', 1, 0.00, '2025-02-08 11:30:00', '2025-02-13', '2025-02-24', 1, 'Compra limpieza y panadería', 'compra', 'RE-P008', 'FC-P008', '2025-02-08', 'Factura A', '2025-02-13'),
(1008, 1008, 1008, 'moondesa', 'stock', 'moondesa', '[{"id":"1016","descripcion":"Producto Prueba 17 - Facturas","cantidad":"40","precio":"420","total":"16800"},{"id":"1017","descripcion":"Producto Prueba 18 - Chocolate","cantidad":"90","precio":"165","total":"14850"}]', 31650.00, 6646.50, 0.00, 0.00, 0.00, 0.00, '38296.50', 1, 0.00, '2025-02-09 08:00:00', '2025-02-14', '2025-02-26', 2, 'Compra panadería y golosinas', 'compra', 'RE-P009', 'FC-P009', '2025-02-09', 'Factura A', '2025-02-14'),
(1009, 1009, 1009, 'moondesa', 'stock', 'moondesa', '[{"id":"1018","descripcion":"Producto Prueba 19 - Caramelos","cantidad":"65","precio":"140","total":"9100"},{"id":"1019","descripcion":"Producto Prueba 20 - Galletas","cantidad":"75","precio":"300","total":"22500"}]', 31600.00, 6636.00, 0.00, 0.00, 0.00, 0.00, '38236', 1, 0.00, '2025-02-10 09:00:00', '2025-02-15', '2025-02-28', 1, 'Compra golosinas', 'compra', 'RE-P010', 'FC-P010', '2025-02-10', 'Factura A', '2025-02-15');

-- =====================================================
-- ACTUALIZAR AUTO_INCREMENT
-- =====================================================
ALTER TABLE `proveedores` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1010;
ALTER TABLE `clientes` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1010;
ALTER TABLE `productos` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1020;
ALTER TABLE `ventas` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10010;
ALTER TABLE `compras` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1010;

COMMIT;

-- =====================================================
-- RESUMEN
-- =====================================================
-- Productos: 1000-1019 (20 productos)
-- Clientes: 1000-1009 (10 clientes)
-- Proveedores: 1000-1009 (10 proveedores)
-- Ventas: 10000-10009 (10 ventas)
-- Compras: 1000-1009 (10 compras)
-- =====================================================
