<?php 
  date_default_timezone_set('America/Argentina/Mendoza'); 
  $cbteDefecto = $objParametros->getCbteDefecto();
  $arrListasPrecio = $objParametros->getListasPrecio();
  $btnPadronAfip = (isset($arrayEmpresa["ws_padron"])) ? '' : 'disabled';
?>

<div class="content-wrapper">
  <section class="content">
    <div class="row">
      <!--=====================================
      EL FORMULARIO
      ======================================-->
      <div class="col-lg-5" >
		<div class="box box-warning">
          <div class="box-header with-border"></div>
            <div class="box-body">
		          <table class="table table-bordered table-striped dt-responsive" style="border: 1px solid black;">
					<tr>
						<td>
						 <div class="input-group">
							<label for="gross_amount" class="col-sm-12 control-label">Día: <?php echo date('d-m-Y') ?></label>
							<?php echo $variable?>
							</div>
						</td>
						<td>
						 <div class="input-group">
							<label for="gross_amount" class="col-sm-12 control-label">Hora: <?php echo date('h:i a') ?></label>
							</div>
						</td>
					<td>
                  <div class="input-group">
                    <span title="Listas de precio" class="input-group-addon" style="background-color: #ddd">Listas $</span>
                      <?php 
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
                 </td>
				</tr>
              </table>

              <input type="hidden" id="fechaActual" name="fechaActual" value="<?php echo date("Y-m-d H:i:s");?>">

              <input type="hidden" name="idVendedor" id="idVendedor" value="<?php echo $_SESSION["id"]; ?>">
              <input type="hidden" id="tokenIdTablaVentas">
 
			  <input type="hidden" name="alto" id="alto" value="">
			
              <table class="table table-bordered table-striped dt-responsive" style="border: 1px solid white;">
				<tr>
					<td>
						 <div class="input-group">
               
                  <span title="Tipos de comprobante" class="input-group-addon" style="background-color: #ddd"><i class="fa fa-bullseye"></i></span>
                  <?php

                  $arrCbtes = json_decode($arrayEmpresa['tipos_cbtes'], true);
                  array_unshift($arrCbtes, 
                              array("codigo"=>"0", "descripcion"=>"X"), 
                              array("codigo"=>"999", "descripcion"=>"Devolucion X")
										);

							  echo '<select title="Seleccione el tipo de comprobante" class="form-control input-sm selectTipoCbte" id="nuevotipoCbte" name="nuevotipoCbte" >';
							  echo '<option value="">Seleccione comprobante</option>';
							  //echo '<option value="0" selected>X</option>';
							  //echo '<option value="999" selected>Devolucion X</option>';
							  foreach ($arrCbtes as $key => $value) {

								if($value["codigo"] == $cbteDefecto){
								  echo '<option value="' . $value["codigo"] . '" selected>' . $value["descripcion"] . '</option>';
								} else {
								  echo '<option value="' . $value["codigo"] . '">' . $value["descripcion"] . '</option>';  
								}

							  }

							  echo '</select>';

							  ?>

							</div>
					</td>
					<td>
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
					</td>
					<td>
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

						if ($key == $arrayEmpresa['concepto_defecto']) {
						  echo '<option value="' . $key . '" selected>' . $value . '</option>';
						} else {
						  echo '<option value="' . $key . '">' . $value . '</option>';
						}

					  }  

					  echo '</select>';

					  ?>

				  </div>
				  </td>
				</tr>
					
                </table>


            <div class="row lineaServicio" style="padding-top: 10px;"  >

            <table class="table table-bordered table-striped dt-responsive" style="border: 1px solid white;">
				<tr>
					<td>
			             <div class="input-group">
							<span class="input-group-addon" style="background-color: #ddd">Desde</span>
								<input type="text" class="form-control input-sm nuevaFecServicios" id="nuevaFecDesde" name="nuevaFecDesde" placeholder="Ingrese fecha">

						 </div>
					</td>
					<td>					
						<div class="input-group">
							<span class="input-group-addon" style="background-color: #ddd">Hasta</span>
								<input type="text" class="form-control input-sm nuevaFecServicios" id="nuevaFecHasta" name="nuevaFecHasta" placeholder="Ingrese fecha">

							</div>
					</td>
					<td>
						<div class="input-group">
							<span class="input-group-addon" style="background-color: #ddd">Vto.</span>
								<input type="text" class="form-control input-sm nuevaFecServicios" id="nuevaFecVto" name="nuevaFecVto" placeholder="Ingrese fecha">

						</div>
					</td>
					</tr>
				</table>
			</div>

          <!--=====================================
          LINEA COMPROBANTES ASOCIADOS
          ======================================-->
          <div class="row lineaCbteAsociados" style="padding-top: 10px;"  >

           <table class="table table-bordered table-striped dt-responsive" style="border: 1px solid white;">
				<tr>
					<td>
						<div class="input-group">
							<span class="input-group-addon" style="background-color: #eee">Tipo cbte. asoc. </span>
							<?php

							  $arrCbtes = json_decode($arrayEmpresa['tipos_cbtes']);

							  echo '<select title="Seleccione el tipo de comprobante" class="form-control input-sm nuevaCbteAsociado" id="nuevotipoCbteAsociado" name="nuevotipoCbteAsociado" >';
							  echo '<option value="">Seleccione comprobante asociado</option>';

							  foreach ($arrCbtes as $key => $value) {

								if($value->codigo == '1' || $value->codigo == '4' || $value->codigo == '6' || $value->codigo == '9' || $value->codigo == '11' || $value->codigo == '15' || $value->codigo == '201' || $value->codigo == '206' || $value->codigo == '211'){

								  echo '<option value="' . $value->codigo . '">' . $value->descripcion . '</option>';  

								}

							  }

							  echo '</select>';

							  ?>
							</div>
					</td>
					<td>
						<div class="input-group">
							<span class="input-group-addon" style="background-color: #eee">Pto. vta. asoc</span>
						<?php

							  $arrPuntos = json_decode($arrayEmpresa['ptos_venta'], true);
							  $arrPuntosHabilitados = explode(',', $_SESSION['puntos_venta']);

							  echo '<select title="Seleccione el punto de venta" class="form-control input-sm nuevaCbteAsociado" id="nuevaPtoVtaAsociado" name="nuevaPtoVtaAsociado">';
							  echo '<option value="0">Seleccione punto de venta asociado</option>';

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
					</td>
					<td>
						<div class="input-group">
							<span class="input-group-addon" style="background-color: #eee">Nro. asoc.</span>

							<input type="text" class="form-control input-sm nuevaCbteAsociado" id="nuevaNroCbteAsociado" name="nuevaNroCbteAsociado" placeholder="Ingrese N° cbte asociado" autocomplete="off">

						</div>
					</td>
					</tr>
					</table>
			</div>

           <!--=====================================
            ENTRADA DEL CLIENTE
            ======================================-->

            <table class="table table-bordered table-striped dt-responsive" style="border: 1px solid white;">
				<tr>
					<th>

						<div class="input-group">

						  <input type="text" class="form-control ui-autocomplete-input input-sm" id="autocompletarClienteCaja" name="autocompletarCliente" placeholder="1-Consumidor Final" autocomplete="off">
						  <input type="hidden" id="seleccionarCliente" name="seleccionarCliente" value="1">
						  <input type="hidden" id="autocompletarClienteCajaMail">

						  <span class="input-group-btn"><button type="button" class="btn btn-default btn-sm" data-toggle="modal" data-target="#modalAgregarCliente" data-dismiss="modal">Agregar cliente</button></span>

						</div>
					</th>
				</tr>
			</table>				

         

            <!--=====================================
            ENTRADA PARA AGREGAR PRODUCTO
            ======================================--> 
                
              <div class="row" style="padding-top: 10px">
                
                <div class="col-xs-2" ><center>Cant.</center></div>
                <div class="col-xs-6" ><center>Artículo</center></div>
        		<div class="col-xs-2" ><center>P. Unitario</center></div>
        		<div class="col-xs-2" ><center>Precio</center></div>

              </div>
              <hr>

              <div class="form-group row nuevoProductoCaja" id="nuevoProductoCaja" style="width:100%; overflow-y:auto; overflow-x: text;"></div>

              <!-- CAMPOS NECESARIOS PARA ENVIAR POR POST PARA GUARDAR LA VENTA -->
              <input type="hidden" id="nuevaVentaCajaForm" name="nuevaVentaCaja">
              
              <input type="hidden" id="listaProductosCaja" name="listaProductosCaja" value="[]">

              <input type="hidden" id="listaDescuentoCaja" name="listaDescuentoCaja">

              <input type="hidden" id="nuevoPrecioImpuestoCaja" name="nuevoPrecioImpuestoCaja"> <!-- No se para que se usa -->

              <input type="hidden" id="listaMetodoPagoCajaForm" name="listaMetodoPagoCaja">

              <input type="hidden" id="nuevoTotalVentaCajaForm" name="nuevoTotalVentaCaja">

              <input type="hidden" id="nuevoInteresPorcentajeCajaForm" name="nuevoInteresPorcentajeCaja">

              <input type="hidden" id="nuevoDescuentoPorcentajeCajaForm" name="nuevoDescuentoPorcentajeCaja">

              <!-- Campos IVA -->
              <!-- <input type="text" id="nuevoVtaCajaIva0" name="nuevoVtaCajaIva0" value="0"> -->
              <input type="hidden" id="nuevoVtaCajaIva2" name="nuevoVtaCajaIva2" value="0">
              <input type="hidden" id="nuevoVtaCajaIva5" name="nuevoVtaCajaIva5" value="0">
              <input type="hidden" id="nuevoVtaCajaIva10" name="nuevoVtaCajaIva10" value="0">
              <input type="hidden" id="nuevoVtaCajaIva21" name="nuevoVtaCajaIva21" value="0">
              <input type="hidden" id="nuevoVtaCajaIva27" name="nuevoVtaCajaIva27" value="0">

              <!-- Campos base imponible -->
              <input type="hidden" id="nuevoVtaCajaBaseImp0" name="nuevoVtaCajaBaseImp0" value="0">
              <input type="hidden" id="nuevoVtaCajaBaseImp2" name="nuevoVtaCajaBaseImp2" value="0">
              <input type="hidden" id="nuevoVtaCajaBaseImp5" name="nuevoVtaCajaBaseImp5" value="0">
              <input type="hidden" id="nuevoVtaCajaBaseImp10" name="nuevoVtaCajaBaseImp10" value="0">
              <input type="hidden" id="nuevoVtaCajaBaseImp21" name="nuevoVtaCajaBaseImp21" value="0">
              <input type="hidden" id="nuevoVtaCajaBaseImp27" name="nuevoVtaCajaBaseImp27" value="0">

              <hr>

         <table class="table table-bordered table-striped dt-responsive" style="border: 1px solid white;">
				<tr>
					<th>

          <div class="input-group">
             <span class="input-group-addon"><i class="ion ion-social-usd"></i></span>

            <input type="number" step="0.01" min="0" class="form-control input-lg" id="nuevoPrecioNetoCajaForm" name="nuevoPrecioNetoCaja" placeholder="0,00" id="nuevoPrecioNetoCajaForm" readonly style="font-size: 50px;text-align: center;">

			</div>
			</td>
		</tr>
	</table>
          </div>

          <div class="box-footer">
         
            <center><button type="submit" class="btn btn-primary" id="btnGuardarVentaCaja">Cobrar (F7)</button></center>

          </div> 


        </div>
        </div>     
      
      <!--=====================================
      LA TABLA DE PRODUCTOS
      ======================================-->
      <div class="col-lg-7 hidden-md hidden-sm hidden-xs">

        <div class="box box-warning">

          <div class="box-header with-border"></div>
      
            <div class="box-body">
      
            <table class="table table-bordered table-striped dt-responsive" style="border: 1px solid white; width:50%">
				<tr>
					<td>
                        <div class="form-group">
							<div class="input-group">
								<span class="input-group-addon"><i class="fa fa-user"></i></span> 
								<input type="text" class="form-control input-sm" id="nuevoVendedor" value="<?php echo $_SESSION["nombre"]; ?>" readonly>
							</div>

						</div> 
					</td>
					<td>
                    <?php
                     $arrSucursal = [ 
                        'stock' => 'Local',
                        '' => 'SIN SUCURSAL ASIGNADA'
                      ];
					?>
                    <input type="hidden" id="sucursalVendedor" value="<?php echo $_SESSION["sucursal"]; ?>">
                    <div class="form-group">
                         <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-building"></i></span> 
                              <input type="text" class="form-control input-sm" value="Sucursal: <?php echo $arrSucursal[$_SESSION["sucursal"]]; ?>" readonly>
						</div>
                    </div>
				</td>
			  </tr>
             </table> 

             <table class="table table-bordered table-striped dt-responsive" id="tablaVentas">
              
               <thead>

                 <tr>
                  <th>Imagen</th>
                  <th>Código</th>
                  <th>Descripcion</th>
                  <th>Stock</th>
                  <th>Precio</th>
                  <th>Acciones</th>
                </tr>

              </thead>

            </table>
             <!--
          <table class="table table-bordered table-striped dt-responsive" style="border: 1px solid white;">
				<tr>
					<td>
						<center>Cod. artículo</center>
					</td>
					<td>
						<center>Cantidad</center>
					</td>
				</tr>
				<tr>
					<th>
					  <input type="text" class="form-control input-sm ventaCajaInputs" id="ventaCajaDetalle" name="ventaCajaDetalle" style="text-align:center;">
					  <input type="hidden" id="ventaCajaDetalleHidden" name="ventaCajaDetalleHidden" >  
					  <input type="hidden" id="seleccionarProducto" name="seleccionarProducto" >
					</th>
					<th>
                    <input type="number" class="form-control input-sm ventaCajaInputs" onfocus="this.select();" id="ventaCajaCantidad" name="ventaCajaCantidad" style="text-align:center;" value="1">
					</th>
				</tr>	
                </table>
                
            -->

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
      <input type="hidden" name="agregarClienteDesde" value="crear-venta-caja">
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
            <!-- ENTRADA PARA EL DOCUMENTO ID -->
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-key"></i></span> 
                <input type="number" min="0" step="1" class="form-control " name="nuevoDocumentoId" id="vtanuevoDocumentoId" placeholder="Ingresar documento">
                <span class="input-group-btn"><button type="button" title="Consultar en padrón de AFIP" id="vtabtnNuevoDocumentoId" class="btn btn-default" <?php echo $btnPadronAfip; ?> ><i class="fa fa-search"></i></button></span>
              </div>
            </div>            

            <!-- ENTRADA PARA TIPO DOCUMENTO -->
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-list-ul"></i></span> 
                <select class="form-control " name="nuevoTipoDocumento" id="vtanuevoTipoDocumento">
                  <option value="0">Seleccionar tipo documento</option>
                  <option value="96">DNI</option>
                  <option value="80">CUIT</option>
                  <option value="86">CUIL</option>
                  <!--<option value="87">CDI</option>
                  <option value="89">LE</option>
                  <option value="90">LC</option>
                  <option value="92">En trámite</option>
                  <option value="93">Acta nacimiento</option>
                  <option value="94">Pasaporte</option>
                  <option value="91">CI extranjera</option>-->
                  <option value="99">Otro</option>
                </select>
              </div>
            </div>

            <!-- ENTRADA PARA EL NOMBRE -->
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-user"></i></span> 
                <input type="text" class="form-control " name="nuevoCliente" id="vtanuevoCliente" placeholder="Ingresar nombre o razón social" required>
              </div>
            </div>

            <!-- ENTRADA PARA TIPO DOCUMENTO -->
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-list-ul"></i></span> 
                <select class="form-control " name="nuevoCondicionIva" id="vtanuevoCondicionIva" required>
								 <option value="">Seleccione condicion I.V.A.</option>
								 <option value="1">IVA Responsable Inscripto</option>
								 <option value="6">Responsable Monotributo</option>
								 <option value="5">Consumidor Final</option>
								 <option value="4">IVA Sujeto Exento</option>
								 <option value="7">Sujeto no categorizado</option>
								 <option value="8">Proveedor del exterior</option>
								 <option value="9">Cliente del exterior</option>
								 <option value="10">IVA Liberado - Ley N° 19.640</option>
								 <option value="13">Monotributista Social</option>
								 <option value="15">IVA No Alcanzado</option>
								 <option value="16">Monotributo Trabajador Independiente Promovido</option>
								</select>


              </div>
            </div>

            <!-- ENTRADA PARA EL EMAIL -->
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-envelope"></i></span> 
                <input type="email" class="form-control " name="nuevoEmail" id="vtanuevoEmail" placeholder="Ingresar email">
              </div>
            </div>

            <!-- ENTRADA PARA EL TELÉFONO -->
            <div class="form-group">
              <div class="input-group">              
                <span class="input-group-addon"><i class="fa fa-phone"></i></span> 
                <input type="text" class="form-control " name="nuevoTelefono" id="vtanuevoTelefono" placeholder="Ingresar teléfono" data-inputmask="'mask':'(999) 999-9999'" data-mask >
              </div>
            </div>

            <!-- ENTRADA PARA LA DIRECCIÓN -->
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-map-marker"></i></span> 
                <input type="text" class="form-control " name="nuevaDireccion" id="vtanuevaDireccion" placeholder="Ingresar dirección" >
              </div>
            </div>

            <!-- ENTRADA PARA LA FECHA DE NACIMIENTO -->            
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-calendar"></i></span> 
                <input type="text" class="form-control " name="nuevaFechaNacimiento" id="vtanuevaFechaNacimiento" placeholder="Ingresar fecha nacimiento" data-inputmask="'alias': 'yyyy/mm/dd'" data-mask>
              </div>
            </div>

            <!-- ENTRADA PARA LAS OBSERVACIONES -->            
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-list"></i></span> 
                <textarea class="form-control " rows="3" name="nuevaObservaciones" id="vtaObservacionesCliente" placeholder="Observaciones"></textarea>
              </div>
            </div>
          </div>
        </div>

        <!--=====================================
        PIE DEL MODAL
        ======================================-->
        <div class="modal-footer">
          <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Salir</button>
          <button id="btnGuardarClienteVenta"  class="btn btn-primary">Guardar cliente</button>
        </div>
    </div>
  </div>
</div>

<!--=====================================
MODAL COBRAR VENTA
======================================-->
<div id="modalCobrarVenta" class="modal fade" role="dialog">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
        <!--=====================================
        CABEZA DEL MODAL
        ======================================-->
        <div class="modal-header" style="background:#3c8dbc; color:white">
          <h4 class="modal-title">Cobro de venta</h4>
        </div>
        <!--=====================================
        CUERPO DEL MODAL
        ======================================-->
        <div class="modal-body">
          <div class="box-body">
            <!-- ENTRADA PARA TIPO (INGRESO / EGRESO)-  -->
            <input type="hidden" name="ingresoCajaTipo" id="ingresoCajaTipo" value="1">
          </div>
          <div class="row" style="padding-bottom:10px">
              <div class="col-md-6"><span id="datosCuentaCorrienteCliente" style="font-size:18px"></span></div>
          </div>
          <div class="row" style="padding-bottom:10px">
          	<div class="col-md-3">
							<div class="input-group">
								<span class="input-group-addon" style="background-color: #eee"><b>PAGO</b></span>
								<input type="text" class="form-control" id="nuevoValorEntrega">
							</div>
          	</div>
          </div>
          <div class="form-group row">
            <div class="col-md-3">
               <div class="input-group">
                  <span title="Agregar medio de pago" class="input-group-btn"><button id="agregarMedioPago" type="button" class="btn btn-success" ><i class="fa fa-plus"></i></button></span>
	                <select class="form-control" id="nuevoMetodoPagoCaja">
	                  <option value="">Medio de pago</option>
	                  <option value="Efectivo">Efectivo</option>
	                  <option value="MP" >Mercado Pago</option>
	                  <option value="TD">Tarjeta Débito</option>     
	                  <option value="TC">Tarjeta Crédito</option>
	                  <option value="CH">Cheque</option>
	                  <option value="TR">Transferencia</option>
	                  <option value="CC">Cuenta Corriente</option>
	                </select>    
              </div>
            </div>
            <div class="cajasMetodoPagoCaja"></div> <!--Aca se cargan los input de codigo tarjeta, select tarjeta, cuotas  -->
          </div>      
					<hr>
          <div class="row">
            <div class="col-md-6">
            	<div class="row" style="display: none;" id="divImportesPagoMixto">
            		<table class="table" id="listadoMetodosPagoMixto">
            			<thead>
            				<!--<tr style="background-color: #eee; text-align: center;"><td colspan="2">Medios Pago</td></tr>-->
            				<tr>
		            			<th><i class="fa fa-minus-square"></i> </th>
		            			<th>Metodo</th>
		            			<th>Importe</th>
	            			</tr>
            			</thead>
            			<tbody>
            				
            			</tbody>
            			<tfoot>
            				<tr>
            					<td></td>
            					<td></td>
            					<td style="font-size: 18px">
            						<b>SALDO: $</b> <span id="nuevoValorSaldo" style="color:red">0</span>
            					</td>
            				</tr>
            			</tfoot>
            		</table>
            	</div>
            	<input type="hidden" id="listaMetodoPagoCaja"> <!--Manda al servidor si se paga en Efectivo, tarjeta debito, tarjeta credito, etc -->
            	<input type="hidden" id="mxMediosPagos"> <!--Array con los medios de pago en pago mixto -->
            </div>
            <div class="col-md-6">
              <table class="table">
								<tr>
								  <td style="vertical-align:middle; border: none;">Total:</td>
								  <td style="border: none;">
								    <div class="input-group">
								      <span class="input-group-addon"><i class="ion ion-social-usd"></i></span>
								      <input type="number" step="0.01" min="0" class="form-control input-sm" id="nuevoPrecioNetoCaja" placeholder="0,00" readonly style="font-size: 18px;">
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
								          <input type="number" step="0.01" min="0" style="text-align:center; font-size: 18px;" class="form-control input-sm nuevoDescuentoCaja" id="nuevoDescuentoPrecioCaja" placeholder="0,00" readonly>
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
											<input type="number" step="0.01" min="0" style="font-size: 18px; font-weight:bold; text-align:center; " class="form-control input-sm" id="nuevoTotalVentaCaja" total="" placeholder="0,00" readonly required>
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
          <button type="button" id="btnSalirMedioPagoCaja" class="btn btn-default pull-left" data-dismiss="modal">Salir (ESC)</button>
          <button type="button" id="btnCobrarMedioPagoCaja" onClick="this.disabled=true;" class="btn btn-primary">Guardar e imprimir (F8)</button>
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
      <!--CABEZA DEL MODAL-->
      <div class="modal-header" style="background:#3c8dbc; color:white">
        <h4 class="modal-title">Ticket</h4>
      </div>
      <!--CUERPO DEL MODAL-->
      <div class="modal-body">
        <div class="box-body">
    		<div class="alert " id="divEventoObservacionAprobada" style="" role="alert"></div>
            <div id="impTicketCobroCaja" style="font-size: 15px;">
             <br>
             <?php 
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
              echo $arrayEmpresa["titular"] . '<br>';
              echo $arrayEmpresa["domicilio"] . '<br>';
              //echo 'Tel.: ' . $arrayEmpresa["telefono"] . '<br>';
              echo 'Localidad: ' . $arrayEmpresa["localidad"] . ' C.P.: ' . $arrayEmpresa["codigo_postal"] . '<br>';
              echo 'CUIT: <span id="cuitEmpresaEmisora">' . $arrayEmpresa["cuit"] . '</span> II.BB.: ' . $arrayEmpresa["numero_iibb"] . '<br>';
              echo 'Cond. I.V.A.: ' . $condIva[$arrayEmpresa["condicion_iva"]] . '<br> ';
              echo 'Defensa del consumidor Mendoza 0800-222-6678 <br>';

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
                <b><span id="tckDatosFacturaTipoCbte"></span></b><br>
                <!-- <span id="tckDatosFacturaPtoNum"></span><br> -->
                <span id="tckDatosFacturaNumCbte"></span><br>
                
                <!-- <span id="tckDatosFacturaTipoDoc"></span><br>
                <span id="tckDatosFacturaNumDoc"></span><br> -->
                <span id="tckDatosFacturaNombreCliente"></span><br>
                <!-- <span id="tckDatosFacturaCondIva"></span> -->
                <hr>

               <center><b>Detalle</b></center>
             <br>
                <table width="100%" id="tckDetalleVentaCaja">
                  
                  <tr>
                    <th width="15%"><center>Cant. * Unit</center></th></center>
                    <th width="55%"><center>Descrip.</center></th>
                    <th width="30%"><center>Total</center></th>
                  </tr>

                </table>
              <br>

              <div>Subtotal: $ <span id="tckSubtotalVentaCaja"></span></div>
              <div>Descuento: $ <span id="tckDescuentoVentaCaja"></span></div>

              <div id="tckDetalleFacturaA"></div>
              <div><b>TOTAL: $ <span id="tckTotalVentaCaja"></span></b></div>
              <div><b>Medio pago: </b><span id="tckMedioPagoVentaCaja"></span></div>
              <br>
              
              <!-- FACTURA: DATOS CAE - VTOCAE -->
              <div id="tckDatosFacturaCAE" style="display: none; font-size: 15px; font-style: italic;">
              <span id="tckDatosFacturaNumCAE"></span> - <span id="tckDatosFacturaVtoCAE"></span>
              <br>
                <div style="padding-top: 10px" id="dibujoCodigoQR"></div>
              </div>
              <div style="text-align: center">Controle su ticket antes de retirarse. No se aceptan devoluciones</div> 
            </div>
        </div>
      </div>
      <!-- PIE DEL MODAL-->
      <div class="modal-footer">
        <button type="button" id="btnSalirTicketControl" class="btn btn-default pull-left" data-dismiss="modal">Salir (ESC)</button>
        <button type="button" id="btnImprimirTicketControl" class="btn btn-primary"><i class="fa fa-ticket" aria-hidden="true"></i> Ticket (F9)</button>
        <button type="button" id="btnImprimirA4Control" class="btn btn-primary"><i class="fa fa-print" aria-hidden="true"></i> A4</button>
        <button type="button" id="btnEnviarMailA4" class="btn btn-primary"><i class="fa fa-envelope" aria-hidden="true"></i> Mail</button>
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
                <div class="col-xs-4"> </div>

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

<script>
    
   var Ancho= screen.width;
   var Alto= screen.height;
   if(Ancho < 450){
		document.getElementById("alto").value = 20;
		document.getElementById("nuevoProductoCaja").style.height = "60px";
		document.getElementById("nuevoPrecioNetoCajaForm").style.height = "40px";
    
	}
	else{
		document.getElementById("alto").value = 200;
		document.getElementById("nuevoProductoCaja").style.height = "200px";
		document.getElementById("nuevoPrecioNetoCajaForm").style.height = "85px";
	}
    </script>