<?php
// Test de debugging del sistema de cobro

// Cargar configuración
require_once "config.php";
require_once "extensiones/vendor/autoload.php";

// Cargar variables de entorno
if (file_exists(__DIR__ . '/.env') && class_exists('Dotenv\Dotenv')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();
}

// Cargar modelos y controladores
require_once "modelos/conexion.php";
require_once "modelos/sistema_cobro.modelo.php";
require_once "controladores/sistema_cobro.controlador.php";
require_once "modelos/mercadopago.modelo.php";
require_once "controladores/mercadopago.controlador.php";

echo "<h1>DEBUG - Sistema de Cobro</h1>";
echo "<hr>";

// Test 1: Conexión a BD
echo "<h2>1. Test de Conexión a BD Moon</h2>";
try {
    $conexion = Conexion::conectarMoon();
    if ($conexion) {
        echo "✅ Conexión exitosa a BD Moon<br>";
    } else {
        echo "❌ Conexión falló (retornó null)<br>";
        die("No se puede continuar sin conexión");
    }
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
    die();
}

// Test 2: Datos del cliente
$idCliente = intval(getenv('MOON_CLIENTE_ID') ?: 7);
echo "<h2>2. Datos del Cliente (ID: $idCliente)</h2>";

$clienteMoon = ControladorSistemaCobro::ctrMostrarClientesCobro($idCliente);
echo "<strong>Cliente:</strong> " . $clienteMoon["nombre"] . "<br>";
echo "<strong>Estado Bloqueo:</strong> " . $clienteMoon["estado_bloqueo"] . "<br>";
echo "<strong>Mensual (columna):</strong> " . (isset($clienteMoon["mensual"]) ? $clienteMoon["mensual"] : "NO EXISTE") . "<br>";
echo "<br>";

$ctaCteCliente = ControladorSistemaCobro::ctrMostrarSaldoCuentaCorriente($idCliente);
echo "<strong>Saldo Cuenta Corriente:</strong> $" . number_format($ctaCteCliente["saldo"], 2) . "<br>";
echo "<br>";

// Test 3: Cálculo de cobro
echo "<h2>3. Cálculo de Monto de Cobro</h2>";
$datosCobro = ControladorMercadoPago::ctrCalcularMontoCobro($clienteMoon, $ctaCteCliente);

echo "<strong>Día actual:</strong> " . $datosCobro['dia_actual'] . "<br>";
echo "<strong>Saldo actual:</strong> $" . number_format($datosCobro['saldo_actual'], 2) . "<br>";
echo "<strong>Abono base:</strong> $" . number_format($datosCobro['abono_base'], 2) . "<br>";
echo "<strong>Tiene recargo:</strong> " . ($datosCobro['tiene_recargo'] ? 'SÍ (' . $datosCobro['porcentaje_recargo'] . '%)' : 'NO') . "<br>";
echo "<strong>Monto final a cobrar:</strong> $" . number_format($datosCobro['monto'], 2) . "<br>";
echo "<strong>Mensaje:</strong> " . $datosCobro['mensaje'] . "<br>";
echo "<br>";

// Test 4: Intentar registrar un intento de pago de prueba
echo "<h2>4. Test de Registro en BD</h2>";
$datosIntentoPrueba = array(
    'id_cliente_moon' => $idCliente,
    'preference_id' => 'TEST-' . time(),
    'monto' => $datosCobro['monto'],
    'descripcion' => 'TEST - Pago mensual - ' . date('m/Y'),
    'fecha_creacion' => date('Y-m-d H:i:s'),
    'estado' => 'test'
);

echo "<pre>";
print_r($datosIntentoPrueba);
echo "</pre>";

$resultado = ControladorMercadoPago::ctrRegistrarIntentoPago($datosIntentoPrueba);
echo "<strong>Resultado del registro:</strong> ";
if ($resultado === "ok") {
    echo "✅ OK - Se guardó correctamente<br>";
} else {
    echo "❌ ERROR<br>";
    echo "<pre>";
    print_r($resultado);
    echo "</pre>";
}

// Test 5: Verificar si se guardó
echo "<h2>5. Verificar Registro en BD</h2>";
$stmt = $conexion->prepare("SELECT * FROM mercadopago_intentos WHERE preference_id = :pref_id");
$stmt->bindParam(":pref_id", $datosIntentoPrueba['preference_id'], PDO::PARAM_STR);
$stmt->execute();
$registro = $stmt->fetch();

if ($registro) {
    echo "✅ Registro encontrado:<br>";
    echo "<pre>";
    print_r($registro);
    echo "</pre>";
} else {
    echo "❌ No se encontró el registro<br>";
}

echo "<hr>";
echo "<p><a href='index.php'>Volver al inicio</a></p>";
?>
