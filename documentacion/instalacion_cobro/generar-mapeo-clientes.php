<?php
/**
 * GENERADOR DE MAPEO DE CLIENTES
 * 
 * Este script consulta la BD Moon y genera un archivo CSV con todos los clientes
 * y sus dominios para facilitar la instalación masiva.
 */

// Configuración de BD Moon
$host = '107.161.23.11';
$db = 'cobrosposmooncom_db';
$user = 'cobrosposmooncom_dbuser';
$pass = '[Us{ynaJAA_o2A_!';

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Generador de Mapeo de Clientes</title>
    <style>
        body { font-family: Arial; padding: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #667eea; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #667eea; color: white; }
        tr:hover { background: #f8f9fa; }
        .btn { display: inline-block; padding: 12px 24px; background: #667eea; color: white; text-decoration: none; border-radius: 5px; margin-top: 20px; cursor: pointer; border: none; font-size: 16px; }
        .btn:hover { background: #5568d3; }
        .alert { padding: 15px; margin: 20px 0; border-radius: 5px; }
        .alert-success { background: #d4edda; color: #155724; border-left: 5px solid #28a745; }
        .alert-error { background: #f8d7da; color: #721c24; border-left: 5px solid #dc3545; }
        .alert-info { background: #d1ecf1; color: #0c5460; border-left: 5px solid #17a2b8; }
        pre { background: #f4f4f4; padding: 15px; border-radius: 5px; overflow: auto; }
        input[type="text"] { padding: 8px; width: 300px; border: 1px solid #ddd; border-radius: 4px; }
        .form-group { margin: 15px 0; }
        label { display: inline-block; width: 150px; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🗺️ Generador de Mapeo de Clientes</h1>
        <p>Consulta la BD Moon y genera el archivo CSV para instalación masiva</p>

        <?php
        try {
            // Conectar a BD Moon
            $conn = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
            $conn->exec("set names utf8");
            
            echo '<div class="alert alert-success">✅ Conexión exitosa a BD Moon</div>';
            
            // Obtener todos los clientes
            $stmt = $conn->query("SELECT id, nombre, dominio, mensual, estado_bloqueo, aplicar_recargos 
                                  FROM clientes 
                                  WHERE dominio IS NOT NULL AND dominio != '' 
                                  ORDER BY nombre");
            $clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $totalClientes = count($clientes);
            
            echo '<div class="alert alert-info">';
            echo '<h3>📊 Estadísticas</h3>';
            echo '<p><strong>Total de clientes encontrados:</strong> ' . $totalClientes . '</p>';
            echo '<p><strong>Con dominio configurado:</strong> ' . $totalClientes . '</p>';
            echo '</div>';
            
            // Generar CSV
            if (isset($_POST['generar_csv'])) {
                $filename = 'clientes-a-instalar-' . date('Y-m-d_His') . '.csv';
                header('Content-Type: text/csv; charset=utf-8');
                header('Content-Disposition: attachment; filename="' . $filename . '"');
                
                $output = fopen('php://output', 'w');
                
                // Encabezados
                fputcsv($output, ['id_cliente', 'dominio', 'usuario_cpanel', 'ruta_public_html', 'nombre_cliente', 'mensual']);
                
                // Datos
                foreach ($clientes as $cliente) {
                    $usuario = extraerUsuario($cliente['dominio']);
                    $ruta = '/home/' . $usuario . '/public_html';
                    
                    fputcsv($output, [
                        $cliente['id'],
                        $cliente['dominio'],
                        $usuario,
                        $ruta,
                        $cliente['nombre'],
                        $cliente['mensual']
                    ]);
                }
                
                fclose($output);
                exit;
            }
            
            // Mostrar tabla
            echo '<h2>📋 Listado de Clientes</h2>';
            echo '<form method="POST">';
            echo '<button type="submit" name="generar_csv" class="btn">📥 Descargar CSV</button>';
            echo '</form>';
            
            echo '<table>';
            echo '<thead>';
            echo '<tr>';
            echo '<th>ID</th>';
            echo '<th>Nombre</th>';
            echo '<th>Dominio</th>';
            echo '<th>Usuario cPanel (estimado)</th>';
            echo '<th>Mensual</th>';
            echo '<th>Estado</th>';
            echo '<th>Recargos</th>';
            echo '</tr>';
            echo '</thead>';
            echo '<tbody>';
            
            foreach ($clientes as $cliente) {
                $usuario = extraerUsuario($cliente['dominio']);
                $estado = $cliente['estado_bloqueo'] == 1 ? '🔴 Bloqueado' : '✅ Activo';
                $recargos = $cliente['aplicar_recargos'] == 1 ? '✅ Sí' : '❌ No';
                
                echo '<tr>';
                echo '<td>' . $cliente['id'] . '</td>';
                echo '<td>' . $cliente['nombre'] . '</td>';
                echo '<td>' . $cliente['dominio'] . '</td>';
                echo '<td>' . $usuario . '</td>';
                echo '<td>$' . number_format($cliente['mensual'], 2) . '</td>';
                echo '<td>' . $estado . '</td>';
                echo '<td>' . $recargos . '</td>';
                echo '</tr>';
            }
            
            echo '</tbody>';
            echo '</table>';
            
            // Instrucciones
            echo '<div class="alert alert-info" style="margin-top: 30px;">';
            echo '<h3>📝 Instrucciones:</h3>';
            echo '<ol>';
            echo '<li>Haz clic en "📥 Descargar CSV" para generar el archivo</li>';
            echo '<li>Abre el CSV y <strong>verifica/corrige</strong> los usuarios de cPanel</li>';
            echo '<li>Guarda el archivo como <code>clientes-a-instalar.csv</code></li>';
            echo '<li>Ejecuta el script: <code>bash script-instalacion-masiva.sh</code></li>';
            echo '</ol>';
            echo '<p><strong>⚠️ Importante:</strong> Los usuarios de cPanel son estimados. Verifica que sean correctos antes de ejecutar el script.</p>';
            echo '</div>';
            
        } catch (PDOException $e) {
            echo '<div class="alert alert-error">';
            echo '<h3>❌ Error de Conexión</h3>';
            echo '<p>' . $e->getMessage() . '</p>';
            echo '<p>Verifica las credenciales de la BD Moon en este archivo (líneas 12-15)</p>';
            echo '</div>';
        }
        
        // Función auxiliar para extraer usuario del dominio
        function extraerUsuario($dominio) {
            // Eliminar extensión y subdominios
            $dominio = str_replace('.posmoon.com.ar', '', $dominio);
            $dominio = str_replace('.com.ar', '', $dominio);
            $dominio = str_replace('.com', '', $dominio);
            $dominio = str_replace('.ar', '', $dominio);
            $dominio = str_replace('.design', '', $dominio);
            
            // Tomar solo la primera parte si hay puntos
            $partes = explode('.', $dominio);
            $usuario = $partes[0];
            
            // Límite de cPanel (16 caracteres)
            if (strlen($usuario) > 16) {
                $usuario = substr($usuario, 0, 16);
            }
            
            return $usuario;
        }
        ?>
    </div>
</body>
</html>


