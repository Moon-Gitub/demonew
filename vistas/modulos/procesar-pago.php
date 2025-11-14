<div class="content-wrapper">

    <section class="content-header">
    
    <?php
      
        //require_once 'extensiones/vendor/autoload.php';
        /*
        $token = $_REQUEST["token"];
        $payment_method_id = $_REQUEST["payment_method_id"];
        $installments = $_REQUEST["installments"];
        $issuer_id = $_REQUEST["issuer_id"];
        $amount=$_POST['amount'];
        $cliente=$_POST['cliente'];
        $idCliente=$_POST['idCliente'];
        */
        
        //-----
        
        $estado = $_GET["status"];
        
        //http://demo.posmoon.com/modulos/vistas/procesar-pago.php?
        //collection_id=1308934168&
        //collection_status=approved&
        //payment_id=1308934168&
        //status=approved&
        //external_reference=null&
        //payment_type=credit_card&
        //merchant_order_id=6277581735&
        //preference_id=1188183100-286bcf11-881e-4862-8a9a-d79c8d6ca9e7&
        //site_id=MLA&
        //processing_mode=aggregator&
        //merchant_account_id=null

        if ($estado == 'approved') {
            
            $diaActual = date("d");
            $interes = 0;
            if ($clienteMoon["estado_bloqueo"] == "1" || $diaActual > 26){ //cliente bloqueado por falta de pago
			    $interes = $abonoMensual * 0.15;
		    } elseif ($diaActual > 10 && $diaActual <= 21){
				$interes = $abonoMensual * 0.10;
			} elseif ($diaActual > 21 && $diaActual <= 26) {
				$interes = $abonoMensual * 0.15;
			} 
    
            if($interes > 0) { //pagó fuera de término y se aplico interes
                ControladorSistemaCobro::ctrRegistrarInteresCuentaCorriente($idCliente, $interes); //ingreso el interes
            }
            
            ControladorSistemaCobro::ctrActualizarClientesCobro(999, 0); //POR SI ESTABA BLOQUEADO LO DESBLOQUEO
            ControladorSistemaCobro::ctrRegistrarMovimientoCuentaCorriente($idCliente, $abonoMensual); //ingreso el pago

            echo'<script>
            swal({
                  type: "success",
                 
                  text: "Pago registrado correctamente",
                  showConfirmButton: true,
                  confirmButtonText: "Cerrar"
                  }).then((result) => {
                        if (result.value) {
                            window.location = "inicio";
                        }
                    })
            </script>';

        } else{
             echo'<script>
            
            swal({
                  type: "error",
                 
                  text: "Ocurrió un error al registrar el pago",
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