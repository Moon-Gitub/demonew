<?php
if ($_SESSION["perfil"] != "Administrador") {
  echo '<script>window.location = "inicio";</script>';
  return;
}

require_once dirname(__DIR__, 2) . "/modelos/permisos_rol.modelo.php";
$roles = ModeloPermisosRol::mdlListarRoles();
$pantallasAgrupadas = ModeloPermisosRol::mdlListarPantallasAgrupadas();
?>
<div class="content-wrapper">
  <section class="content-header">
    <h1>Permisos por rol</h1>
    <ol class="breadcrumb">
      <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>
      <li><a href="empresa"><i class="fa fa-building-o"></i> Empresa</a></li>
      <li class="active">Permisos por rol</li>
    </ol>
  </section>
  <section class="content">
    <div class="box">
      <div class="box-header with-border">
        <p class="help-block">
          Seleccione un rol y marque las pantallas a las que tendrá acceso. Solo se mostrarán en el menú y podrán ser abiertas las pantallas permitidas.
          Los cambios aplican al iniciar sesión: los usuarios deben cerrar sesión y volver a entrar para ver el menú actualizado.
        </p>
        <div class="form-inline" style="margin-bottom: 15px;">
          <label for="selRol" class="control-label" style="margin-right: 10px;">Rol:</label>
          <select id="selRol" class="form-control" style="min-width: 200px;">
            <option value="">-- Elegir rol --</option>
            <?php foreach ($roles as $r): ?>
              <option value="<?php echo htmlspecialchars($r); ?>"><?php echo htmlspecialchars($r); ?></option>
            <?php endforeach; ?>
          </select>
          <button type="button" class="btn btn-success" id="btnGuardarPermisos" style="margin-left: 15px;" disabled>
            <i class="fa fa-save"></i> Guardar permisos
          </button>
        </div>
      </div>
      <div class="box-body" id="contenedorPantallas">
        <?php if (empty($pantallasAgrupadas)): ?>
          <p class="text-muted">No hay pantallas cargadas. Ejecute el script SQL de creación de tablas <code>db/crear-tablas-permisos-rol.sql</code>.</p>
        <?php else: ?>
          <?php foreach ($pantallasAgrupadas as $agrupacion => $pantallas): ?>
            <div class="panel panel-default">
              <div class="panel-heading">
                <strong><?php echo htmlspecialchars($agrupacion); ?></strong>
              </div>
              <div class="panel-body">
                <div class="row">
                  <?php foreach ($pantallas as $p): ?>
                    <div class="col-md-4 col-sm-6" style="margin-bottom: 8px;">
                      <label style="font-weight: normal; cursor: pointer;">
                        <input type="checkbox" class="chkPantalla" value="<?php echo (int) $p['id']; ?>" data-codigo="<?php echo htmlspecialchars($p['codigo']); ?>">
                        <?php echo htmlspecialchars($p['nombre']); ?>
                        <small class="text-muted">(<?php echo htmlspecialchars($p['codigo']); ?>)</small>
                      </label>
                    </div>
                  <?php endforeach; ?>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
  </section>
</div>

<script>
$(function() {
  var csrfToken = $('meta[name="csrf-token"]').attr('content');

  function cargarPermisosRol(rol) {
    if (!rol) {
      $('.chkPantalla').prop('checked', false);
      $('#btnGuardarPermisos').prop('disabled', true);
      return;
    }
    $.post('ajax/permisos_rol.ajax.php', { accion: 'permisosPorRol', rol: rol }, function(r) {
      if (r.ok && r.ids_pantallas) {
        $('.chkPantalla').prop('checked', false);
        r.ids_pantallas.forEach(function(id) {
          $('.chkPantalla[value="' + id + '"]').prop('checked', true);
        });
        $('#btnGuardarPermisos').prop('disabled', false);
      }
    }, 'json').fail(function() {
      $('#btnGuardarPermisos').prop('disabled', false);
    });
  }

  $('#selRol').on('change', function() {
    var rol = $(this).val();
    cargarPermisosRol(rol);
  });

  $('#btnGuardarPermisos').on('click', function() {
    var rol = $('#selRol').val();
    if (!rol) {
      Swal.fire('Atención', 'Seleccione un rol.', 'warning');
      return;
    }
    var ids = [];
    $('.chkPantalla:checked').each(function() {
      ids.push($(this).val());
    });
    $.post('ajax/permisos_rol.ajax.php', {
      accion: 'guardarPermisos',
      csrf_token: csrfToken,
      rol: rol,
      ids_pantallas: ids
    }, function(r) {
      if (r.ok) {
        Swal.fire('Guardado', r.mensaje || 'Permisos guardados correctamente.', 'success');
      } else {
        Swal.fire('Error', r.mensaje || 'Error al guardar.', 'error');
      }
    }, 'json').fail(function() {
      Swal.fire('Error', 'Error de conexión.', 'error');
    });
  });
});
</script>
