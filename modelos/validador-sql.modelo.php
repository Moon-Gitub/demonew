<?php

/**
 * MODELO VALIDADOR SQL
 * 
 * Proporciona validación de tablas y columnas mediante whitelists
 * para prevenir inyección SQL en consultas dinámicas
 */

class ModeloValidadorSQL {
    
    // Tablas permitidas en el sistema
    const TABLAS_PERMITIDAS = [
        'usuarios',
        'productos',
        'categorias',
        'clientes',
        'proveedores',
        'ventas',
        'compras',
        'cajas',
        'caja_cierres',
        'empresa',
        'pedidos',
        'presupuestos',
        'clientes_cuenta_corriente',
        'proveedores_cuenta_corriente',
        'ventas_factura',
        'productos_historial',
        'compras_productos',
        'ventas_productos'
    ];
    
    // Columnas comunes permitidas por tabla
    const COLUMNAS_PERMITIDAS = [
        'usuarios' => ['id', 'nombre', 'usuario', 'password', 'perfil', 'sucursal', 'puntos_venta', 'listas_precio', 'foto', 'estado', 'ultimo_login', 'fecha'],
        'productos' => ['id', 'id_categoria', 'codigo', 'id_proveedor', 'descripcion', 'imagen', 'stock', 'deposito', 'stock_medio', 'stock_bajo', 'precio_compra', 'precio_compra_dolar', 'margen_ganancia', 'precio_venta_neto', 'tipo_iva', 'precio_venta', 'precio_venta_mayorista', 'ventas', 'fecha', 'nombre_usuario', 'cambio_desde'],
        'categorias' => ['id', 'categoria', 'fecha'],
        'clientes' => ['id', 'nombre', 'tipo_documento', 'documento', 'condicion_iva', 'email', 'telefono', 'direccion', 'fecha_nacimiento', 'compras', 'ultima_compra', 'fecha', 'observaciones'],
        'ventas' => ['id', 'uuid', 'codigo', 'cbte_tipo', 'id_cliente', 'id_vendedor', 'productos', 'neto', 'neto_gravado', 'impuesto', 'total', 'metodo_pago', 'estado', 'observaciones', 'fecha', 'pto_vta', 'pedido_afip', 'respuesta_afip'],
        'compras' => ['id', 'codigo', 'id_proveedor', 'usuarioPedido', 'usuarioConfirma', 'fechaEntrega', 'fechaPago', 'productos', 'estado', 'fecha', 'total', 'descuento', 'totalNeto', 'tipo', 'iva'],
        'proveedores' => ['id', 'nombre', 'inicio_actividades', 'tipo_documento', 'cuit', 'ingresos_brutos', 'localidad', 'telefono', 'direccion', 'email', 'observaciones'],
        'cajas' => ['id', 'fecha_apertura', 'fecha_cierre', 'monto_inicial', 'monto_final', 'estado'],
        'empresa' => ['id', 'razon_social', 'titular', 'cuit', 'domicilio', 'localidad', 'codigo_postal', 'mail', 'telefono'],
        'pedidos' => ['id', 'codigo', 'id_cliente', 'productos', 'total', 'estado', 'fecha'],
        'presupuestos' => ['id', 'codigo', 'id_cliente', 'productos', 'total', 'estado', 'fecha']
    ];
    
    /**
     * Validar nombre de tabla contra whitelist
     * 
     * @param string $tabla Nombre de la tabla
     * @return string Tabla validada
     * @throws Exception Si la tabla no está permitida
     */
    static public function validarTabla($tabla) {
        // Limpiar espacios y convertir a minúsculas
        $tabla = trim(strtolower($tabla));
        
        if (!in_array($tabla, self::TABLAS_PERMITIDAS)) {
            error_log("SECURITY: Intento de acceso a tabla no permitida: $tabla");
            error_log("SECURITY: Stack trace: " . json_encode(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 5)));
            throw new Exception("Tabla no permitida: $tabla");
        }
        
        return $tabla;
    }
    
    /**
     * Validar nombre de columna contra whitelist o formato
     * 
     * @param string $tabla Nombre de la tabla
     * @param string $columna Nombre de la columna
     * @return string Columna validada
     * @throws Exception Si la columna no está permitida
     */
    static public function validarColumna($tabla, $columna) {
        // Limpiar espacios
        $columna = trim($columna);
        
        // Si no hay restricción específica para la tabla, validar formato básico
        if (!isset(self::COLUMNAS_PERMITIDAS[$tabla])) {
            // Solo permitir caracteres alfanuméricos y guión bajo
            if (!preg_match('/^[a-zA-Z0-9_]+$/', $columna)) {
                error_log("SECURITY: Intento de usar columna con caracteres inválidos: $columna en tabla $tabla");
                throw new Exception("Columna inválida: $columna");
            }
            return $columna;
        }
        
        // Verificar contra whitelist
        if (!in_array($columna, self::COLUMNAS_PERMITIDAS[$tabla])) {
            error_log("SECURITY: Intento de acceso a columna no permitida: $columna en tabla $tabla");
            error_log("SECURITY: Stack trace: " . json_encode(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 5)));
            throw new Exception("Columna no permitida: $columna en tabla $tabla");
        }
        
        return $columna;
    }
    
    /**
     * Validar orden (ASC/DESC)
     * 
     * @param string $orden Orden a validar
     * @return string Orden validado (ASC o DESC)
     */
    static public function validarOrden($orden) {
        $orden = strtoupper(trim($orden));
        if (!in_array($orden, ['ASC', 'DESC'])) {
            return 'ASC';
        }
        return $orden;
    }
    
    /**
     * Sanitizar valor para LIKE query
     * Escapa caracteres especiales de LIKE (% y _)
     * 
     * @param string $valor Valor a sanitizar
     * @return string Valor sanitizado
     */
    static public function sanitizarLike($valor) {
        // Escapar caracteres especiales de LIKE
        $valor = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $valor);
        return $valor;
    }
    
    /**
     * Validar múltiples columnas
     * 
     * @param string $tabla Nombre de la tabla
     * @param array $columnas Array de nombres de columnas
     * @return array Array de columnas validadas
     */
    static public function validarColumnas($tabla, $columnas) {
        $validadas = [];
        foreach ($columnas as $columna) {
            $validadas[] = self::validarColumna($tabla, $columna);
        }
        return $validadas;
    }
}

