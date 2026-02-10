<?php

// ajax/datatable-productos-inactivos.serverside.php

// ✅ Seguridad AJAX
require_once "seguridad.ajax.php";
SeguridadAjax::inicializar(false);

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

// Subconsulta: solo productos inactivos (activo = 0)
$table = <<<EOT
 (
    SELECT
      pd.codigo,
      c.categoria,
      pv.nombre,
      pd.descripcion,
      pd.stock,
      pd.id
    FROM productos pd
    LEFT JOIN categorias c ON pd.id_categoria = c.id
    LEFT JOIN proveedores pv ON pd.id_proveedor = pv.id
    WHERE pd.activo = 0
 ) temp
EOT;

// Clave primaria
$primaryKey = 'id';

// Columnas expuestas a DataTables
$columns = array(
    array( 'db' => 'codigo',     'dt' => 0 ),
    array( 'db' => 'categoria',  'dt' => 1 ),
    array( 'db' => 'nombre',     'dt' => 2 ),
    array( 'db' => 'descripcion','dt' => 3 ),
    array(
        'db' => 'stock',
        'dt' => 4,
        'formatter' => function( $d, $row ) {
            $d = is_null($d) ? 0 : $d;
            return number_format($d,2);
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

