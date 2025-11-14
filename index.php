<?php

require_once "controladores/plantilla.controlador.php";
require_once "controladores/usuarios.controlador.php";
require_once "controladores/categorias.controlador.php";
require_once "controladores/productos.controlador.php";
require_once "controladores/proveedores.controlador.php";
require_once "controladores/proveedores_cta_cte.controlador.php";
require_once "controladores/clientes.controlador.php";
require_once "controladores/clientes_cta_cte.controlador.php";
require_once "controladores/ventas.controlador.php";
require_once "controladores/cajas.controlador.php";
require_once "controladores/caja-cierres.controlador.php";
require_once "controladores/compras.controlador.php";
require_once "controladores/empresa.controlador.php";
require_once "controladores/cotizacion.controlador.php";
require_once "controladores/presupuestos.controlador.php";
require_once "controladores/pedidos.controlador.php";

require_once "controladores/facturacion/wsaa.class.php";
require_once "controladores/facturacion/wsaa_padron.class.php";
require_once "controladores/facturacion/wsfe.class.php";
require_once "controladores/facturacion/padron.class.php";

require_once "modelos/usuarios.modelo.php";
require_once "modelos/categorias.modelo.php";
require_once "modelos/productos.modelo.php";
require_once "modelos/proveedores.modelo.php";
require_once "modelos/proveedores_cta_cte.modelo.php";
require_once "modelos/clientes.modelo.php";
require_once "modelos/clientes_cta_cte.modelo.php";
require_once "modelos/ventas.modelo.php";
require_once "modelos/cajas.modelo.php";
require_once "modelos/caja-cierres.modelo.php";
require_once "modelos/compras.modelo.php";
require_once "modelos/empresa.modelo.php";
require_once "modelos/presupuestos.modelo.php";
require_once "modelos/pedidos.modelo.php";

require_once "parametros.php";

//SISTEMA COBRO
require_once "controladores/sistema_cobro.controlador.php";
require_once "modelos/sistema_cobro.modelo.php";

require_once "extensiones/vendor/autoload.php";

$plantilla = new ControladorPlantilla();
$plantilla -> ctrPlantilla();