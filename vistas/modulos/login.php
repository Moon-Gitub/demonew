<style>
/* ============================================
   DISEÑO MODERNO DE LOGIN - POS MOON
   Manteniendo colores del logo: #3d4751 y #52658d
   ============================================ */

body.login-page {
    background: linear-gradient(135deg, #3d4751 0%, #52658d 50%, #3d4751 100%);
    background-size: 400% 400%;
    animation: gradientShift 15s ease infinite;
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    overflow: hidden;
}

@keyframes gradientShift {
    0% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
}

/* Partículas de fondo animadas */
body.login-page::before {
    content: '';
    position: absolute;
    width: 100%;
    height: 100%;
    background-image: 
        radial-gradient(circle at 20% 50%, rgba(82, 101, 141, 0.3) 0%, transparent 50%),
        radial-gradient(circle at 80% 80%, rgba(61, 71, 81, 0.3) 0%, transparent 50%),
        radial-gradient(circle at 40% 20%, rgba(82, 101, 141, 0.2) 0%, transparent 50%);
    animation: float 20s ease-in-out infinite;
    z-index: 0;
}

@keyframes float {
    0%, 100% { transform: translateY(0px) rotate(0deg); }
    50% { transform: translateY(-20px) rotate(5deg); }
}

#back {
    display: none;
}

.login-box {
    width: 100%;
    max-width: 420px;
    margin: 0 auto;
    position: relative;
    z-index: 1;
    animation: fadeInUp 0.6s ease-out;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.login-logo {
    text-align: center;
    margin-bottom: 30px;
    padding-top: 0 !important;
}

.login-logo .logo-container {
    display: inline-block;
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(10px);
    border-radius: 20px;
    padding: 25px 40px;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
    border: 1px solid rgba(255, 255, 255, 0.2);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.login-logo .logo-container:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 40px rgba(0, 0, 0, 0.4);
}

.login-logo .logo-icon {
    font-size: 48px;
    color: #ffffff;
    margin-bottom: 10px;
    display: block;
    text-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
}

.login-logo .logo-text {
    font-size: 28px;
    font-weight: 700;
    color: #ffffff;
    letter-spacing: 2px;
    text-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
    margin: 0;
}

.login-logo .logo-subtitle {
    font-size: 14px;
    color: rgba(255, 255, 255, 0.8);
    margin-top: 5px;
    font-weight: 300;
    letter-spacing: 1px;
}

.login-box-body {
    background: rgba(255, 255, 255, 0.95) !important;
    backdrop-filter: blur(10px);
    border-radius: 20px;
    padding: 40px 35px !important;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    border: 1px solid rgba(255, 255, 255, 0.3);
}

.login-box-msg {
    color: #3d4751 !important;
    font-size: 24px;
    font-weight: 600;
    margin-bottom: 30px;
    text-align: center;
    letter-spacing: 0.5px;
}

.form-group {
    margin-bottom: 25px;
    position: relative;
}

.form-control {
    height: 50px;
    border-radius: 12px;
    border: 2px solid #e0e0e0;
    padding-left: 45px;
    font-size: 15px;
    transition: all 0.3s ease;
    background-color: #f8f9fa;
}

.form-control:focus {
    border-color: #52658d;
    background-color: #ffffff;
    box-shadow: 0 0 0 3px rgba(82, 101, 141, 0.1);
    outline: none;
}

.form-control-feedback {
    left: 15px;
    color: #52658d;
    font-size: 18px;
    line-height: 50px;
    transition: color 0.3s ease;
}

.form-group:focus-within .form-control-feedback {
    color: #52658d;
    transform: scale(1.1);
}

.form-control::placeholder {
    color: #999;
    font-weight: 400;
}

.btn-login {
    background: linear-gradient(135deg, #52658d 0%, #3d4751 100%);
    border: none;
    border-radius: 12px;
    height: 50px;
    font-size: 16px;
    font-weight: 600;
    color: #ffffff;
    text-transform: uppercase;
    letter-spacing: 1px;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(82, 101, 141, 0.4);
    position: relative;
    overflow: hidden;
}

.btn-login::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
    transition: left 0.5s;
}

.btn-login:hover::before {
    left: 100%;
}

.btn-login:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(82, 101, 141, 0.6);
    background: linear-gradient(135deg, #5a6fa0 0%, #455066 100%);
}

.btn-login:active {
    transform: translateY(0);
    box-shadow: 0 2px 10px rgba(82, 101, 141, 0.4);
}

/* Responsive */
@media (max-width: 480px) {
    .login-box {
        padding: 20px;
    }
    
    .login-box-body {
        padding: 30px 25px !important;
    }
    
    .login-logo .logo-container {
        padding: 20px 30px;
    }
    
    .login-logo .logo-icon {
        font-size: 40px;
    }
    
    .login-logo .logo-text {
        font-size: 24px;
    }
}

/* Animación de entrada para los campos */
.form-group {
    animation: slideIn 0.5s ease-out;
    animation-fill-mode: both;
}

.form-group:nth-child(1) { animation-delay: 0.1s; }
.form-group:nth-child(2) { animation-delay: 0.2s; }
.form-group:nth-child(3) { animation-delay: 0.3s; }

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateX(-20px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}
</style>

<div class="login-box">
  
  <div class="login-logo">
    <div class="logo-container">
      <i class="fa fa-moon-o logo-icon"></i>
      <h2 class="logo-text">POS | Moon</h2>
      <p class="logo-subtitle">Sistema de Gestión</p>
    </div>
  </div>

  <div class="login-box-body">
    <p class="login-box-msg">Ingresar al sistema</p>

    <form method="post" id="loginForm">
      <div class="form-group has-feedback">
        <input type="text" 
               autocomplete="off" 
               class="form-control" 
               placeholder="Usuario" 
               name="ingUsuario" 
               required
               id="usuarioInput">
        <span class="glyphicon glyphicon-user form-control-feedback"></span>
      </div>

      <div class="form-group has-feedback">
        <input type="password" 
               class="form-control" 
               placeholder="Contraseña" 
               name="ingPassword" 
               required
               id="passwordInput">
        <span class="glyphicon glyphicon-lock form-control-feedback"></span>
      </div>

      <div class="row">
        <div class="col-xs-12">
          <button type="submit" class="btn btn-block btn-login">
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

<script>
// Focus automático en el campo de usuario
$(document).ready(function(){
    $('#usuarioInput').focus();
    
    // Animación al enviar el formulario
    $('#loginForm').on('submit', function(){
        $('.btn-login').html('<i class="fa fa-spinner fa-spin"></i> Ingresando...');
        $('.btn-login').prop('disabled', true);
    });
});
</script>
