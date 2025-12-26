# 📚 Índice de Documentación - Carpeta migracion

## 📁 Archivos en esta Carpeta

### 🛠️ Scripts Principales

1. **`db_alter_generator.py`**
   - **Descripción:** Generador de scripts SQL para sincronización de estructuras de base de datos
   - **Tipo:** GUI (Interfaz Gráfica con Tkinter)
   - **Documentación:** [README_DB_ALTER_GENERATOR.md](./README_DB_ALTER_GENERATOR.md)
   - **Uso:** `python3 db_alter_generator.py`
   - **Funcionalidad:** Compara dos archivos SQL y genera un script de sincronización idempotente

2. **`db_migrator.py`**
   - **Descripción:** Herramienta de migración de datos
   - **Documentación:** [README_DB_MIGRATOR.md](./README_DB_MIGRATOR.md)
   - **Uso:** Ver documentación específica

### 📄 Archivos de Documentación

1. **`README_DB_ALTER_GENERATOR.md`** ⭐
   - Documentación completa del generador de scripts de sincronización
   - Incluye: uso, arquitectura, ejemplos, solución de problemas
   - **Leer primero si vas a usar `db_alter_generator.py`**

2. **`README_DB_MIGRATOR.md`**
   - Documentación del migrador de datos
   - Consultar para migraciones de datos

3. **`README_MIGRACION.md`**
   - Documentación general de migraciones
   - Visión general del proceso de migración

### 🧪 Archivos de Prueba

1. **`test_reconstruccion.py`**
   - Script de prueba para verificar la función de reconstrucción de CREATE TABLE
   - Uso: `python3 test_reconstruccion.py`

### ⚙️ Archivos de Configuración

1. **`requirements.txt`**
   - Dependencias de Python necesarias para los scripts
   - Instalación: `pip3 install -r requirements.txt`

---

## 🚀 Inicio Rápido

### Para Sincronizar Estructuras de Base de Datos

1. **Leer documentación:**
   ```bash
   cat README_DB_ALTER_GENERATOR.md
   ```

2. **Ejecutar el script:**
   ```bash
   python3 db_alter_generator.py
   ```

3. **Seguir los pasos en la GUI:**
   - Cargar DESTINO (modelo)
   - Cargar ORIGEN (a modificar)
   - Generar alter_table.sql

### Para Migrar Datos

1. **Leer documentación:**
   ```bash
   cat README_DB_MIGRATOR.md
   ```

---

## 📖 Guía de Lectura Recomendada

### Si eres nuevo en el proyecto:

1. **Empieza aquí:** `README_MIGRACION.md` (visión general)
2. **Para sincronizar estructuras:** `README_DB_ALTER_GENERATOR.md`
3. **Para migrar datos:** `README_DB_MIGRATOR.md`

### Si necesitas usar el generador de scripts:

1. **Lee:** `README_DB_ALTER_GENERATOR.md` (documentación completa)
2. **Prueba:** `python3 test_reconstruccion.py` (verificar funcionamiento)
3. **Usa:** `python3 db_alter_generator.py` (ejecutar herramienta)

---

## 🔍 Búsqueda Rápida

### ¿Cómo sincronizo estructuras de BD?
→ Ver: `README_DB_ALTER_GENERATOR.md` - Sección "Uso"

### ¿Cómo funciona el parser?
→ Ver: `README_DB_ALTER_GENERATOR.md` - Sección "Funcionamiento Técnico"

### ¿Por qué falla con paréntesis desbalanceados?
→ Ver: `README_DB_ALTER_GENERATOR.md` - Sección "Solución de Problemas"

### ¿Cómo se hace idempotente el script?
→ Ver: `README_DB_ALTER_GENERATOR.md` - Sección "Generación de SQL Idempotente"

### ¿Qué validaciones se aplican?
→ Ver: `README_DB_ALTER_GENERATOR.md` - Sección "Validaciones y Seguridad"

---

## 📝 Notas Importantes

- **Todos los scripts son idempotentes:** Pueden ejecutarse múltiples veces sin causar errores
- **Nunca eliminan datos:** Los scripts solo agregan o modifican, nunca eliminan
- **Siempre hacer backup:** Antes de ejecutar cualquier script SQL generado
- **Validación automática:** Los scripts validan sintaxis antes de escribir archivos

---

**Última actualización:** 2025-12-26
