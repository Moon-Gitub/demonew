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
    putenv('MOON_DB_HOST=107.161.23.11');
}
if (!getenv('MOON_DB_NAME')) {
    putenv('MOON_DB_NAME=cobrosposmooncom_db');
}
if (!getenv('MOON_DB_USER')) {
    putenv('MOON_DB_USER=cobrosposmooncom_dbuser');
}
if (!getenv('MOON_DB_PASS')) {
    putenv('MOON_DB_PASS=[Us{ynaJAA_o2A_!');
}

// ==============================================
// MERCADOPAGO - CREDENCIALES DE PRODUCCIÓN
// ==============================================
if (!getenv('MP_PUBLIC_KEY')) {
    putenv('MP_PUBLIC_KEY=APP_USR-33156d44-12df-4039-8c92-1635d8d3edde');
}
if (!getenv('MP_ACCESS_TOKEN')) {
    putenv('MP_ACCESS_TOKEN=APP_USR-6921807486493458-102300-5f1cec174eb674c42c9782860caf640c-2916747261');
}

// ==============================================
// SISTEMA DE COBRO MOON
// ==============================================
// ID del cliente en la BD Moon (cambiar según corresponda)
if (!getenv('MOON_CLIENTE_ID')) {
    putenv('MOON_CLIENTE_ID=7');
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
