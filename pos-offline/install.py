#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
INSTALADOR AUTOMÁTICO - POS OFFLINE MOON
Instala todas las dependencias y configura el sistema
"""

import os
import sys
import subprocess
import platform
from pathlib import Path

def print_header():
    print("\n" + "="*60)
    print("  POS OFFLINE MOON - INSTALADOR AUTOMÁTICO")
    print("="*60 + "\n")

def check_python_version():
    """Verifica que Python sea 3.7 o superior"""
    version = sys.version_info
    if version.major < 3 or (version.major == 3 and version.minor < 7):
        print("❌ ERROR: Se requiere Python 3.7 o superior")
        print(f"   Versión actual: {version.major}.{version.minor}.{version.micro}")
        return False
    print(f"✅ Python {version.major}.{version.minor}.{version.micro} detectado")
    return True

def create_venv():
    """Crea entorno virtual si no existe"""
    venv_path = Path("venv")
    
    if venv_path.exists():
        print("✅ Entorno virtual ya existe")
        return True
    
    print("\n🔧 Creando entorno virtual...")
    try:
        subprocess.check_call([
            sys.executable, "-m", "venv", "venv"
        ])
        print("✅ Entorno virtual creado")
        return True
    except subprocess.CalledProcessError:
        print("❌ Error al crear entorno virtual")
        print("💡 Asegúrate de tener instalado: python3-venv")
        print("   En Ubuntu/Debian: sudo apt-get install python3-venv")
        return False

def get_venv_python():
    """Obtiene la ruta del Python del entorno virtual"""
    if sys.platform == 'win32':
        return Path("venv") / "Scripts" / "python.exe"
    else:
        return Path("venv") / "bin" / "python"

def install_dependencies():
    """Instala dependencias desde requirements.txt en entorno virtual"""
    print("\n📦 Instalando dependencias en entorno virtual...")
    
    venv_python = get_venv_python()
    
    if not venv_python.exists():
        print("❌ Entorno virtual no encontrado")
        return False
    
    try:
        # Actualizar pip en el venv
        subprocess.check_call([
            str(venv_python), "-m", "pip", "install", "--upgrade", "pip"
        ], stdout=subprocess.DEVNULL, stderr=subprocess.DEVNULL)
        print("✅ pip actualizado")
    except:
        print("⚠️  No se pudo actualizar pip, continuando...")
    
    try:
        subprocess.check_call([
            str(venv_python), "-m", "pip", "install", "-r", "requirements.txt"
        ])
        print("✅ Dependencias instaladas correctamente")
        return True
    except subprocess.CalledProcessError:
        print("❌ Error al instalar dependencias")
        return False

def create_directories():
    """Crea directorios necesarios"""
    print("\n📁 Creando directorios...")
    
    directories = ["data", "logs", "backups"]
    for directory in directories:
        Path(directory).mkdir(exist_ok=True)
        print(f"✅ Directorio '{directory}' creado")

def create_config_file():
    """Crea archivo de configuración si no existe"""
    config_file = Path("config.json")
    
    if config_file.exists():
        print("\n✅ Archivo de configuración ya existe")
        return
    
    print("\n⚙️  Creando archivo de configuración...")
    
    config_example = Path("config.json.example")
    if config_example.exists():
        import shutil
        shutil.copy(config_example, config_file)
        print("✅ Archivo config.json creado desde ejemplo")
        print("⚠️  IMPORTANTE: Edita config.json con tus datos antes de usar")
    else:
        # Crear config básico
        import json
        default_config = {
            "server_url": "https://newmoon.posmoon.com.ar",
            "api_base": "https://newmoon.posmoon.com.ar/api",
            "id_cliente_moon": 14,
            "sync_interval": 60,
            "connection_check_interval": 5,
            "account_check_interval": 300
        }
        with open(config_file, 'w', encoding='utf-8') as f:
            json.dump(default_config, f, indent=4, ensure_ascii=False)
        print("✅ Archivo config.json creado con valores por defecto")
        print("⚠️  IMPORTANTE: Edita config.json con tus datos")

def create_run_script():
    """Crea scripts de ejecución para usar el entorno virtual"""
    print("\n📝 Creando scripts de ejecución...")
    
    system = platform.system()
    base_dir = Path(__file__).parent.absolute()
    
    if system == "Windows":
        # Script batch para Windows
        run_bat = base_dir / "run.bat"
        content = """@echo off
cd /d "%~dp0"
call venv\\Scripts\\activate.bat
python main.py
pause
"""
        run_bat.write_text(content, encoding='utf-8')
        print("✅ run.bat creado")
    else:
        # Script bash para Linux/Mac
        run_sh = base_dir / "run.sh"
        content = """#!/bin/bash
cd "$(dirname "$0")"
source venv/bin/activate
python main.py
"""
        run_sh.write_text(content, encoding='utf-8')
        run_sh.chmod(0o755)
        print("✅ run.sh creado")
    
    # Script para setup también
    if system == "Windows":
        setup_bat = base_dir / "setup.bat"
        content = """@echo off
cd /d "%~dp0"
call venv\\Scripts\\activate.bat
python setup.py
pause
"""
        setup_bat.write_text(content, encoding='utf-8')
    else:
        setup_sh = base_dir / "setup.sh"
        content = """#!/bin/bash
cd "$(dirname "$0")"
source venv/bin/activate
python setup.py
"""
        setup_sh.write_text(content, encoding='utf-8')
        setup_sh.chmod(0o755)

def create_desktop_shortcut():
    """Crea acceso directo en escritorio (Windows/Linux)"""
    print("\n🔗 Creando acceso directo...")
    
    system = platform.system()
    base_dir = Path(__file__).parent.absolute()
    
    if system == "Windows":
        try:
            import winshell
            from win32com.client import Dispatch
            
            desktop = winshell.desktop()
            shortcut_path = os.path.join(desktop, "POS Offline Moon.lnk")
            target = str(base_dir / "run.bat")
            wDir = str(base_dir)
            
            shell = Dispatch('WScript.Shell')
            shortcut = shell.CreateShortCut(shortcut_path)
            shortcut.Targetpath = target
            shortcut.WorkingDirectory = wDir
            shortcut.IconLocation = sys.executable
            shortcut.save()
            
            print("✅ Acceso directo creado en escritorio")
        except ImportError:
            print("⚠️  No se pudo crear acceso directo (instala pywin32)")
        except Exception as e:
            print(f"⚠️  No se pudo crear acceso directo: {e}")
    
    elif system == "Linux":
        desktop_file = Path.home() / "Desktop" / "pos-offline-moon.desktop"
        if not desktop_file.parent.exists():
            desktop_file = Path.home() / ".local" / "share" / "applications" / "pos-offline-moon.desktop"
        
        desktop_file.parent.mkdir(parents=True, exist_ok=True)
        
        run_script = base_dir / "run.sh"
        content = f"""[Desktop Entry]
Version=1.0
Type=Application
Name=POS Offline Moon
Comment=Sistema POS Offline con sincronización
Exec={run_script}
Icon=application-x-executable
Terminal=false
Categories=Office;
Path={base_dir}
"""
        desktop_file.write_text(content, encoding='utf-8')
        desktop_file.chmod(0o755)
        print(f"✅ Acceso directo creado: {desktop_file}")

def main():
    print_header()
    
    # Verificar Python
    if not check_python_version():
        sys.exit(1)
    
    # Crear entorno virtual
    if not create_venv():
        print("\n❌ Error al crear entorno virtual. Revisa los mensajes anteriores.")
        sys.exit(1)
    
    # Instalar dependencias
    if not install_dependencies():
        print("\n❌ Error en la instalación. Revisa los mensajes anteriores.")
        sys.exit(1)
    
    # Crear directorios
    create_directories()
    
    # Crear configuración
    create_config_file()
    
    # Crear acceso directo
    create_desktop_shortcut()
    
    # Crear script de ejecución
    create_run_script()
    
    print("\n" + "="*60)
    print("  ✅ INSTALACIÓN COMPLETADA")
    print("="*60)
    print("\n📝 PRÓXIMOS PASOS:")
    print("  1. Edita 'config.json' con tus datos del servidor")
    print("  2. Ejecuta './run.sh' (Linux/Mac) o 'run.bat' (Windows) para iniciar")
    print("  3. O ejecuta 'python setup.py' para configuración inicial")
    print("\n💡 IMPORTANTE: Usa './run.sh' o 'run.bat' para ejecutar la aplicación")
    print("   (esto asegura que use el entorno virtual correcto)")
    print("\n")

if __name__ == "__main__":
    main()
