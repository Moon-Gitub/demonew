<div id="back"></div>

<div class="login-box">
  
  <div class="login-logo" style="padding-top: 170px">

    <!--<img src="vistas/img/plantilla/imagen.png" class="img-responsive" width="100%" style="padding-top:10px"> -->
  

  </div>

  <div class="login-box-body" style="background-color:#3d4751 ">

    <p class="login-box-msg" style="color:#ffffff" ><b>Ingresar al sistema</b></p>

    <form method="post">

      <div class="form-group has-feedback">

        <input type="text" autocomplete="off" class="form-control" placeholder="Usuario" name="ingUsuario" required>
        <span class="glyphicon glyphicon-user form-control-feedback"></span>

      </div>

      <div class="form-group has-feedback">

        <input type="password" class="form-control" placeholder="Contraseña" name="ingPassword" required>
        <span class="glyphicon glyphicon-lock form-control-feedback"></span>
      
      </div>

      <div class="row">
       
        <div class="col-xs-12">

          <button type="submit" class="btn btn-block btn-flat" style="background-color:#52658d "><b style="color: #ffffff">Ingresar</b></button>
        
        </div>

      </div>

      <?php

        $login = new ControladorUsuarios();
        $login -> ctrIngresoUsuario();
        
      ?>

    </form>

  </div>

</div>
