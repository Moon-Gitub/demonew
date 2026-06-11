#!/usr/bin/env bash
# Cron / systemd: ejecutar a la hora de apertura (ej. 08:00)
cd "$(dirname "$0")/.." || exit 1
export DISPLAY="${DISPLAY:-:0}"
if [ -d "venv" ]; then
  source venv/bin/activate
fi
python3 main.py --auto-login >> logs/matutino.log 2>&1
