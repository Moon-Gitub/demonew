<?php
if ($_SESSION["perfil"] == "Vendedor") {
  echo '<script>window.location = "inicio";</script>';
  return;
}
if (isset($_GET["idListaPrecio"])) {
  ControladorListasPrecio::ctrEliminarListaPrecio();
  return;
}
$id_empresa = isset($_SESSION["empresa"]) ? (int) $_SESSION["empresa"] : 1;
$listasPrecio = ControladorListasPrecio::ctrListar($id_empresa, false);
?>
<div class="content-wrapper">
  <section class="content-header">
    <h1>Listas de precio</h1>
    <ol class="breadcrumb">
      <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>
      <li><a href="empresa"><i class="fa fa-building-o"></i> Empresa</a></li>
      <li class="active">Listas de precio</li>
    </ol>
  </section>
  <section class="content">
    <div class="box">
      <div class="box-header with-border">
        <button class="btn btn-primary" data-toggle="modal" data-target="#modalAgregarListaPrecio">
          <i class="fa fa-plus"></i> Agregar lista de precio
        </button>
      </div>
      <div class="box-body">
        <table class="table table-bordered table-striped tablas" width="100%">
          <thead>
            <tr>
              <th style="width:10px">#</th>
              <th>Código</th>
              <th>Nombre</th>
              <th>Base precio</th>
              <th>Descuento</th>
              <th>Orden</th>
              <th>Estado</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            <?php
            if ($listasPrecio) {
              foreach ($listasPrecio as $row) {
                $descuento = $row['tipo_descuento'] === 'porcentaje' ? $row['valor_descuento'] . '%' : '—';
                echo '<tr>
                  <td>'.$row['id'].'</td>
                  <td class="text-uppercase"><b>'.htmlspecialchars($row['codigo']).'</b></td>
                  <td>'.htmlspecialchars($row['nombre']).'</td>
                  <td>'.htmlspecialchars($row['base_precio']).'</td>
                  <td>'.$descuento.'</td>
                  <td class="text-center">'.$row['orden'].'</td>
                  <td class="text-center">'.($row['activo'] ? '<span class="label label-success">Activo</span>' : '<span class="label label-default">Inactivo</span>').'</td>
                  <td class="text-center">
                    <a class="btn btn-default btn-sm btnEditarListaPrecio" href="#" idListaPrecio="'.$row['id'].'" data-toggle="modal" data-target="#modalEditarListaPrecio" title="Editar"><i class="fa fa-pencil"></i></a>
                    <a class="btn btn-danger btn-sm btnEliminarListaPrecio" href="#" idListaPrecio="'.$row['id'].'" title="Desactivar"><i class="fa fa-times"></i></a>
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

<!-- Modal Agregar -->
<div id="modalAgregarListaPrecio" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <form role="form" method="post">
        <input type="hidden" name="csrf_token" value="<?php echo isset($_SESSION['csrf_token']) ? $_SESSION['csrf_token'] : ''; ?>">
        <div class="modal-header" style="background:#3c8dbc; color:white">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Agregar lista de precio</h4>
        </div>
        <div class="modal-body">
          <input type="hidden" name="nuevoIdEmpresa" value="<?php echo $id_empresa; ?>">
          <div class="form-group">
            <label>Código (único, ej: precio_venta, empleados)</label>
            <input type="text" class="form-control" name="nuevoCodigo" placeholder="solo letras, números y guión bajo" required>
          </div>
          <div class="form-group">
            <label>Nombre (visible en ventas)</label>
            <input type="text" class="form-control" name="nuevoNombre" placeholder="Ej: Precio Público, Empleados" required>
          </div>
          <div class="form-group">
            <label>Base de precio (columna del producto)</label>
            <select class="form-control" name="nuevoBasePrecio" required>
              <option value="precio_venta">precio_venta</option>
              <option value="precio_compra">precio_compra</option>
            </select>
          </div>
          <div class="form-group">
            <label>Tipo de descuento</label>
            <select class="form-control" name="nuevoTipoDescuento" id="nuevoTipoDescuento">
              <option value="ninguno">Ninguno</option>
              <option value="porcentaje">Porcentaje sobre la base</option>
            </select>
          </div>
          <div class="form-group" id="grupoNuevoValorDescuento" style="display:none">
            <label>Valor descuento (%)</label>
            <input type="number" class="form-control" name="nuevoValorDescuento" min="0" max="100" step="0.01" value="0">
          </div>
          <div class="form-group">
            <label>Orden</label>
            <input type="number" class="form-control" name="nuevoOrden" value="0" min="0">
          </div>
          <div class="form-group">
            <label><input type="checkbox" name="nuevoActivo" value="1" checked> Activa</label>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Salir</button>
          <button type="submit" class="btn btn-primary">Guardar</button>
        </div>
        <?php ControladorListasPrecio::ctrCrearListaPrecio(); ?>
      </form>
    </div>
  </div>
</div>

<!-- Modal Editar -->
<div id="modalEditarListaPrecio" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <form role="form" method="post">
        <input type="hidden" name="csrf_token" value="<?php echo isset($_SESSION['csrf_token']) ? $_SESSION['csrf_token'] : ''; ?>">
        <div class="modal-header" style="background:#3c8dbc; color:white">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Editar lista de precio</h4>
        </div>
        <div class="modal-body">
          <input type="hidden" name="idListaPrecio" id="idListaPrecio">
          <div class="form-group">
            <label>Código</label>
            <input type="text" class="form-control" name="editarCodigo" id="editarCodigo" required>
          </div>
          <div class="form-group">
            <label>Nombre</label>
            <input type="text" class="form-control" name="editarNombre" id="editarNombre" required>
          </div>
          <div class="form-group">
            <label>Base de precio</label>
            <select class="form-control" name="editarBasePrecio" id="editarBasePrecio">
              <option value="precio_venta">precio_venta</option>
              <option value="precio_compra">precio_compra</option>
            </select>
          </div>
          <div class="form-group">
            <label>Tipo de descuento</label>
            <select class="form-control" name="editarTipoDescuento" id="editarTipoDescuento">
              <option value="ninguno">Ninguno</option>
              <option value="porcentaje">Porcentaje</option>
            </select>
          </div>
          <div class="form-group" id="grupoEditarValorDescuento" style="display:none">
            <label>Valor descuento (%)</label>
            <input type="number" class="form-control" name="editarValorDescuento" id="editarValorDescuento" min="0" max="100" step="0.01" value="0">
          </div>
          <div class="form-group">
            <label>Orden</label>
            <input type="number" class="form-control" name="editarOrden" id="editarOrden" value="0" min="0">
          </div>
          <div class="form-group">
            <label><input type="checkbox" name="editarActivo" id="editarActivo" value="1"> Activa</label>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Salir</button>
          <button type="submit" class="btn btn-primary">Actualizar</button>
        </div>
        <?php ControladorListasPrecio::ctrEditarListaPrecio(); ?>
      </form>
    </div>
  </div>
</div>

<script>
$(function(){
  $('#nuevoTipoDescuento').on('change', function(){
    $('#grupoNuevoValorDescuento').toggle($(this).val() === 'porcentaje');
  });
  $('#editarTipoDescuento').on('change', function(){
    $('#grupoEditarValorDescuento').toggle($(this).val() === 'porcentaje');
  });
  $('.btnEditarListaPrecio').on('click', function(e){
    e.preventDefault();
    var id = $(this).attr('idListaPrecio');
    $.post('ajax/listas_precio.ajax.php', { idListaPrecio: id, csrf_token: $('meta[name="csrf-token"]').attr('content') }, function(r){
      if (r) {
        $('#idListaPrecio').val(r.id);
        $('#editarCodigo').val(r.codigo);
        $('#editarNombre').val(r.nombre);
        $('#editarBasePrecio').val(r.base_precio);
        $('#editarTipoDescuento').val(r.tipo_descuento);
        $('#editarValorDescuento').val(r.valor_descuento);
        $('#editarOrden').val(r.orden);
        $('#editarActivo').prop('checked', r.activo == 1);
        $('#grupoEditarValorDescuento').toggle(r.tipo_descuento === 'porcentaje');
      }
    }, 'json');
  });
  $('.btnEliminarListaPrecio').on('click', function(e){
    e.preventDefault();
    if (!confirm('¿Desactivar esta lista de precio?')) return;
    var id = $(this).attr('idListaPrecio');
    window.location = 'index.php?ruta=listas-precio&idListaPrecio=' + id;
  });
});
</script>
