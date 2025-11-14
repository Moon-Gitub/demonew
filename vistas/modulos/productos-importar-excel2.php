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
      Importar productos - <small>Versión Avanzada</small>
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
                  <input id="chkCambiarVersionExcel" type="checkbox" checked> 
                  <span class="slider round"> </span>
                </label>
            </div>
          </div>
          <form role="form" method="post" enctype="multipart/form-data">
            <div class="box-body">
              <div class="box">
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
            <center><button type="submit" class="btn btn-primary">Subir Archivo</button></center>
          </div>
        </form>
        <?php
           
           $importarExcel = new ControladorProductos();
           $result = $importarExcel -> ctrObtenerColumnasExcel();
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
                <form role="form" method="post">
                <?php
                    
                    if( $result ) {

                      $resultadoCabeceras = $result[0];

                      $camposProductos = ModeloProductos::mdlEstructuraTablaProductos();

                      echo '<div class="row">';
                        echo '<div class="col-md-6"><b> Base Datos </b>';
                        echo '</div>';
                        echo '<div class="col-md-6"><b> Cabecera Excel</b>';
                        echo '</div>';
                      echo '</div>';

                      for ($i=0; $i < count($resultadoCabeceras) -1; $i++) { 
                        echo '<div class="row">';
                          echo '<div class="col-md-6">';
                            echo '<select class="form-control" name="campoBaseDatos'.$i.'">';

                            echo '<option value="0">------------</option>';
                            foreach ($camposProductos as $key => $value) {
                              
                              echo '<option value="'.$value["Field"].'">'.$value["Field"].'</option>';

                            }
                            echo '</select>';
                          echo '</div>';

                          echo '<div class="col-md-6">';
                            echo '<select class="form-control" name="campoExcel'.$i.'">';
                            foreach ($resultadoCabeceras as $key => $value) {
                              
                              echo '<option value="'.$key.'">'.$value.'</option>';

                            }
                            echo '</select>';
                          echo '</div>';
                        
                        echo '</div>';                        

                      }
                    
                  }

                ?>

                <?php 
                
                if(isset($result[1])) {
                  $valorResult1 = $result[1];
                } else {
                  $valorResult1 = '';
                }

                ?>
                
                <input type="hidden" name="ubicacionArchivoExcel" value="<?php echo $valorResult1; ?>">
                <div class="box-footer">
                  <center><button type="submit" class="btn btn-success">Importar datos</button></center>
                </div>

              </form>

              <?php 

                  $importarExcel -> ctrImportarProductosExcel();

              ?>
              </div>
          </div>        
        </div>
      </div>
    </div>
  </section>
</div>

<script>
    $("#chkCambiarVersionExcel").change(function(){
        window.location = "productos-importar-excel";
    })
</script>