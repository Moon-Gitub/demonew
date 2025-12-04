# 💳 Sistema de Cobro Moon POS

## 🚀 INICIO RÁPIDO

### 1️⃣ **Descarga e Instala**
📥 **[DESCARGA-E-INSTALA.md](DESCARGA-E-INSTALA.md)** ⭐ EMPIEZA AQUÍ
- Cómo descargar de GitHub
- Proceso completo paso a paso
- 20 minutos desde cero

### 2️⃣ **Lee la Guía Detallada**
📖 **[INSTALACION-CPANEL.md](INSTALACION-CPANEL.md)**
- Guía completa con explicaciones
- Screenshots y detalles
- Referencia durante la instalación

### 3️⃣ **Usa el Checklist**
✅ **[CHECKLIST-CPANEL.md](CHECKLIST-CPANEL.md)**
- Lista de verificación visual
- Marca cada paso completado
- Asegura que no olvidas nada

### 4️⃣ **Usa el Template**
📄 **[template-env.txt](template-env.txt)**
- Template del archivo `.env`
- Solo cambiar el ID del cliente
- Copiar y pegar directo

---

## 🔧 Compatibilidad

### ✅ **Funciona en CUALQUIER Sistema POS**

El sistema de cobro es **100% compatible** con:
- ✅ Sistemas completos (AFIP + Cotización + Cobro)
- ✅ Sistemas básicos (solo Cobro)
- ✅ Sistemas con AFIP pero sin Cotización
- ✅ Sistemas con Cotización pero sin AFIP

**El código detecta automáticamente** qué funcionalidades tiene tu sistema y se adapta. No genera errores si faltan funcionalidades. 🎯

---

## 📁 Archivos para Copiar

### **Opción 1: ZIPs Pre-comprimidos (MÁS RÁPIDO) ⭐**

**[zips/](zips/)** - Archivos organizados en ZIPs

- `1-archivos-raiz.zip` (5 KB)
- `2-controladores.zip` (3 KB)
- `3-modelos.zip` (4 KB)
- `4-vistas.zip` (12 KB)
- `5-sistema-completo.zip` (24 KB) - Todo en uno

**Ventaja:** Subir 1 ZIP y extraer en cPanel (3x más rápido)

### **Opción 2: Archivos Individuales**

**[archivos/](archivos/)** - Archivos sueltos para copiar uno por uno

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

