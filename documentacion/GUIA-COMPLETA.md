# Guía Completa del Sistema Multi-Agente POS Moon

## 🎯 Resumen Ejecutivo

Sistema de asistente virtual con arquitectura multi-agente que enruta automáticamente las preguntas del usuario a agentes especializados por dominio.

## 📐 Arquitectura Completa

```
┌─────────────────┐
│  Chat Trigger   │ ← Usuario pregunta aquí
└────────┬────────┘
         │
         ▼
┌─────────────────────┐
│ Workflow Config     │
└────────┬────────────┘
         │
         ▼
┌─────────────────────┐
│ Orquestador Agent   │ ← Analiza y decide qué agente usar
├─────────────────────┤
│ • Chat Model        │
│ • Memory            │
│ • Output Parser     │
└────────┬────────────┘
         │
         ▼
┌─────────────────────┐
│ Parse Orchestrator  │ ← Extrae: {"agent": "ventas", ...}
└────────┬────────────┘
         │
         ▼
┌─────────────────────┐
│  Route to Agent     │ ← Switch con 6 salidas
│     (Switch)        │
└───┬─┬─┬─┬─┬─┬───────┘
    │ │ │ │ │ │
    │ │ │ │ │ └─► rag_soporte (sin SQL)
    │ │ │ │ └───► productos (SQL)
    │ │ │ └─────► cajas (SQL)
    │ │ └───────► proveedores (SQL)
    │ └─────────► clientes (SQL)
    └───────────► ventas (SQL)
```

## 🔄 Flujo Detallado por Tipo de Agente

### Para Agentes SQL (ventas, clientes, proveedores, cajas, productos):

```
Switch Output
    │
    ▼
┌──────────────────┐
│  Agent Node      │ ← Genera SQL basado en prompt + esquema
│  (Especializado) │
└──────┬───────────┘
       │
       ├─► Chat Model (OpenAI gpt-4o-mini)
       ├─► Memory (Conversation Buffer)
       ├─► MySQL Tool (Ejecuta SQL)
       └─► Output Parser (Estructura JSON)
       │
       ▼
┌──────────────────┐
│ Extract JSON     │ ← Parsea respuesta del Agent
└──────┬───────────┘
       │
       ▼
┌──────────────────┐
│ Check Clarification│ ← ¿Necesita más info?
└──────┬───────────┘
       │
       ├─► True ────► Format Response (solo mensaje)
       │
       └─► False ───► Validate SQL Query
                       │
                       ├─► False ───► Format Response (sin resultados)
                       │
                       └─► True ────► Execute SQL Query
                                       │
                                       ▼
                                   Format Response (con resultados)
                                       │
                                       ▼
                                   📱 Usuario recibe respuesta
```

### Para Agente rag_soporte:

```
Switch Output
    │
    ▼
┌──────────────────┐
│  Agent Node      │ ← Responde sin SQL
│  (Soporte)       │
└──────┬───────────┘
       │
       ├─► Chat Model
       └─► Memory
       │
       ▼
┌──────────────────┐
│ Format Response  │ ← Formatea respuesta de texto
└──────┬───────────┘
       │
       ▼
   📱 Usuario recibe respuesta
```

## 📦 Componentes del Workflow

### 1. Nodos Principales (7 nodos)

| Nodo | Tipo | Función |
|------|------|---------|
| Chat Trigger | chatTrigger | Entrada del usuario, chat público |
| Workflow Configuration | set | Configuración global |
| Orquestador Agent | agent | Analiza pregunta, decide agente |
| Orquestador Chat Model | lmChatOpenAi | Modelo LLM para orquestador |
| Orquestador Memory | memoryBufferWindow | Memoria de conversación |
| Orquestador Output Parser | outputParserStructured | Parsea respuesta JSON |
| Parse Orchestrator Response | code | Extrae agent seleccionado |
| Route to Agent | switch | Enruta a 6 agentes |

### 2. Cada Agente SQL (10 nodos por agente × 5 = 50 nodos)

| Nodo | Tipo | Función |
|------|------|---------|
| Agent | agent | Genera SQL basado en prompt |
| Chat Model | lmChatOpenAi | Modelo LLM |
| Memory | memoryBufferWindow | Memoria compartida |
| MySQL Tool | toolSql | Herramienta para ejecutar SQL |
| Output Parser | outputParserStructured | Parsea respuesta JSON |
| Extract JSON | code | Extrae SQL y explicación |
| Check Clarification | if | ¿Necesita más información? |
| Validate SQL | if | ¿Hay SQL para ejecutar? |
| Execute SQL | mySql | Ejecuta la consulta |
| Format Response | code | Formatea resultados en Markdown |

### 3. Agente Soporte (4 nodos)

| Nodo | Tipo | Función |
|------|------|---------|
| Agent | agent | Responde preguntas de ayuda |
| Chat Model | lmChatOpenAi | Modelo LLM |
| Memory | memoryBufferWindow | Memoria compartida |
| Format Response | code | Formatea respuesta de texto |

**Total: 7 + 50 + 4 = 61 nodos**

## 🗂️ Estructura de Archivos

```
multiagente/
├── README.md                    # Documentación principal
├── PROMPTS.md                   # Prompts completos (7 prompts)
├── INSTALACION.md               # Guía de instalación
├── PASOS-CONSTRUCCION-N8N.md    # Pasos detallados
├── GUIA-COMPLETA.md             # Esta guía
├── pos-moon-multi-agente.json   # Workflow completo (63 nodos)
└── generar_workflow_completo.py # Script generador
```

## 🔌 Conexiones Críticas

### Conexiones Principales:

1. **Chat Trigger** → **Workflow Configuration** → **Orquestador Agent**
2. **Orquestador Agent** conecta:
   - Chat Model (ai_languageModel)
   - Memory (ai_memory) + Chat Trigger (ai_memory)
   - Output Parser (ai_outputParser)
3. **Orquestador Agent** → **Parse Orchestrator Response** → **Route to Agent**
4. **Route to Agent** → Cada uno de los 6 agentes

### Conexiones por Agente SQL:

1. **Agent** conecta:
   - Chat Model (ai_languageModel)
   - Memory (ai_memory) + Chat Trigger (ai_memory)
   - MySQL Tool (ai_tool)
   - Output Parser (ai_outputParser)
2. **Agent** → **Extract JSON** → **Check Clarification**
3. **Check Clarification**:
   - True → **Format Response**
   - False → **Validate SQL**
4. **Validate SQL**:
   - False → **Format Response**
   - True → **Execute SQL** → **Format Response**

### Conexiones Agente Soporte:

1. **Agent** conecta:
   - Chat Model (ai_languageModel)
   - Memory (ai_memory) + Chat Trigger (ai_memory)
2. **Agent** → **Format Response**

## 📝 Prompts y Esquemas

### Orquestador
- **Prompt:** Ver `PROMPTS.md` sección 1
- **Output:** `{"agent": "ventas|clientes|...", "reason": "..."}`
- **Schema:** Ver `create_orchestrator_output_parser_schema()`

### Agentes SQL
- **Prompts:** Ver `PROMPTS.md` secciones 2-6
- **Output:** `{"needsMoreInfo": bool, "sqlQuery": "...", "explanation": "..."}`
- **Schema:** Ver `create_sql_output_parser_schema()`
- **Esquemas:** Cada agente tiene solo sus tablas relevantes

### Agente Soporte
- **Prompt:** Ver `PROMPTS.md` sección 7
- **Output:** Texto markdown directo (no JSON)

## ⚙️ Configuración de Credenciales

### OpenAI (13 nodos)
- **Nodos:** Todos los "Chat Model"
- **Credencial:** OpenAI API Key
- **Configuración:** Seleccionar credencial existente

### MySQL (5 nodos)
- **Nodos:** Solo los "MySQL Tool" de agentes SQL
- **Credencial:** MySQL connection
- **Configuración:** Host, User, Password, Database

## 🧪 Testing y Validación

### Test 1: Orquestador
```bash
Input: "cuánta plata en efectivo vendí este mes"
Expected: {"agent": "ventas", "reason": "..."}
```

### Test 2: Routing
Verificar que cada tipo de pregunta enrute correctamente:
- Ventas → "ventas en efectivo"
- Clientes → "deudas de clientes"
- Proveedores → "compras pendientes"
- Cajas → "ingresos de caja"
- Productos → "productos con stock bajo"
- Soporte → "cómo funciona"

### Test 3: SQL Generation
Verificar que cada agente genere SQL válido:
- Revisar que use tablas correctas
- Verificar tipos de datos (INT vs VARCHAR)
- Comprobar funciones JSON cuando corresponda

### Test 4: Response Formatting
Verificar que las respuestas se formateen correctamente:
- Tablas markdown para resultados
- Filtrado de campos sensibles
- Formato de fechas y números

## 🔍 Troubleshooting Avanzado

### Problema: El orquestador no enruta correctamente

**Diagnóstico:**
1. Revisa el output del "Parse Orchestrator Response"
2. Verifica que extraiga correctamente `agent`
3. Revisa las condiciones del Switch

**Solución:**
```javascript
// En Parse Orchestrator Response, verifica que:
const agent = parsedData?.agent || 'rag_soporte';
// Debe ser exactamente: "ventas", "clientes", etc. (sin espacios)
```

### Problema: Un agente no recibe la pregunta del usuario

**Diagnóstico:**
1. Verifica que la Memory esté conectada al Chat Trigger
2. Revisa que el Agent reciba el input correcto

**Solución:**
- Asegúrate de que cada Agent tenga Memory conectada
- La Memory debe conectarse tanto al Agent como al Chat Trigger

### Problema: SQL se genera pero no se ejecuta

**Diagnóstico:**
1. Revisa "Validate SQL Query Exists"
2. Verifica que `sqlQuery` no esté vacío

**Solución:**
- Verifica que el Output Parser tenga el schema correcto
- Asegúrate de que el Agent devuelva el formato JSON correcto

## 📊 Métricas y Monitoreo

### Qué monitorear:
1. **Routing accuracy:** % de enrutamientos correctos
2. **SQL generation:** % de SQL válidos generados
3. **Response time:** Tiempo promedio de respuesta
4. **Error rate:** % de errores por agente

### Cómo monitorear:
- Revisa los logs de ejecución en n8n
- Analiza las respuestas del orquestador
- Verifica los SQL generados
- Revisa los errores de MySQL

## 🚀 Optimizaciones Futuras

1. **Caching:** Cachear esquemas y respuestas comunes
2. **Logging:** Agregar logging detallado para debugging
3. **Métricas:** Dashboard de métricas por agente
4. **Validación:** Validación más estricta de SQL antes de ejecutar
5. **Rate limiting:** Limitar consultas complejas
6. **Feedback loop:** Sistema de feedback para mejorar prompts

## 📚 Referencias Rápidas

- **Prompts:** `PROMPTS.md`
- **Instalación:** `INSTALACION.md`
- **Construcción:** `PASOS-CONSTRUCCION-N8N.md`
- **Arquitectura:** `README.md`
- **Workflow JSON:** `pos-moon-multi-agente.json`

## 🎓 Conceptos Clave

### ¿Por qué multi-agente?
- **Especialización:** Cada agente conoce solo su dominio
- **Precisión:** Menos confusión = mejor SQL
- **Mantenibilidad:** Fácil actualizar un agente sin afectar otros
- **Escalabilidad:** Fácil agregar nuevos agentes

### ¿Cómo funciona el enrutamiento?
1. Usuario pregunta
2. Orquestador analiza la pregunta
3. Orquestador devuelve `{"agent": "nombre"}`
4. Switch enruta según el agente
5. Agente especializado procesa

### ¿Por qué esquemas parciales?
- Cada agente solo ve sus tablas relevantes
- Reduce confusión y errores
- Mejora la precisión del SQL generado

---

## ✅ Checklist Final

Antes de usar el workflow en producción:

- [ ] Todos los prompts actualizados
- [ ] Todos los esquemas correctos
- [ ] Todas las credenciales configuradas
- [ ] Todas las conexiones verificadas
- [ ] Testing completo de cada agente
- [ ] Validación de seguridad (solo SELECT)
- [ ] Filtrado de campos sensibles funcionando
- [ ] Formato de respuestas correcto
- [ ] Manejo de errores implementado
- [ ] Documentación actualizada

---

**¿Preguntas?** Revisa la documentación o consulta los logs de ejecución en n8n.
