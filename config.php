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

// Verificar que el archivo .env existe
if (!file_exists(__DIR__ . '/.env')) {
    die('
    <h1 style="color: red;">⚠️ ERROR: Archivo .env no encontrado</h1>
    <p>El sistema requiere un archivo <strong>.env</strong> en la raíz del proyecto.</p>
    <p>Por favor crea el archivo .env con todas las variables necesarias.</p>
    <p>Ubicación esperada: ' . __DIR__ . '/.env</p>
    ');
}

// Verificar que las variables críticas estén definidas
$variablesRequeridas = [
    'DB_HOST',
    'DB_NAME', 
    'DB_USER',
    'DB_PASS',
    'MOON_DB_HOST',
    'MOON_DB_NAME',
    'MOON_DB_USER',
    'MOON_DB_PASS',
    'MP_PUBLIC_KEY',
    'MP_ACCESS_TOKEN',
    'MOON_CLIENTE_ID'
];

$variablesFaltantes = [];
foreach ($variablesRequeridas as $variable) {
    if (!getenv($variable)) {
        $variablesFaltantes[] = $variable;
    }
}

if (!empty($variablesFaltantes)) {
    die('
    <h1 style="color: red;">⚠️ ERROR: Variables faltantes en .env</h1>
    <p>Las siguientes variables requeridas no están definidas en el archivo .env:</p>
    <ul>
        <li><strong>' . implode('</strong></li><li><strong>', $variablesFaltantes) . '</strong></li>
    </ul>
    <p>Por favor agrega estas variables al archivo .env en la raíz del proyecto.</p>
    ');
}

// Si llegamos aquí, todas las variables están configuradas correctamente
// El sistema funcionará exclusivamente con los valores del .env
