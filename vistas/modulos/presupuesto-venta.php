<?php 

  date_default_timezone_set('America/Argentina/Mendoza'); 

  $item = "id";
  $valor = $_GET["idPresupuesto"];
  $venta = ControladorPresupuestos::ctrMostrarPresupuestos($item, $valor);

  $itemUsuario = "id";
  $valorUsuario = $venta["id_vendedor"];
  $vendedor = ControladorUsuarios::ctrMostrarUsuarios($itemUsuario, $valorUsuario);

  $itemCliente = "id";
  $valorCliente = $venta["id_cliente"];
  $cliente = ControladorClientes::ctrMostrarClientes($itemCliente, $valorCliente);

?>

<div class="content-wrapper">

  <section class="content">

    <div class="row">

      <!--=====================================
      EL FORMULARIO
      ======================================-->
      <div class="col-lg-7 col-xs-12" >
        
        <div class="box box-success">
          
        <div class="box-header with-border">

          <form role="form" method="post" class="formularioVentaCaja" id="ventaCajaFormulario">
			<input type="hidden" id="idPresupuesto" name="idPresupuesto" value="<?php echo $_GET["idPresupuesto"]; ?>">
            <!-- <div class="pull-right">
              
            </div> -->

            <div class="row">
                <div class="col-md-3">
                  <div class="form-group">
                    <label for="gross_amount" class="col-sm-12 control-label">Día: <?php echo date('d-m-Y') ?></label>
                  </div>  
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label for="gross_amount" class="col-sm-12 control-label">Hora: <?php echo date('h:i a') ?></label>
                  </div>  
                </div>
                <div class="col-md-3">
                  
                </div>
                <div class="col-md-4">
                  <div class="input-group">
                    <span title="Listas de precio" class="input-group-addon" style="background-color: #ddd">Listas $</span>
                      <?php 
                      $arrListasPrecio = [
                        'precio_venta' => 'Publico'
                      ];

                      $arrListasPrecioHabilitadas = explode(',', $_SESSION['listas_precio']);

                      echo '<select class="form-control input-sm" name="radioPrecio" id="radioPrecio">';
                      foreach ($arrListasPrecio as $key => $value) {

                        if (in_array($key, $arrListasPrecioHabilitadas)) {
                          echo '<option value="' . $key . '" selected>' . $value . '</option>';
                        } else {
                          echo '<option value="' . $key . '" disabled>' . $value . '</option>';
                        }

                      }  

                      echo '</select>';

                      ?>
                  </div>
                </div>
              </div>

              <input type="hidden" id="fechaActual" name="fechaActual" value="<?php echo date("Y-m-d H:i:s");?>">

              <input type="hidden" name="idVendedor" id="idVendedor" value="<?php echo $_SESSION["id"]; ?>">

            </div>

            <div class="box-body">

            <div class="row">
              <div class="col-md-4">
                <div class="input-group">
               
                  <span title="Tipos de comprobante" class="input-group-addon" style="background-color: #ddd"><i class="fa fa-bullseye"></i></span>
                  <?php

                  $arrCbtes = json_decode($respuesta['tipos_cbtes']);

                  echo '<select title="Seleccione el tipo de comprobante" class="form-control input-sm selectTipoCbte" id="nuevotipoCbte" name="nuevotipoCbte" >';
                  echo '<option value="">Seleccione comprobante</option>';
                  echo '<option value="0" selected>X</option>';
                  foreach ($arrCbtes as $key => $value) {

                    echo '<option value="' . $value->codigo . '">' . $value->descripcion . '</option>';

                  }

                  echo '</select>';

                  ?>

                </div>
              </div>
              <div class="col-md-4">
                <div class="input-group">

                  <span title="Puntos de venta" class="input-group-addon" style="background-color: #ddd"><i class="fa fa-terminal"></i></span>
                  <?php

                    $arrPuntos = json_decode($arrayEmpresa['ptos_venta'], true);
                    $arrPuntosHabilitados = explode(',', $_SESSION['puntos_venta']);

                    echo '<select title="Seleccione el punto de venta" class="form-control input-sm" id="nuevaPtoVta" name="nuevaPtoVta">';
                    echo '<option value="0">Seleccione punto de venta</option>';

                    foreach ($arrPuntos as $key => $value) {

                      if (in_array($value["pto"], $arrPuntosHabilitados)) {
                        echo '<option value="' . $value["pto"] . '" selected>' . $value["pto"] . "-" . $value["det"]  . '</option>';
                      } else {
                        echo '<option value="' . $value["pto"] . '" disabled>' . $value["pto"] . "-" . $value["det"]  . '</option>';
                      }

                    }

                  echo '</select>';

                  ?>

              </div>
              </div>
              <div class="col-md-4">
                <div class="input-group">

                <span title="Concepto" class="input-group-addon" style="background-color: #ddd"><i class="fa fa-circle-o"></i></span>

                  <?php 
                  $arrConceptos = [ 
                    "0" => "Seleccionar concepto",
                    "1" => "Productos",
                    "2" => "Servicios",
                    "3" => "Productos y Servicios"
                  ];

                  echo '<select class="form-control input-sm selectConcepto" name="nuevaConcepto" id="nuevaConcepto">';
                  foreach ($arrConceptos as $key => $value) {

                    if ($key == $respuesta['concepto_defecto']) {
                      echo '<option value="' . $key . '" selected>' . $value . '</option>';
                    } else {
                      echo '<option value="' . $key . '">' . $value . '</option>';
                    }

                  }  

                  echo '</select>';

                  ?>

              </div>
              </div>
            </div>

            <div class="row lineaServicio" style="padding-top: 10px;"  >

            <!--=====================================
            ENTRADA DE SERVICIO DESDE
            ======================================--> 

            <div class="col-md-4">

              <div class="input-group">

                <span class="input-group-addon" style="background-color: #ddd">Desde</span>

                 <input type="text" class="form-control input-sm nuevaFecServicios" id="nuevaFecDesde" name="nuevaFecDesde" placeholder="Ingrese fecha">

              </div>

            </div>

            <!--=====================================
            ENTRADA DE SERVICIO HASTA
            ======================================--> 

            <div class="col-md-4">

              <div class="input-group">

                <span class="input-group-addon" style="background-color: #ddd">Hasta</span>

                  <input type="text" class="form-control input-sm nuevaFecServicios" id="nuevaFecHasta" name="nuevaFecHasta" placeholder="Ingrese fecha">

              </div>

            </div>

            <!--=====================================
            ENTRADA DE SERVICIO VENCIMIENTO
            ======================================--> 

            <div class="col-md-4">

              <div class="input-group">

                <span class="input-group-addon" style="background-color: #ddd">Vto.</span>

                  <input type="text" class="form-control input-sm nuevaFecServicios" id="nuevaFecVto" name="nuevaFecVto" placeholder="Ingrese fecha">

              </div>

            </div>

          </div>

            <!--=====================================
            ENTRADA DEL CLIENTE
            ======================================-->
             <div class="form-group">
                  
                  <div class="input-group" style="padding-top: 10px;">
                    
                    <?php

                     $clienteElegido = ($cliente["id"] == 1 ) ? $cliente["id"]."-".$cliente["nombre"] : $cliente["id"]."-".$cliente["nombre"] ." DNI: ".$cliente["documento"];

                    ?>

                    <input type="text" class="form-control" id="autocompletarClienteCaja" name="autocompletarCliente" required value="<?php echo $clienteElegido; ?>">

                    <input type="hidden" id="seleccionarCliente" name="seleccionarCliente" value="<?php echo $cliente["id"]; ?>">

                    <span class="input-group-btn"><button type="button" class="btn btn-default" data-toggle="modal" data-target="#modalAgregarCliente" data-dismiss="modal">Agregar cliente</button></span>
                  
                  </div>
                
                </div>

            <!--=====================================
            ENTRADA PARA AGREGAR PRODUCTO
            ======================================--> 
                
              <div class="row" style="padding-top: 10px">
                
                <div class="col-xs-2" ><center>Cantidad</center></div>
                <div class="col-xs-6" ><center>Artículo</center></div>
        		<div class="col-xs-2" ><center>P. Unitario</center></div>
        		<div class="col-xs-2" ><center>Precio</center></div>
				
              </div>

                <div class="form-group row nuevoProductoCaja" style="width:100%; height:200px; overflow-y:auto; overflow-x: text;">

                <?php

                $listaProducto = json_decode($venta["productos"], true);

                foreach ($listaProducto as $key => $value) {

                  if($value["id"] != 1) {

                    $item = "id";
                    $valor = $value["id"];
                    $orden = "id";

                    $respuesta = ControladorProductos::ctrMostrarProductos($item, $valor, $orden);

                    $stockAntiguo = $respuesta["stock"] + $value["cantidad"];
                    
                    echo '<div class="row" style="padding-left:25px;padding-bottom:5px;">
					
						<div class="col-xs-2 nuevaCantidad">
                
                            <input type="number" style="text-align:center;" class="form-control nuevaCantidadProductoCaja" name="nuevaCantidadProductoCaja" min="1" value="'.$value["cantidad"].'" stock="'.$stockAntiguo.'" nuevoStock="'.$value["stock"].'" required>

                          </div>
					
              
                          <div class="col-xs-6" style="padding-right:0px">
              
                            <div class="input-group">
                  
                              <span class="input-group-btn"><button type="button" class="btn btn-danger quitarProductoCaja" idProducto="'.$value["id"].'"><i class="fa fa-times"></i></button></span>

                              <input type="text" class="form-control nuevaDescripcionProductoCaja" idProducto="'.$value["id"].'" name="agregarProducto" value="'.$value["descripcion"].'" readonly required>
							  
							  <input type="hidden" class="form-control nuevaCategoria" name="nuevaCategoria" value="'.$value["categoria"].'" readonly required>

                            </div>

                          </div>

                          

                          <div class="col-xs-2 ingresoPrecio">

                            <div class="input-group">

                              <span class="input-group-addon"><i class="ion ion-social-usd"></i></span>
                     
                              <input type="text" style="text-align:center;" class="form-control nuevoPrecioProductoUnitario" precioReal="'.$value["precio"].'" name="nuevoPrecioProductoUnitario" value="'.$value["precio"].'" readonly required>
     
                            </div>
                 
                          </div>
						  
						   <div class="col-xs-2 ingresoPrecio" style="padding-left:0px">

                            <div class="input-group">

                              <span class="input-group-addon"><i class="ion ion-social-usd"></i></span>
                     
                              <input type="text" style="text-align:center;" class="form-control nuevoPrecioProductoCaja" precioReal="'.$value["precio"].'" name="nuevoPrecioProductoCaja" value="'.$value["total"].'" readonly required>
     
                            </div>
                 
                          </div>

                        </div>';

                  } else {

                       echo '<div class="row" style="padding-left:25px;padding-bottom:5px;">
						
							<div class="col-xs-2 nuevaCantidad">
                
                            <input type="number" style="text-align:center;" class="form-control nuevaCantidadProductoCaja" name="nuevaCantidadProductoCaja" min="1" value="'.$value["cantidad"].'" stock="'.$stockAntiguo.'" nuevoStock="'.$value["stock"].'" required>

                          </div>
						  
                          <div class="col-xs-6" style="padding-right:0px">
              
                            <div class="input-group">
                  
                              <span class="input-group-btn"><button type="button" class="btn btn-danger quitarProductoCaja" idProducto="'.$value["id"].'"><i class="fa fa-times"></i></button></span>

                              <input type="text" class="form-control nuevaDescripcionProductoCaja nuevoProductoLibre" idProducto="'.$value["id"].'" name="agregarProducto" value="'.$value["descripcion"].'" required>
								
								 <input type="hidden" class="form-control nuevaCategoria" name="nuevaCategoria" value="'.$value["categoria"].'" readonly required>

                            </div>

                          </div>

                          <div class="col-xs-2 ingresoPrecio">

                            <div class="input-group">

                              <span class="input-group-addon"><i class="ion ion-social-usd"></i></span>
                     
                              <input type="text" style="text-align:center;" class="form-control nuevoPrecioProductoUnitario" precioReal="'.$value["precio"].'" name="nuevoPrecioProductoUnitario" value="'.$value["precio"].'" required>
     
                            </div>
                 
                          </div>

                          <div class="col-xs-2 ingresoPrecio" style="padding-left:0px">

                            <div class="input-group">

                              <span class="input-group-addon"><i class="ion ion-social-usd"></i></span>
                     
                              <input type="number" class="form-control nuevoPrecioProductoCaja nuevoProductoLibre" precioReal="'.$value["precio"].'" name="nuevoPrecioProductoCaja" value="'.$value["total"].'" required>
     
                            </div>
                 
                          </div>

                        </div>';
                  }

                }


                ?>

                </div>

                <input type="hidden" id="listaProductosCaja" name="listaProductosCaja" value="<?php echo htmlspecialchars($venta['productos']); ?>">

               <input type="hidden" id="listaDescuentoCaja" name="listaDescuentoCaja">

              <input type="hidden" id="nuevoPrecioImpuestoCaja" name="nuevoPrecioImpuestoCaja"> <!-- No se para que se usa -->

              <input type="hidden" id="listaMetodoPagoCajaForm" name="listaMetodoPagoCaja">

              <input type="hidden" id="nuevoTotalVentaCajaForm" name="nuevoTotalVentaCaja">

              <input type="hidden" id="nuevoInteresPorcentajeCajaForm" name="nuevoInteresPorcentajeCaja">

              <input type="hidden" id="nuevoDescuentoPorcentajeCajaForm" name="nuevoDescuentoPorcentajeCaja">

              <!-- Campos IVA -->
              <!-- <input type="text" id="nuevoVtaCajaIva0" name="nuevoVtaCajaIva0" value="0"> -->
              <input type="hidden" id="nuevoVtaCajaIva2" name="nuevoVtaCajaIva2" value="<?php echo $venta["iva_2"]?>">
              <input type="hidden" id="nuevoVtaCajaIva5" name="nuevoVtaCajaIva5" value="<?php echo $venta["iva_5"]?>">
              <input type="hidden" id="nuevoVtaCajaIva10" name="nuevoVtaCajaIva10" value="<?php echo $venta["iva_10"]?>">
              <input type="hidden" id="nuevoVtaCajaIva21" name="nuevoVtaCajaIva21" value="<?php echo $venta["iva_21"]?>">
              <input type="hidden" id="nuevoVtaCajaIva27" name="nuevoVtaCajaIva27" value="<?php echo $venta["iva_27"]?>">

              <!-- Campos base imponible -->
              <input type="hidden" id="nuevoVtaCajaBaseImp0" name="nuevoVtaCajaBaseImp0" value="<?php echo $venta["base_imponible_0"]?>">
              <input type="hidden" id="nuevoVtaCajaBaseImp2" name="nuevoVtaCajaBaseImp2" value="<?php echo $venta["base_imponible_2"]?>">
              <input type="hidden" id="nuevoVtaCajaBaseImp5" name="nuevoVtaCajaBaseImp5" value="<?php echo $venta["base_imponible_5"]?>">
              <input type="hidden" id="nuevoVtaCajaBaseImp10" name="nuevoVtaCajaBaseImp10" value="<?php echo $venta["base_imponible_10"]?>">
              <input type="hidden" id="nuevoVtaCajaBaseImp21" name="nuevoVtaCajaBaseImp21" value="<?php echo $venta["base_imponible_21"]?>">
              <input type="hidden" id="nuevoVtaCajaBaseImp27" name="nuevoVtaCajaBaseImp27" value="<?php echo $venta["base_imponible_27"]?>">

              <hr>

				<div class="row">
					<div class="col-lg-3 col-xs-12">
					</div>
					<div class="col-lg-6 col-xs-6">
					<div class="input-group">
						 <span class="input-group-addon"><i class="ion ion-social-usd"></i></span>
						<input type="number" step="0.01" min="0" class="form-control input-lg" id="nuevoPrecioNetoCajaForm" value="<?php echo $venta["total"]?>"value="<?php echo $venta["total"]?>" name="nuevoPrecioNetoCaja" placeholder="0,00" readonly style="font-size: 60px; text-align: center; height:85px;">


				  </div>
				 </div>
				 <div class="col-lg-3 col-xs-12">
				</div>
				</div>

          </div>

          <div class="box-footer">
         
            <center><button type="submit" class="btn btn-primary" id="btnGuardarVentaCaja">Guardar</button></center>

          </div>

        </form>

        </div>
            
      </div>
      <!--=====================================
      LA TABLA DE PRODUCTOS
      ======================================-->

      <div class="col-lg-5 text-md text-sm text-xs">

        <div class="box box-warning">

          <div class="box-header with-border"></div>
      
            <div class="box-body">
      
              <div class="row">
                          
                <div class="col-md-6">
                  
                  <!--=====================================
                  ENTRADA DEL VENDEDOR
                  ======================================-->
              
                  <div class="form-group">
                  
                    <div class="input-group">
                      
                      <span class="input-group-addon"><i class="fa fa-user"></i></span> 

                      <input type="text" class="form-control input-sm" id="nuevoVendedor" value="<?php echo $_SESSION["nombre"]; ?>" readonly>

                    </div>

                  </div> 

                </div>

                <div class="col-md-6">
                  
                  <!--=====================================
                  ENTRADA DEL CÓDIGO
                  ======================================--> 

                  <div class="form-group">
                    
                
                  </div>

                </div>

              </div>
              
              <div class="box-body">

                <div class="row">
                          
                  <div class="col-xs-6" ><center>Cod. artículo</center></div>

                  <div class="col-xs-6" ><center>Cantidad</center></div>

                </div>

                <hr>
                <div class="col-md-6">

                  <input type="text" class="form-control input-sm ventaCajaInputs" id="ventaCajaDetalle" name="ventaCajaDetalle" style="text-align:center;">

                  <input type="hidden" id="ventaCajaDetalleHidden" name="ventaCajaDetalleHidden" >  

                  <input type="hidden" id="seleccionarProducto" name="seleccionarProducto" >

                </div>

                <div class="col-md-6">
                
                  <input type="number" class="form-control input-sm ventaCajaInputs" onfocus="this.select();" id="ventaCajaCantidad" name="ventaCajaCantidad" style="text-align:center;" value="1">

                </div>

             </div>

              
   
                <!--=====================================
                BOTÓN PARA AGREGAR PRODUCTO - este boton se usa en disponsitivos moviles, lo comento por las dudas
                ======================================-->

                <!-- <button type="button" class="btn btn-default text-lg btnAgregarProducto">Agregar producto</button> -->

                <!--=====================================
                ENTRADA MÉTODO DE PAGO
                ======================================-->

        </div>

      </div>

    </div>

<!--Slide-->
<!-- <div class="col-lg-5 text-md text-sm text-xs" id="slide">
	 <div class="box box-success">

          <div class="box-header with-border"></div>
          <div id="myCarousel" class="carousel slide" data-ride="carousel">

    <ol class="carousel-indicators">
        <li data-target="#myCarousel" data-slide-to="0" class="active"></li>
        <li data-target="#myCarousel" data-slide-to="1"></li>
        <li data-target="#myCarousel" data-slide-to="2"></li>
    </ol>


    <div class="carousel-inner">
        <div class="item active">
            <img src="vistas/img/plantilla/logoYunta.png" alt="">
        </div>
        <div class="item">
            <img src="vistas/img/plantilla/logoYunta.png" alt="">
        </div>
        <div class="item">
            <img src="vistas/img/plantilla/logoYunta.png" alt="">
        </div>
    </div>


    <a class="left carousel-control" href="#myCarousel" data-slide="prev">
        <span class="glyphicon glyphicon-chevron-left"></span>
        <span class="sr-only">Previous</span>
    </a>
    <a class="right carousel-control" href="#myCarousel" data-slide="next">
        <span class="glyphicon glyphicon-chevron-right"></span>
        <span class="sr-only">Next</span>
    </a>
</div>
      

	</div>
</div> -->

<!--precioProducto-->
<div class="col-lg-5 text-md text-sm text-xs" id="consultarPrecio"  style="display:none"  >

   <div class="box box-success">

    <div class="box-header with-border"></div>
    <section class="content-header">
    	<center><h1>Ingresar Codigo Producto</h1></center>
    	<hr>
 	
  <div class="form-group">
                  
  <center><div class="input-group">
                    
<input type="text" autofocus class="form-control input-lg " onkeyup="borrarCodigoOculto(this.value);" id="precioProducto" name="precioProducto" style="text-align:center;">

			<input type="text" id="precioProductotext" name="precioProductotext" > 	
			<hr> 
			<center><h2 id="descripcionConsultaPrecio"></h2></center>
			<hr>
			<center><h1 id="consultaPrecioProducto"></h1></center>  
			<hr>									
    </div>
    </center>            
   </div>      

 </section>

    </div>

    </div>

    </div>
  
  </section>

</div>

<!--=====================================
MODAL AGREGAR CLIENTE
======================================-->

<div id="modalAgregarCliente" class="modal fade" role="dialog">
  
  <div class="modal-dialog">

    <div class="modal-content">

      <form role="form" method="post">

        <input type="hidden" name="agregarClienteDesde" value="index.php?ruta=editar-venta&idVenta=<?php echo $_GET["idVenta"]; ?>">

        <!--=====================================
        CABEZA DEL MODAL
        ======================================-->

        <div class="modal-header" style="background:#3c8dbc; color:white">

          <button type="button" class="close" data-dismiss="modal">&times;</button>

          <h4 class="modal-title">Agregar cliente</h4>

        </div>

        <!--=====================================
        CUERPO DEL MODAL
        ======================================-->

        <div class="modal-body">

          <div class="box-body">

            <!-- ENTRADA PARA TIPO DOCUMENTO -->
            
            <div class="form-group">
              
              <div class="input-group">
              
                <span class="input-group-addon"><i class="fa fa-list-ul"></i></span> 

                <select class="form-control input-lg" name="nuevoTipoDocumento">
                  
                  <option value="0">Seleccionar tipo documento</option>
                  <option value="96">DNI</option>
                  <option value="80">CUIT</option>
                  <option value="86">CUIL</option>
                  <option value="87">CDI</option>
                  <option value="89">LE</option>
                  <option value="90">LC</option>
                  <option value="92">En trámite</option>
                  <option value="93">Acta nacimiento</option>
                  <option value="94">Pasaporte</option>
                  <option value="91">CI extranjera</option>

                </select>

              </div>

            </div>

            <!-- ENTRADA PARA EL DOCUMENTO ID -->
            
            <div class="form-group">
              
              <div class="input-group">
              
                <span class="input-group-addon"><i class="fa fa-key"></i></span> 

                <input type="number" min="0" step="1" class="form-control input-lg" name="nuevoDocumentoId" placeholder="Ingresar documento">

              </div>

            </div>            

            <!-- ENTRADA PARA EL NOMBRE -->
            
            <div class="form-group">
              
              <div class="input-group">
              
                <span class="input-group-addon"><i class="fa fa-user"></i></span> 

                <input type="text" class="form-control input-lg" name="nuevoCliente" placeholder="Ingresar nombre o razón social" required>

              </div>

            </div>

            <!-- ENTRADA PARA TIPO DOCUMENTO -->
            
            <div class="form-group">
              
              <div class="input-group">
              
                <span class="input-group-addon"><i class="fa fa-list-ul"></i></span> 

                <select class="form-control input-lg" name="nuevoCondicionIva">

                  <option value="">Seleccione condicion I.V.A.</option>
                  <option value="1">IVA Responsable Inscripto</option>
                  <option value="6">Responsable Monotributo</option>
                  <option value="5">Consumidor Final</option>
                  <option value="2">IVA Sujeto Exento</option>
                  <option value="3">IVA Responsable no Inscripto</option>
                  <option value="4">IVA no Responsable</option>
                  <option value="12">Pequeño Contribuyente Eventual</option>
                  <option value="13">Monotributista Social</option>
                  <option value="14">Pequeño Contribuyente Eventual Social</option>
  
                </select>

              </div>

            </div>

            <!-- ENTRADA PARA EL EMAIL -->
            
            <div class="form-group">
              
              <div class="input-group">
              
                <span class="input-group-addon"><i class="fa fa-envelope"></i></span> 

                <input type="email" class="form-control input-lg" name="nuevoEmail" placeholder="Ingresar email">

              </div>

            </div>

            <!-- ENTRADA PARA EL TELÉFONO -->
            
            <div class="form-group">
              
              <div class="input-group">
              
                <span class="input-group-addon"><i class="fa fa-phone"></i></span> 

                <input type="text" class="form-control input-lg" name="nuevoTelefono" placeholder="Ingresar teléfono" data-inputmask="'mask':'(999) 999-9999'" data-mask>

              </div>

            </div>

            <!-- ENTRADA PARA LA DIRECCIÓN -->
            
            <div class="form-group">
              
              <div class="input-group">
              
                <span class="input-group-addon"><i class="fa fa-map-marker"></i></span> 

                <input type="text" class="form-control input-lg" name="nuevaDireccion" placeholder="Ingresar dirección" >

              </div>

            </div>

             <!-- ENTRADA PARA LA FECHA DE NACIMIENTO -->
            
            <div class="form-group">
              
              <div class="input-group">
              
                <span class="input-group-addon"><i class="fa fa-calendar"></i></span> 

                <input type="text" class="form-control input-lg" name="nuevaFechaNacimiento" placeholder="Ingresar fecha nacimiento" data-inputmask="'alias': 'yyyy/mm/dd'" data-mask>

              </div>

            </div>
  
          </div>

        </div>

        <!--=====================================
        PIE DEL MODAL
        ======================================-->

        <div class="modal-footer">

          <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Salir</button>

          <button type="submit" class="btn btn-primary">Guardar cliente</button>

        </div>

      </form>

      <?php

        $crearCliente = new ControladorClientes();
        $crearCliente -> ctrCrearCliente();

      ?>

    </div>

  </div>

</div>

<!--=====================================
MODAL COBRAR VENTA
======================================-->

<div id="modalCobrarVenta" class="modal fade" role="dialog">
  
  <div class="modal-dialog">

    <div class="modal-content">

      <!-- <form role="form" method="post"> -->

        <!--=====================================
        CABEZA DEL MODAL
        ======================================-->

        <div class="modal-header" style="background:#3c8dbc; color:white">

          <!-- <button type="button" class="close" data-dismiss="modal">&times;</button> -->

          <h4 class="modal-title">Cobro de venta</h4>

        </div>

        <!--=====================================
        CUERPO DEL MODAL
        ======================================-->

        <div class="modal-body">

          <div class="box-body">

            <!-- ENTRADA PARA ID VENTA  -->
            <!-- <input type="text" name="ingresoCajaidVenta" id="ingresoCajaidVenta" value="ventas"> -->

            <!-- ENTRADA PARA MEDIO PAGO  -->
            <!-- <input type="text" name="ingresoMedioPago" id="ingresoMedioPago"> -->

            <!-- ENTRADA PARA TIPO (INGRESO / EGRESO)-  -->
            <input type="hidden" name="ingresoCajaTipo" id="ingresoCajaTipo" value="1">

           <!-- ENTRADA PARA DESCRIPCIONL -->
            
            <div class="form-group">
              
              <div class="input-group">
              
                <span class="input-group-addon"><i class="fa fa-list-ul"></i></span> 

                <input type="text" class="form-control" name="ingresoCajaDescripcion" id="ingresoCajaDescripcion" readonly value="INGRESOS POR VENTA"> 

              </div>

            </div>

          </div>

          <div class="form-group row">
                  
            <div class="col-xs-3">
              
               <div class="input-group">

                  <!-- <span class="input-group-btn"><button type="button" class="btn btn-success agergarMedioPago" ><i class="fa fa-plus"></i></button></span> -->

                <select class="form-control" id="nuevoMetodoPagoCaja">
                  <option value="">Medio de pago</option>
                  <option value="Efectivo">Efectivo</option>
                  <!-- <option value="Presupuesto">Presupuesto</option> -->
                  <option value="TD">Tarjeta Débito</option>     
                  <option value="TC">Tarjeta Crédito</option>
                  <option value="CC">Cuenta Corriente</option>
                  <!-- <option value="Mixto">Pago mixto</option> -->
      
                </select>    

              </div>

            </div>

            <div class="cajasMetodoPagoCaja"></div> <!--Aca se cargan los input de codigo tarjeta, select tarjeta, cuotas  -->

          </div>
		  
      <input type="hidden" id="listaMetodoPagoCaja"> <!--Efectivo, tarjeta debito o tarjeta credito -->

		<div class="form-group">
              <div class="col-md-6">
              <div class="input-group">
              
                <span class="input-group-addon">Entrega</span> 

                <input type="text" class="form-control" id="nuevoValorEfectivo" onkeyup="cambioCalculo(this.value);" name="nuevoValorEfectivo"> 

              </div>
			  </div>
			   <div class="col-md-6">
			   <div class="input-group">
              
                <span class="input-group-addon">Cambio</span> 

                <input type="text" class="form-control" id="cambio" name="cambio"> 

              </div>
			</div>	
            </div>
			<hr>					


          <!-- <input type="text" id="mxMediosPagos" name="mxMediosPagos"> -->

          <hr>

				  <div class="row">
                  
            <div class="col-md-12">

              <!-- <input type="text" name="nuevoImpuestoVentaCaja" id="nuevoImpuestoVentaCaja"> -->

              <!-- <input type="text" name="nuevoPrecioImpuestoCaja" id="nuevoPrecioImpuestoCaja"> -->

              <!-- <input type="text" name="interesTarjetaCaja" id="interesTarjetaCaja"> -->
          
              <!-- <input type="text" name="totalVentaMetodoPagoCaja" id="totalVentaMetodoPagoCaja" value="0">  -->
              
              <!-- <input type="text" name="totalVentaCaja" id="totalVentaCaja"> -->
              
              <table class="table">

                  <tr>

                    <td style="vertical-align:middle; border: none;">Total:</td>

                    <td style="border: none;">

                      <div class="input-group">
                 
                        <span class="input-group-addon"><i class="ion ion-social-usd"></i></span>

                        <input type="number" step="0.01" min="0" class="form-control input-sm" id="nuevoPrecioNetoCaja" value="<?php echo $venta["total"]?>" placeholder="0,00" readonly style="font-size: 18px;">
              
                      </div>

                    </td>
					
					</tr>
				  
				   <tr id="filaInteresCaja" style="display:none;">

                    <td style="vertical-align:middle; border: none;">Interés:</td>

                    <td style=" border: none;">

                        <div class="row">

                          <div class="col-xs-6">
                            
                            <div class="input-group">
                         
                              <span class="input-group-addon"><b>%</b></span>

                              <input type="number" step="0.01" min="0" placeholder="0,00" style="text-align:center; font-size: 18px;" class="form-control input-sm nuevoInteresCaja" id="nuevoInteresPorcentajeCaja">
                      
                            </div>

                          </div>

                          <div class="col-xs-6">
                            
                            <div class="input-group">
                       
                              <span class="input-group-addon"><i class="ion ion-social-usd"></i></span>

                              <input type="number" step="0.01" min="0" placeholder="0,00" style="text-align:center; font-size: 18px;" class="form-control input-sm nuevoInteresCaja" id="nuevoInteresPrecioCaja">
                    
                            </div>

                          </div>
                          
                        </div>

                      </td>

                  </tr>

                  <tr id="filaDescuentoCaja" style="display:none;">

                    <td style="vertical-align:middle; border: none;">Descuento:</td>

                    <td style="border: none;">

                      <div class="row">

                        <div class="col-xs-6">
                          
                          <div class="input-group">
                       
                            <span class="input-group-addon"><b>%</b></span>

                            <input type="number" step="0.01" min="0" placeholder="0,00" style="text-align:center; font-size: 18px;" class="form-control input-sm nuevoDescuentoCaja" id="nuevoDescuentoPorcentajeCaja" >
                    
                          </div>

                        </div>

                        <div class="col-xs-6">
                          
                          <div class="input-group">
                     
                            <span class="input-group-addon"><i class="ion ion-social-usd"></i></span>

                            <input type="number" step="0.01" min="0" style="text-align:center; font-size: 18px;" class="form-control input-sm nuevoDescuentoCaja" id="nuevoDescuentoPrecioCaja" placeholder="0,00" >
                  
                          </div>

                        </div>

                      </div>

                    </td>

                </tr>

                <tr>

                    <td style="vertical-align:middle; border: none;"><b>TOTAL:</b></td>

                    <td style="border: none;">

                    <div class="input-group">

                      <span class="input-group-addon"><i class="ion ion-social-usd"></i></span>

                      <input type="number" step="0.01" min="0" style="font-size: 18px; font-weight:bold; text-align:center; " class="form-control input-sm" id="nuevoTotalVentaCaja" value="<?php echo $venta["total"]?>" total="" placeholder="0,00" readonly required>

                    </div>

                  </td>

                </tr>

              </table>

            </div>

          </div>

        </div>

        <!--=====================================
        PIE DEL MODAL
        ======================================-->

        <div class="modal-footer">

          <button type="button" id="btnSalirMedioPagoCaja" class="btn btn-default pull-left" data-dismiss="modal">Salir</button>

          <button type="button" id="btnCobrarMedioPagoCaja" onClick="this.disabled=true;" class="btn btn-primary">Guardar e imprimir</button>

        </div>

                        <!-- Observaciones facturacion -->
        <div class="box-body" style="display: none; background-color: #f5c5ca" id="divVisualizarObservacionesFactura">
          
          <p>No se pudo autorizar el comprobante</p>
          <span id="impTicketCobroCajaObservacionFact" style="font-size: 12px;">
            
          </span>

        </div>


    </div>

  </div>

</div>

<!--=====================================
IMPRIMIR TICKET CAJA
======================================-->
<div id="modalImprimirTicketCaja" class="modal fade" role="dialog" style="overflow-y: scroll;">
  
  <div class="modal-dialog">

    <div class="modal-content">

      <!--=====================================
      CABEZA DEL MODAL
      ======================================-->

      <div class="modal-header" style="background:#3c8dbc; color:white">

        <!-- <button type="button" class="close" data-dismiss="modal">&times;</button> -->

        <h4 class="modal-title">Ticket</h4>

      </div>

      <!--=====================================
      CUERPO DEL MODAL
      ======================================-->

      <div class="modal-body">

        <div class="box-body">

            <div id="impTicketCobroCaja" style="font-size: 15px;">

             <br>

             <?php 

                $tiposCbtes = array(
                0 => 'X',
                1 => 'Factura A',
                6 => 'Factura B', 
                11 => 'Factura C',
                //'Factura E' => 0, 
                51 => 'Factura M',
                2 => 'Nota Débito A',
                7 => 'Nota Débito B',
                12 => 'Nota Débito C',
                //'Nota Débito E' => 0, 
                52 => 'Nota Débito M',
                3 => 'Nota Crédito A',
                8 => 'Nota Crédito B',
                13 => 'Nota Crédito C',
                //'Nota Crédito E' => 0,
                53 => 'Nota Crédito M',
                4 => 'Recibo A',
                9 => 'Recibo B',
                15 => 'Recibo C',
                //'Recibo E' => 0, 
                54 => 'Recibo M',
                '' => 'no definido'
                );

                $arrTipoDocumento = array(
                96 => "DNI",
                80 => "CUIT",
                86 => "CUIL",
                87 => "CDI",
                89 => "LE",
                90 => "LC",
                92 => "En trámite",
                93 => "Acta nacimiento",
                94 => "Pasaporte",
                91 => "CI extranjera",
                99 => "Otro",
                0 => "(no definido)");

                $condIva = array(
                1 => "IVA Responsable Inscripto ",
                2 => "IVA Sujeto Exento ",
                3 => "IVA Responsable no Inscripto ",
                4 => "IVA no Responsable ",
                5 => "Consumidor Final ",
                6 => "Responsable Monotributo ",
                7 => "Sujeto no Categorizado ",
                8 => "Proveedor del Exterior ",
                9 => "Cliente del Exterior ",
                10 => "IVA Liberado – Ley Nº 19.640 ",
                11 => "IVA Responsable Inscripto – Agente de Percepción ",
                12 => "Pequeño Contribuyente Eventual ",
                13 => "Monotributista Social ",
                14 => "Pequeño Contribuyente Eventual Social",
                ''=>"(no definido)"
                );

              echo '<b>'. $arrayEmpresa["razon_social"] . '</b> <br>';

              echo $arrayEmpresa["domicilio"] . '<br>';
              echo 'Tel.: ' . $arrayEmpresa["telefono"] . '<br>';
              echo 'Localidad: ' . $arrayEmpresa["localidad"] . ' C.P.: ' . $arrayEmpresa["codigo_postal"] . '<br>';
              echo 'CUIT: ' . $arrayEmpresa["cuit"] . ' II.BB.: ' . $arrayEmpresa["numero_iibb"] . '<br>';
              echo 'Cond. I.V.A.: ' . $condIva[$arrayEmpresa["condicion_iva"]] . '<br> <br>';

              ?>
              <hr>
              <!-- <span id="tckFechaVentaCaja">Fecha:</span> <br>
              <span id="tckTipoCbteVentaCaja">TipoCbte:</span> <br>
              <span id="tckPtoVtaNumVentaCaja">PtoVta Num:</span> <br><br>
              <span id="tckNombreVentaCaja">Nombre:</span><br>
              <span id="tckTipoNumDocVentaCaja">TipoNumDoc:</span><br>
              <span id="tckCondIvaVentaCaja">CondIva:</span><br>
              <span id="tckDomicilioVentaCaja">Domicilio:</span> -->

                <!--FACTURA: DATOS RECEPTOR -->
                <span id="tckDatosFacturaFecha"></span><br>
                <span id="tckDatosFacturaTipoCbte"></span><br>
                <!-- <span id="tckDatosFacturaPtoNum"></span><br> -->
                <span id="tckDatosFacturaNumCbte"></span><br>
                
                <!-- <span id="tckDatosFacturaTipoDoc"></span><br>
                <span id="tckDatosFacturaNumDoc"></span><br> -->
                <span id="tckDatosFacturaNombreCliente"></span><br>
                <!-- <span id="tckDatosFacturaCondIva"></span> -->
          <hr>

             <br>
             

            Detalle
             <br>
                <table width="100%" id="tckDetalleVentaCaja">
                  
                  <tr>
                    <th width="10%"><center>Cant.</center></th></center>
                    <th width="60%"><center>Descrip.</center></th>
                    <th width="30%"><center>Total</center></th>
                  </tr>

                </table>
              <br>
              <!-- <div><b>Descuentos: $ <span id="tckDescuentoVentaCaja"></span></b></div> -->
               <!-- <div style="width: 500px; height: 1px; background: #000"></div>  -->

              <div id="tckDetalleFacturaA"></div>
              <div><b>TOTAL: $ <span id="tckTotalVentaCaja"></span></b></div>
              <div><b>Medio pago: <span id="tckMedioPagoVentaCaja"></span></b></div>
			        <br>
              
              <!-- FACTURA: DATOS CAE - VTOCAE -->
              <div id="tckDatosFacturaCAE" style="display: none; font-size: 15px; font-style: italic;">
                <span id="tckDatosFacturaNumCAE"></span><br>
                <span id="tckDatosFacturaVtoCAE"></span>

              </div>

              <div style="text-align: center">Muchas gracias por su compra</div> 


            </div>

        </div>

      </div>

      <!--=====================================
      PIE DEL MODAL
      ======================================-->

      <div class="modal-footer">

        <button type="button" id="btnSalirTicketControl" class="btn btn-default pull-left" data-dismiss="modal">Salir</button>

        <button type="button" id="btnImprimirTicketControl" class="btn btn-primary">Imprimir</button>

      </div>

    </div>

  </div>

</div>



<!--=====================================
AGREGAR PRODUCTO
======================================-->
<div id="modalAgregarProductoCaja" class="modal fade" role="dialog" style="overflow-y: scroll;">
  
  <div class="modal-dialog">

    <div class="modal-content">

      <!--=====================================
      CABEZA DEL MODAL
      ======================================-->

      <div class="modal-header" style="background:#3c8dbc; color:white">

        <h4 class="modal-title">Agregar producto</h4>

      </div>

      <!--=====================================
      CUERPO DEL MODAL
      ======================================-->

      <div class="modal-body">

        <div class="box-body">

           <div class="form-group">
              
              <div class="input-group">
              
                <span class="input-group-addon"><i class="fa fa-code"></i></span> 

                <input type="text" class="form-control" id="nuevoCodigoCaja" name="nuevoCodigo" placeholder="Código producto" required>

              </div>

            </div>

            <!-- ENTRADA PARA LA DESCRIPCIÓN -->

             <div class="form-group">
              
              <div class="input-group">
              
                <span class="input-group-addon"><i class="fa fa-product-hunt"></i></span> 

                <input type="text" class="form-control" id="nuevaDescripcionCaja" name="nuevaDescripcionCaja" placeholder="Ingresar descripción">

              </div>

            </div>

            <div class="row" style="padding-bottom: 10px;">
              <div class="col-xs-12" style="border-bottom-style: groove;">Venta</div>
            </div>

            <!-- ENTRADA PARA PRECIO VENTA -->
             <div class="form-group row">

                <!-- ENTRADA PARA PRECIO VENTA -->
                <div class="col-xs-4">
                
                  <div class="input-group">
                  
                    <span class="input-group-addon"><i class="fa fa-arrow-down"></i></span> 

                    <input type="text" title="Neto" class="form-control" id="nuevoPrecioVentaCaja" name="nuevoPrecioVenta" step="any" min="0" placeholder="Precio de venta" readonly>

                  </div>
                
                  <br>

                </div>

                <!-- ENTRADA PARA IVA -->

                <div class="col-xs-4">
                
                  <div class="input-group">
                  
                  <span class="input-group-addon"><i class="fa fa-percent"></i></span> 
                    <select name="nuevoIvaVenta" id="nuevoIvaVentaCaja" class="form-control">
                      <option value="">I.V.A.</option>
                      <option value="0.00">0%</option>
                      <option value="2.50">2,5%</option>
                      <option value="5.00">5%</option>
                      <option value="10.50">10,5%</option>
                      <option value="21.00" selected>21%</option>
                      <option value="27.00">27%</option>
                    </select>

                  </div>

                </div>

                <!-- ENTRADA PARA PRECIO COMPRA IVA INCLUIDO -->                

                <div class="col-xs-4">
                
                  <div class="input-group">
                  
                    <span class="input-group-addon"><i class="fa fa-usd"></i></span> 

                    <input type="number" title="Precio de venta (IVA incluido)" class="form-control" id="nuevoPrecioVentaIvaIncluidoCaja" name="nuevoPrecioVentaIvaIncluido" step="any" min="0" placeholder="Precio venta (IVA incluido)">

                  </div>

                </div>

            </div>

        </div>  

      </div>

      <!--=====================================
      PIE DEL MODAL
      ======================================-->

      <div class="modal-footer">

        <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Salir</button>

        <button type="button" id="btnGuardarNuevoProductoCaja" class="btn btn-primary">Crear</button>

      </div>

    </div>

  </div>

</div>