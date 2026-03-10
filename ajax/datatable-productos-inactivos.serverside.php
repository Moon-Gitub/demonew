<?php

// ajax/datatable-productos-inactivos.serverside.php

// ✅ Seguridad AJAX
require_once "seguridad.ajax.php";
SeguridadAjax::inicializar(false);
if (session_status() === PHP_SESSION_NONE) session_start();

require_once "../modelos/conexion.php";
$db = new Conexion;
$con = $db->getDatosConexion();

// Datos de conexión para SSP
$sql_details = array(
    'user'    => $con["user"],
    'pass'    => $con["pass"],
    'db'      => $con["db"],
    'host'    => $con["host"],
    'charset' => $con["charset"]
);

// Detectar columnas de stock (compatible stock/deposito/stock2/stock3)
$pdo = new PDO("mysql:host=".$con["host"].";dbname=".$con["db"].";charset=".($con["charset"] ?? "utf8"), $con["user"], $con["pass"]);
$cols = $pdo->query("SHOW COLUMNS FROM productos")->fetchAll(PDO::FETCH_COLUMN);
$tieneStock2 = in_array('stock2', $cols);
$tieneStock3 = in_array('stock3', $cols);
$tieneDeposito = in_array('deposito', $cols);
$tieneDeposito2 = in_array('deposito2', $cols);
$tieneAmeghino = in_array('ameghino', $cols);

$stockTotal = "(IFNULL(IF(COALESCE(pd.stock,0)<0,0,COALESCE(pd.stock,0)),0)";
if ($tieneStock2) {
    $stockTotal .= " + IFNULL(IF(COALESCE(pd.stock2,0)<0,0,COALESCE(pd.stock2,0)),0)";
} elseif ($tieneDeposito) {
    $stockTotal .= " + IFNULL(IF(COALESCE(pd.deposito,0)<0,0,COALESCE(pd.deposito,0)),0)";
}
if ($tieneStock3) {
    $stockTotal .= " + IFNULL(IF(COALESCE(pd.stock3,0)<0,0,COALESCE(pd.stock3,0)),0)";
} elseif ($tieneDeposito2) {
    $stockTotal .= " + IFNULL(IF(COALESCE(pd.deposito2,0)<0,0,COALESCE(pd.deposito2,0)),0)";
} elseif ($tieneAmeghino) {
    $stockTotal .= " + IFNULL(IF(COALESCE(pd.ameghino,0)<0,0,COALESCE(pd.ameghino,0)),0)";
}
$stockTotal .= ")";

// Subconsulta: solo productos inactivos (activo = 0)
$table = " (
    SELECT
      pd.codigo,
      c.categoria,
      pv.nombre,
      pd.descripcion,
      $stockTotal as stock_total,
      pd.id
    FROM productos pd
    LEFT JOIN categorias c ON pd.id_categoria = c.id
    LEFT JOIN proveedores pv ON pd.id_proveedor = pv.id
    WHERE pd.activo = 0
 ) temp";

// Clave primaria
$primaryKey = 'id';

// Columnas expuestas a DataTables
$columns = array(
    array( 'db' => 'codigo',     'dt' => 0 ),
    array( 'db' => 'categoria',  'dt' => 1 ),
    array( 'db' => 'nombre',     'dt' => 2 ),
    array( 'db' => 'descripcion','dt' => 3 ),
    array(
        'db' => 'stock_total',
        'dt' => 4,
        'formatter' => function( $d, $row ) {
            $d = is_null($d) ? 0 : floatval($d);
            return number_format(max(0, $d), 2);
        }
    ),
    array(
        'db'        => 'id',
        'dt'        => 5,
        'formatter' => function( $d, $row ) {
            $idProducto = $row["id"];
            $codigo = htmlspecialchars($row["codigo"] ?? '', ENT_QUOTES, 'UTF-8');

            $html = "<div class='acciones-tabla'>";
            $html .= "<button class='btn-accion btn-success btnActivarProducto' "
                   . "title='Activar producto' "
                   . "idProducto='".$idProducto."' codigo='".$codigo."'>"
                   . "<i class='fa fa-undo'></i></button>";
            $html .= "</div>";

            return $html;
        }
    )
);

// Script de apoyo para DataTables (misma clase que productos/pedidos)
require( '../extensiones/ssp.class.php' );

echo json_encode(
    SSP::simple( $_GET, $sql_details, $table, $primaryKey, $columns )
);

