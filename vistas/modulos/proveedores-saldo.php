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
          <h3 class="mb-0">Saldo proveedores</h3>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-end mb-0">
<li><a href="inicio"><i class="bi bi-speedometer2"></i> Inicio</a></li>
      
      <li class="active">Saldo proveedores</li>
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
        $fechaInicial .= " 00:00:00";
       
        }
?>
    <div class="card">
<div class="card-header with-border">
  <div class="col-xs-2">
    <input type="text" class="form-control" style="text-align:center;" name="fechaInicial" id="fechaInicial" value="<?php echo $fechaInicial;?>" />
   </div>
   <div class="col-xs-2"> 
    <center><button type="button" onclick="mostrarSaldos();" class="btn btn-primary">Consultar</button></center>
</div>
      </div>

      <div class="card-body">
        
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

      </div>
    <!--end::Container-->
  </div>
  <!--end::App Content-->
</main>
<!--end::App Main-->