<div class="content-wrapper">
  <section class="content-header">
    <h1>
      Ingreso de compra
    </h1>
    <ol class="breadcrumb">
      <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
      <li class="active">Ingreso compra</li>
    </ol>
  </section>
  <section class="content">
    <div class="row">
      <!--EL FORMULARIO-->
      <div class="col-lg-8 col-xs-12">
        <div class="box box-success">
          <div class="box-header with-border"></div>
          <form role="form" method="post" class="formularioCompraValidar">
           <div class="box-body">
            <div class="row">
              <?php
                $item = "id";
                $valor = $_GET["idCompra"];
                $compra = ControladorCompras::ctrMostrarCompras($item, $valor);
                $item = "id";
                $valor2 = $compra["id_proveedor"];
                $proveedores = ControladorProveedores::ctrMostrarProveedores($item, $valor2);
              ?>
              <!-- id compra -->
              <input type="hidden" name="editarIngreso" value="<?php echo $compra["id"]; ?>" >
              <!-- Usuario que confirma el pedido -->
              <input type="hidden" name="usuarioConfirma" value="<?php echo $_SESSION["nombre"]; ?>">
              <!-- Usuario que habia realizado el pedido -->
              <input type="hidden" name="usuarioPedido" value="<?php echo $compra["usuarioPedido"]; ?>">
              <!-- Proveedor -->
              <div class="col-md-4">
                <div class="form-group">
                  <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-address-book-o"></i></span>
                    <input type="text" class="form-control" id="proveedorVer" value="<?php echo $proveedores["nombre"]; ?>" readonly>
                    <input type="hidden" name="editarProveedor" value="<?php echo $compra["id_proveedor"]; ?>" >
                  </div>
                </div>
              </div>

              <!-- Tipo comprobante -->
              <div class="col-md-4">
                <div class="form-group">
                  <select class="form-control" id="tipoFactura" name="tipoFactura" onchange="cambioDatosFacturaCompra(this.value);" required>
                    <option value="">Seleccionar Tipo De Facturacion</option>
                    <option value="0">X</option>
                    <option value="1">Factura A</option>
                    <option value="6">Factura B</option>
                    <option value="11">Factura C</option>
                  </select>
                </div>
              </div>

              <!-- fecha comprobante -->
              <div class="col-xs-3">
                <div class="form-group">
                  <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                    <input autocomplete="off" type="text" class="form-control inputFechaCompra" id="fechaEmision"  placeholder="Fecha AAAA-MM-DD">
                    <input type="hidden" name="fechaEmision" id="fechaEmisionHidden">
                  </div>
                </div>
              </div>

              <!-- datos remito -->
              <div class="col-md-4" id="datosRemito" style="display:none;">
                <div class="form-group">
                  <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-file-o"></i></span>
                    <input type="text" class="form-control" id="remitoNumero" name="remitoNumero" placeholder="Numero Del Remito" >
                  </div>
                </div>
              </div>

              <!-- datos factura -->
              <div class="col-xs-12" id="datosFactura" name="datosFactura" style="display:none;">
                <div class="col-xs-3">
                  <div class="form-group">
                    <div class="input-group">
                      <span class="input-group-addon"><i class="fa fa-terminal"></i></span>
                      <input type="text" class="form-control" id="puntoVenta" name="puntoVenta" placeholder="Punto de venta" >
                    </div>
                  </div>
                </div>
                <div class="col-xs-3">
                  <div class="form-group">
                    <div class="input-group">
                      <span class="input-group-addon"><i class="fa fa-file-o"></i></span>
                      <input type="text" class="form-control" id="numeroFactura" name="numeroFactura" placeholder="Numero De La Factura" >
                    </div>
                  </div>
                </div>
              </div>

                <!--=====================================
                ENTRADA PARA AGREGAR PRODUCTO
                ======================================--> 
                <div class="col-xs-12">
                  <div class="row">
                    <div class="col-xs-3" ><center>Descripcion Articulo</center></div>
                    <div class="col-xs-2" ><center>Codigo</center></div>
                    <div class="col-xs-1" ><center>Cant</center></div>
                    <div class="col-xs-2" ><center>P. Compra</center></div>
                    <div class="col-xs-2" ><center>Ganancia</center></div>
                    <div class="col-xs-2" ><center>P. Venta</center></div>
                  </div>
                  <hr>

                  <div class="form-group row nuevoProductoValidar">
                    <?php

                    $listaProducto = json_decode($compra["productos"], true);
                    $totalVer = 0;

                    foreach ($listaProducto as $key => $value) {

                      $item = "id";
                      $valor = $value["id"];
                      $orden = "id";

                      $totalVer = $totalVer + $value["precioCompra"] * $value["pedidos"];
                      $respuesta = ControladorProductos::ctrMostrarProductos($item, $valor, $orden);

                      echo '<div class="row" style="padding:5px 15px">

                      <div class="col-xs-3" style="padding-right:0px">

                      <div class="input-group">

                      <span class="input-group-btn"><button type="button" class="btn btn-danger btn-sm quitarProductoCompra" idProducto="'.$value["id"].'"><i class="fa fa-times"></i></button></span>

                      <input type="text" title="'.$value["descripcion"].'" class="form-control input-sm nuevaDescripcionProductoCompraValidar" idProducto="'.$value["id"].'" value="'.$value["descripcion"].'" readonly>

                      </div>

                      </div>

                      <div class="col-xs-2">

                      <input type="text" class="form-control input-sm codigoProducto" readonly style="text-align:center;" value="'.$respuesta["codigo"].'"  >

                      </div>

                      <div class="col-xs-1">
                      <input type="hidden" class="form-control input-sm nuevaCantidadProductoCompraPedidos" style="text-align:center;" value="'.$value["pedidos"].'" readonly > 

                      <input type="input" class="form-control input-sm nuevaCantidadProductoCompraValidar" style="text-align:center;" value="'.$value["pedidos"].'"  required>
                      </div>

                      <div class="col-xs-2">
                      <input type="hidden" class="nuevoPrecioProductoCompraPedido" value="'.$value["precioCompra"].'"  required>

                      <input type="hidden" class="nuevoPrecioProductoCompraValidarBorrar" value="'.$value["precioCompra"] * $value["pedidos"].'"  required>

                      <input type="text" title="Precio De Compra" class="form-control input-sm nuevoPrecioProductoCompraValidar" style="text-align:center;" min="1" value="'.$value["precioCompra"].'" tipoIva="'.$respuesta["tipo_iva"].'"  required>

                      </div>

                      <div class="col-xs-2">
                      <input type="text" title="Precio De Compra" class="form-control input-sm nuevoPrecioGananciaValidar" style="text-align:center;" min="1" value="'.$value["ganancia"].'"  required>
                      </div>

                      <div class="col-xs-2">
                      <input type="text" title="Precio De Venta"  class="form-control input-sm nuevoPrecioVentaProductoCompraValidar" onchange="listarProductosComprasValidarPrecio();" style="text-align:center;" min="1" value="'.$value["precioVenta"].'"  required>
                      </div>
                      </div>';
                    }

                    ?>

                  </div>

                </div>

                <input type="hidden" id="listaProductosValidarCompra" name="listaProductosValidarCompra">

              </div>

            </div>
            <hr>

            <div class="row">
              <div class="col-lg-8">
              </div>
              <div class="col-lg-4">
                <div class="input-group">
                  <span class="input-group-addon">SubTotal</span>
                  <input type="number" step="0.01" min="0" class="form-control input-lg" id="totalCompraOrden" name="totalCompraOrden" placeholder="0,00" readonly style="font-size: 20px; text-align: center;  ">
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-lg-8">
              </div>
              <div class="col-lg-4">
                <div class="input-group">
                  <span class="input-group-addon">Descuento $</span>
                  <input type="number" step="0.01" min="0" class="form-control input-lg" id="descuentoCompraOrden" name="descuentoCompraOrden" placeholder="0,00" style="font-size: 20px; text-align: center;  ">
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-lg-8">
              </div>
              <div class="col-lg-4">
                <div class="input-group">
                  <span class="input-group-addon">Total</span>
                  <input type="number" step="0.01" min="0" class="form-control input-lg" id="totalTotalCompraOrden" name="totalTotalCompraOrden" placeholder="0,00" readonly style="font-size: 20px; text-align: center;  ">
                </div>
              </div>
            </div>

            <br>

            <div class="box-footer">
              <div class="col-xs-12">
                <div class="form-group">
                  <br>
                  <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-list"></i></span>
                    <textarea class="form-control" name="observacionFactura" id="observacionFactura" rows="3" placeholder="Observacion"></textarea>
                  </div>
                </div>
              </div>

              <br>
              <hr>

              <div class="col-xs-12" id="datosImpositivos" name="datosImpositivos" style="display:none;">
                <div class="panel panel-default">

                  <div class="panel-heading">
                    <h3 class="panel-title">Datos Impositivos</h3>
                  </div>

                  <div class="panel-body">
                    <table class="table">
                      <thead>
                        <tr>
                          <th><center>Importe Neto</center></th>
                          <th><center>I.V.A.</center></th>
                          <th><center>Percep. Ingr. Brutos</center></th>
                          <th><center>Percep. I.V.A.</center></th>
                          <th><center>Percep. Ganancias</center></th>
                          <th><center>Imp. Interno</center></th>
                          <th><center>TOTAL</center></th>
                        </tr>
                      </thead>

                      <tbody>
                        <tr>
                          <th>
                            <div class="input-group">
                              <span class="input-group-addon"><i class="ion ion-social-usd"></i></span>
                              <input type="text" class="form-control input-sm nuevoTotalCompra" style="text-align:center;" id="nuevoTotalCompra" name="nuevoTotalCompra" value="" readonly required>
                              <input type="hidden" name="totalCompra" id="totalCompra" value="">
                            </div>
                          </th>             
                          <th>
                            <div class="input-group">
                              <span class="input-group-addon"><i class="ion ion-social-usd"></i></span>
                              <input type="text" class="form-control input-sm totalIVA" style="text-align:center;" id="totalIVA" name="totalIVA" value="0" required>
                            </div>
                          </th> 
                          <th>
                            <div class="input-group">
                              <span class="input-group-addon"><i class="ion ion-social-usd"></i></span>
                              <input type="text" class="form-control input-sm precepcionesIngresosBrutos" style="text-align:center;" id="precepcionesIngresosBrutos" name="precepcionesIngresosBrutos" value="0" required>
                            </div>
                          </th>
                          <th>
                            <div class="input-group">
                              <span class="input-group-addon"><i class="ion ion-social-usd"></i></span>
                              <input type="text" class="form-control input-sm precepcionesIva" style="text-align:center;" id="precepcionesIva" name="precepcionesIva" value="0" required>
                            </div>
                          </th>
                          <th>
                            <div class="input-group">
                              <span class="input-group-addon"><i class="ion ion-social-usd"></i></span>
                              <input type="text" class="form-control input-sm precepcionesGanancias" style="text-align:center;" id="precepcionesGanancias" name="precepcionesGanancias" value="0" required>
                            </div>
                          </th>             
                          <th>
                            <div class="input-group">
                              <span class="input-group-addon"><i class="ion ion-social-usd"></i></span>
                              <input type="text" class="form-control input-sm impuestoInterno" style="text-align:center;" id="impuestoInterno" name="impuestoInterno" value="0" required>
                            </div>
                          </th>
                          <th>
                            <div class="input-group">
                              <span class="input-group-addon"><i class="ion ion-social-usd"></i></span>
                              <input type="text" class="form-control input-sm nuevoTotalFactura" style="text-align:center;" id="nuevoTotalFactura" name="nuevoTotalFactura" value="<?php echo $compra["total"];?>" readonly required>
                              <input type="hidden" name="totalCompraFactura" id="totalCompraFactura" value="">
                            </div>
                          </th>
                        </tr> 
                      </tbody>

                    </table>
                  </div>
                </div>
              </div>

              <center><button type="submit" class="btn btn-primary">Ingreso Factura</button></center>

            </div>

          </form>

          <?php

          $editarCompra = new ControladorCompras();
          $editarCompra -> ctrEditarCompra();
          
          ?>

        </div>

      </div>

      <!--TABLA DE PRODUCTOS-->
      <div class="col-lg-4 col-xs-12">
        <div class="box box-warning">
          <div class="box-header with-border"></div>
          <div class="box-body">
            <table class="table table-bordered table-striped dt-responsive tablaComprasValidar" width="100%">
             <thead>
               <tr>
                <th>Código</th>
                <th>Descripcion</th>
                <th>$ compra</th>
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