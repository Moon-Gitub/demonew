# 🌙 NewMoon DB Migrator

Herramienta gráfica de migración de bases de datos MySQL con interfaz Tkinter.

## Características

- ✅ Interfaz gráfica moderna con colores oscuros
- ✅ Carga de archivos SQL (origen y destino)
- ✅ Análisis automático de estructuras y conteo de registros
- ✅ Mapeo visual de campos con dropdowns
- ✅ Auto-mapeo de campos con el mismo nombre
- ✅ Creación de campos nuevos en destino
- ✅ Generación de 4 scripts SQL listos para ejecutar

## Requisitos

- Python 3.6 o superior
- Tkinter (incluido en la mayoría de instalaciones de Python)

## Uso

### Ejecutar la aplicación

```bash
python3 db_migrator.py
```

### Flujo de trabajo

1. **Cargar archivos SQL:**
   - Clic en "📁 Cargar Destino" → Selecciona `newmoon_newmoon_db.sql`
   - Clic en "📁 Cargar Origen" → Selecciona tu archivo SQL con datos

2. **Mapear campos:**
   - Selecciona una tabla de los botones superiores
   - Para cada campo origen, elige en el dropdown:
     - `-- Ninguno --`: Ignorar el campo
     - `++ CREAR CAMPO ++`: Crear nuevo campo en destino
     - Nombre de campo destino: Mapear a ese campo

3. **Auto-mapear:**
   - Clic en "⚡ Auto-Mapear" para mapear automáticamente campos con el mismo nombre

4. **Vista previa:**
   - Clic en "👁️ Vista Previa SQL" para ver el script generado

5. **Generar scripts:**
   - Clic en "🚀 Generar Scripts"
   - Selecciona el directorio donde guardar
   - Se generarán 4 archivos:
     - `01_backup.sql`: Backup de seguridad
     - `02_alter_estructura.sql`: Creación de campos nuevos
     - `03_migrar_datos.sql`: Migración de datos
     - `04_verificar.sql`: Verificación post-migración

## Scripts Generados

### 01_backup.sql
Crea tablas de respaldo antes de la migración:
```sql
CREATE TABLE IF NOT EXISTS `_backup_[tabla]` AS SELECT * FROM `[tabla]`;
```

### 02_alter_estructura.sql
Crea campos nuevos en las tablas destino:
```sql
ALTER TABLE `[tabla]` ADD COLUMN IF NOT EXISTS `[campo]` [definición];
```

### 03_migrar_datos.sql
Migra los datos con los mapeos definidos:
```sql
INSERT INTO `[tabla]` (`campo1`, `campo2`, ...)
SELECT o.`campo_origen1`, o.`campo_origen2`, ...
FROM `origen_db`.`[tabla]` o
ON DUPLICATE KEY UPDATE `id` = VALUES(`id`);
```

### 04_verificar.sql
Verifica que la migración fue exitosa:
```sql
SELECT 
    '[tabla]' AS tabla,
    [cantidad] AS registros_origen,
    (SELECT COUNT(*) FROM `[tabla]`) AS registros_destino,
    CASE 
        WHEN [cantidad] = (SELECT COUNT(*) FROM `[tabla]`)
        THEN '✅ OK'
        ELSE '⚠️ DIFERENCIA'
    END AS estado;
```

## Interfaz

- **Colores:**
  - Fondo oscuro: `#1a1a2e`
  - Acento azul: `#00d9ff`
  - Acento verde: `#00ff88`
  - Texto: `#ffffff`

- **Estados de mapeo:**
  - ✅ Mapeado: Campo correctamente mapeado
  - ⚠️ Pendiente: Campo sin mapear
  - ⚪ Ignorado: Campo marcado como "Ninguno"
  - 🔵 Crear: Campo que se creará en destino

## Notas

- Los archivos SQL deben estar en formato UTF-8
- El script asume que los datos origen estarán en una base de datos llamada `origen_db`
- Ajusta los nombres de bases de datos en los scripts generados según tu configuración
- Siempre ejecuta los scripts en orden: 01 → 02 → 03 → 04



