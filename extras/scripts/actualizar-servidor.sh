#!/bin/bash

# Script para actualizar el servidor desde GitHub de forma segura
# Respeta archivos protegidos (.env, error_log, etc.)

set -e  # Salir si hay errores

# Colores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${YELLOW}════════════════════════════════════════════════════════${NC}"
echo -e "${YELLOW}  ACTUALIZACIÓN DEL SERVIDOR DESDE GITHUB${NC}"
echo -e "${YELLOW}════════════════════════════════════════════════════════${NC}"
echo ""

# Verificar que estamos en un repositorio git
if [ ! -d .git ]; then
    echo -e "${RED}✗ Error: No estás en un repositorio git${NC}"
    exit 1
fi

# Verificar que no hay cambios sin commitear (opcional, comentado para permitir cambios locales)
# if [ -n "$(git status --porcelain)" ]; then
#     echo -e "${YELLOW}⚠️  Hay cambios sin commitear. Guardándolos en stash...${NC}"
#     git stash push -m "Cambios locales antes de pull - $(date)"
# fi

# Eliminar archivo de swap si existe
if [ -f .git/.MERGE_MSG.swp ]; then
    echo -e "${YELLOW}Eliminando archivo de swap de Vim...${NC}"
    rm -f .git/.MERGE_MSG.swp
fi

# Verificar si hay un merge en progreso
if [ -f .git/MERGE_HEAD ]; then
    echo -e "${YELLOW}⚠️  Hay un merge en progreso. Cancelándolo...${NC}"
    git merge --abort 2>/dev/null || true
fi

# Obtener últimos cambios
echo -e "${YELLOW}Obteniendo últimos cambios de GitHub...${NC}"
git fetch origin

# Verificar si hay cambios
COMMITS_NUEVOS=$(git rev-list HEAD..origin/main --count 2>/dev/null || echo "0")
if [ "$COMMITS_NUEVOS" -eq 0 ]; then
    echo -e "${GREEN}✓ Ya estás en la última versión${NC}"
    echo -e "${GREEN}Último commit local: $(git log -1 --oneline)${NC}"
    exit 0
fi

# Mostrar commits nuevos
echo -e "${YELLOW}════════════════════════════════════════════════════════${NC}"
echo -e "${YELLOW}Nuevos commits a aplicar ($COMMITS_NUEVOS):${NC}"
echo -e "${YELLOW}════════════════════════════════════════════════════════${NC}"
git log HEAD..origin/main --oneline --decorate
echo ""

# Confirmar (opcional, comentado para automatización)
# read -p "¿Continuar con la actualización? (s/N): " -n 1 -r
# echo
# if [[ ! $REPLY =~ ^[Ss]$ ]]; then
#     echo -e "${YELLOW}Actualización cancelada${NC}"
#     exit 0
# fi

# Actualizar
echo -e "${YELLOW}Actualizando código...${NC}"
if git pull origin main; then
    echo ""
    echo -e "${GREEN}════════════════════════════════════════════════════════${NC}"
    echo -e "${GREEN}  ✓ ACTUALIZACIÓN COMPLETADA EXITOSAMENTE${NC}"
    echo -e "${GREEN}════════════════════════════════════════════════════════${NC}"
    echo -e "${GREEN}Último commit: $(git log -1 --oneline)${NC}"
    echo ""
    echo -e "${YELLOW}📋 Archivos protegidos (no se modificaron):${NC}"
    git ls-files -v | grep ^S | awk '{print "  • " $2}' || echo "  (ninguno)"
    echo ""
    echo -e "${GREEN}✅ El servidor está actualizado${NC}"
else
    echo ""
    echo -e "${RED}════════════════════════════════════════════════════════${NC}"
    echo -e "${RED}  ✗ ERROR AL ACTUALIZAR${NC}"
    echo -e "${RED}════════════════════════════════════════════════════════${NC}"
    echo ""
    echo -e "${YELLOW}Posibles causas:${NC}"
    echo "  1. Conflictos de merge - ejecuta: git status"
    echo "  2. Cambios locales - revisa: git diff"
    echo "  3. Problemas de permisos - verifica permisos de archivos"
    echo ""
    echo -e "${YELLOW}Para resolver conflictos:${NC}"
    echo "  git status                    # Ver archivos en conflicto"
    echo "  git merge --abort             # Cancelar merge"
    echo "  # O resolver conflictos manualmente y luego:"
    echo "  git add ."
    echo "  git commit"
    exit 1
fi
