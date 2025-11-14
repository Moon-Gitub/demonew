<div class="content-wrapper">

  <section class="content-header">
    
    <h1>
      
      Administrar ventas <small>- <b> Informe rentabilidad </b> </small>
    
    </h1>

    <ol class="breadcrumb">
      
      <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>
      
      <li class="active">Administrar ventas</li>
    
    </ol>

  </section>

  <section class="content">

    <div class="box">

      <div class="box-header with-border">
  
        <a class="btn btn-primary" href="ventas">
          
          Volver

        </a>

              <div class="btn-group">
                <button type="button" class="btn btn-default btn-sm" id="daterangeVentasRentabilidad">
           
                    <span>
                    <i class="fa fa-calendar"></i> 

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

      <div class="box-body">

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

  </section>

</div>