<?php

require_once "../controladores/clientes.controlador.php";
require_once "../modelos/clientes.modelo.php";

require_once "../controladores/empresa.controlador.php";
require_once "../modelos/empresa.modelo.php";

require_once "../controladores/facturacion/wsaa_padron.class.php";
require_once "../controladores/facturacion/padron.class.php";

class AjaxClientes{

	public $idCliente;
	public $postClienteVenta;
	public $txtBuscado;

	/*=============================================
	EDITAR CLIENTE
	=============================================*/	
	public function ajaxEditarCliente(){
		$item = "id";
		$valor = $this->idCliente;
		$respuesta = ControladorClientes::ctrMostrarClientes($item, $valor);
		echo json_encode($respuesta);
	}

	public function ajaxListarClientes(){
		$item = null;
		$valor = null;
		$respuesta = ControladorClientes::ctrMostrarClientes($item, $valor);
		echo json_encode($respuesta);
	}

	public function ajaxInsertarClienteVenta(){
		$respuesta = ControladorClientes::ctrCrearClienteVenta($this->postClienteVenta);
		echo json_encode($respuesta);
	}

	/*=============================================
	LISTAR CLIENTES AUTOCOMPLETAR
	=============================================*/
	public function ajaxListadoClientesAutocompletar(){

	  $respuesta = ControladorClientes::ctrMostrarClientesFiltrados($this->txtBuscado);

	  $listaClientes = [];
	  foreach ($respuesta as $key => $value) {
  		switch($value["tipo_documento"]){
  			case 96: 
  			case "96": 
  				$tipoDoc = "DNI";
  				break;
  			case 80: 
  			case "80":
  				$tipoDoc = "CUIT";
  				break;
  			case 86:
  			case "86":
  				$tipoDoc = "CUIL";
  				break;
  			case 87:
  			case "87":
  				$tipoDoc = "CDI";
  				break;
  			case 89:
  			case "89":
  				$tipoDoc = "LE";
  				break;
  			case 90:
  			case "90":
  				$tipoDoc = "LC";
  				break;
  			case 92:
  			case "92":
  				$tipoDoc = "En trámite";
  				break;
  			case 93:
  			case "93":
  				$tipoDoc = "Acta nacimiento";
  				break;
  			case 94:
  			case "94":
  				$tipoDoc = "Pasaporte";
  				break;
  			case 91:
  			case "91":
  				$tipoDoc = "CI extranjera";
  				break;
  			default:
  				$tipoDoc = "Doc";
  				break;

  		}
	    array_push($listaClientes, 
	        array(
	          'label' => $value["nombre"] . ' - ' . $tipoDoc . ': ' . $value["documento"],
	          'value' => array(
	                        'id' => $value["id"],
	                        'tipo_documento' => $tipoDoc,
	                        'documento' => $value["documento"],
	                        'nombre' => $value["nombre"],
	                        'email' => $value["email"]
	                    )
	              )
	        );
	  }

	  echo json_encode($listaClientes);

	} 

	public function ajaxPadronAfip($idPersona){
		$respuesta = ControladorClientes::ctrPadronAfip($idPersona);
		echo json_encode($respuesta);
	}

	public function ajaxPadronAfipLista($arrCUIT){
		$respuesta = ControladorClientes::ctrPadronAfipLista($arrCUIT);
		echo json_encode($respuesta);
	}

}

/*=============================================
EDITAR CLIENTE
=============================================*/	
if(isset($_POST["idCliente"])){

	$cliente = new AjaxClientes();
	$cliente -> idCliente = $_POST["idCliente"];
	$cliente -> ajaxEditarCliente();

}

/*=============================================
LISTAR CLIENTES
=============================================*/	
if(isset($_POST["listarClientes"])){
	$cliente = new AjaxClientes();
	$cliente -> ajaxListarClientes();
}

/*=============================================
AGREGAR CLIENTES DESDE VENTAS
=============================================*/	
if(isset($_POST["nuevoCliente"])){
	$cliente = new AjaxClientes();
	$cliente -> postClienteVenta = $_POST;
	$cliente -> ajaxInsertarClienteVenta();
}

/*=============================================
FILTRAR CLIENTES DESDE VENTAS
=============================================*/	
if(isset($_GET["listadoCliente"])){
  $cliente = new AjaxClientes();
  $cliente -> txtBuscado = $_GET["listadoCliente"];
  $cliente -> ajaxListadoClientesAutocompletar();
}

/*=============================================
BUSCAR DATOS AFIP
=============================================*/	
if(isset($_POST["idPersona"])){
	$cliente = new AjaxClientes();
	$cliente -> ajaxPadronAfip($_POST["idPersona"]);
}

/*=============================================
BUSCAR DATOS AFIP LISTA PERSONAS
=============================================*/	
if(isset($_POST["lstPersona0"])){
	$cliente = new AjaxClientes();
	$arrCUIT = array($_POST["lstPersona0"], $_POST["lstPersona1"], $_POST["lstPersona2"], $_POST["lstPersona3"]);
	$cliente -> ajaxPadronAfipLista($arrCUIT);
}