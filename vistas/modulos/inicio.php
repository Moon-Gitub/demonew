<div class="content-wrapper">
  <section class="content-header">
    <h1>
      Tablero
      <small>Panel de Control</small>
    </h1>
    <ol class="breadcrumb">
      <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>
      <li class="active">Tablero</li>
    </ol>
  </section>
  <section class="content">
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
             echo '<div class="box box-success">
             <div class="box-header">
             <h1>Bienvenid@ ' .$_SESSION["nombre"].'</h1>
             </div>
              <div class="row">
              <div class="col-md-12">
                <a href="productos" class="btn btn-primary"><i class="fa fa-product-hunt"></i> Productos</a>
                <a href="impresion-precios" class="btn btn-primary"><i class="fa fa-print"></i> Imprimir Precios</a>
                <a href="cajas-cajero" class="btn btn-primary"><i class="fa fa-dollar"></i> Caja</a>
                <a href="crear-venta-caja" class="btn btn-primary"><i class="fa fa-plus"></i> Venta</a>
                <a href="ventas" class="btn btn-primary"><i class="fa fa-line-chart"></i> Ventas</a>
                <a href="clientes" class="btn btn-primary"><i class="fa fa-user"></i> Clientes</a>
              </div>
              </div>
             </div>';
          }
          ?>
         </div>
     </div>
  </section>
</div>