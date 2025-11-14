<?php 

  $desdeFecha = (isset($_POST["txtFechaDesdeVentasCategorias"])) ? $_POST["txtFechaDesdeVentasCategorias"] : date('Y-m-d');
  $hastaFecha = (isset($_POST["txtFechaHastaVentasCategorias"])) ? $_POST["txtFechaHastaVentasCategorias"] : date('Y-m-d');

  $item = null;
  $valor = null;
  $categorias = ControladorCategorias::ctrMostrarCategorias($item, $valor);
  $proveedores = ControladorProveedores::ctrMostrarProveedores($item, $valor);

  $categoriaSel = (isset($_POST["informeVentasCatPro"]) && $_POST["informeVentasCatPro"] == "categoria") ? true : null;
  $proveedorSel = (isset($_POST["informeVentasCatPro"]) && $_POST["informeVentasCatPro"] == "proveedor") ? true : null;
  $productosSel = (isset($_POST["informeVentasCatPro"]) && $_POST["informeVentasCatPro"] == "producto") ? true : null;

  $totalVentas = ControladorVentas::ctrRangoFechasVentas($desdeFecha . ' 00:00', $hastaFecha . ' 23:59');

  if ($categoriaSel) {

    foreach ($totalVentas as $key => $value) { //recorro ventas
    
      $productosVta = json_decode($value["productos"], true); //transformo json de ventas a array

      for ($i=0; $i < count($productosVta); $i++) { //recorro los productos de una venta

        $prodIterado = ControladorProductos::ctrMostrarProductos('id', $productosVta[$i]["id"], null); //busco el producto en la bd
        
        for ($x=0; $x < count($categorias); $x++) { //recorro las categorias
          
          if ($categorias[$x]["id"] == $prodIterado["id_categoria"]) { //consulto si la categoria del producto iterado coincide con la categoria

            if (array_key_exists("montoAcumulado", $categorias[$x])) { //consulto si la clave "montoAcum" ya la he insertado en la categoria

              $categorias[$x]["montoAcumulado"] = $categorias[$x]["montoAcumulado"] + $productosVta[$i]["total"]; 
              $categorias[$x]["cantidadVendida"] = $categorias[$x]["cantidadVendida"] + $productosVta[$i]["cantidad"];
                  
            } else { //sino está creada la clave "montoAcumulado" la creo junto a cantidadvendida
              
              $categorias[$x] += ["montoAcumulado" => $productosVta[$i]["total"]];
              $categorias[$x] += ["cantidadVendida" => $productosVta[$i]["cantidad"]];

            }
            
            break 1; //si ya encontré la categoria, salgo el for que recorre las categorias para que no siga buscando al pedo
          }

        }      

      }

    }

  }

  if($proveedorSel){

    foreach ($totalVentas as $key => $value) {
      
        $productosVta = json_decode($value["productos"], true);

        for ($i=0; $i < count($productosVta); $i++) { 

          $prodIterado = ControladorProductos::ctrMostrarProductos('id', $productosVta[$i]["id"], null);
          
          for ($x=0; $x < count($proveedores); $x++) { 
            
            if ($proveedores[$x]["id"] == $prodIterado["id_proveedor"]) {

              if (array_key_exists("montoAcumulado", $proveedores[$x])) {

                $proveedores[$x]["montoAcumulado"] = $proveedores[$x]["montoAcumulado"] + $productosVta[$i]["total"];
                $proveedores[$x]["cantidadVendida"] = $proveedores[$x]["cantidadVendida"] + $productosVta[$i]["cantidad"];
                    
              } else {
                
                $proveedores[$x] += ["montoAcumulado" => $productosVta[$i]["total"]];
                $proveedores[$x] += ["cantidadVendida" => $productosVta[$i]["cantidad"]];

              }
              
              break 1;
            }

          }

        }

      }

  }
  
  if($productosSel){
      
    $arrayProductos = array();

    foreach ($totalVentas as $key => $value) {
      
        $productosVta = json_decode($value["productos"], true);

        for ($i=0; $i < count($productosVta); $i++) { 

            $prodIterado = ControladorProductos::ctrMostrarProductos('id', $productosVta[$i]["id"], null);
            $key = $prodIterado["id"];

            if(array_key_exists($key, $arrayProductos)){
                //el producto SI esta dentro del array sumo cantidades y totales
                $arrayProductos[$key]["cantidad"] += $productosVta[$i]["cantidad"];
                $arrayProductos[$key]["vendido"] += $productosVta[$i]["total"];

            } else {
                //el producto NO esta dentro del array lo agrego al array
                $valor = array("codigo" => $prodIterado["codigo"], "descripcion" => $prodIterado["descripcion"], "cantidad" => $productosVta[$i]["cantidad"], "vendido" => $productosVta[$i]["total"]);
                $arrayProductos[$key] = $valor;

            }
        }
      }
  }

?>

<div class="content-wrapper">

  <section class="content-header">
    
    <h1>
      
      Administrar ventas <small>- <b> Informe de ventas por categorias | proveedor | productos </b> </small>
      <?php 

        echo isset($desdeFecha) ? '<p><small>Desde: '.$desdeFecha.' Hasta: '.$hastaFecha.'</small></p>' : '';

      ?> <p></p>
    
    </h1>

    <ol class="breadcrumb">
      
      <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>
      
      <li class="active">Administrar ventas</li>
    
    </ol>

  </section>

  <section class="content">

    <div class="box">

      <div class="box-header with-border">
  
    <div class="row">		

      <form method="POST">

			<div class="col-md-2">
				
				<div class="form-group">
				    
					<div class="input-group">

						<span class="input-group-addon"><i class="fa fa-calendar"></i> Desde</span> 
						<input id="txtFechaDesdeVentasCategorias" name="txtFechaDesdeVentasCategorias" type="text" class="form-control inputVentasCategorias" placeholder="Fecha desde aaaa/mm/dd" autocomplete="off" required>

					</div>

				</div>

			</div>

			<div class="col-md-2">
				
				<div class="form-group">

					<div class="input-group">

						<span class="input-group-addon"><i class="fa fa-calendar"></i> Hasta</span> 
						<input id="txtFechaHastaVentasCategorias" name="txtFechaHastaVentasCategorias" type="text" class="form-control inputVentasCategorias" placeholder="Fecha hasta aaaa/mm/dd" autocomplete="off" required>

					</div>

				</div>		

			</div>
			<div class="col-md-2">
			    
				<div class="form-group">

					<div class="input-group">

            <div class="form-check">
              <!--<label>-->
                <!--<input type="checkbox" name="informeVentasCategoria" value="true"> Categorias-->
                <input class="form-check-input" type="radio" name="informeVentasCatPro" value="categoria" checked>
                <label class="form-check-label">Categorias</label>
              <!--</label>-->
            </div>

					</div>

				</div>

			</div>
			<div class="col-md-2">
				
				<div class="form-group">

					<div class="input-group">

                        <div class="form-check">
                <!--<label>
                <input type="checkbox" name="informeVentasProveedor" value="true"> Proveedor
                </label>-->
              <input class="form-check-input" type="radio" name="informeVentasCatPro" value="proveedor">
                <label class="form-check-label">Proveedor</label>
            </div>

					</div>

				</div>

			</div>
			<div class="col-md-2">
				
				<div class="form-group">

					<div class="input-group">

                        <div class="form-check">
                <!--<label>
                <input type="checkbox" name="informeVentasProveedor" value="true"> Proveedor
                </label>-->
              <input class="form-check-input" type="radio" name="informeVentasCatPro" value="producto">
                <label class="form-check-label">Productos</label>
            </div>

					</div>

				</div>

			</div>
			<div class="col-md-2">
				<button class="form-control btn btn-primary"><i class="fa fa-search"></i></button>
			</div>
		
        </form>
    </div>

      </div>

      <div class="box-body">

        <center>
        
        <div class="row">


        <div class="col-md-2"></div>
          <?php 


            if($categoriaSel) {


          ?>
              <div class="col-md-8">
               <table class="table table-bordered table-striped tablasBotones" >
                 
                  <thead>
                   
                   <tr>
                     
                     <th colspan="3" style="background-color:#b5afaf"><center>Categorías<center></th>
                     

                   </tr> 
                   <tr>
                     
                     <th width="200px">Categoría</th>
                     <th>$</th>
                     <th>Cant.</th>

                   </tr> 

                  </thead>

                  <tbody>

                    <?php

                      foreach ($categorias as $key => $value) {
                        echo "<tr>";
                          echo "<td>".$value["categoria"]."</td>";
                          $montoVista = (isset($value["montoAcumulado"])) ? $value["montoAcumulado"] : 0;
                          echo "<td>".$montoVista."</td>";
                          $cantVista = (isset($value["cantidadVendida"])) ? $value["cantidadVendida"] : 0;
                          echo "<td>".$cantVista."</td>";
                        echo "</tr>";
                      }

                    ?>

                  </tbody>

               </table>
              </div>

          <?php  

            } 

            if($proveedorSel){

          ?>

            <div class="col-md-8">
               <table class="table table-bordered table-striped tablasBotones">
                 
                  <thead>
                    
                    <tr>
                     
                     <th colspan="3" style="background-color:#b5afaf"><center>Proveedores</center></th>
                     

                   </tr> 
                   <tr>
                     
                     <th width="200px">Nombre</th>
                     <th>$</th>
                     <th>Cant.</th>

                   </tr> 

                  </thead>

                  <tbody>

                    <?php

                      foreach ($proveedores as $key => $value) {
                        echo "<tr>";
                          echo "<td>".$value["nombre"]."</td>";
                          $montoVista = (isset($value["montoAcumulado"])) ? $value["montoAcumulado"] : 0;
                          echo "<td>".$montoVista."</td>";
                          $cantVista = (isset($value["cantidadVendida"])) ? $value["cantidadVendida"] : 0;
                          echo "<td>".$cantVista."</td>";
                        echo "</tr>";
                      }

                    ?>

                  </tbody>

               </table>
            </div>

          <?php 

            }
            
            if($productosSel){

           ?>
           
           <div class="col-md-8">
               <table class="table table-bordered table-striped tablasBotones">
                 
                  <thead>
                    
                    <tr>
                     
                     <th colspan="4" style="background-color:#b5afaf"><center>Productos</center></th>
                     

                   </tr> 
                   <tr>
                     
                     <th width="200px">Codigo</th>
                     <th>Descripcion</th>
                     <th>Cant.</th>
                     <th>$ Vendido</th>

                   </tr> 

                  </thead>

                  <tbody>

                    <?php

                      foreach ($arrayProductos as $key => $value) {
                        echo "<tr>";
                          echo "<td>".$value["codigo"]."</td>";
                          echo "<td>".$value["descripcion"]."</td>";
                          $cantVista = (isset($value["cantidad"])) ? $value["cantidad"] : 0;
                          echo "<td>".$cantVista."</td>";
                          $montoVista = (isset($value["vendido"])) ? $value["vendido"] : 0;
                          echo "<td>".$montoVista."</td>";
                          
                        echo "</tr>";
                      }

                    ?>

                  </tbody>

               </table>
            </div>

          <?php 

            }
            
           ?>

            <div class="col-md-2"></div>

        </div>

      </center>

      </div>

    </div>

  </section>

</div>