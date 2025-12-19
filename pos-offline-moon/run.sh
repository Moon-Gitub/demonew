#!/bin/bash
# Script para ejecutar POS Offline Moon con entorno virtual

cd "$(dirname "$0")"

# Activar entorno virtual
if [ -d "venv" ]; then
    source venv/bin/activate
    python main.py
else
    echo "❌ Entorno virtual no encontrado."
    echo "💡 Ejecuta primero: python install.py"
    exit 1
fi
