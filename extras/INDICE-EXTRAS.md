# Índice de Extras

Este directorio contiene archivos y documentación que no son parte del sistema principal pero pueden ser útiles para referencia, desarrollo o mantenimiento.

## Estructura

### 📁 wiki/
Documentación para el usuario final (formato GitHub Wiki):
- Home, Inicio de sesión, Empresa, Productos, Ventas, Cajas, Clientes, Compras, Proveedores
- Integraciones y cobro, Reportes, Glosario, Crear venta paso a paso

### 📁 config-templates/
Plantillas de configuración:
- `.env.example` — Copiar a la raíz como `.env` y completar variables

### 📁 documentacion/
Guías rápidas y documentos específicos:
- `README-PROYECTO.md` - Descripción completa del proyecto (antes README en raíz)
- `COMO-OBTENER-EXTERNAL-ID-POS.md` - Guía para obtener el External ID del POS de Mercado Pago
- `INSTRUCCIONES-LOGO-LOGIN.md` - Instrucciones para configurar logo en login
- `PASOS-ACTUALIZACION-HOSTING.md` - Pasos para actualizar en hosting

### 📁 documentacion-completa/
Documentación completa del proyecto:
- Guías de instalación y configuración
- Documentación de integraciones (Mercado Pago, n8n, etc.)
- Changelog y resúmenes de actualizaciones
- Scripts de Python para automatización
- Documentación de instalación de módulo de cobro

### 📁 scripts/
Scripts de utilidad y despliegue:
- `setup.sh` - Script de instalación del sistema (Ubuntu)
- `verificar-combos.sh` - Verificación de módulo de combos
- `actualizar-servidor.sh` - Actualización del servidor
- `configurar-servidor.sh` - Configuración del servidor
- `sincronizar-hosting.sh` - Sincronización con hosting
- `analizar-rendimiento.php` - Análisis de rendimiento (ejecutar con PHP CLI)

### 📁 logs/
Copia de logs movidos desde la raíz (p. ej. `error_log`). Ver README en la carpeta.

### 📁 flujos-n8n/
Configuraciones de workflows para n8n:
- Flujos multiagente
- Asistente SQL dinámico

### 📁 mejoras/
Scripts de migración y mejoras ya aplicadas:
- Scripts de migración de passwords
- Otros scripts de mejoras

### 📁 pos-offline-moon/
Sistema POS offline desarrollado en Python. Es la única versión vigente:
- Aplicación desktop independiente
- Documentación de instalación y uso
- Scripts de configuración y de servicio systemd

## Notas

- Estos archivos no son necesarios para el funcionamiento del sistema principal
- Se mantienen aquí para referencia histórica y desarrollo futuro
- La documentación puede contener información desactualizada
