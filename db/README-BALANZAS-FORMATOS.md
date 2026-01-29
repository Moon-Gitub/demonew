# Sistema de Formatos de Balanza (`balanzas_formatos`)

## 📋 Índice

1. [¿Qué resuelve?](#qué-resuelve)
2. [Instalación](#instalación)
3. [Estructura de la tabla](#estructura-de-la-tabla)
4. [Ejemplos prácticos](#ejemplos-prácticos)
5. [Flujo completo del sistema](#flujo-completo-del-sistema)
6. [ABM (Alta/Baja/Modificación)](#abm-altabajamodificación)
7. [Casos de uso comunes](#casos-de-uso-comunes)
8. [Troubleshooting](#troubleshooting)

---

## ¿Qué resuelve?

Este sistema permite definir **cómo se interpreta un código de balanza digital** sin tener que modificar código JavaScript hardcodeado.

**Antes:** Cada nueva balanza requería editar `venta-caja.js` con lógica específica (`if (codigo.startsWith('20')) { ... }`).

**Ahora:** Se configura desde la base de datos mediante un ABM, y el sistema interpreta los códigos de forma genérica.

### Ventajas

- ✅ **Sin tocar código**: Agregar nuevas balanzas solo requiere crear un registro en la BD
- ✅ **Configuración centralizada**: Todos los formatos en un solo lugar
- ✅ **Mantenimiento simple**: Activar/desactivar formatos sin deploy
- ✅ **Multi-empresa**: Cada empresa puede tener sus propios formatos

---

## Instalación

### Paso 1: Crear la tabla

Ejecutar el script SQL:

```bash
mysql -u USUARIO -p NOMBRE_BD < db/crear-tabla-balanzas-formatos.sql
```

O desde phpMyAdmin, copiar y pegar el contenido de `db/crear-tabla-balanzas-formatos.sql`.

### Paso 2: Verificar que se crearon los formatos iniciales

El script incluye 3 formatos de ejemplo que replican la lógica anterior:

```sql
SELECT * FROM balanzas_formatos WHERE id_empresa = 1 AND activo = 1;
```

Deberías ver 3 registros con prefijos `20000`, `20` y `21`.

---

## Estructura de la tabla

### Campos principales

| Campo | Tipo | Descripción | Ejemplo |
|-------|------|-------------|---------|
| `id` | INT | ID único del formato | `1` |
| `id_empresa` | INT | Empresa a la que aplica | `1` |
| `nombre` | VARCHAR(128) | Descripción legible | `"Balanza 20000 (peso en kg)"` |
| `prefijo` | VARCHAR(32) | Texto que debe estar al inicio del código | `"20000"`, `"20"`, `"21"` |
| `longitud_min` | INT | Longitud mínima del código (opcional) | `12` |
| `longitud_max` | INT | Longitud máxima del código (opcional) | `20` |
| `pos_producto` | INT | Posición inicial (base 0) del ID de producto | `5` |
| `longitud_producto` | INT | Cantidad de caracteres del ID de producto | `2` |
| `modo_cantidad` | VARCHAR(16) | Tipo de cantidad: `peso`, `unidad`, `ninguno` | `"peso"` |
| `pos_cantidad` | INT | Posición inicial del campo peso (si `modo_cantidad = 'peso'`) | `7` |
| `longitud_cantidad` | INT | Longitud del campo peso (si `modo_cantidad = 'peso'`) | `5` |
| `factor_divisor` | DECIMAL(10,4) | Divisor para convertir unidades (ej: gramos→kg) | `1000.0000` |
| `cantidad_fija` | DECIMAL(10,3) | Cantidad fija cuando `modo_cantidad = 'unidad'` | `1.000` |
| `orden` | INT | Prioridad cuando múltiples formatos matchean | `10` |
| `activo` | TINYINT(1) | `1` = activo, `0` = desactivado | `1` |

### Modos de cantidad

#### 1. `peso`
La cantidad se lee del código y se divide por `factor_divisor`.

**Ejemplo:** Código `2000005001250`
- Peso en gramos: `01250` (posiciones 7-11)
- Factor divisor: `1000`
- Resultado: `12.50` kg

#### 2. `unidad`
La cantidad es siempre `cantidad_fija` (típicamente `1`).

**Ejemplo:** Código `21000799999`
- Cantidad fija: `1`
- Resultado: `1` unidad

#### 3. `ninguno`
Se usa la cantidad ingresada manualmente por el usuario.

**Ejemplo:** Código `0001235`
- El usuario ingresa cantidad: `5`
- Resultado: `5` unidades

> **⚠️ Importante:**  
> Las posiciones son **base 0** (el primer carácter está en la posición 0), igual que `substr()` en JavaScript.

---

## Ejemplos prácticos

### Ejemplo 1: Código `2000005001250` (Balanza 20000)

**Configuración:**
- Prefijo: `20000`
- Posición producto: `5`, Longitud: `2`
- Modo cantidad: `peso`
- Posición cantidad: `7`, Longitud: `5`
- Factor divisor: `1000`

**Interpretación:**
```
Código: 2000005001250
         ||||| || |||||
         ||||| || |||||-- Peso: 01250 gramos = 12.50 kg
         ||||| ||-- ID producto: 05
         |||||-- Prefijo: 20000
```

**Resultado:**
- `idProducto`: `"05"`
- `cantidad`: `12.50` kg

---

### Ejemplo 2: Código `200006012503` (Balanza 20 genérica)

**Configuración:**
- Prefijo: `20` (pero no `20000`)
- Posición producto: `4`, Longitud: `2`
- Modo cantidad: `peso`
- Posición cantidad: `7`, Longitud: `5`
- Factor divisor: `1000`

**Interpretación:**
```
Código: 200006012503
         || || |||||
         || || |||||-- Peso: 01250 gramos = 12.50 kg
         || ||-- ID producto: 06
         ||-- Prefijo: 20
```

**Resultado:**
- `idProducto`: `"06"`
- `cantidad`: `12.50` kg

> **Nota:** El sistema elige el formato con prefijo más largo cuando hay coincidencias. `20000` tiene prioridad sobre `20` para códigos que empiezan con `20000`.

---

### Ejemplo 3: Código `21000799999` (Balanza 21 - unidad fija)

**Configuración:**
- Prefijo: `21`
- Posición producto: `4`, Longitud: `2`
- Modo cantidad: `unidad`
- Cantidad fija: `1`

**Interpretación:**
```
Código: 21000799999
         || |||||||
         || |||||||-- (ignorado, cantidad fija)
         ||-- ID producto: 07
         -- Prefijo: 21
```

**Resultado:**
- `idProducto`: `"07"`
- `cantidad`: `1` unidad (fija)

---

## Flujo completo del sistema

### 1. Carga de configuración (PHP → JavaScript)

En `vistas/modulos/crear-venta-caja.php`:

```php
// Cargar formatos activos de la empresa
$balanzasFormatosConfig = [];
if (class_exists('ModeloBalanzasFormatos') && ModeloBalanzasFormatos::tablaExiste()) {
    $balanzasFormatosConfig = ModeloBalanzasFormatos::mdlConfigParaVenta();
}
```

```javascript
// Inyectar configuración en JavaScript
var balanzasFormatosConfig = <?php echo json_encode($balanzasFormatosConfig); ?>;
```

**Ejemplo de `balanzasFormatosConfig`:**
```javascript
[
  {
    "id": 1,
    "prefijo": "20000",
    "longitud_min": 12,
    "longitud_max": 20,
    "pos_producto": 5,
    "longitud_producto": 2,
    "modo_cantidad": "peso",
    "pos_cantidad": 7,
    "longitud_cantidad": 5,
    "factor_divisor": 1000,
    "cantidad_fija": 1
  },
  // ... más formatos
]
```

### 2. Interpretación del código (JavaScript)

En `vistas/js/venta-caja.js`, función `interpretarCodigoBalanza()`:

```javascript
function interpretarCodigoBalanza(codigo, cantidadManual) {
    // 1. Validar que existe configuración
    if (!balanzasFormatosConfig || !balanzasFormatosConfig.length) {
        return null;
    }

    // 2. Buscar formato que coincida con el prefijo
    var mejor = null;
    for (var i = 0; i < balanzasFormatosConfig.length; i++) {
        var cfg = balanzasFormatosConfig[i];
        if (codigo.indexOf(cfg.prefijo) === 0) {
            // Validar longitudes si están definidas
            if (cfg.longitud_min && codigo.length < cfg.longitud_min) continue;
            if (cfg.longitud_max && codigo.length > cfg.longitud_max) continue;
            
            // Elegir el prefijo más largo (más específico)
            if (!mejor || mejor.prefijo.length < cfg.prefijo.length) {
                mejor = cfg;
            }
        }
    }

    if (!mejor) return null;

    // 3. Extraer ID de producto
    var idProducto = codigo.substr(mejor.pos_producto, mejor.longitud_producto);

    // 4. Calcular cantidad según modo
    var cantidad = 0;
    if (mejor.modo_cantidad === 'peso') {
        var bruto = codigo.substr(mejor.pos_cantidad, mejor.longitud_cantidad);
        cantidad = parseFloat(bruto) / mejor.factor_divisor;
    } else if (mejor.modo_cantidad === 'unidad') {
        cantidad = mejor.cantidad_fija;
    } else {
        cantidad = parseFloat(cantidadManual) || 1;
    }

    return { idProducto: idProducto, cantidad: cantidad };
}
```

### 3. Integración en agregar producto

En `vistas/js/venta-caja.js`, función `agregarProductoListaCompra()`:

```javascript
var idProductoDos = $("#ventaCajaDetalle").val(); // Código escaneado/ingresado
var cantidadDos = $("#ventaCajaCantidad").val();  // Cantidad manual

var idProducto = idProductoDos;
var cantidad = cantidadDos;

// Intentar interpretar como código de balanza
var parsedBalanza = interpretarCodigoBalanza(idProductoDos, cantidadDos);
if (parsedBalanza && parsedBalanza.idProducto) {
    idProducto = parsedBalanza.idProducto;
    cantidad = parsedBalanza.cantidad;
}

// Continuar con el flujo normal usando idProducto y cantidad
```

---

## ABM (Alta/Baja/Modificación)

### Acceso al ABM

1. Iniciar sesión como **Administrador**
2. Ir a: **Empresa → Formatos de balanza**
3. URL directa: `index.php?ruta=balanzas-formatos`

### Crear un nuevo formato

1. Clic en **"Agregar formato de balanza"**
2. Completar el formulario:
   - **Nombre**: Descripción legible (ej: "Balanza marca X modelo Y")
   - **Prefijo**: Inicio del código (ej: `22`, `30000`)
   - **Longitud mín/máx**: Opcional, para validar tamaño del código
   - **Posición producto**: Dónde empieza el ID (base 0)
   - **Longitud producto**: Cuántos caracteres tiene el ID
   - **Modo cantidad**: `peso`, `unidad` o `ninguno`
   - **Si es peso**: Posición cantidad, longitud cantidad, factor divisor
   - **Si es unidad**: Cantidad fija
   - **Orden**: Prioridad (menor = más prioritario)
   - **Activo**: Checkbox para habilitar/deshabilitar

3. Clic en **"Guardar"**

### Editar un formato existente

1. Clic en el botón **✏️ (lápiz)** de la fila correspondiente
2. Modificar los campos necesarios
3. Clic en **"Actualizar"**

### Desactivar un formato

1. Clic en el botón **❌ (X)** de la fila correspondiente
2. Confirmar la desactivación

> **Nota:** Desactivar no elimina el registro, solo lo marca como `activo = 0`. Se puede reactivar editando.

---

## Casos de uso comunes

### Caso 1: Agregar una nueva balanza con prefijo `22`

**Escenario:** Nueva balanza que genera códigos como `2200015002500` donde:
- Prefijo: `22`
- ID producto: posiciones 4-5 (`01`)
- Peso: posiciones 7-11 (`00250` gramos = 0.25 kg)

**Solución:**
1. Ir al ABM de Formatos de balanza
2. Crear nuevo formato:
   - Nombre: `"Balanza 22 (peso en kg)"`
   - Prefijo: `22`
   - Posición producto: `4`, Longitud: `2`
   - Modo cantidad: `peso`
   - Posición cantidad: `7`, Longitud: `5`
   - Factor divisor: `1000`
   - Orden: `40` (mayor que los existentes)
   - Activo: ✅

3. Guardar y probar escaneando `2200015002500`

---

### Caso 2: Cambiar el factor divisor de una balanza existente

**Escenario:** La balanza `20000` ahora envía el peso en **decigramos** en lugar de gramos.

**Solución:**
1. Ir al ABM
2. Editar el formato "Balanza 20000 (peso en kg)"
3. Cambiar **Factor divisor** de `1000` a `10000` (decigramos → kg)
4. Guardar

---

### Caso 3: Desactivar temporalmente un formato

**Escenario:** Una balanza está en reparación y no queremos que sus códigos se interpreten.

**Solución:**
1. Ir al ABM
2. Clic en **❌** del formato correspondiente
3. Confirmar

El formato queda desactivado pero no se elimina. Para reactivarlo, editarlo y marcar **Activo**.

---

## Troubleshooting

### ❌ El código no se interpreta correctamente

**Posibles causas:**

1. **El prefijo no coincide exactamente**
   - Verificar que el código realmente empiece con el prefijo configurado
   - Ejemplo: Si el código es `2000005001250` y el prefijo es `20`, debería funcionar, pero si hay espacios o caracteres especiales, no funcionará

2. **Las posiciones están mal configuradas**
   - Recordar que las posiciones son **base 0**
   - Ejemplo: Si el ID producto está en los caracteres 6-7 (contando desde 1), la posición base 0 es `5`

3. **La longitud del código no coincide**
   - Verificar `longitud_min` y `longitud_max` si están configuradas
   - Ejemplo: Si `longitud_min = 12` y el código tiene 11 caracteres, no se interpretará

4. **El formato está desactivado**
   - Verificar que `activo = 1` en la tabla o en el ABM

**Solución:**
- Revisar la consola del navegador (F12) para ver errores de JavaScript
- Verificar que `balanzasFormatosConfig` esté cargado correctamente:
  ```javascript
  console.log(balanzasFormatosConfig);
  ```
- Probar manualmente la función:
  ```javascript
  interpretarCodigoBalanza('2000005001250', '1');
  ```

---

### ❌ Múltiples formatos coinciden y se elige el incorrecto

**Causa:** Dos formatos tienen prefijos que coinciden (ej: `20` y `20000`).

**Solución:**
- El sistema automáticamente elige el prefijo **más largo** (más específico)
- Si aún así hay problemas, ajustar el campo `orden` (menor = más prioritario)
- O desactivar el formato menos específico si no se usa

---

### ❌ El ABM no aparece en el menú

**Causa:** No estás logueado como Administrador o la tabla no existe.

**Solución:**
1. Verificar que la tabla `balanzas_formatos` existe:
   ```sql
   SHOW TABLES LIKE 'balanzas_formatos';
   ```
2. Verificar que estás logueado como usuario con `perfil = 'Administrador'`
3. Verificar que el archivo `vistas/modulos/balanzas-formatos.php` existe

---

### ❌ Los cambios en el ABM no se reflejan en las ventas

**Causa:** La configuración se carga al iniciar la página de ventas.

**Solución:**
- **Recargar la página** de crear venta (`Ctrl+R` o `F5`)
- Si usas caché del navegador, hacer una recarga forzada (`Ctrl+Shift+R`)

---

## Archivos relacionados

- **Base de datos:**
  - `db/crear-tabla-balanzas-formatos.sql` - Script de creación

- **Backend (PHP):**
  - `modelos/balanzas_formatos.modelo.php` - Modelo con métodos CRUD
  - `controladores/balanzas_formatos.controlador.php` - Controlador del ABM
  - `vistas/modulos/balanzas-formatos.php` - Vista del ABM
  - `ajax/balanzas_formatos.ajax.php` - Endpoint AJAX para obtener detalles

- **Frontend (JavaScript):**
  - `vistas/js/venta-caja.js` - Función `interpretarCodigoBalanza()` e integración
  - `vistas/modulos/crear-venta-caja.php` - Carga de `balanzasFormatosConfig`

- **Ruteo:**
  - `vistas/plantilla.php` - Registro de ruta `balanzas-formatos`
  - `vistas/modulos/menu.php` - Item de menú "Formatos de balanza"
  - `index.php` - Inclusión de controlador y modelo

---

## Resumen rápido

✅ **Para agregar una nueva balanza:** Crear registro en ABM → Recargar página de ventas → Probar

✅ **Para modificar un formato:** Editar en ABM → Recargar página de ventas → Probar

✅ **Para desactivar:** Clic en ❌ → Confirmar

✅ **Si no funciona:** Verificar prefijo, posiciones (base 0), longitudes, y que el formato esté activo

---

**Última actualización:** Sistema implementado y funcional. ABM disponible para Administradores.
