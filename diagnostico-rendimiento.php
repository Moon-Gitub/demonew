<?php
/**
 * Diagnóstico rápido: ¿la lentitud es del hosting o del sistema?
 * Ejecutar por consola: php diagnostico-rendimiento.php
 * O por navegador: https://tudominio.com/diagnostico-rendimiento.php
 *
 * Borrar o restringir acceso cuando termines de usarlo.
 */

header('Content-Type: text/plain; charset=utf-8');

function medir($nombre, callable $fn) {
    $inicio = microtime(true);
    $fn();
    $fin = microtime(true);
    return ['nombre' => $nombre, 'seg' => round($fin - $inicio, 4)];
}

$resultados = [];
$pdo = null;
$errorDb = null;

// --- 1) PHP puro (CPU / hosting) ---
$resultados[] = medir('PHP: 100.000 operaciones', function () {
    $n = 0;
    for ($i = 0; $i < 100000; $i++) {
        $n += $i;
    }
});

// --- 2) Desglose: autoload, .env, conexión DB ---
try {
    // 2a) Solo autoload de Composer (muchos archivos PHP)
    $resultados[] = medir('Cargar autoload (vendor)', function () {
        require_once __DIR__ . '/extensiones/vendor/autoload.php';
    });

    // 2b) Solo leer y parsear .env
    $resultados[] = medir('Cargar .env (Dotenv)', function () {
        if (file_exists(__DIR__ . '/.env') && class_exists('Dotenv\Dotenv')) {
            $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
            $dotenv->load();
        }
    });

    require_once __DIR__ . '/modelos/conexion.php';

    // 2c) Solo primera conexión a MySQL (TCP + autenticación)
    $resultados[] = medir('Conexión MySQL (primera vez)', function () use (&$pdo) {
        $pdo = Conexion::conectar();
    });

    if ($pdo) {
        $resultados[] = medir('DB: SELECT 1 (ping)', function () use ($pdo) {
            $pdo->query('SELECT 1');
        });
        $resultados[] = medir('DB: SELECT id FROM ventas LIMIT 1', function () use ($pdo) {
            $pdo->query('SELECT id FROM ventas LIMIT 1');
        });
        $resultados[] = medir('DB: COUNT(*) ventas', function () use ($pdo) {
            $pdo->query('SELECT COUNT(*) FROM ventas')->fetchColumn();
        });
        $resultados[] = medir('DB: COUNT(*) productos', function () use ($pdo) {
            $pdo->query('SELECT COUNT(*) FROM productos')->fetchColumn();
        });
    }
} catch (Throwable $e) {
    $resultados[] = ['nombre' => 'DB: no disponible (sin driver o BD caída)', 'seg' => -1];
    $errorDb = $e->getMessage();
}

// --- 5) Memoria y límites PHP ---
$memoria = memory_get_peak_usage(true) / 1024 / 1024;
$limite = ini_get('memory_limit');

// --- Salida ---
echo "===========================================\n";
echo "DIAGNÓSTICO DE RENDIMIENTO (hosting vs sistema)\n";
echo "===========================================\n\n";

foreach ($resultados as $r) {
    if ($r['seg'] < 0) {
        echo sprintf("%-35s %s\n", $r['nombre'] . ':', 'N/A (error)');
    } else {
        $ms = $r['seg'] * 1000;
        $estado = $r['seg'] > 2 ? ' LENTO' : ($r['seg'] > 0.5 ? ' ACEPTABLE' : ' OK');
        echo sprintf("%-35s %8.2f ms %s\n", $r['nombre'] . ':', $ms, $estado);
    }
}
if ($errorDb) {
    echo "\nAviso DB: " . $errorDb . "\n";
}

echo "\n--- PHP ---\n";
echo "Memoria pico: " . round($memoria, 2) . " MB\n";
echo "memory_limit: " . $limite . "\n";

echo "\n===========================================\n";
echo "CÓMO INTERPRETAR:\n";
echo "===========================================\n";
echo "- Si 'PHP: 100.000 operaciones' tarda mucho (> 0,5 s):\n";
echo "  El HOSTING (CPU) está lento o sobrecargado.\n\n";
echo "- Si 'DB: SELECT 1' o 'COUNT(*) ventas/productos' tardan mucho (> 1 s):\n";
echo "  La BASE DE DATOS o el HOSTING (MySQL/red) está lento.\n\n";
echo "- Si estas pruebas son RÁPIDAS pero el sistema en uso es lento:\n";
echo "  La lentitud viene del SISTEMA (consultas pesadas, muchas filas, lógica PHP).\n\n";
echo "Desglose 'arranque':\n";
echo "- Autoload: muchas clases PHP (Composer); suele ser lo que más pesa.\n";
echo "- .env: solo leer un archivo pequeño; normalmente < 1 ms.\n";
echo "- Conexión MySQL: red + handshake; en hosting remoto puede ser varios ms.\n";
echo "===========================================\n";
