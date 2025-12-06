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

<div class="card" >

  <div class="card-header with-border d-flex justify-content-between align-items-center">

    <h3 class="card-title mb-0">Ventas</h3>

    <div class="card-tools">

      <button type="button" class="btn btn-tool" data-bs-toggle="collapse" data-bs-target="#ventasCard">

        <i class="bi bi-dash"></i>

      </button>

      <button type="button" class="btn btn-tool" data-bs-dismiss="card">

        <i class="bi bi-x"></i>

      </button>

    </div>

  </div>
  
  <div class="card-body" id="ventasCard">

    <div class="col-lg-3 col-6">

      <div class="small-box text-bg-info">
        
        <div class="inner">
          
          <h3>$<?php echo number_format($totalHoy, 2, ',', '.'); ?></h3>

          <p><b>Ventas de Hoy</b></p>
        
        </div>
        
        <svg class="small-box-icon" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
          <path d="M2.25 2.25a.75.75 0 000 1.5h1.386c.17 0 .318.114.362.278l2.558 9.592a3.752 3.752 0 00-2.806 3.63c0 .414.336.75.75.75h15.75a.75.75 0 000-1.5H5.378A2.25 2.25 0 017.5 15h11.218a.75.75 0 00.674-.421 60.358 60.358 0 002.96-7.228.75.75 0 00-.525-.965A60.864 60.864 0 005.68 4.509l-.232-.867A1.875 1.875 0 003.636 2.25H2.25zM3.75 20.25a1.5 1.5 0 113 0 1.5 1.5 0 01-3 0zM16.5 20.25a1.5 1.5 0 113 0 1.5 1.5 0 01-3 0z"></path>
        </svg>
        
        <a href="index.php?ruta=ventas&fechaInicial=<?php echo $fechaInicialHoy;?>&fechaFinal=<?php echo $fechaFinalHoy;?>" class="small-box-footer link-light link-underline-opacity-0 link-underline-opacity-50-hover">
          
          Más info <i class="bi bi-link-45deg"></i>
        
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

    <div class="col-lg-3 col-6">

      <div class="small-box text-bg-success">
        
        <div class="inner">
        
          <h3><?php echo number_format($totalSemanaPasada, 2, ',', '.'); ?></h3>

          <p><b>Semana Pasada</b></p>
        
        </div>
        
        <svg class="small-box-icon" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
          <path d="M18.375 2.25c-1.035 0-1.875.84-1.875 1.875v15.75c0 1.035.84 1.875 1.875 1.875h.75c1.035 0 1.875-.84 1.875-1.875V4.125c0-1.036-.84-1.875-1.875-1.875h-.75zM9.75 8.625c0-1.036.84-1.875 1.875-1.875h.75c1.036 0 1.875.84 1.875 1.875v11.25c0 1.035-.84 1.875-1.875 1.875h-.75a1.875 1.875 0 01-1.875-1.875V8.625zM3 13.125c0-1.036.84-1.875 1.875-1.875h.75c1.036 0 1.875.84 1.875 1.875v6.75c0 1.035-.84 1.875-1.875 1.875h-.75A1.875 1.875 0 013 19.875v-6.75z"></path>
        </svg>
        
        <a href="index.php?ruta=ventas&fechaInicial=<?php echo $fechaInicialSemanaAnterior;?>&fechaFinal=<?php echo $fechaFinalSemanaAnterior;?>" class="small-box-footer link-light link-underline-opacity-0 link-underline-opacity-50-hover">
          
          Más info <i class="bi bi-link-45deg"></i>
        
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

    <div class="col-lg-3 col-6">

      <div class="small-box text-bg-warning">
        
        <div class="inner">
        
          <h3><?php echo number_format($totalMesActual, 2, ',', '.'); ?></h3>

          <p><b>Este mes</b></p>
      
        </div>
        
        <svg class="small-box-icon" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
          <path d="M6.25 6.375a4.125 4.125 0 118.25 0 4.125 4.125 0 01-8.25 0zM3.25 19.125a7.125 7.125 0 0114.25 0v.003l-.001.119a.75.75 0 01-.363.63 13.067 13.067 0 01-6.761 1.873c-2.472 0-4.786-.684-6.76-1.873a.75.75 0 01-.364-.63l-.001-.122zM19.75 7.5a.75.75 0 00-1.5 0v2.25H16a.75.75 0 000 1.5h2.25v2.25a.75.75 0 001.5 0v-2.25H22a.75.75 0 000-1.5h-2.25V7.5z"></path>
        </svg>
        
        <a href="index.php?ruta=ventas&fechaInicial=<?php echo $fechaInicialMes;?>&fechaFinal=<?php echo $fechaFinalMes;?>" class="small-box-footer link-dark link-underline-opacity-0 link-underline-opacity-50-hover">

          Más info <i class="bi bi-link-45deg"></i>

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

    <div class="col-lg-3 col-6">

      <div class="small-box text-bg-danger">
      
        <div class="inner">
        
          <h3><?php echo number_format($totalMesAnterior, 2, ',', '.'); ?></h3>

          <p><b>Mes Anterior</b></p>
        
        </div>
        
        <svg class="small-box-icon" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
          <path clip-rule="evenodd" fill-rule="evenodd" d="M2.25 13.5a8.25 8.25 0 018.25-8.25.75.75 0 01.75.75v6.75H18a.75.75 0 01.75.75 8.25 8.25 0 01-16.5 0z"></path>
          <path clip-rule="evenodd" fill-rule="evenodd" d="M12.75 3a.75.75 0 01.75-.75 8.25 8.25 0 018.25 8.25.75.75 0 01-.75.75h-7.5a.75.75 0 01-.75-.75V3z"></path>
        </svg>
        
        <a href="index.php?ruta=ventas&fechaInicial=<?php echo $fechaInicialMesAnterior;?>&fechaFinal=<?php echo $fechaFinalMesAnterior;?>" class="small-box-footer link-light link-underline-opacity-0 link-underline-opacity-50-hover">
          
          Más info <i class="bi bi-link-45deg"></i>
        
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