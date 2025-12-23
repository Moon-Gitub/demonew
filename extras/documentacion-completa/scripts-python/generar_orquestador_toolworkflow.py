#!/usr/bin/env python3
"""
Genera el workflow del orquestador usando toolWorkflow (Tools del Agent)
"""
import json
import uuid

def generate_id(name):
    return str(uuid.uuid5(uuid.NAMESPACE_DNS, f"pos-moon-orch-tool-{name}"))

# Prompt del orquestador (ahora usa herramientas en lugar de devolver JSON)
ORCHESTRATOR_PROMPT = """=Eres un orquestador inteligente que analiza las preguntas de los usuarios y decide qué agente especializado debe responder usando las herramientas disponibles.

**TU TAREA:** Analizar la pregunta del usuario y usar la herramienta (Tool) del agente especializado correspondiente.

**HERRAMIENTAS DISPONIBLES:**

1. **Execute Ventas** - Usa esta herramienta para preguntas sobre ventas y facturas electrónicas
2. **Execute Clientes** - Usa esta herramienta para preguntas sobre clientes y cuenta corriente de clientes
3. **Execute Proveedores** - Usa esta herramienta para preguntas sobre proveedores y cuenta corriente de proveedores
4. **Execute Cajas** - Usa esta herramienta para preguntas sobre movimientos de caja y cierres
5. **Execute Productos** - Usa esta herramienta para preguntas sobre catálogo de productos
6. **Execute Soporte** - Usa esta herramienta para ayuda general del sistema

**REGLAS DE DECISIÓN:**

1. Si contiene palabras de soporte ("cómo", "ayuda", "explicar", "qué es", "funciona", "tutorial") → Usa **Execute Soporte**
2. Si pregunta sobre facturas, CAE, comprobantes, nro_cbte → Usa **Execute Ventas**
3. Si pregunta sobre productos del catálogo, stock, precios, categorías → Usa **Execute Productos**
   - EXCEPCIÓN: Si pregunta "productos vendidos" o "productos en ventas" → Usa **Execute Ventas**
4. Si pregunta sobre ventas, totales vendidos, métodos de pago → Usa **Execute Ventas**
5. Si pregunta sobre datos de clientes, búsqueda de clientes → Usa **Execute Clientes**
6. Si pregunta sobre cuenta corriente, deudas, pagos, saldos de CLIENTES → Usa **Execute Clientes**
7. Si pregunta sobre movimientos de caja (ingresos/egresos) → Usa **Execute Cajas**
8. Si pregunta sobre cierres de caja, totales de cierre → Usa **Execute Cajas**
9. Si pregunta sobre datos de proveedores → Usa **Execute Proveedores**
10. Si pregunta sobre cuenta corriente de PROVEEDORES, compras, pagos a proveedores → Usa **Execute Proveedores**

**EJEMPLOS:**

Usuario: "cuánta plata en efectivo vendí este mes"
→ Usa la herramienta Execute Ventas con la pregunta completa

Usuario: "facturas emitidas este mes"
→ Usa la herramienta Execute Ventas con la pregunta completa

Usuario: "producto más vendido en 2025"
→ Usa la herramienta Execute Productos con la pregunta completa

Usuario: "deudas de clientes"
→ Usa la herramienta Execute Clientes con la pregunta completa

Usuario: "ingresos de caja hoy"
→ Usa la herramienta Execute Cajas con la pregunta completa

Usuario: "compras a proveedores pendientes"
→ Usa la herramienta Execute Proveedores con la pregunta completa

Usuario: "cómo funciona el sistema"
→ Usa la herramienta Execute Soporte con la pregunta completa

Cuando uses una herramienta, pasa la pregunta completa del usuario como parámetro."""

def create_orchestrator_workflow():
    """Crea el workflow del orquestador usando toolWorkflow"""
    nodes = []
    connections = {}
    
    # 1. Chat Trigger
    chat_trigger_id = generate_id("chat-trigger")
    nodes.append({
        "id": chat_trigger_id,
        "name": "Chat Trigger",
        "type": "@n8n/n8n-nodes-langchain.chatTrigger",
        "typeVersion": 1.4,
        "position": [-1296, 112],
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
        "position": [-448, 144],
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
        "position": [-992, 384],
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
        "position": [-1216, 368],
        "parameters": {}
    })
    
    # 5. Tool Workflow nodes (como Tools del Agent)
    tool_workflows = [
        ("ventas", "Execute Ventas", -720, 560),
        ("clientes", "Execute Clientes", -512, 560),
        ("proveedores", "Execute Proveedores", -336, 560),
        ("cajas", "Execute Cajas", -160, 560),
        ("productos", "Execute Productos", 16, 560),
        ("soporte", "Execute Soporte", 176, 560),
    ]
    
    tool_workflow_ids = []
    for agent_key, display_name, x, y in tool_workflows:
        tool_id = generate_id(f"tool-{agent_key}")
        tool_workflow_ids.append(tool_id)
        nodes.append({
            "id": tool_id,
            "name": display_name,
            "type": "@n8n/n8n-nodes-langchain.toolWorkflow",
            "typeVersion": 2.2,
            "position": [x, y],
            "parameters": {}  # Se configurará manualmente en n8n
        })
    
    # Conexiones
    connections["Chat Trigger"] = {
        "main": [[{"node": "Agente Principal", "type": "main", "index": 0}]]
    }
    
    connections["Agente Principal"] = {
        "main": [[]]  # El Agent devuelve directamente la respuesta
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
    
    # Todas las herramientas conectadas como ai_tool
    for tool_id in tool_workflow_ids:
        tool_node = next(n for n in nodes if n["id"] == tool_id)
        connections[tool_node["name"]] = {
            "ai_tool": [[{"node": "Agente Principal", "type": "ai_tool", "index": 0}]]
        }
    
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
        "versionId": str(uuid.uuid4()),
        "meta": {
            "templateCredsSetupCompleted": True,
            "instanceId": ""
        }
    }
    
    return workflow

if __name__ == "__main__":
    print("🔄 Generando orquestador con toolWorkflow...")
    workflow = create_orchestrator_workflow()
    
    with open('orquestador-principal.json', 'w', encoding='utf-8') as f:
        json.dump(workflow, f, indent=2, ensure_ascii=False)
    
    print("✅ Orquestador generado: orquestador-principal.json")
    print("\n📋 Configuración necesaria:")
    print("1. Configura credencial de OpenAI en 'OpenAI Chat Model'")
    print("2. En cada nodo 'Execute [Agente]' (toolWorkflow), configura el workflow correspondiente:")
    print("   - Haz clic en el nodo")
    print("   - Selecciona el workflow desde el dropdown")
    print("   - Los workflows deben estar activos")
    print("3. Activa el workflow del orquestador")
