<?php
/**
 * Generador de Códigos QR para el Sistema de Cobro
 * 
 * Este archivo genera códigos QR usando un servicio de API gratuito y confiable
 * Como alternativa a Google Charts API, usa quickchart.io que es gratuito y sin límites
 */

try {
    // Obtener la URL del parámetro
    if (!isset($_GET['url']) || empty($_GET['url'])) {
        throw new Exception('URL no proporcionada');
    }
    
    $url = $_GET['url'];
    
    // Validar que sea una URL de MercadoPago
    if (strpos($url, 'mercadopago.com') === false && strpos($url, 'mercadolibre.com') === false) {
        throw new Exception('URL no válida');
    }
    
    // Usar QuickChart.io - servicio gratuito y confiable para QR
    // Alternativa: también podríamos usar qrcode.tec-it.com o api.qrserver.com
    $qrApiUrl = 'https://quickchart.io/qr';
    
    $params = http_build_query([
        'text' => $url,
        'size' => 300,
        'margin' => 2,
        'ecLevel' => 'H' // High error correction
    ]);
    
    $qrImageUrl = $qrApiUrl . '?' . $params;
    
    // Obtener la imagen del servicio
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $qrImageUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    
    $imageData = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200 && $imageData !== false) {
        // Enviar la imagen
        header('Content-Type: image/png');
        header('Cache-Control: public, max-age=3600'); // Cache de 1 hora
        echo $imageData;
    } else {
        throw new Exception('Error obteniendo QR del servicio');
    }
    
} catch (Exception $e) {
    // En caso de error, generar una imagen de error
    header('Content-Type: image/png');
    
    // Crear imagen de error simple con GD
    $image = imagecreate(300, 300);
    $bgColor = imagecolorallocate($image, 255, 240, 240);
    $textColor = imagecolorallocate($image, 220, 53, 69);
    
    imagefilledrectangle($image, 0, 0, 300, 300, $bgColor);
    
    $errorText = 'Error generando QR';
    $x = (300 - (strlen($errorText) * 8)) / 2;
    $y = 140;
    imagestring($image, 3, $x, $y, $errorText, $textColor);
    
    imagepng($image);
    imagedestroy($image);
    
    error_log("Error generando QR: " . $e->getMessage());
}

