<?php
/**
 * Autenticación básica para APIs del POS offline.
 * Requiere id_cliente; opcional api_key desde .env (POS_OFFLINE_API_KEY).
 */
function offline_auth_require(): int {
    require_once __DIR__ . '/../extensiones/vendor/autoload.php';
    if (file_exists(__DIR__ . '/../.env') && class_exists('Dotenv\Dotenv')) {
        $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
        $dotenv->safeLoad();
    }

    $id_cliente = isset($_GET['id_cliente']) ? intval($_GET['id_cliente']) : 0;
    if ($id_cliente <= 0) {
        http_response_code(401);
        echo json_encode(['error' => 'Se requiere id_cliente']);
        exit;
    }

    $expectedKey = getenv('POS_OFFLINE_API_KEY') ?: ($_ENV['POS_OFFLINE_API_KEY'] ?? '');
    if ($expectedKey !== '') {
        $got = $_GET['api_key'] ?? $_SERVER['HTTP_X_POS_API_KEY'] ?? '';
        if (!hash_equals((string) $expectedKey, (string) $got)) {
            http_response_code(403);
            echo json_encode(['error' => 'API key inválida']);
            exit;
        }
    }

    return $id_cliente;
}
