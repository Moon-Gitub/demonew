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

      <button type="button" class="btn btn-tool" data-bs-toggle="collapse" data-bs-target="#ctaCteCard">

        <i class="bi bi-dash"></i>

      </button>

      <button type="button" class="btn btn-tool" data-bs-dismiss="card">

        <i class="bi bi-x"></i>

      </button>

    </div>

  </div>

  <div class="card-body collapse show" id="ctaCteCard">

    <div class="float-end col-lg-6 col-xs-6">

          <div class="small-box text-<?php echo $colorBoxProv; ?>">

            <div class="inner">

              <h3>$<?php echo number_format($saldoProv["saldo"], 2, ',', '.'); ?></h3>

              <p><b>Saldo total PROVEEDORES</b></p>

            </div>

            <svg class="small-box-icon" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
              <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1.41 16.09V20h-2.67v-1.93c-1.71-.36-3.16-1.46-3.27-3.4h1.96c.1 1.05.82 1.87 2.65 1.87 1.96 0 2.4-.98 2.4-1.59 0-.83-.44-1.61-2.67-2.14-2.48-.6-4.18-1.62-4.18-3.67 0-1.72 1.39-2.84 3.11-3.21V4h2.67v1.95c1.86.45 2.79 1.86 2.85 3.39H14.3c-.05-1.11-.64-1.87-2.22-1.87-1.5 0-2.4.68-2.4 1.64 0 .84.65 1.39 2.67 1.91s4.18 1.39 4.18 3.91c-.01 1.83-1.38 2.83-3.12 3.16z"></path>
            </svg>

            <a href="proveedores-cuenta-saldos" class="small-box-footer link-light link-underline-opacity-0 link-underline-opacity-50-hover">

              Más info <i class="bi bi-arrow-right-circle-fill"></i>

            </a>

          </div>

        </div>

    <div class="col-lg-6 col-xs-6">

          <div class="small-box text-<?php echo $colorBoxCli; ?>">

            <div class="inner">

              <h3>$<?php echo number_format($saldoClie["saldo"], 2, ',', '.'); ?></h3>

              <p><b>Saldo total CLIENTES</b></p>

            </div>

            <svg class="small-box-icon" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
              <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1.41 16.09V20h-2.67v-1.93c-1.71-.36-3.16-1.46-3.27-3.4h1.96c.1 1.05.82 1.87 2.65 1.87 1.96 0 2.4-.98 2.4-1.59 0-.83-.44-1.61-2.67-2.14-2.48-.6-4.18-1.62-4.18-3.67 0-1.72 1.39-2.84 3.11-3.21V4h2.67v1.95c1.86.45 2.79 1.86 2.85 3.39H14.3c-.05-1.11-.64-1.87-2.22-1.87-1.5 0-2.4.68-2.4 1.64 0 .84.65 1.39 2.67 1.91s4.18 1.39 4.18 3.91c-.01 1.83-1.38 2.83-3.12 3.16z"></path>
            </svg>

            <a href="clientes-cuenta-saldos" class="small-box-footer link-light link-underline-opacity-0 link-underline-opacity-50-hover">

              Más info <i class="bi bi-arrow-right-circle-fill"></i>

            </a>

          </div>

        </div>

  </div>

</div>