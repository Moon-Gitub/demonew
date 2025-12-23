# Guía de Instalación Detallada - POS Offline Moon

## Paso 1: Verificar Python

Abre terminal/consola y ejecuta:
```bash
python --version
# o
python3 --version
```

Debe mostrar Python 3.7 o superior.

## Paso 2: Descargar el Sistema

1. Descarga o clona el proyecto
2. Extrae en una carpeta (ej: `C:\POS-Offline` o `~/POS-Offline`)

## Paso 3: Instalación Automática

Ejecuta el instalador:
```bash
python install.py
```

Esto instalará:
- ✅ Todas las dependencias necesarias
- ✅ Creará las carpetas necesarias
- ✅ Configurará el sistema básico

## Paso 4: Configuración Inicial

Ejecuta el asistente de configuración:
```bash
python setup.py
```

Sigue las instrucciones en pantalla para:
- Configurar URL del servidor
- Configurar ID Cliente Moon
- Probar conexión
- Sincronización inicial

## Paso 5: Primera Ejecución

```bash
python main.py
```

La primera vez:
1. Se abrirá ventana de login
2. Si hay conexión, se sincronizarán usuarios y productos
3. Ingresa con tus credenciales del sistema online

## 🔧 Configuración Manual

Si prefieres configurar manualmente, edita `config.json`:

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

## 📦 Crear Ejecutable (Opcional)

Para crear un .exe que no requiere Python:

```bash
python build_exe.py
```

El ejecutable estará en `dist/`

## 🔄 Actualizar el Sistema

Para actualizar:
1. Descarga la nueva versión
2. Reemplaza los archivos (excepto `config.json` y `data/`)
3. Ejecuta `python install.py` nuevamente

## 🗑️ Desinstalar

Simplemente elimina la carpeta del proyecto.
Los datos están en `data/` si quieres hacer backup.
