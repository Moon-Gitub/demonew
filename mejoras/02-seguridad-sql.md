# 🔒 Seguridad SQL - Alta Prioridad

## Prevención de Inyección SQL

---

## 1. Consultas Dinámicas con Interpolación

### 🔍 Problema Detectado

**Archivos afectados**: Todos los modelos

**Ejemplo**: `modelos/usuarios.modelo.php` (líneas 14, 24, 76, 108)

```php
// ❌ VULNERABLE A SQL INJECTION
$stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla WHERE $item = :$item");
```

Si `$tabla` o `$item` vienen de entrada del usuario, pueden inyectar código SQL:
```
$tabla = "usuarios; DROP TABLE productos--"
```

### ⚡ Riesgo
- **Nivel**: CRÍTICO
- **Impacto**: Lectura/modificación/eliminación no autorizada de datos
- **Vector**: Manipulación de parámetros en peticiones

### ✅ Solución

#### Opción 1: Whitelist de Tablas y Columnas (RECOMENDADA)

**Archivo**: `modelos/validador-sql.modelo.php`

```php
<?php

class ModeloValidadorSQL {
    
    // Tablas permitidas
    const TABLAS_PERMITIDAS = [
        'usuarios',
        'productos',
        'categorias',
        'clientes',
        'proveedores',
        'ventas',
        'compras',
        'cajas',
        'caja_cierres',
        'empresa',
        'pedidos',
        'presupuestos',
        'clientes_cuenta_corriente',
        'proveedores_cuenta_corriente',
        'ventas_factura',
        'productos_historial'
    ];
    
    // Columnas comunes permitidas por tabla
    const COLUMNAS_PERMITIDAS = [
        'usuarios' => ['id', 'nombre', 'usuario', 'password', 'perfil', 'sucursal', 'puntos_venta', 'listas_precio', 'foto', 'estado', 'ultimo_login', 'fecha'],
        'productos' => ['id', 'id_categoria', 'codigo', 'id_proveedor', 'descripcion', 'imagen', 'stock', 'deposito', 'stock_medio', 'stock_bajo', 'precio_compra', 'precio_compra_dolar', 'margen_ganancia', 'precio_venta_neto', 'tipo_iva', 'precio_venta', 'precio_venta_mayorista', 'ventas', 'fecha', 'nombre_usuario', 'cambio_desde'],
        'categorias' => ['id', 'categoria', 'fecha'],
        'clientes' => ['id', 'nombre', 'tipo_documento', 'documento', 'condicion_iva', 'email', 'telefono', 'direccion', 'fecha_nacimiento', 'compras', 'ultima_compra', 'fecha', 'observaciones'],
        'ventas' => ['id', 'uuid', 'codigo', 'cbte_tipo', 'id_cliente', 'id_vendedor', 'productos', 'neto', 'neto_gravado', 'impuesto', 'total', 'metodo_pago', 'estado', 'observaciones', 'fecha', 'pto_vta'],
        // Agregar más según necesidad
    ];
    
    /**
     * Validar nombre de tabla
     */
    static public function validarTabla($tabla) {
        if (!in_array($tabla, self::TABLAS_PERMITIDAS)) {
            error_log("Intento de acceso a tabla no permitida: $tabla");
            throw new Exception("Tabla no permitida");
        }
        return $tabla;
    }
    
    /**
     * Validar nombre de columna
     */
    static public function validarColumna($tabla, $columna) {
        // Si no hay restricción específica para la tabla, validar formato
        if (!isset(self::COLUMNAS_PERMITIDAS[$tabla])) {
            // Solo permitir caracteres alfanuméricos y guión bajo
            if (!preg_match('/^[a-zA-Z0-9_]+$/', $columna)) {
                error_log("Intento de usar columna con caracteres inválidos: $columna");
                throw new Exception("Columna inválida");
            }
            return $columna;
        }
        
        // Verificar contra whitelist
        if (!in_array($columna, self::COLUMNAS_PERMITIDAS[$tabla])) {
            error_log("Intento de acceso a columna no permitida: $columna en tabla $tabla");
            throw new Exception("Columna no permitida");
        }
        
        return $columna;
    }
    
    /**
     * Validar orden (ASC/DESC)
     */
    static public function validarOrden($orden) {
        $orden = strtoupper($orden);
        if (!in_array($orden, ['ASC', 'DESC'])) {
            return 'ASC';
        }
        return $orden;
    }
    
    /**
     * Sanitizar LIKE query
     */
    static public function sanitizarLike($valor) {
        // Escapar caracteres especiales de LIKE
        $valor = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $valor);
        return $valor;
    }
}
```

#### Refactorizar modelos existentes

**Archivo**: `modelos/usuarios.modelo.php` - VERSIÓN SEGURA

```php
<?php

require_once "conexion.php";
require_once "validador-sql.modelo.php";

class ModeloUsuarios {

    /**
     * MOSTRAR USUARIOS - VERSIÓN SEGURA
     */
    static public function mdlMostrarUsuarios($tabla, $item, $valor) {
        
        try {
            // ✅ Validar tabla
            $tabla = ModeloValidadorSQL::validarTabla($tabla);
            
            $pdo = Conexion::conectar();
            
            if ($item != null) {
                // ✅ Validar columna
                $item = ModeloValidadorSQL::validarColumna($tabla, $item);
                
                // ✅ Usar prepared statement correctamente
                $stmt = $pdo->prepare("SELECT * FROM `$tabla` WHERE `$item` = :valor");
                $stmt->bindParam(":valor", $valor, PDO::PARAM_STR);
                $stmt->execute();
                
                return $stmt->fetch();
                
            } else {
                $stmt = $pdo->prepare("SELECT * FROM `$tabla`");
                $stmt->execute();
                
                return $stmt->fetchAll();
            }
            
        } catch (Exception $e) {
            error_log("Error en mdlMostrarUsuarios: " . $e->getMessage());
            return false;
        }
    }

    /**
     * INGRESAR USUARIO - VERSIÓN SEGURA
     */
    static public function mdlIngresarUsuario($tabla, $datos) {
        
        try {
            $tabla = ModeloValidadorSQL::validarTabla($tabla);
            $pdo = Conexion::conectar();
            
            $stmt = $pdo->prepare("
                INSERT INTO `$tabla` (
                    nombre, usuario, password, perfil, sucursal, 
                    puntos_venta, listas_precio, foto
                ) VALUES (
                    :nombre, :usuario, :password, :perfil, :sucursal, 
                    :puntos_venta, :listas_precio, :foto
                )
            ");
            
            $stmt->bindParam(":nombre", $datos["nombre"], PDO::PARAM_STR);
            $stmt->bindParam(":usuario", $datos["usuario"], PDO::PARAM_STR);
            $stmt->bindParam(":password", $datos["password"], PDO::PARAM_STR);
            $stmt->bindParam(":perfil", $datos["perfil"], PDO::PARAM_STR);
            $stmt->bindParam(":sucursal", $datos["sucursal"], PDO::PARAM_STR);
            $stmt->bindParam(":puntos_venta", $datos["puntos_venta"], PDO::PARAM_STR);
            $stmt->bindParam(":listas_precio", $datos["listas_precio"], PDO::PARAM_STR);
            $stmt->bindParam(":foto", $datos["foto"], PDO::PARAM_STR);
            
            if ($stmt->execute()) {
                return "ok";
            } else {
                return $stmt->errorInfo();
            }
            
        } catch (Exception $e) {
            error_log("Error en mdlIngresarUsuario: " . $e->getMessage());
            return ["error" => $e->getMessage()];
        }
    }

    /**
     * EDITAR USUARIO - VERSIÓN SEGURA
     */
    static public function mdlEditarUsuario($tabla, $datos) {
        
        try {
            $tabla = ModeloValidadorSQL::validarTabla($tabla);
            $pdo = Conexion::conectar();
            
            $stmt = $pdo->prepare("
                UPDATE `$tabla` 
                SET nombre = :nombre, 
                    password = :password, 
                    perfil = :perfil, 
                    sucursal = :sucursal, 
                    puntos_venta = :puntos_venta, 
                    listas_precio = :listas_precio, 
                    foto = :foto 
                WHERE usuario = :usuario
            ");
            
            $stmt->bindParam(":nombre", $datos["nombre"], PDO::PARAM_STR);
            $stmt->bindParam(":password", $datos["password"], PDO::PARAM_STR);
            $stmt->bindParam(":perfil", $datos["perfil"], PDO::PARAM_STR);
            $stmt->bindParam(":sucursal", $datos["sucursal"], PDO::PARAM_STR);
            $stmt->bindParam(":puntos_venta", $datos["puntos_venta"], PDO::PARAM_STR);
            $stmt->bindParam(":listas_precio", $datos["listas_precio"], PDO::PARAM_STR);
            $stmt->bindParam(":foto", $datos["foto"], PDO::PARAM_STR);
            $stmt->bindParam(":usuario", $datos["usuario"], PDO::PARAM_STR);
            
            if ($stmt->execute()) {
                return "ok";
            } else {
                return $stmt->errorInfo();
            }
            
        } catch (Exception $e) {
            error_log("Error en mdlEditarUsuario: " . $e->getMessage());
            return ["error" => $e->getMessage()];
        }
    }

    /**
     * ACTUALIZAR USUARIO - VERSIÓN SEGURA
     */
    static public function mdlActualizarUsuario($tabla, $item1, $valor1, $item2, $valor2) {
        
        try {
            $tabla = ModeloValidadorSQL::validarTabla($tabla);
            $item1 = ModeloValidadorSQL::validarColumna($tabla, $item1);
            $item2 = ModeloValidadorSQL::validarColumna($tabla, $item2);
            
            $pdo = Conexion::conectar();
            
            // ✅ Usar placeholders con nombres únicos
            $stmt = $pdo->prepare("UPDATE `$tabla` SET `$item1` = :valor1 WHERE `$item2` = :valor2");
            
            $stmt->bindParam(":valor1", $valor1, PDO::PARAM_STR);
            $stmt->bindParam(":valor2", $valor2, PDO::PARAM_STR);
            
            if ($stmt->execute()) {
                return "ok";
            } else {
                return "error";
            }
            
        } catch (Exception $e) {
            error_log("Error en mdlActualizarUsuario: " . $e->getMessage());
            return "error";
        }
    }

    /**
     * BORRAR USUARIO - VERSIÓN SEGURA
     */
    static public function mdlBorrarUsuario($tabla, $datos) {
        
        try {
            $tabla = ModeloValidadorSQL::validarTabla($tabla);
            $pdo = Conexion::conectar();
            
            $stmt = $pdo->prepare("DELETE FROM `$tabla` WHERE id = :id");
            $stmt->bindParam(":id", $datos, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                return "ok";
            } else {
                return "error";
            }
            
        } catch (Exception $e) {
            error_log("Error en mdlBorrarUsuario: " . $e->getMessage());
            return "error";
        }
    }

    /**
     * MOSTRAR USUARIOS POR ID - VERSIÓN SEGURA
     */
    static public function mdlMostrarUsuariosPorId($idUsuario) {
        
        try {
            $pdo = Conexion::conectar();
            
            $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = :id");
            $stmt->bindParam(":id", $idUsuario, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetch();
            
        } catch (Exception $e) {
            error_log("Error en mdlMostrarUsuariosPorId: " . $e->getMessage());
            return false;
        }
    }
}
```

---

## 2. Validación de Entrada

### 🔍 Problema Detectado

Validación inconsistente de datos de entrada

**Archivo**: `controladores/usuarios.controlador.php`

```php
// ❌ Solo usa regex, no sanitiza
if(preg_match('/^[a-zA-Z0-9]+$/', $_POST["ingUsuario"])){
    // usar directamente
}
```

### ✅ Solución

**Archivo**: `modelos/validacion.modelo.php`

```php
<?php

class ModeloValidacion {
    
    /**
     * Validar y sanitizar texto general
     */
    static public function sanitizarTexto($texto, $maxLength = 255) {
        $texto = trim($texto);
        $texto = strip_tags($texto);
        $texto = htmlspecialchars($texto, ENT_QUOTES, 'UTF-8');
        return substr($texto, 0, $maxLength);
    }
    
    /**
     * Validar email
     */
    static public function validarEmail($email) {
        $email = filter_var($email, FILTER_SANITIZE_EMAIL);
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }
        
        return $email;
    }
    
    /**
     * Validar número entero
     */
    static public function validarEntero($numero, $min = null, $max = null) {
        $numero = filter_var($numero, FILTER_VALIDATE_INT);
        
        if ($numero === false) {
            return false;
        }
        
        if ($min !== null && $numero < $min) {
            return false;
        }
        
        if ($max !== null && $numero > $max) {
            return false;
        }
        
        return $numero;
    }
    
    /**
     * Validar número decimal
     */
    static public function validarDecimal($numero) {
        $numero = filter_var($numero, FILTER_VALIDATE_FLOAT);
        return $numero !== false ? $numero : false;
    }
    
    /**
     * Validar fecha
     */
    static public function validarFecha($fecha, $formato = 'Y-m-d') {
        $d = DateTime::createFromFormat($formato, $fecha);
        return $d && $d->format($formato) === $fecha;
    }
    
    /**
     * Validar CUIT/CUIL (Argentina)
     */
    static public function validarCUIT($cuit) {
        // Remover guiones y espacios
        $cuit = preg_replace('/[^0-9]/', '', $cuit);
        
        // Debe tener 11 dígitos
        if (strlen($cuit) != 11) {
            return false;
        }
        
        // Validar dígito verificador
        $acumulado = 0;
        $digitos = str_split($cuit);
        $multiplicadores = [5, 4, 3, 2, 7, 6, 5, 4, 3, 2];
        
        for ($i = 0; $i < 10; $i++) {
            $acumulado += $digitos[$i] * $multiplicadores[$i];
        }
        
        $verificador = 11 - ($acumulado % 11);
        if ($verificador == 11) $verificador = 0;
        if ($verificador == 10) $verificador = 9;
        
        return $verificador == $digitos[10];
    }
    
    /**
     * Sanitizar JSON
     */
    static public function sanitizarJSON($json) {
        if (is_string($json)) {
            $json = json_decode($json, true);
        }
        
        if (!is_array($json)) {
            return false;
        }
        
        return $json;
    }
    
    /**
     * Validar nombre de usuario
     */
    static public function validarUsername($username) {
        // Solo letras, números y guión bajo, 3-20 caracteres
        if (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username)) {
            return false;
        }
        return $username;
    }
    
    /**
     * Validar contraseña fuerte
     */
    static public function validarPasswordFuerte($password) {
        // Mínimo 8 caracteres, al menos una mayúscula, una minúscula y un número
        if (strlen($password) < 8) {
            return ['valido' => false, 'mensaje' => 'La contraseña debe tener al menos 8 caracteres'];
        }
        
        if (!preg_match('/[A-Z]/', $password)) {
            return ['valido' => false, 'mensaje' => 'La contraseña debe tener al menos una mayúscula'];
        }
        
        if (!preg_match('/[a-z]/', $password)) {
            return ['valido' => false, 'mensaje' => 'La contraseña debe tener al menos una minúscula'];
        }
        
        if (!preg_match('/[0-9]/', $password)) {
            return ['valido' => false, 'mensaje' => 'La contraseña debe tener al menos un número'];
        }
        
        return ['valido' => true];
    }
    
    /**
     * Prevenir XSS en output
     */
    static public function escaparHTML($texto) {
        return htmlspecialchars($texto, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
}
```

#### Actualizar controladores para usar validación

**Ejemplo en `controladores/usuarios.controlador.php`**:

```php
static public function ctrIngresoUsuario(){
    if(isset($_POST["ingUsuario"])){
        
        // ✅ Validar y sanitizar
        $usuario = ModeloValidacion::validarUsername($_POST["ingUsuario"]);
        
        if(!$usuario){
            echo '<br><div class="alert alert-danger">Usuario inválido</div>';
            return;
        }
        
        if(empty($_POST["ingPassword"])){
            echo '<br><div class="alert alert-danger">Contraseña requerida</div>';
            return;
        }
        
        // ✅ Verificar si está bloqueado
        if(ModeloLogin::estaBloqueado($usuario)){
            $tiempo = ceil(ModeloLogin::tiempoRestanteBloqueo($usuario) / 60);
            echo '<br><div class="alert alert-danger">Cuenta bloqueada. Intente en ' . $tiempo . ' minutos</div>';
            return;
        }
        
        $tabla = "usuarios";
        $item = "usuario";
        
        $respuesta = ModeloUsuarios::MdlMostrarUsuarios($tabla, $item, $usuario);
        
        if($respuesta && ModeloSeguridad::verifyPassword($_POST["ingPassword"], $respuesta["password"])){
            
            if($respuesta["estado"] == 1){
                // ✅ Resetear intentos
                ModeloLogin::resetearIntentos($usuario);
                
                // ... código de sesión ...
                
            } else {
                echo '<br><div class="alert alert-danger">El usuario aún no está activado</div>';
            }
            
        } else {
            // ✅ Registrar intento fallido
            ModeloLogin::registrarIntentoFallido($usuario);
            echo '<br><div class="alert alert-danger">Error al ingresar, vuelve a intentarlo</div>';
        }
    }
}
```

---

## 3. Preparación de Queries Complejas

### 🔍 Problema

Queries con múltiples parámetros o condiciones dinámicas

### ✅ Solución

**Archivo**: `modelos/query-builder.modelo.php`

```php
<?php

class ModeloQueryBuilder {
    
    private $select = '*';
    private $from = '';
    private $where = [];
    private $params = [];
    private $orderBy = '';
    private $limit = '';
    private $joins = [];
    
    /**
     * SELECT
     */
    public function select($columns = '*') {
        $this->select = $columns;
        return $this;
    }
    
    /**
     * FROM
     */
    public function from($tabla) {
        $this->from = ModeloValidadorSQL::validarTabla($tabla);
        return $this;
    }
    
    /**
     * WHERE
     */
    public function where($columna, $operador, $valor) {
        $columna = ModeloValidadorSQL::validarColumna($this->from, $columna);
        
        $placeholder = ':param' . count($this->params);
        $this->where[] = "`$columna` $operador $placeholder";
        $this->params[$placeholder] = $valor;
        
        return $this;
    }
    
    /**
     * WHERE IN
     */
    public function whereIn($columna, $valores) {
        $columna = ModeloValidadorSQL::validarColumna($this->from, $columna);
        
        $placeholders = [];
        foreach ($valores as $key => $valor) {
            $placeholder = ':param' . count($this->params);
            $placeholders[] = $placeholder;
            $this->params[$placeholder] = $valor;
        }
        
        $this->where[] = "`$columna` IN (" . implode(',', $placeholders) . ")";
        return $this;
    }
    
    /**
     * ORDER BY
     */
    public function orderBy($columna, $direccion = 'ASC') {
        $columna = ModeloValidadorSQL::validarColumna($this->from, $columna);
        $direccion = ModeloValidadorSQL::validarOrden($direccion);
        
        $this->orderBy = "ORDER BY `$columna` $direccion";
        return $this;
    }
    
    /**
     * LIMIT
     */
    public function limit($limit, $offset = 0) {
        $limit = (int)$limit;
        $offset = (int)$offset;
        
        $this->limit = "LIMIT $offset, $limit";
        return $this;
    }
    
    /**
     * Ejecutar query
     */
    public function get() {
        $sql = "SELECT {$this->select} FROM `{$this->from}`";
        
        if (!empty($this->joins)) {
            $sql .= ' ' . implode(' ', $this->joins);
        }
        
        if (!empty($this->where)) {
            $sql .= ' WHERE ' . implode(' AND ', $this->where);
        }
        
        if ($this->orderBy) {
            $sql .= ' ' . $this->orderBy;
        }
        
        if ($this->limit) {
            $sql .= ' ' . $this->limit;
        }
        
        $pdo = Conexion::conectar();
        $stmt = $pdo->prepare($sql);
        
        foreach ($this->params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Obtener primer resultado
     */
    public function first() {
        $this->limit(1);
        $result = $this->get();
        return $result[0] ?? null;
    }
    
    /**
     * Contar resultados
     */
    public function count() {
        $this->select = 'COUNT(*) as total';
        $result = $this->first();
        return $result['total'] ?? 0;
    }
}

// Ejemplo de uso:
/*
$qb = new ModeloQueryBuilder();
$usuarios = $qb->from('usuarios')
               ->where('perfil', '=', 'administrador')
               ->where('estado', '=', 1)
               ->orderBy('nombre', 'ASC')
               ->limit(10)
               ->get();
*/
```

---

## ✅ Checklist de Implementación

- [ ] Crear ModeloValidadorSQL con whitelists
- [ ] Refactorizar ModeloUsuarios
- [ ] Refactorizar ModeloProductos
- [ ] Refactorizar ModeloCategorias
- [ ] Refactorizar ModeloClientes
- [ ] Refactorizar ModeloProveedores
- [ ] Refactorizar ModeloVentas
- [ ] Refactorizar ModeloCompras
- [ ] Crear ModeloValidacion
- [ ] Actualizar controladores para usar validación
- [ ] Crear ModeloQueryBuilder (opcional pero recomendado)
- [ ] Probar cada módulo refactorizado
- [ ] Realizar pruebas de penetración
- [ ] Documentar cambios

---

## 🧪 Tests de Seguridad Recomendados

1. **SQLMap**: Herramienta para detectar inyección SQL
```bash
sqlmap -u "http://localhost/ajax/productos.ajax.php" --data="idProducto=1" --level=5 --risk=3
```

2. **Burp Suite**: Para pruebas manuales de inyección

3. **Manual**: Intentar inyectar en cada campo:
   - `' OR '1'='1`
   - `1; DROP TABLE usuarios--`
   - `1 UNION SELECT * FROM usuarios--`

---

**Tiempo estimado**: 1-2 semanas  
**Prioridad**: 🟠 ALTA  
**Anterior**: [01-seguridad-critica.md](01-seguridad-critica.md)  
**Siguiente**: [03-arquitectura.md](03-arquitectura.md)

