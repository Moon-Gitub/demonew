<?php 

  if(isset($_GET["fechaInicial"])){

    $fechaInicial = $_GET["fechaInicial"];
    $fechaFinal = $_GET["fechaFinal"];

  }else{

    $fechaInicial =  date("Y-m-01");
    $fechaFinal = date('Y-m-t');
    //$fechaInicial =  date("Y-m-d");
    //$fechaFinal = date('Y-m-d');

  }

 ?>
<div class="content-wrapper">

  <section class="content-header">
    
    <h1>
      
      Administrar cierres caja
    
    </h1>

    <ol class="breadcrumb">
      
      <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>
      
      <li class="active">Administrar cierres caja</li>
    
    </ol>

  </section>

  <section class="content">

    <div class="box">

      <div class="box-header">
        <button type="button" class="btn btn-default pull-right" id="daterangeCierresCajas">
           
            <span>
              <i class="fa fa-calendar"></i> 

              <?php

                echo $fechaInicial . ' - ' . $fechaFinal;

              ?>
            </span>

            <i class="fa fa-caret-down"></i>

         </button>
      </div>

      <div class="box-body">
        
       <table class="table table-bordered table-striped dt-responsive tablas tablaCierresCaja" width="100%">
         
        <thead>
         
         <tr>
           
           
           <th>Fecha</th>
           <th>Punto</th>
           <th>Ingresos</th>
           <th>Egresos</th>
           <th>Detalle</th>
           <th>Acciones</th>

         </tr> 

        </thead>

        <tbody>

        <?php

          $cierres = ControladorCajaCierres::ctrRangoFechasCajaCierres($fechaInicial, $fechaFinal);

          foreach ($cierres as $key => $value) {
           
            echo ' <tr>

                    <td class="text-uppercase">'.$value["fecha_hora"].'</td>
                    <td>'.$value["punto_venta_cobro"].'</td>
                    <td class="text-uppercase">'.$value["total_ingresos"].'</td>
                    <td >'.$value["total_egresos"].'</td>
                    <td >'.$value["detalle"].'</td>
                    <td >
                        <button class="btnCierreCaja" idCierreCaja="'.$value["id"].'" data-toggle="modal" data-target="#modalVerCierreCaja" data-dismiss="modal"><i class="fa fa-search"></i></button>
                        <button class="btnListadoCierreCaja" idCierreCaja="'.$value["id"].'" ><i class="fa fa-list-ul"></i></button>
                    </td>

                  </tr>';
          }

        ?>

        </tbody>

       </table>

      </div>

        <div class="box-body">
           <div id="listadoMovCierreCajaContenedor" style="display: none">
               <hr>
                <h3>Detalle de movimientos</h3>
                
                <table class="table table-bordered table-striped dt-responsive " width="100%" id="listadoMovCierreCajaTabla">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Control</th>
                            <th>Usuario</th>
                            <th>Punto</th>
                            <th>Detalle</th>
                            <th>Medio</th>
                            <th>Entrada</th>
                            <th>Salida</th>
                        </tr>
                    </thead>
                    <tbody>
                        
                    </tbody>
                </table>
            </div>
        </div>

    </div>

  </section>

</div>


<!--=====================================
MODAL INGRESAR MOVIMIENTO
======================================-->
<div id="modalVerCierreCaja" class="modal fade" role="dialog">
  
  <div class="modal-dialog modal-lg">

    <div class="modal-content">

       <!--=====================================
        CABEZA DEL MODAL
        ======================================-->
        <div class="modal-header" style="background:#3c8dbc; color:white">

          <button type="button" class="close" data-dismiss="modal">&times;</button>

          <h4 class="modal-title">Cierre Caja</h4>

        </div>

        <!--=====================================
        CUERPO DEL MODAL
        ======================================-->
        <div class="modal-body">

          <div class="box-body">

            <div class="form-group row">

              <div class="col-xs-3" > <b>Fecha: </b> <span id="resumenCierreCajaFecha"></span></div>
              <div class="col-xs-3" > <b>Punto: </b> <span id="resumenCierreCajaPunto"></span></div>
              <div class="col-xs-3" > <b>Usuario:</b> <span id="resumenCierreCajaUsuario"></span></div>
              <div class="col-xs-3" > <b>Apertura:</b> $ <span id="resumenCierreCajaApertura"></span></div>

            </div>

            <div class="form-group row">

              <div class="col-xs-3" > <b>Detalle:</b> <span id="resumenCierreCajaDetalle"></span></div>

            </div>
      
            <!--DETALLE INGRESOS EGRESOS -->
            <div class="form-group row">

              <div class="col-xs-6" style="color: green; font-size:15px">INGRESOS: <b><span id="resumenCierreTotalIngresos"></span></b></div>
              <div class="col-xs-6" style="color: red; font-size:15px">EGRESOS:  <b><span id="resumenCierreTotalEgresos"></span></b></div>

            </div>
            <hr>
            <!--DETALLE INGRESOS EGRESOS -->
            <b>Detalle Movimientos:</b>
            <div class="form-group row">

              <div class="col-xs-6"><table id="tblIngresosCategoriasResumenCierreCaja" width="100%"></table></div>
              <div class="col-xs-6"><table id="tblEgresosComunesResumenCierreCaja" width="100%"></table></div>

            </div>
            
            <b>Detalle Cuentas Corrientes:</b>
            <div class="form-group row">

              <div class="col-xs-6" style="color: green;">Cta. Cte. Cliente <b></b></div>
              <div class="col-xs-6" style="color: red;"> Cta. Cte. Proveedor: <b></b></div>

            </div>

            <!--DETALLE INGRESOS EGRESOS -->
            <div class="form-group row">

              <div class="col-xs-6"><table id="tblIngresosClientesResumenCierreCaja" width="100%"></table></div>
              <div class="col-xs-6"><table id="tblEgresosProveedoresResumenCierreCaja" width="100%"></table></div>

            </div>

            <hr>

            <!--DETALLE INGRESOS EGRESOS -->
            <b>Detalle medio pago/cobro:</b>
            <div class="form-group row">

              <div class="col-xs-6"><table id="tblIngresosDetalleMediosPago" width="100%"></table></div>
              <div class="col-xs-6"><table id="tblEgresosDetalleMediosPago" width="100%"></table></div>

            </div>
            
          </div>

        </div>

        <!--=====================================
        PIE DEL MODAL
        ======================================-->
        <div class="modal-footer">

          <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Salir</button>

        </div>

    </div>

  </div>

</div>
