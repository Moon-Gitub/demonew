# 🚀 Guía de Despliegue en Servidor

## 📋 Problemas Resueltos

### ✅ **Problema 1: Versión de PHP incompatible**
- **Error:** `Composer detected issues in your platform: Your Composer dependencies require a PHP version ">= 8.2.0"`
- **Solución:** Ajustado `composer.json` para usar PHPSpreadsheet 1.29 (compatible con PHP 7.4+)

### ✅ **Problema 2: Clase ControladorMercadoPago no encontrada**
- **Error:** `Class "ControladorMercadoPago" not found in cabezote.php:58`
- **Solución:** Agregados `require_once` en `index.php`

---

## 🔧 Pasos para Actualizar el Servidor

### **Paso 1: Conectar al Servidor**

```bash
ssh usuario@newmoon.posmoon.com.ar
# O usar cPanel File Manager / FTP
```

---

### **Paso 2: Hacer Backup del Servidor**

⚠️ **IMPORTANTE: Siempre hacer backup antes de actualizar**

```bash
cd /home/newmoon/public_html
tar -czf ../backup_$(date +%Y%m%d_%H%M%S).tar.gz .

# Verificar backup
ls -lh ../backup*.tar.gz
```

---

### **Paso 3: Subir Código Actualizado**

#### **Opción A: Git Pull (Recomendado si tienes Git en servidor)**

```bash
cd /home/newmoon/public_html

# Si NO está inicializado Git:
git init
git remote add origin https://github.com/claudioLuna/newposmoon.git
git fetch origin
git checkout main

# Si ya está inicializado:
git pull origin main
```

#### **Opción B: FTP/SFTP Manual**

Subir estos archivos actualizados:
1. `index.php`
2. `extensiones/composer.json`
3. `controladores/mercadopago.controlador.php`
4. `modelos/mercadopago.modelo.php`
5. `controladores/sistema_cobro.controlador.php`
6. `modelos/sistema_cobro.modelo.php`
7. `vistas/modulos/cabezote-mejorado.php`
8. `webhook-mercadopago.php`

#### **Opción C: cPanel File Manager**

1. Ir a cPanel → File Manager
2. Navegar a `/home/newmoon/public_html`
3. Upload los archivos mencionados arriba
4. Sobrescribir cuando pregunte

---

### **Paso 4: Actualizar Dependencias de Composer**

```bash
cd /home/newmoon/public_html/extensiones

# Eliminar vendor y composer.lock viejos
rm -rf vendor
rm -f composer.lock

# Reinstalar dependencias
composer install --no-dev --optimize-autoloader

# Verificar que no haya errores
composer check-platform-reqs
```

**Si no tienes acceso SSH**, descarga desde local y sube por FTP:

```bash
# En tu máquina local:
cd /home/cluna/Documentos/Moon-Desarrollos/public_html/extensiones
rm -rf vendor composer.lock
composer install --no-dev --optimize-autoloader

# Comprimir vendor
tar -czf vendor.tar.gz vendor/

# Subir vendor.tar.gz al servidor por FTP
# Luego en servidor descomprimir:
# tar -xzf vendor.tar.gz
```

---

### **Paso 5: Configurar Permisos**

```bash
cd /home/newmoon/public_html

# Archivos: 644
find . -type f -exec chmod 644 {} \;

# Directorios: 755
find . -type d -exec chmod 755 {} \;

# Directorios de escritura: 777 (o 775)
chmod -R 777 logs/
chmod -R 777 vistas/img/usuarios/
chmod -R 777 vistas/img/productos/
```

---

### **Paso 6: Crear Tablas de MercadoPago (Si usas el nuevo sistema)**

```bash
# Conectar a MySQL
mysql -u usuario_bd -p nombre_bd

# O desde phpMyAdmin, ejecutar:
```

```sql
-- Copiar el contenido de: mejoras/scripts/crear-tablas-mercadopago.sql
CREATE TABLE IF NOT EXISTS mercadopago_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tipo VARCHAR(50),
    payment_id BIGINT,
    preference_id VARCHAR(100),
    status VARCHAR(50),
    monto DECIMAL(10,2),
    datos TEXT,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_payment_id (payment_id),
    INDEX idx_preference_id (preference_id),
    INDEX idx_status (status),
    INDEX idx_fecha (fecha_creacion)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS mercadopago_webhooks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    topic VARCHAR(50),
    resource_id BIGINT,
    datos_completos TEXT,
    procesado TINYINT(1) DEFAULT 0,
    fecha_recepcion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_proceso TIMESTAMP NULL,
    INDEX idx_resource_id (resource_id),
    INDEX idx_procesado (procesado),
    INDEX idx_fecha (fecha_recepcion)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

### **Paso 7: Verificar PHP en el Servidor**

```bash
php -v
# Debe mostrar: PHP 7.4.x o superior
```

**Si tiene PHP 7.4 o inferior:**

1. **En cPanel:**
   - MultiPHP Manager → Seleccionar dominio
   - Cambiar a PHP 7.4 o PHP 8.1

2. **O en .htaccess:**
```apache
# Agregar al inicio de .htaccess
AddHandler application/x-httpd-php74 .php
# O para PHP 8.1:
# AddHandler application/x-httpd-php81 .php
```

---

### **Paso 8: Probar el Sistema**

#### **1. Verificar que no hay errores PHP:**
```bash
tail -f /home/newmoon/public_html/logs/php_errors.log
# O ver en cPanel → Error Log
```

#### **2. Acceder al sistema:**
```
https://newmoon.posmoon.com.ar/inicio
```

#### **3. Verificar MercadoPago:**
- Debe cargar el modal de cobro sin errores
- Revisar consola del navegador (F12)

#### **4. Verificar clases cargadas:**
Crear archivo temporal `test.php` en raíz:
```php
<?php
require_once "index.php";

if (class_exists('ControladorMercadoPago')) {
    echo "✅ ControladorMercadoPago cargado correctamente<br>";
} else {
    echo "❌ ControladorMercadoPago NO encontrado<br>";
}

if (class_exists('ModeloMercadoPago')) {
    echo "✅ ModeloMercadoPago cargado correctamente<br>";
} else {
    echo "❌ ModeloMercadoPago NO encontrado<br>";
}

echo "<br>PHP Version: " . phpversion();
echo "<br>Composer Autoload: " . (file_exists('extensiones/vendor/autoload.php') ? '✅ Existe' : '❌ NO existe');
```

Acceder a: `https://newmoon.posmoon.com.ar/test.php`

**⚠️ ELIMINAR test.php después de probar**

---

## 🔄 Script Automatizado de Despliegue

Guarda esto como `deploy.sh` en tu máquina local:

```bash
#!/bin/bash

# ============================================
# Script de Despliegue Automatizado
# ============================================

echo "🚀 Iniciando despliegue..."
echo ""

# Variables (CAMBIAR SEGÚN TU SERVIDOR)
SERVER_USER="newmoon"
SERVER_HOST="newmoon.posmoon.com.ar"
SERVER_PATH="/home/newmoon/public_html"
LOCAL_PATH="/home/cluna/Documentos/Moon-Desarrollos/public_html"

echo "📦 Comprimiendo archivos..."
cd "$LOCAL_PATH"
tar -czf /tmp/deploy.tar.gz \
    index.php \
    extensiones/composer.json \
    controladores/mercadopago.controlador.php \
    controladores/sistema_cobro.controlador.php \
    modelos/mercadopago.modelo.php \
    modelos/sistema_cobro.modelo.php \
    webhook-mercadopago.php \
    vistas/modulos/cabezote-mejorado.php

echo "📤 Subiendo al servidor..."
scp /tmp/deploy.tar.gz $SERVER_USER@$SERVER_HOST:/tmp/

echo "🔧 Ejecutando en servidor..."
ssh $SERVER_USER@$SERVER_HOST << 'ENDSSH'
    cd /home/newmoon/public_html
    
    # Backup
    echo "💾 Haciendo backup..."
    tar -czf ../backup_$(date +%Y%m%d_%H%M%S).tar.gz .
    
    # Descomprimir
    echo "📂 Descomprimiendo archivos..."
    tar -xzf /tmp/deploy.tar.gz
    
    # Composer
    echo "📦 Actualizando Composer..."
    cd extensiones
    rm -rf vendor composer.lock
    composer install --no-dev --optimize-autoloader
    
    # Permisos
    echo "🔒 Ajustando permisos..."
    cd ..
    find . -type f -exec chmod 644 {} \;
    find . -type d -exec chmod 755 {} \;
    
    echo "✅ Despliegue completado!"
ENDSSH

echo "🎉 ¡Listo! Verifica en: https://newmoon.posmoon.com.ar"
```

Usar:
```bash
chmod +x deploy.sh
./deploy.sh
```

---

## 📝 Checklist Post-Despliegue

### **Verificaciones Básicas:**
- [ ] El sitio carga sin errores
- [ ] No hay errores en logs de PHP
- [ ] Login funciona correctamente
- [ ] Módulos principales funcionan (Ventas, Productos, etc.)

### **Verificaciones MercadoPago:**
- [ ] Modal de cobro se abre correctamente
- [ ] Se muestra el botón de MercadoPago
- [ ] No hay errores 404 en recursos
- [ ] Credenciales de MP configuradas

### **Verificaciones de Base de Datos:**
- [ ] Tablas `mercadopago_logs` creadas (si aplica)
- [ ] Tablas `mercadopago_webhooks` creadas (si aplica)
- [ ] Conexión a BD Moon funciona

### **Verificaciones de Composer:**
- [ ] Directorio `extensiones/vendor/` existe
- [ ] Archivo `extensiones/vendor/autoload.php` existe
- [ ] No hay errores de platform check

---

## 🚨 Solución de Problemas Comunes

### **Error: "Composer detected issues in your platform"**

**Solución 1:** Actualizar composer.json (ya está hecho)

**Solución 2:** Regenerar vendor
```bash
cd extensiones
rm -rf vendor composer.lock
composer install --ignore-platform-reqs
```

**Solución 3:** Cambiar versión de PHP en cPanel

---

### **Error: "Class ControladorMercadoPago not found"**

**Verificar:**
```bash
# 1. Que el archivo existe
ls -la controladores/mercadopago.controlador.php

# 2. Que está incluido en index.php
grep -n "mercadopago" index.php

# 3. Que no hay errores de sintaxis
php -l controladores/mercadopago.controlador.php
```

---

### **Error: "Cannot modify header information"**

**Causa:** Output antes de headers

**Solución:**
1. Verificar que no haya espacios antes de `<?php`
2. Verificar encoding UTF-8 sin BOM
3. Revisar `ob_start()` en archivos

---

### **Error 500: Internal Server Error**

**Verificar:**
```bash
# Ver logs
tail -100 /home/newmoon/public_html/logs/php_errors.log

# O en cPanel → Error Log
```

**Causas comunes:**
- Permisos incorrectos (archivos deben ser 644, directorios 755)
- Error de sintaxis PHP
- Memoria insuficiente (aumentar en php.ini)

---

## 📞 Contacto y Soporte

Si hay problemas durante el despliegue:

1. **Revisar logs:**
   - `/home/newmoon/public_html/logs/`
   - cPanel → Error Log
   - Browser console (F12)

2. **Restaurar backup:**
   ```bash
   cd /home/newmoon/public_html
   rm -rf *
   tar -xzf ../backup_FECHA.tar.gz
   ```

3. **Documentación adicional:**
   - `mejoras/README.md`
   - `mejoras/GUIA-MERCADOPAGO.md`
   - `mejoras/COMANDOS-GIT.md`

---

**Última actualización:** $(date +"%d/%m/%Y")
**Versión:** 1.0

