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
        
        // Detectar y procesar tablas markdown
        // Buscar patrones como: | Columna1 | Columna2 | seguido de |---| y luego | dato1 | dato2 |
        const tablaPattern = /(\|[^\n]+\|(?:\s*\n\|[\s\|\-\:]+\|)?(?:\s*\n\|[^\n]+\|)+)/g;
        
        html = html.replace(tablaPattern, function(match) {
            const lineas = match.split('\n')
                .map(l => l.trim())
                .filter(l => l && l.length > 0);
            
            if (lineas.length < 2) return match;
            
            // Buscar la primera línea con datos (no separador)
            let headerIndex = -1;
            let separatorIndex = -1;
            
            for (let i = 0; i < lineas.length; i++) {
                const linea = lineas[i];
                // Detectar separador (solo tiene |, -, :, espacios)
                if (linea.match(/^[\s\|\-\:]+$/) && separatorIndex === -1) {
                    separatorIndex = i;
                } else if (linea.includes('|') && headerIndex === -1 && !linea.match(/^[\s\|\-\:]+$/)) {
                    headerIndex = i;
                }
            }
            
            if (headerIndex === -1) return match;
            
            // Extraer encabezados
            const headerLine = lineas[headerIndex];
            const headers = headerLine.split('|')
                .map(c => c.trim())
                .filter(c => c && c.length > 0);
            
            if (headers.length === 0) return match;
            
            // Construir tabla HTML
            let tablaHtml = '<div class="markdown-table-wrapper"><table class="markdown-table"><thead><tr>';
            
            headers.forEach(h => {
                // Limpiar y procesar encabezado
                let headerText = h.replace(/\*\*/g, '').trim();
                tablaHtml += `<th>${headerText}</th>`;
            });
            
            tablaHtml += '</tr></thead><tbody>';
            
            // Procesar filas de datos
            const startDataIndex = separatorIndex !== -1 ? separatorIndex + 1 : headerIndex + 1;
            
            for (let i = startDataIndex; i < lineas.length; i++) {
                const linea = lineas[i];
                // Ignorar separadores
                if (linea.match(/^[\s\|\-\:]+$/)) continue;
                
                if (linea.includes('|')) {
                    const cells = linea.split('|')
                        .map(c => c.trim())
                        .filter(c => c && c.length > 0);
                    
                    if (cells.length > 0) {
                        tablaHtml += '<tr>';
                        cells.forEach(cell => {
                            // Procesar negritas y otros formatos
                            let cellHtml = cell
                                .replace(/\*\*([^*]+)\*\*/g, '<strong>$1</strong>')
                                .replace(/\*/g, '');
                            tablaHtml += `<td>${cellHtml}</td>`;
                        });
                        tablaHtml += '</tr>';
                    }
                }
            }
            
            tablaHtml += '</tbody></table></div>';
            return tablaHtml;
        });
        
        // Convertir negritas **texto** (después de procesar tablas)
        html = html.replace(/\*\*([^*\n]+)\*\*/g, '<strong>$1</strong>');
        
        // Convertir código inline `código`
        html = html.replace(/`([^`\n]+)`/g, '<code class="inline-code">$1</code>');
        
        // Convertir bloques de código ```
        html = html.replace(/```(\w+)?\n([\s\S]*?)```/g, '<pre><code class="code-block">$2</code></pre>');
        
        // Convertir listas con viñetas
        const listasViñetas = html.split(/(<table[\s\S]*?<\/table>|<pre[\s\S]*?<\/pre>)/g);
        for (let i = 0; i < listasViñetas.length; i += 2) {
            if (listasViñetas[i]) {
                listasViñetas[i] = listasViñetas[i].replace(/^[\*\-\+]\s+(.+)$/gm, '<li>$1</li>');
                listasViñetas[i] = listasViñetas[i].replace(/(<li>.*?<\/li>\s*)+/g, function(match) {
                    if (!match.includes('<ul')) {
                        return '<ul class="markdown-list">' + match + '</ul>';
                    }
                    return match;
                });
            }
        }
        html = listasViñetas.join('');
        
        // Dividir en párrafos y procesar
        const partes = html.split(/(<table[\s\S]*?<\/table>|<pre[\s\S]*?<\/pre>|<ul[\s\S]*?<\/ul>|<ol[\s\S]*?<\/ol>)/g);
        let resultado = '';
        
        for (let i = 0; i < partes.length; i++) {
            if (partes[i].match(/^<(table|pre|ul|ol|div)/)) {
                // Es un elemento HTML, dejarlo tal cual
                resultado += partes[i];
            } else if (partes[i].trim()) {
                // Es texto, procesarlo
                let textoProcesado = partes[i]
                    .replace(/\n\n+/g, '</p><p>')
                    .replace(/\n/g, '<br>');
                
                if (!textoProcesado.startsWith('<')) {
                    textoProcesado = '<p>' + textoProcesado;
                }
                if (!textoProcesado.endsWith('>')) {
                    textoProcesado = textoProcesado + '</p>';
                }
                
                resultado += textoProcesado;
            }
        }
        
        return resultado || html;
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

