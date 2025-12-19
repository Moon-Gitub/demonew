<?php
/**
 * API ENDPOINT - HEALTH CHECK
 * Verifica que la API esté funcionando
 */

header('Content-Type: application/json; charset=utf-8');

echo json_encode([
    'status' => 'ok',
    'message' => 'API funcionando correctamente',
    'timestamp' => date('Y-m-d H:i:s')
]);
