<?php
/**
 * AJAX para manejar selección de productos para impresión
 * Usa sesión en lugar de URLs largas
 */

require_once "seguridad.ajax.php";

// Inicializar seguridad (solo sesión y AJAX, sin CSRF para operaciones de lectura)
SeguridadAjax::inicializar(false);

// Inicializar array en sesión si no existe
if (!isset($_SESSION['productos_impresion'])) {
    $_SESSION['productos_impresion'] = [];
}

$accion = $_POST['accion'] ?? $_GET['accion'] ?? '';

switch ($accion) {
    case 'agregar':
        agregarProducto();
        break;
    
    case 'quitar':
        quitarProducto();
        break;
    
    case 'limpiar':
        limpiarSeleccion();
        break;
    
    case 'obtener':
        obtenerSeleccion();
        break;
    
    case 'obtener_ids':
        obtenerIds();
        break;
    
    default:
        http_response_code(400);
        echo json_encode([
            'error' => true,
            'mensaje' => 'Acción no válida'
        ]);
        exit;
}

/**
 * Agregar producto a la selección
 */
function agregarProducto() {
    $idProducto = intval($_POST['idProducto'] ?? 0);
    
    if ($idProducto <= 0) {
        http_response_code(400);
        echo json_encode([
            'error' => true,
            'mensaje' => 'ID de producto inválido'
        ]);
        exit;
    }
    
    // Verificar si ya está seleccionado
    $existe = false;
    foreach ($_SESSION['productos_impresion'] as $item) {
        if ($item['id'] == $idProducto) {
            $existe = true;
            break;
        }
    }
    
    if ($existe) {
        echo json_encode([
            'error' => false,
            'mensaje' => 'Producto ya está seleccionado',
            'ya_seleccionado' => true
        ]);
        exit;
    }
    
    // Obtener datos del producto
    require_once "../modelos/productos.modelo.php";
    $producto = ModeloProductos::mdlMostrarProductos("id", $idProducto);
    
    if (!$producto) {
        http_response_code(404);
        echo json_encode([
            'error' => true,
            'mensaje' => 'Producto no encontrado'
        ]);
        exit;
    }
    
    // Agregar a la sesión
    $_SESSION['productos_impresion'][] = [
        'id' => $producto['id'],
        'codigo' => $producto['codigo'],
        'descripcion' => $producto['descripcion'],
        'precio_venta' => $producto['precio_venta']
    ];
    
    echo json_encode([
        'error' => false,
        'mensaje' => 'Producto agregado correctamente',
        'producto' => [
            'id' => $producto['id'],
            'codigo' => $producto['codigo'],
            'descripcion' => $producto['descripcion'],
            'precio_venta' => $producto['precio_venta']
        ],
        'total' => count($_SESSION['productos_impresion'])
    ]);
    exit;
}

/**
 * Quitar producto de la selección
 */
function quitarProducto() {
    $idProducto = intval($_POST['idProducto'] ?? 0);
    
    if ($idProducto <= 0) {
        http_response_code(400);
        echo json_encode([
            'error' => true,
            'mensaje' => 'ID de producto inválido'
        ]);
        exit;
    }
    
    // Buscar y eliminar
    $encontrado = false;
    foreach ($_SESSION['productos_impresion'] as $key => $item) {
        if ($item['id'] == $idProducto) {
            unset($_SESSION['productos_impresion'][$key]);
            $_SESSION['productos_impresion'] = array_values($_SESSION['productos_impresion']); // Reindexar
            $encontrado = true;
            break;
        }
    }
    
    if (!$encontrado) {
        http_response_code(404);
        echo json_encode([
            'error' => true,
            'mensaje' => 'Producto no encontrado en la selección'
        ]);
        exit;
    }
    
    echo json_encode([
        'error' => false,
        'mensaje' => 'Producto eliminado correctamente',
        'total' => count($_SESSION['productos_impresion'])
    ]);
    exit;
}

/**
 * Limpiar toda la selección
 */
function limpiarSeleccion() {
    $_SESSION['productos_impresion'] = [];
    
    echo json_encode([
        'error' => false,
        'mensaje' => 'Selección limpiada correctamente',
        'total' => 0
    ]);
    exit;
}

/**
 * Obtener toda la selección actual
 */
function obtenerSeleccion() {
    echo json_encode([
        'error' => false,
        'productos' => $_SESSION['productos_impresion'],
        'total' => count($_SESSION['productos_impresion'])
    ]);
    exit;
}

/**
 * Obtener solo los IDs (para scripts de impresión)
 */
function obtenerIds() {
    $ids = [];
    foreach ($_SESSION['productos_impresion'] as $item) {
        $ids[] = $item['id'];
    }
    
    echo json_encode([
        'error' => false,
        'ids' => $ids,
        'total' => count($ids)
    ]);
    exit;
}
