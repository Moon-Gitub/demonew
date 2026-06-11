<?php
/**
 * API - Medios de pago activos (POS offline)
 */
require_once __DIR__ . '/offline_auth.php';
offline_auth_require();

require_once "../modelos/medios_pago.modelo.php";

header('Content-Type: application/json; charset=utf-8');

try {
    $medios = ModeloMediosPago::mdlMostrarMediosPagoActivos();
    $out = [];
    if ($medios && is_array($medios)) {
        foreach ($medios as $m) {
            $out[] = [
                'id' => intval($m['id']),
                'codigo' => $m['codigo'] ?? '',
                'nombre' => $m['nombre'] ?? '',
                'descripcion' => $m['descripcion'] ?? '',
                'activo' => intval($m['activo'] ?? 1),
                'orden' => intval($m['orden'] ?? 0),
                'requiere_codigo' => intval($m['requiere_codigo'] ?? 0),
                'requiere_banco' => intval($m['requiere_banco'] ?? 0),
                'requiere_numero' => intval($m['requiere_numero'] ?? 0),
                'requiere_fecha' => intval($m['requiere_fecha'] ?? 0),
            ];
        }
    }
    echo json_encode($out);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
