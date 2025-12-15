<?php

// Inicializar entorno (.env) para que Conexion pueda leer las credenciales
require_once dirname(__DIR__) . "/extensiones/vendor/autoload.php";
if (class_exists('Dotenv\\Dotenv')) {
    $raiz = dirname(__DIR__);
    if (file_exists($raiz . "/.env")) {
        $dotenv = Dotenv\Dotenv::createImmutable($raiz);
        $dotenv->safeLoad();
    }
}

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
$sSearch = isset($_GET['sSearch']) ? $_GET['sSearch'] : "";
if ( $sSearch != "" ) {
  $whereCondition = "WHERE (";
  for ( $i=0 ; $i<count($tableColumns) ; $i++ ) {
    $whereCondition .= $tableColumns[$i]." LIKE '%".mysqli_real_escape_string($mysqli, $sSearch)."%' OR ";
  }
  $whereCondition = substr_replace( $whereCondition, "", -3 );
  $whereCondition .= ')';
}

/* Individual column filtering */
for ( $i=0 ; $i<count($tableColumns) ; $i++ ) {
  $bSearchable = isset($_GET['bSearchable_'.$i]) ? $_GET['bSearchable_'.$i] : "false";
  $sSearchCol = isset($_GET['sSearch_'.$i]) ? $_GET['sSearch_'.$i] : "";
  if ( $bSearchable == "true" && $sSearchCol != '' ) {
    if ( $whereCondition == "" ) {
      $whereCondition = "WHERE ";
    } else {
      $whereCondition .= " AND ";
    }
    $whereCondition .= $tableColumns[$i]." LIKE '%".mysqli_real_escape_string($mysqli, $sSearchCol)."%' ";
  }
}

$sql = "SELECT id, codigo, descripcion, precio_compra, precio_venta 
FROM productos
$whereCondition 
$orderBy 
$limit";

// Ejecutar consulta con manejo de errores
$result = $mysqli->query($sql);
if (!$result) {
    http_response_code(500);
    echo json_encode([
        "sEcho" => intval($_GET['sEcho'] ?? 1),
        "iTotalRecords" => 0,
        "iTotalDisplayRecords" => 0,
        "aaData" => [],
        "error" => "Error en consulta: " . $mysqli->error
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Contar total de registros
$sql1 = "SELECT count(".$primaryKey.") as total from productos" . ($whereCondition ? " " . $whereCondition : "");
$result1 = $mysqli->query($sql1);
if (!$result1) {
    http_response_code(500);
    echo json_encode([
        "sEcho" => intval($_GET['sEcho'] ?? 1),
        "iTotalRecords" => 0,
        "iTotalDisplayRecords" => 0,
        "aaData" => [],
        "error" => "Error al contar registros: " . $mysqli->error
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$totalRecord = $result1->fetch_array();
$totalRecords = isset($totalRecord[0]) ? intval($totalRecord[0]) : 0;

$data = array();
while($row = $result->fetch_array(MYSQLI_ASSOC)){
    $data[] = mb_convert_encoding($row, 'UTF-8', 'ISO-8859-1');
}

$sEcho = isset($_GET['sEcho']) ? intval($_GET['sEcho']) : 1;

$output = [
    "sEcho" => $sEcho,
    "iTotalRecords" => $totalRecords,
    "iTotalDisplayRecords" => $totalRecords,
    "aaData" => $data
];

header('Content-Type: application/json; charset=utf-8');
echo json_encode($output, JSON_UNESCAPED_UNICODE);

?>
