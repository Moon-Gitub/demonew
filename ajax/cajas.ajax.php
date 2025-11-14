<?php

require_once "../controladores/cajas.controlador.php";
require_once "../modelos/cajas.modelo.php";

require_once "../controladores/caja-cierres.controlador.php";
require_once "../modelos/caja-cierres.modelo.php";

require_once "../controladores/categorias.controlador.php";
require_once "../modelos/categorias.modelo.php";

require_once "../controladores/ventas.controlador.php";
require_once "../modelos/ventas.modelo.php";

require_once "../controladores/productos.controlador.php";
require_once "../modelos/productos.modelo.php";

require_once "../controladores/clientes.controlador.php";
require_once "../modelos/clientes.modelo.php";

require_once "../controladores/proveedores.controlador.php";
require_once "../modelos/proveedores.modelo.php";

require_once "../controladores/usuarios.controlador.php";
require_once "../modelos/usuarios.modelo.php";


class AjaxCajas {

  public $caracteres;

  /*=============================================
  LISTAR AUTOCOMPLETAR TEXTO
  =============================================*/
  public function ajaxListadoDescripcion(){

      $respuesta = ControladorCajas::ctrMostrarDescripcion($this->caracteres);

      $lstDesc = [];
      foreach ($respuesta as $key => $value) {
        array_push($lstDesc, 
            array(
              'label' => $value["descripcion"],
              'value' => $value["descripcion"]
                )
            );
      }

      echo json_encode($lstDesc);

  }  

  /*=============================================
  INFORME CIERRE DE CAJA
  =============================================*/
  public function ajaxInformeCierreCajas($idCierre){
      $respuesta = ControladorCajaCierres::ctrInformeCierreCajas($idCierre);
      echo json_encode($respuesta);
  } 
  
  /*=============================================
  LISTAR AUTOCOMPLETAR TEXTO
  =============================================*/
  public function ajaxListadoCierreCajas($idCierre){
      $respuesta = ControladorCajaCierres::ctrMovimientosCierreCajas($idCierre);
      echo json_encode($respuesta);
  } 

  /*=============================================
  CIERRE DE CAJA POR CAJERO
  =============================================*/
  public function ajaxCierreCajaPorCajero($datos){
      $respuesta = ControladorCajaCierres::ctrCierreCajaCajero($datos);
      echo json_encode($respuesta);
  } 
}

/*=============================================
BUSCAR TEXTO
=============================================*/ 
if(isset($_GET["listadoDesc"])){
  $traerProducto = new AjaxCajas();
  $traerProducto -> caracteres = $_GET["listadoDesc"];
  $traerProducto -> ajaxListadoDescripcion();
}

/*=============================================
INFORME CIERRE DE CAJA
=============================================*/ 
if(isset($_POST["esteCierre"])) {
  $data = new AjaxCajas();
  $data -> ajaxInformeCierreCajas($_POST["esteCierre"]);
}

/*=============================================
LISTADO DE MOVIMIENTOS DE CIERRE DE CAJA
=============================================*/ 
if(isset($_POST["esteCierreListado"])) {
  $data = new AjaxCajas();
  $data -> ajaxListadoCierreCajas($_POST["esteCierreListado"]);
}

/*=============================================
GUARDAR CIERRE DE CAJA CAJERO
=============================================*/ 
if(isset($_POST["cierre_caja_rol_cajero"])) {
  $data = new AjaxCajas();
  $data -> ajaxCierreCajaPorCajero($_POST);
}
