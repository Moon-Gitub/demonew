# 📁 Archivos del Sistema de Cobro

Esta carpeta contiene todos los archivos necesarios para instalar el sistema de cobro en una cuenta.

---

## 📂 Estructura de Archivos

```
archivos/
├── generar-qr.php                    ← COPIAR A: public_html/
├── webhook-mercadopago.php           ← COPIAR A: public_html/
├── helpers.php                       ← COPIAR A: public_html/
├── config.php                        ← COPIAR A: public_html/ (opcional)
├── index.php                         ← REFERENCIA (para verificar)
├── .env.example                      ← REFERENCIA (ver template-env.txt)
├── LEEME-PRIMERO.txt                 ← Instrucciones rápidas
├── README-ARCHIVOS.md                ← Este archivo
│
├── controladores-agregar/            ← COPIAR A: public_html/controladores/
│   ├── sistema_cobro.controlador.php
│   └── mercadopago.controlador.php
│
├── modelos-agregar/                  ← COPIAR A: public_html/modelos/
│   ├── sistema_cobro.modelo.php
│   ├── mercadopago.modelo.php
│   └── conexion.php
│
└── vistas-agregar/                   ← COPIAR A: public_html/vistas/
    └── modulos/
        ├── cabezote-mejorado.php
        └── procesar-pago.php
```

---

## 📋 Descripción de Archivos

### **Archivos en Raíz (public_html/)**

#### `generar-qr.php` ⭐ NUEVO
- **Función:** Genera códigos QR para pago con celular
- **Tecnología:** PHP + QuickChart.io API
- **Sin dependencias:** No requiere librerías adicionales
- **Seguridad:** Valida URLs de MercadoPago
- **Cache:** 1 hora para mejor rendimiento

#### `webhook-mercadopago.php`
- **Función:** Receptor de notificaciones de MercadoPago
- **Procesa:** Pagos aprobados, rechazados, pendientes
- **Actualiza:** Cuenta corriente automáticamente
- **Seguridad:** Valida origen de notificaciones

#### `helpers.php`
- **Función:** Funciones auxiliares para variables de entorno
- **Incluye:** Función `env()` para leer `.env`
- **Compatible:** Funciona con diferentes configuraciones PHP

#### `config.php` (Opcional)
- **Función:** Validaciones de entorno
- **Uso:** Solo si no existe en la cuenta
- **Nota:** La mayoría de cuentas ya lo tienen

#### `index.php` (Referencia)
- **Función:** Solo para verificar requires
- **NO copiar:** Solo consultar para agregar líneas necesarias

---

### **Controladores (`controladores-agregar/`)**

#### `sistema_cobro.controlador.php`
- Maneja lógica de negocio del sistema de cobro
- Consulta clientes, saldos y movimientos
- Actualiza estados de clientes

#### `mercadopago.controlador.php`
- Integración con API de MercadoPago
- Cálculo de montos con recargos
- Registro de intentos de pago

---

### **Modelos (`modelos-agregar/`)**

#### `sistema_cobro.modelo.php`
- Acceso a datos de clientes en BD Moon
- Consultas de cuenta corriente
- Registro de movimientos

#### `mercadopago.modelo.php`
- Gestión de preferencias de pago
- Registro de intentos y confirmaciones
- Logs de webhooks

#### `conexion.php` ⚠️ SOBRESCRIBE EXISTENTE
- Conexión dual: BD Local + BD Moon
- Carga automática de `.env`
- Manejo de errores robusto

---

### **Vistas (`vistas-agregar/modulos/`)**

#### `cabezote-mejorado.php` ⭐ ARCHIVO PRINCIPAL
- Modal de cobro con diseño moderno
- Muestra saldo y cargos pendientes
- **Botón de pago Mercado Pago**
- **Código QR para pagar con celular** 📱
- Cálculo automático de recargos
- 100% responsive

#### `procesar-pago.php`
- Procesa respuesta de MercadoPago
- Muestra confirmación al cliente
- Maneja estados: aprobado, pendiente, rechazado

---

## 🎯 Orden de Instalación

### 1. Archivos en Raíz
```
public_html/
├── generar-qr.php
├── webhook-mercadopago.php
└── helpers.php
```

### 2. Controladores
```
public_html/controladores/
├── sistema_cobro.controlador.php
└── mercadopago.controlador.php
```

### 3. Modelos
```
public_html/modelos/
├── sistema_cobro.modelo.php
├── mercadopago.modelo.php
└── conexion.php (sobrescribir)
```

### 4. Vistas
```
public_html/vistas/modulos/
├── cabezote-mejorado.php
└── procesar-pago.php
```

### 5. Configuración
```
public_html/
└── .env (crear con template-env.txt)
```

---

## ✅ Verificación Rápida

Después de copiar todo, verifica:

- [ ] `generar-qr.php` en raíz
- [ ] `webhook-mercadopago.php` en raíz
- [ ] `helpers.php` en raíz
- [ ] 2 archivos en `/controladores/`
- [ ] 3 archivos en `/modelos/`
- [ ] 2 archivos en `/vistas/modulos/`
- [ ] `.env` configurado con ID correcto

---

## 🧪 Testing

Una vez instalado todo:

1. Acceder al sistema POS
2. Login como administrador
3. Buscar **"💳 Estado Cuenta"** en el navbar
4. Hacer clic y ver el modal
5. Verificar que muestre:
   - Nombre del cliente correcto
   - Saldo pendiente correcto
   - Botón "Pagar con Mercado Pago"
   - **Código QR visible** ✅

---

## 📞 Soporte

Consulta la guía completa:
**[../INSTALACION-CPANEL.md](../INSTALACION-CPANEL.md)**

---

**Última actualización:** Diciembre 2025  
**Versión:** 2.0 (con QR Code)
