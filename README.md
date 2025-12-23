# Sistema POS Moon - Gestión de Ventas y Stock

Sistema de gestión de punto de venta (POS) desarrollado para Moon Desarrollos.

## Estructura del Sistema

```
├── ajax/              # Endpoints AJAX para operaciones del frontend
├── api/               # API REST para integraciones externas
├── controladores/      # Controladores MVC (lógica de negocio)
├── modelos/           # Modelos MVC (acceso a datos)
├── vistas/            # Vistas y frontend (HTML, CSS, JS)
├── db/                # Scripts SQL para base de datos
├── extensiones/       # Librerías externas (vendor)
├── cobro/             # Módulo de cobro
└── extras/            # Documentación y archivos adicionales
```

## Requisitos

- PHP 7.4 o superior
- MySQL 5.7 o superior
- Apache con mod_rewrite habilitado
- Composer (para dependencias)

## Instalación

1. Clonar el repositorio
2. Configurar la base de datos en `modelos/conexion.php`
3. Ejecutar los scripts SQL en `db/` según corresponda
4. Configurar permisos de escritura en carpetas necesarias
5. Acceder al sistema desde el navegador

## Módulos Principales

- **Ventas**: Gestión de ventas y facturación
- **Productos**: ABM de productos y combos
- **Clientes**: Gestión de clientes y cuenta corriente
- **Proveedores**: Gestión de proveedores
- **Cajas**: Control de cajas y cierres
- **Compras**: Gestión de compras
- **Presupuestos**: Generación de presupuestos
- **Integraciones**: Integración con Mercado Pago y otros servicios

## Configuración

### Base de Datos

Editar `modelos/conexion.php` con las credenciales de la base de datos.

### Mercado Pago

Configurar credenciales en: **Configuración de Empresa** → **Configuración de Mercado Pago**

## Documentación

La documentación completa se encuentra en `extras/documentacion-completa/`

## Soporte

Para más información, consultar la documentación en `extras/` o contactar al equipo de desarrollo.
