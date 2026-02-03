# Cómo saber si las DB de tus clientes tienen la misma estructura que la versión en GitHub

Si hay mucha diferencia entre una base y otra, conviene tener una **referencia** (la estructura que usa el código en GitHub) y **comparar** cada cliente contra esa referencia.

---

## 1. Definir la referencia (estructura “correcta”)

**Opción A – Exportar desde una base que funcione bien**

En un servidor donde el sistema funcione bien (o en local con la misma versión de código):

```bash
mysqldump -u USUARIO -p --no-data --skip-add-drop-table NOMBRE_BASE > referencia_estructura.sql
```

- `--no-data`: solo estructura (tablas, columnas, índices), sin datos.
- `--skip-add-drop-table`: no escribe `DROP TABLE`, así el archivo solo tiene `CREATE TABLE` y se puede usar como referencia.

Guarda ese `referencia_estructura.sql` (por ejemplo en el repo en `db/` o en un drive) y úsalo como “versión GitHub” de la estructura.

**Opción B – Armar la referencia con los scripts del repo**

La estructura que espera el código está en los `.sql` del repo:

- Tablas base: `db/cobrosposmooncom_db.sql` o `db/datos-prueba-completo.sql`
- Tablas nuevas: `db/crear-tabla-*.sql`, `db/crear-tablas-*.sql`, `db/Nuevas/*.sql`, `db/reset-todas-tablas-con-datos.sql`
- Cambios posteriores: `db/agregar-campo-*.sql`, `db/agregar-campos-*.sql`, `db/optimizar-indices-dashboard.sql`, etc.

Puedes ejecutar esos scripts en una base vacía y luego exportar con `mysqldump --no-data` como en la opción A para generar tu `referencia_estructura.sql`.

---

## 2. Exportar la estructura de cada cliente

En cada cliente (misma versión de MySQL/MariaDB que uses en referencia):

```bash
mysqldump -u USUARIO -p --no-data --skip-add-drop-table NOMBRE_BASE_CLIENTE > cliente_X_estructura.sql
```

Cada archivo (por ejemplo `cliente_elsanto_estructura.sql`, `cliente_barbas_estructura.sql`) representa la estructura actual de ese cliente.

---

## 3. Comparar

**Opción 1 – Diff de texto**

```bash
diff referencia_estructura.sql cliente_X_estructura.sql
```

O con un comparador de archivos (VS Code, Beyond Compare, etc.). Verás diferencias en `CREATE TABLE`, columnas e índices.

**Opción 2 – Herramientas de comparación de esquema**

- **MySQL Workbench:** Menu Database → Compare Schemas (elegís la referencia y la del cliente).
- **Otros:** dbForge, Navicat, etc., suelen tener “Schema Compare” o “Structure Compare”.

Ahí ves qué tablas o índices faltan en el cliente o están de más.

---

## 4. Qué revisar cuando “hay mucha diferencia”

- **Tablas que faltan:** Por ejemplo `listas_precio`, `medios_pago`, `pantallas`, `permisos_rol`, `balanzas_formatos`, `integraciones`, tablas de Mercado Pago. Si faltan, hay que ejecutar los `crear-tabla-*.sql` / `crear-tablas-*.sql` correspondientes.
- **Columnas que faltan:** En `empresa` (login_fondo, login_logo, mp_public_key, etc.), en `usuarios`, en `productos`, etc. Suelen estar en los `agregar-campo-*.sql` / `agregar-campos-*.sql`.
- **Índices distintos:** Si el código espera un índice y en el cliente no existe, puede haber lentitud o errores raros. El script `db/optimizar-indices-dashboard.sql` y los `CREATE TABLE` del repo son la referencia.

Cuando encuentres diferencias, aplica en el cliente solo los `ALTER TABLE` o scripts que falten (preferible en un backup primero).

---

## 5. Resumen rápido

| Paso | Acción |
|------|--------|
| 1 | Tener una referencia: exportar estructura de una base “buena” con `mysqldump --no-data` → `referencia_estructura.sql`. |
| 2 | Por cada cliente: `mysqldump --no-data` → `cliente_X_estructura.sql`. |
| 3 | Comparar con `diff` o con MySQL Workbench / herramienta de comparación de esquema. |
| 4 | Revisar tablas/columnas/índices que falten o difieran y aplicar los scripts `.sql` del repo que correspondan. |

Así sabés si las tablas de cada cliente están alineadas con la versión que tenés en GitHub y qué aplicar para que queden iguales.
