# 📦 Guía de Vendor/Composer para Instalación en Múltiples Cuentas

Cómo manejar la carpeta `extensiones/vendor/` al instalar en múltiples cuentas del reseller.

---

## 🎯 RESUMEN RÁPIDO

**NO subas la carpeta vendor completa manualmente.**  
Usa Composer en cada cuenta o copia vendor ya compilado.

---

## ❓ ¿QUÉ ES VENDOR?

La carpeta `extensiones/vendor/` contiene todas las librerías PHP de terceros:
- MercadoPago SDK
- PhpSpreadsheet (para Excel)
- TCPDF (para PDFs)
- Dotenv (para archivos .env)

**Tamaño:** ~50-100 MB  
**Archivos:** ~6,000 archivos

---

## 🚀 OPCIONES PARA INSTALAR VENDOR

### **Opción 1: Composer Install (Recomendado si tienes SSH)**

Si tienes acceso SSH a la cuenta:

```bash
cd /home/usuario/public_html/extensiones
composer install
```

✅ **Ventajas:**
- Descarga versiones más actualizadas
- Instalación limpia
- Usa menos espacio

❌ **Desventajas:**
- Requiere SSH
- Requiere Composer instalado
- Tarda 2-3 minutos por cuenta

---

### **Opción 2: Copiar Vendor Completo (Recomendado para cPanel)**

Copiar la carpeta `vendor/` de una cuenta que ya funciona.

**Desde cPanel:**

1. En la cuenta que YA funciona (ej: newmoon):
   - File Manager → `public_html/extensiones/vendor/`
   - Seleccionar carpeta `vendor/`
   - Clic en **"Compress"** → Formato: **ZIP**
   - Esperar que se cree `vendor.zip` (~20-30 MB comprimido)
   - Clic en `vendor.zip` → **Download**

2. En la cuenta NUEVA (ej: amarello):
   - File Manager → `public_html/extensiones/`
   - Clic en **"Upload"**
   - Subir `vendor.zip`
   - Esperar que se suba (puede tardar 2-5 min)
   - **Clic derecho** en `vendor.zip` → **"Extract"**
   - Esperar que se descomprima
   - Eliminar `vendor.zip`

✅ **Ventajas:**
- No necesitas SSH
- Funciona 100% desde cPanel
- Mismo vendor que ya funciona

❌ **Desventajas:**
- Requiere subir archivo grande
- Proceso lento (5-10 min)
- Consume más espacio temporalmente

---

### **Opción 3: Copiar Vendor por SSH (Más rápido)**

Si tienes SSH en el servidor principal:

```bash
# Desde el servidor principal (como root o con permisos)
cp -r /home/newmoon/public_html/extensiones/vendor /home/amarello/public_html/extensiones/

# Ajustar permisos
chown -R amarello:amarello /home/amarello/public_html/extensiones/vendor
```

✅ **Ventajas:**
- Muy rápido (segundos)
- No requiere descargar/subir
- Copia directa en el servidor

❌ **Desventajas:**
- Requiere SSH con permisos elevados
- No siempre disponible en shared hosting

---

### **Opción 4: Vendor Compartido (Avanzado)**

Crear un symlink para que todas las cuentas usen el mismo vendor:

```bash
# En cada cuenta nueva
cd /home/usuario/public_html/extensiones
rm -rf vendor
ln -s /home/newmoon/public_html/extensiones/vendor vendor
```

✅ **Ventajas:**
- Ahorra MUCHO espacio
- Una sola copia de vendor para todas las cuentas
- Actualizaciones centralizadas

❌ **Desventajas:**
- Requiere SSH
- Si se rompe en una cuenta, afecta a todas
- Algunos hostings no permiten symlinks

---

## 📊 COMPARACIÓN DE MÉTODOS

| Método | Tiempo | SSH? | Dificultad | Recomendado para |
|--------|--------|------|------------|------------------|
| Composer | 3 min | Sí | Media | Técnicos |
| Copiar ZIP | 10 min | No | Baja | **cPanel** ⭐ |
| Copiar SSH | 30 seg | Sí | Media | Root/Admin |
| Symlink | 30 seg | Sí | Alta | Expertos |

---

## 🎯 RECOMENDACIÓN PARA TU CASO

**Como usas cPanel sin SSH:** Usa **Opción 2 (Copiar Vendor ZIP)**

### Proceso sugerido:

**Una sola vez:**
1. En cuenta que funciona (newmoon): comprimir `vendor/` → descargar `vendor.zip`
2. Guardar `vendor.zip` en tu PC

**Por cada cuenta nueva:**
1. Subir `vendor.zip` a `public_html/extensiones/`
2. Extraer
3. Eliminar `vendor.zip`
4. Continuar con instalación normal

---

## ⚙️ VERIFICAR SI VENDOR EXISTE

**Desde cPanel:**

1. File Manager → `public_html/extensiones/`
2. Buscar carpeta `vendor/`
3. Si existe y tiene subcarpetas (mercadopago, vlucas, etc.) → ✅ OK
4. Si NO existe → Necesitas instalarlo

**Desde archivo PHP (crear `test-vendor.php`):**

```php
<?php
require_once 'extensiones/vendor/autoload.php';

if (class_exists('MercadoPago\SDK')) {
    echo "✅ MercadoPago SDK instalado\n";
} else {
    echo "❌ MercadoPago SDK NO encontrado\n";
}

if (class_exists('Dotenv\Dotenv')) {
    echo "✅ Dotenv instalado\n";
} else {
    echo "❌ Dotenv NO encontrado\n";
}
?>
```

Acceder a: `https://dominio.com/test-vendor.php`

---

## 🔧 COMPOSER.JSON

El archivo `extensiones/composer.json` define qué librerías se necesitan:

```json
{
    "require": {
        "phpoffice/phpspreadsheet": "^4.1",
        "tecnickcom/tcpdf": "^6.8",
        "mercadopago/dx-php": "^3.1",
        "vlucas/phpdotenv": "^5.6"
    }
}
```

**Librerías necesarias para el sistema de cobro:**
- ✅ `mercadopago/dx-php` - SDK de MercadoPago
- ✅ `vlucas/phpdotenv` - Para leer archivos .env

**Las otras librerías** (phpspreadsheet, tcpdf) son para Excel y PDFs, no son necesarias para el sistema de cobro pero el sistema POS las usa.

---

## 📋 INSTRUCCIONES ESPECÍFICAS PARA CPANEL

### PASO A PASO:

**PASO 1: Preparar vendor.zip (Una sola vez)**

En tu cuenta que funciona (newmoon):

1. cPanel → File Manager
2. Navegar a `public_html/extensiones/`
3. **Clic derecho** en carpeta `vendor/`
4. Seleccionar **"Compress"**
5. Formato: **ZIP Archive**
6. Nombre: `vendor.zip`
7. Clic en **"Compress File(s)"**
8. Esperar... (puede tardar 1-2 minutos)
9. Cuando termine, **clic derecho** en `vendor.zip`
10. Seleccionar **"Download"**
11. Guardar en tu PC

✅ Ahora tienes `vendor.zip` (~20-30 MB)

**PASO 2: Por cada cuenta nueva**

1. cPanel de la nueva cuenta → File Manager
2. Navegar a `public_html/extensiones/`
3. Verificar si ya existe carpeta `vendor/`:
   - **Si existe y tiene archivos:** ✅ No hacer nada, ya está
   - **Si NO existe o está vacía:** Continuar ↓
4. Clic en **"Upload"**
5. Subir `vendor.zip` desde tu PC
6. Esperar que suba (2-5 minutos según conexión)
7. Cerrar uploader
8. **Clic derecho** en `vendor.zip` → **"Extract"**
9. Destino: Dejar `/home/usuario/public_html/extensiones/`
10. Clic en **"Extract File(s)"**
11. Esperar que se descomprima (1-2 minutos)
12. **Eliminar** `vendor.zip` (ya no se necesita)

✅ **Verificar:** Existe carpeta `vendor/` con subcarpetas dentro

---

## ⚠️ IMPORTANTE

**NO subas vendor completo sin comprimir:**
- Son 6,000+ archivos
- Tardará horas
- Puede fallar el upload

**SIEMPRE comprímelo primero:**
- ZIP: ~20-30 MB
- Sube en 2-5 minutos
- Extrae automáticamente

---

## 🎯 RESUMEN PARA TU FLUJO

**Por cada cuenta nueva:**

1. ✅ Subir 6 archivos del sistema de cobro (rápido)
2. ✅ Subir `vendor.zip` (5 min) **← AGREGAR ESTO**
3. ✅ Extraer `vendor.zip` (2 min)
4. ✅ Crear `.env` con el ID del cliente (1 min)
5. ✅ Editar `plantilla.php` (1 min)
6. ✅ Editar `index.php` (1 min)
7. ✅ Probar (1 min)

⏱️ **Total:** ~15-20 minutos por cuenta (incluyendo vendor)

---

## 💡 TIP PRO

Si vas a instalar en 10+ cuentas:

1. Prepara en tu PC:
   - ✅ `vendor.zip` (descargado una vez)
   - ✅ Los 6 archivos del sistema de cobro
   - ✅ `template-env.txt` abierto para copiar

2. Abre WHM en una pestaña

3. Por cada cuenta:
   - WHM → cPanel
   - Upload todos los archivos a la vez
   - Editar solo `.env` y `plantilla.php`
   - Siguiente cuenta

Así puedes hacer 3-4 cuentas por hora fácilmente.

---

**¿Te queda claro cómo manejar vendor?** 📦

