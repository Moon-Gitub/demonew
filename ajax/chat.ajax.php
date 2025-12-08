<?php
// ajax/chat.ajax.php

// Cargar vendor autoload primero (necesario para Dotenv)
require_once "../extensiones/vendor/autoload.php";

// Cargar variables de entorno desde .env PRIMERO (si existe y si Dotenv está instalado)
if (file_exists(__DIR__ . '/../.env') && class_exists('Dotenv\Dotenv')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
    $dotenv->load();
}

// Cargar helpers (incluye función env() para leer variables)
require_once "../helpers.php";

// Iniciar sesión antes de la seguridad
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ✅ Seguridad AJAX
require_once "seguridad.ajax.php";
SeguridadAjax::inicializar();

require_once "../controladores/integraciones.controlador.php";
require_once "../modelos/integraciones.modelo.php";

class AjaxChat {
    
    public $mensaje;
    public $historial;
    
    /**
     * Enviar mensaje a N8N
     */
    public function ajaxEnviarMensaje() {
        
        // Buscar integración activa con webhook (puede ser tipo "n8n" o "webhook")
        $n8n_webhook_url = null;
        
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
                    $n8n_webhook_url = $integracion["webhook_url"];
                    break;
                }
            }
        }
        
        if (!$n8n_webhook_url) {
            echo json_encode([
                'error' => true,
                'mensaje' => 'No hay integración N8N activa configurada. Por favor, configúrala en Integraciones.'
            ]);
            return;
        }
        
        // Preparar el payload JSON para N8N
        $payload = [
            'mensaje' => $this->mensaje,
            'usuario_id' => isset($_SESSION['id']) ? $_SESSION['id'] : null,
            'usuario_nombre' => isset($_SESSION['nombre']) ? $_SESSION['nombre'] : null,
            'empresa_id' => isset($_SESSION['empresa']) ? $_SESSION['empresa'] : null,
            'timestamp' => date('Y-m-d H:i:s'),
            'historial' => $this->historial ? json_decode($this->historial, true) : []
        ];
        
        $payloadJson = json_encode($payload);
        
        // Log del payload (sin datos sensibles)
        error_log("Enviando a N8N - URL: $n8n_webhook_url, Payload: " . substr($payloadJson, 0, 500));
        
        // Inicializar cURL
        $ch = curl_init();
        
        curl_setopt_array($ch, [
            CURLOPT_URL => $n8n_webhook_url,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payloadJson,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Accept: application/json'
            ],
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => false, // En caso de problemas con SSL
            CURLOPT_SSL_VERIFYHOST => false
        ]);
        
        // Ejecutar petición
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($ch);
        
        curl_close($ch);
        
        // Manejar respuesta
        if ($curl_error) {
            error_log("Error cURL en chat: " . $curl_error);
            echo json_encode([
                'error' => true,
                'mensaje' => 'Error de conexión con N8N: ' . $curl_error
            ]);
            return;
        }
        
        if ($http_code !== 200) {
            // Log del error para debugging
            error_log("Error N8N - Código HTTP: $http_code, URL: $n8n_webhook_url, Respuesta: " . substr($response, 0, 500));
            
            $mensajeError = 'Error del servidor N8N. Código: ' . $http_code;
            
            // Intentar obtener más información del error
            $errorInfo = '';
            if ($http_code == 404) {
                $errorInfo = ' (Webhook no encontrado. Verifica la URL)';
            } else if ($http_code == 401 || $http_code == 403) {
                $errorInfo = ' (Error de autenticación. Verifica la API Key si es requerida)';
            } else if ($http_code == 500) {
                $errorInfo = ' (Error interno del servidor N8N. Revisa el workflow)';
            }
            
            echo json_encode([
                'error' => true,
                'mensaje' => $mensajeError . $errorInfo,
                'codigo' => $http_code,
                'respuesta' => substr($response, 0, 200) // Primeros 200 caracteres para no saturar
            ]);
            return;
        }
        
        // Decodificar respuesta de N8N
        $respuesta_n8n = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            // Si no es JSON, devolver como texto
            echo json_encode([
                'error' => false,
                'respuesta' => $response,
                'timestamp' => date('H:i:s')
            ]);
            return;
        }
        
        // Retornar respuesta exitosa
        echo json_encode([
            'error' => false,
            'respuesta' => $respuesta_n8n['respuesta'] ?? $respuesta_n8n['message'] ?? $respuesta_n8n['text'] ?? $response,
            'datos_adicionales' => $respuesta_n8n['datos'] ?? null,
            'timestamp' => date('H:i:s')
        ]);
    }
}

// Procesar petición
if (isset($_POST['mensaje'])) {
    $chat = new AjaxChat();
    $chat->mensaje = $_POST['mensaje'];
    $chat->historial = $_POST['historial'] ?? '[]';
    $chat->ajaxEnviarMensaje();
} else {
    echo json_encode([
        'error' => true,
        'mensaje' => 'No se recibió el mensaje'
    ]);
}

