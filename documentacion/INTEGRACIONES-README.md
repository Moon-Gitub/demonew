# 📡 Sistema de Integraciones y Chat con N8N

## ✅ Implementación Completada

Se ha implementado un sistema completo de integraciones con N8N que incluye:

1. **Módulo de Integraciones**: Gestión completa de webhooks y APIs
2. **Chat Asistente Virtual**: Interfaz de chat que se comunica con N8N
3. **Base de datos**: Tabla `integraciones` para almacenar configuraciones

## 📋 Pasos para Completar la Instalación

### 1. Crear la Tabla en la Base de Datos

Ejecuta el siguiente SQL en tu base de datos:

```sql
CREATE TABLE IF NOT EXISTS `integraciones` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) NOT NULL COMMENT 'Nombre de la integración',
  `tipo` varchar(50) NOT NULL COMMENT 'Tipo: n8n, api, webhook, etc.',
  `webhook_url` varchar(500) DEFAULT NULL COMMENT 'URL del webhook',
  `api_key` varchar(255) DEFAULT NULL COMMENT 'API Key si es necesario',
  `descripcion` text DEFAULT NULL COMMENT 'Descripción de la integración',
  `activo` tinyint(1) DEFAULT 1 COMMENT '1=activo, 0=inactivo',
  `fecha_creacion` datetime DEFAULT CURRENT_TIMESTAMP,
  `fecha_actualizacion` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_tipo` (`tipo`),
  KEY `idx_activo` (`activo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabla de integraciones con servicios externos';
```

**O ejecuta el archivo SQL incluido:**
```bash
# El archivo está en: db/crear-tabla-integraciones.sql
```

### 2. Archivos Creados

✅ **Modelos:**
- `modelos/integraciones.modelo.php`

✅ **Controladores:**
- `controladores/integraciones.controlador.php`

✅ **Vistas:**
- `vistas/modulos/integraciones.php`
- `vistas/modulos/chat.php`

✅ **AJAX:**
- `ajax/integraciones.ajax.php`
- `ajax/chat.ajax.php`

✅ **JavaScript:**
- `vistas/js/integraciones.js`
- `vistas/js/chat.js`

### 3. Archivos Modificados

✅ `vistas/modulos/menu.php` - Agregado menú "Integraciones"
✅ `vistas/plantilla.php` - Agregadas rutas y scripts

## 🚀 Cómo Usar

### Paso 1: Configurar Integración N8N

1. Accede al sistema como **Administrador**
2. Ve al menú **"Integraciones"** (después de Proveedores)
3. Haz clic en **"Agregar integración"**
4. Completa el formulario:
   - **Nombre**: Ej: "Chat N8N Principal"
   - **Tipo**: Selecciona "N8N"
   - **Webhook URL**: Pega la URL de tu webhook de N8N
     - Ejemplo: `https://tu-n8n-instance.com/webhook/chat`
   - **API Key**: (Opcional) Si tu webhook requiere autenticación
   - **Descripción**: (Opcional) Descripción de la integración
   - **Activo**: Marca la casilla para activar
5. Haz clic en **"Guardar integración"**

### Paso 2: Usar el Chat

1. Accede a **"Chat"** o **"Asistente Virtual"** desde el menú
2. Si no hay webhook configurado, verás un mensaje de advertencia
3. Una vez configurado, podrás:
   - Escribir mensajes en el chat
   - Enviar preguntas al asistente
   - Recibir respuestas de N8N

## 📡 Formato de Datos que Envía el Sistema

El sistema envía a N8N un JSON con esta estructura:

```json
{
  "mensaje": "Texto del mensaje del usuario",
  "usuario_id": 123,
  "usuario_nombre": "Nombre del Usuario",
  "empresa_id": 1,
  "timestamp": "2024-01-01 12:00:00",
  "historial": [
    {
      "role": "user",
      "content": "Mensaje anterior",
      "timestamp": "12:00"
    }
  ]
}
```

## 📥 Formato de Respuesta Esperado de N8N

N8N debe responder con uno de estos formatos:

**Opción 1 (JSON):**
```json
{
  "respuesta": "Texto de respuesta del asistente"
}
```

**Opción 2 (JSON alternativo):**
```json
{
  "message": "Texto de respuesta del asistente"
}
```

**Opción 3 (JSON alternativo):**
```json
{
  "text": "Texto de respuesta del asistente"
}
```

**Opción 4 (Texto plano):**
```
Texto de respuesta del asistente
```

## 🔧 Características

- ✅ Gestión completa de integraciones (crear, editar, eliminar)
- ✅ Activación/desactivación de integraciones
- ✅ Interfaz de chat moderna y responsive
- ✅ Historial de conversación (últimos 10 mensajes)
- ✅ Indicador de escritura mientras procesa
- ✅ Manejo de errores y validaciones
- ✅ Seguridad AJAX integrada (CSRF, sesión)

## 📝 Notas Importantes

1. **Solo Administradores** pueden acceder al módulo de Integraciones
2. El chat busca automáticamente la primera integración N8N **activa**
3. Si hay múltiples integraciones N8N, se usa la primera activa encontrada
4. El historial se limita a los últimos 10 mensajes para optimizar

## 🐛 Solución de Problemas

### El chat no funciona
- Verifica que hay una integración N8N activa en "Integraciones"
- Verifica que la URL del webhook es correcta
- Revisa la consola del navegador para errores
- Verifica que N8N está respondiendo correctamente

### Error de conexión
- Verifica que la URL del webhook es accesible desde el servidor
- Verifica que N8N está funcionando
- Revisa los logs del servidor para más detalles

## ✅ Estado de la Implementación

Todos los archivos han sido creados y modificados correctamente. Solo falta:

1. ⚠️ **Ejecutar el SQL** para crear la tabla `integraciones`
2. ✅ Configurar la primera integración N8N
3. ✅ Probar el chat

¡Listo para usar! 🎉

