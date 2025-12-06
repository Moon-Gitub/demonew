<?php

$item = null;
$valor = null;
$orden = "id";

$productos = ControladorProductos::ctrMostrarProductos($item, $valor, $orden);

$totProductos = (count($productos) < 10) ? count($productos) : 10;

 ?>


<div class="card card-primary">

  <div class="card-header with-border">

    <h3 class="card-title">Productos añadidos recientemente</h3>

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

    <ul class="products-list product-list-in-box">

    <?php

    for($i = 0; $i < $totProductos; $i++){

      echo '<li class="item">

        <div class="product-img">

          <img src="'.$productos[$i]["imagen"].'" alt="Product Image">

        </div>

        <div class="product-info">

          <a href="" class="product-title">

            '.$productos[$i]["descripcion"].'

            <span class="label label-warning float-end">$'.$productos[$i]["precio_venta"].'</span>

          </a>
    
       </div>

      </li>';

    }

    ?>

    </ul>

  </div>

  <div class="card-footer text-center">

    <a href="productos" class="uppercase">Ver todos los productos</a>
  
  </div>

</div>
