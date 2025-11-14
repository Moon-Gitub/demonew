<?php

//========================
// CONEXION AFIP
//========================
$conAfip = false;
$msjError="";
if($arrayEmpresa["entorno_facturacion"]){

 try {

   $wsaa = new WSAA($arrayEmpresa);

   if (date('Y-m-d H:i:s', strtotime($wsaa->get_expiration())) < date('Y-m-d H:i:s')) {

     $wsaa->generar_TA();

   }

   $wsfe = new WSFE($arrayEmpresa);
   $test = $wsfe->openTA();

  // $test = $wsfe->PruebaConexion();

   if (isset($test)){
     //if ($test->FEDummyResult->AppServer == 'OK' && $test->FEDummyResult->DbServer == 'OK' && $test->FEDummyResult->AuthServer == 'OK' ){

       $conAfip = true;

     //}
   } else {

     $conAfip = false;

   }

 } catch (Exception $e) {

   $conAfip = false;
   $msjError = $e->getMessage();
 }

}

//========================
// ARCHIVO COTIZACION
//========================
$result=[];
if ($file = fopen("cotizacion", "r")) {
    $i = 0;

    while(!feof($file)) {
        $line = fgets($file);
        $result[$i] = $line;
        $i++;

    }
    fclose($file);
} else {
    $result[0]="No se pudo cargar la ultima cotización";
    $result[1]="0,00";
}

?>
 <header class="main-header">
    
    <!--=====================================
    LOGOTIPO
    ======================================-->
    <a href="inicio" class="logo">
        <!-- logo mini -->
        <span class="logo-mini">
            <i class="fa fa-moon-o fa-2x"></i>
        </span>

        <!-- logo normal -->
        <span class="logo-lg">
            <i class="fa fa-moon-o fa-2x"></i>
            POS | Moon
        </span>
    </a>

    <!--=====================================
    BARRA DE NAVEGACIÓN
    ======================================-->
    <nav class="navbar navbar-static-top"  role="navigation">

        <!-- Botón de navegación -->
        <a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button">
            <span class="sr-only">Toggle navigation</span>
        </a>

        <!-- perfil de usuario -->
        <div class="navbar-custom-menu">
            <ul class="nav navbar-nav">
                <li class="dropdown tasks-menu" style="display: none" id="alertaTiempoSesionRestanteLi">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                      <i class="fa fa-clock-o"></i>
                      <span title="Tiempo restante de sesión" class="label label-danger" id="alertaTiempoSesionRestante"></span>
                    </a>
                </li>
                <li class="dropdown tasks-menu">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                      <img src="vistas/img/plantilla/afipicon.ico" >
                    </a>
                    <ul class="dropdown-menu">
                        <li class="header" style="background-color: #000"><img src="vistas/img/plantilla/AFIPlogoChico.png" width="30%"></li>
                        <li>
                            <!-- inner menu: contains the actual data -->
                            <ul class="menu" style="background-color: #eee;">
                                <?php 

                                echo '<p>Conexion con servidor de AFIP ';

                                if ( $conAfip ){

                                  $fecform = date_create($wsfe->datosTA()["Exp"]);
                                  echo '<i class="fa fa-check-circle-o fa-2x" style="color: green"></i></p>';

                                  echo '<p>CUIT: '. $arrayEmpresa['cuit'] . '</p>
                                  <p>Ticket acceso valido hasta: <br/>' . $fecform->format('d/m/Y - H:i:s') .' </p>';

                                  echo '<p>Entorno: ' .$arrayEmpresa['entorno_facturacion'] . '</p>';

                                } else {

                                    echo '<i class="fa fa-times-circle-o fa-2x" style="color: red"></i></p>';

                                    echo $msjError;

                                }

                                ?>

                            <li class="footer">
                                <!-- Punto de venta? -->
                            </li>

                            </ul>
                        </li>
                    </ul>
                </li>               

            <?php if($objParametros->getPrecioDolar()) { ?>
                <li class="dropdown tasks-menu">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                      <i class="fa fa-money"></i>
                    </a>
                    <ul class="dropdown-menu">
                        <li class="header" style="background-color: #000; color: #fff">Ultima actualizacion dolar</li>
                        <li>
                                <!-- inner menu: contains the actual data -->
                            <ul class="menu" style="background-color: #eee;">

                                <?php
                                 echo '<li>
                                      <h4>
                                        Fecha: <span>'.$result[0].'</span>
                                      </h4>
                                       <h4>
                                        Valor: $ <span id="cabezoteCotizacionPesos">'. $result[1] .'</span>
                                      </h4>

                                  </li>';

                              ?>
                                  <li class="footer">

                                    <center>
                                        <button class="btn btn-primary" data-toggle="modal" data-target="#modalNuevaCotizacion">
                          
                                      Nueva Cotización

                                        </button>
                                    </center>

                                  </li>
                                  <!-- end task item -->

                        </ul>
                      </li>
                    </ul>
                  </li>
            <?php } ?>
            
                <li class="dropdown user user-menu">
                    
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
    
                    <?php
                    
                    if($_SESSION["foto"] != ""){
                        echo '<img src="'.$_SESSION["foto"].'" class="user-image">';
                    }else{
                        echo '<img src="vistas/img/usuarios/default/anonymous.png" class="user-image">';
                    }
                    ?>
                        <span class="hidden-xs"><?php  echo $_SESSION["nombre"]; ?></span>
                    </a>

                    <!-- Dropdown-toggle -->
                    <ul class="dropdown-menu">
                        <li class="header" style="background-color: #000; color: #fff; padding: 5px">Datos usuario</li>
                        <li>
                            <!-- inner menu: contains the actual data -->
                            <ul class="menu" style="background-color: #eee;">
                                <p>Nombre: <?php echo $_SESSION["nombre"]; ?></p>
                                <p>Usuario: <?php echo $_SESSION["usuario"]; ?></p>
                                <p>Perfil: <?php echo $_SESSION["perfil"]; ?></p>
                                <center>
                                    <a href="salir" class="btn btn-primary ">Salir</a>
                                </center>
                            </ul>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </nav>
 </header>

 <!--=====================================
MODAL NUEVA COTIZACION
======================================-->
<div id="modalNuevaCotizacion" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <form role="form" method="post">

        <!--=====================================
        CABEZA DEL MODAL
        ======================================-->
        <div class="modal-header" style="background:#3c8dbc; color:white">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Nueva Cotización</h4>
        </div>

        <!--=====================================
        CUERPO DEL MODAL
        ======================================-->
        <div class="modal-body">
          <div class="box-body">

            <!-- ENTRADA PARA LA FECHA -->
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-calendar"></i></span> 
                <?php
                    date_default_timezone_set('America/Argentina/Buenos_Aires');
                    $fecha = date('d-m-Y');
                ?>
                <input type="text" readonly class="form-control input-lg" id="nuevaCotizacionFecha" name="nuevaCotizacionFecha" value="<?php echo $fecha; ?> ">
              </div>
            </div>
  
            <!-- ENTRADA PARA LA COTIZACION -->
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-th"></i></span> 
                <input type="number" step="0.01" min="0" class="form-control input-lg" name="nuevaCotizacionPesos" placeholder="Ingresar cotización" required>
              </div>
            </div>
  
          </div>
        </div>

        <!--=====================================
        PIE DEL MODAL
        ======================================-->
        <div class="modal-footer">
          <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Salir</button>
          <button type="submit" class="btn btn-primary">Guardar cotización</button>
        </div>

        <?php

          $nuevaCotizacion = new ControladorCotizacion();
          $nuevaCotizacion -> ctrNuevaCotizacion();

        ?>

      </form>
    </div>
  </div>
</div>  