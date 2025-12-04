<?php
/**
 * TEST - Verificación de Cliente ID
 * 
 * Este archivo verifica que el sistema esté tomando correctamente
 * el valor de MOON_CLIENTE_ID desde el archivo .env
 */

// Cargar vendor autoload
require_once __DIR__ . '/../extensiones/vendor/autoload.php';

// Cargar variables de entorno desde .env PRIMERO
if (file_exists(__DIR__ . '/../.env') && class_exists('Dotenv\Dotenv')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
    $dotenv->load();
}

// Cargar helpers (función env())
require_once __DIR__ . '/../helpers.php';

// Cargar configuración
require_once __DIR__ . '/../config.php';

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test - Cliente ID</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
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
        
        .header h1 {
            font-size: 2rem;
            margin-bottom: 10px;
        }
        
        .header .icon {
            font-size: 3rem;
            margin-bottom: 10px;
        }
        
        .content {
            padding: 40px;
        }
        
        .test-result {
            background: #f8f9fa;
            border-left: 5px solid #667eea;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        
        .test-result h3 {
            color: #333;
            margin-bottom: 15px;
            font-size: 1.2rem;
        }
        
        .test-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            background: white;
            border-radius: 8px;
            margin-bottom: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        
        .test-item label {
            font-weight: 600;
            color: #555;
        }
        
        .test-item .value {
            font-size: 1.1rem;
            color: #667eea;
            font-weight: bold;
        }
        
        .status {
            padding: 30px;
            text-align: center;
            border-radius: 10px;
            margin-top: 20px;
        }
        
        .status.success {
            background: #d4edda;
            border: 2px solid #28a745;
            color: #155724;
        }
        
        .status.error {
            background: #f8d7da;
            border: 2px solid #dc3545;
            color: #721c24;
        }
        
        .status.warning {
            background: #fff3cd;
            border: 2px solid #ffc107;
            color: #856404;
        }
        
        .status-icon {
            font-size: 3rem;
            margin-bottom: 10px;
        }
        
        .status h2 {
            font-size: 1.5rem;
            margin-bottom: 10px;
        }
        
        .status p {
            font-size: 1.1rem;
            line-height: 1.6;
        }
        
        .back-link {
            text-align: center;
            margin-top: 30px;
        }
        
        .back-link a {
            display: inline-block;
            padding: 12px 30px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .back-link a:hover {
            background: #764ba2;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        .info-box {
            background: #e7f3ff;
            border-left: 4px solid #2196F3;
            padding: 15px;
            margin-top: 20px;
            border-radius: 5px;
        }
        
        .info-box strong {
            color: #1976D2;
        }
        
        code {
            background: #f4f4f4;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
            color: #d63384;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="icon">🌙</div>
            <h1>Verificación de Cliente ID</h1>
            <p>Sistema de Cobro Moon POS</p>
        </div>
        
        <div class="content">
            <div class="test-result">
                <h3>📊 Valores Detectados</h3>
                
                <div class="test-item">
                    <label>MOON_CLIENTE_ID desde .env:</label>
                    <span class="value"><?php echo env('MOON_CLIENTE_ID', 'NO DEFINIDO'); ?></span>
                </div>
                
                <div class="test-item">
                    <label>Cliente ID que usará el sistema:</label>
                    <span class="value"><?php echo intval(env('MOON_CLIENTE_ID', 7)); ?></span>
                </div>
                
                <div class="test-item">
                    <label>Archivo .env existe:</label>
                    <span class="value"><?php echo file_exists(__DIR__ . '/../.env') ? '✅ SÍ' : '❌ NO'; ?></span>
                </div>
                
                <div class="test-item">
                    <label>Clase Dotenv cargada:</label>
                    <span class="value"><?php echo class_exists('Dotenv\Dotenv') ? '✅ SÍ' : '❌ NO'; ?></span>
                </div>
            </div>
            
            <?php
            $clienteIdEnv = env('MOON_CLIENTE_ID');
            $clienteIdFinal = intval(env('MOON_CLIENTE_ID', 7));
            
            if ($clienteIdEnv && $clienteIdFinal == 2) {
                // TODO CORRECTO - Cliente ID = 2
                ?>
                <div class="status success">
                    <div class="status-icon">✅</div>
                    <h2>¡Perfecto! Sistema Configurado Correctamente</h2>
                    <p>El sistema está tomando correctamente el valor del archivo <code>.env</code></p>
                    <p style="margin-top: 10px;">
                        <strong>Cliente ID activo:</strong> <?php echo $clienteIdFinal; ?>
                    </p>
                </div>
                <?php
            } elseif ($clienteIdEnv && $clienteIdFinal != 2 && $clienteIdFinal != 7) {
                // Cliente ID diferente a 2 y 7 (personalizado)
                ?>
                <div class="status success">
                    <div class="status-icon">✅</div>
                    <h2>Sistema Configurado con Cliente Personalizado</h2>
                    <p>El sistema está tomando el valor del archivo <code>.env</code></p>
                    <p style="margin-top: 10px;">
                        <strong>Cliente ID activo:</strong> <?php echo $clienteIdFinal; ?>
                    </p>
                </div>
                <?php
            } elseif (!$clienteIdEnv && $clienteIdFinal == 2) {
                // Usando valor por defecto de config.php
                ?>
                <div class="status warning">
                    <div class="status-icon">⚠️</div>
                    <h2>Usando Valor por Defecto</h2>
                    <p>La variable <code>MOON_CLIENTE_ID</code> no está en el archivo <code>.env</code></p>
                    <p>Se está usando el valor por defecto de <code>config.php</code></p>
                    <p style="margin-top: 10px;">
                        <strong>Cliente ID activo:</strong> <?php echo $clienteIdFinal; ?>
                    </p>
                </div>
                <?php
            } else {
                // Problema - aún usando el valor hardcodeado original (7)
                ?>
                <div class="status error">
                    <div class="status-icon">❌</div>
                    <h2>Problema Detectado</h2>
                    <p>El sistema NO está tomando el valor del archivo <code>.env</code></p>
                    <p style="margin-top: 10px;">
                        <strong>Cliente ID activo:</strong> <?php echo $clienteIdFinal; ?> (valor antiguo hardcodeado)
                    </p>
                </div>
                
                <div class="info-box">
                    <strong>Posibles soluciones:</strong>
                    <ol style="margin-left: 20px; margin-top: 10px;">
                        <li>Verificar que el archivo <code>.env</code> existe en la raíz del proyecto</li>
                        <li>Verificar que <code>MOON_CLIENTE_ID=2</code> esté correctamente en el <code>.env</code></li>
                        <li>Reiniciar el servidor web: <code>sudo systemctl restart apache2</code></li>
                        <li>Limpiar caché de PHP OPcache si está habilitado</li>
                        <li>Verificar que los archivos <code>cobro/cabezote.php</code> y <code>config.php</code> estén actualizados</li>
                    </ol>
                </div>
                <?php
            }
            ?>
            
            <div class="info-box" style="margin-top: 30px;">
                <strong>ℹ️ Información Adicional:</strong>
                <p style="margin-top: 10px;">
                    El sistema busca el valor de <code>MOON_CLIENTE_ID</code> en el siguiente orden:
                </p>
                <ol style="margin-left: 20px; margin-top: 10px;">
                    <li>Variable de entorno del sistema (getenv)</li>
                    <li>Archivo <code>.env</code> en la raíz del proyecto</li>
                    <li>Valor por defecto en <code>config.php</code></li>
                </ol>
            </div>
            
            <div class="back-link">
                <a href="index.html">← Volver al menú de testing</a>
            </div>
        </div>
    </div>
</body>
</html>

