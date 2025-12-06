<?php 

  $btnPadronAfip = (isset($arrayEmpresa["ws_padron"])) ? '' : 'disabled';

?>

<!--begin::App Content Header-->
  <div class="app-content-header">
    <div class="container-fluid">
      <div class="row">
        <div class="col-sm-6">
          <h3 class="mb-0">Administrar proveedores</h3>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-end mb-0">
<li class="breadcrumb-item"><a href="inicio"><i class="bi bi-speedometer2"></i> Inicio</a></li>
      <li class="breadcrumb-item active" aria-current="page">Administrar proveedores</li>
          </ol>
        </div>
      </div>
    </div>
  </div>
  <!--end::App Content Header-->
  <!--begin::App Content-->
  <div class="app-content">
    <div class="container-fluid">
    <div class="card">
      <div class="card-header with-border">
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAgregarProveedor">
          Agregar proveedor
        </button>
        <a href="proveedores-cuenta-saldos" class="btn btn-primary" title="Lista los proveedores que se encuentran con saldo en cuenta corriente">
          Saldos Cta. Cte.
        </a>
      </div>
      <div class="card-body">
       <table class="table table-bordered table-striped dt-responsive tablas" width="100%">
        <thead>
         <tr>
           <th>Nombre</th>
           <th>Id</th>  
           <th>CUIT</th>
           <th>Email</th>
           <th>Teléfono</th>
           <th>Dirección</th>
           <th style="width:200px"></th>
         </tr> 
        </thead>
        <tbody>

        <?php

          $item = null;
          $valor = null;
          $proveedores = ControladorProveedores::ctrMostrarProveedores($item, $valor);
          foreach ($proveedores as $key => $value) {
              echo '<tr>
                    <td>'.$value["nombre"].'</td>
                    <td>'.$value["id"].'</td>
                    <td>'.$value["cuit"].'</td>
                    <td>'.$value["telefono"].'</td>             
                    <td>'.$value["direccion"].'</td>
                    <td>'.$value["email"].'</td>
                    <td>
                    <center>
                      <div class="btn-group">
                        <a href="index.php?ruta=proveedores_cuenta&id_proveedor='.$value["id"].'" title="Cuenta corriente" class="btn btn-primary" ><i class="fa fa-book fa-fw"></i> Cuenta Cte.</a>
                        <a class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown" href="#">
                          <span class="fa fa-caret-down" title="Menu desplegable"></span>
                        </a>
                        <ul class="dropdown-menu">
                          <li><a class="btnEditarProveedor" data-bs-toggle="modal" data-bs-target="#modalEditarProveedor" idProveedor="'.$value["id"].'"><i class="fa fa-pencil fa-fw"></i> Editar</a></li>
                          <li><a class="btnModificarPrecioProveedor" data-bs-toggle="modal" data-bs-target="#modalModificarPrecioProveedor" idProveedor="'.$value["id"].'" nombreProveedor="'.$value["nombre"].'"><i class="fa fa-sort fa-fw"></i> +/- $ Productos</a></li>';
                       if($_SESSION["perfil"] == "Administrador"){
                          echo '<li><a class="btnEliminarProveedor" idProveedor="'.$value["id"].'" href="#"><i class="bi bi-x fa-fw"></i> Borrar</a></li>';
                      }
                       echo '</ul>
                      </div>
                      </center>
                    </td>
                  </tr>';
            }
        ?>
        </tbody>
       </table>
      </div>
    </div>
  </section>
</div>

<!--=====================================
MODAL AGREGAR PROVEEDOR
======================================-->
<div id="modalAgregarProveedor" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <form role="form" method="post">
        <!--CABEZA DEL MODAL-->
        <div class="modal-header" style="background:#3c8dbc; color:white">
          <button type="button" class="close" data-bs-dismiss="modal">&times;</button>
          <h4 class="modal-title">Agregar proveedor</h4>
        </div>
        <!--CUERPO DEL MODAL-->
        <div class="modal-body">
          <div class="card-body">
            <!-- ENTRADA PARA EL DOCUMENTO ID -->
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-text"><i class="fa fa-key"></i></span> 
                <input autocomplete="off" type="number" min="0" step="1" class="form-control " name="nuevoCuit" id="nuevoDocumentoId" placeholder="Ingresar número de identificacion (documento, CUIT, CUIL, etc.)" required>
                <span class="input-group-btn"><button type="button" title="Consultar en padrón de AFIP" id="btnNuevoDocumentoId" class="btn btn-default" <?php echo $btnPadronAfip; ?> ><i class="fa fa-search"></i></button></span>
              </div>
            </div>            
            <!-- ENTRADA PARA TIPO DOCUMENTO -->
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-text"><i class="fa fa-list-ul"></i></span> 
                <select class="form-control" name="nuevoTipoDocumento" id="nuevoTipoDocumento" required>
                  <option value="0">Seleccionar tipo documento</option>
                  <option value="80">CUIT</option>
                  <option value="86">CUIL</option>
                  <option value="96">DNI</option>
                  <option value="99">Otro</option>
                </select>
              </div>
            </div>
            <!-- ENTRADA PARA EL NOMBRE -->
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-text"><i class="bi bi-person"></i></span> 
                <input type="text" class="form-control" name="nuevoProveedor" id="nuevoCliente" placeholder="Ingresar nombre proveedor" required>
              </div>
            </div>
            <!-- ENTRADA PARA LA DIRECCIÓN -->
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-text"><i class="fa fa-map-marker"></i></span> 
                <input type="text" class="form-control" name="nuevaDireccion" id="nuevaDireccion" placeholder="Ingresar dirección">
              </div>
            </div>
            <!-- ENTRADA PARA LA LOCALIDAD -->
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-text"><i class="fa fa-map-marker"></i></span> 
                <input type="text" class="form-control" name="nuevaLocalidad" placeholder="Ingresar localidad">
              </div>
            </div>
            <!-- ENTRADA PARA EL TELÉFONO -->
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-text"><i class="fa fa-phone"></i></span> 
                <input type="text" class="form-control" name="nuevoTelefono" placeholder="Ingresar teléfono" data-inputmask="'mask':'(999) 999-9999'" data-mask>
              </div>
            </div>
            <!-- ENTRADA PARA EL EMAIL -->
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-text"><i class="fa fa-envelope"></i></span> 
                <input type="email" class="form-control" name="nuevoEmail" placeholder="Ingresar email">
              </div>
            </div>
            <!-- ENTRADA PARA EL INICIO DE ACTIVIDADES -->
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-text"><i class="fa fa-key"></i></span> 
                <input type="text" class="form-control" name="nuevoInicioActividades" placeholder="Ingresar inicio actividades">
              </div>
            </div>
            <!-- ENTRADA PARA INGRESOS BRUTOS -->
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-text"><i class="fa fa-key"></i></span> 
                <input type="text" class="form-control" name="nuevoIngresosBrutos" placeholder="Ingresar num. ingresos brutos">
              </div>
            </div>
            <!-- ENTRADA PARA OBSERVACIONES -->
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-text"><i class="fa fa-list"></i></span> 
                <textarea class="form-control" name="nuevaObservaciones" id="nuevaObservacionesProveedor" rows="3" placeholder="Ingresar observaciones" ></textarea>
              </div>
            </div>
        </div>
       </div>
        <!--PIE DEL MODAL-->
        <div class="modal-footer">
          <button type="button" class="btn btn-default float-start" data-bs-dismiss="modal">Salir</button>
          <button type="submit" class="btn btn-primary">Guardar</button>
        </div>
      </form>
      <?php
        $crearProveedor = new ControladorProveedores();
        $crearProveedor -> ctrCrearProveedor();
      ?>
    </div>
  </div>
</div>

<!--=====================================
MODAL EDITAR PROVEEDOR
======================================-->
<div id="modalEditarProveedor" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <form role="form" method="post">
        <!--CABEZA DEL MODAL-->
        <div class="modal-header" style="background:#3c8dbc; color:white">
          <button type="button" class="close" data-bs-dismiss="modal">&times;</button>
          <h4 class="modal-title">Editar proveedor</h4>
        </div>
        <!--CUERPO DEL MODAL-->
        <div class="modal-body">
          <div class="card-body">
            <!-- ENTRADA PARA EL CUIT -->
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-text"><i class="fa fa-key"></i></span> 
                <input type="text" class="form-control" name="editarCuit" id="editarCuit" placeholder="Ingresar número de identificacion (documento, CUIT, CUIL, etc.)" required>
              </div>
            </div>
            <!-- ENTRADA PARA TIPO DOCUMENTO -->
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-text"><i class="fa fa-list-ul"></i></span> 
                <select class="form-control" name="editarTipoDocumento" id="editarTipoDocumento" required>
                  <option value="0">Seleccionar tipo documento</option>
                  <option value="80">CUIT</option>
                  <option value="86">CUIL</option>
                  <option value="96">DNI</option>
                  <option value="99">Otro</option>
                </select>
              </div>
            </div>
            <!-- ENTRADA PARA EL NOMBRE -->
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-text"><i class="bi bi-person"></i></span> 
                <input type="text" class="form-control" name="editarNombre" id="editarNombre" placeholder="Ingresar nombre proveedor" required>
                <input type="hidden" id="idProveedor" name="idProveedor">
              </div>
            </div>
            <!-- ENTRADA PARA LA DIRECCIÓN -->
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-text"><i class="fa fa-map-marker"></i></span> 
                <input type="text" class="form-control" name="editarDireccion" id="editarDireccion" placeholder="Ingresar dirección">
              </div>
            </div>
            <!-- ENTRADA PARA EL LOCALIDAD -->
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-text"><i class="fa fa-map-marker"></i></span> 
                <input type="text" class="form-control" name="editarLocalidad" id="editarLocalidad" placeholder="Ingresar localidad">
              </div>
            </div>
            <!-- ENTRADA PARA LA TELEFONO -->
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-text"><i class="fa fa-phone"></i></span> 
                <input type="text" class="form-control" name="editarTelefono" id="editarTelefono" placeholder="Ingresar teléfono">
              </div>
            </div>
            <!-- ENTRADA PARA EL EMAIL -->
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-text"><i class="fa fa-envelope"></i></span> 
                <input type="email" class="form-control" name="editarEmail" id="editarEmail" placeholder="Ingresar email">
              </div>
            </div>
            <!-- ENTRADA PARA EL INICIO ACTIVIDADES -->
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-text"><i class="fa fa-key"></i></span> 
                <input type="text" class="form-control" name="editarInicioActividades" id="editarInicioActividades" placeholder="Ingresar inicio actividades">
              </div>
            </div>
            <!-- ENTRADA PARA INGRESOS BRUTOS -->
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-text"><i class="fa fa-key"></i></span> 
                <input type="text" class="form-control" name="editarIngresosBrutos" id="editarIngresosBrutos" placeholder="Ingresar num. ingresos brutos">
              </div>
            </div>
            <!-- ENTRADA PARA OBSERVACIONES -->
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-text"><i class="fa fa-list"></i></span> 
                <!-- <input type="email" class="form-control" name="editarEmail" id="editarEmail"> -->
                <textarea class="form-control" name="editarObservaciones" id="editarObservacionesProveedor" placeholder="Ingresar observaciones" rows="3"></textarea>
              </div>
            </div>
          </div>
        </div>
        <!--PIE DEL MODAL-->
        <div class="modal-footer">
          <button type="button" class="btn btn-default float-start" data-bs-dismiss="modal">Salir</button>
          <button type="submit" class="btn btn-primary">Guardar</button>
        </div>
      </form>
      <?php
        $editarProveedor = new ControladorProveedores();
        $editarProveedor -> ctrEditarProveedor();
      ?>
    </div>
  </div>
</div>

<!--=====================================
MODAL MODIFICAR PRECIO
======================================-->
<div id="modalModificarPrecioProveedor" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <form role="form" method="post">
        <!--CABEZA DEL MODAL-->
        <div class="modal-header" style="background:#3c8dbc; color:white">
          <button type="button" class="close" data-bs-dismiss="modal">&times;</button>
          <h4 class="modal-title">Modificar precios</h4>
        </div>
        <!--CUERPO DEL MODAL-->
        <div class="modal-body">
          <div class="card-body">
            <input type="hidden" id="idProveedorNuevoPrecio" name="idProveedorNuevoPrecio">
            <h4 id="nombreProveedor"></h4>
            <!-- ENTRADA PARA EL PORCENTAJE -->
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-text"><i class="fa fa-percent"></i></span> 
                <input type="number" class="form-control" name="nuevoModificacionPrecio" placeholder="Ingresar porcentaje" required>
              </div>
            </div>
          </div>
        </div>
        <!--PIE DEL MODAL-->
        <div class="modal-footer">
          <button type="button" class="btn btn-default float-start" data-bs-dismiss="modal">Salir</button>
          <button type="submit" class="btn btn-primary">Actualizar precios</button>
        </div>
        <?php
          $nuevoPrecioProveedor = new ControladorProductos();
          $nuevoPrecioProveedor -> ctrModificarPrecioProveedor();
        ?>
      </form>
    </div>
  </div>
</div>
    </div>
    <!--end::Container-->
  </div>
  <!--end::App Content-->
<?php
  $eliminarProveedor = new ControladorProveedores();
  $eliminarProveedor -> ctrEliminarProveedor();
?>