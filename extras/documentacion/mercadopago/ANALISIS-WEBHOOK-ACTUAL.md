# Análisis del Webhook Actual

## Estructura del Webhook

El webhook actual (`webhook-mercadopago.php`) ha sido completamente reescrito y tiene una estructura diferente a la versión anterior.

### Características Principales:

1. **Respuesta Rápida**: Responde 200 OK inmediatamente antes de procesar
2. **Logging Robusto**: Sistema de logging mejorado con directorios configurables
3. **Soporte Múltiple**: Soporta payment, merchant_order, order, point_integration, wallet_connect
4. **Rate Limiting**: Sistema básico de rate limiting
5. **Lock Anti-Duplicados**: Sistema de locks para evitar procesamiento duplicado

### Flujo de Procesamiento:

1. **Healthcheck** (GET sin parámetros) → Responde OK
2. **Debug endpoint** (GET con ?debug) → Muestra último evento
3. **Rate limiting** → Verifica límite de requests por minuto
4. **Responder OK rápido** → Responde 200 OK antes de procesar
5. **Parseo de evento** → Extrae topic, action, data_id
6. **Validación de firma** → Valida firma del webhook (permisiva)
7. **Lock anti-duplicados** → Crea lock para evitar procesamiento simultáneo
8. **Cargar dependencias** → Carga controladores y modelos
9. **Registrar webhook** → Guarda en `mercadopago_webhooks`
10. **Obtener credenciales** → Lee desde .env
11. **Obtener payment/order** → Consulta API de Mercado Pago
12. **Procesar pago** → Si está approved, registra en BD y cuenta corriente

## Posibles Problemas Identificados

### 1. **Problema con `return` en lugar de `exit`**
En la línea 404, 416, 510, 542, 571, 583, 592 hay varios `return` que deberían ser `exit` porque el webhook ya respondió 200 OK. Esto puede causar que el script continúe ejecutándose.

### 2. **Validación de Firma Permisiva**
La validación de firma es permisiva (no bloquea si falla), lo cual está bien, pero puede ocultar problemas de seguridad.

### 3. **Manejo de Errores**
Los errores se loguean pero el webhook siempre responde 200 OK, lo cual está bien para Mercado Pago, pero puede ocultar problemas.

### 4. **Búsqueda de Cliente**
La función `find_client_id` busca el cliente en varios lugares, pero si no lo encuentra, retorna 0, lo cual puede causar que el pago se registre sin cliente.

## Recomendaciones

1. Verificar que los `return` después de `send_ok_once` sean `exit` para evitar ejecución adicional
2. Revisar los logs del webhook para ver qué está pasando
3. Verificar que la función `find_client_id` esté funcionando correctamente
4. Asegurar que la transacción de BD se complete correctamente
