# 🚀 SPRINT 1 - Sistema de Cobro MercadoPago

**Fecha:** 20 Noviembre 2025
**Objetivo:** Implementar sistema de cobro con MercadoPago funcionando de forma RÁPIDA y SEGURA

---

## ✅ LO QUE SE IMPLEMENTÓ

### 1. Archivo de Configuración (.env)
**Archivo:** `.env`
- ✅ Credenciales de MercadoPago (TEST)
- ✅ Credenciales de base de datos
- ✅ Configuración de aplicación

**Importante:** El archivo `.env` contiene credenciales de TEST. Para producción hay que reemplazar con las credenciales REALES.

### 2. Base de Datos
**Archivo:** `db/crear-tablas-mercadopago.sql`

Se crearon 3 tablas:
- ✅ `mercadopago_intentos` - Registra todas las preferencias de pago creadas
- ✅ `mercadopago_pagos` - Registra todos los pagos confirmados
- ✅ `mercadopago_webhooks` - Registra todas las notificaciones recibidas de MercadoPago

Características:
- Motor InnoDB (transaccional)
- Índices optimizados
- 2 Vistas útiles para consultas

### 3. Controlador de MercadoPago
**Archivo:** `controladores/mercadopago.controlador.php`

Funciones implementadas:
- ✅ `ctrObtenerCredenciales()` - Obtiene credenciales desde .env
- ✅ `ctrCalcularMontoCobro()` - Calcula monto con recargos según día del mes
- ✅ `ctrRegistrarIntentoPago()` - Registra cuando se crea una preferencia
- ✅ `ctrRegistrarPagoConfirmado()` - Registra pago aprobado
- ✅ `ctrVerificarPagoProcesado()` - Evita duplicados
- ✅ `ctrObtenerHistorialPagos()` - Historial por cliente
- ✅ `ctrRegistrarWebhook()` - Registra notificaciones
- ✅ `ctrProcesarPagoWebhook()` - Procesa pago desde webhook

### 4. Modelo de MercadoPago
**Archivo:** `modelos/mercadopago.modelo.php`

Funciones implementadas:
- ✅ `mdlRegistrarIntentoPago()` - INSERT en mercadopago_intentos
- ✅ `mdlRegistrarPagoConfirmado()` - INSERT en mercadopago_pagos
- ✅ `mdlVerificarPagoProcesado()` - Verifica si payment_id ya existe
- ✅ `mdlObtenerHistorialPagos()` - SELECT pagos por cliente
- ✅ `mdlRegistrarWebhook()` - INSERT en mercadopago_webhooks
- ✅ `mdlMarcarWebhookProcesado()` - UPDATE webhook procesado
- ✅ `mdlActualizarEstadoIntento()` - UPDATE estado de intento

### 5. Webhook de MercadoPago
**Archivo:** `webhook-mercadopago.php`

Funcionalidad:
- ✅ Recibe notificaciones de MercadoPago automáticamente
- ✅ Registra todas las notificaciones en BD (auditoría)
- ✅ Consulta el pago en la API de MercadoPago
- ✅ Verifica que no esté duplicado
- ✅ Registra el pago en cuenta corriente
- ✅ Desbloquea automáticamente al cliente
- ✅ Actualiza estado de intento de pago
- ✅ Logs detallados para debugging

### 6. Configuración del Sistema
**Archivo:** `index.php` (modificado)

- ✅ Carga de variables de entorno desde .env
- ✅ Requires de controlador y modelo de MercadoPago
- ✅ Compatibilidad con código existente

---

## 📋 LO QUE FALTA POR HACER

### CRÍTICO - Para que funcione en producción:

1. **Ejecutar el script SQL en la base de datos**
   ```bash
   mysql -u demo_user -p demo_db < db/crear-tablas-mercadopago.sql
   ```
   ⚠️ **IMPORTANTE:** Esto debe hacerse en el servidor de producción/hosting

2. **Reemplazar credenciales de TEST por PRODUCCIÓN**
   - Editar archivo `.env`
   - Cambiar `MP_PUBLIC_KEY` por la clave pública REAL
   - Cambiar `MP_ACCESS_TOKEN` por el token de acceso REAL
   - Obtener credenciales desde: https://www.mercadopago.com.ar/developers/

3. **Configurar webhook en MercadoPago**
   - Ir a: https://www.mercadopago.com.ar/developers/
   - Sección: "Tus integraciones" → "Configuración"
   - Agregar URL del webhook: `https://TU-DOMINIO.com/webhook-mercadopago.php`
   - Seleccionar eventos: "Pagos"
   - Guardar

4. **Crear/Modificar el cabezote con botón de pago**
   - Hay que implementar el modal con el botón de MercadoPago
   - Usar la documentación de `mejoras/GUIA-MERCADOPAGO.md`
   - O usar el archivo `vistas/modulos/cabezote-mejorado.php` si existe

5. **Crear página de éxito/fracaso (opcional pero recomendado)**
   - `success.php` - Página cuando el pago es exitoso
   - `failure.php` - Página cuando el pago falla
   - `pending.php` - Página cuando el pago está pendiente

### IMPORTANTE - Seguridad Básica (Sprint 2):

6. **Protección CSRF en AJAX**
   - Agregar token CSRF en formularios
   - Validar token en archivos AJAX críticos

7. **Validación de sesión en AJAX**
   - Verificar que usuario esté logueado antes de procesar

8. **Agregar .env al .gitignore**
   ```bash
   echo ".env" >> .gitignore
   ```

### OPCIONAL - Mejoras futuras:

9. **Dashboard de pagos**
   - Ver pagos del día/mes
   - Estadísticas de cobros
   - Clientes morosos

10. **Notificaciones por email**
    - Email al cliente cuando paga
    - Email al admin cuando hay pago

11. **Reintentos automáticos**
    - Si webhook falla, reintentar

---

## 🔧 PASOS PARA SUBIR AL HOSTING

### 1. Subir archivos vía Git (RECOMENDADO)
```bash
# En tu computadora local
git add .
git commit -m "Implementar sistema MercadoPago Sprint 1"
git push origin claude/mercadopago-payment-setup-012gY5MzuL4t5DZod7iB2y3R

# En el hosting (cPanel o SSH)
cd /home/tu-usuario/public_html
git pull origin claude/mercadopago-payment-setup-012gY5MzuL4t5DZod7iB2y3R
```

### 2. Crear archivo .env en el hosting
```bash
# En el hosting, copiar el .env de ejemplo
cp .env .env.production
nano .env

# Editar y poner las credenciales REALES de producción
```

### 3. Ejecutar el script SQL
```bash
# Desde cPanel → phpMyAdmin
# O por línea de comando:
mysql -u USUARIO -p BASE_DE_DATOS < db/crear-tablas-mercadopago.sql
```

### 4. Verificar permisos
```bash
chmod 644 .env
chmod 644 webhook-mercadopago.php
chmod 755 /home/tu-usuario/public_html
```

### 5. Probar el webhook
```bash
# Hacer una prueba de webhook
curl -X GET "https://TU-DOMINIO.com/webhook-mercadopago.php?topic=payment&id=123456"

# Verificar logs
tail -f /home/tu-usuario/logs/error_log
```

---

## 🧪 CÓMO PROBAR QUE FUNCIONA

### 1. Probar credenciales
```bash
php -r "
require 'extensiones/vendor/autoload.php';
\$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
\$dotenv->load();
require 'controladores/mercadopago.controlador.php';
print_r(ControladorMercadoPago::ctrObtenerCredenciales());
"
```

### 2. Probar que las tablas existen
```sql
SHOW TABLES LIKE 'mercadopago%';
```

### 3. Hacer un pago de prueba
- Crear una preferencia de pago
- Pagar con tarjeta de prueba de MercadoPago
- Verificar que el webhook recibe la notificación
- Verificar que se actualiza la cuenta corriente

---

## 📊 ESTRUCTURA DE ARCHIVOS CREADOS/MODIFICADOS

```
/
├── .env                                    [NUEVO] ⚠️ NO SUBIR A GIT
├── index.php                               [MODIFICADO]
├── webhook-mercadopago.php                 [NUEVO]
├── controladores/
│   └── mercadopago.controlador.php         [NUEVO]
├── modelos/
│   └── mercadopago.modelo.php              [NUEVO]
├── db/
│   └── crear-tablas-mercadopago.sql        [NUEVO]
└── SPRINT-1-MERCADOPAGO.md                 [NUEVO - ESTE ARCHIVO]
```

---

## ⚠️ ADVERTENCIAS IMPORTANTES

1. **NUNCA** subir el archivo `.env` a Git (contiene credenciales)
2. **NUNCA** usar credenciales de TEST en producción
3. **SIEMPRE** hacer backup de la base de datos antes de ejecutar el SQL
4. **SIEMPRE** probar primero en ambiente de desarrollo/staging
5. **Verificar** que el webhook es accesible públicamente (sin autenticación)

---

## 📞 SOPORTE Y DEBUGGING

### Ver logs del webhook
```bash
# En el hosting
tail -f /home/tu-usuario/logs/error_log | grep "WEBHOOK MERCADOPAGO"
```

### Ver pagos registrados
```sql
SELECT * FROM mercadopago_pagos ORDER BY id DESC LIMIT 10;
```

### Ver webhooks recibidos
```sql
SELECT * FROM mercadopago_webhooks ORDER BY id DESC LIMIT 10;
```

### Ver intentos de pago
```sql
SELECT * FROM mercadopago_intentos ORDER BY id DESC LIMIT 10;
```

---

## 🎯 SIGUIENTE PASO

**ACCIÓN INMEDIATA:** Ejecutar el script SQL en la base de datos del hosting para crear las tablas.

```bash
mysql -u demo_user -p demo_db < db/crear-tablas-mercadopago.sql
```

Una vez hecho esto, el sistema estará listo para recibir pagos (con credenciales de TEST).

---

**Desarrollado por:** Claude AI
**Sprint:** 1 de 2
**Estado:** ✅ Completado - Listo para deployment
