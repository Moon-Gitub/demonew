<?php
/**
 * API - Configuración empresa / listas para POS offline
 */
require_once __DIR__ . '/offline_auth.php';
$id_cliente = offline_auth_require();

$id_empresa = isset($_GET['id_empresa']) ? intval($_GET['id_empresa']) : 1;

require_once "../modelos/empresa.modelo.php";
require_once "../modelos/listas_precio.modelo.php";

header('Content-Type: application/json; charset=utf-8');

try {
    $empresa = ModeloEmpresa::mdlMostrarEmpresa("empresa", "id", $id_empresa);
    $listas = [];
    if (class_exists('ModeloListasPrecio') && ModeloListasPrecio::tablaExiste()) {
        $rows = ModeloListasPrecio::mdlListar($id_empresa, true);
        foreach ($rows as $r) {
            $listas[] = [
                'id' => intval($r['id']),
                'codigo' => $r['codigo'],
                'nombre' => $r['nombre'],
                'base_precio' => $r['base_precio'] ?? 'precio_venta',
                'tipo_descuento' => $r['tipo_descuento'] ?? '',
                'valor_descuento' => floatval($r['valor_descuento'] ?? 0),
                'orden' => intval($r['orden'] ?? 0),
            ];
        }
    }

    $pto_vta = 1;
    if ($empresa && isset($empresa['pto_venta_defecto'])) {
        $pto_vta = intval($empresa['pto_venta_defecto']);
    }

    echo json_encode([
        'id_cliente_moon' => $id_cliente,
        'id_empresa' => $id_empresa,
        'empresa' => $empresa ? [
            'id' => intval($empresa['id']),
            'nombre' => $empresa['razon_social'] ?? $empresa['titular'] ?? '',
            'pto_vta' => $pto_vta,
            'concepto_defecto' => intval($empresa['concepto_defecto'] ?? 1),
            'listas_precio' => $empresa['listas_precio'] ?? null,
        ] : null,
        'listas' => $listas,
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
