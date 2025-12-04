# 💳 Sistema de Cobro Moon POS

## 🚀 INICIO RÁPIDO

### 1️⃣ **Lee la Guía de Instalación**
📖 **[INSTALACION-CPANEL.md](INSTALACION-CPANEL.md)**
- Guía completa paso a paso
- Screenshots y explicaciones detalladas
- Tiempo estimado: 15-20 minutos

### 2️⃣ **Usa el Checklist**
✅ **[CHECKLIST-CPANEL.md](CHECKLIST-CPANEL.md)**
- Lista de verificación visual
- Marca cada paso completado
- Asegura que no olvidas nada

### 3️⃣ **Usa el Template**
📄 **[template-env.txt](template-env.txt)**
- Template del archivo `.env`
- Solo cambiar el ID del cliente
- Copiar y pegar directo

---

## 📁 Archivos para Copiar

Todos los archivos listos para instalar están en:

**[archivos/](archivos/)**

### Estructura:
```
archivos/
├── generar-qr.php              ← Raíz
├── webhook-mercadopago.php     ← Raíz
├── helpers.php                 ← Raíz
├── config.php                  ← Raíz (opcional, si no existe)
├── index.php                   ← Raíz (solo para verificar)
├── controladores-agregar/      ← Copiar a /controladores/
│   ├── sistema_cobro.controlador.php
│   └── mercadopago.controlador.php
├── modelos-agregar/            ← Copiar a /modelos/
│   ├── sistema_cobro.modelo.php
│   ├── mercadopago.modelo.php
│   └── conexion.php (sobrescribir)
└── vistas-agregar/             ← Copiar a /vistas/
    └── modulos/
        ├── cabezote-mejorado.php
        └── procesar-pago.php
```

---

## 🗄️ Scripts SQL

**[sql/](sql/)**

1. `01_crear_tablas_mercadopago.sql` - Crear tablas necesarias
2. `02_verificar_instalacion.sql` - Verificar que todo esté OK
3. `03_agregar_control_recargos.sql` - Agregar campos de control

---

## ✨ Características

- ✅ Botón de pago con Mercado Pago
- ✅ **Código QR para pagar con celular** 📱
- ✅ Webhook automático
- ✅ Cálculo de recargos por mora
- ✅ Manejo de pagos parciales
- ✅ Diseño responsive y moderno
- ✅ Fuentes grandes y legibles

---

## 🧪 Testing

Después de instalar, usa estos tests:

```
/testing/test-cliente-id.php         ← Verifica que el ID esté bien
/testing/test-saldo-cliente.php      ← Verifica saldo y movimientos
/testing/clear-cache-and-test.php    ← Limpia cache y verifica .env
```

---

## ⚠️ Troubleshooting

**No aparece el botón Estado Cuenta:**
- Limpiar caché del navegador (Ctrl + Shift + Del)
- Verificar que `.env` tenga el ID correcto
- Ver logs: `tail -50 logs/error_log`

**Aparece "Cliente" en lugar del nombre real:**
- Verificar que el cliente exista en BD Moon
- Verificar que el campo `nombre` no esté vacío
- Ver logs para debug

**El QR no se genera:**
- Verificar que `generar-qr.php` esté en la raíz
- Verificar permisos del archivo (644)
- Ver logs de error

---

## 📞 Soporte Técnico

Para más información técnica, consulta:
**[../ARQUITECTURA-BASES-DATOS.md](../ARQUITECTURA-BASES-DATOS.md)**

---

**Moon Desarrollos** © 2025

