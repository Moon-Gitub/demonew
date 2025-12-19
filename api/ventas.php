<?php
/**
 * API ENDPOINT - VENTAS
 * GET: Listar ventas (historial)
 * POST: Crear venta (desde sistema offline)
 */

// Cargar autoload para Dotenv
require_once "../extensiones/vendor/autoload.php";

// Cargar .env si existe
if (file_exists(__DIR__ . '/../.env') && class_exists('Dotenv\Dotenv')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
    $dotenv->safeLoad();
}

// Verificación básica: requerir ID de cliente Moon como parámetro para GET
$id_cliente = isset($_GET['id_cliente']) ? intval($_GET['id_cliente']) : null;

// Si no se proporciona ID, intentar verificar sesión (para compatibilidad)
if (!$id_cliente && $_SERVER['REQUEST_METHOD'] === 'GET') {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (!isset($_SESSION["iniciarSesion"]) || $_SESSION["iniciarSesion"] != "ok") {
        http_response_code(401);
        echo json_encode(['error' => 'Se requiere id_cliente como parámetro o sesión activa']);
        exit;
    }
}

// Para POST siempre requiere sesión
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once "../seguridad.ajax.php";
    SeguridadAjax::inicializar();
}

require_once "../controladores/ventas.controlador.php";
require_once "../modelos/ventas.modelo.php";

header('Content-Type: application/json; charset=utf-8');

try {
    if($_SERVER['REQUEST_METHOD'] === 'GET') {
        // GET: Listar ventas (historial)
        $fecha_desde = isset($_GET['desde']) ? $_GET['desde'] : null;
        
        if($fecha_desde) {
            // Obtener ventas desde fecha
            $item = "fecha";
            $valor = $fecha_desde;
            $orden = "id";
            
            $ventas = ControladorVentas::ctrMostrarVentas($item, $valor, $orden);
        } else {
            // Obtener todas las ventas
            $ventas = ControladorVentas::ctrMostrarVentas(null, null, "id");
        }
        
        $resultado = [];
        if($ventas && is_array($ventas)) {
            foreach($ventas as $venta) {
                $resultado[] = [
                    'id' => $venta['id'],
                    'fecha' => $venta['fecha'],
                    'cliente' => $venta['id_cliente'] ?? 'Consumidor Final',
                    'total' => floatval($venta['total']),
                    'metodo_pago' => $venta['metodo_pago'] ?? 'Efectivo',
                    'sucursal' => 'Local'
                ];
            }
        }
        
        echo json_encode($resultado);
        
    } elseif($_SERVER['REQUEST_METHOD'] === 'POST') {
        // POST: Crear venta desde sistema offline
        $datos = json_decode(file_get_contents('php://input'), true);
        
        if(!$datos) {
            http_response_code(400);
            echo json_encode(['error' => 'Datos inválidos']);
            exit;
        }
        
        // Preparar datos para el controlador (formato que espera ctrCrearVentaCaja)
        $postVentaCaja = array();
        $postVentaCaja['tokenIdTablaVentas'] = uniqid('offline_', true);
        $postVentaCaja['fechaActual'] = $datos['fecha'] ?? date('Y-m-d H:i:s');
        $postVentaCaja['idVendedor'] = $_SESSION['id'] ?? 1;
        $postVentaCaja['sucursalVendedor'] = $datos['sucursal'] ?? 'Local';
        $postVentaCaja['nombreVendedor'] = $_SESSION['nombre'] ?? 'Sistema';
        $postVentaCaja['seleccionarCliente'] = $datos['cliente'] ?? '1';
        $postVentaCaja['nuevaVentaCaja'] = '1';
        $postVentaCaja['listaProductosCaja'] = json_encode($datos['productos']);
        $postVentaCaja['listaDescuentoCaja'] = '';
        $postVentaCaja['nuevoTotalVentaCaja'] = $datos['total'];
        $postVentaCaja['listaMetodoPagoCaja'] = $datos['metodo_pago'] ?? 'Efectivo';
        $postVentaCaja['nuevoPrecioImpuestoCaja'] = '0';
        $postVentaCaja['nuevoVtaCajaIva2'] = '0';
        $postVentaCaja['nuevoVtaCajaIva5'] = '0';
        $postVentaCaja['nuevoVtaCajaIva10'] = '0';
        $postVentaCaja['nuevoVtaCajaIva21'] = '0';
        $postVentaCaja['nuevoVtaCajaIva27'] = '0';
        $postVentaCaja['nuevoVtaCajaBaseImp0'] = '0';
        $postVentaCaja['nuevoVtaCajaBaseImp2'] = '0';
        $postVentaCaja['nuevoVtaCajaBaseImp5'] = '0';
        $postVentaCaja['nuevoVtaCajaBaseImp10'] = '0';
        $postVentaCaja['nuevoVtaCajaBaseImp21'] = '0';
        $postVentaCaja['nuevoVtaCajaBaseImp27'] = '0';
        $postVentaCaja['nuevoPrecioNetoCaja'] = $datos['total'];
        $postVentaCaja['nuevoInteresPorcentajeCaja'] = '0';
        $postVentaCaja['nuevoDescuentoPorcentajeCaja'] = '0';
        $postVentaCaja['nuevotipoCbte'] = '0';
        $postVentaCaja['nuevaPtoVta'] = '0';
        $postVentaCaja['nuevaConcepto'] = '';
        $postVentaCaja['nuevaFecDesde'] = '';
        $postVentaCaja['nuevaFecHasta'] = '';
        $postVentaCaja['nuevaFecVto'] = '';
        $postVentaCaja['nuevotipoCbteAsociado'] = '';
        $postVentaCaja['nuevaPtoVtaAsociado'] = '';
        $postVentaCaja['nuevaNroCbteAsociado'] = '';
        
        // Preparar método de pago
        $metodoPago = $datos['metodo_pago'] ?? 'Efectivo';
        $postVentaCaja['mxMediosPagos'] = json_encode([[
            'tipo' => $metodoPago,
            'entrega' => $datos['total']
        ]]);
        
        // Llamar al controlador de ventas
        require_once "../controladores/cajas.controlador.php";
        require_once "../modelos/cajas.modelo.php";
        require_once "../controladores/clientes_cta_cte.controlador.php";
        require_once "../modelos/clientes_cta_cte.modelo.php";
        require_once "../controladores/productos.controlador.php";
        require_once "../modelos/productos.modelo.php";
        require_once "../controladores/clientes.controlador.php";
        require_once "../modelos/clientes.modelo.php";
        require_once "../controladores/empresa.controlador.php";
        require_once "../modelos/empresa.modelo.php";
        
        $respuesta = ControladorVentas::ctrCrearVentaCaja($postVentaCaja);
        
        if($respuesta && isset($respuesta['estado']) && $respuesta['estado'] == 'ok') {
            // Obtener el último ID de venta creada
            require_once "../modelos/ventas.modelo.php";
            $ultimoId = ModeloVentas::mdlUltimoId('ventas');
            $ultimoCodigo = ModeloVentas::mdlMostrarUltimoCodigo('ventas');
            
            $codigo = isset($respuesta['codigoVta']) ? $respuesta['codigoVta'] : ($ultimoCodigo['ultimo'] ?? null);
            
            echo json_encode([
                'id' => $ultimoId['ultimo'] ?? null,
                'codigo' => $codigo,
                'success' => true
            ]);
        } else {
            http_response_code(500);
            $error = is_array($respuesta) ? ($respuesta['modeloVentas'] ?? 'Error desconocido') : 'Error al crear la venta';
            echo json_encode(['error' => $error]);
        }
    } else {
        http_response_code(405);
        echo json_encode(['error' => 'Método no permitido']);
    }
    
} catch(Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
