#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
INFORME MENSUAL DE VENTAS — POS Moon
====================================
Todo en un solo archivo. Copiar a:
  /home/posmoonar/informePython/reporte-ventas-mensual.py

Ejecutar (con el venv que ya tenés):
  source /home/posmoonar/virtualenv/informePython/3.11/bin/activate
  cd /home/posmoonar/informePython
  python reporte-ventas-mensual.py

O con mes/año:
  python reporte-ventas-mensual.py --anio 2025 --mes 5

Salida Excel en: ./reportes/reporte_{dbname}_{año}_{mes}.xlsx

Configuración de bases: editar CONEXIONES abajo O crear bases.txt en esta carpeta.
"""
from __future__ import annotations

import argparse
import json
import os
import subprocess
import sys
from datetime import datetime

# =============================================================================
# CONFIGURACIÓN — editar acá las conexiones (una por cliente)
# Si dejás la lista vacía, lee bases.txt del mismo directorio que este script.
# Formato bases.txt (una línea por base):
#   host=localhost;dbname=mi_db;user=mi_user;pass=mi_clave
# =============================================================================
CONEXIONES: list[dict[str, str]] = [
    # {"host": "localhost", "dbname": "cliente_db", "user": "cliente_user", "pass": "clave"},
]

CBTE_EXCLUIDOS = (3, 8, 13, 203, 208, 213, 999)

BASE_DIR = os.path.dirname(os.path.abspath(__file__))
OUTPUT_DIR = os.path.join(BASE_DIR, "reportes")
BASES_PATH = os.path.join(BASE_DIR, "bases.txt")


def instalar_dependencias() -> None:
    paquetes = ["pymysql", "pandas", "openpyxl"]
    print("📦 Instalando dependencias:", ", ".join(paquetes))
    subprocess.check_call(
        [sys.executable, "-m", "pip", "install", "--quiet", *paquetes],
        stdout=subprocess.DEVNULL,
    )


def importar_dependencias():
    try:
        import pymysql  # noqa: F401
        import pandas  # noqa: F401
        import openpyxl  # noqa: F401
    except ImportError:
        instalar_dependencias()
    import pymysql
    import pandas as pd

    return pymysql, pd


pymysql, pd = importar_dependencias()


def leer_conexiones_archivo(ruta: str) -> list[dict[str, str]]:
    conexiones: list[dict[str, str]] = []
    if not os.path.isfile(ruta):
        return conexiones

    with open(ruta, encoding="utf-8") as f:
        for linea in f:
            if not linea.strip() or linea.strip().startswith("#"):
                continue
            try:
                partes: dict[str, str] = {}
                for p in linea.strip().split(";"):
                    if "=" in p:
                        clave, valor = p.split("=", 1)
                        partes[clave.strip()] = valor.strip()
                dbname = partes.get("dbname") or partes.get("db")
                if all(partes.get(k) for k in ("host", "user", "pass")) and dbname:
                    partes["dbname"] = dbname
                    conexiones.append(partes)
            except Exception as exc:
                print(f"⚠️ Línea ignorada en bases.txt: {linea.strip()} ({exc})")
    return conexiones


def obtener_conexiones() -> list[dict[str, str]]:
    if CONEXIONES:
        return list(CONEXIONES)
    desde_archivo = leer_conexiones_archivo(BASES_PATH)
    if desde_archivo:
        return desde_archivo
    return []


def tabla_existe(cursor, nombre: str) -> bool:
    cursor.execute("SHOW TABLES LIKE %s", (nombre,))
    return cursor.fetchone() is not None


def mapa_sucursales(cursor) -> dict[str, int]:
    cursor.execute("SELECT almacenes, numero_establecimiento FROM empresa LIMIT 1")
    row = cursor.fetchone() or {}
    numero_def = row.get("numero_establecimiento")
    if numero_def and str(numero_def).strip().isdigit():
        return {"__default__": int(str(numero_def).strip())}

    try:
        almacenes = json.loads(row.get("almacenes") or "[]")
    except json.JSONDecodeError:
        almacenes = []

    mapping: dict[str, int] = {"__default__": 1}
    for idx, alm in enumerate(almacenes, start=1):
        if isinstance(alm, dict):
            stk = alm.get("stkProd") or alm.get("stock")
            if stk:
                mapping[str(stk)] = idx
    return mapping


def codigo_sucursal(sucursal: str | None, mapping: dict[str, int]) -> int:
    if sucursal and sucursal in mapping:
        return mapping[sucursal]
    return mapping.get("__default__", 1)


def obtener_empresa_info(cursor) -> dict:
    cursor.execute(
        """
        SELECT razon_social, domicilio, localidad, numero_establecimiento
        FROM empresa ORDER BY id LIMIT 1
        """
    )
    return cursor.fetchone() or {}


def reporte_desde_productos_venta(cursor, anio: int, mes: int, mapping_suc: dict[str, int]) -> list[dict]:
    ph = ",".join(["%s"] * len(CBTE_EXCLUIDOS))
    cursor.execute(
        f"""
        SELECT v.fecha, v.sucursal, p.codigo AS ean,
               COALESCE(p.descripcion, '') AS descripcion,
               pv.cantidad, (pv.cantidad * pv.precio_venta) AS total_linea,
               COALESCE(c.categoria, 'Sin categoría') AS rubro
        FROM productos_venta pv
        INNER JOIN ventas v ON v.id = pv.id_venta
        INNER JOIN productos p ON p.id = pv.id_producto
        LEFT JOIN categorias c ON c.id = p.id_categoria
        WHERE v.fecha IS NOT NULL
          AND YEAR(v.fecha) = %s AND MONTH(v.fecha) = %s
          AND (v.cbte_tipo IS NULL OR v.cbte_tipo NOT IN ({ph}))
        ORDER BY v.fecha, v.id, pv.id
        """,
        (anio, mes, *CBTE_EXCLUIDOS),
    )
    filas = []
    for row in cursor.fetchall():
        filas.append({
            "FECHA": row["fecha"],
            "Cod. Sucursal": codigo_sucursal(row.get("sucursal"), mapping_suc),
            "Tipo de Vta": "Piso",
            "EAN": row.get("ean") or "",
            "EAN descripcion": row.get("descripcion") or "",
            "Unidades vendidas": float(row.get("cantidad") or 0),
            "Facturación Neta": float(row.get("total_linea") or 0),
            "Localidad/Provincia": "",
            "Rubro": row.get("rubro") or "Sin categoría",
        })
    return filas


def reporte_desde_json(cursor, anio: int, mes: int, mapping_suc: dict[str, int]) -> list[dict]:
    ph = ",".join(["%s"] * len(CBTE_EXCLUIDOS))
    cursor.execute(
        f"""
        SELECT id, fecha, sucursal, productos FROM ventas
        WHERE fecha IS NOT NULL
          AND YEAR(fecha) = %s AND MONTH(fecha) = %s
          AND productos IS NOT NULL AND productos != '' AND productos != '[]'
          AND (cbte_tipo IS NULL OR cbte_tipo NOT IN ({ph}))
        """,
        (anio, mes, *CBTE_EXCLUIDOS),
    )

    cache_prod: dict[int, dict] = {}
    cache_cat: dict[int, str] = {}
    filas: list[dict] = []

    def datos_producto(pid: int) -> tuple[str, str, str]:
        if pid not in cache_prod:
            cursor.execute(
                "SELECT codigo, descripcion, id_categoria FROM productos WHERE id = %s LIMIT 1",
                (pid,),
            )
            cache_prod[pid] = cursor.fetchone() or {}
        prod = cache_prod[pid]
        ean = prod.get("codigo") or ""
        desc = prod.get("descripcion") or ""
        rubro = "Sin categoría"
        id_cat = prod.get("id_categoria")
        if id_cat:
            if id_cat not in cache_cat:
                cursor.execute("SELECT categoria FROM categorias WHERE id = %s LIMIT 1", (id_cat,))
                c = cursor.fetchone()
                cache_cat[id_cat] = (c or {}).get("categoria") or "Sin categoría"
            rubro = cache_cat[id_cat]
        return ean, desc, rubro

    for venta in cursor.fetchall():
        try:
            items = json.loads(venta["productos"])
        except (json.JSONDecodeError, TypeError):
            continue
        if not isinstance(items, list):
            continue
        for item in items:
            if not isinstance(item, dict):
                continue
            try:
                pid = int(item.get("id") or item.get("id_producto") or 0)
            except (TypeError, ValueError):
                continue
            if pid <= 0:
                continue
            ean, desc_bd, rubro = datos_producto(pid)
            cant = float(item.get("cantidad") or 0)
            total = item.get("total")
            if total in (None, ""):
                precio = float(item.get("precio") or item.get("precio_venta") or 0)
                total = cant * precio
            else:
                total = float(total)
            filas.append({
                "FECHA": venta["fecha"],
                "Cod. Sucursal": codigo_sucursal(venta.get("sucursal"), mapping_suc),
                "Tipo de Vta": "Piso",
                "EAN": ean,
                "EAN descripcion": item.get("descripcion") or desc_bd or "",
                "Unidades vendidas": cant,
                "Facturación Neta": float(total),
                "Localidad/Provincia": "",
                "Rubro": rubro,
            })
    return filas


def exportar_excel(empresa: dict, filas: list[dict], dbname: str, anio: int, mes: int, fuente: str) -> str:
    os.makedirs(OUTPUT_DIR, exist_ok=True)
    localidad = empresa.get("localidad") or ""
    for f in filas:
        f["Localidad/Provincia"] = localidad

    df = pd.DataFrame(filas)
    enc = pd.DataFrame({
        "razon_social": [empresa.get("razon_social", "")],
        "domicilio": [empresa.get("domicilio", "")],
        "localidad": [localidad],
        "periodo": [f"{mes:02d}/{anio}"],
        "fuente": [fuente],
    })
    out = os.path.join(OUTPUT_DIR, f"reporte_{dbname}_{anio}_{mes:02d}.xlsx")
    with pd.ExcelWriter(out, engine="openpyxl") as w:
        enc.to_excel(w, index=False, sheet_name="Reporte", startrow=0)
        cols = [
            "FECHA", "Cod. Sucursal", "Tipo de Vta", "EAN", "EAN descripcion",
            "Unidades vendidas", "Facturación Neta", "Localidad/Provincia", "Rubro",
        ]
        (df if not df.empty else pd.DataFrame(columns=cols)).to_excel(
            w, index=False, sheet_name="Reporte", startrow=4
        )
    return out


def procesar_bd(cfg: dict[str, str], anio: int, mes: int) -> bool:
    db = cfg["dbname"]
    try:
        conn = pymysql.connect(
            host=cfg["host"], user=cfg["user"], password=cfg["pass"], database=db,
            cursorclass=pymysql.cursors.DictCursor, connect_timeout=15,
        )
    except Exception as e:
        print(f"❌ {db}: no se pudo conectar — {e}")
        return False

    print(f"✅ Conectado a {db}")
    try:
        with conn.cursor() as cur:
            empresa = obtener_empresa_info(cur)
            mapping = mapa_sucursales(cur)
            fuente = "productos_venta"
            if tabla_existe(cur, "productos_venta"):
                filas = reporte_desde_productos_venta(cur, anio, mes, mapping)
                if not filas:
                    print(f"   ℹ️ {db}: sin datos en productos_venta, usando JSON")
                    fuente = "ventas.productos (JSON)"
                    filas = reporte_desde_json(cur, anio, mes, mapping)
            else:
                print(f"   ℹ️ {db}: sin tabla productos_venta, usando JSON")
                fuente = "ventas.productos (JSON)"
                filas = reporte_desde_json(cur, anio, mes, mapping)
            ruta = exportar_excel(empresa, filas, db, anio, mes, fuente)
            print(f"📁 {db}: {len(filas)} filas → {ruta}")
            return True
    except Exception as e:
        print(f"⚠️ {db}: error — {e}")
        return False
    finally:
        conn.close()


def pedir_periodo(anio: int | None, mes: int | None) -> tuple[int, int]:
    if anio and mes:
        if 1 <= mes <= 12:
            return anio, mes
        print("❗ El mes debe ser 1-12.")
        sys.exit(1)
    while True:
        try:
            a = int(input("📅 Año (ej: 2025): "))
            m = int(input("📆 Mes (1-12): "))
            if 1 <= m <= 12:
                return a, m
            print("❗ Mes inválido.")
        except ValueError:
            print("❗ Ingresá números.")
        except EOFError:
            h = datetime.now()
            print(f"ℹ️ Usando período actual: {h.month:02d}/{h.year}")
            return h.year, h.month


def main() -> None:
    parser = argparse.ArgumentParser(description="Informe mensual ventas POS Moon")
    parser.add_argument("--anio", type=int)
    parser.add_argument("--mes", type=int)
    parser.add_argument("--install-deps", action="store_true", help="Solo instalar pymysql/pandas/openpyxl")
    args = parser.parse_args()

    if args.install_deps:
        instalar_dependencias()
        print("✅ Listo.")
        return

    conexiones = obtener_conexiones()
    if not conexiones:
        print("❌ Sin conexiones configuradas.")
        print("   Opción 1: editar CONEXIONES al inicio de este script.")
        print(f"   Opción 2: crear {BASES_PATH}")
        print("   Formato: host=...;dbname=...;user=...;pass=...")
        sys.exit(1)

    anio, mes = pedir_periodo(args.anio, args.mes)
    print("=" * 55)
    print(f"  Informe Moon — {mes:02d}/{anio}")
    print(f"  Directorio: {BASE_DIR}")
    print(f"  Bases: {len(conexiones)}")
    print("=" * 55)

    ok = sum(1 for c in conexiones if procesar_bd(c, anio, mes))
    print("-" * 55)
    print(f"✅ Terminado: {ok}/{len(conexiones)} bases OK")
    if ok == 0:
        sys.exit(1)


if __name__ == "__main__":
    main()
