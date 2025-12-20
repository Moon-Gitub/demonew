<?php
// Verificar que los archivos de combos existan antes de cargarlos
// Usar rutas relativas desde este archivo (vistas/modulos/)
$archivoControlador = __DIR__ . "/../../controladores/combos.controlador.php";
$archivoModelo = __DIR__ . "/../../modelos/combos.modelo.php";

// Normalizar rutas para evitar problemas con barras
$archivoControlador = realpath($archivoControlador) ?: $archivoControlador;
$archivoModelo = realpath($archivoModelo) ?: $archivoModelo;

if(file_exists($archivoControlador) && file_exists($archivoModelo)){
	require_once $archivoControlador;
	require_once $archivoModelo;
} else {
	// Si los archivos no existen, mostrar mensaje y salir
	echo '<div class="content-wrapper">
		<section class="content-header">
			<h1>Módulo de Combos no disponible</h1>
		</section>
		<section class="content">
			<div class="alert alert-warning">
				<h4><i class="icon fa fa-warning"></i> Atención</h4>
				<p>El módulo de combos no está completamente instalado. Por favor, asegúrese de que todos los archivos estén presentes en el servidor.</p>
				<p><small>Rutas verificadas:<br>
				Controlador: ' . htmlspecialchars($archivoControlador) . '<br>
				Modelo: ' . htmlspecialchars($archivoModelo) . '<br>
				__DIR__: ' . htmlspecialchars(__DIR__) . '</small></p>
			</div>
		</section>
	</div>';
	exit;
}

require_once "../controladores/productos.controlador.php";
require_once "../modelos/productos.modelo.php";
require_once "../controladores/categorias.controlador.php";
require_once "../modelos/categorias.modelo.php";
?>

<div class="content-wrapper">

  <section class="content-header">
    
    <h1>
      
      Administrar Combos
    
    </h1>

    <ol class="breadcrumb">
      
      <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>
      
      <li class="active">Administrar Combos</li>
    
    </ol>

  </section>

  <section class="content">

    <div class="box">

      <div class="box-header with-border">
  
        <button class="btn btn-primary" data-toggle="modal" data-target="#modalAgregarCombo">
          
          <i class="fa fa-plus"></i> Agregar Combo

        </button>

      </div>

      <div class="box-body">
        
       <table class="table table-bordered table-striped tablas" width="100%" id="tablaCombos">
         
        <thead>
         
         <tr>
           
           <th style="width:10px">#</th>
           <th>Código</th>
           <th>Nombre</th>
           <th>Producto Base</th>
           <th>Precio Venta</th>
           <th>Productos Componentes</th>
           <th>Tipo Descuento</th>
           <th>Estado</th>
           <th>Acciones</th>

         </tr> 

        </thead>

        <tbody>

        <?php

          $item = null;
          $valor = null;

          $combos = ControladorCombos::ctrMostrarCombos($item, $valor);

          if($combos){
            foreach ($combos as $key => $value) {
              $productosCombo = ControladorCombos::ctrMostrarProductosCombo($value["id"]);
              $cantidadProductos = count($productosCombo);
              $estado = $value["activo"] == 1 ? '<span class="label label-success">Activo</span>' : '<span class="label label-danger">Inactivo</span>';
              $tipoDescuento = ucfirst(str_replace('_', ' ', $value["tipo_descuento"]));
              
              echo ' <tr>

                      <td class="text-uppercase"><b>'.$value["id"].'</b></td>
                      <td class="text-uppercase">'.$value["codigo"].'</td>
                      <td class="text-uppercase">'.$value["nombre"].'</td>
                      <td>'.($value["producto_descripcion"] ?? 'N/A').'</td>
                      <td>$'.number_format($value["precio_venta"], 2, ',', '.').'</td>
                      <td><span class="badge bg-blue">'.$cantidadProductos.' producto(s)</span></td>
                      <td>'.$tipoDescuento.'</td>
                      <td>'.$estado.'</td>

                      <td class="text-center">

                        <div class="btn-group dropup acciones-dropdown">
                          <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown">
                            <i class="fa fa-cog"></i> Acciones <span class="caret"></span>
                          </button>
                          <ul class="dropdown-menu dropdown-menu-right">
                            <li><a href="#" class="btnEditarCombo" idCombo="'.$value["id"].'" data-toggle="modal" data-target="#modalEditarCombo"><i class="fa fa-pencil"></i> Editar</a></li>
                            <li><a href="#" class="btnVerProductosCombo" idCombo="'.$value["id"].'" data-toggle="modal" data-target="#modalVerProductosCombo"><i class="fa fa-eye"></i> Ver Productos</a></li>
                            <li><a href="#" class="btnEliminarCombo" idCombo="'.$value["id"].'"><i class="fa fa-times"></i> Borrar</a></li>
                          </ul>
                        </div>  

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

<!--=====================================
MODAL AGREGAR COMBO
======================================-->
<div id="modalAgregarCombo" class="modal fade" role="dialog">
  
  <div class="modal-dialog modal-lg">

    <div class="modal-content">

      <form role="form" method="post" enctype="multipart/form-data" id="formAgregarCombo">

        <!--=====================================
        CABEZA DEL MODAL
        ======================================-->

        <div class="modal-header" style="background:#3c8dbc; color:white">

          <button type="button" class="close" data-dismiss="modal">&times;</button>

          <h4 class="modal-title">Agregar Combo</h4>

        </div>

        <!--=====================================
        CUERPO DEL MODAL
        ======================================-->

        <div class="modal-body">

          <div class="box-body">

            <!-- ENTRADA PARA CÓDIGO -->
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-barcode"></i></span> 
                <input type="text" class="form-control input-lg" name="nuevoCodigoCombo" id="nuevoCodigoCombo" placeholder="Código del combo" required>
              </div>
            </div>

            <!-- ENTRADA PARA PRODUCTO BASE -->
            <div class="form-group">
              <label>Producto Base (Producto que representa el combo)</label>
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-cube"></i></span> 
                <input type="text" class="form-control input-lg" id="autocompletarProductoCombo" placeholder="Buscar producto...">
                <input type="hidden" name="nuevoProductoCombo" id="nuevoProductoCombo">
              </div>
              <small class="help-block">Seleccione el producto que representará este combo en el sistema</small>
            </div>

            <!-- ENTRADA PARA NOMBRE -->
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-tag"></i></span> 
                <input type="text" class="form-control input-lg" name="nuevoNombreCombo" id="nuevoNombreCombo" placeholder="Nombre del combo" required>
              </div>
            </div>

            <!-- ENTRADA PARA DESCRIPCIÓN -->
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-align-left"></i></span> 
                <textarea class="form-control" name="nuevaDescripcionCombo" id="nuevaDescripcionCombo" rows="3" placeholder="Descripción del combo"></textarea>
              </div>
            </div>

            <!-- ENTRADA PARA PRECIO VENTA -->
            <div class="form-group">
              <div class="row">
                <div class="col-xs-6">
                  <label>Precio Venta</label>
                  <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-dollar"></i></span> 
                    <input type="number" step="0.01" class="form-control" name="nuevoPrecioVentaCombo" id="nuevoPrecioVentaCombo" placeholder="0.00" value="0">
                  </div>
                </div>
                <div class="col-xs-6">
                  <label>Precio Mayorista</label>
                  <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-dollar"></i></span> 
                    <input type="number" step="0.01" class="form-control" name="nuevoPrecioMayoristaCombo" id="nuevoPrecioMayoristaCombo" placeholder="0.00">
                  </div>
                </div>
              </div>
            </div>

            <!-- ENTRADA PARA TIPO IVA -->
            <div class="form-group">
              <label>Tipo IVA</label>
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-percent"></i></span> 
                <select class="form-control" name="nuevoIvaCombo" id="nuevoIvaCombo">
                  <option value="0">0%</option>
                  <option value="10.5">10.5%</option>
                  <option value="21" selected>21%</option>
                  <option value="27">27%</option>
                </select>
              </div>
            </div>

            <!-- ENTRADA PARA IMAGEN -->
            <div class="form-group">
              <label>Imagen</label>
              <input type="file" class="form-control" name="nuevaImagenCombo" id="nuevaImagenCombo" accept="image/*">
              <small class="help-block">Formatos permitidos: JPG, PNG</small>
            </div>

            <!-- ENTRADA PARA TIPO DESCUENTO -->
            <div class="form-group">
              <label>Tipo de Descuento</label>
              <select class="form-control" name="nuevoTipoDescuentoCombo" id="nuevoTipoDescuentoCombo">
                <option value="ninguno">Ninguno</option>
                <option value="global">Global (sobre el total)</option>
                <option value="por_producto">Por Producto</option>
                <option value="mixto">Mixto (Global + Por Producto)</option>
              </select>
            </div>

            <!-- ENTRADA PARA DESCUENTO GLOBAL -->
            <div class="form-group" id="grupoDescuentoGlobal" style="display:none;">
              <div class="row">
                <div class="col-xs-6">
                  <label>Descuento Global</label>
                  <div class="input-group">
                    <input type="number" step="0.01" class="form-control" name="nuevoDescuentoGlobalCombo" id="nuevoDescuentoGlobalCombo" placeholder="0.00" value="0">
                    <span class="input-group-addon" id="tipoDescuentoGlobal">%</span>
                  </div>
                </div>
                <div class="col-xs-6">
                  <label>Aplicar como</label>
                  <select class="form-control" name="nuevoAplicarDescuentoGlobalCombo" id="nuevoAplicarDescuentoGlobalCombo">
                    <option value="porcentaje">Porcentaje (%)</option>
                    <option value="monto_fijo">Monto Fijo ($)</option>
                  </select>
                </div>
              </div>
            </div>

            <!-- PRODUCTOS COMPONENTES -->
            <div class="form-group">
              <label>Productos Componentes</label>
              <div class="box box-primary">
                <div class="box-header">
                  <h3 class="box-title">Agregar productos al combo</h3>
                </div>
                <div class="box-body">
                  <div class="row">
                    <div class="col-xs-12">
                      <input type="text" class="form-control" id="autocompletarProductoComponente" placeholder="Buscar producto para agregar...">
                    </div>
                  </div>
                  <hr>
                  <div id="listaProductosComponentes">
                    <p class="text-muted">No hay productos agregados. Busque y seleccione productos para agregarlos al combo.</p>
                  </div>
                </div>
              </div>
              <input type="hidden" name="productosCombo" id="productosCombo" value="[]">
            </div>

            <!-- ACTIVO -->
            <div class="form-group">
              <label>
                <input type="checkbox" name="nuevoActivoCombo" id="nuevoActivoCombo" checked> Combo Activo
              </label>
            </div>
 
          </div>

        </div>

        <!--=====================================
        PIE DEL MODAL
        ======================================-->

        <div class="modal-footer">

          <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Salir</button>

          <button type="submit" class="btn btn-primary">Guardar Combo</button>

        </div>

        <?php

          $crearCombo = new ControladorCombos();
          $crearCombo -> ctrCrearCombo();

        ?>

      </form>

    </div>

  </div>

</div>

<!--=====================================
MODAL EDITAR COMBO
======================================-->
<div id="modalEditarCombo" class="modal fade" role="dialog">
  
  <div class="modal-dialog modal-lg">

    <div class="modal-content">

      <form role="form" method="post" enctype="multipart/form-data" id="formEditarCombo">

        <!--=====================================
        CABEZA DEL MODAL
        ======================================-->

        <div class="modal-header" style="background:#3c8dbc; color:white">

          <button type="button" class="close" data-dismiss="modal">&times;</button>

          <h4 class="modal-title">Editar Combo</h4>

        </div>

        <!--=====================================
        CUERPO DEL MODAL
        ======================================-->

        <div class="modal-body">

          <div class="box-body">

            <input type="hidden" name="idCombo" id="idCombo">
            <input type="hidden" name="editarCodigoCombo" id="editarCodigoCombo">

            <!-- ENTRADA PARA PRODUCTO BASE (SOLO LECTURA) -->
            <div class="form-group">
              <label>Producto Base</label>
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-cube"></i></span> 
                <input type="text" class="form-control input-lg" id="editarProductoComboDisplay" readonly>
                <input type="hidden" name="editarProductoCombo" id="editarProductoCombo">
              </div>
              <small class="help-block">El producto base no puede ser modificado</small>
            </div>

            <!-- ENTRADA PARA NOMBRE -->
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-tag"></i></span> 
                <input type="text" class="form-control input-lg" name="editarNombreCombo" id="editarNombreCombo" placeholder="Nombre del combo" required>
              </div>
            </div>

            <!-- ENTRADA PARA DESCRIPCIÓN -->
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-align-left"></i></span> 
                <textarea class="form-control" name="editarDescripcionCombo" id="editarDescripcionCombo" rows="3" placeholder="Descripción del combo"></textarea>
              </div>
            </div>

            <!-- ENTRADA PARA PRECIO VENTA -->
            <div class="form-group">
              <div class="row">
                <div class="col-xs-6">
                  <label>Precio Venta</label>
                  <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-dollar"></i></span> 
                    <input type="number" step="0.01" class="form-control" name="editarPrecioVentaCombo" id="editarPrecioVentaCombo" placeholder="0.00" value="0">
                  </div>
                </div>
                <div class="col-xs-6">
                  <label>Precio Mayorista</label>
                  <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-dollar"></i></span> 
                    <input type="number" step="0.01" class="form-control" name="editarPrecioMayoristaCombo" id="editarPrecioMayoristaCombo" placeholder="0.00">
                  </div>
                </div>
              </div>
            </div>

            <!-- ENTRADA PARA TIPO IVA -->
            <div class="form-group">
              <label>Tipo IVA</label>
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-percent"></i></span> 
                <select class="form-control" name="editarIvaCombo" id="editarIvaCombo">
                  <option value="0">0%</option>
                  <option value="10.5">10.5%</option>
                  <option value="21" selected>21%</option>
                  <option value="27">27%</option>
                </select>
              </div>
            </div>

            <!-- ENTRADA PARA IMAGEN -->
            <div class="form-group">
              <label>Imagen</label>
              <input type="file" class="form-control" name="editarImagenCombo" id="editarImagenCombo" accept="image/*">
              <small class="help-block">Deje vacío para mantener la imagen actual</small>
              <div id="imagenActualCombo" style="margin-top:10px;"></div>
            </div>

            <!-- ENTRADA PARA TIPO DESCUENTO -->
            <div class="form-group">
              <label>Tipo de Descuento</label>
              <select class="form-control" name="editarTipoDescuentoCombo" id="editarTipoDescuentoCombo">
                <option value="ninguno">Ninguno</option>
                <option value="global">Global (sobre el total)</option>
                <option value="por_producto">Por Producto</option>
                <option value="mixto">Mixto (Global + Por Producto)</option>
              </select>
            </div>

            <!-- ENTRADA PARA DESCUENTO GLOBAL -->
            <div class="form-group" id="grupoDescuentoGlobalEditar" style="display:none;">
              <div class="row">
                <div class="col-xs-6">
                  <label>Descuento Global</label>
                  <div class="input-group">
                    <input type="number" step="0.01" class="form-control" name="editarDescuentoGlobalCombo" id="editarDescuentoGlobalCombo" placeholder="0.00" value="0">
                    <span class="input-group-addon" id="tipoDescuentoGlobalEditar">%</span>
                  </div>
                </div>
                <div class="col-xs-6">
                  <label>Aplicar como</label>
                  <select class="form-control" name="editarAplicarDescuentoGlobalCombo" id="editarAplicarDescuentoGlobalCombo">
                    <option value="porcentaje">Porcentaje (%)</option>
                    <option value="monto_fijo">Monto Fijo ($)</option>
                  </select>
                </div>
              </div>
            </div>

            <!-- PRODUCTOS COMPONENTES -->
            <div class="form-group">
              <label>Productos Componentes</label>
              <div class="box box-primary">
                <div class="box-header">
                  <h3 class="box-title">Agregar productos al combo</h3>
                </div>
                <div class="box-body">
                  <div class="row">
                    <div class="col-xs-12">
                      <input type="text" class="form-control" id="autocompletarProductoComponenteEditar" placeholder="Buscar producto para agregar...">
                    </div>
                  </div>
                  <hr>
                  <div id="listaProductosComponentesEditar">
                    <p class="text-muted">Cargando productos...</p>
                  </div>
                </div>
              </div>
              <input type="hidden" name="productosComboEditar" id="productosComboEditar" value="[]">
            </div>

            <!-- ACTIVO -->
            <div class="form-group">
              <label>
                <input type="checkbox" name="editarActivoCombo" id="editarActivoCombo"> Combo Activo
              </label>
            </div>
 
          </div>

        </div>

        <!--=====================================
        PIE DEL MODAL
        ======================================-->

        <div class="modal-footer">

          <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Salir</button>

          <button type="submit" class="btn btn-primary">Guardar Cambios</button>

        </div>

        <?php

          $editarCombo = new ControladorCombos();
          $editarCombo -> ctrEditarCombo();

        ?>

      </form>

    </div>

  </div>

</div>

<!--=====================================
MODAL VER PRODUCTOS COMBO
======================================-->
<div id="modalVerProductosCombo" class="modal fade" role="dialog">
  
  <div class="modal-dialog">

    <div class="modal-content">

        <!--=====================================
        CABEZA DEL MODAL
        ======================================-->

        <div class="modal-header" style="background:#3c8dbc; color:white">

          <button type="button" class="close" data-dismiss="modal">&times;</button>

          <h4 class="modal-title">Productos del Combo</h4>

        </div>

        <!--=====================================
        CUERPO DEL MODAL
        ======================================-->

        <div class="modal-body">

          <div class="box-body">

            <table class="table table-bordered">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Código</th>
                  <th>Descripción</th>
                  <th>Cantidad</th>
                  <th>Precio Unit.</th>
                  <th>Descuento</th>
                </tr>
              </thead>
              <tbody id="tablaProductosCombo">
                <tr>
                  <td colspan="6" class="text-center">Cargando...</td>
                </tr>
              </tbody>
            </table>

          </div>

        </div>

        <!--=====================================
        PIE DEL MODAL
        ======================================-->

        <div class="modal-footer">

          <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>

        </div>

    </div>

  </div>

</div>

<?php

  $borrarCombo = new ControladorCombos();
  $borrarCombo -> ctrBorrarCombo();

?>
