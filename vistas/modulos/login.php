<style>
/* ============================================
   DISEÑO MODERNO Y VISUAL DE LOGIN - POS MOON
   Colores del logo: #3d4751 y #52658d
   ============================================ */

* {
    box-sizing: border-box;
}

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
    padding: 20px;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
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
    top: 0;
    left: 0;
    background-image: 
        radial-gradient(circle at 20% 50%, rgba(82, 101, 141, 0.4) 0%, transparent 50%),
        radial-gradient(circle at 80% 80%, rgba(61, 71, 81, 0.4) 0%, transparent 50%),
        radial-gradient(circle at 40% 20%, rgba(82, 101, 141, 0.3) 0%, transparent 50%),
        radial-gradient(circle at 60% 70%, rgba(61, 71, 81, 0.3) 0%, transparent 50%);
    animation: float 20s ease-in-out infinite;
    z-index: 0;
}

@keyframes float {
    0%, 100% { transform: translateY(0px) rotate(0deg); opacity: 0.8; }
    50% { transform: translateY(-30px) rotate(5deg); opacity: 1; }
}

/* Círculos decorativos flotantes */
body.login-page::after {
    content: '';
    position: absolute;
    width: 300px;
    height: 300px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(82, 101, 141, 0.2) 0%, transparent 70%);
    top: -150px;
    right: -150px;
    animation: floatCircle 25s ease-in-out infinite;
    z-index: 0;
}

@keyframes floatCircle {
    0%, 100% { transform: translate(0, 0) scale(1); }
    50% { transform: translate(-50px, 50px) scale(1.2); }
}

#back {
    display: none;
}

.login-box {
    width: 100%;
    max-width: 450px;
    margin: 0 auto;
    position: relative;
    z-index: 1;
    animation: fadeInUp 0.8s ease-out;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(40px) scale(0.95);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

.login-logo {
    text-align: center;
    margin-bottom: 40px;
    padding-top: 0 !important;
}

.login-logo .logo-container {
    display: inline-block;
    background: rgba(255, 255, 255, 0.15);
    backdrop-filter: blur(15px);
    -webkit-backdrop-filter: blur(15px);
    border-radius: 25px;
    padding: 35px 50px;
    box-shadow: 
        0 8px 32px rgba(0, 0, 0, 0.3),
        inset 0 1px 0 rgba(255, 255, 255, 0.3);
    border: 1px solid rgba(255, 255, 255, 0.25);
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    position: relative;
    overflow: hidden;
}

.login-logo .logo-container::before {
    content: '';
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
    animation: rotate 20s linear infinite;
}

@keyframes rotate {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

.login-logo .logo-container:hover {
    transform: translateY(-8px) scale(1.02);
    box-shadow: 
        0 15px 50px rgba(0, 0, 0, 0.4),
        inset 0 1px 0 rgba(255, 255, 255, 0.4);
    border-color: rgba(255, 255, 255, 0.4);
}

.login-logo .logo-icon {
    font-size: 56px;
    color: #ffffff;
    margin-bottom: 15px;
    display: block;
    text-shadow: 
        0 0 20px rgba(255, 255, 255, 0.5),
        0 4px 15px rgba(0, 0, 0, 0.3);
    position: relative;
    z-index: 1;
    animation: pulse 3s ease-in-out infinite;
}

@keyframes pulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.05); }
}

.login-logo .logo-text {
    font-size: 32px;
    font-weight: 700;
    color: #ffffff;
    letter-spacing: 3px;
    text-shadow: 
        0 0 15px rgba(255, 255, 255, 0.3),
        0 3px 10px rgba(0, 0, 0, 0.3);
    margin: 0;
    position: relative;
    z-index: 1;
}

.login-logo .logo-subtitle {
    font-size: 13px;
    color: rgba(255, 255, 255, 0.9);
    margin-top: 8px;
    font-weight: 300;
    letter-spacing: 2px;
    text-transform: uppercase;
    position: relative;
    z-index: 1;
}

.login-box-body {
    background: rgba(255, 255, 255, 0.98) !important;
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border-radius: 25px;
    padding: 45px 40px !important;
    box-shadow: 
        0 25px 70px rgba(0, 0, 0, 0.35),
        inset 0 1px 0 rgba(255, 255, 255, 0.5);
    border: 1px solid rgba(255, 255, 255, 0.4);
    position: relative;
    overflow: hidden;
}

.login-box-body::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #52658d 0%, #3d4751 50%, #52658d 100%);
    background-size: 200% 100%;
    animation: shimmer 3s linear infinite;
}

@keyframes shimmer {
    0% { background-position: -200% 0; }
    100% { background-position: 200% 0; }
}

.login-box-msg {
    color: #3d4751 !important;
    font-size: 26px;
    font-weight: 700;
    margin-bottom: 35px;
    text-align: center;
    letter-spacing: 1px;
    position: relative;
}

.login-box-msg::after {
    content: '';
    position: absolute;
    bottom: -10px;
    left: 50%;
    transform: translateX(-50%);
    width: 60px;
    height: 3px;
    background: linear-gradient(90deg, #52658d, #3d4751);
    border-radius: 2px;
}

.form-group {
    margin-bottom: 28px;
    position: relative;
}

.form-control {
    height: 55px;
    border-radius: 15px;
    border: 2px solid #e8e8e8;
    padding-left: 50px;
    padding-right: 15px;
    font-size: 16px;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    background-color: #f8f9fa;
    color: #333;
    font-weight: 400;
}

.form-control:focus {
    border-color: #52658d;
    background-color: #ffffff;
    box-shadow: 
        0 0 0 4px rgba(82, 101, 141, 0.1),
        0 4px 12px rgba(82, 101, 141, 0.15);
    outline: none;
    transform: translateY(-2px);
}

.form-control::placeholder {
    color: #aaa;
    font-weight: 400;
    transition: color 0.3s;
}

.form-control:focus::placeholder {
    color: #ccc;
}

.form-control-feedback {
    left: 18px;
    color: #52658d;
    font-size: 20px;
    line-height: 55px;
    transition: all 0.3s ease;
    z-index: 2;
}

.form-group:focus-within .form-control-feedback {
    color: #52658d;
    transform: scale(1.15);
}

.form-group.has-error .form-control {
    border-color: #e74c3c;
}

.form-group.has-error .form-control-feedback {
    color: #e74c3c;
}

.btn-login {
    background: linear-gradient(135deg, #52658d 0%, #3d4751 100%);
    border: none;
    border-radius: 15px;
    height: 55px;
    font-size: 17px;
    font-weight: 700;
    color: #ffffff;
    text-transform: uppercase;
    letter-spacing: 1.5px;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 
        0 6px 20px rgba(82, 101, 141, 0.4),
        inset 0 1px 0 rgba(255, 255, 255, 0.2);
    position: relative;
    overflow: hidden;
    margin-top: 10px;
}

.btn-login::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
    transition: left 0.6s;
}

.btn-login:hover::before {
    left: 100%;
}

.btn-login:hover {
    transform: translateY(-3px);
    box-shadow: 
        0 10px 30px rgba(82, 101, 141, 0.6),
        inset 0 1px 0 rgba(255, 255, 255, 0.3);
    background: linear-gradient(135deg, #5a6fa0 0%, #455066 100%);
}

.btn-login:active {
    transform: translateY(-1px);
    box-shadow: 
        0 4px 15px rgba(82, 101, 141, 0.5),
        inset 0 1px 0 rgba(255, 255, 255, 0.2);
}

.btn-login:disabled {
    opacity: 0.7;
    cursor: not-allowed;
    transform: none;
}

.btn-login i {
    margin-right: 8px;
}

/* Animaciones de entrada para los campos */
.form-group {
    animation: slideIn 0.6s ease-out;
    animation-fill-mode: both;
}

.form-group:nth-child(1) { animation-delay: 0.1s; }
.form-group:nth-child(2) { animation-delay: 0.2s; }
.form-group:nth-child(3) { animation-delay: 0.3s; }

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateX(-30px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

/* Mensajes de error/éxito */
.alert {
    border-radius: 12px;
    margin-bottom: 20px;
    padding: 15px 20px;
    border: none;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    animation: slideDown 0.4s ease-out;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* ============================================
   RESPONSIVE DESIGN
   ============================================ */

/* Tablets */
@media (max-width: 768px) {
    body.login-page {
        padding: 15px;
    }
    
    .login-box {
        max-width: 100%;
    }
    
    .login-logo .logo-container {
        padding: 30px 40px;
    }
    
    .login-logo .logo-icon {
        font-size: 48px;
    }
    
    .login-logo .logo-text {
        font-size: 28px;
    }
    
    .login-box-body {
        padding: 35px 30px !important;
    }
    
    .login-box-msg {
        font-size: 24px;
    }
}

/* Móviles */
@media (max-width: 480px) {
    body.login-page {
        padding: 10px;
    }
    
    .login-box {
        max-width: 100%;
    }
    
    .login-logo {
        margin-bottom: 30px;
    }
    
    .login-logo .logo-container {
        padding: 25px 30px;
        border-radius: 20px;
    }
    
    .login-logo .logo-icon {
        font-size: 42px;
        margin-bottom: 12px;
    }
    
    .login-logo .logo-text {
        font-size: 24px;
        letter-spacing: 2px;
    }
    
    .login-logo .logo-subtitle {
        font-size: 11px;
        letter-spacing: 1px;
    }
    
    .login-box-body {
        padding: 30px 25px !important;
        border-radius: 20px;
    }
    
    .login-box-msg {
        font-size: 22px;
        margin-bottom: 30px;
    }
    
    .form-control {
        height: 50px;
        font-size: 15px;
        padding-left: 45px;
        border-radius: 12px;
    }
    
    .form-control-feedback {
        left: 15px;
        font-size: 18px;
        line-height: 50px;
    }
    
    .btn-login {
        height: 50px;
        font-size: 15px;
        letter-spacing: 1px;
        border-radius: 12px;
    }
    
    .form-group {
        margin-bottom: 22px;
    }
}

/* Móviles pequeños */
@media (max-width: 360px) {
    .login-logo .logo-container {
        padding: 20px 25px;
    }
    
    .login-logo .logo-icon {
        font-size: 38px;
    }
    
    .login-logo .logo-text {
        font-size: 20px;
    }
    
    .login-box-body {
        padding: 25px 20px !important;
    }
    
    .login-box-msg {
        font-size: 20px;
    }
}

/* Orientación landscape en móviles */
@media (max-height: 600px) and (orientation: landscape) {
    body.login-page {
        align-items: flex-start;
        padding-top: 20px;
    }
    
    .login-logo {
        margin-bottom: 20px;
    }
    
    .login-logo .logo-container {
        padding: 20px 35px;
    }
    
    .login-logo .logo-icon {
        font-size: 40px;
        margin-bottom: 8px;
    }
    
    .login-logo .logo-text {
        font-size: 22px;
    }
    
    .login-box-body {
        padding: 25px 30px !important;
    }
    
    .form-group {
        margin-bottom: 18px;
    }
    
    .form-control {
        height: 45px;
    }
    
    .form-control-feedback {
        line-height: 45px;
    }
    
    .btn-login {
        height: 45px;
    }
}

/* Mejoras de accesibilidad */
@media (prefers-reduced-motion: reduce) {
    *,
    *::before,
    *::after {
        animation-duration: 0.01ms !important;
        animation-iteration-count: 1 !important;
        transition-duration: 0.01ms !important;
    }
}

/* Soporte para modo oscuro del sistema */
@media (prefers-color-scheme: dark) {
    .login-box-body {
        background: rgba(30, 30, 30, 0.95) !important;
    }
    
    .form-control {
        background-color: #2a2a2a;
        color: #ffffff;
        border-color: #444;
    }
    
    .form-control:focus {
        background-color: #333;
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
               autocomplete="username" 
               class="form-control" 
               placeholder="Usuario" 
               name="ingUsuario" 
               required
               id="usuarioInput"
               aria-label="Usuario">
        <span class="glyphicon glyphicon-user form-control-feedback"></span>
      </div>

      <div class="form-group has-feedback">
        <input type="password" 
               autocomplete="current-password" 
               class="form-control" 
               placeholder="Contraseña" 
               name="ingPassword" 
               required
               id="passwordInput"
               aria-label="Contraseña">
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
// Focus automático y mejoras de UX
$(document).ready(function(){
    // Focus automático en el campo de usuario
    $('#usuarioInput').focus();
    
    // Animación al enviar el formulario
    $('#loginForm').on('submit', function(e){
        var $btn = $('.btn-login');
        var originalHtml = $btn.html();
        
        $btn.html('<i class="fa fa-spinner fa-spin"></i> Ingresando...');
        $btn.prop('disabled', true);
        
        // Si hay error, restaurar el botón después de 2 segundos
        setTimeout(function(){
            if($('.alert-danger').length > 0) {
                $btn.html(originalHtml);
                $btn.prop('disabled', false);
            }
        }, 2000);
    });
    
    // Efecto de validación en tiempo real
    $('#usuarioInput, #passwordInput').on('blur', function(){
        if($(this).val().trim() !== '') {
            $(this).closest('.form-group').removeClass('has-error').addClass('has-success');
        }
    });
    
    // Animación de entrada para el logo
    $('.logo-container').css('animation', 'fadeInUp 0.8s ease-out');
});
</script>
