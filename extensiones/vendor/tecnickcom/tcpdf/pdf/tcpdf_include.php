<?php
/**
 * TCPDF Include File
 * 
 * Este archivo carga TCPDF y define las constantes necesarias.
 * Desde: extensiones/vendor/tecnickcom/tcpdf/pdf/
 * TCPDF está en: extensiones/vendor/tecnickcom/tcpdf/
 */

// Definir el directorio base de TCPDF (un nivel arriba desde pdf/)
define('K_PATH_MAIN', dirname(__DIR__) . '/');

// Definir el directorio de fuentes
define('K_PATH_FONTS', K_PATH_MAIN . 'fonts/');

// Definir el directorio de imágenes
define('K_PATH_IMAGES', __DIR__ . '/images/');

// Definir el directorio de cache (opcional, para mejor rendimiento)
define('K_PATH_CACHE', sys_get_temp_dir() . '/');

// Cargar el archivo principal de TCPDF
require_once(K_PATH_MAIN . 'tcpdf.php');

// Definir constantes de configuración si no están definidas
if (!defined('PDF_PAGE_ORIENTATION')) {
    define('PDF_PAGE_ORIENTATION', 'P'); // Portrait
}
if (!defined('PDF_UNIT')) {
    define('PDF_UNIT', 'mm');
}
if (!defined('PDF_PAGE_FORMAT')) {
    define('PDF_PAGE_FORMAT', 'A4');
}
