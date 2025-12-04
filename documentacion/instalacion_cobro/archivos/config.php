<?php
/**
 * ARCHIVO DE CONFIGURACIÓN
 * 
 * ⚠️ IMPORTANTE: Este sistema usa ÚNICAMENTE el archivo .env para configuración
 * 
 * Este archivo solo existe para mantener compatibilidad con código legacy.
 * TODAS las variables deben estar definidas en el archivo .env en la raíz del proyecto.
 * 
 * Si el archivo .env no existe o falta alguna variable, el sistema mostrará un error.
 * Esto es intencional para evitar usar valores por defecto incorrectos.
 * 
 * ============================================================================
 * VARIABLES REQUERIDAS EN .env:
 * ============================================================================
 * 
 * # BASE DE DATOS LOCAL - SISTEMA POS
 * DB_HOST=localhost
 * DB_NAME=tu_base_de_datos
 * DB_USER=tu_usuario
 * DB_PASS=tu_contraseña
 * DB_CHARSET=UTF8MB4
 * 
 * # BASE DE DATOS MOON - SISTEMA DE COBRO
 * MOON_DB_HOST=107.161.23.11
 * MOON_DB_NAME=cobrosposmooncom_db
 * MOON_DB_USER=cobrosposmooncom_dbuser
 * MOON_DB_PASS=tu_password
 * 
 * # MERCADOPAGO - CREDENCIALES DE PRODUCCIÓN
 * MP_PUBLIC_KEY=APP_USR-tu-public-key
 * MP_ACCESS_TOKEN=APP_USR-tu-access-token
 * 
 * # SISTEMA DE COBRO MOON
 * MOON_CLIENTE_ID=14
 * 
 * # CONFIGURACIÓN DE APLICACIÓN
 * APP_ENV=production
 * APP_DEBUG=false
 * 
 * ============================================================================
 */

// ==============================================
// MODO DEBUG: Ver información del .env
// ==============================================
$envPath = __DIR__ . '/.env';
$envExists = file_exists($envPath);
$dotenvLoaded = class_exists('Dotenv\Dotenv');

// Si estamos en modo debug, mostrar información útil
if (isset($_GET['debug_env'])) {
    echo '<pre>';
    echo "═══════════════════════════════════════\n";
    echo "DEBUG: Información del .env\n";
    echo "═══════════════════════════════════════\n\n";
    echo "Archivo .env existe: " . ($envExists ? '✅ SÍ' : '❌ NO') . "\n";
    echo "Ubicación: $envPath\n";
    echo "Dotenv cargado: " . ($dotenvLoaded ? '✅ SÍ' : '❌ NO') . "\n\n";
    
    if ($envExists) {
        echo "Tamaño: " . filesize($envPath) . " bytes\n";
        echo "Permisos: " . substr(sprintf('%o', fileperms($envPath)), -4) . "\n\n";
        echo "Contenido:\n";
        echo "─────────────────────────────────────\n";
        echo file_get_contents($envPath);
        echo "\n─────────────────────────────────────\n\n";
    }
    
    echo "Variables disponibles con env():\n";
    echo "─────────────────────────────────────\n";
    $vars = ['DB_HOST', 'DB_NAME', 'MOON_CLIENTE_ID', 'MP_PUBLIC_KEY'];
    foreach ($vars as $var) {
        $value = function_exists('env') ? env($var) : (isset($_ENV[$var]) ? $_ENV[$var] : getenv($var));
        echo "$var = " . ($value ? $value : 'NO DEFINIDO') . "\n";
    }
    echo "═══════════════════════════════════════\n";
    echo '</pre>';
    exit;
}

// Solo registrar en log si .env no existe, pero NO mostrar errores
if (!$envExists) {
    error_log('INFO: Archivo .env no encontrado. Sistema usará valores por defecto.');
}

// No hacer validaciones que puedan romper el sistema
// conexion.php cargará el .env automáticamente cuando sea necesario
