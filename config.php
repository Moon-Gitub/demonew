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

// Verificar que el archivo .env existe
if (!$envExists) {
    // En lugar de morir, mostrar advertencia pero permitir continuar
    error_log('ADVERTENCIA: Archivo .env no encontrado en ' . $envPath);
    
    // Si estamos en producción y falta .env, sí mostrar error
    $appEnv = isset($_ENV['APP_ENV']) ? $_ENV['APP_ENV'] : (isset($_SERVER['APP_ENV']) ? $_SERVER['APP_ENV'] : null);
    $moonClienteId = isset($_ENV['MOON_CLIENTE_ID']) ? $_ENV['MOON_CLIENTE_ID'] : (isset($_SERVER['MOON_CLIENTE_ID']) ? $_SERVER['MOON_CLIENTE_ID'] : null);
    if ($appEnv === 'production' && !$moonClienteId) {
        die('
        <h1 style="color: red;">⚠️ ERROR: Archivo .env no encontrado</h1>
        <p>El sistema requiere un archivo <strong>.env</strong> en la raíz del proyecto.</p>
        <p>Ubicación esperada: ' . $envPath . '</p>
        <p><a href="?debug_env=1">Ver información de debug</a></p>
        ');
    }
}

// Verificar variables críticas (solo si .env existe)
if ($envExists) {
    $variablesRequeridas = [
        'MOON_CLIENTE_ID' => 'ID del cliente en la BD Moon',
        'DB_HOST' => 'Host de la base de datos local',
        'DB_NAME' => 'Nombre de la base de datos local',
        'MOON_DB_HOST' => 'Host de la base de datos Moon',
        'MOON_DB_NAME' => 'Nombre de la base de datos Moon'
    ];
    
    $variablesFaltantes = [];
    foreach ($variablesRequeridas as $variable => $descripcion) {
        // Intentar leer de $_ENV primero, luego $_SERVER
        $valor = isset($_ENV[$variable]) ? $_ENV[$variable] : (isset($_SERVER[$variable]) ? $_SERVER[$variable] : null);
        if (!$valor) {
            $variablesFaltantes[$variable] = $descripcion;
        }
    }
    
    // Solo mostrar error si faltan variables CRÍTICAS
    $moonClienteId = isset($_ENV['MOON_CLIENTE_ID']) ? $_ENV['MOON_CLIENTE_ID'] : (isset($_SERVER['MOON_CLIENTE_ID']) ? $_SERVER['MOON_CLIENTE_ID'] : null);
    if (!empty($variablesFaltantes) && !$moonClienteId) {
        echo '
        <div style="background: #fff3cd; border: 2px solid #ffc107; padding: 20px; margin: 20px; border-radius: 10px;">
            <h2 style="color: #856404;">⚠️ ADVERTENCIA: Variables faltantes en .env</h2>
            <p>Las siguientes variables no están definidas:</p>
            <ul style="color: #856404;">';
        
        foreach ($variablesFaltantes as $var => $desc) {
            echo '<li><strong>' . $var . '</strong> - ' . $desc . '</li>';
        }
        
        echo '
            </ul>
            <p><a href="?debug_env=1" style="color: #856404; font-weight: bold;">🔍 Ver información de debug completa</a></p>
            <p style="margin-top: 15px; font-size: 0.9em;">
                El sistema intentará funcionar con valores disponibles, pero puede tener comportamiento inesperado.
            </p>
        </div>
        ';
        
        // No morir, solo advertir
        error_log('ADVERTENCIA: Variables faltantes en .env: ' . implode(', ', array_keys($variablesFaltantes)));
    }
}

// El sistema continuará funcionando
