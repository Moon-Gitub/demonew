# 🛠️ Scripts de Ayuda y Ejemplos

## Scripts Útiles para Implementación

---

## 1. Archivo .env.example

Crear este archivo en la raíz del proyecto:

```env
# =================================
# CONFIGURACIÓN DE LA APLICACIÓN
# =================================
# IMPORTANTE: Copiar como .env y configurar con valores reales
# NO SUBIR .env A GIT

# Entorno
APP_NAME="ERP/POS Moon Desarrollos"
APP_ENV=production
APP_DEBUG=false
APP_URL=http://localhost
APP_TIMEZONE=America/Argentina/Mendoza

# Base de datos
DB_HOST=localhost
DB_PORT=3306
DB_NAME=demo_db
DB_USER=demo_user
DB_PASS=TU_CONTRASEÑA_AQUI
DB_CHARSET=utf8mb4

# Seguridad
APP_KEY=GENERAR_KEY_ALEATORIA_32_CARACTERES
SESSION_LIFETIME=7200

# Uploads
UPLOAD_MAX_SIZE=5242880
UPLOAD_PATH=vistas/img/usuarios/

# Logs
LOG_LEVEL=warning
```

---

## 2. Script de Backup

**Archivo**: `scripts/backup.sh`

```bash
#!/bin/bash

# Variables
PROJECT_DIR="/home/cluna/Documentos/Moon-Desarrollos/public_html"
BACKUP_DIR="/home/cluna/backups"
DATE=$(date +"%Y%m%d_%H%M%S")
DB_NAME="demo_db"
DB_USER="demo_user"
DB_PASS="aK4UWccl2ceg"

# Crear directorios
mkdir -p "$BACKUP_DIR/db"
mkdir -p "$BACKUP_DIR/code"

# Backup de base de datos
echo "Respaldando base de datos..."
mysqldump -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" | gzip > "$BACKUP_DIR/db/${DB_NAME}_${DATE}.sql.gz"

# Backup de código
echo "Respaldando código..."
tar -czf "$BACKUP_DIR/code/code_${DATE}.tar.gz" -C "$PROJECT_DIR" .

echo "Backup completado: $DATE"
echo "BD: $BACKUP_DIR/db/${DB_NAME}_${DATE}.sql.gz"
echo "Código: $BACKUP_DIR/code/code_${DATE}.tar.gz"
```

**Hacer ejecutable:**
```bash
chmod +x scripts/backup.sh
```

**Ejecutar:**
```bash
./scripts/backup.sh
```

---

## 3. Script de Instalación de Dependencias

**Archivo**: `scripts/install.sh`

```bash
#!/bin/bash

echo "Instalando dependencias del proyecto..."

# Verificar PHP
if ! command -v php &> /dev/null; then
    echo "Error: PHP no está instalado"
    exit 1
fi

echo "PHP version: $(php -v | head -n 1)"

# Verificar Composer
if ! command -v composer &> /dev/null; then
    echo "Instalando Composer..."
    php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
    php composer-setup.php
    php -r "unlink('composer-setup.php');"
    sudo mv composer.phar /usr/local/bin/composer
fi

echo "Composer version: $(composer --version)"

# Instalar dependencias PHP
echo "Instalando dependencias PHP..."
composer install

# Verificar npm
if ! command -v npm &> /dev/null; then
    echo "Error: npm no está instalado"
    echo "Por favor instalar Node.js y npm"
    exit 1
fi

echo "npm version: $(npm -v)"

# Instalar dependencias npm (opcional)
# npm install

# Crear directorios necesarios
mkdir -p logs
mkdir -p storage/cache

# Permisos
chmod -R 755 logs
chmod -R 755 storage

# Copiar .env si no existe
if [ ! -f .env ]; then
    if [ -f .env.example ]; then
        cp .env.example .env
        echo "Archivo .env creado. Por favor configurarlo."
    fi
fi

echo "Instalación completada!"
```

---

## 4. Script de Tests

**Archivo**: `scripts/test.sh`

```bash
#!/bin/bash

echo "Ejecutando tests..."

# Tests PHP
if [ -f "vendor/bin/phpunit" ]; then
    echo "Ejecutando PHPUnit..."
    vendor/bin/phpunit
else
    echo "PHPUnit no instalado"
fi

# Verificar sintaxis PHP
echo "Verificando sintaxis PHP..."
find src/ -name "*.php" -exec php -l {} \; | grep -v "No syntax errors"

echo "Tests completados!"
```

---

## 5. Script de Deploy

**Archivo**: `scripts/deploy.sh`

```bash
#!/bin/bash

echo "Iniciando deploy..."

# Backup antes de deploy
echo "Creando backup..."
./scripts/backup.sh

# Pull del código
echo "Actualizando código..."
git pull origin main

# Instalar dependencias
echo "Actualizando dependencias..."
composer install --no-dev --optimize-autoloader

# Limpiar caché
echo "Limpiando caché..."
rm -rf storage/cache/*

# Ejecutar migraciones (si las hay)
# php scripts/migrate.php

# Verificar permisos
echo "Verificando permisos..."
chmod -R 755 logs
chmod -R 755 storage

echo "Deploy completado!"
```

---

## 6. Verificar Estado del Sistema

**Archivo**: `scripts/check-system.php`

```php
<?php
/**
 * Verificar estado del sistema
 */

echo "=================================\n";
echo "VERIFICACIÓN DEL SISTEMA\n";
echo "=================================\n\n";

// PHP Version
echo "PHP Version: " . PHP_VERSION . "\n";

// Extensiones requeridas
$required_extensions = ['pdo', 'pdo_mysql', 'mbstring', 'json', 'gd'];
echo "\nExtensiones PHP:\n";
foreach ($required_extensions as $ext) {
    $status = extension_loaded($ext) ? '✓' : '✗';
    echo "  $status $ext\n";
}

// Permisos de directorios
echo "\nPermisos de directorios:\n";
$directories = ['logs', 'storage/cache', 'vistas/img/usuarios'];
foreach ($directories as $dir) {
    if (file_exists($dir)) {
        $perms = substr(sprintf('%o', fileperms($dir)), -4);
        echo "  ✓ $dir ($perms)\n";
    } else {
        echo "  ✗ $dir (no existe)\n";
    }
}

// Archivo .env
echo "\nConfiguración:\n";
if (file_exists('.env')) {
    echo "  ✓ Archivo .env existe\n";
    
    // Verificar variables críticas
    $required_vars = ['DB_HOST', 'DB_NAME', 'DB_USER', 'DB_PASS'];
    foreach ($required_vars as $var) {
        if (getenv($var)) {
            echo "  ✓ $var configurado\n";
        } else {
            echo "  ✗ $var NO configurado\n";
        }
    }
} else {
    echo "  ✗ Archivo .env NO existe\n";
}

// Conexión a BD
echo "\nBase de Datos:\n";
try {
    require_once 'modelos/conexion.php';
    $conn = Conexion::conectar();
    echo "  ✓ Conexión exitosa\n";
    
    // Verificar tablas
    $stmt = $conn->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "  ✓ Tablas encontradas: " . count($tables) . "\n";
    
} catch (Exception $e) {
    echo "  ✗ Error de conexión: " . $e->getMessage() . "\n";
}

// Composer
echo "\nDependencias:\n";
if (file_exists('vendor/autoload.php')) {
    echo "  ✓ Composer autoload existe\n";
} else {
    echo "  ✗ Composer autoload NO existe (ejecutar: composer install)\n";
}

echo "\n=================================\n";
echo "Verificación completada\n";
echo "=================================\n";
```

**Ejecutar:**
```bash
php scripts/check-system.php
```

---

## 7. Generar Clave de Aplicación

**Archivo**: `scripts/generate-key.php`

```php
<?php
/**
 * Generar clave aleatoria para APP_KEY
 */

function generateRandomKey($length = 32) {
    return bin2hex(random_bytes($length / 2));
}

$key = generateRandomKey(32);

echo "Clave generada:\n";
echo "===============\n";
echo $key . "\n\n";

echo "Agregar a .env:\n";
echo "APP_KEY=$key\n";
```

**Ejecutar:**
```bash
php scripts/generate-key.php
```

---

## 8. Limpiar Caché

**Archivo**: `scripts/clear-cache.php`

```php
<?php
/**
 * Limpiar caché del sistema
 */

$cacheDir = __DIR__ . '/../storage/cache/';

echo "Limpiando caché...\n";

if (file_exists($cacheDir)) {
    $files = glob($cacheDir . '*');
    $count = 0;
    
    foreach ($files as $file) {
        if (is_file($file)) {
            unlink($file);
            $count++;
        }
    }
    
    echo "Archivos eliminados: $count\n";
} else {
    echo "Directorio de caché no existe\n";
}

echo "Caché limpiado!\n";
```

**Ejecutar:**
```bash
php scripts/clear-cache.php
```

---

## 9. .gitignore Completo

**Archivo**: `.gitignore`

```gitignore
# Entorno
.env
.env.backup
.env.production

# Composer
/vendor/
composer.lock

# NPM
/node_modules/
package-lock.json
npm-debug.log
yarn.lock

# Caché
/storage/cache/*
!/storage/cache/.gitkeep
/logs/*
!/logs/.gitkeep

# Archivos subidos por usuarios
/vistas/img/usuarios/*
!/vistas/img/usuarios/.gitkeep
/vistas/img/productos/*
!/vistas/img/productos/.gitkeep

# IDE
.vscode/
.idea/
*.swp
*.swo
*~
.DS_Store

# Sistema operativo
Thumbs.db
.DS_Store

# Build
/dist/
/build/
/public/build/

# Tests
/coverage/
.phpunit.result.cache

# Backups
*.sql
*.sql.gz
*.backup
*.bak

# Temporales
*.log
*.tmp
*.temp
```

---

## 10. Comando Rápido de Instalación

Ejecutar en orden:

```bash
# 1. Crear backup
mkdir -p /home/cluna/backups
mysqldump -u demo_user -p demo_db > /home/cluna/backups/backup_$(date +%Y%m%d).sql

# 2. Clonar/actualizar código
cd /home/cluna/Documentos/Moon-Desarrollos/public_html
git init  # si no existe repo
git add .
git commit -m "Estado inicial antes de mejoras"

# 3. Crear .env
cp .env.example .env
nano .env  # editar con valores reales

# 4. Instalar Composer (si no está instalado)
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# 5. Instalar dependencias
composer require vlucas/phpdotenv

# 6. Crear directorios necesarios
mkdir -p logs
mkdir -p storage/cache
chmod -R 755 logs storage

# 7. Verificar instalación
php scripts/check-system.php
```

---

## 11. Crontab para Tareas Automáticas

```bash
# Editar crontab
crontab -e
```

Agregar:
```cron
# Backup diario a las 2 AM
0 2 * * * /home/cluna/Documentos/Moon-Desarrollos/public_html/scripts/backup.sh

# Limpiar caché semanalmente (domingos a las 3 AM)
0 3 * * 0 php /home/cluna/Documentos/Moon-Desarrollos/public_html/scripts/clear-cache.php

# Limpiar logs antiguos (mensualmente)
0 4 1 * * find /home/cluna/Documentos/Moon-Desarrollos/public_html/logs -name "*.log" -mtime +30 -delete
```

---

## 12. Comandos Git Útiles

```bash
# Crear repositorio
git init
git add .
git commit -m "Initial commit"

# Crear branches
git checkout -b develop
git checkout -b staging

# Workflow típico
git checkout develop
git pull origin develop
git checkout -b feature/nueva-funcionalidad
# ... hacer cambios ...
git add .
git commit -m "feat: agregar nueva funcionalidad"
git push origin feature/nueva-funcionalidad

# Ver diferencias
git diff
git status

# Ver historial
git log --oneline --graph

# Crear tag de versión
git tag -a v1.0.0 -m "Versión 1.0.0 - Seguridad implementada"
git push origin v1.0.0
```

---

## 13. Verificar Seguridad Básica

```bash
# Verificar que .env no esté en Git
git ls-files | grep .env

# Buscar contraseñas hardcodeadas
grep -r "password.*=" --include="*.php" src/

# Verificar permisos
find . -type f -name "*.php" -perm 777

# Buscar archivos sospechosos
find . -name "*.php" -exec grep -l "eval" {} \;
find . -name "*.php" -exec grep -l "base64_decode" {} \;
```

---

## 14. Restaurar Backup

```bash
# Restaurar base de datos
mysql -u demo_user -p demo_db < /home/cluna/backups/backup_20241008.sql

# Restaurar código
cd /home/cluna/Documentos/Moon-Desarrollos
rm -rf public_html
tar -xzf /home/cluna/backups/code_20241008.tar.gz
```

---

## 15. Monitoreo Básico

**Archivo**: `scripts/monitor.sh`

```bash
#!/bin/bash

# Verificar espacio en disco
df -h | grep -v tmpfs

# Verificar uso de memoria
free -h

# Verificar procesos PHP
ps aux | grep php

# Verificar errores recientes en logs
if [ -f "logs/app.log" ]; then
    echo "Últimos 10 errores:"
    grep "ERROR" logs/app.log | tail -10
fi

# Verificar conexiones MySQL
mysql -u demo_user -p -e "SHOW PROCESSLIST;"
```

---

Usa estos scripts para facilitar la implementación y mantenimiento del sistema! 🚀

