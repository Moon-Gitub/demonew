# Migración de productos

Script para migrar datos de productos desde la estructura antigua a la nueva tabla.

## Archivos

| Archivo | Descripción |
|---------|-------------|
| `original_datos.sql` | INSERT con estructura antigua (codigoProveedor, deposito, esCombo) |
| `actual.sql` | Referencia de estructura destino (opcional) |
| `migrar_productos.py` | Script que transforma los datos |
| `migrado.sql` | Salida generada (INSERT listo para la tabla nueva) |

## Uso

1. Coloca tu dump/INSERT de productos en `original_datos.sql`
2. Ejecuta:
   ```bash
   python3 migrar_productos.py
   ```
3. El resultado se guarda en `migrado.sql`

## Mapeo de columnas

- `codigoProveedor` → omitido
- `deposito` → `stock2` (NULL → 0.00)
- `stock3` → 0.00 (nuevo)
- `esCombo` → `es_combo` (0)
- `activo` → 1
- Duplicados (mismo `codigo`) → se inserta solo uno
- `id = 0` → se asigna el siguiente id disponible
