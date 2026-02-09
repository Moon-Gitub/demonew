<?php 
  // Habilitar reporte de errores para debugging (temporal)
  error_reporting(E_ALL);
  ini_set('display_errors', 0); // No mostrar en pantalla, solo en logs
  ini_set('log_errors', 1);

  require_once __DIR__ . "/../../controladores/ventas.controlador.php";
  require_once __DIR__ . "/../../controladores/categorias.controlador.php";
  require_once __DIR__ . "/../../controladores/proveedores.controlador.php";
  require_once __DIR__ . "/../../controladores/productos.controlador.php";

  $desdeFecha = (isset($_POST["txtFechaDesdeVentasCategorias"]) || isset($_GET["fechaInicial"])) 
    ? (isset($_POST["txtFechaDesdeVentasCategorias"]) ? $_POST["txtFechaDesdeVentasCategorias"] : $_GET["fechaInicial"])
    : date('Y-m-d');
  $hastaFecha = (isset($_POST["txtFechaHastaVentasCategorias"]) || isset($_GET["fechaFinal"])) 
    ? (isset($_POST["txtFechaHastaVentasCategorias"]) ? $_POST["txtFechaHastaVentasCategorias"] : $_GET["fechaFinal"])
    : date('Y-m-d');

  $item = null;
  $valor = null;
  $categorias = ControladorCategorias::ctrMostrarCategorias($item, $valor);
  $proveedores = ControladorProveedores::ctrMostrarProveedores($item, $valor);
  
  // Validar que categorias y proveedores sean arrays
  if (!is_array($categorias)) {
    $categorias = array();
  }
  if (!is_array($proveedores)) {
    $proveedores = array();
  }

  $categoriaSel = (isset($_POST["informeVentasCatPro"]) && $_POST["informeVentasCatPro"] == "categoria") 
    || (isset($_GET["tipo"]) && $_GET["tipo"] == "categoria");
  $proveedorSel = (isset($_POST["informeVentasCatPro"]) && $_POST["informeVentasCatPro"] == "proveedor")
    || (isset($_GET["tipo"]) && $_GET["tipo"] == "proveedor");
  $productosSel = (isset($_POST["informeVentasCatPro"]) && $_POST["informeVentasCatPro"] == "producto")
    || (isset($_GET["tipo"]) && $_GET["tipo"] == "producto");

  // Si no hay selección, usar categoría por defecto
  if (!$categoriaSel && !$proveedorSel && !$productosSel) {
    $categoriaSel = true;
  }

  $totalVentas = ControladorVentas::ctrRangoFechasVentas($desdeFecha . ' 00:00', $hastaFecha . ' 23:59');

  // Validar que $totalVentas sea un array
  if (!is_array($totalVentas)) {
    $totalVentas = array();
  }

  $totalGeneral = 0;
  $cantidadGeneral = 0;

  if ($categoriaSel) {
    foreach ($totalVentas as $key => $value) {
      // Validar que value tenga id
      if (!isset($value["id"]) || empty($value["id"])) {
        continue;
      }
      
      // Obtener productos con combos expandidos (cada componente como producto separado)
      $productosVta = ControladorVentas::ctrObtenerProductosVentaExpandidoCombos($value["id"]);
      
      if (is_array($productosVta) && !empty($productosVta)) {
        for ($i=0; $i < count($productosVta); $i++) {
          $idProd = $productosVta[$i]["id_producto"] ?? $productosVta[$i]["id"] ?? 0;
          if (empty($idProd)) continue;
          
          $prodIterado = ControladorProductos::ctrMostrarProductos('id', $idProd, null);
          
          // Validar que prodIterado sea válido
          if (!$prodIterado || !isset($prodIterado["id_categoria"])) {
            continue;
          }
          
          for ($x=0; $x < count($categorias); $x++) {
            if (isset($categorias[$x]["id"]) && $categorias[$x]["id"] == $prodIterado["id_categoria"]) {
              $montoProducto = floatval($productosVta[$i]["total"] ?? 0);
              $cantidadProducto = floatval($productosVta[$i]["cantidad"] ?? 0);
              
              if (array_key_exists("montoAcumulado", $categorias[$x])) {
                $categorias[$x]["montoAcumulado"] = floatval($categorias[$x]["montoAcumulado"]) + $montoProducto;
                $categorias[$x]["cantidadVendida"] = floatval($categorias[$x]["cantidadVendida"] ?? 0) + $cantidadProducto;
              } else {
                $categorias[$x]["montoAcumulado"] = $montoProducto;
                $categorias[$x]["cantidadVendida"] = $cantidadProducto;
              }
              $totalGeneral += $montoProducto;
              $cantidadGeneral += $cantidadProducto;
              break 1;
            }
          }
        }
      }
    }
    // Filtrar solo categorías con ventas
    $categorias = array_filter($categorias, function($cat) {
      return isset($cat["montoAcumulado"]) && $cat["montoAcumulado"] > 0;
    });
    // Ordenar por monto descendente
    usort($categorias, function($a, $b) {
      $montoA = $a["montoAcumulado"] ?? 0;
      $montoB = $b["montoAcumulado"] ?? 0;
      return $montoB <=> $montoA;
    });
  }

  if($proveedorSel){
    foreach ($totalVentas as $key => $value) {
      // Validar que value tenga id
      if (!isset($value["id"]) || empty($value["id"])) {
        continue;
      }
      
      // Obtener productos con combos expandidos (cada componente como producto separado)
      $productosVta = ControladorVentas::ctrObtenerProductosVentaExpandidoCombos($value["id"]);
      
      if (is_array($productosVta) && !empty($productosVta)) {
        for ($i=0; $i < count($productosVta); $i++) {
          $idProd = $productosVta[$i]["id_producto"] ?? $productosVta[$i]["id"] ?? 0;
          if (empty($idProd)) continue;
          
          $prodIterado = ControladorProductos::ctrMostrarProductos('id', $idProd, null);
          
          // Validar que prodIterado sea válido
          if (!$prodIterado || !isset($prodIterado["id_proveedor"])) {
            continue;
          }
          
          for ($x=0; $x < count($proveedores); $x++) {
            if (isset($proveedores[$x]["id"]) && $proveedores[$x]["id"] == $prodIterado["id_proveedor"]) {
              $montoProducto = floatval($productosVta[$i]["total"] ?? 0);
              $cantidadProducto = floatval($productosVta[$i]["cantidad"] ?? 0);
              
              if (array_key_exists("montoAcumulado", $proveedores[$x])) {
                $proveedores[$x]["montoAcumulado"] = floatval($proveedores[$x]["montoAcumulado"]) + $montoProducto;
                $proveedores[$x]["cantidadVendida"] = floatval($proveedores[$x]["cantidadVendida"] ?? 0) + $cantidadProducto;
              } else {
                $proveedores[$x]["montoAcumulado"] = $montoProducto;
                $proveedores[$x]["cantidadVendida"] = $cantidadProducto;
              }
              $totalGeneral += $montoProducto;
              $cantidadGeneral += $cantidadProducto;
              break 1;
            }
          }
        }
      }
    }
    // Filtrar solo proveedores con ventas
    $proveedores = array_filter($proveedores, function($prov) {
      return isset($prov["montoAcumulado"]) && $prov["montoAcumulado"] > 0;
    });
    // Ordenar por monto descendente
    usort($proveedores, function($a, $b) {
      $montoA = $a["montoAcumulado"] ?? 0;
      $montoB = $b["montoAcumulado"] ?? 0;
      return $montoB <=> $montoA;
    });
  }
  
  if($productosSel){
    $arrayProductos = array();
    foreach ($totalVentas as $keyVenta => $value) {
      // Validar que value tenga id
      if (!isset($value["id"]) || empty($value["id"])) {
        continue;
      }
      
      // Obtener productos con combos expandidos (cada componente como producto separado)
      $productosVta = ControladorVentas::ctrObtenerProductosVentaExpandidoCombos($value["id"]);
      
      if (is_array($productosVta) && !empty($productosVta)) {
        for ($i=0; $i < count($productosVta); $i++) {
          $idProd = $productosVta[$i]["id_producto"] ?? $productosVta[$i]["id"] ?? 0;
          if (empty($idProd)) continue;
          
          $prodIterado = ControladorProductos::ctrMostrarProductos('id', $idProd, null);
          
          // Validar que prodIterado sea válido
          if (!$prodIterado || !isset($prodIterado["id"])) {
            continue;
          }
          
          $idProducto = intval($prodIterado["id"]);
          $montoProducto = floatval($productosVta[$i]["total"] ?? 0);
          $cantidadProducto = floatval($productosVta[$i]["cantidad"] ?? 0);
          
          if(array_key_exists($idProducto, $arrayProductos)){
            $arrayProductos[$idProducto]["cantidad"] += $cantidadProducto;
            $arrayProductos[$idProducto]["vendido"] += $montoProducto;
          } else {
            $valor = array(
              "codigo" => $prodIterado["codigo"] ?? "",
              "descripcion" => $prodIterado["descripcion"] ?? "Sin descripción", 
              "cantidad" => $cantidadProducto, 
              "vendido" => $montoProducto
            );
            $arrayProductos[$idProducto] = $valor;
          }
          
          $totalGeneral += $montoProducto;
          $cantidadGeneral += $cantidadProducto;
        }
      }
    }
    // Ordenar por monto vendido descendente
    uasort($arrayProductos, function($a, $b) {
      return $b["vendido"] <=> $a["vendido"];
    });
  }

?>

<style>
  /* ============================
     Estilos modernos para informe
     ============================ */

  .vcp-summary-row {
    margin-bottom: 20px;
  }

  .vcp-card {
    background: #ffffff;
    border-radius: 12px;
    padding: 20px 25px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    border: 1px solid #f0f0f0;
    margin-bottom: 15px;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
  }

  .vcp-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.12);
  }

  .vcp-card-title {
    font-size: 13px;
    text-transform: uppercase;
    color: #7f8c8d;
    margin-bottom: 8px;
    font-weight: 600;
    letter-spacing: 0.5px;
  }

  .vcp-card-value {
    font-size: 28px;
    font-weight: 700;
    color: #2c3e50;
    margin-bottom: 5px;
  }

  .vcp-card-sub {
    font-size: 12px;
    color: #95a5a6;
  }

  .vcp-card-primary {
    border-left: 4px solid #3498db;
  }

  .vcp-card-success {
    border-left: 4px solid #2ecc71;
  }

  .vcp-chart-container {
    background: #ffffff;
    border-radius: 12px;
    padding: 20px 25px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    border: 1px solid #f0f0f0;
    margin-bottom: 20px;
  }

  .vcp-chart-title {
    font-size: 16px;
    font-weight: 600;
    margin-bottom: 15px;
    color: #2c3e50;
    border-bottom: 2px solid #f0f0f0;
    padding-bottom: 10px;
  }

  .vcp-table-container {
    background: #ffffff;
    border-radius: 12px;
    padding: 20px 25px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    border: 1px solid #f0f0f0;
    margin-bottom: 20px;
  }

  .vcp-filter-container {
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
    border-radius: 12px;
    padding: 20px 25px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    border: 1px solid #f0f0f0;
    margin-bottom: 20px;
  }

  .vcp-radio-group {
    display: flex;
    gap: 20px;
    align-items: center;
    flex-wrap: wrap;
  }

  .vcp-radio-item {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px 15px;
    border-radius: 8px;
    background: #f8f9fa;
    cursor: pointer;
    transition: all 0.3s ease;
  }

  .vcp-radio-item:hover {
    background: #e9ecef;
  }

  .vcp-radio-item input[type="radio"] {
    margin: 0;
    cursor: pointer;
  }

  .vcp-radio-item input[type="radio"]:checked + label {
    color: #667eea;
    font-weight: 600;
  }

  .vcp-radio-item label {
    margin: 0;
    cursor: pointer;
    font-weight: 500;
  }

  #tablaVentasCategoriaProveedor thead tr {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
  }

  #tablaVentasCategoriaProveedor thead tr th {
    color: white;
    font-weight: 600;
    text-transform: uppercase;
    border: none;
    padding: 12px 8px;
  }
</style>

<div class="content-wrapper">

  <section class="content-header">
    <h1><i class="fa fa-area-chart"></i> Informes <small>Ventas por categoría / proveedor / productos</small></h1>
    <ol class="breadcrumb">
      <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>
      <li><a href="ventas-categoria-proveedor-informe">Informes</a></li>
      <li class="active">Ventas por categoría / proveedor</li>
    </ol>
  </section>

  <section class="content">

    <div class="box" style="border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,0.08); border: 1px solid #e0e0e0;">

      <div class="box-header with-border" style="background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%); border-bottom: 1px solid #e0e0e0; padding: 15px 20px;">
  
        <a class="btn btn-primary" href="ventas" style="border-radius: 8px;">
          <i class="fa fa-arrow-left"></i> Volver
        </a>

      </div>

      <div class="box-body" style="padding: 25px;">

        <!-- =======================
             Filtros
             ======================= -->
        <div class="vcp-filter-container">
          <form method="POST" id="formVentasCategoriaProveedor">
            <div class="row">
              <div class="col-md-4">
                <div class="form-group">
                  <label><i class="fa fa-calendar"></i> Rango de fechas</label>
                  <button type="button" class="btn btn-default form-control" id="daterangeVentasCategoriaProveedor" style="text-align: left;">
                    <span>
                      <i class="fa fa-calendar"></i> 
                      <?php echo $desdeFecha . ' - ' . $hastaFecha; ?>
                    </span>
                    <i class="fa fa-caret-down pull-right"></i>
                  </button>
                  <input type="hidden" id="txtFechaDesdeVentasCategorias" name="txtFechaDesdeVentasCategorias" value="<?php echo $desdeFecha; ?>">
                  <input type="hidden" id="txtFechaHastaVentasCategorias" name="txtFechaHastaVentasCategorias" value="<?php echo $hastaFecha; ?>">
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label>Agrupar por</label>
                  <div class="vcp-radio-group">
                    <div class="vcp-radio-item">
                      <input type="radio" name="informeVentasCatPro" value="categoria" id="radioCategoria" <?php echo $categoriaSel ? 'checked' : ''; ?>>
                      <label for="radioCategoria">Categorías</label>
                    </div>
                    <div class="vcp-radio-item">
                      <input type="radio" name="informeVentasCatPro" value="proveedor" id="radioProveedor" <?php echo $proveedorSel ? 'checked' : ''; ?>>
                      <label for="radioProveedor">Proveedores</label>
                    </div>
                    <div class="vcp-radio-item">
                      <input type="radio" name="informeVentasCatPro" value="producto" id="radioProducto" <?php echo $productosSel ? 'checked' : ''; ?>>
                      <label for="radioProducto">Productos</label>
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-md-2">
                <div class="form-group">
                  <label>&nbsp;</label>
                  <button type="submit" class="btn btn-primary form-control" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; border-radius: 8px;">
                    <i class="fa fa-search"></i> Buscar
                  </button>
                </div>
              </div>
            </div>
          </form>
        </div>

        <?php if ($categoriaSel || $proveedorSel || $productosSel): ?>

          <!-- =======================
               Cards de resumen
               ======================= -->
          <div class="row vcp-summary-row">
            <div class="col-md-4 col-sm-6">
              <div class="vcp-card vcp-card-primary">
                <div class="vcp-card-title">Total Vendido</div>
                <div class="vcp-card-value">
                  $ <?php echo number_format($totalGeneral, 2, ',', '.'); ?>
                </div>
                <div class="vcp-card-sub">Monto total en el período</div>
              </div>
            </div>
            <div class="col-md-4 col-sm-6">
              <div class="vcp-card vcp-card-success">
                <div class="vcp-card-title">Cantidad Total</div>
                <div class="vcp-card-value">
                  <?php echo number_format($cantidadGeneral, 0, ',', '.'); ?>
                </div>
                <div class="vcp-card-sub">Unidades vendidas</div>
              </div>
            </div>
            <div class="col-md-4 col-sm-6">
              <div class="vcp-card vcp-card-primary">
                <div class="vcp-card-title">Promedio por Unidad</div>
                <div class="vcp-card-value">
                  $ <?php echo $cantidadGeneral > 0 ? number_format($totalGeneral / $cantidadGeneral, 2, ',', '.') : '0,00'; ?>
                </div>
                <div class="vcp-card-sub">Precio promedio</div>
              </div>
            </div>
          </div>

          <!-- =======================
               Gráfico
               ======================= -->
          <div class="row">
            <div class="col-md-12">
              <div class="vcp-chart-container">
                <div class="vcp-chart-title">
                  <i class="fa fa-pie-chart"></i> 
                  Distribución de Ventas por <?php echo $categoriaSel ? 'Categorías' : ($proveedorSel ? 'Proveedores' : 'Productos'); ?>
                </div>
                <div class="chart-responsive" style="height: 400px;">
                  <canvas id="vcpChartDistribucion"></canvas>
                </div>
              </div>
            </div>
          </div>

          <!-- =======================
               Tabla de datos
               ======================= -->
          <div class="vcp-table-container">
            <div class="vcp-chart-title">
              <i class="fa fa-table"></i> 
              Detalle de Ventas por <?php echo $categoriaSel ? 'Categorías' : ($proveedorSel ? 'Proveedores' : 'Productos'); ?>
            </div>
            <div class="ventas-table-wrapper" style="overflow-x: auto; -webkit-overflow-scrolling: touch;">
            <table class="table table-bordered table-striped dt-responsive" id="tablaVentasCategoriaProveedor" width="100%" style="min-width: 600px;">
              <thead>
                <tr>
                  <?php if ($categoriaSel): ?>
                    <th>Categoría</th>
                  <?php elseif ($proveedorSel): ?>
                    <th>Proveedor</th>
                  <?php else: ?>
                    <th>Código</th>
                    <th>Descripción</th>
                  <?php endif; ?>
                  <th>Monto Vendido</th>
                  <th>Cantidad</th>
                  <th>Promedio</th>
                  <th>% del Total</th>
                </tr>
              </thead>
              <tbody>
                <?php
                  $datosGrafico = [];
                  $colores = ["#3498db","#e74c3c","#2ecc71","#9b59b6","#f1c40f","#1abc9c","#e67e22","#34495e","#ff7675","#6c5ce7"];
                  $colorIndex = 0;

                  if ($categoriaSel) {
                    foreach ($categorias as $key => $value) {
                      $monto = $value["montoAcumulado"] ?? 0;
                      $cantidad = $value["cantidadVendida"] ?? 0;
                      $promedio = $cantidad > 0 ? $monto / $cantidad : 0;
                      $porcentaje = $totalGeneral > 0 ? ($monto / $totalGeneral) * 100 : 0;
                      
                      echo '<tr>';
                      echo '<td><strong>' . htmlspecialchars($value["categoria"] ?? 'Sin categoría') . '</strong></td>';
                      echo '<td>$ ' . number_format($monto, 2, ',', '.') . '</td>';
                      echo '<td>' . number_format($cantidad, 0, ',', '.') . '</td>';
                      echo '<td>$ ' . number_format($promedio, 2, ',', '.') . '</td>';
                      echo '<td style="font-weight: 600; color: #667eea;">' . number_format($porcentaje, 2, ',', '.') . '%</td>';
                      echo '</tr>';

                      if ($monto > 0) {
                        $datosGrafico[] = [
                          "label" => substr($value["categoria"] ?? 'Sin categoría', 0, 30),
                          "value" => $monto,
                          "color" => $colores[$colorIndex % count($colores)]
                        ];
                        $colorIndex++;
                      }
                    }
                  } elseif ($proveedorSel) {
                    if (empty($proveedores)) {
                      echo '<tr><td colspan="5" class="text-center">No hay datos para el rango de fechas seleccionado.</td></tr>';
                    } else {
                      foreach ($proveedores as $key => $value) {
                        $monto = floatval($value["montoAcumulado"] ?? 0);
                        $cantidad = floatval($value["cantidadVendida"] ?? 0);
                        $promedio = $cantidad > 0 ? $monto / $cantidad : 0;
                        $porcentaje = $totalGeneral > 0 ? ($monto / $totalGeneral) * 100 : 0;
                        
                        echo '<tr>';
                        echo '<td><strong>' . htmlspecialchars($value["nombre"] ?? 'Sin proveedor') . '</strong></td>';
                        echo '<td>$ ' . number_format($monto, 2, ',', '.') . '</td>';
                        echo '<td>' . number_format($cantidad, 0, ',', '.') . '</td>';
                        echo '<td>$ ' . number_format($promedio, 2, ',', '.') . '</td>';
                        echo '<td style="font-weight: 600; color: #667eea;">' . number_format($porcentaje, 2, ',', '.') . '%</td>';
                        echo '</tr>';

                        if ($monto > 0) {
                          $datosGrafico[] = [
                            "label" => substr($value["nombre"] ?? 'Sin proveedor', 0, 30),
                            "value" => $monto,
                            "color" => $colores[$colorIndex % count($colores)]
                          ];
                          $colorIndex++;
                        }
                      }
                    }
                  } else {
                    if (empty($arrayProductos)) {
                      echo '<tr><td colspan="6" class="text-center">No hay datos para el rango de fechas seleccionado.</td></tr>';
                    } else {
                      $topProductos = array_slice($arrayProductos, 0, 50, true);
                      foreach ($topProductos as $key => $value) {
                        $monto = floatval($value["vendido"] ?? 0);
                        $cantidad = floatval($value["cantidad"] ?? 0);
                        $promedio = $cantidad > 0 ? $monto / $cantidad : 0;
                        $porcentaje = $totalGeneral > 0 ? ($monto / $totalGeneral) * 100 : 0;
                        
                        echo '<tr>';
                        echo '<td><strong>' . htmlspecialchars($value["codigo"] ?? '') . '</strong></td>';
                        echo '<td>' . htmlspecialchars($value["descripcion"] ?? 'Sin descripción') . '</td>';
                        echo '<td>$ ' . number_format($monto, 2, ',', '.') . '</td>';
                        echo '<td>' . number_format($cantidad, 0, ',', '.') . '</td>';
                        echo '<td>$ ' . number_format($promedio, 2, ',', '.') . '</td>';
                        echo '<td style="font-weight: 600; color: #667eea;">' . number_format($porcentaje, 2, ',', '.') . '%</td>';
                        echo '</tr>';

                        if ($monto > 0 && count($datosGrafico) < 10) {
                          $datosGrafico[] = [
                            "label" => substr($value["descripcion"] ?? '', 0, 30),
                            "value" => $monto,
                            "color" => $colores[$colorIndex % count($colores)]
                          ];
                          $colorIndex++;
                        }
                      }
                    }
                  }
                ?>
              </tbody>
            </table>
            </div>
          </div>

        <?php else: ?>

          <div class="alert alert-info" style="border-radius: 8px;">
            <i class="fa fa-info-circle"></i> Selecciona un tipo de agrupación y un rango de fechas para generar el informe.
          </div>

        <?php endif; ?>

      </div>

    </div>

  </section>

</div>

<script>
// ============================================
// Daterangepicker
// ============================================
$('#daterangeVentasCategoriaProveedor').daterangepicker(
  {
    ranges   : {
      'Hoy'       : [moment(), moment()],
      'Ayer'   : [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
      'Últimos 7 días' : [moment().subtract(6, 'days'), moment()],
      'Últimos 30 días': [moment().subtract(29, 'days'), moment()],
      'Este mes'  : [moment().startOf('month'), moment().endOf('month')],
      'Último mes'  : [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
    },
    startDate: moment('<?php echo $desdeFecha; ?>'),
    endDate  : moment('<?php echo $hastaFecha; ?>'),
    locale: {
      format: 'YYYY-MM-DD'
    }
  },
  function (start, end) {
    $('#daterangeVentasCategoriaProveedor span').html(start.format('YYYY-MM-DD') + ' - ' + end.format('YYYY-MM-DD'));
    $('#txtFechaDesdeVentasCategorias').val(start.format('YYYY-MM-DD'));
    $('#txtFechaHastaVentasCategorias').val(end.format('YYYY-MM-DD'));
  }
);

// ============================================
// Gráfico de distribución
// ============================================
$(document).ready(function() {
  var datosGrafico = <?php echo json_encode($datosGrafico); ?>;

  if (typeof Chart !== 'undefined' && datosGrafico.length > 0) {
    var ctx = document.getElementById('vcpChartDistribucion');
    if (ctx) {
      var chart = new Chart(ctx.getContext('2d'));
      var pieData = [];
      
      for (var i = 0; i < datosGrafico.length; i++) {
        pieData.push({
          value: datosGrafico[i].value,
          color: datosGrafico[i].color,
          highlight: datosGrafico[i].color,
          label: datosGrafico[i].label
        });
      }
      
      var pieOptions = {
        segmentShowStroke: true,
        segmentStrokeColor: '#fff',
        segmentStrokeWidth: 2,
        percentageInnerCutout: 50,
        animationSteps: 80,
        animationEasing: 'easeOutQuad',
        animateRotate: true,
        animateScale: false,
        responsive: true,
        maintainAspectRatio: false
      };
      
      chart.Doughnut(pieData, pieOptions);
    }
  }
});

// ============================================
// DataTable
// ============================================
var tablaVentasCategoriaProveedor = $("#tablaVentasCategoriaProveedor").DataTable({
  "order": [[ 1, "desc" ]],
  "pageLength": 25,
  "language": {
    "sProcessing": "Procesando...",
    "sLengthMenu": "Mostrar _MENU_ registros",
    "sZeroRecords": "No se encontraron resultados",
    "sEmptyTable": "Ningún dato disponible",
    "sInfo": "Mostrando registros del _START_ al _END_ de un total de _TOTAL_",
    "sInfoEmpty": "Mostrando registros del 0 al 0 de un total de 0",
    "sInfoFiltered": "(filtrado de un total de _MAX_ registros)",
    "sSearch": "Buscar:",
    "oPaginate": {
      "sFirst": "Primero",
      "sLast": "Último",
      "sNext": "Siguiente",
      "sPrevious": "Anterior"
    }
  },
  dom: 'Bfrtip',
  "buttons": [
    {
      extend: 'excelHtml5',
      text: '<i class="fa fa-file-excel-o"></i>',
      titleAttr: 'Exportar a Excel',
      className: 'btn btn-success'
    },
    {
      extend: 'pdfHtml5',
      text: '<i class="fa fa-file-pdf-o"></i> ',
      titleAttr: 'Exportar a PDF',
      className: 'btn btn-danger'
    },
    {
      extend: 'print',
      text: '<i class="fa fa-print"></i> ',
      titleAttr: 'Imprimir',
      className: 'btn btn-info'
    }
  ]
});

</script>
