<?php
// Script de prueba para diagnosticar el error 500
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

header('Content-Type: text/html; charset=utf-8');

echo "<h2>Diagnóstico de impresión</h2>";

// 1. Verificar autoload
$autoloadPath = dirname(__DIR__, 3) . "/autoload.php";
echo "<p>1. Autoload path: " . $autoloadPath . "</p>";
echo "<p>   Existe: " . (file_exists($autoloadPath) ? "SÍ" : "NO") . "</p>";

if (file_exists($autoloadPath)) {
    require_once $autoloadPath;
    echo "<p>   ✅ Autoload cargado</p>";
} else {
    die("<p>❌ Error: No se encuentra autoload.php</p>");
}

// 2. Verificar .env
$raiz = dirname(__DIR__, 3);
if (class_exists('Dotenv\\Dotenv') && file_exists($raiz . "/.env")) {
    $dotenv = Dotenv\Dotenv::createImmutable($raiz);
    $dotenv->safeLoad();
    echo "<p>2. ✅ .env cargado</p>";
} else {
    echo "<p>2. ⚠️ .env no encontrado o Dotenv no disponible</p>";
}

// 3. Verificar sesión
if (isset($_GET['PHPSESSID']) && !empty($_GET['PHPSESSID'])) {
    session_id($_GET['PHPSESSID']);
    echo "<p>3. Session ID establecido: " . htmlspecialchars($_GET['PHPSESSID']) . "</p>";
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
    echo "<p>   ✅ Sesión iniciada</p>";
} else {
    echo "<p>   ⚠️ Sesión ya estaba iniciada</p>";
}

// 4. Verificar productos en sesión
echo "<p>4. Productos en sesión:</p>";
if (isset($_SESSION['productos_impresion'])) {
    echo "<pre>" . print_r($_SESSION['productos_impresion'], true) . "</pre>";
} else {
    echo "<p>   ⚠️ No hay productos en sesión</p>";
}

// 5. Verificar productos en GET
echo "<p>5. Productos en GET (ids):</p>";
if (isset($_GET['ids'])) {
    $idsJson = json_decode(urldecode($_GET['ids']), true);
    if (json_last_error() === JSON_ERROR_NONE) {
        echo "<pre>" . print_r($idsJson, true) . "</pre>";
    } else {
        echo "<p>   ❌ Error al decodificar JSON: " . json_last_error_msg() . "</p>";
    }
} else {
    echo "<p>   ⚠️ No hay parámetro ids en GET</p>";
}

// 6. Verificar controladores
// Desde: extensiones/vendor/tecnickcom/tcpdf/pdf/
// Hacia: controladores/ (en la raíz del proyecto)
// Necesitamos subir 5 niveles: ../../../../../controladores/
$controladorPath = __DIR__ . "/../../../../../controladores/productos.controlador.php";
echo "<p>6. Controlador path: " . $controladorPath . "</p>";
echo "<p>   Existe: " . (file_exists($controladorPath) ? "SÍ" : "NO") . "</p>";

if (file_exists($controladorPath)) {
    require_once $controladorPath;
    echo "<p>   ✅ Controlador cargado</p>";
    
    // 7. Probar obtener un producto
    echo "<p>7. Probando obtener producto con ID 4073:</p>";
    try {
        $producto = ControladorProductos::ctrMostrarProductos('id', 4073, 'id');
        if ($producto) {
            echo "<p>   ✅ Producto encontrado: " . htmlspecialchars($producto['descripcion'] ?? 'N/A') . "</p>";
        } else {
            echo "<p>   ⚠️ Producto no encontrado</p>";
        }
    } catch (Exception $e) {
        echo "<p>   ❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    } catch (Error $e) {
        echo "<p>   ❌ Error fatal: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
} else {
    echo "<p>   ❌ Error: No se encuentra productos.controlador.php</p>";
}

echo "<hr>";
echo "<p><strong>Resumen:</strong> Si todos los pasos muestran ✅, el problema está en la generación del PDF.</p>";
?>
