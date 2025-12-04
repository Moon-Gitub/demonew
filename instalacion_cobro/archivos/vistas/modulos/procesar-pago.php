<?php
/*=============================================
PROCESAR RESPUESTA DE MERCADOPAGO
=============================================*/

$estado = isset($_GET["status"]) ? $_GET["status"] : null;
$paymentId = isset($_GET["payment_id"]) ? $_GET["payment_id"] : null;
$preferenceId = isset($_GET["preference_id"]) ? $_GET["preference_id"] : null;
$externalReference = isset($_GET["external_reference"]) ? $_GET["external_reference"] : null;
$paymentType = isset($_GET["payment_type"]) ? $_GET["payment_type"] : null;
$merchantOrderId = isset($_GET["merchant_order_id"]) ? $_GET["merchant_order_id"] : null;

// ID del cliente desde la referencia externa o desde $_ENV
$idCliente = $externalReference ? intval($externalReference) : (isset($_ENV['MOON_CLIENTE_ID']) ? intval($_ENV['MOON_CLIENTE_ID']) : (isset($_SERVER['MOON_CLIENTE_ID']) ? intval($_SERVER['MOON_CLIENTE_ID']) : 7));

// Obtener datos del cliente y cuenta corriente
$clienteMoon = ControladorSistemaCobro::ctrMostrarClientesCobro($idCliente);
$ctaCteCliente = ControladorSistemaCobro::ctrMostrarSaldoCuentaCorriente($idCliente);

// Calcular monto con recargos
$datosCobro = ControladorMercadoPago::ctrCalcularMontoCobro($clienteMoon, $ctaCteCliente);
$abonoMensual = $datosCobro['monto'];
$abonoBase = $datosCobro['abono_base'];
$tieneRecargo = $datosCobro['tiene_recargo'];
$porcentajeRecargo = $datosCobro['porcentaje_recargo'];

?>

<div class="content-wrapper">
    <section class="content-header">

    <?php

    if (($estado == 'approved' || $estado == 'pending') && $paymentId) {

        try {
            // 1. Guardar el pago en la tabla mercadopago_pagos
            $datosPago = array(
                'id_cliente_moon' => $idCliente,
                'payment_id' => $paymentId,
                'preference_id' => $preferenceId,
                'monto' => $abonoMensual,
                'estado' => $estado,
                'fecha_pago' => date('Y-m-d H:i:s'),
                'payment_type' => $paymentType,
                'payment_method_id' => isset($_GET["payment_method_id"]) ? $_GET["payment_method_id"] : 'desconocido',
                'datos_json' => json_encode($_GET)
            );

            // Verificar que no esté duplicado
            if (!ControladorMercadoPago::ctrVerificarPagoProcesado($paymentId)) {
                $resultadoPago = ControladorMercadoPago::ctrRegistrarPagoConfirmado($datosPago);

                if ($resultadoPago === "ok") {
                    // 2. Actualizar estado del intento
                    if ($preferenceId) {
                        $nuevoEstado = ($estado == 'approved') ? 'completado' : 'pendiente';
                        ModeloMercadoPago::mdlActualizarEstadoIntento($preferenceId, $nuevoEstado);
                    }

                    // Solo aplicar a cuenta corriente si está APPROVED
                    if ($estado == 'approved') {
                        // 3. Registrar interés si corresponde (recargo por mora)
                        if ($tieneRecargo) {
                            $montoInteres = $abonoMensual - $abonoBase;
                            if ($montoInteres > 0) {
                                ControladorSistemaCobro::ctrRegistrarInteresCuentaCorriente($idCliente, $montoInteres);
                            }
                        }

                        // 4. Registrar el pago en cuenta corriente
                        ControladorSistemaCobro::ctrRegistrarMovimientoCuentaCorriente($idCliente, $abonoMensual);

                        // 5. Desbloquear cliente si estaba bloqueado
                        if ($clienteMoon["estado_bloqueo"] == "1") {
                            ControladorSistemaCobro::ctrActualizarClientesCobro($idCliente, 0);
                        }

                        echo '<script>
                        swal({
                            type: "success",
                            title: "¡Pago exitoso!",
                            text: "Tu pago de $' . number_format($abonoMensual, 2, ',', '.') . ' ha sido registrado correctamente",
                            showConfirmButton: true,
                            confirmButtonText: "Cerrar"
                        }).then((result) => {
                            if (result.value) {
                                window.location = "inicio";
                            }
                        })
                        </script>';
                    } else {
                        // Pago pendiente - guardado pero no aplicado
                        echo '<script>
                        swal({
                            type: "warning",
                            title: "Pago pendiente",
                            text: "Tu pago de $' . number_format($abonoMensual, 2, ',', '.') . ' está siendo procesado. Te notificaremos cuando se confirme.",
                            showConfirmButton: true,
                            confirmButtonText: "Cerrar"
                        }).then((result) => {
                            if (result.value) {
                                window.location = "inicio";
                            }
                        })
                        </script>';
                    }

                } else {
                    throw new Exception("Error al guardar el pago en la base de datos");
                }
            } else {
                // Pago ya procesado anteriormente
                echo '<script>
                swal({
                    type: "info",
                    title: "Pago ya procesado",
                    text: "Este pago ya fue registrado anteriormente",
                    showConfirmButton: true,
                    confirmButtonText: "Cerrar"
                }).then((result) => {
                    if (result.value) {
                        window.location = "inicio";
                    }
                })
                </script>';
            }

        } catch (Exception $e) {
            error_log("Error al procesar pago: " . $e->getMessage());

            echo '<script>
            swal({
                type: "error",
                title: "Error al procesar",
                text: "Ocurrió un error al registrar el pago. Por favor contacta a soporte.",
                showConfirmButton: true,
                confirmButtonText: "Cerrar"
            }).then((result) => {
                if (result.value) {
                    window.location = "inicio";
                }
            })
            </script>';
        }

    } else {
        // Pago rechazado o cancelado
        echo '<script>
        swal({
            type: "error",
            title: "Pago no completado",
            text: "El pago no pudo ser procesado. Por favor intenta nuevamente.",
            showConfirmButton: true,
            confirmButtonText: "Cerrar"
        }).then((result) => {
            if (result.value) {
                window.location = "inicio";
            }
        })
        </script>';
    }

    ?>

    </section>
</div>
