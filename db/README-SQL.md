# 📋 RESUMEN: Archivos SQL por Base de Datos

---

## 🗄️ TENEMOS 2 BASES DE DATOS

### 1️⃣ BD LOCAL: demo_db (localhost)
**Archivo SQL:** `db/EJECUTAR-EN-DEMO_DB.sql`

**Para qué:** Sistema POS completo

**Tablas que crea (18 tablas):**
- cajas
- caja_cierres
- categorias
- clientes (del POS local)
- clientes_cuenta_corriente (local)
- compras
- empresa
- pedidos
- presupuestos
- productos
- productos_historial
- proveedores
- proveedores_cuenta_corriente
- usuarios
- ventas
- ventas_factura

**Vistas:**
- productos_cambios

**Triggers:**
- prod_eliminar
- prod_insertar
- prod_modificar

**¿Cuándo usarlo?**
- Al instalar el sistema desde cero
- Para recuperar la estructura si se borra
- Para crear una copia de desarrollo

---

### 2️⃣ BD MOON: moondesa_moon (107.161.23.241)
**Archivo SQL:** `db/EJECUTAR-EN-MOONDESA_MOON.sql`

**Para qué:** Sistema de cobro Moon + MercadoPago

**Tablas que crea (3 tablas NUEVAS):**
- mercadopago_intentos
- mercadopago_pagos
- mercadopago_webhooks

**Vistas:**
- v_mercadopago_resumen
- v_mercadopago_pendientes

**Tablas que YA EXISTEN (no se crean):**
- clientes (de Moon Desarrollos)
- clientes_cuenta_corriente (de Moon)

**¿Cuándo usarlo?**
- ⚠️ **AHORA MISMO** - Para agregar las tablas de MercadoPago
- Primera vez que se instala el sistema de cobro
- Para agregar funcionalidad de pagos online

---

## 📝 INSTRUCCIONES DE USO

### Para BD LOCAL (demo_db):

```bash
# Si ya existe la BD, NO es necesario ejecutar
# Solo si estás creando desde cero

mysql -u demo_user -p demo_db < db/EJECUTAR-EN-DEMO_DB.sql
```

O en phpMyAdmin:
1. Conectar a: localhost
2. Seleccionar BD: demo_db (o crear nueva)
3. Pestaña SQL
4. Copiar contenido de: `db/EJECUTAR-EN-DEMO_DB.sql`
5. Ejecutar

---

### Para BD MOON (moondesa_moon): ⚠️ EJECUTAR ESTE

```bash
# ESTE SÍ HAY QUE EJECUTAR (agrega tablas de MercadoPago)
mysql -h 107.161.23.241 -u moondesa_moon -p moondesa_moon < db/EJECUTAR-EN-MOONDESA_MOON.sql
```

O en phpMyAdmin:
1. Conectar a: 107.161.23.241
2. Usuario: moondesa_moon
3. Seleccionar BD: moondesa_moon
4. Pestaña SQL
5. Copiar contenido de: `db/EJECUTAR-EN-MOONDESA_MOON.sql`
6. Ejecutar

**Guía detallada:** `db/INSTRUCCIONES-PHPMYADMIN.md`

---

## 📊 TABLA COMPARATIVA

| Característica | demo_db (LOCAL) | moondesa_moon (MOON) |
|---|---|---|
| **Servidor** | localhost | 107.161.23.241 |
| **Usuario** | demo_user | moondesa_moon |
| **Uso** | Sistema POS | Cobro Moon + MP |
| **Archivo SQL** | EJECUTAR-EN-DEMO_DB.sql | EJECUTAR-EN-MOONDESA_MOON.sql |
| **Estado** | ✅ Ya existe | ⚠️ Falta ejecutar |
| **Tablas totales** | ~18 | 5 (2 existentes + 3 nuevas) |
| **Acción requerida** | ❌ Ninguna (ya está) | ✅ Ejecutar SQL ahora |

---

## ✅ CHECKLIST COMPLETO

### BD Local (demo_db):
- [x] Tablas del POS ya existen
- [x] Archivo SQL creado (por si se necesita)
- [x] No requiere acción ahora

### BD Moon (moondesa_moon):
- [x] Archivo SQL creado
- [ ] **PENDIENTE:** Ejecutar SQL en servidor
- [ ] Verificar que se crearon las 3 tablas
- [ ] Verificar que se crearon las 2 vistas

---

## 🎯 PRÓXIMO PASO CRÍTICO

**EJECUTAR:** `db/EJECUTAR-EN-MOONDESA_MOON.sql` en la BD Moon

Esto creará las 3 tablas de MercadoPago necesarias para que el sistema de cobro funcione.

---

## 📁 ARCHIVOS SQL DISPONIBLES

```
db/
├── EJECUTAR-EN-DEMO_DB.sql           ← BD Local (POS)
├── EJECUTAR-EN-MOONDESA_MOON.sql     ← BD Moon (Cobro+MP) ⚠️ EJECUTAR
├── INSTRUCCIONES-PHPMYADMIN.md       ← Guía paso a paso
└── crear-tablas-mercadopago.sql      ← (mismo que MOONDESA_MOON)
```

---

**Fecha:** 20 Noviembre 2025
**Estado:** Archivos SQL listos para ambas bases de datos
