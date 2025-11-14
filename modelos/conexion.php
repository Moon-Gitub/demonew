<?php

class Conexion{

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

	static public function conectar(){
		$host = self::$hostDB;
		$db = self::$nameDB;
		$user = self::$userDB;
		$pass = self::$passDB;

		$link = new PDO("mysql:host=$host;dbname=$db","$user","$pass");
		
		$link->exec("set names utf8");

		return $link;

	}

	//CONECTAR A BD MOON PARA VER ESTADO CLIENTE
	static public function conectarMoon(){

		$host = self::$hostDB;
		$db = self::$nameDB;
		$user = self::$userDB;
		$pass = self::$passDB;

		$link = new PDO("mysql:host=$host;dbname=$db","$user","$pass");


		$link->exec("set names utf8");

		return $link;
		

	}

}