# Guía de Instalación del Sistema Offline - POS Offline Moon

## 📋 Índice

1. [Requisitos Previos](#requisitos-previos)
2. [Instalación en Windows](#instalación-en-windows)
3. [Instalación en Linux](#instalación-en-linux)
4. [Configuración Inicial](#configuración-inicial)
5. [Primera Ejecución](#primera-ejecución)
6. [Archivos y Configuraciones](#archivos-y-configuraciones)
7. [Solución de Problemas](#solución-de-problemas)
8. [Actualización y Mantenimiento](#actualización-y-mantenimiento)

---

## Requisitos Previos

### Requisitos Generales

- **Python 3.7 o superior**
- **Conexión a internet** (para sincronización inicial)
- **Acceso al servidor POS Moon**
- **Al menos 500 MB de espacio en disco**

### Requisitos Específicos por Sistema Operativo

**Windows:**
- Windows 7 o superior
- Python 3.7+ con opción "Add Python to PATH" marcada
- Git (opcional, para clonar repositorio)

**Linux:**
- Ubuntu 18.04+ / Debian 10+ / CentOS 7+ / Fedora 30+
- Python 3.7+ y pip3
- python3-tk (para interfaz gráfica)
- python3-venv (para entorno virtual)

---

## Instalación en Windows

### Paso 1: Instalar Python

1. **Descargar Python 3.7 o superior**
   - Ir a [python.org/downloads](https://www.python.org/downloads/)
   - Descargar la versión más reciente de Python 3.x
   - Ejecutar el instalador

2. **Durante la instalación:**
   - ✅ **IMPORTANTE**: Marcar la casilla **"Add Python to PATH"**
   - Seleccionar "Install Now" o "Customize installation"
   - Si eliges "Customize", asegúrate de marcar "pip" y "tcl/tk"

3. **Verificar instalación:**
   ```cmd
   python --version
   ```
   Debe mostrar algo como: `Python 3.11.x`

### Paso 2: Descargar el Sistema

**Opción A: Desde Git (Recomendado)**

```cmd
git clone https://github.com/Moon-Gitub/demonew.git
cd demonew\pos-offline-moon
```

**Opción B: Descargar ZIP**

1. Ir a [github.com/Moon-Gitub/demonew](https://github.com/Moon-Gitub/demonew)
2. Click en "Code" → "Download ZIP"
3. Extraer el ZIP en una carpeta (ej: `C:\POS-Offline-Moon`)
4. Abrir PowerShell o CMD en esa carpeta:
   ```cmd
   cd C:\POS-Offline-Moon\pos-offline-moon
   ```

### Paso 3: Instalación Automática

Ejecutar el instalador automático:

```cmd
python install.py
```

Este script realizará:
- ✅ Verificación de versión de Python
- ✅ Creación de entorno virtual (`venv`)
- ✅ Instalación de todas las dependencias necesarias
- ✅ Creación de directorios necesarios (`data/`, `logs/`, `backups/`)
- ✅ Creación de archivo de configuración inicial (`config.json`)
- ✅ Creación de scripts de ejecución (`run.bat`, `setup.bat`)

**Nota:** Si aparece un error sobre "externally-managed-environment", el script creará automáticamente un entorno virtual para evitar este problema.

### Paso 4: Configuración Inicial

Ejecutar el asistente de configuración:

```cmd
python setup.py
```

O usar el script creado:

```cmd
setup.bat
```

El asistente guiará para:
- Configurar URL del servidor (ej: `https://newmoon.posmoon.com.ar`)
- Configurar ID Cliente Moon (ej: `14`)
- Probar conexión al servidor
- Sincronización inicial de usuarios y productos

### Paso 5: Primera Ejecución

**Opción A: Usando el script (Recomendado)**

```cmd
run.bat
```

**Opción B: Manualmente**

```cmd
venv\Scripts\activate
python main.py
```

### Paso 6: Verificar Funcionamiento

1. Se abrirá la ventana de login
2. Si hay conexión a internet, se sincronizarán usuarios y productos automáticamente
3. Ingresar con las credenciales del sistema online
4. Verificar que se carguen productos y clientes

---

## Instalación en Linux

### Paso 1: Instalar Python y Dependencias del Sistema

**Ubuntu/Debian:**
```bash
sudo apt-get update
sudo apt-get install python3 python3-pip python3-venv python3-tk git
```

**CentOS/RHEL:**
```bash
sudo yum install python3 python3-pip python3-tkinter git
```

**Fedora:**
```bash
sudo dnf install python3 python3-pip python3-tkinter git
```

**Verificar instalación:**
```bash
python3 --version
```
Debe mostrar: `Python 3.7.x` o superior

### Paso 2: Descargar el Sistema

**Opción A: Desde Git (Recomendado)**

```bash
git clone https://github.com/Moon-Gitub/demonew.git
cd demonew/pos-offline-moon
```

**Opción B: Descargar ZIP**

```bash
wget https://github.com/Moon-Gitub/demonew/archive/main.zip
unzip main.zip
cd demonew-main/pos-offline-moon
```

### Paso 3: Instalación Automática

Ejecutar el instalador automático:

```bash
python3 install.py
```

O dar permisos de ejecución y usar:

```bash
chmod +x install.py
./install.py
```

Este script realizará:
- ✅ Verificación de versión de Python
- ✅ Creación de entorno virtual (`venv`)
- ✅ Instalación de todas las dependencias necesarias
- ✅ Creación de directorios necesarios
- ✅ Creación de archivo de configuración inicial
- ✅ Creación de scripts de ejecución (`run.sh`, `setup.sh`)

### Paso 4: Configuración Inicial

Ejecutar el asistente de configuración:

```bash
python3 setup.py
```

O usar el script creado:

```bash
chmod +x setup.sh
./setup.sh
```

### Paso 5: Primera Ejecución

**Opción A: Usando el script (Recomendado)**

```bash
chmod +x run.sh
./run.sh
```

**Opción B: Manualmente**

```bash
source venv/bin/activate
python3 main.py
```

### Paso 6: Verificar Funcionamiento

1. Se abrirá la ventana de login
2. Si hay conexión a internet, se sincronizarán usuarios y productos automáticamente
3. Ingresar con las credenciales del sistema online
4. Verificar que se carguen productos y clientes

---

## Configuración Inicial

### Archivo `config.json`

El archivo de configuración se crea automáticamente durante la instalación. Está ubicado en:

```
pos-offline-moon/config.json
```

**Estructura del archivo:**

```json
{
    "server_url": "https://newmoon.posmoon.com.ar",
    "api_base": "https://newmoon.posmoon.com.ar/api",
    "id_cliente_moon": 14,
    "sync_interval": 60,
    "connection_check_interval": 5,
    "account_check_interval": 300
}
```

**Descripción de parámetros:**

- `server_url`: URL base del servidor POS Moon online
- `api_base`: URL base de la API (generalmente `server_url/api`)
- `id_cliente_moon`: ID del cliente Moon asignado (número)
- `sync_interval`: Intervalo de sincronización automática en segundos (60 = 1 minuto)
- `connection_check_interval`: Intervalo para verificar conexión en segundos (5 = cada 5 segundos)
- `account_check_interval`: Intervalo para verificar estado de cuenta en segundos (300 = 5 minutos)

### Configuración Manual

Si prefieres configurar manualmente, edita `config.json` con un editor de texto:

**Windows:**
```cmd
notepad config.json
```

**Linux:**
```bash
nano config.json
# o
vim config.json
```

---

## Primera Ejecución

### Proceso de Inicio

1. **Ejecutar el sistema:**
   - Windows: `run.bat` o `python main.py`
   - Linux: `./run.sh` o `python3 main.py`

2. **Ventana de Login:**
   - Se abrirá automáticamente
   - Si hay conexión, se sincronizarán usuarios y productos en segundo plano

3. **Sincronización Inicial:**
   - Usuarios: Se descargan desde el servidor
   - Productos: Se descargan desde el servidor
   - Estado de cuenta: Se verifica antes de permitir login
   - Clientes: Se cargan cuando se necesita

4. **Login:**
   - Usar las mismas credenciales del sistema online
   - El sistema validará el estado de cuenta antes de permitir acceso

5. **Interfaz Principal:**
   - Panel de productos (izquierda)
   - Carrito de compras (centro)
   - Panel de acciones y pago (derecha)

---

## Archivos y Configuraciones

### Estructura de Directorios

```
pos-offline-moon/
├── data/                    # Base de datos local (SQLite)
│   └── pos_local.db        # Base de datos principal
├── logs/                    # Archivos de log (si se configuran)
├── backups/                 # Backups automáticos (si se configuran)
├── venv/                    # Entorno virtual Python
├── config.json              # Configuración principal
├── config.json.example      # Ejemplo de configuración
├── requirements.txt          # Dependencias Python
├── install.py               # Instalador automático
├── setup.py                 # Asistente de configuración
├── main.py                  # Punto de entrada principal
├── gui.py                   # Interfaz gráfica
├── database.py              # Modelos de base de datos
├── sync.py                  # Sincronización con servidor
├── auth.py                  # Autenticación
├── connection.py            # Detección de conexión
├── config.py                # Gestión de configuración
├── run.bat                  # Script ejecución Windows
├── run.sh                   # Script ejecución Linux
├── setup.bat                # Script setup Windows
├── setup.sh                 # Script setup Linux
└── README.md                # Documentación principal
```

### Archivos Importantes

**`config.json`** - Configuración principal del sistema
- Se crea automáticamente durante la instalación
- Contiene URLs del servidor, ID cliente, intervalos de sincronización

**`data/pos_local.db`** - Base de datos local SQLite
- Contiene productos, ventas, usuarios sincronizados
- Se crea automáticamente en la primera ejecución
- **IMPORTANTE**: Hacer backup periódico de este archivo

**`venv/`** - Entorno virtual Python
- Contiene todas las dependencias instaladas
- No debe modificarse manualmente
- Se recrea si se ejecuta `install.py` nuevamente

### Scripts de Ejecución

**Windows:**
- `run.bat`: Ejecuta el sistema con entorno virtual activado
- `setup.bat`: Ejecuta el asistente de configuración

**Linux:**
- `run.sh`: Ejecuta el sistema con entorno virtual activado
- `setup.sh`: Ejecuta el asistente de configuración

**Dar permisos de ejecución (Linux):**
```bash
chmod +x run.sh setup.sh
```

---

## Solución de Problemas

### Error: "No module named 'tkinter'"

**Windows:**
- Tkinter viene incluido con Python. Si aparece este error, reinstalar Python marcando "tcl/tk" durante la instalación.

**Linux:**
```bash
sudo apt-get install python3-tk
```

### Error: "externally-managed-environment"

**Solución:**
- El script `install.py` crea automáticamente un entorno virtual para evitar este problema
- Si aparece el error, ejecutar: `python install.py`

### Error de conexión al servidor

**Verificar:**
1. Que `config.json` tenga la URL correcta del servidor
2. Que haya conexión a internet
3. Que el servidor esté accesible desde el navegador

**Probar conexión:**
```bash
# Linux
curl https://newmoon.posmoon.com.ar

# Windows (PowerShell)
Invoke-WebRequest https://newmoon.posmoon.com.ar
```

### Error de base de datos

**Solución:**
1. Hacer backup de `data/pos_local.db` (si contiene datos importantes)
2. Eliminar `data/pos_local.db`
3. Reiniciar el sistema (se creará una nueva base de datos)

### Error: "Cuenta bloqueada" o "Acceso denegado"

**Causas:**
- La cuenta está vencida o sin pago
- El estado de cuenta no se pudo verificar

**Solución:**
1. Realizar el pago correspondiente en el sistema online
2. Esperar unos minutos y volver a intentar
3. Verificar que `id_cliente_moon` en `config.json` sea correcto

### El sistema no inicia

**Windows:**
```cmd
# Verificar Python
python --version

# Verificar que el entorno virtual existe
dir venv\Scripts

# Activar entorno virtual manualmente
venv\Scripts\activate
python main.py
```

**Linux:**
```bash
# Verificar Python
python3 --version

# Verificar que el entorno virtual existe
ls venv/bin

# Activar entorno virtual manualmente
source venv/bin/activate
python3 main.py
```

### Error al sincronizar productos/usuarios

**Verificar:**
1. Conexión a internet activa
2. URL del servidor correcta en `config.json`
3. `id_cliente_moon` correcto en `config.json`
4. Que el servidor esté accesible

**Probar sincronización manual:**
- Usar el botón "Sincronizar" en la interfaz
- Verificar los logs en la consola para ver errores específicos

---

## Actualización y Mantenimiento

### Actualizar el Sistema

1. **Hacer backup:**
   ```bash
   # Backup de configuración
   cp config.json config.json.backup
   
   # Backup de base de datos
   cp data/pos_local.db data/pos_local.db.backup
   ```

2. **Descargar nueva versión:**
   ```bash
   # Si usas Git
   git pull origin main
   
   # Si descargaste ZIP, reemplazar archivos (excepto config.json y data/)
   ```

3. **Reinstalar dependencias:**
   ```bash
   python install.py
   # o
   python3 install.py
   ```

4. **Verificar configuración:**
   - Comparar `config.json` con `config.json.example` si hay cambios
   - Ajustar configuración si es necesario

### Mantenimiento Regular

**Backups:**
- Hacer backup periódico de `data/pos_local.db`
- Hacer backup de `config.json` si se modifican configuraciones

**Limpieza:**
- Los logs se pueden limpiar periódicamente si ocupan mucho espacio
- La base de datos se limpia automáticamente (no requiere mantenimiento manual)

**Actualización de dependencias:**
```bash
# Activar entorno virtual
source venv/bin/activate  # Linux
# o
venv\Scripts\activate     # Windows

# Actualizar dependencias
pip install --upgrade -r requirements.txt
```

### Desinstalar

Simplemente eliminar la carpeta del proyecto:

```bash
# Windows
rmdir /s pos-offline-moon

# Linux
rm -rf pos-offline-moon
```

**Nota:** Los datos están en `data/` - hacer backup antes de eliminar si se necesita conservar.

---

## Comandos Útiles

### Windows

```cmd
# Activar entorno virtual
venv\Scripts\activate

# Ejecutar sistema
python main.py

# Ejecutar instalador
python install.py

# Ejecutar configuración
python setup.py

# Ver versión Python
python --version

# Listar dependencias instaladas
pip list
```

### Linux

```bash
# Activar entorno virtual
source venv/bin/activate

# Ejecutar sistema
python3 main.py

# Ejecutar instalador
python3 install.py

# Ejecutar configuración
python3 setup.py

# Ver versión Python
python3 --version

# Listar dependencias instaladas
pip list

# Ver permisos de scripts
ls -l *.sh

# Dar permisos de ejecución
chmod +x *.sh
```

---

## Soporte Adicional

Para más información:
- Consultar `README.md` para información general
- Consultar `INSTALACION.md` para guía rápida
- Revisar logs en la consola durante la ejecución
- Verificar configuración en `config.json`

---

**Última actualización**: Diciembre 2024
