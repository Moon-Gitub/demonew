// vistas/js/chat.js

$(document).ready(function() {
    
    const $chatMessages = $('#chat-messages');
    const $chatForm = $('#chat-form');
    const $chatInput = $('#chat-input');
    const $chatSendBtn = $('#chat-send-btn');
    
    // Historial de conversación (últimos 10 mensajes)
    let historial = [];
    
    // Función para convertir markdown a HTML
    function markdownToHtml(texto) {
        let html = texto;
        
        // Convertir tablas markdown
        html = html.replace(/\|(.+)\|/g, function(match, contenido) {
            // Detectar si es encabezado de tabla
            if (contenido.match(/^[\s\-\|:]+$/)) {
                return ''; // Ignorar separador
            }
            return match;
        });
        
        // Procesar tablas completas
        html = html.replace(/(\|[^\n]+\|(?:\n\|[^\n]+\|)+)/g, function(tabla) {
            const lineas = tabla.trim().split('\n').filter(l => l.trim());
            if (lineas.length < 2) return tabla;
            
            let tablaHtml = '<div class="markdown-table-wrapper"><table class="markdown-table">';
            
            // Primera línea es el encabezado
            const encabezados = lineas[0].split('|').filter(c => c.trim());
            tablaHtml += '<thead><tr>';
            encabezados.forEach(h => {
                tablaHtml += `<th>${h.trim()}</th>`;
            });
            tablaHtml += '</tr></thead><tbody>';
            
            // Ignorar la segunda línea (separador) y procesar filas
            for (let i = 2; i < lineas.length; i++) {
                const celdas = lineas[i].split('|').filter(c => c.trim());
                if (celdas.length > 0) {
                    tablaHtml += '<tr>';
                    celdas.forEach(c => {
                        tablaHtml += `<td>${c.trim()}</td>`;
                    });
                    tablaHtml += '</tr>';
                }
            }
            
            tablaHtml += '</tbody></table></div>';
            return tablaHtml;
        });
        
        // Convertir negritas **texto**
        html = html.replace(/\*\*([^*]+)\*\*/g, '<strong>$1</strong>');
        
        // Convertir cursivas *texto*
        html = html.replace(/(?<!\*)\*([^*]+)\*(?!\*)/g, '<em>$1</em>');
        
        // Convertir código inline `código`
        html = html.replace(/`([^`]+)`/g, '<code class="inline-code">$1</code>');
        
        // Convertir bloques de código ```
        html = html.replace(/```(\w+)?\n([\s\S]*?)```/g, '<pre><code class="code-block">$2</code></pre>');
        
        // Convertir listas con viñetas
        html = html.replace(/^[\*\-\+]\s+(.+)$/gm, '<li>$1</li>');
        html = html.replace(/(<li>.*<\/li>\n?)+/g, function(match) {
            return '<ul class="markdown-list">' + match + '</ul>';
        });
        
        // Convertir listas numeradas
        html = html.replace(/^\d+\.\s+(.+)$/gm, '<li>$1</li>');
        html = html.replace(/(<li>.*<\/li>\n?)+/g, function(match) {
            if (!match.includes('<ul')) {
                return '<ol class="markdown-list">' + match + '</ol>';
            }
            return match;
        });
        
        // Convertir saltos de línea
        html = html.replace(/\n\n/g, '</p><p>');
        html = html.replace(/\n/g, '<br>');
        
        // Envolver en párrafo si no hay tablas
        if (!html.includes('<table') && !html.includes('<ul') && !html.includes('<ol') && !html.includes('<pre')) {
            html = '<p>' + html + '</p>';
        }
        
        return html;
    }
    
    // Función para agregar mensaje al chat
    function agregarMensaje(texto, esUsuario = false) {
        const hora = new Date().toLocaleTimeString('es-AR', { 
            hour: '2-digit', 
            minute: '2-digit' 
        });
        
        const mensajeClass = esUsuario ? 'user-message' : 'bot-message';
        const icono = esUsuario ? 'fa-user' : 'fa-robot';
        
        // Convertir markdown a HTML (solo para mensajes del bot)
        const contenidoHTML = esUsuario 
            ? $('<div>').text(texto).html().replace(/\n/g, '<br>')
            : markdownToHtml(texto);
        
        const mensajeHTML = `
            <div class="chat-message ${mensajeClass}">
                <div class="message-avatar">
                    <i class="fa ${icono}"></i>
                </div>
                <div class="message-content">
                    ${contenidoHTML}
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
        // Asegurar que se elimine el indicador de todas las formas posibles
        $('#typing-indicator').remove();
        $('.typing-indicator').parent().remove();
        $chatMessages.find('#typing-indicator').remove();
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
        
        // Preparar historial (sin incluir el mensaje que acabamos de agregar)
        const historialParaEnviar = historial.slice(0, -1).slice(-10); // Últimos 10 mensajes, sin el que acabamos de agregar
        
        // NO mostrar indicador de escritura (el usuario no lo quiere)
        // mostrarTyping();
        
        // Enviar a N8N
        // Deshabilitar el loader global para el chat
        $.ajax({
            url: 'ajax/chat.ajax.php',
            type: 'POST',
            dataType: 'json',
            global: false, // Deshabilitar eventos globales (incluyendo el loader)
            data: {
                mensaje: mensaje,
                historial: JSON.stringify(historialParaEnviar)
            },
            success: function(response) {
                // Asegurar que el indicador esté oculto y el loader global también
                ocultarTyping();
                $('#loader').hide();
                $chatSendBtn.prop('disabled', false);
                
                // Verificar si la respuesta es un string (puede pasar si hay error en el servidor)
                if (typeof response === 'string') {
                    try {
                        response = JSON.parse(response);
                    } catch (e) {
                        console.error('Error parseando respuesta:', e, response);
                        agregarMensaje('❌ Error procesando la respuesta del servidor', false);
                        return;
                    }
                }
                
                if (response.error) {
                    let mensajeError = '❌ ' + (response.mensaje || 'Error desconocido');
                    // Si hay información de debug, mostrarla en consola
                    if (response.debug) {
                        console.error('Error detallado:', response.debug);
                    }
                    if (response.respuesta) {
                        console.error('Respuesta de N8N:', response.respuesta);
                    }
                    agregarMensaje(mensajeError, false);
                } else {
                    const textoRespuesta = response.respuesta || response.message || response.text || 'No se recibió respuesta';
                    agregarMensaje(textoRespuesta, false);
                }
            },
            error: function(xhr, status, error) {
                ocultarTyping();
                $('#loader').hide();
                $chatSendBtn.prop('disabled', false);
                
                let mensajeError = '❌ Error de conexión. Por favor, intenta nuevamente.';
                
                // Intentar parsear el error si viene en JSON
                try {
                    if (xhr.responseText) {
                        const errorResponse = JSON.parse(xhr.responseText);
                        if (errorResponse.mensaje) {
                            mensajeError = '❌ ' + errorResponse.mensaje;
                        } else if (errorResponse.error) {
                            mensajeError = '❌ ' + errorResponse.error;
                        }
                    }
                } catch (e) {
                    // Si no es JSON, usar el mensaje por defecto
                    console.error('Error parseando respuesta de error:', e);
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
                // Asegurar que el botón esté habilitado y el typing oculto siempre
                setTimeout(function() {
                    ocultarTyping();
                    // Asegurar que el loader global esté oculto
                    $('#loader').hide();
                }, 100);
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
    
    // Preguntas sugeridas
    const preguntasSugeridas = [
        '¿Cuánto dinero tengo en cuenta corriente?',
        '¿Qué me deben?',
        '¿Cuáles son mis productos más vendidos?',
        '¿Cuántas ventas hice este mes?',
        '¿Qué clientes tienen deuda?',
        '¿Cuál es mi stock actual?',
        '¿Cuánto facturé este mes?',
        '¿Qué productos están por vencer?'
    ];
    
    // Función para mostrar preguntas sugeridas
    function mostrarPreguntasSugeridas() {
        const $suggestedContainer = $('#suggested-questions');
        const $questionsList = $('.suggested-questions-list');
        
        // Solo mostrar si no hay mensajes del usuario aún
        if (historial.filter(m => m.role === 'user').length === 0) {
            $questionsList.empty();
            preguntasSugeridas.slice(0, 4).forEach(pregunta => {
                const $btn = $('<button>')
                    .addClass('suggested-question-btn')
                    .text(pregunta)
                    .on('click', function() {
                        $chatInput.val(pregunta);
                        $chatForm.submit();
                    });
                $questionsList.append($btn);
            });
            $suggestedContainer.show();
        } else {
            $suggestedContainer.hide();
        }
    }
    
    // Mostrar preguntas sugeridas al inicio
    mostrarPreguntasSugeridas();
    
    // Ocultar preguntas sugeridas cuando se envía un mensaje
    $chatForm.on('submit', function() {
        $('#suggested-questions').hide();
    });
});

