# Solución: Webhook muestra 0% Notificaciones entregadas

## Problema Identificado

Mercado Pago muestra **"0% Notificaciones entregadas"** porque las notificaciones no están llegando correctamente al servidor.

## Correcciones Aplicadas

### 1. **Problema crítico: Lectura doble de `php://input`**
   - **Antes**: El webhook leía `php://input` dos veces (una al inicio para el log y otra en el código)
   - **Ahora**: Se lee UNA SOLA VEZ al inicio y se guarda en `$rawInput`
   - **Impacto**: Esto puede causar que el webhook no procese correctamente las notificaciones

### 2. **Mejora en logging**
   - Ahora se registran los headers completos
   - Se registra la URL, método, GET params y el input completo
   - Facilita el debugging

### 3. **Mejora en respuesta HTTP**
   - La función `exitOk()` ahora asegura que siempre se responde 200 OK
   - Se fuerza el flush de la respuesta
   - Compatible con FastCGI

### 4. **Mejora en lectura de headers**
   - Compatible con diferentes servidores (Apache, Nginx, etc.)
   - Lee headers tanto con prefijo `HTTP_` como directamente

## Pasos para Verificar

### 1. Verificar que el webhook es accesible

Ejecuta el script de diagnóstico:
```bash
php test-webhook-accesibilidad.php
```

O accede desde el navegador:
```
https://newmoon.posmoon.com.ar/test-webhook-accesibilidad.php
```

### 2. Probar el webhook manualmente

Desde el panel de Mercado Pago:
1. Ve a **Tus integraciones** > **Tu aplicación** > **Webhooks**
2. Haz clic en **"Simular notificación"**
3. Verifica que recibas una respuesta exitosa

### 3. Verificar logs

Revisa el archivo de log:
```bash
tail -f /tmp/webhook_raw.log
```

O desde el servidor:
```bash
tail -n 50 /tmp/webhook_raw.log
```

### 4. Verificar configuración en Mercado Pago

Asegúrate de que:
- ✅ URL configurada: `https://newmoon.posmoon.com.ar/webhook-mercadopago.php`
- ✅ Eventos activados: **"Pagos"** y **"Order (Mercado Pago)"**
- ✅ La URL es accesible desde internet (no localhost)

## Si el problema persiste

### Verificar accesibilidad desde internet

El webhook debe ser accesible desde internet. Verifica:

1. **Desde tu navegador**:
   ```
   https://newmoon.posmoon.com.ar/webhook-mercadopago.php
   ```
   Deberías ver: `{"error":false,"message":"Webhook activo y funcionando"}`

2. **Desde línea de comandos** (si tienes acceso):
   ```bash
   curl -X GET https://newmoon.posmoon.com.ar/webhook-mercadopago.php
   ```

3. **Verificar firewall**:
   - Asegúrate de que el puerto 443 (HTTPS) esté abierto
   - Mercado Pago necesita poder hacer POST a tu servidor

### Verificar certificado SSL

Mercado Pago requiere HTTPS válido. Verifica:
- El certificado SSL es válido
- No está expirado
- El dominio coincide

### Verificar logs del servidor

Revisa los logs de error de PHP:
```bash
tail -f /var/log/php_errors.log
# O según tu configuración
tail -f error_log
```

## Próximos Pasos

1. **Ejecuta el script de diagnóstico** para verificar accesibilidad
2. **Usa "Simular notificación"** en Mercado Pago para probar
3. **Revisa los logs** en `/tmp/webhook_raw.log`
4. **Realiza un pago de prueba** y verifica si llega la notificación

## Nota Importante

Si después de estas correcciones el problema persiste, puede ser:
- Problema de red/firewall bloqueando peticiones de Mercado Pago
- Problema con el certificado SSL
- El servidor no es accesible desde internet
- Configuración incorrecta de la URL en Mercado Pago

En estos casos, contacta con el administrador del servidor o el proveedor de hosting.
