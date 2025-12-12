# Notas sobre el Workflow Multi-Agente

## Estructura del Workflow

El workflow completo tiene la siguiente estructura:

### Componentes Principales

1. **Chat Trigger** - Entrada del usuario
2. **Workflow Configuration** - Configuración global
3. **Orquestador Agent** - Decide qué agente usar
4. **Parse Orchestrator Response** - Extrae el agente seleccionado
5. **Route to Agent (Switch)** - Enruta a 6 salidas

### Para Cada Agente Especializado (6 agentes)

Cada agente tiene su propia cadena de procesamiento:

#### Agentes SQL (ventas, clientes, proveedores, cajas, productos):

1. **Agent Node** (con su prompt específico)
2. **OpenAI Chat Model** (gpt-4o-mini)
3. **Conversation Memory** (buffer window)
4. **MySQL Tool** (para ejecutar SQL)
5. **Output Parser** (estructurado para SQL)
6. **Extract JSON Response** (Code node)
7. **Check If Needs Clarification** (IF node)
8. **Validate SQL Query Exists** (IF node)
9. **Execute SQL Query** (MySQL node)
10. **Format Response** (Code node)

#### Agente rag_soporte:

1. **Agent Node** (con prompt de soporte)
2. **OpenAI Chat Model**
3. **Conversation Memory**
4. **Format Response** (sin SQL, solo texto)

## Cómo Completar el Workflow

### Opción 1: Crear desde el workflow base existente

1. Abre `pos-moon-asistente-sql-dinamico.json`
2. Duplica la estructura de nodos SQL (Agent → Model → Memory → Tool → Parser → Extract → Validate → Execute → Format)
3. Repite 5 veces (una por cada agente SQL)
4. Crea una versión simplificada para rag_soporte (sin MySQL Tool ni Execute SQL)
5. Agrega el Orquestador y Switch al inicio
6. Conecta todo

### Opción 2: Crear manualmente en n8n

1. Importa un workflow base simple
2. Construye cada agente como un sub-workflow separado
3. Usa el Switch para enrutar
4. Prueba cada agente individualmente

### Opción 3: Usar el script Python

1. Ejecuta `python3 generar_workflow.py`
2. Esto genera una estructura base
3. Completa manualmente los prompts de cada agente desde PROMPTS.md

## Prompts de los Agentes

Los prompts completos están en [PROMPTS.md](./PROMPTS.md). Para cada agente:

1. Copia el prompt del agente correspondiente
2. Pégalo en el campo `systemMessage` del nodo Agent
3. Asegúrate de incluir el esquema parcial en el prompt

## Esquemas Parciales

Cada agente debe recibir solo su esquema:

- **ventas**: `ventas` + `ventas_factura`
- **clientes**: `clientes` + `clientes_cuenta_corriente`
- **proveedores**: `proveedores` + `proveedores_cuenta_corriente`
- **cajas**: `cajas` + `caja_cierres`
- **productos**: `productos` + `categorias`
- **rag_soporte**: Sin esquema (no necesita)

## Posiciones de Nodos (Layout)

Sugerencia de layout:

```
Row 1: Chat Trigger → Config → Orquestador → Parse → Switch
Row 2: [6 columnas, una por cada agente]
```

Cada agente ocupa una columna vertical con todos sus nodos apilados.

## Testing

Después de crear el workflow:

1. **Prueba el Orquestador:**
   - "cuánta plata vendí" → debe ir a ventas
   - "deudas de clientes" → debe ir a clientes
   - "cómo funciona" → debe ir a rag_soporte

2. **Prueba cada Agente:**
   - Envía preguntas específicas de cada dominio
   - Verifica que genera SQL correcto (o respuesta para soporte)
   - Verifica que los resultados se formatean bien

## Troubleshooting

### El Switch no enruta correctamente
- Verifica que el Parse Orchestrator Response extraiga correctamente el campo `agent`
- Revisa que los valores en el Switch coincidan exactamente ("ventas", "clientes", etc.)

### Un agente genera SQL incorrecto
- Verifica que el prompt tenga el esquema correcto
- Revisa que el esquema parcial incluya solo las tablas de ese agente
- Verifica los ejemplos en el prompt

### Error de credenciales
- Configura OpenAI API en todos los Chat Models
- Configura MySQL en todos los MySQL Tools

## Próximos Pasos

Una vez que el workflow base funcione:

1. Optimiza los prompts basándote en errores reales
2. Agrega manejo de errores mejorado
3. Considera agregar logging para debugging
4. Optimiza el formato de respuestas según feedback
