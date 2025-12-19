#!/bin/bash
# Script para ejecutar setup con entorno virtual

cd "$(dirname "$0")"

# Activar entorno virtual
if [ -d "venv" ]; then
    source venv/bin/activate
    python setup.py
else
    echo "❌ Entorno virtual no encontrado."
    echo "💡 Ejecuta primero: python install.py"
    exit 1
fi
