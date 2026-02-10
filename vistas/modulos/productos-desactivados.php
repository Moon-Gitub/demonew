<?php
/**
 * Listado de productos desactivados (activo = 0)
 * Permite reactivar productos para volver a usarlos.
 */
?>

<div class="content-wrapper">
  <section class="content-header">
    <h1>
      Productos desactivados
      <small>Administrar productos dados de baja</small>
    </h1>
    <ol class="breadcrumb">
      <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>
      <li><a href="productos"><i class="fa fa-product-hunt"></i> Productos</a></li>
      <li class="active">Productos desactivados</li>
    </ol>
  </section>

  <section class="content">
    <div class="box">
      <div class="box-header with-border">
        <h3 class="box-title">Listado de productos desactivados</h3>
      </div>
      <div class="box-body">
        <table id="tablaProductosInactivos" class="table table-bordered table-striped dt-responsive" width="100%">
          <thead>
            <tr>
              <th>Código</th>
              <th>Categoría</th>
              <th>Proveedor</th>
              <th>Descripción</th>
              <th>Stock</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody></tbody>
        </table>
      </div>
    </div>
  </section>
</div>

<?php
// Manejar reactivación de productos (activo = 1)
ControladorProductos::ctrActivarProducto();
?>

<script>
// DataTable para productos desactivados
$(function() {
  if (typeof $.fn.DataTable === 'undefined') {
    return;
  }

  $('#tablaProductosInactivos').DataTable({
    ajax: {
      url: 'ajax/datatable-productos-inactivos.serverside.php',
      type: 'GET'
    },
    deferRender: true,
    retrieve: true,
    processing: true,
    serverSide: true,
    dom: 'Bfrtip',
    buttons: (typeof GL_DATATABLE_BOTONES !== 'undefined') ? GL_DATATABLE_BOTONES : ['copy', 'excel', 'pdf', 'print'],
    language: (typeof GL_DATATABLE_LENGUAJE !== 'undefined') ? GL_DATATABLE_LENGUAJE : {},
    order: [[0, 'asc']]
  });

  // Activar producto
  $('#tablaProductosInactivos tbody').on('click', '.btnActivarProducto', function(e) {
    e.preventDefault();

    var idProducto = $(this).attr('idProducto');
    var codigo = $(this).attr('codigo');

    swal({
      title: '¿Activar producto?',
      text: 'El producto volverá a estar disponible para usar en ventas y listados.',
      type: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#3085d6',
      cancelButtonColor: '#d33',
      cancelButtonText: 'Cancelar',
      confirmButtonText: 'Sí, activar'
    }).then(function(result) {
      if (result.value) {
        window.location = 'index.php?ruta=productos-desactivados&idProducto=' + idProducto + '&accion=activar&codigo=' + codigo;
      }
    });
  });
});
</script>

