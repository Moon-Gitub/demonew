<?php

class Conexion{

	/**
	 * Obtener valor de variable de entorno con fallback
	 */
	static private function getEnv($key, $default = null) {
		// Prioridad 1: $_ENV (donde Dotenv carga las variables)
		if (isset($_ENV[$key])) {
			return $_ENV[$key];
		}
		
		// Prioridad 2: $_SERVER (algunos servidores)
		if (isset($_SERVER[$key])) {
			return $_SERVER[$key];
		}
		
		// Prioridad 3: getenv() (fallback adicional)
		$value = getenv($key);
		if ($value !== false) {
			return $value;
		}
		
		// Prioridad 4: Función env() si existe (helper)
		if (function_exists('env')) {
			$value = env($key);
			if ($value !== null) {
				return $value;
			}
		}
		
		// Fallback: valor por defecto
		return $default;
	}

	/**
	 * Obtener datos de conexión (para compatibilidad)
	 */
	static public function getDatosConexion(){
		return array(
			'host' => self::getEnv('DB_HOST', 'localhost'),
			'db' => self::getEnv('DB_NAME', 'newmoon_newmoon_db'),
			'user' => self::getEnv('DB_USER', 'newmoon_newmoon_user'),
			'pass' => self::getEnv('DB_PASS', '61t;t62h5P$}.sXT'),
			'charset' => self::getEnv('DB_CHARSET', 'UTF8MB4')
		);
	}

	/**
	 * CONEXIÓN A BASE DE DATOS LOCAL DEL SISTEMA POS
	 * Lee credenciales desde .env o usa valores por defecto
	 */
	static public function conectar(){

		// Leer desde .env o usar valores por defecto
		$host = self::getEnv('DB_HOST', 'localhost');
		$db = self::getEnv('DB_NAME', 'newmoon_newmoon_db');
		$user = self::getEnv('DB_USER', 'newmoon_newmoon_user');
		$pass = self::getEnv('DB_PASS', '61t;t62h5P$}.sXT');
		$charset = self::getEnv('DB_CHARSET', 'UTF8MB4');

		try {
			$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
			$options = [
				PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
				PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
				PDO::ATTR_EMULATE_PREPARES => false,
			];
			
			$link = new PDO($dsn, $user, $pass, $options);
			$link->exec("set names utf8");
			return $link;
		} catch (PDOException $e) {
			error_log("Error conectando a BD local: Host=$host, DB=$db, User=$user - " . $e->getMessage());
			throw new Exception("Error de conexión a base de datos local");
		}

	}

	/**
	 * CONEXIÓN A BASE DE DATOS MOON (SISTEMA DE COBRO)
	 * Esta BD está en servidor remoto de Moon Desarrollos
	 * Lee credenciales desde .env o usa valores por defecto
	 */
	static public function conectarMoon(){

		// Leer desde .env o usar valores por defecto
		$host = self::getEnv('MOON_DB_HOST', '107.161.23.11');
		$db = self::getEnv('MOON_DB_NAME', 'cobrosposmooncom_db');
		$user = self::getEnv('MOON_DB_USER', 'cobrosposmooncom_dbuser');
		$pass = self::getEnv('MOON_DB_PASS', '[Us{ynaJAA_o2A_!');
		$charset = self::getEnv('MOON_DB_CHARSET', 'UTF8MB4');

		try {
			$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
			$options = [
				PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
				PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
				PDO::ATTR_EMULATE_PREPARES => false,
			];
			
			$link = new PDO($dsn, $user, $pass, $options);
			$link->exec("set names utf8");
			return $link;
		} catch (PDOException $e) {
			error_log("Error conectando a BD Moon: " . $e->getMessage());
			// No lanzar excepción, retornar null para hacer el sistema de cobro opcional
			return null;
		}

	}

}
