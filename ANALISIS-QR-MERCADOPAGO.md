# Análisis: Implementación QR Mercado Pago vs Documentación Oficial

## Decisión: QR DINÁMICO ✅

**Recomendación: Usar QR Dinámico**

### Ventajas del QR Dinámico:
1. ✅ **Monto incluido automáticamente** - El cliente no necesita ingresar el monto
2. ✅ **Más seguro** - Cada transacción tiene su propio QR único
3. ✅ **Mejor UX** - Proceso más rápido y simple
4. ✅ **API moderna** - Usa `/v1/orders` (recomendado por Mercado Pago)

### Desventajas del QR Estático:
1. ❌ Requiere que el cliente ingrese el monto manualmente
2. ❌ Menos seguro (mismo QR para múltiples transacciones)
3. ❌ API antigua (`merchant_orders`)

---

## Estado Actual de la Implementación

### ✅ Lo que está bien:
1. **QR Dinámico implementado** - Usa `mode: "dynamic"` en `/v1/orders`
2. **Webhook actualizado** - Maneja `type: "order"` y `action: "order.processed"`
3. **External reference** - Usa ID del cliente cuando está disponible
4. **Estructura de order** - Sigue la documentación oficial

### ⚠️ Lo que necesita corrección:

#### 1. Extracción de `qr_data`
**Problema**: Según la documentación, para QR dinámico, el `qr_data` puede estar en diferentes ubicaciones en la respuesta.

**Ubicaciones posibles según la doc**:
- `config.qr.qr_data` (más común)
- `qr_data` (directo en la order)
- Puede no venir en la respuesta inicial (solo después de consultar la order)

**Solución**: Mejorar la extracción y agregar consulta adicional si no viene.

#### 2. Validación de `x-signature` (Seguridad)
**Problema**: No hay validación de la firma del webhook.

**Según la documentación**:
- Mercado Pago envía `x-signature` en el header
- Formato: `ts=1742505638683,v1=ced36ab6d33566bb1e16c125819b8d840d6b8ef136b0b9127c76064466f5229b`
- Debe validarse con HMAC SHA256 usando la clave secreta de la aplicación
- Template: `id:[data.id_url];request-id:[x-request-id_header];ts:[ts_header];`
- El `data.id` debe estar en **minúsculas**

**Solución**: Agregar función de validación (opcional pero recomendado).

#### 3. Procesamiento de notificaciones
**Estado**: ✅ Ya maneja `order.processed` correctamente
**Mejora**: Asegurar que todos los estados se procesen (`order.canceled`, `order.refunded`, `order.expired`)

---

## Cambios Necesarios

### 1. Mejorar extracción de `qr_data`
```php
// Después de crear la order, si no viene qr_data, consultar la order nuevamente
if (!$qrData && isset($order['id'])) {
    // Consultar order para obtener qr_data
    $orderUrl = "https://api.mercadopago.com/v1/orders/{$order['id']}";
    $orderCompleta = consultarMP($orderUrl, $credenciales['access_token']);
    if ($orderCompleta && isset($orderCompleta['config']['qr']['qr_data'])) {
        $qrData = $orderCompleta['config']['qr']['qr_data'];
    }
}
```

### 2. Agregar validación de x-signature (opcional)
```php
function validarFirmaWebhook($xSignature, $xRequestId, $dataId, $secretKey) {
    // Extraer ts y v1 del header
    $parts = explode(',', $xSignature);
    $ts = null;
    $hash = null;
    foreach ($parts as $part) {
        list($key, $value) = explode('=', trim($part), 2);
        if ($key === 'ts') $ts = $value;
        if ($key === 'v1') $hash = $value;
    }
    
    // data.id debe estar en minúsculas
    $dataIdLower = strtolower($dataId);
    
    // Generar manifest
    $manifest = "id:$dataIdLower;request-id:$xRequestId;ts:$ts;";
    
    // Calcular HMAC
    $calculatedHash = hash_hmac('sha256', $manifest, $secretKey);
    
    return $calculatedHash === $hash;
}
```

### 3. Asegurar procesamiento de todos los estados
- `order.processed` ✅ Ya implementado
- `order.canceled` ⚠️ Verificar
- `order.refunded` ⚠️ Verificar
- `order.expired` ⚠️ Verificar

---

## Recomendación Final

**MANTENER QR DINÁMICO** - Es la mejor opción según la documentación oficial y las mejores prácticas.

**Mejoras a implementar**:
1. ✅ Mejorar extracción de `qr_data`
2. ⚠️ Agregar validación de `x-signature` (opcional pero recomendado)
3. ✅ Verificar procesamiento de todos los estados de order
