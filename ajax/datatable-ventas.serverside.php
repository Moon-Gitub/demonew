<?php
/*
 * DataTables server-side processing para ventas
 * Optimizado con JOINs para evitar consultas N+1
 */

// ✅ Seguridad AJAX
require_once "seguridad.ajax.php";
SeguridadAjax::inicializar(false);

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
require_once "../controladores/empresa.controlador.php";

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

// Obtener fechas del filtro (GET o POST)
$fechaInicial = isset($_GET['fechaInicial']) ? $_GET['fechaInicial'] : (isset($_POST['fechaInicial']) ? $_POST['fechaInicial'] : null);
$fechaFinal = isset($_GET['fechaFinal']) ? $_GET['fechaFinal'] : (isset($_POST['fechaFinal']) ? $_POST['fechaFinal'] : null);

// Obtener datos de empresa para puntos de venta
$arrayEmpresa = ControladorEmpresa::ctrMostrarempresa('id', 1);
$arrPuntos = json_decode($arrayEmpresa['ptos_venta'], true);
if (!is_array($arrPuntos)) {
    $arrPuntos = [];
}

// Tipos de comprobantes
$tiposCbtes = array(
    0 => 'X',
    999 => 'Devolucion',
    1 => 'Factura A',
    6 => 'Factura B', 
    11 => 'Factura C',
    51 => 'Factura M',
    2 => 'Nota Débito A',
    7 => 'Nota Débito B',
    12 => 'Nota Débito C',
    52 => 'Nota Débito M',
    3 => 'Nota Crédito A',
    8 => 'Nota Crédito B',
    13 => 'Nota Crédito C',
    53 => 'Nota Crédito M',
    4 => 'Recibo A',
    9 => 'Recibo B',
    15 => 'Recibo C',
    54 => 'Recibo M',
    '' => 'no definido'
);

// Construir WHERE para fechas ANTES de crear la subconsulta
$whereFechaSubquery = "";
if ($fechaInicial && $fechaFinal) {
    // Limpiar fechas (pueden venir con o sin hora)
    $fechaInicial = trim($fechaInicial);
    $fechaFinal = trim($fechaFinal);
    
    // Si no tienen hora, agregarla
    if (strlen($fechaInicial) == 10) {
        $fechaInicial .= ' 00:00';
    }
    if (strlen($fechaFinal) == 10) {
        $fechaFinal .= ' 23:59';
    }
    
    if ($fechaInicial == $fechaFinal) {
        $whereFechaSubquery = "WHERE v.fecha LIKE '%" . substr($fechaFinal, 0, 10) . "%'";
    } else {
        $fechaActual = new DateTime();
        $fechaActual->add(new DateInterval("P1D"));
        $fechaActualMasUno = $fechaActual->format("Y-m-d");
        $fechaFinal2 = new DateTime($fechaFinal);
        $fechaFinal2->add(new DateInterval("P1D"));
        $fechaFinalMasUno = $fechaFinal2->format("Y-m-d");
        
        if ($fechaFinalMasUno == $fechaActualMasUno) {
            $whereFechaSubquery = "WHERE v.fecha BETWEEN '" . $fechaInicial . "' AND '" . $fechaFinalMasUno . " 23:59'";
        } else {
            $whereFechaSubquery = "WHERE v.fecha BETWEEN '" . $fechaInicial . "' AND '" . $fechaFinal . "'";
        }
    }
} else {
    // Si no hay fechas, usar fecha de hoy
    date_default_timezone_set('America/Argentina/Mendoza');
    $hoy = date('Y-m-d');
    $whereFechaSubquery = "WHERE v.fecha BETWEEN '" . $hoy . " 00:00' AND '" . $hoy . " 23:59'";
}

// Tabla con JOINs optimizados (con WHERE en la subconsulta)
$table = <<<EOT
 (
    SELECT
      v.id,
      v.fecha,
      v.codigo,
      v.cbte_tipo,
      v.pto_vta,
      v.id_cliente,
      v.id_empresa,
      v.estado,
      v.total,
      v.metodo_pago,
      v.observaciones_vta,
      e.titular as empresa_titular,
      c.nombre as cliente_nombre,
      c.email as cliente_email,
      vf.nro_cbte,
      vf.cae,
      vf.fec_factura
    FROM ventas v
    LEFT JOIN empresa e ON v.id_empresa = e.id
    LEFT JOIN clientes c ON v.id_cliente = c.id
    LEFT JOIN ventas_factura vf ON v.id = vf.id_venta
    $whereFechaSubquery
 ) temp
EOT;

// Table's primary key
$primaryKey = 'id';
 
// Array of database columns which should be read and sent back to DataTables.
$columns = array(
    // Columna 0: Fecha
    array( 'db' => 'fecha', 'dt' => 0 ),
    
    // Columna 1: Empresa
    array( 
        'db' => 'empresa_titular', 
        'dt' => 1,
        'formatter' => function( $d, $row ) {
            return $d ? $d : '';
        }
    ),
    
    // Columna 2: Nro. Int. (codigo)
    array( 
        'db' => 'codigo', 
        'dt' => 2,
        'formatter' => function( $d, $row ) {
            return '<a href="index.php?ruta=editar-venta&idVenta='.$row["id"].'">' . $d . '</a>';
        }
    ),
    
    // Columna 3: Sucursal (pto_vta)
    array( 
        'db' => 'pto_vta', 
        'dt' => 3,
        'formatter' => function( $d, $row ) use ($arrPuntos) {
            if (is_array($arrPuntos) && !empty($arrPuntos)) {
                $buscoPto = array_search($d, array_column($arrPuntos, 'pto'));
                return ($buscoPto !== false && isset($arrPuntos[$buscoPto]["det"])) ? $arrPuntos[$buscoPto]["det"] : '';
            }
            return '';
        }
    ),
    
    // Columna 4: Cbte. (tipo + número factura)
    array( 
        'db' => 'cbte_tipo', 
        'dt' => 4,
        'formatter' => function( $d, $row ) use ($tiposCbtes) {
            $facturada = !empty($row["nro_cbte"]);
            
            if ($facturada) {
                $imgAut = '<i class="fa fa-check" style="color: green;"></i>';
                $ptoVta = str_pad($row["pto_vta"], 5, "0", STR_PAD_LEFT);
                $numCte = str_pad($row["nro_cbte"], 8, "0", STR_PAD_LEFT);
                $numFact = $ptoVta . '-' . $numCte;
            } else {
                $imgAut = '';
                if (!empty($row["observaciones_vta"])) {
                    $imgAut = '<i class="fa fa-exclamation-triangle" style="color: #f39c12;"></i>';
                } else {
                    if ($d == 0 || $d == 999) {
                        $imgAut = '';
                    } else {
                        $imgAut = '<i class="fa fa-times" style="color: red;"></i>';
                    }
                }
                $numFact = "";
            }
            
            $tpCbte = $imgAut . ' ' . (isset($tiposCbtes[$d]) ? $tiposCbtes[$d] : 'no definido');
            return '<center>' . $tpCbte . '<br>' . $numFact . '</center>';
        }
    ),
    
    // Columna 5: Cliente
    array( 
        'db' => 'cliente_nombre', 
        'dt' => 5,
        'formatter' => function( $d, $row ) {
            if (!$d) {
                return 'Cliente no encontrado';
            }
            if ($row["id_cliente"] == 1) {
                return $d;
            } else {
                return '<a href="index.php?ruta=clientes_cuenta&id_cliente='.$row["id_cliente"].'">'.$d.'</a>';
            }
        }
    ),
    
    // Columna 6: Medio pago
    array( 
        'db' => 'metodo_pago', 
        'dt' => 6,
        'formatter' => function( $d, $row ) {
            $arrMetodoPago = json_decode($d);
            $metPago = "";
            if (is_array($arrMetodoPago)) {
                for ($i=0; $i < count($arrMetodoPago); $i++) { 
                    $metPago .= $arrMetodoPago[$i]->tipo . '<br>';
                }
            }
            return $metPago;
        }
    ),
    
    // Columna 7: Estado
    array( 
        'db' => 'estado', 
        'dt' => 7,
        'formatter' => function( $d, $row ) {
            $signoVta = ($row["cbte_tipo"] == 3 || $row["cbte_tipo"] == 8 || $row["cbte_tipo"] == 13 || $row["cbte_tipo"] == 999 || $row["cbte_tipo"] == 203 || $row["cbte_tipo"] == 208 || $row["cbte_tipo"] == 213) ? '-' : '';
            
            if ($signoVta == '') {
                if ($d == 0) { // Adeudada
                    return '<span style="cursor: pointer" class="label label-danger btnCobrarVenta" data-toggle="modal" data-target="#modalCobrarVenta" data-dismiss="modal" idVenta="'.$row["id"].'">Adeudado</span>';
                } elseif ($d == 1) { // Pagada
                    return '<span class="label label-success">Pagado</span>';
                } elseif ($d == 2) { // Cta. Cte.
                    return '<span class="label label-warning">Cta. Cte.</span>';
                }
            }
            return '';
        }
    ),
    
    // Columna 8: Total
    array( 
        'db' => 'total', 
        'dt' => 8,
        'formatter' => function( $d, $row ) {
            $signoVta = ($row["cbte_tipo"] == 3 || $row["cbte_tipo"] == 8 || $row["cbte_tipo"] == 13 || $row["cbte_tipo"] == 999 || $row["cbte_tipo"] == 203 || $row["cbte_tipo"] == 208 || $row["cbte_tipo"] == 213) ? '-' : '';
            return $signoVta . round($d, 2);
        }
    ),
    
    // Columna 9: Acciones
    array( 
        'db' => 'id', 
        'dt' => 9,
        'formatter' => function( $d, $row ) use ($tiposCbtes) {
            $facturada = !empty($row["nro_cbte"]);
            $signoVta = ($row["cbte_tipo"] == 3 || $row["cbte_tipo"] == 8 || $row["cbte_tipo"] == 13 || $row["cbte_tipo"] == 999 || $row["cbte_tipo"] == 203 || $row["cbte_tipo"] == 208 || $row["cbte_tipo"] == 213) ? '-' : '';
            
            // Determinar estados de botones
            $deshAutorizarA = $facturada ? 'pointer-events: none; opacity: 0.4' : '';
            if ($signoVta != '') {
                $deshAutorizarA = 'pointer-events: none; opacity: 0.4';
            }
            
            $botonCobro = 'pointer-events: none;';
            $lblEstado = '';
            if ($signoVta == '') {
                if ($row["estado"] == 0) { // Adeudada
                    $botonCobro = 'cursor: pointer;';
                } elseif ($row["estado"] == 1) { // Pagada
                    $botonCobro = 'pointer-events: none;';
                } elseif ($row["estado"] == 2) { // Cta. Cte.
                    $botonCobro = 'pointer-events: none;';
                }
            }
            
            $html = '<div class="acciones-ventas">';
            
            // Botón Cobrar
            $html .= '<a class="btn-accion btn-success btnCobrarVenta" title="Cobrar venta" style="' . $botonCobro . '" data-toggle="modal" data-target="#modalCobrarVenta" data-dismiss="modal" idVenta="'.$d.'"><i class="fa fa-usd"></i></a>';
            
            // Botón Autorizar
            $html .= '<a class="btn-accion btn-primary btnAutorizarCbte" title="Autorizar comprobante" style="' . $deshAutorizarA . '" data-toggle="modal" data-target="#modalAutorizarComprobante" data-dismiss="modal" idVenta="'.$d.'"><i class="fa fa-exchange"></i></a>';
            
            // Botón Ver/Editar
            $html .= '<a class="btn-accion btnEditarVenta" title="Ver/Editar venta" style="cursor: pointer;" idVenta="'.$d.'"><i class="fa fa-pencil"></i></a>';
            
            // Botón Descargar
            $html .= '<a class="btn-accion btnDescargarFactura" title="Descargar factura" style="cursor: pointer;" codigoVenta="'.$row["codigo"].'"><i class="fa fa-download"></i></a>';
            
            // Botón Imprimir
            $html .= '<a class="btn-accion btnImprimirFactura" title="Imprimir factura" style="cursor: pointer;" codigoVenta="'.$row["codigo"].'"><i class="fa fa-print"></i></a>';
            
            // Botón Remito
            $html .= '<a class="btn-accion btnImprimirRemito" title="Imprimir remito" style="cursor: pointer;" codigoVenta="'.$row["codigo"].'"><i class="fa fa-cubes"></i></a>';
            
            // Botón Ticket
            $html .= '<a class="btn-accion btnImprimirTicket" title="Imprimir ticket" style="cursor: pointer;" idVenta="'.$d.'" data-toggle="modal" data-target="#modalImprimirTicketCajaVenta" data-dismiss="modal"><i class="fa fa-ticket"></i></a>';
            
            // Botón Email
            $emailCliente = isset($row["cliente_email"]) ? $row["cliente_email"] : "";
            $html .= '<a class="btn-accion btnMailComprobante" title="Enviar por email" codigoVenta="'.$row["codigo"].'" mailCliente="'.$emailCliente.'"><i class="fa fa-envelope"></i></a>';
            
            // Botón Eliminar (solo administradores)
            if (isset($_SESSION["perfil"]) && $_SESSION["perfil"] == "Administrador") {
                if ($facturada) {
                    $html .= '<a class="btn-accion disabled" title="No se puede eliminar venta facturada" style="cursor: not-allowed; opacity: 0.4;"><i class="fa fa-times"></i></a>';
                } else {
                    $html .= '<a class="btn-accion btn-danger btnEliminarVenta" title="Eliminar venta" style="cursor: pointer;" idVenta="'.$d.'"><i class="fa fa-times"></i></a>';
                }
            }
            
            $html .= '</div>';
            return $html;
        }
    ),
    
    // Columnas ocultas para uso interno
    array( 'db' => 'id', 'dt' => 10 ), // Para búsqueda
    array( 'db' => 'id_cliente', 'dt' => 11 ), // Para uso interno
    array( 'db' => 'nro_cbte', 'dt' => 12 ), // Para uso interno
    array( 'db' => 'observaciones_vta', 'dt' => 13 ), // Para uso interno
);

require( '../extensiones/ssp.class.php' );

// Usar SSP::complex - el WHERE ya está en la subconsulta, no necesitamos whereAll adicional
echo json_encode(
    SSP::complex( $_GET, $sql_details, $table, $primaryKey, $columns, null, null )
);
