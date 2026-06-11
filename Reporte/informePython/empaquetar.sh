#!/usr/bin/env bash
# Genera informePython-deploy.zip para subir por cPanel al servidor.
set -euo pipefail
DIR="$(cd "$(dirname "$0")" && pwd)"
cd "$DIR"
OUT="$DIR/informePython-deploy.zip"

rm -f "$OUT"
zip -j "$OUT" \
  reporte.py \
  reporte_moon.py \
  reporte_automatico.py \
  reporte_menu.py \
  reporte_util.py \
  instalar.sh \
  verificar.sh \
  mail_config.example.env \
  bases2.txt

echo "Creado: $OUT"
echo "Subí el ZIP a /home/posmoonar/informePython/ y extraé (sobrescribir)."
echo "NO subas instalador.sh (es del POS offline, incorrecto)."
