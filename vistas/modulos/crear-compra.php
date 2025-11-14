<?php

if($_SESSION["perfil"] == "Especial"){
  echo '<script>
    window.location = "inicio";
  </script>';
  return;
}

?>

<div class="content-wrapper">
    <section class="content-header">
        <h1>
          Crear orden compra
        </h1>
        <ol class="breadcrumb">
          <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
          <li class="active">Crear orden compra</li>
        </ol>
    </section>
    <section class="content">
        <div class="row">
            <!--=====================================
            EL FORMULARIO
            =====================================-->
            <div class="col-lg-7 col-xs-12">
                <div class="box box-success">
                    <div class="box-header with-border"></div>
                    <form role="form" method="post" class="formularioCompra">
                        <div class="box-body">
                            <div class="row">
                                <center>  
                                    <div class="col-md-12">
                                        <!--=====================================
                                        ENTRADA DEL VENDEDOR
                                        ======================================-->
                                        <input type="hidden" class="form-control input-sm" id="usuarioPedido" value="<?php echo $_SESSION["nombre"]; ?>" readonly>
                                        <input type="hidden" name="usuarioPedidoOculto" value="<?php echo $_SESSION["nombre"]; ?>">
                                        <?php
                                            date_default_timezone_set('America/Argentina/Mendoza'); 
                                            $fecha = date('Y-m-d');
                                            $fechaForm = date('d/m/Y');
                                            $nuevafecha = strtotime ( '+10 day' , strtotime ( $fecha ) ) ;
                                            $nuevafecha = date ( 'Y-m-d' , $nuevafecha );
                                            $nuevafechaForm = date('d/m/Y', strtotime($nuevafecha));
                                        ?>
                                    </div>
                                </center>
                            </div>
                            <div class="row">   
                              <center>
                                <div class="col-md-4">
                                  <div class="form-group">
                                    <div class="input-group">
                                      <span class="input-group-addon input-sm" style="background-color: #ddd">Fecha</span>
                                      <input type="text" class="form-control input-sm inputFechaCompra" style="text-align:center;" value="<?php echo $fechaForm; ?>" >
                                    </div>
                                  </div>
                                </div>
                                <div class="col-md-4">
                                  <div class="form-group">
                                    <div class="input-group">
                                      <span class="input-group-addon input-sm" style="background-color: #ddd">F. Entrega</span>
                                      <input type="text" class="form-control input-sm inputFechaCompra" style="text-align:center;" id="fechaEntrega" value="<?php echo $fechaForm; ?>">
                                      <input type="hidden" id="fechaEntregaHidden" name="fechaEntrega" value="<?php echo $fecha; ?>">
                                    </div>
                                  </div>
                                </div>
                                <div class="col-md-4">
                                  <div class="form-group">
                                    <div class="input-group">
                                      <span class="input-group-addon input-sm" style="background-color: #ddd">F. Pago</span>
                                      <input type="text" class="form-control input-sm inputFechaCompra" style="text-align:center;" id="fechaPago" value="<?php echo $nuevafechaForm; ?>">
                                      <input type="hidden" id="fechaPagoHidden" name="fechaPago" value="<?php echo $nuevafecha; ?>">
                                    </div>   
                                  </div>
                                </div>
                              </center> 
                            </div>
                            <br>
                            <!--=====================================
                            ENTRADA DEL PROVEEDOR
                            ======================================--> 
                            <div class="form-group">
                              <div class="input-group">
                                <input type="text" class="form-control input-sm" id="autocompletarProveedor" required>
                                <input type="hidden" id="seleccionarProveedor" name="seleccionarProveedor" >
                                <span class="input-group-btn"><button type="button" class="btn btn-default btn-sm" data-toggle="modal" data-target="#modalAgregarProveedor" data-dismiss="modal">Agregar proveedor</button></span>
                              </div>
                            </div>
                            <!--=====================================
                            ENTRADA PARA AGREGAR PRODUCTO
                            ======================================--> 
                           <div class="row">
                              <div class="col-xs-4" ><center>Descripcion Articulo</center></div>
                              <div class="col-xs-2" ><center>Cant.</center></div>
                              <div class="col-xs-2" ><center>P. Compra</center></div>
                              <div class="col-xs-2" ><center>Ganancia</center></div>
                              <div class="col-xs-2" ><center>P. Venta</center></div>
                            </div>
                            <hr>
        
                            <div class="form-group row nuevoProducto" style="width:100%; height:200px; overflow-y:auto; overflow-x: hidden;">
                            </div>
                
                            <input type="hidden" id="listaProductosCompras" name="listaProductosCompras">
        
                            <!--=====================================
                            ENTRADA IMPUESTOS Y TOTAL
                            ======================================-->
                            <hr>
                            <div class="col-xs-10 col-xs-offset-1">
                                <table class="table">
                                    <tr>
                                        <td style="vertical-align:middle; border: none;"><b>ARTICULOS:</b></td>
                                        <td style="border: none;">
                                            <div class="input-group">
                                                <input type="number" step="0.01" style="font-size: 18px; font-weight:bold; text-align:center; " class="form-control input-sm" id="cantidadArticulos" name="cantidadArticulos" readonly required>
                                            </div>
                                        </td>
                                        <td style="vertical-align:middle; border: none;"><b>TOTAL:</b></td>
                                        <td style="border: none;">
                                          <div class="input-group">
                                            <span class="input-group-addon"><i class="ion ion-social-usd"></i></span>
                                            <input type="number" step="0.01" min="0" style="font-size: 18px; font-weight:bold; text-align:center; " class="form-control input-sm" id="nuevoTotalCompra" name="nuevoTotalCompra" total="" placeholder="0,00" readonly required>
                                            <input type="hidden" name="totalCompra" id="totalCompra">
                                          </div>
                                        </td>
                                    </tr>
                                </table>
                            </div>
        
                        </div>
          
                        <div class="box-footer">
                            <center><button type="submit" class="btn btn-primary">Guardar compra</button></center>
                        </div>
                    </div>
                </form>
                <?php
                  $guardarCompra = new ControladorCompras();
                  $guardarCompra -> ctrCrearCompra();
                ?>
            </div>
        
            <!--=====================================
            LA TABLA DE PRODUCTOS
            ======================================-->
            <div class="col-lg-5 col-xs-12">
                <div class="box box-warning">
                    <div class="box-header with-border"></div>
                    <div class="box-body">
                        <table id="tablaCompras" class="table table-bordered table-striped dt-responsive">
                           <thead>
                             <tr>
                              <th>Código</th>
                              <th>Descripcion</th>
                              <th>Precio Anterior</th>
                              <th>Agregar</th>
                            </tr>
                          </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<!--=====================================
MODAL AGREGAR PROVEEDOR
=====================================-->
<div id="modalAgregarProveedor" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <form role="form" method="post">
        <!--=====================================
        CABEZA DEL MODAL
        ======================================-->
        <div class="modal-header" style="background:#3c8dbc; color:white">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Agregar proveedor</h4>
        </div>
        <!--=====================================
        CUERPO DEL MODAL
        ======================================-->
        <div class="modal-body">
            <div class="box-body">
                <input type="hidden" id="nuevoProveedorDesde" name="nuevoProveedorDesde" value="compras">
                <!-- ENTRADA PARA EL NOMBRE -->
                <div class="form-group">
                  <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-user"></i></span> 
                    <input type="text" class="form-control input-lg" name="nuevoProveedor" placeholder="Ingresar nombre proveedor" required>
                  </div>
                </div>
                <!-- ENTRADA PARA EL NOMBRE -->
                <div class="form-group">
                  <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-key"></i></span> 
                    <input type="text" class="form-control input-lg" name="nuevoNombre" placeholder="Ingresar nombre" required>
                  </div>
                </div>
                <!-- ENTRADA PARA LA LOCALIDAD -->
                <div class="form-group">
                  <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-key"></i></span> 
                    <input type="text" class="form-control input-lg" name="nuevaLocalidad" placeholder="Ingresar localidad" required>
                  </div>
                </div>
                <!-- ENTRADA PARA EL TELÉFONO -->
                <div class="form-group">
                  <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-phone"></i></span> 
                    <input type="text" class="form-control input-lg" name="nuevoTelefono" placeholder="Ingresar teléfono" data-inputmask="'mask':'(999) 999-9999'" data-mask required>
                  </div>
                </div>
                <!-- ENTRADA PARA LA DIRECCIÓN -->
                <div class="form-group">
                  <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-map-marker"></i></span> 
                    <input type="text" class="form-control input-lg" name="nuevaDireccion" placeholder="Ingresar dirección" required>
                  </div>
                </div>
                <!-- ENTRADA PARA EL EMAIL -->
                <div class="form-group">
                  <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-envelope"></i></span> 
                    <input type="email" class="form-control input-lg" name="nuevoEmail" placeholder="Ingresar email" required>
                  </div>
                </div>
            </div>
        </div>
        <!--=====================================
        PIE DEL MODAL
        ======================================-->
        <div class="modal-footer">
          <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Salir</button>
          <button type="submit" class="btn btn-primary">Guardar proveedor</button>
        </div>
      </form>
      <?php
        $crearProveedor = new ControladorProveedores();
        $crearProveedor -> ctrCrearProveedorCompra();
      ?>
    </div>
  </div>
</div>