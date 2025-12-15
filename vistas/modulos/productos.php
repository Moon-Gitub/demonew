<?php
    $objProducto = new ControladorProductos();
    $listasPrecio = $objParametros->getListasPrecio();
    $precioDolar = ($objParametros->getPrecioDolar()) ? '' : 'display:none;';
?>

<style>
/* Estilos específicos para página de productos - Mejor separación y diseño */
.productos-header-buttons {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    margin-bottom: 20px;
    align-items: center;
}

.productos-header-buttons .btn {
    margin: 0;
    flex: 0 0 auto;
}

.productos-columnas-selector {
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
    padding: 20px 25px;
    border-radius: 12px;
    margin: 30px 0 25px 0;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.productos-columnas-selector strong {
    color: #2c3e50;
    font-size: 15px;
    font-weight: 600;
    margin-right: 15px;
    display: inline-block;
}

.productos-columnas-selector a {
    color: #667eea;
    text-decoration: none;
    font-weight: 500;
    transition: all 0.3s ease;
    margin: 0 5px;
    padding: 5px 10px;
    border-radius: 6px;
    display: inline-block;
    position: relative;
}

.productos-columnas-selector a:hover {
    color: #ffffff;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    transform: translateY(-2px);
    box-shadow: 0 2px 6px rgba(102, 126, 234, 0.3);
    text-decoration: none;
}

.productos-columnas-selector a.active {
    color: #ffffff;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    box-shadow: 0 2px 6px rgba(102, 126, 234, 0.3);
}

.productos-columnas-selector .separator {
    color: #d0d0d0;
    margin: 0 3px;
    font-weight: 300;
}

/* Estilos para el buscador de DataTables - Input visible, alineado y bonito */
#tablaProductos_filter {
    margin: 30px 0 25px 0 !important;
    text-align: left !important;
    padding: 0 !important;
    position: relative;
    display: flex;
    align-items: center;
    gap: 15px;
}

/* Ocultar completamente el texto "Buscar:" del label de DataTables, mantener solo el input visible */
#tablaProductos_filter label {
    display: flex !important;
    align-items: center !important;
    gap: 0 !important;
    margin: 0 !important;
    padding: 0 !important;
    position: relative;
    flex: 0 0 auto;
    font-size: 0 !important; /* Ocultar todo el texto del label */
    line-height: 0 !important;
    color: transparent !important;
}

/* Ocultar cualquier span o texto dentro del label */
#tablaProductos_filter label > span,
#tablaProductos_filter label:not(:has(input)) {
    display: none !important;
    font-size: 0 !important;
    visibility: hidden !important;
}

/* Ocultar el texto "Buscar:" que DataTables inserta directamente en el label */
#tablaProductos_filter label {
    text-indent: -9999px;
    overflow: hidden;
}

/* Restaurar el input a tamaño normal */
#tablaProductos_filter label input {
    font-size: 14px !important;
    line-height: normal !important;
    color: #333 !important;
    text-indent: 0 !important;
}

/* Crear nuestro propio label "Buscar:" limpio y separado ANTES del contenedor */
#tablaProductos_filter::before {
    content: "Buscar:";
    font-family: inherit;
    color: #2c3e50;
    font-weight: 600;
    font-size: 15px;
    display: inline-block;
    white-space: nowrap;
    line-height: 1;
    vertical-align: middle;
    flex-shrink: 0;
}

/* Input visible, alineado y bonito */
#tablaProductos_filter input {
    border: 2px solid #e0e0e0 !important;
    border-radius: 8px !important;
    padding: 12px 15px 12px 45px !important;
    font-size: 14px !important;
    transition: all 0.3s ease !important;
    background: #ffffff !important;
    width: 350px !important;
    max-width: 100% !important;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08) !important;
    position: relative !important;
    margin-left: 0 !important;
    display: inline-block !important;
    visibility: visible !important;
    opacity: 1 !important;
    flex-shrink: 0;
}

/* Icono de lupa DENTRO del input, posicionado correctamente */
#tablaProductos_filter label::after {
    content: "\f002";
    font-family: "FontAwesome";
    color: #667eea;
    font-size: 16px;
    position: absolute;
    left: 15px; /* Dentro del input, al inicio */
    top: 50%;
    transform: translateY(-50%);
    z-index: 2;
    pointer-events: none;
}

#tablaProductos_filter input:focus {
    border-color: #667eea !important;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1), 0 4px 12px rgba(102, 126, 234, 0.15) !important;
    outline: none !important;
    transform: translateY(-1px);
}

#tablaProductos_filter input::placeholder {
    color: #aaa !important;
    font-style: italic;
}

/* Contenedor principal de la tabla de productos */
.productos-table-container {
    margin-top: 20px;
}

/* Quitar borde superior visual de la tabla para que se integre mejor con el recuadro */
#tablaProductos {
    border-top: none !important;
}

/* Estilos para mobile - Responsive */
@media (max-width: 768px) {
    /* Botones de acción en mobile */
    .productos-header-buttons {
        flex-direction: column;
        gap: 10px;
        width: 100%;
    }
    
    .productos-header-buttons .btn {
        width: 100%;
        margin: 0;
    }
    
    /* Selector de columnas en mobile */
    .productos-columnas-selector {
        padding: 15px !important;
        margin: 20px 0 15px 0 !important;
    }
    
    .productos-columnas-selector > div {
        flex-direction: column;
        align-items: flex-start !important;
        gap: 12px !important;
    }
    
    .productos-columnas-selector strong {
        margin-bottom: 10px;
        display: block;
        width: 100%;
        font-size: 14px;
    }
    
    .productos-columnas-selector a {
        margin: 5px 0 !important;
        display: inline-block;
        width: auto;
        min-width: 120px;
        text-align: center;
    }
    
    .productos-columnas-selector .separator {
        display: none;
    }
    
    /* Buscador en mobile - Input visible, alineado y bonito */
    #tablaProductos_filter {
        margin: 20px 0 15px 0 !important;
        width: 100%;
        flex-direction: column;
        align-items: flex-start !important;
        gap: 12px !important;
    }
    
    /* Label "Buscar:" separado arriba en mobile */
    #tablaProductos_filter::before {
        display: block;
        margin-bottom: 0;
    }
    
    #tablaProductos_filter label {
        width: 100%;
        flex-direction: row;
        align-items: center;
        gap: 0 !important;
    }
    
    /* Icono de lupa dentro del input en mobile */
    #tablaProductos_filter label::after {
        left: 15px !important; /* En mobile, el icono va al inicio del input */
        top: 50%;
    }
    
    #tablaProductos_filter input {
        width: 100% !important;
        padding: 12px 15px 12px 45px !important;
        margin: 0 !important;
        visibility: visible !important;
        opacity: 1 !important;
        display: inline-block !important;
    }
    
    /* Contenedor de tabla en mobile */
    .productos-table-container {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        margin-top: 15px;
        width: 100%;
    }
    
    .productos-table-container table {
        min-width: 800px;
    }
    
    /* Box body en mobile */
    .box-body {
        padding: 15px !important;
    }
}

/* ===============================
   FOOTER DE LA TABLA (FILTROS)
   =============================== */

/* Estilo general del footer: sin borde superior fuerte y fondo suave */
#tablaProductos tfoot {
    background: #f8f9fb;
    border-top: 1px solid #e0e0e0;
}

/* Celdas del footer alineadas y sin borde superior grueso */
#tablaProductos tfoot th {
    border-top: none !important;
    padding: 8px 6px !important;
    text-align: center;
}

/* Inputs de filtro del footer */
#tablaProductos tfoot th input {
    width: 100%;
    max-width: 120px;
    border: 1px solid #d0d4e4;
    border-radius: 6px;
    padding: 6px 8px;
    font-size: 12px;
    color: #34495e;
    background: #ffffff;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    transition: all 0.2s ease;
}

#tablaProductos tfoot th input::placeholder {
    color: #a0a4b8;
}

#tablaProductos tfoot th input:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 2px rgba(102,126,234,0.15);
    outline: none;
}
</style>

<div class="content-wrapper">
  <section class="content-header">
    <h1>
      Administrar productos
    </h1>
    <ol class="breadcrumb">
      <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>
      <li class="active">Administrar productos</li>
    </ol>
  </section>
  <section class="content">
    <div class="box">
      <div class="box-header with-border">
          <div class="row">
          <div class="col-md-6">
            <div class="productos-header-buttons">
              <button class="btn btn-primary" data-toggle="modal" data-target="#modalAgregarProducto">
                <i class="fa fa-plus"></i> Agregar producto
              </button>
              <a href="productos-stock-medio" class="btn btn-warning">
                <i class="fa fa-exclamation-triangle"></i> Stock Medio
              </a>
              <a href="productos-stock-bajo" class="btn btn-danger">
                <i class="fa fa-warning"></i> Stock Bajo
              </a>
              <a href="productos-stock-valorizado" class="btn btn-primary">
                <i class="fa fa-dollar"></i> Stock Valorizado
              </a>
            </div>
         </div>
		  <div class="col-md-3">
		      <button class="btn btn-danger" onclick="borradoMultiple()"  id="boronBorrado" style="display:none;"  id="verSeleccion">
              <i class="fa fa-file-pdf-o"> Borrado Multiple</i>
        </button>
          </div>
          <div class="col-md-3">
		  <div class="panel panel-default" id="precioPlace" style="display:none;">
		  <div class="panel-heading">
            <center><h4 id="contador"></h4>
			<button class="btn btn-primary" onclick="verProductosBorrar()"  id="detallePlace" style="display:none;" data-toggle="modal" data-target="#modalVerSeleccion">
              <i class="fa fa-file-pdf-o"> Ver Seleccion</i>
            </button>
			
			</center>
          </div>
		  </div>
		 
		  </div>
      </div>
      <div class="box-body">
        <div class="productos-columnas-selector">
          <div style="display: flex; align-items: center; flex-wrap: wrap; gap: 8px;">
            <strong>Columnas:</strong>
            <a class="toggle-vis active" data-column="1">Categoría</a>
            <span class="separator">|</span>
            <a class="toggle-vis active" data-column="2">Proveedor</a>
            <span class="separator">|</span>
            <a class="toggle-vis active" data-column="3">Descripcion</a>
            <span class="separator">|</span>
            <a class="toggle-vis active" data-column="4">STK</a>
            <span class="separator">|</span>
            <a class="toggle-vis active" data-column="5">STK TOTAL</a>
            <span class="separator">|</span>
            <a class="toggle-vis active" data-column="6">$ Compra</a>
            <span class="separator">|</span>
            <a class="toggle-vis active" data-column="7">US$ Compra</a>
            <span class="separator">|</span>
            <a class="toggle-vis active" data-column="8">IVA</a>
            <span class="separator">|</span>
            <a class="toggle-vis active" data-column="9">$ Venta</a>
          </div>
        </div>
        
        <input type="hidden" id="arrayProductosBorrarMultiple" name="arrayProductosBorrarMultiple"/>
        
        <div class="productos-table-container">
       <table class="table table-bordered table-striped dt-responsive" id="tablaProductos" width="100%">
        <thead>
         <tr>
           <th>Código</th>
           <th>Categoria</th>
           <th>Proveedor</th>
           <th>Descripción</th>
           <th>STK</th>
           <th>STK TOTAL</th>
           <th>$ Compra</th>
           <th>US$ Compra</th>
           <th>IVA</th> 
           <th>$ Venta</th>
           <th>Acciones</th>
           <th>id</th>
           <th>stk medio</th>
           <th>stk bajo</th>
         </tr>
        </thead>
        <tfoot>
         <tr>
           <th>Código</th>
           <th>Categoria</th>
           <th>Proveedor</th>
           <th>Descripción</th>
           <th>STK </th>
           <th>STK TOTAL</th>
           <th>$ Compra</th>
           <th>US$ Compra</th>
           <th>IVA</th> 
           <th>$ Venta</th>
           <th>Acciones</th>
           <th>id</th>
           <th>stk medio</th>
           <th>stk bajo</th>
         </tr>
        </tfoot>
       </table>
        </div>
       <input type="hidden" value="<?php echo $_SESSION['perfil']; ?>" id="perfilOculto">
      </div>
    </div>
  </section>
</div>

<!--=====================================
MODAL AGREGAR PRODUCTO
======================================-->
<div id="modalAgregarProducto" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <form role="form" method="post" enctype="multipart/form-data" id="formNuevoProducto">
        <!--CABEZA DEL MODAL-->
        <div class="modal-header" style="background:#3c8dbc; color:white">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Agregar producto</h4>
        </div>
        <!--CUERPO DEL MODAL-->
        <div class="modal-body">
          <div class="box-body">
            <!-- ENTRADA PARA SELECCIONAR CATEGORÍA -->
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-th"></i></span> 
                <!-- <select class="form-control " id="nuevaCategoria" name="nuevaCategoria" required onchange="llenarSubcategoria('nuevaCategoria', 'nuevaSubCategoria', 0)"> -->
                  <select class="form-control " id="nuevaCategoria" name="nuevaCategoria" required>
                  <option value="">Seleccionar categoría</option>

                  <?php
                    $item = null;
                    $valor = null;
                    $categorias = ControladorCategorias::ctrMostrarCategorias($item, $valor);
                    foreach ($categorias as $key => $value) {
                      echo '<option value="'.$value["id"].'" >'.$value["categoria"].' </option>';
                    }
                  ?>
                </select>
              </div>
            </div>

            <!-- ENTRADA PARA SELECCIONAR PROVEEDOR -->
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-th"></i></span> 
                <select class="form-control " id="nuevoProveedor" name="nuevoProveedor" required>
                  <option value="">Seleccionar proveedor</option>

                  <?php
                    $item = null;
                    $valor = null;
                    $proveedores = ControladorProveedores::ctrMostrarProveedores($item, $valor);
                    foreach ($proveedores as $key => $value) {
                      echo '<option value="'.$value["id"].'" >'.$value["nombre"].' </option>';
                    }
                  ?>

                </select>
              </div>
            </div>

            <!-- ENTRADA PARA EL CÓDIGO -->
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-code"></i></span> 
                <input type="text" autocomplete="off" class="form-control " id="nuevoCodigo" name="nuevoCodigo" placeholder="Código producto" required>
                 <!-- <span class="input-group-addon" style="background-color: #3c8dbc; color: white" id="nuevoGenerarCodigo"><i class="fa fa-retweet"></i></span> -->
              </div>
            </div>

            <!-- ENTRADA PARA LA DESCRIPCIÓN -->
             <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-product-hunt"></i></span> 
                <input type="text" autocomplete="off" class="form-control " name="nuevaDescripcion" placeholder="Ingresar descripción" required>
              </div>
            </div>

             <!-- ENTRADA PARA STOCK CABECERA-->
             <div class="row" style="padding-bottom: 10px;">
              <div class="col-xs-12" style="border-bottom-style: groove;">Stock</div>
            </div>
            <!-- ENTRADA PARA STOCK TOTAL-->
             <div class="form-group row" >
              <div class="col-xs-4">
                INDICADORES:
                <!--
                  <span class="input-group-addon"><i class="fa fa-check"></i></span> 
                  <input type="number" step="any" class="form-control " name="nuevoStock" min="0" placeholder="Stock" required>
                -->
              </div>
             <!-- ENTRADA PARA STOCK INTERMEDIO-->
              <div class="col-xs-4">
                <div class="input-group ">
                  <span class="input-group-addon" style="background: #f39c12"><i class="fa fa-check"></i></span> 
                  <input type="number" step="any" class="form-control " name="nuevoStockMedio" min="0" placeholder="Stock medio" value="5">
                </div>
              </div>
             <!-- ENTRADA PARA STOCK BAJO-->
              <div class="col-xs-4">
                <div class="input-group">
                  <span class="input-group-addon" style="background: #dd4b39"><i class="fa fa-check"></i></span> 
                  <input type="number" step="any" class="form-control " name="nuevoStockBajo" min="0" placeholder="Stock bajo" value="3">
                </div>
              </div>
            </div>

            <!-- ENTRADA PARA STOCK DEPOSITO-->
             <div class="form-group row" >
              <div class="col-xs-3">
                STK DEPOSITO
                <div class="input-group">
                  <span class="input-group-addon"><i class="fa fa-check"></i></span> 
                  <input type="number" step="any" class="form-control " name="nuevoStock" min="0" placeholder="Stock" required>
                </div>
              </div>
            </div>

            <!-- ENTRADA PARA PRECIO COMPRA -->
            <div class="row" style="padding-bottom: 10px;">
              <div class="col-xs-12" style="border-bottom-style: groove;">Compra</div>
            </div>
             <!-- ENTRADA PARA PRECIO COMPRA -->
             <div class="form-group row">
                <div class="col-xs-6">
                  <div class="input-group">
                    <span class="input-group-addon">$</span> 
                    <input type="number" class="form-control " id="nuevoPrecioCompraNeto" name="nuevoPrecioCompraNeto" step="any" min="0" placeholder="Precio compra" required>
                    <span class="input-group-addon">
                        <input type="radio" name="precioCompraMoneda" class="precioCompraPesoDolar" value="peso" checked>
                    </span>
                  </div>
                </div>

                <div class="col-xs-6">
                  <!-- CHECKBOX PARA PORCENTAJE -->
                  <div class="col-xs-6">
                    <div class="form-group">
                      <label>
                        <!-- <input type="checkbox" id="nuevoPorcentajeChk"> -->
                        <input type="checkbox" class="porcentajeChk" id="nuevoPorcentajeChk" checked>
                        Utilizar procentaje
                      </label>
                    </div>
                  </div>

                  <!-- ENTRADA PARA PORCENTAJE -->
                  <div class="col-xs-6" style="padding:0">
                    <div class="input-group">
                      <input type="number" title="Margen de ganancia (%)" class="form-control " id="nuevoPorcentajeText" name="nuevoPorcentajeText" min="0" value="40">
                      <span class="input-group-addon"><i class="fa fa-percent"></i></span>
                    </div>
                  </div>
                </div>
            </div>
            <div class="form-group row">
                <div class="col-xs-6" style="<?php echo $precioDolar; ?>">
                  <div class="input-group">
                    <span class="input-group-addon">U$S</span> 
                    <input type="number" class="form-control " id="nuevoPrecioCompraNetoDolar" name="nuevoPrecioCompraNetoDolar" step="any" min="0" placeholder="Precio compra dólar" readonly>
                    <span class="input-group-addon">
                      <input type="radio" name="precioCompraMoneda" class="precioCompraPesoDolar" value="dolar">
                    </span>
                  </div>
                </div>
            </div>

            <div class="row" style="padding-bottom: 10px;">
              <div class="col-xs-12" style="border-bottom-style: groove;">Venta</div>
            </div>

            <!-- ENTRADA PARA PRECIO VENTA -->
             <div class="form-group row">
                <!-- ENTRADA PARA PRECIO VENTA -->
                <div class="col-xs-4" style="display:none">
                  <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-arrow-down"></i></span> 
                    <input type="number" title="Precio de venta (sin I.V.A)" class="form-control " id="nuevoPrecioVenta" name="nuevoPrecioVenta" step="any" min="0" placeholder="Precio de venta" >
                  </div>
                  <br>
                </div>

                <!-- ENTRADA PARA IVA -->
                <?php if($arrayEmpresa["condicion_iva"] == 6) { ?>
                    <input type="hidden" name="nuevoIvaVenta" value="0">
                <?php } else { ?>
                    <div class="col-xs-4">
                      <div class="input-group">
                      <span class="input-group-addon"><i class="fa fa-percent"></i></span> 
                        <select name="nuevoIvaVenta" id="nuevoIvaVenta" class="form-control ">
                          <option value="">I.V.A.</option>
                          <!--<option value="0.00">0%</option>
                          <option value="2.50">2,5%</option>
                          <option value="5.00">5%</option>-->
                          <option value="10.50">10,5%</option>
                          <option value="21.00" selected>21%</option>
                          <!--<option value="27.00">27%</option>-->
                        </select>
                      </div>
                    </div>
                <?php } ?>

            </div>

            <div class="form-group row">
                <!-- ENTRADA PARA PRECIO venta minorista -->
                <div class="col-xs-3">
                  $ Venta
                  <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-usd"></i></span> 
                    <input type="number" title="$ venta publico IVA INCLUIDO" class="form-control " id="nuevoPrecioVentaIvaIncluido" name="nuevoPrecioVentaIvaIncluido" step="any" min="0" placeholder="$ publico" >
                  </div>
                </div>
            </div>

            <!-- ENTRADA PARA SUBIR FOTO -->
             <div class="form-group">
              <div class="panel">SUBIR IMAGEN</div>
              <input type="file" class="nuevaImagen" name="nuevaImagen">
              <p class="help-block">Peso máximo de la imagen 2MB</p>
              <img src="vistas/img/productos/default/anonymous.png" class="img-thumbnail previsualizar" width="100px">
            </div>
          </div>
        </div>

        <!--  PIE DEL MODAL -->
        <div class="modal-footer">
          <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Salir</button>
          <button type="submit" class="btn btn-primary">Guardar producto</button>
        </div>
      </form>
        <?php
          $objProducto -> ctrCrearProducto();
        ?>  
    </div>
  </div>
</div>

<!--=====================================
MODAL EDITAR PRODUCTO
======================================-->
<div id="modalEditarProducto" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <form role="form" method="post" enctype="multipart/form-data">
         
          
        <!--=====================================
        CABEZA DEL MODAL
        ======================================-->
        <div class="modal-header" style="background:#3c8dbc; color:white">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Editar producto</h4>
        </div>

        <input type="hidden" id="editarId" name="editarId">
        
        <!--=====================================
        CUERPO DEL MODAL
        ======================================-->
        <div class="modal-body">
          <div class="box-body">
            
            <!-- ENTRADA PARA SELECCIONAR CATEGORÍA -->
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-th"></i></span> 
                  <select class="form-control " id="editarCategoria" name="editarCategoria" required>
                  <option value="">Seleccionar categoría</option>
                  <?php
                      $item = null;
                      $valor = null;
                      $categorias = ControladorCategorias::ctrMostrarCategorias($item, $valor);
                      foreach ($categorias as $key => $value) {
                        echo '<option value="'.$value["id"].'" >'.$value["categoria"].' </option>';
                      }
                  ?>
                </select>

              </div>
            </div>

            <!-- ENTRADA PARA SELECCIONAR PROVEEDOR -->
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-th"></i></span> 
                <select class="form-control " id="editarProveedor" name="editarProveedor" required>
                  <option value="">Seleccionar proveedor</option>
                  <?php
                    $item = null;
                    $valor = null;
                    $proveedores = ControladorProveedores::ctrMostrarProveedores($item, $valor);
                    foreach ($proveedores as $key => $value) {
                      echo '<option value="'.$value["id"].'" >'.$value["nombre"].' </option>';
                    }
                  ?>
                </select>
              </div>
            </div>            

            <!-- ENTRADA PARA EL CÓDIGO -->
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-code"></i></span> 
                <input type="text" autocomplete="off" class="form-control " id="editarCodigo" name="editarCodigo" readonly required>
              </div>
            </div>

            <!-- ENTRADA PARA LA DESCRIPCIÓN -->
             <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-product-hunt"></i></span> 
                <input type="text" class="form-control " id="editarDescripcion" name="editarDescripcion" required>
              </div>
            </div>

             <!-- ENTRADA PARA STOCK -->
             <div class="row" style="padding-bottom: 10px;">
              <div class="col-xs-12" style="border-bottom-style: groove;">Stock</div>
            </div>

             <div class="form-group row" >
              <div class="col-xs-4">
                  <!--<span class="input-group-addon"><i class="fa fa-check"></i></span>--> 
                  INDICADORES:
              </div>

             <!-- ENTRADA PARA STOCK INTERMEDIO-->
              <div class="col-xs-4">
                <div class="input-group ">
                  <span class="input-group-addon" style="background: #f39c12"><i class="fa fa-check"></i></span> 
                  <input type="number" step="any" class="form-control " name="editarStockMedio" id="editarStockMedio" min="0" placeholder="Stock medio">
                </div>
              </div>

             <!-- ENTRADA PARA STOCK BAJO-->
              <div class="col-xs-4">
                <div class="input-group">
                  <span class="input-group-addon" style="background: #dd4b39"><i class="fa fa-check"></i></span> 
                  <input type="number" step="any" class="form-control " name="editarStockBajo" id="editarStockBajo" min="0" placeholder="Stock bajo">
                </div>
              </div>
            </div>

            <!-- ENTRADA PARA STOCK DEPOSITO-->
             <div class="form-group row" >
              <div class="col-xs-3">
                STK 
                <div class="input-group">
                  <span class="input-group-addon"><i class="fa fa-check"></i></span> 
                  <input type="number" step="any" class="form-control " name="editarStock" id="editarStock" min="0" placeholder="Stock Deposito" required>
                </div>
              </div>
            </div>

            <!-- ENTRADA PARA PRECIO COMPRA -->
            <div class="row" style="padding-bottom: 10px;">
              <div class="col-xs-12" style="border-bottom-style: groove;">Compra</div>
            </div>

             <!-- ENTRADA PARA PRECIO COMPRA -->
            <div class="form-group row">
                <div class="col-xs-6">
                  <div class="input-group">
                    <span class="input-group-addon">$</span> 
                    <input type="number" class="form-control " id="editarPrecioCompraNeto" name="editarPrecioCompraNeto" step="any" min="0" placeholder="Precio compra" required>
                    <span class="input-group-addon">
                        <input type="radio" id="radioPrecioCompraPeso" name="precioCompraMonedaEditar" class="precioCompraPesoDolarEditar" value="peso">
                    </span>
                  </div>
                </div>

                <div class="col-xs-6">
                  <!-- CHECKBOX PARA PORCENTAJE -->
                  <div class="col-xs-6">
                    <div class="form-group">
                      <label>
                        <!-- <input type="checkbox" id="nuevoPorcentajeChk"> -->
                        <input type="checkbox" class="porcentajeChk" id="editarPorcentajeChk" checked>
                        Utilizar procentaje
                      </label>
                    </div>
                  </div>

                  <!-- ENTRADA PARA PORCENTAJE -->
                  <div class="col-xs-6" style="padding:0">
                    <div class="input-group">
                      <input type="number" title="Margen de ganancia (%)" class="form-control " id="editarPorcentajeText" name="editarPorcentajeText" min="0" value="40">
                      <span class="input-group-addon"><i class="fa fa-percent"></i></span>
                    </div>
                  </div>
                </div>

            </div>

            <div class="form-group row">

                <div class="col-xs-6" style="<?php echo $precioDolar; ?>">
                
                  <div class="input-group">
                  
                    <span class="input-group-addon">U$S</span> 

                    <input type="number" class="form-control" id="editarPrecioCompraNetoDolar" name="editarPrecioCompraNetoDolar" step="any" min="0" placeholder="Precio compra dolar" readonly>

                    <span class="input-group-addon">

                          <input type="radio" id="radioPrecioCompraDolar" name="precioCompraMonedaEditar" class="precioCompraPesoDolarEditar" value="dolar">

                    </span>

                  </div>

                </div>

            </div>

            <div class="row" style="padding-bottom: 10px;">
              <div class="col-xs-12" style="border-bottom-style: groove;">Venta</div>
            </div>

            <div class="form-group row">

                <!-- ENTRADA PARA IVA -->
                <?php if($arrayEmpresa["condicion_iva"] == 6) { ?>
                    <input type="hidden" name="editarIvaVenta"  value="0">
                <?php } else { ?>
                    <div class="col-xs-4">
                      <div class="input-group">
                      <span class="input-group-addon"><i class="fa fa-percent"></i></span> 
                        <select name="editarIvaVenta" id="editarIvaVenta" class="form-control ">
                          <option value="">I.V.A.</option>
                          <!--<option value="0.00">0%</option>
                          <option value="2.50">2,5%</option>
                          <option value="5.00">5%</option>-->
                          <option value="10.50">10,5%</option>
                          <option value="21.00">21%</option>
                          <!--<option value="27.00">27%</option>-->
                        </select>
                      </div>
                    </div>
                <?php } ?>


            </div>

            <div class="form-group row">

                <!-- ENTRADA PARA PRECIO VENTA IVA INCLUIDO -->                
                <div class="col-xs-3">
                
                  $ Venta
                  <div class="input-group">
                  
                    <span class="input-group-addon"><i class="fa fa-usd"></i></span> 

                    <input type="number" title="Precio de venta minorista (IVA incluido)" class="form-control " id="editarPrecioVentaIvaIncluido" name="editarPrecioVentaIvaIncluido" step="any" min="0" placeholder="$ minorista" >

                  </div>

                </div>

              </div>

              <div class="row">

               <!-- ENTRADA PARA SUBIR FOTO -->
               <div class="form-group">
                <div class="panel">SUBIR IMAGEN</div>
                <input type="file" class="nuevaImagen" name="editarImagen">
                <p class="help-block">Peso máximo de la imagen 2MB</p>
                <img src="vistas/img/productos/default/anonymous.png" class="img-thumbnail previsualizar" width="100px">
                <input type="hidden" name="imagenActual" id="imagenActual">
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
         $objProducto -> ctrEditarProducto();
        ?>      
    </div>
  </div>
</div>
<!--=====================================
MODAL AGREGAR MARCA
======================================-->
<div id="modalVerSeleccion" class="modal fade" role="dialog">

  <div class="modal-dialog">
    <div class="modal-content">
      <form role="form" method="post">
        <!--=====================================
        CABEZA DEL MODAL
        ======================================-->
        <div class="modal-header" style="background:#3c8dbc; color:white">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Productos Seleccionados</h4>
        </div>
        <!--=====================================
        CUERPO DEL MODAL
        ======================================-->
        <div class="modal-body">
          <div class="box-body">
			<table class="table table-bordered table-striped dt-responsive" id="tablaProductosBorrarMultiple" width="100%">
          <thead>
		<tr>
           <th><center>Código</center></th>
		   <th><center>Descripción</center></th>
        </tr> 

        </thead>      

       </table>
  
          </div>

        </div>

        <!--=====================================
        PIE DEL MODAL
        ======================================-->

        <div class="modal-footer">

          <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Salir</button>


        </div>

      </form>

    </div>

  </div>

</div>
 <style>
.uniqueClassName {
    text-align: center;
}
</style>
<!--=====================================
MODAL EDITAR PRODUCTO - AJUSTE STOCK
======================================-->
<div id="modalEditarProductoAjusteStock" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <form role="form" method="post">
        <!--=====================================
        CABEZA DEL MODAL
        ======================================-->
        <div class="modal-header" style="background:#3c8dbc; color:white">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Editar producto - Ajuste Stock</h4>
        </div>
        <!--=====================================
        CUERPO DEL MODAL
        ======================================-->
        <div class="modal-body">
          <div class="box-body">

            <!-- ENTRADA PARA EL CÓDIGO -->
            <p id="editarCodigoAjusteStocksP"></p>
            <input type="hidden" id="editarIdAjusteStock" name="editarIdAjusteStock">
            <input type="hidden" id="editarAjusteStockAlmacen" name="editarAjusteStockAlmacen">
            <p id="editarDescripcionAjusteStock" name="editarDescripcionAjusteStock"></p>

            <!-- ENTRADA PARA STOCK -->
            <div class="row" style="padding-bottom: 10px;">
              <div class="col-xs-12" style="border-bottom-style: groove;">Stock</div>
            </div>
            <div class="row" style="padding-bottom: 10px;">
              <div class="col-xs-12">Stock Actual: <p id="editarStockActualAjuste"> </p></div>
              <input type="hidden" id="editarStockAnterior" name="editarStockAnterior">
            </div>
             <div class="form-group row" >
              <div class="col-xs-4">
                Ingresar Nuevo Stock:
                <div class="input-group">
                  <span class="input-group-addon"><i class="fa fa-check"></i></span> 
                  <input type="number" step="any" class="form-control" id="editarStockAjuste" name="editarStockAjuste" min="0" placeholder="Cantidad" required>
                </div>
              </div>
            </div>
            <!--
            <div class="form-group row" >
              <div class="col-xs-12">
                Motivo:
                <div class="input-group">
                  <span class="input-group-addon"><i class="fa fa-list-ul"></i></span> 
                  <textarea class="form-control" name="editarStockAjusteMotivo" id="editarStockAjusteMotivo" cols="3"></textarea>
                </div>
              </div>
            </div>
            -->
          </div>
        </div>
        <!--=====================================
        PIE DEL MODAL
        ======================================-->
        <div class="modal-footer">
          <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Salir</button>
          <button type="submit" class="btn btn-primary">Guardar</button>
        </div>
      </form>
        <?php
          $objProducto -> ctrIngresarAjusteStockProducto();
        ?>      
    </div>
  </div>
</div>
<?php
  $objProducto -> ctrEliminarProducto();
?>

