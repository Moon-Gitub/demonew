# ⚙️ Cómo Configurar los Tools del Orquestador

## 🚨 Problema Actual

Los nodos `Execute Workflow` **NO pueden usarse directamente como Tools** del Agent en n8n. Necesitas usar `toolCode` que haga HTTP requests a los webhooks de los sub-agentes.

## ✅ Solución: Usar toolCode con HTTP Requests

### Paso 1: Obtener URLs de los Webhooks

1. Abre cada sub-agente en n8n
2. Haz clic en el nodo "Webhook"
3. Copia la **Production URL** completa
4. Anótala en esta tabla:

| Sub-Agente | Webhook URL |
|------------|-------------|
| Ventas | `https://tu-n8n.com/webhook/agenteventas` |
| Clientes | `https://tu-n8n.com/webhook/agenteclientes` |
| Proveedores | `https://tu-n8n.com/webhook/agenteproveedores` |
| Cajas | `https://tu-n8n.com/webhook/agentecajas` |
| Productos | `https://tu-n8n.com/webhook/agenteproductos` |
| Soporte | `https://tu-n8n.com/webhook/agentesoporte` |

### Paso 2: Reemplazar Execute Workflow por toolCode

Para cada sub-agente, necesitas:

1. **Eliminar** el nodo `Execute Workflow` actual
2. **Crear** un nodo `toolCode` (tipo: `@n8n/n8n-nodes-langchain.toolCode`)

### Paso 3: Configurar cada toolCode

Ejemplo para **AgenteVentas**:

**Nombre:** `AgenteVentas`

**Description:** 
```
Herramienta para consultar información sobre ventas y facturas electrónicas. 
Úsala cuando el usuario pregunte sobre: ventas, facturas, CAE, comprobantes, métodos de pago, totales vendidos.
```

**Code:**
```javascript
const http = require('http');
const https = require('https');

// URL del webhook del sub-agente Ventas
const webhookUrl = 'https://tu-n8n.com/webhook/agenteventas'; // REEMPLAZA CON TU URL

// Obtener la pregunta del usuario desde el input del tool
const pregunta = $input.item.json.chatInput || $input.item.json.message || $input.item.json.text || '';

// Preparar el payload
const payload = JSON.stringify({
  chatInput: pregunta,
  message: pregunta,
  text: pregunta
});

// Parsear la URL
const urlObj = new URL(webhookUrl);
const isHttps = urlObj.protocol === 'https:';
const httpModule = isHttps ? https : http;

const options = {
  hostname: urlObj.hostname,
  port: urlObj.port || (isHttps ? 443 : 80),
  path: urlObj.pathname,
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'Content-Length': Buffer.byteLength(payload)
  }
};

// Hacer la request
return new Promise((resolve, reject) => {
  const req = httpModule.request(options, (res) => {
    let data = '';
    
    res.on('data', (chunk) => {
      data += chunk;
    });
    
    res.on('end', () => {
      try {
        const response = JSON.parse(data);
        // Extraer la respuesta del sub-agente
        const respuesta = response.output || response.text || response.message || data;
        resolve([{ json: { resultado: respuesta } }]);
      } catch (e) {
        resolve([{ json: { resultado: data } }]);
      }
    });
  });
  
  req.on('error', (error) => {
    reject(error);
  });
  
  req.write(payload);
  req.end();
});
```

### Paso 4: Repetir para cada Sub-Agente

Crea 6 nodos `toolCode` (uno por cada sub-agente) con:
- Nombre: `AgenteVentas`, `AgenteClientes`, etc.
- Description: Describe cuándo usarlo
- Code: Código JavaScript con la URL del webhook correspondiente

### Paso 5: Conectar como Tools

1. Cada `toolCode` debe conectarse al Agent Principal como `ai_tool`
2. Elimina las conexiones antiguas de los `Execute Workflow`

## 🔄 Alternativa Más Simple: Enrutamiento Manual

Si `toolCode` es muy complejo, puedes usar enrutamiento manual:

1. Agregar **Output Parser** al Agent Principal
2. Agregar **Switch** que evalúe qué agente usar
3. Cada salida del Switch ejecuta el workflow con **Execute Workflow**

**Pasos:**

1. **Output Parser Structured** después del Agent Principal
   - Schema:
   ```json
   {
     "type": "object",
     "properties": {
       "agent": {"type": "string"},
       "reason": {"type": "string"}
     },
     "required": ["agent"]
   }
   ```

2. **Switch Node** con 6 reglas:
   - Regla 1: `$json.agent == "ventas"` → Execute Workflow (Ventas)
   - Regla 2: `$json.agent == "clientes"` → Execute Workflow (Clientes)
   - ... (etc.)

3. **Execute Workflow** después de cada salida del Switch
   - Configura el Workflow ID manualmente (seleccionando el workflow desde la lista)

## ⚠️ Nota Importante

- Los `Execute Workflow` necesitan que configures el **Workflow ID manualmente** desde el dropdown en n8n
- No puedes usar expresiones como `=$('workflow-x').id` en el campo Workflow ID
- Debes seleccionar el workflow desde la lista desplegable

## 🎯 Recomendación

Usa la **Alternativa Más Simple** (Enrutamiento Manual) porque:
- ✅ Más fácil de configurar
- ✅ No requiere URLs de webhooks
- ✅ Más mantenible
- ✅ Funciona directamente con Execute Workflow

Los Tools del Agent son mejores para acciones simples, no para ejecutar workflows completos.
