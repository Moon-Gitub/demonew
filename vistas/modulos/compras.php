<?php

if($_SESSION["perfil"] == "Especial"){

  echo '<script>

    window.location = "inicio";

  </script>';

  return;

}

?>

<!--begin::App Content Header-->
  <div class="app-content-header">
    <div class="container-fluid">
      <div class="row">
        <div class="col-sm-6">
          <h3 class="mb-0">Administrar compras</h3>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-end mb-0">
            <li class="breadcrumb-item"><a href="inicio"><i class="bi bi-speedometer2"></i> Inicio</a></li>
            <li class="breadcrumb-item active" aria-current="page">Administrar compras</li>
          </ol>
        </div>
      </div>
    </div>
  </div>
  <!--end::App Content Header-->
  <!--begin::App Content-->
  <div class="app-content">
    <div class="container-fluid">

    <div class="card">

      <div class="card-header with-border">
  
        <a href="crear-compra" class="btn btn-primary">Agregar compra</a>

         <button type="button" class="btn btn-default float-end" id="daterange-btnCompras">
           
            <span>
              <i class="bi bi-calendar"></i> 

              <?php

                if(isset($_GET["fechaInicial"])){

                  echo $_GET["fechaInicial"]." - ".$_GET["fechaFinal"];
                
                }else{
                 
                  echo 'Hoy';

                }

              ?>
            </span>

            <i class="fa fa-caret-down"></i>

         </button>

      </div>

      <div class="card-body">
        
       <table class="table table-bordered table-striped " id="tablaListarCompras">
         
        <thead>
         
         <tr>
           
          <th>Fecha</th>
          <th>Nro. Int.</th>
          <th>Fec. Emision</th>
          <th>Remito/Factura</th>
          <th>Proveedor</th>
          <th>Usuario Pedido</th>
          <th>Usuario Confirma</th>
          <th>Subtotal</th>
          <th>Descuento</th>
          <th>Neto</th>
          <th>IVA</th>
          <th>IIBB</th>
          <th>Perc. IVA</th>
          <th>Perc. Ganancia</th>
          <th>Imp. Int.</th>
          <th>Total</th>
          <th style="width:20px">Acciones</th>

         </tr> 

        </thead>

        <tfoot>
         
         <tr>
           
          <th>Fecha</th>
          <th>Nro. Int.</th>
          <th>Fec. Emision</th>
          <th>Remito/Factura</th>
          <th>Proveedor</th>
          <th>Usuario Pedido</th>
          <th>Usuario Confirma</th>
          <th>Subtotal</th>
          <th>Descuento</th>
          <th>Neto</th>
          <th>IVA</th>
          <th>IIBB</th>
          <th>Perc. IVA</th>
          <th>Perc. Ganancia</th>
          <th>Imp. Int.</th>
          <th>Total</th>
          <th></th>

         </tr> 

        </tfoot>

        <tbody>

        <?php

          if(isset($_GET["fechaInicial"])){

            $fechaInicial = $_GET["fechaInicial"];
            $fechaFinal = $_GET["fechaFinal"];

          }else{

            $fechaInicial = date('Y-m-d 00:00:00');
            $fechaFinal = date('Y-m-d 23:59:59');

          }

          $respuesta = ControladorCompras::ctrRangoFechasComprasIngresadas($fechaInicial, $fechaFinal);
      
          foreach ($respuesta as $key => $value) {

            $proveedores = ControladorProveedores::ctrMostrarProveedores("id", $value["id_proveedor"]);

            $subtotal = $value["totalNeto"] + $value["descuento"];

            echo '<tr>

              <td>'.$value["fecha"].'</td>

              <td>'.$value["id"].'</td>

              <td>'.$value["fechaEmision"].'</td>';

            echo ($value["remitoNumero"] == "") ? '<td>Fac.: '.$value["numeroFactura"].'</td>' : '<td>Rem.: '.$value["remitoNumero"].'</td>';

            echo '<td><a href="index.php?ruta=proveedores_cuenta&id_proveedor='.$proveedores["id"].'"> '.$proveedores["nombre"].'</a></td>

              <td>'.$value["usuarioPedido"].'</td>

              <td>'.$value["usuarioConfirma"].'</td>

              <td>'.$subtotal.'</td>
              <td>'.$value["descuento"].'</td>
              <td>'.$value["totalNeto"].'</td>
              <td>'.$value["iva"].'</td>
              <td>'.$value["precepcionesIngresosBrutos"].'</td>
              <td>'.$value["precepcionesIva"].'</td>
              <td>'.$value["precepcionesGanancias"].'</td>
              <td>'.$value["impuestoInterno"].'</td>
              <td>'.$value["total"].'</td>';

                echo '<td>
                   <div class="btn-group">
                      <button class="btn btn-info btnImprimirIngresoMercaderia" codigoCompra="'.$value["id"].'">
                       <i class="bi bi-printer"></i>
                      </button>';

                      if($_SESSION["perfil"] == "Administrador"){

                          echo '<button class="btn btn-danger btnEliminarCompra" idCompra="'.$value["id"].'"><i class="bi bi-x"></i></button>';
                      }

                echo '</div></center>

                  </td>';
            
            echo '</tr>';

          }

        ?>
               
        </tbody>

       </table>

        <?php

          $borrarCompra = new ControladorCompras();
          $borrarCompra -> ctrEliminarCompra();

        ?>

      </div>

    </div>
    </div>
    <!--end::Container-->
  </div>
  <!--end::App Content-->

<script>
$(document).ready(function() {
  // Inicializar DataTable para compras
  if ($.fn.DataTable) {
    $('#tablaListarCompras').DataTable({
      "language": {
        "sProcessing": "Procesando...",
        "sLengthMenu": "Mostrar _MENU_ registros",
        "sZeroRecords": "No se encontraron resultados",
        "sEmptyTable": "Ningún dato disponible en esta tabla",
        "sInfo": "Mostrando registros del _START_ al _END_ de un total de _TOTAL_",
        "sInfoEmpty": "Mostrando registros del 0 al 0 de un total de 0",
        "sInfoFiltered": "(filtrado de un total de _MAX_ registros)",
        "sInfoPostFix": "",
        "sSearch": "Buscar:",
        "sUrl": "",
        "sInfoThousands": ",",
        "sLoadingRecords": "Cargando...",
        "oPaginate": {
          "sFirst": "Primero",
          "sLast": "Último",
          "sNext": "Siguiente",
          "sPrevious": "Anterior"
        },
        "oAria": {
          "sSortAscending": ": Activar para ordenar la columna de manera ascendente",
          "sSortDescending": ": Activar para ordenar la columna de manera descendente"
        }
      },
      "responsive": true,
      "autoWidth": false,
      "order": [[0, "desc"]]
    });
  }
});
</script>