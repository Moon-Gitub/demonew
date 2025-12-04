# 📁 Archivos para Instalación del Sistema de Cobro

Esta carpeta contiene **TODOS** los archivos necesarios para instalar el sistema de cobro en una cuenta del reseller.

---

## 📦 ESTRUCTURA DE ARCHIVOS

```
archivos/
│
├── 📄 ARCHIVOS DE CONFIGURACIÓN (Raíz del sitio)
│   ├── .env.example            → TEMPLATE para crear .env
│   ├── config.php              → Archivo de configuración (opcional)
│   ├── helpers.php             → Funciones helper (opcional)
│   └── index.php               → REFERENCIA de qué agregar
│
├── 📂 cobro-original/          
│   │   SISTEMA DE COBRO BASE - Copiar a controladores/modelos
│   │
│   ├── sistema_cobro.controlador.php  → public_html/controladores/
│   ├── sistema_cobro.modelo.php       → public_html/modelos/
│   └── cabezote.php                   → (backup, no copiar)
│
├── 📂 controladores-agregar/   
│   │   CONTROLADOR MERCADOPAGO - Copiar a controladores/
│   │
│   └── mercadopago.controlador.php    → public_html/controladores/
│
├── 📂 modelos-agregar/         
│   │   MODELOS NUEVOS - Copiar a modelos/
│   │
│   ├── mercadopago.modelo.php         → public_html/modelos/
│   └── conexion.php                   → public_html/modelos/ ⚠️ REEMPLAZAR
│
└── 📂 vistas-agregar/          
    │   VISTAS DEL SISTEMA DE COBRO - Copiar a vistas/modulos/
    │
    └── modulos/
        ├── cabezote-mejorado.php      → public_html/vistas/modulos/
        └── procesar-pago.php          → public_html/vistas/modulos/
```

---

## ✅ CHECKLIST DE ARCHIVOS A COPIAR

### 📂 En `public_html/controladores/`
- [ ] `sistema_cobro.controlador.php` ← desde `cobro-original/`
- [ ] `mercadopago.controlador.php` ← desde `controladores-agregar/`

### 📂 En `public_html/modelos/`
- [ ] `sistema_cobro.modelo.php` ← desde `cobro-original/`
- [ ] `mercadopago.modelo.php` ← desde `modelos-agregar/`
- [ ] `conexion.php` ⚠️ ← desde `modelos-agregar/` (REEMPLAZA existente)

### 📂 En `public_html/vistas/modulos/`
- [ ] `cabezote-mejorado.php` ← desde `vistas-agregar/modulos/`
- [ ] `procesar-pago.php` ← desde `vistas-agregar/modulos/`

### 📄 En `public_html/` (raíz)
- [ ] `.env` ⚠️ ← CREAR NUEVO (usar .env.example como base)
- [ ] `helpers.php` ← OPCIONAL (recomendado)

**TOTAL: 7 archivos + 1 .env nuevo = 8 archivos**

**Archivos NO copiar:**
- ❌ `config.php` (opcional, solo si no existe)
- ❌ `index.php` (solo como referencia)
- ❌ `cobro-original/cabezote.php` (backup, no usar)

---

## 🎯 PROCESO DE COPIADO (cPanel)

### PASO 1: Controladores

1. File Manager → `public_html/controladores/`
2. Upload:
   - `cobro-original/sistema_cobro.controlador.php`
   - `controladores-agregar/mercadopago.controlador.php`

### PASO 2: Modelos

1. File Manager → `public_html/modelos/`
2. Upload:
   - `cobro-original/sistema_cobro.modelo.php`
   - `modelos-agregar/mercadopago.modelo.php`
   - `modelos-agregar/conexion.php` ⚠️ Si pregunta sobrescribir: **SÍ**

### PASO 3: Vistas

1. File Manager → `public_html/vistas/modulos/`
2. Upload:
   - `vistas-agregar/modulos/cabezote-mejorado.php`
   - `vistas-agregar/modulos/procesar-pago.php`

### PASO 4: Crear .env

1. File Manager → `public_html/` (raíz)
2. **+ File** → Nombre: `.env`
3. Editar → Copiar contenido de `.env.example`
4. **Cambiar** `MOON_CLIENTE_ID=14` por el ID real del cliente
5. Save
6. Permisos: 600

---

## 📋 MAPEO DE ARCHIVOS

| Archivo Original | Copiar a | Acción |
|------------------|----------|--------|
| `cobro-original/sistema_cobro.controlador.php` | `controladores/` | Agregar |
| `cobro-original/sistema_cobro.modelo.php` | `modelos/` | Agregar |
| `controladores-agregar/mercadopago.controlador.php` | `controladores/` | Agregar |
| `modelos-agregar/mercadopago.modelo.php` | `modelos/` | Agregar |
| `modelos-agregar/conexion.php` | `modelos/` | **Reemplazar** ⚠️ |
| `vistas-agregar/modulos/cabezote-mejorado.php` | `vistas/modulos/` | Agregar |
| `vistas-agregar/modulos/procesar-pago.php` | `vistas/modulos/` | Agregar |
| `.env.example` | `.env` en raíz | Crear nuevo |

---

## ⚠️ ARCHIVOS IMPORTANTES

### ⚠️ conexion.php
**DEBE REEMPLAZARSE** el existente porque la nueva versión:
- ✅ Tiene método `conectarMoon()` (conexión a BD Moon)
- ✅ Usa `$_ENV` correctamente
- ✅ Valores por defecto actualizados

**Si NO lo reemplazas:** El sistema no se conectará a la BD Moon.

### ⚠️ .env
**DEBE CREARSE NUEVO** con el ID del cliente específico:
```env
MOON_CLIENTE_ID=14  ← Cambiar por el ID real
```

**Si NO lo creas:** El sistema usará ID 7 por defecto (incorrecto).

---

## 📝 NOTAS

### Archivos OPCIONALES:
- `helpers.php` - Solo si quieres usar la función `env()`
- `config.php` - Solo si no existe (para validaciones)
- `index.php` - Solo como REFERENCIA de qué agregar

### Archivos OBLIGATORIOS:
- ✅ Los 2 controladores
- ✅ Los 3 modelos (incluyendo conexion.php)
- ✅ Las 2 vistas
- ✅ El .env con MOON_CLIENTE_ID

---

## 🎯 DESPUÉS DE COPIAR

Además de copiar archivos, recuerda:

1. **Editar** `vistas/plantilla.php`:
   - Cambiar `include "modulos/cabezote.php";`
   - Por: `include "modulos/cabezote-mejorado.php";`

2. **Editar** `index.php` (si es necesario):
   - Agregar requires de sistema_cobro y mercadopago
   - Agregar ruta "procesar-pago"

3. **Probar** que funciona

---

**Para instrucciones detalladas, ver:** [INSTALACION-CPANEL.md](../INSTALACION-CPANEL.md)

