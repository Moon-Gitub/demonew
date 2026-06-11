<?php
/**
 * API - Catálogo unificado: productos + categorías + listas (config)
 */
require_once __DIR__ . '/offline_auth.php';
offline_auth_require();

$id_empresa = isset($_GET['id_empresa']) ? intval($_GET['id_empresa']) : 1;

require_once "../controladores/productos.controlador.php";
require_once "../modelos/productos.modelo.php";
require_once "../controladores/categorias.controlador.php";
require_once "../modelos/categorias.modelo.php";
require_once "../modelos/listas_precio.modelo.php";

header('Content-Type: application/json; charset=utf-8');

try {
    $productos = ControladorProductos::ctrMostrarProductos(null, null, "id");
    $prods = [];
    if ($productos && is_array($productos)) {
        foreach ($productos as $prod) {
            $prods[] = [
                'id' => intval($prod['id']),
                'codigo' => $prod['codigo'],
                'descripcion' => $prod['descripcion'],
                'precio_venta' => floatval($prod['precio_venta']),
                'precio_compra' => floatval($prod['precio_compra'] ?? 0),
                'stock' => floatval($prod['stock'] ?? 0) + floatval($prod['stock2'] ?? 0) + floatval($prod['stock3'] ?? 0),
                'categoria' => $prod['id_categoria'] ?? '',
                'proveedor' => $prod['id_proveedor'] ?? '',
                'tipo_iva' => floatval($prod['tipo_iva'] ?? 21),
            ];
        }
    }

    $categorias = [];
    if (class_exists('ControladorCategorias')) {
        $cats = ControladorCategorias::ctrMostrarCategorias(null, null);
        if ($cats && is_array($cats)) {
            foreach ($cats as $c) {
                $categorias[] = [
                    'id' => intval($c['id']),
                    'nombre' => $c['categoria'] ?? $c['nombre'] ?? '',
                ];
            }
        }
    }

    $listas = [];
    $listas_config = [];
    if (class_exists('ModeloListasPrecio') && ModeloListasPrecio::tablaExiste()) {
        $rows = ModeloListasPrecio::mdlListar($id_empresa, true);
        $codigos = [];
        foreach ($rows as $r) {
            $listas[] = [
                'id' => intval($r['id']),
                'codigo' => $r['codigo'],
                'nombre' => $r['nombre'],
                'base_precio' => $r['base_precio'] ?? 'precio_venta',
                'tipo_descuento' => $r['tipo_descuento'] ?? '',
                'valor_descuento' => floatval($r['valor_descuento'] ?? 0),
            ];
            $codigos[] = $r['codigo'];
        }
        if (!empty($codigos)) {
            $listas_config = ModeloListasPrecio::mdlConfigPorCodigos($codigos, $id_empresa);
        }
    }

    echo json_encode([
        'productos' => $prods,
        'categorias' => $categorias,
        'listas' => $listas,
        'listas_config' => $listas_config,
        'id_empresa' => $id_empresa,
        'synced_at' => date('c'),
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
