#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Parser de códigos de balanza (EAN-13 pesable).
Formato típico: 2 + código producto (5) + peso/precio (5) + dígito control.
"""

import re


def parse_scale_barcode(code, prefix="2", product_digits=5):
    """
    Devuelve dict {codigo_producto, cantidad_kg} o None si no aplica.
    """
    code = str(code).strip()
    if not code.isdigit() or len(code) < 8:
        return None
    if prefix and not code.startswith(prefix):
        return None
    try:
        # Plu + peso en gramos (últimos 5 antes del check digit en muchos formatos)
        if len(code) >= 13:
            plu = code[1:1 + product_digits]
            peso_raw = code[1 + product_digits:1 + product_digits + 5]
            cantidad = int(peso_raw) / 1000.0
            if cantidad <= 0:
                return None
            return {"codigo": plu.lstrip("0") or plu, "cantidad": cantidad, "raw": code}
        if len(code) >= 8:
            plu = code[1:1 + product_digits]
            peso_raw = code[1 + product_digits:1 + product_digits + 5]
            cantidad = int(peso_raw) / 1000.0
            if cantidad > 0:
                return {"codigo": plu.lstrip("0") or plu, "cantidad": cantidad, "raw": code}
    except (ValueError, IndexError):
        return None
    return None


def looks_like_scale(code):
    return parse_scale_barcode(code) is not None
