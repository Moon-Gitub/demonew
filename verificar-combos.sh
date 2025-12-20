#!/bin/bash
# Script para verificar que todos los archivos de combos estén presentes

echo "🔍 Verificando archivos del módulo de combos..."
echo ""

ARCHIVOS_REQUERIDOS=(
    "modelos/combos.modelo.php"
    "controladores/combos.controlador.php"
    "ajax/combos.ajax.php"
    "vistas/modulos/combos.php"
    "vistas/js/combos.js"
    "db/crear-tablas-combos.sql"
)

TODOS_PRESENTES=true

for archivo in "${ARCHIVOS_REQUERIDOS[@]}"; do
    if [ -f "$archivo" ]; then
        echo "✅ $archivo - PRESENTE"
    else
        echo "❌ $archivo - FALTANTE"
        TODOS_PRESENTES=false
    fi
done

echo ""
if [ "$TODOS_PRESENTES" = true ]; then
    echo "✅ Todos los archivos están presentes"
    exit 0
else
    echo "❌ Faltan algunos archivos. Ejecuta: git pull origin main"
    exit 1
fi
