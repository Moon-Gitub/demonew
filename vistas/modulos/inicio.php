<!--begin::App Main-->
<main class="app-main">
  <!--begin::App Content Header-->
  <div class="app-content-header">
    <div class="container-fluid">
      <div class="row">
        <div class="col-sm-6">
          <h3 class="mb-0">Tablero</h3>
          <small class="text-muted">Panel de Control</small>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-end mb-0">
            <li class="breadcrumb-item"><a href="inicio"><i class="bi bi-house"></i> Inicio</a></li>
            <li class="breadcrumb-item active" aria-current="page">Tablero</li>
          </ol>
        </div>
      </div>
    </div>
  </div>
  <!--end::App Content Header-->
  <!--begin::App Content-->
  <div class="app-content">
    <div class="container-fluid">
    <?php

      //VENTAS (INCLUYE CAJAS Y GRAFICO)
      if($_SESSION["perfil"] =="Administrador"){
        include "inicio/cajas-superiores.php";
      }

    ?>

    <div class="row">
        <div class="col-lg-6">

          <?php

            //ctas ctes clientes - proveedores
            if($_SESSION["perfil"] =="Administrador"){            
             include "inicio/cuentas-corrientes.php";
            }

          ?>

        </div>

        <div class="col-lg-6">
          <?php

            if($_SESSION["perfil"] =="Administrador"){
             include "reportes/productos-mas-vendidos.php";
            }

          ?>
        </div>

        <div class="col-lg-6">
          <?php

            if($_SESSION["perfil"] =="Administrador"){
             include "inicio/productos-recientes.php";
            }

          ?>
        </div>

         <div class="col-lg-12">
          <?php

          if($_SESSION["perfil"] !="Administrador"){
             echo '<div class="card card-success">
             <div class="card-header">
             <h1>Bienvenid@ ' .$_SESSION["nombre"].'</h1>
             </div>
              <div class="card-body">
              <div class="row">
              <div class="col-md-12">
                <a href="productos" class="btn btn-primary"><i class="bi bi-box-seam"></i> Productos</a>
                <a href="impresion-precios" class="btn btn-primary"><i class="bi bi-printer"></i> Imprimir Precios</a>
                <a href="cajas-cajero" class="btn btn-primary"><i class="bi bi-cash-coin"></i> Caja</a>
                <a href="crear-venta-caja" class="btn btn-primary"><i class="bi bi-plus-circle"></i> Venta</a>
                <a href="ventas" class="btn btn-primary"><i class="bi bi-graph-up"></i> Ventas</a>
                <a href="clientes" class="btn btn-primary"><i class="bi bi-people"></i> Clientes</a>
              </div>
              </div>
              </div>
             </div>';
          }
          ?>
         </div>
     </div>
    </div>
    <!--end::Container-->
  </div>
  <!--end::App Content-->
</main>
<!--end::App Main-->