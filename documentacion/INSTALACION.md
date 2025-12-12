# Guía de Instalación - Sistema Multi-Agente POS Moon

## 📋 Pre-requisitos

- n8n instalado y funcionando
- Credenciales configuradas:
  - OpenAI API Key
  - MySQL Connection

## 🚀 Paso 1: Importar Workflows

### 1.1 Importar Orquestador

1. Abre n8n
2. Ve a **Workflows** → **Import from File**
3. Selecciona `orquestador-principal.json`
4. Haz clic en **Import**

### 1.2 Importar Sub-Agentes

Repite el proceso para cada uno de los 6 sub-agentes:
- `subagente-ventas.json`
- `subagente-clientes.json`
- `subagente-proveedores.json`
- `subagente-cajas.json`
- `subagente-productos.json`
- `subagente-soporte.json`

**Después de importar, anota el ID de cada workflow** (lo necesitarás para configurar las conexiones).

## ⚙️ Paso 2: Configurar Credenciales

### 2.1 OpenAI (7 nodos)

En cada workflow, configura la credencial de OpenAI en:
- **Orquestador:** OpenAI Chat Model
- **Cada sub-agente:** OpenAI Chat Model

**Cómo:**
1. Haz clic en el nodo "OpenAI Chat Model"
2. Selecciona tu credencial de OpenAI
3. Verifica que el modelo sea `gpt-4o-mini` (o el que prefieras)

### 2.2 MySQL (5 nodos)

Solo para los sub-agentes SQL:
- Ventas: MySQL Tool
- Clientes: MySQL Tool
- Proveedores: MySQL Tool
- Cajas: MySQL Tool
- Productos: MySQL Tool

**Cómo:**
1. Haz clic en el nodo "MySQL Tool"
2. Selecciona tu credencial de MySQL
3. Verifica que apunte a la base de datos correcta

## 📝 Paso 3: Configurar Prompts

### 3.1 Orquestador

1. Abre el workflow `orquestador-principal.json`
2. Haz clic en el nodo "Agente Principal"
3. En **System Message**, copia el prompt del orquestador desde `../PROMPTS-AGENTES-COMPLETOS.md` (sección 1)

### 3.2 Cada Sub-Agente

Para cada sub-agente:

1. Abre el workflow correspondiente
2. Haz clic en el nodo "Agente de [Nombre]"
3. En **System Message**, copia el prompt correspondiente desde `../PROMPTS-AGENTES-COMPLETOS.md`:
   - Ventas → Sección 2
   - Clientes → Sección 3
   - Proveedores → Sección 4
   - Cajas → Sección 5
   - Productos → Sección 6
   - Soporte → Sección 7

## 🔗 Paso 4: Configurar Conexión Orquestador ↔ Sub-Agentes

**IMPORTANTE:** n8n tiene limitaciones para usar workflows como Tools directos del Agent.

### Opción A: Usar toolCode con HTTP (Complejo)

1. En el orquestador, crea 6 nodos `toolCode` (uno por cada sub-agente)
2. Cada toolCode debe hacer HTTP request al webhook del sub-agente
3. Configura cada toolCode como Tool del Agent Principal

### Opción B: Sistema de Enrutamiento (Recomendado)

**Esta opción NO usa Tools del Agent, sino un sistema de enrutamiento:**

1. Modifica el orquestador para que el Agent Principal tenga Output Parser
2. Agrega un nodo Switch que evalúe qué agente usar
3. Cada salida del Switch ejecuta el workflow correspondiente usando "Execute Workflow"

**Pasos detallados:**

1. **Agregar Output Parser al Orquestador:**
   - Crea un nodo "Output Parser Structured"
   - Schema:
     ```json
     {
       "type": "object",
       "properties": {
         "agent": {"type": "string"},
         "reason": {"type": "string"}
       },
       "required": ["agent"]
     }
     ```

2. **Agregar Switch:**
   - Crea un nodo Switch después del Parser
   - Configura 6 reglas (una por cada sub-agente)
   - Cada regla evalúa: `$json.agent == "ventas"` (o "clientes", etc.)

3. **Agregar Execute Workflow:**
   - Después de cada salida del Switch, agrega "Execute Workflow"
   - Configura el Workflow ID del sub-agente correspondiente

## 🧪 Paso 5: Testing

### 5.1 Test Orquestador

1. Activa el workflow del orquestador
2. Envía: "cuánta plata en efectivo vendí este mes"
3. Verifica que enrute correctamente a AgenteVentas

### 5.2 Test Sub-Agente Directo

1. Activa un sub-agente (ej: Ventas)
2. Llama al webhook del sub-agente directamente
3. Verifica que procese correctamente

## ✅ Checklist Final

- [ ] Todos los workflows importados
- [ ] Credenciales OpenAI configuradas (7 nodos)
- [ ] Credenciales MySQL configuradas (5 nodos)
- [ ] Prompts agregados en todos los Agent nodes
- [ ] Orquestador configurado con enrutamiento a sub-agentes
- [ ] Workflow IDs configurados en Execute Workflow nodes
- [ ] Todos los workflows activados
- [ ] Testing completado

## 🐛 Troubleshooting

### El orquestador no ejecuta los sub-agentes

- Verifica que los Workflow IDs estén correctos
- Asegúrate de que los sub-agentes estén activados
- Revisa los logs de ejecución

### Los sub-agentes no reciben la pregunta

- Verifica que el Execute Workflow pase los datos correctamente
- Revisa que el trigger "When Executed by Another Workflow" esté funcionando

### Error de credenciales

- Verifica que todas las credenciales estén configuradas
- Asegúrate de que sean válidas

## 📚 Recursos

- Prompts completos: `../PROMPTS-AGENTES-COMPLETOS.md`
- Documentación: `README.md`
