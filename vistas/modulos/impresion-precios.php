<div class="content-wrapper">

  <section class="content-header">
    <h1>Impresión precios productos</h1>
    <ol class="breadcrumb">
      <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>
      <li class="active">Impresión precios productos</li>
    </ol>
  </section>

  <section class="content impresion-precios-modern">

    <div class="row">
      
      <!-- PANEL IZQUIERDO: PRODUCTOS SELECCIONADOS PARA IMPRIMIR -->
      <div class="col-lg-7 col-md-7 col-sm-12">
        <div class="box box-primary">
          <div class="box-header with-border">
            <h3 class="box-title">
              <i class="fa fa-list"></i> Productos seleccionados para imprimir
              <span class="badge bg-purple" id="contadorSeleccion">0</span>
            </h3>
            <div class="box-tools pull-right">
              <button type="button" class="btn btn-sm btn-danger" id="btnLimpiarSeleccion" title="Limpiar todo">
                <i class="fa fa-trash"></i> Limpiar
              </button>
            </div>
          </div>
          <div class="box-body">
            <!-- Botones de impresión -->
            <div class="impresion-buttons-row">
              <button class="btn btn-primary btn-lg btn-impresion" id="btnImprimirPreciosComunProductos" title="Impresión normal">
                <i class="fa fa-newspaper-o"></i> Etiquetas normales
              </button>
              <button class="btn btn-success btn-lg btn-impresion" id="btnImprimirPreciosSuperProductos" title="Impresión oferta">
                <i class="fa fa-file-pdf-o"></i> Etiquetas oferta
              </button>
              <button class="btn btn-warning btn-lg btn-impresion" id="btnImprimirCodigosQr" title="Imprimir código QR">
                <i class="fa fa-qrcode"></i> Códigos QR
              </button>
              <button class="btn btn-danger btn-lg btn-impresion" id="btnImprimirCodigosBarra" title="Imprimir código de barras">
                <i class="fa fa-barcode"></i> Códigos de barras
              </button>
            </div>
            <hr>
            <!-- Lista de productos seleccionados -->
            <div class="lista-productos-seleccionados" id="listaSeleccionImpresion">
              <div class="empty-state">
                <i class="fa fa-inbox fa-3x text-muted"></i>
                <p class="text-muted">No hay productos seleccionados</p>
                <p class="text-muted small">Usa el panel derecho para buscar y agregar productos</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- PANEL DERECHO: BUSCAR Y AGREGAR PRODUCTOS -->
      <div class="col-lg-5 col-md-5 col-sm-12">
        <div class="box box-success">
          <div class="box-header with-border">
            <h3 class="box-title">
              <i class="fa fa-search"></i> Buscar productos
            </h3>
          </div>
          <div class="box-body">
            <!-- Búsqueda rápida -->
            <div class="form-group">
              <label>Buscar por código o descripción</label>
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-search"></i></span>
                <input type="text" class="form-control" id="buscarProductoImpresion" placeholder="Escribe código o nombre del producto..." autocomplete="off">
              </div>
            </div>
            <hr>
            <!-- Tabla de productos disponibles -->
            <div class="table-responsive">
              <table class="table table-bordered table-striped" id="tablaImpresionProductosImpresion">
                <thead>
                  <tr>
                    <th style="width: 15%;"><center>Código</center></th>
                    <th style="width: 50%;"><center>Descripción</center></th>
                    <th style="width: 20%;"><center>Precio</center></th>
                    <th style="width: 15%;"><center>Acción</center></th>
                  </tr>
                </thead>
                <tbody>
                  <!-- DataTable se carga aquí -->
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

    </div>

  </section>

</div>

<style>
/* ============================================
   ESTILOS MODERNOS PARA IMPRESIÓN PRECIOS
   Diseño tipo POS con dos paneles
   ============================================ */

.impresion-precios-modern .box {
  border-radius: 12px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  border: none;
}

.impresion-precios-modern .box-header {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: #ffffff;
  border-radius: 12px 12px 0 0;
  padding: 15px 20px;
  border-bottom: none;
}

.impresion-precios-modern .box-header h3 {
  color: #ffffff;
  font-weight: 600;
  margin: 0;
}

.impresion-precios-modern .box-header .badge {
  background: rgba(255, 255, 255, 0.3);
  color: #ffffff;
  font-size: 14px;
  padding: 5px 10px;
  margin-left: 10px;
}

.impresion-precios-modern .box-success .box-header {
  background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
}

.impresion-precios-modern .impresion-buttons-row {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
  margin-bottom: 20px;
}

.impresion-precios-modern .btn-impresion {
  flex: 1;
  min-width: 150px;
  padding: 12px 20px;
  font-weight: 600;
  border-radius: 8px;
  transition: all 0.3s ease;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
}

.impresion-precios-modern .btn-impresion:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.25);
}

.impresion-precios-modern .lista-productos-seleccionados {
  min-height: 300px;
  max-height: 500px;
  overflow-y: auto;
  padding: 10px;
  background: #f8f9fa;
  border-radius: 8px;
  border: 2px dashed #e0e0e0;
}

.impresion-precios-modern .empty-state {
  text-align: center;
  padding: 60px 20px;
  color: #95a5a6;
}

.impresion-precios-modern .empty-state i {
  margin-bottom: 15px;
  opacity: 0.5;
}

.impresion-precios-modern .item-producto-seleccionado {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px 15px;
  margin-bottom: 8px;
  background: #ffffff;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
  border-left: 4px solid #667eea;
  transition: all 0.2s ease;
}

.impresion-precios-modern .item-producto-seleccionado:hover {
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
  transform: translateX(4px);
}

.impresion-precios-modern .item-producto-seleccionado .info-producto {
  flex: 1;
  margin-right: 15px;
}

.impresion-precios-modern .item-producto-seleccionado .codigo-producto {
  font-size: 11px;
  color: #7f8c8d;
  font-weight: 600;
  text-transform: uppercase;
  margin-bottom: 4px;
}

.impresion-precios-modern .item-producto-seleccionado .descripcion-producto {
  font-size: 14px;
  color: #2c3e50;
  font-weight: 500;
  margin-bottom: 4px;
}

.impresion-precios-modern .item-producto-seleccionado .precio-producto {
  font-size: 16px;
  color: #667eea;
  font-weight: 700;
}

.impresion-precios-modern .item-producto-seleccionado .btn-quitar {
  padding: 8px 12px;
  border-radius: 6px;
  font-size: 12px;
}

.impresion-precios-modern .input-group-addon {
  background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
  border: 2px solid #e0e0e0;
  border-right: none;
  color: #2c3e50;
  font-weight: 600;
}

.impresion-precios-modern #buscarProductoImpresion {
  border-left: none;
  border-radius: 0 8px 8px 0;
}

.impresion-precios-modern #buscarProductoImpresion:focus {
  border-color: #667eea;
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.impresion-precios-modern .table {
  margin-bottom: 0;
}

.impresion-precios-modern .table thead {
  background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
  color: #ffffff;
}

.impresion-precios-modern .table thead th {
  border: none;
  font-weight: 600;
  padding: 12px 8px;
}

.impresion-precios-modern .table tbody tr {
  transition: all 0.2s ease;
}

.impresion-precios-modern .table tbody tr:hover {
  background: #f0f7ff;
}

.impresion-precios-modern .table tbody td {
  vertical-align: middle;
  padding: 10px 8px;
}

.impresion-precios-modern .btn-agregar-producto {
  padding: 6px 12px;
  border-radius: 6px;
  font-size: 12px;
  font-weight: 600;
}

/* Responsive */
@media (max-width: 991px) {
  .impresion-precios-modern .col-lg-7,
  .impresion-precios-modern .col-lg-5 {
    margin-bottom: 20px;
  }
  
  .impresion-precios-modern .impresion-buttons-row {
    flex-direction: column;
  }
  
  .impresion-precios-modern .btn-impresion {
    width: 100%;
  }
}
</style>
