<!--begin::App Content Header-->
  <div class="app-content-header">
    <div class="container-fluid">
      <div class="row">
        <div class="col-sm-6">
          <h3 class="mb-0">Administrar ventas <small>- <b> Informe rentabilidad </b> </small></h3>
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
                <button type="button" class="btn btn-default btn-sm" id="daterangeVentasRentabilidad">
           
                    <span>
                    <i class="bi bi-calendar"></i> 

                    <?php

                      if(isset($_GET["fechaInicial"])){

                        echo $_GET["fechaInicial"]." - ".$_GET["fechaFinal"];
                      
                      }else{
                       
                        echo 'Rango de fecha';

                      }

                    ?>
                  </span>

                  <i class="fa fa-caret-down"></i>

                </button>

                <?php

                  if(isset($_GET["fechaInicial"])){

                    $desdeFecha = $_GET["fechaInicial"];
                    $hastaFecha = $_GET["fechaFinal"];

                  }else{

                    $desdeFecha = date('Y-m-d');
                    $hastaFecha = $desdeFecha;

                  }

                ?>
              </div><!-- /btn-group -->

      </div>

      <div class="card-body">

        <center>
        
       <table class="table table-bordered table-striped" width="50%">
         
        <thead>
         
         <tr>
           
           <th width="200px">Descripcion</th>
           <th>$</th>

         </tr> 

        </thead>

        <tbody>

        <?php

          //TOTALES VENTA
          $totalVentas = ControladorVentas::ctrRangoFechasTotalVentas($desdeFecha, $hastaFecha);
          echo '<tr>

                <td>Total Ventas</td>

                <td>'.round($totalVentas["total"],2).'</td>';

          echo '</tr>';

          //TOTAL COSTO
          $ventas = ControladorVentas::ctrRangoFechasVentas($desdeFecha, $hastaFecha);

          $costoTotal = 0;
          foreach ($ventas as $key => $value) {
            
            $productos = json_decode($value["productos"], true);
            $costoVenta = 0;
            foreach ($productos as $keyp => $valuep) {

                $costoVenta += $valuep["cantidad"] * $valuep["precio_compra"];

            }
            $costoTotal += $costoVenta;

          }

          echo '<tr>

                <td>Total Costo</td>

                <td style="color: red">'.round($costoTotal,2).'</td>';

          echo '</tr>';          

          $renta = $totalVentas["total"]-$costoTotal;
          echo '<tr>

                <td><b>Rentabilidad</b></td>

                <td><b>'.round($renta,2).'</b></td>';

          echo '</tr>';    

          $gastos = ControladorCajas::ctrRangoTotalesGastos($desdeFecha, $hastaFecha);
          echo '<tr>

                <td>Gastos</td>

                <td style="color: red">'.round($gastos["gastos"],2).'</td>';

          echo '</tr>';  

          // $retiros = ControladorCajas::ctrRangoTotalesRetirosMM($desdeFecha, $hastaFecha);
          // echo '<tr>

          //       <td>Retiros MM</td>

          //       <td style="color: red">'.round($retiros["retiros"],2).'</td>';

          // echo '</tr>';  

          // $consumiciones = ControladorCajas::ctrRangoTotalesConsumicionesMM($desdeFecha, $hastaFecha);
          // echo '<tr>

          //       <td>Consumiciones MM</td>

          //       <td style="color: red">'.round($consumiciones["consumiciones"],2).'</td>';

          // echo '</tr>'; 

          // $totalTotal = $renta - $gastos["gastos"] - $retiros["retiros"] - $consumiciones["consumiciones"];
          // echo '<tr>

          //       <td>TOTAL</td>

          //       <td>'.$totalTotal.'</td>';

          // echo '</tr>'; 

          
        ?>

          

        </tbody>

       </table>
      </center>

      </div>

    </div>

      </div>
    <!--end::Container-->
  </div>
  <!--end::App Content-->