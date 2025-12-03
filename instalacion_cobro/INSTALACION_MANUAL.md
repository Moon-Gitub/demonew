# 📖 Guía de Instalación Manual - Sistema de Cobro Moon POS

Esta guía te llevará paso a paso por la instalación manual del sistema de cobro.

## 📋 Antes de Empezar

### Información que necesitas tener a mano:

1. **Base de Datos Moon (Remota):**
   - Host: _________________
   - Nombre BD: _________________
   - Usuario: _________________
   - Contraseña: _________________

2. **MercadoPago:**
   - Public Key: _________________
   - Access Token: _________________

3. **Cliente:**
   - ID del cliente en tabla `clientes`: _________________

---

## PASO 1: Preparar el Servidor

### 1.1 Hacer Backup

```bash
# Conecta por SSH o FTP y haz backup completo
cd /home/tuusuario/public_html
tar -czf backup_antes_de_cobro_$(date +%Y%m%d).tar.gz .
```

### 1.2 Verificar estructura del proyecto

Tu sistema debe tener esta estructura:
```
/home/tuusuario/public_html/
├── controladores/
├── modelos/
├── vistas/
│   └── modulos/
├── extensiones/
└── index.php
```

✅ **Verificar:** Confirma que existen estas carpetas antes de continuar.

---

## PASO 2: Instalar Dependencias PHP

### 2.1 Instalar Composer (si no está instalado)

```bash
cd /home/tuusuario/public_html/extensiones
curl -sS https://getcomposer.org/installer | php
```

### 2.2 Actualizar composer.json

Edita el archivo `extensiones/composer.json` y agrega las dependencias:

```json
{
    "require": {
        "phpoffice/phpspreadsheet": "^4.1",
        "tecnickcom/tcpdf": "^6.8",
        "mercadopago/dx-php": "^3.1",
        "vlucas/phpdotenv": "^5.6"
    }
}
```

### 2.3 Instalar dependencias

```bash
cd /home/tuusuario/public_html/extensiones
php composer.phar install
# O si tienes composer global:
composer install
```

✅ **Verificar:** Deberías ver la carpeta `vendor/` creada con todas las librerías.

---

## PASO 3: Configurar Base de Datos

### 3.1 Acceder a phpMyAdmin

1. Abre phpMyAdmin desde cPanel
2. Selecciona la base de datos Moon (remota)

### 3.2 Crear las tablas

Ejecuta el script SQL:

**Opción A:** Desde phpMyAdmin:
1. Clic en "SQL" (arriba)
2. Copia y pega el contenido de `sql/01_crear_tablas_mercadopago.sql`
3. Clic en "Continuar"

**Opción B:** Desde terminal:
```bash
mysql -h 107.161.23.11 -u usuario_bd -p nombre_bd < instalacion_cobro/sql/01_crear_tablas_mercadopago.sql
```

### 3.3 Agregar control de recargos por cliente

Ejecuta el script SQL para agregar el campo `aplicar_recargos`:

**Opción A:** Desde phpMyAdmin:
1. Clic en "SQL" (arriba)
2. Copia y pega el contenido de `sql/03_agregar_control_recargos.sql`
3. Clic en "Continuar"

**Opción B:** Desde terminal:
```bash
mysql -h 107.161.23.11 -u usuario_bd -p nombre_bd < instalacion_cobro/sql/03_agregar_control_recargos.sql
```

✅ **Verificar:** Ejecuta `sql/02_verificar_instalacion.sql` y confirma que todas las tablas muestran ✓ OK.

---

## PASO 4: Copiar Archivos del Sistema

### 4.1 Copiar Controladores

```bash
# Desde la raíz del proyecto:
cp instalacion_cobro/archivos/controladores/mercadopago.controlador.php controladores/
```

✅ **Verificar:** El archivo existe en `controladores/mercadopago.controlador.php`

### 4.2 Copiar Modelos

```bash
cp instalacion_cobro/archivos/modelos/mercadopago.modelo.php modelos/
```

✅ **Verificar:** El archivo existe en `modelos/mercadopago.modelo.php`

### 4.3 Copiar Vistas

```bash
cp instalacion_cobro/archivos/vistas/modulos/cabezote-mejorado.php vistas/modulos/
cp instalacion_cobro/archivos/vistas/modulos/procesar-pago.php vistas/modulos/
```

✅ **Verificar:** Los archivos existen en `vistas/modulos/`

### 4.4 Copiar Configuración

```bash
cp instalacion_cobro/archivos/config.php .
```

✅ **Verificar:** El archivo existe en la raíz: `config.php`

---

## PASO 5: Configurar Variables de Entorno

### 5.1 Crear archivo .env

```bash
cp instalacion_cobro/archivos/.env.example .env
```

### 5.2 Editar .env con tus credenciales

Abre el archivo `.env` y configura:

```env
# BASE DE DATOS MOON
MOON_DB_HOST=107.161.23.11
MOON_DB_NAME=tu_base_de_datos
MOON_DB_USER=tu_usuario
MOON_DB_PASS=tu_contraseña

# MERCADOPAGO
MP_PUBLIC_KEY=APP_USR-tu-public-key
MP_ACCESS_TOKEN=APP_USR-tu-access-token

# ID DEL CLIENTE
MOON_CLIENTE_ID=7
```

⚠️ **IMPORTANTE:**
- Reemplaza TODOS los valores de ejemplo
- NO uses comillas en los valores
- NO dejes espacios antes/después del =

### 5.3 Proteger el archivo .env

```bash
chmod 600 .env
```

✅ **Verificar:** El archivo `.env` tiene permisos 600 y contiene tus credenciales.

---

## PASO 6: Modificar index.php

### 6.1 Abrir index.php

Busca la sección donde se carga el autoload de composer (generalmente cerca del inicio).

### 6.2 Agregar configuración

Después de la línea que carga el autoload, agrega:

```php
// Cargar vendor autoload primero
require_once "extensiones/vendor/autoload.php";

// Cargar configuración
require_once "config.php";

// Cargar variables de entorno desde .env (si existe y si Dotenv está instalado)
if (file_exists(__DIR__ . '/.env') && class_exists('Dotenv\Dotenv')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();
}
```

✅ **Verificar:** Guarda el archivo y accede al sistema, no debería dar errores.

---

## PASO 7: Modificar conexion.php

### 7.1 Abrir modelos/conexion.php

### 7.2 Agregar función conectarMoon()

Al final de la clase `Conexion`, antes del último `}`, agrega:

```php
/*=============================================
CONEXIÓN A BASE DE DATOS MOON (REMOTA)
=============================================*/
static public function conectarMoon() {

    $host = getenv('MOON_DB_HOST') ?: '107.161.23.11';
    $db = getenv('MOON_DB_NAME') ?: 'nombre_bd';
    $user = getenv('MOON_DB_USER') ?: 'usuario';
    $pass = getenv('MOON_DB_PASS') ?: 'contraseña';

    try {
        $link = new PDO("mysql:host=$host;dbname=$db", "$user", "$pass");
        $link->exec("set names utf8");
        return $link;
    } catch (PDOException $e) {
        error_log("Error conectando a BD Moon: " . $e->getMessage());
        // No lanzar excepción, retornar null para hacer el sistema de cobro opcional
        return null;
    }
}
```

✅ **Verificar:** Guarda el archivo.

---

## PASO 8: Modificar plantilla.php

### 8.1 Abrir vistas/plantilla.php

### 8.2 Buscar la línea que incluye el cabezote

Busca algo como:
```php
include "modulos/cabezote.php";
```

### 8.3 Reemplazar por cabezote-mejorado.php

```php
// CABEZOTE CON SISTEMA DE COBRO MERCADOPAGO
include "modulos/cabezote-mejorado.php";
```

✅ **Verificar:** Guarda el archivo.

---

## PASO 9: Crear Controlador y Modelo de Sistema Cobro

### 9.1 Verificar si existen

Verifica si ya existen estos archivos:
- `controladores/sistema_cobro.controlador.php`
- `modelos/sistema_cobro.modelo.php`

**Si NO existen**, cópialos desde el sistema demo:
```bash
# Desde demo.posmoon.com.ar o desde otro cliente que ya los tenga
scp usuario@servidor:/ruta/controladores/sistema_cobro.controlador.php controladores/
scp usuario@servidor:/ruta/modelos/sistema_cobro.modelo.php modelos/
```

**Si YA existen**, no hagas nada, están listos.

✅ **Verificar:** Ambos archivos existen.

---

## PASO 10: Configurar Rutas

### 10.1 Abrir index.php

### 10.2 Buscar el array de rutas

Busca donde se definen las rutas del sistema (generalmente un `if` o `switch`).

### 10.3 Agregar ruta procesar-pago

Agrega esta ruta:

```php
if ($rutasArray[0] == "procesar-pago") {
    include "vistas/modulos/procesar-pago.php";
}
```

✅ **Verificar:** Guarda el archivo.

---

## PASO 11: Verificar Instalación

### 11.1 Verificar Base de Datos

En phpMyAdmin, ejecuta:
```sql
source instalacion_cobro/sql/02_verificar_instalacion.sql
```

Deberías ver ✓ OK en todas las verificaciones.

### 11.2 Verificar Archivos

Accede a:
```
http://tudominio.com/verificador.php
```

Deberías ver una página con verificaciones en verde.

### 11.3 Probar el Sistema

1. Inicia sesión en el POS
2. Deberías ver el ícono de la luna (🌙) en la navbar superior derecha
3. Haz clic en el ícono
4. Debería abrirse un dropdown con información del saldo
5. Haz clic en "Pagar Ahora"
6. Debería abrirse el modal con el desglose de cargos

✅ **Verificar:** Todo funciona correctamente.

---

## PASO 12: Configurar Webhook (Opcional pero Recomendado)

### 12.1 Subir webhook-mercadopago.php

```bash
cp instalacion_cobro/archivos/webhook-mercadopago.php .
```

### 12.2 Configurar en MercadoPago

1. Accede a: https://www.mercadopago.com.ar/developers/panel/app
2. Selecciona tu aplicación
3. Ve a "Webhooks"
4. Agrega URL: `https://tudominio.com/webhook-mercadopago.php`
5. Selecciona eventos: `payment` y `merchant_order`

✅ **Verificar:** MercadoPago muestra el webhook como activo.

---

## PASO 13: Limpieza y Seguridad

### 13.1 Eliminar carpeta de instalación

```bash
rm -rf instalacion_cobro/
```

### 13.2 Proteger archivos sensibles

```bash
chmod 600 .env
chmod 600 config.php
```

### 13.3 Verificar .gitignore

Si usas Git, asegúrate de que `.gitignore` incluya:
```
.env
config.php
```

✅ **Verificar:** Los archivos sensibles están protegidos.

---

## 🎉 ¡Instalación Completada!

El sistema de cobro está instalado y listo para usar.

### Próximos pasos:

1. **Prueba con credenciales TEST de MercadoPago**
2. **Verifica que los cargos se muestren correctamente**
3. **Haz una prueba de pago completa**
4. **Revisa que el pago se registre en la BD**
5. **Cuando todo funcione, cambia a credenciales de PRODUCCIÓN**

### Control de Recargos por Cliente

El sistema incluye control individual de recargos por cliente. Por defecto, todos los clientes tienen recargos habilitados.

**Para eximir a un cliente de recargos:**
```sql
UPDATE clientes SET aplicar_recargos = 0 WHERE id = [id_del_cliente];
```

**Para aplicar recargos nuevamente:**
```sql
UPDATE clientes SET aplicar_recargos = 1 WHERE id = [id_del_cliente];
```

**Ver estado actual:**
```sql
SELECT id, nombre, aplicar_recargos FROM clientes WHERE id = [id_del_cliente];
```

**Casos de uso comunes:**
- Clientes VIP o con contrato especial
- Clientes en período de prueba
- Acuerdos comerciales especiales sin recargos

---

## ⚠️ Problemas Comunes

### Error: "Class Dotenv\Dotenv not found"
**Solución:** Instala las dependencias con composer:
```bash
cd extensiones && composer install
```

### Error: "Access denied for user"
**Solución:** Verifica las credenciales en el archivo `.env`

### El modal no se abre
**Solución:** Verifica que:
1. El cabezote-mejorado.php esté incluido en plantilla.php
2. El cliente tenga saldo pendiente en cuenta corriente
3. No haya errores de JavaScript en la consola del navegador

### El pago no se registra
**Solución:** Verifica que:
1. La ruta "procesar-pago" esté configurada en index.php
2. El archivo procesar-pago.php esté en vistas/modulos/
3. La conexión a la BD Moon funcione correctamente

---

## 📞 Soporte

Si tienes problemas, revisa:
- [TROUBLESHOOTING.md](TROUBLESHOOTING.md)
- Logs de errores PHP
- Documentación de MercadoPago

---

**¡Éxito en tu instalación!** 🚀
