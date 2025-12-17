<?php
// vistas/modulos/chat.php

// Buscar integración activa con webhook (puede ser tipo "n8n" o "webhook")
// Primero buscar por "n8n", luego por "webhook", o buscar todas y filtrar
$webhookUrl = null;

// Buscar primero por tipo "n8n"
$item = "tipo";
$valor = "n8n";
$integraciones = ControladorIntegraciones::ctrMostrarIntegraciones($item, $valor);

// Si no encuentra, buscar por tipo "webhook"
if(!$integraciones || !is_array($integraciones) || count($integraciones) == 0){
    $valor = "webhook";
    $integraciones = ControladorIntegraciones::ctrMostrarIntegraciones($item, $valor);
}

// Si aún no encuentra, buscar todas las integraciones activas con webhook
if(!$integraciones || !is_array($integraciones) || count($integraciones) == 0){
    $todas = ControladorIntegraciones::ctrMostrarIntegraciones(null, null);
    if($todas && is_array($todas)){
        $integraciones = array_filter($todas, function($int) {
            $activo = isset($int["activo"]) ? (int)$int["activo"] : 0;
            return $activo == 1 && !empty($int["webhook_url"]);
        });
    }
}

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
            
            <!-- Preguntas sugeridas -->
            <div id="suggested-questions" style="padding: 15px 20px; background: #f8f9fa; border-top: 1px solid #e0e0e0; display: none;">
              <div style="font-size: 12px; color: #666; margin-bottom: 10px; font-weight: 600;">
                <i class="fa fa-lightbulb-o"></i> Preguntas sugeridas:
              </div>
              <div class="suggested-questions-list" style="display: flex; flex-wrap: wrap; gap: 8px;">
                <!-- Las preguntas se agregarán dinámicamente -->
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
  margin: 0 0 8px 0;
  word-wrap: break-word;
  white-space: pre-wrap;
}

.message-content p:last-child {
  margin-bottom: 0;
}

/* Estilos para tablas markdown */
.markdown-table-wrapper {
  margin: 12px 0;
  overflow-x: auto;
  border-radius: 8px;
}

.markdown-table {
  width: 100%;
  border-collapse: collapse;
  background: white;
  box-shadow: 0 2px 8px rgba(0,0,0,0.08);
  border-radius: 8px;
  overflow: hidden;
  min-width: 200px;
}

.markdown-table thead {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
}

.markdown-table th {
  padding: 12px 16px;
  text-align: left;
  font-weight: 600;
  font-size: 13px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.markdown-table tbody tr {
  border-bottom: 1px solid #f0f0f0;
  transition: background 0.2s;
}

.markdown-table tbody tr:hover {
  background: #f8f9fa;
}

.markdown-table tbody tr:last-child {
  border-bottom: none;
}

.markdown-table td {
  padding: 10px 16px;
  font-size: 14px;
  color: #333;
}

.markdown-table tbody tr:nth-child(even) {
  background: #fafafa;
}

.markdown-table tbody tr:nth-child(even):hover {
  background: #f0f0f0;
}

/* Estilos para listas markdown */
.markdown-list {
  margin: 10px 0;
  padding-left: 20px;
}

.markdown-list li {
  margin: 6px 0;
  line-height: 1.6;
}

/* Estilos para código */
.inline-code {
  background: #f4f4f4;
  padding: 2px 6px;
  border-radius: 4px;
  font-family: 'Courier New', monospace;
  font-size: 0.9em;
  color: #e83e8c;
  border: 1px solid #e0e0e0;
}

.code-block {
  background: #2d2d2d;
  color: #f8f8f2;
  padding: 16px;
  border-radius: 8px;
  overflow-x: auto;
  font-family: 'Courier New', monospace;
  font-size: 13px;
  line-height: 1.5;
  margin: 12px 0;
  display: block;
}

pre {
  margin: 12px 0;
  border-radius: 8px;
  overflow: hidden;
}

/* Estilos para preguntas sugeridas */
.suggested-question-btn {
  background: white;
  border: 1.5px solid #667eea;
  color: #667eea;
  padding: 8px 14px;
  border-radius: 20px;
  font-size: 13px;
  cursor: pointer;
  transition: all 0.2s;
  white-space: nowrap;
  font-weight: 500;
}

.suggested-question-btn:hover {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  transform: translateY(-1px);
  box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3);
}

.suggested-question-btn:active {
  transform: translateY(0);
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

