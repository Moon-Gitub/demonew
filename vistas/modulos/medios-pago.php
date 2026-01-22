<?php

if($_SESSION["perfil"] == "Vendedor"){
  echo '<script>
  window.location = "inicio";
  </script>';
  return;
}

$item = null;
$valor = null;
$mediosPago = ControladorMediosPago::ctrMostrarMediosPago($item, $valor);

?>
<div class="content-wrapper">

  <section class="content-header">
    
    <h1>
      
      Administrar Medios de Pago
    
    </h1>

    <ol class="breadcrumb">
      
      <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>
      
      <li class="active">Medios de Pago</li>
    
    </ol>

  </section>

  <section class="content">

    <div class="box">

      <div class="box-header with-border">
  
        <button class="btn btn-primary" data-toggle="modal" data-target="#modalAgregarMedioPago">
          
          Agregar medio de pago

        </button>

      </div>

      <div class="box-body">
        
       <table class="table table-bordered table-striped tablas" width="100%">
         
        <thead>
         
         <tr>
           
           <th style="width:10px">#</th>
           <th>Código</th>
           <th>Nombre</th>
           <th>Descripción</th>
           <th>Requiere Código</th>
           <th>Requiere Banco</th>
           <th>Requiere Número</th>
           <th>Requiere Fecha</th>
           <th>Orden</th>
           <th>Estado</th>
           <th>Acciones</th>

         </tr> 

        </thead>

        <tbody>

        <?php

          if($mediosPago) {
            foreach ($mediosPago as $key => $value) {
             
              echo ' <tr>

                      <td class="text-uppercase"><b>'.$value["id"].'</b></td>

                      <td class="text-uppercase"><b>'.$value["codigo"].'</b></td>
                      
                      <td class="text-uppercase">'.$value["nombre"].'</td>

                      <td>'.($value["descripcion"] ?? '').'</td>

                      <td class="text-center">'.($value["requiere_codigo"] ? '<span class="label label-success">Sí</span>' : '<span class="label label-default">No</span>').'</td>

                      <td class="text-center">'.($value["requiere_banco"] ? '<span class="label label-success">Sí</span>' : '<span class="label label-default">No</span>').'</td>

                      <td class="text-center">'.($value["requiere_numero"] ? '<span class="label label-success">Sí</span>' : '<span class="label label-default">No</span>').'</td>

                      <td class="text-center">'.($value["requiere_fecha"] ? '<span class="label label-success">Sí</span>' : '<span class="label label-default">No</span>').'</td>

                      <td class="text-center">'.$value["orden"].'</td>

                      <td class="text-center">'.($value["activo"] ? '<span class="label label-success">Activo</span>' : '<span class="label label-danger">Inactivo</span>').'</td>

                      <td class="text-center">
                        <div class="acciones-tabla">
                          <a class="btn-accion btnEditarMedioPago" title="Editar medio de pago" href="#" idMedioPago="'.$value["id"].'" data-toggle="modal" data-target="#modalEditarMedioPago"><i class="fa fa-pencil"></i></a>
                          <a class="btn-accion btn-danger btnEliminarMedioPago" title="Borrar medio de pago" href="#" idMedioPago="'.$value["id"].'"><i class="fa fa-times"></i></a>
                        </div>
                      </td>

                    </tr>';
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
MODAL AGREGAR MEDIO DE PAGO
======================================-->
<div id="modalAgregarMedioPago" class="modal fade" role="dialog">
  
  <div class="modal-dialog">

    <div class="modal-content">

      <form role="form" method="post">

        <!--=====================================
        CABEZA DEL MODAL
        ======================================-->

        <div class="modal-header" style="background:#3c8dbc; color:white">

          <button type="button" class="close" data-dismiss="modal">&times;</button>

          <h4 class="modal-title">Agregar medio de pago</h4>

        </div>

        <!--=====================================
        CUERPO DEL MODAL
        ======================================-->

        <div class="modal-body">

          <div class="box-body">

            <!-- ENTRADA PARA EL CÓDIGO -->
            
            <div class="form-group">
              
              <div class="input-group">
              
                <span class="input-group-addon"><i class="fa fa-code"></i></span> 

                <input type="text" class="form-control input-lg" name="nuevoCodigo" placeholder="Código (ej: EF, TD)" maxlength="10" required>

              </div>

            </div>

            <!-- ENTRADA PARA EL NOMBRE -->
            
            <div class="form-group">
              
              <div class="input-group">
              
                <span class="input-group-addon"><i class="fa fa-credit-card"></i></span> 

                <input type="text" class="form-control input-lg" name="nuevoNombre" placeholder="Nombre del medio de pago" required>

              </div>

            </div>

            <!-- ENTRADA PARA LA DESCRIPCIÓN -->
            
            <div class="form-group">
              
              <div class="input-group">
              
                <span class="input-group-addon"><i class="fa fa-info"></i></span> 

                <textarea class="form-control" name="nuevaDescripcion" placeholder="Descripción (opcional)" rows="2"></textarea>

              </div>

            </div>

            <!-- CHECKBOXES PARA REQUISITOS -->
            
            <div class="form-group">
              <label>
                <input type="checkbox" name="nuevoRequiereCodigo" value="1"> Requiere código de transacción
              </label>
            </div>

            <div class="form-group">
              <label>
                <input type="checkbox" name="nuevoRequiereBanco" value="1"> Requiere banco
              </label>
            </div>

            <div class="form-group">
              <label>
                <input type="checkbox" name="nuevoRequiereNumero" value="1"> Requiere número de referencia
              </label>
            </div>

            <div class="form-group">
              <label>
                <input type="checkbox" name="nuevoRequiereFecha" value="1"> Requiere fecha de vencimiento
              </label>
            </div>

            <!-- ENTRADA PARA ORDEN -->
            
            <div class="form-group">
              
              <div class="input-group">
              
                <span class="input-group-addon"><i class="fa fa-sort-numeric-asc"></i></span> 

                <input type="number" class="form-control" name="nuevoOrden" placeholder="Orden de visualización" value="0" min="0">

              </div>

            </div>

            <!-- CHECKBOX PARA ACTIVO -->
            
            <div class="form-group">
              <label>
                <input type="checkbox" name="nuevoActivo" value="1" checked> Activo
              </label>
            </div>
 
          </div>

        </div>

        <!--=====================================
        PIE DEL MODAL
        ======================================-->

        <div class="modal-footer">

          <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Salir</button>

          <button type="submit" class="btn btn-primary">Guardar medio de pago</button>

        </div>

        <?php

          $crearMedioPago = new ControladorMediosPago();
          $crearMedioPago -> ctrCrearMedioPago();

        ?>

      </form>

    </div>

  </div>

</div>

<!--=====================================
MODAL EDITAR MEDIO DE PAGO
======================================-->
<div id="modalEditarMedioPago" class="modal fade" role="dialog">
  
  <div class="modal-dialog">

    <div class="modal-content">

      <form role="form" method="post">

        <!--=====================================
        CABEZA DEL MODAL
        ======================================-->

        <div class="modal-header" style="background:#3c8dbc; color:white">

          <button type="button" class="close" data-dismiss="modal">&times;</button>

          <h4 class="modal-title">Editar medio de pago</h4>

        </div>

        <!--=====================================
        CUERPO DEL MODAL
        ======================================-->

        <div class="modal-body">

          <div class="box-body">

            <!-- ENTRADA PARA EL CÓDIGO -->
            
            <div class="form-group">
              
              <div class="input-group">
              
                <span class="input-group-addon"><i class="fa fa-code"></i></span> 

                <input type="text" class="form-control input-lg" name="editarCodigo" id="editarCodigo" maxlength="10" required>

                <input type="hidden" name="idMedioPago" id="idMedioPago" required>

              </div>

            </div>

            <!-- ENTRADA PARA EL NOMBRE -->
            
            <div class="form-group">
              
              <div class="input-group">
              
                <span class="input-group-addon"><i class="fa fa-credit-card"></i></span> 

                <input type="text" class="form-control input-lg" name="editarNombre" id="editarNombre" required>

              </div>

            </div>

            <!-- ENTRADA PARA LA DESCRIPCIÓN -->
            
            <div class="form-group">
              
              <div class="input-group">
              
                <span class="input-group-addon"><i class="fa fa-info"></i></span> 

                <textarea class="form-control" name="editarDescripcion" id="editarDescripcion" rows="2"></textarea>

              </div>

            </div>

            <!-- CHECKBOXES PARA REQUISITOS -->
            
            <div class="form-group">
              <label>
                <input type="checkbox" name="editarRequiereCodigo" id="editarRequiereCodigo" value="1"> Requiere código de transacción
              </label>
            </div>

            <div class="form-group">
              <label>
                <input type="checkbox" name="editarRequiereBanco" id="editarRequiereBanco" value="1"> Requiere banco
              </label>
            </div>

            <div class="form-group">
              <label>
                <input type="checkbox" name="editarRequiereNumero" id="editarRequiereNumero" value="1"> Requiere número de referencia
              </label>
            </div>

            <div class="form-group">
              <label>
                <input type="checkbox" name="editarRequiereFecha" id="editarRequiereFecha" value="1"> Requiere fecha de vencimiento
              </label>
            </div>

            <!-- ENTRADA PARA ORDEN -->
            
            <div class="form-group">
              
              <div class="input-group">
              
                <span class="input-group-addon"><i class="fa fa-sort-numeric-asc"></i></span> 

                <input type="number" class="form-control" name="editarOrden" id="editarOrden" min="0">

              </div>

            </div>

            <!-- CHECKBOX PARA ACTIVO -->
            
            <div class="form-group">
              <label>
                <input type="checkbox" name="editarActivo" id="editarActivo" value="1"> Activo
              </label>
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

      <?php

          $editarMedioPago = new ControladorMediosPago();
          $editarMedioPago -> ctrEditarMedioPago();

        ?> 

      </form>

    </div>

  </div>

</div>

<?php

  $borrarMedioPago = new ControladorMediosPago();
  $borrarMedioPago -> ctrBorrarMedioPago();

?>

<script src="vistas/js/medios_pago.js"></script>
