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

// Para POST: verificar sesión o permitir desde sistema offline
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verificar si viene del sistema offline (tiene id_cliente en datos)
    $raw_input = file_get_contents('php://input');
    $datos_temp = json_decode($raw_input, true);
    
    // Si no hay sesión pero viene del sistema offline, permitir
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (!isset($_SESSION["iniciarSesion"]) || $_SESSION["iniciarSesion"] != "ok") {
        // Si no hay sesión, verificar si es del sistema offline
        if (isset($datos_temp['creado_local']) && $datos_temp['creado_local'] === true) {
            // Permitir desde sistema offline, usar valores por defecto
            $_SESSION['id'] = 1; // Usuario por defecto
            $_SESSION['nombre'] = 'Sistema Offline';
            error_log("Permitiendo venta desde sistema offline sin sesión activa");
        } else {
            // Requerir sesión para otros casos
            require_once "../seguridad.ajax.php";
            SeguridadAjax::inicializar();
        }
    } else {
        // Hay sesión activa, todo bien
        error_log("Sesión activa encontrada: usuario " . ($_SESSION['id'] ?? 'N/A'));
    }
}

require_once "../controladores/ventas.controlador.php";
require_once "../modelos/ventas.modelo.php";

header('Content-Type: application/json; charset=utf-8');

try {
    if($_SERVER['REQUEST_METHOD'] === 'GET') {
        // GET: Listar ventas (historial)
        $fecha_desde = isset($_GET['desde']) ? $_GET['desde'] : null;
        
        require_once "../modelos/conexion.php";
        
        if($fecha_desde) {
            // Convertir fecha ISO a formato MySQL si es necesario
            if(strpos($fecha_desde, 'T') !== false) {
                $fecha_desde = str_replace('T', ' ', $fecha_desde);
                $fecha_desde = substr($fecha_desde, 0, 19); // Quitar timezone si existe
            }
            
            // Obtener ventas desde fecha usando consulta directa con >=
            $stmt = Conexion::conectar()->prepare("SELECT * FROM ventas WHERE fecha >= :fecha_desde ORDER BY id DESC");
            $stmt->bindParam(":fecha_desde", $fecha_desde, PDO::PARAM_STR);
            $stmt->execute();
            $ventas = $stmt->fetchAll();
        } else {
            // Obtener todas las ventas
            $ventas = ControladorVentas::ctrMostrarVentas(null, null, "id");
        }
        
        $resultado = [];
        if($ventas && is_array($ventas)) {
            foreach($ventas as $venta) {
                // Obtener nombre del cliente si existe
                $cliente_nombre = 'Consumidor Final';
                if(isset($venta['id_cliente']) && $venta['id_cliente'] != 1) {
                    require_once "../modelos/clientes.modelo.php";
                    $cliente = ModeloClientes::mdlMostrarClientesPorId($venta['id_cliente']);
                    if($cliente && isset($cliente['nombre'])) {
                        $cliente_nombre = $cliente['nombre'];
                    }
                }
                
                // Parsear método de pago si es JSON
                $metodo_pago = 'Efectivo';
                if(isset($venta['metodo_pago'])) {
                    $metodo_pago_raw = $venta['metodo_pago'];
                    if(is_string($metodo_pago_raw) && (strpos($metodo_pago_raw, '[') === 0 || strpos($metodo_pago_raw, '{') === 0)) {
                        $metodo_pago_array = json_decode($metodo_pago_raw, true);
                        if(is_array($metodo_pago_array) && isset($metodo_pago_array[0]['tipo'])) {
                            $metodo_pago = $metodo_pago_array[0]['tipo'];
                        }
                    } else {
                        $metodo_pago = $metodo_pago_raw;
                    }
                }
                
                $resultado[] = [
                    'id' => intval($venta['id']),
                    'fecha' => $venta['fecha'],
                    'cliente' => $cliente_nombre,
                    'total' => floatval($venta['total']),
                    'metodo_pago' => $metodo_pago,
                    'sucursal' => $venta['sucursal'] ?? 'Local'
                ];
            }
        }
        
        echo json_encode($resultado);
        
    } elseif($_SERVER['REQUEST_METHOD'] === 'POST') {
        // POST: Crear venta desde sistema offline
        error_log("=== INICIO CREAR VENTA OFFLINE ===");
        
        $raw_input = file_get_contents('php://input');
        error_log("Raw input recibido: " . substr($raw_input, 0, 500));
        
        $datos = json_decode($raw_input, true);
        
        if(!$datos) {
            $error = json_last_error_msg();
            error_log("Error decodificando JSON: " . $error);
            http_response_code(400);
            echo json_encode(['error' => 'Datos inválidos: ' . $error]);
            exit;
        }
        
        error_log("Datos decodificados: " . print_r($datos, true));
        
        // Validar que hay productos
        if(empty($datos['productos']) || !is_array($datos['productos'])) {
            error_log("Error: No hay productos o no es un array");
            http_response_code(400);
            echo json_encode(['error' => 'No hay productos en la venta']);
            exit;
        }
        
        // Preparar datos para el controlador (formato que espera ctrCrearVentaCaja)
        $postVentaCaja = array();
        $postVentaCaja['tokenIdTablaVentas'] = uniqid('offline_', true);
        
        // Formatear fecha correctamente
        $fecha = $datos['fecha'] ?? date('Y-m-d H:i:s');
        if(strpos($fecha, 'T') !== false) {
            // Convertir formato ISO a formato MySQL
            $fecha = str_replace('T', ' ', $fecha);
            $fecha = substr($fecha, 0, 19); // Quitar timezone si existe
        }
        $postVentaCaja['fechaActual'] = $fecha;
        
        $postVentaCaja['idVendedor'] = $_SESSION['id'] ?? 1;
        // Mapear 'Local' a 'stock' ya que 'Local' no es una columna válida en productos
        $sucursal = $datos['sucursal'] ?? 'stock';
        if ($sucursal === 'Local') {
            $sucursal = 'stock';
        }
        $postVentaCaja['sucursalVendedor'] = $sucursal;
        $postVentaCaja['nombreVendedor'] = $_SESSION['nombre'] ?? 'Sistema';
        // El cliente puede venir como ID (número) o como string "ID-Nombre"
        $cliente_id = $datos['cliente'] ?? '1';
        if (is_string($cliente_id) && strpos($cliente_id, '-') !== false) {
            // Si viene como "ID-Nombre", extraer solo el ID
            $cliente_id = intval(explode('-', $cliente_id)[0]);
        } else {
            $cliente_id = intval($cliente_id);
        }
        $postVentaCaja['seleccionarCliente'] = $cliente_id;
        $postVentaCaja['nuevaVentaCaja'] = '1';
        
        // Asegurar que los productos tengan el formato correcto
        $productos_formateados = [];
        foreach($datos['productos'] as $prod) {
            $productos_formateados[] = [
                'id' => intval($prod['id'] ?? $prod['id_producto'] ?? 0),
                'descripcion' => $prod['descripcion'] ?? '',
                'cantidad' => floatval($prod['cantidad'] ?? 1),
                'categoria' => $prod['categoria'] ?? '',
                'stock' => floatval($prod['stock'] ?? 0),
                'precio_compra' => floatval($prod['precio_compra'] ?? 0),
                'precio' => floatval($prod['precio'] ?? $prod['precio_venta'] ?? 0),
                'total' => floatval($prod['total'] ?? $prod['subtotal'] ?? 0)
            ];
        }
        
        $postVentaCaja['listaProductosCaja'] = json_encode($productos_formateados);
        error_log("Lista productos formateada: " . $postVentaCaja['listaProductosCaja']);
        
        $postVentaCaja['listaDescuentoCaja'] = '';
        $postVentaCaja['nuevoTotalVentaCaja'] = floatval($datos['total']);
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
        
        error_log("Llamando a ctrCrearVentaCaja con datos: " . print_r($postVentaCaja, true));
        
        try {
            $respuesta = ControladorVentas::ctrCrearVentaCaja($postVentaCaja);
            error_log("Respuesta del controlador: " . print_r($respuesta, true));
            
            if($respuesta && isset($respuesta['estado']) && $respuesta['estado'] == 'ok') {
                // Obtener el último ID de venta creada
                require_once "../modelos/ventas.modelo.php";
                $ultimoId = ModeloVentas::mdlUltimoId('ventas');
                $ultimoCodigo = ModeloVentas::mdlMostrarUltimoCodigo('ventas');
                
                $codigo = isset($respuesta['codigoVta']) ? $respuesta['codigoVta'] : ($ultimoCodigo['ultimo'] ?? null);
                
                error_log("✅ Venta creada exitosamente. ID: " . ($ultimoId['ultimo'] ?? 'N/A'));
                
                echo json_encode([
                    'id' => $ultimoId['ultimo'] ?? null,
                    'codigo' => $codigo,
                    'success' => true
                ]);
            } else {
                $error = is_array($respuesta) ? ($respuesta['modeloVentas'] ?? $respuesta['modeloCaja'] ?? 'Error desconocido') : (is_string($respuesta) ? $respuesta : 'Error al crear la venta');
                error_log("❌ Error en respuesta del controlador: " . $error);
                http_response_code(500);
                echo json_encode(['error' => $error, 'respuesta_completa' => $respuesta]);
            }
        } catch(Exception $e) {
            error_log("❌ Excepción al crear venta: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            http_response_code(500);
            echo json_encode(['error' => 'Excepción: ' . $e->getMessage()]);
        }
    } else {
        http_response_code(405);
        echo json_encode(['error' => 'Método no permitido']);
    }
    
} catch(Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
