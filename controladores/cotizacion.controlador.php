<?php

class ControladorCotizacion{

	/*=============================================
	CREAR CAJA
	=============================================*/

	static public function ctrNuevaCotizacion(){

		if(isset($_POST["nuevaCotizacionPesos"])){

		   	$file = fopen("cotizacion","w");
            fwrite($file,$_POST["nuevaCotizacionFecha"].PHP_EOL);
            fwrite($file,$_POST["nuevaCotizacionPesos"].PHP_EOL);
            fclose($file);

            $respuesta = ModeloProductos::mdlModificarPrecioCotizacion($_POST["nuevaCotizacionPesos"]);

		   	if($file){

		   			echo'<script>

						swal({
						  type: "success",
						  title: "Cotización",
						  text: "Cotización cargada correctamente",
						  showConfirmButton: true,
						  confirmButtonText: "Cerrar"
						  }).then(function(){

						  	window.location="inicio";

						  })
	
					</script>';

	   		}

		}
			
	}

 }
