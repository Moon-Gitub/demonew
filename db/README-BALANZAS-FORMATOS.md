# Formatos de balanza (`balanzas_formatos`)

## ¿Qué resuelve?

Permite definir **cómo se interpreta un código de balanza** sin tener que tocar el código de `venta-caja.js`.

Cada formato indica:

- Qué **prefijo** debe tener el código (ej. `20`, `21`, `20000`).
- Dónde está el **id de producto** dentro del string.
- Dónde está el **peso/cantidad** (si aplica) y cómo convertirlo (ej. gramos → kilos).
- Si la cantidad es **peso** o **unidad fija**.

El frontend lee esta tabla y calcula `idProducto` y `cantidad` de forma genérica.

---

## Crear la tabla

Si aún no existe, ejecutá:

```bash
mysql -u USUARIO -p NOMBRE_BD < db/crear-tabla-balanzas-formatos.sql
```

O desde phpMyAdmin, pegando el contenido de `db/crear-tabla-balanzas-formatos.sql`.

### Campos principales

- `id_empresa`: empresa a la que aplica el formato.
- `nombre`: descripción legible (ej. “Balanza 20000 (peso en kg)”).
- `prefijo`: texto que debe estar al inicio del código (ej. `20`, `21`, `20000`).
- `pos_producto`: posición inicial (base 0) del id de producto.
- `longitud_producto`: cantidad de caracteres del id de producto.
- `modo_cantidad`:
  - `peso`: la cantidad se obtiene de un substring y se divide por `factor_divisor`.
  - `unidad`: la cantidad es fija y vale `cantidad_fija`.
  - `ninguno`: se usa la cantidad ingresada manualmente.
- `pos_cantidad`, `longitud_cantidad`: posición y longitud del campo de peso/cantidad (cuando `modo_cantidad = 'peso'`).
- `factor_divisor`: para pasar, por ejemplo, gramos a kilos (`1000`).
- `cantidad_fija`: para formatos de unidad (por defecto 1).
- `orden`: prioridad cuando más de un formato matchea el mismo código.
- `activo`: 1 = se usa, 0 = desactivado.

> **Importante:**  
> Las posiciones son **base 0** (primer carácter = 0), igual que `substr()` en JavaScript.

---

## Ejemplo de configuración incluida

El script de creación incluye 3 formatos típicos (empresa `1`), que replican la lógica vieja:

1. **Códigos que empiezan con `20000`**  
   - `prefijo`: `20000`  
   - `idProducto`: `substr(codigo, 5, 2)`  
   - `peso`: `substr(codigo, 7, 5) / 1000` (pasa de gramos a kilos)

2. **Códigos que empiezan con `20` (pero no 20000)**  
   - `prefijo`: `20`  
   - `idProducto`: `substr(codigo, 4, 2)`  
   - `peso`: `substr(codigo, 7, 5) / 1000`

3. **Códigos que empiezan con `21`**  
   - `prefijo`: `21`  
   - `idProducto`: `substr(codigo, 4, 2)`  
   - `cantidad`: fija en `1` unidad (`modo_cantidad = 'unidad'`)

Con estos tres registros, el nuevo sistema se comporta igual que el código hardcodeado anterior.

---

## Flujo en el sistema

1. En `crear-venta-caja.php` se cargan los formatos activos de la empresa y se envían al JS como:

   ```js
   var balanzasFormatosConfig = {
     // clave interna -> objeto con prefijo, posiciones, etc.
   };
   ```

2. En `vistas/js/venta-caja.js`:
   - Cuando el usuario escanea/escribe un código de balanza, una función genérica:
     - Busca el formato cuyo `prefijo` matchea el inicio del código.
     - Extrae `idProducto` y `cantidad` según `pos_*` y `longitud_*`.
   - Si no encuentra formato que matchee, se usa el comportamiento normal (código completo como id y cantidad manual).

3. Si se agrega una balanza nueva:
   - Solo hay que crear un nuevo registro en `balanzas_formatos`
   - **No** hace falta editar `venta-caja.js`.

---

## ABM (pendiente / en desarrollo)

El diseño contempla un futuro módulo “Formatos de balanza” dentro de **Empresa** para:

- Listar todos los formatos.
- Crear/editar/desactivar formatos.

Por ahora, los ejemplos iniciales se pueden ajustar directamente desde la base de datos (UPDATE/INSERT).

