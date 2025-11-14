<?php

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

$mysqli = new mysqli($sql_details["host"],$sql_details["user"],$sql_details["pass"],$sql_details["db"]);
$tableColumns = array('id', 'codigo', 'descripcion','precio_compra', 'precio_venta');
$primaryKey = "id";
$limit = "";

if (isset($_GET['iDisplayStart'] ) && $_GET['iDisplayLength'] != '-1' ) {
  $limit = "LIMIT ".mysqli_real_escape_string($mysqli,$_GET['iDisplayStart'] ).", ".
    mysqli_real_escape_string($mysqli,$_GET['iDisplayLength'] );
}

/*
 * Ordering
 */
if ( isset( $_GET['iSortCol_0'] ) ) {

  $orderBy = "ORDER BY  ";
  for ( $i=0 ; $i<intval( $_GET['iSortingCols'] ) ; $i++ ) {
    if ( $_GET[ 'bSortable_'.intval($_GET['iSortCol_'.$i]) ] == "true" ) {
      $orderBy .= $tableColumns[ intval( $_GET['iSortCol_'.$i] ) ]."
        ".mysqli_real_escape_string($mysqli,$_GET['sSortDir_'.$i] ) .", ";
    }
  }
  
  $orderBy = substr_replace( $orderBy, "", -2 );
  if ( $orderBy == "ORDER BY" ) {
    $orderBy = "";
  }
}

/* 
 * Filtering
 */
$whereCondition = "";
if ( $_GET['sSearch'] != "" ) {
  $whereCondition = "WHERE (";
  for ( $i=0 ; $i<count($tableColumns) ; $i++ ) {
    $whereCondition .= $tableColumns[$i]." LIKE '%".mysqli_real_escape_string($mysqli,$_GET['sSearch'] )."%' OR ";
  }
  $whereCondition = substr_replace( $whereCondition, "", -3 );
  $whereCondition .= ')';
}

/* Individual column filtering */
for ( $i=0 ; $i<count($tableColumns) ; $i++ ) {
  if ( $_GET['bSearchable_'.$i] == "true" && $_GET['sSearch_'.$i] != '' ) {
    if ( $whereCondition == "" ) {
      $whereCondition = "WHERE ";
    } else {
      $whereCondition .= " AND ";
    }
    $whereCondition .= $tableColumns[$i]." LIKE '%".mysqli_real_escape_string($mysqli,$_GET['sSearch_'.$i])."%' ";
  }
}

$sql = "SELECT id, codigo, descripcion, precio_compra, precio_venta 
FROM productos
$whereCondition 
$orderBy 
$limit";

// echo $sql;die;
$result = $mysqli->query($sql);

$sql1 = "SELECT count(".$primaryKey.") from productos";
$result1 = $mysqli->query($sql1);
$totalRecord=$result1->fetch_array();

$data=array();
while($row = $result->fetch_array(MYSQLI_ASSOC)){
  $data[] =  mb_convert_encoding($row, 'UTF-8', 'ISO-8859-1'); //array_map("utf8_encode",$row);
}

$output = ["sEcho" => intval($_GET['sEcho']),
          "iTotalRecords" => $totalRecord[0],
          "iTotalDisplayRecords" => $totalRecord[0],
          "aaData" => $data ];

echo json_encode($output, JSON_UNESCAPED_UNICODE);

?>
