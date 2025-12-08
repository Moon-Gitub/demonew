<footer class="main-footer">
	
	<strong>Copyright &copy; <?php echo date('Y'); ?> <a href="https://www.moondesarrollos.com" target="_blank">Moon Desarrollos</a>.</strong>

	Todos los derechos reservados.


</footer>

<!-- Burbuja flotante de Chat -->
<?php
// Verificar si hay integración N8N activa para mostrar la burbuja
if(isset($_SESSION["iniciarSesion"]) && $_SESSION["iniciarSesion"] == "ok"){
    $item = "tipo";
    $valor = "n8n";
    $integraciones = ControladorIntegraciones::ctrMostrarIntegraciones($item, $valor);
    
    $webhookUrl = null;
    // Verificar que $integraciones sea un array antes de iterar
    if($integraciones && is_array($integraciones) && count($integraciones) > 0){
        foreach($integraciones as $integracion){
            // Verificar activo (puede venir como int 1 o string "1")
            $activo = isset($integracion["activo"]) ? (int)$integracion["activo"] : 0;
            $tieneWebhook = !empty($integracion["webhook_url"]);
            
            if($activo == 1 && $tieneWebhook){
                $webhookUrl = $integracion["webhook_url"];
                break;
            }
        }
    }
    
    if($webhookUrl && (!isset($_GET["ruta"]) || $_GET["ruta"] != "chat")){
?>
<div id="chat-floating-button" style="position: fixed; bottom: 20px; right: 20px; z-index: 9998; cursor: pointer;">
    <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 12px rgba(0,0,0,0.3); transition: transform 0.3s ease;">
        <i class="fa fa-comments" style="color: white; font-size: 24px;"></i>
    </div>
    <div style="position: absolute; bottom: 70px; right: 0; background: #333; color: white; padding: 8px 12px; border-radius: 6px; white-space: nowrap; font-size: 12px; opacity: 0; transition: opacity 0.3s ease; pointer-events: none;">
        Chatea con nuestro asistente
    </div>
</div>

<style>
#chat-floating-button:hover > div:first-child {
    transform: scale(1.1);
}

#chat-floating-button:hover > div:last-child {
    opacity: 1;
}

#chat-floating-button {
    animation: pulse-chat 2s infinite;
}

@keyframes pulse-chat {
    0%, 100% {
        transform: scale(1);
    }
    50% {
        transform: scale(1.05);
    }
}
</style>

<script>
$(document).ready(function(){
    $('#chat-floating-button').on('click', function(){
        window.location.href = 'chat';
    });
});
</script>
<?php
    }
}
?>