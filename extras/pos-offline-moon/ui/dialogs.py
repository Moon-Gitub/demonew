#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""Diálogos reutilizables (cobro, confirmaciones)."""

from tkinter import messagebox


def confirmar_cobro(total, medio):
    return messagebox.askyesno(
        "Confirmar cobro",
        f"Total: ${total:.2f}\nMedio: {medio}\n\n¿Confirmar venta?",
    )
