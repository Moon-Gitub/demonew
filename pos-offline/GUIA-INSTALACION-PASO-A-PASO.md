# Guía de Instalación Paso a Paso - POS Offline Moon

## 📍 Ubicación del Sistema

El sistema está en: `/home/cluna/Documentos/7-Moon-Desarrollos/demonew/demonew/pos-offline/`

## 🚀 Instalación Completa (Primera Vez)

### Paso 1: Ir a la carpeta del sistema

```bash
cd /home/cluna/Documentos/7-Moon-Desarrollos/demonew/demonew/pos-offline
```

### Paso 2: Instalar dependencias y crear entorno virtual

```bash
python3 install.py
```

**¿Qué hace este comando?**
- ✅ Verifica que tengas Python 3.7+
- ✅ Crea el entorno virtual (`venv/`)
- ✅ Instala todas las dependencias (requests, sqlalchemy, bcrypt, etc.)
- ✅ Crea los directorios necesarios (`data/`, `logs/`, `backups/`)
- ✅ Crea el archivo `config.json` si no existe
- ✅ Crea los scripts `run.sh` y `setup.sh`

**Tiempo estimado:** 2-5 minutos

### Paso 3: Configurar el sistema

```bash
./setup.sh
```

O si prefieres hacerlo manualmente:

```bash
source venv/bin/activate
python setup.py
```

**¿Qué hace este comando?**
- Te pregunta:
  - URL del servidor (ej: `https://newmoon.posmoon.com.ar`)
  - URL de la API (ej: `https://newmoon.posmoon.com.ar/api`)
  - ID Cliente Moon (tu número de cuenta)
  - Intervalo de sincronización (por defecto 60 segundos)
- Guarda todo en `config.json`
- Intenta hacer una sincronización inicial (si hay internet)

### Paso 4: Ejecutar la aplicación

```bash
./run.sh
```

O manualmente:

```bash
source venv/bin/activate
python main.py
```

## 🔄 Si Ya Tienes el Entorno Virtual Creado

Si ya ejecutaste `install.py` antes, solo necesitas:

### Opción 1: Usar el script (Recomendado)

```bash
cd /home/cluna/Documentos/7-Moon-Desarrollos/demonew/demonew/pos-offline
./run.sh
```

### Opción 2: Manual

```bash
cd /home/cluna/Documentos/7-Moon-Desarrollos/demonew/demonew/pos-offline
source venv/bin/activate
python main.py
```

## ⚠️ Solución de Problemas

### Error: "python: orden no encontrada"

**Causa:** El script está buscando `python` pero tu sistema tiene `python3`

**Solución:** Los scripts `run.sh` y `setup.sh` ya están corregidos. Si aún tienes problemas:

```bash
# Activar el entorno virtual manualmente
source venv/bin/activate

# Verificar que python esté disponible
which python

# Si funciona, ejecutar
python main.py
```

### Error: "source: not found"

**Causa:** Estás ejecutando con `sh` en lugar de `bash`

**Solución:** Usa `bash` explícitamente:

```bash
bash run.sh
```

O dale permisos de ejecución:

```bash
chmod +x run.sh
./run.sh
```

### Error: "No se encontró el entorno virtual"

**Solución:** Ejecuta la instalación:

```bash
python3 install.py
```

### Error: "ModuleNotFoundError"

**Solución:** Las dependencias no están instaladas. Reinstala:

```bash
source venv/bin/activate
pip install -r requirements.txt
```

### El sistema no inicia

**Verifica:**
1. ¿Tienes Python 3.7+? → `python3 --version`
2. ¿Existe el entorno virtual? → `ls -la venv/`
3. ¿Está activado? → Deberías ver `(venv)` en tu prompt
4. ¿Existe `config.json`? → `ls -la config.json`

## 📝 Estructura de Archivos

```
pos-offline/
├── venv/              # Entorno virtual (se crea con install.py)
├── data/              # Base de datos SQLite local
│   └── pos_local.db
├── logs/              # Logs del sistema
├── backups/           # Backups automáticos
├── config.json        # Configuración (se crea con setup.py)
├── main.py            # Punto de entrada principal
├── gui.py             # Interfaz gráfica
├── auth.py            # Autenticación
├── database.py        # Base de datos
├── sync.py            # Sincronización
├── connection.py      # Detección de conexión
├── install.py         # Instalador automático
├── setup.py           # Configuración inicial
├── run.sh             # Script de ejecución (Linux/Mac)
├── setup.sh           # Script de configuración
└── requirements.txt   # Dependencias Python
```

## 🎯 Flujo de Uso Normal

1. **Primera vez:**
   ```bash
   cd pos-offline
   python3 install.py    # Instala todo
   ./setup.sh            # Configura
   ./run.sh              # Ejecuta
   ```

2. **Uso diario:**
   ```bash
   cd pos-offline
   ./run.sh              # Solo esto
   ```

## 💡 Tips

- **Siempre usa `./run.sh`** para ejecutar (asegura que use el entorno virtual correcto)
- **Si cambias de ubicación** del proyecto, solo necesitas ejecutar `install.py` de nuevo
- **El entorno virtual** (`venv/`) es específico de esta carpeta, no lo muevas
- **La base de datos** está en `data/pos_local.db` - haz backups periódicos
