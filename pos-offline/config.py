#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
CONFIGURACIÓN CENTRALIZADA
Lee configuración desde config.json
"""

import json
from pathlib import Path
from datetime import datetime

class Config:
    def __init__(self):
        self.config_file = Path(__file__).parent / "config.json"
        self.config = self._load_config()
    
    def _load_config(self):
        """Carga configuración desde archivo"""
        if not self.config_file.exists():
            # Crear configuración por defecto
            default = {
                "server_url": "https://newmoon.posmoon.com.ar",
                "api_base": "https://newmoon.posmoon.com.ar/api",
                "id_cliente_moon": 14,
                "sync_interval": 60,
                "connection_check_interval": 5,
                "account_check_interval": 300
            }
            self._save_config(default)
            return default
        
        try:
            with open(self.config_file, 'r', encoding='utf-8') as f:
                return json.load(f)
        except Exception as e:
            print(f"Error cargando configuración: {e}")
            return self._get_default()
    
    def _get_default(self):
        """Configuración por defecto"""
        return {
            "server_url": "https://newmoon.posmoon.com.ar",
            "api_base": "https://newmoon.posmoon.com.ar/api",
            "id_cliente_moon": 14,
            "sync_interval": 60,
            "connection_check_interval": 5,
            "account_check_interval": 300
        }
    
    def _save_config(self, config):
        """Guarda configuración"""
        with open(self.config_file, 'w', encoding='utf-8') as f:
            json.dump(config, f, indent=4, ensure_ascii=False)
    
    @property
    def SERVER_URL(self):
        return self.config.get("server_url", "https://newmoon.posmoon.com.ar")
    
    @property
    def API_BASE(self):
        return self.config.get("api_base", f"{self.SERVER_URL}/api")
    
    @property
    def ID_CLIENTE_MOON(self):
        return self.config.get("id_cliente_moon", 14)
    
    @property
    def SYNC_INTERVAL(self):
        return self.config.get("sync_interval", 60)
    
    @property
    def CONNECTION_CHECK_INTERVAL(self):
        return self.config.get("connection_check_interval", 5)
    
    @property
    def ACCOUNT_CHECK_INTERVAL(self):
        return self.config.get("account_check_interval", 300)
    
    @property
    def DB_PATH(self):
        base_dir = Path(__file__).parent
        data_dir = base_dir / "data"
        data_dir.mkdir(exist_ok=True)
        return data_dir / "pos_local.db"
    
    def update(self, key, value):
        """Actualiza un valor de configuración"""
        self.config[key] = value
        self._save_config(self.config)

# Instancia global
config = Config()
