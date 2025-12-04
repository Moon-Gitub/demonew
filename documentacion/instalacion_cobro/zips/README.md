# 📦 Archivos Comprimidos - Instalación Rápida

ZIPs organizados por categoría para facilitar la instalación del sistema de cobro.

---

## 📁 ARCHIVOS DISPONIBLES

### **1️⃣ `1-archivos-raiz.zip`** (MÁS IMPORTANTE)
**Copiar a:** `public_html/` (raíz del sitio)

**Contiene:**
- ✅ `generar-qr.php` - Generador de códigos QR
- ✅ `webhook-mercadopago.php` - Receptor de notificaciones
- ✅ `helpers.php` - Funciones auxiliares

**Tamaño:** ~15 KB

---

### **2️⃣ `2-controladores.zip`**
**Copiar a:** `public_html/controladores/`

**Contiene:**
- ✅ `sistema_cobro.controlador.php`
- ✅ `mercadopago.controlador.php`

**Tamaño:** ~8 KB

---

### **3️⃣ `3-modelos.zip`**
**Copiar a:** `public_html/modelos/`

**Contiene:**
- ✅ `sistema_cobro.modelo.php`
- ✅ `mercadopago.modelo.php`
- ✅ `conexion.php` (⚠️ sobrescribe el existente)

**Tamaño:** ~10 KB

---

### **4️⃣ `4-vistas.zip`**
**Copiar a:** `public_html/vistas/modulos/`

**Contiene:**
- ✅ `cabezote-mejorado.php` (modal con QR)
- ✅ `procesar-pago.php` (confirmación)

**Tamaño:** ~25 KB

---

### **5️⃣ `5-sistema-completo.zip`** (TODO EN UNO)
**Para:** Descargar todo de una vez

**Contiene:**
- ✅ Todos los archivos anteriores
- ✅ Estructura de carpetas completa

**Tamaño:** ~45 KB

**Nota:** Este ZIP mantiene la estructura de carpetas, debes extraer cada carpeta en su ubicación correspondiente.

---

## 🚀 CÓMO USAR EN cPanel

### **Método 1: Subir ZIP por ZIP (Recomendado)**

#### **Para archivos raíz:**
1. cPanel → File Manager → `public_html/`
2. Clic en **"Upload"**
3. Subir **`1-archivos-raiz.zip`**
4. Una vez subido, clic derecho → **"Extract"**
5. Confirmar extracción
6. Eliminar el ZIP

#### **Para controladores:**
1. Navegar a `public_html/controladores/`
2. Clic en **"Upload"**
3. Subir **`2-controladores.zip`**
4. Clic derecho → **"Extract"**
5. Confirmar extracción
6. Eliminar el ZIP

#### **Para modelos:**
1. Navegar a `public_html/modelos/`
2. Clic en **"Upload"**
3. Subir **`3-modelos.zip`**
4. Clic derecho → **"Extract"**
5. Confirmar extracción
6. Eliminar el ZIP

#### **Para vistas:**
1. Navegar a `public_html/vistas/modulos/`
2. Clic en **"Upload"**
3. Subir **`4-vistas.zip`**
4. Clic derecho → **"Extract"**
5. Confirmar extracción
6. Eliminar el ZIP

---

### **Método 2: Usar Sistema Completo**

1. Descargar **`5-sistema-completo.zip`**
2. Extraer en tu PC
3. Copiar manualmente cada archivo a su ubicación según estructura

---

## ⏱️ VENTAJAS DE USAR ZIPs

✅ **Más rápido:** Subir 1 archivo en vez de 10  
✅ **Más seguro:** Menos probabilidad de error  
✅ **Más ordenado:** Organizado por categoría  
✅ **Más fácil:** Extract y listo  

---

## 📋 ORDEN DE INSTALACIÓN RECOMENDADO

```
1. ✓ Subir y extraer: 1-archivos-raiz.zip
2. ✓ Subir y extraer: 2-controladores.zip
3. ✓ Subir y extraer: 3-modelos.zip
4. ✓ Subir y extraer: 4-vistas.zip
5. ✓ Crear .env (usando template-env.txt)
6. ✓ Modificar plantilla.php
7. ✓ Verificar index.php
8. ✓ Probar el sistema
```

**Tiempo total:** 12-15 minutos

---

## ⚠️ IMPORTANTE

Después de extraer cada ZIP:
- ✅ **Eliminar el archivo ZIP** (para no dejar basura)
- ✅ **Verificar que los archivos se extrajeron** correctamente
- ✅ **No modificar los archivos** (usar como están)

---

## 🎯 CONTENIDO GARANTIZADO

**TODOS los archivos en estos ZIPs son:**
- ✅ Última versión actualizada
- ✅ Probados y funcionales
- ✅ Con todas las features (botón + QR)
- ✅ Listos para producción

---

## 📞 MÁS INFORMACIÓN

- **Guía completa:** ../INSTALACION-CPANEL.md
- **Checklist:** ../CHECKLIST-CPANEL.md
- **Descarga e instala:** ../DESCARGA-E-INSTALA.md

---

**Moon Desarrollos** © 2025  
Archivos comprimidos actualizados al: Diciembre 2025

