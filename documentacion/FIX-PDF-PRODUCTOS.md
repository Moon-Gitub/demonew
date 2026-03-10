# Fix: Error "Resolve configuration errors: savePath" en Administrar productos

## Problema
Al abrir Administrar productos aparecía un modal bloqueante: **"Resolve configuration errors - You must resolve all errors before continuing: savePath"**, impidiendo usar la tabla correctamente.

## Causa probable
El error se producía por la exportación a PDF de DataTables (botón pdfHtml5), que usa **pdfmake** y **vfs_fonts.js**:

1. **Rutas relativas en Hostinger**: Tras la migración, la app puede estar en una subcarpeta (`/public_html/`, `/demonew/`, etc.). Los scripts locales `vistas/bower_components/.../vfs_fonts.js` (archivo ~1MB) podían no cargarse bien por rutas relativas incorrectas.

2. **vfs_fonts.js muy grande**: El archivo local tiene ~955KB. En conexiones lentas o con caché corrupta, puede fallar la carga y quedar `pdfMake.vfs` vacío o indefinido.

3. **Validación de "savePath"**: Cuando pdfmake intenta generar el PDF, necesita el sistema de fuentes virtual (vfs). Si `pdfMake.vfs` no está definido correctamente, la librería o el navegador pueden mostrar errores de configuración relacionados con la ruta de guardado.

## Solución aplicada
Se reemplazaron los scripts locales de pdfmake por versiones desde **CDN** (cdnjs.cloudflare.com):

- `pdfmake.min.js` v0.1.68
- `vfs_fonts.min.js` v0.1.68

**Ventajas del CDN:**
- Rutas absolutas: no dependen de la estructura de carpetas del proyecto
- Caché del navegador y CDN
- Versión estable y probada

## Archivos modificados
- `vistas/plantilla.php`: carga de pdfmake y vfs_fonts desde CDN
- `vistas/js/productos.js`: se restauró el botón PDF (había sido desactivado temporalmente)

## Si el error persiste
1. Revisar la consola del navegador (F12) para ver el error exacto
2. Comprobar que no hay bloqueo de scripts externos (extensiones, firewall)
3. Si se trabaja offline, considerar volver a los scripts locales y verificar que las rutas sean correctas para el entorno de despliegue
