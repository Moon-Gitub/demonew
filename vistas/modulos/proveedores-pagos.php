<?php

if($_SESSION["perfil"] == "Especial"){

  echo '<script>

    window.location = "inicio";

  </script>';

  return;

}

?>

<div class="content-wrapper">

  <section class="content-header">
    
    <h1>
      
      Pagos a proveedores
    
    </h1>

    <ol class="breadcrumb">
      
      <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>
      
      <li class="active">Pagos a proveedores</li>
    
    </ol>

  </section>

  <section class="content">
<?php
if(isset($_GET["fechaInicial"])){

        $fechaInicial = $_GET["fechaInicial"];
       
        }else{

        $fechaInicial = date("Y-m-d");
              
        }
?>
    <div class="box">
<div class="box-header with-border">
  <div class="col-xs-2">
    <input type="text" class="form-control" style="text-align:center;" name="fechaInicial" id="fechaInicial" value="<?php echo $fechaInicial;?>" />
   </div>
   <div class="col-xs-2"> 
    <center><button type="button" onclick="mostrarProveedoresPagos();" class="btn btn-primary">Consultar</button></center>
</div>
      </div>

      <div class="box-body">
        
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

  </section>

</div>

