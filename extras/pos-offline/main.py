#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
APLICACIÓN PRINCIPAL - POS OFFLINE MOON
"""

import os
import sys
from pathlib import Path


def _relaunch_with_venv_if_needed():
    """Si existe venv y no estamos usándolo, relanzar con venv/bin/python."""
    app_dir = Path(__file__).resolve().parent
    venv_py = app_dir / "venv" / "bin" / "python"
    if not venv_py.is_file():
        return
    try:
        current = Path(sys.executable).resolve()
        target = venv_py.resolve()
    except OSError:
        return
    if current == target:
        return
    os.execv(str(target), [str(target), str(Path(__file__).resolve()), *sys.argv[1:]])


_relaunch_with_venv_if_needed()


def _print_install_help():
    app_dir = Path(__file__).resolve().parent
    print()
    print("Instalá dependencias en el venv (una sola vez):")
    print(f"  cd {app_dir}")
    print("  ./scripts/instalar-todo.sh")
    print()
    print("Luego ejecutá:")
    print(f"  {app_dir}/run.sh")
    print(f"  # o: {app_dir}/venv/bin/python main.py")
    print()


def main():
    print("Iniciando aplicación...")

    try:
        import tkinter as tk  # noqa: F401
    except ImportError:
        print("ERROR: Falta Tkinter. En Ubuntu: sudo apt install python3-tk")
        sys.exit(1)

    try:
        from gui import LoginWindow
        from config import config
    except ImportError as e:
        print(f"ERROR de importación: {e}")
        _print_install_help()
        sys.exit(1)

    import tkinter as tk

    print("Módulos importados correctamente")

    root = tk.Tk()
    root.withdraw()

    print("Creando ventana de login...")
    LoginWindow(root)

    root.update()
    root.update_idletasks()
    print("Iniciando interfaz...")
    root.mainloop()


if __name__ == "__main__":
    try:
        main()
    except KeyboardInterrupt:
        print("\nAplicación cerrada")
        sys.exit(0)
    except Exception as e:
        print(f"ERROR: {e}")
        import traceback
        traceback.print_exc()
        sys.exit(1)
