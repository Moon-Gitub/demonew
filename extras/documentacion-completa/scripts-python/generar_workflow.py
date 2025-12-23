#!/usr/bin/env python3
"""
Script para generar el workflow JSON completo del sistema multi-agente
"""
import json
import uuid

def generate_workflow():
    """Genera el workflow JSON completo"""
    
    # Leer los prompts desde PROMPTS.md
    with open('PROMPTS.md', 'r', encoding='utf-8') as f:
        prompts_content = f.read()
    
    # Extraer prompts (simplificado - en producción se parsearía mejor)
    # Por ahora usaremos placeholders y el usuario los actualizará manualmente
    
    nodes = []
    connections = {}
    
    # UUIDs para cada nodo (generados determinísticamente para consistencia)
    import hashlib
    
    def generate_id(name):
        """Genera un ID consistente basado en el nombre"""
        return str(uuid.uuid5(uuid.NAMESPACE_DNS, f"pos-moon-multi-agent-{name}"))
    
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
            "initialMessages": "¡Hola! 👋 Soy el asistente virtual del Sistema POS Moon. Puedo ayudarte a consultar información usando lenguaje natural. ¿Qué necesitas saber?",
            "options": {
                "loadPreviousSession": "memory"
            }
        },
        "webhookId": str(uuid.uuid4())
    })
    
    # 2. Workflow Configuration
    config_id = generate_id("workflow-config")
    nodes.append({
        "id": config_id,
        "name": "Workflow Configuration",
        "type": "n8n-nodes-base.set",
        "typeVersion": 3.4,
        "position": [784, -16],
        "parameters": {
            "assignments": {
                "assignments": [
                    {
                        "id": "id-1",
                        "name": "systemName",
                        "value": "Sistema POS Moon Multi-Agente",
                        "type": "string"
                    }
                ]
            },
            "includeOtherFields": True,
            "options": {}
        }
    })
    
    # 3. Orquestador Agent
    orchestrator_id = generate_id("orchestrator-agent")
    orchestrator_model_id = generate_id("orchestrator-model")
    orchestrator_memory_id = generate_id("orchestrator-memory")
    
    nodes.append({
        "id": orchestrator_id,
        "name": "Orquestador Agent",
        "type": "@n8n/n8n-nodes-langchain.agent",
        "typeVersion": 3,
        "position": [1008, -16],
        "parameters": {
            "options": {
                "systemMessage": "=Eres un orquestador inteligente que analiza las preguntas de los usuarios y decide qué agente especializado debe responder.\n\n**TU ÚNICA TAREA:** Analizar la pregunta del usuario y devolver SOLO un JSON con el agente a usar.\n\n**FORMATO DE RESPUESTA OBLIGATORIO (SOLO JSON, sin texto adicional):**\n{\"agent\": \"nombre_agente\", \"reason\": \"breve explicación\"}\n\n**AGENTES DISPONIBLES:**\n1. \"ventas\" - Ventas y facturas electrónicas\n2. \"clientes\" - Clientes y cuenta corriente de clientes\n3. \"proveedores\" - Proveedores y cuenta corriente de proveedores\n4. \"cajas\" - Movimientos de caja y cierres\n5. \"productos\" - Catálogo de productos\n6. \"rag_soporte\" - Ayuda general del sistema\n\n**REGLAS DE DECISIÓN:**\n1. Si contiene palabras de soporte (\"cómo\", \"ayuda\", \"explicar\", \"qué es\", \"funciona\", \"tutorial\") → \"rag_soporte\"\n2. Si pregunta sobre facturas, CAE, comprobantes → \"ventas\"\n3. Si pregunta sobre productos del catálogo, stock, precios → \"productos\"\n   - EXCEPCIÓN: Si pregunta \"productos vendidos\" → \"ventas\"\n4. Si pregunta sobre ventas, totales vendidos, métodos de pago → \"ventas\"\n5. Si pregunta sobre datos de clientes → \"clientes\"\n6. Si pregunta sobre cuenta corriente, deudas, pagos de CLIENTES → \"clientes\"\n7. Si pregunta sobre movimientos de caja → \"cajas\"\n8. Si pregunta sobre cierres de caja → \"cajas\"\n9. Si pregunta sobre datos de proveedores → \"proveedores\"\n10. Si pregunta sobre cuenta corriente de PROVEEDORES → \"proveedores\"\n\nResponde SOLO con JSON válido, sin markdown."
            }
        }
    })
    
    nodes.append({
        "id": orchestrator_model_id,
        "name": "Orquestador Chat Model",
        "type": "@n8n/n8n-nodes-langchain.lmChatOpenAi",
        "typeVersion": 1.3,
        "position": [1024, 208],
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
    
    nodes.append({
        "id": orchestrator_memory_id,
        "name": "Orquestador Memory",
        "type": "@n8n/n8n-nodes-langchain.memoryBufferWindow",
        "typeVersion": 1.3,
        "position": [512, 208],
        "parameters": {}
    })
    
    # 4. Parse Orchestrator Response
    parse_orch_id = generate_id("parse-orchestrator")
    nodes.append({
        "id": parse_orch_id,
        "name": "Parse Orchestrator Response",
        "type": "n8n-nodes-base.code",
        "typeVersion": 2,
        "position": [1232, -16],
        "parameters": {
            "jsCode": "const input = $input.first().json;\nlet rawOutput = '';\nif (input.output) {\n  rawOutput = typeof input.output === 'string' ? input.output : JSON.stringify(input.output);\n} else if (input.text) {\n  rawOutput = input.text;\n} else {\n  rawOutput = JSON.stringify(input);\n}\nrawOutput = rawOutput.replace(/```json\\s*/g, '').replace(/```\\s*/g, '').trim();\nlet parsedData = null;\nconst jsonMatch = rawOutput.match(/\\{[^}]*\\}/s);\nif (jsonMatch) {\n  try {\n    parsedData = JSON.parse(jsonMatch[0]);\n  } catch (e) {\n    try {\n      parsedData = JSON.parse(rawOutput);\n    } catch (e2) {\n      parsedData = input.output || input;\n    }\n  }\n} else {\n  parsedData = input.output || input;\n}\nconst agent = parsedData?.agent || 'rag_soporte';\nconst reason = parsedData?.reason || 'Default routing';\nreturn [{\n  json: {\n    agent: agent,\n    reason: reason,\n    originalQuestion: input.chatInput || input.text || ''\n  }\n}];"
        }
    })
    
    # 5. Switch Node (Route to Agent)
    switch_id = generate_id("route-switch")
    nodes.append({
        "id": switch_id,
        "name": "Route to Agent",
        "type": "n8n-nodes-base.switch",
        "typeVersion": 3.1,
        "position": [1552, -16],
        "parameters": {
            "options": {
                "fallbackOutput": 6  # rag_soporte como fallback
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
                                    "operator": {
                                        "type": "string",
                                        "operation": "equals"
                                    }
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
                                    "operator": {
                                        "type": "string",
                                        "operation": "equals"
                                    }
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
                                    "operator": {
                                        "type": "string",
                                        "operation": "equals"
                                    }
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
                                    "operator": {
                                        "type": "string",
                                        "operation": "equals"
                                    }
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
                                    "operator": {
                                        "type": "string",
                                        "operation": "equals"
                                    }
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
                                    "rightValue": "rag_soporte",
                                    "operator": {
                                        "type": "string",
                                        "operation": "equals"
                                    }
                                }
                            ],
                            "combinator": "and"
                        },
                        "renameOutput": True,
                        "outputKey": "rag_soporte"
                    }
                ]
            }
        }
    })
    
    # NOTA: El resto de nodos de cada agente se agregarían aquí
    # Por espacio, esto es un ejemplo simplificado
    # El workflow completo tendría ~50-70 nodos totales
    
    # Conexiones básicas
    connections = {
        "Chat Trigger": {
            "main": [[{"node": "Workflow Configuration", "type": "main", "index": 0}]]
        },
        "Workflow Configuration": {
            "main": [[{"node": "Orquestador Agent", "type": "main", "index": 0}]]
        },
        "Orquestador Agent": {
            "main": [[{"node": "Parse Orchestrator Response", "type": "main", "index": 0}]]
        },
        "Orquestador Chat Model": {
            "ai_languageModel": [[{"node": "Orquestador Agent", "type": "ai_languageModel", "index": 0}]]
        },
        "Orquestador Memory": {
            "ai_memory": [
                [
                    {"node": "Orquestador Agent", "type": "ai_memory", "index": 0},
                    {"node": "Chat Trigger", "type": "ai_memory", "index": 0}
                ]
            ]
        },
        "Parse Orchestrator Response": {
            "main": [[{"node": "Route to Agent", "type": "main", "index": 0}]]
        }
    }
    
    workflow = {
        "name": "POS Moon - Sistema Multi-Agente",
        "nodes": nodes,
        "connections": connections,
        "pinData": {},
        "settings": {
            "executionOrder": "v1"
        },
        "staticData": None,
        "tags": [],
        "triggerCount": 0,
        "updatedAt": "2025-01-01T00:00:00.000Z",
        "versionId": str(uuid.uuid4())
    }
    
    return workflow

if __name__ == "__main__":
    workflow = generate_workflow()
    
    # Guardar el workflow
    with open('pos-moon-multi-agente.json', 'w', encoding='utf-8') as f:
        json.dump(workflow, f, indent=2, ensure_ascii=False)
    
    print("✅ Workflow generado: pos-moon-multi-agente.json")
    print(f"📊 Total de nodos: {len(workflow['nodes'])}")
    print("\n⚠️  NOTA: Este es un workflow base. Necesitas:")
    print("1. Agregar los nodos de cada agente especializado")
    print("2. Completar las conexiones del Switch")
    print("3. Actualizar los prompts desde PROMPTS.md")
    print("4. Configurar credenciales en n8n")
