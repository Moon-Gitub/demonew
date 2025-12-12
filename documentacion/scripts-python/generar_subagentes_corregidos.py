#!/usr/bin/env python3
"""
Genera los workflows de subagentes CORREGIDOS con prompts reales
"""
import json
import uuid
import re

def generate_id(name):
    return str(uuid.uuid5(uuid.NAMESPACE_DNS, f"pos-moon-sub-{name}"))

def extract_prompt_from_markdown(content, agent_name):
    """Extrae el prompt del agente desde el markdown"""
    # Buscar la sección del agente
    patterns = {
        "ventas": r'## 2\. PROMPT DEL AGENTE "ventas"[\s\S]*?```\n(.*?)```',
        "clientes": r'## 3\. PROMPT DEL AGENTE "clientes"[\s\S]*?```\n(.*?)```',
        "proveedores": r'## 4\. PROMPT DEL AGENTE "proveedores"[\s\S]*?```\n(.*?)```',
        "cajas": r'## 5\. PROMPT DEL AGENTE "cajas"[\s\S]*?```\n(.*?)```',
        "productos": r'## 6\. PROMPT DEL AGENTE "productos"[\s\S]*?```\n(.*?)```',
        "soporte": r'## 7\. PROMPT DEL AGENTE "rag_soporte"[\s\S]*?```\n(.*?)```',
    }
    
    pattern = patterns.get(agent_name)
    if not pattern:
        return f"Prompt para {agent_name}"
    
    match = re.search(pattern, content)
    if match:
        return f"={match.group(1).strip()}"
    
    return f"Prompt para {agent_name}"

def create_sub_agent_workflow(agent_name, agent_display_name, is_sql_agent, prompt_text):
    """Crea un workflow de subagente CORREGIDO"""
    nodes = []
    connections = {}
    
    # 1. Webhook
    webhook_id = generate_id(f"{agent_name}-webhook")
    nodes.append({
        "parameters": {
            "httpMethod": "POST",
            "path": f"agente{agent_name}",
            "options": {}
        },
        "id": webhook_id,
        "name": "Webhook",
        "type": "n8n-nodes-base.webhook",
        "typeVersion": 2.1,
        "position": [288, -16],
        "webhookId": str(uuid.uuid4())
    })
    
    # 2. When Executed by Another Workflow
    execute_trigger_id = generate_id(f"{agent_name}-execute-trigger")
    nodes.append({
        "parameters": {},
        "id": execute_trigger_id,
        "name": "When Executed by Another Workflow",
        "type": "n8n-nodes-base.executeWorkflowTrigger",
        "typeVersion": 1,
        "position": [288, 208]
    })
    
    # 3. Merge Triggers
    merge_id = generate_id(f"{agent_name}-merge")
    nodes.append({
        "parameters": {
            "mode": "multiplex",
            "options": {}
        },
        "id": merge_id,
        "name": "Merge Triggers",
        "type": "n8n-nodes-base.merge",
        "typeVersion": 3,
        "position": [512, 96]
    })
    
    # 4. Prepare Question (extrae la pregunta del input)
    prepare_id = generate_id(f"{agent_name}-prepare")
    nodes.append({
        "parameters": {
            "jsCode": """// Extraer pregunta del input
const input = $input.first().json;

// Buscar la pregunta en diferentes campos posibles
let question = '';
if (input.originalQuestion) {
  question = input.originalQuestion;
} else if (input.chatInput) {
  question = input.chatInput;
} else if (input.body && input.body.chatInput) {
  question = input.body.chatInput;
} else if (input.body && input.body.text) {
  question = input.body.text;
} else if (input.text) {
  question = input.text;
} else if (input.question) {
  question = input.question;
} else if (typeof input === 'string') {
  question = input;
} else {
  // Último recurso: buscar cualquier campo de texto
  question = JSON.stringify(input);
}

return [{
  json: {
    chatInput: question
  }
}];"""
        },
        "id": prepare_id,
        "name": "Prepare Question",
        "type": "n8n-nodes-base.code",
        "typeVersion": 2,
        "position": [736, 96]
    })
    
    # 5. Agente
    agent_id = generate_id(f"{agent_name}-agent")
    nodes.append({
        "parameters": {
            "options": {
                "systemMessage": prompt_text
            }
        },
        "id": agent_id,
        "name": f"Agente de {agent_display_name}",
        "type": "@n8n/n8n-nodes-langchain.agent",
        "typeVersion": 3,
        "position": [960, 96]
    })
    
    # 6. OpenAI Chat Model
    model_id = generate_id(f"{agent_name}-model")
    nodes.append({
        "parameters": {
            "model": {
                "__rl": True,
                "mode": "list",
                "value": "gpt-4o-mini"
            },
            "builtInTools": {},
            "options": {}
        },
        "id": model_id,
        "name": "OpenAI Chat Model",
        "type": "@n8n/n8n-nodes-langchain.lmChatOpenAi",
        "typeVersion": 1.3,
        "position": [976, 320]
    })
    
    # 7. Memory
    memory_id = generate_id(f"{agent_name}-memory")
    nodes.append({
        "parameters": {},
        "id": memory_id,
        "name": "Memory",
        "type": "@n8n/n8n-nodes-langchain.memoryBufferWindow",
        "typeVersion": 1.3,
        "position": [464, 320]
    })
    
    # 8. MySQL Tool (solo para agentes SQL)
    if is_sql_agent:
        tool_id = generate_id(f"{agent_name}-tool")
        nodes.append({
            "parameters": {
                "options": {}
            },
            "id": tool_id,
            "name": "MySQL Tool",
            "type": "@n8n/n8n-nodes-langchain.toolSql",
            "typeVersion": 1,
            "position": [464, 544]
        })
    
    # 9. Success (nodo manual, NO trigger)
    success_id = generate_id(f"{agent_name}-success")
    nodes.append({
        "parameters": {},
        "id": success_id,
        "name": "Success",
        "type": "n8n-nodes-base.manualTrigger",
        "typeVersion": 1,
        "position": [1456, -16]
    })
    
    # 10. Error (nodo manual, NO trigger)
    error_id = generate_id(f"{agent_name}-error")
    nodes.append({
        "parameters": {},
        "id": error_id,
        "name": "Error",
        "type": "n8n-nodes-base.manualTrigger",
        "typeVersion": 1,
        "position": [1456, 208]
    })
    
    # Conexiones
    connections["Webhook"] = {
        "main": [[{"node": "Merge Triggers", "type": "main", "index": 0}]]
    }
    
    connections["When Executed by Another Workflow"] = {
        "main": [[{"node": "Merge Triggers", "type": "main", "index": 1}]]
    }
    
    connections["Merge Triggers"] = {
        "main": [[{"node": "Prepare Question", "type": "main", "index": 0}]]
    }
    
    connections["Prepare Question"] = {
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
    
    if is_sql_agent:
        connections["MySQL Tool"] = {
            "ai_tool": [[{"node": f"Agente de {agent_display_name}", "type": "ai_tool", "index": 0}]]
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
    # Leer prompts desde el archivo markdown
    prompts_file = "../../PROMPTS-AGENTES-COMPLETOS.md"
    try:
        with open(prompts_file, 'r', encoding='utf-8') as f:
            prompts_content = f.read()
    except FileNotFoundError:
        print(f"⚠️  No se encontró {prompts_file}, usando prompts placeholder")
        prompts_content = ""
    
    # Definir agentes
    agents = [
        ("ventas", "Ventas", True, "ventas"),
        ("clientes", "Clientes", True, "clientes"),
        ("proveedores", "Proveedores", True, "proveedores"),
        ("cajas", "Cajas", True, "cajas"),
        ("productos", "Productos", True, "productos"),
        ("soporte", "Soporte", False, "soporte"),  # NO SQL
    ]
    
    print("🔄 Generando subagentes CORREGIDOS...\n")
    
    for agent_key, display_name, is_sql, agent_name in agents:
        prompt_text = extract_prompt_from_markdown(prompts_content, agent_name) if prompts_content else f"Prompt para {display_name}"
        
        print(f"  ✓ Generando {display_name}...")
        workflow = create_sub_agent_workflow(agent_key, display_name, is_sql, prompt_text)
        
        filename = f"subagente-{agent_key}.json"
        with open(filename, 'w', encoding='utf-8') as f:
            json.dump(workflow, f, indent=2, ensure_ascii=False)
        
        print(f"    ✅ {filename}")
    
    print("\n✅ Todos los subagentes generados correctamente!")
    print("\n📋 Configuración necesaria:")
    print("1. Configura credencial de OpenAI en cada 'OpenAI Chat Model'")
    print("2. Configura credencial de MySQL en cada 'MySQL Tool' (solo agentes SQL)")
    print("3. Activa todos los workflows")
