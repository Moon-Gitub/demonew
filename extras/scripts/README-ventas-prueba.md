# Generar ventas de prueba

Script para crear ventas de prueba con datos variados (empresas, medios de pago, puntos de venta, etc.).

## Uso

**Desde terminal:**
```bash
cd /ruta/al/proyecto/demonew
php extras/scripts/generar-ventas-prueba.php 20
```
El número `20` es la cantidad de ventas a generar (por defecto 15, máximo 100).

**Desde navegador:**
```
https://tu-dominio.com/extras/scripts/generar-ventas-prueba.php?cantidad=20
```

## Requisitos

- Archivo `.env` configurado con `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`
- Al menos un cliente en la tabla `clientes`
- Al menos una empresa en la tabla `empresa` (si la tabla ventas tiene columna `id_empresa`)

## Datos generados

- **Empresas:** Se reparten entre todas las empresas existentes
- **Medios de pago:** Efectivo, MP-C, MP-D, Transferencia, Tarjeta Débito, Tarjeta Crédito, Cheque
- **Tipos de comprobante:** X (0), Factura B (6), Factura C (11)
- **Puntos de venta:** 1 y 2
- **Estados:** Pagado y Adeudado
- **Fechas:** Últimos 30 días
- **Totales:** Entre $500 y $50.000 (aleatorio)
