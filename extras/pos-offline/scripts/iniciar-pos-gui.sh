#!/usr/bin/env bash
# Lanza POS offline cuando ya hay escritorio gráfico (systemd user).
set -euo pipefail

APP_DIR="$(cd "$(dirname "$0")/.." && pwd)"
cd "$APP_DIR"

LOG_DIR="$APP_DIR/logs"
mkdir -p "$LOG_DIR"

if [[ ! -d "$APP_DIR/venv" ]]; then
  echo "ERROR: No existe venv en $APP_DIR"
  echo "Ejecutá: python3 -m venv venv && source venv/bin/activate && pip install -r requirements.txt"
  exit 1
fi

# shellcheck source=/dev/null
source "$APP_DIR/venv/bin/activate"

# Esperar pantalla (hasta ~60 s)
for _ in $(seq 1 60); do
  if [[ -n "${DISPLAY:-}" ]] && xdpyinfo >/dev/null 2>&1; then
    break
  fi
  # Intentar DISPLAY por defecto en sesión local
  if [[ -z "${DISPLAY:-}" ]] && [[ -S "/tmp/.X11-unix/X0" ]]; then
    export DISPLAY=:0
  fi
  if [[ -n "${DISPLAY:-}" ]] && xdpyinfo >/dev/null 2>&1; then
    break
  fi
  sleep 1
done

if ! xdpyinfo >/dev/null 2>&1; then
  echo "$(date -Iseconds) Sin DISPLAY disponible" >> "$LOG_DIR/systemd.log"
  exit 1
fi

exec python3 -u "$APP_DIR/main.py" >> "$LOG_DIR/systemd.log" 2>&1
