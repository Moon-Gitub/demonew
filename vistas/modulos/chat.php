<?php
// vistas/modulos/chat.php

require_once "../controladores/integraciones.controlador.php";
require_once "../modelos/integraciones.modelo.php";

// Buscar integración N8N activa
$item = "tipo";
$valor = "n8n";
$integraciones = ControladorIntegraciones::ctrMostrarIntegraciones($item, $valor);

$webhookUrl = null;
foreach($integraciones as $integracion){
    if($integracion["activo"] == 1 && !empty($integracion["webhook_url"])){
        $webhookUrl = $integracion["webhook_url"];
        break;
    }
}
?>

<div class="content-wrapper">
  <section class="content-header">
    <h1>
      Asistente Virtual
      <small>Chat con IA</small>
    </h1>
    <ol class="breadcrumb">
      <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>
      <li class="active">Asistente Virtual</li>
    </ol>
  </section>
  <section class="content">
    <div class="row">
      <div class="col-md-8 col-md-offset-2">
        <div class="box box-primary">
          <div class="box-header with-border">
            <h3 class="box-title">
              <i class="fa fa-comments"></i> Asistente Virtual
            </h3>
          </div>
          <div class="box-body" style="padding: 0;">
            <?php if (!$webhookUrl): ?>
              <div class="alert alert-warning" style="margin: 20px;">
                <i class="fa fa-warning"></i> 
                <strong>Webhook no configurado:</strong> 
                Por favor, configura una integración N8N activa en 
                <a href="integraciones">Integraciones</a>
              </div>
            <?php endif; ?>
            
            <!-- Área de mensajes -->
            <div id="chat-messages" style="height: 500px; overflow-y: auto; padding: 20px; background: #f9f9f9;">
              <div class="chat-message bot-message">
                <div class="message-avatar">
                  <i class="fa fa-robot"></i>
                </div>
                <div class="message-content">
                  <p>¡Hola! Soy tu asistente virtual. ¿En qué puedo ayudarte?</p>
                  <span class="message-time"><?php echo date('H:i'); ?></span>
                </div>
              </div>
            </div>
            
            <!-- Área de entrada -->
            <div class="box-footer" style="border-top: 1px solid #ddd;">
              <form id="chat-form">
                <div class="input-group">
                  <input 
                    type="text" 
                    id="chat-input" 
                    class="form-control" 
                    placeholder="Escribe tu pregunta aquí..." 
                    autocomplete="off"
                    <?php echo !$webhookUrl ? 'disabled' : ''; ?>
                  >
                  <span class="input-group-btn">
                    <button type="submit" class="btn btn-primary" id="chat-send-btn" <?php echo !$webhookUrl ? 'disabled' : ''; ?>>
                      <i class="fa fa-paper-plane"></i> Enviar
                    </button>
                  </span>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</div>

<style>
.chat-message {
  display: flex;
  margin-bottom: 20px;
  animation: fadeIn 0.3s ease-in;
}

.chat-message.user-message {
  flex-direction: row-reverse;
}

.message-avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 18px;
  flex-shrink: 0;
}

.bot-message .message-avatar {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  margin-right: 10px;
}

.user-message .message-avatar {
  background: #3c8dbc;
  color: white;
  margin-left: 10px;
}

.message-content {
  max-width: 70%;
  padding: 12px 16px;
  border-radius: 18px;
  position: relative;
}

.bot-message .message-content {
  background: white;
  border: 1px solid #e0e0e0;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.user-message .message-content {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
}

.message-content p {
  margin: 0;
  word-wrap: break-word;
  white-space: pre-wrap;
}

.message-time {
  font-size: 11px;
  opacity: 0.7;
  display: block;
  margin-top: 5px;
}

.user-message .message-time {
  color: rgba(255,255,255,0.9);
}

.bot-message .message-time {
  color: #999;
}

#chat-messages {
  scroll-behavior: smooth;
}

#chat-messages::-webkit-scrollbar {
  width: 6px;
}

#chat-messages::-webkit-scrollbar-track {
  background: #f1f1f1;
}

#chat-messages::-webkit-scrollbar-thumb {
  background: #888;
  border-radius: 3px;
}

#chat-messages::-webkit-scrollbar-thumb:hover {
  background: #555;
}

.typing-indicator {
  display: flex;
  align-items: center;
  padding: 12px 16px;
}

.typing-indicator span {
  height: 8px;
  width: 8px;
  background: #999;
  border-radius: 50%;
  display: inline-block;
  margin-right: 4px;
  animation: typing 1.4s infinite;
}

.typing-indicator span:nth-child(2) {
  animation-delay: 0.2s;
}

.typing-indicator span:nth-child(3) {
  animation-delay: 0.4s;
}

@keyframes typing {
  0%, 60%, 100% {
    transform: translateY(0);
    opacity: 0.7;
  }
  30% {
    transform: translateY(-10px);
    opacity: 1;
  }
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

#chat-input:focus {
  border-color: #667eea;
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}
</style>

<script src="vistas/js/chat.js"></script>

