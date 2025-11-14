<?php

require_once "../controladores/proveedores.controlador.php";
require_once "../modelos/proveedores.modelo.php";

class AjaxProveedores{

	public $idProveedor;
	public $txtBuscado;


	public function ajaxEditarProveedor(){
		$item = "id";
		$valor = $this->idProveedor;
		$respuesta = ControladorProveedores::ctrMostrarProveedores($item, $valor);
		echo json_encode($respuesta);
	}
	
	public function ajaxListarProveedores(){
		$item = null;
		$valor = null;
		$respuesta = ControladorProveedores::ctrMostrarProveedores($item, $valor);
		echo json_encode($respuesta);
	}

  //LISTAR CLIENTES AUTOCOMPLETAR
  public function ajaxListadoProveedorAutocompletar(){
      $respuesta = ControladorProveedores::ctrMostrarProveedoresFiltrados($this->txtBuscado);
      $listaProveedores = [];
      foreach ($respuesta as $key => $value) {
        array_push($listaProveedores, 
            array(
              'label' => $value["nombre"] . ' - ' . $value["cuit"],
              'value' => array(
                            'id' => $value["id"],
                            'tipo_documento' => $value["tipo_documento"],
                            'cuit' => $value["cuit"],
                            'nombre' => $value["nombre"]
                        )
                  )
            );
      }
      echo json_encode($listaProveedores);
  } 

}

//EDITAR PROVEEDOR
if(isset($_POST["idProveedor"])){
	$proveedor = new AjaxProveedores();
	$proveedor -> idProveedor = $_POST["idProveedor"];
	$proveedor -> ajaxEditarProveedor();

}

if(isset($_POST["listarProveedores"])){
	$proveedor = new AjaxProveedores();
	$proveedor -> ajaxListarProveedores();
}

//FILTRAR PROVEEDORES DESDE COMPRAS
if(isset($_GET["listadoProveedor"])){
  $prov = new AjaxProveedores();
  $prov -> txtBuscado = $_GET["listadoProveedor"];
  $prov -> ajaxListadoProveedorAutocompletar();
}