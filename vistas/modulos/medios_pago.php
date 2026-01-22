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
      <li><a href="empresa"><i class="fa fa-building-o"></i> Empresa</a></li>
      <li class="active">Medios de Pago</li>
    </ol>

  </section>

  <section class="content">

    <div class="box">

      <div class="box-header with-border">
        <button class="btn btn-primary" data-toggle="modal" data-target="#modalAgregarMedioPago">
          <i class="fa fa-plus"></i> Agregar Medio de Pago
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
              <th>Requisitos</th>
              <th>Orden</th>
              <th>Estado</th>
              <th>Acciones</th>
            </tr> 
          </thead>
          <tbody>
            <?php
              foreach ($mediosPago as $key => $value) {
                $requisitos = [];
                if($value["requiere_codigo"]) $requisitos[] = "Código";
                if($value["requiere_banco"]) $requisitos[] = "Banco";
                if($value["requiere_numero"]) $requisitos[] = "Número";
                if($value["requiere_fecha"]) $requisitos[] = "Fecha";
                $requisitosStr = !empty($requisitos) ? implode(", ", $requisitos) : "Ninguno";
                
                $estadoBadge = $value["activo"] ? '<span class="label label-success">Activo</span>' : '<span class="label label-danger">Inactivo</span>';
                
                echo '<tr>
                        <td><b>'.$value["id"].'</b></td>
                        <td class="text-uppercase"><b>'.$value["codigo"].'</b></td>
                        <td>'.$value["nombre"].'</td>
                        <td>'.($value["descripcion"] ?: '-').'</td>
                        <td><small>'.$requisitosStr.'</small></td>
                        <td>'.$value["orden"].'</td>
                        <td>'.$estadoBadge.'</td>
                        <td class="text-center">
                          <div class="acciones-tabla">
                            <a class="btn-accion btnEditarMedioPago" title="Editar" href="#" idMedioPago="'.$value["id"].'" data-toggle="modal" data-target="#modalEditarMedioPago"><i class="fa fa-pencil"></i></a>
                            <a class="btn-accion btn-danger btnEliminarMedioPago" title="Eliminar" href="#" idMedioPago="'.$value["id"].'"><i class="fa fa-times"></i></a>
                          </div>
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
MODAL AGREGAR MEDIO DE PAGO
======================================-->
<div id="modalAgregarMedioPago" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <form role="form" method="post">
        <div class="modal-header" style="background:#3c8dbc; color:white">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Agregar Medio de Pago</h4>
        </div>
        <div class="modal-body">
          <div class="box-body">
            <!-- CÓDIGO -->
            <div class="form-group">
              <label>Código (máx. 10 caracteres)</label>
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-code"></i></span>
                <input type="text" class="form-control input-lg" name="nuevoCodigo" placeholder="Ej: EF, TD, TC" maxlength="10" required>
              </div>
              <small class="text-muted">Código único para identificar el medio de pago</small>
            </div>

            <!-- NOMBRE -->
            <div class="form-group">
              <label>Nombre</label>
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-tag"></i></span>
                <input type="text" class="form-control input-lg" name="nuevoNombre" placeholder="Ej: Efectivo, Tarjeta Débito" required>
              </div>
            </div>

            <!-- DESCRIPCIÓN -->
            <div class="form-group">
              <label>Descripción (opcional)</label>
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-file-text"></i></span>
                <textarea class="form-control" name="nuevaDescripcion" rows="2" placeholder="Descripción del medio de pago"></textarea>
              </div>
            </div>

            <!-- REQUISITOS -->
            <div class="form-group">
              <label>Requisitos adicionales</label>
              <div class="checkbox">
                <label>
                  <input type="checkbox" name="nuevoRequiereCodigo" value="1">
                  Requiere código de transacción
                </label>
              </div>
              <div class="checkbox">
                <label>
                  <input type="checkbox" name="nuevoRequiereBanco" value="1">
                  Requiere banco
                </label>
              </div>
              <div class="checkbox">
                <label>
                  <input type="checkbox" name="nuevoRequiereNumero" value="1">
                  Requiere número de referencia
                </label>
              </div>
              <div class="checkbox">
                <label>
                  <input type="checkbox" name="nuevoRequiereFecha" value="1">
                  Requiere fecha de vencimiento
                </label>
              </div>
            </div>

            <!-- ORDEN -->
            <div class="form-group">
              <label>Orden de visualización</label>
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-sort-numeric-asc"></i></span>
                <input type="number" class="form-control" name="nuevoOrden" value="0" min="0">
              </div>
              <small class="text-muted">Menor número = aparece primero</small>
            </div>

            <!-- ACTIVO -->
            <div class="form-group">
              <div class="checkbox">
                <label>
                  <input type="checkbox" name="nuevoActivo" value="1" checked>
                  <strong>Activo</strong> (aparecerá en los dropdowns)
                </label>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-primary">Guardar Medio de Pago</button>
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
        <div class="modal-header" style="background:#3c8dbc; color:white">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Editar Medio de Pago</h4>
        </div>
        <div class="modal-body">
          <div class="box-body">
            <input type="hidden" name="idMedioPago" id="idMedioPago">

            <!-- CÓDIGO -->
            <div class="form-group">
              <label>Código (máx. 10 caracteres)</label>
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-code"></i></span>
                <input type="text" class="form-control input-lg" name="editarCodigo" id="editarCodigo" maxlength="10" required>
              </div>
            </div>

            <!-- NOMBRE -->
            <div class="form-group">
              <label>Nombre</label>
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-tag"></i></span>
                <input type="text" class="form-control input-lg" name="editarNombre" id="editarNombre" required>
              </div>
            </div>

            <!-- DESCRIPCIÓN -->
            <div class="form-group">
              <label>Descripción (opcional)</label>
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-file-text"></i></span>
                <textarea class="form-control" name="editarDescripcion" id="editarDescripcion" rows="2"></textarea>
              </div>
            </div>

            <!-- REQUISITOS -->
            <div class="form-group">
              <label>Requisitos adicionales</label>
              <div class="checkbox">
                <label>
                  <input type="checkbox" name="editarRequiereCodigo" id="editarRequiereCodigo" value="1">
                  Requiere código de transacción
                </label>
              </div>
              <div class="checkbox">
                <label>
                  <input type="checkbox" name="editarRequiereBanco" id="editarRequiereBanco" value="1">
                  Requiere banco
                </label>
              </div>
              <div class="checkbox">
                <label>
                  <input type="checkbox" name="editarRequiereNumero" id="editarRequiereNumero" value="1">
                  Requiere número de referencia
                </label>
              </div>
              <div class="checkbox">
                <label>
                  <input type="checkbox" name="editarRequiereFecha" id="editarRequiereFecha" value="1">
                  Requiere fecha de vencimiento
                </label>
              </div>
            </div>

            <!-- ORDEN -->
            <div class="form-group">
              <label>Orden de visualización</label>
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-sort-numeric-asc"></i></span>
                <input type="number" class="form-control" name="editarOrden" id="editarOrden" min="0">
              </div>
            </div>

            <!-- ACTIVO -->
            <div class="form-group">
              <div class="checkbox">
                <label>
                  <input type="checkbox" name="editarActivo" id="editarActivo" value="1">
                  <strong>Activo</strong> (aparecerá en los dropdowns)
                </label>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-primary">Guardar Cambios</button>
        </div>
        <?php
          $editarMedioPago = new ControladorMediosPago();
          $editarMedioPago -> ctrEditarMedioPago();
        ?>
      </form>
    </div>
  </div>
</div>

<script>
$(document).ready(function(){
  // Editar medio de pago
  $(".btnEditarMedioPago").click(function(){
    var idMedioPago = $(this).attr("idMedioPago");
    var datos = new FormData();
    datos.append("idMedioPago", idMedioPago);
    
    $.ajax({
      url: "ajax/medios_pago.ajax.php",
      method: "POST",
      data: datos,
      cache: false,
      contentType: false,
      processData: false,
      dataType: "json",
      success: function(respuesta){
        $("#idMedioPago").val(respuesta["id"]);
        $("#editarCodigo").val(respuesta["codigo"]);
        $("#editarNombre").val(respuesta["nombre"]);
        $("#editarDescripcion").val(respuesta["descripcion"] || "");
        $("#editarOrden").val(respuesta["orden"]);
        $("#editarRequiereCodigo").prop("checked", respuesta["requiere_codigo"] == 1);
        $("#editarRequiereBanco").prop("checked", respuesta["requiere_banco"] == 1);
        $("#editarRequiereNumero").prop("checked", respuesta["requiere_numero"] == 1);
        $("#editarRequiereFecha").prop("checked", respuesta["requiere_fecha"] == 1);
        $("#editarActivo").prop("checked", respuesta["activo"] == 1);
      }
    });
  });

  // Eliminar medio de pago
  $(".btnEliminarMedioPago").click(function(){
    var idMedioPago = $(this).attr("idMedioPago");
    swal({
      title: '¿Está seguro de borrar el medio de pago?',
      text: "¡Si no lo está puede cancelar la acción!",
      type: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#3085d6',
      cancelButtonColor: '#d33',
      cancelButtonText: 'Cancelar',
      confirmButtonText: '¡Sí, borrar!'
    }).then(function(result){
      if (result.value) {
        window.location = "medios-pago&idMedioPago="+idMedioPago;
      }
    });
  });
});
</script>

<?php
  $borrarMedioPago = new ControladorMediosPago();
  $borrarMedioPago -> ctrBorrarMedioPago();
?>
