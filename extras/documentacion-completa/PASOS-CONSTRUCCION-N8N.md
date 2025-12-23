# Pasos para Construir el Sistema Multi-Agente en n8n

## 📋 Pre-requisitos

1. n8n instalado y funcionando
2. Credenciales configuradas:
   - OpenAI API (para todos los Chat Models)
   - MySQL (para los MySQL Tools)

## 🚀 Opción 1: Importar el Workflow Completo

### Paso 1: Importar el JSON

1. Abre n8n
2. Ve a **Workflows** → **Import from File**
3. Selecciona el archivo `pos-moon-multi-agente.json`
4. Haz clic en **Import**

### Paso 2: Configurar Credenciales

Necesitas configurar credenciales en **todos** estos nodos:

#### OpenAI (13 nodos):
- Orquestador Chat Model
- Ventas Chat Model
- Clientes Chat Model
- Proveedores Chat Model
- Cajas Chat Model
- Productos Chat Model
- Soporte Chat Model

**Cómo:**
1. Haz clic en cada nodo "Chat Model"
2. En **Credential for OpenAI** → Selecciona tu credencial de OpenAI
3. Verifica que el modelo sea `gpt-4o-mini` (o el que prefieras)

#### MySQL (5 nodos - solo agentes SQL):
- Ventas MySQL Tool
- Clientes MySQL Tool
- Proveedores MySQL Tool
- Cajas MySQL Tool
- Productos MySQL Tool

**Cómo:**
1. Haz clic en cada nodo "MySQL Tool"
2. En **Credential for MySQL** → Selecciona tu credencial de MySQL
3. Verifica que apunte a la base de datos correcta

### Paso 3: Verificar Conexiones

Verifica que todas las conexiones estén correctas:

1. **Chat Trigger** → **Workflow Configuration** → **Orquestador Agent**
2. **Orquestador Agent** → **Parse Orchestrator Response** → **Route to Agent (Switch)**
3. **Switch** → Cada uno de los 6 agentes
4. Cada agente tiene su cadena completa de procesamiento

### Paso 4: Activar y Probar

1. Haz clic en **Activate** (arriba a la derecha)
2. Prueba con: "cuánta plata en efectivo vendí este mes"
3. Verifica que el orquestador enrute correctamente

---

## 🔧 Opción 2: Construir Manualmente (Paso a Paso)

Si prefieres construir desde cero o entender la estructura:

### Fase 1: Nodos Principales

#### 1. Chat Trigger
- Tipo: `@n8n/n8n-nodes-langchain.chatTrigger`
- Configuración:
  - Public: `true`
  - Initial Messages: "¡Hola! 👋 Soy el asistente virtual..."
  - Load Previous Session: `memory`

#### 2. Workflow Configuration
- Tipo: `n8n-nodes-base.set`
- Agregar campo: `systemName` = "Sistema POS Moon Multi-Agente"

#### 3. Orquestador Agent
- Tipo: `@n8n/n8n-nodes-langchain.agent`
- System Message: Copiar desde `PROMPTS.md` sección "1. PROMPT DEL ORQUESTADOR"
- Conectar: Chat Model, Memory, Output Parser

#### 4. Orquestador Chat Model
- Tipo: `@n8n/n8n-nodes-langchain.lmChatOpenAi`
- Model: `gpt-4o-mini`
- Credencial: OpenAI

#### 5. Orquestador Memory
- Tipo: `@n8n/n8n-nodes-langchain.memoryBufferWindow`

#### 6. Orquestador Output Parser
- Tipo: `@n8n/n8n-nodes-langchain.outputParserStructured`
- Schema: 
```json
{
  "type": "object",
  "properties": {
    "agent": {"type": "string"},
    "reason": {"type": "string"}
  },
  "required": ["agent", "reason"]
}
```

#### 7. Parse Orchestrator Response
- Tipo: `n8n-nodes-base.code`
- Código: Ver `generar_workflow_completo.py` → `PARSE_ORCHESTRATOR_CODE`

#### 8. Route to Agent (Switch)
- Tipo: `n8n-nodes-base.switch`
- Configurar 6 reglas:
  1. `agent == "ventas"` → Output "ventas"
  2. `agent == "clientes"` → Output "clientes"
  3. `agent == "proveedores"` → Output "proveedores"
  4. `agent == "cajas"` → Output "cajas"
  5. `agent == "productos"` → Output "productos"
  6. `agent == "rag_soporte"` → Output "rag_soporte" (fallback)

### Fase 2: Construir Cada Agente SQL (Repetir 5 veces)

Para cada agente (ventas, clientes, proveedores, cajas, productos):

#### Paso 1: Agent Node
- Tipo: `@n8n/n8n-nodes-langchain.agent`
- System Message: Copiar prompt del agente desde `PROMPTS.md`

#### Paso 2: Chat Model
- Tipo: `@n8n/n8n-nodes-langchain.lmChatOpenAi`
- Model: `gpt-4o-mini`
- Conectar a Agent como `ai_languageModel`

#### Paso 3: Memory
- Tipo: `@n8n/n8n-nodes-langchain.memoryBufferWindow`
- Conectar a Agent como `ai_memory`
- También conectar Chat Trigger como `ai_memory`

#### Paso 4: MySQL Tool
- Tipo: `@n8n/n8n-nodes-langchain.toolSql`
- Credencial: MySQL
- Conectar a Agent como `ai_tool`

#### Paso 5: Output Parser
- Tipo: `@n8n/n8n-nodes-langchain.outputParserStructured`
- Schema: Ver `create_sql_output_parser_schema()` en el script
- Conectar a Agent como `ai_outputParser`

#### Paso 6: Extract JSON Response
- Tipo: `n8n-nodes-base.code`
- Código: `EXTRACT_JSON_CODE` (ver script)

#### Paso 7: Check If Needs Clarification
- Tipo: `n8n-nodes-base.if`
- Condición: `needsMoreInfo == true`
- True → Format Response
- False → Validate SQL Query Exists

#### Paso 8: Validate SQL Query Exists
- Tipo: `n8n-nodes-base.if`
- Condición: `sqlQuery isNotEmpty`
- True → Execute SQL Query
- False → Format Response (sin ejecutar)

#### Paso 9: Execute SQL Query
- Tipo: `n8n-nodes-base.mySql`
- Operation: `executeQuery`
- Query: `={{ $json.sqlQuery }}`
- Credencial: MySQL

#### Paso 10: Format Response
- Tipo: `n8n-nodes-base.code`
- Código: `FORMAT_RESPONSE_CODE` (ajustar nombres de nodos)
- Esto formatea los resultados y los devuelve al chat

### Fase 3: Agente de Soporte (Sin SQL)

#### Paso 1-3: Igual que agentes SQL (Agent, Chat Model, Memory)

#### Paso 4: Format Response
- Tipo: `n8n-nodes-base.code`
- Código: `FORMAT_SOPORTE_CODE`
- Este agente NO tiene MySQL Tool ni Execute SQL

---

## 🔗 Orden de Conexiones

### Flujo Principal:
```
Chat Trigger 
  → Workflow Configuration 
  → Orquestador Agent 
  → Parse Orchestrator Response 
  → Route to Agent (Switch)
```

### Para cada Agente SQL:
```
Switch Output 
  → Agent 
  → Extract JSON 
  → Check Clarification
    ├─ True → Format Response (solo mensaje)
    └─ False → Validate SQL
        ├─ False → Format Response (sin resultados)
        └─ True → Execute SQL → Format Response (con resultados)
```

### Para Agente Soporte:
```
Switch Output 
  → Agent 
  → Format Response (respuesta directa)
```

---

## 📝 Checklist de Verificación

- [ ] Chat Trigger configurado
- [ ] Workflow Configuration con systemName
- [ ] Orquestador Agent con prompt completo
- [ ] Orquestador Chat Model con credencial OpenAI
- [ ] Orquestador Memory conectado
- [ ] Orquestador Output Parser con schema correcto
- [ ] Parse Orchestrator Response con código correcto
- [ ] Switch con 6 reglas configuradas
- [ ] Cada agente SQL (5) con:
  - [ ] Agent con prompt completo
  - [ ] Chat Model con credencial
  - [ ] Memory conectado
  - [ ] MySQL Tool con credencial
  - [ ] Output Parser con schema
  - [ ] Extract JSON Response
  - [ ] Check Clarification
  - [ ] Validate SQL
  - [ ] Execute SQL
  - [ ] Format Response
- [ ] Agente Soporte con:
  - [ ] Agent con prompt
  - [ ] Chat Model con credencial
  - [ ] Memory conectado
  - [ ] Format Response
- [ ] Todas las conexiones verificadas
- [ ] Workflow activado

---

## 🧪 Testing

### Test 1: Orquestador
1. Envía: "cuánta plata en efectivo vendí este mes"
2. Verifica: Debe enrutar a **Ventas**
3. Revisa el nodo "Parse Orchestrator Response" → debe mostrar `{"agent": "ventas"}`

### Test 2: Cada Agente
1. **Ventas**: "ventas en efectivo este mes"
2. **Clientes**: "deudas de clientes"
3. **Proveedores**: "compras pendientes"
4. **Cajas**: "ingresos de caja hoy"
5. **Productos**: "productos con stock bajo"
6. **Soporte**: "cómo funciona el sistema"

### Test 3: Casos Especiales
1. "productos vendidos" → debe ir a **Ventas** (no Productos)
2. "ayuda" → debe ir a **Soporte**
3. Pregunta ambigua → debe pedir clarificación

---

## 🐛 Troubleshooting

### El orquestador no enruta correctamente
- Verifica que el Output Parser tenga el schema correcto
- Revisa el prompt del Orquestador
- Verifica que "Parse Orchestrator Response" extraiga correctamente el campo `agent`

### Un agente genera SQL incorrecto
- Verifica que el prompt tenga el esquema completo del agente
- Revisa que el esquema incluya solo las tablas de ese agente
- Verifica los ejemplos en el prompt

### Error de credenciales
- Verifica que todas las credenciales estén configuradas
- Asegúrate de que las credenciales sean válidas

### La respuesta no se muestra en el chat
- Verifica que "Format Response" tenga el código correcto
- Asegúrate de que devuelva el campo `text` correctamente

---

## 📚 Referencias

- Prompts completos: `PROMPTS.md`
- Código JavaScript: `generar_workflow_completo.py`
- Instalación: `INSTALACION.md`
- Arquitectura: `README.md`
