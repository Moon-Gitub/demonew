<?php

if($_SESSION["perfil"] == "Especial"){

  echo '<script>

    window.location = "inicio";

  </script>';

  return;

}

?>

<!--begin::App Main-->
<main class="app-main">
  <!--begin::App Content Header-->
  <div class="app-content-header">
    <div class="container-fluid">
      <div class="row">
        <div class="col-sm-6">
          <h3 class="mb-0">Pagos a proveedores</h3>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-end mb-0">
<li><a href="inicio"><i class="bi bi-speedometer2"></i> Inicio</a></li>
      
      <li class="active">Pagos a proveedores</li>
          </ol>
        </div>
      </div>
    </div>
  </div>
  <!--end::App Content Header-->
  <!--begin::App Content-->
  <div class="app-content">
    <div class="container-fluid">
<?php
if(isset($_GET["fechaInicial"])){

        $fechaInicial = $_GET["fechaInicial"];
       
        }else{

        $fechaInicial = date("Y-m-d");
              
        }
?>
    <div class="card">
<div class="card-header with-border">
  <div class="col-xs-2">
    <input type="text" class="form-control" style="text-align:center;" name="fechaInicial" id="fechaInicial" value="<?php echo $fechaInicial;?>" />
   </div>
   <div class="col-xs-2"> 
    <center><button type="button" onclick="mostrarProveedoresPagos();" class="btn btn-primary">Consultar</button></center>
</div>
      </div>

      <div class="card-body">
        
       <table class="table table-bordered table-striped dt-responsive tablaSaldoProveedor" width="100%">
         
        <thead>
         
         <tr>

           <th><center>Nombre</center></th>
           <th><center>Metodo De Pago</center></th>
           <th><center>Descripcion</center></th>
           <th><center>Importe</center></th>
    	   <th><center>Usuario</center></th>
         
         </tr> 

        </thead>

        <tbody>

        <?php


          $valor = $fechaInicial;

          $pagos = ControladorProveedores::ctrMostrarPagosProveedores($valor);

          foreach ($pagos as $key => $value) {
            
	         $item = 'id';
		
	        $valor = $value["id_proveedor"];

    	    $proveedor = ControladorProveedores::ctrMostrarProveedores($item, $valor);
	       
         if($value["metodo_pago"]==1){
          $metodo_pago_movimiento = "Efectivo";
         }
         if($value["metodo_pago"]==2){
          $metodo_pago_movimiento = "Trasnferencia";
         }
         if($value["metodo_pago"]==3){
          $metodo_pago_movimiento = "Cheque";
         }
                 echo '<tr>

                    <td><center>'.$proveedor["organizacion"].'</center></td>

                    <td><center>'.$metodo_pago_movimiento.'</center></td>

                    <td><center>'.$value["descripcion"].'</center></td>

                    <td><center>'.$value["importe"].'</center></td>
                    
                    <td><center>'.$value["id_usuario"].'</center></td>
         			
                  </tr>';
          
            }

        ?>
   

        </tbody>

       </table>

      </div>

    </div>

      </div>
    <!--end::Container-->
  </div>
  <!--end::App Content-->
</main>
<!--end::App Main-->