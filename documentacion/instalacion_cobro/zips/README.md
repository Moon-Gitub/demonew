# 📦 Archivos ZIP - Instalación Rápida

ZIPs organizados para facilitar la instalación del sistema de cobro.

---

## 📁 ARCHIVOS DISPONIBLES

### 1️⃣ `1-archivos-raiz.zip` (5 KB)
**Extraer en:** `public_html/` (raíz del sitio)

**Contiene:**
- `generar-qr.php` - Generador de códigos QR para pago
- `webhook-mercadopago.php` - Receptor de notificaciones de MercadoPago
- `helpers.php` - Funciones auxiliares para variables de entorno

---

### 2️⃣ `2-controladores.zip` (3 KB)
**Extraer en:** `public_html/controladores/`

**Contiene:**
- `sistema_cobro.controlador.php` - Lógica de negocio del sistema de cobro
- `mercadopago.controlador.php` - Integración con API de MercadoPago

---

### 3️⃣ `3-modelos.zip` (4 KB)
**Extraer en:** `public_html/modelos/`

**Contiene:**
- `sistema_cobro.modelo.php` - Acceso a datos de clientes
- `mercadopago.modelo.php` - Gestión de pagos y webhooks
- `conexion.php` - Conexión dual a bases de datos (⚠️ sobrescribe el existente)

---

### 4️⃣ `4-vistas.zip` (12 KB)
**Extraer en:** `public_html/vistas/modulos/`

**Contiene:**
- `cabezote-mejorado.php` - Modal de cobro con botón y QR
- `procesar-pago.php` - Página de confirmación de pago

---

### 5️⃣ `5-sistema-completo.zip` (24 KB)
**Para:** Descargar todo de una vez

**Contiene:** Todos los archivos anteriores con estructura de carpetas

⚠️ **Nota:** Este ZIP mantiene la estructura de carpetas, debes mover cada archivo a su ubicación correspondiente.

---

## 🚀 INSTRUCCIONES DE USO EN cPanel

### Proceso para cada ZIP:

1. **Subir:**
   - cPanel → File Manager → Navegar a la carpeta destino
   - Clic en **"Upload"**
   - Seleccionar el archivo ZIP
   - Esperar que se suba (100%)

2. **Extraer:**
   - Clic derecho en el archivo ZIP
   - Seleccionar **"Extract"** o **"Extraer"**
   - Confirmar la extracción
   - Los archivos se extraerán en la carpeta actual

3. **Limpiar:**
   - Seleccionar el archivo ZIP
   - Clic en **"Delete"** o **"Eliminar"**
   - Confirmar

4. **Verificar:**
   - Asegurarse que los archivos se extrajeron correctamente
   - Verificar permisos (deben ser 644 para .php)

---

## 📋 ORDEN RECOMENDADO

```
1. ✓ public_html/           → 1-archivos-raiz.zip
2. ✓ controladores/         → 2-controladores.zip
3. ✓ modelos/               → 3-modelos.zip
4. ✓ vistas/modulos/        → 4-vistas.zip
5. ✓ Crear .env
6. ✓ Modificar plantilla.php
7. ✓ Listo!
```

---

## ⏱️ TIEMPO

- **Con ZIPs:** 12-15 minutos
- **Sin ZIPs:** 20-22 minutos
- **Ahorro:** 30-40% más rápido

---

## ✅ CONTENIDO VERIFICADO

Todos los archivos en estos ZIPs son:
- ✅ Última versión (Diciembre 2025)
- ✅ Probados en producción
- ✅ Con todas las features (botón + QR)
- ✅ 100% funcionales

---

## 📞 MÁS INFORMACIÓN

- **Guía completa:** ../INSTALACION-CPANEL.md
- **Descarga e instala:** ../DESCARGA-E-INSTALA.md
- **Checklist:** ../CHECKLIST-CPANEL.md

---

**Moon Desarrollos** © 2025

