#!/usr/bin/env python3
"""
Migra datos de productos desde estructura antigua a nueva.

Lee:
  - migracion/productos/original_datos.sql  → INSERT con estructura antigua
  - migracion/productos/actual.sql          → estructura destino (CREATE TABLE o referencia)

Escribe:
  - migracion/productos/migrado.sql         → INSERT transformado para tabla nueva

Mapeo de columnas:
  - codigoProveedor → omitido
  - deposito → stock2 (NULL→0.00)
  - stock3 → 0.00 (nuevo)
  - esCombo → es_combo (0)
  - activo → 1
  - Duplicados (id=0, mismo codigo) → solo 1 por codigo
  - id=0 → se asigna id siguiente disponible
"""

import re
import os
from pathlib import Path

DIR = Path(__file__).resolve().parent
ORIGINAL = DIR / "original_datos.sql"
ACTUAL = DIR / "actual.sql"
SALIDA = DIR / "migrado.sql"


def parsear_valor(v):
    v = v.strip()
    if v.upper() == "NULL":
        return None
    return v


def extraer_valores_fila(texto_fila):
    """Extrae los 23 valores de una fila del INSERT antiguo."""
    valores = []
    i = 0
    n = len(texto_fila)
    while i < n:
        # Saltar espacios y comas iniciales
        while i < n and texto_fila[i] in " \t,":
            i += 1
        if i >= n:
            break
        c = texto_fila[i]
        if c == "'":
            # String: buscar cierre. Escapes: '' (SQL) y \' (backslash)
            j = i + 1
            while j < n:
                if texto_fila[j] == "\\" and j + 1 < n:
                    j += 2  # \X → saltar ambos
                    continue
                if texto_fila[j] == "'":
                    if j + 1 < n and texto_fila[j + 1] == "'":
                        j += 2  # '' → SQL escaped quote
                        continue
                    break
                j += 1
            valores.append(texto_fila[i : j + 1])
            i = j + 1
        elif texto_fila[i : i + 4].upper() == "NULL" and (i + 4 >= n or texto_fila[i + 4] in ", )"):
            valores.append("NULL")
            i += 4
        else:
            # Número u otro
            j = i
            while j < n and texto_fila[j] not in ",)":
                j += 1
            valores.append(texto_fila[i:j].strip())
            i = j
    return valores


def extraer_filas_insert(contenido):
    """Extrae cada fila ( ... ) del INSERT, respetando strings con paréntesis."""
    filas = []
    i = 0
    n = len(contenido)
    while i < n:
        i = contenido.find("(", i)
        if i < 0:
            break
        depth = 0
        j = i
        in_string = False
        escape = False
        while j < n:
            c = contenido[j]
            if escape:
                escape = False
                j += 1
                continue
            if c == "\\" and in_string:
                escape = True
                j += 1
                continue
            if in_string:
                if c == "'" and (j + 1 >= n or contenido[j + 1] != "'"):
                    in_string = False
                elif c == "'" and j + 1 < n and contenido[j + 1] == "'":
                    j += 1
                j += 1
                continue
            if c == "'":
                in_string = True
                j += 1
                continue
            if c == "(":
                depth += 1
                j += 1
                continue
            if c == ")":
                depth -= 1
                if depth == 0:
                    filas.append(contenido[i + 1 : j])
                    break
                j += 1
                continue
            j += 1
        i = j + 1 if j < n else n
    return filas


def parsear_insert_antiguo(contenido):
    """
    Estructura antigua: id, id_categoria, codigo, codigoProveedor, id_proveedor,
    descripcion, imagen, stock, deposito, stock_medio, stock_bajo, precio_compra,
    precio_compra_dolar, margen_ganancia, precio_venta_neto, tipo_iva, precio_venta,
    precio_venta_mayorista, ventas, fecha, nombre_usuario, esCombo, cambio_desde
    """
    filas = []
    for texto in extraer_filas_insert(contenido):
        if "INSERT" in texto.upper() or "VALUES" in texto.upper():
            continue
        try:
            vals = extraer_valores_fila(texto)
            if len(vals) >= 23:
                # Filtrar: primera columna debe ser id numérico (evita lista de columnas)
                id_val = vals[0].strip()
                if id_val.lstrip("-").isdigit():
                    filas.append(vals)
        except Exception:
            pass
    return filas


def transformar_fila(vals):
    """Convierte fila antigua a nueva estructura."""
    (id_, id_cat, codigo, _codigo_prov, id_prov, desc, img, stock, deposito,
     stock_med, stock_baj, prec_comp, prec_comp_dol, margen, prec_venta_neto,
     tipo_iva, prec_venta, prec_venta_may, ventas, fecha, nom_usuario,
     _es_combo, cambio_desde) = vals[:23]

    stock2 = "0.00" if parsear_valor(deposito) is None else deposito.strip()
    stock3 = "0.00"
    ventas_val = "0" if parsear_valor(ventas) is None else ventas.strip()
    es_combo = "0"
    activo = "1"

    return (
        id_, id_cat, codigo, id_prov, desc, img, stock, stock2, stock3,
        stock_med, stock_baj, prec_comp, prec_comp_dol, margen, prec_venta_neto,
        tipo_iva, prec_venta, prec_venta_may, ventas_val, fecha, nom_usuario,
        cambio_desde, es_combo, activo
    )


def escapar_sql(val):
    """Escapa un valor para SQL: comillas simples se duplican ('')."""
    if val is None or (isinstance(val, str) and val.upper() == "NULL"):
        return "NULL"
    s = str(val).strip()
    if s.upper() == "NULL":
        return "NULL"
    # Quitar comillas externas si las tiene
    if s.startswith("'") and s.endswith("'"):
        s = s[1:-1].replace("''", "'")  # normalizar escapes SQL existentes
    # Normalizar escapes con backslash (\' \") a carácter simple
    s = s.replace("\\'", "'").replace('\\"', '"').replace("\\\\", "\\")
    # Colapsar comillas múltiples consecutivas (ej: 18''' -> 18') para evitar
    # que phpMyAdmin/parsers interpreten mal secuencias como 18''''
    while "''" in s:
        s = s.replace("''", "'")
    # Escapar comilla simple para SQL: ' -> ''
    s = s.replace("'", "''")
    return f"'{s}'"


def es_numero(v):
    """Indica si el valor es numérico para SQL (sin comillas)."""
    s = v.strip().lstrip("-")
    return s.replace(".", "").isdigit()


def formatear_fila_nueva(vals):
    """Formatea una fila para el INSERT nuevo."""
    partes = []
    for v in vals:
        v = v.strip()
        if v.upper() == "NULL":
            partes.append("NULL")
        elif es_numero(v):
            partes.append(v)
        else:
            partes.append(escapar_sql(v))
    return "(" + ", ".join(partes) + ")"


def main():
    if not ORIGINAL.exists():
        print(f"Error: No existe {ORIGINAL}")
        print("Coloca ahí el INSERT con la estructura antigua de productos.")
        return 1

    contenido_orig = ORIGINAL.read_text(encoding="utf-8", errors="replace")
    filas_orig = parsear_insert_antiguo(contenido_orig)

    if not filas_orig:
        print("No se encontraron filas válidas en original_datos.sql")
        return 1

    vistos = set()
    resultado = []
    next_id = 440
    max_id = 0

    for vals in filas_orig:
        codigo = vals[2].strip().strip("'") if len(vals) > 2 else ""
        if codigo in vistos:
            continue
        vistos.add(codigo)

        id_val = vals[0].strip()
        if id_val == "0":
            id_val = str(next_id)
            next_id += 1
        else:
            try:
                max_id = max(max_id, int(id_val))
            except ValueError:
                pass

        vals[0] = id_val
        fila_nueva = transformar_fila(vals)
        resultado.append(formatear_fila_nueva(fila_nueva))

    header = """-- ================================================================
-- MIGRACIÓN PRODUCTOS - Generado por migrar_productos.py
-- ================================================================
-- Origen: original_datos.sql (estructura antigua)
-- Destino: tabla productos (stock2, stock3, es_combo, activo)
-- ================================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET FOREIGN_KEY_CHECKS = 0;
DROP TRIGGER IF EXISTS `prod_insertar`;

INSERT INTO `productos` (`id`, `id_categoria`, `codigo`, `id_proveedor`, `descripcion`, `imagen`, `stock`, `stock2`, `stock3`, `stock_medio`, `stock_bajo`, `precio_compra`, `precio_compra_dolar`, `margen_ganancia`, `precio_venta_neto`, `tipo_iva`, `precio_venta`, `precio_venta_mayorista`, `ventas`, `fecha`, `nombre_usuario`, `cambio_desde`, `es_combo`, `activo`) VALUES
"""

    footer = """
;
SET FOREIGN_KEY_CHECKS = 1;
"""

    salida = header + ",\n".join(resultado) + footer
    SALIDA.write_text(salida, encoding="utf-8")

    print(f"Migración completada: {len(resultado)} filas")
    print(f"Salida: {SALIDA}")
    if ACTUAL.exists():
        print(f"Referencia: {ACTUAL}")
    return 0


if __name__ == "__main__":
    exit(main())
