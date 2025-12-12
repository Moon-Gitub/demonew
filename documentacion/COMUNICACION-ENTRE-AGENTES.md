# 🔄 Comunicación Entre Agentes - Explicación Detallada

## 📡 Cómo se Comunica Cada Componente

### Flujo Completo de Datos

```
┌─────────────────────────────────────────────────────────────┐
│ 1. USUARIO ENVÍA PREGUNTA                                    │
│    "cuánta plata en efectivo vendí este mes"                │
└─────────────────┬───────────────────────────────────────────┘
                  │
                  ▼
┌─────────────────────────────────────────────────────────────┐
│ 2. CHAT TRIGGER                                              │
│    • Recibe pregunta del usuario                             │
│    • Almacena en Memory (session)                            │
│    • Pasa a siguiente nodo                                   │
└─────────────────┬───────────────────────────────────────────┘
                  │
                  ▼
┌─────────────────────────────────────────────────────────────┐
│ 3. WORKFLOW CONFIGURATION                                    │
│    • Agrega configuración global                             │
│    • Pasa datos al Orquestador                               │
└─────────────────┬───────────────────────────────────────────┘
                  │
                  ▼
┌─────────────────────────────────────────────────────────────┐
│ 4. ORQUESTADOR AGENT                                         │
│    • Recibe pregunta desde Memory (compartida)               │
│    • Analiza con LLM (gpt-4o-mini)                          │
│    • Genera: {"agent": "ventas", "reason": "..."}           │
└─────────────────┬───────────────────────────────────────────┘
                  │
                  ▼
┌─────────────────────────────────────────────────────────────┐
│ 5. PARSE ORCHESTRATOR RESPONSE                              │
│    • Extrae JSON del output                                  │
│    • Preserva pregunta original: originalQuestion           │
│    • Output: {agent: "ventas", originalQuestion: "..."}     │
└─────────────────┬───────────────────────────────────────────┘
                  │
                  ▼
┌─────────────────────────────────────────────────────────────┐
│ 6. ROUTE TO AGENT (SWITCH)                                  │
│    • Evalúa: $json.agent == "ventas"                        │
│    • Enruta a salida correspondiente                         │
│    • Pasa TODOS los datos (incluye originalQuestion)        │
└─────────────────┬───────────────────────────────────────────┘
                  │
                  ▼ (si agent == "ventas")
┌─────────────────────────────────────────────────────────────┐
│ 7. VENTAS AGENT                                              │
│    • Recibe pregunta desde Memory (compartida con Trigger)  │
│    • NO usa el input del Switch directamente                │
│    • La pregunta viene de: Chat Trigger → Memory → Agent    │
│    • Genera SQL basado en prompt + esquema                  │
└─────────────────┬───────────────────────────────────────────┘
                  │
                  ▼
┌─────────────────────────────────────────────────────────────┐
│ 8. VENTAS EXTRACT JSON                                       │
│    • Parsea respuesta del Agent                              │
│    • Extrae: sqlQuery, explanation, needsMoreInfo           │
└─────────────────┬───────────────────────────────────────────┘
                  │
                  ▼
┌─────────────────────────────────────────────────────────────┐
│ 9. VENTAS EXECUTE SQL (si hay SQL válido)                  │
│    • Ejecuta: SELECT SUM(total) ...                          │
│    • Devuelve resultados                                     │
└─────────────────┬───────────────────────────────────────────┘
                  │
                  ▼
┌─────────────────────────────────────────────────────────────┐
│ 10. VENTAS FORMAT RESPONSE                                  │
│     • Formatea resultados en Markdown                        │
│     • Filtra campos sensibles                                │
│     • Devuelve: {text: "📊 Resultados: ..."}                │
└─────────────────┬───────────────────────────────────────────┘
                  │
                  ▼
┌─────────────────────────────────────────────────────────────┐
│ 11. CHAT TRIGGER RECIBE RESPUESTA                           │
│     • Muestra respuesta formateada al usuario                │
└─────────────────────────────────────────────────────────────┘
```

## 🔑 Puntos Clave de la Comunicación

### 1. Memory Compartida

**CRÍTICO:** Cada Agent tiene su Memory conectada a:
- ✅ Su propio Agent node
- ✅ El Chat Trigger

Esto permite que cada agente acceda a la conversación completa y la pregunta original del usuario.

**Ejemplo de conexión:**
```javascript
// En las conexiones del workflow:
"Ventas Memory": {
  "ai_memory": [
    [
      {"node": "Ventas Agent", "type": "ai_memory", "index": 0},
      {"node": "Chat Trigger", "type": "ai_memory", "index": 0}
    ]
  ]
}
```

### 2. Parse Orchestrator Preserva Datos

El nodo "Parse Orchestrator Response" preserva la pregunta original:

```javascript
const originalQuestion = input.chatInput || input.text || '';

return [{
  json: {
    agent: agent,
    reason: reason,
    originalQuestion: originalQuestion  // ← Preservado
  }
}];
```

### 3. Switch Enruta pero No Modifica

El Switch solo evalúa y enruta, pero **NO modifica** los datos. Todos los datos pasan intactos a cada salida, incluyendo `originalQuestion`.

### 4. Agent Nodes Usan Memory

Los Agent nodes en n8n LangChain **NO usan directamente el input del nodo anterior**. En su lugar:

1. Reciben el mensaje del usuario desde la **Memory compartida**
2. Procesan con el LLM
3. Generan la respuesta (SQL o texto)
4. Devuelven la respuesta estructurada

**Por eso es importante:**
- ✅ Memory conectada al Chat Trigger
- ✅ Memory conectada al Agent
- ✅ NO necesitas pasar la pregunta manualmente

## 🔍 Verificación de Comunicación

### ¿Cómo verificar que funciona?

1. **Verifica las conexiones de Memory:**
   ```
   Cada Memory debe tener 2 conexiones:
   - Una al Agent correspondiente
   - Una al Chat Trigger
   ```

2. **Prueba con una pregunta:**
   ```
   Input: "cuánta plata vendí este mes"
   
   Verifica:
   - Orquestador devuelve: {"agent": "ventas"}
   - Switch enruta a Ventas Agent
   - Ventas Agent recibe la pregunta (desde Memory)
   - Ventas Agent genera SQL
   - SQL se ejecuta
   - Resultados se formatean
   - Usuario recibe respuesta
   ```

3. **Revisa los logs de ejecución:**
   - Abre n8n → Workflows → Ejecución
   - Revisa cada nodo en la secuencia
   - Verifica los datos que pasan entre nodos

## 🛠️ Si Algo No Funciona

### Problema: El agente no recibe la pregunta

**Causa:** Memory no conectada correctamente

**Solución:**
1. Verifica que cada Memory tenga 2 conexiones:
   - Al Agent (ai_memory)
   - Al Chat Trigger (ai_memory)
2. Si falta, reconecta manualmente en n8n

### Problema: El orquestador no pasa la pregunta

**Causa:** Parse Orchestrator no preserva originalQuestion

**Solución:**
1. Verifica el código del nodo "Parse Orchestrator Response"
2. Asegúrate de que extraiga `input.chatInput` o `input.text`
3. Verifica que devuelva `originalQuestion` en el JSON

### Problema: El Switch no pasa los datos

**Causa:** Switch mal configurado

**Solución:**
1. Verifica que el Switch use "passthrough" (pasa todos los datos)
2. Cada salida recibe TODOS los datos del input
3. No necesitas configurar campos específicos

## 📝 Código de Referencia

### Parse Orchestrator Response (Correcto)
```javascript
// Preservar pregunta original
const originalQuestion = input.chatInput || input.text || '';

return [{
  json: {
    agent: agent,
    reason: reason,
    originalQuestion: originalQuestion  // ← Preservado
  }
}];
```

### Format Response (Referencia a nodos)
```javascript
// IMPORTANTE: Ajustar nombres de nodos según el agente
const agentOutput = $('Ventas Extract JSON').first().json;
const sqlResults = $('Ventas Execute SQL').all().map(item => item.json);
```

## ✅ Checklist de Comunicación

- [ ] Chat Trigger configurado
- [ ] Cada Memory conectada a su Agent
- [ ] Cada Memory conectada a Chat Trigger
- [ ] Orquestador recibe pregunta desde Memory
- [ ] Parse Orchestrator preserva originalQuestion
- [ ] Switch enruta correctamente
- [ ] Cada Agent recibe pregunta desde Memory
- [ ] Format Response referencia nodos correctos
- [ ] Respuesta se devuelve al chat

## 🎯 Resumen

**La comunicación funciona así:**

1. **Usuario pregunta** → Chat Trigger → Memory (almacenada)
2. **Orquestador** → Analiza desde Memory → Decide agente
3. **Switch** → Enruta según agente seleccionado
4. **Agente seleccionado** → Recibe pregunta desde Memory → Procesa
5. **Format Response** → Formatea y devuelve al chat

**Punto clave:** La pregunta original viene de la **Memory compartida**, no del input del Switch. El Switch solo determina **qué agente** debe procesar.

---

**¿Dudas sobre la comunicación?** Revisa los logs de ejecución en n8n o verifica las conexiones de Memory.
