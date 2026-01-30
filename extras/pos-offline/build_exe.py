#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
CREAR EJECUTABLE STANDALONE
Genera un .exe (Windows) o binario (Linux/Mac) con PyInstaller
"""

import PyInstaller.__main__
import sys
from pathlib import Path

def build_executable():
    """Crea ejecutable con PyInstaller"""
    
    print("\n" + "="*60)
    print("  CREANDO EJECUTABLE - POS OFFLINE MOON")
    print("="*60 + "\n")
    
    # Opciones de PyInstaller
    options = [
        'main.py',
        '--name=POS-Offline-Moon',
        '--onefile',  # Un solo archivo ejecutable
        '--windowed',  # Sin consola (GUI)
        '--hidden-import=sqlalchemy.dialects.sqlite',
        '--hidden-import=bcrypt',
        '--collect-all=tkinter',
        '--noconfirm',  # No preguntar
        '--clean'  # Limpiar cache
    ]
    
    # Agregar datos adicionales según plataforma
    if sys.platform == 'win32':
        options.append('--add-data=config.json.example;.')
    else:
        options.append('--add-data=config.json.example:.')
    
    print("🔨 Compilando ejecutable...")
    print("   Esto puede tardar varios minutos...\n")
    
    try:
        PyInstaller.__main__.run(options)
        print("\n✅ Ejecutable creado exitosamente")
        print("📁 Ubicación: dist/POS-Offline-Moon.exe (Windows)")
        print("              dist/POS-Offline-Moon (Linux/Mac)")
        print("\n💡 Puedes distribuir este ejecutable sin necesidad de Python")
    except Exception as e:
        print(f"\n❌ Error al crear ejecutable: {e}")
        sys.exit(1)

if __name__ == "__main__":
    build_executable()
