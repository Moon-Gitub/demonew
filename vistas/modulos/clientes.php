<?php 
  $btnPadronAfip = (isset($arrayEmpresa["ws_padron"])) ? '' : 'disabled';
  $objClientes = new ControladorClientes();
?>

<div class="content-wrapper">
  <section class="content-header">
    <h1>
      Administrar clientes
    </h1>
    <ol class="breadcrumb">
      <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>
      <li class="active">Administrar clientes</li>
    </ol>
  </section>
  <section class="content">
    <div class="box">
      <div class="box-header with-border">
        <button class="btn btn-primary" data-toggle="modal" data-target="#modalAgregarCliente">
          Agregar cliente
        </button>
        <a href="clientes-cuenta-saldos" class="btn btn-primary" title="Lista los clientes que se encuentran con saldo en cuenta corriente">
          Saldos Cta. Cte.
        </a>
        <!--<a href="clientes-cuenta-deuda" class="btn btn-primary" title="Lista los clientes que se encuentran con saldo en cuenta corriente con fecha mayor a 30 días">
          Deudas Cta. Cte.
        </a>-->
      </div>
      <div class="box-body">
       <table class="table table-bordered table-striped dt-responsive tablasBotonesCtaCteCliente" width="100%">
        <thead>
         <tr>
           <th>Nombre</th>
           <th>Documento ID</th>
           <th>Email</th>
           <th>Teléfono</th>
           <th>Dirección</th>
           <th></th>
         </tr> 
        </thead>
        <tbody>
        <?php
          
          $clientes = $objClientes -> ctrMostrarClientes(null, null);
          foreach ($clientes as $key => $value) {
            if($value["id"]!=1){
              echo '<tr>
                    <td>'.$value["nombre"].'</td>
                    <td>'.$value["documento"].'</td>
                    <td>'.$value["email"].'</td>
                    <td>'.$value["telefono"].'</td>
                    <td>'.$value["direccion"].'</td>';
                    echo '<td>
                    <div class="btn-group">
                        <a href="index.php?ruta=clientes_cuenta&id_cliente='.$value["id"].'" class="btn btn-primary" ><i class="fa fa-book fa-fw"></i> Cuenta Cte.</a>
                        <a class="btn btn-primary dropdown-toggle" data-toggle="dropdown" href="#">
                          <span class="fa fa-caret-down" title="Toggle dropdown menu"></span>
                        </a>
                        <ul class="dropdown-menu">
                          <li><a class="btnEditarCliente" data-toggle="modal" data-target="#modalEditarCliente" idCliente="'.$value["id"].'"><i class="fa fa-pencil fa-fw"></i> Editar</a></li>';
                       if($_SESSION["perfil"] == "Administrador"){
                          echo '<li><a class="btnEliminarCliente" idCliente="'.$value["id"].'" href="#"><i class="fa fa-times fa-fw"></i> Borrar</a></li>';
                      }
                       echo '</ul>
                      </div>
                    </td>';
                  echo '</tr>';
                }
            }
        ?>
        </tbody>
       </table>
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
        <input type="hidden" name="agregarClienteDesde" value="clientes">
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
                <input type="number" min="0" step="1" class="form-control " name="nuevoDocumentoId" id="nuevoDocumentoId" placeholder="Ingresar documento" required>
                <span class="input-group-btn"><button type="button" title="Consultar en padrón de AFIP" id="btnNuevoDocumentoId" class="btn btn-default" <?php echo $btnPadronAfip; ?> ><i class="fa fa-search"></i></button></span>
              </div>
            </div>            
            <!-- ENTRADA PARA TIPO DOCUMENTO -->
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-list-ul"></i></span> 
                <select class="form-control " name="nuevoTipoDocumento" id="nuevoTipoDocumento" >
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
                <input type="text" class="form-control " name="nuevoCliente" id="nuevoCliente" placeholder="Ingresar nombre o razón social" required>
              </div>
            </div>
            <!-- ENTRADA PARA TIPO DOCUMENTO -->
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-list-ul"></i></span> 
                <select class="form-control " name="nuevoCondicionIva" required>
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
                <input type="email" class="form-control " name="nuevoEmail" id="nuevoEmail" placeholder="Ingresar email">
              </div>
            </div>
            <!-- ENTRADA PARA EL TELÉFONO -->
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-phone"></i></span> 
                <input type="text" class="form-control " name="nuevoTelefono" id="nuevoTelefono" placeholder="Ingresar teléfono" data-inputmask="'mask':'(999) 999-9999'" data-mask>
              </div>
            </div>
            <!-- ENTRADA PARA LA DIRECCIÓN -->
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-map-marker"></i></span> 
                <input type="text" class="form-control " name="nuevaDireccion" id="nuevaDireccion"  placeholder="Ingresar dirección">
              </div>
            </div>
             <!-- ENTRADA PARA LA FECHA DE NACIMIENTO -->
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-calendar"></i></span> 
                <input type="text" class="form-control " name="nuevaFechaNacimiento" placeholder="Ingresar fecha nacimiento" data-inputmask="'alias': 'yyyy/mm/dd'" data-mask>
              </div>
            </div>
            <!-- ENTRADA PARA OBSERVACIONES -->
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-list"></i></span> 
                <textarea class="form-control" name="nuevaObservaciones" id="nuevaObservacionesCliente" placeholder="Ingresar observaciones" rows="3"></textarea>
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
        $objClientes -> ctrCrearCliente();
      ?>
    </div>
  </div>
</div>

<!--=====================================
MODAL EDITAR CLIENTE
======================================-->
<div id="modalEditarCliente" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <form role="form" method="post">
        <!--=====================================
        CABEZA DEL MODAL
        ======================================-->
        <div class="modal-header" style="background:#3c8dbc; color:white">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Editar cliente</h4>
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
                <input autocomplete="off" type="number" min="0" class="form-control " name="editarDocumentoId" id="editarDocumentoId"  required >
              </div>
            </div>
            <!-- ENTRADA PARA TIPO DOCUMENTO -->
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-list-ul"></i></span> 
                <select class="form-control " name="editarTipoDocumento" id="editarTipoDocumento" >
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
                  <option value="99">Otro</option>                  
                </select>
              </div>
            </div>
            <!-- ENTRADA PARA EL NOMBRE -->
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-user"></i></span> 
                <input type="text" autocomplete="off" class="form-control " name="editarCliente" id="editarCliente" required>
                <input type="hidden" id="idCliente" name="idCliente">
              </div>
            </div>
            <!-- ENTRADA PARA CONDICION IVA -->
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-list-ul"></i></span> 
                <select class="form-control " name="editarCondicionIva" id="editarCondicionIva" required>
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
                <input autocomplete="off" type="email" class="form-control " name="editarEmail" id="editarEmail">
              </div>
            </div>
            <!-- ENTRADA PARA EL TELÉFONO -->
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-phone"></i></span> 
                <input autocomplete="off" type="text" class="form-control " name="editarTelefono" id="editarTelefono" data-inputmask="'mask':'(999) 999-9999'" data-mask >
              </div>
            </div>
            <!-- ENTRADA PARA LA DIRECCIÓN -->
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-map-marker"></i></span> 
                <input autocomplete="off" type="text" class="form-control " name="editarDireccion" id="editarDireccion"  >
              </div>
            </div>
            <!-- ENTRADA PARA LA FECHA DE NACIMIENTO -->
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-calendar"></i></span> 
                <input autocomplete="off" type="text" class="form-control " name="editarFechaNacimiento" id="editarFechaNacimiento"  data-inputmask="'alias': 'yyyy/mm/dd'" data-mask>
              </div>
            </div>
            <!-- ENTRADA PARA OBSERVACIONES -->
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-list"></i></span> 
                <textarea class="form-control" name="editarObservaciones" id="editarObservacionesCliente" placeholder="Ingresar observaciones" rows="3"></textarea>
              </div>
            </div>
          </div>
        </div>
        <!--=====================================
        PIE DEL MODAL
        ======================================-->
        <div class="modal-footer">
          <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Salir</button>
          <button type="submit" class="btn btn-primary">Guardar cambios</button>
        </div>
      </form>
      <?php
        $objClientes -> ctrEditarCliente();
      ?>
    </div>
  </div>
</div>
<?php
  $objClientes -> ctrEliminarCliente();
?>