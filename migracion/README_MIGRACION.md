# Migrador de Bases de Datos Interactivo

Script en Python para migrar datos desde un archivo SQL a la base de datos `newmoon_newmoon_db`, permitiendo mapear campos entre ambas estructuras.

## Características

- ✅ Visualización de tablas y campos de ambas bases de datos
- ✅ Comparación lado a lado de estructuras
- ✅ Mapeo interactivo de campos (origen → destino)
- ✅ Mapeo automático por nombre exacto
- ✅ Guardado y carga de mapeos en formato JSON
- ✅ Migración real de datos con validación
- ✅ Preview antes de ejecutar la migración

## Requisitos

- Python 3.6 o superior
- MySQL/MariaDB
- Acceso a la base de datos destino (`newmoon_newmoon_db`)

## Instalación

1. Instalar las dependencias:
```bash
pip install -r requirements.txt
```

## Uso

### 1. Ejecutar el script

```bash
python3 migrate_db_interactive.py
```

### 2. Configurar conexión a la base de datos destino

El script solicitará:
- Host (por defecto: localhost)
- Puerto (por defecto: 3306)
- Usuario
- Contraseña
- Nombre de la base de datos (por defecto: newmoon_newmoon_db)

### 3. Seleccionar archivo SQL de origen

Ingrese la ruta completa del archivo SQL que contiene los datos a migrar.

### 4. Menú principal

El script mostrará un menú con las siguientes opciones:

#### Opción 1: Ver comparación de campos
Muestra una comparación lado a lado de los campos de una tabla específica entre origen y destino.

**Ejemplo:**
```
Tabla: productos

Campos ORIGEN (Archivo SQL)      | Campos DESTINO (newmoon_newmoon_db)
----------------------------------------------------------------------------
id                         ✓     | id
codigo                     ✓     | codigo
descripcion                ✓     | descripcion
precio_compra                    | precio_compra
stock                            | stock
                                  | deposito
                                  | precio_compra_dolar
```

#### Opción 2: Mapear campos de una tabla
Permite mapear interactivamente cada campo del origen a un campo del destino.

- Los campos con nombre idéntico se mapean automáticamente
- Para campos diferentes, el script solicita el mapeo manual
- Puede omitir campos escribiendo 'skip' o 'NULL'

**Ejemplo de mapeo:**
```
Campo ORIGEN: precio_compra
Opciones:
  - Escriba el nombre del campo DESTINO
  - Escriba 'NULL' para no mapear
  - Escriba 'skip' para omitir este campo
  > precio_compra
  ✓ Mapeado: precio_compra -> precio_compra
```

#### Opción 3: Migrar datos de una tabla
Ejecuta la migración de datos de una tabla específica usando los mapeos definidos.

#### Opción 4: Ver mapeos guardados
Muestra todos los mapeos actualmente definidos.

#### Opción 5: Guardar mapeos
Guarda los mapeos en un archivo JSON para reutilización.

**Archivo generado (mapping.json):**
```json
{
  "productos": {
    "id": "id",
    "codigo": "codigo",
    "descripcion": "descripcion",
    "precio_compra": "precio_compra",
    "stock": "stock"
  },
  "ventas": {
    "id": "id",
    "codigo": "codigo",
    "total": "total"
  }
}
```

#### Opción 6: Cargar mapeos
Carga mapeos previamente guardados desde un archivo JSON.

#### Opción 7: Migrar todas las tablas
Migra todas las tablas usando los mapeos guardados (requiere confirmación).

## Flujo de trabajo recomendado

1. **Ejecutar el script** y conectarse a la base de datos destino
2. **Seleccionar el archivo SQL** de origen
3. **Para cada tabla importante:**
   - Ver comparación de campos (Opción 1)
   - Mapear campos (Opción 2)
   - Guardar mapeos (Opción 5)
4. **Migrar datos:**
   - Migrar tabla por tabla (Opción 3) o
   - Migrar todas las tablas (Opción 7)

## Notas importantes

⚠️ **Backup**: Siempre haga un backup de la base de datos destino antes de migrar datos.

⚠️ **Validación**: El script valida que los campos mapeados existan en la tabla destino antes de insertar.

⚠️ **Transacciones**: Los errores durante la migración activan un rollback automático para mantener la integridad.

⚠️ **Campos sin mapear**: Los campos del origen que no se mapeen se insertarán como NULL en la tabla destino.

## Ejemplo completo

```bash
$ python3 migrate_db_interactive.py

================================================================================
MIGRADOR DE BASES DE DATOS INTERACTIVO
================================================================================

Configuración de la base de datos DESTINO (newmoon_newmoon_db):
  Host [localhost]: 
  Puerto [3306]: 
  Usuario: root
  Contraseña: ****
  Base de datos [newmoon_newmoon_db]: 
✓ Conectado a base de datos destino: newmoon_newmoon_db

Selección de archivo SQL de ORIGEN
Ruta del archivo SQL de origen: /ruta/al/archivo.sql
✓ Encontradas 15 tablas en el archivo SQL

Tablas DISPONIBLES
Tablas en ORIGEN (archivo SQL): 15
  - cajas
  - categorias
  - clientes
  ...

================================================================================
MENÚ PRINCIPAL
================================================================================
1. Ver comparación de campos de una tabla
2. Mapear campos de una tabla
3. Migrar datos de una tabla
...
```

## Solución de problemas

### Error de conexión
- Verifique que MySQL/MariaDB esté ejecutándose
- Confirme credenciales y permisos de usuario
- Verifique que la base de datos destino exista

### Campos no encontrados
- Use la opción 1 para verificar los nombres exactos de los campos
- Los nombres son case-sensitive

### Errores de inserción
- Verifique que los tipos de datos sean compatibles
- Algunos campos pueden tener restricciones (NOT NULL, UNIQUE, etc.)
- Revise los mensajes de error específicos





