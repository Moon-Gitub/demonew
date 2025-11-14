<?php

require_once "conexion.php";

class ModeloClientesCtaCte{

	/*=============================================
	MOSTRAR CTA CTE CLIENTE
	=============================================*/
	static public function mdlMostrarCtaCteCliente($tabla, $item, $valor){

		if($item != null){

			$stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla WHERE $item = :$item ORDER BY fecha ASC");

			$stmt -> bindParam(":".$item, $valor, PDO::PARAM_STR);

			$stmt -> execute();

			return $stmt -> fetchAll();

		} else {

			$stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla ORDER BY id ASC");

			$stmt -> execute();

			return $stmt -> fetchAll();

		}

		$stmt -> close();

		$stmt = null;

	}

	/*=============================================
	MOSTRAR REGISTRO DE CTA CTE CLIENTE
	=============================================*/
	static public function mdlMostrarCtaCteClienteId($tabla, $item, $valor){

		$stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla WHERE $item = :$item");

		$stmt -> bindParam(":".$item, $valor, PDO::PARAM_INT);

		$stmt -> execute();

		return $stmt -> fetch();

		$stmt -> close();

		$stmt = null;

	}	

	/*=============================================
	INGRESAR REGISTRO A CTA CTE CLIENTE
	=============================================*/
	static public function mdlIngresarCtaCte($tabla, $datos){

		$stmt = Conexion::conectar()->prepare(
			"INSERT INTO $tabla(fecha, id_cliente, tipo, descripcion, id_venta, importe, metodo_pago, numero_recibo) 
			VALUES (:fecha,
			:id_cliente,
			:tipo,
			:descripcion,
			:id_venta,
			:importe, 
			:metodo_pago,
			:numero_recibo)");

		$stmt->bindParam(":fecha", $datos["fecha"], PDO::PARAM_STR);
		$stmt->bindParam(":id_cliente", $datos["id_cliente"], PDO::PARAM_INT);		
		$stmt->bindParam(":tipo", $datos["tipo"], PDO::PARAM_INT);
		$stmt->bindParam(":descripcion", $datos["descripcion"], PDO::PARAM_STR);
		$stmt->bindParam(":id_venta", $datos["id_venta"], PDO::PARAM_INT);		
		$stmt->bindParam(":importe", $datos["importe"], PDO::PARAM_STR);
		$stmt->bindParam(":metodo_pago", $datos["metodo_pago"], PDO::PARAM_STR);
		$stmt->bindParam(":numero_recibo", $datos["numero_recibo"], PDO::PARAM_INT);
		
		if($stmt->execute()){

			return "ok";

		}else{

			return $stmt->errorInfo();
		
		}

		$stmt->close();
		$stmt = null;

	}

	/*=============================================
	EDITAR REGISTRO A CTA CTE CLIENTE
	=============================================*/
	static public function mdlEditarCtaCte($tabla, $datos){

		$stmt = Conexion::conectar()->prepare("UPDATE $tabla SET 
				fecha = :fecha,
				id_cliente = :id_cliente, 
				tipo = :tipo,
				descripcion = :descripcion, 
				id_venta = :id_venta, 
				importe = :importe, 
				metodo_pago = :metodo_pago,
				numero_recibo = :numero_recibo
			WHERE id = :id");

		$stmt->bindParam(":id", $datos["id"], PDO::PARAM_INT);
		$stmt->bindParam(":fecha", $datos["fecha"], PDO::PARAM_STR);
		$stmt->bindParam(":id_cliente", $datos["id_cliente"], PDO::PARAM_INT);		
		$stmt->bindParam(":tipo", $datos["tipo"], PDO::PARAM_INT);
		$stmt->bindParam(":descripcion", $datos["descripcion"], PDO::PARAM_STR);
		$stmt->bindParam(":id_venta", $datos["id_venta"], PDO::PARAM_INT);		
		$stmt->bindParam(":importe", $datos["importe"], PDO::PARAM_STR);
		$stmt->bindParam(":metodo_pago", $datos["metodo_pago"], PDO::PARAM_STR);
		$stmt->bindParam(":numero_recibo", $datos["numero_recibo"], PDO::PARAM_INT);
		
		if($stmt->execute()){

			return "ok";

		}else{

			return $stmt->errorInfo();
		
		}

		$stmt->close();
		$stmt = null;

	}	

	/*=============================================
	ELIMINAR MOVIMIENTO CTA CTE
	=============================================*/
	static public function mdlEliminarCtaCte($tabla, $datos){

		$stmt = Conexion::conectar()->prepare("DELETE FROM $tabla WHERE id = :id");

		$stmt -> bindParam(":id", $datos, PDO::PARAM_INT);

		if($stmt -> execute()){

			return "ok";
		
		}else{

			return $stmt->errorInfo();	

		}

		$stmt -> close();

		$stmt = null;

	}

	/*=============================================
	ELIMINAR VENTA EN CTA CTE (esta funcion se usa al eliminar una venta desde el modulo ventas.php)
	=============================================*/
	static public function mdlEliminarVentaCtaCte($tabla, $idVenta){

		$stmt = Conexion::conectar()->prepare("DELETE FROM $tabla WHERE id_venta = :id_venta AND tipo = 0");

		$stmt -> bindParam(":id_venta", $idVenta, PDO::PARAM_INT);

		if($stmt -> execute()){

			return "ok";
		
		}else{

			return $stmt->errorInfo();	

		}

		$stmt -> close();

		$stmt = null;

	}

	/*=============================================
	SUMAR EL TOTAL DE CUOTAS VENCIDAS
	=============================================*/
	static public function mdlCtaCteSumaCuotasVencidas($tabla){	

		$stmt = Conexion::conectar()->prepare("SELECT SUM(neto) as total FROM $tabla");

		$stmt -> execute();

		return $stmt -> fetch();

		$stmt -> close();

		$stmt = null;

	}

	/*=============================================
	MUESTRA LOS REGISTROS DE CADA PAGO QUE SE HIZO POR UNA DETERMINADA VENTA
	=============================================*/
	static public function mdlMostrarEntregasXVenta($tabla, $item, $valor){	

		$stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla WHERE $item = $valor");

		$stmt -> execute();

		return $stmt -> fetchAll();

		$stmt -> close();

		$stmt = null;

	}

	/*=============================================
	MUESTRA REGISTRO DE LA VENTA EN LA CUENTA CORRIENTE (usado en ventas controlador - desde editar ventas)
	=============================================*/
	static public function mdlMostrarCtaCteXVenta($tabla, $item, $valor){	

		$stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla WHERE $item = :valor AND tipo = 0");

		$stmt -> bindParam(":valor", $valor, PDO::PARAM_INT);

		$stmt -> execute();

		return $stmt -> fetch();

		$stmt -> close();

		$stmt = null;

	}

	/*=============================================
	LISTADO DE CLIENTES CON SALDO EN CUENTA CORRIENTE
	Esta consulta trae los clientes donde total de ventas - total de pagos es distindo de 0
	Usada en clientes.php
	=============================================*/
	static public function mdlMostrarSaldos(){	

		//Todos los clientes que tengan registro en la cuenta corriente
		// $stmt = Conexion::conectar()->prepare("SELECT DISTINCT cc.id_cliente, c.nombre, c.documento, SUM(IF (cc.tipo = 0, cc.importe, 0)) AS ventas, SUM(IF (cc.tipo = 1, cc.importe, 0)) AS pagos FROM clientes_cuenta_corriente cc INNER JOIN clientes c ON cc.id_cliente = c.id
			// GROUP BY cc.id_cliente ORDER BY c.nombre");

		//Solo traigo donde ventas - compras es mayor a 0
		$stmt = Conexion::conectar()->prepare("SELECT DISTINCT cc.id_cliente, c.nombre, c.telefono, c.email, -SUM(IF (cc.tipo = 0, cc.importe, 0)) AS ventas, SUM(IF (cc.tipo = 1, cc.importe, 0)) AS pagos, (-SUM(IF (cc.tipo = 0, cc.importe, 0)) + SUM(IF (cc.tipo = 1, cc.importe, 0))) as diferencia FROM clientes_cuenta_corriente cc INNER JOIN clientes c ON cc.id_cliente = c.id GROUP BY cc.id_cliente ORDER BY c.nombre");

		//$stmt -> bindParam(":valor", $valor, PDO::PARAM_INT);

		$stmt -> execute();

		return $stmt -> fetchAll();

		$stmt -> close();

		$stmt = null;

	}

	/*=============================================
	SALDO TOTAL EN CUENTA CORRIENTE
	Usada en clientes-cuenta-saldos y en inicio
	=============================================*/
	static public function mdlMostrarSaldoTotal(){	
		$stmt = Conexion::conectar()->prepare("SELECT (SUM(IF (cc.tipo = 1, cc.importe, 0)) - SUM(IF (cc.tipo = 0, cc.importe, 0))) as saldo FROM clientes_cuenta_corriente cc");
		$stmt -> execute();
		return $stmt -> fetch();
		$stmt -> close();
		$stmt = null;
	}
	
	/*=============================================
	SALDO TOTAL EN CUENTA CORRIENTE X CLIENTE
	Usada en crear-venta y crear-venta-caja
	=============================================*/
	static public function mdlMostrarSaldoTotalXCliente($id){	
		$stmt = Conexion::conectar()->prepare("SELECT (SUM(IF (cc.tipo = 1, cc.importe, 0)) - SUM(IF (cc.tipo = 0, cc.importe, 0))) as saldo FROM clientes_cuenta_corriente cc WHERE cc.id_cliente = $id");
		$stmt -> execute();
		return $stmt -> fetch();
		$stmt -> close();
		$stmt = null;
	}

	/*=============================================
	LISTADO DE CLIENTES CON SALDO EN CUENTA CORRIENTE
	Esta consulta trae los clientes donde total de ventas - total de pagos es distindo de 0
	Usada en clientes.php
	=============================================*/
	static public function mdlMostrarDeudas(){	

		//Todos los clientes que tengan registro en la cuenta corriente
		// $stmt = Conexion::conectar()->prepare("SELECT DISTINCT cc.id_cliente, c.nombre, c.documento, SUM(IF (cc.tipo = 0, cc.importe, 0)) AS ventas, SUM(IF (cc.tipo = 1, cc.importe, 0)) AS pagos FROM clientes_cuenta_corriente cc INNER JOIN clientes c ON cc.id_cliente = c.id
			// GROUP BY cc.id_cliente ORDER BY c.nombre");

		//Solo traigo donde ventas - compras es mayor a 0
		$stmt = Conexion::conectar()->prepare("SELECT v.id as id_venta, v.codigo, c.id as id_cliente, c.nombre, DATE_FORMAT(v.fecha, '%Y-%m-%d') as fecha_venta, DATE_FORMAT(v.fecha + INTERVAL 30 DAY, '%Y-%m-%d') as vencimiento_pago, DATEDIFF(CURDATE(), DATE_FORMAT(v.fecha + INTERVAL 30 DAY, '%Y-%m-%d')) AS dias_vencido, v.total, (SELECT SUM(importe) FROM clientes_cuenta_corriente WHERE tipo = 1 AND id_venta = v.id) as entrega FROM ventas v INNER JOIN clientes c ON v.id_cliente = c.id WHERE JSON_EXTRACT(v.metodo_pago, '$[*].tipo') LIKE '%CC%' AND v.estado = 2 AND v.fecha < CURDATE() - INTERVAL 30 DAY ORDER BY c.id");

		//$stmt -> bindParam(":valor", $valor, PDO::PARAM_INT);

		$stmt -> execute();

		return $stmt -> fetchAll();

		$stmt -> close();

		$stmt = null;

	}	

	/*=============================================
	MUESTRA ULTIMO NUMERO DE RECIBO
	=============================================*/
	static public function mdlMostrarUltimoNumeroRecibo(){	
		$stmt = Conexion::conectar()->prepare("SELECT MAX(numero_recibo) as num_recibo FROM clientes_cuenta_corriente");
		$stmt -> execute();
		return $stmt -> fetch();
		$stmt -> close();
		$stmt = null;
	}
}