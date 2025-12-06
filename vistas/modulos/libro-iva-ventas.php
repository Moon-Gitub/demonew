<?php 

  date_default_timezone_set('America/Argentina/Mendoza');
  if(isset($_GET["fechaInicial"])){

    $fechaInicial = $_GET["fechaInicial"];
    $fechaFinal = $_GET["fechaFinal"];

  }else{

      $hoy = date('Y-m-d');

     $fechaInicial = $hoy . ' 00:00';
     $fechaFinal = $hoy . ' 23:59';

  }

 ?>

<!--begin::App Content Header-->
  <div class="app-content-header">
    <div class="container-fluid">
      <div class="row">
        <div class="col-sm-6">
          <h3 class="mb-0">Administrar Ventas <small>- <b> Libro IVA Ventas </b> </small></h3>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-end mb-0">
<li class="breadcrumb-item"><a href="inicio"><i class="bi bi-speedometer2"></i> Inicio</a></li>
      
      <li class="breadcrumb-item active" aria-current="page">Administrar ventas</li>
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
  
        <a class="btn btn-primary" href="ventas">
          
          Volver

        </a>

        <div class="btn-group">
          <a class="btn btn-primary" > Libro IVA digital</a>
          <a class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown" href="#">
            <span class="fa fa-caret-down" title="Toggle dropdown menu"></span>
          </a>
          <ul class="dropdown-menu">
            <li>
                <?php echo '<a href="vistas/modulos/libro-iva-ventas-digital.php?fechaInicial='.$fechaInicial.'&fechaFinal='.$fechaFinal.'"><i class="fa fa-file-text-o fa-fw"></i> VENTAS_CBTE</a>'; ?>
            </li>

            <li>
                <?php echo '<a href="vistas/modulos/libro-iva-ventas-digital-alicuotas.php?fechaInicial='.$fechaInicial.'&fechaFinal='.$fechaFinal.'"><i class="fa fa-file-text-o fa-fw"></i> VENTAS_ALICUOTAS</a>'; ?>
            </li>

          </ul>
        </div>

        

          

        </a>

        <button type="button" class="btn btn-default float-end claseRangoLibroIva" id="daterange-btnLibroIvaVentas">
         
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
        
       <table class="table table-bordered table-striped dt-responsive tablasBotones" width="100%">
         
        <thead>
         
         <tr>
           
           <th>Fecha</th>
           <th>Concepto</th>
           <th>Tipo Cbte</th>
           <th>Pto. Vta.</th>
           <th>Nro.</th>
           <th>Tipo Doc.</th>
           <th>Doc.</th>
           <th>Nombre</th>
           <th>B.I. 0%</th>
           <th>B.I. 2,5%</th>
           <th>B.I. 5%</th>
           <th>B.I. 10,5%</th>
           <th>B.I. 21%</th>
           <th>B.I. 27%</th>
           <th><b>Neto</b></th>
           <th>IVA 2,5%</th>
           <th>IVA 5%</th>
           <th>IVA 10,5%</th>
           <th>IVA 21%</th>
           <th>IVA 27%</th>
           <th><b>IVA</b></th>
           <th><b>Total</b></th>
           <th>CAE</th>
           <th>Vto.CAE</th>

         </tr> 

        </thead>

        <tbody>

        <?php

          $libroIva = ControladorVentas::ctrLibroIvaVentas($fechaInicial, $fechaFinal);

          foreach ($libroIva as $key => $value) {

              echo '<tr>';

                      echo '<td>'.$value["fecha"].'</td>
                      <td>'.$value["concepto"].'</td>
                      <td>'.$value["cbte_tipo"].'</td>
                      <td>'.$value["pto_vta"].'</td>
                      <td>'.$value["nro_cbte"].'</td>
                      <td>'.$value["tipo_documento"].'</td>
                      <td>'.$value["documento"].'</td>
                      <td>'.$value["nombre"].'</td>
                      <td>'.$value["base_imponible_0"].'</td>
                      <td>'.$value["base_imponible_2"].'</td>
                      <td>'.$value["base_imponible_5"].'</td>
                      <td>'.$value["base_imponible_10"].'</td>
                      <td>'.$value["base_imponible_21"].'</td>
                      <td>'.$value["base_imponible_27"].'</td>
                      <td><b>'.$value["total_neto"].'</b></td>
                      <td>'.$value["iva_2"].'</td>
                      <td>'.$value["iva_5"].'</td>
                      <td>'.$value["iva_10"].'</td>
                      <td>'.$value["iva_21"].'</td>
                      <td>'.$value["iva_27"].'</td>
                      <td><b>'.$value["total_impuesto"].'</b></td>
                      <td><b>'.$value["total"].'</b></td>
                      <td>'.$value["cae"].'</td>
                      <td>'.$value["fec_vto_cae"].'</td>';

              echo '</tr>';

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