<?php

/**
 * MODELO DE SEGURIDAD
 * 
 * Funciones para manejo seguro de contraseñas y seguridad
 */

class ModeloSeguridad {
    
    /**
     * Hash seguro de contraseña usando password_hash
     * 
     * @param string $password Contraseña en texto plano
     * @return string Hash de la contraseña
     */
    static public function hashPassword($password) {
        // Usa PASSWORD_DEFAULT para adaptarse automáticamente
        // a algoritmos más seguros en futuras versiones de PHP
        return password_hash($password, PASSWORD_DEFAULT, [
            'cost' => 12  // Mayor seguridad (mínimo 10, recomendado 12)
        ]);
    }
    
    /**
     * Verificar contraseña contra hash
     * 
     * @param string $password Contraseña en texto plano
     * @param string $hash Hash almacenado
     * @return bool True si la contraseña es correcta
     */
    static public function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }
    
    /**
     * Verificar si el hash necesita actualización
     * Útil para migrar hashes antiguos a nuevos algoritmos
     * 
     * @param string $hash Hash actual
     * @return bool True si necesita actualización
     */
    static public function needsRehash($hash) {
        return password_needs_rehash($hash, PASSWORD_DEFAULT, ['cost' => 12]);
    }
    
    /**
     * Verificar si un hash es del formato antiguo (crypt)
     * 
     * @param string $hash Hash a verificar
     * @return bool True si es formato antiguo
     */
    static public function isOldFormat($hash) {
        // Los hashes modernos de password_hash empiezan con $2y$
        // Los hashes antiguos de crypt pueden empezar con $2a$ o tener otros formatos
        return !preg_match('/^\$2[ay]\$/', $hash);
    }
}

