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

		// Leer de $_ENV primero (donde Dotenv carga), sino valores por defecto
		$host = isset($_ENV['DB_HOST']) ? $_ENV['DB_HOST'] : (isset($_SERVER['DB_HOST']) ? $_SERVER['DB_HOST'] : self::$hostDB);
		$db = isset($_ENV['DB_NAME']) ? $_ENV['DB_NAME'] : (isset($_SERVER['DB_NAME']) ? $_SERVER['DB_NAME'] : self::$nameDB);
		$user = isset($_ENV['DB_USER']) ? $_ENV['DB_USER'] : (isset($_SERVER['DB_USER']) ? $_SERVER['DB_USER'] : self::$userDB);
		$pass = isset($_ENV['DB_PASS']) ? $_ENV['DB_PASS'] : (isset($_SERVER['DB_PASS']) ? $_SERVER['DB_PASS'] : self::$passDB);

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

		// Leer de $_ENV primero, sino $_SERVER, sino valores por defecto
		$host = isset($_ENV['MOON_DB_HOST']) ? $_ENV['MOON_DB_HOST'] : (isset($_SERVER['MOON_DB_HOST']) ? $_SERVER['MOON_DB_HOST'] : self::$hostDB);
		$db = isset($_ENV['MOON_DB_NAME']) ? $_ENV['MOON_DB_NAME'] : (isset($_SERVER['MOON_DB_NAME']) ? $_SERVER['MOON_DB_NAME'] : self::$nameDB);
		$user = isset($_ENV['MOON_DB_USER']) ? $_ENV['MOON_DB_USER'] : (isset($_SERVER['MOON_DB_USER']) ? $_SERVER['MOON_DB_USER'] : self::$userDB);
		$pass = isset($_ENV['MOON_DB_PASS']) ? $_ENV['MOON_DB_PASS'] : (isset($_SERVER['MOON_DB_PASS']) ? $_SERVER['MOON_DB_PASS'] : self::$passDB);

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
