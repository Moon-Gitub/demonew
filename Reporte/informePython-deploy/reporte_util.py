#!/usr/bin/env python3
"""Utilidades compartidas: período, mail, compresión."""
from __future__ import annotations

import os
import smtplib
import ssl
import zipfile
from datetime import date
from email import encoders
from email.mime.base import MIMEBase
from email.mime.multipart import MIMEMultipart
from email.mime.text import MIMEText

BASE_DIR = os.path.dirname(os.path.abspath(__file__))
MAIL_CONFIG = os.path.join(BASE_DIR, "mail_config.env")
MAIL_TO_DEFAULT = "moondesarrollos@gmail.com"


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
                if base_dir:
                    arcname = os.path.relpath(ruta, base_dir)
                else:
                    arcname = os.path.basename(ruta)
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
