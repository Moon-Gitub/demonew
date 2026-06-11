#!/usr/bin/env bash
# Instala servicio systemd de usuario para arrancar POS offline al iniciar sesión gráfica.
set -euo pipefail

APP_DIR="$(cd "$(dirname "$0")/.." && pwd)"
LAUNCHER="$APP_DIR/scripts/iniciar-pos-gui.sh"
SERVICE_NAME="pos-offline.service"
USER_UNIT_DIR="${XDG_CONFIG_HOME:-$HOME/.config}/systemd/user"
SERVICE_FILE="$USER_UNIT_DIR/$SERVICE_NAME"

echo "=============================================="
echo "  POS Offline — instalador systemd (usuario)"
echo "=============================================="
echo "Carpeta: $APP_DIR"
echo

if [[ ! -f "$APP_DIR/main.py" ]]; then
  echo "ERROR: No se encuentra main.py en $APP_DIR"
  exit 1
fi

if [[ ! -d "$APP_DIR/venv" ]]; then
  echo "ERROR: Falta el entorno virtual."
  echo "  cd \"$APP_DIR\""
  echo "  ./scripts/instalar-todo.sh"
  exit 1
fi

chmod +x "$LAUNCHER"

mkdir -p "$USER_UNIT_DIR"
mkdir -p "$APP_DIR/logs"

# Detectar ruta de Xauthority (sesión gráfica local)
XAUTH="${XAUTHORITY:-$HOME/.Xauthority}"
if [[ ! -f "$XAUTH" ]]; then
  XAUTH="$HOME/.Xauthority"
fi

cat > "$SERVICE_FILE" <<EOF
[Unit]
Description=POS Offline Moon (arranque automático)
After=graphical-session.target
PartOf=graphical-session.target

[Service]
Type=simple
WorkingDirectory=$APP_DIR
Environment=HOME=$HOME
Environment=DISPLAY=:0
Environment=XAUTHORITY=$XAUTH
ExecStart=$LAUNCHER
Restart=on-failure
RestartSec=5

[Install]
WantedBy=default.target
EOF

echo "Servicio escrito en:"
echo "  $SERVICE_FILE"
echo

systemctl --user daemon-reload
systemctl --user enable "$SERVICE_NAME"
echo
echo "Servicio habilitado. Se iniciará al entrar a tu sesión gráfica."
echo

read -r -p "¿Iniciarlo ahora? (s/n) [s]: " START_NOW
START_NOW=${START_NOW:-s}
if [[ "$START_NOW" =~ ^[sS]$ ]]; then
  systemctl --user start "$SERVICE_NAME"
  sleep 2
  systemctl --user status "$SERVICE_NAME" --no-pager || true
fi

echo
echo "Comandos útiles:"
echo "  systemctl --user status pos-offline"
echo "  systemctl --user restart pos-offline"
echo "  systemctl --user stop pos-offline"
echo "  journalctl --user -u pos-offline -f"
echo "  tail -f \"$APP_DIR/logs/systemd.log\""
echo
echo "Desinstalar:"
echo "  $APP_DIR/scripts/desinstalar-servicio-systemd.sh"
echo
echo "Opcional — arrancar aunque no haya login gráfico previo (kiosk):"
echo "  sudo loginctl enable-linger \$USER"
