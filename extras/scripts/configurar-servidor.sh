#!/bin/bash
# ============================================
# Script de Configuración para Servidor A
# ============================================
# Este script:
# 1. Protege archivos locales del servidor
# 2. Extrae credenciales de BD de modelos/conexion.php
# 3. Genera archivo .env con configuración completa
# ============================================

set -e

# Colores
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

print_info() { echo -e "${BLUE}ℹ️  $1${NC}"; }
print_success() { echo -e "${GREEN}✅ $1${NC}"; }
print_warning() { echo -e "${YELLOW}⚠️  $1${NC}"; }
print_error() { echo -e "${RED}❌ $1${NC}"; }

SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
cd "$SCRIPT_DIR"

echo ""
echo "════════════════════════════════════════════════════════"
echo "  🔧 CONFIGURACIÓN DE SERVIDOR A"
echo "════════════════════════════════════════════════════════"
echo ""

# ============================================
# PASO 1: Extraer credenciales de BD
# ============================================
print_info "Extrayendo credenciales de base de datos..."

CONEXION_FILE="modelos/conexion.php"

if [ ! -f "$CONEXION_FILE" ]; then
    print_error "No se encontró: $CONEXION_FILE"
    exit 1
fi

# Buscar patrón: new PDO("mysql:host=...;dbname=...","...","...")
PDO_LINE=$(grep 'new PDO' "$CONEXION_FILE" | grep 'mysql:host' | head -1)

DB_HOST="localhost"
DB_NAME=""
DB_USER=""
DB_PASS=""

if [ ! -z "$PDO_LINE" ]; then
    # Extraer host
    if echo "$PDO_LINE" | grep -q 'mysql:host='; then
        DB_HOST=$(echo "$PDO_LINE" | sed -n 's/.*mysql:host=\([^;]*\).*/\1/p' | tr -d ' ' | tr -d '"')
    fi
    
    # Extraer dbname
    if echo "$PDO_LINE" | grep -q 'dbname='; then
        DB_NAME=$(echo "$PDO_LINE" | sed -n 's/.*dbname=\([^"]*\).*/\1/p' | tr -d ' ')
    fi
    
    # Extraer usuario y contraseña (están entre comillas después de mysql:host)
    # Buscar todas las cadenas entre comillas dobles
    QUOTES_ARRAY=($(echo "$PDO_LINE" | grep -oP '"[^"]*"' | tr -d '"'))
    
    # El usuario suele ser el segundo elemento, la contraseña el tercero
    if [ ${#QUOTES_ARRAY[@]} -ge 2 ]; then
        DB_USER="${QUOTES_ARRAY[1]}"
    fi
    if [ ${#QUOTES_ARRAY[@]} -ge 3 ]; then
        DB_PASS="${QUOTES_ARRAY[2]}"
    fi
    
    # Si no funcionó, intentar método alternativo más agresivo
    if [ -z "$DB_NAME" ] || [ -z "$DB_USER" ] || [ -z "$DB_PASS" ]; then
        # Buscar línea completa y extraer manualmente
        # Patrón: new PDO("mysql:host=HOST;dbname=DB","USER","PASS")
        TEMP_DB=$(echo "$PDO_LINE" | sed -n 's/.*dbname=\([^";]*\).*/\1/p')
        if [ ! -z "$TEMP_DB" ]; then
            DB_NAME="$TEMP_DB"
        fi
        
        # Extraer usuario (segunda cadena entre comillas)
        TEMP_USER=$(echo "$PDO_LINE" | sed -n 's/.*"\([^"]*\)".*"\([^"]*\)".*/\2/p')
        if [ ! -z "$TEMP_USER" ] && [ "$TEMP_USER" != "$PDO_LINE" ]; then
            DB_USER="$TEMP_USER"
        fi
        
        # Extraer contraseña (tercera cadena entre comillas)
        TEMP_PASS=$(echo "$PDO_LINE" | sed -n 's/.*"\([^"]*\)".*"\([^"]*\)".*"\([^"]*\)".*/\3/p')
        if [ ! -z "$TEMP_PASS" ] && [ "$TEMP_PASS" != "$PDO_LINE" ] && [ "$TEMP_PASS" != "$TEMP_USER" ]; then
            DB_PASS="$TEMP_PASS"
        fi
    fi
fi

# Validar credenciales
if [ -z "$DB_NAME" ] || [ -z "$DB_USER" ] || [ -z "$DB_PASS" ]; then
    print_warning "No se pudieron extraer todas las credenciales automáticamente."
    print_info "Por favor, ingresa las credenciales manualmente:"
    echo ""
    
    read -p "DB_HOST [localhost]: " DB_HOST
    DB_HOST=${DB_HOST:-localhost}
    
    read -p "DB_NAME: " DB_NAME
    if [ -z "$DB_NAME" ]; then
        print_error "DB_NAME es requerido"
        exit 1
    fi
    
    read -p "DB_USER: " DB_USER
    if [ -z "$DB_USER" ]; then
        print_error "DB_USER es requerido"
        exit 1
    fi
    
    read -sp "DB_PASS: " DB_PASS
    echo ""
    if [ -z "$DB_PASS" ]; then
        print_error "DB_PASS es requerido"
        exit 1
    fi
fi

DB_HOST=${DB_HOST:-localhost}
DB_CHARSET="UTF8MB4"

print_success "Credenciales extraídas:"
echo "  DB_HOST: $DB_HOST"
echo "  DB_NAME: $DB_NAME"
echo "  DB_USER: $DB_USER"
echo "  DB_PASS: [OCULTO]"
echo ""

# ============================================
# PASO 2: Proteger archivos del servidor
# ============================================
print_info "Protegiendo archivos locales del servidor..."

ARCHIVOS_PROTEGER=(
    "error_log"
    "ajax/error_log"
    "controladores/facturacion/keys"
    "controladores/facturacion/xml"
)

PROTEGIDOS=0
for archivo in "${ARCHIVOS_PROTEGER[@]}"; do
    if [ -e "$archivo" ]; then
        if [ -d "$archivo" ]; then
            # Es un directorio, proteger todos los archivos dentro
            find "$archivo" -type f 2>/dev/null | while read f; do
                if git update-index --skip-worktree "$f" 2>/dev/null; then
                    ((PROTEGIDOS++)) || true
                fi
            done
            print_success "Directorio protegido: $archivo"
        else
            # Es un archivo
            if git update-index --skip-worktree "$archivo" 2>/dev/null; then
                print_success "Archivo protegido: $archivo"
                ((PROTEGIDOS++))
            else
                print_warning "No se pudo proteger: $archivo (puede no estar en git)"
            fi
        fi
    else
        print_warning "No existe: $archivo"
    fi
done

echo ""

# ============================================
# PASO 3: Generar archivo .env
# ============================================
print_info "Generando archivo .env..."

ENV_FILE=".env"

# Backup del .env existente si existe
if [ -f "$ENV_FILE" ]; then
    BACKUP_FILE=".env.backup.$(date +%Y%m%d_%H%M%S)"
    cp "$ENV_FILE" "$BACKUP_FILE"
    print_success "Backup creado: $BACKUP_FILE"
fi

# Generar nuevo .env
cat > "$ENV_FILE" << EOF
# ==============================================
# BASE DE DATOS LOCAL - SISTEMA POS
# ==============================================
DB_HOST=$DB_HOST
DB_NAME=$DB_NAME
DB_USER=$DB_USER
DB_PASS=$DB_PASS
DB_CHARSET=$DB_CHARSET


# ==============================================
# BASE DE DATOS MOON - SISTEMA DE COBRO
# ==============================================
MOON_DB_HOST=107.161.23.11
MOON_DB_NAME=cobrosposmooncom_db
MOON_DB_USER=cobrosposmooncom_dbuser
MOON_DB_PASS=[Us{ynaJAA_o2A_!

# ==============================================
# MERCADOPAGO - CREDENCIALES DE PRODUCCIÓN
# ==============================================
MP_PUBLIC_KEY=APP_USR-fae622f2-9df5-42ec-a643-5dd5366158c8
MP_ACCESS_TOKEN=APP_USR-7101882075144875-102300-176b1351adb161d81438a5f958bd70f6-1188183100

# ==============================================
# SISTEMA DE COBRO MOON
# ==============================================
MOON_CLIENTE_ID=14

# ==============================================
# CONFIGURACIÓN DE APLICACIÓN
# ==============================================
APP_ENV=production
APP_DEBUG=false

# ============================================
# SEGURIDAD
# ============================================
# Generar una clave aleatoria segura:
# php -r "echo bin2hex(random_bytes(32));"
APP_KEY=8fd875a4c4e5c7e6c37847ab9a64ca858cfe77c08161fe23ae6d7382e1bd8652

# Tiempo de vida de sesión en segundos (7200 = 2 horas)
SESSION_LIFETIME=7200
EOF

# Proteger el .env también
chmod 600 "$ENV_FILE"
git update-index --skip-worktree "$ENV_FILE" 2>/dev/null || true

print_success "Archivo .env generado correctamente"
echo ""

# ============================================
# RESUMEN
# ============================================
echo "════════════════════════════════════════════════════════"
echo "  ✅ CONFIGURACIÓN COMPLETADA"
echo "════════════════════════════════════════════════════════"
echo ""
echo "📋 Resumen:"
echo "  • Archivos protegidos: $PROTEGIDOS"
echo "  • Archivo .env: Generado"
echo "  • Base de datos local: $DB_NAME"
echo ""
echo "💡 Comandos útiles:"
echo "  • Ver archivos protegidos: git ls-files -v | grep ^S"
echo "  • Desproteger archivo: git update-index --no-skip-worktree <archivo>"
echo ""
echo "⚠️  IMPORTANTE:"
echo "  • El archivo .env tiene permisos 600 (solo lectura para propietario)"
echo "  • Los archivos protegidos NO se modificarán al hacer git pull"
echo ""