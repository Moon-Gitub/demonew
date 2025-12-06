<?php $paginaActualMenu = (isset($_GET["ruta"]) ? $_GET["ruta"] : 'inicio'); ?>

<aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark">
	<!--begin::Sidebar Brand-->
	<div class="sidebar-brand">
		<a href="inicio" class="brand-link">
			<img src="vistas/img/plantilla/icono-blanco.png" alt="POS Moon Logo" class="brand-image opacity-75 shadow" style="max-height: 33px;">
			<span class="brand-text fw-light">POS | Moon</span>
		</a>
	</div>
	<!--end::Sidebar Brand-->
	
	<!--begin::Sidebar Wrapper-->
	<div class="sidebar-wrapper">
		<nav class="mt-2">
			<ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" role="menu" data-accordion="false">
			<?php
			if($_SESSION["perfil"] == "Administrador"){
			?>
				<li class="nav-item">
					<a href="inicio" class="nav-link <?php echo ($paginaActualMenu == 'inicio') ? 'active' : ''; ?>">
						<i class="nav-icon bi bi-speedometer2"></i>
						<p>Inicio</p>
					</a>
				</li>
					
				<li class="nav-item <?php echo (in_array($paginaActualMenu, ['empresa', 'usuarios'])) ? 'menu-open' : '' ?>">
					<a href="#" class="nav-link <?php echo (in_array($paginaActualMenu, ['empresa', 'usuarios'])) ? 'active' : '' ?>">
						<i class="nav-icon bi bi-building"></i>
						<p>Empresa<i class="nav-arrow bi bi-chevron-right"></i></p>
					</a>
					<ul class="nav nav-treeview">
						<li class="nav-item"><a href="empresa" class="nav-link <?php echo ($paginaActualMenu == 'empresa') ? 'active' : ''; ?>"><i class="nav-icon bi bi-circle"></i><p>Datos Empresa</p></a></li>
						<li class="nav-item"><a href="usuarios" class="nav-link <?php echo ($paginaActualMenu == 'usuarios') ? 'active' : ''; ?>"><i class="nav-icon bi bi-circle"></i><p>Usuarios</p></a></li>
					</ul>
				</li>

				<li class="nav-item <?php echo (in_array($paginaActualMenu, ['productos', 'categorias', 'impresion-precios', 'productos-importar-excel2'])) ? 'menu-open' : '' ?>">
					<a href="#" class="nav-link <?php echo (in_array($paginaActualMenu, ['productos', 'categorias', 'impresion-precios', 'productos-importar-excel2'])) ? 'active' : '' ?>">
						<i class="nav-icon bi bi-box-seam"></i>
						<p>Productos<i class="nav-arrow bi bi-chevron-right"></i></p>
					</a>
					<ul class="nav nav-treeview">
						<li class="nav-item"><a href="productos" class="nav-link <?php echo ($paginaActualMenu == 'productos') ? 'active' : ''; ?>"><i class="nav-icon bi bi-circle"></i><p>Administrar Productos</p></a></li>
						<li class="nav-item"><a href="categorias" class="nav-link <?php echo ($paginaActualMenu == 'categorias') ? 'active' : ''; ?>"><i class="nav-icon bi bi-circle"></i><p>Categorias</p></a></li>
						<li class="nav-item"><a href="impresion-precios" class="nav-link <?php echo ($paginaActualMenu == 'impresion-precios') ? 'active' : ''; ?>"><i class="nav-icon bi bi-circle"></i><p>Imprimir Precios</p></a></li>
						<li class="nav-item"><a href="productos-importar-excel2" class="nav-link <?php echo ($paginaActualMenu == 'productos-importar-excel2') ? 'active' : ''; ?>"><i class="nav-icon bi bi-circle"></i><p>Importar excel</p></a></li>
					</ul>
				</li>

				<li class="nav-item <?php echo (in_array($paginaActualMenu, ['pedidos-generar-movimiento', 'pedidos-nuevos', 'pedidos-validados'])) ? 'menu-open' : '' ?>">
					<a href="#" class="nav-link <?php echo (in_array($paginaActualMenu, ['pedidos-generar-movimiento', 'pedidos-nuevos', 'pedidos-validados'])) ? 'active' : '' ?>">
						<i class="nav-icon bi bi-arrow-left-right"></i>
						<p>Mov. De Productos<i class="nav-arrow bi bi-chevron-right"></i></p>
					</a>
					<ul class="nav nav-treeview">
						<li class="nav-item"><a href="pedidos-generar-movimiento" class="nav-link <?php echo ($paginaActualMenu == 'pedidos-generar-movimiento') ? 'active' : ''; ?>"><i class="nav-icon bi bi-circle"></i><p>Generar Movimiento</p></a></li>
						<li class="nav-item"><a href="pedidos-nuevos" class="nav-link <?php echo ($paginaActualMenu == 'pedidos-nuevos') ? 'active' : ''; ?>"><i class="nav-icon bi bi-circle"></i><p>Validar Movimiento</p></a></li>
						<li class="nav-item"><a href="pedidos-validados" class="nav-link <?php echo ($paginaActualMenu == 'pedidos-validados') ? 'active' : ''; ?>"><i class="nav-icon bi bi-circle"></i><p>Movimientos Validados</p></a></li>
					</ul>
				</li>

				<li class="nav-item <?php echo (in_array($paginaActualMenu, ['cajas', 'cajas-cierre'])) ? 'menu-open' : '' ?>">
					<a href="#" class="nav-link <?php echo (in_array($paginaActualMenu, ['cajas', 'cajas-cierre'])) ? 'active' : '' ?>">
						<i class="nav-icon bi bi-cash-coin"></i>
						<p>Cajas<i class="nav-arrow bi bi-chevron-right"></i></p>
					</a>
					<ul class="nav nav-treeview">
						<li class="nav-item"><a href="cajas" class="nav-link <?php echo ($paginaActualMenu == 'cajas') ? 'active' : ''; ?>"><i class="nav-icon bi bi-circle"></i><p>Administrar Caja</p></a></li>
						<li class="nav-item"><a href="cajas-cierre" class="nav-link <?php echo ($paginaActualMenu == 'cajas-cierre') ? 'active' : ''; ?>"><i class="nav-icon bi bi-circle"></i><p>Cierres de caja</p></a></li>
					</ul>
				</li>

				<li class="nav-item <?php echo (in_array($paginaActualMenu, ['ventas', 'presupuestos', 'crear-venta-caja', 'ventas-productos', 'ventas-rentabilidad', 'ventas-categoria-proveedor-informe'])) ? 'menu-open' : '' ?>">
					<a href="#" class="nav-link <?php echo (in_array($paginaActualMenu, ['ventas', 'presupuestos', 'crear-venta-caja', 'ventas-productos', 'ventas-rentabilidad', 'ventas-categoria-proveedor-informe'])) ? 'active' : '' ?>">
						<i class="nav-icon bi bi-graph-up-arrow"></i>
						<p>Ventas<i class="nav-arrow bi bi-chevron-right"></i></p>
					</a>
					<ul class="nav nav-treeview">
						<li class="nav-item"><a href="ventas" class="nav-link <?php echo ($paginaActualMenu == 'ventas') ? 'active' : ''; ?>"><i class="nav-icon bi bi-circle"></i><p>Adm. ventas</p></a></li>
						<li class="nav-item"><a href="presupuestos" class="nav-link <?php echo ($paginaActualMenu == 'presupuestos') ? 'active' : ''; ?>"><i class="nav-icon bi bi-circle"></i><p>Adm. presupuestos</p></a></li>
						<li class="nav-item"><a href="crear-venta-caja" class="nav-link <?php echo ($paginaActualMenu == 'crear-venta-caja') ? 'active' : ''; ?>"><i class="nav-icon bi bi-circle"></i><p>Crear venta</p></a></li>
						<li class="nav-item"><a href="ventas-productos" class="nav-link <?php echo ($paginaActualMenu == 'ventas-productos') ? 'active' : ''; ?>"><i class="nav-icon bi bi-circle"></i><p>Productos Vendidos</p></a></li>
						<li class="nav-item"><a href="ventas-rentabilidad" class="nav-link <?php echo ($paginaActualMenu == 'ventas-rentabilidad') ? 'active' : ''; ?>"><i class="nav-icon bi bi-circle"></i><p>Informe rentabilidad</p></a></li>
						<li class="nav-item"><a href="ventas-categoria-proveedor-informe" class="nav-link <?php echo ($paginaActualMenu == 'ventas-categoria-proveedor-informe') ? 'active' : ''; ?>"><i class="nav-icon bi bi-circle"></i><p>Informe de ventas</p></a></li>
					</ul>
				</li>

				<li class="nav-item">
					<a href="clientes" class="nav-link <?php echo (in_array($paginaActualMenu, ['clientes', 'clientes_cuenta'])) ? 'active' : ''; ?>">
						<i class="nav-icon bi bi-people"></i>
						<p>Clientes</p>
					</a>
				</li>

				<li class="nav-item <?php echo (in_array($paginaActualMenu, ['compras', 'crear-compra', 'ingreso'])) ? 'menu-open' : ''; ?>">
					<a href="#" class="nav-link <?php echo (in_array($paginaActualMenu, ['compras', 'crear-compra', 'ingreso'])) ? 'active' : ''; ?>">
						<i class="nav-icon bi bi-cart"></i>
						<p>Compras<i class="nav-arrow bi bi-chevron-right"></i></p>
					</a>
					<ul class="nav nav-treeview">
						<li class="nav-item"><a href="compras" class="nav-link <?php echo ($paginaActualMenu == 'compras') ? 'active' : ''; ?>"><i class="nav-icon bi bi-circle"></i><p>Adm. Compras</p></a></li>
						<li class="nav-item"><a href="crear-compra" class="nav-link <?php echo ($paginaActualMenu == 'crear-compra') ? 'active' : ''; ?>"><i class="nav-icon bi bi-circle"></i><p>Crear Compra</p></a></li>
						<li class="nav-item"><a href="ingreso" class="nav-link <?php echo ($paginaActualMenu == 'ingreso') ? 'active' : ''; ?>"><i class="nav-icon bi bi-circle"></i><p>Ingreso Mercaderia</p></a></li>
					</ul>
				</li>

				<li class="nav-item">
					<a href="proveedores" class="nav-link <?php echo (in_array($paginaActualMenu, ['proveedores', 'proveedores_cuenta'])) ? 'active' : ''; ?>">
						<i class="nav-icon bi bi-truck"></i>
						<p>Proveedores</p>
					</a>
				</li>

			<?php } 
			
			if($_SESSION["perfil"] == "Vendedor"){ 	?>
				<li class="nav-item">
					<a href="inicio" class="nav-link <?php echo ($paginaActualMenu == 'inicio') ? 'active' : ''; ?>">
						<i class="nav-icon bi bi-speedometer2"></i>
						<p>Inicio</p>
					</a>
				</li>
				<li class="nav-item">
					<a href="productos" class="nav-link <?php echo ($paginaActualMenu == 'productos') ? 'active' : ''; ?>">
						<i class="nav-icon bi bi-box-seam"></i>
						<p>Administrar Productos</p>
					</a>
				</li>
				<li class="nav-item">
					<a href="impresion-precios" class="nav-link <?php echo ($paginaActualMenu == 'impresion-precios') ? 'active' : ''; ?>">
						<i class="nav-icon bi bi-printer"></i>
						<p>Imprimir Precios</p>
					</a>
				</li>
				<li class="nav-item">
					<a href="cajas-cajero" class="nav-link <?php echo ($paginaActualMenu == 'cajas-cajero') ? 'active' : ''; ?>">
						<i class="nav-icon bi bi-cash-coin"></i>
						<p>Caja</p>
					</a>
				</li>
				<li class="nav-item">
					<a href="ventas" class="nav-link <?php echo ($paginaActualMenu == 'ventas') ? 'active' : ''; ?>">
						<i class="nav-icon bi bi-graph-up-arrow"></i>
						<p>Adm. ventas</p>
					</a>
				</li>
				<li class="nav-item">
					<a href="crear-venta-caja" class="nav-link <?php echo ($paginaActualMenu == 'crear-venta-caja') ? 'active' : ''; ?>">
						<i class="nav-icon bi bi-plus-circle"></i>
						<p>Crear venta</p>
					</a>
				</li>
				<li class="nav-item">
					<a href="clientes" class="nav-link <?php echo ($paginaActualMenu == 'clientes') ? 'active' : ''; ?>">
						<i class="nav-icon bi bi-people"></i>
						<p>Clientes</p>
					</a>
				</li>
			<?php } ?>
			</ul>
		</nav>
	</div>
	<!--end::Sidebar Wrapper-->
</aside>
<!--end::Sidebar-->
