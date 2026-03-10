<?php

/*
 * DataTables example server-side processing script.
 *
 * Please note that this script is intentionally extremely simple to show how
 * server-side processing can be implemented, and probably shouldn't be used as
 * the basis for a large complex system. It is suitable for simple use cases as
 * for learning.
 *
 * See http://datatables.net/usage/server-side for full details on the server-
 * side processing requirements of DataTables.
 *
 * @license MIT - http://datatables.net/license_mit
 */

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * Easy set variables
 */
 // ✅ Seguridad AJAX
require_once "seguridad.ajax.php";
SeguridadAjax::inicializar(false);
if (session_status() === PHP_SESSION_NONE) session_start();

 // Cargar vendor autoload primero (necesario para Dotenv)
require_once "../extensiones/vendor/autoload.php";

// Cargar variables de entorno desde .env PRIMERO (si existe y si Dotenv está instalado)
if (file_exists(__DIR__ . '/../.env') && class_exists('Dotenv\Dotenv')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
    $dotenv->load();
}

// Cargar helpers (incluye función env() para leer variables)
require_once "../helpers.php";
 
require_once "../modelos/conexion.php";
$db = new Conexion;
$con = $db->getDatosConexion();

// SQL server connection information
$sql_details = array(
    'user' => $con["user"],
    'pass' => $con["pass"],
    'db'   => $con["db"],
    'host' => $con["host"],
    'charset' => $con["charset"]
);

// Detectar columnas de stock (compatible con schema antigua stock/deposito y nueva stock/stock2/stock3)
$pdo = new PDO("mysql:host=".$con["host"].";dbname=".$con["db"].";charset=".($con["charset"] ?? "utf8"), $con["user"], $con["pass"]);
$cols = $pdo->query("SHOW COLUMNS FROM productos")->fetchAll(PDO::FETCH_COLUMN);
$tieneStock2 = in_array('stock2', $cols);
$tieneStock3 = in_array('stock3', $cols);
$tieneDeposito = in_array('deposito', $cols);

$stockCols = "IFNULL(IF(COALESCE(pd.stock,0)<0,0,COALESCE(pd.stock,0)),0) as stock";
if ($tieneStock2) {
    $stockCols .= ", IFNULL(IF(COALESCE(pd.stock2,0)<0,0,COALESCE(pd.stock2,0)),0) as stock2";
} elseif ($tieneDeposito) {
    $stockCols .= ", IFNULL(IF(COALESCE(pd.deposito,0)<0,0,COALESCE(pd.deposito,0)),0) as stock2";
} else {
    $stockCols .= ", 0 as stock2";
}
if ($tieneStock3) {
    $stockCols .= ", IFNULL(IF(COALESCE(pd.stock3,0)<0,0,COALESCE(pd.stock3,0)),0) as stock3";
} else {
    $stockCols .= ", 0 as stock3";
}

$table = " (
    SELECT
      pd.codigo,
      c.categoria,
      pv.nombre,
      pd.descripcion,
      $stockCols,
      pd.precio_compra,
      pd.precio_compra_dolar,
      pd.tipo_iva,
      pd.precio_venta,
      pd.id,
      pd.stock_medio,
      pd.stock_bajo
    FROM productos pd
    LEFT JOIN categorias c ON pd.id_categoria = c.id
    LEFT JOIN proveedores pv ON pd.id_proveedor = pv.id
    WHERE pd.activo = 1
 ) temp";

// Table's primary key
$primaryKey = 'id';
 
// Array of database columns which should be read and sent back to DataTables.
// The `db` parameter represents the column name in the database, while the `dt`
// parameter represents the DataTables column identifier. In this case simple
// indexes

$columns = array(
    array( 'db' => 'codigo',        
           'dt' => 0,
           'formatter' => function( $d, $row ) {
                return '<a href="index.php?ruta=productos-historial&idProducto='.$row["id"].'">'.$d.'</a>';
           }
       ),

    array( 'db' => 'categoria',     'dt' => 1 ),
    array( 'db' => 'nombre',        'dt' => 2 ),
    array( 'db' => 'descripcion',   'dt' => 3 ),
    array(
        'db' => 'stock',
        'dt' => 4,
        'formatter' => function( $d, $row ) {
            $suc = $_SESSION["sucursal"] ?? 'stock';
            $map = ['stock'=>'stock','stock1'=>'stock','deposito'=>'stock2','stock2'=>'stock2','stock3'=>'stock3','ameghino'=>'stock3','deposito2'=>'stock3'];
            $almacenDesde = $map[$suc] ?? (isset($row[$suc]) ? $suc : 'stock');
            $stk = isset($row[$almacenDesde]) ? (($row[$almacenDesde] < 0) ? 0 : floatval($row[$almacenDesde])) : 0;
            if($row["id"]>9) {
                if($stk <= $row["stock_bajo"]){
                    return '<h4><a class="btnEditarProductoAjusteStock" data-toggle="modal" data-target="#modalEditarProductoAjusteStock" idProducto="'.$row["id"].'" almacenDesde="'.htmlspecialchars($almacenDesde).'"><span class="label label-danger">'.number_format($stk,2).'</span></a></h4>';
                }else if($stk > $row["stock_bajo"] && $stk <= $row["stock_medio"]){
                    return '<h4><a class="btnEditarProductoAjusteStock" data-toggle="modal" data-target="#modalEditarProductoAjusteStock" idProducto="'.$row["id"].'" almacenDesde="'.htmlspecialchars($almacenDesde).'"><span class="label label-warning">'.number_format($stk,2).'</span></a></h4>';
                }else{
                    return '<h4><a class="btnEditarProductoAjusteStock" data-toggle="modal" data-target="#modalEditarProductoAjusteStock" idProducto="'.$row["id"].'" almacenDesde="'.htmlspecialchars($almacenDesde).'"><span class="label label-success">'.number_format($stk,2).'</span></a></h4>';
                }
            }
            return '-';
        }
    ),
    array(
        'db'        => 'id',
        'dt'        => 5,
        'formatter' => function( $d, $row ) {
            $almacenDesde = $_SESSION["sucursal"] ?? 'stock';
            if ($almacenDesde === 'deposito') $almacenDesde = 'stock2';
            if ($almacenDesde === 'stock1') $almacenDesde = 'stock';
            if (!isset($row[$almacenDesde])) $almacenDesde = 'stock';
            $total = floatval($row["stock"] ?? 0) + floatval($row["stock2"] ?? 0) + floatval($row["stock3"] ?? 0);
            if ($total < 0) $total = 0;

            if($total <= $row["stock_bajo"]){

                return '<h4><span class="label label-danger">'.number_format($total,2).'</span></h4>';

            }else if($total > $row["stock_bajo"] && $total <= $row["stock_medio"]){

                return '<h4><span class="label label-warning">'.number_format($total,2).'</span></h4>';

            }else{

                return '<h4><span class="label label-success">'.number_format($total,2).'</span></h4>';

            }
        }
    ),
    array(
        'db'        => 'precio_compra',
        'dt'        => 6,
        'formatter' => function( $d, $row ) {
            $d = is_null($d) ? 0 : $d;
            return '$ '.number_format($d,2);
        }
    ),
    array(
        'db'        => 'precio_compra_dolar',
        'dt'        => 7,
        'formatter' => function( $d, $row ) {
            $d = is_null($d) ? 0 : $d;
            return 'US$ '.number_format($d,2);
        }
    ),
    array(
        'db'        => 'tipo_iva',
        'dt'        => 8,
        'formatter' => function( $d, $row ) {
            $d = is_null($d) ? 0 : $d;
            return number_format($d,2).'%';
        }
    ),
    array(
        'db'        => 'precio_venta',
        'dt'        => 9,
        'formatter' => function( $d, $row ) {
            $d = is_null($d) ? 0 : $d;
            return '$ '.number_format($d,2);
        }
    ),
    array( 'db' => 'id', 
           'dt' => 10,
           'formatter' => function( $d, $row ) {
                $d = is_null($d) ? 0 : $d;
                $idProducto = $row["id"];
                $codigo = htmlspecialchars($row["codigo"] ?? '', ENT_QUOTES, 'UTF-8');
                
                $html = "<div class='acciones-tabla'>";
                $html .= "<a class='btn-accion btnEditarProducto' title='Editar producto' href='#' idProducto='".$idProducto."' data-toggle='modal' data-target='#modalEditarProducto'><i class='fa fa-pencil'></i></a>";
                
                if($row["id"] >= 10){
                    $html .= "<a class='btn-accion btn-danger btnEliminarProducto' title='Borrar producto' href='#' idProducto='".$idProducto."' codigo='".$codigo."'><i class='fa fa-times'></i></a>";
                }
                
                $html .= "</div>";
                return $html;
        } 
    ),
    array( 'db' => 'id', 'dt' => 11 ),
    array( 'db' => 'stock_medio', 'dt' => 12 ),
    array( 'db' => 'stock_bajo', 'dt' => 13 )
);

///PRUEBO RENDERIZAR DESDE EL FRONT (DE ACA MANDO EL DATO EN CRUDO)
/*
$columns = array(
    array( 'db' => 'codigo', 'dt' => 0),
    array( 'db' => 'categoria', 'dt' => 1),
    array( 'db' => 'nombre', 'dt' => 2),
    array( 'db' => 'descripcion', 'dt' => 3),
    array( 'db' => 'stock', 'dt' => 4),
    array( 'db' => 'stock_balloffet', 'dt' => 5),
    array( 'db' => 'stock_moreno', 'dt' => 6),
    array( 'db' => 'stock_edison', 'dt' => 7),
    array( 'db' => 'id', 'dt' => 8),
    array( 'db' => 'precio_compra', 'dt' => 9),
    array( 'db' => 'precio_compra_dolar', 'dt' => 10),
    array( 'db' => 'tipo_iva', 'dt' => 11),
    array( 'db' => 'precio_venta', 'dt' => 12),

    array( 'db' => 'id', 'dt' => 16),
    array( 'db' => 'id', 'dt' => 17 ),
    array( 'db' => 'stock_medio', 'dt' => 18 ),
    array( 'db' => 'stock_bajo', 'dt' => 19 )
);
 */ 
 
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * If you just want to use the basic configuration for DataTables with PHP
 * server-side, there is no need to edit below this line.
 */

require( '../extensiones/ssp.class.php' );

echo json_encode(SSP::simple( $_GET, $sql_details, $table, $primaryKey, $columns ));