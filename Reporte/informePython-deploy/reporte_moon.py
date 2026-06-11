#!/usr/bin/env python3
"""
Reporte mensual POS Moon — esquema nuevo (productos_venta).
Compatible con bases viejas (sin ventas.sucursal, sin empresa.almacenes, etc.).
Salida: reportes/reporte_moon_{dbname}_{año}_{mes}.xlsx
"""
from __future__ import annotations

import json
import os

import pandas as pd
import pymysql

BASE_DIR = os.path.dirname(os.path.abspath(__file__))
OUTPUT_DIR = os.path.join(BASE_DIR, "reportes")
BASES_DEFAULT = "bases2.txt"


def pedir_periodo(anio=None, mes=None, interactivo=True):
    if anio is not None and mes is not None:
        return int(anio), int(mes)
    if os.environ.get("MOON_ANIO") and os.environ.get("MOON_MES"):
        return int(os.environ["MOON_ANIO"]), int(os.environ["MOON_MES"])
    if not interactivo:
        raise SystemExit("❌ Modo automático: falta año/mes (MOON_ANIO / MOON_MES).")
    while True:
        try:
            a = int(input("📅 Ingresá el año del reporte (ej: 2025): "))
            m = int(input("📆 Ingresá el número de mes (1 a 12): "))
            if 1 <= m <= 12:
                return a, m
            print("❗ El mes debe estar entre 1 y 12.")
        except ValueError:
            print("❗ Ingresá valores numéricos válidos.")


def ruta_bases(archivo=None):
    nombre = archivo or os.environ.get("MOON_BASES", BASES_DEFAULT)
    return os.path.join(BASE_DIR, nombre)


CBTE_EXCLUIDOS = (3, 8, 13, 203, 208, 213, 999)
RUBRO_DEFECTO = "Almacén"


def leer_conexiones(ruta_archivo: str) -> list[dict[str, str]]:
    conexiones: list[dict[str, str]] = []
    with open(ruta_archivo, encoding="utf-8") as f:
        for linea in f:
            if not linea.strip() or linea.strip().startswith("#"):
                continue
            try:
                partes: dict[str, str] = {}
                for p in linea.strip().split(";"):
                    if "=" in p:
                        clave, valor = p.split("=", 1)
                        partes[clave.strip()] = valor.strip()
                if all(k in partes for k in ("host", "dbname", "user", "pass")):
                    conexiones.append(partes)
            except Exception as exc:
                print(f"⚠️ Línea ignorada: {linea.strip()} ({exc})")
    return conexiones


def tabla_existe(cursor, nombre: str) -> bool:
    cursor.execute("SHOW TABLES LIKE %s", (nombre,))
    return cursor.fetchone() is not None


def columna_existe(cursor, tabla: str, columna: str) -> bool:
    cursor.execute(
        """
        SELECT 1 FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = %s AND COLUMN_NAME = %s
        LIMIT 1
        """,
        (tabla, columna),
    )
    return cursor.fetchone() is not None


def detectar_esquema(cursor) -> dict[str, bool]:
    return {
        "ventas_sucursal": columna_existe(cursor, "ventas", "sucursal"),
        "ventas_cbte_tipo": columna_existe(cursor, "ventas", "cbte_tipo"),
        "empresa_almacenes": columna_existe(cursor, "empresa", "almacenes"),
        "empresa_numero_establecimiento": columna_existe(cursor, "empresa", "numero_establecimiento"),
        "productos_id_categoria": columna_existe(cursor, "productos", "id_categoria"),
        "tabla_categorias": tabla_existe(cursor, "categorias"),
        "tabla_productos_venta": tabla_existe(cursor, "productos_venta"),
    }


def filtro_cbte(alias: str, esquema: dict[str, bool]) -> tuple[str, tuple]:
    if not esquema.get("ventas_cbte_tipo"):
        return "", ()
    ph = ",".join(["%s"] * len(CBTE_EXCLUIDOS))
    col = f"{alias}.cbte_tipo" if alias else "cbte_tipo"
    return f" AND ({col} IS NULL OR {col} NOT IN ({ph}))", CBTE_EXCLUIDOS


def mapa_sucursales(cursor, esquema: dict[str, bool]) -> dict[str, int]:
    mapping: dict[str, int] = {"__default__": 1}
    cols = []
    if esquema.get("empresa_numero_establecimiento"):
        cols.append("numero_establecimiento")
    if esquema.get("empresa_almacenes"):
        cols.append("almacenes")
    if not cols:
        return mapping

    cursor.execute(f"SELECT {', '.join(cols)} FROM empresa LIMIT 1")
    row = cursor.fetchone() or {}

    numero_def = row.get("numero_establecimiento")
    if numero_def and str(numero_def).strip().isdigit():
        mapping["__default__"] = int(str(numero_def).strip())

    if esquema.get("empresa_almacenes"):
        try:
            almacenes = json.loads(row.get("almacenes") or "[]")
        except json.JSONDecodeError:
            almacenes = []
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


def fila_reporte(fecha, sucursal, ean, descripcion, cantidad, total, localidad, rubro, mapping):
    return {
        "FECHA": fecha,
        "Cod. Sucursal": codigo_sucursal(sucursal, mapping),
        "Tipo de Vta": "Piso",
        "EAN": ean or "",
        "EAN descripcion": descripcion or "",
        "Unidades vendidas": cantidad,
        "Facturación Neta": total,
        "Localidad/Provincia": localidad,
        "Rubro": rubro or RUBRO_DEFECTO,
    }


def rubro_en_query(esquema: dict[str, bool]) -> tuple[str, str]:
    if esquema.get("tabla_categorias") and esquema.get("productos_id_categoria"):
        return (
            "COALESCE(c.categoria, %s) AS rubro",
            "LEFT JOIN categorias c ON c.id = p.id_categoria",
        )
    return "%s AS rubro", ""


def reporte_desde_productos_venta(cursor, anio, mes, mapping, localidad_def, esquema):
    cbte_sql, cbte_params = filtro_cbte("v", esquema)
    sucursal_sql = "v.sucursal," if esquema.get("ventas_sucursal") else ""
    rubro_sel, join_cat = rubro_en_query(esquema)

    cursor.execute(
        f"""
        SELECT v.fecha, {sucursal_sql}
               p.codigo AS ean,
               COALESCE(p.descripcion, '') AS descripcion,
               pv.cantidad, (pv.cantidad * pv.precio_venta) AS total_linea,
               {rubro_sel}
        FROM productos_venta pv
        INNER JOIN ventas v ON v.id = pv.id_venta
        INNER JOIN productos p ON p.id = pv.id_producto
        {join_cat}
        WHERE v.fecha IS NOT NULL
          AND YEAR(v.fecha) = %s AND MONTH(v.fecha) = %s
          {cbte_sql}
        ORDER BY v.fecha, v.id, pv.id
        """,
        (RUBRO_DEFECTO, anio, mes, *cbte_params),
    )
    filas = []
    for row in cursor.fetchall():
        filas.append(fila_reporte(
            row["fecha"], row.get("sucursal"), row.get("ean"), row.get("descripcion"),
            float(row.get("cantidad") or 0), float(row.get("total_linea") or 0),
            localidad_def, row.get("rubro"), mapping,
        ))
    return filas


def reporte_desde_json(cursor, anio, mes, mapping, localidad_def, esquema):
    cbte_sql, cbte_params = filtro_cbte("", esquema)

    cols = ["fecha"]
    if esquema.get("ventas_sucursal"):
        cols.append("sucursal")
    cols.append("productos")

    cursor.execute(
        f"""
        SELECT {', '.join(cols)} FROM ventas
        WHERE fecha IS NOT NULL
          AND YEAR(fecha) = %s AND MONTH(fecha) = %s
          AND productos IS NOT NULL AND productos != '' AND productos != '[]'
          {cbte_sql}
        """,
        (anio, mes, *cbte_params),
    )

    usa_categorias = esquema.get("tabla_categorias") and esquema.get("productos_id_categoria")
    cache_prod: dict[int, dict] = {}
    cache_cat: dict[int, str] = {}
    filas = []

    def datos_producto(pid: int) -> tuple[str, str, str]:
        if pid not in cache_prod:
            if usa_categorias:
                cursor.execute(
                    "SELECT codigo, descripcion, id_categoria FROM productos WHERE id = %s LIMIT 1",
                    (pid,),
                )
            else:
                cursor.execute(
                    "SELECT codigo, descripcion FROM productos WHERE id = %s LIMIT 1",
                    (pid,),
                )
            cache_prod[pid] = cursor.fetchone() or {}
        prod = cache_prod[pid]
        rubro = RUBRO_DEFECTO
        if usa_categorias:
            id_cat = prod.get("id_categoria")
            if id_cat:
                if id_cat not in cache_cat:
                    cursor.execute("SELECT categoria FROM categorias WHERE id = %s LIMIT 1", (id_cat,))
                    c = cursor.fetchone()
                    cache_cat[id_cat] = (c or {}).get("categoria") or RUBRO_DEFECTO
                rubro = cache_cat[id_cat]
        return prod.get("codigo") or "", prod.get("descripcion") or "", rubro

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
                total = cant * float(item.get("precio") or item.get("precio_venta") or 0)
            else:
                total = float(total)
            filas.append(fila_reporte(
                venta["fecha"], venta.get("sucursal"), ean,
                item.get("descripcion") or desc_bd, cant, float(total),
                localidad_def, rubro, mapping,
            ))
    return filas


def procesar_bd(config: dict[str, str], anio: int, mes: int, output_dir=None) -> list[str]:
    dbname = config["dbname"]
    generados: list[str] = []
    destino = output_dir or os.environ.get("MOON_OUTPUT_DIR") or OUTPUT_DIR
    try:
        conexion = pymysql.connect(
            host=config["host"], user=config["user"], password=config["pass"],
            database=dbname, cursorclass=pymysql.cursors.DictCursor, connect_timeout=15,
        )
    except Exception as exc:
        print(f"❌ Error al conectar a {dbname}: {exc}")
        return generados

    print(f"✅ Conectado a {dbname}")
    datos_reporte: list[dict] = []

    try:
        with conexion.cursor() as cursor:
            esquema = detectar_esquema(cursor)
            cursor.execute("SELECT razon_social, domicilio, localidad FROM empresa LIMIT 1")
            empresa_info = cursor.fetchone() or {}
            localidad = empresa_info.get("localidad") or ""
            mapping = mapa_sucursales(cursor, esquema)

            fuente = "productos_venta"
            if esquema["tabla_productos_venta"]:
                datos_reporte = reporte_desde_productos_venta(
                    cursor, anio, mes, mapping, localidad, esquema
                )
                if not datos_reporte:
                    print(f"   ℹ️ {dbname}: productos_venta vacío en el período, usando JSON")
                    fuente = "JSON"
                    datos_reporte = reporte_desde_json(
                        cursor, anio, mes, mapping, localidad, esquema
                    )
            else:
                print(f"   ℹ️ {dbname}: sin tabla productos_venta, usando JSON")
                fuente = "JSON"
                datos_reporte = reporte_desde_json(
                    cursor, anio, mes, mapping, localidad, esquema
                )

        df = pd.DataFrame(datos_reporte)
        encabezado = pd.DataFrame({
            "razon_social": [empresa_info.get("razon_social", "")],
            "domicilio": [empresa_info.get("domicilio", "")],
            "localidad": [empresa_info.get("localidad", "")],
        })

        os.makedirs(destino, exist_ok=True)
        nombre = f"reporte_moon_{dbname}_{anio}_{mes:02d}.xlsx"
        ruta = os.path.join(destino, nombre)

        with pd.ExcelWriter(ruta, engine="openpyxl") as writer:
            encabezado.to_excel(writer, index=False, sheet_name="Reporte", startrow=0)
            df.to_excel(writer, index=False, sheet_name="Reporte", startrow=3)

        print(f"📁 Reporte generado ({len(datos_reporte)} filas, {fuente}): {ruta}")
        generados.append(ruta)

    except Exception as exc:
        print(f"⚠️ Error procesando {dbname}: {exc}")
    finally:
        conexion.close()
    return generados


def ejecutar(anio=None, mes=None, bases_archivo=None, interactivo=True, output_dir=None):
    """Ejecuta reporte sistemas nuevos. Devuelve lista de archivos Excel generados."""
    anio, mes = pedir_periodo(anio, mes, interactivo=interactivo)
    bases_path = ruta_bases(bases_archivo)
    if not os.path.isfile(bases_path):
        print(f"❌ No se encontró {bases_path}")
        raise SystemExit(1)

    destino = output_dir or os.environ.get("MOON_OUTPUT_DIR") or OUTPUT_DIR
    os.makedirs(destino, exist_ok=True)
    print(f"📂 Sistemas nuevos — {mes:02d}/{anio} — {os.path.basename(bases_path)}")
    print(f"   Salida: {destino}")
    generados: list[str] = []
    for config in leer_conexiones(bases_path):
        generados.extend(procesar_bd(config, anio, mes, destino))
    return generados


if __name__ == "__main__":
    if os.environ.get("MOON_AUTO") == "1":
        ejecutar(
            anio=int(os.environ["MOON_ANIO"]),
            mes=int(os.environ["MOON_MES"]),
            bases_archivo=os.environ.get("MOON_BASES"),
            interactivo=False,
            output_dir=os.environ.get("MOON_OUTPUT_DIR"),
        )
    else:
        ejecutar()
