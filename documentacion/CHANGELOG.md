# 📝 Historial de Cambios - Moon POS

Registro de todas las versiones y cambios importantes del proyecto.

---

## [2.0.0] - Diciembre 2025

### 🎉 **VERSIÓN MAYOR - Sistema de Cobro Integrado**

### ✨ Agregado

#### Sistema de Cobro Completo:
- Modal automático de pago según día del mes
- Integración completa con MercadoPago
- Recargos por mora progresivos (10%, 15%, 20%, 30%)
- Bloqueo automático del sistema después del día 26
- Control individual de recargos por cliente
- Webhook para notificaciones automáticas
- Desglose detallado de cargos (servicios vs otros)

#### Base de Datos:
- Tabla `mercadopago_intentos` - Registro de intentos de pago
- Tabla `mercadopago_pagos` - Pagos confirmados
- Tabla `mercadopago_webhooks` - Notificaciones recibidas
- Campo `estado_bloqueo` en tabla `clientes`
- Campo `aplicar_recargos` en tabla `clientes`

#### Archivos Nuevos:
- `webhook-mercadopago.php` - Webhook de MercadoPago
- `controladores/mercadopago.controlador.php` - Controlador MP
- `controladores/sistema_cobro.controlador.php` - Controlador cobro
- `modelos/mercadopago.modelo.php` - Modelo MP
- `modelos/sistema_cobro.modelo.php` - Modelo cobro
- `vistas/modulos/cabezote-mejorado.php` - Cabezote con sistema de cobro
- `vistas/modulos/procesar-pago.php` - Procesamiento de pagos
- `helpers.php` - Funciones helper (env())
- `config.php` - Configuración centralizada

#### Documentación:
- Carpeta `documentacion/` creada con toda la documentación
- Carpeta `documentacion/instalacion_cobro/` - Paquete completo
- 13 guías de instalación y configuración
- Suite completa de tests (20+ archivos)
- Scripts de instalación masiva

### 🔧 Modificado

- `index.php` - Carga de .env y requires de sistema de cobro
- `modelos/conexion.php` - Método `conectarMoon()` agregado
- `vistas/plantilla.php` - Include de cabezote-mejorado
- Estructura del proyecto reorganizada

### 🐛 Bugs Corregidos

- Fix: `$_ENV` vs `getenv()` en servidores con `variables_order=GPCS`
- Fix: `lastInsertId()` llamado en PDOStatement en lugar de PDO
- Fix: `execute() on null` en mdlActualizarClientesCobro
- Fix: Orden de carga de .env vs config.php
- Fix: Webhook responde correctamente a tests de MercadoPago
- Fix: Conexiones a BD usando valores por defecto correctos

### 🔒 Seguridad

- Variables de entorno en archivo .env
- Credenciales no hardcodeadas en código
- Webhook con validación de duplicados
- Logs de auditoría completos

---

## [1.0.0] - Pre-Diciembre 2025

### Características Base

- Sistema POS completo
- Gestión de ventas y compras
- Control de inventario
- Clientes y proveedores
- Cuenta corriente
- Facturación AFIP
- Reportes básicos

---

## 🎯 Roadmap Futuro

### v2.1 (Próximo)
- [ ] Dashboard de pagos de clientes
- [ ] Notificaciones por email cuando hay pago
- [ ] Estadísticas de cobros mensuales
- [ ] Reportes de clientes morosos
- [ ] Instalador automático (wizard)

### v2.2
- [ ] API REST para integraciones
- [ ] App móvil para consultas
- [ ] Multi-empresa desde un solo sistema
- [ ] Backup automático a la nube

### v3.0
- [ ] Migración a framework moderno (Laravel/Symfony)
- [ ] Frontend con Vue.js/React
- [ ] Microservicios
- [ ] Escalabilidad mejorada

---

## 📊 Estadísticas

### Versión 2.0:
- **Archivos creados:** 50+
- **Líneas de código:** ~5,000
- **Líneas de documentación:** ~4,000
- **Tests creados:** 20+
- **Bugs corregidos:** 8

### Instalaciones:
- **Cuentas activas:** 2 (newmoon, amarello)
- **Pagos procesados:** 2 exitosos
- **Webhook:** ✅ Funcionando

---

## 🔗 Enlaces

- **Repositorio:** https://github.com/Moon-Gitub/demonew
- **Documentación:** `/documentacion/`
- **Tests:** `/testing/`
- **Instalación:** `/documentacion/instalacion_cobro/`

---

**Mantenido por:** Moon Desarrollos  
**Última actualización:** Diciembre 4, 2025

