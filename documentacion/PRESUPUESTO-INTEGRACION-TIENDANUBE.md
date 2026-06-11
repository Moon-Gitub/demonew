# PRESUPUESTO
## Integración Tiendanube con Sistema POS

---

**Para:** [Nombre del cliente]  
**Fecha:** Marzo 2026  
**Válido hasta:** [Fecha + 30 días]  
**Referencia:** INT-TN-2026-001

---

## 1. Descripción del Servicio

Se propone desarrollar la **integración entre la tienda online Tiendanube** (Nuvemshop) y el **sistema de gestión comercial (POS)** que utiliza el cliente, con el objetivo de mantener un **catálogo unificado** y un **stock sincronizado** entre ambos canales de venta.

De esta forma, cuando se venda en el local físico el stock se descuenta automáticamente en la tienda online, y cuando se venda en la tienda online el stock se descuenta en el sistema del local. El cliente evita vender productos sin stock, desactualizar manualmente la web y tener que manejar dos inventarios por separado.

La integración se realiza mediante la [API pública de Tiendanube](https://tiendanube.github.io/api-documentation/resources), que permite gestionar productos, variantes, stock y órdenes de forma programática.

---

## 2. Funcionalidades Incluidas

### 2.1 Catálogo unificado
- Vinculación de productos del POS con productos de Tiendanube (por código/SKU)
- Sincronización de precios desde el POS hacia la tienda online (o viceversa, según configuración)
- Sincronización de categorías para mantener la organización en ambos sistemas
- Posibilidad de elegir qué productos del POS se publican en la tienda online

### 2.2 Sincronización de stock
- **Venta en local:** al registrar una venta en el POS, se descuenta el stock en Tiendanube
- **Venta online:** al concretarse una venta en Tiendanube, se descuenta el stock en el POS
- Sincronización en tiempo real mediante webhooks (notificaciones automáticas de Tiendanube)
- Opción de sincronización programada como respaldo (ej. cada X minutos)

### 2.3 Panel de configuración
- Pantalla en el sistema POS para conectar la cuenta de Tiendanube (autorización OAuth)
- Configuración de qué sucursal o almacén del POS se vincula con la tienda online
- Mapeo de productos (asociar códigos del POS con productos de Tiendanube)
- Registro de sincronizaciones y errores para facilitar el soporte

### 2.4 Manejo de variantes
- Soporte para productos con variantes (talle, color, etc.) en Tiendanube
- Vinculación de cada variante con el stock correspondiente en el POS

---

## 3. Lo que el cliente debe proporcionar

- Cuenta activa en Tiendanube con tienda configurada
- Acceso para crear una aplicación en el [panel de Socios Tecnológicos de Tiendanube](https://ayuda.tiendanube.com/es_ES/socios-tecnologicos/que-es-la-api-de-tiendanube)
- Sistema POS ya instalado y operativo (versión compatible)
- URL pública del sistema (HTTPS) para recibir notificaciones de Tiendanube
- Datos de contacto para pruebas y coordinación

---

## 4. Limitaciones y consideraciones

- La integración depende del correcto uso de **códigos/SKU** en ambos sistemas para vincular productos
- Productos sin código o con códigos distintos no se sincronizarán automáticamente
- Tiendanube permite hasta 1000 variantes por producto
- La API de Tiendanube tiene límites de solicitudes por minuto; la integración los respeta para evitar bloqueos

---

## 5. Forma de trabajo

1. Reunión inicial para definir criterios de mapeo (códigos, sucursales, productos a sincronizar)
2. Desarrollo e instalación de la integración en el sistema del cliente
3. Configuración y vinculación de la cuenta de Tiendanube
4. Pruebas con productos de prueba
5. Puesta en producción y capacitación breve al usuario

**Tiempo estimado:** 2 a 3 semanas desde la confirmación del presupuesto.

---

## 6. Inversión

| Ítem | Descripción | Monto |
|------|-------------|-------|
| 1 | Desarrollo de la integración (conexión API, OAuth, mapeo de productos) | |
| 2 | Sincronización bidireccional de stock (POS ↔ Tiendanube) | |
| 3 | Panel de configuración y vinculación de cuenta | |
| 4 | Manejo de webhooks y sincronización en tiempo real | |
| 5 | Pruebas, instalación y puesta en producción | |
| | | |
| | **TOTAL APROXIMADO** | **$ 95.000** |

*Precio en pesos argentinos. IVA no incluido (si corresponde).*

*El monto puede ajustarse según cantidad de productos, complejidad del catálogo (variantes) y requisitos adicionales.*

---

## 7. Forma de pago

- 50% al iniciar el desarrollo  
- 50% al finalizar la integración y entrega operativa  

*O según acuerdo previo con el cliente.*

---

## 8. Mantenimiento y soporte

- Se incluyen 30 días de soporte post-entrega para ajustes menores
- Modificaciones o nuevas funcionalidades fuera de este alcance se presupuestan por separado

---

## 9. Referencias técnicas

- [API Tiendanube – Recursos](https://tiendanube.github.io/api-documentation/resources) (Product, Product Variant, Order, Webhook)
- [¿Qué es la API de Tiendanube?](https://ayuda.tiendanube.com/es_ES/socios-tecnologicos/que-es-la-api-de-tiendanube)

---

## 10. Contacto

**Moon Desarrollos**  
[Teléfono / Email / Web]

---

*Documento generado para fines de presupuesto. No constituye contrato hasta su aceptación por escrito.*
