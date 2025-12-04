# 🔔 Configurar Webhook de MercadoPago

Guía rápida para configurar el webhook en el panel de MercadoPago.

---

## 🎯 ¿QUÉ ES EL WEBHOOK?

El webhook permite que **MercadoPago notifique automáticamente** cuando hay un pago, sin que el cliente tenga que volver a tu sitio.

**Sin webhook:**
- ❌ El pago solo se registra si el cliente vuelve al sitio
- ❌ Si cierra la ventana, el pago no se registra
- ❌ Menos confiable

**Con webhook:**
- ✅ MercadoPago notifica SIEMPRE que hay un pago
- ✅ El pago se registra automáticamente
- ✅ 100% confiable

---

## 🔧 PASO A PASO PARA CONFIGURAR

### PASO 1: Acceder al panel de MercadoPago

1. Ir a: https://www.mercadopago.com.ar/developers/panel/app
2. Login con tu cuenta de MercadoPago
3. Seleccionar tu aplicación

### PASO 2: Ir a Configuración de Webhooks

1. En el menú lateral, buscar **"Webhooks"**
2. Clic en **"Webhooks"**

### PASO 3: Configurar URL

En el campo **"URL de producción"**:

**❌ INCORRECTO:**
```
https://webhook-mercadopago.php
```

**✅ CORRECTO:**
```
https://newmoon.com/webhook-mercadopago.php
```

⚠️ **Cambiar** `newmoon.com` por tu dominio real.

**Ejemplos:**
- `https://cobros.posmoon.com.ar/webhook-mercadopago.php`
- `https://demo.posmoon.com.ar/webhook-mercadopago.php`
- `https://amarello.posmoon.com.ar/webhook-mercadopago.php`

### PASO 4: Seleccionar Eventos

En **"Eventos recomendados"**, marcar:
- ✅ **Pagos** (Payment) ← OBLIGATORIO

Dejar desmarcados:
- ❌ Alertas de fraude
- ❌ Card Updater
- ❌ Reclamos
- ❌ Contracargos
- ❌ Etc.

### PASO 5: Guardar

1. Clic en **"Guardar"** (abajo)
2. Esperar confirmación

### PASO 6: Probar

1. Clic en **"Nueva prueba"** (botón azul)
2. **Debe mostrar:**
   ```
   ✅ 200 - OK
   ```

Si muestra error:
- **404 Not Found:** La URL es incorrecta o el archivo no existe
- **405 Method Not Allowed:** Hacer git pull (ya corregí esto)
- **500 Internal Error:** Ver logs de PHP

---

## ⚠️ IMPORTANTE PARA RESELLER (MÚLTIPLES CUENTAS)

### PROBLEMA:

Todas tus cuentas usan **las mismas credenciales de MercadoPago**, entonces **solo puedes configurar UN webhook**.

### SOLUCIÓN: Webhook Centralizado

**Opción A (Recomendada): Webhook en cuenta principal**

1. Configurar webhook en la cuenta principal (ej: cobros.posmoon.com.ar)
2. URL: `https://cobros.posmoon.com.ar/webhook-mercadopago.php`
3. Este webhook recibe pagos de TODAS las cuentas
4. El `external_reference` indica a qué cliente pertenece

**Ventajas:**
- ✅ Un solo webhook para todas las cuentas
- ✅ Centralizado y fácil de mantener
- ✅ Logs centralizados
- ✅ Ya implementado así

**Desventajas:**
- ⚠️ Si esa cuenta cae, afecta a todas

---

**Opción B: Credenciales separadas por cliente**

Cada cliente tendría su propia cuenta de MercadoPago (no práctico y costoso).

---

## 🎯 RECOMENDACIÓN FINAL

### Para tu caso (reseller con ~20 cuentas):

1. **Elegir una cuenta central** para el webhook:
   - Sugerencia: `cobros.posmoon.com.ar` (si existe)
   - O: `newmoon.com`

2. **Configurar webhook** apuntando a esa cuenta:
   ```
   https://cobros.posmoon.com.ar/webhook-mercadopago.php
   ```

3. **En esa cuenta**, asegúrate de tener:
   - ✅ `webhook-mercadopago.php` (actualizado)
   - ✅ Todas las dependencias (vendor, controladores, modelos)
   - ✅ Conexión a BD Moon

4. **En las demás cuentas:**
   - Solo necesitan `procesar-pago.php` (para cuando el cliente vuelve)
   - El webhook centralizado manejará las notificaciones automáticas

---

## 🔍 VERIFICAR QUE FUNCIONA

Después de configurar:

1. Hacer un pago de prueba
2. Ver los logs:
   ```bash
   tail -f /home/usuario/logs/error_log | grep "WEBHOOK"
   ```

3. Verificar en BD:
   ```sql
   SELECT * FROM mercadopago_webhooks ORDER BY id DESC LIMIT 5;
   SELECT * FROM mercadopago_pagos ORDER BY id DESC LIMIT 5;
   ```

4. Debe mostrar:
   - ✅ Webhook recibido y registrado
   - ✅ Pago procesado y guardado
   - ✅ Cuenta corriente actualizada

---

## 📋 CHECKLIST DE WEBHOOK

- [ ] URL correcta con dominio completo
- [ ] Evento "Pagos" marcado
- [ ] Test de MercadoPago muestra 200 OK
- [ ] Archivo webhook-mercadopago.php existe en el servidor
- [ ] Hacer git pull origin main (para tener versión actualizada)
- [ ] Reiniciar servidor web
- [ ] Probar con pago real de test
- [ ] Verificar en logs que se reciba la notificación
- [ ] Verificar en BD que se guarde el pago

---

**URL correcta para configurar:**
```
https://newmoon.com/webhook-mercadopago.php
```

(O el dominio de la cuenta que elijas como central)

