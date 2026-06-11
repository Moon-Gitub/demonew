#!/usr/bin/env python3
"""Reporte clásico — sistemas viejos (JSON ventas.productos). Usa bases.txt por defecto."""
import json
import os

import pandas as pd
import pymysql

BASE_DIR = os.path.dirname(os.path.abspath(__file__))
OUTPUT_DIR = os.path.join(BASE_DIR, "reportes")
BASES_DEFAULT = "bases.txt"


def leer_conexiones(ruta_archivo):
    conexiones = []
    with open(ruta_archivo, encoding="utf-8") as f:
        for linea in f:
            if not linea.strip() or linea.strip().startswith("#"):
                continue
            try:
                partes = {}
                for p in linea.strip().split(";"):
                    if "=" in p:
                        clave, valor = p.split("=", 1)
                        partes[clave.strip()] = valor.strip()
                if all(k in partes for k in ("host", "dbname", "user", "pass")):
                    conexiones.append(partes)
            except Exception as e:
                print(f"⚠️ Línea ignorada: {linea.strip()} ({e})")
    return conexiones


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


def procesar_bd(config, anio, mes, output_dir=None):
    generados = []
    destino = output_dir or os.environ.get("MOON_OUTPUT_DIR") or OUTPUT_DIR
    try:
        conexion = pymysql.connect(
            host=config["host"],
            user=config["user"],
            password=config["pass"],
            database=config["dbname"],
            cursorclass=pymysql.cursors.DictCursor,
        )
    except Exception as e:
        print(f"❌ Error al conectar a {config['dbname']}: {e}")
        return generados

    print(f"✅ Conectado a {config['dbname']}")
    try:
        with conexion.cursor() as cursor:
            cursor.execute("SELECT razon_social, domicilio, localidad FROM empresa LIMIT 1")
            empresa_info = cursor.fetchone() or {}
            cursor.execute("SELECT fecha, productos FROM ventas")
            ventas = cursor.fetchall()
            datos_reporte = []

            for venta in ventas:
                fecha = venta["fecha"]
                if not fecha or fecha.month != mes or fecha.year != anio:
                    continue
                try:
                    lista_productos = json.loads(venta["productos"])
                except json.JSONDecodeError:
                    continue
                for item in lista_productos:
                    id_producto = item.get("id")
                    if not id_producto:
                        continue
                    cursor.execute("SELECT codigo FROM productos WHERE id = %s", (id_producto,))
                    resultado = cursor.fetchone()
                    ean = resultado["codigo"] if resultado else ""
                    datos_reporte.append({
                        "FECHA": fecha,
                        "Cod. Sucursal": 1,
                        "Tipo de Vta": "Piso",
                        "EAN": ean,
                        "EAN descripcion": item.get("descripcion", ""),
                        "Unidades vendidas": item.get("cantidad", ""),
                        "Facturación Neta": item.get("total", ""),
                        "Localidad/Provincia": "San Rafael/Mendoza",
                        "Rubro": "Almacén",
                    })

        df = pd.DataFrame(datos_reporte)
        encabezado = pd.DataFrame({
            "razon_social": [empresa_info.get("razon_social", "")],
            "domicilio": [empresa_info.get("domicilio", "")],
            "localidad": [empresa_info.get("localidad", "")],
        })
        nombre_archivo = f"reporte_{config['dbname']}_{anio}_{mes:02d}.xlsx"
        ruta_salida = os.path.join(destino, nombre_archivo)
        with pd.ExcelWriter(ruta_salida, engine="openpyxl") as writer:
            encabezado.to_excel(writer, index=False, sheet_name="Reporte", startrow=0)
            df.to_excel(writer, index=False, sheet_name="Reporte", startrow=3)
        print(f"📁 Reporte generado: {ruta_salida}")
        generados.append(ruta_salida)
    except Exception as e:
        print(f"⚠️ Error procesando {config['dbname']}: {e}")
    finally:
        conexion.close()
    return generados


def ejecutar(anio=None, mes=None, bases_archivo=None, interactivo=True, output_dir=None):
    """Ejecuta reporte sistemas viejos. Devuelve lista de archivos Excel generados."""
    anio, mes = pedir_periodo(anio, mes, interactivo=interactivo)
    bases_path = ruta_bases(bases_archivo)
    if not os.path.isfile(bases_path):
        print(f"❌ No se encontró {bases_path}")
        raise SystemExit(1)

    destino = output_dir or os.environ.get("MOON_OUTPUT_DIR") or OUTPUT_DIR
    os.makedirs(destino, exist_ok=True)
    print(f"📂 Sistemas viejos — {mes:02d}/{anio} — {os.path.basename(bases_path)}")
    print(f"   Salida: {destino}")
    generados = []
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
