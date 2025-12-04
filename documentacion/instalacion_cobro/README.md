# 💳 Sistema de Cobro Moon POS - Instalación

Sistema completo de cobro integrado con MercadoPago para el sistema POS.

## 📋 Documentación Disponible

- **[INSTALACION-CPANEL.md](INSTALACION-CPANEL.md)** - Guía completa de instalación paso a paso
- **[CHECKLIST-CPANEL.md](CHECKLIST-CPANEL.md)** - Lista de verificación para instalación
- **[template-env.txt](template-env.txt)** - Template del archivo .env

## 🎯 Características

✅ **Integración completa con MercadoPago**
- Botón de pago directo
- Código QR para pagar con celular
- Webhook automático para procesar pagos

✅ **Gestión de cobros automática**
- Cálculo automático de recargos por mora (10%, 15%, 30%)
- Manejo de pagos parciales
- Suspensión automática después del día 26

✅ **Interfaz moderna y responsive**
- Diseño limpio tipo móvil
- Fuentes grandes y legibles
- Funciona en desktop, tablet y móvil

✅ **Base de datos dual**
- BD Local: Sistema POS (clientes, ventas, productos)
- BD Moon: Sistema de cobro (cuenta corriente, pagos)

## 📁 Estructura de Archivos

```
documentacion/
├── ARQUITECTURA-BASES-DATOS.md  ← Arquitectura técnica
└── instalacion_cobro/
    ├── README.md                 ← Este archivo
    ├── INSTALACION-CPANEL.md     ← Guía de instalación
    ├── CHECKLIST-CPANEL.md       ← Checklist de instalación
    ├── template-env.txt          ← Template del .env
    ├── archivos/                 ← Archivos para copiar
    │   ├── generar-qr.php
    │   ├── helpers.php
    │   ├── webhook-mercadopago.php
    │   ├── controladores-agregar/
    │   ├── modelos-agregar/
    │   └── vistas-agregar/
    └── sql/                      ← Scripts SQL
        ├── 01_crear_tablas_mercadopago.sql
        ├── 02_verificar_instalacion.sql
        └── 03_agregar_control_recargos.sql
```

## 🚀 Instalación Rápida

1. Lee la **[guía completa de instalación](INSTALACION-CPANEL.md)**
2. Sigue el **[checklist](CHECKLIST-CPANEL.md)** paso a paso
3. Usa el **[template .env](template-env.txt)** para configurar

## 🧪 Testing

Después de instalar, prueba con:

```bash
# Tests disponibles en /testing/
testing/test-cliente-id.php         # Verifica MOON_CLIENTE_ID
testing/test-saldo-cliente.php      # Verifica saldo y movimientos
testing/clear-cache-and-test.php    # Limpia cache y verifica variables
```

## 📞 Soporte

Para dudas o problemas:
- Revisa los logs: `tail -100 /home/usuario/logs/error_log`
- Verifica el checklist completo
- Consulta ARQUITECTURA-BASES-DATOS.md para entender el sistema

---

**Moon Desarrollos** - Sistema POS con Cobro Integrado

