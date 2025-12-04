<?php 

require_once "conexion.php";

class ModeloSistemaCobro{

	/*=============================================
	MOSTRAR CLIENTES
	=============================================*/
	static public function mdlMostrarClientesCobro($idCliente){

		if($idCliente != null){

			$stmt = Conexion::conectarMoon()->prepare("SELECT * FROM clientes WHERE id = :id");
			$stmt -> bindParam(":id", $idCliente, PDO::PARAM_INT);
			$stmt -> execute();

			return $stmt -> fetch();

		}else{

			$stmt = Conexion::conectarMoon()->prepare("SELECT * FROM clientes");

			$stmt -> execute();

			return $stmt -> fetchAll();

		}
		

		$stmt -> close();

		$stmt = null;

	}

	/*=============================================
	SALDO EN CUENTA CORRIENTE
	=============================================*/
	static public function mdlMostrarSaldoCuentaCorriente($idCliente){	

		//Solo traigo donde ventas - compras es mayor a 0
		$stmt = Conexion::conectarMoon()->prepare("SELECT SUM(IF (cc.tipo = 0, cc.importe, 0)) AS ventas, SUM(IF (cc.tipo = 1, cc.importe, 0)) AS pagos, (SUM(IF (cc.tipo = 0, cc.importe, 0)) - SUM(IF (cc.tipo = 1, cc.importe, 0))) as saldo FROM clientes_cuenta_corriente cc WHERE cc.id_cliente = :id_cliente");
		$stmt -> bindParam(":id_cliente", $idCliente, PDO::PARAM_INT);
		$stmt -> execute();
		return $stmt -> fetch();
		$stmt -> close();
		$stmt = null;
	}

	/*=============================================
	ACTUALIZAR ESTADO CLIENTE
	=============================================*/
	static public function mdlActualizarClientesCobro($idCliente, $estado){

		if($idCliente != null) {
			$stmt = Conexion::conectarMoon()->prepare("UPDATE clientes SET estado_bloqueo = :estado WHERE id = :id");
			$stmt -> bindParam(":estado", $estado, PDO::PARAM_INT);
			$stmt -> bindParam(":id", $idCliente, PDO::PARAM_INT);
		}

		if($stmt -> execute()) {
			return $estado;
		} else {
			return errorInfo();
		}

		$stmt -> close();

		$stmt = null;

	}

	/*=============================================
	consulto el ultimo registro del cliente (para sacar la descripcion de lo que está debiendo)
	=============================================*/
	static public function mdlMostrarMovimientoCuentaCorriente($idCliente){	

		//Solo traigo donde ventas - compras es mayor a 0
		$stmt = Conexion::conectarMoon()->prepare("SELECT * FROM clientes_cuenta_corriente WHERE id_cliente = :id_cliente AND id = (SELECT MAX(id) FROM clientes_cuenta_corriente WHERE id_cliente = :id_cliente AND tipo = 0)");

		$stmt -> bindParam(":id_cliente", $idCliente, PDO::PARAM_INT);

		$stmt -> execute();

		return $stmt -> fetch();

		$stmt -> close();

		$stmt = null;

	}

	/*=============================================
	REGISTRAR PAGO CLIENTE
	=============================================*/
	static public function mdlRegistrarMovimientoCuentaCorriente($idCliente, $abonoMensual){

		$stmt = Conexion::conectarMoon()->prepare("INSERT INTO clientes_cuenta_corriente (fecha, id_cliente, tipo, descripcion, importe) VALUES(:fecha, :id_cliente, 1, 'PAGO CTA CTE DESDE MERCADO PAGO', :importe)");
		$fecha = date('Y-m-d H:i');
		$stmt -> bindParam(":fecha", $fecha, PDO::PARAM_STR);
		$stmt -> bindParam(":id_cliente", $idCliente, PDO::PARAM_INT);
		$stmt -> bindParam(":importe", $abonoMensual, PDO::PARAM_STR);

		if($stmt -> execute()) {
			return "ok";
		} else {
			return $stmt -> errorInfo();
		}

		$stmt -> close();

		$stmt = null;

	}
	
	/*=============================================
	REGISTRAR INTERES EN CTA CTE CLIENTE
	=============================================*/
	static public function mdlRegistrarInteresCuentaCorriente($idCliente, $interes){

		$stmt = Conexion::conectarMoon()->prepare("INSERT INTO clientes_cuenta_corriente (fecha, id_cliente, tipo, descripcion, importe) VALUES(:fecha, :id_cliente, 0, 'INTERES', :importe)");
		$fecha = date('Y-m-d H:i');
		$stmt -> bindParam(":fecha", $fecha, PDO::PARAM_STR);
		$stmt -> bindParam(":id_cliente", $idCliente, PDO::PARAM_INT);
		$stmt -> bindParam(":importe", $interes, PDO::PARAM_STR);

		if($stmt -> execute()) {
			return "ok";
		} else {
			return $stmt -> errorInfo();
		}

		$stmt -> close();

		$stmt = null;

	}

}