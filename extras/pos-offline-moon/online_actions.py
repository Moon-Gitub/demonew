#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""Acciones que requieren conexión: facturar, Mercado Pago, impresión."""

import webbrowser
import requests
from tkinter import messagebox
from config import config
from api_client import offline_params


def facturar_venta_servidor(id_venta_servidor):
    """Abre flujo de facturación en el servidor (requiere sesión web del usuario)."""
    url = f"{config.SERVER_URL}/index.php?ruta=ventas"
    messagebox.showinfo(
        "Facturar",
        f"Con conexión, facture la venta #{id_venta_servidor} desde Administrar ventas.\n\nSe abrirá el navegador.",
    )
    webbrowser.open(url)


def abrir_mercado_pago():
    base = config.SERVER_URL.rstrip("/")
    webbrowser.open(f"{base}/index.php?ruta=crear-venta-caja")


def imprimir_ticket_local(venta_id):
    """Intenta servicio de impresión local (Flask) si está activo."""
    try:
        r = requests.post(
            "http://127.0.0.1:8765/imprimir",
            json={"venta_id": venta_id},
            timeout=3,
        )
        return r.status_code == 200
    except Exception:
        messagebox.showwarning(
            "Impresión",
            "Servicio local no disponible. Ver extras/mejoras/impresion_local.md",
        )
        return False
