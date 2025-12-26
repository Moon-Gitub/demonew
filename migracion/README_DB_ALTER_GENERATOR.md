# 📚 Documentación: db_alter_generator.py

## 🌙 NewMoon DB Alter Generator - Generador de Scripts de Sincronización de Estructura

### 📋 Descripción General

`db_alter_generator.py` es una herramienta GUI (interfaz gráfica) escrita en Python que compara dos archivos SQL de estructura de base de datos y genera un script de sincronización idempotente y robusto.

**Propósito:**
- Comparar la estructura de una base de datos DESTINO (modelo) con una base de datos ORIGEN (a modificar)
- Generar automáticamente un script SQL (`alter_table.sql`) que sincronice la estructura del ORIGEN para que sea idéntica al DESTINO
- Garantizar que el script generado sea **idempotente** (se puede ejecutar múltiples veces sin errores)
- Validar la sintaxis SQL antes de generar el archivo

---

## 🎯 Características Principales

### ✅ Funcionalidades Implementadas

1. **Comparación Inteligente de Estructuras**
   - Detecta tablas nuevas que faltan en ORIGEN
   - Detecta columnas nuevas que faltan en ORIGEN
   - Detecta diferencias en columnas existentes (tipo, NULL, DEFAULT, AUTO_INCREMENT)
   - Detecta índices faltantes

2. **Generación Idempotente de SQL**
   - `CREATE TABLE IF NOT EXISTS` para tablas nuevas
   - Verificación de existencia antes de agregar columnas (usando `INFORMATION_SCHEMA`)
   - `ALTER TABLE MODIFY COLUMN` para columnas que necesitan cambios
   - El script puede ejecutarse múltiples veces sin causar errores

3. **Validación y Seguridad**
   - Validación de sintaxis SQL antes de escribir el archivo
   - Manejo correcto de comillas simples en valores DEFAULT
   - Escape correcto de caracteres especiales
   - Validación de paréntesis balanceados
   - Detección de problemas potenciales

4. **Manejo Robusto de Valores DEFAULT**
   - NULL (sin comillas)
   - Números (sin comillas)
   - Strings (con comillas simples, correctamente escapadas)
   - Funciones SQL como `current_timestamp()` (sin comillas)
   - Valores con caracteres especiales (#, ', ", \)

5. **Interfaz Gráfica Intuitiva**
   - Carga de archivos SQL DESTINO y ORIGEN
   - Visualización de diferencias por tabla
   - Vista previa del SQL generado
   - Generación y guardado del archivo `alter_table.sql`

---

## 🚀 Uso

### Requisitos Previos

```bash
# Python 3.6 o superior
python3 --version

# Dependencias (si no están instaladas)
pip3 install tkinter  # Generalmente viene con Python
```

### Ejecución

```bash
cd migracion
python3 db_alter_generator.py
```

### Pasos de Uso

1. **Cargar DESTINO (modelo)**
   - Click en "📁 Cargar DESTINO (modelo)"
   - Seleccionar el archivo SQL que representa la estructura deseada (modelo)

2. **Cargar ORIGEN**
   - Click en "📁 Cargar ORIGEN"
   - Seleccionar el archivo SQL que representa la estructura actual (a modificar)

3. **Analizar Diferencias**
   - Click en "🔍 Analizar" para ver un resumen de cambios detectados
   - Navegar por las pestañas de tablas para ver diferencias detalladas

4. **Vista Previa (Opcional)**
   - Click en "👁️ Vista Previa alter_table.sql" para ver el SQL generado sin guardarlo

5. **Generar Script**
   - Click en "🚀 Generar alter_table.sql"
   - Elegir ubicación y nombre del archivo
   - El script se generará con todas las validaciones aplicadas

---

## 🔧 Funcionamiento Técnico

### Arquitectura del Script

```
┌─────────────────────────────────────────────────────────┐
│  SimpleSQLParser                                         │
│  - Parsea archivos SQL                                   │
│  - Extrae tablas, columnas, índices, tipos, defaults     │
│  - Reconstruye CREATE TABLE desde datos parseados       │
└─────────────────────────────────────────────────────────┘
                    ↓
┌─────────────────────────────────────────────────────────┐
│  comparar_estructuras()                                 │
│  - Compara DESTINO vs ORIGEN                            │
│  - Genera lista de cambios (CREATE, ADD, MODIFY, INDEX)│
└─────────────────────────────────────────────────────────┘
                    ↓
┌─────────────────────────────────────────────────────────┐
│  generar_sql()                                          │
│  - Genera SQL idempotente                               │
│  - Valida sintaxis                                      │
│  - Formatea valores DEFAULT correctamente               │
└─────────────────────────────────────────────────────────┘
```

### Componentes Principales

#### 1. SimpleSQLParser

**Responsabilidades:**
- Parsear archivos SQL (dumps de MySQL/MariaDB)
- Extraer información de tablas, columnas, tipos, defaults, índices
- Reconstruir CREATE TABLE statements desde datos parseados

**Métodos Clave:**
- `parse_file(path)`: Carga y parsea un archivo SQL
- `_parse_tables()`: Extrae todas las tablas del SQL
- `_extraer_default()`: Extrae valores DEFAULT (maneja strings, números, NULL, funciones)
- `_reconstruir_create_table()`: Reconstruye CREATE TABLE válido desde datos parseados

**Ventaja de Reconstrucción:**
En lugar de extraer el CREATE TABLE del SQL original (que puede tener errores), el script **reconstruye** el CREATE TABLE desde los datos parseados. Esto garantiza:
- Paréntesis siempre balanceados
- Estructura SQL válida
- IF NOT EXISTS incluido automáticamente

#### 2. Funciones de Validación y Formateo

**`formatear_default_sql(valor_default)`**
- Formatea valores DEFAULT para SQL
- Escapa comillas simples correctamente (`'texto'` → `'texto con ''comilla'''`)
- Maneja NULL, números, funciones SQL, strings

**`validar_sintaxis_sql(sql)`**
- Valida sintaxis SQL básica
- Verifica paréntesis balanceados (ignorando strings)
- Verifica comillas balanceadas
- Detecta tipos de datos potencialmente inválidos
- Ignora comentarios en la validación

#### 3. Comparación de Estructuras

**`comparar_estructuras(destino, origen)`**
- Compara tablas entre DESTINO y ORIGEN
- Genera lista de cambios:
  - `CREATE_TABLE`: Tablas que faltan en ORIGEN
  - `ADD_COLUMN`: Columnas que faltan en ORIGEN
  - `MODIFY_COLUMN`: Columnas que existen pero difieren
  - `ADD_INDEX`: Índices que faltan en ORIGEN

**Comparación Case-Insensitive:**
Los nombres de columnas se comparan sin distinguir mayúsculas/minúsculas (MySQL es case-insensitive por defecto).

#### 4. Generación de SQL Idempotente

**`generar_sql(cambios, destino, archivo_destino, archivo_origen)`**
- Genera el script SQL completo
- Aplica todas las validaciones
- Formatea correctamente todos los valores

**Estrategia de Idempotencia:**

1. **CREATE TABLE:**
   ```sql
   CREATE TABLE IF NOT EXISTS `tabla` (...);
   ```
   - Si la tabla ya existe, no hace nada

2. **ADD COLUMN:**
   ```sql
   SET @col_exists = (
     SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE()
       AND TABLE_NAME = 'tabla'
       AND COLUMN_NAME = 'campo'
   );
   SET @sql = IF(@col_exists = 0,
     'ALTER TABLE `tabla` ADD COLUMN `campo` ...',
     'SELECT ''Columna ya existe, se omite'' AS mensaje'
   );
   PREPARE stmt FROM @sql;
   EXECUTE stmt;
   DEALLOCATE PREPARE stmt;
   ```
   - Verifica si la columna existe antes de agregarla
   - Si existe, muestra mensaje y continúa
   - Si no existe, la agrega

3. **MODIFY COLUMN:**
   ```sql
   ALTER TABLE `tabla` MODIFY COLUMN `campo` ...;
   ```
   - Se ejecuta siempre (no causa error si ya está correcto)

---

## 🛡️ Validaciones y Seguridad

### Validaciones Aplicadas

1. **Paréntesis Balanceados**
   - Cuenta paréntesis ignorando strings y backticks
   - Detecta paréntesis de cierre sin apertura
   - Detecta paréntesis de apertura sin cierre

2. **Comillas Balanceadas**
   - Verifica que las comillas simples estén balanceadas
   - Ignora comillas dentro de comentarios

3. **Escape de Comillas en Strings SQL**
   - Duplica comillas simples dentro de strings SQL para PREPARE
   - Ejemplo: `'#52658d'` → `''#52658d''` dentro del string SQL

4. **Tipos de Datos Válidos**
   - Valida que los tipos de datos sean válidos para MySQL/MariaDB
   - Detecta tipos potencialmente inválidos

5. **Completitud de Statements**
   - Verifica que los CREATE TABLE tengan ENGINE=
   - Verifica que los statements terminen con punto y coma

### Manejo de Errores

- **Errores de Sintaxis:** El script valida antes de escribir y muestra advertencias
- **Columnas Duplicadas:** El script verifica existencia antes de agregar
- **Tablas Existentes:** Usa IF NOT EXISTS para evitar errores
- **Valores DEFAULT Mal Formados:** El script los detecta y corrige automáticamente

---

## 📝 Ejemplos de Uso

### Ejemplo 1: Sincronización Básica

**DESTINO (modelo):**
```sql
CREATE TABLE `productos` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(255) NOT NULL,
  `precio` DECIMAL(10,2) NULL DEFAULT 0.00,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
```

**ORIGEN (actual):**
```sql
CREATE TABLE `productos` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
```

**SQL Generado:**
```sql
-- Agregar columna 'precio' (solo si no existe)
SET @col_exists = (
  SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'productos'
    AND COLUMN_NAME = 'precio'
);
SET @sql = IF(@col_exists = 0,
  'ALTER TABLE `productos` ADD COLUMN `precio` DECIMAL(10,2) NULL DEFAULT 0.00',
  'SELECT ''Columna `precio` ya existe en `productos`, se omite'' AS mensaje'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
```

### Ejemplo 2: Tabla Nueva

**DESTINO tiene tabla `combos`, ORIGEN no la tiene**

**SQL Generado:**
```sql
CREATE TABLE IF NOT EXISTS `combos` (
  `id` INT(11) NOT NULL,
  `nombre` VARCHAR(255) NOT NULL,
  `precio` DECIMAL(11,2) NULL DEFAULT 0.00,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
```

### Ejemplo 3: Valores DEFAULT con Comillas

**DESTINO:**
```sql
`login_color_boton` VARCHAR(50) NULL DEFAULT '#52658d'
```

**SQL Generado:**
```sql
SET @sql = IF(@col_exists = 0,
  'ALTER TABLE `empresa` ADD COLUMN `login_color_boton` VARCHAR(50) NULL DEFAULT ''#52658d''',
  'SELECT ''Columna ya existe, se omite'' AS mensaje'
);
```

Las comillas simples dentro del valor DEFAULT se escapan correctamente (`'` → `''`).

---

## 🔍 Solución de Problemas

### Error: "Paréntesis desbalanceados"

**Causa:** El SQL original puede tener paréntesis desbalanceados.

**Solución:** El script ahora **reconstruye** el CREATE TABLE desde datos parseados, garantizando paréntesis balanceados. Si el error persiste, verifica que el SQL de ORIGEN esté bien formado.

### Error: "#1060 - Nombre duplicado de columna"

**Causa:** El parser no detectó la columna en el SQL de ORIGEN, pero la columna ya existe en la base de datos.

**Solución:** El script ahora verifica existencia antes de agregar columnas usando `INFORMATION_SCHEMA`. Si la columna ya existe, se omite automáticamente.

### Error: "#1064 - Error de sintaxis SQL"

**Causa:** Comillas simples dentro de valores DEFAULT no están escapadas correctamente en el string SQL para PREPARE.

**Solución:** El script ahora escapa correctamente las comillas simples duplicándolas dentro del string SQL.

### Advertencia: "Tipo de dato potencialmente inválido"

**Causa:** El parser detectó un tipo de dato que no está en la lista de tipos válidos conocidos.

**Solución:** Generalmente es una falsa alarma. Verifica manualmente si el tipo es válido para tu versión de MySQL/MariaDB.

---

## 📊 Estructura del SQL Generado

El script genera un archivo SQL con la siguiente estructura:

```sql
-- ================================================================
-- SCRIPT DE SINCRONIZACIÓN DE ESTRUCTURA
-- ================================================================
-- Generado: 2025-12-26 00:32:00
-- DESTINO (modelo): archivo_destino.sql
-- ORIGEN (a modificar): archivo_origen.sql
-- ⚠️  IMPORTANTE: Hacer backup antes de ejecutar
-- ================================================================

SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';

-- ================================================================
-- TABLAS NUEVAS A CREAR
-- ================================================================
-- CREATE TABLE IF NOT EXISTS para cada tabla nueva
...

-- ================================================================
-- TABLA: nombre_tabla
-- Agregar: X campos
-- Modificar: Y campos
-- ================================================================
-- Comandos ADD COLUMN con verificación de existencia
-- Comandos MODIFY COLUMN
-- Comandos ADD INDEX
...

SET FOREIGN_KEY_CHECKS = 1;

-- ================================================================
-- RESUMEN
-- ================================================================
-- Tablas creadas: X
-- Columnas agregadas: Y
-- Columnas modificadas: Z
-- Índices agregados: W
-- ✅ Validación de sintaxis SQL: PASADA
-- ================================================================
```

---

## 🎨 Interfaz Gráfica

### Componentes de la GUI

1. **Header:** Título de la aplicación
2. **Carga de Archivos:** Botones para cargar DESTINO y ORIGEN
3. **Pestañas de Tablas:** Navegación entre tablas comunes
4. **Vista de Detalles:** Comparación campo por campo entre DESTINO y ORIGEN
5. **Botones de Acción:**
   - 🔍 Analizar: Muestra resumen de cambios
   - 👁️ Vista Previa: Muestra SQL generado sin guardar
   - 🚀 Generar: Genera y guarda el archivo SQL

### Estados de Campos

- ✅ **OK:** Campo existe y es idéntico en ambos
- ⚠️ **Falta en ORIGEN:** Campo existe en DESTINO pero no en ORIGEN (se agregará)
- ✏️ **Diferente (MODIFY):** Campo existe pero difiere (se modificará)
- ℹ️ **Solo ORIGEN:** Campo existe solo en ORIGEN (no se toca)

---

## 🔧 Mejoras Implementadas

### Versión Actual (Mejorada)

1. **Reconstrucción de CREATE TABLE**
   - ✅ Reconstruye desde datos parseados (no extrae del SQL original)
   - ✅ Garantiza paréntesis balanceados
   - ✅ Siempre incluye IF NOT EXISTS

2. **Idempotencia Completa**
   - ✅ Verifica existencia de columnas antes de agregar
   - ✅ Usa IF NOT EXISTS para tablas
   - ✅ El script puede ejecutarse múltiples veces sin errores

3. **Manejo Robusto de Valores DEFAULT**
   - ✅ Escapa comillas simples correctamente
   - ✅ Maneja NULL, números, strings, funciones SQL
   - ✅ Valida valores antes de usar

4. **Validación de Sintaxis**
   - ✅ Valida paréntesis balanceados
   - ✅ Valida comillas balanceadas
   - ✅ Ignora comentarios en la validación
   - ✅ Detecta problemas potenciales

5. **Comparación Case-Insensitive**
   - ✅ Compara nombres de columnas sin distinguir mayúsculas/minúsculas
   - ✅ Maneja variaciones en el case de nombres

---

## 📋 Reglas del Script

### ✅ Lo que SÍ hace:

- ✅ Crea tablas nuevas con `CREATE TABLE IF NOT EXISTS`
- ✅ Agrega columnas nuevas (verificando existencia primero)
- ✅ Modifica columnas existentes que difieren
- ✅ Agrega índices faltantes
- ✅ Valida sintaxis SQL antes de escribir
- ✅ Escapa correctamente caracteres especiales

### ❌ Lo que NO hace:

- ❌ **NUNCA** elimina columnas (DROP COLUMN)
- ❌ **NUNCA** elimina tablas (DROP TABLE)
- ❌ **NUNCA** elimina índices existentes
- ❌ **NUNCA** modifica datos existentes (solo estructura)

**Filosofía:** El script es **aditivo y modificativo**, nunca destructivo.

---

## 🧪 Testing

### Prueba Rápida

```bash
cd migracion
python3 test_reconstruccion.py
```

Este script de prueba verifica que:
- La función de reconstrucción genera SQL válido
- Los paréntesis están balanceados
- La validación de sintaxis funciona correctamente

---

## 📚 Referencias Técnicas

### Tipos de Datos Soportados

- **Enteros:** INT, INTEGER, BIGINT, SMALLINT, TINYINT, MEDIUMINT
- **Decimales:** DECIMAL, NUMERIC, FLOAT, DOUBLE, REAL
- **Strings:** VARCHAR, CHAR, TEXT, TINYTEXT, MEDIUMTEXT, LONGTEXT
- **Binarios:** BLOB, TINYBLOB, MEDIUMBLOB, LONGBLOB
- **Fechas:** DATE, TIME, DATETIME, TIMESTAMP, YEAR
- **Especiales:** ENUM, SET, JSON, BOOLEAN, BOOL

### Funciones SQL Soportadas en DEFAULT

- `CURRENT_TIMESTAMP`
- `NOW()`
- `CURRENT_DATE`
- `CURRENT_TIME`
- Cualquier función con sintaxis `nombre_funcion(...)`

---

## 🐛 Problemas Conocidos y Limitaciones

### Limitaciones

1. **No lee la base de datos directamente:** Solo compara archivos SQL
   - Si una columna existe en la BD pero no en el SQL de ORIGEN, el script no la detectará
   - Solución: Asegúrate de que el SQL de ORIGEN esté actualizado

2. **No soporta DROP:** Por diseño, el script nunca elimina nada
   - Si necesitas eliminar columnas/tablas, hazlo manualmente

3. **Depende de la calidad del SQL de entrada:**
   - Si el SQL de ORIGEN está mal formado, el parser puede no detectar algunas columnas
   - Solución: Regenera el dump SQL desde la base de datos

### Problemas Conocidos Resueltos

✅ **Paréntesis desbalanceados:** Resuelto con reconstrucción de CREATE TABLE  
✅ **Comillas sin cerrar:** Resuelto con escape correcto  
✅ **Columnas duplicadas:** Resuelto con verificación de existencia  
✅ **Tablas existentes:** Resuelto con IF NOT EXISTS  
✅ **Valores DEFAULT con comillas:** Resuelto con escape en strings SQL  

---

## 📝 Changelog

### Versión Actual (2025-12-26)

- ✅ Reconstrucción de CREATE TABLE desde datos parseados
- ✅ Verificación de existencia antes de ADD COLUMN
- ✅ Escape correcto de comillas en strings SQL para PREPARE
- ✅ Validación de sintaxis mejorada (ignora comentarios)
- ✅ Comparación case-insensitive de nombres de columnas
- ✅ Manejo robusto de valores DEFAULT
- ✅ Validación de paréntesis balanceados

### Versiones Anteriores

- **v1.0:** Versión inicial con extracción de CREATE TABLE del SQL original
- **v1.1:** Agregado IF NOT EXISTS para CREATE TABLE
- **v1.2:** Mejoras en validación de sintaxis
- **v1.3:** Reconstrucción de CREATE TABLE (versión actual)

---

## 🤝 Contribuciones

Para mejorar el script:

1. Mantén la filosofía de **nunca eliminar datos**
2. Asegúrate de que el script sea **idempotente**
3. Valida la sintaxis SQL antes de escribir
4. Escapa correctamente todos los caracteres especiales
5. Documenta los cambios en este archivo

---

## 📞 Soporte

Si encuentras problemas:

1. Verifica que los archivos SQL de entrada estén bien formados
2. Revisa los mensajes de validación en el resumen del SQL generado
3. Ejecuta el script de prueba: `python3 test_reconstruccion.py`
4. Revisa los logs de error si los hay

---

## 📄 Licencia

Este script es parte del proyecto NewMoon y sigue las mismas condiciones de licencia del proyecto principal.

---

**Última actualización:** 2025-12-26  
**Versión del script:** 1.3 (Reconstrucción de CREATE TABLE + Idempotencia completa)
