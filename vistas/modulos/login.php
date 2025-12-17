<?php
// ========== DEBUG - INICIO ==========
// Mostrar en comentarios HTML qué valores está recibiendo
echo "<!-- ==================== DEBUG LOGIN CONFIG ==================== -->\n";
echo "<!-- arrayEmpresa existe: " . (isset($arrayEmpresa) ? 'SI' : 'NO') . " -->\n";
if(isset($arrayEmpresa)) {
    echo "<!-- login_fondo de BD: '" . (isset($arrayEmpresa['login_fondo']) ? $arrayEmpresa['login_fondo'] : 'NO EXISTE') . "' -->\n";
    echo "<!-- login_logo de BD: '" . (isset($arrayEmpresa['login_logo']) ? $arrayEmpresa['login_logo'] : 'NO EXISTE') . "' -->\n";
    echo "<!-- login_fondo_form de BD: '" . (isset($arrayEmpresa['login_fondo_form']) ? $arrayEmpresa['login_fondo_form'] : 'NO EXISTE') . "' -->\n";
    echo "<!-- login_color_boton de BD: '" . (isset($arrayEmpresa['login_color_boton']) ? $arrayEmpresa['login_color_boton'] : 'NO EXISTE') . "' -->\n";
    echo "<!-- login_fuente de BD: '" . (isset($arrayEmpresa['login_fuente']) ? $arrayEmpresa['login_fuente'] : 'NO EXISTE') . "' -->\n";
}
// ========== DEBUG - FIN ==========

// Obtener configuración del login desde $arrayEmpresa (ya disponible desde plantilla.php)
// Valores por defecto si no están configurados
$loginFondo = !empty($arrayEmpresa['login_fondo']) ? $arrayEmpresa['login_fondo'] : 'linear-gradient(rgba(0,0,0,1), rgba(0,30,50,1))';
$loginLogo = !empty($arrayEmpresa['login_logo']) ? $arrayEmpresa['login_logo'] : 'vistas/img/plantilla/logo-moon-desarrollos.png';
$loginFondoForm = !empty($arrayEmpresa['login_fondo_form']) ? $arrayEmpresa['login_fondo_form'] : 'rgba(255, 255, 255, 0.98)';
$loginColorBoton = !empty($arrayEmpresa['login_color_boton']) ? $arrayEmpresa['login_color_boton'] : '#52658d';
$loginFuente = !empty($arrayEmpresa['login_fuente']) ? $arrayEmpresa['login_fuente'] : 'Segoe UI, Tahoma, Geneva, Verdana, sans-serif';
$loginColorTextoTitulo = !empty($arrayEmpresa['login_color_texto_titulo']) ? $arrayEmpresa['login_color_texto_titulo'] : '#ffffff';

// ========== DEBUG - VALORES FINALES ==========
echo "<!-- Valor final loginFondo: '" . htmlspecialchars($loginFondo) . "' -->\n";
echo "<!-- Valor final loginLogo: '" . htmlspecialchars($loginLogo) . "' -->\n";
echo "<!-- Valor final loginFondoForm: '" . htmlspecialchars($loginFondoForm) . "' -->\n";
echo "<!-- Valor final loginColorBoton: '" . htmlspecialchars($loginColorBoton) . "' -->\n";
echo "<!-- Valor final loginFuente: '" . htmlspecialchars($loginFuente) . "' -->\n";
echo "<!-- Valor final loginColorTextoTitulo: '" . htmlspecialchars($loginColorTextoTitulo) . "' -->\n";
echo "<!-- ==================== FIN DEBUG ==================== -->\n";
// ========== DEBUG - FIN ==========

// Convertir color hexadecimal a RGB para rgba en hover
function hexToRgb($hex) {
    $hex = str_replace('#', '', $hex);
    if(strlen($hex) == 3) {
        $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
    }
    $r = hexdec(substr($hex, 0, 2));
    $g = hexdec(substr($hex, 2, 2));
    $b = hexdec(substr($hex, 4, 2));
    return "$r, $g, $b";
}
$loginColorBotonRgb = hexToRgb($loginColorBoton);
?>
<style>
/* ============================================
   DISEÑO MODERNO Y VISUAL DE LOGIN - POS MOON
   Configuración dinámica desde base de datos
   ============================================ */

* {
    box-sizing: border-box;
}

html, body, body.login-page {
    background: <?php echo $loginFondo; ?> !important;
    min-height: 100vh !important;
}

body.login-page {
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    overflow: hidden;
    padding: 20px;
    font-family: <?php echo $loginFuente; ?>;
}

/* Elemento de fondo - solo si es una URL de imagen */
body.login-page #back {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100vh;
    <?php if(strpos($loginFondo, 'url(') !== false): ?>
    background: <?php echo $loginFondo; ?> !important;
    background-size: cover !important;
    background-position: center center !important;
    background-repeat: no-repeat !important;
    <?php else: ?>
    display: none !important;
    <?php endif; ?>
    overflow: hidden;
    z-index: -1;
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
    background: transparent !important;
    padding: 0;
    position: relative;
    transition: none;
    border: none;
    box-shadow: none !important;
    filter: none;
}

.login-logo .logo-container:hover {
    transform: none;
}

.login-logo .logo-img {
    max-width: 100%;
    height: auto;
    display: block;
    width: 100%;
    max-width: 400px;
    margin: 0 auto;
    filter: none;
    box-shadow: none;
    transition: none;
}

.login-logo .logo-container:hover .logo-img {
    filter: none;
    box-shadow: none;
}

body.login-page .login-box-body {
    background: <?php echo $loginFondoForm; ?> !important;
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
    background: linear-gradient(90deg, <?php echo $loginColorBoton; ?> 0%, <?php echo $loginColorBoton; ?> 50%, <?php echo $loginColorBoton; ?> 100%);
    background-size: 200% 100%;
    animation: shimmer 3s linear infinite;
}

@keyframes shimmer {
    0% { background-position: -200% 0; }
    100% { background-position: 200% 0; }
}

.login-box-msg {
    color: <?php echo $loginColorTextoTitulo; ?> !important;
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
    background: <?php echo $loginColorBoton; ?>;
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
    padding-left: 20px;
    padding-right: 20px;
    font-size: 16px;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    background-color: #f8f9fa;
    color: #333;
    font-weight: 400;
}

.form-control:focus {
    border-color: <?php echo $loginColorBoton; ?>;
    background-color: #ffffff;
    box-shadow: 
        0 0 0 4px rgba(<?php echo $loginColorBotonRgb; ?>, 0.1),
        0 4px 12px rgba(<?php echo $loginColorBotonRgb; ?>, 0.15);
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

.form-group.has-error .form-control {
    border-color: #e74c3c;
}

.btn-login {
    background: <?php echo $loginColorBoton; ?> !important;
    border: 2px solid <?php echo $loginColorBoton; ?> !important;
    border-radius: 15px;
    height: 55px;
    font-size: 17px;
    font-weight: 700;
    color: #ffffff;
    text-transform: uppercase;
    letter-spacing: 1.5px;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 
        0 6px 20px rgba(<?php echo $loginColorBotonRgb; ?>, 0.4),
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
        0 10px 30px rgba(<?php echo $loginColorBotonRgb; ?>, 0.6),
        inset 0 1px 0 rgba(255, 255, 255, 0.3);
    background: <?php echo $loginColorBoton; ?> !important;
    border-color: <?php echo $loginColorBoton; ?> !important;
    opacity: 0.9;
}

.btn-login:active {
    transform: translateY(-1px);
    box-shadow: 
        0 4px 15px rgba(<?php echo $loginColorBotonRgb; ?>, 0.5),
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
    
    .login-logo .logo-img {
        max-width: 350px;
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
    
    .login-logo .logo-img {
        max-width: 280px;
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
        padding-left: 20px;
        padding-right: 20px;
        border-radius: 12px;
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
    .login-logo .logo-img {
        max-width: 240px;
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
    
    .login-logo .logo-img {
        max-width: 250px;
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

<!-- Elemento de fondo con imagen back2.png -->
<div id="back"></div>

<div class="login-box">
  
  <div class="login-logo">
    <div class="logo-container">
      <img src="<?php echo htmlspecialchars($loginLogo); ?>" 
           alt="MOON DESARROLLOS" 
           class="logo-img">
    </div>
  </div>

  <div class="login-box-body">
    <p class="login-box-msg">Ingresar al sistema</p>

    <form method="post" id="loginForm">
      <div class="form-group">
        <input type="text" 
               autocomplete="username" 
               class="form-control" 
               placeholder="Usuario" 
               name="ingUsuario" 
               required
               id="usuarioInput"
               aria-label="Usuario">
      </div>

      <div class="form-group">
        <input type="password" 
               autocomplete="current-password" 
               class="form-control" 
               placeholder="Contraseña" 
               name="ingPassword" 
               required
               id="passwordInput"
               aria-label="Contraseña">
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
});
</script>
