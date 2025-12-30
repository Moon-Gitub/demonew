#!/bin/bash

# Script para sincronizar el hosting con GitHub
# Hace reset hard a la versión de GitHub, eliminando todos los cambios locales

set -e

# Colores
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

echo -e "${YELLOW}════════════════════════════════════════════════════════${NC}"
echo -e "${YELLOW}  SINCRONIZACIÓN DEL HOSTING CON GITHUB${NC}"
echo -e "${YELLOW}════════════════════════════════════════════════════════${NC}"
echo ""
echo -e "${RED}⚠️  ADVERTENCIA: Este script eliminará TODOS los cambios locales${NC}"
echo -e "${RED}   y dejará el hosting exactamente igual a GitHub${NC}"
echo ""

# Verificar que estamos en un repositorio git
if [ ! -d .git ]; then
    echo -e "${RED}✗ Error: No estás en un repositorio git${NC}"
    exit 1
fi

# Eliminar archivo de swap si existe
if [ -f .git/.MERGE_MSG.swp ]; then
    echo -e "${YELLOW}Eliminando archivo de swap de Vim...${NC}"
    rm -f .git/.MERGE_MSG.swp
fi

# Cancelar cualquier merge en progreso
if [ -f .git/MERGE_HEAD ]; then
    echo -e "${YELLOW}Cancelando merge en progreso...${NC}"
    git merge --abort 2>/dev/null || true
fi

# Obtener últimos cambios de GitHub
echo -e "${YELLOW}Obteniendo última versión de GitHub...${NC}"
git fetch origin

# Mostrar qué commits se van a aplicar
echo ""
echo -e "${YELLOW}Commits que se aplicarán:${NC}"
git log HEAD..origin/main --oneline --decorate || echo "  (ya está actualizado)"

# Hacer reset hard a origin/main (elimina TODOS los cambios locales)
echo ""
echo -e "${YELLOW}Haciendo reset hard a origin/main...${NC}"
echo -e "${RED}Esto eliminará todos los cambios locales no commiteados${NC}"

git reset --hard origin/main

# Limpiar archivos no rastreados (opcional, comentado por seguridad)
# echo -e "${YELLOW}Limpiando archivos no rastreados...${NC}"
# git clean -fd

echo ""
echo -e "${GREEN}════════════════════════════════════════════════════════${NC}"
echo -e "${GREEN}  ✅ SINCRONIZACIÓN COMPLETADA${NC}"
echo -e "${GREEN}════════════════════════════════════════════════════════${NC}"
echo ""
echo -e "${GREEN}Último commit: $(git log -1 --oneline)${NC}"
echo ""
echo -e "${YELLOW}📋 Archivos protegidos (no se modificaron):${NC}"
git ls-files -v | grep ^S | awk '{print "  • " $2}' || echo "  (ninguno)"
echo ""
echo -e "${GREEN}✅ El hosting está ahora sincronizado con GitHub${NC}"
