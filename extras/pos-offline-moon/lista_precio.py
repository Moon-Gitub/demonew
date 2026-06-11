#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""Cálculo de precio por lista (misma lógica que venta-caja.js)."""


def precio_con_lista(producto, lista_config):
    """
    producto: dict con precio_venta, precio_compra
    lista_config: dict base_precio, tipo_descuento, valor_descuento
    """
    if not lista_config:
        return float(producto.get("precio_venta", 0) or 0)

    base_key = lista_config.get("base_precio") or "precio_venta"
    if base_key == "precio_compra":
        precio_base = float(producto.get("precio_compra", 0) or 0)
    else:
        precio_base = float(producto.get("precio_venta", 0) or 0)

    if lista_config.get("tipo_descuento") == "porcentaje":
        porc = float(lista_config.get("valor_descuento", 0) or 0)
        if porc > 0:
            return round(precio_base - (precio_base * porc / 100), 2)
    return round(precio_base, 2)
