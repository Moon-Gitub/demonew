#!/usr/bin/env python3
"""
Transforma INSERT de productos (estructura antigua) a estructura nueva.
Uso: python3 transformar-productos-migracion.py < archivo_origen.sql > migrar-datos.sql
O pegar el INSERT en stdin.
"""
import re
import sys

# Origen: id, id_categoria, codigo, codigoProveedor, id_proveedor, descripcion, imagen, stock, deposito, stock_medio, stock_bajo, precio_compra, precio_compra_dolar, margen_ganancia, precio_venta_neto, tipo_iva, precio_venta, precio_venta_mayorista, ventas, fecha, nombre_usuario, esCombo, cambio_desde
# Destino: id, id_categoria, codigo, id_proveedor, descripcion, imagen, stock, stock2, stock3, stock_medio, stock_bajo, precio_compra, precio_compra_dolar, margen_ganancia, precio_venta_neto, tipo_iva, precio_venta, precio_venta_mayorista, ventas, fecha, nombre_usuario, cambio_desde, es_combo, activo

def parse_val(v):
    v = v.strip()
    if v.upper() == 'NULL':
        return None
    return v

def transform_row(match):
    g = match.groups()
    id_, id_cat, codigo, codigo_prov, id_prov, desc, img, stock, deposito, stock_med, stock_baj, prec_comp, prec_comp_dol, margen, prec_venta_neto, tipo_iva, prec_venta, prec_venta_may, ventas, fecha, nom_usuario, esCombo, cambio_desde = g
    
    stock2 = '0.00' if parse_val(deposito) is None else deposito.strip()
    stock3 = '0.00'
    ventas_val = '0' if parse_val(ventas) is None else ventas.strip()
    es_combo = '0'
    activo = '1'
    
    return f"({id_}, {id_cat}, {codigo}, {id_prov}, {desc}, {img}, {stock}, {stock2}, {stock3}, {stock_med}, {stock_baj}, {prec_comp}, {prec_comp_dol}, {margen}, {prec_venta_neto}, {tipo_iva}, {prec_venta}, {prec_venta_may}, {ventas_val}, {fecha}, {nom_usuario}, {cambio_desde}, {es_combo}, {activo})"

def main():
    data = sys.stdin.read()
    # Patrón para cada fila del INSERT
    pat = r"\((\d+),\s*(\d+),\s*'([^']*(?:''[^']*)*)',\s*(NULL|[^,]+),\s*(\d+),\s*'([^']*(?:''[^']*)*)',\s*'([^']*(?:''[^']*)*)',\s*([-\d.]+),\s*([-\d.]+|NULL),\s*([-\d.]+),\s*([-\d.]+),\s*([-\d.]+|NULL),\s*([-\d.]+|NULL),\s*([-\d.]+),\s*([-\d.]+),\s*([-\d.]+|NULL),\s*([-\d.]+|NULL),\s*([-\d.]+|NULL),\s*(\d+|NULL),\s*'([^']*(?:''[^']*)*)',\s*'([^']*(?:''[^']*)*)',\s*(NULL|[^,]+),\s*'([^']*(?:''[^']*)*)'\)"
    
    rows = re.findall(pat, data)
    seen_codes = set()
    result = []
    max_id = 0
    next_id = 440  # Para id=0
    
    for r in rows:
        id_, id_cat, codigo, codigo_prov, id_prov, desc, img, stock, deposito, stock_med, stock_baj, prec_comp, prec_comp_dol, margen, prec_venta_neto, tipo_iva, prec_venta, prec_venta_may, ventas, fecha, nom_usuario, esCombo, cambio_desde = r
        
        if int(id_) > max_id:
            max_id = int(id_)
        
        # Duplicados por codigo (mantener solo el primero)
        if codigo in seen_codes:
            continue
        seen_codes.add(codigo)
        
        if id_ == '0':
            id_ = str(next_id)
            next_id += 1
        
        stock2 = '0.00' if deposito == 'NULL' else deposito
        stock3 = '0.00'
        ventas_val = '0' if ventas == 'NULL' else ventas
        es_combo = '0'
        activo = '1'
        
        row = f"({id_}, {id_cat}, {codigo}, {id_prov}, {desc}, {img}, {stock}, {stock2}, {stock3}, {stock_med}, {stock_baj}, {prec_comp}, {prec_comp_dol}, {margen}, {prec_venta_neto}, {tipo_iva}, {prec_venta}, {prec_venta_may}, {ventas_val}, {fecha}, {nom_usuario}, {cambio_desde}, {es_combo}, {activo})"
        result.append(row)
    
    header = """-- ================================================================
-- MIGRAR DATOS DE PRODUCTOS - Estructura antigua → nueva
-- ================================================================
-- deposito → stock2, stock3=0, codigoProveedor/esCombo omitidos
-- Duplicados (mismo codigo): solo 1. id=0 → asignado id nuevo
-- ================================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET FOREIGN_KEY_CHECKS = 0;
DROP TRIGGER IF EXISTS `prod_insertar`;

INSERT INTO `productos` (`id`, `id_categoria`, `codigo`, `id_proveedor`, `descripcion`, `imagen`, `stock`, `stock2`, `stock3`, `stock_medio`, `stock_bajo`, `precio_compra`, `precio_compra_dolar`, `margen_ganancia`, `precio_venta_neto`, `tipo_iva`, `precio_venta`, `precio_venta_mayorista`, `ventas`, `fecha`, `nombre_usuario`, `cambio_desde`, `es_combo`, `activo`) VALUES
"""
    footer = """
;
CREATE TRIGGER `prod_insertar` AFTER INSERT ON `productos` FOR EACH ROW INSERT INTO productos_historial SELECT 'insertar', NULL, CONVERT_TZ(NOW(), @@session.time_zone, '-3:00'), 
pro.id, pro.stock, pro.precio_compra, pro.precio_venta, pro.precio_venta_mayorista, pro.nombre_usuario, pro.cambio_desde FROM productos AS pro WHERE pro.id = NEW.id;
SET FOREIGN_KEY_CHECKS = 1;
"""
    print(header + ',\n'.join(result) + footer)

if __name__ == '__main__':
    main()
