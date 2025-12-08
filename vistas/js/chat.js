// vistas/js/chat.js

$(document).ready(function() {
    
    const $chatMessages = $('#chat-messages');
    const $chatForm = $('#chat-form');
    const $chatInput = $('#chat-input');
    const $chatSendBtn = $('#chat-send-btn');
    
    // Historial de conversación (últimos 10 mensajes)
    let historial = [];
    
    // Función para agregar mensaje al chat
    function agregarMensaje(texto, esUsuario = false) {
        const hora = new Date().toLocaleTimeString('es-AR', { 
            hour: '2-digit', 
            minute: '2-digit' 
        });
        
        const mensajeClass = esUsuario ? 'user-message' : 'bot-message';
        const icono = esUsuario ? 'fa-user' : 'fa-robot';
        
        // Escapar HTML pero permitir saltos de línea
        const textoEscapado = $('<div>').text(texto).html().replace(/\n/g, '<br>');
        
        const mensajeHTML = `
            <div class="chat-message ${mensajeClass}">
                <div class="message-avatar">
                    <i class="fa ${icono}"></i>
                </div>
                <div class="message-content">
                    <p>${textoEscapado}</p>
                    <span class="message-time">${hora}</span>
                </div>
            </div>
        `;
        
        $chatMessages.append(mensajeHTML);
        scrollToBottom();
        
        // Agregar al historial (máximo 10 mensajes)
        historial.push({
            role: esUsuario ? 'user' : 'assistant',
            content: texto,
            timestamp: hora
        });
        
        if (historial.length > 10) {
            historial.shift();
        }
    }
    
    // Función para mostrar indicador de escritura
    function mostrarTyping() {
        const typingHTML = `
            <div class="chat-message bot-message" id="typing-indicator">
                <div class="message-avatar">
                    <i class="fa fa-robot"></i>
                </div>
                <div class="message-content typing-indicator">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
            </div>
        `;
        $chatMessages.append(typingHTML);
        scrollToBottom();
    }
    
    // Función para ocultar indicador de escritura
    function ocultarTyping() {
        $('#typing-indicator').remove();
    }
    
    // Función para hacer scroll al final
    function scrollToBottom() {
        $chatMessages.animate({
            scrollTop: $chatMessages[0].scrollHeight
        }, 300);
    }
    
    // Enviar mensaje
    $chatForm.on('submit', function(e) {
        e.preventDefault();
        
        const mensaje = $chatInput.val().trim();
        
        if (!mensaje || $chatSendBtn.prop('disabled')) {
            return;
        }
        
        // Agregar mensaje del usuario
        agregarMensaje(mensaje, true);
        
        // Limpiar input
        $chatInput.val('');
        $chatInput.focus();
        
        // Deshabilitar botón
        $chatSendBtn.prop('disabled', true);
        
        // Mostrar indicador de escritura
        mostrarTyping();
        
        // Preparar historial (sin incluir el mensaje que acabamos de agregar)
        const historialParaEnviar = historial.slice(0, -1).slice(-10); // Últimos 10 mensajes, sin el que acabamos de agregar
        
        // Enviar a N8N
        $.ajax({
            url: 'ajax/chat.ajax.php',
            type: 'POST',
            dataType: 'json',
            data: {
                mensaje: mensaje,
                historial: JSON.stringify(historialParaEnviar)
            },
            success: function(response) {
                ocultarTyping();
                
                if (response.error) {
                    agregarMensaje('❌ ' + response.mensaje, false);
                } else {
                    agregarMensaje(response.respuesta || 'No se recibió respuesta', false);
                }
            },
            error: function(xhr, status, error) {
                ocultarTyping();
                let mensajeError = '❌ Error de conexión. Por favor, intenta nuevamente.';
                
                // Intentar parsear el error si viene en JSON
                try {
                    const errorResponse = JSON.parse(xhr.responseText);
                    if (errorResponse.mensaje) {
                        mensajeError = '❌ ' + errorResponse.mensaje;
                    }
                } catch (e) {
                    // Si no es JSON, usar el mensaje por defecto
                }
                
                agregarMensaje(mensajeError, false);
                console.error('Error en chat:', {
                    status: xhr.status,
                    statusText: xhr.statusText,
                    error: error,
                    responseText: xhr.responseText
                });
            },
            complete: function() {
                $chatSendBtn.prop('disabled', false);
            }
        });
    });
    
    // Enviar con Enter (sin Shift)
    $chatInput.on('keypress', function(e) {
        if (e.which === 13 && !e.shiftKey) {
            e.preventDefault();
            $chatForm.submit();
        }
    });
    
    // Auto-focus en el input
    $chatInput.focus();
});

