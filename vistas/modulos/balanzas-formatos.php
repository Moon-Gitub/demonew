<?php
if ($_SESSION["perfil"] == "Vendedor") {
  echo '<script>window.location = "inicio";</script>';
  return;
}

if (isset($_GET["idBalanzaFormato"])) {
  ControladorBalanzasFormatos::ctrEliminar();
  return;
}

$id_empresa = isset($_SESSION["empresa"]) ? (int) $_SESSION["empresa"] : 1;
$formatos = ControladorBalanzasFormatos::ctrListar($id_empresa, false);
?>

<div class="content-wrapper">
  <section class="content-header">
    <h1>Formatos de balanza</h1>
    <ol class="breadcrumb">
      <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>
      <li><a href="empresa"><img src="vistas/img/plantilla/icono-negro.png" style="width:16px;margin-top:-2px;"> Empresa</a></li>
      <li class="active">Formatos de balanza</li>
    </ol>
  </section>

  <section class="content">
    <div class="box">
      <div class="box-header with-border">
        <button class="btn btn-primary" data-toggle="modal" data-target="#modalAgregarBalanzaFormato">
          <i class="fa fa-plus"></i> Agregar formato de balanza
        </button>
      </div>

      <div class="box-body">
        <p class="help-block">
          Configure aquí cómo se interpretan los códigos de las balanzas (prefijo, posición del producto y del peso/cantidad).
        </p>

        <table class="table table-bordered table-striped tablas" width="100%">
          <thead>
            <tr>
              <th style="width:40px">#</th>
              <th>Nombre</th>
              <th>Prefijo</th>
              <th>Longitud</th>
              <th>Pos. producto</th>
              <th>Modo cantidad</th>
              <th>Pos. cant/peso</th>
              <th>Factor / Cant. fija</th>
              <th>Orden</th>
              <th>Estado</th>
              <th style="width:120px">Acciones</th>
            </tr>
          </thead>
          <tbody>
            <?php if ($formatos): ?>
              <?php foreach ($formatos as $row): ?>
                <tr>
                  <td><?php echo (int) $row["id"]; ?></td>
                  <td><?php echo htmlspecialchars($row["nombre"]); ?></td>
                  <td><code><?php echo htmlspecialchars($row["prefijo"]); ?></code></td>
                  <td>
                    <?php
                      $min = $row["longitud_min"];
                      $max = $row["longitud_max"];
                      if ($min || $max) {
                        echo ($min ?: '?') . ' - ' . ($max ?: '?');
                      } else {
                        echo '-';
                      }
                    ?>
                  </td>
                  <td>
                    <?php echo (int) $row["pos_producto"]; ?>
                    /
                    <?php echo (int) $row["longitud_producto"]; ?>
                  </td>
                  <td><?php echo htmlspecialchars($row["modo_cantidad"]); ?></td>
                  <td>
                    <?php
                      if ($row["modo_cantidad"] === 'peso') {
                        echo (is_null($row["pos_cantidad"]) ? '-' : (int)$row["pos_cantidad"]);
                        echo ' / ';
                        echo (is_null($row["longitud_cantidad"]) ? '-' : (int)$row["longitud_cantidad"]);
                      } elseif ($row["modo_cantidad"] === 'unidad') {
                        echo '—';
                      } else {
                        echo 'Manual';
                      }
                    ?>
                  </td>
                  <td>
                    <?php
                      if ($row["modo_cantidad"] === 'peso') {
                        echo '÷ ' . (float) $row["factor_divisor"];
                      } elseif ($row["modo_cantidad"] === 'unidad') {
                        echo 'Cantidad: ' . (float) $row["cantidad_fija"];
                      } else {
                        echo '—';
                      }
                    ?>
                  </td>
                  <td class="text-center"><?php echo (int) $row["orden"]; ?></td>
                  <td class="text-center">
                    <?php if ($row["activo"]): ?>
                      <span class="label label-success">Activo</span>
                    <?php else: ?>
                      <span class="label label-default">Inactivo</span>
                    <?php endif; ?>
                  </td>
                  <td class="text-center">
                    <button class="btn btn-default btn-sm btnEditarBalanzaFormato"
                            data-id="<?php echo (int) $row["id"]; ?>"
                            data-toggle="modal"
                            data-target="#modalEditarBalanzaFormato"
                            title="Editar">
                      <i class="fa fa-pencil"></i>
                    </button>
                    <button class="btn btn-danger btn-sm btnEliminarBalanzaFormato"
                            data-id="<?php echo (int) $row["id"]; ?>"
                            title="Desactivar">
                      <i class="fa fa-times"></i>
                    </button>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>

        <p class="help-block">
          Ejemplos cargados por defecto replican la lógica de códigos que empiezan con <code>20000</code>, <code>20</code> y <code>21</code>.
        </p>
      </div>
    </div>
  </section>
</div>

<!-- Modal Agregar -->
<div id="modalAgregarBalanzaFormato" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <form role="form" method="post">
        <input type="hidden" name="csrf_token" value="<?php echo isset($_SESSION['csrf_token']) ? $_SESSION['csrf_token'] : ''; ?>">
        <div class="modal-header" style="background:#3c8dbc; color:white">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Agregar formato de balanza</h4>
        </div>
        <div class="modal-body">
          <input type="hidden" name="nuevoIdEmpresa" value="<?php echo $id_empresa; ?>">

          <div class="form-group">
            <label>Nombre</label>
            <input type="text" class="form-control" name="nuevoNombre" placeholder="Ej: Balanza 20000 (peso en kg)" required>
          </div>

          <div class="form-group">
            <label>Prefijo (inicio del código, ej: 20, 21, 20000)</label>
            <input type="text" class="form-control" name="nuevoPrefijo" placeholder="20, 21, 20000" required>
          </div>

          <div class="row">
            <div class="col-xs-6">
              <div class="form-group">
                <label>Longitud mínima (opcional)</label>
                <input type="number" class="form-control" name="nuevoLongitudMin" min="0" placeholder="Ej: 12">
              </div>
            </div>
            <div class="col-xs-6">
              <div class="form-group">
                <label>Longitud máxima (opcional)</label>
                <input type="number" class="form-control" name="nuevoLongitudMax" min="0" placeholder="Ej: 20">
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-xs-6">
              <div class="form-group">
                <label>Posición inicio producto (base 0)</label>
                <input type="number" class="form-control" name="nuevoPosProducto" min="0" required>
              </div>
            </div>
            <div class="col-xs-6">
              <div class="form-group">
                <label>Longitud producto</label>
                <input type="number" class="form-control" name="nuevoLongitudProducto" min="1" required>
              </div>
            </div>
          </div>

          <div class="form-group">
            <label>Modo cantidad</label>
            <select class="form-control" name="nuevoModoCantidad" id="nuevoModoCantidad">
              <option value="ninguno">Usar cantidad manual</option>
              <option value="peso">Peso (leer del código)</option>
              <option value="unidad">Unidad fija</option>
            </select>
          </div>

          <div id="grupoCantidadPeso" style="display:none">
            <div class="row">
              <div class="col-xs-6">
                <div class="form-group">
                  <label>Posición inicio cantidad/peso</label>
                  <input type="number" class="form-control" name="nuevoPosCantidad" min="0">
                </div>
              </div>
              <div class="col-xs-6">
                <div class="form-group">
                  <label>Longitud cantidad/peso</label>
                  <input type="number" class="form-control" name="nuevoLongitudCantidad" min="1">
                </div>
              </div>
            </div>

            <div class="form-group">
              <label>Factor divisor (ej: 1000 para pasar gramos a kg)</label>
              <input type="number" step="0.0001" class="form-control" name="nuevoFactorDivisor" value="1000">
            </div>
          </div>

          <div id="grupoCantidadFija" style="display:none">
            <div class="form-group">
              <label>Cantidad fija (ej: 1)</label>
              <input type="number" step="0.001" class="form-control" name="nuevoCantidadFija" value="1">
            </div>
          </div>

          <div class="form-group">
            <label>Orden</label>
            <input type="number" class="form-control" name="nuevoOrden" value="0" min="0">
          </div>

          <div class="form-group">
            <label><input type="checkbox" name="nuevoActivo" value="1" checked> Activo</label>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Salir</button>
          <button type="submit" class="btn btn-primary">Guardar</button>
        </div>
        <?php ControladorBalanzasFormatos::ctrCrear(); ?>
      </form>
    </div>
  </div>
</div>

<!-- Modal Editar -->
<div id="modalEditarBalanzaFormato" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <form role="form" method="post">
        <input type="hidden" name="csrf_token" value="<?php echo isset($_SESSION['csrf_token']) ? $_SESSION['csrf_token'] : ''; ?>">
        <div class="modal-header" style="background:#3c8dbc; color:white">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Editar formato de balanza</h4>
        </div>
        <div class="modal-body">
          <input type="hidden" name="idBalanzaFormato" id="idBalanzaFormato">

          <div class="form-group">
            <label>Nombre</label>
            <input type="text" class="form-control" name="editarNombre" id="editarNombre" required>
          </div>

          <div class="form-group">
            <label>Prefijo</label>
            <input type="text" class="form-control" name="editarPrefijo" id="editarPrefijo" required>
          </div>

          <div class="row">
            <div class="col-xs-6">
              <div class="form-group">
                <label>Longitud mínima</label>
                <input type="number" class="form-control" name="editarLongitudMin" id="editarLongitudMin" min="0">
              </div>
            </div>
            <div class="col-xs-6">
              <div class="form-group">
                <label>Longitud máxima</label>
                <input type="number" class="form-control" name="editarLongitudMax" id="editarLongitudMax" min="0">
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-xs-6">
              <div class="form-group">
                <label>Posición inicio producto</label>
                <input type="number" class="form-control" name="editarPosProducto" id="editarPosProducto" min="0" required>
              </div>
            </div>
            <div class="col-xs-6">
              <div class="form-group">
                <label>Longitud producto</label>
                <input type="number" class="form-control" name="editarLongitudProducto" id="editarLongitudProducto" min="1" required>
              </div>
            </div>
          </div>

          <div class="form-group">
            <label>Modo cantidad</label>
            <select class="form-control" name="editarModoCantidad" id="editarModoCantidad">
              <option value="ninguno">Usar cantidad manual</option>
              <option value="peso">Peso (leer del código)</option>
              <option value="unidad">Unidad fija</option>
            </select>
          </div>

          <div id="grupoEditarCantidadPeso" style="display:none">
            <div class="row">
              <div class="col-xs-6">
                <div class="form-group">
                  <label>Posición inicio cantidad/peso</label>
                  <input type="number" class="form-control" name="editarPosCantidad" id="editarPosCantidad" min="0">
                </div>
              </div>
              <div class="col-xs-6">
                <div class="form-group">
                  <label>Longitud cantidad/peso</label>
                  <input type="number" class="form-control" name="editarLongitudCantidad" id="editarLongitudCantidad" min="1">
                </div>
              </div>
            </div>

            <div class="form-group">
              <label>Factor divisor</label>
              <input type="number" step="0.0001" class="form-control" name="editarFactorDivisor" id="editarFactorDivisor" value="1000">
            </div>
          </div>

          <div id="grupoEditarCantidadFija" style="display:none">
            <div class="form-group">
              <label>Cantidad fija</label>
              <input type="number" step="0.001" class="form-control" name="editarCantidadFija" id="editarCantidadFija" value="1">
            </div>
          </div>

          <div class="form-group">
            <label>Orden</label>
            <input type="number" class="form-control" name="editarOrden" id="editarOrden" value="0" min="0">
          </div>

          <div class="form-group">
            <label><input type="checkbox" name="editarActivo" id="editarActivo" value="1"> Activo</label>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Salir</button>
          <button type="submit" class="btn btn-primary">Actualizar</button>
        </div>
        <?php ControladorBalanzasFormatos::ctrEditar(); ?>
      </form>
    </div>
  </div>
</div>

<script>
$(function(){
  function toggleGruposCrear() {
    var modo = $('#nuevoModoCantidad').val();
    $('#grupoCantidadPeso').toggle(modo === 'peso');
    $('#grupoCantidadFija').toggle(modo === 'unidad');
  }
  function toggleGruposEditar() {
    var modo = $('#editarModoCantidad').val();
    $('#grupoEditarCantidadPeso').toggle(modo === 'peso');
    $('#grupoEditarCantidadFija').toggle(modo === 'unidad');
  }

  $('#nuevoModoCantidad').on('change', toggleGruposCrear);
  $('#editarModoCantidad').on 'change', toggleGruposEditar);

  $('.btnEditarBalanzaFormato').on('click', function(e){
    e.preventDefault();
    var id = $(this).data('id');
    $.post('ajax/balanzas_formatos.ajax.php', { idBalanzaFormato: id, csrf_token: $('meta[name=\"csrf-token\"]').val() }, function(r){
      if (r) {
        $('#idBalanzaFormato').val(r.id);
        $('#editarNombre').val(r.nombre);
        $('#editarPrefijo').val(r.prefijo);
        $('#editarLongitudMin').val(r.longitud_min);
        $('#editarLongitudMax').val(r.longitud_max);
        $('#editarPosProducto').val(r.pos_producto);
        $('#editarLongitudProducto').val(r.longitud_producto);
        $('#editarModoCantidad').val(r.modo_cantidad);
        $('#editarPosCantidad').val(r.pos_cantidad);
        $('#editarLongitudCantidad').val(r.longitud_cantidad);
        $('#editarFactorDivisor').val(r.factor_divisor);
        $('#editarCantidadFija').val(r.cantidad_fija);
        $('#editarOrden').val(r.orden);
        $('#editarActivo').prop('checked', r.activo == 1);
        toggleGruposEditar();
      }
    }, 'json');
  });

  $('.btnEliminarBalanzaFormato').on('click', function(e){
    e.preventDefault();
    var id = $(this).data('id');
    if (!confirm('¿Desactivar este formato de balanza?')) return;
    window.location = 'index.php?ruta=balanzas-formatos&idBalanzaFormato=' + id;
  });

  toggleGruposCrear();
});
</script>

