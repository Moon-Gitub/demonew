# Instrucciones para el Logo en el Login

## 📋 Especificaciones de la Imagen

Para que el logo se vea perfectamente en la pantalla de login, necesitas una imagen con las siguientes características:

### ✅ Tamaño Recomendado
- **Ancho:** 720 píxeles (o múltiplos: 1440px, 2160px para alta resolución)
- **Alto:** 247 píxeles (o proporcional según el ancho)
- **Relación de aspecto:** 2.9:1 (ancho:alto)

### ✅ Formato
- **PNG con fondo transparente** (recomendado) O
- **PNG con fondo sólido** del color `#5F738E` (azul grisáceo)

### ✅ Ubicación del Archivo
Coloca la imagen en:
```
vistas/img/plantilla/logo-moon-desarrollos.png
```

### ✅ Nombre del Archivo
El archivo debe llamarse exactamente:
```
logo-moon-desarrollos.png
```

## 🎨 Colores

El fondo de la pantalla de login es: **#5F738E** (azul grisáceo)

Si tu logo tiene fondo transparente, se verá perfectamente sobre este color.
Si tu logo tiene fondo sólido, debe ser exactamente **#5F738E** para que coincida.

## 📱 Responsive

El logo se ajusta automáticamente a diferentes tamaños de pantalla:
- **Desktop:** máximo 400px de ancho
- **Tablet:** máximo 350px de ancho
- **Móvil:** máximo 280px de ancho
- **Móvil pequeño:** máximo 240px de ancho
- **Landscape:** máximo 250px de ancho

## 🔧 Si la Imagen no Aparece

1. Verifica que el archivo existe en `vistas/img/plantilla/logo-moon-desarrollos.png`
2. Verifica los permisos del archivo (debe ser legible)
3. Verifica que la ruta en el código sea correcta
4. Limpia la caché del navegador (Ctrl+Shift+R o Cmd+Shift+R)

## 📝 Notas

- El logo se muestra superpuesto sobre el fondo de la página
- Tiene un efecto de sombra sutil para darle profundidad
- Al pasar el mouse, se eleva ligeramente (efecto hover)
- La imagen mantiene su proporción original en todos los tamaños
