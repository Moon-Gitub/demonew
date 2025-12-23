# 📥 Descargar e Instalar en Nuevo Hosting

Guía rápida para descargar los archivos de GitHub e instalarlos en un nuevo hosting.

---

## 🎯 PROCESO COMPLETO (20 minutos)

### ✅ **TODOS LOS ARCHIVOS ESTÁN ACTUALIZADOS EN GITHUB**

Los archivos en `documentacion/instalacion_cobro/archivos/` son:
- ✅ **100% funcionales**
- ✅ **Última versión** con todas las features
- ✅ **Probados en producción**
- ✅ **Listos para copiar** directo a hosting

---

## 📥 PASO 1: Descargar de GitHub (2 min)

### **Opción A - Usar ZIPs Pre-comprimidos (MÁS RÁPIDO) ⭐**

1. Ir a: https://github.com/Moon-Gitub/demonew
2. Navegar a: `documentacion/instalacion_cobro/zips/`
3. Descargar los ZIPs que necesites:
   - **`1-archivos-raiz.zip`** (obligatorio)
   - **`2-controladores.zip`** (obligatorio)
   - **`3-modelos.zip`** (obligatorio)
   - **`4-vistas.zip`** (obligatorio)
   - O **`5-sistema-completo.zip`** (todo en uno)

**Ventaja:** Subes 1 archivo y extraes en cPanel (muy rápido)

### **Opción B - Descargar Todo el Repositorio:**

1. Ir a: https://github.com/Moon-Gitub/demonew
2. Clic en botón verde **"Code"**
3. Seleccionar **"Download ZIP"**
4. Guardar en tu PC
5. Extraer el ZIP
6. Ir a la carpeta: `demonew-main/documentacion/instalacion_cobro/`

### **Opción C - Clonar con Git:**

```bash
git clone https://github.com/Moon-Gitub/demonew.git
cd demonew/documentacion/instalacion_cobro/
```

---

## 📖 PASO 2: Leer la Guía (2 min)

Abre en tu navegador:

**`documentacion/instalacion_cobro/INICIO.md`** ⭐ Empieza aquí

Esto te llevará a:
- INSTALACION-CPANEL.md (guía completa)
- CHECKLIST-CPANEL.md (checklist)
- template-env.txt (template)

---

## 📂 PASO 3: Identificar los Archivos (1 min)

Todos los archivos para copiar están en:

**`documentacion/instalacion_cobro/archivos/`**

```
archivos/
├── generar-qr.php                    ← Copiar a raíz
├── webhook-mercadopago.php           ← Copiar a raíz
├── helpers.php                       ← Copiar a raíz
│
├── controladores-agregar/
│   ├── sistema_cobro.controlador.php ← Copiar a /controladores/
│   └── mercadopago.controlador.php   ← Copiar a /controladores/
│
├── modelos-agregar/
│   ├── sistema_cobro.modelo.php      ← Copiar a /modelos/
│   ├── mercadopago.modelo.php        ← Copiar a /modelos/
│   └── conexion.php                  ← Copiar a /modelos/ (sobrescribe)
│
└── vistas-agregar/
    └── modulos/
        ├── cabezote-mejorado.php     ← Copiar a /vistas/modulos/
        └── procesar-pago.php         ← Copiar a /vistas/modulos/
```

---

## 🖱️ PASO 4: Copiar con cPanel (10 min)

### **Método A: Usar ZIPs (MÁS RÁPIDO) ⭐ RECOMENDADO**

#### 4.1 Acceder a cPanel
1. WHM → List Accounts → Buscar cuenta
2. Clic en **cP** (ícono cPanel)
3. Ir a **Files → File Manager**
4. Navegar a **public_html**

#### 4.2 Archivos Raíz (desde ZIP)
1. Estar en `public_html/`
2. Clic en **"Upload"**
3. Subir **`1-archivos-raiz.zip`**
4. Clic derecho en el ZIP → **"Extract"**
5. Confirmar
6. Eliminar el ZIP
7. ✅ Verás: generar-qr.php, webhook-mercadopago.php, helpers.php

#### 4.3 Controladores (desde ZIP)
1. Navegar a `public_html/controladores/`
2. Upload → **`2-controladores.zip`**
3. Extract → Confirmar
4. Eliminar ZIP
5. ✅ Verás: 2 archivos .controlador.php

#### 4.4 Modelos (desde ZIP)
1. Navegar a `public_html/modelos/`
2. Upload → **`3-modelos.zip`**
3. Extract → Confirmar
4. Eliminar ZIP
5. ✅ Verás: 3 archivos .modelo.php y conexion.php

#### 4.5 Vistas (desde ZIP)
1. Navegar a `public_html/vistas/modulos/`
2. Upload → **`4-vistas.zip`**
3. Extract → Confirmar
4. Eliminar ZIP
5. ✅ Verás: cabezote-mejorado.php, procesar-pago.php

---

### **Método B: Copiar Archivos Individuales**

Si prefieres subir archivo por archivo (sin ZIPs):

#### 4.1 Archivos en Raíz
1. Estar en `public_html/`
2. Upload:
   - `archivos/generar-qr.php`
   - `archivos/webhook-mercadopago.php`
   - `archivos/helpers.php`

#### 4.2 Controladores
1. En `public_html/controladores/`
2. Upload:
   - `archivos/controladores-agregar/sistema_cobro.controlador.php`
   - `archivos/controladores-agregar/mercadopago.controlador.php`

#### 4.3 Modelos
1. En `public_html/modelos/`
2. Upload:
   - `archivos/modelos-agregar/sistema_cobro.modelo.php`
   - `archivos/modelos-agregar/mercadopago.modelo.php`
   - `archivos/modelos-agregar/conexion.php`

#### 4.4 Vistas
1. En `public_html/vistas/modulos/`
2. Upload:
   - `archivos/vistas-agregar/modulos/cabezote-mejorado.php`
   - `archivos/vistas-agregar/modulos/procesar-pago.php`

---

## ⚙️ PASO 5: Configurar (5 min)

### 5.1 Crear archivo .env

1. En `public_html/`, clic en **"+ File"**
2. Nombre: `.env` (con el punto)
3. Create New File
4. **Clic derecho** en `.env` → **Edit**
5. Copiar contenido de **`template-env.txt`**
6. **CAMBIAR** `MOON_CLIENTE_ID=14` por el ID real
7. Save Changes
8. Clic derecho → Permissions → **600**

### 5.2 Modificar plantilla.php

1. Ir a `public_html/vistas/`
2. Clic derecho en `plantilla.php` → Edit
3. Buscar: `include "modulos/cabezote.php";`
4. Cambiar a: `include "modulos/cabezote-mejorado.php";`
5. Save Changes

### 5.3 Verificar index.php

1. Ir a `public_html/`
2. Clic derecho en `index.php` → Edit
3. Verificar que tenga estos requires:
   ```php
   require_once "helpers.php";
   require_once "controladores/sistema_cobro.controlador.php";
   require_once "modelos/sistema_cobro.modelo.php";
   require_once "controladores/mercadopago.controlador.php";
   require_once "modelos/mercadopago.modelo.php";
   ```
4. Si faltan, agregarlos después de los otros requires
5. Save Changes

---

## 🧪 PASO 6: Probar (2 min)

1. Acceder al sistema POS del cliente
2. Login como administrador
3. Buscar **"💳 Estado Cuenta"** en el navbar (arriba derecha)
4. Hacer clic
5. Verificar que aparezca:
   - ✅ Nombre del cliente correcto
   - ✅ Saldo pendiente correcto
   - ✅ Botón "Pagar con Mercado Pago"
   - ✅ **Código QR visible y funcional**
   - ✅ Diseño responsive y limpio

---

## ✅ VERIFICACIÓN FINAL

- [ ] Botón "Estado Cuenta" visible en navbar
- [ ] Modal se abre al hacer clic
- [ ] Nombre del cliente correcto
- [ ] Saldo correcto
- [ ] Botón Mercado Pago funciona
- [ ] Código QR se ve correctamente
- [ ] Diseño responsive (probar en móvil)

---

## 🎯 ARCHIVOS GARANTIZADOS

**TODOS los archivos en `documentacion/instalacion_cobro/archivos/` están:**

✅ Actualizados (última versión en GitHub)  
✅ Funcionales (probados en producción)  
✅ Completos (incluyen todas las features)  
✅ Listos (copiar y pegar sin modificar)  
✅ **Compatibles (funcionan con o sin AFIP/Cotización)**

---

## 🔧 COMPATIBILIDAD TOTAL

### ✅ **Funciona en CUALQUIER Sistema POS**

El sistema de cobro es **100% compatible** con sistemas que:
- ✅ Tienen AFIP → Muestra AFIP + Estado Cuenta
- ✅ No tienen AFIP → Muestra solo Estado Cuenta
- ✅ Tienen Cotización → Muestra Cotización + Estado Cuenta
- ✅ No tienen Cotización → Muestra solo Estado Cuenta
- ✅ Son básicos → Muestra solo Estado Cuenta

**No necesitas modificar nada.** El código detecta automáticamente qué funcionalidades tiene tu sistema y se adapta. 🎯  

---

## 📞 Si Necesitas Más Info

- **Guía completa:** INSTALACION-CPANEL.md
- **Checklist:** CHECKLIST-CPANEL.md
- **Arquitectura:** ../ARQUITECTURA-BASES-DATOS.md

---

## ⏱️ TIEMPO TOTAL

| Actividad | Tiempo |
|-----------|--------|
| Descargar de GitHub | 2 min |
| Leer guía | 2 min |
| Identificar archivos | 1 min |
| Copiar archivos | 10 min |
| Configurar | 5 min |
| Probar | 2 min |
| **TOTAL** | **20-22 min** |

---

**Moon Desarrollos** © 2025  
Sistema de Cobro POS v2.0 (con QR Code)

