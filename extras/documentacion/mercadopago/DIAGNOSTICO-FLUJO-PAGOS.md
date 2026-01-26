# DIAGNÓSTICO COMPLETO DEL FLUJO DE PAGOS MERCADOPAGO

## Situación Actual

✅ **Funciona:** Prevención de duplicados en `mercadopago_intentos`  
❌ **NO funciona:** Registro de pagos en `mercadopago_pagos`

Solo hay 1 pago registrado del 8 de enero (payment_id: 141260593212).
Pagos nuevos NO se están registrando.

## Análisis del Flujo Completo

### FLUJO ESPERADO:

```
1. Usuario abre página
   ↓
2. Se crea/reutiliza preferencia (✅ OK - sin duplicados)
   ↓
3. Usuario paga con QR/Botón
   ↓
4. MercadoPago aprueba el pago
   ↓
5A. WEBHOOK recibe notificación → Registra en mercadopago_pagos
    O
5B. VERIFICACIÓN AUTOMÁTICA (cada 3seg) → Registra en mercadopago_pagos
   ↓
6. Actualiza cuenta corriente del cliente
   ↓
7. Desbloquea cliente
   ↓
8. Muestra confirmación y recarga página
```

### PROBLEMA IDENTIFICADO:

**Paso 5A (WEBHOOK):**
- ✅ Recibe notificaciones
- ❌ Ignora IDs de prueba (123456) - CORRECTO
- ❌ Pero NO está procesando payment_ids REALES tampoco
- **Causa:** Después de ignorar test IDs, el flujo termina con `exitOk()` y nunca llega al código de procesamiento real

**Paso 5B (VERIFICACIÓN AUTOMÁTICA):**
- ✅ Código agregado en cabezote-mejorado.php
- ✅ Endpoint ajax/verificar-pago-preference.ajax.php creado
- ❌ **Posible problema:** Solo se activa cuando se ABRE el modal
- ❌ Si el usuario cierra el modal antes de pagar, no verifica
- ❌ Si el usuario paga desde el botón de MP (fuera del modal), no verifica

## SOLUCIONES NECESARIAS:

### 1. WEBHOOK: Debe procesar IDs reales
El webhook actualmente hace esto:
```php
if (ID es 123456 o <9 dígitos) {
    exitOk('Ignorado');  // ← SALE AQUÍ
}
// El código de abajo NUNCA se ejecuta para IDs reales
```

**DEBE hacer:**
```php
if (ID es 123456 o <9 dígitos) {
    exitOk('Ignorado');
}
// CONTINUAR procesando IDs reales (9+ dígitos)
```

### 2. VERIFICACIÓN AUTOMÁTICA: Debe ejecutarse siempre
Actualmente solo verifica si el modal está abierto.

**DEBE:**
- Verificar si hay preferencia pendiente
- Iniciar verificación automáticamente en background
- NO depender de que el modal esté abierto

### 3. FALLBACK: Verificación al volver a cargar
Si el usuario:
1. Paga
2. Cierra navegador
3. Vuelve después

El sistema debe detectar que tiene una preferencia pendiente y verificar si fue pagada.

## ARCHIVOS A MODIFICAR:

1. ✅ `webhook-mercadopago.php` - Ya responde 200 OK
2. ❌ Falta: Continuar procesamiento después de ignorar test IDs
3. ❌ `vistas/modulos/cabezote-mejorado.php` - Verificación solo con modal abierto
4. ❌ Falta: Verificación al cargar página si hay preferencia pendiente

## PRÓXIMOS PASOS:

1. Modificar webhook para que NO salga después de ignorar test ID
2. Agregar verificación automática al cargar página (no solo modal)
3. Agregar logs detallados en cada punto del flujo
4. Probar con pago real y verificar que se registra
