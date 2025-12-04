<?php
/**
 * TEST MANUAL DE DOTENV
 * 
 * Este archivo prueba específicamente la carga del .env
 * para diagnosticar problemas con getenv() vs $_ENV vs $_SERVER
 */

echo "<pre>";
echo "═══════════════════════════════════════\n";
echo "TEST MANUAL DE DOTENV\n";
echo "═══════════════════════════════════════\n\n";

require_once "extensiones/vendor/autoload.php";

echo "1. Autoload cargado: ✅\n\n";

$envPath = __DIR__ . '/.env';
echo "2. Ruta del .env: $envPath\n";
echo "   Existe: " . (file_exists($envPath) ? '✅ SÍ' : '❌ NO') . "\n\n";

echo "3. Clase Dotenv existe: " . (class_exists('Dotenv\Dotenv') ? '✅ SÍ' : '❌ NO') . "\n\n";

if (class_exists('Dotenv\Dotenv')) {
    echo "4. Intentando cargar .env...\n";
    try {
        $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
        $dotenv->load();
        echo "   ✅ load() ejecutado sin errores\n\n";
    } catch (Exception $e) {
        echo "   ❌ ERROR: " . $e->getMessage() . "\n\n";
    }
}

echo "5. Probando getenv():\n";
echo "   (getenv() lee variables del sistema operativo)\n";
$vars = ['MOON_CLIENTE_ID', 'DB_HOST', 'DB_NAME', 'MP_PUBLIC_KEY'];
$getenvCount = 0;
foreach ($vars as $var) {
    $value = getenv($var);
    if ($value) $getenvCount++;
    echo "   $var = " . ($value ? "✅ $value" : "❌ NO DEFINIDO") . "\n";
}
echo "   Total encontradas: $getenvCount/" . count($vars) . "\n\n";

echo "6. Probando \$_ENV:\n";
echo "   (\$_ENV es el array de variables de entorno de PHP)\n";
$envCount = 0;
foreach ($vars as $var) {
    $value = isset($_ENV[$var]) ? $_ENV[$var] : null;
    if ($value) $envCount++;
    echo "   \$_ENV['$var'] = " . ($value ? "✅ $value" : "❌ NO DEFINIDO") . "\n";
}
echo "   Total encontradas: $envCount/" . count($vars) . "\n\n";

echo "7. Probando \$_SERVER:\n";
echo "   (\$_SERVER contiene información del servidor y variables de entorno)\n";
$serverCount = 0;
foreach ($vars as $var) {
    $value = isset($_SERVER[$var]) ? $_SERVER[$var] : null;
    if ($value) $serverCount++;
    echo "   \$_SERVER['$var'] = " . ($value ? "✅ $value" : "❌ NO DEFINIDO") . "\n";
}
echo "   Total encontradas: $serverCount/" . count($vars) . "\n\n";

echo "8. Verificar configuración PHP:\n";
echo "   variables_order = " . ini_get('variables_order') . "\n";
echo "   (Debe contener 'E' para \$_ENV y 'S' para \$_SERVER)\n\n";

echo "9. DIAGNÓSTICO:\n";
if ($getenvCount > 0) {
    echo "   ✅ getenv() funciona - El problema está en otro lado\n";
} elseif ($envCount > 0) {
    echo "   ⚠️ Las variables están en \$_ENV pero NO en getenv()\n";
    echo "   SOLUCIÓN: Usar \$_ENV en lugar de getenv() en el código\n";
} elseif ($serverCount > 0) {
    echo "   ⚠️ Las variables están en \$_SERVER pero NO en getenv() ni \$_ENV\n";
    echo "   SOLUCIÓN: Verificar configuración PHP variables_order\n";
} else {
    echo "   ❌ Las variables NO se están cargando en ningún lado\n";
    echo "   Posibles causas:\n";
    echo "   - Error al ejecutar load()\n";
    echo "   - Permisos del .env incorrectos\n";
    echo "   - variables_order de PHP no incluye E\n";
}

echo "\n═══════════════════════════════════════\n";
echo "INFORMACIÓN ADICIONAL\n";
echo "═══════════════════════════════════════\n";
echo "PHP Version: " . phpversion() . "\n";
echo "Dotenv Version: ";
if (class_exists('Dotenv\Dotenv')) {
    $reflection = new ReflectionClass('Dotenv\Dotenv');
    $dir = dirname($reflection->getFileName());
    if (file_exists($dir . '/../../composer.json')) {
        $composer = json_decode(file_get_contents($dir . '/../../composer.json'), true);
        echo isset($composer['version']) ? $composer['version'] : 'Unknown';
    } else {
        echo 'Unknown';
    }
} else {
    echo 'Not installed';
}
echo "\n";
echo "═══════════════════════════════════════\n";
echo "</pre>";

