#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""Configuración centralizada: config.json + secrets.env"""

import json
import os
from pathlib import Path
from datetime import datetime

BASE_DIR = Path(__file__).parent
SECRETS_FILE = BASE_DIR / "secrets.env"


def _load_dotenv():
    try:
        from dotenv import load_dotenv
        if SECRETS_FILE.exists():
            load_dotenv(SECRETS_FILE)
        alt = BASE_DIR / ".env.offline"
        if alt.exists():
            load_dotenv(alt)
    except ImportError:
        if SECRETS_FILE.exists():
            for line in SECRETS_FILE.read_text(encoding="utf-8").splitlines():
                line = line.strip()
                if not line or line.startswith("#") or "=" not in line:
                    continue
                k, _, v = line.partition("=")
                os.environ.setdefault(k.strip(), v.strip().strip('"').strip("'"))


_load_dotenv()


class Config:
    def __init__(self):
        self.config_file = BASE_DIR / "config.json"
        self.config = self._load_config()

    def _load_config(self):
        if not self.config_file.exists():
            default = self._get_default()
            self._save_config(default)
            return default
        try:
            with open(self.config_file, "r", encoding="utf-8") as f:
                data = json.load(f)
            default = self._get_default()
            for k, v in default.items():
                data.setdefault(k, v)
            return data
        except Exception as e:
            print(f"Error cargando configuración: {e}")
            return self._get_default()

    def _get_default(self):
        return {
            "server_url": "https://newmoon.posmoon.com.ar",
            "api_base": "https://newmoon.posmoon.com.ar/api",
            "id_cliente_moon": 14,
            "id_empresa": 1,
            "sync_interval": 60,
            "connection_check_interval": 5,
            "account_check_interval": 300,
            "auto_login_enabled": False,
            "auto_login_time": "08:00",
            "auto_sync_on_login": True,
            "session_ttl_hours": 12,
            "pos_api_key": "",
        }

    def _save_config(self, config):
        with open(self.config_file, "w", encoding="utf-8") as f:
            json.dump(config, f, indent=4, ensure_ascii=False)

    @property
    def SERVER_URL(self):
        return self.config.get("server_url", "https://newmoon.posmoon.com.ar").rstrip("/")

    @property
    def API_BASE(self):
        return self.config.get("api_base", f"{self.SERVER_URL}/api").rstrip("/")

    @property
    def ID_CLIENTE_MOON(self):
        return int(self.config.get("id_cliente_moon", 14))

    @property
    def ID_EMPRESA(self):
        return int(self.config.get("id_empresa", 1))

    @property
    def SYNC_INTERVAL(self):
        return int(self.config.get("sync_interval", 60))

    @property
    def CONNECTION_CHECK_INTERVAL(self):
        return int(self.config.get("connection_check_interval", 5))

    @property
    def ACCOUNT_CHECK_INTERVAL(self):
        return int(self.config.get("account_check_interval", 300))

    @property
    def AUTO_LOGIN_ENABLED(self):
        return bool(self.config.get("auto_login_enabled", False))

    @property
    def AUTO_LOGIN_TIME(self):
        return str(self.config.get("auto_login_time", "08:00"))

    @property
    def AUTO_SYNC_ON_LOGIN(self):
        return bool(self.config.get("auto_sync_on_login", True))

    @property
    def SESSION_TTL_HOURS(self):
        return int(self.config.get("session_ttl_hours", 12))

    @property
    def POS_API_KEY(self):
        return os.environ.get("POS_OFFLINE_API_KEY", "") or self.config.get("pos_api_key", "")

    @property
    def AUTO_LOGIN_USER(self):
        return os.environ.get("POS_OFFLINE_USER", "").strip()

    @property
    def AUTO_LOGIN_PASSWORD(self):
        return os.environ.get("POS_OFFLINE_PASSWORD", "")

    @property
    def DB_PATH(self):
        data_dir = BASE_DIR / "data"
        data_dir.mkdir(exist_ok=True)
        return data_dir / "pos_local.db"

    def update(self, key, value):
        self.config[key] = value
        self._save_config(self.config)

    def save_secrets(self, usuario, password, chmod_restrict=True):
        lines = [
            "# Credenciales POS Offline - NO subir a git",
            f"POS_OFFLINE_USER={usuario}",
            f"POS_OFFLINE_PASSWORD={password}",
        ]
        if self.POS_API_KEY:
            lines.append(f"POS_OFFLINE_API_KEY={self.POS_API_KEY}")
        SECRETS_FILE.write_text("\n".join(lines) + "\n", encoding="utf-8")
        if chmod_restrict and os.name != "nt":
            try:
                os.chmod(SECRETS_FILE, 0o600)
            except OSError:
                pass
        self.config["auto_login_enabled"] = True
        self._save_config(self.config)

    def has_auto_credentials(self):
        return bool(self.AUTO_LOGIN_USER and self.AUTO_LOGIN_PASSWORD)


config = Config()
