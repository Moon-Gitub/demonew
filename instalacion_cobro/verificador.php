<?php
/**
 * VERIFICADOR DE INSTALACIÓN
 * Sistema de Cobro Moon POS
 *
 * Este archivo verifica que todos los componentes del sistema de cobro
 * estén correctamente instalados y configurados.
 */

// Cargar configuración
$erroresConfig = [];
if (file_exists('../config.php')) {
    require_once '../config.php';
} else {
    $erroresConfig[] = 'Archivo config.php no encontrado';
}

if (file_exists('../extensiones/vendor/autoload.php')) {
    require_once '../extensiones/vendor/autoload.php';
} else {
    $erroresConfig[] = 'Composer autoload no encontrado. Ejecuta: composer install';
}

// Verificar .env
if (file_exists('../.env') && class_exists('Dotenv\Dotenv')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
    $dotenv->load();
} else {
    $erroresConfig[] = 'Archivo .env no encontrado o Dotenv no instalado';
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificador de Instalación - Sistema de Cobro Moon POS</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            padding: 40px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }
        h1 {
            color: #667eea;
            margin-bottom: 10px;
        }
        .subtitle {
            color: #6c757d;
            margin-bottom: 30px;
        }
        .verification-section {
            margin-bottom: 30px;
            border-left: 4px solid #667eea;
            padding-left: 20px;
        }
        .verification-section h2 {
            color: #333;
            margin-bottom: 15px;
            font-size: 20px;
        }
        .check-item {
            padding: 12px;
            margin: 8px 0;
            border-radius: 5px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .check-item.ok {
            background: #d4edda;
            border-left: 4px solid #28a745;
        }
        .check-item.error {
            background: #f8d7da;
            border-left: 4px solid #dc3545;
        }
        .check-item.warning {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
        }
        .status {
            font-weight: 600;
            padding: 4px 12px;
            border-radius: 4px;
        }
        .status.ok {
            background: #28a745;
            color: white;
        }
        .status.error {
            background: #dc3545;
            color: white;
        }
        .status.warning {
            background: #ffc107;
            color: #856404;
        }
        .summary {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-top: 30px;
        }
        .summary-item {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #dee2e6;
        }
        .summary-item:last-child {
            border-bottom: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Verificador de Instalación</h1>
        <p class="subtitle">Sistema de Cobro Moon POS con MercadoPago</p>

        <?php
        $totalChecks = 0;
        $passedChecks = 0;
        $failedChecks = 0;
        $warningChecks = 0;

        // SECCIÓN 1: Archivos PHP
        echo '<div class="verification-section">';
        echo '<h2>📁 Archivos del Sistema</h2>';

        $archivosRequeridos = [
            '../config.php' => 'Configuración general',
            '../.env' => 'Variables de entorno',
            '../controladores/mercadopago.controlador.php' => 'Controlador MercadoPago',
            '../modelos/mercadopago.modelo.php' => 'Modelo MercadoPago',
            '../vistas/modulos/cabezote-mejorado.php' => 'Cabezote con sistema de cobro',
            '../vistas/modulos/procesar-pago.php' => 'Procesador de pagos'
        ];

        foreach ($archivosRequeridos as $archivo => $descripcion) {
            $totalChecks++;
            $existe = file_exists($archivo);
            if ($existe) $passedChecks++; else $failedChecks++;

            echo '<div class="check-item ' . ($existe ? 'ok' : 'error') . '">';
            echo '<span>' . $descripcion . '</span>';
            echo '<span class="status ' . ($existe ? 'ok' : 'error') . '">' . ($existe ? '✓ OK' : '✗ FALTA') . '</span>';
            echo '</div>';
        }
        echo '</div>';

        // SECCIÓN 2: Dependencias Composer
        echo '<div class="verification-section">';
        echo '<h2>📦 Dependencias (Composer)</h2>';

        $dependencias = [
            'MercadoPago SDK' => class_exists('MercadoPago\MercadoPagoConfig'),
            'PHP Dotenv' => class_exists('Dotenv\Dotenv')
        ];

        foreach ($dependencias as $nombre => $instalada) {
            $totalChecks++;
            if ($instalada) $passedChecks++; else $failedChecks++;

            echo '<div class="check-item ' . ($instalada ? 'ok' : 'error') . '">';
            echo '<span>' . $nombre . '</span>';
            echo '<span class="status ' . ($instalada ? 'ok' : 'error') . '">' . ($instalada ? '✓ Instalada' : '✗ No instalada') . '</span>';
            echo '</div>';
        }
        echo '</div>';

        // SECCIÓN 3: Variables de Entorno
        echo '<div class="verification-section">';
        echo '<h2>⚙️ Configuración (.env)</h2>';

        $variables = [
            'MOON_DB_HOST' => 'Host BD Moon',
            'MOON_DB_NAME' => 'Nombre BD Moon',
            'MOON_DB_USER' => 'Usuario BD Moon',
            'MOON_DB_PASS' => 'Contraseña BD Moon',
            'MP_PUBLIC_KEY' => 'Public Key MercadoPago',
            'MP_ACCESS_TOKEN' => 'Access Token MercadoPago',
            'MOON_CLIENTE_ID' => 'ID del Cliente'
        ];

        foreach ($variables as $var => $descripcion) {
            $totalChecks++;
            $valor = getenv($var);
            $configurada = !empty($valor);

            if ($configurada) $passedChecks++; else $failedChecks++;

            echo '<div class="check-item ' . ($configurada ? 'ok' : 'error') . '">';
            echo '<span>' . $descripcion . ' (' . $var . ')</span>';
            echo '<span class="status ' . ($configurada ? 'ok' : 'error') . '">' . ($configurada ? '✓ Configurada' : '✗ No configurada') . '</span>';
            echo '</div>';
        }
        echo '</div>';

        // SECCIÓN 4: Conexión a Base de Datos
        echo '<div class="verification-section">';
        echo '<h2>💾 Conexión a Base de Datos</h2>';

        if (file_exists('../modelos/conexion.php')) {
            require_once '../modelos/conexion.php';

            try {
                $totalChecks++;
                $conn = Conexion::conectarMoon();
                if ($conn) {
                    $passedChecks++;
                    echo '<div class="check-item ok">';
                    echo '<span>Conexión a BD Moon</span>';
                    echo '<span class="status ok">✓ Conectada</span>';
                    echo '</div>';

                    // Verificar tablas
                    $tablas = ['mercadopago_intentos', 'mercadopago_pagos', 'mercadopago_webhooks'];
                    foreach ($tablas as $tabla) {
                        $totalChecks++;
                        $stmt = $conn->query("SHOW TABLES LIKE '$tabla'");
                        $existe = $stmt->rowCount() > 0;

                        if ($existe) $passedChecks++; else $failedChecks++;

                        echo '<div class="check-item ' . ($existe ? 'ok' : 'error') . '">';
                        echo '<span>Tabla: ' . $tabla . '</span>';
                        echo '<span class="status ' . ($existe ? 'ok' : 'error') . '">' . ($existe ? '✓ Existe' : '✗ No existe') . '</span>';
                        echo '</div>';
                    }

                    // Verificar campo aplicar_recargos en tabla clientes
                    $totalChecks++;
                    $stmtCampo = $conn->query("SHOW COLUMNS FROM clientes LIKE 'aplicar_recargos'");
                    $campoExiste = $stmtCampo->rowCount() > 0;

                    if ($campoExiste) $passedChecks++; else $failedChecks++;

                    echo '<div class="check-item ' . ($campoExiste ? 'ok' : 'error') . '">';
                    echo '<span>Campo aplicar_recargos en tabla clientes</span>';
                    echo '<span class="status ' . ($campoExiste ? 'ok' : 'error') . '">' . ($campoExiste ? '✓ Existe' : '✗ No existe') . '</span>';
                    echo '</div>';
                } else {
                    $failedChecks++;
                    echo '<div class="check-item error">';
                    echo '<span>Conexión a BD Moon</span>';
                    echo '<span class="status error">✗ Error de conexión</span>';
                    echo '</div>';
                }
            } catch (Exception $e) {
                $totalChecks++;
                $failedChecks++;
                echo '<div class="check-item error">';
                echo '<span>Conexión a BD Moon</span>';
                echo '<span class="status error">✗ Error: ' . $e->getMessage() . '</span>';
                echo '</div>';
            }
        } else {
            $totalChecks++;
            $failedChecks++;
            echo '<div class="check-item error">';
            echo '<span>Archivo conexion.php</span>';
            echo '<span class="status error">✗ No encontrado</span>';
            echo '</div>';
        }

        echo '</div>';

        // RESUMEN
        $porcentajeExito = ($totalChecks > 0) ? round(($passedChecks / $totalChecks) * 100) : 0;
        $estadoGeneral = ($porcentajeExito >= 90) ? 'ok' : (($porcentajeExito >= 70) ? 'warning' : 'error');

        echo '<div class="summary">';
        echo '<h3 style="margin-bottom: 15px; color: #333;">📊 Resumen de Verificación</h3>';

        echo '<div class="summary-item">';
        echo '<strong>Total de verificaciones:</strong>';
        echo '<span>' . $totalChecks . '</span>';
        echo '</div>';

        echo '<div class="summary-item">';
        echo '<strong>Exitosas:</strong>';
        echo '<span style="color: #28a745; font-weight: 600;">' . $passedChecks . ' ✓</span>';
        echo '</div>';

        echo '<div class="summary-item">';
        echo '<strong>Fallidas:</strong>';
        echo '<span style="color: #dc3545; font-weight: 600;">' . $failedChecks . ' ✗</span>';
        echo '</div>';

        echo '<div class="summary-item">';
        echo '<strong>Porcentaje de éxito:</strong>';
        echo '<span style="font-size: 24px; font-weight: 700; color: ' .
             ($porcentajeExito >= 90 ? '#28a745' : ($porcentajeExito >= 70 ? '#ffc107' : '#dc3545')) .
             ';">' . $porcentajeExito . '%</span>';
        echo '</div>';

        echo '</div>';

        // MENSAJE FINAL
        if ($porcentajeExito >= 90) {
            echo '<div style="background: #d4edda; padding: 20px; border-radius: 10px; margin-top: 20px; border-left: 4px solid #28a745;">';
            echo '<h3 style="color: #155724; margin-bottom: 10px;">✅ ¡Instalación Exitosa!</h3>';
            echo '<p style="color: #155724;">El sistema de cobro está correctamente instalado y configurado. Puedes comenzar a usarlo.</p>';
            echo '</div>';
        } elseif ($porcentajeExito >= 70) {
            echo '<div style="background: #fff3cd; padding: 20px; border-radius: 10px; margin-top: 20px; border-left: 4px solid #ffc107;">';
            echo '<h3 style="color: #856404; margin-bottom: 10px;">⚠️ Instalación Parcial</h3>';
            echo '<p style="color: #856404;">El sistema está parcialmente instalado. Revisa los elementos fallidos arriba para completar la instalación.</p>';
            echo '</div>';
        } else {
            echo '<div style="background: #f8d7da; padding: 20px; border-radius: 10px; margin-top: 20px; border-left: 4px solid #dc3545;">';
            echo '<h3 style="color: #721c24; margin-bottom: 10px;">❌ Instalación Incompleta</h3>';
            echo '<p style="color: #721c24;">Hay problemas significativos con la instalación. Revisa la guía de instalación manual y corrige los errores.</p>';
            echo '</div>';
        }
        ?>

        <div style="text-align: center; margin-top: 30px;">
            <a href="../index.php" style="display: inline-block; background: #667eea; color: white; padding: 12px 30px; border-radius: 5px; text-decoration: none; font-weight: 600; margin: 5px;">
                Ir al Sistema POS
            </a>
            <a href="INSTALACION_MANUAL.md" style="display: inline-block; background: #6c757d; color: white; padding: 12px 30px; border-radius: 5px; text-decoration: none; font-weight: 600; margin: 5px;">
                Ver Guía de Instalación
            </a>
        </div>
    </div>
</body>
</html>
