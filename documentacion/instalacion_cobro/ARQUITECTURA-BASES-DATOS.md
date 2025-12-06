# 🗄️ Arquitectura de Bases de Datos

## 📋 RESUMEN

El sistema utiliza **DOS bases de datos separadas**:

1. **Base de Datos LOCAL** - Sistema POS (ventas, productos, clientes, etc.)
2. **Base de Datos MOON** - Sistema de Cobro con MercadoPago (remota)

---

## 🏗️ ARQUITECTURA

```
┌──────────────────────────────────────┐
│     SISTEMA POS (demo_db)            │
│     localhost                        │
├──────────────────────────────────────┤
│  • usuarios                          │
│  • productos                         │
│  • categorias                        │
│  • ventas                            │
│  • compras                           │
│  • clientes                          │
│  • proveedores                       │
│  • cajas                             │
│  • etc...                            │
└──────────────────────────────────────┘
            ↕
    Conexion::conectar()


┌──────────────────────────────────────┐
│  SISTEMA COBRO MOON (moondesa_moon)  │
│  107.161.23.241 (remoto)             │
├──────────────────────────────────────┤
│  • clientes                          │
│  • clientes_cuenta_corriente         │
│  • mercadopago_intentos       [NEW]  │
│  • mercadopago_pagos          [NEW]  │
│  • mercadopago_webhooks       [NEW]  │
└──────────────────────────────────────┘
            ↕
    Conexion::conectarMoon()
```

---

## 🔧 CONFIGURACIÓN

### Archivo `.env`

```env
# ==============================================
# BASE DE DATOS LOCAL - SISTEMA POS
# ==============================================
DB_HOST=localhost
DB_NAME=demo_db
DB_USER=demo_user
DB_PASS=aK4UWccl2ceg
DB_CHARSET=UTF8MB4

# ==============================================
# BASE DE DATOS MOON - SISTEMA DE COBRO
# ==============================================
MOON_DB_HOST=107.161.23.241
MOON_DB_NAME=moondesa_moon
MOON_DB_USER=moondesa_moon
MOON_DB_PASS=F!b+hn#i3Vk-
```

---

## 📂 ¿DÓNDE VAN LAS TABLAS DE MERCADOPAGO?

### ⚠️ IMPORTANTE: Las tablas de MercadoPago van en la BD MOON (remota)

```bash
# Ejecutar el script SQL en la BD MOON, NO en la local
mysql -h 107.161.23.241 -u moondesa_moon -p moondesa_moon < db/crear-tablas-mercadopago.sql
```

**¿Por qué?**
- Porque el sistema de cobro es de Moon Desarrollos
- Los clientes y su cuenta corriente ya están en esa BD
- Las tablas de MercadoPago deben estar junto a los clientes

---

## 🔌 CONEXIONES EN EL CÓDIGO

### modelos/conexion.php

```php
class Conexion {

    /**
     * Conexión a BD LOCAL del sistema POS
     * Usa: DB_HOST, DB_NAME, DB_USER, DB_PASS
     */
    static public function conectar() {
        $host = getenv('DB_HOST') ?: 'localhost';
        $db = getenv('DB_NAME') ?: 'demo_db';
        $user = getenv('DB_USER') ?: 'demo_user';
        $pass = getenv('DB_PASS') ?: 'aK4UWccl2ceg';

        return new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    }

    /**
     * Conexión a BD MOON (remota) para sistema de cobro
     * Usa: MOON_DB_HOST, MOON_DB_NAME, MOON_DB_USER, MOON_DB_PASS
     */
    static public function conectarMoon() {
        $host = getenv('MOON_DB_HOST') ?: 'localhost';
        $db = getenv('MOON_DB_NAME') ?: 'demo_db';
        $user = getenv('MOON_DB_USER') ?: 'demo_user';
        $pass = getenv('MOON_DB_PASS') ?: 'aK4UWccl2ceg';

        return new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    }
}
```

---

## 📋 ¿QUÉ MODELO USA QUÉ CONEXIÓN?

### BD LOCAL (Conexion::conectar())
- ✅ `ModeloUsuarios`
- ✅ `ModeloProductos`
- ✅ `ModeloCategorias`
- ✅ `ModeloVentas`
- ✅ `ModeloCompras`
- ✅ `ModeloClientes` (clientes locales del POS)
- ✅ `ModeloProveedores`
- ✅ `ModeloCajas`
- ✅ Etc...

### BD MOON (Conexion::conectarMoon())
- ✅ `ModeloSistemaCobro` (clientes Moon, cuenta corriente)
- ✅ `ModeloMercadoPago` (intentos, pagos, webhooks)

---

## 🧪 CÓMO PROBAR LAS CONEXIONES

### Probar conexión LOCAL

```bash
php -r "
require 'extensiones/vendor/autoload.php';
\$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
\$dotenv->load();
require 'modelos/conexion.php';
\$conn = Conexion::conectar();
echo 'Conexión LOCAL exitosa!' . PHP_EOL;
"
```

### Probar conexión MOON

```bash
php -r "
require 'extensiones/vendor/autoload.php';
\$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
\$dotenv->load();
require 'modelos/conexion.php';
\$conn = Conexion::conectarMoon();
echo 'Conexión MOON exitosa!' . PHP_EOL;
"
```

### Verificar tablas en BD MOON

```bash
mysql -h 107.161.23.241 -u moondesa_moon -p -e "
USE moondesa_moon;
SHOW TABLES LIKE 'mercadopago%';
"
```

Deberías ver:
```
mercadopago_intentos
mercadopago_pagos
mercadopago_webhooks
```

---

## ⚠️ ERRORES COMUNES

### Error: "Access denied for user..."

**Problema:** Las credenciales de Moon en `.env` están incorrectas

**Solución:**
```bash
# Verificar credenciales en .env
cat .env | grep MOON

# Probar conexión manual
mysql -h 107.161.23.241 -u moondesa_moon -p
```

### Error: "Table 'demo_db.mercadopago_intentos' doesn't exist"

**Problema:** Las tablas se crearon en la BD local en lugar de la Moon

**Solución:**
```bash
# Eliminar de BD local (si existen)
mysql -u demo_user -p demo_db -e "
DROP TABLE IF EXISTS mercadopago_intentos;
DROP TABLE IF EXISTS mercadopago_pagos;
DROP TABLE IF EXISTS mercadopago_webhooks;
"

# Crear en BD Moon
mysql -h 107.161.23.241 -u moondesa_moon -p moondesa_moon < db/crear-tablas-mercadopago.sql
```

### Error: "Can't connect to MySQL server on '107.161.23.241'"

**Problema:** El servidor remoto no acepta conexiones desde tu IP

**Solución:**
- Verificar firewall del servidor Moon
- Verificar que tu IP esté autorizada
- Contactar al administrador del servidor

---

## 🔒 SEGURIDAD

### Backup de Ambas Bases

```bash
# Backup BD local
mysqldump -u demo_user -p demo_db > backup_local_$(date +%Y%m%d).sql

# Backup BD Moon
mysqldump -h 107.161.23.241 -u moondesa_moon -p moondesa_moon > backup_moon_$(date +%Y%m%d).sql
```

### Protección del .env

```bash
# Verificar que .env NO esté en git
cat .gitignore | grep .env

# Si no está, agregarlo
echo ".env" >> .gitignore
```

---

## 📊 FLUJO DE DATOS

### Cuando un cliente PAGA:

```
1. Cliente hace clic en "Pagar con MercadoPago"
   ↓
2. Se crea preferencia de pago (MercadoPago API)
   ↓
3. Se registra intento en BD MOON
   INSERT INTO mercadopago_intentos (Conexion::conectarMoon())
   ↓
4. Cliente paga en MercadoPago
   ↓
5. MercadoPago envía notificación a webhook
   ↓
6. Webhook registra pago en BD MOON
   INSERT INTO mercadopago_pagos (Conexion::conectarMoon())
   ↓
7. Webhook actualiza cuenta corriente en BD MOON
   INSERT INTO clientes_cuenta_corriente (Conexion::conectarMoon())
   ↓
8. Webhook desbloquea cliente en BD MOON
   UPDATE clientes SET estado_bloqueo = 0 (Conexion::conectarMoon())
```

**IMPORTANTE:** TODO el flujo de cobro usa `Conexion::conectarMoon()`, NO `Conexion::conectar()`

---

## ✅ CHECKLIST DE CONFIGURACIÓN

- [ ] Archivo `.env` creado con AMBAS conexiones
- [ ] `.env` agregado al `.gitignore`
- [ ] Probada conexión a BD local
- [ ] Probada conexión a BD Moon (remota)
- [ ] Script SQL ejecutado en BD Moon (NO en local)
- [ ] Tablas `mercadopago_*` creadas en BD Moon
- [ ] Verificado que `ModeloMercadoPago` usa `conectarMoon()`
- [ ] Verificado que `ModeloSistemaCobro` usa `conectarMoon()`
- [ ] Backup de ambas bases de datos

---

## 📞 SOPORTE

Si tienes problemas de conexión:

1. Verificar credenciales en `.env`
2. Probar conexión manual con mysql CLI
3. Verificar firewall/IP autorizada
4. Revisar logs: `tail -f /var/log/apache2/error.log`

---

**Fecha:** 20 Noviembre 2025
**Arquitectura:** 2 Bases de Datos Separadas
**Local:** Sistema POS
**Remota:** Sistema Cobro Moon + MercadoPago
