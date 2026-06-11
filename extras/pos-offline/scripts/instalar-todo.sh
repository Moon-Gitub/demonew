#!/usr/bin/env bash
# Instalación completa POS offline (Ubuntu/Debian, PEP 668).
# Usa SIEMPRE venv/bin/pip — nunca pip del sistema.
set -euo pipefail

APP_DIR="$(cd "$(dirname "$0")/.." && pwd)"
cd "$APP_DIR"

echo "=============================================="
echo "  POS Offline — instalación (venv)"
echo "=============================================="
echo "Carpeta: $APP_DIR"
echo

if ! command -v python3 >/dev/null; then
  echo "ERROR: Instalá Python 3: sudo apt install python3 python3-venv python3-full"
  exit 1
fi

instalar_paquetes_sistema() {
  local pkgs=(python3-venv python3-full python3-tk x11-utils)
  if command -v apt-get >/dev/null 2>&1; then
    if command -v sudo >/dev/null 2>&1; then
      echo "→ Paquetes del sistema (sudo apt)..."
      sudo apt-get update -qq || {
        echo "⚠️ apt-get update falló; se continúa sin paquetes del sistema."
        return 0
      }
      sudo apt-get install -y "${pkgs[@]}" || {
        echo "⚠️ apt-get install falló."
        echo "   Instalá manualmente: sudo apt install ${pkgs[*]}"
      }
    elif [[ "$(id -u)" -eq 0 ]]; then
      echo "→ Paquetes del sistema (apt, root)..."
      apt-get update -qq || true
      apt-get install -y "${pkgs[@]}" || true
    else
      echo "⚠️ Hay apt-get pero no sudo. Omitiendo paquetes del sistema."
      echo "   Pedí al admin: apt install ${pkgs[*]}"
    fi
  else
    echo "→ Sin apt-get (no es Debian/Ubuntu). Omitiendo paquetes del sistema."
    echo "   Asegurate de tener python3-venv y python3-tk instalados."
  fi
}

instalar_paquetes_sistema

crear_venv() {
  if [[ -d "$APP_DIR/venv/bin" && -x "$APP_DIR/venv/bin/python" ]]; then
    echo "→ venv ya existe"
    return 0
  fi

  if [[ -d "$APP_DIR/venv" ]]; then
    echo "→ venv roto/incompleto; recreando..."
    rm -rf "$APP_DIR/venv"
  fi

  echo "→ Creando entorno virtual..."
  if ! python3 -m venv "$APP_DIR/venv"; then
    echo "ERROR: no se pudo crear venv."
    echo "  En Ubuntu/Debian: sudo apt install python3-venv python3-full"
    exit 1
  fi
}

crear_venv

VENV_PY="$APP_DIR/venv/bin/python"
VENV_PIP="$APP_DIR/venv/bin/pip"

if [[ ! -x "$VENV_PY" || ! -x "$VENV_PIP" ]]; then
  echo "ERROR: venv incompleto (falta bin/python o bin/pip)."
  echo "  rm -rf venv && ./scripts/instalar-todo.sh"
  exit 1
fi

echo "→ Actualizando pip en el venv..."
"$VENV_PIP" install --upgrade pip

echo "→ Instalando dependencias (sin Pillow, no se usa)..."
"$VENV_PIP" install "requests>=2.31.0" "sqlalchemy>=2.0.23" "bcrypt>=4.1.2"

echo "→ Verificando imports..."
"$VENV_PY" -c "import sqlalchemy, bcrypt, requests; print('OK: dependencias listas')"

if ! "$VENV_PY" -c "import tkinter" 2>/dev/null; then
  echo "⚠️ Falta Tkinter (python3-tk). La GUI no abrirá hasta instalarlo."
  echo "   Ubuntu/Debian: sudo apt install python3-tk"
fi

chmod +x "$APP_DIR/run.sh" 2>/dev/null || true
cat > "$APP_DIR/run.sh" <<'RUNSH'
#!/usr/bin/env bash
set -euo pipefail
APP_DIR="$(cd "$(dirname "$0")" && pwd)"
cd "$APP_DIR"
if [[ ! -d "$APP_DIR/venv" ]]; then
  echo "ERROR: Ejecutá ./scripts/instalar-todo.sh"
  exit 1
fi
exec "$APP_DIR/venv/bin/python" -u "$APP_DIR/main.py" "$@"
RUNSH
chmod +x "$APP_DIR/run.sh"

mkdir -p "$APP_DIR/logs" "$APP_DIR/data"

if [[ ! -f "$APP_DIR/config.json" ]]; then
  echo
  echo "→ Siguiente paso: configurar servidor e ID cliente"
  echo "  $VENV_PY setup.py"
else
  echo "→ config.json ya existe"
fi

echo
echo "=============================================="
echo "  Instalación terminada"
echo "=============================================="
echo
echo "Probar la app:"
echo "  ./run.sh"
echo "  # (también: $VENV_PY main.py — main.py relanza solo al venv si existe)"
echo
echo "Autostart al iniciar sesión (systemd):"
echo "  $APP_DIR/scripts/instalar-servicio-systemd.sh"
echo
