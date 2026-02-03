#!/usr/bin/env bash
# =============================================================================
# Script: actualizar-desde-github.sh
# Actualiza el proyecto a la última versión del repo GitHub (Moon-Gitub/demonew).
#
# CÓMO USARLO EN EL VPS (por cada cuenta):
#   1. Subir este script a la cuenta (o clonar el repo una vez y usarlo desde ahí).
#   2. En la terminal del VPS:
#      cd /home/CUENTA/public_html
#      bash /ruta/donde/esté/actualizar-desde-github.sh
#      # o, si el script está dentro del proyecto:
#      bash actualizar-desde-github.sh
#   3. La primera vez pedirá usuario y contraseña de GitHub:
#      - Usuario: tu usuario de GitHub (ej. Moon-Gitub)
#      - Contraseña: un Personal Access Token (no la contraseña de la cuenta).
#   4. Para que no pida cada vez: git config --global credential.helper store
#
# Comandos:
#   ./actualizar-desde-github.sh                      # pisa todo con la versión del repo (default)
#   ./actualizar-desde-github.sh /home/cuenta/public_html
#   ./actualizar-desde-github.sh --merge               # intenta git pull (conserva cambios locales si puede)
# =============================================================================

set -e

# HTTPS: funciona en el VPS con usuario + token (no requiere SSH)
REPO_URL="https://github.com/Moon-Gitub/demonew.git"
RAMA="main"

# Por defecto: siempre pisar con la versión del repo (reset --hard + clean)
MERGE=0
RUTA_PROYECTO=""

for arg in "$@"; do
  if [ "$arg" = "--merge" ] || [ "$arg" = "-m" ]; then
    MERGE=1
  elif [ -z "$RUTA_PROYECTO" ]; then
    RUTA_PROYECTO="$arg"
  fi
done

if [ -z "$RUTA_PROYECTO" ]; then
  RUTA_PROYECTO="$(pwd)"
fi

if [ ! -d "$RUTA_PROYECTO" ]; then
  echo "Creando directorio y clonando repositorio en: $RUTA_PROYECTO"
  git clone -b "$RAMA" "$REPO_URL" "$RUTA_PROYECTO"
  echo "Listo. Proyecto clonado en la rama $RAMA."
  exit 0
fi

cd "$RUTA_PROYECTO"

if [ ! -d .git ]; then
  echo "Error: '$RUTA_PROYECTO' no es un repositorio git."
  echo "Para clonar desde cero: rm -rf $RUTA_PROYECTO && $0 $RUTA_PROYECTO"
  exit 1
fi

# Asegurar que el remote apunte al repo correcto
REMOTE_URL=$(git config --get remote.origin.url 2>/dev/null || true)
if [ -z "$REMOTE_URL" ]; then
  echo "Configurando remote 'origin' -> $REPO_URL"
  git remote add origin "$REPO_URL"
elif [ "$REMOTE_URL" != "$REPO_URL" ]; then
  echo "Actualizando remote 'origin' a: $REPO_URL"
  git remote set-url origin "$REPO_URL"
fi

echo "Obteniendo última versión desde GitHub (rama $RAMA)..."
git fetch origin "$RAMA"

if [ "$MERGE" -eq 1 ]; then
  echo "Intentando merge (git pull)..."
  git pull origin "$RAMA"
else
  echo "Actualizando y pisando cambios locales (reset --hard + clean)..."
  git reset --hard "origin/$RAMA"
  git clean -fd
fi

echo "Proyecto actualizado a la última versión en $RAMA."
