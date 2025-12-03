<?php
// Test: Ver qué parámetros llegan desde MercadoPago

echo "<h1>Test - URL de Respuesta MercadoPago</h1>";
echo "<hr>";

echo "<h2>Parámetros GET recibidos:</h2>";
if (count($_GET) > 0) {
    echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
    echo "<tr style='background: #667eea; color: white;'><th>Parámetro</th><th>Valor</th></tr>";
    foreach ($_GET as $key => $value) {
        echo "<tr>";
        echo "<td><strong>$key</strong></td>";
        echo "<td>$value</td>";
        echo "</tr>";
    }
    echo "</table>";

    echo "<hr>";
    echo "<h2>Análisis:</h2>";

    if (isset($_GET['status'])) {
        $status = $_GET['status'];
        echo "<p><strong>Estado del pago:</strong> <span style='color: " . ($status == 'approved' ? 'green' : ($status == 'pending' ? 'orange' : 'red')) . "; font-size: 20px;'>$status</span></p>";

        if ($status == 'pending') {
            echo "<div style='background: #fff3cd; padding: 15px; border-left: 4px solid #ffc107;'>";
            echo "<strong>⚠️ El pago está pendiente</strong><br>";
            echo "Esto puede ser porque:<br>";
            echo "- Se usó un método de pago que requiere confirmación (efectivo, transferencia, etc.)<br>";
            echo "- La tarjeta requiere verificación adicional<br>";
            echo "- El binary_mode no está funcionando correctamente<br>";
            echo "</div>";
        } elseif ($status == 'approved') {
            echo "<div style='background: #d4edda; padding: 15px; border-left: 4px solid #28a745;'>";
            echo "<strong>✅ El pago está aprobado</strong><br>";
            echo "El sistema debería procesarlo correctamente.";
            echo "</div>";
        } elseif ($status == 'rejected') {
            echo "<div style='background: #f8d7da; padding: 15px; border-left: 4px solid #dc3545;'>";
            echo "<strong>❌ El pago fue rechazado</strong>";
            echo "</div>";
        }
    }

    if (isset($_GET['payment_id'])) {
        echo "<p><strong>Payment ID:</strong> " . $_GET['payment_id'] . "</p>";
    }

    if (isset($_GET['payment_type'])) {
        echo "<p><strong>Tipo de pago:</strong> " . $_GET['payment_type'] . "</p>";
    }

} else {
    echo "<p style='color: orange;'>No se recibieron parámetros GET. Haz un pago de prueba para ver qué llega aquí.</p>";
}

echo "<hr>";
echo "<p><a href='index.php'>Volver al inicio</a></p>";
?>
