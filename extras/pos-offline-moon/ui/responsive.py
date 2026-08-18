#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""Adaptación de las ventanas al tamaño real de la pantalla.

Las PC de los comercios van de 1024x768 a 1920x1080, así que ninguna ventana
puede tener una medida fija pensada para un monitor grande: lo que sobra se
recorta y el usuario pierde botones sin darse cuenta.
"""

import tkinter as tk
from tkinter import ttk

ALTO_REFERENCIA = 1000


def area_util(win):
    """Pantalla realmente aprovechable, sin la barra de tareas."""
    try:
        win.update_idletasks()
    except tk.TclError:
        pass

    ancho = win.winfo_screenwidth()
    alto = win.winfo_screenheight()
    try:
        # En Windows wm_maxsize ya descuenta la barra de tareas; en varios
        # escritorios de Linux devuelve valores enormes, de ahí el mínimo.
        max_ancho, max_alto = win.wm_maxsize()
        if max_ancho > 0:
            ancho = min(ancho, max_ancho)
        if max_alto > 0:
            alto = min(alto, max_alto)
    except tk.TclError:
        pass
    return ancho, alto


def ajustar_ventana(win, ancho_ideal, alto_ideal, min_ancho=800, min_alto=520, centrar=True):
    """Deja la ventana en su medida ideal o en la mayor que entre en pantalla."""
    disponible_ancho, disponible_alto = area_util(win)

    ancho = max(400, min(ancho_ideal, disponible_ancho - 20))
    alto = max(300, min(alto_ideal, disponible_alto - 60))

    win.minsize(min(min_ancho, ancho), min(min_alto, alto))
    if centrar:
        x = max(0, (disponible_ancho - ancho) // 2)
        y = max(0, (disponible_alto - alto) // 3)
        win.geometry(f"{ancho}x{alto}+{x}+{y}")
    else:
        win.geometry(f"{ancho}x{alto}")
    return ancho, alto


class Escala:
    """Reduce fuentes y espacios de forma proporcional en pantallas chicas."""

    def __init__(self, factor):
        self.factor = factor

    def fuente(self, tamaño, negrita=False, familia="Arial"):
        puntos = max(7, int(round(tamaño * self.factor)))
        return (familia, puntos, "bold") if negrita else (familia, puntos)

    def px(self, valor, minimo=1):
        return max(minimo, int(round(valor * self.factor)))

    @property
    def compacta(self):
        return self.factor < 0.95


def escala(win):
    _, alto = area_util(win)
    return Escala(max(0.7, min(1.0, round(alto / ALTO_REFERENCIA, 2))))


def columnas_proporcionales(tree, proporciones):
    """Reparte el ancho de una tabla entre sus columnas cada vez que cambia.

    Un Treeview no achica sus columnas solo: conserva el ancho pedido y recorta
    lo que no entra, así que en pantallas chicas desaparecían Precio y Stock.
    """
    columnas = list(tree["columns"])
    total = float(sum(proporciones)) or 1.0

    def _ajustar(_evento=None):
        ancho = tree.winfo_width()
        if ancho <= 1:
            return
        disponible = max(160, ancho - 4)
        for columna, proporcion in zip(columnas, proporciones):
            tree.column(columna, width=max(35, int(disponible * proporcion / total)))

    tree.bind("<Configure>", _ajustar, add="+")
    return _ajustar


def columna_scrollable(padre, bg="white", ancho=320):
    """Columna de ancho fijo con scroll vertical propio.

    Devuelve (contenedor, interior): el contenedor se empaqueta en el padre y
    los widgets se crean dentro de interior. La barra sólo aparece cuando el
    contenido no entra.
    """
    contenedor = tk.Frame(padre, bg=bg, relief=tk.RAISED, bd=1, width=ancho)
    contenedor.pack_propagate(False)

    lienzo = tk.Canvas(contenedor, bg=bg, highlightthickness=0, bd=0)
    barra = ttk.Scrollbar(contenedor, orient=tk.VERTICAL, command=lienzo.yview)
    interior = tk.Frame(lienzo, bg=bg)

    ventana = lienzo.create_window((0, 0), window=interior, anchor="nw")
    lienzo.configure(yscrollcommand=barra.set)
    lienzo.pack(side=tk.LEFT, fill=tk.BOTH, expand=True)

    def _ajustar(_evento=None):
        lienzo.configure(scrollregion=lienzo.bbox("all"))
        lienzo.itemconfigure(ventana, width=lienzo.winfo_width())
        hace_falta = interior.winfo_reqheight() > lienzo.winfo_height()
        if hace_falta and not barra.winfo_ismapped():
            barra.pack(side=tk.RIGHT, fill=tk.Y)
        elif not hace_falta and barra.winfo_ismapped():
            barra.pack_forget()

    interior.bind("<Configure>", _ajustar)
    lienzo.bind("<Configure>", _ajustar)

    def _rueda(evento):
        if not barra.winfo_ismapped():
            return
        arriba = getattr(evento, "delta", 0) > 0 or getattr(evento, "num", 0) == 4
        lienzo.yview_scroll(-1 if arriba else 1, "units")

    # La rueda se engancha sólo mientras el puntero está sobre la columna,
    # para no robarle el scroll a la lista de productos.
    def _activar(_evento=None):
        for secuencia in ("<MouseWheel>", "<Button-4>", "<Button-5>"):
            lienzo.bind_all(secuencia, _rueda)

    def _desactivar(_evento=None):
        for secuencia in ("<MouseWheel>", "<Button-4>", "<Button-5>"):
            lienzo.unbind_all(secuencia)

    contenedor.bind("<Enter>", _activar)
    contenedor.bind("<Leave>", _desactivar)

    return contenedor, interior
