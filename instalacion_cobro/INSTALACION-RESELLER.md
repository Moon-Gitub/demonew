# 🏢 Guía de Instalación para Hosting Reseller

Esta guía te ayudará a instalar el sistema de cobro en **MÚLTIPLES cuentas** de tu hosting reseller de forma rápida y eficiente.

---

## 📋 ANTES DE EMPEZAR

### Información que necesitas:

1. **Acceso al servidor:**
   - Usuario SSH del servidor principal
   - Acceso WHM (Web Host Manager)

2. **Base de Datos Moon:**
   - Host: `107.161.23.11`
   - BD: `cobrosposmooncom_db`
   - Usuario: `cobrosposmooncom_dbuser`
   - Password: `[Us{ynaJAA_o2A_!`

3. **MercadoPago:**
   - Public Key
   - Access Token

4. **Lista de clientes a instalar:**
   - ID de cada cliente en la tabla `clientes` de BD Moon
   - Dominio/cuenta de cada cliente
   - Usuario cPanel de cada cuenta

---

## 🎯 ESTRATEGIA DE INSTALACIÓN

### Opción 1: Instalación Manual Por Cuenta (Recomendado al inicio)

Para las primeras instalaciones, hazlo manualmente cuenta por cuenta para familiarizarte con el proceso.

### Opción 2: Instalación Masiva con Script

Una vez que domines el proceso, usa el script de instalación masiva.

---

## 📝 INSTALACIÓN MANUAL POR CUENTA

### PASO 1: Preparar Información del Cliente

Antes de instalar en cada cuenta, necesitas saber:

| Campo | Ejemplo | Dónde obtenerlo |
|-------|---------|-----------------|
| ID Cliente Moon | 14 | `SELECT id, nombre FROM clientes WHERE dominio LIKE '%amarello%'` |
| Dominio | amarello.posmoon.com.ar | WHM → List Accounts |
| Usuario cPanel | amarello | WHM → List Accounts |
| Base de Datos | amarello_db o newmoon_newmoon_db | cPanel → MySQL Databases |

### PASO 2: Acceder a la Cuenta del Cliente

**Desde WHM:**
1. Ir a **Account Functions → Login to cPanel**
2. Buscar la cuenta (ej: amarello)
3. Hacer clic en el icono de cPanel

O por SSH:
```bash
ssh usuario@amarello.posmoon.com.ar
```

### PASO 3: Subir Archivos

**Opción A - Git (Si la cuenta tiene Git):**
```bash
cd /home/amarello/public_html
git pull origin main
```

**Opción B - File Manager de cPanel:**
1. Ir a **Files → File Manager**
2. Navegar a `public_html`
3. Subir/editar archivos según instalación manual

**Opción C - SCP desde servidor principal:**
```bash
# Desde el servidor principal
scp -r /ruta/plantilla/vistas/modulos/cabezote-mejorado.php amarello@localhost:/home/amarello/public_html/vistas/modulos/
```

### PASO 4: Configurar el ID del Cliente

Editar `vistas/modulos/cabezote-mejorado.php` línea 15:

```php
// Cambiar el 7 por el ID real del cliente
$idCliente = isset($_ENV['MOON_CLIENTE_ID']) ? intval($_ENV['MOON_CLIENTE_ID']) : 14;
```

**O crear .env en la raíz de la cuenta:**
```env
MOON_CLIENTE_ID=14
```

### PASO 5: Verificar Archivos Necesarios

Asegúrate de que existen en la cuenta del cliente:

```
/home/cliente/public_html/
├── controladores/
│   ├── sistema_cobro.controlador.php ✓
│   └── mercadopago.controlador.php ✓
├── modelos/
│   ├── sistema_cobro.modelo.php ✓
│   ├── mercadopago.modelo.php ✓
│   └── conexion.php (con método conectarMoon()) ✓
├── vistas/modulos/
│   ├── cabezote-mejorado.php ✓
│   └── procesar-pago.php ✓
├── index.php (con requires de sistema_cobro y mercadopago) ✓
└── helpers.php (opcional)
```

### PASO 6: Verificar Conexión a BD Moon

Crear archivo temporal `test-moon.php` en la cuenta:

```php
<?php
require_once 'modelos/conexion.php';
try {
    $conn = Conexion::conectarMoon();
    if ($conn) {
        echo "✅ Conexión a BD Moon exitosa\n";
        
        $id = 14; // Cambiar por ID real
        $stmt = $conn->prepare("SELECT * FROM clientes WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $cliente = $stmt->fetch();
        
        if ($cliente) {
            echo "✅ Cliente encontrado: " . $cliente['nombre'] . "\n";
            echo "✅ Sistema de cobro funcionará correctamente\n";
        } else {
            echo "❌ Cliente ID $id no encontrado en BD Moon\n";
        }
    } else {
        echo "❌ No se pudo conectar a BD Moon\n";
    }
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
```

Ejecutar: `https://dominio.com/test-moon.php`

### PASO 7: Verificar que Funciona

1. Acceder al sistema del cliente
2. Verificar que aparece el ícono 🌙 en la navbar
3. Si tiene deuda, debe aparecer el modal de pago
4. Eliminar `test-moon.php`

---

## 🚀 INSTALACIÓN MASIVA

### Preparar Lista de Clientes

Crear archivo `clientes-a-instalar.csv`:

```csv
id_cliente,dominio,usuario_cpanel,base_datos
14,amarello.posmoon.com.ar,amarello,amarello_db
7,demo.posmoon.com.ar,demo,demo_db
2,abisko.posmoon.com.ar,abisko,abisko_db
```

### Script de Instalación Masiva

Ver archivo `script-instalacion-masiva.sh` incluido en esta carpeta.

---

## 📊 LISTADO DE CUENTAS EN TU RESELLER

Según la imagen que mostraste, tienes estas cuentas:

| Dominio | Usuario | ID Cliente (a determinar) |
|---------|---------|---------------------------|
| abisko.posmoon.com.ar | abisko | ? |
| adrimar.posmoon.com.ar | adrimar | ? |
| amarello.posmoon.com.ar | amarello | 14 ✓ |
| anapozo.posmoon.com.ar | anapozo | ? |
| animatico.design | animatico | ? |
| barbas.posmoon.com.ar | barbas | ? |
| bloke.posmoon.com.ar | bloke | ? |
| bluejeans.posmoon.com.ar | bluejeans | ? |
| demo.posmoon.com.ar | demo | 7 ✓ |
| ... | ... | ... |

### Obtener IDs de Clientes

Ejecutar en BD Moon:

```sql
SELECT id, nombre, dominio 
FROM clientes 
ORDER BY nombre;
```

Y completar la tabla con los IDs correspondientes.

---

## ⚙️ CONFIGURACIÓN POR CUENTA

### Dos Métodos:

**Método A - Valor Hardcodeado (Más Simple):**

Editar `cabezote-mejorado.php` línea 15:
```php
$idCliente = 14; // ID específico de este cliente
```

✅ Ventajas:
- Simple y directo
- No depende de .env
- Funciona siempre

❌ Desventajas:
- Hay que editar el archivo para cada cliente
- Si actualizas el código, puedes perder el cambio

**Método B - Archivo .env (Más Profesional):**

Crear `.env` en cada cuenta:
```env
MOON_CLIENTE_ID=14
```

Y el código leerá automáticamente de `$_ENV`.

✅ Ventajas:
- Fácil de cambiar
- No toca el código
- Puedes actualizar el código sin perder la configuración

❌ Desventajas:
- Requiere crear .env en cada cuenta
- Puede tener problemas con configuración PHP

---

## 🔧 RECOMENDACIÓN FINAL

**Para instalación masiva en reseller:**

1. **Usar valores hardcodeados en primera instalación** (Método A)
2. **Documentar en un archivo** qué ID tiene cada cuenta
3. **Migrar a .env gradualmente** cuando todo esté estable

**Archivo de mapeo sugerido: `clientes-ids.txt`**
```
# Mapeo de cuentas → IDs de clientes
amarello.posmoon.com.ar = 14
demo.posmoon.com.ar = 7
abisko.posmoon.com.ar = 2
# ...
```

---

## 📞 SOPORTE

Si necesitas instalar en 10+ cuentas, considera:
- Automatizar con script bash
- Usar API de cPanel para despliegue masivo
- Crear plantilla base y clonar

---

**Siguiente:** Ver `script-instalacion-masiva.sh` para instalación automática.

