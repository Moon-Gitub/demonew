<?php
// Test de movimientos de cuenta corriente

require_once "config.php";
require_once "extensiones/vendor/autoload.php";

if (file_exists(__DIR__ . '/.env') && class_exists('Dotenv\Dotenv')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();
}

require_once "modelos/conexion.php";

echo "<h1>Movimientos de Cuenta Corriente - Cliente ID 7</h1>";
echo "<hr>";

$conexion = Conexion::conectarMoon();
$idCliente = 7;

// Obtener todos los movimientos
$stmt = $conexion->prepare("SELECT * FROM clientes_cuenta_corriente WHERE id_cliente = :id ORDER BY fecha DESC");
$stmt->bindParam(":id", $idCliente, PDO::PARAM_INT);
$stmt->execute();
$movimientos = $stmt->fetchAll();

echo "<h2>Total de movimientos: " . count($movimientos) . "</h2>";

if (count($movimientos) > 0) {
    echo "<table border='1' cellpadding='10' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background: #667eea; color: white;'>";
    echo "<th>ID</th><th>Fecha</th><th>Tipo</th><th>Importe</th><th>Descripción</th><th>Saldo Acumulado</th>";
    echo "</tr>";

    $saldoAcumulado = 0;
    $totalVentas = 0;
    $totalPagos = 0;

    foreach ($movimientos as $mov) {
        $tipo = ($mov['tipo'] == 0) ? 'VENTA (cargo)' : 'PAGO (abono)';
        $color = ($mov['tipo'] == 0) ? '#ffdddd' : '#ddffdd';

        if ($mov['tipo'] == 0) {
            $saldoAcumulado += floatval($mov['importe']);
            $totalVentas += floatval($mov['importe']);
        } else {
            $saldoAcumulado -= floatval($mov['importe']);
            $totalPagos += floatval($mov['importe']);
        }

        echo "<tr style='background: $color;'>";
        echo "<td>" . $mov['id'] . "</td>";
        echo "<td>" . $mov['fecha'] . "</td>";
        echo "<td><strong>" . $tipo . "</strong></td>";
        echo "<td>$" . number_format($mov['importe'], 2) . "</td>";
        echo "<td>" . $mov['descripcion'] . "</td>";
        echo "<td><strong>$" . number_format($saldoAcumulado, 2) . "</strong></td>";
        echo "</tr>";
    }

    echo "</table>";

    echo "<br><h2>Resumen:</h2>";
    echo "<strong>Total Ventas (cargos):</strong> $" . number_format($totalVentas, 2) . "<br>";
    echo "<strong>Total Pagos (abonos):</strong> $" . number_format($totalPagos, 2) . "<br>";
    echo "<strong style='color: red; font-size: 20px;'>Saldo Final (debe):</strong> $" . number_format($totalVentas - $totalPagos, 2) . "<br>";

} else {
    echo "<p style='color: red;'>No hay movimientos registrados para este cliente</p>";
}

// Verificar cómo calcula el saldo el sistema
echo "<hr><h2>Cálculo del sistema:</h2>";
$stmt2 = $conexion->prepare("SELECT
    (SUM(IF (cc.tipo = 0, cc.importe, 0)) - SUM(IF (cc.tipo = 1, cc.importe, 0))) as saldo
    FROM clientes_cuenta_corriente cc
    WHERE cc.id_cliente = :id");
$stmt2->bindParam(":id", $idCliente, PDO::PARAM_INT);
$stmt2->execute();
$resultado = $stmt2->fetch();

echo "<strong>Saldo según query del sistema:</strong> $" . number_format($resultado['saldo'], 2);

echo "<hr>";
echo "<p><a href='index.php'>Volver al inicio</a></p>";
?>
