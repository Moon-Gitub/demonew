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
                    <div class="box-header with-border">
                        <div class="form-group">
                            <label>
                                <input type="checkbox" id="modoFacturaDirecta" name="modoFacturaDirecta" onchange="toggleModoFactura()">
                                <strong> Cargar factura directa (sin orden previa)</strong>
                            </label>
                            <small class="help-block">Si está marcado, se cargará la factura directamente con datos impositivos. Si no, se creará una orden de compra.</small>
                        </div>
                    </div>
                    <form role="form" method="post" class="formularioCompra" id="formularioCompra">
                        <div class="box-body">
                            <div class="row">
                                <center>  
                                    <div class="col-md-12">
                                        <!--=====================================
                                        ENTRADA DEL VENDEDOR
                                        ======================================-->
                                        <input type="hidden" class="form-control input-sm" id="usuarioPedido" value="<?php echo $_SESSION["nombre"]; ?>" readonly>
                                        <input type="hidden" name="usuarioPedidoOculto" value="<?php echo $_SESSION["nombre"]; ?>">
                                        <input type="hidden" name="usuarioConfirma" value="<?php echo $_SESSION["nombre"]; ?>">
                                        <!-- Token CSRF -->
                                        <input type="hidden" name="csrf_token" value="<?php echo isset($_SESSION['csrf_token']) ? $_SESSION['csrf_token'] : ''; ?>">
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
                            
                            <!--=====================================
                            CAMPOS PARA FACTURA DIRECTA (OCULTOS POR DEFECTO)
                            ======================================-->
                            <div id="camposFacturaDirecta" style="display:none;">
                                <hr>
                                <h4><strong>Datos de Factura</strong></h4>
                                
                                <!-- Tipo comprobante -->
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Tipo de Comprobante</label>
                                            <select class="form-control" id="tipoFacturaDirecta" name="tipoFactura" onchange="cambioDatosFacturaCompra(this.value);" required>
                                                <option value="">Seleccionar Tipo</option>
                                                <option value="0">X</option>
                                                <option value="1">Factura A</option>
                                                <option value="6">Factura B</option>
                                                <option value="11">Factura C</option>
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <!-- Fecha emisión -->
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Fecha Emisión</label>
                                            <div class="input-group">
                                                <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                                <input autocomplete="off" type="text" class="form-control inputFechaCompra" id="fechaEmisionDirecta" placeholder="Fecha AAAA-MM-DD">
                                                <input type="hidden" name="fechaEmision" id="fechaEmisionHiddenDirecta">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Datos remito (oculto por defecto) -->
                                <div class="row" id="datosRemitoDirecta" style="display:none;">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label>Número de Remito</label>
                                            <div class="input-group">
                                                <span class="input-group-addon"><i class="fa fa-file-o"></i></span>
                                                <input type="text" class="form-control" id="remitoNumeroDirecta" name="remitoNumero" placeholder="Número del Remito">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Datos factura (oculto por defecto) -->
                                <div class="row" id="datosFacturaDirecta" style="display:none;">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Punto de Venta</label>
                                            <div class="input-group">
                                                <span class="input-group-addon"><i class="fa fa-terminal"></i></span>
                                                <input type="text" class="form-control" id="puntoVentaDirecta" name="puntoVenta" placeholder="Punto de venta">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Número de Factura</label>
                                            <div class="input-group">
                                                <span class="input-group-addon"><i class="fa fa-file-o"></i></span>
                                                <input type="text" class="form-control" id="numeroFacturaDirecta" name="numeroFactura" placeholder="Número de la Factura">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <hr>
                                
                                <!-- Totales e impuestos -->
                                <div class="row">
                                    <div class="col-lg-8">
                                        <div class="alert alert-info" id="alertaMontoManual" style="display:none; margin-bottom: 10px;">
                                            <i class="fa fa-info-circle"></i> <strong>Factura de servicio:</strong> Ingrese el monto total de la factura en el campo "SubTotal" ya que no hay productos agregados.
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="input-group">
                                            <span class="input-group-addon">SubTotal</span>
                                            <input type="number" step="0.01" min="0" class="form-control input-lg" id="totalCompraOrdenDirecta" name="totalCompraOrden" placeholder="0,00" style="font-size: 20px; text-align: center;">
                                        </div>
                                        <small class="help-block" id="ayudaSubTotal" style="display:none;">Ingrese el monto neto de la factura</small>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-lg-8"></div>
                                    <div class="col-lg-4">
                                        <div class="input-group">
                                            <span class="input-group-addon">Descuento $</span>
                                            <input type="number" step="0.01" min="0" class="form-control input-lg" id="descuentoCompraOrdenDirecta" name="descuentoCompraOrden" placeholder="0,00" style="font-size: 20px; text-align: center;">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-lg-8"></div>
                                    <div class="col-lg-4">
                                        <div class="input-group">
                                            <span class="input-group-addon">Total</span>
                                            <input type="number" step="0.01" min="0" class="form-control input-lg" id="totalTotalCompraOrdenDirecta" name="totalTotalCompraOrden" placeholder="0,00" readonly style="font-size: 20px; text-align: center;">
                                        </div>
                                    </div>
                                </div>
                                
                                <br>
                                
                                <!-- Datos impositivos (oculto hasta seleccionar tipo) -->
                                <div class="col-xs-12" id="datosImpositivosDirecta" name="datosImpositivos" style="display:none;">
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
                                                                <input type="text" class="form-control input-sm nuevoTotalCompraDirecta" style="text-align:center;" id="nuevoTotalCompraDirecta" name="nuevoTotalCompra" value="" readonly required>
                                                                <input type="hidden" name="totalCompraDirecta" id="totalCompraDirecta" value="">
                                                            </div>
                                                        </th>             
                                                        <th>
                                                            <div class="input-group">
                                                                <span class="input-group-addon"><i class="ion ion-social-usd"></i></span>
                                                                <input type="text" class="form-control input-sm totalIVADirecta" style="text-align:center;" id="totalIVADirecta" name="totalIVA" value="0" required>
                                                            </div>
                                                        </th> 
                                                        <th>
                                                            <div class="input-group">
                                                                <span class="input-group-addon"><i class="ion ion-social-usd"></i></span>
                                                                <input type="text" class="form-control input-sm precepcionesIngresosBrutosDirecta" style="text-align:center;" id="precepcionesIngresosBrutosDirecta" name="precepcionesIngresosBrutos" value="0" required>
                                                            </div>
                                                        </th>
                                                        <th>
                                                            <div class="input-group">
                                                                <span class="input-group-addon"><i class="ion ion-social-usd"></i></span>
                                                                <input type="text" class="form-control input-sm precepcionesIvaDirecta" style="text-align:center;" id="precepcionesIvaDirecta" name="precepcionesIva" value="0" required>
                                                            </div>
                                                        </th>
                                                        <th>
                                                            <div class="input-group">
                                                                <span class="input-group-addon"><i class="ion ion-social-usd"></i></span>
                                                                <input type="text" class="form-control input-sm precepcionesGananciasDirecta" style="text-align:center;" id="precepcionesGananciasDirecta" name="precepcionesGanancias" value="0" required>
                                                            </div>
                                                        </th>             
                                                        <th>
                                                            <div class="input-group">
                                                                <span class="input-group-addon"><i class="ion ion-social-usd"></i></span>
                                                                <input type="text" class="form-control input-sm impuestoInternoDirecta" style="text-align:center;" id="impuestoInternoDirecta" name="impuestoInterno" value="0" required>
                                                            </div>
                                                        </th>
                                                        <th>
                                                            <div class="input-group">
                                                                <span class="input-group-addon"><i class="ion ion-social-usd"></i></span>
                                                                <input type="text" class="form-control input-sm nuevoTotalFacturaDirecta" style="text-align:center;" id="nuevoTotalFacturaDirecta" name="nuevoTotalFactura" value="" readonly required>
                                                                <input type="hidden" name="totalCompraFacturaDirecta" id="totalCompraFacturaDirecta" value="">
                                                            </div>
                                                        </th>
                                                    </tr> 
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Observaciones -->
                                <div class="col-xs-12">
                                    <div class="form-group">
                                        <label>Observaciones</label>
                                        <textarea class="form-control" name="observacionFactura" id="observacionFacturaDirecta" rows="3" placeholder="Observaciones"></textarea>
                                    </div>
                                </div>
                            </div>
        
                        </div>
          
                        <div class="box-footer">
                            <center>
                                <button type="submit" class="btn btn-primary" id="btnGuardarCompra">Guardar compra</button>
                            </center>
                        </div>
                    </div>
                </form>
                <?php
                  $guardarCompra = new ControladorCompras();
                  $guardarCompra -> ctrCrearCompra();
                  $guardarCompra -> ctrCrearFacturaDirecta();
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

<script>
// Función para alternar entre modo orden y factura directa
function toggleModoFactura() {
    var modoFactura = document.getElementById('modoFacturaDirecta').checked;
    var camposFactura = document.getElementById('camposFacturaDirecta');
    var btnGuardar = document.getElementById('btnGuardarCompra');
    var formulario = document.getElementById('formularioCompra');
    
    if (modoFactura) {
        camposFactura.style.display = 'block';
        btnGuardar.textContent = 'Cargar Factura Directa';
        // Cambiar el action del formulario para que use el método de factura directa
        // Esto se manejará en el JavaScript que procesa el submit
    } else {
        camposFactura.style.display = 'none';
        btnGuardar.textContent = 'Guardar compra';
    }
}

// Función para mostrar/ocultar campos según tipo de factura (reutilizar de editar-ingreso)
function cambioDatosFacturaCompra(valor) {
    var datosRemito = document.getElementById('datosRemitoDirecta');
    var datosFactura = document.getElementById('datosFacturaDirecta');
    var datosImpositivos = document.getElementById('datosImpositivosDirecta');
    
    if (valor == "0") {
        // Remito
        datosRemito.style.display = 'block';
        datosFactura.style.display = 'none';
        datosImpositivos.style.display = 'none';
    } else if (valor == "1" || valor == "6" || valor == "11") {
        // Factura A, B o C
        datosRemito.style.display = 'none';
        datosFactura.style.display = 'block';
        datosImpositivos.style.display = 'block';
    } else {
        datosRemito.style.display = 'none';
        datosFactura.style.display = 'none';
        datosImpositivos.style.display = 'none';
    }
}

// Interceptar el submit del formulario para determinar qué método usar
document.addEventListener('DOMContentLoaded', function() {
    var formulario = document.getElementById('formularioCompra');
    if (formulario) {
        formulario.addEventListener('submit', function(e) {
            var modoFactura = document.getElementById('modoFacturaDirecta').checked;
            if (modoFactura) {
                // Crear un input hidden para indicar que es factura directa
                var inputHidden = document.createElement('input');
                inputHidden.type = 'hidden';
                inputHidden.name = 'crearFacturaDirecta';
                inputHidden.value = '1';
                formulario.appendChild(inputHidden);
            }
        });
    }
});
</script>

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