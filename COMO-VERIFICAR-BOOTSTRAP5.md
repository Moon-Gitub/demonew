# 🔍 Cómo Verificar que Bootstrap 5 está Activo

## Método 1: Verificar en el Código Fuente del Navegador

1. **Abre el sistema en el navegador**
2. **Presiona F12** (o clic derecho → Inspeccionar)
3. **Ve a la pestaña "Network" (Red)**
4. **Recarga la página (F5)**
5. **Busca "bootstrap" en los archivos cargados**

Deberías ver:
- ✅ `bootstrap@5.3.2/dist/css/bootstrap.min.css` (desde CDN)
- ✅ `bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js` (desde CDN)
- ✅ `bootstrap3-to-5-shim.js` (archivo local)
- ✅ `bootstrap-compat.css` (archivo local)

## Método 2: Verificar en la Consola del Navegador

1. **Abre la consola (F12 → Console)**
2. **Escribe y presiona Enter:**

```javascript
// Verificar versión de Bootstrap
console.log(bootstrap.Tooltip.VERSION);
```

**Resultado esperado:** Debería mostrar `"5.3.2"`

## Método 3: Verificar en Elements/Inspector

1. **Abre el inspector (F12 → Elements)**
2. **Busca en el `<head>` las etiquetas:**

```html
<!-- Deberías ver esto: -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" ...>
<link rel="stylesheet" href="vistas/css/bootstrap-compat.css">

<!-- Y en el body, antes de cerrar: -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" ...></script>
<script src="vistas/js/bootstrap3-to-5-shim.js"></script>
```

## Método 4: Verificar que el Shim está Funcionando

1. **Abre la consola (F12 → Console)**
2. **Deberías ver este mensaje:**

```
[Bootstrap Shim] Inicializando compatibilidad Bootstrap 3 → 5
[Bootstrap Shim] Clases mapeadas correctamente
[Bootstrap Shim] Observer de DOM configurado
[Bootstrap Shim] Compatibilidad JavaScript configurada
```

## Método 5: Probar Funcionalidad

### Probar Modal:
1. Busca un botón que abra un modal
2. Haz clic
3. **Si funciona = Bootstrap 5 está activo**

### Probar Dropdown:
1. Busca un dropdown en el navbar
2. Haz clic
3. **Si se abre = Bootstrap 5 está activo**

### Verificar en Consola:
- **NO debe haber errores** relacionados con Bootstrap
- Si ves errores como "bootstrap is not defined" = algo falló

## Método 6: Verificar Archivos en el Servidor

```bash
# Verificar que los archivos existen
ls -lh vistas/js/bootstrap3-to-5-shim.js
ls -lh vistas/css/bootstrap-compat.css

# Verificar contenido de plantilla.php
grep "Bootstrap 5" vistas/plantilla.php
grep "bootstrap3-to-5-shim" vistas/plantilla.php
```

## ⚠️ Si NO ves Bootstrap 5:

### Posibles causas:

1. **Caché del navegador:**
   - Presiona **Ctrl + Shift + R** (o Cmd + Shift + R en Mac)
   - O limpia caché: F12 → Network → "Disable cache"

2. **Archivos no se cargaron:**
   - Verifica que los archivos existen en el servidor
   - Verifica permisos de lectura

3. **CDN bloqueado:**
   - Verifica conexión a internet
   - Verifica que jsdelivr.net no esté bloqueado

4. **Rama incorrecta:**
   - Verifica que estás en la rama `bootstrap-update-safe`
   - O que los cambios se mergearon a `main`

## ✅ Checklist Rápido:

- [ ] Archivo `vistas/js/bootstrap3-to-5-shim.js` existe
- [ ] Archivo `vistas/css/bootstrap-compat.css` existe
- [ ] `plantilla.php` tiene referencia a Bootstrap 5.3.2
- [ ] `plantilla.php` carga el shim
- [ ] Consola del navegador muestra mensajes del shim
- [ ] Modales funcionan
- [ ] Dropdowns funcionan
- [ ] No hay errores en consola

## 🔧 Comando para Verificar Todo:

```bash
cd /home/cluna/Documentos/7-Moon-Desarrollos/demonew/demonew

echo "=== VERIFICACIÓN BOOTSTRAP 5 ==="
echo ""
echo "1. Archivo shim:"
test -f vistas/js/bootstrap3-to-5-shim.js && echo "✅ Existe" || echo "❌ No existe"
echo ""
echo "2. Archivo CSS compat:"
test -f vistas/css/bootstrap-compat.css && echo "✅ Existe" || echo "❌ No existe"
echo ""
echo "3. Bootstrap 5 en plantilla.php:"
grep -q "bootstrap@5.3.2" vistas/plantilla.php && echo "✅ Encontrado" || echo "❌ No encontrado"
echo ""
echo "4. Shim en plantilla.php:"
grep -q "bootstrap3-to-5-shim" vistas/plantilla.php && echo "✅ Encontrado" || echo "❌ No encontrado"
echo ""
echo "=== FIN VERIFICACIÓN ==="
```

