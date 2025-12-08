<?php
// ajax/chat.ajax.php
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
        
        // Buscar integración N8N activa
        $item = "tipo";
        $valor = "n8n";
        $integraciones = ControladorIntegraciones::ctrMostrarIntegraciones($item, $valor);
        
        // Buscar la primera integración activa de tipo n8n
        $n8n_webhook_url = null;
        // Verificar que $integraciones sea un array antes de iterar
        if($integraciones && is_array($integraciones) && count($integraciones) > 0){
            foreach($integraciones as $integracion){
                if(isset($integracion["activo"]) && $integracion["activo"] == 1 && !empty($integracion["webhook_url"])){
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
        
        // Inicializar cURL
        $ch = curl_init();
        
        curl_setopt_array($ch, [
            CURLOPT_URL => $n8n_webhook_url,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Accept: application/json'
            ],
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CONNECTTIMEOUT => 10
        ]);
        
        // Ejecutar petición
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($ch);
        
        curl_close($ch);
        
        // Manejar respuesta
        if ($curl_error) {
            echo json_encode([
                'error' => true,
                'mensaje' => 'Error de conexión: ' . $curl_error
            ]);
            return;
        }
        
        if ($http_code !== 200) {
            echo json_encode([
                'error' => true,
                'mensaje' => 'Error del servidor N8N. Código: ' . $http_code,
                'respuesta' => $response
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

