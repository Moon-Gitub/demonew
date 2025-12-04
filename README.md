# 🌙 Moon POS - Sistema de Punto de Venta

Sistema completo de punto de venta (POS) con integración de **sistema de cobro automático** con MercadoPago.

---

## 📋 DESCRIPCIÓN

Sistema POS desarrollado en PHP con las siguientes características:

- ✅ Gestión completa de ventas, compras e inventario
- ✅ Manejo de clientes y proveedores con cuenta corriente
- ✅ Control de cajas y cierres
- ✅ Reportes y estadísticas
- ✅ Facturación electrónica AFIP
- ✅ **Sistema de cobro automático con MercadoPago** 🆕
- ✅ Integración con hosting reseller (multi-cuenta)

---

## 🚀 INICIO RÁPIDO

### Requisitos:
- PHP 7.4 o superior
- MySQL 5.7+ / MariaDB 10.3+
- Apache/Nginx con mod_rewrite
- Composer (para dependencias)

### Instalación:

```bash
# Clonar repositorio
git clone https://github.com/Moon-Gitub/demonew.git
cd demonew

# Instalar dependencias
cd extensiones
composer install

# Configurar base de datos
# Importar db/demo_db.sql en MySQL
# Configurar credenciales en modelos/conexion.php

# Acceder
https://tudominio.com
```

---

## 📦 SISTEMA DE COBRO (NUEVO)

El proyecto incluye un **sistema de cobro automático** integrado con MercadoPago:

### Características:
- 🌙 Modal automático según día del mes
- 💰 Recargos por mora (10%, 15%, 20%, 30%)
- 🔒 Bloqueo automático del sistema por falta de pago
- 📊 Desglose detallado de cargos
- 🔔 Webhook para notificaciones automáticas
- 🎯 Control individual de recargos por cliente
- 📱 Responsive design

### Instalación del Sistema de Cobro:

Ver documentación completa en:
📁 **`documentacion/instalacion_cobro/`**

**Guías disponibles:**
- ⭐ **INSTALACION-CPANEL.md** - Instalación vía cPanel (recomendado)
- 📋 **CHECKLIST-CPANEL.md** - Checklist paso a paso
- 📦 **GUIA-VENDOR-COMPOSER.md** - Manejo de librerías PHP
- 🔔 **CONFIGURAR-WEBHOOK-MERCADOPAGO.md** - Configurar webhook

---

## 📁 ESTRUCTURA DEL PROYECTO

```
demonew/
├── 📚 documentacion/                  # Toda la documentación
│   ├── instalacion_cobro/             # Paquete de instalación completo
│   │   ├── Guías de instalación
│   │   ├── Scripts y herramientas
│   │   ├── archivos/ (para copiar)
│   │   └── sql/ (scripts de BD)
│   └── Documentos técnicos
│
├── 🧪 testing/                        # Suite de tests
│   ├── Tests de configuración
│   ├── Tests de simulación por día
│   └── Tests de diagnóstico
│
├── 🔧 Sistema POS (producción)
│   ├── ajax/                          # Endpoints AJAX
│   ├── controladores/                 # Controladores MVC
│   ├── modelos/                       # Modelos y BD
│   ├── vistas/                        # Vistas y frontend
│   ├── cobro/                         # Sistema de cobro
│   ├── extensiones/                   # Librerías PHP (vendor)
│   ├── db/                            # Scripts SQL
│   ├── index.php                      # Punto de entrada
│   ├── config.php                     # Configuración
│   ├── helpers.php                    # Funciones helper
│   └── webhook-mercadopago.php        # Webhook MP
```

---

## 🔐 CONFIGURACIÓN

### Variables de Entorno (.env)

Crear archivo `.env` en la raíz con:

```env
# Base de datos local
DB_HOST=localhost
DB_NAME=tu_base_datos
DB_USER=tu_usuario
DB_PASS=tu_password

# Base de datos Moon (sistema de cobro)
MOON_DB_HOST=107.161.23.11
MOON_DB_NAME=cobrosposmooncom_db
MOON_DB_USER=cobrosposmooncom_dbuser
MOON_DB_PASS=tu_password_moon

# MercadoPago
MP_PUBLIC_KEY=APP_USR-tu-public-key
MP_ACCESS_TOKEN=APP_USR-tu-access-token

# ID del cliente (sistema de cobro)
MOON_CLIENTE_ID=14
```

---

## 🧪 TESTING

Suite completa de tests en la carpeta `testing/`:

### Tests de Configuración:
- `test-cliente-id.php` - Verificar ID del cliente
- `test-env.php` - Verificar variables de entorno
- `test-bd-cobros.php` - Verificar conexión BD Moon
- `test-saldo-cliente.php` - Verificar saldo y deuda
- `test-conexion-directa.php` - Test de conexión
- `clear-cache-and-test.php` - Limpiar caché

### Tests de Simulación:
- `test-dia-X.php` - Simular diferentes días del mes
- `test-dia-custom.php` - Día personalizado

Acceder a: `https://tudominio.com/testing/`

---

## 📖 DOCUMENTACIÓN COMPLETA

Toda la documentación está en `documentacion/`:

### Sistema de Cobro:
- **INSTALACION-CPANEL.md** - Guía de instalación vía cPanel
- **CONFIGURAR-WEBHOOK-MERCADOPAGO.md** - Configurar webhook
- **GUIA-VENDOR-COMPOSER.md** - Manejo de vendor/

### Documentación Técnica:
- **ARQUITECTURA-BASES-DATOS.md** - Arquitectura dual BD
- **SPRINT-1-MERCADOPAGO.md** - Sprint de desarrollo
- **TABLAS-POR-BASE-DE-DATOS.md** - Esquema de BD

---

## 🏢 INSTALACIÓN EN HOSTING RESELLER

Si tienes múltiples cuentas en un hosting reseller:

1. **Generar mapeo de clientes:**
   ```
   https://dominio.com/documentacion/instalacion_cobro/generar-mapeo-clientes.php
   ```

2. **Seguir guía de instalación:**
   ```
   documentacion/instalacion_cobro/INSTALACION-CPANEL.md
   ```

3. **Por cada cuenta:**
   - Copiar 7 archivos
   - Crear .env con ID del cliente
   - Verificar con tests

⏱️ **Tiempo:** 12-15 minutos por cuenta

---

## 🔔 WEBHOOK DE MERCADOPAGO

**URL a configurar en MercadoPago:**
```
https://tudominio.com/webhook-mercadopago.php
```

Ver guía completa en: `documentacion/CONFIGURAR-WEBHOOK-MERCADOPAGO.md`

---

## 📊 CARACTERÍSTICAS DEL SISTEMA DE COBRO

### Recargos por Mora:

| Días | Recargo | Modal | Estado |
|------|---------|-------|--------|
| 1-4 | 0% | Puede cerrar | Normal |
| 5-9 | 0% | Puede cerrar | Advertencia |
| 10-14 | 10% | Puede cerrar | Mora 1 |
| 15-19 | 15% | Puede cerrar | Mora 2 |
| 20-24 | 20% | Puede cerrar | Mora 3 |
| 25-26 | 30% | Puede cerrar | Mora Máxima |
| 27+ | 30% | **NO puede cerrar** | **BLOQUEADO** |

**Nota:** Los recargos se aplican SOLO sobre servicios mensuales POS.

---

## 🛠️ TECNOLOGÍAS UTILIZADAS

### Backend:
- PHP 7.4+
- MySQL/MariaDB
- PDO para base de datos
- Composer para dependencias

### Frontend:
- AdminLTE (template)
- Bootstrap 3
- jQuery
- DataTables
- SweetAlert2

### Integraciones:
- MercadoPago SDK
- Dotenv (variables de entorno)
- PhpSpreadsheet (Excel)
- TCPDF (PDFs)

---

## 👥 CRÉDITOS

**Desarrollado por:** Moon Desarrollos  
**Versión:** 2.0  
**Fecha:** Diciembre 2025  
**Licencia:** Propietario

---

## 📞 SOPORTE

Para documentación adicional, consultar:
- 📁 `documentacion/` - Documentación técnica completa
- 📁 `documentacion/instalacion_cobro/` - Paquete de instalación
- 🧪 `testing/` - Suite de tests y diagnósticos

---

**Sistema POS Moon** - Punto de venta profesional con sistema de cobro integrado 🌙

