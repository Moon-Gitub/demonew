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
	 * Requiere .env configurado
	 */
	static public function getDatosConexion(){
		$host = self::getEnv('DB_HOST');
		$db = self::getEnv('DB_NAME');
		$user = self::getEnv('DB_USER');
		$pass = self::getEnv('DB_PASS');
		$charset = self::getEnv('DB_CHARSET', 'UTF8MB4');
		
		if (empty($host) || empty($db) || empty($user) || empty($pass)) {
			throw new Exception("Error: Archivo .env no configurado. Variables DB_HOST, DB_NAME, DB_USER, DB_PASS son requeridas.");
		}
		
		return array(
			'host' => $host,
			'db' => $db,
			'user' => $user,
			'pass' => $pass,
			'charset' => $charset
		);
	}

	/**
	 * CONEXIÓN A BASE DE DATOS LOCAL DEL SISTEMA POS
	 * Lee credenciales desde .env (REQUERIDO)
	 */
	static public function conectar(){

		// Leer desde .env (REQUERIDO - no usar valores por defecto inseguros)
		$host = self::getEnv('DB_HOST');
		$db = self::getEnv('DB_NAME');
		$user = self::getEnv('DB_USER');
		$pass = self::getEnv('DB_PASS');
		$charset = self::getEnv('DB_CHARSET', 'UTF8MB4');
		
		// Validar que las credenciales críticas estén configuradas
		if (empty($host) || empty($db) || empty($user) || empty($pass)) {
			error_log("ERROR CRÍTICO: Variables de entorno no configuradas. Crear archivo .env con DB_HOST, DB_NAME, DB_USER, DB_PASS");
			throw new Exception("Error de configuración: Archivo .env no encontrado o incompleto. Por favor, crea el archivo .env con las credenciales de la base de datos.");
		}

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
	 * Lee credenciales desde .env (REQUERIDO)
	 */
	static public function conectarMoon(){

		// Leer desde .env (REQUERIDO - no usar valores por defecto inseguros)
		$host = self::getEnv('MOON_DB_HOST');
		$db = self::getEnv('MOON_DB_NAME');
		$user = self::getEnv('MOON_DB_USER');
		$pass = self::getEnv('MOON_DB_PASS');
		$charset = self::getEnv('MOON_DB_CHARSET', 'UTF8MB4');
		
		// Validar que las credenciales críticas estén configuradas
		if (empty($host) || empty($db) || empty($user) || empty($pass)) {
			error_log("ERROR: Variables de entorno MOON_DB_* no configuradas. Sistema de cobro no disponible.");
			// Retornar null en lugar de lanzar excepción para hacer el sistema de cobro opcional
			return null;
		}

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
