<?php

require_once "conexion.php";

class ModeloProductos{

	/*=============================================
	MOSTRAR PRODUCTOS
	=============================================*/
	static public function mdlMostrarProductos($tabla, $item, $valor, $orden){
		if($item != null){
			$stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla WHERE $item = :$item ORDER BY id DESC");
			$stmt -> bindParam(":".$item, $valor, PDO::PARAM_STR);
			$stmt -> execute();
			return $stmt -> fetch();
		}else{
	        $stmt = Conexion::conectar()->prepare("SELECT productos.*, proveedores.nombre FROM $tabla LEFT JOIN proveedores ON productos.id_proveedor = proveedores.id ORDER BY productos.$orden DESC");
			$stmt -> execute();
			return $stmt -> fetchAll();
		}
		$stmt -> close();
		$stmt = null;
	}
	
	/*=============================================
	MOSTRAR PRODUCTOS PAGINADOS
	=============================================*/
	static public function mdlMostrarProductosPaginados($desde, $limite){
        $stmt = Conexion::conectar()->prepare("SELECT productos.*, proveedores.nombre FROM productos LEFT JOIN proveedores ON productos.id_proveedor = proveedores.id ORDER BY productos.id ASC LIMIT $desde, $limite");
		$stmt -> execute();
		return $stmt -> fetchAll();
		$stmt -> close();
		$stmt = null;
	}
	
	/*=============================================
	ACTUALIZAR PRODUCTO
	=============================================*/
	static public function mdlActualizarProductoDos($tabla, $item1, $valor1, $valor){

		$nomUsuario = "";
		if (isset($_REQUEST['nombreVendedor'])) {
			$nomUsuario = $_REQUEST['nombreVendedor'];
		} elseif(isset($_SERVER['nombre'])){
			$nomUsuario = $_SERVER['nombre'];
		} else {
			$nomUsuario = '(sin especificar)';
		}

		$txt = $_SERVER['HTTP_REFERER'];
		$cambioDesde = '';
		if(strpos($txt, 'venta') !== false){
		    $cambioDesde = "Ventas";
		} else{
		    $cambioDesde = "(sin especificar)";
		}
		
		//$stmt = Conexion::conectar()->prepare("UPDATE $tabla SET $item1 = :$item1 WHERE id = :id");
		$stmt = Conexion::conectar()->prepare("UPDATE $tabla SET $item1 = :$item1, nombre_usuario = :nombre_usuario, cambio_desde = :cambio_desde WHERE id = :id");
		
		$stmt -> bindParam(":".$item1, $valor1, PDO::PARAM_STR);
		$stmt -> bindParam(":id", $valor, PDO::PARAM_STR);
		$stmt -> bindParam(":nombre_usuario", $nomUsuario, PDO::PARAM_STR);
		$stmt -> bindParam(":cambio_desde", $cambioDesde, PDO::PARAM_STR);

		if($stmt -> execute()){

			return "ok";
		
		}else{

			return "error";	

		}

		$stmt -> close();

		$stmt = null;

	}
	
		/*=============================================
	ACTUALIZAR PRODUCTO
	=============================================*/
	static public function actualizarProductoPos($tabla, $id, $idTienda, $tiendanube_variant_id,  $tiendanube_stock, $tiendanube_price){

		$stmt = Conexion::conectar()->prepare("UPDATE $tabla SET tiendanube_id = '$idTienda',  tiendanube_variant_id  = '$tiendanube_variant_id', tiendanube_stock = '$tiendanube_stock', tiendanube_price = '$tiendanube_price'  WHERE id = '$id'");

		if($stmt -> execute()){

			return "ok";
		
		}else{

			return "error";	

		}

		$stmt -> close();

		$stmt = null;

	}
	
	/*=============================================
	MOSTRAR PRODUCTO POR ID
	=============================================*/
	static public function mdlMostrarProductoXId($idProducto){
		$stmt = Conexion::conectar()->prepare("SELECT * FROM productos WHERE id = :id");
		$stmt -> bindParam(":id", $idProducto, PDO::PARAM_INT);
		$stmt -> execute();
		return $stmt -> fetch();
		$stmt -> close();
		$stmt = null;
	}

	/*=============================================
	REGISTRO DE PRODUCTO
	=============================================*/
	static public function mdlIngresarProducto($tabla, $datos){
		$stmt = Conexion::conectar()->prepare("INSERT INTO $tabla(id_categoria, codigo, id_proveedor, descripcion, stock, stock_medio, stock_bajo, precio_compra, precio_compra_dolar, margen_ganancia, tipo_iva, precio_venta, imagen, nombre_usuario, cambio_desde) VALUES (:id_categoria, :codigo, :id_proveedor, :descripcion, :stock, :stock_medio, :stock_bajo, :precio_compra, :precio_compra_dolar, :margen_ganancia, :tipo_iva, :precio_venta, :imagen, :nombre_usuario, 'Administrar Productos')");
		$stmt->bindParam(":id_categoria", $datos["id_categoria"], PDO::PARAM_INT);
		$stmt->bindParam(":codigo", $datos["codigo"], PDO::PARAM_STR);
		$stmt->bindParam(":id_proveedor", $datos["id_proveedor"], PDO::PARAM_INT);
		$stmt->bindParam(":descripcion", $datos["descripcion"], PDO::PARAM_STR);
		$stmt->bindParam(":stock", $datos["stock"], PDO::PARAM_STR);
		$stmt->bindParam(":stock_medio", $datos["stock_medio"], PDO::PARAM_STR);
		$stmt->bindParam(":stock_bajo", $datos["stock_bajo"], PDO::PARAM_STR);
		$stmt->bindParam(":precio_compra", $datos["precio_compra"], PDO::PARAM_STR);
		$stmt->bindParam(":precio_compra_dolar", $datos["precio_compra_dolar"], PDO::PARAM_STR);
		$stmt->bindParam(":margen_ganancia", $datos["margen_ganancia"], PDO::PARAM_STR);
		$stmt->bindParam(":tipo_iva", $datos["tipo_iva"], PDO::PARAM_STR);
		$stmt->bindParam(":precio_venta", $datos["precio_venta"], PDO::PARAM_STR);
		$stmt->bindParam(":imagen", $datos["imagen"], PDO::PARAM_STR);
		$stmt->bindParam(":nombre_usuario", $_SESSION['nombre'], PDO::PARAM_STR);

		if($stmt->execute()){
			return "ok";
		}else{
			return $stmt->errorInfo();
		}
		$stmt->close();
		$stmt = null;
	}

	/*=============================================
	EDITAR PRODUCTO
	=============================================*/
	static public function mdlEditarProducto($tabla, $datos){
		$stmt = Conexion::conectar()->prepare("UPDATE $tabla SET codigo = :codigo, id_categoria = :id_categoria, id_proveedor = :id_proveedor, descripcion = :descripcion, stock = :stock, stock_medio = :stock_medio, stock_bajo = :stock_bajo, precio_compra = :precio_compra, precio_compra_dolar = :precio_compra_dolar, margen_ganancia = :margen_ganancia, tipo_iva = :tipo_iva, precio_venta = :precio_venta, imagen = :imagen, nombre_usuario = :nombre_usuario, cambio_desde = 'Administrar Productos' WHERE id = :id");

		$stmt->bindParam(":id", $datos["id"], PDO::PARAM_INT);
		$stmt->bindParam(":id_categoria", $datos["id_categoria"], PDO::PARAM_INT);
		$stmt->bindParam(":codigo", $datos["codigo"], PDO::PARAM_STR);
		$stmt->bindParam(":id_proveedor", $datos["id_proveedor"], PDO::PARAM_INT);
		$stmt->bindParam(":descripcion", $datos["descripcion"], PDO::PARAM_STR);
		$stmt->bindParam(":stock", $datos["stock"], PDO::PARAM_STR);
		$stmt->bindParam(":stock_medio", $datos["stock_medio"], PDO::PARAM_STR);
		$stmt->bindParam(":stock_bajo", $datos["stock_bajo"], PDO::PARAM_STR);
		$stmt->bindParam(":precio_compra", $datos["precio_compra"], PDO::PARAM_STR);
		$stmt->bindParam(":precio_compra_dolar", $datos["precio_compra_dolar"], PDO::PARAM_STR);
		$stmt->bindParam(":margen_ganancia", $datos["margen_ganancia"], PDO::PARAM_STR);
		$stmt->bindParam(":tipo_iva", $datos["tipo_iva"], PDO::PARAM_STR);
		$stmt->bindParam(":precio_venta", $datos["precio_venta"], PDO::PARAM_STR);
		$stmt->bindParam(":imagen", $datos["imagen"], PDO::PARAM_STR);
		$stmt->bindParam(":nombre_usuario", $_SESSION['nombre'], PDO::PARAM_STR);

		if($stmt->execute()){
			return "ok";
		}else{
			return $stmt->errorInfo();
		}
		$stmt->close();
		$stmt = null;
	}

	/*=============================================
	BORRAR PRODUCTO
	=============================================*/
	static public function mdlEliminarProducto($tabla, $datos){
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
	ACTUALIZAR PRODUCTO
	=============================================*/
	static public function mdlActualizarProducto($tabla, $item1, $valor1, $valor, $cambioDesde){
		$nomUsuario = "";
		if (isset($_REQUEST['nombreVendedor'])) {
			$nomUsuario = $_REQUEST['nombreVendedor'];
		} elseif(isset($_SERVER['nombre'])){
			$nomUsuario = $_SERVER['nombre'];
		} elseif (isset($_SESSION['nombre'])) {
		    $nomUsuario = $_SESSION['nombre'];
		} else {
			$nomUsuario = '(sin especificar)';
		}

		/*$txt = $_SERVER['HTTP_REFERER'];
		$cambioDesde = '';
		if(strpos($txt, 'venta') !== false){
		    $cambioDesde = "Ventas";
		} else{
		    $cambioDesde = "(sin especificar)";
		}*/

		$stmt = Conexion::conectar()->prepare("UPDATE $tabla SET $item1 = :$item1, nombre_usuario = :nombre_usuario, cambio_desde = :cambio_desde WHERE id = :id");

		$stmt -> bindParam(":".$item1, $valor1, PDO::PARAM_STR);
		$stmt -> bindParam(":id", $valor, PDO::PARAM_INT);
		$stmt -> bindParam(":nombre_usuario", $nomUsuario, PDO::PARAM_STR);
		$stmt -> bindParam(":cambio_desde", $cambioDesde, PDO::PARAM_STR);

		if($stmt -> execute()){

			return "ok";
		
		}else{

			return $stmt->errorInfo();

		}

		$stmt -> close();

		$stmt = null;

	}

	/*=============================================
	MOSTRAR SUMA VENTAS
	=============================================*/	
	static public function mdlMostrarSumaVentas($tabla){
		$stmt = Conexion::conectar()->prepare("SELECT SUM(ventas) as total FROM $tabla");
		$stmt -> execute();
		return $stmt -> fetch();
		$stmt -> close();
		$stmt = null;
	}

	/*=============================================
	ACTUALIZAR PRODUCTO
	=============================================*/
	static public function mdlActualizarProductoCaja($tabla, $item1, $valor1, $valor, $cambioDesde){
	    
	    $nomUsuario = "";
		if (isset($_REQUEST['nombreVendedor'])) {
			$nomUsuario = $_REQUEST['nombreVendedor'];
		} elseif(isset($_SERVER['nombre'])){
			$nomUsuario = $_SERVER['nombre'];
		} else {
			$nomUsuario = '(sin especificar)';
		}

		$stmt = Conexion::conectar()->prepare("UPDATE $tabla SET $item1 = :$item1, cambio_desde = :cambio_desde, nombre_usuario = :nombre_usuario WHERE codigo = :codigo");

		$stmt -> bindParam(":".$item1, $valor1, PDO::PARAM_STR);
		$stmt -> bindParam(":codigo", $valor, PDO::PARAM_STR);
		$stmt -> bindParam(":cambio_desde", $cambioDesde, PDO::PARAM_STR);
		$stmt -> bindParam(":nombre_usuario", $nomUsuario, PDO::PARAM_STR);

		if($stmt -> execute()){

			return "ok";
		
		}else{

			return $stmt -> errorInfo();	

		}

		$stmt -> close();

		$stmt = null;

	}
	
		/*=============================================
	DEVOLVER ULTIMO ID
	=============================================*/

	static public function mdlUltimoId($tabla){	

		$stmt = Conexion::conectar()->prepare("SELECT MAX(id) as ultimo FROM $tabla");

		$stmt -> execute();

		return $stmt -> fetch();

		$stmt -> close();

		$stmt = null;

	}

	/*=============================================
	ACTUALIZAR STOCK PRODUCTO
	=============================================*/
	static public function mdlActualizarStockProductoCaja($sucursal, $valor1, $valor, $cambioDesde){
	    
	   	$nomUsuario = "";
		if (isset($_REQUEST['nombreVendedor'])) {
			$nomUsuario = $_REQUEST['nombreVendedor'];
		} elseif(isset($_SERVER['nombre'])){
			$nomUsuario = $_SERVER['nombre'];
		} else {
			$nomUsuario = '(sin especificar)';
		}

		$stmt = Conexion::conectar()->prepare("UPDATE productos SET $sucursal = :stock, cambio_desde = :cambio_desde, nombre_usuario = :nombre_usuario WHERE id = :id");

		$stmt -> bindParam(":stock", $valor1, PDO::PARAM_STR);
		$stmt -> bindParam(":id", $valor, PDO::PARAM_INT);
		$stmt -> bindParam(":cambio_desde", $cambioDesde, PDO::PARAM_STR);
		$stmt -> bindParam(":nombre_usuario", $nomUsuario, PDO::PARAM_STR);
		if($stmt -> execute()){
			return "ok";
		}else{
			return $stmt -> errorInfo();	
		}
		$stmt -> close();
		$stmt = null;
	}	

	/*=============================================
	MOSTRAR PRODUCTOS LISTADO
	=============================================*/
	static public function mdlMostrarProductosListado($tabla, $item, $valor){

		if($item != null){

			$stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla WHERE $item = :$item");

			$stmt -> bindParam(":".$item, $valor, PDO::PARAM_STR);

			$stmt -> execute();

			return $stmt -> fetch();

		}else{

			$stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla");

			$stmt -> execute();

			return $stmt -> fetchAll();

		}

		$stmt -> close();

		$stmt = null;

	}


	/*=============================================
	ESTADO STOCK PRODUCTOS (LISTADO)
	=============================================*/

	static public function mdlEstadoStockLista(){

		// stock critico
		// SELECT * FROM productos WHERE stock <= stock_bajo
		// stock regular
		// SELECT * FROM productos WHERE stock > stock_bajo AND stock <= stock_medio
		// stock bueno
		// SELECT * FROM productos WHERE stock >= stock_medio

		$stmt = Conexion::conectar()->prepare("SELECT * FROM productos");

		$stmt -> execute();

		return $stmt -> fetchAll();

		$stmt -> close();

		$stmt = null;

	}

	/*=============================================
	ESTADO STOCK PRODUCTOS (CANTIDAD)
	=============================================*/
	static public function mdlEstadoStockCantidad(){

		// stock critico
		// SELECT COUNT(*) FROM productos WHERE stock <= stock_bajo
		// stock regular
		// SELECT COUNT(*) FROM productos WHERE stock > stock_bajo AND stock <= stock_medio
		// stock bueno
		// SELECT COUNT(*) FROM productos WHERE stock >= stock_medio
		$stmt = Conexion::conectar()->prepare("SELECT * FROM productos");
		$stmt -> execute();
		return $stmt -> fetchAll();
		$stmt -> close();
		$stmt = null;
	}


	/*=============================================
	LISTAR PRODUCTOS CON STOCK MEDIO (ENTRE BAJO Y MEDIO)
	=============================================*/
	static public function mdlMostrarStockMedio(){
		//$stmt = Conexion::conectar()->prepare("SELECT * FROM productos WHERE (stock + stock_balloffet + stock_moreno + stock_edison) <= stock_medio AND (stock + stock_balloffet + stock_moreno + stock_edison) > stock_bajo ");
		$stmt = Conexion::conectar()->prepare("SELECT * FROM productos WHERE (IF(stock<0,0,stock)) <= stock_medio AND (IF(stock<0,0,stock)) > stock_bajo ");
		$stmt -> execute();
		return $stmt -> fetchAll();
		$stmt -> close();
		$stmt = null;
	}

	/*=============================================
	LISTAR PRODUCTOS CON STOCK BAJO
	=============================================*/
	static public function mdlMostrarStockBajo(){
		$stmt = Conexion::conectar()->prepare("SELECT * FROM productos WHERE (IF(stock<0,0,stock)) <= stock_bajo");
		$stmt -> execute();
		return $stmt -> fetchAll();
		$stmt -> close();
		$stmt = null;
	}

	/*=============================================
	LISTAR PRODUCTOS STOCK VALORIZADO
	=============================================*/
	static public function mdlMostrarStockValorizado(){
		$stmt = Conexion::conectar()->prepare("SELECT codigo, descripcion, (IF(stock<0,0,stock)) as stock, precio_compra, ROUND((IF(stock<0,0,stock)) * precio_compra, 2) as invertido, precio_venta, ROUND((IF(stock<0,0,stock)) * precio_venta, 2) as valorizado FROM productos WHERE (IF(stock<0,0,stock)) > 0");
		$stmt -> execute();
		return $stmt -> fetchAll();
		$stmt -> close();
		$stmt = null;
	}

	/*=============================================
	LISTAR PRODUCTOS STOCK VALORIZADO TOTALES
	=============================================*/
	static public function mdlMostrarStockValorizadoTotales(){
		$stmt = Conexion::conectar()->prepare("SELECT SUM(ROUND((IF(stock<0,0,stock)) * precio_compra,2)) as invertido, SUM(ROUND((IF(stock<0,0,stock)) * precio_venta,2)) as valorizado FROM productos WHERE stock > 0");
		$stmt -> execute();
		return $stmt -> fetch();
		$stmt -> close();
		$stmt = null;
	}


	/*=============================================
	AGREGAR PRODUCTO DESDE VENTA CAJA
	=============================================*/
	static public function mdlAgregarProductoVentaCaja($datos){
	$stmt = Conexion::conectar()->prepare("INSERT INTO productos(id_categoria, codigo, id_proveedor, descripcion, stock, stock_medio, stock_bajo, precio_compra, margen_ganancia, tipo_iva, precio_venta, imagen, nombre_usuario, cambio_desde) VALUES (1, :codigo, 1, :descripcion, '0', '0', '0', '0', '0', :tipo_iva, :precio_venta, 'vistas/img/productos/default/anonymous.png', :nombre_usuario, 'Crear Venta')");

		// $stmt->bindParam(":id_categoria", 1, PDO::PARAM_INT);
		$stmt->bindParam(":codigo", $datos["codigo"], PDO::PARAM_STR);
		// $stmt->bindParam(":id_proveedor", $datos["id_proveedor"], PDO::PARAM_INT);
		$stmt->bindParam(":descripcion", $datos["descripcion"], PDO::PARAM_STR);
		// $stmt->bindParam(":stock", "0", PDO::PARAM_STR);
		// $stmt->bindParam(":stock_medio", "0", PDO::PARAM_STR);
		// $stmt->bindParam(":stock_bajo", "0", PDO::PARAM_STR);
		// $stmt->bindParam(":precio_compra", "0", PDO::PARAM_STR);
		// $stmt->bindParam(":margen_ganancia", "0", PDO::PARAM_STR);
		$stmt->bindParam(":tipo_iva", $datos["tipo_iva"], PDO::PARAM_STR);
		$stmt->bindParam(":precio_venta", $datos["precio_venta"], PDO::PARAM_STR);
		$stmt->bindParam(":nombre_usuario", $_SESSION['nombre'], PDO::PARAM_STR);

		if($stmt->execute()){
			return "ok";
		}else{
			return $stmt->errorInfo();
		}
		$stmt->close();
		$stmt = null;
	}

	/*=============================================
	ACTUALIZAR precio productos
	=============================================*/
	static public function mdlActualizarPrecioVenta($datos){

		$stmt = Conexion::conectar()->prepare("UPDATE productos SET precio_venta = :precio_venta, nombre_usuario = :nombre_usuario, cambio_desde = ''  WHERE codigo = :codigo");

		$stmt -> bindParam(":precio_venta", $datos["precio_venta"], PDO::PARAM_STR);
		$stmt -> bindParam(":codigo", $datos["codigoProducto"], PDO::PARAM_STR);
		$stmt -> bindParam(":nombre_usuario", $_SESSION['nombre'], PDO::PARAM_STR);

		if($stmt -> execute()){

			return "ok";
		
		}else{

			return $stmt->errorInfo();

		}

		$stmt -> close();

		$stmt = null;

	}


	/*=============================================
	ACTUALIZAR PRECIO DE PRODUCTOS POR CATEGORIA
	=============================================*/	
	static public function mdlModificarPrecioCategoria($tabla, $id_categoria, $porcentaje){

		$stmt = Conexion::conectar()->prepare(
		"UPDATE productos SET precio_compra = precio_compra + (precio_compra * :porcentaje / 100), precio_venta = precio_venta + (precio_venta * :porcentaje / 100), nombre_usuario = :nombre_usuario, cambio_desde = 'Administrar Categorias' WHERE id_categoria = :id_categoria;");

		$stmt->bindParam(":porcentaje", $porcentaje, PDO::PARAM_STR);
		$stmt->bindParam(":id_categoria", $id_categoria, PDO::PARAM_INT);
		$stmt->bindParam(":nombre_usuario", $_SESSION['nombre'] , PDO::PARAM_STR);
		
		if($stmt->execute()){

			return "ok";

		}else{

			return $stmt->errorInfo();
		
		}

		$stmt->close();
		$stmt = null;
	}
	
	public static function mdlObtenerTiendanubeId($tabla, $idProducto){
        $sql = "SELECT tiendanube_id FROM $tabla WHERE id = :id";
        $stmt = Conexion::conectar()->prepare($sql);
        $stmt->bindParam(":id", $idProducto, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
        }


	/*=============================================
	ACTUALIZAR PRECIO DE PRODUCTOS POR PROVEEDOR
	=============================================*/	
	static public function mdlModificarPrecioProveedor($tabla, $id_proveedor, $porcentaje){
		$stmt = Conexion::conectar()->prepare(
		"UPDATE productos SET precio_compra = precio_compra + (precio_compra * :porcentaje / 100), precio_venta = precio_venta + (precio_venta * :porcentaje / 100), nombre_usuario = :nombre_usuario, cambio_desde = 'Administrar proveedores' WHERE id_proveedor = :id_proveedor;");
		
		$stmt->bindParam(":porcentaje", $porcentaje, PDO::PARAM_STR);
		$stmt->bindParam(":id_proveedor", $id_proveedor, PDO::PARAM_INT);
		$stmt->bindParam(":nombre_usuario", $_SESSION['nombre'] , PDO::PARAM_STR);
		
		if($stmt->execute()){

			return "ok";

		}else{

			return $stmt->errorInfo();
		
		}

		$stmt->close();
		$stmt = null;
	}

	/*=============================================
	ACTUALIZAR PRECIO COMPRA DE PRODUCTOS POR COTIZACION
	=============================================*/	
	static public function mdlModificarPrecioCotizacion($pesos){

		$stmt = Conexion::conectar()->prepare("UPDATE productos SET precio_compra = (precio_compra_dolar * :pesos) WHERE precio_compra_dolar <> 0;");

		$stmt->bindParam(":pesos", $pesos, PDO::PARAM_STR);
		$stmt->bindParam(":nombre_usuario", $_SESSION["nombre"], PDO::PARAM_STR);
		
		if($stmt->execute()){

			return "ok";

		}else{

			return $stmt->errorInfo();
		
		}

		$stmt->close();
		$stmt = null;
	}

	/*=============================================
	productos filtraor por autocomplete
	=============================================*/	
	static public function mdlMostrarProductosFiltrados($tabla, $filtro){

		$stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla WHERE codigo LIKE '%$filtro%' OR descripcion LIKE '%$filtro%' ORDER BY descripcion LIMIT 0,20");
		$stmt -> execute();

		return $stmt -> fetchAll();

		$stmt -> close();

		$stmt = null;
	}

	/*=============================================
	MOSTRAR PRODUCTOS EXCEL
	=============================================*/
	static public function mdlMostrarProductosExcel($tabla, $datosConsulta){

		$stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla WHERE codigo = :codigo AND id_proveedor = :id_proveedor ORDER BY id DESC");

		$stmt -> bindParam(":codigo", $datosConsulta["codigo"], PDO::PARAM_STR);
		$stmt -> bindParam(":id_proveedor", $datosConsulta["id_proveedor"], PDO::PARAM_INT);

		$stmt -> execute();

		return $stmt -> fetch();

		$stmt -> close();

		$stmt = null;

	}

	/*=============================================
	REGISTRO DE PRODUCTO - EXCEL
	=============================================*/
	static public function mdlIngresarProductoExcel($tabla, $datos){

		$stmt = Conexion::conectar()->prepare("INSERT INTO $tabla(id_categoria, id_proveedor, codigo, descripcion, imagen, precio_compra, precio_compra_dolar, tipo_iva, precio_venta, nombre_usuario, cambio_desde) VALUES (:id_categoria, :id_proveedor, :codigo, :descripcion, 'vistas/img/productos/default/anonymous.png', :precio_compra, :precio_compra_dolar, :tipo_iva, :precio_venta, :nombre_usuario, 'Importar Excel')");
										
		$stmt->bindParam(":id_proveedor", $datos["id_proveedor"], PDO::PARAM_INT);
		$stmt->bindParam(":id_categoria", $datos["id_categoria"], PDO::PARAM_INT);
		$stmt->bindParam(":codigo", $datos["codigo"], PDO::PARAM_STR);
		$stmt->bindParam(":descripcion", $datos["descripcion"], PDO::PARAM_STR);
		$stmt->bindParam(":precio_compra", $datos["precio_compra"], PDO::PARAM_STR);
		$stmt->bindParam(":precio_compra_dolar", $datos["precio_compra_dolar"], PDO::PARAM_STR);
		$stmt->bindParam(":tipo_iva", $datos["tipo_iva"], PDO::PARAM_STR);
		$stmt->bindParam(":precio_venta", $datos["precio_venta"], PDO::PARAM_STR);
		$stmt->bindParam(":nombre_usuario", $_SESSION['nombre'], PDO::PARAM_STR);

		if($stmt->execute()){

			return "ok";

		}else{

			return $stmt->errorInfo();

		}

		$stmt->close();
		$stmt = null;

	}


	/*=============================================
	EDITAR PRODUCTO - EXCEL
	=============================================*/
	static public function mdlEditarProductoExcel($tabla, $datos){

		$stmt = Conexion::conectar()->prepare("UPDATE $tabla SET 
					descripcion = :descripcion,
					precio_compra = :precio_compra, 
					tipo_iva = :tipo_iva,
					precio_venta = :precio_venta,
					nombre_usuario = :nombre_usuario, 
					cambio_desde = :cambio_desde 
					WHERE codigo = :codigo");

		$stmt->bindParam(":descripcion", $datos["descripcion"], PDO::PARAM_STR);
		$stmt->bindParam(":precio_compra", $datos["precio_compra"], PDO::PARAM_STR);
		$stmt->bindParam(":tipo_iva", $datos["tipo_iva"], PDO::PARAM_STR);
		$stmt->bindParam(":precio_venta", $datos["precio_venta"], PDO::PARAM_STR);
		$stmt->bindParam(":nombre_usuario", $_SESSION['nombre'], PDO::PARAM_STR);
		$moduloSistema = 'Importar Excel (basico)';
		$stmt->bindParam(":cambio_desde", $moduloSistema, PDO::PARAM_STR);
		$stmt->bindParam(":codigo", $datos["codigo"], PDO::PARAM_STR);

		if($stmt->execute()){

			return "ok";

		}else{

			return $stmt->errorInfo();

		}

		$stmt->close();
		$stmt = null;

	}

	/*=============================================
	ACTUALIZAR PRODUCTO COMPRA (CREAR COMPRA)
	=============================================*/	
	static public function mdlActualizarProductoCompraIngreso($precioCompra, $ganancia, $precioVenta, $valor, $cambioDesde){
		
		$stmt = Conexion::conectar()->prepare("UPDATE productos SET precio_compra = :precio_compra, margen_ganancia = :margen_ganancia, precio_venta = :precio_venta, nombre_usuario = :nombre_usuario, cambio_desde = :cambio_desde WHERE id = :id");

		$stmt->bindParam(":precio_compra", $precioCompra, PDO::PARAM_STR);
		$stmt->bindParam(":margen_ganancia", $ganancia, PDO::PARAM_STR);
		$stmt->bindParam(":precio_venta", $precioVenta, PDO::PARAM_STR);
		$stmt->bindParam(":nombre_usuario", $_SESSION['nombre'], PDO::PARAM_STR);
		$stmt->bindParam(":cambio_desde", $cambioDesde, PDO::PARAM_STR);

		$stmt->bindParam(":id", $valor, PDO::PARAM_INT);

		if($stmt->execute()){

			return "ok";

		}else{

			return $stmt->errorInfo();
		
		}

		$stmt->close();
		$stmt = null;

	}

	/*=============================================
	LISTAR HISTORIAL DE PRODUCTOS
	=============================================*/
	static public function mdlMostrarProductosHistorial($idProducto){

		$stmt = Conexion::conectar()->prepare("SELECT * FROM productos_cambios WHERE id_prod = :id_prod ORDER BY fecha_hora DESC");
		
		$stmt -> bindParam(":id_prod", $idProducto, PDO::PARAM_INT);

		$stmt -> execute();

		return $stmt -> fetchAll();

		$stmt -> close();

		$stmt = null;
	}

	/*=============================================
	MOSTRAR CATEGORIA DE PRODUCTOS
	=============================================*/
	static public function mdlMostrarCategoriaProducto($idProducto){

		$stmt = Conexion::conectar()->prepare("SELECT c.id, c.categoria FROM productos p INNER JOIN categorias c ON p.id_categoria = c.id WHERE p.id = :id_prod");
		
		$stmt -> bindParam(":id_prod", $idProducto, PDO::PARAM_INT);

		$stmt -> execute();

		return $stmt -> fetch();

		$stmt -> close();

		$stmt = null;
	}

	/*=============================================
	PRODUCTOS MAS VENDIDOS
	=============================================*/
	static public function mdlMostrarProductosMasVendidos($fechaInicial, $fechaFinal){

		$caracter = '"';
		
		$stmt = Conexion::conectar()->prepare("SELECT REPLACE(REPLACE(REPLACE(REPLACE(JSON_EXTRACT(productos, '$[*].id'), '$caracter',''), '[', ''), ']',''), ' ', '') as productosA, REPLACE(REPLACE(REPLACE(REPLACE(JSON_EXTRACT(productos, '$[*].cantidad'), '$caracter',''), '[', ''), ']',''), ' ', '') as cantidadesA, REPLACE(REPLACE(REPLACE(REPLACE(JSON_EXTRACT(productos, '$[*].descripcion'), '$caracter',''), '[', ''), ']',''), ' ', '') as descripcionA  FROM ventas WHERE fecha BETWEEN '$fechaInicial%' AND '$fechaFinal'");

		$stmt -> execute();

		return $stmt -> fetchAll();

		$stmt -> close();

		$stmt = null;
	}

	/*=============================================
	REGISTRO DE AJUSTE DE STOCK DE PRODUCTO
	=============================================*/
	static public function mdlIngresarAjusteStockProducto($datos){

        $almacen = $datos['almacen'];
		$stmtEditar = Conexion::conectar()->prepare("UPDATE productos SET $almacen = :stock, nombre_usuario = :nombre_usuario, cambio_desde = 'Ajuste de Stock' WHERE id = :id");

		$stmtEditar->bindParam(":id", $datos["id"], PDO::PARAM_STR);
		$stmtEditar->bindParam(":stock", $datos["stock_actual"], PDO::PARAM_STR);
        $stmtEditar->bindParam(":nombre_usuario", $_SESSION['nombre'], PDO::PARAM_STR);

		if($stmtEditar->execute()){

			return "ok";

		} else {

			return $stmtEditar->errorInfo();

		}

		$stmtEditar->close();
		$stmtEditar = null;

	}
	
	/*=============================================
	MUESTRO COLUMNAS TABLA PRODUCTOS PARA USAR EN EXCEL
	=============================================*/
	static public function mdlEstructuraTablaProductos(){
		$stmt = Conexion::conectar()->prepare("SHOW COLUMNS FROM productos WHERE FIELD NOT IN ('id','ventas','fecha','nombre_usuario','cambio_desde')");
	    $stmt -> execute();
		return $stmt -> fetchAll();
		$stmt -> close();
		$stmt = null;
	}

	/*=============================================
	EJECUTAR QUERY PRODUCTOS
	=============================================*/	
	static public function mdlEjecutarQueryProductos($query){
		
		$stmt = Conexion::conectar()->prepare($query);

		if($stmt->execute()){

			return "ok";

		}else{

			return $stmt->errorInfo();
		
		}

		$stmt->close();
		$stmt = null;

	}

	/*=============================================
	MOSTRAR ULTIMA FECHA ACTUALIZADA
	=============================================*/
	static public function mdlFechaActualizacion(){
		$stmt = Conexion::conectar()->prepare("SELECT MAX(fecha) as fecha FROM productos");
		$stmt -> execute();
		return $stmt -> fetch();
		$stmt -> close();
		$stmt = null;
	}
	
	/*=============================================
	MOSTRAR PRODUCTOS QUE COINCIDA CON CODIGO O CODIGOPROVEEDOR
	=============================================*/
	static public function mdlMostrarProductosLector($valor){

		$stmt = Conexion::conectar()->prepare("SELECT * FROM productos WHERE codigo = :valor");

		$stmt -> bindParam(":valor", $valor, PDO::PARAM_STR);

		$stmt -> execute();

		return $stmt -> fetch();

		$stmt -> close();

		$stmt = null;

	}
	
		/*=============================================
	BORRAR PRODUCTO MASIVO
	=============================================*/

	static public function mdlBorrarProductosMasivo($tabla, $datos){

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

}