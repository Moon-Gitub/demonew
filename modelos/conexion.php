<?php

class Conexion{

	// Valores por defecto (solo si .env falla)
	static public $hostDB = 'localhost';
	static public $nameDB = 'newmoon_newmoon_db';
	static public $userDB = 'newmoon_newmoon_user';
	static public $passDB = '61t;t62h5P$}.sXT';
	static public $charset = 'UTF8MB4';
	
	// Variable para controlar si ya se intentó cargar el .env
	static private $envCargado = false;

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

		// SIMPLE: Usar valores por defecto directamente
		$host = self::$hostDB;
		$db = self::$nameDB;
		$user = self::$userDB;
		$pass = self::$passDB;

		try {
			$link = new PDO("mysql:host=$host;dbname=$db","$user","$pass");
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
	 */
	static public function conectarMoon(){

		// SIMPLE: Valores fijos para BD Moon
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
