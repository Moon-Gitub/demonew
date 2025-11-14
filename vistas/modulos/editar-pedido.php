<div class="content-wrapper">

  <section class="content-header">
    
    <h1>
      
      Validar pedido
    
    </h1>

    <ol class="breadcrumb">
      
      <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
      
      <li class="active">Validar pedido</li>
    
    </ol>

  </section>

  <section class="content">

    <div class="row">

      <!--=====================================
      EL FORMULARIO
      ======================================-->
      
      <div class="col-lg-5 col-xs-12">
        
        <div class="box box-success">
          
          <div class="box-header with-border"></div>

          <!--<form role="form" action="../layunta/vistas/modulos/procesar.php" method="post" class="formularioPedidoValidar">-->
		  
		  <form role="form" method="post" class="formularioPedidoValidar">
		  
		  <input type="hidden" name="token" value="<?php echo $_SESSION['token'] ?>" />

			<div class="box-body">
  
               <div class="row">
                  
                <div class="col-md-12">
                <?php

                    $item = "id";
                    $valor = $_GET["idPedido"];

                    $pedido = ControladorPedidos::ctrMostrarPedidos($item, $valor);
					
                ?>

                <!--=====================================
                ENTRADA DEL VENDEDOR
                ======================================-->
		          <div class="form-group">
                
                  <div class="input-group">
                    
                    <span class="input-group-addon"><i class="fa fa-user"></i></span> 

                    <input type="text" class="form-control" id="idVendedorVisual" value="<?php echo $pedido["id_vendedor"]; ?>" readonly>

                    <input type="hidden" name="idVendedor" value="<?php echo $pedido["id_vendedor"]; ?>">
					<input type="hidden" name="urlActual" id="urlActual" value="1">
					<input type="hidden" name="usuarioConfirma" value="<?php echo $_SESSION["nombre"]; ?>">
					
                  </div>

                </div> 
				</div>
                <!--=====================================
                ENTRADA DEL CÓDIGO
                ======================================--> 
				<input type="hidden" class="form-control" id="editarPedido" name="editarPedido" value="<?php echo $pedido["id"]; ?>" readonly>
				  
                
				<div class="col-xs-6">
                <div class="form-group">
                  
                  <div class="input-group">
                    <span class="input-group-addon">Origen</span>
					<input type="text" class="form-control" style="text-align:center;" id="editarOrigenVer" name="editarOrigenVer" value="<?php echo $pedido["origen"]; ?>" readonly>
                    <input type="hidden" class="form-control" style="text-align:center;" id="editarOrigen" name="editarOrigen" value="<?php echo $pedido["origen"]; ?>" readonly>
                                      
                  </div>
                
                </div>
				</div>
				
				<div class="col-xs-6">
				<div class="form-group">
                  
                    <div class="input-group">
					<span class="input-group-addon">Destino</span>		
                    <input type="text" class="form-control" style="text-align:center;" id="editarDestinoVer" name="editarDestinoVer" value="<?php echo $pedido["destino"]; ?>" readonly>
                    <input type="hidden" class="form-control" style="text-align:center;" id="editarDestino" name="editarDestino" value="<?php echo $pedido["destino"]; ?>" readonly>
                                      
                  </div>
                                      
                  </div>
                
                </div>
								
                <!--=====================================
                ENTRADA PARA AGREGAR PRODUCTO
                ======================================--> 
				<div class="col-xs-12">
                
				<div class="row">
                  
                  <div class="col-xs-6" ><center>Articulo</center></div>
                  <div class="col-xs-2" ><center>Código</center></div>
				  <div class="col-xs-2" ><center>Pedidos</center></div>
	              <div class="col-xs-2" ><center>Enviados</center></div>

                </div>
				<hr>
				<div class="form-group row nuevoProducto">
                <?php

                $listaProducto = json_decode($pedido["productos"], true);

                foreach ($listaProducto as $key => $value) {

                  $item = "id";
                  $valor = $value["id"];
                  $orden = "id";

                  $respuesta = ControladorProductos::ctrMostrarProductos($item, $valor, $orden);


                  echo '<div class="row" style="padding:5px 15px;">

                        <div class="col-xs-6" style="padding-right:0px">

                          <div class="input-group">

                            <span class="input-group-btn"><button type="button" class="btn btn-danger btn-sm quitarProductoValidar" idProducto="'.$value["id"].'"><i class="fa fa-times"></i></button></span>

                            <input type="text" title="'.$value["descripcion"].'" class="form-control input-sm nuevaDescripcionProducto" idProducto="'.$value["id"].'" name="agregarProducto" value="'.$value["descripcion"].'" readonly required>

                          </div>

                        </div>
						<div class="col-xs-2">

                          <input type="text" step="any" class="form-control input-sm nuevoCodigo" style="text-align:center;" name="nuevoCodigo" value="'.$respuesta["codigo"].'" readonly required>

                        </div>
	
						<div class="col-xs-2">

                          <input type="text" step="any" class="form-control input-sm nuevaCantidadProductoValidar" style="text-align:center;" name="nuevaCantidadProductoValidar" value="'.$value["cantidad"].'" readonly required>

                        </div>
						
                        <div class="col-xs-2">

                          <input type="text" step="any" class="form-control input-sm nuevaCantidadProducto" style="text-align:center;" name="nuevaCantidadProducto" value="'.$value["cantidad"].'"  required>
						
                        </div>

                    </div>';
                }


                ?>
				
                </div>
				</div>
                <input type="hidden" id="listaProductosPedidosValidar" name="listaProductosPedidosValidar" value="">

                <div class="row">
        
              </div>
      
              </div>

          </div>

          <div class="box-footer">

            <!--<center><button type="submit" class="btn btn-primary" onClick="this.disabled=true;">Validar pedido</button></center>-->
	    <center><button type="submit" class="btn btn-primary" >Validar pedido</button></center>

          </div>

        </form>

        <?php

          $editarPedido = new ControladorPedidos();
          $editarPedido -> ctrEditarPedido();
          
        ?>

        </div>
            
      </div>

      <!--=====================================
      LA TABLA DE PRODUCTOS
      ======================================-->

      <div class="col-lg-7 col-xs-12">
        
        <div class="box box-warning">

          <div class="box-header with-border"></div>

          <div class="box-body">
            
            <table class="table table-bordered table-striped dt-responsive" id="tablaPedidos" width="100%">

        <thead>

         <tr>

           <th>Código</th>
           <th>Categoria</th>
           <th>Descripción</th>
           <th>Local</th>
           <th>Deposito</th>
		   <th>id</th>
         </tr>

        </thead>
        
         </table>

          </div>

        </div>


      </div>
</div>
    </div>
   
  </section>

</div>
<script>
window.addEventListener("load", function(event) {
   // function listarProductosPedidosValidar(){

	var listaProductosPedidosValidar = [];

	var descripcion = $(".nuevaDescripcionProducto");

	var cantidad = $(".nuevaCantidadProducto");
	
	var recibido = $(".nuevaCantidadProductoValidar");

	var precio = $(".nuevoPrecioProducto");

	for(var i = 0; i < descripcion.length; i++){
	
		listaProductosPedidosValidar.push({ "id" : $(descripcion[i]).attr("idProducto"), 
							  "descripcion" : $(descripcion[i]).val(),
							   "cantidad" : $(recibido[i]).val(),
							  "recibida" : $(cantidad[i]).val()})

	}

	$("#listaProductosPedidosValidar").val(JSON.stringify(listaProductosPedidosValidar)); 

//}
});
</script>