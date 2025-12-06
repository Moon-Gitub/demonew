<?php

  $saldoProv = ControladorProveedoresCtaCte::ctrMostrarSaldoTotal();
  $colorBoxProv = ($saldoProv["saldo"] < 0) ? 'bg-warning' : 'bg-success';
  
  $saldoClie = ControladorClientesCtaCte::ctrMostrarSaldoTotal();
  $colorBoxCli = ($saldoProv["saldo"] > 0) ? 'bg-warning' : 'bg-success';

?>

<div class="card card-primary">

  <div class="card-header with-border">

    <h3 class="card-title">Saldos Cuenta Corriente</h3>

    <div class="card-tools float-end">

      <button type="button" class="btn btn-tool" data-bs-toggle="collapse">

        <i class="bi bi-dash"></i>

      </button>

      <button type="button" class="btn btn-tool" data-bs-dismiss="card">

        <i class="bi bi-x"></i>

      </button>

    </div>

  </div>
  
  <div class="card-body">

    <div class="float-end col-lg-6 col-xs-6">

          <div class="small-box <?php echo $colorBoxProv; ?>">
            
            <div class="inner" style="color: #000">
              
              <h3  >$<?php echo number_format($saldoProv["saldo"], 2, ',', '.'); ?></h3>

              <p><b>Saldo total PROVEEDORES</b></p>
            
            </div>
            
            <div class="icon">
              
              <i class="ion ion-social-usd"></i>
            
            </div>
            
            <a href="proveedores-cuenta-saldos" class="small-card-footer">
              
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
            
            <a href="clientes-cuenta-saldos" class="small-card-footer">
              
              Más info <i class="fa fa-arrow-circle-right"></i>
            
            </a>

          </div>

        </div>

  </div>

</div>