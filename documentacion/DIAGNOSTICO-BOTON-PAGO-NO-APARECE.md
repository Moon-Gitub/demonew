# Diagnóstico: El botón de pago no aparece en ningún sistema

## Resumen

El botón "Pagar con Mercado Pago" puede no mostrarse por varias causas. Este documento lista las más frecuentes y cómo verificarlas.

---

## 1. Base de datos Moon no disponible (causa más frecuente)

**Síntoma:** No ves ni el dropdown "Estado Cuenta" ni el modal de pago. El cabezote se carga normal pero sin sistema de cobro.

**Error típico en logs:**
```
Error conectando a BD Moon: SQLSTATE[28000] [1045] Access denied for user 'cobrosposmooncom_dbuser'@'...'
=== SISTEMA DE COBRO NO DISPONIBLE ===
Error: BD Moon no disponible
=== CARGANDO CABEZOTE NORMAL ===
```

**Qué ocurre:** Si falla la conexión a la BD Moon (`cobrosposmooncom_db`), el sistema carga el cabezote de respaldo (`cabezote.php`), que **no incluye** el sistema de cobro ni el botón de pago.

**Solución:**
- Revisar en `.env` las variables de conexión Moon:
  - `MOON_DB_HOST`
  - `MOON_DB_NAME`
  - `MOON_DB_USER`
  - `MOON_DB_PASS`
- Confirmar con el hosting que el servidor puede conectarse al host de la BD Moon (puerto 3306).
- En algunos hostings (ej. Hostinger) hay restricciones de conexión saliente; verificar con soporte.

---

## 2. Error al crear la preferencia de MercadoPago

**Síntoma:** Ves el dropdown "Estado Cuenta" y el botón "Pagar Ahora", pero al abrir el modal no aparece el botón azul de MercadoPago.

**Error típico en logs:**
```
ERROR creando preferencia MP: [mensaje del error]
```
O:
```
MPResponse::__construct(): Argument #2 ($content) must be of type array, null given
```

**Qué ocurre:** La API de MercadoPago devuelve un error o una respuesta que el SDK no puede interpretar. En ese caso `$preference` queda en `null` y el botón no se renderiza.

**Posibles causas:**
- Credenciales MP inválidas o expiradas.
- Access token de prueba usado en producción (o al revés).
- Monto 0 o items inválidos en la preferencia.
- Problemas de red o timeout hacia la API de MercadoPago.

**Solución:**
- Revisar credenciales en `.env`:
  - `MP_PUBLIC_KEY`
  - `MP_ACCESS_TOKEN`
- Si usas credenciales desde la tabla `empresa` (mp_public_key, mp_access_token), verificar que estén correctas.
- Revisar el `error_log` para el mensaje exacto del error.

---

## 3. Cliente sin deuda (saldo ≤ 0)

**Síntoma:** El dropdown "Estado Cuenta" puede mostrarse, pero el modal no muestra el botón de pago.

**Qué ocurre:** El botón solo se muestra cuando `$muestroModal` es `true` y existe `$preference`. Si el cliente está al día (saldo ≤ 0), no se muestra el botón.

**Solución:** Es el comportamiento esperado. Para probar, usar un cliente con saldo pendiente en la BD Moon.

---

## 4. Usuario no es Administrador

**Síntoma:** No ves el dropdown "Estado Cuenta" en el cabezote.

**Qué ocurre:** El bloque del sistema de cobro está dentro de:
```php
<?php if($_SESSION["perfil"] == "Administrador") { ?>
```

**Solución:** Iniciar sesión con un usuario con perfil Administrador.

---

## 5. Clave pública vacía o inválida

**Síntoma:** El modal se abre pero el botón de MercadoPago no se renderiza; puede haber errores en la consola del navegador.

**Qué ocurre:** El SDK de MercadoPago necesita una clave pública válida. Si `clavePublicaMP` está vacía o es incorrecta, `mp.checkout()` puede fallar.

**Solución:**
- Verificar que `MP_PUBLIC_KEY` esté definida en `.env` o en la tabla `empresa`.
- Revisar la consola del navegador (F12) para errores de JavaScript.

---

## 6. SDK de MercadoPago bloqueado

**Síntoma:** El modal se abre pero el botón no aparece; en la consola puede haber errores de CORS o de carga de script.

**Qué ocurre:** El script `https://sdk.mercadopago.com/js/v2` no se carga (bloqueado por firewall, adblocker, política de seguridad, etc.).

**Solución:**
- Desactivar extensiones que bloqueen scripts (adblockers).
- Verificar que el dominio esté permitido en las políticas de seguridad del navegador o del servidor.

---

## Checklist de verificación rápida

| Verificación | Cómo comprobarlo |
|--------------|------------------|
| BD Moon conecta | Revisar `error_log`; no debe aparecer "BD Moon no disponible" |
| Credenciales MP | `.env` con `MP_PUBLIC_KEY` y `MP_ACCESS_TOKEN` correctos |
| Usuario Administrador | Iniciar sesión con perfil Administrador |
| Cliente con deuda | En BD Moon, el cliente debe tener saldo > 0 |
| Script MP carga | En DevTools → Network, ver que `sdk.mercadopago.com/js/v2` cargue bien |
| Consola sin errores | En DevTools → Console, no debe haber errores de MercadoPago |

---

## Script de diagnóstico

Puedes ejecutar el script de prueba (si existe):

```
extras/tests/diagnostico-sistema-cobro.php
```

Este script comprueba conexión a BD Moon, credenciales MP y otros puntos críticos.

---

## Archivos relevantes

- `vistas/modulos/cabezote-mejorado.php` – Cabezote con sistema de cobro (principal)
- `vistas/modulos/cabezote.php` – Cabezote de respaldo (sin cobro)
- `controladores/mercadopago.controlador.php` – `ctrObtenerCredenciales()`
- `modelos/conexion.php` – `conectarMoon()`
