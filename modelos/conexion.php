<?php

class Conexion{

	// Valores por defecto (para compatibilidad si no existe .env)
	static public $hostDB = 'localhost';
	static public $nameDB = 'newmoon_newmoon_db';
	static public $userDB = 'newmoon_newmoon_user';
	static public $passDB = '61t;t62h5P$}.sXT';
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

		// Usar siempre valores por defecto para BD local (más confiable)
		$host = self::$hostDB;
		$db = self::$nameDB;
		$user = self::$userDB;
		$pass = self::$passDB;

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

		// BD Moon siempre usa credenciales fijas (más confiable)
		$host = '107.161.23.11';
		$db = 'cobrosposmooncom_db';
		$user = 'cobrosposmooncom_dbuser';
		$pass = '[Us{ynaJAA_o2A_!';

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
