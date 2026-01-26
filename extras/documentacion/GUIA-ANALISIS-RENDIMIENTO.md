# Guía de Análisis de Rendimiento

## 🔍 Herramientas para Analizar el Rendimiento

### 1. Chrome DevTools (Recomendado - Gratis)

#### Performance Tab
1. Abre Chrome DevTools (F12)
2. Ve a la pestaña **Performance**
3. Haz clic en el botón **Record** (círculo rojo)
4. Recarga la página
5. Detén la grabación
6. Analiza:
   - **Main Thread**: Ver qué funciones consumen más tiempo
   - **Network**: Ver qué recursos tardan en cargar
   - **Frames**: Ver si hay problemas de renderizado

#### Network Tab
1. Abre Chrome DevTools (F12)
2. Ve a la pestaña **Network**
3. Recarga la página
4. Analiza:
   - **Tiempo de respuesta** de cada recurso
   - **Tamaño** de cada archivo
   - **Tipo** de recurso (XHR, JS, CSS, etc.)
   - Busca recursos que tarden más de 1 segundo

#### Lighthouse Tab
1. Abre Chrome DevTools (F12)
2. Ve a la pestaña **Lighthouse**
3. Selecciona **Performance**
4. Haz clic en **Generate report**
5. Revisa:
   - **LCP** (Largest Contentful Paint): Debe ser < 2.5s
   - **FID** (First Input Delay): Debe ser < 100ms
   - **CLS** (Cumulative Layout Shift): Debe ser < 0.1
   - **TBT** (Total Blocking Time): Debe ser < 200ms

### 2. Script PHP de Análisis (Incluido)

Ejecuta el script `analizar-rendimiento.php`:

```bash
php analizar-rendimiento.php
```

O accede desde el navegador:
```
https://tudominio.com/analizar-rendimiento.php
```

Este script analiza:
- Consultas a base de datos
- Problemas N+1
- Uso de memoria
- Tiempo de ejecución

### 3. Análisis de Logs del Servidor

#### PHP Error Log
Revisa el archivo `error_log` en la raíz del proyecto:
```bash
tail -f error_log
```

Busca:
- Errores de PHP
- Warnings sobre memoria
- Timeouts

#### MySQL Slow Query Log
Si está habilitado, revisa las consultas lentas:
```sql
SHOW VARIABLES LIKE 'slow_query_log%';
```

### 4. Herramientas Online

#### GTmetrix
- URL: https://gtmetrix.com
- Gratis (con límites)
- Analiza velocidad, tamaño de página, requests
- Proporciona recomendaciones específicas

#### WebPageTest
- URL: https://www.webpagetest.org
- Gratis
- Prueba desde diferentes ubicaciones
- Muestra waterfall de recursos

#### Google PageSpeed Insights
- URL: https://pagespeed.web.dev
- Gratis
- Analiza rendimiento móvil y desktop
- Proporciona puntuación y recomendaciones

## 📊 Métricas Clave a Revisar

### Frontend (Cliente)
- **LCP** (Largest Contentful Paint): < 2.5s
- **FID** (First Input Delay): < 100ms
- **CLS** (Cumulative Layout Shift): < 0.1
- **TBT** (Total Blocking Time): < 200ms
- **TTI** (Time to Interactive): < 3.8s

### Backend (Servidor)
- **Tiempo de respuesta PHP**: < 500ms
- **Consultas a BD**: < 100ms cada una
- **Memoria usada**: < 128MB
- **Número de consultas**: Minimizar (evitar N+1)

## 🔧 Problemas Comunes y Soluciones

### 1. Consultas N+1
**Síntoma**: Muchas consultas a la base de datos
**Solución**: Usar JOINs en lugar de consultas individuales

### 2. Archivos JavaScript/CSS Grandes
**Síntoma**: Tiempo de descarga largo
**Solución**: Minificar y comprimir archivos

### 3. Imágenes Sin Optimizar
**Síntoma**: Tamaño de página grande
**Solución**: Comprimir imágenes, usar formatos modernos (WebP)

### 4. Sin Caché
**Síntoma**: Recursos se descargan cada vez
**Solución**: Configurar headers de caché

### 5. Consultas Sin Índices
**Síntoma**: Consultas lentas
**Solución**: Agregar índices a columnas frecuentemente consultadas

## 📝 Checklist de Análisis

- [ ] Ejecutar Lighthouse y revisar puntuación
- [ ] Revisar Network tab para recursos lentos
- [ ] Ejecutar script PHP de análisis
- [ ] Revisar logs de errores
- [ ] Verificar consultas N+1
- [ ] Revisar uso de memoria
- [ ] Analizar tiempo de respuesta del servidor
- [ ] Verificar tamaño de archivos JS/CSS
- [ ] Revisar imágenes sin optimizar
- [ ] Verificar configuración de caché

## 🚀 Próximos Pasos

1. Ejecuta el script `analizar-rendimiento.php`
2. Revisa Chrome DevTools Performance tab
3. Ejecuta Lighthouse y revisa las recomendaciones
4. Comparte los resultados para identificar el problema específico
