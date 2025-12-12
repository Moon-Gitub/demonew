#!/usr/bin/env python3
"""
Script completo para generar el workflow JSON del sistema multi-agente
con todos los nodos, conexiones y prompts completos
"""
import json
import uuid
import re

def extract_prompt_from_md(md_content, section_num):
    """Extrae el prompt de una sección específica del markdown"""
    # Buscar la sección con el número
    pattern = rf"## {section_num}\. PROMPT.*?\n```\n(.*?)\n```"
    match = re.search(pattern, md_content, re.DOTALL)
    if match:
        prompt = match.group(1)
        # Remover el prefijo = si existe (para systemMessage de n8n)
        if prompt.startswith('='):
            prompt = prompt[1:]
        return prompt.strip()
    return None

def extract_sql_agent_prompt(md_content, agent_name):
    """Extrae el prompt de un agente SQL específico"""
    pattern = rf"## \d+\. PROMPT DEL AGENTE \"{agent_name}\".*?\n```\n(.*?)\n```"
    match = re.search(pattern, md_content, re.DOTALL)
    if match:
        prompt = match.group(1)
        if prompt.startswith('='):
            prompt = prompt[1:]
        return prompt.strip()
    return None

def generate_id(name):
    """Genera un ID UUID5 determinístico basado en el nombre"""
    return str(uuid.uuid5(uuid.NAMESPACE_DNS, f"pos-moon-multi-agent-{name}"))

# Código JavaScript reutilizable
EXTRACT_JSON_CODE = """// Extraer y parsear el JSON del output del modelo
const input = $input.first().json;

let rawOutput = '';
if (input.output) {
  rawOutput = typeof input.output === 'string' ? input.output : JSON.stringify(input.output);
} else if (input.text) {
  rawOutput = input.text;
} else if (input.response) {
  rawOutput = input.response;
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

let needsMoreInfo = false;
let clarificationMessage = '';
let sqlQuery = '';
let explanation = '';

if (parsedData) {
  if (parsedData.output && typeof parsedData.output === 'object') {
    parsedData = parsedData.output;
  }
  
  needsMoreInfo = parsedData.needsMoreInfo === true;
  clarificationMessage = parsedData.clarificationMessage || '';
  sqlQuery = parsedData.sqlQuery || '';
  explanation = parsedData.explanation || '';
}

return [{
  json: {
    needsMoreInfo: needsMoreInfo,
    clarificationMessage: clarificationMessage,
    sqlQuery: sqlQuery,
    explanation: explanation,
    rawOutput: rawOutput
  }
}];"""

FORMAT_RESPONSE_CODE = """// Obtener datos del agente
const agentOutput = $('Extract JSON Response').first().json;
const needsMoreInfo = agentOutput.needsMoreInfo || false;
const clarificationMessage = agentOutput.clarificationMessage || '';
const explanation = agentOutput.explanation || '';
const sqlQuery = agentOutput.sqlQuery || '';

if (needsMoreInfo) {
  return [{
    json: {
      text: clarificationMessage
    }
  }];
}

let sqlResults = [];
try {
  const sqlNode = $('Execute SQL Query');
  if (sqlNode && sqlNode.all().length > 0) {
    sqlResults = sqlNode.all().map(item => item.json);
  }
} catch (e) {}

const camposSensibles = [
  'password', 'pass', 'pwd', 'contraseña', 'passwd',
  'token', 'api_key', 'secret', 'private_key', 'access_token',
  'refresh_token', 'auth_token', 'session_id', 'session_key'
];

function esCampoSensible(nombreCampo) {
  const nombreLower = nombreCampo.toLowerCase();
  return camposSensibles.some(campo => nombreLower.includes(campo.toLowerCase()));
}

sqlResults = sqlResults.map(row => {
  const rowFiltrado = {};
  Object.keys(row).forEach(key => {
    if (!esCampoSensible(key)) {
      rowFiltrado[key] = row[key];
    }
  });
  return rowFiltrado;
});

function formatearValor(value) {
  if (value === null || value === undefined) {
    return 'N/A';
  }
  if (value instanceof Date) {
    return value.toLocaleString('es-ES', { 
      year: 'numeric', 
      month: '2-digit', 
      day: '2-digit',
      hour: '2-digit',
      minute: '2-digit'
    });
  }
  if (typeof value === 'number') {
    return value.toLocaleString('es-ES');
  }
  return String(value);
}

let formattedResponse = '';

if (explanation) {
  formattedResponse += explanation + '\\n\\n';
}

if (sqlResults.length > 0 && Object.keys(sqlResults[0]).length > 0) {
  formattedResponse += '📊 **Resultados:**\\n\\n';
  
  const columns = Object.keys(sqlResults[0]);
  
  if (sqlResults.length <= 20) {
    const header = '| ' + columns.map(c => c.charAt(0).toUpperCase() + c.slice(1)).join(' | ') + ' |';
    const separator = '|' + columns.map(() => '---').join('|') + '|';
    
    formattedResponse += header + '\\n';
    formattedResponse += separator + '\\n';
    
    sqlResults.forEach(row => {
      const rowData = columns.map(col => {
        const value = formatearValor(row[col]);
        const cleanValue = value.replace(/\\|/g, '│').replace(/\\n/g, ' ').substring(0, 80);
        return cleanValue || 'N/A';
      });
      formattedResponse += '| ' + rowData.join(' | ') + ' |\\n';
    });
  } else {
    formattedResponse += `*Mostrando los primeros 20 de ${sqlResults.length} registros*\\n\\n`;
    
    sqlResults.slice(0, 20).forEach((row, index) => {
      formattedResponse += `**Registro ${index + 1}:**\\n`;
      columns.forEach(col => {
        const value = formatearValor(row[col]);
        const cleanValue = value.replace(/\\n/g, ' ').substring(0, 150);
        formattedResponse += `  • ${col}: ${cleanValue}\\n`;
      });
      formattedResponse += '\\n';
    });
    
    if (sqlResults.length > 20) {
      formattedResponse += `*... y ${sqlResults.length - 20} registros más*\\n\\n`;
    }
  }
  
  formattedResponse += `\\n**Total:** ${sqlResults.length} registro${sqlResults.length !== 1 ? 's' : ''}`;
} else {
  if (sqlQuery) {
    formattedResponse += '✅ La consulta se ejecutó correctamente, pero no se encontraron resultados.';
  } else {
    formattedResponse += '❌ No se pudo generar una consulta SQL válida.';
  }
}

return [{
  json: {
    text: formattedResponse.trim(),
    output: formattedResponse.trim(),
    respuesta: formattedResponse.trim(),
    message: formattedResponse.trim()
  }
}];"""

FORMAT_SOPORTE_CODE = """// Formatear respuesta de soporte (sin SQL)
const input = $input.first().json;

let response = '';
if (input.output) {
  response = typeof input.output === 'string' ? input.output : JSON.stringify(input.output);
} else if (input.text) {
  response = input.text;
} else if (input.response) {
  response = input.response;
} else {
  response = JSON.stringify(input);
}

return [{
  json: {
    text: response.trim(),
    output: response.trim(),
    respuesta: response.trim(),
    message: response.trim()
  }
}];"""

PARSE_ORCHESTRATOR_CODE = """// Parsear respuesta del orquestador
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

const agent = parsedData?.agent || 'rag_soporte';
const reason = parsedData?.reason || 'Default routing';

// Preservar la pregunta original para pasarla al agente
const originalQuestion = input.chatInput || input.text || '';

return [{
  json: {
    agent: agent,
    reason: reason,
    originalQuestion: originalQuestion
  }
}];"""

def create_sql_output_parser_schema():
    """Crea el schema del output parser para SQL"""
    return {
        "type": "object",
        "properties": {
            "needsMoreInfo": {
                "type": "boolean",
                "description": "Indicates whether more information is needed from the user"
            },
            "clarificationMessage": {
                "type": "string",
                "description": "Message to ask the user for clarification when needsMoreInfo is true"
            },
            "sqlQuery": {
                "type": "string",
                "description": "The generated SQL query when needsMoreInfo is false"
            },
            "explanation": {
                "type": "string",
                "description": "Explanation of what the SQL query does when needsMoreInfo is false"
            }
        },
        "required": ["needsMoreInfo"],
        "oneOf": [
            {
                "properties": {
                    "needsMoreInfo": {"const": True},
                    "clarificationMessage": {"type": "string"}
                },
                "required": ["clarificationMessage"]
            },
            {
                "properties": {
                    "needsMoreInfo": {"const": False},
                    "sqlQuery": {"type": "string"},
                    "explanation": {"type": "string"}
                },
                "required": ["sqlQuery", "explanation"]
            }
        ]
    }

def create_orchestrator_output_parser_schema():
    """Crea el schema del output parser para el orquestador"""
    return {
        "type": "object",
        "properties": {
            "agent": {
                "type": "string",
                "description": "Name of the agent to route to"
            },
            "reason": {
                "type": "string",
                "description": "Brief explanation of why this agent was selected"
            }
        },
        "required": ["agent", "reason"]
    }

def create_sql_agent_chain(agent_name, agent_label, prompt, base_x, base_y):
    """Crea la cadena completa de nodos para un agente SQL"""
    nodes = []
    connections = {}
    
    # IDs
    agent_id = generate_id(f"{agent_name}-agent")
    model_id = generate_id(f"{agent_name}-model")
    memory_id = generate_id(f"{agent_name}-memory")
    tool_id = generate_id(f"{agent_name}-tool")
    parser_id = generate_id(f"{agent_name}-parser")
    extract_id = generate_id(f"{agent_name}-extract")
    check_clarification_id = generate_id(f"{agent_name}-check-clarification")
    validate_sql_id = generate_id(f"{agent_name}-validate-sql")
    execute_sql_id = generate_id(f"{agent_name}-execute-sql")
    format_id = generate_id(f"{agent_name}-format")
    
    # 1. Agent Node
    nodes.append({
        "id": agent_id,
        "name": f"{agent_label} Agent",
        "type": "@n8n/n8n-nodes-langchain.agent",
        "typeVersion": 3,
        "position": [base_x, base_y],
        "parameters": {
            "options": {
                "systemMessage": f"={prompt}"
            }
        }
    })
    
    # 2. Chat Model
    nodes.append({
        "id": model_id,
        "name": f"{agent_label} Chat Model",
        "type": "@n8n/n8n-nodes-langchain.lmChatOpenAi",
        "typeVersion": 1.3,
        "position": [base_x + 16, base_y + 224],
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
    
    # 3. Memory
    nodes.append({
        "id": memory_id,
        "name": f"{agent_label} Memory",
        "type": "@n8n/n8n-nodes-langchain.memoryBufferWindow",
        "typeVersion": 1.3,
        "position": [base_x - 272, base_y + 224],
        "parameters": {}
    })
    
    # 4. MySQL Tool
    nodes.append({
        "id": tool_id,
        "name": f"{agent_label} MySQL Tool",
        "type": "@n8n/n8n-nodes-langchain.toolSql",
        "typeVersion": 1,
        "position": [base_x - 256, base_y + 448],
        "parameters": {
            "options": {}
        }
    })
    
    # 5. Output Parser
    nodes.append({
        "id": parser_id,
        "name": f"{agent_label} Output Parser",
        "type": "@n8n/n8n-nodes-langchain.outputParserStructured",
        "typeVersion": 1.3,
        "position": [base_x + 144, base_y + 224],
        "parameters": {
            "schemaType": "manual",
            "inputSchema": json.dumps(create_sql_output_parser_schema())
        }
    })
    
    # 6. Extract JSON Response
    nodes.append({
        "id": extract_id,
        "name": f"{agent_label} Extract JSON",
        "type": "n8n-nodes-base.code",
        "typeVersion": 2,
        "position": [base_x + 224, base_y],
        "parameters": {
            "jsCode": EXTRACT_JSON_CODE
        }
    })
    
    # 7. Check If Needs Clarification
    nodes.append({
        "id": check_clarification_id,
        "name": f"{agent_label} Check Clarification",
        "type": "n8n-nodes-base.if",
        "typeVersion": 2.2,
        "position": [base_x + 352, base_y],
        "parameters": {
            "conditions": {
                "options": {
                    "caseSensitive": False,
                    "leftValue": "",
                    "typeValidation": "loose"
                },
                "conditions": [
                    {
                        "id": "id-1",
                        "leftValue": "={{ $json.needsMoreInfo }}",
                        "rightValue": True,
                        "operator": {
                            "type": "boolean",
                            "operation": "equals"
                        }
                    }
                ],
                "combinator": "and"
            },
            "options": {}
        }
    })
    
    # 8. Validate SQL Query Exists
    nodes.append({
        "id": validate_sql_id,
        "name": f"{agent_label} Validate SQL",
        "type": "n8n-nodes-base.if",
        "typeVersion": 2.2,
        "position": [base_x + 544, base_y + 96],
        "parameters": {
            "conditions": {
                "options": {
                    "caseSensitive": True,
                    "leftValue": "",
                    "typeValidation": "strict"
                },
                "conditions": [
                    {
                        "id": "id-1",
                        "leftValue": "={{ $json.sqlQuery }}",
                        "rightValue": "",
                        "operator": {
                            "type": "string",
                            "operation": "isNotEmpty"
                        }
                    }
                ],
                "combinator": "and"
            },
            "options": {}
        }
    })
    
    # 9. Execute SQL Query
    nodes.append({
        "id": execute_sql_id,
        "name": f"{agent_label} Execute SQL",
        "type": "n8n-nodes-base.mySql",
        "typeVersion": 2.5,
        "position": [base_x + 720, base_y + 208],
        "parameters": {
            "operation": "executeQuery",
            "query": "={{ $json.sqlQuery }}",
            "options": {}
        }
    })
    
    # 10. Format Response
    nodes.append({
        "id": format_id,
        "name": f"{agent_label} Format Response",
        "type": "n8n-nodes-base.code",
        "typeVersion": 2,
        "position": [base_x + 848, base_y - 32],
        "parameters": {
            "jsCode": FORMAT_RESPONSE_CODE.replace("$('Extract JSON Response')", f"$('{agent_label} Extract JSON')").replace("$('Execute SQL Query')", f"$('{agent_label} Execute SQL')")
        }
    })
    
    # Conexiones
    connections[f"{agent_label} Agent"] = {
        "main": [[{"node": f"{agent_label} Extract JSON", "type": "main", "index": 0}]]
    }
    
    connections[f"{agent_label} Chat Model"] = {
        "ai_languageModel": [[{"node": f"{agent_label} Agent", "type": "ai_languageModel", "index": 0}]]
    }
    
    connections[f"{agent_label} Memory"] = {
        "ai_memory": [
            [
                {"node": f"{agent_label} Agent", "type": "ai_memory", "index": 0},
                {"node": "Chat Trigger", "type": "ai_memory", "index": 0}
            ]
        ]
    }
    
    connections[f"{agent_label} MySQL Tool"] = {
        "ai_tool": [[{"node": f"{agent_label} Agent", "type": "ai_tool", "index": 0}]]
    }
    
    connections[f"{agent_label} Output Parser"] = {
        "ai_outputParser": [[{"node": f"{agent_label} Agent", "type": "ai_outputParser", "index": 0}]]
    }
    
    connections[f"{agent_label} Extract JSON"] = {
        "main": [[{"node": f"{agent_label} Check Clarification", "type": "main", "index": 0}]]
    }
    
    connections[f"{agent_label} Check Clarification"] = {
        "main": [
            [{"node": f"{agent_label} Format Response", "type": "main", "index": 0}],
            [{"node": f"{agent_label} Validate SQL", "type": "main", "index": 0}]
        ]
    }
    
    connections[f"{agent_label} Validate SQL"] = {
        "main": [
            [],
            [{"node": f"{agent_label} Execute SQL", "type": "main", "index": 0}]
        ]
    }
    
    connections[f"{agent_label} Execute SQL"] = {
        "main": [[{"node": f"{agent_label} Format Response", "type": "main", "index": 0}]]
    }
    
    return nodes, connections

def create_soporte_agent_chain(prompt, base_x, base_y):
    """Crea la cadena de nodos para el agente de soporte (sin SQL)"""
    nodes = []
    connections = {}
    
    agent_id = generate_id("soporte-agent")
    model_id = generate_id("soporte-model")
    memory_id = generate_id("soporte-memory")
    format_id = generate_id("soporte-format")
    
    # Agent
    nodes.append({
        "id": agent_id,
        "name": "Soporte Agent",
        "type": "@n8n/n8n-nodes-langchain.agent",
        "typeVersion": 3,
        "position": [base_x, base_y],
        "parameters": {
            "options": {
                "systemMessage": f"={prompt}"
            }
        }
    })
    
    # Model
    nodes.append({
        "id": model_id,
        "name": "Soporte Chat Model",
        "type": "@n8n/n8n-nodes-langchain.lmChatOpenAi",
        "typeVersion": 1.3,
        "position": [base_x + 16, base_y + 224],
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
    
    # Memory
    nodes.append({
        "id": memory_id,
        "name": "Soporte Memory",
        "type": "@n8n/n8n-nodes-langchain.memoryBufferWindow",
        "typeVersion": 1.3,
        "position": [base_x - 272, base_y + 224],
        "parameters": {}
    })
    
    # Format Response
    nodes.append({
        "id": format_id,
        "name": "Soporte Format Response",
        "type": "n8n-nodes-base.code",
        "typeVersion": 2,
        "position": [base_x + 224, base_y],
        "parameters": {
            "jsCode": FORMAT_SOPORTE_CODE
        }
    })
    
    connections["Soporte Agent"] = {
        "main": [[{"node": "Soporte Format Response", "type": "main", "index": 0}]]
    }
    
    connections["Soporte Chat Model"] = {
        "ai_languageModel": [[{"node": "Soporte Agent", "type": "ai_languageModel", "index": 0}]]
    }
    
    connections["Soporte Memory"] = {
        "ai_memory": [
            [
                {"node": "Soporte Agent", "type": "ai_memory", "index": 0},
                {"node": "Chat Trigger", "type": "ai_memory", "index": 0}
            ]
        ]
    }
    
    return nodes, connections

def generate_complete_workflow():
    """Genera el workflow completo"""
    
    # Leer prompts
    with open('PROMPTS.md', 'r', encoding='utf-8') as f:
        prompts_md = f.read()
    
    orchestrator_prompt = extract_prompt_from_md(prompts_md, "1")
    ventas_prompt = extract_sql_agent_prompt(prompts_md, "ventas")
    clientes_prompt = extract_sql_agent_prompt(prompts_md, "clientes")
    proveedores_prompt = extract_sql_agent_prompt(prompts_md, "proveedores")
    cajas_prompt = extract_sql_agent_prompt(prompts_md, "cajas")
    productos_prompt = extract_sql_agent_prompt(prompts_md, "productos")
    soporte_prompt = extract_sql_agent_prompt(prompts_md, "rag_soporte")
    
    if not soporte_prompt:
        # Intentar extraer de otra forma
        pattern = r"## 7\. PROMPT DEL AGENTE \"rag_soporte\".*?\n```\n(.*?)\n```"
        match = re.search(pattern, prompts_md, re.DOTALL)
        if match:
            soporte_prompt = match.group(1)
            if soporte_prompt.startswith('='):
                soporte_prompt = soporte_prompt[1:]
            soporte_prompt = soporte_prompt.strip()
    
    nodes = []
    all_connections = {}
    
    # === NODOS PRINCIPALES ===
    
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
    orch_agent_id = generate_id("orchestrator-agent")
    orch_model_id = generate_id("orchestrator-model")
    orch_memory_id = generate_id("orchestrator-memory")
    orch_parser_id = generate_id("orchestrator-parser")
    
    nodes.append({
        "id": orch_agent_id,
        "name": "Orquestador Agent",
        "type": "@n8n/n8n-nodes-langchain.agent",
        "typeVersion": 3,
        "position": [1008, -16],
        "parameters": {
            "options": {
                "systemMessage": f"={orchestrator_prompt}"
            }
        }
    })
    
    nodes.append({
        "id": orch_model_id,
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
        "id": orch_memory_id,
        "name": "Orquestador Memory",
        "type": "@n8n/n8n-nodes-langchain.memoryBufferWindow",
        "typeVersion": 1.3,
        "position": [512, 208],
        "parameters": {}
    })
    
    nodes.append({
        "id": orch_parser_id,
        "name": "Orquestador Output Parser",
        "type": "@n8n/n8n-nodes-langchain.outputParserStructured",
        "typeVersion": 1.3,
        "position": [1152, 208],
        "parameters": {
            "schemaType": "manual",
            "inputSchema": json.dumps(create_orchestrator_output_parser_schema())
        }
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
            "jsCode": PARSE_ORCHESTRATOR_CODE
        }
    })
    
    # 5. Switch Node
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
                                    "rightValue": "rag_soporte",
                                    "operator": {"type": "string", "operation": "equals"}
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
    
    # === AGENTES SQL ===
    
    agents = [
        ("ventas", "Ventas", ventas_prompt, 1800, 0),
        ("clientes", "Clientes", clientes_prompt, 1800, 600),
        ("proveedores", "Proveedores", proveedores_prompt, 1800, 1200),
        ("cajas", "Cajas", cajas_prompt, 1800, 1800),
        ("productos", "Productos", productos_prompt, 1800, 2400),
    ]
    
    for agent_name, agent_label, prompt, base_x, base_y in agents:
        agent_nodes, agent_connections = create_sql_agent_chain(agent_name, agent_label, prompt, base_x, base_y)
        nodes.extend(agent_nodes)
        all_connections.update(agent_connections)
    
    # === AGENTE SOPORTE ===
    soporte_nodes, soporte_connections = create_soporte_agent_chain(soporte_prompt, 1800, 3000)
    nodes.extend(soporte_nodes)
    all_connections.update(soporte_connections)
    
    # === CONEXIONES PRINCIPALES ===
    
    all_connections["Chat Trigger"] = {
        "main": [[{"node": "Workflow Configuration", "type": "main", "index": 0}]]
    }
    
    all_connections["Workflow Configuration"] = {
        "main": [[{"node": "Orquestador Agent", "type": "main", "index": 0}]]
    }
    
    all_connections["Orquestador Agent"] = {
        "main": [[{"node": "Parse Orchestrator Response", "type": "main", "index": 0}]]
    }
    
    all_connections["Orquestador Chat Model"] = {
        "ai_languageModel": [[{"node": "Orquestador Agent", "type": "ai_languageModel", "index": 0}]]
    }
    
    all_connections["Orquestador Memory"] = {
        "ai_memory": [
            [
                {"node": "Orquestador Agent", "type": "ai_memory", "index": 0},
                {"node": "Chat Trigger", "type": "ai_memory", "index": 0}
            ]
        ]
    }
    
    all_connections["Orquestador Output Parser"] = {
        "ai_outputParser": [[{"node": "Orquestador Agent", "type": "ai_outputParser", "index": 0}]]
    }
    
    all_connections["Parse Orchestrator Response"] = {
        "main": [[{"node": "Route to Agent", "type": "main", "index": 0}]]
    }
    
    # Conexiones del Switch a cada agente
    all_connections["Route to Agent"] = {
        "main": [
            [{"node": "Ventas Agent", "type": "main", "index": 0}],  # ventas
            [{"node": "Clientes Agent", "type": "main", "index": 0}],  # clientes
            [{"node": "Proveedores Agent", "type": "main", "index": 0}],  # proveedores
            [{"node": "Cajas Agent", "type": "main", "index": 0}],  # cajas
            [{"node": "Productos Agent", "type": "main", "index": 0}],  # productos
            [{"node": "Soporte Agent", "type": "main", "index": 0}]  # rag_soporte
        ]
    }
    
    # Conexión final: todos los Format Response al chat
    # Necesitamos un Merge node para combinar todas las respuestas
    merge_id = generate_id("merge-responses")
    nodes.append({
        "id": merge_id,
        "name": "Merge Responses",
        "type": "n8n-nodes-base.merge",
        "typeVersion": 3,
        "position": [3000, 1500],
        "parameters": {
            "mode": "multiplex",
            "options": {}
        }
    })
    
    # Conectar todos los Format Response al Merge
    format_nodes = ["Ventas Format Response", "Clientes Format Response", "Proveedores Format Response", 
                    "Cajas Format Response", "Productos Format Response", "Soporte Format Response"]
    
    for format_node in format_nodes:
        if format_node in all_connections:
            # Agregar conexión al Merge
            if "Merge Responses" not in all_connections:
                all_connections["Merge Responses"] = {"main": [[]]}
            
            # Insertar conexión en el merge (cada output va a una entrada diferente)
            idx = format_nodes.index(format_node)
            while len(all_connections["Merge Responses"]["main"]) <= idx:
                all_connections["Merge Responses"]["main"].append([])
            
            if not all_connections["Merge Responses"]["main"][idx]:
                all_connections["Merge Responses"]["main"][idx] = []
            
            # Actualizar la conexión del Format Response
            if format_node not in all_connections:
                all_connections[format_node] = {"main": [[]]}
            
            # La respuesta del Format Response debe ir directo al chat, no al merge
            # En realidad, cada Format Response ya devuelve la respuesta final
            # El Merge no es necesario si cada agente responde independientemente
            
    # Mejor: cada Format Response ya tiene la respuesta final formateada
    # No necesitamos Merge, cada uno responde directamente al chat
    
    workflow = {
        "name": "POS Moon - Sistema Multi-Agente",
        "nodes": nodes,
        "connections": all_connections,
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
        "updatedAt": "2025-01-01T00:00:00.000Z",
        "versionId": str(uuid.uuid4())
    }
    
    return workflow

if __name__ == "__main__":
    print("🔄 Generando workflow completo...")
    workflow = generate_complete_workflow()
    
    with open('pos-moon-multi-agente.json', 'w', encoding='utf-8') as f:
        json.dump(workflow, f, indent=2, ensure_ascii=False)
    
    print(f"✅ Workflow generado: pos-moon-multi-agente.json")
    print(f"📊 Total de nodos: {len(workflow['nodes'])}")
    print(f"📊 Total de conexiones: {len(workflow['connections'])}")
    print("\n⚠️  IMPORTANTE:")
    print("1. Abre el archivo JSON en n8n")
    print("2. Configura las credenciales de OpenAI y MySQL en todos los nodos")
    print("3. Verifica que los prompts estén correctos")
    print("4. Prueba cada agente individualmente")
