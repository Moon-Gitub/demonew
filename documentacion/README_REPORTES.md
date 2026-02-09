# Informes ejecutivos - Sistema POS Moon

Documentación de los informes para toma de decisiones del negocio.

## Estructura implementada

- **Base de datos**: `db/reportes_ejecutivos_queries.sql` – Consultas SQL reutilizables (Dashboard, Rentabilidad, Inventario, Ventas periódico, Flujo de caja).
- **Modelo**: `modelos/reporte-dashboard-ejecutivo.modelo.php` – Métricas del dashboard diario (resumen día, top productos, medios de pago, saldo caja).
- **Vista**: `vistas/modulos/informe-dashboard-ejecutivo.php` – Pantalla del Dashboard ejecutivo con tarjetas (KPIs) y gráficos (Chart.js).

## Acceso

- **Menú**: Informes → **Dashboard ejecutivo**.
- **URL**: `index.php?ruta=informe-dashboard-ejecutivo`.
- **Permisos**: Visible para usuarios con pantalla `informe-dashboard-ejecutivo`; el perfil Administrador puede abrirlo siempre.

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

## Informes previstos (especificación)

1. **Dashboard ejecutivo diario** – Implementado.
2. Rentabilidad por producto.
3. Análisis clientes y cuenta corriente.
4. Control de inventario y stock.
5. Análisis proveedores y compras.
6. Análisis de ventas periódico.
7. Flujo de caja.
8. Presupuestos y conversión.

Para ampliar: reutilizar las consultas de `db/reportes_ejecutivos_queries.sql`, crear un modelo en `modelos/reporte-*.modelo.php` y una vista en `vistas/modulos/informe-*.php` con el mismo estilo (tarjetas + Chart.js).

## Cómo agregar un nuevo informe

1. Añadir consultas en `db/reportes_ejecutivos_queries.sql` (o archivo específico).
2. Crear `modelos/reporte-nombre-informe.modelo.php` con métodos estáticos que usen `Conexion::conectar()`.
3. Cargar el modelo en `index.php` con `require_once "modelos/reporte-nombre-informe.modelo.php"`.
4. Crear `vistas/modulos/informe-nombre-informe.php` (tarjetas, tablas, gráficos Chart.js).
5. En `vistas/plantilla.php`: incluir la ruta en la lista de rutas permitidas y, si aplica, permitir acceso a Administrador.
6. En `vistas/modulos/menu.php`: añadir la opción en Informes y en `$verInformes` / array `active`.
