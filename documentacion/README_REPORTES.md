# Informes ejecutivos - Sistema POS Moon

Documentación de los informes para toma de decisiones del negocio.

## Estructura implementada

- **Base de datos**: `db/reportes_ejecutivos_queries.sql` – Consultas SQL reutilizables (Dashboard, Rentabilidad, Inventario, Ventas periódico, Flujo de caja).
- **Modelo**: `modelos/reporte-dashboard-ejecutivo.modelo.php` – Métricas del dashboard diario (resumen día, top productos, medios de pago, saldo caja).
- **Vista**: `vistas/modulos/informe-dashboard-ejecutivo.php` – Pantalla del Dashboard ejecutivo con tarjetas (KPIs) y gráficos (Chart.js).
- **Modelo**: `modelos/reporte-gestion-pedidos.modelo.php` – Gestión inteligente de pedidos (productos críticos, días cobertura, cantidad sugerida, ROI, por proveedor, baja rotación).
- **Vista**: `vistas/modulos/informe-gestion-pedidos.php` – Informe "¿Qué debo comprar?" con alertas, tablas y gráfico de días de cobertura.

## Acceso

- **Menú**: Informes → **Dashboard ejecutivo** / **Gestión de pedidos**.
- **URL**: `index.php?ruta=informe-dashboard-ejecutivo` o `index.php?ruta=informe-gestion-pedidos`.
- **Permisos**: Visible para usuarios con la pantalla correspondiente; el perfil Administrador puede abrir ambos siempre.

## Dashboard ejecutivo diario

**Métricas:**

- Ventas del día (total facturado).
- Cantidad de transacciones.
- Ticket promedio.
- Clientes atendidos (distintos).
- Variación % vs. día anterior.
- Saldo de caja acumulado al día seleccionado.

**Gráficos:**

- Barras: Top 10 productos más vendidos por monto.
- Circular: Distribución por medio de pago.

**Filtro:** Fecha (por defecto: hoy). Formulario con `type="date"` y botón Consultar.

## Esquema de datos utilizado

- **ventas**: `id`, `fecha`, `total`, `id_cliente`, `metodo_pago`, `cbte_tipo`. Se excluyen comprobantes con `cbte_tipo` en (3, 8, 13, 203, 208, 213, 999).
- **productos_venta**: `id_venta`, `id_producto`, `cantidad`, `precio_venta`, `precio_compra`.
- **cajas**: `fecha`, `tipo` (1 = ingreso, 0 = egreso), `monto`.

## Gestión inteligente de pedidos

Responde **"¿Qué debo comprar?"** con:

- **Productos críticos**: Stock actual, ventas 7/30 días, promedio diario, días de cobertura, cantidad sugerida, inversión, ganancia esperada, ROI. Estado: CRÍTICO (≤3 días), URGENTE (4–7), NORMAL (>7).
- **Resumen**: Inversión total sugerida, inversión solo críticos, ganancia esperada, cantidad de productos y de críticos/urgentes.
- **Alertas**: Productos sin stock en 48 h, ganancia si repone top 10, monto para reponer solo críticos, productos de baja rotación.
- **Gráfico**: Top 20 productos por días de cobertura (menor = más urgente).
- **Por proveedor**: Listado agrupado con productos a pedir y total por proveedor.
- **Baja rotación**: Productos con stock pero sin ventas en 90 días (no pedir más / liquidar).

**Parámetros**: Días de análisis (7–90, default 30), días de cobertura deseado (7–90, default 30). Esquema real: `productos.stock`, `productos.stock_bajo`, `productos_venta.cantidad`, `ventas.cbte_tipo` excluidos.

**Rendimiento**: Las consultas parten de `ventas` (filtradas por fecha) y agregan por producto; si sigue siendo lento, ejecutar los índices recomendados en `db/reportes_indices_recomendados.sql`.

## Informes previstos (especificación)

1. **Dashboard ejecutivo diario** – Implementado.
2. **Gestión inteligente de pedidos** – Implementado.
3. Rentabilidad por producto.
4. Análisis clientes y cuenta corriente.
5. Control de inventario y stock.
6. Análisis proveedores y compras.
7. Análisis de ventas periódico.
8. Flujo de caja.
9. Presupuestos y conversión.

Para ampliar: reutilizar las consultas de `db/reportes_ejecutivos_queries.sql`, crear un modelo en `modelos/reporte-*.modelo.php` y una vista en `vistas/modulos/informe-*.php` con el mismo estilo (tarjetas + Chart.js).

## Cómo agregar un nuevo informe

1. Añadir consultas en `db/reportes_ejecutivos_queries.sql` (o archivo específico).
2. Crear `modelos/reporte-nombre-informe.modelo.php` con métodos estáticos que usen `Conexion::conectar()`.
3. Cargar el modelo en `index.php` con `require_once "modelos/reporte-nombre-informe.modelo.php"`.
4. Crear `vistas/modulos/informe-nombre-informe.php` (tarjetas, tablas, gráficos Chart.js).
5. En `vistas/plantilla.php`: incluir la ruta en la lista de rutas permitidas y, si aplica, permitir acceso a Administrador.
6. En `vistas/modulos/menu.php`: añadir la opción en Informes y en `$verInformes` / array `active`.
