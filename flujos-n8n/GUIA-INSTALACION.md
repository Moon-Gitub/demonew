# 📖 Guía Completa de Instalación - Flujo N8N POS Moon

## 🎯 Objetivo

Este flujo permite crear un asistente virtual completo que puede:
- Consultar ventas diarias y estadísticas
- Consultar productos y stock
- Generar sugerencias de compras
- Consultar información de clientes y proveedores
- Responder consultas en lenguaje natural sobre la base de datos MySQL
- Proporcionar soporte técnico mediante RAG

## 📋 Requisitos Previos

1. **N8N instalado y funcionando**
   - Versión 1.0 o superior
   - Acceso a internet para modelos de IA

2. **Credenciales de Base de Datos MySQL**
   - Host, puerto, base de datos, usuario y contraseña
   - Acceso desde el servidor donde corre N8N

3. **API Key de IA** (opcional pero recomendado)
   - OpenAI API Key (para GPT-4)
   - O cualquier otro proveedor compatible

4. **RAG configurado** (opcional)
   - Vector Store (Pinecone, Weaviate, etc.)
   - O embeddings locales

## 🚀 Paso 1: Importar el Flujo

1. Abre tu instancia de N8N
2. Ve a **Workflows** → **Import from File**
3. Selecciona el archivo `pos-moon-asistente-virtual.json`
4. El flujo se importará con la estructura básica

## ⚙️ Paso 2: Configurar Credenciales

### A. Credenciales de MySQL

1. En N8N, ve a **Credentials** → **Add Credential**
2. Selecciona **MySQL**
3. Configura:
   - **Host**: Tu host de MySQL (ej: `localhost` o IP)
   - **Database**: Nombre de tu base de datos POS
   - **User**: Usuario de MySQL
   - **Password**: Contraseña de MySQL
   - **Port**: `3306` (o el puerto que uses)
4. Guarda como **"MySQL POS"**

### B. Credenciales de IA (OpenAI u otro)

1. Ve a **Credentials** → **Add Credential**
2. Selecciona **OpenAI** (o tu proveedor)
3. Ingresa tu **API Key**
4. Guarda la credencial

## 🔧 Paso 3: Configurar el Chat Trigger

1. Abre el nodo **"Chat Trigger"**
2. Configura:
   - **Path**: `chat` (o el que prefieras)
   - **Response Mode**: `Response Node`
3. **Activa el workflow**
4. **Copia la URL del webhook** que aparece
   - Ejemplo: `https://tu-n8n.com/webhook/chat`

## 🤖 Paso 4: Configurar el AI Agent

1. Abre el nodo **"AI Agent"**
2. Configura:
   - **Model**: Selecciona tu modelo (GPT-4, GPT-3.5, Claude, etc.)
   - **API Key**: Selecciona la credencial creada
   - **Temperature**: `0.7` (recomendado)
   - **Max Tokens**: `2000`
3. En **Prompt (User Message)**, usa: `{{ $json.chatInput }}`
4. En **System Message**, pega el mensaje del sistema (ver abajo)

### System Message para el AI Agent

```
Eres un asistente virtual experto en sistemas POS (Punto de Venta) Moon. Tu función es ayudar a los usuarios a consultar información sobre ventas, productos, stock, clientes, proveedores y estadísticas del sistema.

INSTRUCCIONES:
- Responde siempre en español de forma clara y profesional
- Usa las herramientas disponibles para obtener información real de la base de datos
- Si no tienes información, dilo claramente
- Para consultas de ventas, siempre especifica fechas cuando sea relevante
- Para consultas de stock, identifica productos con stock bajo o medio
- Para sugerencias de compras, identifica productos que necesitan reposición
- Formatea los números con separadores de miles y decimales
- Sé conciso pero completo en tus respuestas

CONTEXTO DEL SISTEMA:
- Base de datos MySQL
- Tablas principales: ventas, productos, clientes, proveedores, cajas
- El sistema maneja ventas diarias, productos con stock, clientes y proveedores
- Las ventas tienen campos: id, fecha, codigo, total, neto, impuesto, id_cliente, id_vendedor
- Los productos tienen: id, codigo, descripcion, stock, stock_medio, stock_bajo, precio_compra, precio_venta
- Los clientes tienen: id, nombre, documento, email, telefono, compras, ultima_compra

Usa las herramientas disponibles para responder las preguntas del usuario de forma precisa y útil.
```

## 🛠️ Paso 5: Configurar las Herramientas

El AI Agent necesita herramientas para consultar la base de datos. Debes configurar cada herramienta:

### Herramienta 1: Consultar Ventas

**Función**: `consultar_ventas`

**Descripción**: Consulta información sobre ventas del sistema. Puede consultar ventas diarias, por rango de fechas, o totales.

**Parámetros**:
- `fecha` (string, opcional): Fecha específica en formato YYYY-MM-DD
- `fecha_inicio` (string, opcional): Fecha de inicio en formato YYYY-MM-DD
- `fecha_fin` (string, opcional): Fecha de fin en formato YYYY-MM-DD
- `tipo` (string, opcional): "diarias", "totales", "por_cliente"

**Ejemplo de uso**:
```json
{
  "fecha": "2025-12-08",
  "tipo": "diarias"
}
```

### Herramienta 2: Consultar Productos

**Función**: `consultar_productos`

**Descripción**: Busca productos por código, descripción o filtra por stock mínimo.

**Parámetros**:
- `codigo` (string, opcional): Código del producto
- `descripcion` (string, opcional): Buscar por descripción (búsqueda parcial)
- `stock_minimo` (number, opcional): Filtrar por stock mínimo

### Herramienta 3: Consultar Stock

**Función**: `consultar_stock`

**Descripción**: Consulta el estado del stock de productos. Puede filtrar por stock bajo, medio o todos.

**Parámetros**:
- `tipo` (string, opcional): "bajo", "medio", "todos"
- `producto_id` (number, opcional): ID específico del producto

### Herramienta 4: Sugerencias de Compras

**Función**: `sugerencias_compras`

**Descripción**: Identifica productos que necesitan reposición basándose en stock_medio y stock_bajo.

**Parámetros**: Ninguno

### Herramienta 5: Consultar Clientes

**Función**: `consultar_clientes`

**Descripción**: Busca información de clientes por nombre, documento o ID.

**Parámetros**:
- `nombre` (string, opcional): Buscar por nombre (búsqueda parcial)
- `documento` (string, opcional): Buscar por documento
- `id` (number, opcional): ID específico del cliente

### Herramienta 6: Consultar Estadísticas

**Función**: `consultar_estadisticas`

**Descripción**: Proporciona estadísticas generales del sistema.

**Parámetros**:
- `tipo` (string, opcional): "ventas", "productos", "clientes"
- `periodo` (string, opcional): "dia", "semana", "mes", "año"

### Herramienta 7: Consulta SQL Personalizada

**Función**: `consulta_sql`

**Descripción**: Permite realizar consultas SQL personalizadas en lenguaje natural. Solo permite SELECT por seguridad.

**Parámetros**:
- `query` (string, requerido): Consulta SQL o descripción en lenguaje natural

**⚠️ IMPORTANTE**: Esta herramienta valida que solo se ejecuten consultas SELECT. No permite INSERT, UPDATE, DELETE u otras operaciones peligrosas.

### Herramienta 8: RAG Soporte Técnico (Opcional)

**Función**: `soporte_tecnico`

**Descripción**: Busca información en la base de conocimiento para responder preguntas de soporte técnico.

**Parámetros**:
- `pregunta` (string, requerido): Pregunta sobre soporte técnico

## 📝 Paso 6: Crear las Funciones de Herramientas

Para cada herramienta, necesitas crear un nodo **Code** o **Function** que:

1. Reciba los parámetros del AI Agent
2. Construya la consulta SQL correspondiente
3. Ejecute la consulta en MySQL
4. Procese los resultados
5. Devuelva la información formateada

### Ejemplo: Función Consultar Ventas

Crea un nodo **Code** con este contenido:

```javascript
// Herramienta: Consultar Ventas
const fecha = $input.item.json.fecha || null;
const fechaInicio = $input.item.json.fecha_inicio || null;
const fechaFin = $input.item.json.fecha_fin || null;
const tipo = $input.item.json.tipo || 'diarias';

let query = '';
let params = [];

if (fecha) {
  query = `SELECT 
    COUNT(*) as cantidad_ventas,
    SUM(total) as total_ventas,
    SUM(neto) as total_neto,
    SUM(impuesto) as total_impuestos,
    AVG(total) as promedio_venta
  FROM ventas 
  WHERE DATE(fecha) = ? 
    AND cbte_tipo NOT IN (3, 8, 13, 203, 208, 213, 999)`;
  params = [fecha];
} else if (fechaInicio && fechaFin) {
  query = `SELECT 
    COUNT(*) as cantidad_ventas,
    SUM(total) as total_ventas,
    SUM(neto) as total_neto,
    SUM(impuesto) as total_impuestos,
    AVG(total) as promedio_venta
  FROM ventas 
  WHERE fecha BETWEEN ? AND ? 
    AND cbte_tipo NOT IN (3, 8, 13, 203, 208, 213, 999)`;
  params = [fechaInicio, fechaFin];
} else if (tipo === 'diarias') {
  query = `SELECT 
    COUNT(*) as cantidad_ventas,
    SUM(total) as total_ventas,
    SUM(neto) as total_neto,
    SUM(impuesto) as total_impuestos,
    AVG(total) as promedio_venta
  FROM ventas 
  WHERE DATE(fecha) = CURDATE() 
    AND cbte_tipo NOT IN (3, 8, 13, 203, 208, 213, 999)`;
} else if (tipo === 'totales') {
  query = `SELECT 
    COUNT(*) as cantidad_ventas,
    SUM(total) as total_ventas,
    SUM(neto) as total_neto,
    SUM(impuesto) as total_impuestos,
    AVG(total) as promedio_venta
  FROM ventas 
  WHERE cbte_tipo NOT IN (3, 8, 13, 203, 208, 213, 999)`;
}

return {
  json: {
    query: query,
    params: params,
    tipo: 'consultar_ventas'
  }
};
```

Luego conecta este nodo a un nodo **MySQL** que ejecute la consulta.

## 🔗 Paso 7: Conectar los Nodos

La estructura del flujo debe ser:

```
Chat Trigger
    ↓
AI Agent
    ↓ (cuando necesita datos)
Tool: [Nombre de la herramienta]
    ↓
MySQL Execute
    ↓
Procesar Resultados
    ↓ (vuelve al AI Agent)
AI Agent
    ↓
Respond to Chat
```

## 📊 Paso 8: Configurar el Nodo MySQL

1. Crea un nodo **MySQL**
2. Configura:
   - **Operation**: `Execute Query`
   - **Query**: `={{ $json.query }}`
   - **Query Parameters**: `={{ $json.params }}`
3. Selecciona la credencial **"MySQL POS"** creada anteriormente

## 🎨 Paso 9: Procesar Resultados

Crea un nodo **Code** que procese los resultados de MySQL y los formatee para el AI Agent:

```javascript
const resultados = $input.all();
const tipoConsulta = resultados[0]?.json?.tipo || 'general';
const datos = resultados.map(item => item.json);

// Formatear según el tipo de consulta
let respuesta = '';

if (tipoConsulta === 'consultar_ventas') {
  const venta = datos[0];
  respuesta = `VENTAS ENCONTRADAS:\n`;
  respuesta += `- Cantidad: ${venta.cantidad_ventas || 0}\n`;
  respuesta += `- Total: $${parseFloat(venta.total_ventas || 0).toLocaleString('es-AR')}\n`;
  // ... más formato
}

return {
  json: {
    resultado: respuesta,
    tipo: tipoConsulta
  }
};
```

## 🔄 Paso 10: Configurar Respond to Chat

1. Crea un nodo **Respond to Webhook**
2. Configura:
   - **Respond With**: `JSON`
   - **Response Body**: 
   ```json
   {
     "output": "{{ $json.resultado || $json.respuesta || 'No se pudo procesar la consulta.' }}"
   }
   ```

## ✅ Paso 11: Activar y Probar

1. **Activa el workflow** en N8N
2. **Copia la URL del webhook** del Chat Trigger
3. **Configura la URL en el sistema POS**:
   - Ve a **Integraciones**
   - Crea o edita la integración N8N
   - Pega la URL del webhook
   - Marca como activa
4. **Prueba el chat** en el sistema POS

## 🧪 Ejemplos de Prueba

### Ventas
- "¿Cuántas ventas hubo hoy?"
- "¿Cuál fue el total de ventas del mes?"
- "Muéstrame las ventas de la última semana"

### Productos
- "¿Qué productos tenemos con stock bajo?"
- "Muéstrame el producto con código 123"
- "¿Cuántos productos tenemos en total?"

### Estadísticas
- "¿Cuáles son las estadísticas de ventas del día?"
- "Muéstrame un resumen del mes"

### Sugerencias
- "¿Qué productos debería comprar?"
- "Muéstrame productos que necesitan reposición"

## 🐛 Troubleshooting

### Error: "No se puede conectar a MySQL"
- Verifica las credenciales
- Asegúrate de que MySQL permita conexiones remotas
- Verifica el firewall

### Error: "AI Agent no responde"
- Verifica la API key
- Revisa los límites de tu plan
- Verifica que el modelo esté disponible

### Error: "Herramienta no encontrada"
- Asegúrate de que todas las herramientas estén configuradas en el AI Agent
- Verifica que los nombres de las funciones coincidan

### Error: "No se recibió respuesta"
- Verifica que el workflow esté activo
- Revisa los logs de N8N
- Verifica que el webhook esté correctamente configurado

## 📚 Documentación Adicional

Para más información sobre:
- **N8N Chat Trigger**: https://docs.n8n.io/integrations/builtin/core-nodes/n8n-nodes-base.chattrigger/
- **N8N AI Agent**: https://docs.n8n.io/integrations/builtin/langchain-chains/n8n-nodes-langchain.agent/
- **N8N MySQL**: https://docs.n8n.io/integrations/builtin/app-nodes/n8n-nodes-base.mysql/

## 🔒 Seguridad

- ✅ Solo se permiten consultas SELECT
- ✅ Las consultas SQL están validadas
- ✅ No se exponen credenciales en el flujo
- ✅ Se recomienda usar variables de entorno para credenciales

## 📞 Soporte

Para problemas o preguntas, consulta la documentación de N8N o contacta al equipo de desarrollo.

