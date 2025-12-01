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

		// Intentar obtener desde .env, sino usar valores por defecto
		$host = getenv('DB_HOST') ?: self::$hostDB;
		$db = getenv('DB_NAME') ?: self::$nameDB;
		$user = getenv('DB_USER') ?: self::$userDB;
		$pass = getenv('DB_PASS') ?: self::$passDB;

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

		// Intentar obtener desde .env
		$host = getenv('MOON_DB_HOST');
		$db = getenv('MOON_DB_NAME');
		$user = getenv('MOON_DB_USER');
		$pass = getenv('MOON_DB_PASS');

		// Si no están en .env, usar valores por defecto (misma BD local - compatibilidad)
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
			throw new Exception("Error de conexión a base de datos Moon");
		}

	}

}
