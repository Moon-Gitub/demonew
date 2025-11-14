<?php

require_once "../controladores/productos.controlador.php";
require_once "../modelos/productos.modelo.php";

require_once "../controladores/categorias.controlador.php";
require_once "../modelos/categorias.modelo.php";

class AjaxProductos{

  public $idCategoria;
  public $codigoProducto;
  public $idProducto;
  public $traerProductos;
  public $nombreProducto;
  public $idProductoBorrar;
  public $idCatUltCod;
  public $idPro;
  public $txtBuscado;

  /*=============================================
  GENERAR CÓDIGO A PARTIR DE ID CATEGORIA
  =============================================*/
  public function ajaxCrearCodigoProducto(){

    $item = "id_categoria";
    $valor = $this->idCategoria;
    $orden = "id";

    $respuesta = ControladorProductos::ctrMostrarProductos($item, $valor, $orden);

    echo json_encode($respuesta);

  }

  /*=============================================
  TRAER PRODUCTO POR CODIGO
  =============================================*/
  public function ajaxListarProducto(){

    $item = "codigo";
    $valor = $this->codigoProducto;
    $orden = "id";

    $respuesta = ControladorProductos::ctrMostrarProductos($item, $valor, $orden);

    echo json_encode($respuesta);

  }

  public function ajaxListarProductos(){

    $item = null;
    $valor = null;

    $respuesta = ControladorProductos::ctrMostrarProductosListado($item, $valor);

    echo json_encode($respuesta);

  }
  
  /*=============================================
  EDITAR PRODUCTO
  =============================================*/ 
  public function ajaxEditarProducto(){

    if($this->traerProductos == "ok"){

      $item = null;
      $valor = null;
      $orden = "id";

      $respuesta = ControladorProductos::ctrMostrarProductos($item, $valor,
        $orden);

      echo json_encode($respuesta);


    }else if($this->nombreProducto != ""){

      $item = "descripcion";
      $valor = $this->nombreProducto;
      $orden = "id";

      $respuesta = ControladorProductos::ctrMostrarProductos($item, $valor,
        $orden);

      echo json_encode($respuesta);

    }else{

      $item = "id";
      $valor = $this->idProducto;
      $orden = "id";

      $respuesta = ControladorProductos::ctrMostrarProductos($item, $valor,
        $orden);

      echo json_encode($respuesta);

    }

  }
  
  /*=============================================
  Borrar Masivo
  =============================================*/
  public function ajaxBorrarProductos(){

    $valor = $this->idProductoBorrar;

    $respuesta = ControladorProductos::ctrBorrarProductosMasivo($valor);

    echo json_encode($respuesta);

  }

  /*=============================================
  ULTIMO CÓDIGO PRODUCTO
  =============================================*/
  public function ajaxUltimoCodigoProducto(){

    $valor = $this->idCatUltCod;

    $respuesta = ControladorProductos::ctrUltimoCodigoProductos($valor);

    echo json_encode($respuesta);

  }

  public function ajaxAgregarProductoVentaCaja($datosProducto){

    $respuesta = ControladorProductos::ctrAgregarProductoVentaCaja($datosProducto);

    echo json_encode($respuesta);

  }

  
  public function ajaxActualizarPrecioVenta($datosProducto){

    $respuesta = ControladorProductos::ctrActualizarPrecioVenta($datosProducto);

    echo json_encode($respuesta);

  }

  /*=============================================
  MOSTRAR PRODUCTO POR CODIGO O CODIGOPROVEEDOR (usado en crear-venta para traer con lector de codigo de barra)
  =============================================*/ 
  public function ajaxMostrarProductoLector($valor){

    $respuesta = ControladorProductos::ctrMostrarProductosLector($valor);

    echo json_encode($respuesta);

  }
  
   
  public function ajaxTraerProducto(){
    $item = "id";
    $valor = $this->idPro;
    $orden = "id";

    $respuesta = ControladorProductos::ctrMostrarProductos($item, $valor, $orden);

    echo json_encode($respuesta);

  }

  /*=============================================
  LISTAR PRODUCTOS AUTOCOMPLETAR
  =============================================*/
  public function ajaxListadoProductosAutocompletar(){

      $respuesta = ControladorProductos::ctrMostrarProductosFiltrados($this->txtBuscado);

      $listaProducto = [];
      foreach ($respuesta as $key => $value) {
        array_push($listaProducto, 
            array(
              'label' => $value["codigo"] . ' - ' . $value["descripcion"],
              'value' => array(
                            'id' => $value["id"],
                            'codigo' => $value["codigo"],
                            'descripcion' => $value["descripcion"],
                            'stock' => $value["stock"],
                            'tipo_iva' => $value["tipo_iva"],
                            'precio_venta' => $value["precio_venta"]                            
                             )
                  )
            );
      }

      echo json_encode($listaProducto);

  }  
 
 	//FUNCION PARA VALIDAR SI EL CODIGO QUE INGREAN YA SE ENCUENTRA EN LA BD
 	public function ajaxValidarCodigoProducto($codigo){

		$respuesta = ControladorProductos::ctrMostrarProductos('codigo', $codigo, null);

		echo json_encode($respuesta);

	}

}


/*=============================================
GENERAR CÓDIGO A PARTIR DE ID CATEGORIA
=============================================*/ 

if(isset($_POST["idCategoria"])){

  $codigoProducto = new AjaxProductos();
  $codigoProducto -> idCategoria = $_POST["idCategoria"];
  $codigoProducto -> ajaxCrearCodigoProducto();

}
/*=============================================
EDITAR PRODUCTO
=============================================*/ 

if(isset($_POST["idProducto"])){

  $editarProducto = new AjaxProductos();
  $editarProducto -> idProducto = $_POST["idProducto"];
  $editarProducto -> ajaxEditarProducto();

}

/*=============================================
TRAER PRODUCTO
=============================================*/ 

if(isset($_POST["traerProductos"])){

  $traerProductos = new AjaxProductos();
  $traerProductos -> traerProductos = $_POST["traerProductos"];
  $traerProductos -> ajaxEditarProducto();

}

/*=============================================
TRAER PRODUCTO
=============================================*/ 

if(isset($_POST["nombreProducto"])){

  $traerProductos = new AjaxProductos();
  $traerProductos -> nombreProducto = $_POST["nombreProducto"];
  $traerProductos -> ajaxEditarProducto();

}

/*=============================================
TRAER PRODUCTO POR CODIGO
=============================================*/ 

if(isset($_POST["codigoProducto"])){

  $traerProducto = new AjaxProductos();
  $traerProducto -> codigoProducto = $_POST["codigoProducto"];
  $traerProducto -> ajaxListarProducto();

}

/*=============================================
ULTIMO CODIGO POR CATEGORIA | MARCA | PROVEEDOR
=============================================*/ 

if(isset($_POST["idCatUltCod"])){

  $traerProducto = new AjaxProductos();
  $traerProducto -> idCatUltCod = $_POST["idCatUltCod"];
  $traerProducto -> ajaxUltimoCodigoProducto();

}

if(isset($_POST["listarProductos"])){

  $productos = new AjaxProductos();
  //$cliente -> idCliente = $_POST["idCliente"];
  $productos -> ajaxListarProductos();

}

/*=============================================
AGERGAR PRODUCTOS DESDE VENTA CAJA
=============================================*/ 
if(isset($_POST["productoVentaCaja"])){

  $productos = new AjaxProductos();
  $productos -> ajaxAgregarProductoVentaCaja($_POST);

}

/*=============================================
ACTUALIZO EL PRECIO DE VENTA SI ES 0 (DESCARTADOS CODIGOS DEL 1 AL 10)
=============================================*/ 
if(isset($_POST["actualizarPrecio"])){

  $productos = new AjaxProductos();
  $productos -> ajaxActualizarPrecioVenta($_POST);

}

if(isset($_GET["listadoProd"])){

  $traerProducto = new AjaxProductos();
  $traerProducto -> txtBuscado = $_GET["listadoProd"];
  $traerProducto -> ajaxListadoProductosAutocompletar();

}

/*=============================================
VALIDAR NO REPETIR CODIGO PRODUCTO
=============================================*/
if(isset( $_POST["validarCodigoProducto"])){

	$valCod = new AjaxProductos();
	$valCod -> ajaxValidarCodigoProducto($_POST["validarCodigoProducto"]);

}



/*=============================================
TRAER PRODUCTO IMPRIMIR PRECIOS
=============================================*/ 

if(isset($_POST["idPro"])){

  $editarProducto = new AjaxProductos();
  $editarProducto -> idPro = $_POST["idPro"];
  $editarProducto -> ajaxTraerProducto();

}

/*=============================================
BORRAR PRODUCTO MASIVO
=============================================*/ 
if(isset($_POST["idProductoBorrar"])){
  $borrarProductos = new AjaxProductos();
  $borrarProductos -> idProductoBorrar = $_POST["idProductoBorrar"];
  $borrarProductos -> ajaxBorrarProductos();
}
/*=============================================
MOSTRAR PRODUCTO POR CODIGO O CODIGOPROVEEDOR (usado en crear-venta para traer con lector de codigo de barra)
=============================================*/ 
if(isset($_POST["idProductoLector"])){
  $codProv = new AjaxProductos();
  $codProv -> ajaxMostrarProductoLector($_POST["idProductoLector"]);

}