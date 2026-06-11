#!/usr/bin/env python3
"""Menú principal: elegir reporte sistemas viejos o sistemas nuevos."""
from __future__ import annotations

import reporte
import reporte_moon

BASES_VIEJOS = "bases.txt"
BASES_NUEVOS = "bases2.txt"


def elegir_sistema() -> str:
    print("=" * 50)
    print("  INFORMES MOON")
    print("=" * 50)
    print("  1) sistemas_viejos  → reporte.py   + bases.txt")
    print("  2) sistemas_nuevos  → reporte_moon.py + bases2.txt")
    print("=" * 50)
    while True:
        op = input("Elegí opción (1 o 2): ").strip()
        if op in ("1", "2"):
            return op
        print("❗ Ingresá 1 o 2.")


def main() -> None:
    op = elegir_sistema()
    if op == "1":
        reporte.ejecutar(bases_archivo=BASES_VIEJOS)
    else:
        reporte_moon.ejecutar(bases_archivo=BASES_NUEVOS)


if __name__ == "__main__":
    main()
