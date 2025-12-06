# ✅ Checklist de Pruebas Bootstrap 5

**Fecha de actualización:** $(date)  
**Versión Bootstrap:** 5.3.2  
**Shim de compatibilidad:** Activo

---

## 📋 Módulos a Probar

### Módulos Principales
- [ ] **Login** - Formulario de ingreso
- [ ] **Dashboard/Inicio** - Página principal
- [ ] **Usuarios** - CRUD de usuarios
- [ ] **Productos** - CRUD de productos
- [ ] **Ventas** - Crear y editar ventas
- [ ] **Compras** - Crear y editar compras
- [ ] **Clientes** - CRUD de clientes
- [ ] **Proveedores** - CRUD de proveedores
- [ ] **Cajas** - Gestión de cajas
- [ ] **Reportes** - Visualización de reportes

---

## 🔍 Funcionalidades a Verificar

### Modales
- [ ] Abrir modal con botón
- [ ] Cerrar modal con botón X
- [ ] Cerrar modal con botón "Cerrar"
- [ ] Cerrar modal haciendo click fuera (backdrop)
- [ ] Modal responsive en móvil
- [ ] Modal con formularios funciona
- [ ] Modal con DataTables funciona

### Dropdowns
- [ ] Abrir dropdown en navbar
- [ ] Cerrar dropdown
- [ ] Dropdown en tablas
- [ ] Dropdown con submenús
- [ ] Dropdown responsive

### Formularios
- [ ] Inputs se ven correctamente
- [ ] Labels alineados correctamente
- [ ] Botones funcionan
- [ ] Validaciones visuales funcionan
- [ ] Input groups funcionan
- [ ] Selects funcionan
- [ ] Checkboxes y radios funcionan

### Tablas (DataTables)
- [ ] Tabla se renderiza correctamente
- [ ] Paginación funciona
- [ ] Búsqueda funciona
- [ ] Ordenamiento funciona
- [ ] Responsive funciona
- [ ] Botones de exportar funcionan
- [ ] Selección de filas funciona

### Componentes Bootstrap
- [ ] **Tabs** - Navegación por pestañas
- [ ] **Collapse** - Acordeones
- [ ] **Tooltips** - Información al hover
- [ ] **Popovers** - Información emergente
- [ ] **Alerts** - Mensajes de alerta
- [ ] **Badges** - Etiquetas
- [ ] **Breadcrumbs** - Migas de pan
- [ ] **Pagination** - Paginación

### Grid System (Responsive)
- [ ] Desktop (1920px) - Todo se ve bien
- [ ] Laptop (1366px) - Layout correcto
- [ ] Tablet (768px) - Responsive funciona
- [ ] Móvil (375px) - Todo accesible
- [ ] Columnas col-xs-* funcionan
- [ ] Columnas col-sm-* funcionan
- [ ] Columnas col-md-* funcionan
- [ ] Columnas col-lg-* funcionan

### JavaScript
- [ ] SweetAlert funciona
- [ ] AJAX funciona correctamente
- [ ] Eventos se disparan correctamente
- [ ] No hay errores en consola del navegador
- [ ] jQuery funciona correctamente
- [ ] AdminLTE funciona correctamente

---

## 🌐 Navegadores a Probar

- [ ] **Chrome/Edge** (última versión)
- [ ] **Firefox** (última versión)
- [ ] **Safari** (si tienes Mac)
- [ ] **Chrome Android** (móvil)
- [ ] **Safari iOS** (iPhone/iPad)

---

## 🔧 Verificaciones Técnicas

### Consola del Navegador
- [ ] No hay errores JavaScript
- [ ] No hay errores CSS
- [ ] No hay warnings de Bootstrap
- [ ] Shim se carga correctamente

### Network (Red)
- [ ] Bootstrap 5 se carga desde CDN
- [ ] Shim se carga correctamente
- [ ] CSS de compatibilidad se carga
- [ ] No hay recursos 404

### Performance
- [ ] Página carga rápido
- [ ] No hay bloqueos de renderizado
- [ ] Animaciones suaves

---

## 📝 Notas de Pruebas

**Probar en:** [Fecha]  
**Probado por:** [Nombre]  
**Resultado general:** [ ] ✅ Todo OK | [ ] ⚠️ Problemas menores | [ ] ❌ Problemas críticos

### Problemas Encontrados:

1. 
2. 
3. 

### Soluciones Aplicadas:

1. 
2. 
3. 

---

## 🚨 Rollback (Si es necesario)

Si encuentras problemas críticos, puedes revertir fácilmente:

```bash
# Opción 1: Revertir a Bootstrap 3.4.1
git checkout backups/bootstrap-update/20251206-163527/plantilla.php vistas/plantilla.php
cp backups/bootstrap-update/20251206-163527/bootstrap/dist/css/bootstrap.min.css \
   vistas/bower_components/bootstrap/dist/css/bootstrap.min.css
cp backups/bootstrap-update/20251206-163527/bootstrap/dist/js/bootstrap.min.js \
   vistas/bower_components/bootstrap/dist/js/bootstrap.min.js

# Opción 2: Revertir commit completo
git reset --hard HEAD~1
```

---

## ✅ Firmas

**Probado y aprobado por:**  
**Fecha:**  
**Versión Bootstrap:** 5.3.2

