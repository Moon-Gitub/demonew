#!/usr/bin/env bash
set -euo pipefail

SERVICE_NAME="pos-offline.service"
USER_UNIT_DIR="${XDG_CONFIG_HOME:-$HOME/.config}/systemd/user"
SERVICE_FILE="$USER_UNIT_DIR/$SERVICE_NAME"

systemctl --user stop "$SERVICE_NAME" 2>/dev/null || true
systemctl --user disable "$SERVICE_NAME" 2>/dev/null || true

if [[ -f "$SERVICE_FILE" ]]; then
  rm -f "$SERVICE_FILE"
  echo "Eliminado: $SERVICE_FILE"
fi

systemctl --user daemon-reload
echo "Servicio pos-offline desinstalado."
