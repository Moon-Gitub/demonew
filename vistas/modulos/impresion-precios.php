<div class="content-wrapper">

  <section class="content-header">
    <h1>Impresión precios productos</h1>
    <ol class="breadcrumb">
      <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>
      <li class="active">Impresión precios productos</li>
    </ol>
  </section>

  <section class="content">

    <div class="box">

      <div class="box-header with-border">
        <input type="hidden" id="idSucursal" name="idSucursal" value="<?php echo $_SESSION["sucursal"]; ?>" required>
        <div class="row">

          <div class="col-md-8 col-sm-12">
            <div class="impresion-header-buttons">
              <button class="btn btn-primary" title="Impresión normal" id="btnImprimirPreciosComunProductos">
                <i class="fa fa-newspaper-o"></i> Etiquetas normales
              </button>
              <button class="btn btn-success" title="Impresión oferta" id="btnImprimirPreciosSuperProductos">
                <i class="fa fa-file-pdf-o"></i> Etiquetas oferta
              </button>
              <button class="btn btn-warning" title="Imprimir código QR" id="btnImprimirCodigosQr">
                <i class="fa fa-qrcode"></i> Códigos QR
              </button>
              <button class="btn btn-danger" title="Imprimir código de barras" id="btnImprimirCodigosBarra">
                <i class="fa fa-barcode"></i> Códigos de barras
              </button>
              <input type="hidden" id="arrayProductosImpresion" name="arrayProductosImpresion"/>
            </div>
          </div>

          <div class="col-md-4 col-sm-12">
            <div class="panel panel-default panel-seleccion-impresion">
              <div class="panel-heading">
                <div class="panel-title">
                  <strong>Seleccionados para imprimir</strong>
                  <span class="badge bg-purple" id="contadorSeleccion">0</span>
                </div>
              </div>
              <div class="panel-body lista-seleccion-impresion" id="listaSeleccionImpresion">
                <p class="text-muted texto-sin-seleccion">
                  No hay productos seleccionados. Usa el botón <strong>Agregar</strong> de la tabla para armar tu lista.
                </p>
              </div>
            </div>
          </div>

        </div>
      </div>

      <div class="box-body">
        <table class="table table-bordered" id="tablaImpresionProductosImpresion" name="tablaImpresionProductosImpresion">
          <thead>
            <tr>
              <th><center>Id</center></th>
              <th><center>Código</center></th>
              <th><center>Descripción</center></th>
              <th><center>Precio venta</center></th>
              <th><center>Quitar</center></th>
              <th><center>Agregar</center></th>
            </tr>
          </thead>
        </table>
      </div>

    </div>

  </section>

</div>

<style>
.uniqueClassName {
  text-align: center;
}

.impresion-header-buttons {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
  align-items: center;
  margin-bottom: 10px;
}

.impresion-header-buttons .btn {
  margin-bottom: 5px;
}

.panel-seleccion-impresion {
  margin-top: 5px;
}

.panel-seleccion-impresion .panel-title {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.lista-seleccion-impresion {
  max-height: 210px;
  overflow-y: auto;
  padding: 8px 12px;
}

.item-seleccion-impresion {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 4px 0;
  border-bottom: 1px solid #f0f0f0;
  font-size: 12px;
}

.item-seleccion-impresion:last-child {
  border-bottom: none;
}

.item-seleccion-impresion .descripcion {
  flex: 1;
  margin-right: 6px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.item-seleccion-impresion .precio {
  font-weight: 600;
  margin-right: 6px;
}

.item-seleccion-impresion .btn-xs {
  padding: 2px 5px;
  font-size: 11px;
}

@media (max-width: 768px) {
  .lista-seleccion-impresion {
    max-height: 150px;
  }
}
</style>
