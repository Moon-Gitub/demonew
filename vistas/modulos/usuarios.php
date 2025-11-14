<?php
    $listasPrecio = $objParametros->getListasPrecio();
    $objUsuario = new ControladorUsuarios();
?>

<div class="content-wrapper">
  <section class="content-header">
    <h1>
      Administrar usuarios
    </h1>
    <ol class="breadcrumb">
      <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>
      <li class="active">Administrar usuarios</li>
    </ol>
  </section>

  <section class="content">
    <div class="box">

      <div class="box-header with-border">
        <button class="btn btn-primary" data-toggle="modal" data-target="#modalAgregarUsuario">
          Agregar usuario
        </button>
      </div>

      <div class="box-body">
       <table class="table table-bordered table-striped dt-responsive tablas" width="100%">
        <thead>
         <tr>
           <th style="width:10px">#</th>
           <th>Nombre</th>
           <th>Usuario</th>
           <th>Foto</th>
           <th>Perfil</th>
           <th>Estado</th>
           <th>Último login</th>
           <th></th>
         </tr> 
        </thead>

        <tbody>
        <?php

            $item = null;
            $valor = null;
            $usuarios = $objUsuario->ctrMostrarUsuarios($item, $valor);

            foreach ($usuarios as $key => $value){

                if($value["usuario"] != "moondesa") {
             
                    echo ' <tr>
                      <td>'.$value["id"].'</td>
                      <td>'.$value["nombre"].'</td>
                      <td>'.$value["usuario"].'</td>';
    
                      if($value["foto"] != ""){
                        echo '<td><img src="'.$value["foto"].'" class="img-thumbnail" width="40px"></td>';
                      }else{
                        echo '<td><img src="vistas/img/usuarios/default/anonymous.png" class="img-thumbnail" width="40px"></td>';
                      }
    
                      echo '<td>'.$value["perfil"].'</td>';
    
                      if($value["estado"] != 0){
                        echo '<td><button class="btn btn-success btn-xs btnActivar" idUsuario="'.$value["id"].'" estadoUsuario="0">Activado</button></td>';
                      }else{
                        echo '<td><button class="btn btn-danger btn-xs btnActivar" idUsuario="'.$value["id"].'" estadoUsuario="1">Desactivado</button></td>';
                      }             
    
                      echo '<td>'.$value["ultimo_login"].'</td>
                        <td>
                          <div class="btn-group">
                            <a class="btn btn-primary" ><i class="fa fa-cog fa-fw"></i> Acciones</a>
                            <a class="btn btn-primary dropdown-toggle" data-toggle="dropdown" href="#">
                              <span class="fa fa-caret-down" title="Toggle dropdown menu"></span>
                            </a>
                            <ul class="dropdown-menu"><li><a class="btnEditarUsuario" idUsuario="'.$value["id"].'" data-toggle="modal" data-target="#modalEditarUsuario"><i class="fa fa-pencil fa-fw"></i> Editar</a></li>';
                            if($_SESSION["perfil"] == "Administrador"){
                              echo '<li><a class="btnEliminarUsuario" idProveedor="'.$value["id"].'" idUsuario="'.$value["id"].'" fotoUsuario="'.$value["foto"].'" usuario="'.$value["usuario"].'" href="#"><i class="fa fa-times fa-fw"></i> Borrar</a></li>';
                            }
                           echo '</ul>
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
MODAL AGREGAR USUARIO
======================================-->
<div id="modalAgregarUsuario" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <form role="form" method="post" enctype="multipart/form-data">
        <!--CABEZA DEL MODAL-->
        <div class="modal-header" style="background:#3c8dbc; color:white">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Agregar usuario</h4>
        </div>
        <!--CUERPO DEL MODAL-->
        <div class="modal-body">
          <div class="box-body">
            <!-- ENTRADA PARA EL NOMBRE -->
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-user"></i></span> 
                <input type="text" autocomplete="off" class="form-control" name="nuevoNombre" placeholder="Ingresar nombre" required>
              </div>
            </div>

            <!-- ENTRADA PARA EL USUARIO -->
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-key"></i></span> 
                <input type="text" autocomplete="off" class="form-control" name="nuevoUsuario" placeholder="Ingresar usuario" id="nuevoUsuario" value="" required>
              </div>
            </div>

            <!-- ENTRADA PARA LA CONTRASEÑA -->
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-lock"></i></span> 
                <input type="password" class="form-control" name="nuevoPassword" placeholder="Ingresar contraseña" value="" required>
              </div>
            </div>

            <!-- ENTRADA PARA SELECCIONAR SU PERFIL -->
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-users"></i></span> 
                <select class="form-control" name="nuevoPerfil">
                  <option value="">Selecionar perfil</option>
                  <option value="Administrador">Administrador</option>
                  <option value="Especial">Especial</option>
                  <option value="Vendedor">Vendedor</option>
                </select>
              </div>
            </div>

            <!-- ENTRADA PARA SUCURSAL -->
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-building"></i></span> 
                <select class="form-control" name="nuevaSucursal" required>
                  <option value="">Selecionar Sucursal</option>
                  <option value="stock">Local</option>
                </select>
              </div>
            </div>
            
            <!-- ENTRADA PARA PUNTOS DE VENTA -->
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-terminal"></i></span> 
                <input type="text" autocomplete="off" class="form-control" name="nuevoPuntoVenta" placeholder="Ingresar puntos de venta separados por coma" id="nuevoPuntoVenta" required>
              </div>
            </div>
            
            <!-- ENTRADA PARA LISTAS DE PRECIOS 
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-list-ul"></i></span> 
                <select class="form-control" name="nuevaListaPrecio" required>
                  <option value="">Selecionar Lista Precio</option>
                  <option value="precio_venta">Precio Público</option>
                </select>
              </div>
            </div>-->
            
            <!-- ENTRADA PARA LISTAS DE PRECIOS -->
            <div class="panel">Listas de precio</div>
            <?php foreach ($listasPrecio as $key => $value){ ?>
                    <label for="<?php echo $key; ?>"><?php echo $value; ?></label>
                    <input type="checkbox" name="nuevoPreciosVentaUsuario[]"  value="<?php echo $key; ?>"> <br>
            <?php } ?>

            <!-- ENTRADA PARA SUBIR FOTO -->
             <div class="form-group">
              <div class="panel">SUBIR FOTO</div>
              <input type="file" class="nuevaFoto" name="nuevaFoto">
              <p class="help-block">Peso máximo de la foto 2MB</p>
              <img src="vistas/img/usuarios/default/anonymous.png" class="img-thumbnail previsualizar" width="100px">
            </div>
          </div>
        </div>

        <!--=====================================
        PIE DEL MODAL
        ======================================-->
        <div class="modal-footer">
          <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Salir</button>
          <button type="submit" class="btn btn-primary">Guardar usuario</button>
        </div>
        <?php
          $objUsuario -> ctrCrearUsuario();
        ?>
      </form>
    </div>
  </div>
</div>

<!--=====================================
MODAL EDITAR USUARIO
======================================-->
<div id="modalEditarUsuario" class="modal fade" role="dialog">
    <div class="modal-dialog">
    <div class="modal-content">
      <form role="form" method="post" enctype="multipart/form-data">
        
        <!--=====================================
        CABEZA DEL MODAL
        ======================================-->
        <div class="modal-header" style="background:#3c8dbc; color:white">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Editar usuario</h4>
        </div>

        <!--=====================================
        CUERPO DEL MODAL
        ======================================-->
        <div class="modal-body">
          <div class="box-body">

            <!-- ENTRADA PARA EL NOMBRE -->
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-user"></i></span> 
                <input type="text" autocomplete="off" class="form-control" id="editarNombre" name="editarNombre" value="" required>
              </div>
            </div>

            <!-- ENTRADA PARA EL USUARIO -->
             <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-key"></i></span> 
                <input type="text" autocomplete="off" class="form-control" id="editarUsuario" name="editarUsuario" value="" readonly>
              </div>
            </div>

            <!-- ENTRADA PARA LA CONTRASEÑA -->
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-lock"></i></span> 
                <input type="password" class="form-control" name="editarPassword" placeholder="Escriba la nueva contraseña">
                <input type="hidden" id="passwordActual" name="passwordActual">
              </div>
            </div>

            <!-- ENTRADA PARA SELECCIONAR SU PERFIL -->
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-users"></i></span> 
                <select class="form-control" name="editarPerfil">
                  <option value="" id="editarPerfil"></option>
                  <option value="Administrador">Administrador</option>
                  <option value="Especial">Especial</option>
                  <option value="Vendedor">Vendedor</option>
                </select>
              </div>
            </div>

            <!-- ENTRADA PARA SUCURSAL -->
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-building"></i></span> 
                <select title="Sucursal desde donde se va a descontar STOCK" class="form-control" name="editarSucursal" required>
                   <option value="" id="editarSucursal"></option>
                  <option value="stock">Local</option>
                </select>
              </div>
            </div>
            
            <!-- ENTRADA PARA PUNTOS DE VENTA -->
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-terminal"></i></span> 
                <input type="text" autocomplete="off" class="form-control" name="editarPuntoVenta" placeholder="Ingresar puntos de venta separados por coma" id="editarPuntoVenta" required>
              </div>
            </div>
            
            <!-- ENTRADA PARA LISTAS DE PRECIOS 
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-list-ul"></i></span> 
                <select class="form-control" name="editarListaPrecio" required>
                  <option value="" id="editarListaPrecio"></option>
                  <option value="precio_venta">Precio Público</option>
                </select>
              </div>
            </div>-->
            
            <!-- ENTRADA PARA LISTAS DE PRECIOS -->
            <div class="panel">Listas de precio</div>
            <?php foreach ($listasPrecio as $key => $value){ ?>
                    <label for="<?php echo $key; ?>"><?php echo $value; ?></label>
                    <input type="checkbox" class="preciosVentaUsuario" name="editarPreciosVentaUsuario[]"  value="<?php echo $key; ?>"> <br>
            <?php } ?>

            <!-- ENTRADA PARA SUBIR FOTO -->
             <div class="form-group">
              <div class="panel">SUBIR FOTO</div>
              <input type="file" class="nuevaFoto" name="editarFoto">
              <p class="help-block">Peso máximo de la foto 2MB</p>
              <img src="vistas/img/usuarios/default/anonymous.png" class="img-thumbnail previsualizarEditar" width="100px">
              <input type="hidden" name="fotoActual" id="fotoActual">
            </div>
            
          </div>
        </div>

        <!--=====================================
        PIE DEL MODAL
        ======================================-->
        <div class="modal-footer">
          <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Salir</button>
          <button type="submit" class="btn btn-primary">Modificar usuario</button>
        </div>

        <?php
          $objUsuario -> ctrEditarUsuario();
        ?> 
      </form>
    </div>
  </div>
</div>

<?php

   $objUsuario -> ctrBorrarUsuario();

?>