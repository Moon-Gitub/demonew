# Cómo Obtener el External ID del POS de Mercado Pago

## Opción 1: Si ya creaste el POS manualmente

Si creaste el POS "QR-Cobro" manualmente desde la aplicación móvil de Mercado Pago o desde el panel de desarrolladores, el `external_id` es el valor que **tú definiste** al crearlo.

**Ejemplos de external_id:**
- `QR-COBRO-001`
- `POS123456`
- `TIENDA001POS001`
- `QRCOBRO`

Si no recuerdas cuál fue, puedes usar cualquiera de las opciones siguientes.

## Opción 2: Obtenerlo desde la API (si tienes el pos_id)

Si ya tienes el `pos_id` guardado en la base de datos, puedes obtener el `external_id` haciendo una petición a la API:

```bash
curl -X GET "https://api.mercadopago.com/pos/{pos_id}" \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN"
```

La respuesta incluirá el campo `external_id`.

## Opción 3: Crear un nuevo POS con un external_id conocido

Si no tienes un POS creado o no recuerdas el external_id, puedes crear uno nuevo desde la aplicación móvil de Mercado Pago:

1. Abre la aplicación móvil de Mercado Pago
2. Ve a "Cobrar" o "Punto de venta"
3. Crea un nuevo punto de venta QR
4. Al crearlo, define un `external_id` que recuerdes (ej: `QR-COBRO-001`)
5. Anota ese `external_id` y guárdalo en la configuración de empresa

## Opción 4: Usar un external_id simple

Si no tienes restricciones, puedes usar un external_id simple como:
- `QRCOBRO`
- `POS001`
- `QR-001`

**Importante:** El `external_id` debe ser:
- Alfanumérico (solo letras y números, sin espacios ni caracteres especiales)
- Único para cada POS en tu cuenta
- Hasta 40 caracteres

## Cómo guardar el External ID en el sistema

1. Ve a "Configuración de Empresa"
2. En la sección "Configuración de Mercado Pago"
3. Busca el campo "External ID del POS (Opcional - Solo si no puede acceder a tiendas)"
4. Ingresa el `external_id` que obtuviste
5. Guarda los cambios

Una vez guardado, el sistema usará este `external_id` directamente sin necesidad de acceder a las tiendas, lo que evita el error 403.
