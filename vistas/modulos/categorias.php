<div class="app-content">

  <section class="content-header">
    
    <h1>
      
      Administrar categorías
    
    </h1>

    <ol class="breadcrumb">
      
      <li><a href="inicio"><i class="bi bi-speedometer2"></i> Inicio</a></li>
      
      <li class="active">Administrar categorías</li>
    
    </ol>

  </section>

  <section class="content">

    <div class="card">

      <div class="card-header with-border">
  
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAgregarCategoria">
          
          Agregar categoría

        </button>

      </div>

      <div class="card-body">
        
       <table class="table table-bordered table-striped dt-responsive tablas" width="100%">
         
        <thead>
         
         <tr>
           
           <th style="width:10px">#</th>
           <!-- <th>Número Categoria</th> -->
           <th>Categoria</th>
           <th>Acciones</th>

         </tr> 

        </thead>

        <tbody>

        <?php

          $item = null;
          $valor = null;

          $categorias = ControladorCategorias::ctrMostrarCategorias($item, $valor);

          foreach ($categorias as $key => $value) {
           
            echo ' <tr>

                    <td class="text-uppercase"><b>'.$value["id"].'</b></td>

                    
                    <td class="text-uppercase">'.$value["categoria"].'</td>

                    <td>

                      <div class="btn-group">
                          
                        <button title="Editar" class="btn btn-primary btnEditarCategoria" idCategoria="'.$value["id"].'" data-bs-toggle="modal" data-bs-target="#modalEditarCategoria"><i class="fa fa-pencil"></i></button>';

                        echo '<button title="Modificar precio" class="btn btn-primary btnModificarPrecioCategoria" data-bs-toggle="modal" data-bs-target="#modalModificarPrecioCategoria" idCategoria="'.$value["id"].'" nombreCategoria="'.$value["categoria"].'"><i class="fa fa-sort"></i></button>';

                        echo '<button class="btn btn-danger btnEliminarCategoria" idCategoria="'.$value["id"].'"><i class="bi bi-x"></i></button>';

                      echo '</div>  

                    </td>

                  </tr>';
          }

        ?>

        </tbody>

       </table>

      </div>

    </div>

  </section>

</div>

<!--=====================================
MODAL AGREGAR CATEGORÍA
======================================-->
<div id="modalAgregarCategoria" class="modal fade" role="dialog">
  
  <div class="modal-dialog">

    <div class="modal-content">

      <form role="form" method="post">

        <!--=====================================
        CABEZA DEL MODAL
        ======================================-->

        <div class="modal-header" style="background:#3c8dbc; color:white">

          <button type="button" class="close" data-bs-dismiss="modal">&times;</button>

          <h4 class="modal-title">Agregar categoría</h4>

        </div>

        <!--=====================================
        CUERPO DEL MODAL
        ======================================-->

        <div class="modal-body">

          <div class="card-body">

            <!-- ENTRADA PARA EL NOMBRE -->
            
            <div class="form-group">
              
              <div class="input-group">
              
                <span class="input-group-addon"><i class="fa fa-th"></i></span> 

                <input type="text" class="form-control input-lg" name="nuevaCategoria" placeholder="Ingresar categoría" required>

              </div>

            </div>
 
          </div>

        </div>

        <!--=====================================
        PIE DEL MODAL
        ======================================-->

        <div class="modal-footer">

          <button type="button" class="btn btn-default float-start" data-bs-dismiss="modal">Salir</button>

          <button type="submit" class="btn btn-primary">Guardar categoría</button>

        </div>

        <?php

          $crearCategoria = new ControladorCategorias();
          $crearCategoria -> ctrCrearCategoria();

        ?>

      </form>

    </div>

  </div>

</div>

<!--=====================================
MODAL EDITAR CATEGORÍA
======================================-->
<div id="modalEditarCategoria" class="modal fade" role="dialog">
  
  <div class="modal-dialog">

    <div class="modal-content">

      <form role="form" method="post">

        <!--=====================================
        CABEZA DEL MODAL
        ======================================-->

        <div class="modal-header" style="background:#3c8dbc; color:white">

          <button type="button" class="close" data-bs-dismiss="modal">&times;</button>

          <h4 class="modal-title">Editar categoría</h4>

        </div>

        <!--=====================================
        CUERPO DEL MODAL
        ======================================-->

        <div class="modal-body">

          <div class="card-body">

            <!-- ENTRADA PARA EL NOMBRE -->
            
            <div class="form-group">
              
              <div class="input-group">
              
                <span class="input-group-addon"><i class="fa fa-th"></i></span> 

                <input type="text" class="form-control input-lg" name="editarCategoria" id="editarCategoria" required>

                <input type="hidden"  name="idCategoria" id="idCategoria" required>
                 <input type="hidden"  name="editarIdTiendaNube" id="editarIdTiendaNube" required>
                

              </div>

            </div>
 
          </div>

        </div>

        <!--=====================================
        PIE DEL MODAL
        ======================================-->

        <div class="modal-footer">

          <button type="button" class="btn btn-default float-start" data-bs-dismiss="modal">Salir</button>

          <button type="submit" class="btn btn-primary">Guardar cambios</button>

        </div>

      <?php

          $editarCategoria = new ControladorCategorias();
          $editarCategoria -> ctrEditarCategoria();

        ?> 

      </form>

    </div>

  </div>

</div>

<!--=====================================
MODAL MODIFICAR PRECIO
======================================-->
<div id="modalModificarPrecioCategoria" class="modal fade" role="dialog">
  
  <div class="modal-dialog">

    <div class="modal-content">

      <form role="form" method="post">

        <!--=====================================
        CABEZA DEL MODAL
        ======================================-->

        <div class="modal-header" style="background:#3c8dbc; color:white">

          <button type="button" class="close" data-bs-dismiss="modal">&times;</button>

          <h4 class="modal-title">Modificar precios</h4>

        </div>

        <!--=====================================
        CUERPO DEL MODAL
        ======================================-->

        <div class="modal-body">

          <div class="card-body">

            <input type="hidden" id="idCategoriaNuevoPrecio" name="idCategoriaNuevoPrecio">

            <h4 id="nombreCategoria"></h4>

            <!-- ENTRADA PARA EL PORCENTAJE -->
            
            <div class="form-group">
              
              <div class="input-group">
              
                <span class="input-group-addon"><i class="fa fa-percent"></i></span> 

                <input type="number" class="form-control" name="nuevoModificacionPrecio" placeholder="Ingresar porcentaje" required>

              </div>

            </div>
  
          </div>

        </div>

        <!--=====================================
        PIE DEL MODAL
        ======================================-->

        <div class="modal-footer">

          <button type="button" class="btn btn-default float-start" data-bs-dismiss="modal">Salir</button>

          <button type="submit" class="btn btn-primary">Actualizar precios</button>

        </div>

        <?php

          $nuevoPrecioCategoria = new ControladorProductos();
          $nuevoPrecioCategoria -> ctrModificarPrecioCategoria();

        ?>

      </form>

    </div>

  </div>

</div>

<?php

  $borrarCategoria = new ControladorCategorias();
  $borrarCategoria -> ctrBorrarCategoria();

?>