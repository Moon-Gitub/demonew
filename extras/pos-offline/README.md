# POS Offline Moon

Sistema de punto de venta offline con sincronización automática.

## 🚀 Instalación Rápida

### Windows

1. **Descargar Python 3.7+**
   - Descarga desde [python.org](https://www.python.org/downloads/)
   - ✅ Marca "Add Python to PATH" durante instalación

2. **Instalar el sistema**
   ```bash
   # Abre PowerShell o CMD en la carpeta del proyecto
   python install.py
   ```

3. **Configurar**
   ```bash
   python setup.py
   ```

4. **Ejecutar**
   ```bash
   python main.py
   ```

### Linux/Mac

1. **Instalar Python 3.7+** (si no está instalado)
   ```bash
   # Ubuntu/Debian
   sudo apt-get install python3 python3-pip
   
   # Mac
   brew install python3
   ```

2. **Instalar el sistema**
   ```bash
   python3 install.py
   ```

3. **Configurar**
   ```bash
   python3 setup.py
   ```

4. **Ejecutar**
   ```bash
   python3 main.py
   ```

## 📋 Requisitos

- Python 3.7 o superior
- Conexión a internet (para sincronización inicial)
- Acceso al servidor POS Moon

## ⚙️ Configuración

Edita `config.json` con tus datos:

```json
{
    "server_url": "https://tu-servidor.com",
    "api_base": "https://tu-servidor.com/api",
    "id_cliente_moon": 14,
    "sync_interval": 60
}
```

## 🔄 Sincronización

- **Automática**: Se sincroniza cuando detecta conexión
- **Manual**: Botón "Sincronizar" en la interfaz
- **Productos**: Se descargan desde servidor
- **Ventas**: Se suben al servidor cuando hay conexión
- **Estado de cuenta**: Se verifica antes de permitir login
- **Usuarios**: Se sincronizan desde servidor

## 🔐 Autenticación

- Usa las mismas credenciales del sistema online
- Valida estado de cuenta/pago antes de permitir acceso
- Bloquea acceso si la cuenta está vencida o sin pago
- Funciona offline con credenciales sincronizadas

## 📦 Crear Ejecutable

Para crear un ejecutable standalone (no requiere Python instalado):

```bash
python build_exe.py
```

El ejecutable estará en `dist/POS-Offline-Moon.exe` (Windows)

## 🆘 Solución de Problemas

### Error: "No module named 'tkinter'"
**Solución**: Instala tkinter
- Windows: Viene con Python
- Linux: `sudo apt-get install python3-tk`
- Mac: Viene con Python

### Error de conexión
**Solución**: Verifica que `config.json` tenga la URL correcta

### Error de base de datos
**Solución**: Elimina `data/pos_local.db` y reinicia

### Error: "Cuenta bloqueada"
**Solución**: Realiza el pago correspondiente en el sistema online

## 📞 Soporte

Para más ayuda, consulta la documentación completa en `INSTALACION.md`
