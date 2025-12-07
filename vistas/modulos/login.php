<div id="back"></div>

<div class="login-box">
  
  <div class="login-logo" style="padding-top: 50px; text-align: center;">
    <i class="fa fa-moon-o" style="font-size: 80px; color: #667eea; margin-bottom: 20px;"></i>
    <h2 style="color: #667eea; font-weight: 600; margin: 0;">
      <?php echo (isset($arrayEmpresa) && is_array($arrayEmpresa) && isset($arrayEmpresa['razon_social'])) ? $arrayEmpresa['razon_social'] : 'Sistema POS'; ?>
    </h2>
    <p style="color: #666; margin-top: 5px;">Punto de Venta</p>
  </div>

  <div class="login-box-body">

    <p class="login-box-msg"><b>Ingresar al sistema</b></p>

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

          <button type="submit" class="btn btn-primary btn-block btn-flat" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; padding: 12px; font-size: 16px; font-weight: 600;">
            <i class="fa fa-sign-in"></i> Ingresar
          </button>
        
        </div>

      </div>

      <?php

        $login = new ControladorUsuarios();
        $login -> ctrIngresoUsuario();
        
      ?>

    </form>

  </div>

</div>

<style>
/* Mejoras adicionales para login */
#back {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: -1;
}

.login-box {
    position: relative;
    z-index: 1;
}

.login-box-body {
    background: #fff !important;
    border-radius: 10px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.2);
}

.login-box-msg {
    color: #333 !important;
    font-size: 18px;
    margin-bottom: 25px;
}

@media (max-width: 767px) {
    .login-logo {
        padding-top: 30px !important;
    }
    
    .login-logo i {
        font-size: 60px !important;
    }
    
    .login-logo h2 {
        font-size: 20px !important;
    }
}
</style>
