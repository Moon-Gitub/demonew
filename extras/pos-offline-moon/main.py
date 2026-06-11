#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
APLICACIÓN PRINCIPAL - POS OFFLINE MOON
"""

import argparse
import sys
import tkinter as tk
from tkinter import messagebox


def parse_args():
    p = argparse.ArgumentParser(description="POS Offline Moon")
    p.add_argument("--auto-login", action="store_true", help="Intentar login automático")
    p.add_argument("--setup-secrets", action="store_true", help="Asistente credenciales")
    p.add_argument("--sync-only", action="store_true", help="Sincronizar y salir")
    return p.parse_args()


def run_setup_secrets():
    from config import config
    print("=== Guardar credenciales para inicio automático ===")
    usuario = input("Usuario: ").strip()
    import getpass
    password = getpass.getpass("Contraseña: ")
    if not usuario or not password:
        print("Cancelado.")
        return 1
    config.save_secrets(usuario, password)
    hora = input("Hora auto-login (HH:MM) [08:00]: ").strip() or "08:00"
    config.update("auto_login_enabled", True)
    config.update("auto_login_time", hora)
    print("✅ secrets.env guardado. No subir a git.")
    return 0


def run_sync_only():
    from sync import SyncManager
    from connection import ConnectionMonitor
    if not ConnectionMonitor().check_connection():
        print("Sin conexión.")
        return 1
    SyncManager().sync_all()
    return 0


def open_pos(auth_manager, sync_manager, connection_monitor, root):
    from config import config as cfg
    from ui.pos_app import POSApp
    POSApp(root, auth_manager, sync_manager, connection_monitor, cfg.ID_CLIENTE_MOON).run()


def main():
    args = parse_args()

    if args.setup_secrets:
        sys.exit(run_setup_secrets())
    if args.sync_only:
        sys.exit(run_sync_only())

    from config import config
    from auth import AuthManager
    from sync import SyncManager
    from connection import ConnectionMonitor
    from scheduler import MorningScheduler

    auth = AuthManager()
    sync = SyncManager()
    conn = ConnectionMonitor()

    def morning_job():
        if conn.check_connection():
            sync.sync_all(silent=True)
        auth.try_auto_login(sync_first=True)

    scheduler = MorningScheduler(on_morning_job=morning_job)
    scheduler.start()

    root = tk.Tk()
    root.withdraw()
    root.update_idletasks()

    print("POS Offline Moon — iniciando interfaz...", flush=True)

    auto = args.auto_login or config.AUTO_LOGIN_ENABLED
    if auto:
        if conn.check_connection() and config.AUTO_SYNC_ON_LOGIN:
            sync.sync_all(silent=True)
        result = auth.try_auto_login(sync_first=False)
        if result.get("success"):
            open_pos(auth, sync, conn, root)
            root.mainloop()
            return
        if auto:
            print("Auto-login falló:", result.get("message", ""), flush=True)
            messagebox.showwarning(
                "Auto-login",
                result.get("message", "No se pudo iniciar sesión automática.\nIngresá usuario y contraseña."),
            )

    from ui.login import LoginWindow
    LoginWindow(root, auth, sync, conn, on_success=lambda: open_pos(auth, sync, conn, root))
    root.mainloop()


if __name__ == "__main__":
    try:
        main()
    except KeyboardInterrupt:
        sys.exit(0)
    except Exception as e:
        print(f"Error: {e}")
        import traceback
        traceback.print_exc()
        sys.exit(1)
