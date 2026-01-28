# Tabla listas_precio – Listas de precio con ABM

## Qué es

La tabla `listas_precio` permite definir listas de precio por empresa (base de precio + descuento opcional) y asignarlas a usuarios. En **Crear venta**, el usuario elige una lista y el precio se calcula según esa configuración, sin hardcodear en código.

## Crear la tabla

Si la tabla aún no existe, ejecutá el script:

```bash
mysql -u USUARIO -p NOMBRE_BD < db/crear-tabla-listas-precio.sql
```

O desde phpMyAdmin: importar o pegar el contenido de `db/crear-tabla-listas-precio.sql`.

## Estructura

- **id_empresa**: empresa a la que pertenece la lista.
- **codigo**: clave única (ej. `precio_venta`, `empleados`, `trabajadores_valle_grande`). Es el valor que se guarda en el usuario y se usa en el dropdown "Listas $".
- **nombre**: texto visible (ej. "Precio Público", "Empleados").
- **base_precio**: columna del producto que se usa como base (`precio_venta` o `precio_compra`).
- **tipo_descuento**: `ninguno` o `porcentaje`.
- **valor_descuento**: número (porcentaje 0–100 si tipo es `porcentaje`).
- **orden**, **activo**: orden en listados y si está activa.

## Uso en el sistema

1. **Empresa → Listas de precio**: ABM para crear/editar/desactivar listas.
2. **Empresa → Usuarios**: en cada usuario se asignan las listas que puede usar (checkboxes).
3. **Crear venta**: el dropdown "Listas $" muestra solo las listas asignadas al usuario; el precio del producto se calcula con la lista elegida (base + descuento si corresponde).

Si la tabla no existe, el sistema sigue usando la configuración de `parametros.php` (fallback).
