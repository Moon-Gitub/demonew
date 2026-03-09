<?php
/**
 * Script para generar ventas de prueba con distintas empresas, medios de pago y datos variados.
 * Uso: php extras/scripts/generar-ventas-prueba.php [cantidad]
 *      o desde navegador: extras/scripts/generar-ventas-prueba.php?cantidad=20
 *
 * Requiere: .env configurado con DB_HOST, DB_NAME, DB_USER, DB_PASS
 */

// Cargar autoload y .env
$raiz = dirname(__DIR__, 2);
require_once $raiz . '/extensiones/vendor/autoload.php';
if (file_exists($raiz . '/.env') && class_exists('Dotenv\Dotenv')) {
    $dotenv = Dotenv\Dotenv::createImmutable($raiz);
    $dotenv->safeLoad();
}
require_once $raiz . '/modelos/conexion.php';

$cantidad = isset($argv[1]) ? (int)$argv[1] : (isset($_GET['cantidad']) ? (int)$_GET['cantidad'] : 15);
$cantidad = max(1, min($cantidad, 100));

$mediosPago = [
    ['tipo' => 'Efectivo', 'entrega' => ''],
    ['tipo' => 'MP-C', 'entrega' => ''],
    ['tipo' => 'MP-D', 'entrega' => ''],
    ['tipo' => 'Transferencia', 'entrega' => ''],
    ['tipo' => 'Tarjeta Débito', 'entrega' => ''],
    ['tipo' => 'Tarjeta Crédito', 'entrega' => ''],
    ['tipo' => 'Cheque', 'entrega' => ''],
];

$cbteTipos = [0, 6, 11]; // X, Factura B, Factura C
$ptosVta = [1, 2];
$estados = [0, 1]; // Adeudado, Pagado

try {
    $pdo = \Conexion::conectar();

    // Obtener empresas
    $stmt = $pdo->query("SELECT id, razon_social FROM empresa ORDER BY id");
    $empresas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (empty($empresas)) {
        $empresas = [['id' => 1, 'razon_social' => 'Empresa 1']];
    }

    // Obtener clientes
    $stmt = $pdo->query("SELECT id, nombre FROM clientes ORDER BY id LIMIT 30");
    $clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (empty($clientes)) {
        throw new Exception("No hay clientes en la base de datos. Cree al menos uno.");
    }

    // Obtener un vendedor válido
    $stmt = $pdo->query("SELECT id FROM usuarios ORDER BY id LIMIT 1");
    $vendedor = $stmt->fetch(PDO::FETCH_ASSOC);
    $idVendedor = $vendedor ? (int)$vendedor['id'] : 1;

    // Verificar si existe columna id_empresa
    $stmt = $pdo->query("SHOW COLUMNS FROM ventas LIKE 'id_empresa'");
    $tieneIdEmpresa = $stmt->rowCount() > 0;

    // Obtener último codigo
    $stmt = $pdo->query("SELECT COALESCE(MAX(codigo), 0) as max_cod FROM ventas");
    $maxCod = (int)$stmt->fetch(PDO::FETCH_ASSOC)['max_cod'];

    $insertadas = 0;
    $hoy = new DateTime();
    $hoy->modify('-30 days');

    for ($i = 0; $i < $cantidad; $i++) {
        $empresa = $empresas[array_rand($empresas)];
        $cliente = $clientes[array_rand($clientes)];
        $medio = $mediosPago[array_rand($mediosPago)];
        $total = round(rand(500, 50000) + (rand(0, 99) / 100), 2);
        $netoGravado = round($total / 1.21, 2);
        $impuesto = round($total - $netoGravado, 2);
        $medio['entrega'] = (string)$total;

        $codigo = $maxCod + 1 + $i;
        $uuid = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000, mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff));

        $fecha = clone $hoy;
        $fecha->modify('+' . rand(0, 720) . ' hours'); // 30 días atrás hasta hoy
        $fechaStr = $fecha->format('Y-m-d H:i:s');

        $cbteTipo = $cbteTipos[array_rand($cbteTipos)];
        $ptoVta = $ptosVta[array_rand($ptosVta)];
        $estado = $estados[array_rand($estados)];
        $metodoPagoJson = json_encode([$medio]);

        $impuestoDetalle = $impuesto > 0
            ? json_encode([['id' => 5, 'descripcion' => 'IVA 21%', 'baseImponible' => (string)$netoGravado, 'iva' => (string)$impuesto]])
            : '[]';

        $cols = "uuid, fecha, codigo, cbte_tipo, id_cliente, id_vendedor, productos, impuesto, impuesto_detalle, neto, neto_gravado, base_imponible_0, base_imponible_2, base_imponible_5, base_imponible_10, base_imponible_21, base_imponible_27, iva_2, iva_5, iva_10, iva_21, iva_27, total, metodo_pago, pto_vta, concepto, estado";
        $placeholders = ":uuid, :fecha, :codigo, :cbte_tipo, :id_cliente, :id_vendedor, :productos, :impuesto, :impuesto_detalle, :neto, :neto_gravado, :base_0, :base_2, :base_5, :base_10, :base_21, :base_27, :iva_2, :iva_5, :iva_10, :iva_21, :iva_27, :total, :metodo_pago, :pto_vta, :concepto, :estado";

        if ($tieneIdEmpresa) {
            $cols = "id_empresa, " . $cols;
            $placeholders = ":id_empresa, " . $placeholders;
        }

        $sql = "INSERT INTO ventas ($cols) VALUES ($placeholders)";
        $stmt = $pdo->prepare($sql);

        $params = [
            ':uuid' => $uuid,
            ':fecha' => $fechaStr,
            ':codigo' => $codigo,
            ':cbte_tipo' => $cbteTipo,
            ':id_cliente' => $cliente['id'],
            ':id_vendedor' => $idVendedor,
            ':productos' => '[]',
            ':impuesto' => $impuesto,
            ':impuesto_detalle' => $impuestoDetalle,
            ':neto' => $netoGravado,
            ':neto_gravado' => $netoGravado,
            ':base_0' => 0, ':base_2' => 0, ':base_5' => 0, ':base_10' => 0,
            ':base_21' => $netoGravado, ':base_27' => 0,
            ':iva_2' => 0, ':iva_5' => 0, ':iva_10' => 0,
            ':iva_21' => $impuesto, ':iva_27' => 0,
            ':total' => $total,
            ':metodo_pago' => $metodoPagoJson,
            ':pto_vta' => $ptoVta,
            ':concepto' => 1,
            ':estado' => $estado,
        ];
        if ($tieneIdEmpresa) {
            $params[':id_empresa'] = $empresa['id'];
        }

        $stmt->execute($params);
        $insertadas++;
    }

    $salida = "OK: Se generaron $insertadas ventas de prueba.\n";
    $salida .= "- Empresas usadas: " . count($empresas) . "\n";
    $salida .= "- Medios de pago: Efectivo, MP-C, MP-D, Transferencia, Tarjeta, Cheque\n";
    $salida .= "- Tipos comprobante: X (0), Factura B (6), Factura C (11)\n";
    $salida .= "- Puntos de venta: 1 y 2\n";

    if (php_sapi_name() === 'cli') {
        echo $salida;
    } else {
        header('Content-Type: text/plain; charset=utf-8');
        echo $salida;
    }
} catch (Exception $e) {
    $msg = "Error: " . $e->getMessage();
    if (php_sapi_name() === 'cli') {
        echo $msg . "\n";
    } else {
        header('Content-Type: text/plain; charset=utf-8');
        echo $msg;
    }
    exit(1);
}
