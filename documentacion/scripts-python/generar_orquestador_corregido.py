#!/usr/bin/env python3
"""
Genera el workflow del orquestador CORREGIDO sin Tools mal configuradas
Usa Output Parser + Switch + Execute Workflow
"""
import json
import uuid

def generate_id(name):
    return str(uuid.uuid5(uuid.NAMESPACE_DNS, f"pos-moon-orch-{name}"))

# Prompt del orquestador (devuelve JSON)
ORCHESTRATOR_PROMPT = """=Eres un orquestador inteligente que analiza las preguntas de los usuarios y decide qué agente especializado debe responder.

**TU ÚNICA TAREA:** Analizar la pregunta del usuario y devolver SOLO un JSON con el agente a usar.

**FORMATO DE RESPUESTA OBLIGATORIO (SOLO JSON, sin texto adicional):**
{"agent": "nombre_agente", "reason": "breve explicación"}

**IMPORTANTE:**
- Responde SOLO con JSON válido
- NO uses markdown, NO uses texto fuera del JSON
- El JSON debe estar en el nivel raíz
- El campo "agent" DEBE ser exactamente uno de: "ventas", "clientes", "proveedores", "cajas", "productos", "soporte"

**AGENTES DISPONIBLES:**

1. **"ventas"** - Ventas y facturas electrónicas
2. **"clientes"** - Clientes y cuenta corriente de clientes
3. **"proveedores"** - Proveedores y cuenta corriente de proveedores
4. **"cajas"** - Movimientos de caja y cierres
5. **"productos"** - Catálogo de productos
6. **"soporte"** - Ayuda general del sistema

**REGLAS DE DECISIÓN:**

1. Si contiene palabras de soporte ("cómo", "ayuda", "explicar", "qué es", "funciona", "tutorial") → **"soporte"**
2. Si pregunta sobre facturas, CAE, comprobantes, nro_cbte → **"ventas"**
3. Si pregunta sobre productos del catálogo, stock, precios, categorías → **"productos"**
   - EXCEPCIÓN: Si pregunta "productos vendidos" o "productos en ventas" → **"ventas"**
4. Si pregunta sobre ventas, totales vendidos, métodos de pago → **"ventas"**
5. Si pregunta sobre datos de clientes, búsqueda de clientes → **"clientes"**
6. Si pregunta sobre cuenta corriente, deudas, pagos, saldos de CLIENTES → **"clientes"**
7. Si pregunta sobre movimientos de caja (ingresos/egresos) → **"cajas"**
8. Si pregunta sobre cierres de caja, totales de cierre → **"cajas"**
9. Si pregunta sobre datos de proveedores → **"proveedores"**
10. Si pregunta sobre cuenta corriente de PROVEEDORES, compras, pagos a proveedores → **"proveedores"**

**EJEMPLOS:**

Usuario: "cuánta plata en efectivo vendí este mes"
→ {"agent": "ventas", "reason": "Consulta sobre ventas y método de pago"}

Usuario: "facturas emitidas este mes"
→ {"agent": "ventas", "reason": "Consulta sobre facturas electrónicas"}

Usuario: "producto más vendido en 2025"
→ {"agent": "productos", "reason": "Consulta sobre análisis de productos"}

Usuario: "deudas de clientes"
→ {"agent": "clientes", "reason": "Consulta sobre cuenta corriente de clientes"}

Usuario: "ingresos de caja hoy"
→ {"agent": "cajas", "reason": "Consulta sobre movimientos de caja"}

Usuario: "compras a proveedores pendientes"
→ {"agent": "proveedores", "reason": "Consulta sobre cuenta corriente de proveedores"}

Usuario: "cómo funciona el sistema"
→ {"agent": "soporte", "reason": "Consulta de ayuda general"}"""

def create_orchestrator_workflow():
    """Crea el workflow del orquestador CORREGIDO"""
    nodes = []
    connections = {}
    
    # 1. Chat Trigger
    chat_trigger_id = generate_id("chat-trigger")
    nodes.append({
        "id": chat_trigger_id,
        "name": "Chat Trigger",
        "type": "@n8n/n8n-nodes-langchain.chatTrigger",
        "typeVersion": 1.4,
        "position": [432, -16],
        "parameters": {
            "public": True,
            "initialMessages": "¡Hola! 👋 Soy el asistente virtual del Sistema POS Moon. Puedo ayudarte con ventas, clientes, productos, cajas y más. ¿Qué necesitas saber?",
            "options": {
                "loadPreviousSession": "memory"
            }
        },
        "webhookId": str(uuid.uuid4())
    })
    
    # 2. Agente Principal
    agent_id = generate_id("agent")
    nodes.append({
        "id": agent_id,
        "name": "Agente Principal",
        "type": "@n8n/n8n-nodes-langchain.agent",
        "typeVersion": 3,
        "position": [784, -16],
        "parameters": {
            "options": {
                "systemMessage": ORCHESTRATOR_PROMPT
            }
        }
    })
    
    # 3. OpenAI Chat Model
    model_id = generate_id("model")
    nodes.append({
        "id": model_id,
        "name": "OpenAI Chat Model",
        "type": "@n8n/n8n-nodes-langchain.lmChatOpenAi",
        "typeVersion": 1.3,
        "position": [800, 208],
        "parameters": {
            "model": {
                "__rl": True,
                "mode": "list",
                "value": "gpt-4o-mini"
            },
            "builtInTools": {},
            "options": {}
        }
    })
    
    # 4. Memory
    memory_id = generate_id("memory")
    nodes.append({
        "id": memory_id,
        "name": "Postgres Chat Memory",
        "type": "@n8n/n8n-nodes-langchain.memoryBufferWindow",
        "typeVersion": 1.3,
        "position": [288, 208],
        "parameters": {}
    })
    
    # 5. Output Parser (estructura la respuesta JSON)
    parser_id = generate_id("parser")
    nodes.append({
        "id": parser_id,
        "name": "Output Parser",
        "type": "@n8n/n8n-nodes-langchain.outputParserStructured",
        "typeVersion": 1.3,
        "position": [1008, -16],
        "parameters": {
            "schemaType": "manual",
            "inputSchema": json.dumps({
                "type": "object",
                "properties": {
                    "agent": {
                        "type": "string",
                        "description": "Nombre del agente a usar"
                    },
                    "reason": {
                        "type": "string",
                        "description": "Razón de la selección"
                    }
                },
                "required": ["agent", "reason"]
            })
        }
    })
    
    # 6. Parse Response (extrae el JSON)
    parse_code_id = generate_id("parse-code")
    nodes.append({
        "id": parse_code_id,
        "name": "Parse Response",
        "type": "n8n-nodes-base.code",
        "typeVersion": 2,
        "position": [1232, -16],
        "parameters": {
            "jsCode": """// Parsear respuesta del orquestador
const input = $input.first().json;

let rawOutput = '';
if (input.output) {
  rawOutput = typeof input.output === 'string' ? input.output : JSON.stringify(input.output);
} else if (input.text) {
  rawOutput = input.text;
} else {
  rawOutput = JSON.stringify(input);
}

rawOutput = rawOutput.replace(/```json\\s*/g, '').replace(/```\\s*/g, '').trim();

let parsedData = null;
const jsonMatch = rawOutput.match(/\\{[^}]*\\}/s);
if (jsonMatch) {
  try {
    parsedData = JSON.parse(jsonMatch[0]);
  } catch (e) {
    try {
      parsedData = JSON.parse(rawOutput);
    } catch (e2) {
      parsedData = input.output || input;
    }
  }
} else {
  parsedData = input.output || input;
}

const agent = parsedData?.agent || 'soporte';
const reason = parsedData?.reason || 'Default routing';

// Preservar pregunta original
const originalQuestion = input.chatInput || input.text || '';

return [{
  json: {
    agent: agent,
    reason: reason,
    originalQuestion: originalQuestion
  }
}];"""
        }
    })
    
    # 7. Switch (enruta según el agente)
    switch_id = generate_id("switch")
    nodes.append({
        "id": switch_id,
        "name": "Route to Agent",
        "type": "n8n-nodes-base.switch",
        "typeVersion": 3.1,
        "position": [1456, -16],
        "parameters": {
            "options": {
                "fallbackOutput": 6  # soporte como fallback
            },
            "rules": {
                "values": [
                    {
                        "conditions": {
                            "options": {
                                "caseSensitive": False,
                                "leftValue": "",
                                "typeValidation": "strict"
                            },
                            "conditions": [
                                {
                                    "id": "id-1",
                                    "leftValue": "={{ $json.agent }}",
                                    "rightValue": "ventas",
                                    "operator": {"type": "string", "operation": "equals"}
                                }
                            ],
                            "combinator": "and"
                        },
                        "renameOutput": True,
                        "outputKey": "ventas"
                    },
                    {
                        "conditions": {
                            "options": {
                                "caseSensitive": False,
                                "leftValue": "",
                                "typeValidation": "strict"
                            },
                            "conditions": [
                                {
                                    "id": "id-1",
                                    "leftValue": "={{ $json.agent }}",
                                    "rightValue": "clientes",
                                    "operator": {"type": "string", "operation": "equals"}
                                }
                            ],
                            "combinator": "and"
                        },
                        "renameOutput": True,
                        "outputKey": "clientes"
                    },
                    {
                        "conditions": {
                            "options": {
                                "caseSensitive": False,
                                "leftValue": "",
                                "typeValidation": "strict"
                            },
                            "conditions": [
                                {
                                    "id": "id-1",
                                    "leftValue": "={{ $json.agent }}",
                                    "rightValue": "proveedores",
                                    "operator": {"type": "string", "operation": "equals"}
                                }
                            ],
                            "combinator": "and"
                        },
                        "renameOutput": True,
                        "outputKey": "proveedores"
                    },
                    {
                        "conditions": {
                            "options": {
                                "caseSensitive": False,
                                "leftValue": "",
                                "typeValidation": "strict"
                            },
                            "conditions": [
                                {
                                    "id": "id-1",
                                    "leftValue": "={{ $json.agent }}",
                                    "rightValue": "cajas",
                                    "operator": {"type": "string", "operation": "equals"}
                                }
                            ],
                            "combinator": "and"
                        },
                        "renameOutput": True,
                        "outputKey": "cajas"
                    },
                    {
                        "conditions": {
                            "options": {
                                "caseSensitive": False,
                                "leftValue": "",
                                "typeValidation": "strict"
                            },
                            "conditions": [
                                {
                                    "id": "id-1",
                                    "leftValue": "={{ $json.agent }}",
                                    "rightValue": "productos",
                                    "operator": {"type": "string", "operation": "equals"}
                                }
                            ],
                            "combinator": "and"
                        },
                        "renameOutput": True,
                        "outputKey": "productos"
                    },
                    {
                        "conditions": {
                            "options": {
                                "caseSensitive": False,
                                "leftValue": "",
                                "typeValidation": "strict"
                            },
                            "conditions": [
                                {
                                    "id": "id-1",
                                    "leftValue": "={{ $json.agent }}",
                                    "rightValue": "soporte",
                                    "operator": {"type": "string", "operation": "equals"}
                                }
                            ],
                            "combinator": "and"
                        },
                        "renameOutput": True,
                        "outputKey": "soporte"
                    }
                ]
            }
        }
    })
    
    # 8. Execute Workflow nodes (después del Switch, NO como Tools)
    execute_nodes = []
    agents = [
        ("ventas", "Ventas", 1680, -16),
        ("clientes", "Clientes", 1680, 96),
        ("proveedores", "Proveedores", 1680, 208),
        ("cajas", "Cajas", 1680, 320),
        ("productos", "Productos", 1680, 432),
        ("soporte", "Soporte", 1680, 544),
    ]
    
    for agent_name, display_name, x, y in agents:
        exec_id = generate_id(f"execute-{agent_name}")
        execute_nodes.append({
            "id": exec_id,
            "name": f"Execute {display_name}",
            "type": "n8n-nodes-base.executeWorkflow",
            "typeVersion": 1,
            "position": [x, y],
            "parameters": {
                "workflowId": "",  # Se debe configurar manualmente en n8n
                "source": {
                    "__rl": True,
                    "value": "workflow",
                    "mode": "list"
                },
                "fieldsToSend": "allEntries",
                "options": {}
            }
        })
        nodes.append(execute_nodes[-1])
    
    # Conexiones
    connections["Chat Trigger"] = {
        "main": [[{"node": "Agente Principal", "type": "main", "index": 0}]]
    }
    
    connections["Agente Principal"] = {
        "main": [[{"node": "Output Parser", "type": "main", "index": 0}]]
    }
    
    connections["OpenAI Chat Model"] = {
        "ai_languageModel": [[{"node": "Agente Principal", "type": "ai_languageModel", "index": 0}]]
    }
    
    connections["Postgres Chat Memory"] = {
        "ai_memory": [
            [
                {"node": "Agente Principal", "type": "ai_memory", "index": 0},
                {"node": "Chat Trigger", "type": "ai_memory", "index": 0}
            ]
        ]
    }
    
    connections["Output Parser"] = {
        "ai_outputParser": [[{"node": "Agente Principal", "type": "ai_outputParser", "index": 0}]],
        "main": [[{"node": "Parse Response", "type": "main", "index": 0}]]
    }
    
    connections["Parse Response"] = {
        "main": [[{"node": "Route to Agent", "type": "main", "index": 0}]]
    }
    
    # Switch → Execute Workflow (6 salidas)
    connections["Route to Agent"] = {
        "main": [
            [{"node": "Execute Ventas", "type": "main", "index": 0}],      # salida 0: ventas
            [{"node": "Execute Clientes", "type": "main", "index": 0}],    # salida 1: clientes
            [{"node": "Execute Proveedores", "type": "main", "index": 0}], # salida 2: proveedores
            [{"node": "Execute Cajas", "type": "main", "index": 0}],       # salida 3: cajas
            [{"node": "Execute Productos", "type": "main", "index": 0}],   # salida 4: productos
            [{"node": "Execute Soporte", "type": "main", "index": 0}]      # salida 5: soporte
        ]
    }
    
    # NO hay conexiones de Tools - el Agent Principal NO usa Tools
    
    workflow = {
        "name": "POS Moon - Orquestador Principal",
        "nodes": nodes,
        "connections": connections,
        "pinData": {},
        "settings": {
            "executionOrder": "v1",
            "saveManualExecutions": True,
            "callerPolicy": "workflowsFromSameOwner",
            "errorWorkflow": ""
        },
        "staticData": None,
        "tags": [],
        "triggerCount": 0,
        "updatedAt": "2025-01-12T00:00:00.000Z",
        "versionId": str(uuid.uuid4())
    }
    
    return workflow

if __name__ == "__main__":
    print("🔄 Generando orquestador CORREGIDO...")
    workflow = create_orchestrator_workflow()
    
    with open('orquestador-principal.json', 'w', encoding='utf-8') as f:
        json.dump(workflow, f, indent=2, ensure_ascii=False)
    
    print("✅ Orquestador corregido: orquestador-principal.json")
    print("\n📋 Configuración necesaria:")
    print("1. Configura credencial de OpenAI en 'OpenAI Chat Model'")
    print("2. En cada nodo 'Execute [Agente]', selecciona el workflow correspondiente desde el dropdown")
    print("3. Activa todos los sub-workflows primero")
