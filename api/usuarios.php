<?php
/**
 * API ENDPOINT - LISTAR USUARIOS
 * Para sincronización con sistema offline
 */

require_once "../seguridad.ajax.php";
SeguridadAjax::inicializar();

require_once "../controladores/usuarios.controlador.php";
require_once "../modelos/usuarios.modelo.php";

header('Content-Type: application/json; charset=utf-8');

try {
    $usuarios = ModeloUsuarios::mdlMostrarUsuarios("usuarios", null, null);
    $resultado = [];
    
    if($usuarios && is_array($usuarios)) {
        foreach($usuarios as $user) {
            $resultado[] = [
                'id' => $user['id'],
                'usuario' => $user['usuario'],
                'password' => $user['password'], // Hash
                'nombre' => $user['nombre'],
                'perfil' => $user['perfil'],
                'sucursal' => $user['sucursal'],
                'estado' => $user['estado']
            ];
        }
    }
    
    echo json_encode($resultado);
    
} catch(Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
