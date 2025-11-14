<?php

class ControladorClientesCtaCte{

	/*=============================================
	MOSTRAR CTA CTE CLIENTE
	=============================================*/
	static public function ctrMostrarCtaCteCliente($item, $valor){
		$tabla = "clientes_cuenta_corriente";

		$respuesta = ModeloClientesCtaCte::mdlMostrarCtaCteCliente($tabla, $item, $valor);

		return $respuesta;

	}

	/*=============================================
	MOSTRAR REGISTRO DE CTA CTE CLIENTE POR ID
	=============================================*/
	static public function ctrMostrarCtaCteClienteId($item, $valor){

		$tabla = "clientes_cuenta_corriente";

		$respuesta = ModeloClientesCtaCte::mdlMostrarCtaCteClienteId($tabla, $item, $valor);

		return $respuesta;

	}

	/*=============================================
	CREAR REGISTRO CTA CTE CLIENTES
	=============================================*/
	static public function ctrIngresarCtaCte() {

		if(isset($_POST["tipoMovimientoCtaCteCliente"])){ //0 deibtos del cliente (ventas) - 1 creditos del cliente (pagos)

			date_default_timezone_set('America/Argentina/Mendoza');

			$fecha = date('Y-m-d');
			$hora = date('H:i:s');
			$fec_hor = $fecha.' '.$hora;

			$dineroMedio = (isset($_POST["ingresoMedioPago"])) ? $_POST["ingresoMedioPago"] : 'Efectivo';
			$dineroMedio = (isset($_POST["ingresoMedioPagoCtaCteCliente"])) ? $_POST["ingresoMedioPagoCtaCteCliente"] : $dineroMedio;

		   	$tabla = "clientes_cuenta_corriente";

	   		$msjCaja = (($_POST["tipoMovimientoCtaCteCliente"] == 1) ? "Crédito" : "Débito");

	   		$numeroRecibo = ($_POST["tipoMovimientoCtaCteCliente"] == 1 && $dineroMedio != 'Bonificacion') ? ModeloClientesCtaCte::mdlMostrarUltimoNumeroRecibo()["ult_recibo"] + 1 : null;
				
	   		$datosCtaCte = array(
					'fecha' => $fec_hor,
					'id_cliente' => $_POST["idClienteMovimientoCtaCteCliente"],
					'tipo' => $_POST["tipoMovimientoCtaCteCliente"],
					'descripcion' => $_POST["detalleMovimientoCtaCteCliente"], 
					'id_venta' => null, 
					'importe' => $_POST["montoMovimientoCtaCteCliente"], 
					'metodo_pago' => $dineroMedio, 
					'numero_recibo' => $numeroRecibo);
	   		
			$respuesta = ModeloClientesCtaCte::mdlIngresarCtaCte($tabla, $datosCtaCte);

	   		if($_POST["tipoMovimientoCtaCteCliente"] == 1 && $dineroMedio != 'Bonificacion') {
		   		//INGRESO DATOS A CAJA
		   		$datos = array(
		   				'fecha' => $fec_hor,
		   				'id_usuario' => $_POST['idUsuarioMovimientoCtaCteCliente'],
		   				'punto_venta' => $_POST['puntoVentaMovimientoCtaCteCliente'],
		   				'tipo' => $_POST['tipoMovimientoCtaCteCliente'],
		   				'monto' => $_POST['montoMovimientoCtaCteCliente'],
		   				'medio_pago' => $dineroMedio,
		   				'descripcion' => $_POST['detalleMovimientoCtaCteCliente'],
		   				'codigo_venta' => null,
		   				"id_venta" => null,
	   					"id_cliente_proveedor" => $_POST["idClienteMovimientoCtaCteCliente"],
		   				'observaciones' => null);

		   		$respuesta = ModeloCajas::mdlIngresarCaja('cajas', $datos);

	   		}

		   	if($respuesta == "ok"){

	   			echo'<script>

				swal({
				  type: "success",
				  title: "Caja",
				  text: "' . $msjCaja . ' cargado correctamente",
				  showConfirmButton: true,
				  confirmButtonText: "Cerrar",
				  allowOutsideClick: false
				  }).then(function(result){
							if (result.value) {

							window.location = "index.php?ruta=clientes_cuenta&id_cliente='.$_POST["idClienteMovimientoCtaCteCliente"].'";

							}
						})

				</script>';

			}
			
		}

	}

	/*=============================================
	ELIMINAR REGISTRO CTA CTE CLIENTES
	=============================================*/
	static public function ctrEliminarCtaCte(){

	}

	/*=============================================
	ENTREGAS REALIZADAS POR VENTA (lo uso en ventas.php para saber que entregas hay por venta)
	=============================================*/
	static public function ctrMostrarEntregasXVenta($item, $valor){
		$tabla="clientes_cuenta_corriente";

		$respuesta = ModeloClientesCtaCte::mdlMostrarEntregasXVenta($tabla, $item, $valor);

		return $respuesta;

	}

	/*=============================================
	LISTADO DE CLIENTES CON SALDO EN CUENTA CORRIENTE
	Esta consulta trae los clientes donde total de ventas - total de pagos es distindo de 0
	Usada en clientes.php
	=============================================*/
	static public function ctrMostrarSaldos(){
		// $tabla="clientes_cuenta_corriente";

		$respuesta = ModeloClientesCtaCte::mdlMostrarSaldos();

		return $respuesta;

	}

	/*=============================================
	SALDO TOTAL EN CUENTA CORRIENTE
	Usada en clientes-cuenta-saldos y en inicio
	=============================================*/
	static public function ctrMostrarSaldoTotal(){
		$respuesta = ModeloClientesCtaCte::mdlMostrarSaldoTotal();
		return $respuesta;
	}
	
	/*=============================================
	SALDO TOTAL EN CUENTA CORRIENTE POR CLIENTE
	Usada en crear venta
	=============================================*/
	static public function ctrMostrarSaldoTotalXCliente($id){
		$respuesta = ModeloClientesCtaCte::mdlMostrarSaldoTotalXCliente($id);
		return $respuesta;
	}

	/*=============================================
	LISTADO DE CLIENTES CON DEUDA EN CUENTA CORRIENTE
	Esta consulta trae los clientes donde total de ventas - total de pagos es distindo de 0, y la fecha limite cliente es mayor a la actual
	Usada en clientes.php
	=============================================*/
	static public function ctrMostrarDeudas(){
		// $tabla="clientes_cuenta_corriente";

		$respuesta = ModeloClientesCtaCte::mdlMostrarDeudas();

		return $respuesta;

	}

    /*=============================================
	ENVIO MAIL 
	=============================================*/
	static public function ctrEnviarMailInformeSaldo($arr){

		$arrayEmpresa = ModeloEmpresa::mdlMostrarEmpresa('empresa', 'id', 1);
		$to = $arr['mailCliente'];
		$from = $arrayEmpresa["mail"];
		$fromName = $arrayEmpresa["razon_social"];
		$subject = 'Detalle Cuenta Corriente';

		/*$message = $arr['mensajeCtaCteCliente'];
		$message .= "<br><p><b>".$respEmpresa["razon_social"]."</b><br>";
		$message .= "Domicilio: ".$respEmpresa["domicilio"]."<br>";
		$message .= "Telefono: ".$respEmpresa["telefono"]."<br>";
		$message .= "Email: ".$respEmpresa["mail"]."</p>";*/

		$msj = '<html>

			  <head></head>

			  <body>

			    <p>'.$arr['textoCliente'].'</p>

			    <div>

			      '.$arrayEmpresa["razon_social"].'.<br>

			      Dirección: '. $arrayEmpresa["domicilio"] . ' - ' . $arrayEmpresa["localidad"] .'<br>

			      Telefono: '.$arrayEmpresa["telefono"].'.<br>

			      E-mail: '.$arrayEmpresa["mail"].'.<br>

			    </div>

			  </body>

			  </html>';

			  $eol = PHP_EOL;
			$cabeceras = "From: " . $fromName . ' <'.$from.'>' . $eol;
			$cabeceras .= 'MIME-Version: 1.0' . $eol;
			$cabeceras .= "Content-Type: text/html; charset=\"iso-8859-1\"" . $eol . $eol;

		    if(mail ( $to , $subject , $msj, $cabeceras )){

		    	$respuesta = "ok";

		    } else {

		    	$respuesta = "error";

		    }

		return $respuesta;

	}
    
}