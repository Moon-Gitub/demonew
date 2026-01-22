<?php

require_once "../modelos/medios_pago.modelo.php";

if(isset($_POST["idMedioPago"])){
    $item = "id";
    $valor = $_POST["idMedioPago"];
    $medioPago = ModeloMediosPago::mdlMostrarMediosPago("medios_pago", $item, $valor);
    echo json_encode($medioPago);
}
