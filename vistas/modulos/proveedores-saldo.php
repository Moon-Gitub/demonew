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
      
      Saldo proveedores
    
    </h1>

    <ol class="breadcrumb">
      
      <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>
      
      <li class="active">Saldo proveedores</li>
    
    </ol>

  </section>

  <section class="content">
<?php
if(isset($_GET["fechaInicial"])){

        $fechaInicial = $_GET["fechaInicial"];
       
        }else{

        $fechaInicial = date("Y-m-d");
        $fechaInicial .= " 00:00:00";
       
        }
?>
    <div class="box">
<div class="box-header with-border">
  <div class="col-xs-2">
    <input type="text" class="form-control" style="text-align:center;" name="fechaInicial" id="fechaInicial" value="<?php echo $fechaInicial;?>" />
   </div>
   <div class="col-xs-2"> 
    <center><button type="button" onclick="mostrarSaldos();" class="btn btn-primary">Consultar</button></center>
</div>
      </div>

      <div class="box-body">
        
       <table class="table table-bordered table-striped dt-responsive tablaSaldoProveedor" width="100%">
         
        <thead>
         
         <tr>

           <th><center>Organizacion</center></th>
           <th><center>Nombre</center></th>
    	   <th><center>Saldo</center></th>
         
         </tr> 

        </thead>

        <tbody>

        <?php


          $item = null;
          $valor = null;

          $proveedores = ControladorProveedores::ctrMostrarProveedores($item, $valor);

          foreach ($proveedores as $key => $value) {
            
	   $item = 'id';
		
	   $valor = $value["id"];

    	   $proveedor = ControladorProveedores::ctrMostrarProveedores($item, $valor);
	
           $compras = ControladorProveedoresCtaCte::ctrSumarComprasListado($valor, $fechaInicial);

    	   $remitos = ControladorProveedoresCtaCte::ctrSumarRemitosListado($valor, $fechaInicial);

    	   $pagos = ControladorProveedoresCtaCte::ctrSumarPagosListado($valor, $fechaInicial);

    	   $notas = ControladorProveedoresCtaCte::ctrNotasCreditosListado($valor, $fechaInicial);

            echo '<tr>

                    <td><center>'.$value["organizacion"].'</center></td>

                    <td><center>'.$value["nombre"].'</center></td>

                    <td><center>'.number_format(round(($compras["compras"] + $remitos["compras"] - $pagos["pagos"] - $notas["cuentas"]),2),2).'</center></td>
         			
                  </tr>';
          
            }

        ?>
   

        </tbody>

       </table>

      </div>

    </div>

  </section>

</div>

