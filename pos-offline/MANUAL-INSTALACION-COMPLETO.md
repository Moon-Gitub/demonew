# 📘 Manual Completo de Instalación - POS Offline Moon
## Guía Paso a Paso para Windows y Linux

---

## 📋 Índice

1. [Requisitos Previos](#requisitos-previos)
2. [Instalación en Windows](#instalación-en-windows)
3. [Instalación en Linux](#instalación-en-linux)
4. [Configuración Inicial](#configuración-inicial)
5. [Primera Ejecución](#primera-ejecución)
6. [Uso Diario](#uso-diario)
7. [Solución de Problemas](#solución-de-problemas)
8. [Preguntas Frecuentes](#preguntas-frecuentes)

---

## 🔧 Requisitos Previos

### Para Windows:
- ✅ Windows 7 o superior
- ✅ Python 3.7 o superior
- ✅ Conexión a internet (para descargar dependencias y sincronizar)
- ✅ 500 MB de espacio libre en disco

### Para Linux:
- ✅ Ubuntu 18.04+ / Debian 10+ / CentOS 7+ / Fedora 30+
- ✅ Python 3.7 o superior
- ✅ Conexión a internet (para descargar dependencias y sincronizar)
- ✅ 500 MB de espacio libre en disco
- ✅ Permisos de escritura en la carpeta del proyecto

---

## 🪟 Instalación en Windows

### Paso 1: Verificar Python

1. **Abrir PowerShell o CMD** (Presiona `Win + R`, escribe `cmd` o `powershell` y presiona Enter)

2. **Verificar que Python esté instalado:**
   ```cmd
   python --version
   ```
   
   Debe mostrar algo como: `Python 3.9.7` o superior.

3. **Si NO tienes Python:**
   - Descarga desde: https://www.python.org/downloads/
   - ⚠️ **IMPORTANTE**: Durante la instalación, marca la casilla **"Add Python to PATH"**
   - Instala Python 3.7 o superior
   - Reinicia la terminal después de instalar

### Paso 2: Navegar a la Carpeta del Sistema

1. **Abrir PowerShell o CMD**

2. **Ir a la carpeta del sistema:**
   ```cmd
   cd C:\ruta\a\tu\proyecto\pos-offline
   ```
   
   O si estás en la raíz del proyecto:
   ```cmd
   cd pos-offline
   ```

3. **Verificar que estás en la carpeta correcta:**
   ```cmd
   dir
   ```
   
   Debes ver archivos como: `main.py`, `install.py`, `setup.py`, `requirements.txt`

### Paso 3: Instalar Dependencias y Crear Entorno Virtual

1. **Ejecutar el instalador automático:**
   ```cmd
   python install.py
   ```

2. **¿Qué hace este comando?**
   - ✅ Verifica que tengas Python 3.7+
   - ✅ Crea el entorno virtual (`venv\`)
   - ✅ Instala todas las dependencias necesarias:
     - `requests` (para comunicación con servidor)
     - `sqlalchemy` (para base de datos local)
     - `bcrypt` (para encriptación de contraseñas)
     - `Pillow` (para imágenes)
     - `pyinstaller` (para crear ejecutables)
   - ✅ Crea los directorios necesarios (`data\`, `logs\`, `backups\`)
   - ✅ Crea el archivo `config.json` si no existe
   - ✅ Crea los scripts `run.bat` y `setup.bat`

3. **Tiempo estimado:** 2-5 minutos (depende de tu conexión)

4. **Si todo salió bien, verás:**
   ```
   ✅ Instalación completada exitosamente!
   ✅ Entorno virtual creado en: venv\
   ✅ Dependencias instaladas
   ✅ Directorios creados
   ```

### Paso 4: Configurar el Sistema (Primera Vez)

1. **Ejecutar el asistente de configuración:**
   ```cmd
   python setup.py
   ```
   
   O usar el script creado:
   ```cmd
   setup.bat
   ```

2. **El asistente te preguntará:**
   
   **a) URL del servidor:**
   ```
   Ingrese la URL del servidor (ej: https://newmoon.posmoon.com.ar):
   ```
   - Ingresa la URL completa sin barra final
   - Ejemplo: `https://newmoon.posmoon.com.ar`
   
   **b) URL de la API:**
   ```
   Ingrese la URL base de la API (ej: https://newmoon.posmoon.com.ar/api):
   ```
   - Generalmente es: `https://newmoon.posmoon.com.ar/api`
   
   **c) ID Cliente Moon:**
   ```
   Ingrese su ID Cliente Moon (número):
   ```
   - Este es tu número de cuenta en el sistema
   - Lo puedes obtener del sistema online o preguntar a soporte
   - Ejemplo: `14`
   
   **d) ID Empresa:**
   ```
   Ingrese su ID Empresa (número, por defecto 1):
   ```
   - Generalmente es `1` si tienes una sola empresa
   - Presiona Enter para usar el valor por defecto
   
   **e) Intervalo de sincronización:**
   ```
   Intervalo de sincronización automática en segundos (60):
   ```
   - Presiona Enter para usar 60 segundos (recomendado)
   - O ingresa otro valor si prefieres

3. **El sistema intentará:**
   - Conectarse al servidor
   - Verificar que la configuración sea correcta
   - Hacer una sincronización inicial (si hay internet)

4. **Si todo salió bien, verás:**
   ```
   ✅ Configuración guardada exitosamente en config.json
   ✅ Conexión al servidor verificada
   ✅ Sincronización inicial completada
   ```

### Paso 5: Ejecutar la Aplicación

1. **Opción A: Usar el script (Recomendado)**
   ```cmd
   run.bat
   ```
   
   Este script:
   - Activa automáticamente el entorno virtual
   - Ejecuta la aplicación
   - Muestra errores si los hay

2. **Opción B: Ejecución manual**
   ```cmd
   venv\Scripts\activate
   python main.py
   ```

3. **La primera vez que ejecutes:**
   - Se abrirá una ventana de login
   - Si hay conexión a internet, se sincronizarán usuarios y productos automáticamente
   - Verás mensajes en la consola indicando el progreso

4. **Login:**
   - Usa las mismas credenciales que usas en el sistema online
   - El sistema validará tu estado de cuenta antes de permitir acceso
   - Si tu cuenta está bloqueada, no podrás acceder

---

## 🐧 Instalación en Linux

### Paso 1: Instalar Dependencias del Sistema

**Para Ubuntu/Debian:**
```bash
sudo apt-get update
sudo apt-get install -y python3 python3-pip python3-venv python3-tk git
```

**Para CentOS/RHEL:**
```bash
sudo yum install -y python3 python3-pip python3-tkinter git
```

**Para Fedora:**
```bash
sudo dnf install -y python3 python3-pip python3-tkinter git
```

**Para Arch Linux:**
```bash
sudo pacman -S python python-pip tk git
```

### Paso 2: Verificar Python

```bash
python3 --version
```

Debe mostrar: `Python 3.7.x` o superior.

### Paso 3: Navegar a la Carpeta del Sistema

```bash
cd /ruta/a/tu/proyecto/pos-offline
```

O si estás en la raíz del proyecto:
```bash
cd pos-offline
```

**Verificar que estás en la carpeta correcta:**
```bash
ls -la
```

Debes ver archivos como: `main.py`, `install.py`, `setup.py`, `requirements.txt`

### Paso 4: Instalar Dependencias y Crear Entorno Virtual

1. **Dar permisos de ejecución (si es necesario):**
   ```bash
   chmod +x install.py
   ```

2. **Ejecutar el instalador automático:**
   ```bash
   python3 install.py
   ```

3. **¿Qué hace este comando?**
   - ✅ Verifica que tengas Python 3.7+
   - ✅ Crea el entorno virtual (`venv/`)
   - ✅ Instala todas las dependencias necesarias
   - ✅ Crea los directorios necesarios (`data/`, `logs/`, `backups/`)
   - ✅ Crea el archivo `config.json` si no existe
   - ✅ Crea los scripts `run.sh` y `setup.sh` con permisos de ejecución

4. **Tiempo estimado:** 2-5 minutos

5. **Si todo salió bien, verás:**
   ```
   ✅ Instalación completada exitosamente!
   ✅ Entorno virtual creado en: venv/
   ✅ Dependencias instaladas
   ✅ Directorios creados
   ```

### Paso 5: Configurar el Sistema (Primera Vez)

1. **Dar permisos de ejecución:**
   ```bash
   chmod +x setup.sh
   ```

2. **Ejecutar el asistente de configuración:**
   ```bash
   ./setup.sh
   ```
   
   O manualmente:
   ```bash
   source venv/bin/activate
   python3 setup.py
   ```

3. **El asistente te preguntará lo mismo que en Windows:**
   - URL del servidor
   - URL de la API
   - ID Cliente Moon
   - ID Empresa
   - Intervalo de sincronización

4. **Si todo salió bien, verás:**
   ```
   ✅ Configuración guardada exitosamente en config.json
   ✅ Conexión al servidor verificada
   ✅ Sincronización inicial completada
   ```

### Paso 6: Ejecutar la Aplicación

1. **Opción A: Usar el script (Recomendado)**
   ```bash
   chmod +x run.sh
   ./run.sh
   ```
   
   Este script:
   - Activa automáticamente el entorno virtual
   - Ejecuta la aplicación
   - Muestra errores si los hay

2. **Opción B: Ejecución manual**
   ```bash
   source venv/bin/activate
   python3 main.py
   ```

3. **La primera vez que ejecutes:**
   - Se abrirá una ventana de login
   - Si hay conexión a internet, se sincronizarán usuarios y productos automáticamente
   - Verás mensajes en la terminal indicando el progreso

4. **Login:**
   - Usa las mismas credenciales que usas en el sistema online
   - El sistema validará tu estado de cuenta antes de permitir acceso

---

## ⚙️ Configuración Inicial

### Archivo `config.json`

Después de ejecutar `setup.py`, se crea el archivo `config.json` con esta estructura:

```json
{
    "server_url": "https://newmoon.posmoon.com.ar",
    "api_base": "https://newmoon.posmoon.com.ar/api",
    "id_cliente_moon": 14,
    "id_empresa": 1,
    "sync_interval": 60,
    "connection_check_interval": 5,
    "account_check_interval": 300
}
```

**Parámetros explicados:**
- `server_url`: URL base del servidor POS Moon
- `api_base`: URL base de la API (generalmente termina en `/api`)
- `id_cliente_moon`: Tu número de cuenta en el sistema
- `id_empresa`: ID de tu empresa (generalmente 1)
- `sync_interval`: Cada cuántos segundos se sincroniza automáticamente (60 = 1 minuto)
- `connection_check_interval`: Cada cuántos segundos verifica conexión (5 = cada 5 segundos)
- `account_check_interval`: Cada cuántos segundos verifica estado de cuenta (300 = 5 minutos)

### Editar Configuración Manualmente

**Windows:**
```cmd
notepad config.json
```

**Linux:**
```bash
nano config.json
# o
gedit config.json
```

---

## 🚀 Primera Ejecución

### Proceso Completo

1. **Ejecutar la aplicación** (ver pasos anteriores)

2. **Ventana de Login:**
   - Se abrirá automáticamente
   - Si hay conexión, verás "🟢 En línea"
   - Si no hay conexión, verás "🔴 Sin conexión"

3. **Sincronización Inicial:**
   - Si hay conexión, el sistema automáticamente:
     - Descarga usuarios desde el servidor
     - Descarga productos desde el servidor
     - Verifica tu estado de cuenta
   - Esto puede tardar 1-2 minutos la primera vez

4. **Login:**
   - Ingresa tu **usuario** (el mismo que usas en el sistema online)
   - Ingresa tu **contraseña** (la misma que usas en el sistema online)
   - Haz clic en **"Ingresar"**

5. **Validación:**
   - El sistema verifica tu estado de cuenta
   - Si tu cuenta está al día, te permite acceder
   - Si tu cuenta está bloqueada, verás un mensaje de error

6. **Interfaz Principal:**
   - Una vez dentro, verás la interfaz de ventas
   - Puedes trabajar offline (sin internet)
   - Las ventas se guardan localmente
   - Se sincronizan automáticamente cuando hay conexión

---

## 📅 Uso Diario

### Ejecutar el Sistema

**Windows:**
```cmd
cd pos-offline
run.bat
```

**Linux:**
```bash
cd pos-offline
./run.sh
```

### Flujo de Trabajo Normal

1. **Abrir la aplicación** (usando `run.bat` o `./run.sh`)

2. **Login:**
   - Ingresar usuario y contraseña
   - El sistema verifica estado de cuenta automáticamente

3. **Trabajar:**
   - Buscar productos
   - Agregar al carrito
   - Seleccionar cliente
   - Seleccionar método de pago
   - Cobrar venta

4. **Sincronización:**
   - **Automática**: Cuando detecta conexión, sincroniza ventas automáticamente
   - **Manual**: Botón "Sincronizar" en la interfaz

5. **Cerrar:**
   - Cerrar la ventana normalmente
   - Las ventas pendientes se sincronizarán la próxima vez que haya conexión

### Trabajar Offline

- ✅ Puedes crear ventas sin conexión a internet
- ✅ Las ventas se guardan localmente en la base de datos SQLite
- ✅ Cuando vuelva la conexión, se sincronizan automáticamente
- ✅ Los productos se actualizan cuando hay conexión

---

## 🆘 Solución de Problemas

### Error: "python: no se reconoce como comando"

**Windows:**
- Python no está en el PATH
- Solución: Reinstalar Python marcando "Add Python to PATH"
- O usar la ruta completa: `C:\Python39\python.exe install.py`

**Linux:**
- Usar `python3` en lugar de `python`
- O crear un alias: `alias python=python3`

### Error: "No module named 'tkinter'"

**Windows:**
- Reinstalar Python marcando "tcl/tk" durante la instalación

**Linux:**
```bash
# Ubuntu/Debian
sudo apt-get install python3-tk

# CentOS/RHEL
sudo yum install python3-tkinter

# Fedora
sudo dnf install python3-tkinter
```

### Error: "externally-managed-environment"

**Solución:**
- El script `install.py` crea automáticamente un entorno virtual
- Ejecutar: `python install.py` (o `python3 install.py`)
- NO instalar paquetes globalmente

### Error: "cannot access local variable 'get_session'"

**Causa:** Versión desactualizada del código

**Solución:**
1. Actualizar el código desde el repositorio
2. Ejecutar `python install.py` nuevamente
3. Reiniciar la aplicación

### Error de conexión al servidor

**Verificar:**
1. URL correcta en `config.json`
2. Conexión a internet activa
3. Servidor accesible desde navegador

**Probar conexión:**

**Windows (PowerShell):**
```powershell
Invoke-WebRequest https://newmoon.posmoon.com.ar
```

**Linux:**
```bash
curl https://newmoon.posmoon.com.ar
```

### Error: "Cuenta bloqueada"

**Causa:**
- Cuenta vencida o sin pago

**Solución:**
1. Realizar pago en el sistema online
2. Esperar 5-10 minutos
3. Verificar `id_cliente_moon` en `config.json`
4. Intentar login nuevamente

### El sistema no inicia

**Windows:**
```cmd
# Verificar Python
python --version

# Activar entorno virtual manualmente
venv\Scripts\activate

# Ejecutar con mensajes de error
python main.py
```

**Linux:**
```bash
# Verificar Python
python3 --version

# Activar entorno virtual manualmente
source venv/bin/activate

# Ejecutar con mensajes de error
python3 main.py
```

### Error: "Permission denied" (Linux)

**Solución:**
```bash
# Dar permisos de ejecución
chmod +x run.sh
chmod +x setup.sh
chmod +x install.py

# O ejecutar con bash explícitamente
bash run.sh
```

### La ventana no se abre (Linux)

**Causa:** Falta tkinter o no hay servidor X

**Solución:**
```bash
# Instalar tkinter
sudo apt-get install python3-tk

# Si estás en servidor sin GUI, necesitas X11 forwarding
# O usar una máquina con interfaz gráfica
```

### Base de datos corrupta

**Solución:**
```bash
# Hacer backup
cp data/pos_local.db data/pos_local.db.backup

# Eliminar base de datos corrupta
rm data/pos_local.db

# Reiniciar aplicación (creará nueva BD)
./run.sh
```

---

## ❓ Preguntas Frecuentes

### ¿Necesito internet para usar el sistema?

**No.** Puedes trabajar completamente offline. Solo necesitas internet para:
- Sincronizar ventas al servidor
- Descargar productos actualizados
- Verificar estado de cuenta

### ¿Dónde se guardan las ventas offline?

En la base de datos local SQLite: `data/pos_local.db`

### ¿Cómo hago backup de mis datos?

**Windows:**
```cmd
copy data\pos_local.db backups\pos_local_YYYYMMDD.db
```

**Linux:**
```bash
cp data/pos_local.db backups/pos_local_$(date +%Y%m%d).db
```

### ¿Puedo usar el sistema en múltiples computadoras?

**Sí**, pero cada computadora tiene su propia base de datos local. Las ventas se sincronizan al servidor desde cada computadora.

### ¿Cómo actualizo el sistema?

1. Descargar nueva versión
2. Reemplazar archivos (excepto `config.json` y `data/`)
3. Ejecutar `python install.py` nuevamente
4. Reiniciar aplicación

### ¿Puedo crear un ejecutable (.exe en Windows)?

**Sí:**
```bash
python build_exe.py
```

El ejecutable estará en `dist/POS-Offline-Moon.exe` (Windows) o `dist/POS-Offline-Moon` (Linux)

### ¿Qué pasa si pierdo la conexión mientras trabajo?

**Nada.** El sistema sigue funcionando normalmente. Las ventas se guardan localmente y se sincronizan cuando vuelva la conexión.

### ¿Cómo veo las ventas sincronizadas?

- Botón "Ver Ventas (Últimos 30 días)" en la interfaz
- O consultar directamente en el sistema online

### ¿Puedo cambiar la configuración después de instalado?

**Sí**, edita `config.json` o ejecuta `setup.py` nuevamente.

---

## 📞 Soporte

Si tienes problemas que no se resuelven con esta guía:

1. Revisar los logs en `logs/`
2. Verificar `config.json`
3. Contactar a soporte con:
   - Sistema operativo
   - Versión de Python (`python --version`)
   - Mensaje de error completo
   - Logs relevantes

---

**Última actualización:** Enero 2025
**Versión del sistema:** 1.0
