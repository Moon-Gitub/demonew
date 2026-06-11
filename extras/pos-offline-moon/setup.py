#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
CONFIGURACIÓN INICIAL GUIADA
Guía al usuario para configurar el sistema por primera vez
"""

import json
import os
from pathlib import Path
import requests
from datetime import datetime

def print_header():
    print("\n" + "="*60)
    print("  CONFIGURACIÓN INICIAL - POS OFFLINE MOON")
    print("="*60 + "\n")

def get_input(prompt, default=None, validator=None):
    """Obtiene entrada del usuario con validación"""
    while True:
        if default:
            response = input(f"{prompt} [{default}]: ").strip()
            if not response:
                response = default
        else:
            response = input(f"{prompt}: ").strip()
        
        if validator:
            if validator(response):
                return response
            else:
                print("❌ Valor inválido, intenta nuevamente")
        else:
            return response

def test_connection(url):
    """Prueba conexión al servidor"""
    try:
        response = requests.get(f"{url}/", timeout=5)
        return response.status_code == 200
    except:
        return False

def main():
    print_header()
    
    config_file = Path("config.json")
    config = {}
    
    if config_file.exists():
        with open(config_file, 'r', encoding='utf-8') as f:
            config = json.load(f)
        print("📋 Configuración actual encontrada\n")
    
    print("Por favor, completa la siguiente información:\n")
    
    # URL del servidor
    server_url = get_input(
        "URL del servidor online",
        default=config.get("server_url", "https://newmoon.posmoon.com.ar"),
        validator=lambda x: x.startswith("http")
    )
    
    print(f"\n🔍 Probando conexión a {server_url}...")
    if test_connection(server_url):
        print("✅ Conexión exitosa")
    else:
        print("⚠️  No se pudo conectar. Verifica la URL o continúa para modo offline")
        continuar = input("¿Continuar de todas formas? (s/n): ").strip().lower()
        if continuar != 's':
            return
    
    # API Base
    api_base = get_input(
        "URL base de la API",
        default=config.get("api_base", f"{server_url}/api")
    )
    
    # ID Cliente Moon
    id_cliente = get_input(
        "ID Cliente Moon (número)",
        default=str(config.get("id_cliente_moon", 14)),
        validator=lambda x: x.isdigit()
    )
    
    # Intervalos
    sync_interval = get_input(
        "Intervalo de sincronización (segundos)",
        default=str(config.get("sync_interval", 60)),
        validator=lambda x: x.isdigit()
    )
    
    auto = get_input("¿Habilitar login automático? (s/n)", default="s").strip().lower() == "s"
    auto_time = "08:00"
    if auto:
        auto_time = get_input("Hora de auto-login (HH:MM)", default="08:00")

    # Guardar configuración
    config = {
        "server_url": server_url,
        "api_base": api_base,
        "id_cliente_moon": int(id_cliente),
        "id_empresa": int(get_input("ID Empresa", default="1", validator=lambda x: x.isdigit())),
        "sync_interval": int(sync_interval),
        "connection_check_interval": 5,
        "account_check_interval": 300,
        "auto_login_enabled": auto,
        "auto_login_time": auto_time,
        "auto_sync_on_login": True,
        "session_ttl_hours": 12,
        "configurado_en": datetime.now().isoformat()
    }
    
    with open(config_file, 'w', encoding='utf-8') as f:
        json.dump(config, f, indent=4, ensure_ascii=False)
    
    print("\n✅ Configuración guardada en config.json")

    if auto:
        guardar = get_input("¿Guardar usuario/contraseña en secrets.env? (s/n)", default="s").strip().lower()
        if guardar == "s":
            import getpass
            from config import config as app_config
            u = get_input("Usuario POS")
            p = getpass.getpass("Contraseña: ")
            if u and p:
                app_config.save_secrets(u, p)
                print("✅ secrets.env creado (no subir a git)")

    # Intentar sincronización inicial
    print("\n🔄 Intentando sincronización inicial...")
    try:
        from sync import SyncManager
        from connection import ConnectionMonitor
        
        connection_monitor = ConnectionMonitor()
        if connection_monitor.check_connection():
            sync_manager = SyncManager()
            sync_manager.sync_all(id_cliente_moon=int(id_cliente))
            print("✅ Sincronización inicial completada")
        else:
            print("⚠️  Sin conexión. La sincronización se hará cuando haya internet")
    except Exception as e:
        print(f"⚠️  Error en sincronización inicial: {e}")
        print("   Puedes sincronizar manualmente desde la aplicación")
    
    print("\n" + "="*60)
    print("  ✅ CONFIGURACIÓN COMPLETADA")
    print("="*60)
    print("\n🚀 Puedes iniciar la aplicación con: python main.py\n")

if __name__ == "__main__":
    main()
