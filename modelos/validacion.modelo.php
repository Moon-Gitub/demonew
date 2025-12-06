<?php

/**
 * MODELO DE VALIDACIÓN
 * 
 * Funciones para validar y sanitizar datos de entrada
 * previniendo XSS, inyección SQL y otros ataques
 */

class ModeloValidacion {
    
    /**
     * Validar y sanitizar texto general
     * 
     * @param string $texto Texto a sanitizar
     * @param int $maxLength Longitud máxima
     * @return string Texto sanitizado
     */
    static public function sanitizarTexto($texto, $maxLength = 255) {
        $texto = trim($texto);
        $texto = strip_tags($texto);
        $texto = htmlspecialchars($texto, ENT_QUOTES, 'UTF-8');
        return substr($texto, 0, $maxLength);
    }
    
    /**
     * Validar email
     * 
     * @param string $email Email a validar
     * @return string|false Email validado o false si es inválido
     */
    static public function validarEmail($email) {
        $email = filter_var($email, FILTER_SANITIZE_EMAIL);
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }
        
        return $email;
    }
    
    /**
     * Validar número entero
     * 
     * @param mixed $numero Número a validar
     * @param int|null $min Valor mínimo (opcional)
     * @param int|null $max Valor máximo (opcional)
     * @return int|false Número validado o false si es inválido
     */
    static public function validarEntero($numero, $min = null, $max = null) {
        $numero = filter_var($numero, FILTER_VALIDATE_INT);
        
        if ($numero === false) {
            return false;
        }
        
        if ($min !== null && $numero < $min) {
            return false;
        }
        
        if ($max !== null && $numero > $max) {
            return false;
        }
        
        return $numero;
    }
    
    /**
     * Validar número decimal
     * 
     * @param mixed $numero Número a validar
     * @return float|false Número validado o false si es inválido
     */
    static public function validarDecimal($numero) {
        $numero = filter_var($numero, FILTER_VALIDATE_FLOAT);
        return $numero !== false ? $numero : false;
    }
    
    /**
     * Validar fecha
     * 
     * @param string $fecha Fecha a validar
     * @param string $formato Formato esperado (default: 'Y-m-d')
     * @return bool True si la fecha es válida
     */
    static public function validarFecha($fecha, $formato = 'Y-m-d') {
        $d = DateTime::createFromFormat($formato, $fecha);
        return $d && $d->format($formato) === $fecha;
    }
    
    /**
     * Validar CUIT/CUIL (Argentina)
     * 
     * @param string $cuit CUIT a validar
     * @return bool True si el CUIT es válido
     */
    static public function validarCUIT($cuit) {
        // Remover guiones y espacios
        $cuit = preg_replace('/[^0-9]/', '', $cuit);
        
        // Debe tener 11 dígitos
        if (strlen($cuit) != 11) {
            return false;
        }
        
        // Validar dígito verificador
        $acumulado = 0;
        $digitos = str_split($cuit);
        $multiplicadores = [5, 4, 3, 2, 7, 6, 5, 4, 3, 2];
        
        for ($i = 0; $i < 10; $i++) {
            $acumulado += intval($digitos[$i]) * $multiplicadores[$i];
        }
        
        $verificador = 11 - ($acumulado % 11);
        if ($verificador == 11) $verificador = 0;
        if ($verificador == 10) $verificador = 9;
        
        return $verificador == intval($digitos[10]);
    }
    
    /**
     * Sanitizar JSON
     * 
     * @param mixed $json JSON a sanitizar
     * @return array|false Array sanitizado o false si es inválido
     */
    static public function sanitizarJSON($json) {
        if (is_string($json)) {
            $json = json_decode($json, true);
        }
        
        if (!is_array($json)) {
            return false;
        }
        
        return $json;
    }
    
    /**
     * Validar nombre de usuario
     * Solo letras, números y guión bajo, 3-20 caracteres
     * 
     * @param string $username Username a validar
     * @return string|false Username validado o false si es inválido
     */
    static public function validarUsername($username) {
        // Solo letras, números y guión bajo, 3-20 caracteres
        if (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username)) {
            return false;
        }
        return $username;
    }
    
    /**
     * Validar contraseña fuerte
     * 
     * @param string $password Contraseña a validar
     * @return array ['valido' => bool, 'mensaje' => string]
     */
    static public function validarPasswordFuerte($password) {
        // Mínimo 8 caracteres
        if (strlen($password) < 8) {
            return ['valido' => false, 'mensaje' => 'La contraseña debe tener al menos 8 caracteres'];
        }
        
        // Al menos una mayúscula
        if (!preg_match('/[A-Z]/', $password)) {
            return ['valido' => false, 'mensaje' => 'La contraseña debe tener al menos una mayúscula'];
        }
        
        // Al menos una minúscula
        if (!preg_match('/[a-z]/', $password)) {
            return ['valido' => false, 'mensaje' => 'La contraseña debe tener al menos una minúscula'];
        }
        
        // Al menos un número
        if (!preg_match('/[0-9]/', $password)) {
            return ['valido' => false, 'mensaje' => 'La contraseña debe tener al menos un número'];
        }
        
        return ['valido' => true];
    }
    
    /**
     * Prevenir XSS en output
     * Escapa caracteres HTML especiales
     * 
     * @param string $texto Texto a escapar
     * @return string Texto escapado
     */
    static public function escaparHTML($texto) {
        return htmlspecialchars($texto, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
    
    /**
     * Sanitizar para búsqueda LIKE
     * 
     * @param string $texto Texto a sanitizar
     * @return string Texto sanitizado
     */
    static public function sanitizarBusqueda($texto) {
        // Remover caracteres peligrosos pero mantener % y _ para LIKE
        $texto = trim($texto);
        $texto = strip_tags($texto);
        // Limitar longitud
        return substr($texto, 0, 100);
    }
}

