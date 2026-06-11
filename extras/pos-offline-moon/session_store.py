#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""Sesión local del día (SQLite configuracion)."""

import json
from datetime import datetime, timedelta
from database import get_session, Configuracion
from config import config


def _get(clave, default=None):
    session = get_session()
    try:
        row = session.query(Configuracion).filter_by(clave=clave).first()
        return row.valor if row else default
    finally:
        session.close()


def _set(clave, valor):
    session = get_session()
    try:
        row = session.query(Configuracion).filter_by(clave=clave).first()
        if row:
            row.valor = valor
        else:
            session.add(Configuracion(clave=clave, valor=valor))
        session.commit()
    finally:
        session.close()


def save_session(usuario_id, usuario_nombre, perfil="", sucursal=""):
    data = {
        "usuario_id": usuario_id,
        "usuario": usuario_nombre,
        "perfil": perfil,
        "sucursal": sucursal,
        "at": datetime.now().isoformat(),
    }
    _set("active_session", json.dumps(data))


def load_session():
    raw = _get("active_session")
    if not raw:
        return None
    try:
        data = json.loads(raw)
        at = datetime.fromisoformat(data.get("at", datetime.now().isoformat()))
        if datetime.now() - at > timedelta(hours=config.SESSION_TTL_HOURS):
            clear_session()
            return None
        return data
    except Exception:
        return None


def clear_session():
    session = get_session()
    try:
        session.query(Configuracion).filter_by(clave="active_session").delete()
        session.commit()
    finally:
        session.close()
