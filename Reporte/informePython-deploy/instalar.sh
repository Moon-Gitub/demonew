#!/usr/bin/env bash
set -euo pipefail

DIR="$(cd "$(dirname "$0")" && pwd)"
VENV_CPANEL="/home/posmoonar/virtualenv/informePython/3.11/bin/activate"
VENV_LOCAL="$DIR/venv/bin/activate"

echo "=============================================="
echo "  Informe Python Moon — instalador"
echo "=============================================="
echo "Carpeta: $DIR"
echo

activar_python() {
  if [[ -f "$VENV_CPANEL" ]]; then
    source "$VENV_CPANEL"
  elif [[ -f "$VENV_LOCAL" ]]; then
    source "$VENV_LOCAL"
  else
    python3 -m venv "$DIR/venv"
    source "$VENV_LOCAL"
  fi
  cd "$DIR"
  echo "→ Python: $(which python) ($(python --version 2>&1))"
}

activar_python

python -m pip install --upgrade pip -q
python -m pip install pymysql pandas openpyxl -q
python -c "import pymysql, pandas, openpyxl; print('OK: dependencias listas')"

mkdir -p "$DIR/reportes" "$DIR/reportes/sistemas_viejos" "$DIR/reportes/sistemas_nuevos" "$DIR/logs"
chmod +x "$DIR"/reporte_menu.py "$DIR"/reporte_automatico.py 2>/dev/null || true

[[ -f "$DIR/bases.txt" ]] && echo "→ bases.txt (sistemas viejos)" || echo "⚠️ Falta bases.txt"
[[ -f "$DIR/bases2.txt" ]] && echo "→ bases2.txt (sistemas nuevos)" || echo "⚠️ Falta bases2.txt"
[[ -f "$DIR/mail_config.env" ]] && echo "→ mail_config.env OK" || echo "⚠️ Copiá mail_config.example.env → mail_config.env"

echo
echo "=============================================="
echo "  Listo"
echo "=============================================="
echo
echo "Menú interactivo (elegís viejos o nuevos):"
echo "  python reporte_menu.py"
echo
echo "Reporte clásico directo:"
echo "  python reporte.py          # bases.txt"
echo
echo "Reporte nuevo directo:"
echo "  python reporte_moon.py     # bases2.txt"
echo
echo "Automático (mes anterior + ZIP + mail):"
echo "  python reporte_automatico.py"
echo
