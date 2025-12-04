# ✅ Checklist Visual - Instalación por cPanel

Guía paso a paso con checkboxes para instalar cuenta por cuenta usando solo cPanel.

---

## 📝 INFORMACIÓN DE LA CUENTA

**Cliente:** ______________________________  
**ID en BD Moon:** ______  
**Dominio:** ______________________________  
**Usuario cPanel:** ______________________________  
**Fecha instalación:** ___/___/_____  

---

## 🎯 PASO A PASO

### ☐ PASO 1: Preparación (5 min)

- [ ] Buscar ID del cliente en BD Moon
  - Usar: `generar-mapeo-clientes.php`
  - O query: `SELECT id, nombre FROM clientes WHERE dominio LIKE '%nombre%'`
  - **ID encontrado:** ______

- [ ] Anotar ID del cliente arriba ↑

- [ ] Tener archivos de `instalacion_cobro/archivos/` descargados en tu PC

---

### ☐ PASO 2: Acceso (1 min)

- [ ] Abrir WHM: `https://servidor.com:2087`
- [ ] Ir a **Account Functions → List Accounts**
- [ ] Buscar la cuenta del cliente
- [ ] Clic en ícono **cP** (abre cPanel del cliente)
- [ ] Ir a **Files → File Manager**
- [ ] Navegar a **public_html**

---

### ☐ PASO 3: Subir Archivos (4 min)

**Archivos en Raíz (public_html/):**
- [ ] Ir a `public_html/` (raíz)
- [ ] Clic en **Upload**
- [ ] Subir: `generar-qr.php` ⭐ NUEVO
- [ ] Subir: `webhook-mercadopago.php`
- [ ] Subir: `helpers.php`
- [ ] Cerrar uploader

**Controladores:**
- [ ] Ir a `public_html/controladores/`
- [ ] Clic en **Upload**
- [ ] Subir: `sistema_cobro.controlador.php`
- [ ] Subir: `mercadopago.controlador.php`
- [ ] Cerrar uploader

**Modelos:**
- [ ] Ir a `public_html/modelos/`
- [ ] Clic en **Upload**
- [ ] Subir: `sistema_cobro.modelo.php`
- [ ] Subir: `mercadopago.modelo.php`
- [ ] Subir: `conexion.php` (sobrescribir si existe)
- [ ] Cerrar uploader

**Vistas:**
- [ ] Ir a `public_html/vistas/modulos/`
- [ ] Clic en **Upload**
- [ ] Subir: `cabezote-mejorado.php`
- [ ] Subir: `procesar-pago.php`
- [ ] Cerrar uploader

---

### ☐ PASO 4: Crear y Configurar archivo .env (3 min)

**Crear el archivo:**
- [ ] En File Manager, ir a `public_html/` (raíz)
- [ ] Clic en **"+ File"** (arriba)
- [ ] Nombre: `.env` (con el punto al inicio)
- [ ] Clic en "Create New File"

**Editar el archivo:**
- [ ] **Clic derecho** en `.env` → **Edit**
- [ ] Copiar este contenido:
```env
MOON_CLIENTE_ID=14
MOON_DB_HOST=107.161.23.11
MOON_DB_NAME=cobrosposmooncom_db
MOON_DB_USER=cobrosposmooncom_dbuser
MOON_DB_PASS=[Us{ynaJAA_o2A_!
MP_PUBLIC_KEY=APP_USR-33156d44-12df-4039-8c92-1635d8d3edde
MP_ACCESS_TOKEN=APP_USR-6921807486493458-102300-5f1cec174eb674c42c9782860caf640c-2916747261
```
- [ ] **Cambiar el 14** por el ID REAL de este cliente
- [ ] **Save Changes**
- [ ] Cerrar editor

**Proteger el archivo:**
- [ ] Clic derecho en `.env` → **"Permissions"**
- [ ] Configurar: **600** (Owner: Read+Write, resto desmarcado)
- [ ] Clic en "Change Permissions"

✅ **ID del cliente configurado:** ______ (anotar aquí)

---

### ☐ PASO 5: Modificar plantilla.php (1 min)

- [ ] En File Manager, ir a `public_html/vistas/`
- [ ] **Clic derecho** en `plantilla.php` → **Edit**
- [ ] **Ctrl+F** buscar: `cabezote.php`
- [ ] Encontrar línea: `include "modulos/cabezote.php";`
- [ ] Cambiar a: `include "modulos/cabezote-mejorado.php";`
- [ ] **Save Changes**
- [ ] Cerrar editor

---

### ☐ PASO 6: Modificar index.php (2 min)

- [ ] En File Manager, ir a `public_html/`
- [ ] **Clic derecho** en `index.php` → **Edit**

**Verificar requires (líneas 50-60 aprox):**
- [ ] Buscar: `require_once "controladores/`
- [ ] Verificar que existan:
  ```php
  require_once "controladores/sistema_cobro.controlador.php";
  require_once "modelos/sistema_cobro.modelo.php";
  require_once "controladores/mercadopago.controlador.php";
  require_once "modelos/mercadopago.modelo.php";
  ```
- [ ] Si NO existen, copiar y pegar después de los otros requires

**Verificar ruta procesar-pago (líneas 200-220 aprox):**
- [ ] Buscar: `$_GET["ruta"] ==`
- [ ] Verificar que existe: `$_GET["ruta"] == "procesar-pago" ||`
- [ ] Si NO existe, agregar en la lista de rutas válidas

- [ ] **Save Changes**
- [ ] Cerrar editor

---

### ☐ PASO 7: Pruebas (2 min)

**Test de conexión:**
- [ ] Subir `test-conexion-directa.php` a public_html
- [ ] Acceder: `https://dominio.com/test-conexion-directa.php`
- [ ] Debe mostrar: ✅ CONEXIÓN EXITOSA
- [ ] Eliminar archivo de test

**Test del sistema:**
- [ ] Acceder: `https://dominio.com`
- [ ] Login como administrador
- [ ] Verificar: Aparece ícono 🌙 en navbar
- [ ] Si hay deuda: Modal se abre automáticamente
- [ ] Modal muestra cliente correcto
- [ ] Monto es correcto

---

### ☐ PASO 8: Documentación

- [ ] Anotar instalación completada:
  ```
  Cliente: _______________________
  ID: _______
  Fecha: ___/___/_____
  Estado: ✅ Funcionando
  Observaciones: _________________
  ```

---

## 🎯 RESUMEN TIEMPOS

| Paso | Tiempo |
|------|--------|
| 1. Preparación | 5 min |
| 2. Acceso | 1 min |
| 3. Subir archivos | 3 min |
| 4. Configurar ID | 2 min |
| 5. Modificar plantilla | 1 min |
| 6. Modificar index | 2 min |
| 7. Pruebas | 2 min |
| 8. Documentación | 1 min |
| **TOTAL** | **15-17 min** |

---

## 📊 PROGRESO GENERAL

Cuentas completadas: _____ / _____

| # | Cliente | ID | Dominio | Estado | Fecha |
|---|---------|----|---------| -------|-------|
| 1 | | | | ☐ | |
| 2 | | | | ☐ | |
| 3 | | | | ☐ | |
| 4 | | | | ☐ | |
| 5 | | | | ☐ | |

---

## ⚠️ SI ALGO FALLA

**Error al subir archivos:**
- Verificar espacio en disco
- Probar con archivos más pequeños primero
- Usar FTP si el uploader falla

**No aparece ícono 🌙:**
- Limpiar caché del navegador
- Verificar que plantilla.php incluya cabezote-mejorado
- Revisar logs de PHP en cPanel

**Dice "al día" cuando tiene deuda:**
- Verificar ID en cabezote-mejorado.php línea 15
- Usar test-saldo-cliente.php para verificar
- Consultar BD Moon para confirmar el ID

---

✅ **Instalación completada exitosamente**  
⏱️ **Tiempo total:** _______ minutos  
📝 **Notas:** _________________________________

