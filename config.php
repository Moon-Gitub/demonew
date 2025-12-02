<?php
/**
 * ARCHIVO DE CONFIGURACIÓN
 *
 * Este archivo define las variables de entorno necesarias para el sistema.
 * Prioridad:
 * 1. Variables de entorno del servidor (getenv)
 * 2. Valores definidos en este archivo
 */

// ==============================================
// BASE DE DATOS LOCAL - SISTEMA POS
// ==============================================
if (!getenv('DB_HOST')) {
    putenv('DB_HOST=localhost');
}
if (!getenv('DB_NAME')) {
    putenv('DB_NAME=demo_db');
}
if (!getenv('DB_USER')) {
    putenv('DB_USER=demo_user');
}
if (!getenv('DB_PASS')) {
    putenv('DB_PASS=aK4UWccl2ceg');
}
if (!getenv('DB_CHARSET')) {
    putenv('DB_CHARSET=UTF8MB4');
}

// ==============================================
// BASE DE DATOS MOON - SISTEMA DE COBRO
// ==============================================
if (!getenv('MOON_DB_HOST')) {
    putenv('MOON_DB_HOST=107.161.23.241');
}
if (!getenv('MOON_DB_NAME')) {
    putenv('MOON_DB_NAME=moondesa_moon');
}
if (!getenv('MOON_DB_USER')) {
    putenv('MOON_DB_USER=moondesa_moon');
}
if (!getenv('MOON_DB_PASS')) {
    putenv('MOON_DB_PASS=F!b+hn#i3Vk-');
}

// ==============================================
// MERCADOPAGO - CREDENCIALES
// ==============================================
// IMPORTANTE: Reemplazar con credenciales reales
if (!getenv('MP_PUBLIC_KEY')) {
    putenv('MP_PUBLIC_KEY=TEST-TU_PUBLIC_KEY_AQUI');
}
if (!getenv('MP_ACCESS_TOKEN')) {
    putenv('MP_ACCESS_TOKEN=TEST-TU_ACCESS_TOKEN_AQUI');
}

// ==============================================
// CONFIGURACIÓN DE APLICACIÓN
// ==============================================
if (!getenv('APP_ENV')) {
    putenv('APP_ENV=production');
}
if (!getenv('APP_DEBUG')) {
    putenv('APP_DEBUG=false');
}
