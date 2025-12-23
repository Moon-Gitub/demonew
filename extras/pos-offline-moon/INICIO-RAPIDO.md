# Inicio Rápido - POS Offline Moon

## 🚀 Instalación en 3 Pasos

### 1. Instalar dependencias
```bash
python install.py
```

### 2. Configurar sistema
```bash
python setup.py
```

### 3. Ejecutar aplicación
```bash
python main.py
```

## 📝 Configuración Inicial

El asistente `setup.py` te pedirá:
- URL del servidor (ej: https://newmoon.posmoon.com.ar)
- ID Cliente Moon (número de tu cuenta)
- Intervalo de sincronización (por defecto 60 segundos)

## 🔐 Primera Vez

1. Al ejecutar `main.py`, se abrirá ventana de login
2. Si hay conexión, se sincronizarán usuarios automáticamente
3. Ingresa con tus credenciales del sistema online
4. El sistema validará tu estado de cuenta antes de permitir acceso

## ⚠️ Importante

- **Estado de cuenta**: El sistema verifica automáticamente si tu cuenta está al día
- **Sin pago**: No podrás acceder si la cuenta está bloqueada
- **Offline**: Puedes trabajar sin internet, las ventas se sincronizarán cuando vuelva la conexión

## 🔄 Sincronización

- **Automática**: Cuando detecta conexión a internet
- **Manual**: Botón "Sincronizar" en la interfaz
- **Productos**: Se actualizan desde servidor
- **Ventas**: Se suben al servidor cuando hay conexión

## 💡 Tips

- Las ventas offline se guardan localmente y se sincronizan automáticamente
- Puedes ver ventas de últimos 30 días desde el botón "Ver Ventas"
- El sistema verifica estado de cuenta cada 5 minutos
