-- =====================================================
-- DATOS DE PRUEBA PARA SISTEMA POS MOON
-- =====================================================
-- Este script inserta datos de prueba para:
-- - Clientes
-- - Ventas
-- - Cuenta Corriente de Clientes
-- - Cajas (movimientos de caja con diferentes medios de pago)
-- =====================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

-- =====================================================
-- 1. CLIENTES DE PRUEBA
-- =====================================================
-- Nota: Incluye todas las columnas posibles (dominio, mensual, estado_cuenta, estado_bloqueo)
-- para compatibilidad con diferentes versiones del esquema
INSERT INTO `clientes` (`id`, `nombre`, `dominio`, `tipo_documento`, `documento`, `condicion_iva`, `email`, `telefono`, `direccion`, `fecha_nacimiento`, `compras`, `ultima_compra`, `mensual`, `fecha`, `observaciones`, `estado_cuenta`, `estado_bloqueo`) VALUES
(100, 'SUPERMERCADO CENTRAL', NULL, 80, '20123456789', 1, 'contacto@supercentral.com', '(260) 444-1234', 'Av. San Martín 500', NULL, 45, '2025-01-15 14:30:00', 0, NOW(), 'Cliente frecuente', 0, 0),
(101, 'ALMACEN DON JUAN', NULL, 80, '20987654321', 1, 'ventas@donjuan.com', '(260) 444-5678', 'Rivadavia 850', NULL, 32, '2025-01-14 10:15:00', 0, NOW(), NULL, 0, 0),
(102, 'KIOSCO LA ESQUINA', NULL, 96, '12345678', 5, 'kiosco@laesquina.com', '(260) 444-9012', 'Esquina Mitre y Belgrano', NULL, 18, '2025-01-13 16:45:00', 0, NOW(), 'Cliente minorista', 0, 0),
(103, 'MINIMARKET SAN RAFAEL', NULL, 80, '20345678901', 1, 'info@minimarket.com', '(260) 444-3456', 'Av. Libertador 1200', NULL, 28, '2025-01-12 09:20:00', 0, NOW(), NULL, 0, 0),
(104, 'DISTRIBUIDORA EL PROGRESO', NULL, 80, '20456789012', 1, 'pedidos@elprogreso.com', '(260) 444-7890', 'Ruta 40 Km 5', NULL, 15, '2025-01-11 11:00:00', 0, NOW(), 'Cliente mayorista', 0, 0),
(105, 'CONSUMIDOR FINAL', NULL, 99, '0', 5, NULL, NULL, NULL, NULL, 0, NULL, 0, NOW(), 'Cliente genérico', 0, 0);

-- =====================================================
-- 2. VENTAS DE PRUEBA (25 ventas con estructura completa)
-- =====================================================
-- Incluye todos los campos requeridos por la tabla ventas
INSERT INTO `ventas` (`id`, `uuid`, `codigo`, `cbte_tipo`, `id_cliente`, `id_vendedor`, `productos`, `neto`, `neto_gravado`, `base_imponible_0`, `base_imponible_2`, `base_imponible_5`, `base_imponible_10`, `base_imponible_21`, `base_imponible_27`, `iva_2`, `iva_5`, `iva_10`, `iva_21`, `iva_27`, `impuesto`, `impuesto_detalle`, `total`, `metodo_pago`, `estado`, `observaciones_vta`, `observaciones`, `fecha`, `concepto`, `pto_vta`, `fec_desde`, `fec_hasta`, `fec_vencimiento`, `asociado_tipo_cbte`, `asociado_pto_vta`, `asociado_nro_cbte`, `pedido_afip`, `respuesta_afip`) VALUES
-- Ventas recientes con IVA 21%
(1000, '550e8400-e29b-41d4-a716-446655440001', 1001, 1, 100, 1, '[{"id":"1","descripcion":"Producto A","cantidad":"10","categoria":"1","stock":"0","precio_compra":"0.00","precio":"1500","total":"15000"}]', 15000.00, 15000.00, 0.00, 0.00, 0.00, 0.00, 15000.00, 0.00, 0.00, 0.00, 0.00, 3150.00, 0.00, 3150.00, '[{"id":5,"descripcion":"IVA 21%","baseImponible":"15000","iva":"3150"}]', 18150.00, '[{"tipo":"Efectivo","entrega":"18150"}]', 1, '', NULL, '2025-01-20 14:30:00', 1, 1, '', '', '', 0, 0, 0, NULL, NULL),
(1001, '550e8400-e29b-41d4-a716-446655440002', 1002, 1, 101, 1, '[{"id":"2","descripcion":"Producto B","cantidad":"5","categoria":"1","stock":"0","precio_compra":"0.00","precio":"2500","total":"12500"}]', 12500.00, 12500.00, 0.00, 0.00, 0.00, 0.00, 12500.00, 0.00, 0.00, 0.00, 0.00, 2625.00, 0.00, 2625.00, '[{"id":5,"descripcion":"IVA 21%","baseImponible":"12500","iva":"2625"}]', 15125.00, '[{"tipo":"TD-","entrega":"15125"}]', 1, '', NULL, '2025-01-20 10:15:00', 1, 1, '', '', '', 0, 0, 0, NULL, NULL),
(1002, '550e8400-e29b-41d4-a716-446655440003', 1003, 1, 102, 1, '[{"id":"3","descripcion":"Producto C","cantidad":"20","categoria":"1","stock":"0","precio_compra":"0.00","precio":"500","total":"10000"}]', 10000.00, 10000.00, 0.00, 0.00, 0.00, 0.00, 10000.00, 0.00, 0.00, 0.00, 0.00, 2100.00, 0.00, 2100.00, '[{"id":5,"descripcion":"IVA 21%","baseImponible":"10000","iva":"2100"}]', 12100.00, '[{"tipo":"Efectivo","entrega":"12100"}]', 1, '', NULL, '2025-01-19 16:45:00', 1, 1, '', '', '', 0, 0, 0, NULL, NULL),
(1003, '550e8400-e29b-41d4-a716-446655440004', 1004, 1, 103, 1, '[{"id":"4","descripcion":"Producto D","cantidad":"8","categoria":"1","stock":"0","precio_compra":"0.00","precio":"1800","total":"14400"}]', 14400.00, 14400.00, 0.00, 0.00, 0.00, 0.00, 14400.00, 0.00, 0.00, 0.00, 0.00, 3024.00, 0.00, 3024.00, '[{"id":5,"descripcion":"IVA 21%","baseImponible":"14400","iva":"3024"}]', 17424.00, '[{"tipo":"TR--","entrega":"17424"}]', 1, '', NULL, '2025-01-19 09:20:00', 1, 1, '', '', '', 0, 0, 0, NULL, NULL),
(1004, '550e8400-e29b-41d4-a716-446655440005', 1005, 1, 104, 1, '[{"id":"5","descripcion":"Producto E","cantidad":"15","categoria":"1","stock":"0","precio_compra":"0.00","precio":"3200","total":"48000"}]', 48000.00, 48000.00, 0.00, 0.00, 0.00, 0.00, 48000.00, 0.00, 0.00, 0.00, 0.00, 10080.00, 0.00, 10080.00, '[{"id":5,"descripcion":"IVA 21%","baseImponible":"48000","iva":"10080"}]', 58080.00, '[{"tipo":"TC-","entrega":"58080"}]', 1, '', NULL, '2025-01-18 11:00:00', 1, 1, '', '', '', 0, 0, 0, NULL, NULL),
(1005, '550e8400-e29b-41d4-a716-446655440006', 1006, 1, 100, 1, '[{"id":"1","descripcion":"Producto A","cantidad":"12","categoria":"1","stock":"0","precio_compra":"0.00","precio":"1500","total":"18000"}]', 18000.00, 18000.00, 0.00, 0.00, 0.00, 0.00, 18000.00, 0.00, 0.00, 0.00, 0.00, 3780.00, 0.00, 3780.00, '[{"id":5,"descripcion":"IVA 21%","baseImponible":"18000","iva":"3780"}]', 21780.00, '[{"tipo":"Efectivo","entrega":"21780"}]', 1, '', NULL, '2025-01-18 15:30:00', 1, 1, '', '', '', 0, 0, 0, NULL, NULL),
(1006, '550e8400-e29b-41d4-a716-446655440007', 1007, 1, 101, 1, '[{"id":"2","descripcion":"Producto B","cantidad":"6","categoria":"1","stock":"0","precio_compra":"0.00","precio":"2500","total":"15000"}]', 15000.00, 15000.00, 0.00, 0.00, 0.00, 0.00, 15000.00, 0.00, 0.00, 0.00, 0.00, 3150.00, 0.00, 3150.00, '[{"id":5,"descripcion":"IVA 21%","baseImponible":"15000","iva":"3150"}]', 18150.00, '[{"tipo":"TD-","entrega":"18150"}]', 1, '', NULL, '2025-01-17 13:20:00', 1, 1, '', '', '', 0, 0, 0, NULL, NULL),
(1007, '550e8400-e29b-41d4-a716-446655440008', 1008, 1, 105, 1, '[{"id":"3","descripcion":"Producto C","cantidad":"3","categoria":"1","stock":"0","precio_compra":"0.00","precio":"500","total":"1500"}]', 1500.00, 1500.00, 0.00, 0.00, 0.00, 0.00, 1500.00, 0.00, 0.00, 0.00, 0.00, 315.00, 0.00, 315.00, '[{"id":5,"descripcion":"IVA 21%","baseImponible":"1500","iva":"315"}]', 1815.00, '[{"tipo":"Efectivo","entrega":"1815"}]', 1, '', NULL, '2025-01-17 17:00:00', 1, 1, '', '', '', 0, 0, 0, NULL, NULL),
(1008, '550e8400-e29b-41d4-a716-446655440009', 1009, 1, 102, 1, '[{"id":"4","descripcion":"Producto D","cantidad":"25","categoria":"1","stock":"0","precio_compra":"0.00","precio":"800","total":"20000"}]', 20000.00, 20000.00, 0.00, 0.00, 0.00, 0.00, 20000.00, 0.00, 0.00, 0.00, 0.00, 4200.00, 0.00, 4200.00, '[{"id":5,"descripcion":"IVA 21%","baseImponible":"20000","iva":"4200"}]', 24200.00, '[{"tipo":"Efectivo","entrega":"15000"},{"tipo":"TD-","entrega":"9200"}]', 1, '', NULL, '2025-01-16 11:30:00', 1, 1, '', '', '', 0, 0, 0, NULL, NULL),
(1009, '550e8400-e29b-41d4-a716-446655440010', 1010, 1, 103, 1, '[{"id":"5","descripcion":"Producto E","cantidad":"10","categoria":"1","stock":"0","precio_compra":"0.00","precio":"2200","total":"22000"}]', 22000.00, 22000.00, 0.00, 0.00, 0.00, 0.00, 22000.00, 0.00, 0.00, 0.00, 0.00, 4620.00, 0.00, 4620.00, '[{"id":5,"descripcion":"IVA 21%","baseImponible":"22000","iva":"4620"}]', 26620.00, '[{"tipo":"TC-","entrega":"26620"}]', 1, '', NULL, '2025-01-16 14:00:00', 1, 1, '', '', '', 0, 0, 0, NULL, NULL),
(1010, '550e8400-e29b-41d4-a716-446655440011', 1011, 1, 104, 1, '[{"id":"1","descripcion":"Producto A","cantidad":"30","categoria":"1","stock":"0","precio_compra":"0.00","precio":"1200","total":"36000"}]', 36000.00, 36000.00, 0.00, 0.00, 0.00, 0.00, 36000.00, 0.00, 0.00, 0.00, 0.00, 7560.00, 0.00, 7560.00, '[{"id":5,"descripcion":"IVA 21%","baseImponible":"36000","iva":"7560"}]', 43560.00, '[{"tipo":"TR--","entrega":"43560"}]', 1, '', NULL, '2025-01-15 09:00:00', 1, 1, '', '', '', 0, 0, 0, NULL, NULL),
(1011, '550e8400-e29b-41d4-a716-446655440012', 1012, 1, 100, 1, '[{"id":"2","descripcion":"Producto B","cantidad":"7","categoria":"1","stock":"0","precio_compra":"0.00","precio":"2800","total":"19600"}]', 19600.00, 19600.00, 0.00, 0.00, 0.00, 0.00, 19600.00, 0.00, 0.00, 0.00, 0.00, 4116.00, 0.00, 4116.00, '[{"id":5,"descripcion":"IVA 21%","baseImponible":"19600","iva":"4116"}]', 23716.00, '[{"tipo":"Efectivo","entrega":"23716"}]', 1, '', NULL, '2025-01-15 16:20:00', 1, 1, '', '', '', 0, 0, 0, NULL, NULL),
(1012, '550e8400-e29b-41d4-a716-446655440013', 1013, 1, 101, 1, '[{"id":"3","descripcion":"Producto C","cantidad":"15","categoria":"1","stock":"0","precio_compra":"0.00","precio":"600","total":"9000"}]', 9000.00, 9000.00, 0.00, 0.00, 0.00, 0.00, 9000.00, 0.00, 0.00, 0.00, 0.00, 1890.00, 0.00, 1890.00, '[{"id":5,"descripcion":"IVA 21%","baseImponible":"9000","iva":"1890"}]', 10890.00, '[{"tipo":"TD-","entrega":"10890"}]', 1, '', NULL, '2025-01-14 10:45:00', 1, 1, '', '', '', 0, 0, 0, NULL, NULL),
(1013, '550e8400-e29b-41d4-a716-446655440014', 1014, 1, 102, 1, '[{"id":"4","descripcion":"Producto D","cantidad":"12","categoria":"1","stock":"0","precio_compra":"0.00","precio":"1900","total":"22800"}]', 22800.00, 22800.00, 0.00, 0.00, 0.00, 0.00, 22800.00, 0.00, 0.00, 0.00, 0.00, 4788.00, 0.00, 4788.00, '[{"id":5,"descripcion":"IVA 21%","baseImponible":"22800","iva":"4788"}]', 27588.00, '[{"tipo":"Efectivo","entrega":"27588"}]', 1, '', NULL, '2025-01-14 13:15:00', 1, 1, '', '', '', 0, 0, 0, NULL, NULL),
(1014, '550e8400-e29b-41d4-a716-446655440015', 1015, 1, 103, 1, '[{"id":"5","descripcion":"Producto E","cantidad":"20","categoria":"1","stock":"0","precio_compra":"0.00","precio":"1500","total":"30000"}]', 30000.00, 30000.00, 0.00, 0.00, 0.00, 0.00, 30000.00, 0.00, 0.00, 0.00, 0.00, 6300.00, 0.00, 6300.00, '[{"id":5,"descripcion":"IVA 21%","baseImponible":"30000","iva":"6300"}]', 36300.00, '[{"tipo":"TC-","entrega":"36300"}]', 1, '', NULL, '2025-01-13 08:30:00', 1, 1, '', '', '', 0, 0, 0, NULL, NULL),
(1015, '550e8400-e29b-41d4-a716-446655440016', 1016, 1, 104, 1, '[{"id":"1","descripcion":"Producto A","cantidad":"18","categoria":"1","stock":"0","precio_compra":"0.00","precio":"1400","total":"25200"}]', 25200.00, 25200.00, 0.00, 0.00, 0.00, 0.00, 25200.00, 0.00, 0.00, 0.00, 0.00, 5292.00, 0.00, 5292.00, '[{"id":5,"descripcion":"IVA 21%","baseImponible":"25200","iva":"5292"}]', 30492.00, '[{"tipo":"TR--","entrega":"30492"}]', 1, '', NULL, '2025-01-13 15:00:00', 1, 1, '', '', '', 0, 0, 0, NULL, NULL),
(1016, '550e8400-e29b-41d4-a716-446655440017', 1017, 1, 105, 1, '[{"id":"2","descripcion":"Producto B","cantidad":"4","categoria":"1","stock":"0","precio_compra":"0.00","precio":"3000","total":"12000"}]', 12000.00, 12000.00, 0.00, 0.00, 0.00, 0.00, 12000.00, 0.00, 0.00, 0.00, 0.00, 2520.00, 0.00, 2520.00, '[{"id":5,"descripcion":"IVA 21%","baseImponible":"12000","iva":"2520"}]', 14520.00, '[{"tipo":"Efectivo","entrega":"14520"}]', 1, '', NULL, '2025-01-12 12:00:00', 1, 1, '', '', '', 0, 0, 0, NULL, NULL),
(1017, '550e8400-e29b-41d4-a716-446655440018', 1018, 1, 100, 1, '[{"id":"3","descripcion":"Producto C","cantidad":"22","categoria":"1","stock":"0","precio_compra":"0.00","precio":"700","total":"15400"}]', 15400.00, 15400.00, 0.00, 0.00, 0.00, 0.00, 15400.00, 0.00, 0.00, 0.00, 0.00, 3234.00, 0.00, 3234.00, '[{"id":5,"descripcion":"IVA 21%","baseImponible":"15400","iva":"3234"}]', 18634.00, '[{"tipo":"Efectivo","entrega":"10000"},{"tipo":"TD-","entrega":"8634"}]', 1, '', NULL, '2025-01-12 17:30:00', 1, 1, '', '', '', 0, 0, 0, NULL, NULL),
(1018, '550e8400-e29b-41d4-a716-446655440019', 1019, 1, 101, 1, '[{"id":"4","descripcion":"Producto D","cantidad":"9","categoria":"1","stock":"0","precio_compra":"0.00","precio":"2100","total":"18900"}]', 18900.00, 18900.00, 0.00, 0.00, 0.00, 0.00, 18900.00, 0.00, 0.00, 0.00, 0.00, 3969.00, 0.00, 3969.00, '[{"id":5,"descripcion":"IVA 21%","baseImponible":"18900","iva":"3969"}]', 22869.00, '[{"tipo":"TC-","entrega":"22869"}]', 1, '', NULL, '2025-01-11 09:15:00', 1, 1, '', '', '', 0, 0, 0, NULL, NULL),
(1019, '550e8400-e29b-41d4-a716-446655440020', 1020, 1, 102, 1, '[{"id":"5","descripcion":"Producto E","cantidad":"14","categoria":"1","stock":"0","precio_compra":"0.00","precio":"1100","total":"15400"}]', 15400.00, 15400.00, 0.00, 0.00, 0.00, 0.00, 15400.00, 0.00, 0.00, 0.00, 0.00, 3234.00, 0.00, 3234.00, '[{"id":5,"descripcion":"IVA 21%","baseImponible":"15400","iva":"3234"}]', 18634.00, '[{"tipo":"Efectivo","entrega":"18634"}]', 1, '', NULL, '2025-01-11 14:45:00', 1, 1, '', '', '', 0, 0, 0, NULL, NULL),
-- Ventas con IVA 0% (exentas)
(1020, '550e8400-e29b-41d4-a716-446655440021', 1021, 1, 100, 1, '[{"id":"6","descripcion":"Producto Exento","cantidad":"5","categoria":"1","stock":"0","precio_compra":"0.00","precio":"2000","total":"10000"}]', 10000.00, 10000.00, 10000.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, '[{"id":3,"descripcion":"IVA 0%","baseImponible":"10000","iva":"0"}]', 10000.00, '[{"tipo":"Efectivo","entrega":"10000"}]', 1, '', NULL, '2025-01-10 10:00:00', 1, 1, '', '', '', 0, 0, 0, NULL, NULL),
(1021, '550e8400-e29b-41d4-a716-446655440022', 1022, 1, 101, 1, '[{"id":"7","descripcion":"Producto Exento 2","cantidad":"8","categoria":"1","stock":"0","precio_compra":"0.00","precio":"1500","total":"12000"}]', 12000.00, 12000.00, 12000.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, '[{"id":3,"descripcion":"IVA 0%","baseImponible":"12000","iva":"0"}]', 12000.00, '[{"tipo":"TD-","entrega":"12000"}]', 1, '', NULL, '2025-01-09 11:30:00', 1, 1, '', '', '', 0, 0, 0, NULL, NULL),
(1022, '550e8400-e29b-41d4-a716-446655440023', 1023, 1, 102, 1, '[{"id":"8","descripcion":"Producto Exento 3","cantidad":"12","categoria":"1","stock":"0","precio_compra":"0.00","precio":"800","total":"9600"}]', 9600.00, 9600.00, 9600.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, '[{"id":3,"descripcion":"IVA 0%","baseImponible":"9600","iva":"0"}]', 9600.00, '[{"tipo":"Efectivo","entrega":"9600"}]', 1, '', NULL, '2025-01-08 15:20:00', 1, 1, '', '', '', 0, 0, 0, NULL, NULL),
-- Ventas a cuenta corriente (estado 2)
(1023, '550e8400-e29b-41d4-a716-446655440024', 1024, 1, 103, 1, '[{"id":"9","descripcion":"Producto CC","cantidad":"10","categoria":"1","stock":"0","precio_compra":"0.00","precio":"2500","total":"25000"}]', 25000.00, 25000.00, 0.00, 0.00, 0.00, 0.00, 25000.00, 0.00, 0.00, 0.00, 0.00, 5250.00, 0.00, 5250.00, '[{"id":5,"descripcion":"IVA 21%","baseImponible":"25000","iva":"5250"}]', 30250.00, '[{"tipo":"CC","entrega":"30250"}]', 2, '', NULL, '2025-01-07 09:00:00', 1, 1, '', '', '', 0, 0, 0, NULL, NULL),
(1024, '550e8400-e29b-41d4-a716-446655440025', 1025, 1, 104, 1, '[{"id":"10","descripcion":"Producto CC 2","cantidad":"20","categoria":"1","stock":"0","precio_compra":"0.00","precio":"1800","total":"36000"}]', 36000.00, 36000.00, 0.00, 0.00, 0.00, 0.00, 36000.00, 0.00, 0.00, 0.00, 0.00, 7560.00, 0.00, 7560.00, '[{"id":5,"descripcion":"IVA 21%","baseImponible":"36000","iva":"7560"}]', 43560.00, '[{"tipo":"CC","entrega":"43560"}]', 2, '', NULL, '2025-01-06 14:30:00', 1, 1, '', '', '', 0, 0, 0, NULL, NULL);

-- =====================================================
-- 2.1. VENTAS FACTURADAS (CAE de AFIP)
-- =====================================================
INSERT INTO `ventas_factura` (`id`, `id_venta`, `fec_factura`, `nro_cbte`, `cae`, `fec_vto_cae`) VALUES
(100, 1000, '20250120', 1, '71234567890123', '20250220'),
(101, 1001, '20250120', 2, '71234567890124', '20250220'),
(102, 1002, '20250119', 3, '71234567890125', '20250219'),
(103, 1003, '20250119', 4, '71234567890126', '20250219'),
(104, 1004, '20250118', 5, '71234567890127', '20250218'),
(105, 1005, '20250118', 6, '71234567890128', '20250218'),
(106, 1006, '20250117', 7, '71234567890129', '20250217'),
(107, 1007, '20250117', 8, '71234567890130', '20250217'),
(108, 1008, '20250116', 9, '71234567890131', '20250216'),
(109, 1009, '20250116', 10, '71234567890132', '20250216'),
(110, 1010, '20250115', 11, '71234567890133', '20250215'),
(111, 1011, '20250115', 12, '71234567890134', '20250215'),
(112, 1012, '20250114', 13, '71234567890135', '20250214'),
(113, 1013, '20250114', 14, '71234567890136', '20250214'),
(114, 1014, '20250113', 15, '71234567890137', '20250213');

-- =====================================================
-- 3. CUENTA CORRIENTE DE CLIENTES
-- =====================================================
-- tipo: 0 = Debe (venta a crédito), 1 = Haber (pago)
INSERT INTO `clientes_cuenta_corriente` (`id`, `fecha`, `id_cliente`, `tipo`, `descripcion`, `id_venta`, `importe`, `metodo_pago`, `numero_recibo`) VALUES
-- Deudas (tipo 0)
(1000, '2025-01-15 14:30:00', 100, 0, 'Venta a crédito - Factura 1001', 1000, 18150.00, NULL, NULL),
(1001, '2025-01-14 10:15:00', 101, 0, 'Venta a crédito - Factura 1002', 1001, 15125.00, NULL, NULL),
(1002, '2025-01-13 16:45:00', 102, 0, 'Venta a crédito - Factura 1003', 1002, 12100.00, NULL, NULL),
(1003, '2025-01-12 09:20:00', 103, 0, 'Venta a crédito - Factura 1004', 1003, 17424.00, NULL, NULL),
(1004, '2025-01-11 11:00:00', 104, 0, 'Venta a crédito - Factura 1005', 1004, 58080.00, NULL, NULL),
-- Pagos parciales (tipo 1)
(1005, '2025-01-16 10:00:00', 100, 1, 'Pago parcial cuenta corriente', 1000, 10000.00, 'Efectivo', 1),
(1006, '2025-01-16 11:30:00', 100, 1, 'Pago parcial cuenta corriente', 1000, 5000.00, 'Transferencia', 2),
(1007, '2025-01-15 15:00:00', 101, 1, 'Pago total cuenta corriente', 1001, 15125.00, 'Tarjeta Débito', 3),
(1008, '2025-01-14 17:00:00', 102, 1, 'Pago total cuenta corriente', 1002, 12100.00, 'Efectivo', 4),
(1009, '2025-01-13 10:00:00', 103, 1, 'Pago parcial cuenta corriente', 1003, 10000.00, 'Transferencia', 5),
(1010, '2025-01-12 14:00:00', 104, 1, 'Pago parcial cuenta corriente', 1004, 30000.00, 'Tarjeta Crédito', 6);

-- =====================================================
-- 4. MOVIMIENTOS DE CAJA (DIFERENTES MEDIOS DE PAGO)
-- =====================================================
-- tipo: 1 = Ingreso, 2 = Egreso
INSERT INTO `cajas` (`id`, `fecha`, `id_usuario`, `punto_venta`, `tipo`, `monto`, `medio_pago`, `descripcion`, `codigo_venta`, `id_venta`, `id_cliente_proveedor`, `observaciones`) VALUES
-- Ingresos en efectivo
(1000, '2025-01-15 14:30:00', 1, 1, 1, 18150.00, 'Efectivo', 'Venta Factura 1001 - SUPERMERCADO CENTRAL', '1001', 1000, 100, NULL),
(1001, '2025-01-13 16:45:00', 1, 1, 1, 12100.00, 'Efectivo', 'Venta Factura 1003 - KIOSCO LA ESQUINA', '1003', 1002, 102, NULL),
(1002, '2025-01-10 15:30:00', 1, 1, 1, 21780.00, 'Efectivo', 'Venta Factura 1006 - SUPERMERCADO CENTRAL', '1006', 1005, 100, NULL),
(1003, '2025-01-08 17:00:00', 1, 1, 1, 1815.00, 'Efectivo', 'Venta Factura 1008 - CONSUMIDOR FINAL', '1008', 1007, 105, NULL),
-- Ingresos con tarjeta débito
(1004, '2025-01-14 10:15:00', 1, 1, 1, 15125.00, 'Tarjeta Débito', 'Venta Factura 1002 - ALMACEN DON JUAN', '1002', 1001, 101, NULL),
(1005, '2025-01-09 13:20:00', 1, 1, 1, 18150.00, 'Tarjeta Débito', 'Venta Factura 1007 - ALMACEN DON JUAN', '1007', 1006, 101, NULL),
-- Ingresos con tarjeta crédito
(1006, '2025-01-11 11:00:00', 1, 1, 1, 58080.00, 'Tarjeta Crédito', 'Venta Factura 1005 - DISTRIBUIDORA EL PROGRESO', '1005', 1004, 104, NULL),
-- Ingresos por transferencia
(1007, '2025-01-12 09:20:00', 1, 1, 1, 17424.00, 'Transferencia', 'Venta Factura 1004 - MINIMARKET SAN RAFAEL', '1004', 1003, 103, NULL),
-- Ingresos por cobro de cuenta corriente
(1008, '2025-01-16 10:00:00', 1, 1, 1, 10000.00, 'Efectivo', 'Cobro Cta. Cte. - SUPERMERCADO CENTRAL', NULL, NULL, 100, 'Pago parcial'),
(1009, '2025-01-16 11:30:00', 1, 1, 1, 5000.00, 'Transferencia', 'Cobro Cta. Cte. - SUPERMERCADO CENTRAL', NULL, NULL, 100, 'Pago parcial'),
(1010, '2025-01-15 15:00:00', 1, 1, 1, 15125.00, 'Tarjeta Débito', 'Cobro Cta. Cte. - ALMACEN DON JUAN', NULL, NULL, 101, 'Pago total'),
(1011, '2025-01-14 17:00:00', 1, 1, 1, 12100.00, 'Efectivo', 'Cobro Cta. Cte. - KIOSCO LA ESQUINA', NULL, NULL, 102, 'Pago total'),
(1012, '2025-01-13 10:00:00', 1, 1, 1, 10000.00, 'Transferencia', 'Cobro Cta. Cte. - MINIMARKET SAN RAFAEL', NULL, NULL, 103, 'Pago parcial'),
(1013, '2025-01-12 14:00:00', 1, 1, 1, 30000.00, 'Tarjeta Crédito', 'Cobro Cta. Cte. - DISTRIBUIDORA EL PROGRESO', NULL, NULL, 104, 'Pago parcial'),
-- Egresos (ejemplos)
(1014, '2025-01-15 16:00:00', 1, 1, 2, 5000.00, 'Efectivo', 'Pago a proveedor', NULL, NULL, NULL, 'Compra de mercadería'),
(1015, '2025-01-14 12:00:00', 1, 1, 2, 2500.00, 'Transferencia', 'Gasto administrativo', NULL, NULL, NULL, 'Servicios');

-- =====================================================
-- RESUMEN DE DATOS INSERTADOS
-- =====================================================
-- Clientes: 6 registros (incluyendo Consumidor Final)
-- Ventas: 25 registros con estructura completa
--   - 20 ventas con IVA 21%
--   - 3 ventas con IVA 0% (exentas)
--   - 2 ventas a cuenta corriente (estado 2)
-- Ventas Facturadas: 15 registros con CAE de AFIP
-- Cuenta Corriente: 11 registros (6 deudas + 5 pagos)
-- Cajas: 16 registros (13 ingresos + 3 egresos)
-- 
-- Medios de pago utilizados en ventas:
-- - Efectivo (Efectivo)
-- - Tarjeta Débito (TD-)
-- - Tarjeta Crédito (TC-)
-- - Transferencia (TR--)
-- - Cuenta Corriente (CC)
-- - Combinaciones de métodos
-- 
-- Fechas: Ventas distribuidas desde 2025-01-06 hasta 2025-01-20
-- 
-- NOTA: Todas las ventas tienen estructura completa con:
-- - Todos los campos de base imponible e IVA
-- - impuesto_detalle en formato JSON
-- - metodo_pago en formato JSON
-- - productos en formato JSON con estructura completa
-- =====================================================

