<?php
$paginaActualMenu = (isset($_GET["ruta"]) ? $_GET["ruta"] : 'inicio');
function _menuPuedeVer($ruta) {
	if (!isset($_SESSION["permisos_pantallas"]) || !is_array($_SESSION["permisos_pantallas"])) return true;
	return in_array($ruta, $_SESSION["permisos_pantallas"], true);
}
$verEmpresa = ($_SESSION["perfil"] == "Administrador") || _menuPuedeVer('empresa') || _menuPuedeVer('usuarios') || _menuPuedeVer('listas-precio') || _menuPuedeVer('balanzas-formatos') || _menuPuedeVer('permisos-rol') || _menuPuedeVer('medios-pago');
$verProductos = _menuPuedeVer('productos') || _menuPuedeVer('categorias') || _menuPuedeVer('combos') || _menuPuedeVer('impresion-precios') || _menuPuedeVer('productos-importar-excel2');
$verMov = _menuPuedeVer('pedidos-generar-movimiento') || _menuPuedeVer('pedidos-nuevos') || _menuPuedeVer('pedidos-validados');
$verCajas = _menuPuedeVer('cajas') || _menuPuedeVer('cajas-cierre');
$verVentas = _menuPuedeVer('ventas') || _menuPuedeVer('presupuestos') || _menuPuedeVer('crear-venta-caja');
$verCompras = _menuPuedeVer('compras') || _menuPuedeVer('crear-compra') || _menuPuedeVer('ingreso');
$verInformes = _menuPuedeVer('productos-stock-bajo') || _menuPuedeVer('productos-stock-medio') || _menuPuedeVer('productos-stock-valorizado') || _menuPuedeVer('ventas-rentabilidad') || _menuPuedeVer('ventas-categoria-proveedor-informe') || _menuPuedeVer('ventas-productos') || _menuPuedeVer('proveedores-saldo') || _menuPuedeVer('proveedores-cuenta-saldos') || _menuPuedeVer('clientes-cuenta-saldos') || _menuPuedeVer('informe-dashboard-ejecutivo') || _menuPuedeVer('informe-gestion-pedidos');
$verIntegraciones = _menuPuedeVer('integraciones') || _menuPuedeVer('chat');
?>
<aside class="main-sidebar">
	<section class="sidebar">
		<ul class="sidebar-menu" data-widget="tree">
		<?php
		if ($_SESSION["perfil"] == "Administrador") {
			if (_menuPuedeVer('inicio')) { ?>
			<li class="<?php echo ($paginaActualMenu == 'inicio') ? 'active' : ''; ?>"><a href="inicio"><i class="fa fa-home"></i><span>Inicio</span></a></li>
			<?php }
			if ($verEmpresa) { ?>
			<li class="treeview <?php echo (in_array($paginaActualMenu, ['empresa', 'usuarios', 'listas-precio', 'medios-pago', 'balanzas-formatos', 'permisos-rol'])) ? 'active' : '' ?>">
				<a><i class="fa fa-building-o"></i><span>Empresa</span><span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span></a>
				<ul class="treeview-menu">
					<?php if (_menuPuedeVer('empresa')) { ?><li><a href="empresa"><i class="fa fa-circle-o"></i><span>Datos Empresa</span></a></li><?php } ?>
					<?php if (_menuPuedeVer('usuarios')) { ?><li><a href="usuarios"><i class="fa fa-circle-o"></i><span>Usuarios</span></a></li><?php } ?>
					<?php if (_menuPuedeVer('listas-precio')) { ?><li><a href="listas-precio"><i class="fa fa-circle-o"></i><span>Listas de Precio</span></a></li><?php } ?>
					<?php if (_menuPuedeVer('balanzas-formatos')) { ?><li><a href="balanzas-formatos"><i class="fa fa-circle-o"></i><span>Formatos de Balanza</span></a></li><?php } ?>
					<?php if ($_SESSION["perfil"] == "Administrador" || _menuPuedeVer('permisos-rol')) { ?><li><a href="permisos-rol"><i class="fa fa-circle-o"></i><span>Permisos por Rol</span></a></li><?php } ?>
					<?php if (_menuPuedeVer('medios-pago')) { ?><li><a href="medios-pago"><i class="fa fa-circle-o"></i><span>Cargar Medios de Pago</span></a></li><?php } ?>
				</ul>
			</li>
			<?php } ?>

			<li class="treeview <?php echo (in_array($paginaActualMenu, ['productos', 'categorias', 'combos', 'impresion-precios', 'productos-importar-excel2'])) ? 'active' : '' ?>">
				<a><i class="fa fa-product-hunt"></i><span>Productos</span><span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span></a>
				<ul class="treeview-menu">
					<li><a href="productos"><i class="fa fa-circle-o"></i><span>Administrar Productos</span></a></li>
					<li><a href="categorias"><i class="fa fa-circle-o"></i><span>Categorias</span></a></li>
					<li><a href="combos"><i class="fa fa-circle-o"></i><span>Combos</span></a></li>
					<li><a href="impresion-precios"><i class="fa fa-circle-o"></i><span>Imprimir Precios</span></a></li>
					<li><a href="productos-importar-excel2"><i class="fa fa-circle-o"></i><span>Importar excel</span></a></li>
				</ul>
			</li>

			<li class="treeview <?php echo (in_array($paginaActualMenu, ['pedidos-generar-movimiento', 'pedidos-nuevos', 'pedidos-validados'])) ? 'active' : '' ?>">
				<a><i class="fa fa-exchange"></i><span>Mov. De Productos</span><span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span></a>
				<ul class="treeview-menu">
					<li><a href="pedidos-generar-movimiento"><i class="fa fa-circle-o"></i><span>Generar Movimiento</span></a></li>
					<li><a href="pedidos-nuevos"><i class="fa fa-circle-o"></i><span>Validar Movimiento</span></a></li>
					<li><a href="pedidos-validados"><i class="fa fa-circle-o"></i><span>Movimientos Validados</span></a></li>
				</ul>
			</li>

			<li class="treeview <?php echo (in_array($paginaActualMenu, ['cajas', 'cajas-cierre'])) ? 'active' : '' ?>">
				<a><i class="fa fa-usd"></i><span>Cajas</span><span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span></a>
				<ul class="treeview-menu">
					<li><a href="cajas"><i class="fa fa-circle-o"></i><span>Administrar Caja</span></a></li>
					<li><a href="cajas-cierre"><i class="fa fa-circle-o"></i><span>Cierres de caja</span></a></li>
				</ul>
			</li>

			<li class="treeview <?php echo (in_array($paginaActualMenu, ['ventas', 'presupuestos', 'crear-venta-caja'])) ? 'active' : '' ?>">
				<a><i class="fa fa-line-chart"></i><span>Ventas</span>	<span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span></a>
				<ul class="treeview-menu">
					<li><a href="ventas"><i class="fa fa-circle-o"></i><span>Adm. ventas</span></a></li>
					<li><a href="presupuestos"><i class="fa fa-circle-o"></i><span>Adm. presupuestos</span></a></li>
					<li><a href="crear-venta-caja"><i class="fa fa-circle-o"></i><span>Crear venta</span></a></li>
				</ul>
			</li>

			<li class="<?php echo (in_array($paginaActualMenu, ['clientes', 'clientes_cuenta'])) ? 'active' : ''; ?>"><a href="clientes"><i class="fa fa-users"></i><span>Clientes</span></a></li>

			<li class="treeview <?php echo (in_array($paginaActualMenu, ['compras', 'crear-compra', 'ingreso'])) ? 'active' : ''; ?>">
				<a><i class="fa fa-shopping-cart"></i><span>Compras</span><span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span></a>
				<ul class="treeview-menu">	
					<li><a href="compras"><i class="fa fa-circle-o"></i><span>Adm. Compras</span></a></li>				
					<li><a href="crear-compra"><i class="fa fa-circle-o"></i><span>Crear Compra</span></a></li>
					<li><a href="ingreso"><i class="fa fa-circle-o"></i><span>Ingreso Mercaderia</span></a></li>
				</ul>
			</li>

			<li class="<?php echo (in_array($paginaActualMenu, ['proveedores', 'proveedores_cuenta'])) ? 'active' : ''; ?>"><a href="proveedores"><i class="fa fa-address-book-o" aria-hidden="true"></i><span>Proveedores</span></a></li>

			<?php if ($verInformes) { ?>
			<li class="treeview <?php echo (in_array($paginaActualMenu, ['productos-stock-bajo', 'productos-stock-medio', 'productos-stock-valorizado', 'ventas-rentabilidad', 'ventas-categoria-proveedor-informe', 'ventas-productos', 'proveedores-saldo', 'proveedores-cuenta-saldos', 'clientes-cuenta-saldos', 'informe-dashboard-ejecutivo', 'informe-gestion-pedidos'])) ? 'active' : ''; ?>">
				<a><i class="fa fa-area-chart"></i><span>Informes</span><span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span></a>
				<ul class="treeview-menu">
					<li class="header" style="padding: 5px 10px; color: #999; font-size: 11px;">EJECUTIVO</li>
					<?php if (_menuPuedeVer('informe-dashboard-ejecutivo')) { ?><li><a href="informe-dashboard-ejecutivo"><i class="fa fa-circle-o"></i><span>Dashboard ejecutivo</span></a></li><?php } ?>
					<?php if (_menuPuedeVer('informe-gestion-pedidos')) { ?><li><a href="informe-gestion-pedidos"><i class="fa fa-circle-o"></i><span>Gestión de pedidos</span></a></li><?php } ?>
					<li class="header" style="padding: 5px 10px; color: #999; font-size: 11px;">VENTAS</li>
					<?php if (_menuPuedeVer('ventas-productos')) { ?><li><a href="ventas-productos"><i class="fa fa-circle-o"></i><span>Productos vendidos</span></a></li><?php } ?>
					<?php if (_menuPuedeVer('ventas-rentabilidad')) { ?><li><a href="ventas-rentabilidad"><i class="fa fa-circle-o"></i><span>Rentabilidad</span></a></li><?php } ?>
					<?php if (_menuPuedeVer('ventas-categoria-proveedor-informe')) { ?><li><a href="ventas-categoria-proveedor-informe"><i class="fa fa-circle-o"></i><span>Ventas por categoría/proveedor</span></a></li><?php } ?>
					<li class="header" style="padding: 5px 10px; color: #999; font-size: 11px;">PRODUCTOS Y STOCK</li>
					<?php if (_menuPuedeVer('productos-stock-bajo')) { ?><li><a href="productos-stock-bajo"><i class="fa fa-circle-o"></i><span>Stock bajo</span></a></li><?php } ?>
					<?php if (_menuPuedeVer('productos-stock-medio')) { ?><li><a href="productos-stock-medio"><i class="fa fa-circle-o"></i><span>Stock medio</span></a></li><?php } ?>
					<?php if (_menuPuedeVer('productos-stock-valorizado')) { ?><li><a href="productos-stock-valorizado"><i class="fa fa-circle-o"></i><span>Stock valorizado</span></a></li><?php } ?>
					<li class="header" style="padding: 5px 10px; color: #999; font-size: 11px;">PROVEEDORES Y CLIENTES</li>
					<?php if (_menuPuedeVer('proveedores-saldo')) { ?><li><a href="proveedores-saldo"><i class="fa fa-circle-o"></i><span>Saldos por proveedor</span></a></li><?php } ?>
					<?php if (_menuPuedeVer('proveedores-cuenta-saldos')) { ?><li><a href="proveedores-cuenta-saldos"><i class="fa fa-circle-o"></i><span>Ctas. ctes. proveedores</span></a></li><?php } ?>
					<?php if (_menuPuedeVer('clientes-cuenta-saldos')) { ?><li><a href="clientes-cuenta-saldos"><i class="fa fa-circle-o"></i><span>Ctas. ctes. clientes</span></a></li><?php } ?>
				</ul>
			</li>
			<?php } ?>

			<li class="treeview <?php echo (in_array($paginaActualMenu, ['integraciones', 'chat'])) ? 'active' : ''; ?>">
				<a><i class="fa fa-plug"></i><span>Integraciones</span><span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span></a>
				<ul class="treeview-menu">
					<li><a href="integraciones"><i class="fa fa-circle-o"></i><span>Gestionar Integraciones</span></a></li>
					<li><a href="chat"><i class="fa fa-comments"></i><span>Asistente Virtual</span></a></li>
				</ul>
			</li>

		<?php } 
		
		if($_SESSION["perfil"] == "Vendedor"){ 	?>
			<li class="<?php echo ($paginaActualMenu == 'inicio') ? 'active' : ''; ?>"><a href="inicio"><i class="fa fa-home"></i><span>Inicio</span></a></li>
			<li class="<?php echo ($paginaActualMenu == 'productos') ? 'active' : ''; ?>"><a href="productos"><i class="fa fa-product-hunt"></i><span>Administrar Productos</span></a></li>
			<li class="<?php echo ($paginaActualMenu == 'impresion-precios') ? 'active' : ''; ?>"><a href="impresion-precios"><i class="fa fa-print"></i><span>Imprimir Precios</span></a></li>
			<li class="<?php echo ($paginaActualMenu == 'cajas-cajero') ? 'active' : ''; ?>"><a href="cajas-cajero"><i class="fa fa-dollar"></i><span>Caja</span></a></li>
			<li class="<?php echo ($paginaActualMenu == 'ventas') ? 'active' : ''; ?>"><a href="ventas"><i class="fa fa-line-chart"></i><span>Adm. ventas</span></a></li>
			<li class="<?php echo ($paginaActualMenu == 'crear-venta-caja') ? 'active' : ''; ?>"><a href="crear-venta-caja"><i class="fa fa-plus"></i><span>Crear venta</span></a></li>
			<li class="<?php echo ($paginaActualMenu == 'clientes') ? 'active' : ''; ?>"><a href="clientes"><i class="fa fa-users"></i><span>Clientes</span></a></li>
			<li class="<?php echo ($paginaActualMenu == 'chat') ? 'active' : ''; ?>"><a href="chat"><i class="fa fa-comments"></i><span>Asistente Virtual</span></a></li>

		<?php } ?>

		</ul>
	</section>
</aside>