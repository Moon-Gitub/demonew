#!/bin/bash

################################################################################
# SCRIPT DE INSTALACIÓN Y CONFIGURACIÓN - SISTEMA POS DEMONEW
################################################################################
#
# Este script automatiza la instalación y configuración completa del sistema
# de punto de venta (POS) en Ubuntu.
#
# COMPONENTES QUE INSTALA:
# - Apache Web Server
# - MySQL/MariaDB Database Server
# - PHP 8.1+ con extensiones necesarias
# - Composer (gestor de dependencias PHP)
# - Dependencias del proyecto (PhpSpreadsheet, TCPDF, MercadoPago)
#
# REQUISITOS:
# - Ubuntu 20.04 o superior
# - Permisos de sudo
# - Conexión a Internet
#
# USO:
#   chmod +x setup.sh
#   ./setup.sh
#
################################################################################

# Colores para mensajes en terminal
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # Sin color

# Variables de configuración
PROJECT_DIR=$(pwd)
DB_NAME="demo_db"
DB_USER="demonew_user"
DB_PASS="demonew_pass_2025"
APACHE_CONF="/etc/apache2/sites-available/demonew.conf"
PROJECT_NAME="demonew"

################################################################################
# FUNCIONES AUXILIARES
################################################################################

# Función para imprimir mensajes con formato
print_message() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

print_success() {
    echo -e "${GREEN}[✓]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[!]${NC} $1"
}

print_error() {
    echo -e "${RED}[✗]${NC} $1"
}

# Función para verificar si un comando se ejecutó correctamente
check_success() {
    if [ $? -eq 0 ]; then
        print_success "$1"
    else
        print_error "$2"
        exit 1
    fi
}

################################################################################
# PASO 1: VERIFICACIONES INICIALES
################################################################################

print_message "======================================================================"
print_message "  INSTALACIÓN DEL SISTEMA POS DEMONEW"
print_message "======================================================================"
echo ""

# Verificar que el script se ejecuta como root o con sudo
if [ "$EUID" -ne 0 ]; then
    print_error "Este script debe ejecutarse con permisos de sudo"
    echo "Por favor, ejecuta: sudo ./setup.sh"
    exit 1
fi

print_success "Verificación de permisos: OK"
echo ""

################################################################################
# PASO 2: ACTUALIZAR SISTEMA
################################################################################

print_message "PASO 1/10: Actualizando el sistema operativo..."
echo ""

# Actualizar lista de paquetes disponibles
apt update -qq
check_success "Lista de paquetes actualizada" "Error al actualizar lista de paquetes"

# Actualizar paquetes instalados (opcional, comentado para hacerlo más rápido)
# apt upgrade -y
# check_success "Sistema actualizado" "Error al actualizar el sistema"

echo ""

################################################################################
# PASO 3: INSTALAR APACHE WEB SERVER
################################################################################

print_message "PASO 2/10: Instalando Apache Web Server..."
echo ""

# Instalar Apache2
apt install -y apache2
check_success "Apache instalado correctamente" "Error al instalar Apache"

# Habilitar módulo rewrite de Apache (necesario para URLs amigables)
a2enmod rewrite
check_success "Módulo rewrite habilitado" "Error al habilitar módulo rewrite"

# Habilitar módulo headers
a2enmod headers

echo ""

################################################################################
# PASO 4: INSTALAR MYSQL/MARIADB
################################################################################

print_message "PASO 3/10: Instalando MySQL Server..."
echo ""

# Instalar MySQL Server
apt install -y mysql-server
check_success "MySQL instalado correctamente" "Error al instalar MySQL"

# Iniciar servicio MySQL
systemctl start mysql
systemctl enable mysql
check_success "Servicio MySQL iniciado y habilitado" "Error al iniciar MySQL"

echo ""

################################################################################
# PASO 5: INSTALAR PHP Y EXTENSIONES
################################################################################

print_message "PASO 4/10: Instalando PHP y extensiones necesarias..."
echo ""

# Instalar PHP y extensiones requeridas por el sistema
apt install -y php php-cli php-fpm php-mysql php-zip php-gd php-mbstring \
    php-curl php-xml php-pear php-bcmath php-intl php-soap php-json \
    libapache2-mod-php
check_success "PHP y extensiones instaladas" "Error al instalar PHP"

# Verificar versión de PHP instalada
PHP_VERSION=$(php -v | head -n 1)
print_success "Versión de PHP instalada: $PHP_VERSION"

echo ""

################################################################################
# PASO 6: INSTALAR COMPOSER
################################################################################

print_message "PASO 5/10: Instalando Composer (gestor de dependencias PHP)..."
echo ""

# Descargar instalador de Composer
cd /tmp
curl -sS https://getcomposer.org/installer -o composer-setup.php
check_success "Instalador de Composer descargado" "Error al descargar Composer"

# Instalar Composer globalmente
php composer-setup.php --install-dir=/usr/local/bin --filename=composer
check_success "Composer instalado globalmente" "Error al instalar Composer"

# Limpiar archivo de instalación
rm composer-setup.php

# Verificar instalación de Composer
COMPOSER_VERSION=$(composer --version)
print_success "$COMPOSER_VERSION instalado correctamente"

cd "$PROJECT_DIR"

echo ""

################################################################################
# PASO 7: INSTALAR DEPENDENCIAS DEL PROYECTO
################################################################################

print_message "PASO 6/10: Instalando dependencias del proyecto con Composer..."
echo ""

# Verificar que existe composer.json
if [ ! -f "extensiones/composer.json" ]; then
    print_warning "No se encontró extensiones/composer.json"
else
    # Instalar dependencias PHP del proyecto
    cd extensiones
    composer install --no-interaction --prefer-dist
    check_success "Dependencias instaladas (PhpSpreadsheet, TCPDF, MercadoPago)" "Error al instalar dependencias"
    cd ..
fi

echo ""

################################################################################
# PASO 8: CONFIGURAR BASE DE DATOS
################################################################################

print_message "PASO 7/10: Configurando base de datos MySQL..."
echo ""

# Crear base de datos y usuario
print_message "Creando base de datos '$DB_NAME' y usuario '$DB_USER'..."

mysql -u root <<MYSQL_SCRIPT
-- Crear base de datos
CREATE DATABASE IF NOT EXISTS $DB_NAME CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;

-- Crear usuario y otorgar permisos
CREATE USER IF NOT EXISTS '$DB_USER'@'localhost' IDENTIFIED BY '$DB_PASS';
GRANT ALL PRIVILEGES ON $DB_NAME.* TO '$DB_USER'@'localhost';
FLUSH PRIVILEGES;
MYSQL_SCRIPT

check_success "Base de datos y usuario creados" "Error al crear base de datos"

# Importar dump SQL si existe
if [ -f "demo_db.sql" ]; then
    print_message "Importando estructura y datos desde demo_db.sql..."
    mysql -u root $DB_NAME < demo_db.sql
    check_success "Base de datos importada correctamente" "Error al importar base de datos"
else
    print_warning "No se encontró demo_db.sql - la base de datos está vacía"
fi

echo ""
print_success "Credenciales de la base de datos:"
echo "  - Base de datos: $DB_NAME"
echo "  - Usuario: $DB_USER"
echo "  - Contraseña: $DB_PASS"
echo "  - Host: localhost"
echo ""

################################################################################
# PASO 9: CONFIGURAR ARCHIVO DE CONEXIÓN (SI EXISTE)
################################################################################

print_message "PASO 8/10: Buscando archivos de configuración de BD..."
echo ""

# Buscar archivos de configuración comunes
CONFIG_FILES=$(find . -maxdepth 2 -type f \( -name "config.php" -o -name "conexion.php" -o -name "database.php" \) 2>/dev/null)

if [ -n "$CONFIG_FILES" ]; then
    print_warning "Se encontraron archivos de configuración:"
    echo "$CONFIG_FILES"
    echo ""
    print_warning "IMPORTANTE: Debes actualizar manualmente las credenciales de BD en estos archivos:"
    echo "  - DB_NAME: $DB_NAME"
    echo "  - DB_USER: $DB_USER"
    echo "  - DB_PASS: $DB_PASS"
    echo "  - DB_HOST: localhost"
else
    print_message "No se encontraron archivos de configuración automáticamente"
    print_warning "Verifica y crea la conexión a BD según la estructura del proyecto"
fi

echo ""

################################################################################
# PASO 10: CONFIGURAR APACHE VIRTUAL HOST
################################################################################

print_message "PASO 9/10: Configurando Apache Virtual Host..."
echo ""

# Crear archivo de configuración de Apache para el proyecto
cat > $APACHE_CONF <<EOF
<VirtualHost *:80>
    # Configuración del servidor
    ServerName localhost
    ServerAlias www.localhost
    DocumentRoot $PROJECT_DIR

    # Configuración del directorio del proyecto
    <Directory $PROJECT_DIR>
        Options Indexes FollowSymLinks MultiViews
        AllowOverride All
        Require all granted

        # Habilitar archivos .htaccess
        Order allow,deny
        allow from all
    </Directory>

    # Logs de Apache
    ErrorLog \${APACHE_LOG_DIR}/${PROJECT_NAME}_error.log
    CustomLog \${APACHE_LOG_DIR}/${PROJECT_NAME}_access.log combined

    # Configuración de PHP
    <FilesMatch \.php$>
        SetHandler application/x-httpd-php
    </FilesMatch>
</VirtualHost>
EOF

check_success "Archivo de configuración de Apache creado" "Error al crear configuración"

# Deshabilitar sitio por defecto de Apache
a2dissite 000-default.conf 2>/dev/null

# Habilitar el nuevo sitio
a2ensite ${PROJECT_NAME}.conf
check_success "Sitio habilitado en Apache" "Error al habilitar sitio"

echo ""

################################################################################
# PASO 11: CONFIGURAR PERMISOS DE ARCHIVOS
################################################################################

print_message "PASO 10/10: Configurando permisos de archivos y directorios..."
echo ""

# Establecer propietario correcto (usuario de Apache)
chown -R www-data:www-data $PROJECT_DIR
check_success "Propietario establecido (www-data)" "Error al cambiar propietario"

# Establecer permisos correctos
# Directorios: 755 (rwxr-xr-x)
# Archivos: 644 (rw-r--r--)
find $PROJECT_DIR -type d -exec chmod 755 {} \;
find $PROJECT_DIR -type f -exec chmod 644 {} \;
check_success "Permisos de archivos configurados" "Error al configurar permisos"

# Dar permisos de escritura a directorios que lo necesiten
WRITABLE_DIRS=("uploads" "temp" "cache" "logs")
for dir in "${WRITABLE_DIRS[@]}"; do
    if [ -d "$PROJECT_DIR/$dir" ]; then
        chmod -R 775 "$PROJECT_DIR/$dir"
        print_success "Permisos de escritura establecidos en: $dir"
    fi
done

echo ""

################################################################################
# PASO 12: REINICIAR SERVICIOS
################################################################################

print_message "Reiniciando servicios..."
echo ""

# Reiniciar Apache para aplicar cambios
systemctl restart apache2
check_success "Apache reiniciado" "Error al reiniciar Apache"

# Verificar estado de Apache
systemctl is-active --quiet apache2
check_success "Apache está activo y funcionando" "Apache no está funcionando correctamente"

# Verificar estado de MySQL
systemctl is-active --quiet mysql
check_success "MySQL está activo y funcionando" "MySQL no está funcionando correctamente"

echo ""

################################################################################
# INSTALACIÓN COMPLETADA
################################################################################

print_message "======================================================================"
print_success "  ¡INSTALACIÓN COMPLETADA CON ÉXITO!"
print_message "======================================================================"
echo ""

print_message "RESUMEN DE LA INSTALACIÓN:"
echo ""
echo "📁 Directorio del proyecto: $PROJECT_DIR"
echo "🌐 URL de acceso: http://localhost"
echo "🗄️  Base de datos: $DB_NAME"
echo "👤 Usuario BD: $DB_USER"
echo "🔑 Contraseña BD: $DB_PASS"
echo ""

print_message "SERVICIOS INSTALADOS:"
echo "  ✓ Apache Web Server"
echo "  ✓ MySQL Database Server"
echo "  ✓ PHP $(php -r 'echo PHP_VERSION;')"
echo "  ✓ Composer $(composer --version --no-ansi | cut -d' ' -f3)"
echo ""

print_message "PRÓXIMOS PASOS:"
echo ""
echo "1. Verifica la configuración de conexión a la base de datos en los archivos PHP"
echo "2. Accede a http://localhost en tu navegador"
echo "3. Si hay errores, revisa los logs de Apache:"
echo "   sudo tail -f /var/log/apache2/${PROJECT_NAME}_error.log"
echo ""

print_warning "IMPORTANTE - SEGURIDAD:"
echo "  - Cambia las credenciales de la base de datos en producción"
echo "  - Configura un firewall (ufw) si es necesario"
echo "  - Para acceso externo, configura DNS o /etc/hosts"
echo ""

print_message "COMANDOS ÚTILES:"
echo ""
echo "  # Reiniciar Apache:"
echo "  sudo systemctl restart apache2"
echo ""
echo "  # Ver logs de errores:"
echo "  sudo tail -f /var/log/apache2/${PROJECT_NAME}_error.log"
echo ""
echo "  # Acceder a MySQL:"
echo "  mysql -u $DB_USER -p$DB_PASS $DB_NAME"
echo ""
echo "  # Verificar estado de servicios:"
echo "  sudo systemctl status apache2"
echo "  sudo systemctl status mysql"
echo ""

print_success "¡El sistema está listo para usar!"
echo ""

################################################################################
# FIN DEL SCRIPT
################################################################################
