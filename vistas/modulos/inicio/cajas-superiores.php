<?php

$item = null;
$valor = null;
$orden = "id";

//$ventas = ControladorVentas::ctrSumaTotalVentas();

$categorias = ControladorCategorias::ctrMostrarCategorias($item, $valor);
$totalCategorias = count($categorias);

$clientes = ControladorClientes::ctrMostrarClientes($item, $valor);
$totalClientes = count($clientes);

$productos = ControladorProductos::ctrMostrarProductos($item, $valor, $orden);
$totalProductos = count($productos);

date_default_timezone_set('America/Argentina/Mendoza');

  /*=============================================
  CAJA VENTAS MENSUALES
  =============================================*/

//Ventas Hoy
$fechaInicialHoy=date('Y-m-d');  
$fechaFinalHoy = date('Y-m-d'); 

$ventasHoy = ControladorVentas::ctrRangoFechasSoloVentas($fechaInicialHoy, $fechaFinalHoy);

$arrayFechas = array();
$arrayVentas = array();
$sumaPagosMes = array();
$totalHoy=0;

foreach ($ventasHoy as $key => $value) {

  #Capturamos sólo el año y el mes
  $fecha = substr($value["fecha"],0,7);

  #Introducir las fechas en arrayFechas
  array_push($arrayFechas, $fecha);

  #Capturamos las ventas
  $arrayVentas = array($fecha => $value["total"]);

  #Sumamos los pagos que ocurrieron el mismo mes
  foreach ($arrayVentas as $key => $value) {
    
    $totalHoy += $value;
  }

}

?>

<div class="box" >

  <div class="box-header with-border">

    <h3 class="box-title">Ventas</h3>

    <div class="box-tools pull-right">

      <button type="button" class="btn btn-box-tool" data-widget="collapse">

        <i class="fa fa-minus"></i>

      </button>

      <button type="button" class="btn btn-box-tool" data-widget="remove">

        <i class="fa fa-times"></i>

      </button>

    </div>

  </div>
  
  <div class="box-body">

    <div class="col-lg-3 col-xs-6">

      <div class="small-box bg-aqua">
        
        <div class="inner">
          
          <h3>$<?php echo number_format($totalHoy, 2, ',', '.'); ?></h3>

          <p><b>Ventas de Hoy</b></p>
        
        </div>
        
        <div class="icon">
          
          <i class="ion ion-social-usd"></i>
        
        </div>
        
        <a href="index.php?ruta=ventas&fechaInicial=<?php echo $fechaInicialHoy;?>&fechaFinal=<?php echo $fechaFinalHoy;?>" class="small-box-footer">
          
          Más info <i class="fa fa-arrow-circle-right"></i>
        
        </a>

      </div>

    </div>

     <!--=============================================
      CAJA VENTAS SEMANA PASADA
      ============================================= -->
    <?php

    //Ventas Semana Pasada
    $fechaInicialSemanaAnterior=date('Y-m-d', strtotime('last week'));  
    $fechaFinalSemanaAnterior = date('Y-m-d', strtotime('last sunday')); 

    $ventasSemanaAnterior = ControladorVentas::ctrRangoFechasSoloVentas($fechaInicialSemanaAnterior, $fechaFinalSemanaAnterior);

    $arrayFechas = array();
    $arrayVentas = array();
    $sumaPagosMes = array();
    $totalSemanaPasada = 0;

    foreach ($ventasSemanaAnterior as $key => $value) {

      #Capturamos sólo el año y el mes
      $fecha = substr($value["fecha"],0,7);

      #Introducir las fechas en arrayFechas
      array_push($arrayFechas, $fecha);

      #Capturamos las ventas
      $arrayVentas = array($fecha => $value["total"]);

      #Sumamos los pagos que ocurrieron el mismo mes
      foreach ($arrayVentas as $key => $value) {
        
        $totalSemanaPasada += $value;
      }

    }

    ?>

    <div class="col-lg-3 col-xs-6">

      <div class="small-box bg-green">
        
        <div class="inner">
        
          <h3><?php echo number_format($totalSemanaPasada, 2, ',', '.'); ?></h3>

          <p><b>Semana Pasada</b></p>
        
        </div>
        
        <div class="icon">
           <i class="ion ion-social-usd"></i>
        
        </div>
        
        <a href="index.php?ruta=ventas&fechaInicial=<?php echo $fechaInicialSemanaAnterior;?>&fechaFinal=<?php echo $fechaFinalSemanaAnterior;?>" class="small-box-footer">
          
          Más info <i class="fa fa-arrow-circle-right"></i>
        
        </a>

      </div>

    </div>


     <!--=============================================
      CAJA VENTAS MES ACTUAL
      ============================================= -->
    <?php
    //Mes Actual
    $fechaInicialMes = date("Y-m-01");
    $fechaFinalMes = date("Y-m-t"); 
    $ventasMesActual = ControladorVentas::ctrRangoFechasSoloVentas($fechaInicialMes, $fechaFinalMes);

    $arrayFechas = array();
    $arrayVentas = array();
    $sumaPagosMes = array();
    $totalMesActual = 0;

    foreach ($ventasMesActual as $key => $value) {

      #Capturamos sólo el año y el mes
      $fecha = substr($value["fecha"],0,7);

      #Introducir las fechas en arrayFechas
      array_push($arrayFechas, $fecha);

      #Capturamos las ventas
      $arrayVentas = array($fecha => $value["total"]);

      #Sumamos los pagos que ocurrieron el mismo mes
      foreach ($arrayVentas as $key => $value) {
        
        $totalMesActual += $value;
      }

    }

    ?>

    <div class="col-lg-3 col-xs-6">

      <div class="small-box bg-yellow">
        
        <div class="inner">
        
          <h3><?php echo number_format($totalMesActual, 2, ',', '.'); ?></h3>

          <p><b>Este mes</b></p>
      
        </div>
        
        <div class="icon">
        
       <i class="ion ion-social-usd"></i>
        
        </div>
        
        <a href="index.php?ruta=ventas&fechaInicial=<?php echo $fechaInicialMes;?>&fechaFinal=<?php echo $fechaFinalMes;?>" class="small-box-footer">

          Más info <i class="fa fa-arrow-circle-right"></i>

        </a>

      </div>

    </div>

     <!--=============================================
      CAJA VENTAS MES ANTERIOR
      ============================================= -->
    <?php

    //Mes Anterior
    $fechaInicialMesAnterior=date('Y-m-d', strtotime('first day of last month'));  
    $fechaFinalMesAnterior = date('Y-m-d', strtotime('last day of last month')); 

    $ventasMesAnterior = ControladorVentas::ctrRangoFechasSoloVentas($fechaInicialMesAnterior, $fechaFinalMesAnterior);

    $arrayFechas = array();
    $arrayVentas = array();
    $sumaPagosMes = array();
    $totalMesAnterior = 0;

    foreach ($ventasMesAnterior as $key => $value) {

      #Capturamos sólo el año y el mes
      $fecha = substr($value["fecha"],0,7);

      #Introducir las fechas en arrayFechas
      array_push($arrayFechas, $fecha);

      #Capturamos las ventas
      $arrayVentas = array($fecha => $value["total"]);

      #Sumamos los pagos que ocurrieron el mismo mes
      foreach ($arrayVentas as $key => $value) {
        
        $totalMesAnterior += $value;
      }

    }

    ?>

    <div class="col-lg-3 col-xs-6">

      <div class="small-box bg-red">
      
        <div class="inner">
        
          <h3><?php echo number_format($totalMesAnterior, 2, ',', '.'); ?></h3>

          <p><b>Mes Anterior</b></p>
        
        </div>
        
        <div class="icon">

             <i class="ion ion-social-usd"></i>
        
        </div>
        
        <a href="index.php?ruta=ventas&fechaInicial=<?php echo $fechaInicialMesAnterior;?>&fechaFinal=<?php echo $fechaFinalMesAnterior;?>" class="small-box-footer">
          
          Más info <i class="fa fa-arrow-circle-right"></i>
        
        </a>

      </div>

    </div>

  <?php

  if(isset($_GET["fechaInicial"])){

     $fechaInicial = $_GET["fechaInicial"];
     $fechaFinal = $_GET["fechaFinal"];

  }else{

     $fechaInicial = null;
     $fechaFinal = null;

  }

  $respuesta = ControladorVentas::ctrRangoVentasPorMesAnio($fechaInicial, $fechaFinal);

  ?>

  <!--=====================================
  GRÁFICO DE VENTAS
  ======================================-->
  <div class="border-radius-none nuevoGraficoVentas">
    <div class="chart" id="line-chart-ventas" style="height: 250px; background-color: #39cccc;"></div>
  </div>

  <script>
    
   var line = new Morris.Line({
      element          : 'line-chart-ventas',
      resize           : true,
      data             : [

      <?php

        foreach ($respuesta as $key => $value) {
           echo "{ y: '".$value["fecha"]."', ventas: ".$value["total"]." },";
        }

      ?>

      ],
      xkey             : 'y',
      ykeys            : ['ventas'],
      labels           : ['ventas'],
      lineColors       : ['#fff'],
      lineWidth        : 2,
      hideHover        : 'auto',
      gridTextColor    : '#fff',
      gridStrokeWidth  : 0.4,
      pointSize        : 4,
      pointStrokeColors: ['#fff'],
      gridLineColor    : '#fff',
      gridTextFamily   : 'Open Sans',
      preUnits         : '$',
      gridTextSize     : 10
    });

  </script>

  </div>

</div>