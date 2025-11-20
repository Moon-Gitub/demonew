# 🚀 Modernización del Sistema

## Actualización de Tecnologías y Herramientas

---

## 1. Migración a PHP 8+

### 🔍 Estado Actual

El servidor usa PHP 8.4.11, pero el código está escrito para PHP 5.x/7.x

### ✅ Aprovechar Features de PHP 8

#### Named Arguments

**Antes:**
```php
function crearUsuario($nombre, $email, $password, $perfil, $sucursal, $puntos_venta, $listas_precio, $foto) {
    // ...
}

crearUsuario('Juan', 'juan@email.com', 'pass123', 'admin', 'sucursal1', '1,2', 'lista1', 'foto.jpg');
```

**Después:**
```php
function crearUsuario(
    string $nombre,
    string $email,
    string $password,
    string $perfil,
    ?string $sucursal = null,
    ?string $puntos_venta = null,
    ?string $listas_precio = null,
    ?string $foto = null
) {
    // ...
}

crearUsuario(
    nombre: 'Juan',
    email: 'juan@email.com',
    password: 'pass123',
    perfil: 'admin',
    foto: 'foto.jpg'
);
```

#### Union Types

```php
<?php

namespace App\Models;

class UsuariosModel {
    
    public static function mdlMostrarUsuarios(
        string $tabla,
        ?string $item,
        string|int|null $valor
    ): array|object|false {
        // ...
    }
}
```

#### Constructor Property Promotion

**Antes:**
```php
class ProductoDTO {
    private int $id;
    private string $codigo;
    private string $descripcion;
    private float $precio;
    
    public function __construct(int $id, string $codigo, string $descripcion, float $precio) {
        $this->id = $id;
        $this->codigo = $codigo;
        $this->descripcion = $descripcion;
        $this->precio = $precio;
    }
}
```

**Después:**
```php
class ProductoDTO {
    public function __construct(
        private int $id,
        private string $codigo,
        private string $descripcion,
        private float $precio
    ) {}
    
    public function getId(): int { return $this->id; }
    public function getCodigo(): string { return $this->codigo; }
    public function getDescripcion(): string { return $this->descripcion; }
    public function getPrecio(): float { return $this->precio; }
}
```

#### Match Expression

**Antes:**
```php
switch ($tipoComprobante) {
    case 1:
        $nombre = 'Factura A';
        break;
    case 2:
        $nombre = 'Factura B';
        break;
    case 3:
        $nombre = 'Factura C';
        break;
    default:
        $nombre = 'Desconocido';
}
```

**Después:**
```php
$nombre = match ($tipoComprobante) {
    1 => 'Factura A',
    2 => 'Factura B',
    3 => 'Factura C',
    default => 'Desconocido'
};
```

#### Nullsafe Operator

**Antes:**
```php
$nombreUsuario = null;
if ($venta !== null && $venta->getUsuario() !== null) {
    $nombreUsuario = $venta->getUsuario()->getNombre();
}
```

**Después:**
```php
$nombreUsuario = $venta?->getUsuario()?->getNombre();
```

#### Attributes (en lugar de anotaciones)

```php
<?php

namespace App\Controllers;

use App\Middleware\Auth;
use App\Middleware\CSRF;

class VentasController {
    
    #[Auth(requiredRole: 'vendedor')]
    #[CSRF]
    public function crear(array $datos): array {
        // ...
    }
}
```

---

## 2. Actualizar Frontend

### 🔍 Estado Actual

- Bower (deprecado desde 2017)
- AdminLTE 2.x
- jQuery 2.x
- Bootstrap 3.x

### ✅ Migración Recomendada

#### Opción 1: Mantener Stack Similar (Más fácil)

**Actualizar a versiones modernas:**

```bash
# Remover Bower
rm -rf vistas/bower_components
rm bower.json

# Inicializar npm
npm init -y

# Instalar dependencias modernas
npm install --save \
    jquery@3.7.1 \
    bootstrap@5.3.2 \
    admin-lte@3.2.0 \
    @fortawesome/fontawesome-free@6.5.1 \
    datatables.net@1.13.8 \
    datatables.net-bs5@1.13.8 \
    sweetalert2@11.10.2 \
    moment@2.29.4 \
    chart.js@4.4.1
```

**package.json:**
```json
{
  "name": "erp-pos-frontend",
  "version": "1.0.0",
  "scripts": {
    "dev": "webpack --mode development --watch",
    "build": "webpack --mode production",
    "copy-assets": "node scripts/copy-assets.js"
  },
  "dependencies": {
    "jquery": "^3.7.1",
    "bootstrap": "^5.3.2",
    "admin-lte": "^3.2.0",
    "@fortawesome/fontawesome-free": "^6.5.1",
    "datatables.net": "^1.13.8",
    "datatables.net-bs5": "^1.13.8",
    "sweetalert2": "^11.10.2",
    "moment": "^2.29.4",
    "chart.js": "^4.4.1"
  },
  "devDependencies": {
    "webpack": "^5.89.0",
    "webpack-cli": "^5.1.4",
    "css-loader": "^6.8.1",
    "style-loader": "^3.3.3",
    "mini-css-extract-plugin": "^2.7.6"
  }
}
```

#### Opción 2: Stack Moderno (Más trabajo)

**Con Vue.js o React:**

```bash
# Vue.js
npm install vue@3 vue-router@4 pinia axios

# O React
npm install react react-dom react-router-dom axios
```

---

## 3. Implementar Testing

### 🔍 Estado Actual

Sin tests automatizados

### ✅ Solución: PHPUnit

**Instalar PHPUnit:**

```bash
composer require --dev phpunit/phpunit ^9.5
```

**Archivo**: `phpunit.xml`

```xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:noNamespaceSchemaLocation="vendor/phpunit/phpunit/phpunit.xsd"
         bootstrap="vendor/autoload.php"
         colors="true">
    <testsuites>
        <testsuite name="Unit">
            <directory>tests/Unit</directory>
        </testsuite>
        <testsuite name="Feature">
            <directory>tests/Feature</directory>
        </testsuite>
    </testsuites>
    <coverage>
        <include>
            <directory suffix=".php">src</directory>
        </include>
    </coverage>
</phpunit>
```

**Ejemplo de Test**: `tests/Unit/ValidacionTest.php`

```php
<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Utils\Validacion;

class ValidacionTest extends TestCase {
    
    public function testValidarEmailCorrecto() {
        $email = 'test@ejemplo.com';
        $resultado = Validacion::validarEmail($email);
        
        $this->assertEquals($email, $resultado);
    }
    
    public function testValidarEmailIncorrecto() {
        $email = 'email-invalido';
        $resultado = Validacion::validarEmail($email);
        
        $this->assertFalse($resultado);
    }
    
    public function testValidarCUITValido() {
        $cuit = '20-12345678-9';
        $resultado = Validacion::validarCUIT($cuit);
        
        $this->assertTrue($resultado);
    }
    
    public function testSanitizarTexto() {
        $texto = '<script>alert("XSS")</script>Hola';
        $resultado = Validacion::sanitizarTexto($texto);
        
        $this->assertStringNotContainsString('<script>', $resultado);
        $this->assertStringContainsString('Hola', $resultado);
    }
}
```

**Ejemplo de Test**: `tests/Feature/UsuariosTest.php`

```php
<?php

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;
use App\Models\UsuariosModel;
use App\Utils\Seguridad;

class UsuariosTest extends TestCase {
    
    protected function setUp(): void {
        parent::setUp();
        // Setup de base de datos de prueba
    }
    
    public function testCrearUsuario() {
        $datos = [
            'nombre' => 'Usuario Test',
            'usuario' => 'usertest',
            'password' => Seguridad::hashPassword('password123'),
            'perfil' => 'vendedor',
            'sucursal' => 'sucursal1',
            'puntos_venta' => '1',
            'listas_precio' => 'precio_venta',
            'foto' => ''
        ];
        
        $resultado = UsuariosModel::mdlIngresarUsuario('usuarios', $datos);
        
        $this->assertEquals('ok', $resultado);
    }
    
    public function testMostrarUsuarioPorId() {
        $usuario = UsuariosModel::mdlMostrarUsuariosPorId(1);
        
        $this->assertIsArray($usuario);
        $this->assertArrayHasKey('id', $usuario);
        $this->assertArrayHasKey('nombre', $usuario);
        $this->assertArrayHasKey('usuario', $usuario);
    }
}
```

**Ejecutar tests:**

```bash
vendor/bin/phpunit

# Con coverage
vendor/bin/phpunit --coverage-html coverage/
```

---

## 4. Control de Versiones y Git

### ✅ Configuración Recomendada

**Archivo**: `.gitignore`

```gitignore
# Entorno
.env
.env.backup
.env.production

# Composer
/vendor/
composer.lock

# NPM
/node_modules/
package-lock.json
npm-debug.log

# Caché
/storage/cache/*
!/storage/cache/.gitkeep
/logs/*
!/logs/.gitkeep

# Archivos subidos
/vistas/img/usuarios/*
!/vistas/img/usuarios/.gitkeep
/vistas/img/productos/*
!/vistas/img/productos/.gitkeep

# IDE
.vscode/
.idea/
*.swp
*.swo
*~

# Sistema operativo
.DS_Store
Thumbs.db

# Build
/dist/
/build/
/public/build/

# Tests
/coverage/
.phpunit.result.cache

# Backups
*.sql
*.sql.gz
*.backup
```

**Archivo**: `.gitattributes`

```gitattributes
* text=auto

*.php text eol=lf
*.js text eol=lf
*.css text eol=lf
*.json text eol=lf
*.md text eol=lf
*.sql text eol=lf

*.jpg binary
*.png binary
*.gif binary
*.pdf binary
```

### Estructura de Branches Recomendada

```
main (producción)
├── develop (desarrollo)
│   ├── feature/nueva-funcionalidad
│   ├── feature/modulo-reportes
│   └── bugfix/correccion-ventas
└── hotfix/seguridad-critica
```

**Workflow sugerido:**

```bash
# Nueva feature
git checkout develop
git pull origin develop
git checkout -b feature/nueva-funcionalidad
# ... hacer cambios ...
git add .
git commit -m "feat: agregar nueva funcionalidad"
git push origin feature/nueva-funcionalidad
# Crear Pull Request a develop

# Bugfix
git checkout develop
git checkout -b bugfix/correccion-ventas
# ... hacer cambios ...
git commit -m "fix: corregir cálculo de total en ventas"
git push origin bugfix/correccion-ventas

# Hotfix urgente
git checkout main
git checkout -b hotfix/seguridad-critica
# ... hacer cambios ...
git commit -m "security: corregir vulnerabilidad SQL"
git push origin hotfix/seguridad-critica
# Mergear a main Y develop
```

---

## 5. CI/CD - Integración y Deployment Continuos

### ✅ GitHub Actions

**Archivo**: `.github/workflows/tests.yml`

```yaml
name: Tests

on:
  push:
    branches: [ develop, main ]
  pull_request:
    branches: [ develop, main ]

jobs:
  test:
    runs-on: ubuntu-latest
    
    services:
      mysql:
        image: mysql:8.0
        env:
          MYSQL_ROOT_PASSWORD: root
          MYSQL_DATABASE: test_db
        ports:
          - 3306:3306
        options: --health-cmd="mysqladmin ping" --health-interval=10s --health-timeout=5s --health-retries=3
    
    steps:
    - uses: actions/checkout@v3
    
    - name: Setup PHP
      uses: shivammathur/setup-php@v2
      with:
        php-version: '8.4'
        extensions: mbstring, pdo, pdo_mysql
        coverage: xdebug
    
    - name: Install Composer Dependencies
      run: composer install --prefer-dist --no-progress
    
    - name: Copy .env
      run: cp .env.example .env
    
    - name: Run Tests
      run: vendor/bin/phpunit --coverage-text
      env:
        DB_HOST: 127.0.0.1
        DB_PORT: 3306
        DB_DATABASE: test_db
        DB_USERNAME: root
        DB_PASSWORD: root
```

**Archivo**: `.github/workflows/deploy.yml`

```yaml
name: Deploy to Production

on:
  push:
    branches: [ main ]

jobs:
  deploy:
    runs-on: ubuntu-latest
    
    steps:
    - uses: actions/checkout@v3
    
    - name: Deploy to Server
      uses: appleboy/ssh-action@master
      with:
        host: ${{ secrets.SERVER_HOST }}
        username: ${{ secrets.SERVER_USER }}
        key: ${{ secrets.SERVER_SSH_KEY }}
        script: |
          cd /home/cluna/Documentos/Moon-Desarrollos/public_html
          git pull origin main
          composer install --no-dev --optimize-autoloader
          php artisan migrate --force
          php artisan cache:clear
```

---

## 6. Documentación con API Documentation

### ✅ Swagger/OpenAPI

**Archivo**: `docs/api/swagger.yaml`

```yaml
openapi: 3.0.0
info:
  title: ERP/POS API
  version: 1.0.0
  description: API para sistema ERP/POS

servers:
  - url: http://localhost/ajax
    description: Servidor de desarrollo

paths:
  /usuarios.ajax.php:
    post:
      summary: Gestión de usuarios
      parameters:
        - in: query
          name: action
          schema:
            type: string
            enum: [crear, editar, eliminar, listar]
          required: true
      requestBody:
        content:
          application/json:
            schema:
              type: object
              properties:
                nombre:
                  type: string
                usuario:
                  type: string
                password:
                  type: string
                perfil:
                  type: string
      responses:
        '200':
          description: Operación exitosa
          content:
            application/json:
              schema:
                type: object
                properties:
                  success:
                    type: boolean
                  mensaje:
                    type: string
                  data:
                    type: object
```

---

## 7. Monitoreo y Logs

### ✅ Implementar Monolog

```bash
composer require monolog/monolog
```

**Archivo**: `src/Utils/Logger.php`

```php
<?php

namespace App\Utils;

use Monolog\Logger as MonologLogger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Formatter\LineFormatter;

class Logger {
    
    private static $instances = [];
    
    public static function channel($name = 'app') {
        if (!isset(self::$instances[$name])) {
            self::$instances[$name] = self::createLogger($name);
        }
        
        return self::$instances[$name];
    }
    
    private static function createLogger($name) {
        $logger = new MonologLogger($name);
        
        // Handler para archivos rotativos
        $handler = new RotatingFileHandler(
            base_path("logs/$name.log"),
            30, // Mantener 30 días
            MonologLogger::DEBUG
        );
        
        // Formato personalizado
        $formatter = new LineFormatter(
            "[%datetime%] %channel%.%level_name%: %message% %context%\n",
            "Y-m-d H:i:s"
        );
        
        $handler->setFormatter($formatter);
        $logger->pushHandler($handler);
        
        return $logger;
    }
}

// Uso:
// Logger::channel('ventas')->info('Venta creada', ['id' => 123]);
// Logger::channel('seguridad')->warning('Intento de login fallido', ['usuario' => 'test']);
// Logger::channel('app')->error('Error inesperado', ['exception' => $e]);
```

---

## ✅ Checklist de Implementación

- [ ] Actualizar código para PHP 8+ features
- [ ] Agregar type hints a todas las funciones
- [ ] Usar named arguments donde corresponda
- [ ] Migrar de switch a match
- [ ] Implementar nullsafe operator
- [ ] Remover Bower
- [ ] Instalar npm y dependencias modernas
- [ ] Actualizar a AdminLTE 3.x
- [ ] Actualizar Bootstrap a 5.x
- [ ] Configurar PHPUnit
- [ ] Escribir tests unitarios
- [ ] Escribir tests de integración
- [ ] Configurar .gitignore correctamente
- [ ] Implementar GitHub Actions
- [ ] Configurar Monolog
- [ ] Documentar API con Swagger

---

**Tiempo estimado**: 4-6 semanas  
**Prioridad**: 🟢 BAJA (pero importante para futuro)  
**Anterior**: [04-optimizacion.md](04-optimizacion.md)  
**Siguiente**: [06-base-datos.md](06-base-datos.md)

