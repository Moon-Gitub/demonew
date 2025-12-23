# 🚀 Solución Rápida - Configurar Orquestador

## ❌ Problema

Los nodos `Execute Workflow` no pueden usarse como Tools del Agent. Aparecen como "Workflow: undefined".

## ✅ Solución: Cambiar a Enrutamiento con Switch

Necesitas modificar el orquestador para usar **Output Parser + Switch + Execute Workflow** en lugar de Tools.

### Paso 1: Agregar Output Parser

1. Después del nodo "Agente Principal", agrega un nodo **"Output Parser Structured"**
2. Configura el schema:
```json
{
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
  "required": ["agent"]
}
```

3. Conecta:
   - **Agent Principal** → **Output Parser** (main connection)

### Paso 2: Modificar el Prompt del Orquestador

Cambia el prompt del Agent Principal para que devuelva JSON:

```
=Eres un orquestador que analiza preguntas y devuelve SOLO JSON con el agente a usar.

**FORMATO OBLIGATORIO (SOLO JSON):**
{"agent": "ventas|clientes|proveedores|cajas|productos|soporte", "reason": "..."}

**AGENTES:**
- "ventas" - Ventas y facturas
- "clientes" - Clientes y cuenta corriente
- "proveedores" - Proveedores y cuenta corriente
- "cajas" - Movimientos de caja
- "productos" - Catálogo
- "soporte" - Ayuda general

**REGLAS:**
1. Soporte ("cómo", "ayuda") → "soporte"
2. Facturas, CAE → "ventas"
3. Productos catálogo → "productos"
4. Ventas, totales → "ventas"
5. Datos clientes → "clientes"
6. Cta corriente clientes → "clientes"
7. Movimientos caja → "cajas"
8. Cierres caja → "cajas"
9. Datos proveedores → "proveedores"
10. Cta corriente proveedores → "proveedores"
```

### Paso 3: Agregar Switch

1. Después del Output Parser, agrega un nodo **Switch**
2. Configura 6 reglas (una por cada agente):

**Regla 1 - Ventas:**
- Campo: `={{ $json.agent }}`
- Operador: `equals`
- Valor: `ventas`

**Regla 2 - Clientes:**
- Campo: `={{ $json.agent }}`
- Operador: `equals`
- Valor: `clientes`

**Regla 3 - Proveedores:**
- Campo: `={{ $json.agent }}`
- Operador: `equals`
- Valor: `proveedores`

**Regla 4 - Cajas:**
- Campo: `={{ $json.agent }}`
- Operador: `equals`
- Valor: `cajas`

**Regla 5 - Productos:**
- Campo: `={{ $json.agent }}`
- Operador: `equals`
- Valor: `productos`

**Regla 6 - Soporte:**
- Campo: `={{ $json.agent }}`
- Operador: `equals`
- Valor: `soporte`

### Paso 4: Configurar Execute Workflow

Después de cada salida del Switch:

1. Agrega un nodo **Execute Workflow**
2. En **Workflow**, selecciona el workflow del sub-agente desde el dropdown
   - Regla 1 → Selecciona "POS Moon - Ventas"
   - Regla 2 → Selecciona "POS Moon - Clientes"
   - etc.

3. En **Fields to Send**, selecciona "All Entries"

### Paso 5: Eliminar Conexiones Antiguas

1. **Elimina** las conexiones de Tools del Agent Principal a los nodos Execute Workflow antiguos
2. Los nuevos Execute Workflow van después del Switch, NO como Tools

### Paso 6: Conectar Output Parser → Switch → Execute Workflow

1. **Output Parser** → **Switch** (main connection)
2. Cada salida del **Switch** → **Execute Workflow** correspondiente

## 📋 Resumen del Flujo Nuevo

```
Chat Trigger
  ↓
Agente Principal
  ↓
Output Parser (estructura JSON)
  ↓
Switch (6 salidas)
  ├─→ Execute Workflow (Ventas)
  ├─→ Execute Workflow (Clientes)
  ├─→ Execute Workflow (Proveedores)
  ├─→ Execute Workflow (Cajas)
  ├─→ Execute Workflow (Productos)
  └─→ Execute Workflow (Soporte)
```

## ⚠️ Importante

- El Agent Principal **YA NO usa Tools**
- Usa Output Parser para estructurar la respuesta
- El Switch enruta según el agente seleccionado
- Execute Workflow ejecuta el sub-workflow correspondiente

## 🎯 Ventajas

✅ Funciona directamente con Execute Workflow  
✅ No necesitas URLs de webhooks  
✅ Más fácil de mantener  
✅ Fácil de debuggear  
