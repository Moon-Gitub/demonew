#!/usr/bin/env bash
# Instalador para informePython en hosting compartido (sin sudo, sin GUI).
# NO es el instalador del POS offline de escritorio.
set -euo pipefail

DIR="/home/posmoonar/informePython"
VENV="/home/posmoonar/virtualenv/informePython/3.11/bin/activate"

echo "=============================================="
echo "  Informe ventas Moon — instalador (hosting)"
echo "=============================================="
echo "Carpeta: $DIR"
echo

if [[ ! -d "$DIR" ]]; then
  echo "ERROR: No existe $DIR"
  exit 1
fi

if [[ ! -f "$VENV" ]]; then
  echo "ERROR: No se encontró el virtualenv:"
  echo "  $VENV"
  echo "Creá la app Python 3.11 en cPanel → Setup Python App → informePython"
  exit 1
fi

# shellcheck source=/dev/null
source "$VENV"
cd "$DIR"

echo "→ Python: $(which python)"
echo "→ Instalando pymysql, pandas, openpyxl..."
python -m pip install --upgrade pip
python -m pip install pymysql pandas openpyxl

echo "→ Verificando..."
python -c "import pymysql, pandas, openpyxl; print('OK: dependencias del informe listas')"

mkdir -p "$DIR/reportes"

if [[ ! -f "$DIR/bases.txt" ]]; then
  cat > "$DIR/bases.txt.example" <<'EOF'
# Una línea por base de datos. Copiar a bases.txt y completar.
# host=localhost;dbname=MI_DB;user=MI_USER;pass=MI_CLAVE
EOF
  echo
  echo "⚠️ Falta bases.txt — copiá bases.txt.example → bases.txt y completá las conexiones."
else
  echo "→ bases.txt encontrado"
fi

if [[ ! -f "$DIR/reporte-ventas-mensual.py" ]]; then
  echo "⚠️ Falta reporte-ventas-mensual.py en $DIR"
else
  echo "→ reporte-ventas-mensual.py OK"
fi

echo
echo "=============================================="
echo "  Listo"
echo "=============================================="
echo
echo "Ejecutar informe:"
echo "  source $VENV"
echo "  cd $DIR"
echo "  python reporte-ventas-mensual.py --anio 2025 --mes 5"
echo
echo "Excel en: $DIR/reportes/"
echo
echo "NOTA: El POS offline (./run.sh, tkinter) va en la PC de caja del cliente,"
echo "      NO en este servidor compartido."
echo
