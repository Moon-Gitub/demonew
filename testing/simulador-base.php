<?php
/**
 * SIMULADOR BASE DEL SISTEMA DE COBRO MOON POS
 * Este archivo simula el comportamiento del sistema en diferentes días del mes
 */

// Función para simular una fecha específica
function simularFecha($dia) {
    // Sobrescribir la función date() temporalmente
    // NOTA: En producción, esto NO afecta al sistema real

    $mes = date('m');
    $anio = date('Y');

    return [
        'd' => str_pad($dia, 2, '0', STR_PAD_LEFT),
        'm' => $mes,
        'Y' => $anio,
        'fecha_completa' => "$anio-$mes-" . str_pad($dia, 2, '0', STR_PAD_LEFT)
    ];
}

// Función para calcular recargos según el día
function calcularRecargosPorDia($dia, $subtotalMensuales, $subtotalOtros) {
    $tieneRecargo = false;
    $porcentajeRecargo = 0;
    $mensajeEstado = "";
    $colorEstado = "";
    $bloqueaModal = false;

    if ($dia >= 1 && $dia <= 4) {
        $mensajeEstado = "Sin recargo - Período normal";
        $colorEstado = "#28a745";

    } elseif ($dia > 4 && $dia <= 9) {
        $mensajeEstado = "Sin recargo - Período de gracia";
        $colorEstado = "#17a2b8";

    } elseif ($dia >= 10 && $dia <= 14) {
        $tieneRecargo = true;
        $porcentajeRecargo = 10;
        $mensajeEstado = "10% de recargo sobre servicios mensuales";
        $colorEstado = "#ffc107";

    } elseif ($dia >= 15 && $dia <= 19) {
        $tieneRecargo = true;
        $porcentajeRecargo = 15;
        $mensajeEstado = "15% de recargo sobre servicios mensuales";
        $colorEstado = "#fd7e14";

    } elseif ($dia >= 20 && $dia <= 24) {
        $tieneRecargo = true;
        $porcentajeRecargo = 20;
        $mensajeEstado = "20% de recargo sobre servicios mensuales";
        $colorEstado = "#fd7e14";

    } elseif ($dia >= 25 && $dia <= 26) {
        $tieneRecargo = true;
        $porcentajeRecargo = 30;
        $mensajeEstado = "30% de recargo sobre servicios mensuales";
        $colorEstado = "#dc3545";

    } elseif ($dia >= 27) {
        $tieneRecargo = true;
        $porcentajeRecargo = 30;
        $bloqueaModal = true;
        $mensajeEstado = "30% de recargo + SISTEMA BLOQUEADO";
        $colorEstado = "#dc3545";
    }

    // Calcular montos
    $montoRecargo = $tieneRecargo ? ($subtotalMensuales * ($porcentajeRecargo / 100)) : 0;
    $total = $subtotalMensuales + $subtotalOtros + $montoRecargo;

    return [
        'tiene_recargo' => $tieneRecargo,
        'porcentaje_recargo' => $porcentajeRecargo,
        'monto_recargo' => $montoRecargo,
        'subtotal_mensuales' => $subtotalMensuales,
        'subtotal_otros' => $subtotalOtros,
        'total' => $total,
        'mensaje_estado' => $mensajeEstado,
        'color_estado' => $colorEstado,
        'bloquea_modal' => $bloqueaModal
    ];
}

// Datos de ejemplo para pruebas
function obtenerDatosEjemplo() {
    return [
        'cliente' => [
            'nombre' => 'ALMACEN 1933 (Julia Salcedo)',
            'id' => 7
        ],
        'servicios_mensuales' => [
            ['descripcion' => 'Servicio POS octubre 2025', 'importe' => 7500.00],
            ['descripcion' => 'Servicio POS noviembre 2025', 'importe' => 7500.00]
        ],
        'otros_cargos' => [
            // Ejemplo de otro cargo sin recargo
            // ['descripcion' => 'Trabajo Mejoras', 'importe' => 10000.00]
        ]
    ];
}

// Función para mostrar el resultado de la simulación
function mostrarResultadoSimulacion($dia, $datos, $resultado) {
    $fecha = simularFecha($dia);
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Simulación - Día <?php echo $dia; ?> del mes</title>
        <style>
            * { margin: 0; padding: 0; box-sizing: border-box; }
            body {
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                min-height: 100vh;
                padding: 20px;
            }
            .container {
                max-width: 900px;
                margin: 0 auto;
                background: white;
                border-radius: 15px;
                padding: 40px;
                box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            }
            .back-btn {
                display: inline-block;
                color: #667eea;
                text-decoration: none;
                margin-bottom: 20px;
                font-weight: 600;
            }
            .back-btn:hover { text-decoration: underline; }
            h1 {
                color: #333;
                margin-bottom: 10px;
            }
            .fecha-simulada {
                background: <?php echo $resultado['color_estado']; ?>;
                color: white;
                padding: 15px 20px;
                border-radius: 10px;
                margin: 20px 0;
                font-size: 18px;
                font-weight: 600;
            }
            .info-box {
                background: #f8f9fa;
                border-left: 4px solid #667eea;
                padding: 20px;
                margin: 20px 0;
                border-radius: 5px;
            }
            .info-box h3 {
                color: #333;
                margin-bottom: 15px;
            }
            table {
                width: 100%;
                border-collapse: collapse;
                margin: 20px 0;
            }
            th, td {
                padding: 12px;
                text-align: left;
                border-bottom: 1px solid #dee2e6;
            }
            th {
                background: #f8f9fa;
                font-weight: 600;
                color: #495057;
            }
            .precio {
                text-align: right;
                font-weight: 600;
            }
            .total-row {
                background: #f8f9fa;
                font-weight: 700;
                font-size: 18px;
            }
            .recargo-row {
                background: #fff3cd;
                color: #856404;
            }
            .alert {
                padding: 15px 20px;
                border-radius: 5px;
                margin: 20px 0;
            }
            .alert-warning {
                background: #fff3cd;
                border-left: 4px solid #ffc107;
                color: #856404;
            }
            .alert-danger {
                background: #f8d7da;
                border-left: 4px solid #dc3545;
                color: #721c24;
            }
            .alert-success {
                background: #d4edda;
                border-left: 4px solid #28a745;
                color: #155724;
            }
            .categoria-titulo {
                background: #667eea;
                color: white;
                padding: 8px 12px;
                font-weight: 600;
                font-size: 14px;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <a href="index.html" class="back-btn">← Volver al menú principal</a>

            <h1>🧪 Simulación del Sistema de Cobro</h1>

            <div class="fecha-simulada">
                📅 Simulando día <?php echo $dia; ?> del mes (<?php echo $fecha['fecha_completa']; ?>)
                <br>
                Estado: <?php echo $resultado['mensaje_estado']; ?>
            </div>

            <div class="info-box">
                <h3>👤 Información del Cliente</h3>
                <p><strong>Cliente:</strong> <?php echo $datos['cliente']['nombre']; ?></p>
                <p><strong>ID:</strong> <?php echo $datos['cliente']['id']; ?></p>
            </div>

            <?php if ($resultado['bloquea_modal']): ?>
            <div class="alert alert-danger">
                <strong>🚫 SISTEMA BLOQUEADO</strong><br>
                El cliente ha superado el día 26 sin pagar. El modal de cobro no se puede cerrar y el sistema permanecerá bloqueado hasta que se regularice la situación.
            </div>
            <?php endif; ?>

            <h3 style="margin-top: 30px; color: #333;">💳 Desglose de Cargos Pendientes</h3>

            <table>
                <?php if (count($datos['servicios_mensuales']) > 0): ?>
                <tr>
                    <th colspan="2" class="categoria-titulo">SERVICIOS MENSUALES POS</th>
                </tr>
                <?php
                foreach ($datos['servicios_mensuales'] as $servicio):
                ?>
                <tr>
                    <td><?php echo $servicio['descripcion']; ?></td>
                    <td class="precio">$<?php echo number_format($servicio['importe'], 2, ',', '.'); ?></td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>

                <?php if (count($datos['otros_cargos']) > 0): ?>
                <tr>
                    <th colspan="2" class="categoria-titulo" style="background: #6c757d;">OTROS CARGOS (sin recargo)</th>
                </tr>
                <?php
                foreach ($datos['otros_cargos'] as $cargo):
                ?>
                <tr>
                    <td><?php echo $cargo['descripcion']; ?></td>
                    <td class="precio">$<?php echo number_format($cargo['importe'], 2, ',', '.'); ?></td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>

                <tr>
                    <td><strong>SUBTOTAL</strong></td>
                    <td class="precio">$<?php echo number_format($resultado['subtotal_mensuales'] + $resultado['subtotal_otros'], 2, ',', '.'); ?></td>
                </tr>

                <?php if ($resultado['tiene_recargo']): ?>
                <tr class="recargo-row">
                    <td>
                        ⚠️ Recargo por mora sobre servicios mensuales (<?php echo $resultado['porcentaje_recargo']; ?>%)
                    </td>
                    <td class="precio">$<?php echo number_format($resultado['monto_recargo'], 2, ',', '.'); ?></td>
                </tr>
                <?php endif; ?>

                <tr class="total-row">
                    <td>TOTAL A PAGAR</td>
                    <td class="precio" style="color: #dc3545;">$<?php echo number_format($resultado['total'], 2, ',', '.'); ?></td>
                </tr>
            </table>

            <?php if ($resultado['tiene_recargo']): ?>
            <div class="alert alert-warning">
                <strong>💡 Nota sobre recargos:</strong><br>
                Los recargos se aplican <strong>únicamente sobre los servicios mensuales POS</strong> ($<?php echo number_format($resultado['subtotal_mensuales'], 2, ',', '.'); ?>).
                Los otros cargos no llevan recargo.
            </div>
            <?php else: ?>
            <div class="alert alert-success">
                <strong>✅ Sin recargos</strong><br>
                El cliente está pagando dentro del período sin recargos.
            </div>
            <?php endif; ?>

            <div style="margin-top: 30px; padding: 20px; background: #e7f3ff; border-radius: 10px;">
                <h4 style="color: #004085; margin-bottom: 15px;">📋 Resumen de la Simulación</h4>
                <ul style="color: #004085; line-height: 2;">
                    <li><strong>Día simulado:</strong> <?php echo $dia; ?></li>
                    <li><strong>Subtotal servicios mensuales:</strong> $<?php echo number_format($resultado['subtotal_mensuales'], 2, ',', '.'); ?></li>
                    <li><strong>Subtotal otros cargos:</strong> $<?php echo number_format($resultado['subtotal_otros'], 2, ',', '.'); ?></li>
                    <li><strong>Recargo aplicado:</strong> <?php echo $resultado['porcentaje_recargo']; ?>% (solo sobre mensuales)</li>
                    <li><strong>Monto de recargo:</strong> $<?php echo number_format($resultado['monto_recargo'], 2, ',', '.'); ?></li>
                    <li><strong>Total a pagar:</strong> $<?php echo number_format($resultado['total'], 2, ',', '.'); ?></li>
                    <li><strong>Modal bloqueado:</strong> <?php echo $resultado['bloquea_modal'] ? 'SÍ - No se puede cerrar' : 'NO - Se puede cerrar'; ?></li>
                </ul>
            </div>

            <div style="text-align: center; margin-top: 30px;">
                <a href="index.html" style="display: inline-block; background: #667eea; color: white; padding: 12px 30px; border-radius: 5px; text-decoration: none; font-weight: 600;">
                    Volver al Menú Principal
                </a>
            </div>
        </div>
    </body>
    </html>
    <?php
}
?>
