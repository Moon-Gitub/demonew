#!/usr/bin/env python3
"""
Genera la nueva estructura multi-agente:
- 1 workflow Orquestador Principal (con sub-agentes como Tools)
- 6 workflows de sub-agentes independientes
"""
import json
import uuid
import re

def generate_id(name):
    return str(uuid.uuid5(uuid.NAMESPACE_DNS, f"pos-moon-{name}"))

def extract_prompt_from_md(md_content, section):
    """Extrae prompt desde markdown"""
    # Por ahora retornar placeholder, luego leer desde archivo
    return f"Prompt placeholder para {section}"

# Prompts del orquestador (resumen)
ORCHESTRATOR_PROMPT = """=Eres un orquestador inteligente que analiza las preguntas de los usuarios y decide qué agente especializado debe responder.

**TU ÚNICA TAREA:** Analizar la pregunta del usuario y usar la herramienta (Tool) del agente especializado correspondiente.

**AGENTES DISPONIBLES (Tools):**

1. **AgenteVentas** - Para preguntas sobre ventas y facturas electrónicas
2. **AgenteClientes** - Para preguntas sobre clientes y cuenta corriente de clientes  
3. **AgenteProveedores** - Para preguntas sobre proveedores y cuenta corriente de proveedores
4. **AgenteCajas** - Para preguntas sobre movimientos de caja y cierres
5. **AgenteProductos** - Para preguntas sobre catálogo de productos
6. **AgenteSoporte** - Para ayuda general del sistema (no SQL)

**REGLAS DE DECISIÓN:**
1. Si contiene palabras de soporte ("cómo", "ayuda", "explicar", "qué es") → Usa AgenteSoporte
2. Si pregunta sobre facturas, CAE, comprobantes → Usa AgenteVentas
3. Si pregunta sobre productos del catálogo, stock, precios, categorías → Usa AgenteProductos
4. Si pregunta sobre ventas, totales vendidos, métodos de pago → Usa AgenteVentas
5. Si pregunta sobre datos de clientes, búsqueda de clientes → Usa AgenteClientes
6. Si pregunta sobre cuenta corriente de CLIENTES, deudas, pagos → Usa AgenteClientes
7. Si pregunta sobre movimientos de caja (ingresos/egresos) → Usa AgenteCajas
8. Si pregunta sobre cierres de caja → Usa AgenteCajas
9. Si pregunta sobre datos de proveedores → Usa AgenteProveedores
10. Si pregunta sobre cuenta corriente de PROVEEDORES, compras, pagos a proveedores → Usa AgenteProveedores

Cuando determines qué agente usar, ejecuta su Tool pasando la pregunta del usuario como parámetro."""

def create_orchestrator_workflow():
    """Crea el workflow del Orquestador Principal"""
    nodes = []
    connections = {}
    
    # 1. Chat Trigger
    chat_trigger_id = generate_id("orchestrator-chat-trigger")
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
    
    # 2. Orquestador Agent
    agent_id = generate_id("orchestrator-agent")
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
    
    # 3. Chat Model
    model_id = generate_id("orchestrator-model")
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
    memory_id = generate_id("orchestrator-memory")
    nodes.append({
        "id": memory_id,
        "name": "Postgres Chat Memory",
        "type": "@n8n/n8n-nodes-langchain.memoryBufferWindow",
        "typeVersion": 1.3,
        "position": [288, 208],
        "parameters": {}
    })
    
    # 5. Tools: Execute Workflow para cada sub-agente
    tools = [
        ("AgenteVentas", "Ventas", 800, 448),
        ("AgenteClientes", "Clientes", 800, 544),
        ("AgenteProveedores", "Proveedores", 800, 640),
        ("AgenteCajas", "Cajas", 800, 736),
        ("AgenteProductos", "Productos", 800, 832),
        ("AgenteSoporte", "Soporte", 800, 928),
    ]
    
    tool_nodes = []
    for tool_name, display_name, x, y in tools:
        tool_id = generate_id(f"tool-{tool_name.lower()}")
        tool_nodes.append({
            "id": tool_id,
            "name": tool_name,
            "type": "n8n-nodes-base.executeWorkflow",
            "typeVersion": 1,
            "position": [x, y],
            "parameters": {
                "workflowId": f"={{$('workflow-{tool_name.lower()}').id}}",  # Se configurará manualmente
                "source": {
                    "__rl": True,
                    "value": "workflow",
                    "mode": "list"
                },
                "fieldsToSend": "allEntries",
                "options": {}
            }
        })
        nodes.append(tool_nodes[-1])
    
    # Conexiones
    connections["Chat Trigger"] = {
        "main": [[{"node": "Agente Principal", "type": "main", "index": 0}]]
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
    
    # Conectar cada tool como ai_tool del Agent
    for tool_name, _, _, _ in tools:
        if "Agente Principal" not in connections:
            connections["Agente Principal"] = {"ai_tool": [[]]}
        connections["Agente Principal"]["ai_tool"][0].append({"node": tool_name, "type": "ai_tool", "index": 0})
    
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

def create_sub_agent_workflow(agent_name, agent_display_name, is_sql_agent=True, prompt_text=""):
    """Crea un workflow para un sub-agente"""
    nodes = []
    connections = {}
    
    # 1. Webhook Trigger
    webhook_id = generate_id(f"{agent_name.lower()}-webhook")
    nodes.append({
        "id": webhook_id,
        "name": "Webhook",
        "type": "n8n-nodes-base.webhook",
        "typeVersion": 2.1,
        "position": [288, -16],
        "parameters": {
            "httpMethod": "POST",
            "path": agent_name.lower(),
            "options": {}
        },
        "webhookId": str(uuid.uuid4())
    })
    
    # 2. When Executed by Another Workflow
    workflow_trigger_id = generate_id(f"{agent_name.lower()}-workflow-trigger")
    nodes.append({
        "id": workflow_trigger_id,
        "name": "When Executed by Another Workflow",
        "type": "n8n-nodes-base.executeWorkflowTrigger",
        "typeVersion": 1,
        "position": [288, 208],
        "parameters": {}
    })
    
    # 3. Merge triggers
    merge_id = generate_id(f"{agent_name.lower()}-merge")
    nodes.append({
        "id": merge_id,
        "name": "Merge Triggers",
        "type": "n8n-nodes-base.merge",
        "typeVersion": 3,
        "position": [512, 96],
        "parameters": {
            "mode": "multiplex",
            "options": {}
        }
    })
    
    # 4. Agent Node
    agent_id = generate_id(f"{agent_name.lower()}-agent")
    nodes.append({
        "id": agent_id,
        "name": f"Agente de {agent_display_name}",
        "type": "@n8n/n8n-nodes-langchain.agent",
        "typeVersion": 3,
        "position": [736, 96],
        "parameters": {
            "options": {
                "systemMessage": f"={prompt_text}" if prompt_text else f"Prompt para {agent_display_name}"
            }
        }
    })
    
    # 5. Chat Model
    model_id = generate_id(f"{agent_name.lower()}-model")
    nodes.append({
        "id": model_id,
        "name": "OpenAI Chat Model",
        "type": "@n8n/n8n-nodes-langchain.lmChatOpenAi",
        "typeVersion": 1.3,
        "position": [752, 320],
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
    
    # 6. Memory
    memory_id = generate_id(f"{agent_name.lower()}-memory")
    nodes.append({
        "id": memory_id,
        "name": "Memory",
        "type": "@n8n/n8n-nodes-langchain.memoryBufferWindow",
        "typeVersion": 1.3,
        "position": [240, 320],
        "parameters": {}
    })
    
    # 7. Tools (solo para SQL agents)
    if is_sql_agent:
        tool_id = generate_id(f"{agent_name.lower()}-mysql-tool")
        nodes.append({
            "id": tool_id,
            "name": f"MySQL Tool",
            "type": "@n8n/n8n-nodes-langchain.toolSql",
            "typeVersion": 1,
            "position": [240, 544],
            "parameters": {
                "options": {}
            }
        })
        
        connections[f"MySQL Tool"] = {
            "ai_tool": [[{"node": f"Agente de {agent_display_name}", "type": "ai_tool", "index": 0}]]
        }
    
    # 8. Success output (manual node)
    success_id = generate_id(f"{agent_name.lower()}-success")
    nodes.append({
        "id": success_id,
        "name": "Success",
        "type": "n8n-nodes-base.manualTrigger",
        "typeVersion": 1,
        "position": [1232, -16],
        "parameters": {}
    })
    
    # 9. Error output (manual node)
    error_id = generate_id(f"{agent_name.lower()}-error")
    nodes.append({
        "id": error_id,
        "name": "Error",
        "type": "n8n-nodes-base.manualTrigger",
        "typeVersion": 1,
        "position": [1232, 208],
        "parameters": {}
    })
    
    # Conexiones
    connections["Webhook"] = {
        "main": [[{"node": "Merge Triggers", "type": "main", "index": 0}]]
    }
    
    connections["When Executed by Another Workflow"] = {
        "main": [[{"node": "Merge Triggers", "type": "main", "index": 1}]]
    }
    
    connections["Merge Triggers"] = {
        "main": [[{"node": f"Agente de {agent_display_name}", "type": "main", "index": 0}]]
    }
    
    connections[f"Agente de {agent_display_name}"] = {
        "main": [
            [{"node": "Success", "type": "main", "index": 0}],
            [{"node": "Error", "type": "main", "index": 0}]
        ]
    }
    
    connections["OpenAI Chat Model"] = {
        "ai_languageModel": [[{"node": f"Agente de {agent_display_name}", "type": "ai_languageModel", "index": 0}]]
    }
    
    connections["Memory"] = {
        "ai_memory": [[{"node": f"Agente de {agent_display_name}", "type": "ai_memory", "index": 0}]]
    }
    
    workflow = {
        "name": f"POS Moon - {agent_display_name}",
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
    print("🔄 Generando nueva estructura multi-agente...")
    
    # Orquestador
    orch_workflow = create_orchestrator_workflow()
    with open('orquestador-principal.json', 'w', encoding='utf-8') as f:
        json.dump(orch_workflow, f, indent=2, ensure_ascii=False)
    print("✅ Orquestador Principal: orquestador-principal.json")
    
    # Sub-agentes
    agents = [
        ("Ventas", "Ventas", True, ""),
        ("Clientes", "Clientes", True, ""),
        ("Proveedores", "Proveedores", True, ""),
        ("Cajas", "Cajas", True, ""),
        ("Productos", "Productos", True, ""),
        ("Soporte", "Soporte", False, ""),
    ]
    
    for agent_name, display_name, is_sql, prompt in agents:
        workflow = create_sub_agent_workflow(f"Agente{agent_name}", display_name, is_sql, prompt)
        filename = f"subagente-{agent_name.lower()}.json"
        with open(filename, 'w', encoding='utf-8') as f:
            json.dump(workflow, f, indent=2, ensure_ascii=False)
        print(f"✅ {display_name}: {filename}")
    
    print("\n📊 Total: 1 orquestador + 6 sub-agentes = 7 workflows")
    print("\n⚠️  IMPORTANTE:")
    print("1. Los prompts deben agregarse manualmente en cada Agent node")
    print("2. Los workflow IDs deben configurarse en los Execute Workflow nodes")
    print("3. Configura credenciales de OpenAI y MySQL")
