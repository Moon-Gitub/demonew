# 🏗️ Mejoras de Arquitectura

## Modernización de la Estructura del Código

---

## 1. Implementar Autoloading PSR-4

### 🔍 Problema Detectado

**Archivo**: `index.php` (líneas 3-46)

```php
// ❌ 40+ líneas de require_once manuales
require_once "controladores/plantilla.controlador.php";
require_once "controladores/usuarios.controlador.php";
require_once "controladores/categorias.controlador.php";
// ... y así 40 más
```

### ⚡ Impacto
- Mantenimiento difícil
- Errores por olvidar includes
- Carga innecesaria de archivos
- No sigue estándares PSR

### ✅ Solución

#### Paso 1: Reestructurar directorios

```
/home/cluna/Documentos/Moon-Desarrollos/public_html/
├── src/
│   ├── Controllers/
│   │   ├── PlantillaController.php
│   │   ├── UsuariosController.php
│   │   ├── ProductosController.php
│   │   └── ...
│   ├── Models/
│   │   ├── Conexion.php
│   │   ├── UsuariosModel.php
│   │   ├── ProductosModel.php
│   │   └── ...
│   ├── Views/
│   │   └── (mantener estructura actual)
│   ├── Middleware/
│   │   ├── AuthMiddleware.php
│   │   └── CSRFMiddleware.php
│   └── Utils/
│       ├── ValidadorSQL.php
│       ├── Validacion.php
│       ├── Seguridad.php
│       └── Upload.php
├── public/
│   ├── index.php
│   ├── ajax/
│   └── vistas/
├── config/
│   ├── database.php
│   └── app.php
├── composer.json
└── .env
```

#### Paso 2: Configurar Composer

**Archivo**: `composer.json` (raíz del proyecto)

```json
{
    "name": "moon-desarrollos/erp-pos",
    "description": "Sistema ERP/POS",
    "type": "project",
    "require": {
        "php": ">=7.4",
        "vlucas/phpdotenv": "^5.5",
        "phpoffice/phpspreadsheet": "^1.29",
        "tecnickcom/tcpdf": "^6.6"
    },
    "require-dev": {
        "phpunit/phpunit": "^9.5"
    },
    "autoload": {
        "psr-4": {
            "App\\Controllers\\": "src/Controllers/",
            "App\\Models\\": "src/Models/",
            "App\\Middleware\\": "src/Middleware/",
            "App\\Utils\\": "src/Utils/"
        },
        "files": [
            "src/helpers.php"
        ]
    },
    "scripts": {
        "post-install-cmd": [
            "@php -r \"file_exists('.env') || copy('.env.example', '.env');\""
        ]
    }
}
```

#### Paso 3: Generar autoload

```bash
cd /home/cluna/Documentos/Moon-Desarrollos/public_html
composer dump-autoload
```

#### Paso 4: Nuevo index.php

**Archivo**: `public/index.php`

```php
<?php

/**
 * Punto de entrada principal de la aplicación
 */

// Cargar autoloader de Composer
require_once __DIR__ . '/../vendor/autoload.php';

// Cargar variables de entorno
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Configurar zona horaria
date_default_timezone_set($_ENV['APP_TIMEZONE'] ?? 'America/Argentina/Mendoza');

// Configurar manejo de errores
if ($_ENV['APP_DEBUG'] === 'true') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Iniciar sesión
session_start();
setcookie(session_name(), session_id(), 0, "/");

// Usar namespaces
use App\Controllers\PlantillaController;

// Inicializar aplicación
$plantilla = new PlantillaController();
$plantilla->ctrPlantilla();
```

#### Paso 5: Ejemplo de Controller con namespace

**Archivo**: `src/Controllers/PlantillaController.php`

```php
<?php

namespace App\Controllers;

class PlantillaController {
    
    public function ctrPlantilla() {
        include __DIR__ . '/../../vistas/plantilla.php';
    }
}
```

**Archivo**: `src/Controllers/UsuariosController.php`

```php
<?php

namespace App\Controllers;

use App\Models\UsuariosModel;
use App\Models\EmpresaModel;
use App\Utils\Seguridad;
use App\Utils\Validacion;
use App\Utils\Upload;

class UsuariosController {
    
    /**
     * Ingreso de usuario
     */
    public static function ctrIngresoUsuario() {
        
        if (!isset($_POST["ingUsuario"])) {
            return;
        }
        
        // Validar entrada
        $usuario = Validacion::validarUsername($_POST["ingUsuario"]);
        if (!$usuario) {
            self::mostrarError("Usuario inválido");
            return;
        }
        
        // Resto de la lógica...
    }
    
    /**
     * Mostrar error
     */
    private static function mostrarError($mensaje) {
        echo '<br><div class="alert alert-danger">' . htmlspecialchars($mensaje) . '</div>';
    }
    
    /**
     * Mostrar éxito
     */
    private static function mostrarExito($mensaje) {
        echo '<script>
            swal({
                type: "success",
                title: "Éxito",
                text: "' . addslashes($mensaje) . '",
                showConfirmButton: true,
                confirmButtonText: "Cerrar"
            });
        </script>';
    }
}
```

---

## 2. Separar Lógica de Presentación

### 🔍 Problema Detectado

Controladores generan HTML y JavaScript directamente:

```php
// ❌ Controlador mezclado con presentación
echo '<script>
    swal({
        type: "success",
        title: "Usuarios",
        text: "¡El usuario ha sido guardado correctamente!",
        showConfirmButton: true,
        confirmButtonText: "Cerrar"
    });
    window.location.href = "usuarios";
</script>';
```

### ✅ Solución

#### Crear sistema de respuestas JSON

**Archivo**: `src/Utils/Response.php`

```php
<?php

namespace App\Utils;

class Response {
    
    /**
     * Enviar respuesta JSON
     */
    public static function json($data, $status = 200) {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    /**
     * Respuesta de éxito
     */
    public static function success($mensaje, $data = null) {
        return self::json([
            'success' => true,
            'mensaje' => $mensaje,
            'data' => $data
        ]);
    }
    
    /**
     * Respuesta de error
     */
    public static function error($mensaje, $codigo = 400) {
        return self::json([
            'success' => false,
            'mensaje' => $mensaje
        ], $codigo);
    }
    
    /**
     * Respuesta de validación fallida
     */
    public static function validationError($errores) {
        return self::json([
            'success' => false,
            'mensaje' => 'Errores de validación',
            'errores' => $errores
        ], 422);
    }
    
    /**
     * Redirección
     */
    public static function redirect($url) {
        header("Location: $url");
        exit;
    }
}
```

#### Refactorizar AJAX para usar JSON

**Archivo**: `ajax/usuarios.ajax.php` - VERSIÓN MEJORADA

```php
<?php

require_once "../vendor/autoload.php";
require_once "seguridad.ajax.php";

use App\Controllers\UsuariosController;
use App\Utils\Response;

// Verificar seguridad
SeguridadAjax::inicializar();

// Enrutamiento simple
$action = $_POST['action'] ?? $_GET['action'] ?? null;

try {
    switch ($action) {
        case 'crear':
            $resultado = UsuariosController::ctrCrearUsuarioAjax($_POST);
            Response::success('Usuario creado correctamente', $resultado);
            break;
            
        case 'editar':
            $resultado = UsuariosController::ctrEditarUsuarioAjax($_POST);
            Response::success('Usuario actualizado correctamente', $resultado);
            break;
            
        case 'eliminar':
            $resultado = UsuariosController::ctrEliminarUsuarioAjax($_POST);
            Response::success('Usuario eliminado correctamente');
            break;
            
        case 'listar':
            $resultado = UsuariosController::ctrListarUsuarios();
            Response::json($resultado);
            break;
            
        default:
            Response::error('Acción no válida', 400);
    }
    
} catch (Exception $e) {
    error_log("Error en usuarios.ajax.php: " . $e->getMessage());
    Response::error('Ocurrió un error inesperado', 500);
}
```

#### Actualizar JavaScript

**Archivo**: `vistas/js/usuarios.js` - MEJORA

```javascript
/**
 * Crear usuario vía AJAX
 */
function crearUsuario(formData) {
    formData.append('action', 'crear');
    formData.append('csrf_token', getCSRFToken());
    
    $.ajax({
        url: 'ajax/usuarios.ajax.php',
        method: 'POST',
        data: formData,
        cache: false,
        contentType: false,
        processData: false,
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                swal({
                    type: 'success',
                    title: 'Éxito',
                    text: response.mensaje,
                    showConfirmButton: true,
                    confirmButtonText: 'Cerrar'
                }).then(function() {
                    window.location.href = 'usuarios';
                });
            } else {
                swal({
                    type: 'error',
                    title: 'Error',
                    text: response.mensaje,
                    showConfirmButton: true,
                    confirmButtonText: 'Cerrar'
                });
            }
        },
        error: function(xhr, status, error) {
            console.error('Error AJAX:', error);
            swal({
                type: 'error',
                title: 'Error',
                text: 'Ocurrió un error inesperado',
                showConfirmButton: true,
                confirmButtonText: 'Cerrar'
            });
        }
    });
}
```

---

## 3. Manejo Centralizado de Errores

### 🔍 Problema

No hay manejo de excepciones ni logging estructurado

### ✅ Solución

**Archivo**: `src/Utils/ErrorHandler.php`

```php
<?php

namespace App\Utils;

class ErrorHandler {
    
    private static $logFile = __DIR__ . '/../../logs/app.log';
    
    /**
     * Registrar manejador de errores
     */
    public static function register() {
        set_error_handler([self::class, 'handleError']);
        set_exception_handler([self::class, 'handleException']);
        register_shutdown_function([self::class, 'handleShutdown']);
    }
    
    /**
     * Manejar errores
     */
    public static function handleError($errno, $errstr, $errfile, $errline) {
        if (!(error_reporting() & $errno)) {
            return false;
        }
        
        $mensaje = "Error [$errno]: $errstr en $errfile:$errline";
        self::log($mensaje, 'error');
        
        if ($_ENV['APP_DEBUG'] === 'true') {
            echo "<b>Error:</b> $errstr en <b>$errfile</b> línea <b>$errline</b><br>";
        }
        
        return true;
    }
    
    /**
     * Manejar excepciones
     */
    public static function handleException($exception) {
        $mensaje = sprintf(
            "Excepción no capturada: %s en %s:%d\nStack trace:\n%s",
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine(),
            $exception->getTraceAsString()
        );
        
        self::log($mensaje, 'critical');
        
        if ($_ENV['APP_DEBUG'] === 'true') {
            echo "<h1>Excepción</h1>";
            echo "<p><b>Mensaje:</b> " . $exception->getMessage() . "</p>";
            echo "<p><b>Archivo:</b> " . $exception->getFile() . "</p>";
            echo "<p><b>Línea:</b> " . $exception->getLine() . "</p>";
            echo "<pre>" . $exception->getTraceAsString() . "</pre>";
        } else {
            echo "<h1>Error del Sistema</h1>";
            echo "<p>Ha ocurrido un error inesperado. Por favor, contacte al administrador.</p>";
        }
        
        exit(1);
    }
    
    /**
     * Manejar errores fatales
     */
    public static function handleShutdown() {
        $error = error_get_last();
        
        if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
            $mensaje = "Error fatal: {$error['message']} en {$error['file']}:{$error['line']}";
            self::log($mensaje, 'critical');
            
            if ($_ENV['APP_DEBUG'] !== 'true') {
                echo "<h1>Error Fatal</h1>";
                echo "<p>La aplicación ha encontrado un error crítico.</p>";
            }
        }
    }
    
    /**
     * Registrar en log
     */
    public static function log($mensaje, $nivel = 'info') {
        $fecha = date('Y-m-d H:i:s');
        $nivel = strtoupper($nivel);
        $logLine = "[$fecha] [$nivel] $mensaje" . PHP_EOL;
        
        // Crear directorio si no existe
        $logDir = dirname(self::$logFile);
        if (!file_exists($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        file_put_contents(self::$logFile, $logLine, FILE_APPEND);
        
        // También enviar a syslog en producción
        if ($_ENV['APP_ENV'] === 'production') {
            error_log($mensaje);
        }
    }
}
```

#### Integrar en index.php

```php
<?php

require_once __DIR__ . '/../vendor/autoload.php';

// Cargar .env
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// ✅ Registrar manejador de errores
use App\Utils\ErrorHandler;
ErrorHandler::register();

// Resto del código...
```

---

## 4. Sistema de Configuración

### ✅ Solución

**Archivo**: `config/database.php`

```php
<?php

return [
    'host' => $_ENV['DB_HOST'] ?? 'localhost',
    'database' => $_ENV['DB_NAME'] ?? 'demo_db',
    'username' => $_ENV['DB_USER'] ?? 'root',
    'password' => $_ENV['DB_PASS'] ?? '',
    'charset' => $_ENV['DB_CHARSET'] ?? 'utf8mb4',
    'options' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
    ]
];
```

**Archivo**: `config/app.php`

```php
<?php

return [
    'name' => $_ENV['APP_NAME'] ?? 'ERP/POS',
    'env' => $_ENV['APP_ENV'] ?? 'production',
    'debug' => $_ENV['APP_DEBUG'] === 'true',
    'timezone' => $_ENV['APP_TIMEZONE'] ?? 'America/Argentina/Mendoza',
    'locale' => $_ENV['APP_LOCALE'] ?? 'es_AR',
    
    'session' => [
        'lifetime' => $_ENV['SESSION_LIFETIME'] ?? 7200,
        'path' => '/',
        'domain' => $_ENV['SESSION_DOMAIN'] ?? null,
        'secure' => $_ENV['SESSION_SECURE'] === 'true',
        'httponly' => true,
        'samesite' => 'Lax'
    ],
    
    'upload' => [
        'max_size' => $_ENV['UPLOAD_MAX_SIZE'] ?? 5242880, // 5MB
        'allowed_types' => ['image/jpeg', 'image/png', 'image/gif'],
        'path' => $_ENV['UPLOAD_PATH'] ?? 'vistas/img/usuarios/'
    ],
    
    'security' => [
        'password_cost' => 12,
        'csrf_token_name' => 'csrf_token',
        'max_login_attempts' => 5,
        'lockout_time' => 900 // 15 minutos
    ]
];
```

**Archivo**: `src/Utils/Config.php`

```php
<?php

namespace App\Utils;

class Config {
    
    private static $config = [];
    
    /**
     * Cargar archivo de configuración
     */
    public static function load($file) {
        $path = __DIR__ . '/../../config/' . $file . '.php';
        
        if (!file_exists($path)) {
            throw new \Exception("Archivo de configuración no encontrado: $file");
        }
        
        self::$config[$file] = require $path;
    }
    
    /**
     * Obtener valor de configuración
     */
    public static function get($key, $default = null) {
        $keys = explode('.', $key);
        $file = array_shift($keys);
        
        if (!isset(self::$config[$file])) {
            self::load($file);
        }
        
        $value = self::$config[$file];
        
        foreach ($keys as $k) {
            if (!isset($value[$k])) {
                return $default;
            }
            $value = $value[$k];
        }
        
        return $value;
    }
}

// Uso:
// Config::get('app.name')
// Config::get('database.host')
// Config::get('app.upload.max_size')
```

---

## 5. Helpers Globales

**Archivo**: `src/helpers.php`

```php
<?php

use App\Utils\Config;
use App\Utils\ErrorHandler;

/**
 * Funciones helper globales
 */

if (!function_exists('config')) {
    function config($key, $default = null) {
        return Config::get($key, $default);
    }
}

if (!function_exists('env')) {
    function env($key, $default = null) {
        return $_ENV[$key] ?? getenv($key) ?: $default;
    }
}

if (!function_exists('base_path')) {
    function base_path($path = '') {
        return __DIR__ . '/../' . ltrim($path, '/');
    }
}

if (!function_exists('public_path')) {
    function public_path($path = '') {
        return base_path('public/' . ltrim($path, '/'));
    }
}

if (!function_exists('view_path')) {
    function view_path($path = '') {
        return base_path('vistas/' . ltrim($path, '/'));
    }
}

if (!function_exists('asset')) {
    function asset($path) {
        $baseUrl = rtrim(env('APP_URL', '/'), '/');
        return $baseUrl . '/' . ltrim($path, '/');
    }
}

if (!function_exists('csrf_token')) {
    function csrf_token() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return $_SESSION['csrf_token'] ?? '';
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field() {
        return '<input type="hidden" name="csrf_token" value="' . csrf_token() . '">';
    }
}

if (!function_exists('old')) {
    function old($key, $default = '') {
        return $_SESSION['old'][$key] ?? $default;
    }
}

if (!function_exists('dd')) {
    function dd(...$vars) {
        foreach ($vars as $var) {
            echo '<pre>';
            var_dump($var);
            echo '</pre>';
        }
        die(1);
    }
}

if (!function_exists('logger')) {
    function logger($mensaje, $nivel = 'info') {
        ErrorHandler::log($mensaje, $nivel);
    }
}
```

---

## ✅ Checklist de Implementación

- [ ] Crear estructura de directorios src/
- [ ] Configurar composer.json con PSR-4
- [ ] Generar autoload con composer
- [ ] Crear archivos de configuración
- [ ] Migrar controladores a namespaces
- [ ] Migrar modelos a namespaces
- [ ] Crear clase Response
- [ ] Crear ErrorHandler
- [ ] Crear Config
- [ ] Crear helpers.php
- [ ] Actualizar index.php
- [ ] Actualizar archivos AJAX
- [ ] Actualizar JavaScript para JSON responses
- [ ] Probar cada módulo migrado
- [ ] Documentar nueva estructura

---

**Tiempo estimado**: 2-3 semanas  
**Prioridad**: 🟠 ALTA  
**Anterior**: [02-seguridad-sql.md](02-seguridad-sql.md)  
**Siguiente**: [04-optimizacion.md](04-optimizacion.md)

