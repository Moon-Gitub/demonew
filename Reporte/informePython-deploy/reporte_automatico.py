#!/usr/bin/env python3
"""
Reporte automático mensual — TODO EN ESTE ARCHIVO (no necesita reporte_util.py).

- Mes anterior al actual, sin preguntar fechas
- Sistemas viejos → reportes/sistemas_viejos/  (reporte.py + bases.txt)
- Sistemas nuevos → reportes/sistemas_nuevos/   (reporte_moon.py + bases2.txt)
- ZIP + mail a moondesarrollos@gmail.com

Requiere en el servidor reporte.py y reporte_moon.py ACTUALIZADOS (con MOON_AUTO).

Cron (día 1, 06:00):
  0 6 1 * * source /home/posmoonar/virtualenv/informePython/3.11/bin/activate && cd /home/posmoonar/informePython && python reporte_automatico.py >> logs/auto.log 2>&1
"""
from __future__ import annotations

import glob
import os
import smtplib
import ssl
import subprocess
import sys
import zipfile
from datetime import date
from email import encoders
from email.mime.base import MIMEBase
from email.mime.multipart import MIMEMultipart
from email.mime.text import MIMEText

BASE_DIR = os.path.dirname(os.path.abspath(__file__))
OUTPUT_DIR = os.path.join(BASE_DIR, "reportes")
DIR_VIEJOS = os.path.join(OUTPUT_DIR, "sistemas_viejos")
DIR_NUEVOS = os.path.join(OUTPUT_DIR, "sistemas_nuevos")
LOGS_DIR = os.path.join(BASE_DIR, "logs")
MAIL_CONFIG = os.path.join(BASE_DIR, "mail_config.env")
BASES_VIEJOS = "bases.txt"
BASES_NUEVOS = "bases2.txt"
MAIL_TO_DEFAULT = "moondesarrollos@gmail.com"


# --- utilidades (antes en reporte_util.py) ---

def mes_anterior(hoy: date | None = None) -> tuple[int, int]:
    hoy = hoy or date.today()
    if hoy.month == 1:
        return hoy.year - 1, 12
    return hoy.year, hoy.month - 1


def leer_mail_config() -> dict[str, str]:
    cfg: dict[str, str] = {
        "SMTP_HOST": "localhost",
        "SMTP_PORT": "25",
        "SMTP_USER": "",
        "SMTP_PASS": "",
        "SMTP_TLS": "0",
        "MAIL_FROM": "informe@posmoon.com.ar",
        "MAIL_TO": MAIL_TO_DEFAULT,
    }
    if not os.path.isfile(MAIL_CONFIG):
        return cfg
    with open(MAIL_CONFIG, encoding="utf-8") as f:
        for linea in f:
            linea = linea.strip()
            if not linea or linea.startswith("#") or "=" not in linea:
                continue
            k, v = linea.split("=", 1)
            cfg[k.strip()] = v.strip()
    return cfg


def comprimir_archivos(
    archivos: list[str],
    zip_path: str,
    log_texto: str = "",
    base_dir: str | None = None,
) -> str:
    os.makedirs(os.path.dirname(zip_path) or ".", exist_ok=True)
    with zipfile.ZipFile(zip_path, "w", zipfile.ZIP_DEFLATED) as zf:
        if log_texto:
            zf.writestr("ejecucion.log", log_texto)
        for ruta in archivos:
            if ruta and os.path.isfile(ruta):
                arcname = os.path.relpath(ruta, base_dir) if base_dir else os.path.basename(ruta)
                zf.write(ruta, arcname=arcname)
    return zip_path


def enviar_mail(adjunto: str, asunto: str, cuerpo: str, cfg: dict[str, str] | None = None) -> None:
    cfg = cfg or leer_mail_config()
    mail_to = cfg.get("MAIL_TO", MAIL_TO_DEFAULT)
    mail_from = cfg.get("MAIL_FROM", "informe@posmoon.com.ar")
    host = cfg.get("SMTP_HOST", "localhost")
    port = int(cfg.get("SMTP_PORT", "25"))
    user = cfg.get("SMTP_USER", "")
    password = cfg.get("SMTP_PASS", "")
    use_tls = cfg.get("SMTP_TLS", "0") in ("1", "true", "True", "yes")

    msg = MIMEMultipart()
    msg["From"] = mail_from
    msg["To"] = mail_to
    msg["Subject"] = asunto
    msg.attach(MIMEText(cuerpo, "plain", "utf-8"))

    if adjunto and os.path.isfile(adjunto):
        part = MIMEBase("application", "octet-stream")
        with open(adjunto, "rb") as f:
            part.set_payload(f.read())
        encoders.encode_base64(part)
        part.add_header("Content-Disposition", f'attachment; filename="{os.path.basename(adjunto)}"')
        msg.attach(part)

    if use_tls:
        context = ssl.create_default_context()
        with smtplib.SMTP(host, port, timeout=60) as server:
            server.starttls(context=context)
            if user:
                server.login(user, password)
            server.sendmail(mail_from, [mail_to], msg.as_string())
    else:
        with smtplib.SMTP(host, port, timeout=60) as server:
            if user:
                server.login(user, password)
            server.sendmail(mail_from, [mail_to], msg.as_string())

    print(f"📧 Mail enviado a {mail_to}")


# --- ejecución de reportes en subproceso ---

def verificar_scripts() -> bool:
    ok = True
    for nombre in ("reporte.py", "reporte_moon.py"):
        ruta = os.path.join(BASE_DIR, nombre)
        if not os.path.isfile(ruta):
            print(f"❌ Falta {nombre}")
            ok = False
            continue
        with open(ruta, encoding="utf-8", errors="ignore") as f:
            contenido = f.read()
        if "MOON_AUTO" not in contenido or "def ejecutar" not in contenido:
            print(f"❌ {nombre} es versión VIEJA — reemplazalo con el del repo.")
            ok = False
        else:
            print(f"✅ {nombre} OK")
    return ok


def correr_script(script: str, anio: int, mes: int, bases: str, output_dir: str) -> tuple[list[str], str]:
    env = os.environ.copy()
    env["MOON_AUTO"] = "1"
    env["MOON_ANIO"] = str(anio)
    env["MOON_MES"] = str(mes)
    env["MOON_BASES"] = bases
    env["MOON_OUTPUT_DIR"] = output_dir

    script_path = os.path.join(BASE_DIR, script)
    if not os.path.isfile(script_path):
        return [], f"❌ No existe {script_path}\n"

    proc = subprocess.run(
        [sys.executable, script_path],
        env=env,
        cwd=BASE_DIR,
        capture_output=True,
        text=True,
        stdin=subprocess.DEVNULL,
    )
    salida = proc.stdout or ""
    if proc.stderr:
        salida += proc.stderr
    if proc.returncode != 0:
        salida += f"\n❌ {script} terminó con código {proc.returncode}\n"
    if "Ingresá el año" in salida or "Ingresá el año" in (proc.stderr or ""):
        salida += f"\n❌ {script} pidió fecha — subí la versión nueva con MOON_AUTO.\n"

    prefijo = "reporte_moon_" if script == "reporte_moon.py" else "reporte_"
    archivos = sorted(glob.glob(os.path.join(output_dir, f"{prefijo}*_{anio}_{mes:02d}.xlsx")))
    return archivos, salida


def main() -> None:
    anio, mes = mes_anterior()
    os.makedirs(DIR_VIEJOS, exist_ok=True)
    os.makedirs(DIR_NUEVOS, exist_ok=True)
    os.makedirs(LOGS_DIR, exist_ok=True)

    print("=" * 55)
    print(f"  REPORTE AUTOMÁTICO — período {mes:02d}/{anio}")
    print("=" * 55)

    if not verificar_scripts():
        print("\nSubí estos 3 archivos desde Reporte/informePython/ del repo:")
        print("  reporte.py  reporte_moon.py  reporte_automatico.py")
        sys.exit(1)

    log_partes = [f"Reporte automático Moon — {mes:02d}/{anio}\n{'=' * 40}\n"]
    todos_archivos: list[str] = []

    print("\n--- Sistemas viejos (bases.txt) ---")
    archivos_viejos, log_v = correr_script("reporte.py", anio, mes, BASES_VIEJOS, DIR_VIEJOS)
    print(log_v, end="")
    log_partes.append("\n[SISTEMAS VIEJOS]\n" + log_v)
    todos_archivos.extend(archivos_viejos)

    print("\n--- Sistemas nuevos (bases2.txt) ---")
    archivos_nuevos, log_n = correr_script("reporte_moon.py", anio, mes, BASES_NUEVOS, DIR_NUEVOS)
    print(log_n, end="")
    log_partes.append("\n[SISTEMAS NUEVOS]\n" + log_n)
    todos_archivos.extend(archivos_nuevos)

    log_completo = "".join(log_partes)
    log_path = os.path.join(LOGS_DIR, f"auto_{anio}_{mes:02d}.log")
    with open(log_path, "w", encoding="utf-8") as f:
        f.write(log_completo)

    zip_path = os.path.join(OUTPUT_DIR, f"informes_moon_{anio}_{mes:02d}.zip")
    comprimir_archivos(todos_archivos, zip_path, log_completo, base_dir=OUTPUT_DIR)
    print(f"\n📦 ZIP: {zip_path} ({len(todos_archivos)} Excel)")

    cfg = leer_mail_config()
    try:
        enviar_mail(
            zip_path,
            f"Informes Moon {mes:02d}/{anio} — auto",
            f"Período: {mes:02d}/{anio}\nExcel: {len(todos_archivos)}\n",
            cfg,
        )
    except Exception as exc:
        print(f"❌ Error enviando mail: {exc}")
        print(f"   Creá mail_config.env (ver mail_config.example.env)")
        sys.exit(1)

    print("✅ Proceso automático terminado.")


if __name__ == "__main__":
    main()
