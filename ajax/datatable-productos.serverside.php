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

// DB table to use
//$table = 'productos';
$table = <<<EOT
 (
    SELECT
      pd.codigo,
      c.categoria,
      pv.nombre,
      pd.descripcion,
      pd.stock, 
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
 ) temp
EOT;

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
            
            if($row["id"]>9) {
                if($d <= $row["stock_bajo"]){
    
                    return '<h4><a class="btnEditarProductoAjusteStock" data-toggle="modal" data-target="#modalEditarProductoAjusteStock" idProducto="'.$row["id"].'" almacenDesde="stock"><span class="label label-danger">'.number_format($d,2).'</span></a></h4>';
    
                }else if($d > $row["stock_bajo"] && $d <= $row["stock_medio"]){
    
                    return '<h4><a class="btnEditarProductoAjusteStock" data-toggle="modal" data-target="#modalEditarProductoAjusteStock" idProducto="'.$row["id"].'" almacenDesde="stock"><span class="label label-warning">'.number_format($d,2).'</span></a></h4>';
    
                }else{
    
                    return '<h4><a class="btnEditarProductoAjusteStock" data-toggle="modal" data-target="#modalEditarProductoAjusteStock" idProducto="'.$row["id"].'" almacenDesde="stock"><span class="label label-success">'.number_format($d,2).'</span></a></h4>';
    
                }
            }

        }
    ),
    array(
        'db'        => 'id',
        'dt'        => 5,
        'formatter' => function( $d, $row ) {
            $stkDepo = ($row["stock"] < 0) ? 0 : $row["stock"];

            $total = $stkDepo;

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
                if($row["id"] < 10){
                    return "<div class='btn-group'><button class='btn btn-warning btnEditarProducto' idProducto='".$row["id"]."' data-toggle='modal' data-target='#modalEditarProducto'><i class='fa fa-pencil'></i></button></div>";
                } else {
                    return "<div class='btn-group'><button class='btn btn-warning btnEditarProducto' idProducto='".$row["id"]."' data-toggle='modal' data-target='#modalEditarProducto'><i class='fa fa-pencil'></i></button><button class='btn btn-danger btnEliminarProducto' idProducto='".$row["id"]."' codigo='".$row["codigo"]."' ><i class='fa fa-times'></i></button><button class='btn btn-info' onclick='cargarArrarBorrarMultiple(".$row["id"].")' idProducto='".$row["id"]."' codigo='".$row["codigo"]."'><i class='fa fa-times'></i></button></div></div>";   
                }
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