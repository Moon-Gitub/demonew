<?php

if($_SESSION["perfil"] == "Vendedor"){
  echo '<script>
  window.location = "inicio";
  </script>';
  return;
}

?>

<div class="content-wrapper">
  <section class="content-header">
    <h1>
      Administrar integraciones
    </h1>
    <ol class="breadcrumb">
      <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>
      <li class="active">Administrar integraciones</li>
    </ol>
  </section>
  <section class="content">
    <div class="box">
      <div class="box-header with-border">
        <button class="btn btn-primary" data-toggle="modal" data-target="#modalAgregarIntegracion">
          <i class="fa fa-plus"></i> Agregar integración
        </button>
      </div>
      <div class="box-body">
       <table class="table table-bordered table-striped dt-responsive tablas" width="100%">
        <thead>
         <tr>
           <th>Nombre</th>
           <th>Tipo</th>
           <th>Webhook URL</th>
           <th>Estado</th>
           <th>Fecha creación</th>
           <th style="width:200px">Acciones</th>
         </tr> 
        </thead>
        <tbody>

        <?php

          $item = null;
          $valor = null;
          $integraciones = ControladorIntegraciones::ctrMostrarIntegraciones($item, $valor);
          
          if($integraciones && is_array($integraciones) && count($integraciones) > 0){
            foreach ($integraciones as $key => $value) {
              $estado = $value["activo"] == 1 ? '<span class="label label-success">Activo</span>' : '<span class="label label-danger">Inactivo</span>';
              $fecha = date('d/m/Y H:i', strtotime($value["fecha_creacion"]));
              $webhookUrl = !empty($value["webhook_url"]) ? (strlen($value["webhook_url"]) > 50 ? substr($value["webhook_url"], 0, 50) . '...' : $value["webhook_url"]) : '<span class="text-muted">No configurado</span>';
              
              echo '<tr>
                    <td>'.$value["nombre"].'</td>
                    <td><span class="label label-info">'.strtoupper($value["tipo"]).'</span></td>
                    <td>'.$webhookUrl.'</td>
                    <td>'.$estado.'</td>
                    <td>'.$fecha.'</td>
                    <td class="text-center">
                      <div class="acciones-tabla">
                        <a class="btn-accion btnEditarIntegracion" title="Editar integración" data-toggle="modal" data-target="#modalEditarIntegracion" idIntegracion="'.$value["id"].'"><i class="fa fa-pencil"></i></a>';
              if($_SESSION["perfil"] == "Administrador"){
                echo '<a class="btn-accion btn-danger btnEliminarIntegracion" title="Borrar integración" idIntegracion="'.$value["id"].'" href="#"><i class="fa fa-times"></i></a>';
              }
              echo '</div>
                    </td>
                  </tr>';
            }
          } else {
            echo '<tr><td colspan="6" class="text-center">No hay integraciones registradas</td></tr>';
          }
        ?>
        </tbody>
       </table>
      </div>
    </div>
  </section>
</div>

<!--=====================================
MODAL AGREGAR INTEGRACIÓN
======================================-->
<div id="modalAgregarIntegracion" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <form role="form" method="post">
        <!--CABEZA DEL MODAL-->
        <div class="modal-header" style="background:#3c8dbc; color:white">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Agregar integración</h4>
        </div>
        <!--CUERPO DEL MODAL-->
        <div class="modal-body">
          <div class="box-body">
            <!-- ENTRADA PARA EL NOMBRE -->
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-tag"></i></span> 
                <input type="text" class="form-control" name="nuevoNombre" id="nuevoNombre" placeholder="Ingresar nombre de la integración" required>
              </div>
            </div>
            
            <!-- ENTRADA PARA EL TIPO -->
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-list-ul"></i></span> 
                <select class="form-control" name="nuevoTipo" id="nuevoTipo" required>
                  <option value="">Seleccionar tipo</option>
                  <option value="n8n">N8N</option>
                  <option value="api">API REST</option>
                  <option value="webhook">Webhook</option>
                  <option value="otro">Otro</option>
                </select>
              </div>
            </div>
            
            <!-- ENTRADA PARA WEBHOOK URL -->
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-link"></i></span> 
                <input type="url" class="form-control" name="nuevoWebhookUrl" id="nuevoWebhookUrl" placeholder="https://tu-n8n-instance.com/webhook/chat">
              </div>
              <p class="help-block">URL completa del webhook de N8N o servicio externo</p>
            </div>
            
            <!-- ENTRADA PARA API KEY -->
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-key"></i></span> 
                <input type="text" class="form-control" name="nuevoApiKey" id="nuevoApiKey" placeholder="API Key (opcional)">
              </div>
              <p class="help-block">Clave API si es requerida por el servicio</p>
            </div>
            
            <!-- ENTRADA PARA DESCRIPCIÓN -->
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-comment"></i></span> 
                <textarea class="form-control" name="nuevaDescripcion" id="nuevaDescripcion" rows="3" placeholder="Descripción de la integración (opcional)"></textarea>
              </div>
            </div>
            
            <!-- ENTRADA PARA ACTIVO -->
            <div class="form-group">
              <div class="input-group">
                <label>
                  <input type="checkbox" name="nuevoActivo" checked> 
                  Integración activa
                </label>
              </div>
              <p class="help-block">Desmarcar para desactivar temporalmente</p>
            </div>
        </div>
       </div>
        <!--PIE DEL MODAL-->
        <div class="modal-footer">
          <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Salir</button>
          <button type="submit" class="btn btn-primary">Guardar integración</button>
        </div>
      </form>
      <?php
        $crearIntegracion = new ControladorIntegraciones();
        $crearIntegracion -> ctrCrearIntegracion();
      ?>
    </div>
  </div>
</div>

<!--=====================================
MODAL EDITAR INTEGRACIÓN
======================================-->
<div id="modalEditarIntegracion" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <form role="form" method="post">
        <!--CABEZA DEL MODAL-->
        <div class="modal-header" style="background:#3c8dbc; color:white">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Editar integración</h4>
        </div>
        <!--CUERPO DEL MODAL-->
        <div class="modal-body">
          <div class="box-body">
            <!-- ENTRADA PARA EL NOMBRE -->
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-tag"></i></span> 
                <input type="text" class="form-control" name="editarNombre" id="editarNombre" placeholder="Ingresar nombre de la integración" required>
                <input type="hidden" id="idIntegracion" name="idIntegracion">
              </div>
            </div>
            
            <!-- ENTRADA PARA EL TIPO -->
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-list-ul"></i></span> 
                <select class="form-control" name="editarTipo" id="editarTipo" required>
                  <option value="">Seleccionar tipo</option>
                  <option value="n8n">N8N</option>
                  <option value="api">API REST</option>
                  <option value="webhook">Webhook</option>
                  <option value="otro">Otro</option>
                </select>
              </div>
            </div>
            
            <!-- ENTRADA PARA WEBHOOK URL -->
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-link"></i></span> 
                <input type="url" class="form-control" name="editarWebhookUrl" id="editarWebhookUrl" placeholder="https://tu-n8n-instance.com/webhook/chat">
              </div>
              <p class="help-block">URL completa del webhook de N8N o servicio externo</p>
            </div>
            
            <!-- ENTRADA PARA API KEY -->
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-key"></i></span> 
                <input type="text" class="form-control" name="editarApiKey" id="editarApiKey" placeholder="API Key (opcional)">
              </div>
              <p class="help-block">Clave API si es requerida por el servicio</p>
            </div>
            
            <!-- ENTRADA PARA DESCRIPCIÓN -->
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-comment"></i></span> 
                <textarea class="form-control" name="editarDescripcion" id="editarDescripcion" rows="3" placeholder="Descripción de la integración (opcional)"></textarea>
              </div>
            </div>
            
            <!-- ENTRADA PARA ACTIVO -->
            <div class="form-group">
              <div class="input-group">
                <label>
                  <input type="checkbox" name="editarActivo" id="editarActivo"> 
                  Integración activa
                </label>
              </div>
              <p class="help-block">Desmarcar para desactivar temporalmente</p>
            </div>
        </div>
       </div>
        <!--PIE DEL MODAL-->
        <div class="modal-footer">
          <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Salir</button>
          <button type="submit" class="btn btn-primary">Guardar cambios</button>
        </div>
      </form>
      <?php
        $editarIntegracion = new ControladorIntegraciones();
        $editarIntegracion -> ctrEditarIntegracion();
        $editarIntegracion -> ctrEliminarIntegracion();
      ?>
    </div>
  </div>
</div>

