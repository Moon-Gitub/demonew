<?php

/**
 * MODELO DE UPLOAD SEGURO
 * 
 * Funciones para validar y procesar archivos subidos de forma segura
 */

class ModeloUpload {

    const ALLOWED_TYPES = ['image/jpeg', 'image/png', 'image/gif'];
    const MAX_SIZE = 5242880; // 5MB
    const UPLOAD_DIR = 'vistas/img/usuarios/';
    
    /**
     * Validar y procesar imagen de usuario de forma segura
     * 
     * @param array $file Array $_FILES del archivo
     * @param string $nombreUsuario Nombre del usuario para crear directorio
     * @return array ['error' => bool, 'mensaje' => string, 'ruta' => string]
     */
    static public function procesarImagenUsuario($file, $nombreUsuario) {
        
        // Validación 1: Verificar que se subió un archivo
        if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
            return ['error' => true, 'mensaje' => 'No se recibió ningún archivo'];
        }
        
        // Validación 2: Verificar errores de PHP
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $mensajesError = [
                UPLOAD_ERR_INI_SIZE => 'El archivo excede el tamaño máximo permitido',
                UPLOAD_ERR_FORM_SIZE => 'El archivo excede el tamaño máximo del formulario',
                UPLOAD_ERR_PARTIAL => 'El archivo se subió parcialmente',
                UPLOAD_ERR_NO_FILE => 'No se subió ningún archivo',
                UPLOAD_ERR_NO_TMP_DIR => 'Falta la carpeta temporal',
                UPLOAD_ERR_CANT_WRITE => 'Error al escribir el archivo en disco',
                UPLOAD_ERR_EXTENSION => 'Una extensión de PHP detuvo la subida'
            ];
            return ['error' => true, 'mensaje' => $mensajesError[$file['error']] ?? 'Error al subir el archivo'];
        }
        
        // Validación 3: Verificar tamaño
        if ($file['size'] > self::MAX_SIZE) {
            return ['error' => true, 'mensaje' => 'El archivo es demasiado grande (máximo 5MB)'];
        }
        
        // Validación 4: Verificar tipo MIME real con finfo (más seguro que $_FILES['type'])
        if (!function_exists('finfo_open')) {
            return ['error' => true, 'mensaje' => 'Extensión finfo no disponible en el servidor'];
        }
        
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mimeType, self::ALLOWED_TYPES)) {
            return ['error' => true, 'mensaje' => 'Tipo de archivo no permitido. Solo se permiten imágenes JPG, PNG o GIF'];
        }
        
        // Validación 5: Verificar que es una imagen válida
        $imageInfo = @getimagesize($file['tmp_name']);
        if ($imageInfo === false) {
            return ['error' => true, 'mensaje' => 'El archivo no es una imagen válida'];
        }
        
        list($ancho, $alto) = $imageInfo;
        $nuevoAncho = 500;
        $nuevoAlto = 500;
        
        // Crear directorio si no existe
        $directorio = self::UPLOAD_DIR . $nombreUsuario;
        if (!file_exists($directorio)) {
            if (!mkdir($directorio, 0755, true)) {
                return ['error' => true, 'mensaje' => 'No se pudo crear el directorio de imágenes'];
            }
        }
        
        // Generar nombre único y seguro
        $extension = '';
        switch ($mimeType) {
            case 'image/jpeg':
                $extension = 'jpg';
                break;
            case 'image/png':
                $extension = 'png';
                break;
            case 'image/gif':
                $extension = 'gif';
                break;
            default:
                return ['error' => true, 'mensaje' => 'Tipo de imagen no soportado'];
        }
        
        $nombreArchivo = uniqid('foto_', true) . '.' . $extension;
        $rutaDestino = $directorio . '/' . $nombreArchivo;
        
        // Procesar imagen según tipo
        try {
            switch ($mimeType) {
                case 'image/jpeg':
                    $origen = @imagecreatefromjpeg($file['tmp_name']);
                    break;
                case 'image/png':
                    $origen = @imagecreatefrompng($file['tmp_name']);
                    break;
                case 'image/gif':
                    $origen = @imagecreatefromgif($file['tmp_name']);
                    break;
                default:
                    return ['error' => true, 'mensaje' => 'Tipo de imagen no soportado'];
            }
            
            if (!$origen) {
                return ['error' => true, 'mensaje' => 'No se pudo procesar la imagen'];
            }
            
            // Redimensionar
            $destino = imagecreatetruecolor($nuevoAncho, $nuevoAlto);
            
            // Preservar transparencia para PNG y GIF
            if ($mimeType === 'image/png' || $mimeType === 'image/gif') {
                imagealphablending($destino, false);
                imagesavealpha($destino, true);
                $transparent = imagecolorallocatealpha($destino, 0, 0, 0, 127);
                imagefill($destino, 0, 0, $transparent);
            }
            
            imagecopyresampled($destino, $origen, 0, 0, 0, 0, $nuevoAncho, $nuevoAlto, $ancho, $alto);
            
            // Guardar
            $guardado = false;
            if ($extension === 'jpg') {
                $guardado = @imagejpeg($destino, $rutaDestino, 85);
            } elseif ($extension === 'png') {
                $guardado = @imagepng($destino, $rutaDestino, 9);
            } elseif ($extension === 'gif') {
                $guardado = @imagegif($destino, $rutaDestino);
            }
            
            imagedestroy($origen);
            imagedestroy($destino);
            
            if (!$guardado) {
                return ['error' => true, 'mensaje' => 'No se pudo guardar la imagen'];
            }
            
            return ['error' => false, 'ruta' => $rutaDestino];
            
        } catch (Exception $e) {
            error_log("Error procesando imagen: " . $e->getMessage());
            return ['error' => true, 'mensaje' => 'Error al procesar la imagen: ' . $e->getMessage()];
        }
    }
    
    /**
     * Eliminar imagen de usuario
     * 
     * @param string $ruta Ruta del archivo a eliminar
     * @return bool True si se eliminó correctamente
     */
    static public function eliminarImagenUsuario($ruta) {
        if (file_exists($ruta)) {
            return @unlink($ruta);
        }
        return true;
    }
}

