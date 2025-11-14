<?php

require_once "conexion.php";

class ModeloProveedoresCtaCte{

	/*=============================================
	MOSTRAR CTA CTE Proveedores
	=============================================*/
	static public function mdlMostrarCtaCteProveedor($tabla, $item, $valor){

		if($item != null){

			$stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla WHERE $item = :$item ORDER BY fecha_movimiento DESC");

			$stmt -> bindParam(":".$item, $valor, PDO::PARAM_STR);

			$stmt -> execute();

			return $stmt -> fetchAll();

		} else {

			$stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla ORDER BY id DESC");

			$stmt -> execute();

			return $stmt -> fetchAll();

		}

		$stmt -> close();

		$stmt = null;

	}
	
	
	/*=============================================
	MOSTRAR CTA CTE Proveedores
	=============================================*/
	static public function mdlSumarCompras($tablaCtaCte, $valor){

		$stmt = Conexion::conectar()->prepare("SELECT SUM(importe) as compras FROM $tablaCtaCte WHERE id_proveedor = $valor AND tipo=0 ");

		$stmt -> execute();

		return $stmt -> fetch();

		$stmt -> close();

		$stmt = null;
	}

	/*=============================================
	MOSTRAR CTA CTE Proveedores
	=============================================*/
	static public function mdlSumarComprasListado($tablaCtaCte, $valor, $fecha){

		$stmt = Conexion::conectar()->prepare("SELECT SUM(importe) as compras FROM $tablaCtaCte WHERE id_proveedor = $valor AND tipo=0 AND fecha_movimiento <= '$fecha'");

		$stmt -> execute();

		return $stmt -> fetch();

		$stmt -> close();

		$stmt = null;
	}
	
	/*=============================================
	MOSTRAR CTA CTE Proveedores
	=============================================*/
	static public function mdlSumarRemitos($tablaCtaCte, $valor){

		$stmt = Conexion::conectar()->prepare("SELECT SUM(importe) as compras FROM $tablaCtaCte WHERE id_proveedor = $valor AND (tipo=3 OR tipo=4)");

		$stmt -> execute();

		return $stmt -> fetch();

		$stmt -> close();

		$stmt = null;
	}

	/*=============================================
	MOSTRAR CTA CTE Proveedores
	=============================================*/
	static public function mdlSumarRemitosListado($tablaCtaCte, $valor, $fecha){

		$stmt = Conexion::conectar()->prepare("SELECT SUM(importe) as compras FROM $tablaCtaCte WHERE id_proveedor = $valor AND (tipo=3 OR tipo=4) AND fecha_movimiento <='$fecha'");

		$stmt -> execute();

		return $stmt -> fetch();

		$stmt -> close();

		$stmt = null;
	}
	
	/*=============================================
	MOSTRAR CTA CTE Proveedores
	=============================================*/
	static public function mdlSumarPagos($tablaCtaCte, $valor){

		$stmt = Conexion::conectar()->prepare("SELECT SUM(importe) as pagos FROM $tablaCtaCte WHERE id_proveedor = $valor AND (tipo=1 OR tipo=4)");

		$stmt -> execute();

		return $stmt -> fetch();

		$stmt -> close();

		$stmt = null;

	}
	
	/*=============================================
	MOSTRAR CTA CTE Proveedores
	=============================================*/
	static public function mdlSumarPagosListado($tablaCtaCte, $valor, $fecha){

		$stmt = Conexion::conectar()->prepare("SELECT SUM(importe) as pagos FROM $tablaCtaCte WHERE id_proveedor = $valor AND (tipo=1 OR tipo=4) AND fecha_movimiento <= '$fecha'");

		$stmt -> execute();

		return $stmt -> fetch();

		$stmt -> close();

		$stmt = null;

	}

	/*=============================================
	MOSTRAR CTA CTE Proveedores
	=============================================*/
	static public function mdlCuentasPagos($tablaCtaCte, $valor){

		$stmt = Conexion::conectar()->prepare("SELECT SUM(importe) as cuentas FROM $tablaCtaCte WHERE id_proveedor = $valor AND tipo=2");

		$stmt -> execute();

		return $stmt -> fetch();

		$stmt -> close();

		$stmt = null;

	}
	
	/*=============================================
	MOSTRAR CTA CTE Proveedores
	=============================================*/
	static public function mdlCuentasPagosListado($tablaCtaCte, $valor, $fecha){

		$stmt = Conexion::conectar()->prepare("SELECT SUM(importe) as cuentas FROM $tablaCtaCte WHERE id_proveedor = $valor AND tipo=2 AND fecha_movimiento <='$fecha'");

		$stmt -> execute();

		return $stmt -> fetch();

		$stmt -> close();

		$stmt = null;

	}

	/*=============================================
	MOSTRAR CTA CTE Proveedores
	=============================================*/
	static public function mdlMostrarCtaCteProveedorDos($tablaCtaCte, $valor){

		$stmt = Conexion::conectar()->prepare("SELECT * FROM $tablaCtaCte WHERE id_proveedor = $valor ORDER BY fecha_movimiento ASC");

		$stmt -> execute();

		return $stmt -> fetchAll();

		$stmt -> close();

		$stmt = null;

	}

	/*=============================================
	INGRESAR REGISTRO A CTA CTE Proveedores
	=============================================*/
	static public function mdlIngresarCtaCteProveedor($tabla, $datos){

		$stmt = Conexion::conectar()->prepare(
			"INSERT INTO $tabla(fecha_movimiento, id_proveedor, tipo, descripcion, id_compra, importe, metodo_pago, id_usuario) 
			VALUES (:fecha_movimiento,
			:id_proveedor,
			:tipo,
			:descripcion,
			:id_compra,
			:importe, 
			:metodo_pago,
			:id_usuario)");

		$stmt->bindParam(":fecha_movimiento", $datos["fecha_movimiento"], PDO::PARAM_STR);
		$stmt->bindParam(":id_proveedor", $datos["id_proveedor"], PDO::PARAM_INT);		
		$stmt->bindParam(":tipo", $datos["tipo"], PDO::PARAM_INT);
		$stmt->bindParam(":descripcion", $datos["descripcion"], PDO::PARAM_STR);
		$stmt->bindParam(":id_compra", $datos["id_compra"], PDO::PARAM_INT);
		$stmt->bindParam(":importe", $datos["importe"], PDO::PARAM_STR);
		$stmt->bindParam(":metodo_pago", $datos["metodo_pago"], PDO::PARAM_STR);
		$stmt->bindParam(":id_usuario", $datos["id_usuario"], PDO::PARAM_INT);
		
		if($stmt->execute()){

			return "ok";

		}else{

			return $stmt->errorInfo();
		
		}

		$stmt->close();
		$stmt = null;

	}

	/*=============================================
	INGRESAR REGISTRO A CTA CTE Proveedores
	=============================================*/
	static public function mdlIngresarPago($tabla, $datos){

		$stmt = Conexion::conectar()->prepare("INSERT INTO $tabla(id_compra, id_proveedor, fecha_movimiento, importe, tipo, estado, metodo_pago, descripcion, id_usuario) 
			VALUES (:id_compra,	:id_proveedor, :fecha_movimiento, :importe, :tipo, :estado, :metodo_pago, :descripcion, :id_usuario)");

		$stmt->bindParam(":id_compra", $datos["id_compra"], PDO::PARAM_INT);
		$stmt->bindParam(":id_proveedor", $datos["id_proveedor"], PDO::PARAM_INT);
		$stmt->bindParam(":fecha_movimiento", $datos["fecha_movimiento"], PDO::PARAM_STR);
		$stmt->bindParam(":importe", $datos["importe"], PDO::PARAM_STR);
		$stmt->bindParam(":tipo", $datos["tipo"], PDO::PARAM_INT);
		$stmt->bindParam(":estado", $datos["estado"], PDO::PARAM_INT);
		$stmt->bindParam(":metodo_pago", $datos["metodo_pago"], PDO::PARAM_STR);
		$stmt->bindParam(":id_usuario", $datos["id_usuario"], PDO::PARAM_INT);
		$stmt->bindParam(":descripcion", $datos["descripcion"], PDO::PARAM_STR);
		
		if($stmt->execute()){

			return "ok";

		}else{

			return $stmt->errorInfo();
		
		}

		$stmt->close();
		$stmt = null;

	}

	/*=============================================
	ELIMINAR VENTA
	=============================================*/
	static public function mdlEliminarCtaCteProveedores($tabla, $datos){

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
	MOSTRAR REGISTRO DE PAGO
	=============================================*/
	static public function mdlMostrarRegistroCtaCteProveedor($tabla, $idReg){	

		$stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla WHERE id = $idReg");

		$stmt -> execute();

		return $stmt -> fetch();

		$stmt -> close();

		$stmt = null;

	}

	/*=============================================
	LISTADO DE PROVEEDORES CON SALDO EN CUENTA CORRIENTE
	Esta consulta trae los proveedores donde total de compras - total de pagos es distindo de 0
	Usada en proveedores.php
	=============================================*/
	static public function mdlMostrarSaldos(){	

		//Solo traigo donde compras - compras es mayor a 0
		$stmt = Conexion::conectar()->prepare("SELECT DISTINCT cc.id_proveedor, p.nombre, p.telefono, p.email, SUM(IF (cc.tipo = 1, cc.importe, 0)) AS compras, -SUM(IF (cc.tipo = 0, cc.importe, 0)) AS pagos, (SUM(IF (cc.tipo = 0, cc.importe, 0)) - SUM(IF (cc.tipo = 1, cc.importe, 0))) as diferencia FROM proveedores_cuenta_corriente cc INNER JOIN proveedores p ON cc.id_proveedor = p.id GROUP BY cc.id_proveedor ORDER BY p.nombre");

		$stmt -> execute();

		return $stmt -> fetchAll();

		$stmt -> close();

		$stmt = null;

	}

	/*=============================================
	SALDO TOTAL EN CUENTA CORRIENTE
	Usada en proveedores-cuenta-saldos y en inicio
	=============================================*/
	static public function mdlMostrarSaldoTotal(){	

		$stmt = Conexion::conectar()->prepare("SELECT (SUM(IF (cc.tipo = 0, cc.importe, 0)) - SUM(IF (cc.tipo = 1, cc.importe, 0))) as saldo FROM proveedores_cuenta_corriente cc");

		$stmt -> execute();

		return $stmt -> fetch();

		$stmt -> close();

		$stmt = null;

	}
	
}