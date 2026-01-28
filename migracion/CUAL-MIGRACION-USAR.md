# ¿Qué migración usar: completa o sinjson?

**Ambas hacen lo mismo:** migrar productos de ventas desde el campo JSON (`ventas.productos`) a la tabla relacional `productos_venta`.

La diferencia está en **cómo** lo hacen y **qué opciones** te dan.

---

## Resumen rápido

| Si quieres… | Usa |
|-------------|-----|
| Hacer todo en 2 pasos, sin pensar | **completa** |
| Diagnosticar antes, elegir con/sin FK, o migrar por PHP | **sinjson** |

---

## Migración **completa** (recomendada para la mayoría)

**Ruta:** `migracion/completa/`

**Qué es:** Un flujo en 2 pasos (o 3 si ya tenías la tabla sin PRIMARY KEY). Todo en SQL, con un procedimiento almacenado que hace la migración.

**Pasos:**
1. Ejecutar `01-CREAR-ESTRUCTURA.sql` (crea tabla, índices, FKs y el procedimiento de migración).
2. Ejecutar `02-EJECUTAR-MIGRACION.sql` (ejecuta la migración y borra el procedimiento).
3. Solo si la tabla `productos_venta` ya existía sin PRIMARY KEY: ejecutar `03-FIX-PRIMARY-KEY.sql`.

**Ventajas:**
- Pocos pasos, todo seguido.
- Un solo flujo: 01 → 02 (y 03 si hace falta).
- Documentado en `completa/LEEME.md`.

**Cuándo usarla:**
- Primera migración.
- Quieres terminar rápido.
- No necesitas diagnóstico previo ni elegir “con/sin FK”.

**Importante:** Si hay productos en el JSON que ya no existen en la tabla `productos`, el procedimiento los **omite** y sigue con el resto.

---

## Migración **sinjson** (más control y opciones)

**Ruta:** `migracion/sinjson/`

**Qué es:** Varios scripts modulares: crear tabla, migrar con validación, migrar sin validación FK, diagnosticar productos inexistentes, y un script maestro. Además hay opción de migrar con PHP.

**Pasos típicos:**
1. (Opcional) `diagnosticar-productos-inexistentes.sql` para ver qué productos fallarían.
2. `crear-tabla-productos-venta.sql` para crear la tabla.
3. Una de:
   - `migrar-productos-venta.sql` (recomendado: valida y omite productos inexistentes), o
   - `migrar-productos-venta-sin-fk.sql` (migra todo, incluso productos que ya no existen; desactiva FKs de forma temporal).

**O bien:** usar `00-SCRIPT-MAESTRO-COMPLETO.sql` si quieres un solo archivo que haga estructura + migración (similar en idea a **completa**, pero en otra carpeta).

**Ventajas:**
- Puedes diagnosticar antes con `diagnosticar-productos-inexistentes.sql`.
- Puedes elegir migración “con FK” (omitir inexistentes) o “sin FK” (incluir histórico con productos borrados).
- Tienes la alternativa de migrar con PHP (`migrar-ventas-pendientes.php`).
- Más documentación y guías paso a paso en esa carpeta.

**Cuándo usarla:**
- Quieres ver antes qué productos son problemáticos.
- Tienes muchas ventas antiguas con productos que ya no existen y quieres migrarlas igual (con `migrar-productos-venta-sin-fk.sql`).
- Prefieres scripts modulares o migrar vía PHP.

---

## Recomendación práctica

- **Para la gran mayoría de casos:** usar **completa**:
  1. Backup de la base de datos.
  2. `01-CREAR-ESTRUCTURA.sql`
  3. `02-EJECUTAR-MIGRACION.sql`
  4. Si ya existía `productos_venta` sin PRIMARY KEY: `03-FIX-PRIMARY-KEY.sql`

- **Usar sinjson** cuando:
  - Necesites un informe previo de productos inexistentes, o
  - Quieras migrar sí o sí todo el histórico (incluidos productos que ya no existen) con `migrar-productos-venta-sin-fk.sql`, o
  - Prefieras el flujo por PHP o los scripts modulares de esa carpeta.

---

## Ubicación de los archivos

```
migracion/
├── completa/                    ← Migración en 2–3 pasos
│   ├── 01-CREAR-ESTRUCTURA.sql
│   ├── 02-EJECUTAR-MIGRACION.sql
│   ├── 03-FIX-PRIMARY-KEY.sql   (solo si hace falta)
│   └── LEEME.md
│
└── sinjson/                     ← Migración modular y con más opciones
    ├── 00-SCRIPT-MAESTRO-COMPLETO.sql
    ├── crear-tabla-productos-venta.sql
    ├── migrar-productos-venta.sql
    ├── migrar-productos-venta-sin-fk.sql
    ├── diagnosticar-productos-inexistentes.sql
    ├── migrar-ventas-pendientes.php
    ├── README.md
    └── INSTRUCCIONES-MIGRACION.md
```

---

**En una frase:** si no tienes un motivo concreto para afinar producto por producto o usar PHP, **usa la migración completa** (2 pasos: 01 y 02).
