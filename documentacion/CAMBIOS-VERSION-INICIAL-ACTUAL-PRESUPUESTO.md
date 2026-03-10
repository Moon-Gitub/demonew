# Notas de versión: Cambios desde versión inicial hasta actual

**Documento para presupuesto — POS Moon**

---

## Resumen

Desde la versión inicial se han realizado ~912 commits, modificando ~540 archivos y añadiendo ~110.000 líneas de código. A continuación se detallan las mejoras por área y qué implica cada cambio.

---

## 1. Login y seguridad

### Login en un solo intento
- **Mejora:** Se llama a `session_write_close()` antes del redirect tras un login correcto, para que la sesión se guarde antes de que el navegador siga a "inicio".
- **Implica:** El usuario ya no tiene que ingresar credenciales dos veces; el login funciona correctamente en el primer intento.

### Protección contra fuerza bruta
- **Mejora:** Límite de intentos fallidos por usuario con bloqueo temporal. Registro de intentos en sesión y mensajes de intentos restantes / tiempo de bloqueo.
- **Implica:** Mayor seguridad ante ataques de adivinación de contraseñas; el sistema se protege solo.

### Contraseñas
- **Mejora:** Compatibilidad con formato antiguo (crypt) y nuevo (password_hash). Migración automática al nuevo formato y rehash cuando corresponde.
- **Implica:** Contraseñas más seguras sin romper usuarios existentes; migración transparente.

### Token CSRF
- **Mejora:** Generación y uso de token CSRF en sesión para protección de formularios.
- **Implica:** Protección frente a ataques de falsificación de peticiones entre sitios.

### Pantalla de login configurable
- **Mejora:** Configuración dinámica desde base de datos (fondo, logo, color de botón, fuente, etc.) por empresa.
- **Implica:** Cada empresa puede personalizar la pantalla de ingreso con su identidad visual.

---

## 2. Permisos por rol

### Tablas pantallas y permisos_rol
- **Mejora:** ABM de pantallas del sistema y asignación por rol (qué pantallas ve cada perfil).
- **Implica:** Control granular de acceso; cada rol ve solo lo que necesita.

### Menú según permisos
- **Mejora:** El menú solo muestra las pantallas permitidas para el rol del usuario logueado.
- **Implica:** Interfaz más limpia y segura; el vendedor no ve opciones de administración.

### Roles iniciales
- **Mejora:** Administrador (todas las pantallas) y Vendedor (conjunto reducido: inicio, productos, caja, ventas, clientes, chat).
- **Implica:** Configuración lista para usar; se pueden crear roles personalizados.

---

## 3. Listas de precio

### Tabla listas_precio
- **Mejora:** ABM de listas por empresa (código, nombre, base de precio, tipo y valor de descuento, orden, activo).
- **Implica:** Múltiples precios por producto según cliente o canal (público, costo, empleados, etc.).

### Base de precio y descuento
- **Mejora:** Uso de `precio_venta` o `precio_compra` como base; descuento ninguno o porcentaje.
- **Implica:** Flexibilidad para definir listas desde costo o desde precio de venta.

### Asignación por usuario
- **Mejora:** Cada usuario puede tener listas de precio permitidas; el sistema aplica la configuración en ventas y presupuestos.
- **Implica:** Cada vendedor o sucursal puede tener su propia lista sin modificar el producto.

---

## 4. Medios de pago

### Tabla medios_pago
- **Mejora:** ABM de medios (código, nombre, descripción, flags: requiere_codigo, requiere_banco, requiere_numero, requiere_fecha, orden).
- **Implica:** Medios configurables sin tocar código; se pueden agregar o quitar según el negocio.

### Uso en ventas y cuenta corriente
- **Mejora:** Los medios se leen desde la BD en ventas, presupuestos, cuenta corriente clientes y proveedores.
- **Implica:** Un solo lugar para mantener medios; consistencia en todo el sistema.

---

## 5. Formatos de balanza

### Tabla balanzas_formatos
- **Mejora:** Configuración por empresa de códigos de balanza (prefijo, posiciones de producto y cantidad/peso, factor divisor, cantidad fija, etc.).
- **Implica:** Soporte para distintas marcas de balanza sin hardcodear; cada empresa configura la suya.

---

## 6. Facturación AFIP y ventas

### Helper reutilizable AFIP
- **Mejora:** Clase `FacturacionAfipHelper` para armar FeCAEReq, soporte Monotributo/RI, filtro de tipos de comprobante por empresa.
- **Implica:** Código centralizado y reutilizable; menos errores y mantenimiento más simple.

### Selector de empresa en Autorizar
- **Mejora:** Modal Autorizar Comprobante permite elegir razón social y tipos A/B o C según condición IVA de la empresa.
- **Implica:** En multiempresa se puede facturar desde cualquier razón social sin confundirse.

### Corrección redondeo AFIP 10048
- **Mejora:** Redondeo correcto de importes para evitar rechazo de AFIP.
- **Implica:** Menos rechazos por diferencias de centavos; facturación más estable.

### Facturación por lote
- **Mejora:** Selección múltiple de ventas, modal Autorizar (empresa + tipo), envío a AFIP en una sola llamada, orden por fecha, soporte tipo elegido para ventas tipo X.
- **Implica:** Facturar muchas ventas de una vez; ahorro de tiempo y menos llamadas a AFIP.

### Regenerar TA si vencido
- **Mejora:** Regenerar Ticket de Acceso AFIP antes de facturar por lote si está vencido.
- **Implica:** Evita errores por TA expirado; el lote se procesa sin intervención manual.

### Evitar duplicados productos_venta
- **Mejora:** UNIQUE + ON DUPLICATE KEY UPDATE en inserción de productos por venta.
- **Implica:** No se duplican líneas de producto en una misma venta; datos más limpios.

### Informes excluir anuladas
- **Mejora:** Ventas con cbte_tipo 999 y notas de crédito no suman en totales de informes.
- **Implica:** Los reportes reflejan ventas reales; no se inflan con anulaciones.

### Columna punto de venta en Sucursal
- **Mejora:** Mostrar número de pto_vta en tabla ventas para identificar al facturar por lote.
- **Implica:** Se distingue fácilmente qué ventas corresponden a cada punto de venta.

---

## 7. Configuración de empresa

### UI puntos de venta y almacenes
- **Mejora:** Interfaz para agregar/quitar puntos de venta y almacenes con botones + y filas dinámicas, sin escribir JSON manual.
- **Implica:** Configuración accesible para usuarios no técnicos; menos errores de sintaxis.

### Mejor contraste visual
- **Mejora:** Inputs con fondo blanco, botón + visible, texto legible en paneles morados.
- **Implica:** Mayor legibilidad; menos fatiga visual y menos errores al completar datos.

### Descripciones help-block
- **Mejora:** Descripción en cada campo del formulario (como en la sección Mercado Pago).
- **Implica:** El usuario entiende qué debe cargar en cada campo sin documentación externa.

---

## 8. Usuarios

### Fix ModeloEmpresa not found
- **Mejora:** Carga correcta del modelo de empresa en AJAX de usuarios.
- **Implica:** La edición de usuarios funciona sin errores; se pueden asignar sucursales y almacenes.

### Selector sucursal y almacenes
- **Mejora:** Asignación de sucursal por usuario; carga de almacenes por empresa.
- **Implica:** Cada usuario puede estar vinculado a una sucursal y ver solo sus almacenes.

---

## 9. Productos

### Campo activo
- **Mejora:** Desactivar productos en lugar de borrar si tienen ventas asociadas.
- **Implica:** Se mantiene el historial de ventas; no se pierden referencias al borrar.

### Pantalla productos desactivados
- **Mejora:** Nueva ruta y menú para ver y reactivar productos inactivos.
- **Implica:** Posibilidad de reactivar productos sin buscarlos en la base de datos.

### Ocultar inactivos en listados
- **Mejora:** Filtro activo=1 en DataTables y consultas.
- **Implica:** Los listados no se llenan de productos que ya no se venden.

---

## 10. Crear venta caja (POS)

### Layout responsive
- **Mejora:** Columnas adaptables, cobro integrado, optimizado para móvil.
- **Implica:** Uso cómodo en tablets y celulares; mejor experiencia en mostrador.

### Medios de pago desde BD
- **Mejora:** Tabla medios_pago; sin hardcodeo en código.
- **Implica:** Medios configurables; se agregan o quitan sin tocar código.

### Atajos de teclado
- **Mejora:** Modal F1 con atajos, Alt+P para punto de venta.
- **Implica:** Mayor velocidad para usuarios que trabajan con teclado.

### Descuento e interés
- **Mejora:** Alineación de campos, actualización del total en tiempo real.
- **Implica:** Interfaz más clara; el total se actualiza al instante.

---

## 11. Informes y dashboard

### Dashboard ejecutivo
- **Mejora:** Dashboard diario, menú Informes unificado.
- **Implica:** Vista rápida del negocio; decisión basada en datos.

### Gestión inteligente de pedidos
- **Mejora:** Top 20, productos críticos, baja rotación, por proveedor, DataTables con filtros y exportación.
- **Implica:** Control de stock y compras; identificación de productos a reponer o descontinuar.

### Informe ventas por productos
- **Mejora:** Resumen agregado por producto (cantidad, compra, venta, margen %), DataTable.
- **Implica:** Análisis de rentabilidad por producto; decisiones de precios y surtido.

### Optimización dashboard
- **Mejora:** Quitar widgets pesados (productos más vendidos/recientes).
- **Implica:** Dashboard más rápido; menos carga en el servidor.

---

## 12. Compras

### Factura directa
- **Mejora:** Opción sin crear orden pendiente; solo factura y actualización de stock.
- **Implica:** Flujo más corto cuando no se usa pedido; menos pasos para el usuario.

### Cantidad pedidos cuando recibidos=0
- **Mejora:** Usar cantidad de pedidos para actualizar stock cuando aún no hay recibos.
- **Implica:** Stock correcto desde el primer ingreso; menos inconsistencias.

---

## 13. Cajas

### Egresos por devoluciones
- **Mejora:** Detalle en cierre de caja con número de venta y productos devueltos.
- **Implica:** Trazabilidad de devoluciones; cierre de caja más claro.

---

## 14. Sistema de cobro y Mercado Pago (v2.0)

### Modal automático de pago
- **Mejora:** Modal de pago según día del mes.
- **Implica:** Recordatorio de cobro en el momento adecuado; menos morosidad.

### Integración Mercado Pago
- **Mejora:** Widget de pago, webhook, procesamiento de pagos.
- **Implica:** Cobro online integrado; el cliente paga sin salir del sistema.

### Recargos por mora
- **Mejora:** Recargos progresivos (10%, 15%, 20%, 30%).
- **Implica:** Incentivo para pagar a tiempo; política de cobro clara.

### Bloqueo automático
- **Mejora:** Bloqueo del sistema después del día 26 si no hay pago.
- **Implica:** Presión controlada para regularizar; se evita uso sin pago.

### Cabezote mejorado
- **Mejora:** Widget de cobro en cabecera; conexión a BD Moon para saldos.
- **Implica:** El cliente ve su deuda y puede pagar desde la cabecera.

### Variables de entorno y credenciales
- **Mejora:** Credenciales en .env; no hardcodeadas en código.
- **Implica:** Mayor seguridad; configuración por entorno sin exponer claves.

---

## 15. Extras y utilidades

### POS Offline Moon
- **Mejora:** Aplicación Python con tkinter, sincronización con servidor.
- **Implica:** Ventas sin conexión; sincronización cuando vuelve internet.

### Script generar ventas de prueba
- **Mejora:** Script PHP para datos de prueba.
- **Implica:** Pruebas rápidas sin cargar datos manualmente.

### Diagnóstico de rendimiento
- **Mejora:** Script para medir PHP, MySQL, autoload.
- **Implica:** Detección de cuellos de botella; optimización basada en datos.

### Script actualizar-desde-github.sh
- **Mejora:** Actualización automática desde GitHub en el servidor.
- **Implica:** Despliegue más rápido y menos errores manuales.

---

## 16. Base de datos

### Nuevas tablas
- **Mejora:** listas_precio, medios_pago, balanzas_formatos, pantallas, permisos_rol, mercadopago_intentos, mercadopago_pagos, mercadopago_webhooks.
- **Implica:** Estructura preparada para las nuevas funcionalidades; datos organizados.

### Nuevas columnas
- **Mejora:** productos.activo, usuarios.sucursal, clientes.estado_bloqueo, clientes.aplicar_recargos, etc.
- **Implica:** Soporte para desactivar productos, asignar sucursales y controlar cobro por cliente.

---

## Resumen para presupuesto

| Área | Cambios principales | Impacto para el negocio |
|------|---------------------|-------------------------|
| **Facturación AFIP** | Helper, lote, selector empresa, redondeo | Facturación más rápida, menos errores, multiempresa |
| **Sistema de cobro** | Mercado Pago, webhook, recargos, bloqueo | Cobro online, menos morosidad |
| **Informes** | Dashboard, gestión pedidos, ventas por producto | Mejor toma de decisiones |
| **Empresa** | Puntos venta, almacenes, descripciones | Configuración más fácil |
| **Usuarios** | Sucursal, almacenes, permisos | Control de acceso y multi-sucursal |
| **Productos** | Activo, desactivados | Historial preservado, listados limpios |
| **Crear venta caja** | Responsive, medios BD, atajos | Mejor experiencia en mostrador |
| **Login y seguridad** | Un intento, fuerza bruta, CSRF, contraseñas | Mayor seguridad |
| **Listas, medios, balanzas** | Tablas configurables | Flexibilidad sin programar |
| **Extras** | POS offline, scripts, .env | Operación sin internet, despliegue más simple |

---

*Documento generado para uso en presupuestos. Formato: notas de versión con mejora e implicación de cada cambio.*
