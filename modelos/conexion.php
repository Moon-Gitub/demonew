<?php

class Conexion{

	// Valores por defecto (para compatibilidad si no existe .env)
	static public $hostDB = 'localhost';
	static public $nameDB = 'demo_db';
	static public $userDB = 'demo_user';
	static public $passDB = 'aK4UWccl2ceg';
	static public $charset = 'UTF8MB4';

	static public function getDatosConexion(){

		return array(
			'host' => self::$hostDB,
			'db' => self::$nameDB,
			'user' => self::$userDB,
			'pass' => self::$passDB,
			'charset' => self::$charset
		);
	}

	/**
	 * CONEXIÓN A BASE DE DATOS LOCAL DEL SISTEMA POS
	 */
	static public function conectar(){

		// Intentar obtener desde .env usando función env()
		$host = function_exists('env') ? env('DB_HOST', self::$hostDB) : self::$hostDB;
		$db = function_exists('env') ? env('DB_NAME', self::$nameDB) : self::$nameDB;
		$user = function_exists('env') ? env('DB_USER', self::$userDB) : self::$userDB;
		$pass = function_exists('env') ? env('DB_PASS', self::$passDB) : self::$passDB;

		try {
			$link = new PDO("mysql:host=$host;dbname=$db","$user","$pass");
			$link->exec("set names utf8");
			return $link;
		} catch (PDOException $e) {
			error_log("Error conectando a BD local: " . $e->getMessage());
			throw new Exception("Error de conexión a base de datos local");
		}

	}

	/**
	 * CONEXIÓN A BASE DE DATOS MOON (SISTEMA DE COBRO)
	 * Esta BD está en servidor remoto de Moon Desarrollos
	 */
	static public function conectarMoon(){

		// Intentar obtener desde .env usando función env()
		if (function_exists('env')) {
			$host = env('MOON_DB_HOST');
			$db = env('MOON_DB_NAME');
			$user = env('MOON_DB_USER');
			$pass = env('MOON_DB_PASS');
		} else {
			// Fallback a $_ENV o valores por defecto
			$host = isset($_ENV['MOON_DB_HOST']) ? $_ENV['MOON_DB_HOST'] : self::$hostDB;
			$db = isset($_ENV['MOON_DB_NAME']) ? $_ENV['MOON_DB_NAME'] : self::$nameDB;
			$user = isset($_ENV['MOON_DB_USER']) ? $_ENV['MOON_DB_USER'] : self::$userDB;
			$pass = isset($_ENV['MOON_DB_PASS']) ? $_ENV['MOON_DB_PASS'] : self::$passDB;
		}

		// Si no están definidas, usar valores por defecto (compatibilidad)
		if (!$host || !$db || !$user || !$pass) {
			$host = self::$hostDB;
			$db = self::$nameDB;
			$user = self::$userDB;
			$pass = self::$passDB;
		}

		try {
			$link = new PDO("mysql:host=$host;dbname=$db","$user","$pass");
			$link->exec("set names utf8");
			return $link;
		} catch (PDOException $e) {
			error_log("Error conectando a BD Moon: " . $e->getMessage());
			// No lanzar excepción, retornar null para hacer el sistema de cobro opcional
			return null;
		}

	}

}
