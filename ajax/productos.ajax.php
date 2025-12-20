<?php
// ✅ Seguridad AJAX
require_once "seguridad.ajax.php";

// Para autocomplete (GET) no requerir CSRF, solo sesión y AJAX
if (isset($_GET["listadoProd"])) {
    SeguridadAjax::inicializar(false); // false = no verificar CSRF para GET
} else {
    SeguridadAjax::inicializar(); // Verificar CSRF para POST
}


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

    // Primero buscar en productos normales
    $respuesta = ControladorProductos::ctrMostrarProductos($item, $valor, $orden);

    // Si no se encuentra, buscar en combos
    if(!$respuesta || (is_array($respuesta) && empty($respuesta))){
      // Cargar controlador de combos si existe
      if(file_exists(dirname(__DIR__) . "/controladores/combos.controlador.php")){
        require_once dirname(__DIR__) . "/controladores/combos.controlador.php";
        
        // Buscar combo por código
        $combo = ControladorCombos::ctrMostrarCombos("codigo", $valor);
        
        if($combo && is_array($combo) && !empty($combo)){
          // Obtener el producto base del combo
          $productoBase = ControladorProductos::ctrMostrarProductos("id", $combo["id_producto"], "id");
          
          if($productoBase && is_array($productoBase) && !empty($productoBase)){
            // Si es un array de productos, tomar el primero
            if(isset($productoBase[0])){
              $productoBase = $productoBase[0];
            }
            
            // Formatear respuesta como producto pero con flag de combo
            $respuesta = array(
              "id" => $combo["id_producto"],
              "codigo" => $combo["codigo"],
              "descripcion" => $combo["nombre"],
              "precio_venta" => $combo["precio_venta"],
              "precio_venta_mayorista" => $combo["precio_venta_mayorista"],
              "tipo_iva" => $combo["tipo_iva"],
              "imagen" => $combo["imagen"] ? $combo["imagen"] : ($productoBase["imagen"] ?? ""),
              "id_categoria" => $productoBase["id_categoria"] ?? null,
              "id_proveedor" => $productoBase["id_proveedor"] ?? null,
              "stock" => $productoBase["stock"] ?? 0,
              "stock_1" => $productoBase["stock_1"] ?? 0,
              "stock_2" => $productoBase["stock_2"] ?? 0,
              "stock_3" => $productoBase["stock_3"] ?? 0,
              "es_combo" => true, // Flag para indicar que es combo
              "id_combo" => $combo["id"] // ID del combo para referencia
            );
          }
        }
      }
    }

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
    // Limpiar cualquier output previo
    if (ob_get_level()) {
      ob_clean();
    }
    
    // Establecer header JSON
    header('Content-Type: application/json; charset=utf-8');

    if(isset($this->idProducto) && $this->idProducto != "" && $this->idProducto != null){

      $item = "id";
      $valor = $this->idProducto;
      $orden = "id";

      $respuesta = ControladorProductos::ctrMostrarProductos($item, $valor, $orden);

      // Devolver solo el primer producto (debería ser único por ID)
      if(is_array($respuesta) && !empty($respuesta) && isset($respuesta[0])){
        echo json_encode($respuesta[0]);
        exit; // Asegurar que no se ejecute más código
      } else if(is_array($respuesta) && empty($respuesta)){
        // Si el array está vacío, devolver error
        echo json_encode(array("error" => "Producto no encontrado"));
        exit;
      } else {
        // Si no es array, devolver tal cual
        echo json_encode($respuesta);
        exit;
      }

    }else if($this->traerProductos == "ok"){

      $item = null;
      $valor = null;
      $orden = "id";

      $respuesta = ControladorProductos::ctrMostrarProductos($item, $valor,
        $orden);

      echo json_encode($respuesta);
      exit;


    }else if($this->nombreProducto != ""){

      $item = "descripcion";
      $valor = $this->nombreProducto;
      $orden = "id";

      $respuesta = ControladorProductos::ctrMostrarProductos($item, $valor,
        $orden);

      echo json_encode($respuesta);
      exit;

    }else{

      $item = "id";
      $valor = $this->idProducto;
      $orden = "id";

      $respuesta = ControladorProductos::ctrMostrarProductos($item, $valor,
        $orden);

      echo json_encode($respuesta);
      exit;

    }

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

      // Establecer header JSON
      header('Content-Type: application/json; charset=utf-8');
      
      try {
          $respuesta = ControladorProductos::ctrMostrarProductosFiltrados($this->txtBuscado);

          $listaProducto = [];
          
          // Validar que $respuesta sea un array
          if (is_array($respuesta)) {
              foreach ($respuesta as $key => $value) {
                  // Validar que cada elemento tenga los campos necesarios
                  if (isset($value["id"]) && isset($value["codigo"]) && isset($value["descripcion"])) {
                      array_push($listaProducto, 
                          array(
                            'label' => $value["codigo"] . ' - ' . $value["descripcion"],
                            'value' => array(
                                          'id' => $value["id"],
                                          'codigo' => $value["codigo"],
                                          'descripcion' => $value["descripcion"],
                                          'stock' => $value["stock"] ?? 0,
                                          'tipo_iva' => $value["tipo_iva"] ?? 0,
                                          'precio_venta' => $value["precio_venta"] ?? 0                            
                                           )
                                )
                          );
                  }
              }
          }
          
          echo json_encode($listaProducto);
          
      } catch (Exception $e) {
          // En caso de error, devolver array vacío
          error_log("Error en ajaxListadoProductosAutocompletar: " . $e->getMessage());
          echo json_encode([]);
      }

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

if(isset($_POST["idProducto"]) && (!isset($_POST["traerProductos"]) || $_POST["traerProductos"] != "ok")){

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
  // Si también se envía idProducto, asignarlo para obtener un producto específico
  if(isset($_POST["idProducto"]) && $_POST["idProducto"] != "" && $_POST["idProducto"] != null){
    $traerProductos -> idProducto = $_POST["idProducto"];
  }
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

if(isset($_GET["listadoProd"]) || isset($_POST["listadoProd"])){

  $traerProducto = new AjaxProductos();
  $traerProducto -> txtBuscado = $_GET["listadoProd"] ?? $_POST["listadoProd"] ?? '';
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
MOSTRAR PRODUCTO POR CODIGO O CODIGOPROVEEDOR (usado en crear-venta para traer con lector de codigo de barra)
=============================================*/ 
if(isset($_POST["idProductoLector"])){
  $codProv = new AjaxProductos();
  $codProv -> ajaxMostrarProductoLector($_POST["idProductoLector"]);

}