<?php
// ========== DEBUG - INICIO ==========
// Mostrar en comentarios HTML qué valores está recibiendo
echo "<!-- ==================== DEBUG LOGIN CONFIG ==================== -->\n";
echo "<!-- arrayEmpresa existe: " . (isset($arrayEmpresa) ? 'SI' : 'NO') . " -->\n";
if(isset($arrayEmpresa)) {
    echo "<!-- login_fondo de BD: '" . (isset($arrayEmpresa['login_fondo']) ? $arrayEmpresa['login_fondo'] : 'NO EXISTE') . "' -->\n";
    echo "<!-- login_logo de BD: '" . (isset($arrayEmpresa['login_logo']) ? $arrayEmpresa['login_logo'] : 'NO EXISTE') . "' -->\n";
    echo "<!-- login_fondo_form de BD: '" . (isset($arrayEmpresa['login_fondo_form']) ? $arrayEmpresa['login_fondo_form'] : 'NO EXISTE') . "' -->\n";
echo "<!-- login_fondo_form tipo: " . (isset($arrayEmpresa['login_fondo_form']) ? gettype($arrayEmpresa['login_fondo_form']) : 'N/A') . " -->\n";
echo "<!-- login_fondo_form vacío?: " . (isset($arrayEmpresa['login_fondo_form']) && empty($arrayEmpresa['login_fondo_form']) ? 'SI' : 'NO') . " -->\n";
    echo "<!-- login_color_boton de BD: '" . (isset($arrayEmpresa['login_color_boton']) ? $arrayEmpresa['login_color_boton'] : 'NO EXISTE') . "' -->\n";
    echo "<!-- login_fuente de BD: '" . (isset($arrayEmpresa['login_fuente']) ? $arrayEmpresa['login_fuente'] : 'NO EXISTE') . "' -->\n";
}
// ========== DEBUG - FIN ==========

// Obtener configuración del login desde $arrayEmpresa (ya disponible desde plantilla.php)
// Valores por defecto si no están configurados
$loginFondo = !empty($arrayEmpresa['login_fondo']) ? $arrayEmpresa['login_fondo'] : 'linear-gradient(rgba(0,0,0,1), rgba(0,30,50,1))';
$loginLogo = !empty($arrayEmpresa['login_logo']) ? $arrayEmpresa['login_logo'] : 'vistas/img/plantilla/logo-moon-desarrollos.png';
// Leer login_fondo_form de la BD - usar isset y verificar que no sea string vacío
$loginFondoForm = (isset($arrayEmpresa['login_fondo_form']) && trim($arrayEmpresa['login_fondo_form']) !== '') 
    ? trim($arrayEmpresa['login_fondo_form']) 
    : 'rgba(255, 255, 255, 0.98)';
$loginColorBoton = !empty($arrayEmpresa['login_color_boton']) ? $arrayEmpresa['login_color_boton'] : '#52658d';
$loginFuente = !empty($arrayEmpresa['login_fuente']) ? $arrayEmpresa['login_fuente'] : 'Segoe UI, Tahoma, Geneva, Verdana, sans-serif';
$loginColorTextoTitulo = !empty($arrayEmpresa['login_color_texto_titulo']) ? $arrayEmpresa['login_color_texto_titulo'] : '#ffffff';

// ========== DEBUG - VALORES FINALES ==========
echo "<!-- Valor final loginFondo: '" . htmlspecialchars($loginFondo) . "' -->\n";
echo "<!-- Valor final loginLogo: '" . htmlspecialchars($loginLogo) . "' -->\n";
echo "<!-- Valor final loginFondoForm: '" . htmlspecialchars($loginFondoForm) . "' -->\n";
echo "<!-- Valor final loginFondoForm (raw): " . var_export($loginFondoForm, true) . " -->\n";
echo "<!-- loginFondoForm length: " . strlen($loginFondoForm) . " -->\n";
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
    flex-direction: column;
    align-items: center;
    justify-content: center;
    position: relative;
    overflow: hidden;
    padding: 30px 20px;
    font-family: <?php echo $loginFuente; ?>;
    min-height: 100vh;
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
    max-width: 400px;
    margin: 0 auto;
    position: relative;
    z-index: 1;
    animation: fadeInUp 0.5s ease-out;
    display: flex;
    flex-direction: column;
    gap: 0;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px) scale(0.98);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

.login-logo {
    text-align: center;
    margin-bottom: 0;
    padding-top: 0 !important;
    padding-bottom: 0;
    flex-shrink: 0;
    order: 1;
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
    max-width: 100%;
}

.login-logo .logo-container:hover {
    transform: none;
}

.login-logo .logo-img {
    max-width: 100%;
    height: auto;
    display: block;
    width: auto;
    max-width: 100%;
    max-height: 150px;
    margin: 0 auto;
    filter: none;
    box-shadow: none;
    transition: none;
    object-fit: contain;
    object-position: center;
}

/* Adaptación dinámica: el logo puede ser de cualquier tamaño */
.login-logo .logo-img {
    /* Permitir que el logo sea flexible */
    min-height: 40px;
}

/* Si el logo es más grande, el formulario se adapta automáticamente */
.login-logo {
    /* El espaciado se ajusta automáticamente con flexbox */
    min-height: 60px;
}

.login-logo .logo-container:hover .logo-img {
    filter: none;
    box-shadow: none;
}

/* FORZAR fondo del formulario - máxima especificidad - DISEÑO ELEGANTE Y REFINADO */
/* DEBUG: Valor aplicado: <?php echo htmlspecialchars($loginFondoForm, ENT_QUOTES, 'UTF-8'); ?> */
html body.login-page .login-box .login-box-body,
body.login-page .login-box .login-box-body,
body.login-page .login-box-body,
.login-box .login-box-body,
.login-box-body {
    background: <?php echo htmlspecialchars($loginFondoForm, ENT_QUOTES, 'UTF-8'); ?> !important;
    background-color: <?php echo htmlspecialchars($loginFondoForm, ENT_QUOTES, 'UTF-8'); ?> !important;
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border-radius: 14px;
    padding: 40px 36px !important;
    box-shadow: 
        0 8px 24px rgba(0, 0, 0, 0.15),
        0 0 0 1px rgba(135, 206, 250, 0.2),
        0 0 20px rgba(135, 206, 250, 0.1);
    border: 1px solid rgba(135, 206, 250, 0.3);
    position: relative;
    overflow: hidden;
    flex-shrink: 0;
    order: 2;
    margin-top: 28px;
    /* El formulario se adapta automáticamente al espacio disponible */
    width: 100%;
}

.login-box-body::before {
    display: none;
}

.login-box-msg {
    color: <?php echo $loginColorTextoTitulo; ?> !important;
    font-size: 24px;
    font-weight: 700;
    margin-bottom: 32px;
    text-align: center;
    letter-spacing: 0.2px;
    position: relative;
    line-height: 1.3;
}

.form-group {
    margin-bottom: 20px;
    position: relative;
}

.form-control {
    height: 48px;
    border-radius: 10px;
    border: 1px solid #d0d0d0;
    padding-left: 16px;
    padding-right: 16px;
    font-size: 14px;
    transition: all 0.2s ease;
    background-color: #ffffff;
    color: #2c3e50 !important;
    font-weight: 400;
    box-shadow: none;
}

.form-control:-webkit-autofill,
.form-control:-webkit-autofill:hover,
.form-control:-webkit-autofill:focus {
    -webkit-text-fill-color: #2c3e50 !important;
    -webkit-box-shadow: 0 0 0px 1000px #ffffff inset !important;
    box-shadow: 0 0 0px 1000px #ffffff inset !important;
}

.form-control:focus {
    border-color: #87ceeb;
    background-color: #ffffff;
    box-shadow: 0 0 0 2px rgba(135, 206, 235, 0.2);
    outline: none;
}

.form-control::placeholder {
    color: #999;
    font-weight: 400;
    transition: color 0.2s;
}

.form-control:focus::placeholder {
    color: #bbb;
}

.form-group.has-error .form-control {
    border-color: #e74c3c;
}

.btn-login {
    background: <?php echo $loginColorBoton; ?> !important;
    border: 1px solid <?php echo $loginColorBoton; ?> !important;
    border-radius: 10px;
    height: 48px;
    font-size: 14px;
    font-weight: 500;
    color: #ffffff !important;
    text-transform: none;
    letter-spacing: 0.2px;
    transition: all 0.2s ease;
    box-shadow: none;
    position: relative;
    overflow: hidden;
    margin-top: 8px;
}

.btn-login::before {
    display: none;
}

.btn-login:hover {
    background: rgba(<?php echo $loginColorBotonRgb; ?>, 0.9) !important;
    border-color: <?php echo $loginColorBoton; ?> !important;
    color: #ffffff !important;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(<?php echo $loginColorBotonRgb; ?>, 0.3);
}

.btn-login:active {
    background: rgba(<?php echo $loginColorBotonRgb; ?>, 0.8) !important;
    transform: translateY(0);
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
        max-width: 100%;
        max-height: 120px;
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
        margin-bottom: 0;
    }

    .login-logo .logo-img {
        max-width: 100%;
        max-height: 100px;
    }
    
    .login-box-body {
        margin-top: 20px;
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
        max-width: 100%;
        max-height: 80px;
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
        margin-bottom: 0;
    }

    .login-logo .logo-img {
        max-width: 100%;
        max-height: 90px;
    }
    
    .login-box-body {
        margin-top: 16px;
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
