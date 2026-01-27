# 📊 Análisis del Modal de Cobro - Sistema de Usuarios

## Resumen de Apariciones del Modal `#modalCobro`

### Ubicaciones del Modal

El modal de cobro relacionado con usuarios aparece en **2 archivos principales**:

1. **`extras/cobro/cabezote.php`** (Sistema de cobro básico)
2. **`vistas/modulos/cabezote-mejorado.php`** (Sistema de cobro mejorado)

---

## 📍 Detalle por Archivo

### 1. `extras/cobro/cabezote.php`

#### Definición del Modal HTML
- **Línea 285**: `<div id="modalCobro" class="modal fade" role="dialog">`
- **Total: 1 vez**

#### Botones que Abren el Modal
- **Línea 220**: `<button class="btn btn-primary" data-toggle="modal" data-target="#modalCobro">`
- **Total: 1 vez**

#### Aperturas Automáticas del Modal (JavaScript)

**Modal Fijo (no se puede cerrar):**
- **Línea 378**: `$("#modalCobro").modal({backdrop: 'static', keyboard: false});`
- **Condición**: Cuando `$muestroModal && $fijoModal` son `true`
- **Total: 1 vez**

**Modal Normal (se puede cerrar):**
- **Línea 393**: `$("#modalCobro").modal();`
- **Condición**: Cuando `$muestroModal` es `true` pero `$fijoModal` es `false`
- **Límite**: Máximo 5 veces por día (controlado por localStorage)
- **Total: 1 vez**

---

### 2. `vistas/modulos/cabezote-mejorado.php`

#### Definición del Modal HTML
- **Línea 669**: `<div id="modalCobro" class="modal fade" role="dialog">`
- **Total: 1 vez**

#### Botones que Abren el Modal
- **Línea 282**: `<button class="btn btn-primary btn-block" data-toggle="modal" data-target="#modalCobro">`
- **Total: 1 vez**

#### Aperturas Automáticas del Modal (JavaScript)

**Modal Fijo (no se puede cerrar):**
- **Línea 1024**: `$("#modalCobro").modal({backdrop: 'static', keyboard: false});`
- **Condición**: Cuando `$muestroModal && $fijoModal` son `true`
- **Total: 1 vez**

**Modal Normal (se puede cerrar):**
- **Línea 1038**: `$("#modalCobro").modal();`
- **Condición**: Cuando `$muestroModal` es `true` pero `$fijoModal` es `false`
- **Límite**: Máximo 5 veces por día (controlado por localStorage)
- **Total: 1 vez**

---

## 📊 Resumen Total

### Por Tipo de Aparición:

| Tipo | Cantidad | Ubicaciones |
|------|----------|-------------|
| **Definiciones HTML del modal** | **2** | `extras/cobro/cabezote.php` (línea 285)<br>`vistas/modulos/cabezote-mejorado.php` (línea 669) |
| **Botones que abren el modal** | **2** | `extras/cobro/cabezote.php` (línea 220)<br>`vistas/modulos/cabezote-mejorado.php` (línea 282) |
| **Aperturas automáticas (modal fijo)** | **2** | `extras/cobro/cabezote.php` (línea 378)<br>`vistas/modulos/cabezote-mejorado.php` (línea 1024) |
| **Aperturas automáticas (modal normal)** | **2** | `extras/cobro/cabezote.php` (línea 393)<br>`vistas/modulos/cabezote-mejorado.php` (línea 1038) |

### Total General: **8 apariciones**

---

## 🔍 Condiciones para Mostrar el Modal

### Modal Fijo (No se puede cerrar):
- **Cliente bloqueado** (`estado_bloqueo == "1"`)
- **Día actual > 26** (sistema suspendido)

### Modal Normal (Se puede cerrar):
- **Día actual entre 5 y 9**: Recordatorio de abono mensual
- **Día actual entre 10 y 21**: Recordatorio con 10% de interés
- **Día actual entre 21 y 26**: Advertencia con 15% de interés y días restantes

### Límite de Apariciones:
- **Máximo 5 veces por día** (controlado por `localStorage.getItem('modalCobroMostrado')`)
- Después de 5 veces, no se muestra hasta el día siguiente

---

## 📝 Notas Importantes

1. **Solo uno de los archivos se usa a la vez**:
   - Si el sistema usa `cabezote-mejorado.php`, NO usa `extras/cobro/cabezote.php`
   - Por lo tanto, en la práctica, el modal aparece **4 veces** (2 definiciones + 2 aperturas)

2. **El modal muestra información del usuario**:
   - Nombre del cliente: `$clienteMoon["nombre"]`
   - Servicio: `$ctaCteMov["descripcion"]`
   - Precio: `$abonoMensual` (con intereses aplicados si corresponde)

3. **Control de apariciones**:
   - Usa `localStorage` para controlar cuántas veces se muestra por día
   - Resetea el contador cada día nuevo

---

## 🎯 Respuesta Directa

**¿Cuántas veces aparece el modal relacionado con usuarios en el sistema de cobro?**

- **Definiciones del modal**: **2 veces** (una en cada archivo)
- **Aperturas automáticas**: **4 veces** (2 modal fijo + 2 modal normal)
- **Botones manuales**: **2 veces** (uno en cada archivo)

**Total de apariciones en el código**: **8 veces**

**En la práctica (solo uno de los archivos se usa)**: **4 veces** (1 definición + 1 botón + 2 aperturas automáticas)

---

**Fecha de análisis**: Enero 2025
