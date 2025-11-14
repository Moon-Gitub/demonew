 <?php

      $item = "id";
      $valor = $_GET["idVenta"];
      $venta = ControladorVentas::ctrMostrarVentas($item, $valor);

      $facturada = ControladorVentas::ctrVentaFacturadaDatos($_GET["idVenta"]);

      $itemUsuario = "id";
      $valorUsuario = $venta["id_vendedor"];
      $vendedor = ControladorUsuarios::ctrMostrarUsuarios($itemUsuario, $valorUsuario);

      $itemCliente = "id";
      $valorCliente = $venta["id_cliente"];
      $cliente = ControladorClientes::ctrMostrarClientes($itemCliente, $valorCliente);

      $porcentajeImpuesto = $venta["impuesto"] * 100 / $venta["neto"];

      $boxDisabled = ($venta["estado"] == 0 || $facturada == false) ? '' : 'style="pointer-events: none;"';

      if($venta["estado"] == 1 || $venta["estado"] == 2 || $facturada) {

        $boxDisabled = 'style="pointer-events: none;"';

      } else {

        $boxDisabled = '';
      }
          
?>

<div class="content-wrapper">

    <input type="hidden" id="estoyEditando" value="1">

    <section class="content-header">

      <h1>

        Ver-Editar venta N°: <b><?php echo $venta["codigo"] . '</b> | <a href="comprobante/'.$venta["codigo"].'"><i class="fa fa-print fa-fw"></i> </a> | <a href="extensiones/vendor/tecnickcom/tcpdf/pdf/comprobanteMail.php?codigo='.$venta["codigo"].'"><i class="fa fa-envelope fa-fw"></i> </a>'; ?>

      </h1>

    <ol class="breadcrumb">
      
      <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
      
      <li class="active">Ver-Editar venta</li>
    
    </ol>

  </section>

  <section class="content" >

    <div class="row">

      <!--=====================================
      EL FORMULARIO
      ======================================-->
      
      <div class="col-lg-7 col-xs-12">
        
        <div class="box box-success" <?php echo $boxDisabled; ?>>

          <?php 

            $tiposCbtes = array(
              0 => 'X',
              999 => 'Devolucion',
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

            if($facturada){

              echo '<div class="alert " style="background-color: #dff0d8; margin-bottom:0px;" role="alert"><b>Autorizado!</b>';

                $ptoVta = str_pad($venta["pto_vta"], 5, "0", STR_PAD_LEFT);
                $numCte = str_pad($facturada["nro_cbte"], 8, "0", STR_PAD_LEFT);
              
                    echo '<p>'.$tiposCbtes[$venta["cbte_tipo"]].' - N°: '.$ptoVta . '-'. $numCte.'</p>';
                    echo '<p style="font-style: italic;">CAE: '.$facturada["cae"].' - Vto. CAE: '.$facturada["fec_vto_cae"].'</p>';

              echo '</div>';

            } else {

              if ($venta["observaciones"] != ""){
                
                echo '<div class="alert" style="background-color: #f2dede" role="alert"><b>Error!</b><br>';
                $respAfip = json_decode($venta["observaciones"]);

                  foreach ($respAfip as $key => $value) {
                    echo '<p';
                    if (strpos($key, 'obs') !== false) {
                      echo "Observaciones: </br>";
                    } elseif (strpos($key, 'err') !== false){
                      echo "Errores: </br>";
                    } elseif (strpos($key, 'evt') !== false){
                      echo "Eventos: </br>";
                    }
                    echo $value;
                    echo '</p>';

                  }
                  echo '</div>';
                
                }               

            }

          ?>

          <form role="form" method="post" class="formularioVenta">

            <input type="hidden" name="idVendedor" value="<?php echo $venta["id_vendedor"]; ?>">

            <div class="box-body">

              <div class="row" style="padding-top: 0px;">
  
              <div class="col-md-6">

                  <div class="form-group">
            
                    <div class="input-group">
                      
                     <span class="input-group-addon" style="background-color: #ddd">Id. Venta</span>

                     <input type="text" class="form-control" id="nuevaVenta" name="editarVenta" value="<?php echo $venta["codigo"]; ?>" readonly>
                 
                    </div>
                  
                  </div>

              </div>

              <div class="col-md-6">

                  <div class="form-group">
            
                    <div class="input-group">
                      
                     <!-- <span class="input-group-addon" style="background-color: #ddd">Estado</span> -->

                     <?php

                     if($venta["estado"] == 0) { //Adeudada
                     
                        echo '<span class="label label-danger">Adeudado</span>' ;

                     } elseif($venta["estado"] == 1) { //Pagada

                         echo '<span class="label label-success">Pagado</span>';

                      } elseif ($venta["estado"] == 2) {

                        echo '<span class="label label-warning">Cta. Cte.</span>';
                       
                      }

                    ?>
                 
                    </div>
                  
                  </div>

              </div>

              </div>

              <div class="row" style="padding-top: 0px;">

              <!--=====================================
              ENTRADA TIPO DE COMPROBANTE
              ======================================-->

              <div class="col-md-3">

                <div class="input-group">
                 
                    <span title="Tipo de comprobante" class="input-group-addon" style="background-color: #ddd"><i class="fa fa-bullseye"></i></span>
                    <?php

                    $arrCbtes = json_decode($arrayEmpresa['tipos_cbtes'], true);
                    array_unshift($arrCbtes, array("codigo"=>"0", "descripcion"=>"X"));
                    array_unshift($arrCbtes, array("codigo"=>"999", "descripcion"=>"Devolucion"));

                    echo '<select title="Seleccione el tipo de comprobante" class="form-control input-sm" id="editartipoCbte" name="editartipoCbte" >';
                    echo '<option value="">Seleccione comprobante</option>';

                    foreach ($arrCbtes as $key => $value) {

                      if($venta["cbte_tipo"] == $value["codigo"]) {

                          echo '<option value="' . $value["codigo"] . '" selected>' . $value["descripcion"] . '</option>';

                        } else {

                          echo '<option value="' . $value["codigo"] . '" >' . $value["descripcion"] . '</option>';

                        }

                    }

                    echo '</select>';

                    ?>


                </div>

              </div>

            <!--=====================================
            ENTRADA FECHA EMISION
            ======================================-->            
            <div class="col-md-3">

              <div class="input-group">

                  <span class="input-group-addon" style="background-color: #ddd"><i class="fa fa-calendar-o"></i></span>

                  <input type="text" class="form-control input-sm" id="editarFecEmision" name="editarFecEmision" placeholder="Ingrese fecha" value="<?php echo date('d-m-Y', strtotime($venta['fecha']));?>">

              </div>  

            </div>

              <!--=====================================
              ENTRADA DE PUNTO VENTA
              ======================================--> 
              <div class="col-md-3">

                <div class="input-group">
                   
                    <span title="Puntos de venta" class="input-group-addon" style="background-color: #ddd"><i class="fa fa-terminal"></i></span>
                    <?php

                      $arrPuntos = json_decode($arrayEmpresa['ptos_venta'], true);
                      $arrPuntosHabilitados = explode(',', $_SESSION['puntos_venta']);

                      echo '<select title="Seleccione el punto de venta" class="form-control input-sm" id="editarPtoVta" name="editarPtoVta">';
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

              <!--=====================================
              ENTRADA DE CONCEPTO
              ======================================--> 
              <div class="col-md-3">

                <div class="input-group">

                  <span title="Concepto" class="input-group-addon" style="background-color: #ddd"><i class="fa fa-circle-o"></i></span>

                    <?php 
                    $arrConceptos = [ 
                      "0" => "Seleccionar concepto",
                      "1" => "Productos",
                      "2" => "Servicios",
                      "3" => "Productos y Servicios"
                    ];

                    echo '<select class="form-control input-sm selectConcepto" name="editarConcepto" id="editarConcepto">';
                    foreach ($arrConceptos as $key => $value) {

                      if ($key == $venta['concepto']) {
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

              <div class="row" style="padding-top: 10px;">

              </div>

              <div class="row lineaServicio" style="padding-top: 10px;"  >

                <!--=====================================
                ENTRADA DE SERVICIO DESDE
                ======================================--> 

                <div class="col-md-4">

                  <div class="input-group">

                    <span class="input-group-addon" style="background-color: #ddd">Desde</span>

                     <input type="text" class="form-control input-sm editaFecServicios" id="editarFecDesde" name="editarFecDesde" placeholder="Ingrese fecha">

                  </div>

                </div>

                <!--=====================================
                ENTRADA DE SERVICIO HASTA
                ======================================--> 

                <div class="col-md-4">

                  <div class="input-group">

                    <span class="input-group-addon" style="background-color: #ddd">Hasta</span>

                      <input type="text" class="form-control input-sm editaFecServicios" id="editarFecHasta" name="editarFecHasta" placeholder="Ingrese fecha">

                  </div>

                </div>

                <!--=====================================
                ENTRADA DE SERVICIO VENCIMIENTO
                ======================================--> 

                <div class="col-md-4">

                  <div class="input-group">

                    <span class="input-group-addon" style="background-color: #ddd">Vto.</span>

                      <input type="text" class="form-control input-sm editaFecServicios" id="editarFecVto" name="editarFecVto" placeholder="Ingrese fecha">

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

                <hr>
                <div class="row"><div class="col-xs-12" style="text-align: center" ><b>Detalle:</b></div></div>
                <div class="row">
                  
                  <div class="col-xs-6" >Artículo</div>
                  <div class="col-xs-3" >Cant.</div>
                  <div class="col-xs-3" >$</div>

                </div>

                <div class="form-group row nuevoProducto">

                <?php

                $listaProducto = json_decode($venta["productos"], true);

                foreach ($listaProducto as $key => $value) {

                  // if($value["id"] != 1) {

                    $item = "id";
                    $valor = $value["id"];
                    $orden = "id";

                    $respuesta = ControladorProductos::ctrMostrarProductos($item, $valor, $orden);

                    $stockAntiguo = $respuesta["stock"] + $value["cantidad"];
                    
                    echo '<div class="row" style="padding:5px 15px">
              
                          <div class="col-xs-6" style="padding-right:0px">
              
                            <div class="input-group">
                  
                              <span class="input-group-btn"><button type="button" class="btn btn-danger quitarProducto" idProducto="'.$value["id"].'"><i class="fa fa-times"></i></button></span>

                              <input type="text" class="form-control nuevaDescripcionProducto" idProducto="'.$value["id"].'" name="agregarProducto" value="'.$value["descripcion"].'" readonly required>

                            </div>

                          </div>

                          <div class="col-xs-3">
                
                            <input type="number" class="form-control nuevaCantidadProducto" name="nuevaCantidadProducto" min="1" value="'.$value["cantidad"].'" stock="'.$stockAntiguo.'" nuevoStock="'.$value["stock"].'" required>

                          </div>

                          <div class="col-xs-3 ingresoPrecio" style="padding-left:0px">

                            <div class="input-group">

                              <span class="input-group-addon"><i class="ion ion-social-usd"></i></span>
                     
                              <input type="text" class="form-control nuevoPrecioProducto" precioReal="'.$value["precio"].'" name="nuevoPrecioProducto" value="'.$value["total"].'" readonly required>
     
                            </div>
                 
                          </div>

                        </div>';

                  // } else {

                  //      echo '<div class="row" style="padding:5px 15px">
              
                  //         <div class="col-xs-6" style="padding-right:0px">
              
                  //           <div class="input-group">
                  
                  //             <span class="input-group-btn"><button type="button" class="btn btn-danger quitarProducto" idProducto="'.$value["id"].'"><i class="fa fa-times"></i></button></span>

                  //             <input type="text" class="form-control nuevaDescripcionProducto nuevoProductoLibre" idProducto="'.$value["id"].'" name="agregarProducto" value="'.$value["descripcion"].'" required>

                  //           </div>

                  //         </div>

                  //         <div class="col-xs-3">
                
                  //           <input type="number" class="form-control nuevaCantidadProducto" name="nuevaCantidadProducto" min="1" value="'.$value["cantidad"].'" stock="0" nuevoStock="0" readonly required>

                  //         </div>

                  //         <div class="col-xs-3 ingresoPrecio" style="padding-left:0px">

                  //           <div class="input-group">

                  //             <span class="input-group-addon"><i class="ion ion-social-usd"></i></span>
                     
                  //             <input type="number" class="form-control nuevoPrecioProducto nuevoProductoLibre" precioReal="'.$value["precio"].'" name="nuevoPrecioProducto" value="'.$value["total"].'" required>
     
                  //           </div>
                 
                  //         </div>

                  //       </div>';
                  // }

                }


                ?>

                </div>

                <input type="hidden" id="listaProductos" name="listaProductos" value="<?php echo htmlspecialchars($venta['productos']); ?>">

                <!--=====================================
                BOTÓN PARA AGREGAR PRODUCTO
                ======================================-->

                <button type="button" class="btn btn-default hidden-lg btnAgregarProducto">Agregar producto</button>

                <hr>

                <div class="form-group row">
                  
                  <div class="col-xs-5" style="padding-right:0px">
      
      <h4>Medios de pago:</h4>              
  <!--                   <div class="input-group">

                      <select class="form-control" id="nuevoMetodoPago" name="nuevoMetodoPago" required>
                        <option value="">Medio de pago</option>-->
                        <?php 
/*
                        $listaMetodos = array(
                            "Efectivo"=>"Efectivo", 
                            "TD"=>"Tarjeta Débito", 
                            "TC"=>"Tarjeta Crédito",
                            "CC" => "Cuenta Corriente");

                        $metodoElegido = json_decode($venta["metodo_pago"]);

                        $tipo = explode("-", $metodoElegido[0]->tipo);

                        //Pone valor seleccionado en select medios pago
                        foreach ($listaMetodos as $key => $value) {

                          if($tipo[0] == $key) {

                              echo '<option value="'.$key.'" selected>'.$value.'</option>';

                          } else {

                              echo '<option value="'.$key.'">'.$value.'</option>';

                          }

                        }
*/
                        ?>
            
  <!--                    </select>-->

                      <?php
                        $des_o_int = $venta["total"] - $venta["neto"];
                        /*
                        $intereses = 0; // $metodoElegido[0]->interes;
                        $descuentos = 0; // $metodoElegido[0]->descuento;

                          if($tipo[0] == "TD" || $tipo[0] == "TC") {
                            
                              echo '<input type="hidden" id="codigoTransaccion" value="'.$tipo[1].'">';

                          } 
                        */
                        $metodoElegido = json_decode($venta["metodo_pago"]);
                        echo '<ul>';
                        $metPago ="";
                        for ($i=0; $i < count($metodoElegido); $i++) { 
                            $metPago .= '<li>' . $metodoElegido[$i]->tipo . '  $ ' . number_format($metodoElegido[$i]->entrega, 2, ',', '.') .'</li>';
                          }
                        
                        echo $metPago;
                        echo '</ul>';
                        ?>
                        
                        

                    </div>

                  </div>

                  <div class="cajasMetodoPago"></div>

                </div>

                <!--<input type="hidden" id="listaMetodoPago" name="listaMetodoPago" value="">-->

                <!--=====================================
                ENTRADA IMPUESTOS Y TOTAL
                ======================================-->
                <div class="row">
                  
                  <div class="col-md-6"></div>
                  <div class="col-md-6">

                    <input type="hidden" name="nuevoImpuestoVenta" id="nuevoImpuestoVenta">

                    <input type="hidden" name="nuevoPrecioImpuesto" id="nuevoPrecioImpuesto" value="1">

                    <!-- <input type="hidden" name="interesTarjeta" id="interesTarjeta"> -->
                
                    <input type="hidden" name="totalVentaMetodoPago" id="totalVentaMetodoPago" value="<?php echo $venta['total']; ?>"> 
                    
                    <input type="hidden" name="totalVenta" id="totalVenta" value="<?php echo $venta['total']; ?>"> 
                    
                    <table class="table">

                        <tr>

                          <td style="vertical-align:middle; border: none;">Subtotal:</td>

                          <td style="border: none;">

                            <div class="input-group">
                       
                              <span class="input-group-addon"><i class="ion ion-social-usd"></i></span>

                              <input type="number" step="0.01" class="form-control input-sm" name="nuevoPrecioNeto" id="nuevoPrecioNeto" placeholder="0.00" value="<?php echo $venta['neto']; ?>" readonly style="font-size: 18px;">
                    
                            </div>

                          </td>

                        </tr>

                        <tr id="filaInteres" style="display:none;">

                          <td style="vertical-align:middle; border: none;">Interés:</td>

                          <td style=" border: none;">

                              <div class="row">

                                <div class="col-xs-6">
                                  
                                  <div class="input-group">
                               
                                    <span class="input-group-addon"><b>%</b></span>

                                    <input type="number" step="any" min="0" placeholder="0.00" style="text-align:center; font-size: 18px;" class="form-control input-sm nuevoInteres" name="nuevoInteresPorcentaje" id="nuevoInteresPorcentaje" value="">
                            
                                  </div>

                                </div>

                                <div class="col-xs-6">
                                  
                                  <div class="input-group">
                             
                                    <span class="input-group-addon"><i class="ion ion-social-usd"></i></span>

                                    <input type="number" step="any" min="0" placeholder="0.00" style="text-align:center; font-size: 18px;" class="form-control input-sm nuevoInteres" name="nuevoInteresPrecio" id="nuevoInteresPrecio">
                          
                                  </div>

                                </div>
                                
                              </div>

                            </td>

                        </tr>

                        <tr id="filaDescuento" >

                          <td style="vertical-align:middle; border: none;">Descuento:</td>

                          <td style="border: none;">

                            <div class="row">

                              <div class="col-xs-12">
                                
                                
                                <div class="input-group">
                           
                                  <span class="input-group-addon"><i class="ion ion-social-usd"></i></span>

                                  <input type="text"  style="font-size: 18px;" class="form-control input-sm " value="<?php echo $des_o_int; ?> "placeholder="0.00">
                        
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

                            <input type="number" step="0.01" min="0" style=" font-size: 18px; font-weight:bold;" class="form-control input-sm"  name="nuevoTotalVenta" total="<?php echo $venta['total']; ?>" value="<?php echo $venta['total']; ?>" placeholder="0.00" readonly required>

                          </div>

                        </td>

                      </tr>

                    </table>

                  </div>

                </div>

              <!-- ENTRADA OBSERVACIONES -->

              <div class="row">

                <div class="col-md-12">

                  <textarea name="editarObservacionVenta" class="form-control" placeholder="Observaciones"><?php echo $venta['observaciones_vta']; ?></textarea>

                </div>

              </div>
      
          </div>

          <div class="box-footer">

            <?php 

              if($venta["estado"] == 1 || $facturada) {

                $btnEditarVenta = 'disabled';
                $divDisabled = 'style="pointer-events: none;  opacity: 0.4;"';

              } else {

                $btnEditarVenta = '';
                $divDisabled = '';
              }

              // $btnEditarVenta = ($venta["estado"] == 0 || !$facturada) ? '' : 'disabled';
              // $divDisabled = ($venta["estado"] == 0 || !$facturada) ? '' : 'style="pointer-events: none;  opacity: 0.4;"';

            ?>

            <button type="submit" <?php echo $btnEditarVenta; ?>  class="btn btn-primary pull-right">Guardar cambios</button>

          </div>

        </form>

        <?php

          $editarVenta = new ControladorVentas();
          $editarVenta -> ctrEditarVenta();

        ?>

        </div>
            
      </div>

      <!--=====================================
      LA TABLA DE PRODUCTOS
      ======================================-->

<!--       <div class="col-lg-7 hidden-md hidden-sm hidden-xs">
        
        <div class="box box-warning">

          <div class="box-body" <?php echo $divDisabled; ?>>
            
            <table class="table table-bordered table-striped dt-responsive " id="tablaVentas">
              
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

          </div>

        </div>


      </div> -->

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
MODAL DETALLE PRODUCTOS
======================================-->

<div class="modal fade bd-example-modal-lg" id="modalDetProd" tabindex="-1" role="dialog">

  <div class="modal-dialog modal-lg">

    <div class="modal-content">

      <div class="modal-header">

        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>

        <h4 class="modal-title" id="myModalLabel">Detalles de articulo</h4>

      </div>

      <div class="modal-body">

        <div class="row">
          
          <div class="col-md-6">

            <img id="modDetProdImagen" height="300px" style="padding-bottom: 10px;">

          </div>

          <div class="col-md-4">

            <div class="row">

              <div class="form-group">
              
                <div class="input-group">
                
                  <span class="input-group-addon">Codigo Int.</i></span> 

                  <input type="text" class="form-control" id="modDetProdCodigo" readonly>

                </div>

              </div>
              
            </div>

            <div class="row">

              <div class="form-group">
              
                <div class="input-group">
                
                  <span class="input-group-addon">Categoría</i></span> 

                  <input type="text" class="form-control" id="modDetProdCategoria" readonly>

                </div>

              </div>
              
            </div>

          </div>
          
        </div>

        <div class="row">

          <div class="col-md-3">
            
            <div class="form-group">
            
              <div class="input-group">
              
                <span class="input-group-addon">Marca</i></span> 

                <input type="text" class="form-control " id="modDetProdMarca" readonly>

              </div>

            </div>

          </div>

<!--           <div class="col-md-3">
            
            <div class="form-group">
            
              <div class="input-group">
              
                <span class="input-group-addon">Proveedor</i></span> 

                <input type="text" class="form-control " id="modDetProdProveedor" readonly>

              </div>

            </div>

          </div> -->

          <div class="col-md-3">
            
            <div class="form-group">
            
              <div class="input-group">
              
                <span class="input-group-addon">Stk Total</i></span> 

                <input type="text" class="form-control " id="modDetProdTotal" readonly>

              </div>

            </div>

          </div>
  
        </div>

        <div class="row">

          <div class="col-md-12">
            
            <div class="form-group">
            
              <div class="input-group">
              
                <span class="input-group-addon">Info Adicional</i></span> 

                <textarea class="form-control" id="modDetProdInfoAdicional" rows="3" readonly></textarea>
                <!-- <input type="text" class="form-control " id="modDetProdInfoAdicional" readonly> -->

              </div>

            </div>

          </div>
  
        </div>

        <div class="row">

          <div class="col-md-3">
          
            <div class="form-group">
              
                <div class="input-group">
                
                  <span class="input-group-addon">Precio venta</i></span> 

                  <input type="text" class="form-control " id="modDetProdPrecioVenta" readonly>

                </div>

              </div>

          </div>

        </div>

      </div>

      <div class="modal-footer">

        <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>

      </div>

    </div>

  </div>

</div>