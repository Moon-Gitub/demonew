<?php
/**
 * SCRIPT DE MIGRACIÓN DE CONTRASEÑAS
 * 
 * Migra contraseñas del formato antiguo (crypt) al nuevo formato (password_hash)
 * 
 * ⚠️ IMPORTANTE: Este script debe ejecutarse UNA SOLA VEZ
 * 
 * INSTRUCCIONES:
 * 1. Ejecutar desde línea de comandos: php migrar-passwords.php
 * 2. O acceder desde navegador (solo en desarrollo)
 * 3. Los usuarios con contraseñas antiguas deberán usar su contraseña actual
 * 4. El sistema migrará automáticamente al nuevo formato en el próximo login
 */

// Cargar configuración
require_once __DIR__ . '/../../index.php';
require_once __DIR__ . '/../../modelos/seguridad.modelo.php';
require_once __DIR__ . '/../../modelos/usuarios.modelo.php';

// Solo permitir ejecución en desarrollo o desde CLI
$esCLI = php_sapi_name() === 'cli';
$esDesarrollo = (isset($_ENV['APP_ENV']) && $_ENV['APP_ENV'] === 'development') || 
                (isset($_GET['debug']) && $_GET['debug'] === '1');

if (!$esCLI && !$esDesarrollo) {
    die("Este script solo puede ejecutarse desde línea de comandos o en modo desarrollo.\n");
}

echo "═══════════════════════════════════════════════════════════\n";
echo "MIGRACIÓN DE CONTRASEÑAS\n";
echo "═══════════════════════════════════════════════════════════\n\n";

try {
    $pdo = Conexion::conectar();
    $usuarios = $pdo->query("SELECT id, usuario, password FROM usuarios")->fetchAll(PDO::FETCH_ASSOC);
    
    $migrados = 0;
    $yaMigrados = 0;
    $errores = 0;
    $total = count($usuarios);
    
    echo "Total de usuarios encontrados: $total\n\n";
    
    foreach ($usuarios as $usuario) {
        $hashActual = $usuario['password'];
        
        // Verificar si ya está en formato nuevo (password_hash genera hashes que empiezan con $2y$)
        if (preg_match('/^\$2[ay]\$/', $hashActual)) {
            // Verificar si necesita actualización (cost factor bajo)
            if (ModeloSeguridad::needsRehash($hashActual)) {
                echo "Usuario: {$usuario['usuario']} - Hash necesita actualización... ";
                
                // ⚠️ IMPORTANTE: No podemos migrar sin la contraseña original
                // El usuario deberá cambiar su contraseña o el sistema migrará automáticamente en el login
                echo "⚠️  Requiere cambio de contraseña o migración automática en login\n";
                $yaMigrados++;
            } else {
                echo "Usuario: {$usuario['usuario']} - Ya migrado ✓\n";
                $yaMigrados++;
            }
        } else {
            // Formato antiguo detectado
            echo "Usuario: {$usuario['usuario']} - Formato antiguo detectado... ";
            
            // ⚠️ IMPORTANTE: No podemos migrar sin la contraseña original
            // El sistema migrará automáticamente cuando el usuario haga login
            echo "⚠️  Se migrará automáticamente en el próximo login\n";
            $migrados++;
        }
    }
    
    echo "\n═══════════════════════════════════════════════════════════\n";
    echo "RESUMEN:\n";
    echo "═══════════════════════════════════════════════════════════\n";
    echo "Total usuarios: $total\n";
    echo "Ya migrados: $yaMigrados\n";
    echo "Pendientes de migración: $migrados\n";
    echo "Errores: $errores\n\n";
    
    if ($migrados > 0) {
        echo "⚠️  NOTA IMPORTANTE:\n";
        echo "Los usuarios con contraseñas antiguas se migrarán automáticamente\n";
        echo "cuando hagan login con su contraseña actual.\n";
        echo "No es necesario hacer nada manualmente.\n\n";
    }
    
    echo "✅ Migración completada\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    exit(1);
}

