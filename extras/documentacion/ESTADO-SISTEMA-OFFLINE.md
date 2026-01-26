# Estado Actual del Sistema Offline - POS Moon

## 📍 Ubicación del Sistema

El sistema offline se encuentra en dos ubicaciones:

1. **`pos-offline/`** (raíz del proyecto) - Versión con configuración local
2. **`extras/pos-offline-moon/`** - Versión completa con mejoras y documentación

**Recomendación**: Usar `extras/pos-offline-moon/` como versión principal.

---

## 🔍 Estado Actual del Sistema

### Componentes Implementados

✅ **Sistema Base**
- Interfaz gráfica con Tkinter (GUI moderna)
- Base de datos local SQLite (`data/pos_local.db`)
- Sistema de autenticación con validación de cuenta
- Sincronización automática con servidor online
- Detección automática de conexión a internet

✅ **Funcionalidades**
- Login con validación de estado de cuenta
- Gestión de productos (descarga desde servidor)
- Gestión de clientes
- Creación de ventas offline
- Sincronización de ventas cuando hay conexión
- Verificación periódica de estado de cuenta
- Interfaz de venta similar al sistema online

✅ **Sincronización**
- Sincronización automática cuando detecta conexión
- Sincronización manual desde la interfaz
- Descarga de productos desde servidor
- Subida de ventas al servidor
- Sincronización de usuarios
- Verificación de estado de cuenta

✅ **Instalación y Configuración**
- Instalador automático (`install.py`)
- Asistente de configuración (`setup.py`)
- Scripts de ejecución para Windows y Linux
- Entorno virtual automático
- Creación de accesos directos

✅ **Documentación**
- README.md con guía rápida
- INSTALACION.md con guía detallada
- INICIO-RAPIDO.md para inicio rápido
- Documentación de mejoras (impresión local, etc.)

### Archivos Principales

```
extras/pos-offline-moon/
├── main.py              # Punto de entrada principal
├── gui.py               # Interfaz gráfica (Tkinter)
├── database.py          # Modelos de base de datos (SQLAlchemy)
├── sync.py              # Sincronización con servidor
├── auth.py              # Autenticación y validación
├── connection.py        # Detección de conexión
├── config.py            # Gestión de configuración
├── install.py           # Instalador automático
├── setup.py             # Asistente de configuración
├── build_exe.py         # Generador de ejecutable
├── requirements.txt     # Dependencias Python
├── config.json.example  # Ejemplo de configuración
└── data/                # Base de datos local (SQLite)
```

### Dependencias

```
requests==2.31.0
sqlalchemy==2.0.23
bcrypt==4.1.2
Pillow==10.1.0
pyinstaller==6.3.0
```

---

## 🪟 CÓMO EJECUTAR EN WINDOWS

### Opción 1: Instalación Rápida (Recomendado)

1. **Abrir PowerShell o CMD** en la carpeta del proyecto:
   ```cmd
   cd extras\pos-offline-moon
   ```

2. **Ejecutar instalador**:
   ```cmd
   python install.py
   ```
   Esto creará:
   - Entorno virtual (`venv/`)
   - Instalará dependencias
   - Creará directorios necesarios
   - Creará scripts de ejecución (`run.bat`, `setup.bat`)

3. **Configurar sistema** (primera vez):
   ```cmd
   python setup.py
   ```
   O usar el script:
   ```cmd
   setup.bat
   ```
   El asistente pedirá:
   - URL del servidor (ej: `https://newmoon.posmoon.com.ar`)
   - ID Cliente Moon (número)
   - Intervalo de sincronización

4. **Ejecutar sistema**:
   ```cmd
   run.bat
   ```
   O manualmente:
   ```cmd
   venv\Scripts\activate
   python main.py
   ```

### Opción 2: Ejecución Manual

1. **Activar entorno virtual**:
   ```cmd
   cd extras\pos-offline-moon
   venv\Scripts\activate
   ```

2. **Verificar configuración**:
   - Editar `config.json` si es necesario
   - Verificar que tenga la URL del servidor y ID cliente

3. **Ejecutar**:
   ```cmd
   python main.py
   ```

### Requisitos Windows

- ✅ Python 3.7 o superior
- ✅ Marcar "Add Python to PATH" durante instalación de Python
- ✅ Tkinter (viene incluido con Python)

### Verificar Instalación

```cmd
# Verificar Python
python --version

# Verificar entorno virtual
dir venv\Scripts

# Verificar dependencias
venv\Scripts\activate
pip list
```

---

## 🐧 CÓMO EJECUTAR EN LINUX

### Opción 1: Instalación Rápida (Recomendado)

1. **Instalar dependencias del sistema**:
   ```bash
   # Ubuntu/Debian
   sudo apt-get update
   sudo apt-get install python3 python3-pip python3-venv python3-tk git
   
   # CentOS/RHEL
   sudo yum install python3 python3-pip python3-tkinter git
   
   # Fedora
   sudo dnf install python3 python3-pip python3-tkinter git
   ```

2. **Navegar a la carpeta**:
   ```bash
   cd extras/pos-offline-moon
   ```

3. **Ejecutar instalador**:
   ```bash
   python3 install.py
   ```
   O con permisos:
   ```bash
   chmod +x install.py
   ./install.py
   ```

4. **Configurar sistema** (primera vez):
   ```bash
   python3 setup.py
   ```
   O usar el script:
   ```bash
   chmod +x setup.sh
   ./setup.sh
   ```

5. **Ejecutar sistema**:
   ```bash
   chmod +x run.sh
   ./run.sh
   ```
   O manualmente:
   ```bash
   source venv/bin/activate
   python3 main.py
   ```

### Opción 2: Ejecución Manual

1. **Activar entorno virtual**:
   ```bash
   cd extras/pos-offline-moon
   source venv/bin/activate
   ```

2. **Verificar configuración**:
   ```bash
   cat config.json
   ```
   Editar si es necesario:
   ```bash
   nano config.json
   ```

3. **Ejecutar**:
   ```bash
   python3 main.py
   ```

### Requisitos Linux

- ✅ Python 3.7 o superior
- ✅ python3-tk (para interfaz gráfica)
- ✅ python3-venv (para entorno virtual)
- ✅ pip3

### Verificar Instalación

```bash
# Verificar Python
python3 --version

# Verificar entorno virtual
ls venv/bin

# Verificar dependencias
source venv/bin/activate
pip list

# Verificar tkinter
python3 -c "import tkinter; print('Tkinter OK')"
```

---

## ⚙️ Configuración

### Archivo `config.json`

Ubicación: `extras/pos-offline-moon/config.json`

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

**Parámetros:**
- `server_url`: URL base del servidor POS Moon
- `api_base`: URL base de la API
- `id_cliente_moon`: ID del cliente Moon (número)
- `sync_interval`: Intervalo de sincronización automática (segundos)
- `connection_check_interval`: Intervalo para verificar conexión (segundos)
- `account_check_interval`: Intervalo para verificar estado de cuenta (segundos)

---

## 🔄 Flujo de Trabajo

### Primera Ejecución

1. **Instalar**: `python install.py` (o `python3 install.py`)
2. **Configurar**: `python setup.py` (o `python3 setup.py`)
3. **Ejecutar**: `run.bat` (Windows) o `./run.sh` (Linux)
4. **Login**: Usar credenciales del sistema online
5. **Sincronización inicial**: Se descargan usuarios y productos automáticamente

### Uso Normal

1. **Ejecutar**: `run.bat` (Windows) o `./run.sh` (Linux)
2. **Login**: Con credenciales del sistema online
3. **Trabajar offline**: Crear ventas sin conexión
4. **Sincronización automática**: Cuando detecta conexión, sincroniza ventas automáticamente

---

## 🆘 Solución de Problemas Comunes

### Error: "No module named 'tkinter'"

**Windows:**
- Reinstalar Python marcando "tcl/tk" durante la instalación

**Linux:**
```bash
sudo apt-get install python3-tk
```

### Error: "externally-managed-environment"

**Solución:**
- El script `install.py` crea automáticamente un entorno virtual
- Ejecutar: `python install.py` (o `python3 install.py`)

### Error de conexión al servidor

**Verificar:**
1. URL correcta en `config.json`
2. Conexión a internet activa
3. Servidor accesible desde navegador

**Probar:**
```bash
# Linux
curl https://newmoon.posmoon.com.ar

# Windows (PowerShell)
Invoke-WebRequest https://newmoon.posmoon.com.ar
```

### Error: "Cuenta bloqueada"

**Causa:**
- Cuenta vencida o sin pago

**Solución:**
1. Realizar pago en el sistema online
2. Esperar unos minutos
3. Verificar `id_cliente_moon` en `config.json`

### El sistema no inicia

**Windows:**
```cmd
# Verificar Python
python --version

# Activar entorno virtual manualmente
venv\Scripts\activate
python main.py
```

**Linux:**
```bash
# Verificar Python
python3 --version

# Activar entorno virtual manualmente
source venv/bin/activate
python3 main.py
```

---

## 📦 Crear Ejecutable Standalone

Para crear un ejecutable que no requiere Python instalado:

```bash
# Windows o Linux
python build_exe.py
```

El ejecutable estará en:
- Windows: `dist/POS-Offline-Moon.exe`
- Linux: `dist/POS-Offline-Moon`

---

## 📝 Comandos Útiles

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

# Listar dependencias
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

# Listar dependencias
pip list

# Dar permisos a scripts
chmod +x *.sh
```

---

## 🔗 Documentación Adicional

- **README.md**: Guía rápida de instalación
- **INSTALACION.md**: Guía detallada paso a paso
- **INICIO-RAPIDO.md**: Inicio rápido en 3 pasos
- **mejoras/instalacion_sistema_offline.md**: Mejoras del sistema
- **mejoras/impresion_local.md**: Servicio de impresión local

---

**Última actualización**: Enero 2025
