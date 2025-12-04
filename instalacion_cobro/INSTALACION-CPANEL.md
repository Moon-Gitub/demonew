# 🖱️ Instalación Vía cPanel - Sistema de Cobro Moon POS

Guía completa para instalar el sistema de cobro usando **ÚNICAMENTE cPanel** (sin necesidad de terminal/SSH).

---

## 📋 ANTES DE EMPEZAR

### Información que necesitas:

1. **Datos del Cliente:**
   - ID del cliente en BD Moon: ______
   - Nombre del cliente: ______________________
   - Dominio: ______________________

2. **Acceso:**
   - Acceso WHM (para login automático a cPanel)
   - O acceso directo a cPanel de la cuenta

### Archivos que necesitas tener descargados en tu PC:

Descarga la carpeta `instalacion_cobro/` desde GitHub:
```
https://github.com/Moon-Gitub/demonew/tree/main/instalacion_cobro
```

O desde otra cuenta donde ya esté instalado.

---

## PASO 1: Acceder al cPanel de la Cuenta

### Desde WHM (Web Host Manager):

1. Login en WHM: `https://tuservidor.com:2087`
2. Buscar **"Account Functions"** en el menú izquierdo
3. Clic en **"List Accounts"**
4. Buscar la cuenta del cliente (ej: "amarello")
5. Clic en el ícono **"cP"** (cPanel) de esa cuenta
6. Se abrirá el cPanel del cliente automáticamente

### O directamente en cPanel:

Si tienes las credenciales del cliente:
```
https://dominio.com/cpanel
```

---

## PASO 2: Abrir File Manager

1. En cPanel, buscar **"Files"** en el panel
2. Clic en **"File Manager"**
3. Se abrirá el administrador de archivos
4. Navegar a **`public_html`** (raíz del sitio web)

---

## PASO 3: Crear Carpetas (si no existen)

### 3.1 Verificar estructura:

Debes tener estas carpetas dentro de `public_html`:
- ✅ `controladores/`
- ✅ `modelos/`
- ✅ `vistas/`
- ✅ `vistas/modulos/`

Si no existen, créalas:

1. Clic en **"+ Folder"** (arriba)
2. Escribir nombre de la carpeta
3. Clic en **"Create New Folder"**

---

## PASO 4: Subir Archivos del Sistema de Cobro

### 4.1 Subir Controladores

**Desde tu PC:**

1. En File Manager, navega a **`public_html/controladores/`**
2. Clic en **"Upload"** (arriba)
3. Se abrirá el uploader
4. Arrastra o selecciona estos archivos:
   - `instalacion_cobro/archivos/cobro/sistema_cobro.controlador.php`
   - `instalacion_cobro/archivos/controladores/mercadopago.controlador.php`
5. Esperar que se suban (100%)
6. Cerrar el uploader

✅ **Verificar:** En `public_html/controladores/` deben aparecer:
- `sistema_cobro.controlador.php` ✓
- `mercadopago.controlador.php` ✓

### 4.2 Subir Modelos

1. En File Manager, navega a **`public_html/modelos/`**
2. Clic en **"Upload"**
3. Subir estos archivos:
   - `instalacion_cobro/archivos/cobro/sistema_cobro.modelo.php`
   - `instalacion_cobro/archivos/modelos/mercadopago.modelo.php`
   - `instalacion_cobro/archivos/modelos/conexion.php` (¡sobrescribir si existe!)

✅ **Verificar:** En `public_html/modelos/` deben aparecer:
- `sistema_cobro.modelo.php` ✓
- `mercadopago.modelo.php` ✓
- `conexion.php` (actualizado) ✓

### 4.3 Subir Vistas

1. En File Manager, navega a **`public_html/vistas/modulos/`**
2. Clic en **"Upload"**
3. Subir estos archivos:
   - `instalacion_cobro/archivos/vistas/modulos/cabezote-mejorado.php`
   - `instalacion_cobro/archivos/vistas/modulos/procesar-pago.php`

✅ **Verificar:** En `public_html/vistas/modulos/` deben aparecer:
- `cabezote-mejorado.php` ✓
- `procesar-pago.php` ✓

---

## PASO 5: Configurar ID del Cliente

### 5.1 Editar cabezote-mejorado.php

1. En File Manager, navega a **`public_html/vistas/modulos/`**
2. **Clic derecho** en `cabezote-mejorado.php`
3. Seleccionar **"Edit"** o **"Code Editor"**
4. Se abrirá el editor de código
5. Ir a la **línea 15** (aproximadamente)
6. Buscar esta línea:
   ```php
   $idCliente = isset($_ENV['MOON_CLIENTE_ID']) ? intval($_ENV['MOON_CLIENTE_ID']) : (isset($_SERVER['MOON_CLIENTE_ID']) ? intval($_SERVER['MOON_CLIENTE_ID']) : 7);
   ```
7. **Reemplazarla completamente** por:
   ```php
   $idCliente = 14; // AMARELLO (Valentina Herrera)
   ```
   ⚠️ **Cambiar el 14 por el ID REAL de este cliente**

8. Clic en **"Save Changes"** (arriba a la derecha)
9. Cerrar el editor

✅ **Verificar:** El archivo debe tener el ID correcto en la línea 15

### 5.2 ¿Cómo saber qué ID usar?

Usa la herramienta de mapeo:

1. Sube `instalacion_cobro/generar-mapeo-clientes.php` a cualquier cuenta
2. Accede a: `https://dominio.com/generar-mapeo-clientes.php`
3. Verás una tabla con TODOS los clientes y sus IDs
4. Busca el nombre o dominio del cliente
5. Anota el ID

**Ejemplo:**
```
ID: 14
Nombre: AMARELLO (Valentina Herrera)
Dominio: amarello.posmoon.com.ar
```

---

## PASO 6: Modificar plantilla.php

### 6.1 Editar plantilla.php

1. En File Manager, navega a **`public_html/vistas/`**
2. **Clic derecho** en `plantilla.php`
3. Seleccionar **"Edit"** o **"Code Editor"**
4. Usar **Ctrl+F** para buscar: `cabezote.php`
5. Encontrarás una línea como (aproximadamente línea 160):
   ```php
   include "modulos/cabezote.php";
   ```
6. **Reemplazarla** por:
   ```php
   //CABEZOTE CON SISTEMA DE COBRO MERCADOPAGO
   include "modulos/cabezote-mejorado.php";
   ```
7. Clic en **"Save Changes"**
8. Cerrar el editor

✅ **Verificar:** `plantilla.php` ahora incluye `cabezote-mejorado.php`

---

## PASO 7: Modificar index.php

### 7.1 Verificar requires del sistema de cobro

1. En File Manager, navega a **`public_html/`** (raíz)
2. **Clic derecho** en `index.php`
3. Seleccionar **"Edit"**
4. Buscar la sección donde se cargan los controladores (líneas 15-30 aprox)
5. **Verificar** que existan estas líneas:
   ```php
   require_once "controladores/sistema_cobro.controlador.php";
   require_once "modelos/sistema_cobro.modelo.php";
   require_once "controladores/mercadopago.controlador.php";
   require_once "modelos/mercadopago.modelo.php";
   ```

6. **Si NO existen**, agregarlas después de los otros requires

7. Buscar la sección de rutas (donde están los `if` de rutas)

8. **Verificar** que exista la ruta "procesar-pago":
   ```php
   $_GET["ruta"] == "procesar-pago" ||
   ```

9. Si NO existe, agregarla en la lista de rutas válidas

10. Clic en **"Save Changes"**

✅ **Verificar:** `index.php` tiene los requires y la ruta configurada

---

## PASO 8: Verificar que Funciona

### 8.1 Subir archivo de test

1. En File Manager, ir a `public_html/`
2. Clic en **"Upload"**
3. Subir `test-conexion-directa.php` (desde el repositorio)
4. Acceder a: `https://dominio.com/test-conexion-directa.php`

**Debe mostrar:**
```
✅ CONEXIÓN EXITOSA!
Total usuarios: X
```

### 8.2 Probar el sistema

1. Acceder al sistema: `https://dominio.com`
2. Iniciar sesión como administrador
3. **Verificar:**
   - ✅ Aparece el ícono 🌙 en la navbar superior derecha
   - ✅ Si hay deuda, aparece el modal automáticamente
   - ✅ El modal muestra el cliente correcto
   - ✅ El monto es correcto

### 8.3 Test de saldo

1. Subir `testing/test-saldo-cliente.php`
2. Acceder a: `https://dominio.com/test-saldo-cliente.php`
3. Verificar que muestra el cliente y saldo correctos

---

## PASO 9: Limpieza

### 9.1 Eliminar archivos de test

1. En File Manager, seleccionar:
   - `test-conexion-directa.php`
   - `test-saldo-cliente.php`
2. Clic derecho → **"Delete"**
3. Confirmar eliminación

### 9.2 Documentar la instalación

Anotar en tu archivo de control:
```
✅ Cliente: AMARELLO
✅ ID: 14
✅ Dominio: amarello.posmoon.com.ar
✅ Fecha: 04/12/2025
✅ Estado: Funcionando
```

---

## ✅ CHECKLIST RÁPIDO

Por cada cuenta:

- [ ] Acceder a cPanel de la cuenta
- [ ] Abrir File Manager → public_html
- [ ] Subir 6 archivos (2 controladores, 3 modelos, 2 vistas)
- [ ] Editar cabezote-mejorado.php → Configurar ID del cliente
- [ ] Editar plantilla.php → Cambiar include a cabezote-mejorado
- [ ] Editar index.php → Agregar requires y ruta procesar-pago
- [ ] Probar con test-conexion-directa.php
- [ ] Verificar en el sistema real
- [ ] Eliminar archivos de test
- [ ] Documentar instalación

⏱️ **Tiempo estimado:** 10-15 minutos por cuenta

---

## 🎯 TIPS PARA ACELERAR

### Tip 1: Abrir múltiples pestañas

- Pestaña 1: WHM (para cambiar de cuenta rápido)
- Pestaña 2: cPanel File Manager de la cuenta actual
- Pestaña 3: Editor de código
- Pestaña 4: Sistema del cliente para probar

### Tip 2: Copiar/Pegar código

Ten abiertos en tu editor local:
- El código del ID del cliente: `$idCliente = XX;`
- Los requires para index.php
- El include para plantilla.php

Así solo tienes que cambiar el número del ID y pegar.

### Tip 3: Usar búsqueda de cPanel

En el editor de cPanel:
- **Ctrl+F** para buscar texto
- Buscar `cabezote.php` en plantilla.php
- Buscar `require_once "controladores` en index.php

### Tip 4: Template de IDs

Mantén un archivo de texto con:
```
amarello = 14
demo = 7
abisko = 2
adrimar = ?
...
```

---

## ⚠️ PROBLEMAS COMUNES

### No aparece el ícono 🌙

**Solución:**
1. Verificar que `plantilla.php` incluya `cabezote-mejorado.php`
2. Limpiar caché del navegador (Ctrl+Shift+Del)
3. Revisar logs de errores en cPanel → "Errors"

### Dice "al día" cuando tiene deuda

**Solución:**
1. Verificar ID del cliente en `cabezote-mejorado.php` línea 15
2. Usar `test-saldo-cliente.php` para verificar el saldo
3. Consultar BD Moon para ver si el ID es correcto

### No se puede subir archivos

**Solución:**
1. Verificar espacio en disco (cPanel → Disk Usage)
2. Verificar permisos de carpetas (deben ser 755)
3. Intentar con FTP si el uploader falla

---

## 🎓 TUTORIAL VISUAL - cPanel File Manager

### Navegación:
```
1. cPanel → Files → File Manager
2. Barra lateral izquierda: estructura de carpetas
3. Panel central: archivos de la carpeta actual
4. Botones arriba: Upload, New File, New Folder, etc.
```

### Subir archivos:
```
1. Navegar a la carpeta destino
2. Clic en "Upload" (arriba)
3. Arrastrar archivos o clic en "Select Files"
4. Esperar 100%
5. Cerrar uploader
```

### Editar archivos:
```
1. Clic derecho en el archivo
2. "Edit" o "Code Editor"
3. Hacer cambios
4. Clic en "Save Changes" (arriba derecha)
5. Confirmar y cerrar
```

### Crear carpetas:
```
1. Clic en "+ Folder" (arriba)
2. Escribir nombre
3. Clic en "Create New Folder"
```

---

## 📊 ORDEN SUGERIDO PARA MÚLTIPLES CUENTAS

### Cuenta 1 (Primera instalación):
- ⏱️ Tiempo: 20 minutos
- Lee toda la guía
- Haz todos los pasos con calma
- Documenta cualquier problema

### Cuenta 2-3:
- ⏱️ Tiempo: 15 minutos cada una
- Ya conoces el proceso
- Usa el checklist rápido

### Cuenta 4+:
- ⏱️ Tiempo: 10 minutos cada una
- Ya eres experto
- Proceso rutinario

---

## 🎯 RESUMEN ULTRA-RÁPIDO

Para cada cuenta:

1. **Acceso:** WHM → Login to cPanel de la cuenta
2. **Upload:** File Manager → Subir 6 archivos
3. **Editar línea 15:** `cabezote-mejorado.php` → ID del cliente
4. **Editar línea ~160:** `plantilla.php` → include cabezote-mejorado
5. **Editar líneas ~55:** `index.php` → requires de sistema_cobro
6. **Probar:** Acceder al sistema → Ver ícono 🌙
7. **Listo:** Siguiente cuenta

---

## 📞 SOPORTE

Si tienes problemas:
1. Revisar logs en cPanel → "Errors"
2. Usar tests de diagnóstico
3. Consultar CHECKLIST-INSTALACION.md

---

**Creado para:** Instalación vía cPanel sin terminal  
**Tiempo estimado:** 10-15 min/cuenta  
**Dificultad:** Baja  
**Conocimientos necesarios:** cPanel básico

