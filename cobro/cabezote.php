 <header class="main-header">
 	
	<!--=====================================
	LOGOTIPO
	======================================-->
	<a href="inicio" class="logo">
		
		<!-- logo mini -->
		<span class="logo-mini">
			
			<!--<img src="vistas/img/plantilla/icono-blanco.png" class="img-responsive" style="padding:10px"> -->
			<i class="fa fa-moon-o fa-2x"></i>

		</span>

		<!-- logo normal -->

		<span class="logo-lg">
			
			<!--<img src="vistas/img/plantilla/logo-blanco-lineal.png" class="img-responsive" style="padding:10px 0px"> -->
			<i class="fa fa-moon-o fa-2x"></i>
			POS | Moon

		</span>

	</a>
	<?php
	//==================================
	//      SISTEMA DE COBRO
	//==================================
	//CONEXION A MOONDESARROLLOS
	//ACÁ SE DEBE COLOCAR EL ID DEL CLIENTE(SISTEMA DE COBRO).
	// Se obtiene desde .env, si no existe usa 7 como fallback
	$idCliente = intval(getenv('MOON_CLIENTE_ID') ?: 7);
	$clavePublicaMercadoPago = 'APP_USR-3cb7e729-47de-4703-8d21-a07136e22d34';	
	$accesTokenMercadoPago = 'APP_USR-3927436741225472-082909-292500aeed544c3108afcfa534c55e57-1188183100';
	
	if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on')
	     $rutaRespuesta = "https://";   
	else  
	     $rutaRespuesta = "http://";
	
	$rutaRespuesta .= $_SERVER['HTTP_HOST'];  
	$rutaRespuesta .= "/index.php?ruta=procesar-pago";
	$clienteMoon = ControladorSistemaCobro::ctrMostrarClientesCobro($idCliente);
	$ctaCteCliente = ControladorSistemaCobro::ctrMostrarSaldoCuentaCorriente($idCliente);
	$ctaCteMov = ControladorSistemaCobro::ctrMostrarMovimientoCuentaCorriente($idCliente);
	$abonoMensual = $clienteMoon["mensual"];
	
	$muestroModal = false;
	$fijoModal = false;
	
	if(!isset($_GET["preference_id"])){
	
		require_once 'extensiones/vendor/autoload.php';
	
		//AGREGA CREDENCIALES
		//CLAVE PRIVADA (Access token es la clave privada de la aplicación para generar pagos. Debes usarla solo para tus integraciones)
		MercadoPago\SDK::setAccessToken($accesTokenMercadoPago);
	
		// CREA UN OBJETO DE PREFERENCIA
		$preference = new MercadoPago\Preference();
	
		$estadoClienteBarra = '';
		$mensajeCliente = '';
	
		$diaActual = date("d");
	
		$diasCorte = 26 - $diaActual;
	
		if($ctaCteCliente["saldo"] <= 0) { //saldo 0, está al día, mayor a 0 tiene $ a favor
				
				ControladorSistemaCobro::ctrActualizarClientesCobro($idCliente, 0); //POR SI ESTABA BLOQUEADO LO DESBLOQUEO
	
		} else {
	
			//DEL 1 AL 5 	-> OK - no muestro nada
			//DEL 6 AL 9 	-> OK - solo aviso del cobro
			//DEL 10 AL 20	-> muestro modal - aplico 10% interes
			//DEL 21 AL 25	-> NARANJA - muestro modal y días para bloqueo - aplico 15% interes
			//MAYOR A 26	-> BLOQUEO - ROJO - muestro modal fijo - aplico 15% interes
			if ($clienteMoon["estado_bloqueo"] == "1"){ //cliente bloqueado por falta de pago
	
				$estadoClienteBarra = 'style="background-color: red;"' ;
				$mensajeCliente = '<span> <center>SISTEMA SUSPENDIDO. Regularice su situación</center></span>';
				$muestroModal = true;
				$fijoModal = true;
				$abonoMensual = $abonoMensual * 1.1;
	
			} else {
	
				if ($diaActual > 4 && $diaActual <= 9){
	
					//$estadoClienteBarra = 'style="background-color: orange;"' ;
					$mensajeCliente = '<span style="font-size: 12px; color: #fff"> <center>Estimado Cliente! Se le recuerda el abono mensual del sistema.</center></span>';
					$abonoMensual = $abonoMensual;
					$muestroModal = true;
	
				} elseif ($diaActual > 10 && $diaActual <= 21){
	
					//$estadoClienteBarra = 'style="background-color: orange;"' ;
					$mensajeCliente = '<span style="font-size: 15px; color: #fff"> <center>Estimado Cliente! Se le recuerda el abono mensual del sistema.</center></span>';
					$abonoMensual = $abonoMensual * 1.10;
					$muestroModal = true;
	
				} elseif ($diaActual > 21 && $diaActual <= 26) {
	
					$estadoClienteBarra = 'style="background-color: orange;"' ;
					$mensajeCliente = '<span style="font-size: 20px"> <center>Estimado cliente, solicitamos el abono mensual del uso del sistema. Restan '.$diasCorte.' días para proceder a la suspensión.</center></span>';
					$abonoMensual = $abonoMensual * 1.15;
					$muestroModal = true;
	
				} elseif ($diaActual > 26) {
	
					ControladorSistemaCobro::ctrActualizarClientesCobro($idCliente, 1); //BLOQUEO EL SISTEMA
					$estadoClienteBarra = 'style="background-color: red;"' ;
					$mensajeCliente = '<span> <center>SISTEMA SUSPENDIDO. Regularice su situación</center></span>';
					$abonoMensual = $abonoMensual * 1.15;
					$muestroModal = true;
					$fijoModal = true;
	
				}
	      
	      $abonoMensual = $ctaCteCliente["saldo"] - $clienteMoon["mensual"] + $abonoMensual;
	
			}
	
			$item = new MercadoPago\Item();
			$item->title = "Mensual-POS";
			$item->quantity = 1;
			$item->unit_price = $abonoMensual;
			$preference->items = array($item);
			$preference->save();
	
			 $preference->back_urls = array(
					"success" => $rutaRespuesta,
					"failure" => $rutaRespuesta
					);
	
		    $preference->auto_return = "approved"; 
		    $preference->binary_mode = true;
		    $preference->save(); 
	
		}
	}
	
	//==================================
	//      FIN SISTEMA DE COBRO
	//==================================
	?>
	<!--=====================================
	BARRA DE NAVEGACIÓN
	======================================-->
	<nav class="navbar navbar-static-top" role="navigation">

		<!-- Botón de navegación -->

	 	<a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button">

        	<span class="sr-only">Toggle navigation</span>

      	</a>

		<!-- perfil de usuario -->

		<div class="navbar-custom-menu">
				
			<ul class="nav navbar-nav">
			<?php if($_SESSION["perfil"] == "Administrador") { //dejo esto para que solo lo vean los administradores?>

			<li>
	 	 <?php echo $mensajeCliente; ?>
		  </li>
  
			<li class="dropdown tasks-menu">
			<a href="#" class="dropdown-toggle" data-toggle="dropdown">
				<i class="fa fa-moon-o" aria-hidden="true"></i>
	  		</a>
	  	<ul class="dropdown-menu">
			<li class="header" style="background-color: #000; color: #fff"><i class="fa fa-moon-o"></i> ESTADO DE CUENTA - Moon Desarrollos</li>
			<li>
				
			   <input type="hidden" id="hiddenClavePublicaMP" value="<?php echo $clavePublicaMercadoPago; ?>">
			  <!-- inner menu: contains the actual data -->
			  <ul class="menu" style="background-color: #eee;">
				  <?php 

				  echo '<p>Estado de cuenta Moon: </p>';
				  echo 'Plan mensual: $' . $clienteMoon["mensual"];

				  ?>
				  
				  <center><!--
					  <button class="btn btn-primary" data-toggle="modal" data-target="#modalCobro">
						  Pagar
					  </button>-->
				  </center>
				 
			  </ul>
			</li>
	  </ul>
	</li>	

 <?php } ?>   
								
				<li class="dropdown user user-menu">
					
					<a href="#" class="dropdown-toggle" data-toggle="dropdown">
						

					<?php
					
					if($_SESSION["foto"] != ""){

						echo '<img src="'.$_SESSION["foto"].'" class="user-image">';

					}else{


						echo '<img src="vistas/img/usuarios/default/anonymous.png" class="user-image">';

					}


					?>
						
						<span class="hidden-xs"><?php  echo $_SESSION["nombre"]; ?></span>

					</a>

					<!-- Dropdown-toggle -->

					<ul class="dropdown-menu">
						
						<li class="user-body">
							
							<div class="pull-right">
								
								<a href="salir" class="btn btn-default btn-flat">Salir</a>

							</div>

						</li>

					</ul>

				</li>

			</ul>

		</div>

	</nav>

 </header>
 <!--=====================================
MODAL COBRO
======================================-->
<div id="modalCobro" class="modal fade" role="dialog">
  
  <div class="modal-dialog">

    <div class="modal-content">

      <!--=====================================
      CABEZA DEL MODAL
      ======================================-->
      <div class="modal-header" style="background:#3c8dbc; color:white">
        <div class="modal-header">
            <h4 class="text-center well text-muted text-uppercase" id="#">SERVICIO MENSUAL</h4>
            <div class="alert alert-danger" role="alert">
              <strong>Importante!</strong> <br>Recordamos que los pagos del servicio mensual deberán realizarse antes del 10 de cada mes, pasado el 10 y hasta el 20 inclusive se cobrará un 3% de interés, pasado el 20, se cobrará un 5% de recargo y pasado el 25 se pausará el sistema hasta regular la situación.
            </div>
          </div>
      </div>

      <!--=====================================
      CUERPO DEL MODAL
      ======================================-->
      <div class="modal-body">
      	<table class="table ">
          <thead>
            <tr>
              <th>Cliente</th>
              <th>Servicio</th>
              <th>Precio</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td><?php echo $clienteMoon["nombre"];?></td>
              <td><?php echo $ctaCteMov["descripcion"] ?></td>
              <td><?php echo $abonoMensual;?></td>
            </tr>
          </tbody>
        </table>
        <div class="col-sm-6 col-xs-12 pull-right">
		<table class="table  ">
			<tbody>
				<tr>
					<td><b>Total</td>
					<td><span class="cambioDivisa"></span><b> $<span class="valorSubtotal" valor=""><?php echo $abonoMensual; ?></span></b></td>
				</tr>
				<tr> 
				    <td><div class="checkout-btn"></div></td>
				</tr>
			</tbody>
		</table>
	</div>
		
	<?php if($muestroModal) { ?>
	
		<!-- AGREGA CREDENCIALES SDK-->
		<script src="https://sdk.mercadopago.com/js/v2"></script>
		<script type="text/javascript">
                	var clavePublicaMP = document.getElementById('hiddenClavePublicaMP').value
			//CLAVE PUBLICA
			const mp = new MercadoPago(clavePublicaMP, {locale: "es-AR"}	);
			// INICIALIZA EL CHECKOUT EN MI PÁGINA
			mp.checkout({
				preference: {
					id: '<?php echo $preference->id; ?>',
				},
				render: {
					container: '.checkout-btn', // Indica el nombre de la clase donde se mostrar en el boton de pago
					label: 'Pagar', // Cambia el texto del boton de pago (opcional)
				},
			});

		</script>
		
	<?php } ?>
			
	</div>

        <!--==================================
        PIE DEL MODAL
        ======================================-->
        <div class="modal-footer">

        </div>

    </div>

  </div>

</div>

<script type="text/javascript">
	$(function(){
		<?php if($muestroModal && $fijoModal) { ?>
			$("#modalCobro").modal({backdrop: 'static', keyboard: false});
	
	<?php } elseif ($muestroModal) {?>
		
		var diaDeHoyModal = new Date, dateCformat = [diaDeHoyModal.getDate(), (diaDeHoyModal.getMonth()+1), diaDeHoyModal.getFullYear()].join('/');

    var diaAnterior = localStorage.getItem('diaMostrandoModal');

    if(dateCformat != diaAnterior){

    		var cantidadMostrado = Number(localStorage.getItem('modalCobroMostrado'));
		if(!cantidadMostrado){
		    localStorage.setItem('modalCobroMostrado', 0);
		}
		if(cantidadMostrado != 5) {
			$("#modalCobro").modal();
			cantidadMostrado = cantidadMostrado + 1;
			localStorage.setItem('modalCobroMostrado', cantidadMostrado);
		} else if (cantidadMostrado == 5) {
			localStorage.setItem('diaMostrandoModal', dateCformat);
			localStorage.setItem('modalCobroMostrado', 0);
		}

    }

	<?php } ?>	
	});
</script>