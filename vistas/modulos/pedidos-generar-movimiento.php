<div class="content-wrapper">

  <section class="content-header">
    
    <h1>
      
      Cargar pedido
    
    </h1>

    <ol class="breadcrumb">
      
      <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
      
      <li class="active">Cargar pedido</li>
    
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

          <form role="form" method="post" class="formularioPedido">
			 <div class="box-body">
  
               <div class="row">
                  
                <div class="col-md-6">
                <!--=====================================
                ENTRADA DEL VENDEDOR
                ======================================-->
            
                <div class="form-group">
                
                  <div class="input-group">
                    
                    <span class="input-group-addon"><i class="fa fa-user"></i></span> 
					<input type="hidden" name="urlActual" id="urlActual" value="0">
                    <input type="text" class="form-control" id="nuevoVendedor" value="<?php echo $_SESSION["nombre"]; ?>" readonly>

                    <input type="hidden" name="idVendedor" value="<?php echo $_SESSION["nombre"]; ?>">

					<input type="hidden" name="estado" id="estado" value="0">
					
					<input type="hidden" name="usuarioConfirma" id="usuarioConfirma" value="0">
                  </div>

                </div> 
				</div> 
                <!--=====================================
                ENTRADA DEL CÓDIGO
                ======================================--> 
				<div class="col-md-6">
                <div class="form-group">
                  
                  <div class="input-group">
                    
                    <span class="input-group-addon"><i class="fa fa-key"></i></span>

                    <?php

                    $pedidos = ControladorPedidos::ctrMostrarUltimoCodigo();

                    if(!$pedidos){

                      echo '<input type="text" class="form-control" id="nuevoPedido" name="nuevoPedido" value="10001" readonly>';
                  

                    }else{

                      $codigo = $pedidos["ultimocodigo"] + 1;

                      echo '<input type="text" class="form-control" id="nuevoPedido" name="nuevoPedido" value="'.$codigo.'" readonly>';
                  

                    }

                    ?>
                    
                    
               		</div>
                    
                    </div>                    

                  </div>

                </div>
                <!--=====================================
                ENTRADA DEL ORIGEN
                ======================================--> 
				<div class="col-xs-6">
					<div class="form-group">
					  
					  <div class="form-group">
					  
					  <div class="input-group">
						
						<span class="input-group-addon"><i class="fa fa-exchange"></i></span>
						
							<select class="form-control" id="nuevoOrigen" name="nuevoOrigen" required>

							<option value="">Seleccionar Origen</option>
							<option value="stock">Local</option>
							<option value="deposito">Deposito</option>							
							</select>
										  
					  </div>
					
					</div>
					</div>
				</div>	
				<div class="col-xs-6">
				<div class="form-group">
                  
                  <div class="input-group">
                    
                    <span class="input-group-addon"><i class="fa fa-exchange"></i></span>
                    
                    <select class="form-control" id="nuevoDestino" name="nuevoDestino" required>

                    <option value="">Seleccionar Destino</option>
					<option value="stock">Local</option>
					<option value="deposito">Deposito</option>
                    </select>
                                      
                  </div>
                
                </div>
				</div>
                <!--=====================================
                ENTRADA PARA AGREGAR PRODUCTO
                ======================================--> 

               <div class="row">
                  
                  <div class="col-xs-6" ><center>Descripcion Articulo</center></div>
		  <div class="col-xs-3" ><center>Código</center></div>
                  <div class="col-xs-3" ><center>Cantidad</center></div>

                </div>
		 <div class="form-group row nuevoProducto" style="width:100%; height:200px; overflow-y:auto; overflow-x: hidden;">
				
                </div>

                <input type="hidden" id="listaProductosPedidos" name="listaProductosPedidos">

                <div class="row">

                </div>
     <div class="col-xs-8 col-xs-offset-2">
                    
                    <table class="table">
			<tr>

                          <td style="vertical-align:middle; border: none;"><b>ARTICULOS:</b></td>

                          <td style="border: none;">

                          <div class="input-group">

                            

                            <input type="number" step="0.01" style="font-size: 60px; text-align: center; height:75px;" class="form-control input-sm" id="cantidadArticulosPedido" name="cantidadArticulosPedido" readonly required>
				
                          </div>

                        </td>
		</tr>
		</table>
</div>
          <div class="box-footer">

            <center><button type="submit" class="btn btn-primary" >Cargar pedido</button></center>

          </div>
		</div>

        </div>
        </form>

        <?php

          $guardarPedido = new ControladorPedidos();
          $guardarPedido -> ctrCrearPedido();
          
        ?>

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

           <th>Codigo</th>
		   <th>Categoria</th>
           <th>Descripcion</th>
           <th>Stock</th>
		   <th>Deposito</th>
		   <th>id</th>
         </tr>

        </thead>
        
         </table>

          </div>

        </div>

      </div>

    </div>
   
  </section>

</div>
