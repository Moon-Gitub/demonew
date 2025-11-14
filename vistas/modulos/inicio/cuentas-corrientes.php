<?php

  $saldoProv = ControladorProveedoresCtaCte::ctrMostrarSaldoTotal();
  $colorBoxProv = ($saldoProv["saldo"] < 0) ? 'bg-warning' : 'bg-success';
  
  $saldoClie = ControladorClientesCtaCte::ctrMostrarSaldoTotal();
  $colorBoxCli = ($saldoProv["saldo"] > 0) ? 'bg-warning' : 'bg-success';

?>

<div class="box box-primary">

  <div class="box-header with-border">

    <h3 class="box-title">Saldos Cuenta Corriente</h3>

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

    <div class="pull-right col-lg-6 col-xs-6">

          <div class="small-box <?php echo $colorBoxProv; ?>">
            
            <div class="inner" style="color: #000">
              
              <h3  >$<?php echo number_format($saldoProv["saldo"], 2, ',', '.'); ?></h3>

              <p><b>Saldo total PROVEEDORES</b></p>
            
            </div>
            
            <div class="icon">
              
              <i class="ion ion-social-usd"></i>
            
            </div>
            
            <a href="proveedores-cuenta-saldos" class="small-box-footer">
              
              Más info <i class="fa fa-arrow-circle-right"></i>
            
            </a>

          </div>

        </div>

    <div class="col-lg-6 col-xs-6">

          <div class="small-box <?php echo $colorBoxCli; ?>">
            
            <div class="inner" style="color: #000">
              
              <h3>$<?php echo number_format($saldoClie["saldo"], 2, ',', '.'); ?></h3>

              <p><b>Saldo total CLIENTES</b></p>
            
            </div>
            
            <div class="icon">
              
              <i class="ion ion-social-usd"></i>
            
            </div>
            
            <a href="clientes-cuenta-saldos" class="small-box-footer">
              
              Más info <i class="fa fa-arrow-circle-right"></i>
            
            </a>

          </div>

        </div>

  </div>

</div>