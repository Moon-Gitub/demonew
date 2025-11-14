<style>
.switch {
  position: relative;
  display: inline-block;
  width: 40px;
  height: 20px;
}

.switch input { 
  opacity: 0;
  width: 0;
  height: 0;
}

.slider {
  position: absolute;
  cursor: pointer;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: #ccc;
  -webkit-transition: .4s;
  transition: .4s;
}

.slider:before {
  position: absolute;
  content: "";
  height: 18px;
  width: 18px;
  left: 4px;
  bottom: 1px;
  background-color: white;
  -webkit-transition: .4s;
  transition: .4s;
}

input:checked + .slider {
  background-color: #2196F3;
}

input:focus + .slider {
  box-shadow: 0 0 1px #2196F3;
}

input:checked + .slider:before {
  -webkit-transform: translateX(15px);
  -ms-transform: translateX(15px);
  transform: translateX(15px);
}

/* Rounded sliders */
.slider.round {
  border-radius: 34px;
}

.slider.round:before {
  border-radius: 50%;
}
</style>

<div class="content-wrapper">
  <section class="content-header">
    <h1>
      Importar productos - <small>Versión Básica</small>
    </h1>
    <ol class="breadcrumb">
      <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
      <li class="active">Importar productos</li>
    </ol>
  </section>
  <section class="content">
    <div class="row">
      <!--=====================================
      EL FORMULARIO
      ======================================-->
      <div class="col-lg-6 col-xs-12">
        <div class="box box-success">
          <div class="box-header with-border">
            <div class="pull-right">
                <!-- Rounded switch -->
                <b>Avanzado</b>
                <label class="switch">
                  <input type="checkbox" id="chkCambiarVersionExcel"> 
                  <span class="slider round"> </span>
                </label>
            </div>
          </div>
          <form role="form" method="post" enctype="multipart/form-data">
            <div class="box-body">
              <div class="box">

                <!--=====================================
                ENTRADA DEL PROVEEDOR
                ======================================--> 
                <div class="form-group">
                  <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-users"></i></span>
                    <select class="form-control" id="seleccionarProveedor" name="seleccionarProveedor" required>
                    <option value="">Seleccionar proveedor</option>
                    <?php
                      $item = null;

                      $valor = null;
                      $proveedores = ControladorProveedores::ctrMostrarProveedores($item, $valor);
                       foreach ($proveedores as $key => $value) {
                         echo '<option value="'.$value["id"].'">'.$value["nombre"].' (ID: '.$value["id"].')</option>';
                       }
                    ?>
                    </select>
                  </div>
                </div>
                </div>
                <!--=====================================
                  ENTRADA PARA SUBIR ARCHIVO 
                  ======================================-->
                <div class="form-group">
                  <p class="help-block">Seleccione un archivo Excel (en formato xlsx)</p>
                  <input type="file" class="form-group nuevoArchivoExcel" name="nuevaExcel" id="nuevaExcel" required>
                  <img src="vistas/img/plantilla/excelbn.png" class="img-thumbnail previsualizar" width="100px">
                </div>
          </div>
          <div class="box-footer">
            <center><button type="submit" class="btn btn-primary">Importar archivo</button></center>
          </div>
        </form>
        <?php
           
           $importarExcel = new ControladorProductos();
           $importarExcel -> ctrImportarExcel();

        ?>
        </div>
      </div>
      <div class="col-lg-6 col-xs-12">

        <div class="box box-danger">

          <div class="box-header with-border">

            Datos del archivo importado

          </div>
          <div class="box-body">
              <div class="box">
                <?php
                    if(file_exists("vistas/dist/xlsx/detalle_datos_importados")){
                        $detalleTmp = file_get_contents("vistas/dist/xlsx/detalle_datos_importados");
                        echo $detalleTmp;
                        unlink("vistas/dist/xlsx/detalle_datos_importados");
                      } else {
                        echo '<p> No se ha cargado ningún archivo </p>';
                      }
                ?>
              </div>
          </div>        
        </div>
      </div>
    </div> 
    <div class="row">
      <div class="col-xs-12">

        <div class="box box-primary">

          <div class="box-header with-border">

            Datos importados

          </div>
            <div class="box-body">
              <div class="box">
                <table width="100" style="font-size: 10px" class="table table-bordered table-striped">

                  <thead>
                    <th>Codigo</th>
                    <!--<th title="Revisar modulo Categorias. Por defecto carga N° 1"><i class="fa fa-info-circle"></i> Categoria (numerico)</th>-->
                    <th>Descripcion</th>
                    <th>Precio compra</th>
                    <!--<th>Precio compra dolar</th>-->
                    <th title="Sino se incluye carga por defecto valor 21"><i class="fa fa-info-circle"></i> IVA</th>
                    <th>Precio venta </th>
                  </thead>

                  <tbody>
                    <?php 
                      if(file_exists("vistas/dist/xlsx/tabla_datos_importados")){
                        $tablaTmp = file_get_contents("vistas/dist/xlsx/tabla_datos_importados");
                        echo $tablaTmp;
                        unlink("vistas/dist/xlsx/tabla_datos_importados");
                      } else {
                        echo '<tr> <td colspan=15 style="text-align: center;"> Sin datos importados </td> </tr>';
                      }
                    ?>
                  </tbody>
                </table>
              </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</div>

<script>
    $("#chkCambiarVersionExcel").change(function(){
        window.location = "productos-importar-excel2";
    })
</script>