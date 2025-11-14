<?php
    $objProducto = new ControladorProductos();
    $listasPrecio = $objParametros->getListasPrecio();
    $precioDolar = ($objParametros->getPrecioDolar()) ? '' : 'display:none;';
?>

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
        <button class="btn btn-primary" data-toggle="modal" data-target="#modalAgregarProducto">
          Agregar producto
        </button>
        <a href="productos-stock-medio" class="btn btn-warning">
          Stock Medio
        </a>
        <a href="productos-stock-bajo" class="btn btn-danger">
          Stock Bajo
        </a>
        <a href="productos-stock-valorizado" class="btn btn-primary">
          Stock Valorizado
        </a>
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
        Columnas: 
        <a class="toggle-vis" data-column="1">Categoría</a> | 
        <a class="toggle-vis" data-column="2">Proveedor</a> | 
        <a class="toggle-vis" data-column="3">Descripcion</a> | 
        <a class="toggle-vis" data-column="4">STK</a> | 
        <a class="toggle-vis" data-column="5">STK TOTAL</a>| 
        <a class="toggle-vis" data-column="6">$ Compra</a> | 
        <a class="toggle-vis" data-column="7">US$ Compra</a> | 
        <a class="toggle-vis" data-column="8">IVA</a> | 
        <a class="toggle-vis" data-column="9">$ Venta</a>
        
        <input type="hidden" id="arrayProductosBorrarMultiple" name="arrayProductosBorrarMultiple"/>
        
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

