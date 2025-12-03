<?php
/**
 * LIMPIADOR DE CACHÉ + TEST DE CLIENTE ID
 * 
 * Este archivo limpia el caché de PHP y verifica el cliente ID
 * Úsalo cuando cambies el .env y no se refleje el cambio
 */

// Cargar vendor autoload
require_once __DIR__ . '/../extensiones/vendor/autoload.php';

// Cargar configuración
require_once __DIR__ . '/../config.php';

// IMPORTANTE: Limpiar caché ANTES de cargar .env
if (function_exists('opcache_reset')) {
    opcache_reset();
    $opcache_limpiado = true;
} else {
    $opcache_limpiado = false;
}

if (function_exists('apc_clear_cache')) {
    apc_clear_cache();
    $apc_limpiado = true;
} else {
    $apc_limpiado = false;
}

// Ahora cargar variables de entorno desde .env
if (file_exists(__DIR__ . '/../.env') && class_exists('Dotenv\Dotenv')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
    $dotenv->load();
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Limpiador de Caché + Test Cliente ID</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
            max-width: 800px;
            width: 100%;
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 { font-size: 2rem; margin-bottom: 10px; }
        .header .icon { font-size: 3rem; margin-bottom: 10px; }
        .content { padding: 40px; }
        .section {
            background: #f8f9fa;
            border-left: 5px solid #667eea;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .section h3 {
            color: #333;
            margin-bottom: 15px;
            font-size: 1.2rem;
        }
        .item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            background: white;
            border-radius: 8px;
            margin-bottom: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        .item label { font-weight: 600; color: #555; }
        .item .value {
            font-size: 1.1rem;
            font-weight: bold;
            padding: 5px 15px;
            border-radius: 20px;
        }
        .value.success { background: #d4edda; color: #155724; }
        .value.error { background: #f8d7da; color: #721c24; }
        .value.info { background: #d1ecf1; color: #0c5460; }
        .status {
            padding: 30px;
            text-align: center;
            border-radius: 10px;
            margin-top: 20px;
        }
        .status.success { background: #d4edda; border: 2px solid #28a745; color: #155724; }
        .status.error { background: #f8d7da; border: 2px solid #dc3545; color: #721c24; }
        .status.warning { background: #fff3cd; border: 2px solid #ffc107; color: #856404; }
        .status-icon { font-size: 3rem; margin-bottom: 10px; }
        .status h2 { font-size: 1.5rem; margin-bottom: 10px; }
        .btn {
            display: inline-block;
            padding: 12px 30px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.3s;
            margin-top: 20px;
        }
        .btn:hover {
            background: #764ba2;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        .timestamp {
            text-align: center;
            color: #6c757d;
            margin-top: 20px;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="icon">🧹</div>
            <h1>Limpiador de Caché</h1>
            <p>Fuerza recarga del archivo .env</p>
        </div>
        
        <div class="content">
            <!-- SECCIÓN 1: Estado del Limpiado de Caché -->
            <div class="section">
                <h3>🧹 Limpieza de Caché PHP</h3>
                
                <div class="item">
                    <label>OPcache:</label>
                    <span class="value <?php echo $opcache_limpiado ? 'success' : 'error'; ?>">
                        <?php echo $opcache_limpiado ? '✅ LIMPIADO' : '❌ No disponible'; ?>
                    </span>
                </div>
                
                <div class="item">
                    <label>APC Cache:</label>
                    <span class="value <?php echo $apc_limpiado ? 'success' : 'error'; ?>">
                        <?php echo $apc_limpiado ? '✅ LIMPIADO' : '❌ No disponible'; ?>
                    </span>
                </div>
            </div>

            <!-- SECCIÓN 2: Valores Detectados -->
            <div class="section">
                <h3>📊 Valores Después de Limpiar Caché</h3>
                
                <div class="item">
                    <label>MOON_CLIENTE_ID desde .env:</label>
                    <span class="value info"><?php echo getenv('MOON_CLIENTE_ID') ?: 'NO DEFINIDO'; ?></span>
                </div>
                
                <div class="item">
                    <label>Cliente ID que usará el sistema:</label>
                    <span class="value success"><?php echo intval(getenv('MOON_CLIENTE_ID') ?: 7); ?></span>
                </div>
                
                <div class="item">
                    <label>Archivo .env existe:</label>
                    <span class="value <?php echo file_exists(__DIR__ . '/../.env') ? 'success' : 'error'; ?>">
                        <?php echo file_exists(__DIR__ . '/../.env') ? '✅ SÍ' : '❌ NO'; ?>
                    </span>
                </div>
            </div>

            <!-- SECCIÓN 3: Verificación del Valor del .env Original -->
            <div class="section">
                <h3>📄 Contenido Real del Archivo .env</h3>
                <?php
                $envPath = __DIR__ . '/../.env';
                if (file_exists($envPath)) {
                    $envContent = file_get_contents($envPath);
                    if (preg_match('/MOON_CLIENTE_ID=(\d+)/', $envContent, $matches)) {
                        $valorEnArchivo = $matches[1];
                        echo '<div class="item">';
                        echo '<label>Valor en el archivo .env (sin caché):</label>';
                        echo '<span class="value info">' . $valorEnArchivo . '</span>';
                        echo '</div>';
                        
                        $valorEnMemoria = getenv('MOON_CLIENTE_ID');
                        if ($valorEnArchivo != $valorEnMemoria) {
                            echo '<div class="status warning">';
                            echo '<div class="status-icon">⚠️</div>';
                            echo '<h2>Inconsistencia Detectada</h2>';
                            echo '<p>El archivo .env dice <strong>' . $valorEnArchivo . '</strong> pero PHP está usando <strong>' . $valorEnMemoria . '</strong></p>';
                            echo '<p style="margin-top: 10px;">Posibles causas:</p>';
                            echo '<ul style="text-align: left; margin: 10px auto; max-width: 500px;">';
                            echo '<li>El servidor web no se ha reiniciado</li>';
                            echo '<li>Hay otro archivo .env en otra ubicación</li>';
                            echo '<li>El config.php tiene un valor hardcodeado</li>';
                            echo '</ul>';
                            echo '</div>';
                        }
                    }
                } else {
                    echo '<div class="status error">';
                    echo '<p>❌ El archivo .env no existe en: ' . $envPath . '</p>';
                    echo '</div>';
                }
                ?>
            </div>

            <?php
            $clienteIdEnv = getenv('MOON_CLIENTE_ID');
            $clienteIdFinal = intval(getenv('MOON_CLIENTE_ID') ?: 7);
            
            if ($clienteIdEnv && $clienteIdFinal == 14) {
                ?>
                <div class="status success">
                    <div class="status-icon">✅</div>
                    <h2>¡Perfecto! Cliente ID = 14</h2>
                    <p>El sistema está usando correctamente el cliente ID 14 del archivo .env</p>
                </div>
                <?php
            } elseif ($clienteIdEnv) {
                ?>
                <div class="status success">
                    <div class="status-icon">✅</div>
                    <h2>Sistema Configurado</h2>
                    <p>Cliente ID activo: <strong><?php echo $clienteIdFinal; ?></strong></p>
                </div>
                <?php
            } else {
                ?>
                <div class="status error">
                    <div class="status-icon">❌</div>
                    <h2>Problema: No se está leyendo el .env</h2>
                    <p>El sistema no está tomando el valor del archivo .env</p>
                </div>
                <?php
            }
            ?>

            <div style="text-align: center; margin-top: 30px;">
                <a href="javascript:location.reload()" class="btn">🔄 Recargar Página</a>
                <a href="index.html" class="btn" style="background: #6c757d; margin-left: 10px;">← Volver al Menú</a>
            </div>

            <div class="timestamp">
                📅 Ejecutado: <?php echo date('d/m/Y H:i:s'); ?>
            </div>
        </div>
    </div>
</body>
</html>

