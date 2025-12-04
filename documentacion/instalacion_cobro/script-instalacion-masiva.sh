#!/bin/bash

#=============================================================================
# SCRIPT DE INSTALACIÓN MASIVA - SISTEMA DE COBRO MOON POS
# Para hosting reseller con múltiples cuentas
#=============================================================================

# Colores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo -e "${BLUE}═══════════════════════════════════════════════════════════════${NC}"
echo -e "${BLUE}   INSTALACIÓN MASIVA - SISTEMA DE COBRO MOON POS${NC}"
echo -e "${BLUE}═══════════════════════════════════════════════════════════════${NC}"
echo ""

#=============================================================================
# CONFIGURACIÓN
#=============================================================================

# Archivo CSV con la lista de clientes
# Formato: id_cliente,dominio,usuario_cpanel,ruta_public_html
ARCHIVO_CLIENTES="clientes-a-instalar.csv"

# Ruta donde está el paquete de instalación
RUTA_INSTALACION="/home/tuusuario/instalacion_cobro"

# Archivos a copiar
ARCHIVOS_A_COPIAR=(
    "controladores/sistema_cobro.controlador.php"
    "controladores/mercadopago.controlador.php"
    "modelos/sistema_cobro.modelo.php"
    "modelos/mercadopago.modelo.php"
    "vistas/modulos/cabezote-mejorado.php"
    "vistas/modulos/procesar-pago.php"
)

#=============================================================================
# FUNCIONES
#=============================================================================

# Función para mostrar mensajes
msg_info() {
    echo -e "${BLUE}ℹ️  $1${NC}"
}

msg_success() {
    echo -e "${GREEN}✅ $1${NC}"
}

msg_warning() {
    echo -e "${YELLOW}⚠️  $1${NC}"
}

msg_error() {
    echo -e "${RED}❌ $1${NC}"
}

# Función para instalar en una cuenta
instalar_en_cuenta() {
    local id_cliente=$1
    local dominio=$2
    local usuario=$3
    local ruta=$4
    
    echo ""
    echo -e "${BLUE}───────────────────────────────────────${NC}"
    msg_info "Instalando en: $dominio (Cliente ID: $id_cliente)"
    echo -e "${BLUE}───────────────────────────────────────${NC}"
    
    # Verificar que la ruta existe
    if [ ! -d "$ruta" ]; then
        msg_error "La ruta $ruta no existe. Saltando..."
        return 1
    fi
    
    # Backup de archivos existentes
    msg_info "Creando backup..."
    if [ -f "$ruta/vistas/modulos/cabezote-mejorado.php" ]; then
        cp "$ruta/vistas/modulos/cabezote-mejorado.php" "$ruta/vistas/modulos/cabezote-mejorado.php.backup.$(date +%Y%m%d)"
    fi
    
    # Copiar archivos
    msg_info "Copiando archivos del sistema de cobro..."
    
    # Controladores
    cp "$RUTA_INSTALACION/archivos/controladores/sistema_cobro.controlador.php" "$ruta/controladores/" 2>/dev/null
    cp "$RUTA_INSTALACION/archivos/controladores/mercadopago.controlador.php" "$ruta/controladores/" 2>/dev/null
    
    # Modelos  
    cp "$RUTA_INSTALACION/archivos/modelos/sistema_cobro.modelo.php" "$ruta/modelos/" 2>/dev/null
    cp "$RUTA_INSTALACION/archivos/modelos/mercadopago.modelo.php" "$ruta/modelos/" 2>/dev/null
    
    # Vistas
    cp "$RUTA_INSTALACION/archivos/vistas/modulos/cabezote-mejorado.php" "$ruta/vistas/modulos/" 2>/dev/null
    cp "$RUTA_INSTALACION/archivos/vistas/modulos/procesar-pago.php" "$ruta/vistas/modulos/" 2>/dev/null
    
    # Actualizar el ID del cliente en cabezote-mejorado.php
    msg_info "Configurando ID del cliente ($id_cliente)..."
    sed -i "s/isset(\$_SERVER\['MOON_CLIENTE_ID'\]) ? intval(\$_SERVER\['MOON_CLIENTE_ID'\]) : 7/$id_cliente/g" "$ruta/vistas/modulos/cabezote-mejorado.php"
    
    # Verificar que index.php tenga los requires necesarios
    msg_info "Verificando index.php..."
    if ! grep -q "sistema_cobro.controlador.php" "$ruta/index.php"; then
        msg_warning "Falta require de sistema_cobro en index.php"
        echo "Agrega estas líneas al index.php:"
        echo "require_once 'controladores/sistema_cobro.controlador.php';"
        echo "require_once 'modelos/sistema_cobro.modelo.php';"
        echo "require_once 'controladores/mercadopago.controlador.php';"
        echo "require_once 'modelos/mercadopago.modelo.php';"
    fi
    
    # Verificar que plantilla.php incluya cabezote-mejorado
    msg_info "Verificando plantilla.php..."
    if ! grep -q "cabezote-mejorado.php" "$ruta/vistas/plantilla.php"; then
        msg_warning "plantilla.php no incluye cabezote-mejorado.php"
        msg_info "Cambia el include de cabezote.php a cabezote-mejorado.php"
    fi
    
    # Ajustar permisos
    msg_info "Ajustando permisos..."
    chown -R $usuario:$usuario "$ruta/controladores/"
    chown -R $usuario:$usuario "$ruta/modelos/"
    chown -R $usuario:$usuario "$ruta/vistas/modulos/"
    
    msg_success "Instalación completada para $dominio"
    msg_info "Cliente ID configurado: $id_cliente"
    msg_info "Verificar en: https://$dominio"
    
    return 0
}

#=============================================================================
# SCRIPT PRINCIPAL
#=============================================================================

# Verificar que existe el archivo de clientes
if [ ! -f "$ARCHIVO_CLIENTES" ]; then
    msg_error "No se encontró el archivo $ARCHIVO_CLIENTES"
    echo ""
    echo "Crea un archivo CSV con este formato:"
    echo "id_cliente,dominio,usuario_cpanel,ruta_public_html"
    echo "14,amarello.posmoon.com.ar,amarello,/home/amarello/public_html"
    echo "7,demo.posmoon.com.ar,demo,/home/demo/public_html"
    exit 1
fi

# Contador
total=0
exitosas=0
fallidas=0

# Leer archivo CSV y procesar cada línea
while IFS=',' read -r id_cliente dominio usuario ruta; do
    # Saltar la primera línea (encabezados)
    if [ "$id_cliente" == "id_cliente" ]; then
        continue
    fi
    
    ((total++))
    
    if instalar_en_cuenta "$id_cliente" "$dominio" "$usuario" "$ruta"; then
        ((exitosas++))
    else
        ((fallidas++))
    fi
    
    # Esperar un poco entre instalaciones
    sleep 1
    
done < "$ARCHIVO_CLIENTES"

# Resumen final
echo ""
echo -e "${BLUE}═══════════════════════════════════════════════════════════════${NC}"
echo -e "${BLUE}   RESUMEN DE INSTALACIÓN${NC}"
echo -e "${BLUE}═══════════════════════════════════════════════════════════════${NC}"
echo ""
echo "Total de cuentas procesadas: $total"
echo -e "${GREEN}Instalaciones exitosas: $exitosas${NC}"
if [ $fallidas -gt 0 ]; then
    echo -e "${RED}Instalaciones fallidas: $fallidas${NC}"
fi
echo ""
echo -e "${YELLOW}⚠️  IMPORTANTE:${NC}"
echo "1. Verificar cada cuenta manualmente"
echo "2. Probar que el modal de pago se muestra"
echo "3. Revisar los logs de error de cada cuenta"
echo ""
msg_success "Instalación masiva completada"


