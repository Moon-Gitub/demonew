# 🤖 Flujo N8N - Asistente Virtual POS Moon (SQL Dinámico)

## 📋 Descripción

Este flujo de N8N proporciona un asistente virtual inteligente para el sistema POS Moon que **genera consultas SQL automáticamente** basándose en las preguntas del usuario en lenguaje natural.

### ✨ Características Principales

- ✅ **Generación automática de SQL**: El AI Agent genera consultas SQL dinámicamente según la pregunta del usuario
- ✅ **Validación de seguridad**: Solo permite consultas SELECT, bloquea cualquier operación peligrosa
- ✅ **Inteligente**: Puede pedir aclaraciones al usuario si falta información (fechas, nombres, etc.)
- ✅ **Flexible**: Responde a cualquier pregunta sin necesidad de consultas predefinidas
- ✅ **Contexto completo**: Conoce el esquema completo de la base de datos

## 🚀 Instalación Rápida

### 1. Importar el Flujo

1. Abre tu instancia de N8N
2. Ve a **Workflows** → **Import from File**
3. Selecciona el archivo `pos-moon-asistente-sql-dinamico.json`
4. El flujo se importará con todos los nodos configurados

### 2. Configurar Credenciales

#### A. Credenciales de MySQL

1. En N8N, ve a **Credentials** → **Add Credential**
2. Selecciona **MySQL**
3. Configura:
   - **Host**: Tu host de MySQL
   - **Database**: Nombre de tu base de datos POS
   - **User**: Usuario de MySQL
   - **Password**: Contraseña de MySQL
   - **Port**: `3306`
4. Guarda como **"MySQL POS"**

#### B. Credenciales del AI Agent

1. Ve a **Credentials** → **Add Credential**
2. Selecciona **OpenAI** (o tu proveedor de IA)
3. Ingresa tu **API Key**
4. Guarda la credencial

### 3. Configurar el AI Agent

1. Abre el nodo **"AI Agent"**
2. Configura:
   - **Model**: Selecciona tu modelo (GPT-4 recomendado)
   - **API Key**: Selecciona la credencial creada
   - **Temperature**: `0.7`
   - **Max Tokens**: `2000`

3. **Agregar Herramienta**:
   - Ve a la sección **Tools**
   - Agrega una herramienta con este JSON Schema:

```json
{
  "type": "function",
  "function": {
    "name": "generar_consulta_sql",
    "description": "Genera una consulta SQL SELECT basándose en la pregunta del usuario. Analiza qué información necesita y genera la consulta apropiada. Si falta información necesaria (como fechas, nombres específicos), pregunta al usuario antes de generar la consulta.",
    "parameters": {
      "type": "object",
      "properties": {
        "sql": {
          "type": "string",
          "description": "La consulta SQL SELECT completa y válida. Debe ser una consulta SELECT que responda a la pregunta del usuario. Usa parámetros preparados (?) si es necesario."
        },
        "pregunta": {
          "type": "string",
          "description": "La pregunta original del usuario para contexto"
        },
        "params": {
          "type": "array",
          "items": {
            "type": "string"
          },
          "description": "Array de parámetros para la consulta preparada (opcional)"
        }
      },
      "required": ["sql", "pregunta"]
    }
  }
}
```

### 4. Activar el Workflow

1. **Activa el workflow** en N8N
2. **Copia la URL del webhook** del nodo **Chat Trigger**
3. **Configura la URL en el sistema POS**:
   - Ve a **Integraciones** → **Gestionar Integraciones**
   - Crea o edita la integración N8N
   - Pega la URL del webhook
   - Marca como activa

## 📊 Estructura del Flujo

```
Chat Trigger
    ↓
AI Agent (analiza pregunta)
    ↓ (si necesita datos)
Tool: Generar Consulta SQL
    ↓
Validación de Seguridad (solo SELECT)
    ↓
MySQL Execute
    ↓
Procesar Resultados
    ↓ (vuelve al AI Agent)
AI Agent (formatea respuesta)
    ↓
Respond to Chat
```

## 🎯 Cómo Funciona

1. **Usuario pregunta**: "¿Cuántas ventas hubo hoy?"
2. **AI Agent analiza**: Entiende que necesita consultar la tabla `ventas` con filtro de fecha de hoy
3. **Genera SQL**: `SELECT COUNT(*) as cantidad FROM ventas WHERE DATE(fecha) = CURDATE() AND cbte_tipo NOT IN (3, 8, 13, 203, 208, 213, 999)`
4. **Valida seguridad**: Verifica que solo sea SELECT
5. **Ejecuta en MySQL**: Obtiene los resultados
6. **Procesa y formatea**: Convierte los resultados a texto legible
7. **Responde al usuario**: "Hoy hubo 15 ventas"

### Si falta información

Si el usuario pregunta "¿Cuántas ventas hubo?" sin especificar fecha:
- El AI Agent pregunta: "¿De qué fecha quieres las ventas? ¿Hoy, este mes, o un rango específico?"
- Espera la respuesta del usuario
- Luego genera la consulta SQL apropiada

## 📝 Ejemplos de Uso

### Ventas
- "¿Cuántas ventas hubo hoy?"
- "¿Cuál fue el total de ventas del mes?"
- "Muéstrame las ventas de la última semana"
- "¿Cuál es el cliente que más compra?"

### Productos y Stock
- "¿Qué productos tenemos con stock bajo?"
- "Muéstrame todos los productos"
- "¿Cuánto stock tiene el producto con código 123?"
- "¿Qué productos debería comprar?"

### Estadísticas
- "¿Cuáles son las estadísticas de ventas?"
- "¿Cuál es el producto más vendido?"
- "Muéstrame un resumen del día"

### Consultas Personalizadas
- "¿Cuántos clientes tenemos?"
- "¿Cuál es el total de productos en stock?"
- "Muéstrame los proveedores activos"
- "¿Qué clientes tienen deuda?"

## 🔒 Seguridad

El flujo incluye validación estricta de seguridad:

- ✅ Solo permite consultas SELECT
- ✅ Bloquea INSERT, UPDATE, DELETE, DROP, ALTER, CREATE, etc.
- ✅ Valida que no haya múltiples comandos SQL
- ✅ Usa parámetros preparados cuando es necesario

## 🛠️ Personalización

### Modificar el System Message

Puedes modificar el `systemMessage` del AI Agent para:
- Agregar más contexto sobre el esquema de la base de datos
- Cambiar el tono de las respuestas
- Agregar reglas de negocio específicas

### Ajustar el Formato de Resultados

Puedes modificar el nodo **"Procesar Resultados"** para:
- Cambiar el formato de los números
- Agregar más información contextual
- Personalizar cómo se muestran los datos

## 🐛 Troubleshooting

### Error: "No se generó ninguna consulta SQL"
- El AI Agent no entendió la pregunta
- Intenta reformular la pregunta de manera más específica
- Verifica que la herramienta esté correctamente configurada

### Error: "Consulta SQL rechazada por seguridad"
- El AI Agent intentó generar una consulta no permitida
- Esto es normal y el sistema está protegiendo la base de datos
- Reformula la pregunta para que solo requiera consultas SELECT

### Error: "No se puede conectar a MySQL"
- Verifica las credenciales en el nodo MySQL
- Asegúrate de que el servidor MySQL permita conexiones remotas
- Verifica el firewall

### Error: "AI Agent no responde"
- Verifica la API key del proveedor de IA
- Revisa los límites de tu plan
- Verifica que el modelo esté disponible

## 📚 Documentación Adicional

- **herramientas-sql.md**: Referencia del esquema de la base de datos y ejemplos de consultas
- [N8N Chat Trigger Documentation](https://docs.n8n.io/integrations/builtin/core-nodes/n8n-nodes-base.chattrigger/)
- [N8N AI Agent Documentation](https://docs.n8n.io/integrations/builtin/langchain-chains/n8n-nodes-langchain.agent/)
- [N8N MySQL Documentation](https://docs.n8n.io/integrations/builtin/app-nodes/n8n-nodes-base.mysql/)

## ⚠️ Notas Importantes

1. **Performance**: Para grandes volúmenes de datos, considera agregar índices en las tablas
2. **Límites**: El sistema limita los resultados a 20 registros para evitar respuestas muy grandes
3. **Contexto**: El AI Agent tiene contexto completo del esquema, pero puedes agregar más información si es necesario
4. **Fechas**: El sistema entiende "hoy", "ayer", "este mes", etc., pero también acepta fechas específicas en formato YYYY-MM-DD

## 📞 Soporte

Para más información o problemas, consulta la documentación de N8N o contacta al equipo de desarrollo.
