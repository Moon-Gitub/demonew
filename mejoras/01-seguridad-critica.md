# 🔴 Seguridad Crítica - URGENTE

## ⚠️ ESTAS VULNERABILIDADES REQUIEREN ATENCIÓN INMEDIATA

---

## 1. Credenciales Expuestas en el Código

### 🔍 Problema Detectado

**Archivo**: `modelos/conexion.php` (líneas 5-9)

```php
static public $hostDB = 'localhost';
static public $nameDB = 'demo_db';
static public $userDB = 'demo_user';
static public $passDB = 'aK4UWccl2ceg';  // ❌ CONTRASEÑA EN TEXTO PLANO
static public $charset = 'UTF8MB4';
```

### ⚡ Riesgo
- **Nivel**: CRÍTICO
- **Impacto**: Si el código es comprometido, acceso total a la base de datos
- **Exposición**: Cualquier persona con acceso al código ve las credenciales

### ✅ Solución

#### Paso 1: Crear archivo `.env`
```bash
cd /home/cluna/Documentos/Moon-Desarrollos/public_html
touch .env
chmod 600 .env
```

#### Paso 2: Contenido del archivo `.env`
```env
# Base de datos
DB_HOST=localhost
DB_NAME=demo_db
DB_USER=demo_user
DB_PASS=aK4UWccl2ceg
DB_CHARSET=UTF8MB4

# Entorno
APP_ENV=production
APP_DEBUG=false

# Seguridad
APP_KEY=GENERAR_KEY_ALEATORIA_AQUI
SESSION_LIFETIME=7200
```

#### Paso 3: Agregar `.env` al `.gitignore`
```bash
echo ".env" >> .gitignore
```

#### Paso 4: Instalar vlucas/phpdotenv
```bash
cd extensiones
composer require vlucas/phpdotenv
```

#### Paso 5: Modificar `modelos/conexion.php`
```php
<?php

class Conexion {

    static public function conectar() {
        $host = $_ENV['DB_HOST'] ?? getenv('DB_HOST');
        $db = $_ENV['DB_NAME'] ?? getenv('DB_NAME');
        $user = $_ENV['DB_USER'] ?? getenv('DB_USER');
        $pass = $_ENV['DB_PASS'] ?? getenv('DB_PASS');
        $charset = $_ENV['DB_CHARSET'] ?? getenv('DB_CHARSET');

        try {
            $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            
            $link = new PDO($dsn, $user, $pass, $options);
            return $link;
            
        } catch (PDOException $e) {
            error_log("Error de conexión: " . $e->getMessage());
            throw new Exception("Error de conexión a la base de datos");
        }
    }
}
```

#### Paso 6: Cargar variables en `index.php`
```php
<?php
// Al inicio del archivo, después de la línea 1
require_once __DIR__ . '/extensiones/vendor/autoload.php';

// Cargar variables de entorno
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Resto del código...
```

---

## 2. Algoritmo de Encriptación Débil

### 🔍 Problema Detectado

**Archivo**: `controladores/usuarios.controlador.php` (línea 14, 133, 255)

```php
$encriptar = crypt($_POST["ingPassword"], '$2a$07$asxx54ahjppf45sd87a5a4dDDGsystemdev$');
```

### ⚡ Riesgo
- **Nivel**: CRÍTICO
- **Problemas**:
  - Salt fijo (todos los usuarios tienen el mismo)
  - Cost factor bajo (07 es muy débil)
  - Vulnerable a rainbow tables
  - No cumple estándares modernos

### ✅ Solución

#### Crear nueva clase de seguridad

**Archivo**: `modelos/seguridad.modelo.php`
```php
<?php

class ModeloSeguridad {
    
    /**
     * Hash seguro de contraseña
     */
    static public function hashPassword($password) {
        // Usa PASSWORD_DEFAULT para adaptarse automáticamente
        // a algoritmos más seguros en futuras versiones de PHP
        return password_hash($password, PASSWORD_DEFAULT, [
            'cost' => 12  // Mayor seguridad (mínimo 10, recomendado 12)
        ]);
    }
    
    /**
     * Verificar contraseña
     */
    static public function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }
    
    /**
     * Verificar si el hash necesita actualización
     */
    static public function needsRehash($hash) {
        return password_needs_rehash($hash, PASSWORD_DEFAULT, ['cost' => 12]);
    }
}
```

#### Modificar controlador de usuarios

**Archivo**: `controladores/usuarios.controlador.php`

**CAMBIO 1**: Login (línea 8-83)
```php
static public function ctrIngresoUsuario(){
    if(isset($_POST["ingUsuario"])){
        if(preg_match('/^[a-zA-Z0-9]+$/', $_POST["ingUsuario"])){
            
            $tabla = "usuarios";
            $item = "usuario";
            $valor = $_POST["ingUsuario"];
            
            $respuesta = ModeloUsuarios::MdlMostrarUsuarios($tabla, $item, $valor);
            
            if($respuesta && $respuesta["usuario"] == $_POST["ingUsuario"]) {
                
                // ✅ Nueva verificación segura
                if(ModeloSeguridad::verifyPassword($_POST["ingPassword"], $respuesta["password"])){
                    
                    if($respuesta["estado"] == 1){
                        
                        // ✅ Verificar si el hash necesita actualización
                        if(ModeloSeguridad::needsRehash($respuesta["password"])){
                            $nuevoHash = ModeloSeguridad::hashPassword($_POST["ingPassword"]);
                            ModeloUsuarios::mdlActualizarUsuario($tabla, "password", $nuevoHash, "id", $respuesta["id"]);
                        }
                        
                        $_SESSION["iniciarSesion"] = "ok";
                        $_SESSION["id"] = $respuesta["id"];
                        $_SESSION["nombre"] = $respuesta["nombre"];
                        $_SESSION["usuario"] = $respuesta["usuario"];
                        $_SESSION["foto"] = $respuesta["foto"];
                        $_SESSION["perfil"] = $respuesta["perfil"];
                        $_SESSION["sucursal"] = $respuesta["sucursal"];
                        $_SESSION["puntos_venta"] = $respuesta["puntos_venta"];
                        $_SESSION["listas_precio"] = $respuesta["listas_precio"];
                        
                        // ✅ Token CSRF
                        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                        
                        // Registrar último login
                        date_default_timezone_set('America/Argentina/Mendoza');
                        $fechaActual = date('Y-m-d H:i:s');
                        ModeloUsuarios::mdlActualizarUsuario($tabla, "ultimo_login", $fechaActual, "id", $respuesta["id"]);
                        
                        echo '<script>window.location = "inicio";</script>';
                        
                    } else {
                        echo '<br><div class="alert alert-danger">El usuario aún no está activado</div>';
                    }
                    
                } else {
                    echo '<br><div class="alert alert-danger">Error al ingresar, vuelve a intentarlo</div>';
                }
            } else {
                echo '<br><div class="alert alert-danger">Error al ingresar, vuelve a intentarlo</div>';
            }
        }
    }
}
```

**CAMBIO 2**: Crear usuario (línea 88-189)
```php
static public function ctrCrearUsuario(){
    if(isset($_POST["nuevoUsuario"])){
        if(preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ ]+$/', $_POST["nuevoNombre"]) &&
           preg_match('/^[a-zA-Z0-9]+$/', $_POST["nuevoUsuario"]) &&
           preg_match('/^[a-zA-Z0-9]+$/', $_POST["nuevoPassword"])){
            
            // ... código de validación de imagen ...
            
            $tabla = "usuarios";
            
            // ✅ Hash seguro
            $encriptar = ModeloSeguridad::hashPassword($_POST["nuevoPassword"]);
            
            $listasPrecio = '';
            if(!empty($_POST['nuevoPreciosVentaUsuario'])) {    
                foreach($_POST['nuevoPreciosVentaUsuario'] as $value){
                    $listasPrecio .= $value.',';
                }
                $listasPrecio = substr($listasPrecio, 0, -1);
            }
            
            $datos = array(
                "nombre" => $_POST["nuevoNombre"],
                "usuario" => $_POST["nuevoUsuario"],
                "password" => $encriptar,
                "perfil" => $_POST["nuevoPerfil"],
                "sucursal" => $_POST["nuevaSucursal"],
                "puntos_venta" => $_POST["nuevoPuntoVenta"],
                "listas_precio" => $listasPrecio,
                "foto" => $ruta
            );
            
            // ... resto del código ...
        }
    }
}
```

#### Script de migración de contraseñas

**Archivo**: `mejoras/scripts/migrar-passwords.php`
```php
<?php
/**
 * Script para migrar contraseñas antiguas al nuevo formato
 * EJECUTAR UNA SOLA VEZ
 */

require_once "../modelos/conexion.php";
require_once "../modelos/seguridad.modelo.php";

echo "MIGRACIÓN DE CONTRASEÑAS\n";
echo "========================\n\n";

$pdo = Conexion::conectar();
$usuarios = $pdo->query("SELECT id, usuario, password FROM usuarios")->fetchAll();

$migrados = 0;
$errores = 0;

foreach ($usuarios as $usuario) {
    // Si el hash no empieza con $2y$ es el formato antiguo
    if (substr($usuario['password'], 0, 4) !== '$2y$') {
        echo "Migrando usuario: {$usuario['usuario']}... ";
        
        // IMPORTANTE: Este script asume que tienes las contraseñas en texto plano
        // O que usarás una contraseña temporal para todos
        
        // OPCIÓN 1: Usar contraseña temporal
        $nuevaPassword = "cambiar123"; // Los usuarios deberán cambiarla
        $nuevoHash = ModeloSeguridad::hashPassword($nuevaPassword);
        
        // OPCIÓN 2: Si tienes las contraseñas originales en algún lado
        // $nuevoHash = ModeloSeguridad::hashPassword($passwordOriginal);
        
        $stmt = $pdo->prepare("UPDATE usuarios SET password = :password WHERE id = :id");
        $stmt->execute(['password' => $nuevoHash, 'id' => $usuario['id']]);
        
        echo "✅ OK\n";
        $migrados++;
    }
}

echo "\n";
echo "Usuarios migrados: $migrados\n";
echo "Errores: $errores\n";
```

---

## 3. Falta de Validación en Archivos AJAX

### 🔍 Problema Detectado

**Archivos afectados**: Todos los archivos en `ajax/`

Ejemplo: `ajax/ventas.ajax.php`
```php
<?php
// ❌ NO HAY VERIFICACIÓN DE SESIÓN
require_once "../controladores/ventas.controlador.php";

if(isset($_POST["idVenta"])){
    $venta = new AjaxVentas();
    $venta -> idVenta = $_POST["idVenta"];
    $venta -> ajaxEditarVenta();
}
```

### ⚡ Riesgo
- **Nivel**: CRÍTICO
- **Impacto**: Acceso no autorizado a funcionalidades
- **Exposición**: Cualquiera puede llamar estos endpoints

### ✅ Solución

#### Crear middleware de seguridad

**Archivo**: `ajax/seguridad.ajax.php`
```php
<?php
/**
 * Middleware de seguridad para archivos AJAX
 */

class SeguridadAjax {
    
    /**
     * Verificar que la sesión está activa
     */
    static public function verificarSesion() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION["iniciarSesion"]) || $_SESSION["iniciarSesion"] != "ok") {
            http_response_code(401);
            echo json_encode([
                'error' => true,
                'mensaje' => 'No autorizado'
            ]);
            exit;
        }
    }
    
    /**
     * Verificar token CSRF
     */
    static public function verificarCSRF() {
        $token = $_POST['csrf_token'] ?? $_GET['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
        
        if (!$token || !isset($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
            http_response_code(403);
            echo json_encode([
                'error' => true,
                'mensaje' => 'Token CSRF inválido'
            ]);
            exit;
        }
    }
    
    /**
     * Verificar que la petición es AJAX
     */
    static public function verificarAjax() {
        if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || 
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
            http_response_code(403);
            echo json_encode([
                'error' => true,
                'mensaje' => 'Solo peticiones AJAX'
            ]);
            exit;
        }
    }
    
    /**
     * Inicialización completa
     */
    static public function inicializar($verificarCSRF = true) {
        self::verificarSesion();
        self::verificarAjax();
        
        if ($verificarCSRF) {
            self::verificarCSRF();
        }
    }
}
```

#### Modificar archivos AJAX

**Ejemplo**: `ajax/ventas.ajax.php`
```php
<?php
// ✅ Agregar al inicio
require_once "seguridad.ajax.php";
SeguridadAjax::inicializar();

require_once "../controladores/ventas.controlador.php";
require_once "../modelos/ventas.modelo.php";

// ... resto del código ...
```

#### Modificar JavaScript para incluir token CSRF

**Archivo**: Agregar a `vistas/js/plantilla.js`
```javascript
// Obtener token CSRF del meta tag o hidden input
function getCSRFToken() {
    return $('meta[name="csrf-token"]').attr('content') || 
           $('input[name="csrf_token"]').val();
}

// Configurar AJAX para incluir token en todas las peticiones
$.ajaxSetup({
    beforeSend: function(xhr, settings) {
        if (!/^(GET|HEAD|OPTIONS|TRACE)$/i.test(settings.type)) {
            xhr.setRequestHeader("X-CSRF-TOKEN", getCSRFToken());
        }
        xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");
    }
});
```

#### Agregar meta tag en plantilla

**Archivo**: `vistas/plantilla.php` (después de línea 27)
```php
<meta name="csrf-token" content="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
```

---

## 4. Validación Insegura de Archivos Subidos

### 🔍 Problema Detectado

**Archivo**: `controladores/usuarios.controlador.php` (líneas 97-131)

```php
// ❌ Solo valida el tipo MIME reportado por el navegador
if($_FILES["nuevaFoto"]["type"] == "image/jpeg"){
    // procesar imagen
}
```

### ⚡ Riesgo
- **Nivel**: CRÍTICO
- **Impacto**: Upload de archivos maliciosos (shell PHP, malware)
- **Exposición**: Ejecución remota de código

### ✅ Solución

**Archivo**: `modelos/upload.modelo.php`
```php
<?php

class ModeloUpload {
    
    const ALLOWED_TYPES = ['image/jpeg', 'image/png', 'image/gif'];
    const MAX_SIZE = 5242880; // 5MB
    const UPLOAD_DIR = 'vistas/img/usuarios/';
    
    /**
     * Validar y procesar imagen de forma segura
     */
    static public function procesarImagenUsuario($file, $nombreUsuario) {
        
        // Validación 1: Verificar que se subió un archivo
        if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
            return ['error' => true, 'mensaje' => 'No se recibió ningún archivo'];
        }
        
        // Validación 2: Verificar errores de PHP
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['error' => true, 'mensaje' => 'Error al subir el archivo'];
        }
        
        // Validación 3: Verificar tamaño
        if ($file['size'] > self::MAX_SIZE) {
            return ['error' => true, 'mensaje' => 'El archivo es demasiado grande (máx 5MB)'];
        }
        
        // Validación 4: Verificar tipo MIME real con finfo
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mimeType, self::ALLOWED_TYPES)) {
            return ['error' => true, 'mensaje' => 'Tipo de archivo no permitido'];
        }
        
        // Validación 5: Verificar que es una imagen válida
        $imageInfo = getimagesize($file['tmp_name']);
        if ($imageInfo === false) {
            return ['error' => true, 'mensaje' => 'El archivo no es una imagen válida'];
        }
        
        list($ancho, $alto) = $imageInfo;
        $nuevoAncho = 500;
        $nuevoAlto = 500;
        
        // Crear directorio si no existe
        $directorio = self::UPLOAD_DIR . $nombreUsuario;
        if (!file_exists($directorio)) {
            mkdir($directorio, 0755, true);
        }
        
        // Generar nombre único y seguro
        $extension = $mimeType === 'image/jpeg' ? 'jpg' : 'png';
        $nombreArchivo = uniqid('foto_', true) . '.' . $extension;
        $rutaDestino = $directorio . '/' . $nombreArchivo;
        
        // Procesar imagen según tipo
        try {
            switch ($mimeType) {
                case 'image/jpeg':
                    $origen = imagecreatefromjpeg($file['tmp_name']);
                    break;
                case 'image/png':
                    $origen = imagecreatefrompng($file['tmp_name']);
                    break;
                case 'image/gif':
                    $origen = imagecreatefromgif($file['tmp_name']);
                    break;
                default:
                    return ['error' => true, 'mensaje' => 'Tipo de imagen no soportado'];
            }
            
            if (!$origen) {
                return ['error' => true, 'mensaje' => 'No se pudo procesar la imagen'];
            }
            
            // Redimensionar
            $destino = imagecreatetruecolor($nuevoAncho, $nuevoAlto);
            
            // Preservar transparencia para PNG
            if ($mimeType === 'image/png') {
                imagealphablending($destino, false);
                imagesavealpha($destino, true);
            }
            
            imagecopyresampled($destino, $origen, 0, 0, 0, 0, $nuevoAncho, $nuevoAlto, $ancho, $alto);
            
            // Guardar
            $guardado = false;
            if ($extension === 'jpg') {
                $guardado = imagejpeg($destino, $rutaDestino, 85);
            } else {
                $guardado = imagepng($destino, $rutaDestino, 9);
            }
            
            imagedestroy($origen);
            imagedestroy($destino);
            
            if (!$guardado) {
                return ['error' => true, 'mensaje' => 'No se pudo guardar la imagen'];
            }
            
            return ['error' => false, 'ruta' => $rutaDestino];
            
        } catch (Exception $e) {
            error_log("Error procesando imagen: " . $e->getMessage());
            return ['error' => true, 'mensaje' => 'Error al procesar la imagen'];
        }
    }
    
    /**
     * Eliminar imagen de usuario
     */
    static public function eliminarImagenUsuario($ruta) {
        if (file_exists($ruta)) {
            return unlink($ruta);
        }
        return true;
    }
}
```

#### Modificar controlador de usuarios

Reemplazar el código de procesamiento de imágenes por:

```php
// Validar imagen
$ruta = "";
if(isset($_FILES["nuevaFoto"]["tmp_name"]) && $_FILES["nuevaFoto"]["tmp_name"] != ""){
    
    $resultado = ModeloUpload::procesarImagenUsuario($_FILES["nuevaFoto"], $_POST["nuevoUsuario"]);
    
    if($resultado['error']){
        echo '<script>
        swal({
            type: "error",
            title: "Error",
            text: "'.$resultado['mensaje'].'",
            showConfirmButton: true,
            confirmButtonText: "Cerrar"
        });
        </script>';
        return;
    }
    
    $ruta = $resultado['ruta'];
}
```

---

## 5. Sin Protección contra Fuerza Bruta

### 🔍 Problema Detectado

El sistema no tiene limitación de intentos de login

### ⚡ Riesgo
- **Nivel**: ALTO
- **Impacto**: Ataques de fuerza bruta en formulario de login

### ✅ Solución

**Archivo**: `modelos/login.modelo.php`
```php
<?php

class ModeloLogin {
    
    const MAX_INTENTOS = 5;
    const TIEMPO_BLOQUEO = 900; // 15 minutos
    
    /**
     * Registrar intento fallido
     */
    static public function registrarIntentoFallido($usuario) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['intentos_login'])) {
            $_SESSION['intentos_login'] = [];
        }
        
        $_SESSION['intentos_login'][$usuario] = [
            'intentos' => ($_SESSION['intentos_login'][$usuario]['intentos'] ?? 0) + 1,
            'ultimo_intento' => time()
        ];
    }
    
    /**
     * Verificar si está bloqueado
     */
    static public function estaBloqueado($usuario) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['intentos_login'][$usuario])) {
            return false;
        }
        
        $datos = $_SESSION['intentos_login'][$usuario];
        $tiempoTranscurrido = time() - $datos['ultimo_intento'];
        
        // Si pasó el tiempo de bloqueo, resetear
        if ($tiempoTranscurrido > self::TIEMPO_BLOQUEO) {
            unset($_SESSION['intentos_login'][$usuario]);
            return false;
        }
        
        return $datos['intentos'] >= self::MAX_INTENTOS;
    }
    
    /**
     * Resetear intentos (después de login exitoso)
     */
    static public function resetearIntentos($usuario) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (isset($_SESSION['intentos_login'][$usuario])) {
            unset($_SESSION['intentos_login'][$usuario]);
        }
    }
    
    /**
     * Obtener tiempo restante de bloqueo
     */
    static public function tiempoRestanteBloqueo($usuario) {
        if (!isset($_SESSION['intentos_login'][$usuario])) {
            return 0;
        }
        
        $datos = $_SESSION['intentos_login'][$usuario];
        $tiempoTranscurrido = time() - $datos['ultimo_intento'];
        $tiempoRestante = self::TIEMPO_BLOQUEO - $tiempoTranscurrido;
        
        return max(0, $tiempoRestante);
    }
}
```

---

## ✅ Checklist de Implementación

- [ ] Mover credenciales a archivo `.env`
- [ ] Agregar `.env` al `.gitignore`
- [ ] Instalar vlucas/phpdotenv
- [ ] Actualizar clase Conexion
- [ ] Crear clase ModeloSeguridad
- [ ] Migrar contraseñas existentes
- [ ] Actualizar controlador de usuarios (login)
- [ ] Actualizar controlador de usuarios (crear)
- [ ] Actualizar controlador de usuarios (editar)
- [ ] Crear middleware SeguridadAjax
- [ ] Actualizar todos los archivos AJAX
- [ ] Agregar token CSRF a plantilla
- [ ] Configurar AJAX global para incluir CSRF
- [ ] Crear clase ModeloUpload
- [ ] Actualizar procesamiento de imágenes
- [ ] Implementar protección fuerza bruta
- [ ] Probar todas las funcionalidades
- [ ] Documentar cambios realizados

---

**Tiempo estimado**: 2-3 semanas  
**Prioridad**: 🔴 CRÍTICA  
**Siguiente paso**: [02-seguridad-sql.md](02-seguridad-sql.md)

