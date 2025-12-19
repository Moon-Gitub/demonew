<?php
/**
 * API ENDPOINT - LISTAR PRODUCTOS
 * Para sincronización con sistema offline
 */

require_once "../seguridad.ajax.php";
SeguridadAjax::inicializar();

require_once "../controladores/productos.controlador.php";
require_once "../modelos/productos.modelo.php";

header('Content-Type: application/json; charset=utf-8');

try {
    $productos = ControladorProductos::ctrMostrarProductos(null, null, "id");
    $resultado = [];
    
    if($productos && is_array($productos)) {
        foreach($productos as $prod) {
            $resultado[] = [
                'id' => $prod['id'],
                'codigo' => $prod['codigo'],
                'descripcion' => $prod['descripcion'],
                'precio_venta' => floatval($prod['precio_venta']),
                'precio_compra' => floatval($prod['precio_compra'] ?? 0),
                'stock' => floatval($prod['stock'] ?? 0),
                'categoria' => $prod['id_categoria'] ?? '',
                'proveedor' => $prod['id_proveedor'] ?? '',
                'tipo_iva' => floatval($prod['tipo_iva'] ?? 0)
            ];
        }
    }
    
    echo json_encode($resultado);
    
} catch(Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
