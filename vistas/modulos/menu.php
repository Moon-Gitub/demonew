
<?php $paginaActualMenu = (isset($_GET["ruta"]) ? $_GET["ruta"] : 'inicio'); ?>

<aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark">
	<div class="sidebar-wrapper">
		<nav class="mt-2">
			<ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" role="navigation" aria-label="Main navigation" data-accordion="false">
			<?php
			if($_SESSION["perfil"] == "Administrador"){
			?>
				<li class="nav-item">
					<a href="inicio" class="nav-link <?php echo ($paginaActualMenu == 'inicio') ? 'active' : ''; ?>">
						<i class="nav-icon bi bi-house"></i>
						<p>Inicio</p>
					</a>
				</li>
					
				<li class="nav-item <?php echo (in_array($paginaActualMenu, ['empresa', 'usuarios'])) ? 'menu-open' : '' ?>">
					<a href="#" class="nav-link <?php echo (in_array($paginaActualMenu, ['empresa', 'usuarios'])) ? 'active' : '' ?>">
						<i class="nav-icon bi bi-building"></i>
						<p>Empresa<i class="nav-arrow bi bi-chevron-right"></i></p>
					</a>
					<ul class="nav nav-treeview">
						<li class="nav-item"><a href="empresa" class="nav-link"><i class="nav-icon bi bi-circle"></i><p>Datos Empresa</p></a></li>
						<li class="nav-item"><a href="usuarios" class="nav-link"><i class="nav-icon bi bi-circle"></i><p>Usuarios</p></a></li>
					</ul>
				</li>

			<li class="nav-item nav-treeview <?php echo (in_array($paginaActualMenu, ['productos', 'categorias', 'impresion-precios', 'productos-importar-excel2'])) ? 'active' : '' ?>">
				<a><i class="bi bi-box-seam"></i><span>Productos</span><span class="float-end-container"><i class="bi bi-chevron-left float-end"></i></span></a>
				<ul class="nav-item nav-nav-treeview">
					<li><a href="productos"><i class="bi bi-circle"></i><span>Administrar Productos</span></a></li>
					<li><a href="categorias"><i class="bi bi-circle"></i><span>Categorias</span></a></li>
					<li><a href="impresion-precios"><i class="bi bi-circle"></i><span>Imprimir Precios</span></a></li>
					<li><a href="productos-importar-excel2"><i class="bi bi-circle"></i><span>Importar excel</span></a></li>
				</ul>
			</li>

			<li class="nav-item nav-treeview <?php echo (in_array($paginaActualMenu, ['pedidos-generar-movimiento', 'pedidos-nuevos', 'pedidos-validados'])) ? 'active' : '' ?>">
				<a><i class="fa fa-exchange"></i><span>Mov. De Productos</span><span class="float-end-container"><i class="bi bi-chevron-left float-end"></i></span></a>
				<ul class="nav-item nav-nav-treeview">
					<li><a href="pedidos-generar-movimiento"><i class="bi bi-circle"></i><span>Generar Movimiento</span></a></li>
					<li><a href="pedidos-nuevos"><i class="bi bi-circle"></i><span>Validar Movimiento</span></a></li>
					<li><a href="pedidos-validados"><i class="bi bi-circle"></i><span>Movimientos Validados</span></a></li>
				</ul>
			</li>

			<li class="nav-item nav-treeview <?php echo (in_array($paginaActualMenu, ['cajas', 'cajas-cierre'])) ? 'active' : '' ?>">
				<a><i class="fa fa-usd"></i><span>Cajas</span><span class="float-end-container"><i class="bi bi-chevron-left float-end"></i></span></a>
				<ul class="nav-item nav-nav-treeview">
					<li><a href="cajas"><i class="bi bi-circle"></i><span>Administrar Caja</span></a></li>
					<li><a href="cajas-cierre"><i class="bi bi-circle"></i><span>Cierres de caja</span></a></li>
				</ul>
			</li>

			<li class="nav-item nav-treeview <?php echo (in_array($paginaActualMenu, ['ventas', 'presupuestos', 'crear-venta-caja', 'ventas-productos', 'ventas-rentabilidad', 'ventas-categoria-proveedor-informe'])) ? 'active' : '' ?>">
				<a><i class="bi bi-graph-up"></i><span>Ventas</span>	<span class="float-end-container"><i class="bi bi-chevron-left float-end"></i></span></a>
				<ul class="nav-item nav-nav-treeview">
					<li><a href="ventas"><i class="bi bi-circle"></i><span>Adm. ventas</span></a></li>
					<li><a href="presupuestos"><i class="bi bi-circle"></i><span>Adm. presupuestos</span></a></li>
					<li><a href="crear-venta-caja"><i class="bi bi-circle"></i><span>Crear venta</span></a></li>
					<li><a href="ventas-productos"><i class="bi bi-circle"></i><span>Productos Vendidos</span></a></li>
					<li><a href="ventas-rentabilidad"><i class="bi bi-circle"></i><span>Informe rentabilidad</span></a></li>
					<li><a href="ventas-categoria-proveedor-informe"><i class="bi bi-circle"></i><span>Informe de ventas</span></a></li>
				</ul>
			</li>

			<li class="<?php echo (in_array($paginaActualMenu, ['clientes', 'clientes_cuenta'])) ? 'active' : ''; ?>"><a href="clientes"><i class="bi bi-persons"></i><span>Clientes</span></a></li>

			<li class="nav-item nav-treeview <?php echo (in_array($paginaActualMenu, ['compras', 'crear-compra', 'ingreso'])) ? 'active' : ''; ?>">
				<a><i class="bi bi-cart"></i><span>Compras</span><span class="float-end-container"><i class="bi bi-chevron-left float-end"></i></span></a>
				<ul class="nav-item nav-nav-treeview">	
					<li><a href="compras"><i class="bi bi-circle"></i><span>Adm. Compras</span></a></li>				
					<li><a href="crear-compra"><i class="bi bi-circle"></i><span>Crear Compra</span></a></li>
					<li><a href="ingreso"><i class="bi bi-circle"></i><span>Ingreso Mercaderia</span></a></li>
				</ul>
			</li>

			<li class="<?php echo (in_array($paginaActualMenu, ['proveedores', 'proveedores_cuenta'])) ? 'active' : ''; ?>"><a href="proveedores"><i class="fa fa-address-book-o" aria-hidden="true"></i><span>Proveedores</span></a></li>

		<?php } 
		
		if($_SESSION["perfil"] == "Vendedor"){ 	?>
			<li class="<?php echo ($paginaActualMenu == 'inicio') ? 'active' : ''; ?>"><a href="inicio"><i class="bi bi-house"></i><span>Inicio</span></a></li>
			<li class="<?php echo ($paginaActualMenu == 'productos') ? 'active' : ''; ?>"><a href="productos"><i class="bi bi-box-seam"></i><span>Administrar Productos</span></a></li>
			<li class="<?php echo ($paginaActualMenu == 'impresion-precios') ? 'active' : ''; ?>"><a href="impresion-precios"><i class="bi bi-printer"></i><span>Imprimir Precios</span></a></li>
			<li class="<?php echo ($paginaActualMenu == 'cajas-cajero') ? 'active' : ''; ?>"><a href="cajas-cajero"><i class="bi bi-cash-coin"></i><span>Caja</span></a></li>
			<li class="<?php echo ($paginaActualMenu == 'ventas') ? 'active' : ''; ?>"><a href="ventas"><i class="bi bi-graph-up"></i><span>Adm. ventas</span></a></li>
			<li class="<?php echo ($paginaActualMenu == 'crear-venta-caja') ? 'active' : ''; ?>"><a href="crear-venta-caja"><i class="bi bi-plus-circle"></i><span>Crear venta</span></a></li>
			<li class="<?php echo ($paginaActualMenu == 'clientes') ? 'active' : ''; ?>"><a href="clientes"><i class="bi bi-persons"></i><span>Clientes</span></a></li>

		<?php } ?>

		</ul>
	</section>
</aside>