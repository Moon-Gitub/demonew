# 🔧 Configuración de Herramientas para AI Agent

Este documento explica cómo configurar cada herramienta en el AI Agent de N8N.

## 📋 Herramientas Disponibles

El AI Agent debe tener acceso a las siguientes herramientas:

1. **consultar_ventas** - Consultar información de ventas
2. **consultar_productos** - Buscar productos
3. **consultar_stock** - Consultar estado del stock
4. **sugerencias_compras** - Generar sugerencias de compras
5. **consultar_clientes** - Buscar información de clientes
6. **consultar_estadisticas** - Obtener estadísticas del sistema
7. **consulta_sql** - Consultas SQL personalizadas (solo SELECT)

## 🛠️ Configuración en N8N

### Paso 1: Configurar el AI Agent

1. Abre el nodo **AI Agent**
2. Ve a la sección **Tools**
3. Agrega cada herramienta usando el formato JSON Schema

### Paso 2: Definir cada Herramienta

#### Herramienta 1: Consultar Ventas

```json
{
  "type": "function",
  "function": {
    "name": "consultar_ventas",
    "description": "Consulta información sobre ventas del sistema. Puede consultar ventas diarias, por rango de fechas, o totales. Excluye anulaciones y notas de crédito.",
    "parameters": {
      "type": "object",
      "properties": {
        "fecha": {
          "type": "string",
          "description": "Fecha específica en formato YYYY-MM-DD (ej: 2025-12-08)"
        },
        "fecha_inicio": {
          "type": "string",
          "description": "Fecha de inicio en formato YYYY-MM-DD para rango de fechas"
        },
        "fecha_fin": {
          "type": "string",
          "description": "Fecha de fin en formato YYYY-MM-DD para rango de fechas"
        },
        "tipo": {
          "type": "string",
          "enum": ["diarias", "totales", "por_cliente"],
          "description": "Tipo de consulta: 'diarias' para ventas de hoy, 'totales' para todas las ventas, 'por_cliente' para ventas agrupadas por cliente"
        }
      }
    }
  }
}
```

#### Herramienta 2: Consultar Productos

```json
{
  "type": "function",
  "function": {
    "name": "consultar_productos",
    "description": "Busca productos por código, descripción o filtra por stock mínimo. Retorna información detallada del producto incluyendo stock, precios y categorías.",
    "parameters": {
      "type": "object",
      "properties": {
        "codigo": {
          "type": "string",
          "description": "Código del producto a buscar"
        },
        "descripcion": {
          "type": "string",
          "description": "Buscar productos por descripción (búsqueda parcial, no requiere coincidencia exacta)"
        },
        "stock_minimo": {
          "type": "number",
          "description": "Filtrar productos con stock mayor o igual a este valor"
        }
      }
    }
  }
}
```

#### Herramienta 3: Consultar Stock

```json
{
  "type": "function",
  "function": {
    "name": "consultar_stock",
    "description": "Consulta el estado del stock de productos. Puede filtrar por stock bajo, medio o todos los productos con stock. Identifica productos que necesitan reposición.",
    "parameters": {
      "type": "object",
      "properties": {
        "tipo": {
          "type": "string",
          "enum": ["bajo", "medio", "todos"],
          "description": "Tipo de consulta: 'bajo' para productos con stock bajo, 'medio' para stock medio, 'todos' para todos los productos con stock"
        },
        "producto_id": {
          "type": "number",
          "description": "ID específico del producto para consultar su stock"
        }
      }
    }
  }
}
```

#### Herramienta 4: Sugerencias de Compras

```json
{
  "type": "function",
  "function": {
    "name": "sugerencias_compras",
    "description": "Genera sugerencias de compras identificando productos que necesitan reposición. Prioriza productos con stock bajo o medio. Incluye información del proveedor y cantidad sugerida.",
    "parameters": {
      "type": "object",
      "properties": {}
    }
  }
}
```

#### Herramienta 5: Consultar Clientes

```json
{
  "type": "function",
  "function": {
    "name": "consultar_clientes",
    "description": "Busca información de clientes por nombre, documento o ID. Retorna información de contacto, historial de compras y estado de cuenta.",
    "parameters": {
      "type": "object",
      "properties": {
        "nombre": {
          "type": "string",
          "description": "Buscar clientes por nombre (búsqueda parcial)"
        },
        "documento": {
          "type": "string",
          "description": "Buscar cliente por número de documento"
        },
        "id": {
          "type": "number",
          "description": "ID específico del cliente"
        }
      }
    }
  }
}
```

#### Herramienta 6: Consultar Estadísticas

```json
{
  "type": "function",
  "function": {
    "name": "consultar_estadisticas",
    "description": "Proporciona estadísticas generales del sistema. Puede consultar estadísticas de ventas, productos o clientes para diferentes períodos.",
    "parameters": {
      "type": "object",
      "properties": {
        "tipo": {
          "type": "string",
          "enum": ["ventas", "productos", "clientes"],
          "description": "Tipo de estadísticas a consultar: 'ventas' para estadísticas de ventas, 'productos' para estadísticas de productos, 'clientes' para estadísticas de clientes"
        },
        "periodo": {
          "type": "string",
          "enum": ["dia", "semana", "mes", "año"],
          "description": "Período para las estadísticas (solo aplica para tipo 'ventas'): 'dia' para hoy, 'semana' para últimos 7 días, 'mes' para mes actual, 'año' para año actual"
        }
      }
    }
  }
}
```

#### Herramienta 7: Consulta SQL Personalizada

```json
{
  "type": "function",
  "function": {
    "name": "consulta_sql",
    "description": "Permite realizar consultas SQL personalizadas en lenguaje natural o SQL directo. IMPORTANTE: Solo se permiten consultas SELECT por seguridad. No se permiten INSERT, UPDATE, DELETE u otras operaciones.",
    "parameters": {
      "type": "object",
      "properties": {
        "query": {
          "type": "string",
          "description": "Consulta SQL (solo SELECT) o descripción en lenguaje natural de lo que se quiere consultar"
        }
      },
      "required": ["query"]
    }
  }
}
```

## 🔗 Conectar Herramientas con Nodos

Cada herramienta debe estar conectada a un nodo **Code** que:

1. Reciba los parámetros del AI Agent
2. Construya la consulta SQL correspondiente
3. Pase la consulta al nodo **MySQL Execute**

### Flujo de Ejecución

```
AI Agent (llama herramienta)
    ↓
Tool: [Nombre] (nodo Code)
    ↓
MySQL Execute
    ↓
Procesar Resultados (nodo Code)
    ↓
AI Agent (recibe resultados)
    ↓
Respond to Chat
```

## 📝 Ejemplo de Configuración Completa

### En el AI Agent:

1. **Tools**: Agrega todas las herramientas usando el formato JSON Schema mostrado arriba
2. **Model**: Selecciona tu modelo (GPT-4, GPT-3.5, etc.)
3. **Temperature**: `0.7`
4. **Max Tokens**: `2000`

### En cada Tool Node (Code):

Cada nodo Code debe:
- Recibir los parámetros de la herramienta
- Construir la consulta SQL
- Retornar `{ query, params, tipo }`

### En MySQL Execute:

- **Operation**: `Execute Query`
- **Query**: `={{ $json.query }}`
- **Query Parameters**: `={{ $json.params }}`

### En Procesar Resultados:

- Formatea los resultados de MySQL
- Retorna `{ resultado, tipo }` para que el AI Agent pueda usarlo

## ⚠️ Notas Importantes

1. **Seguridad**: La herramienta `consulta_sql` valida que solo se ejecuten consultas SELECT
2. **Límites**: Las consultas tienen límites (LIMIT) para evitar respuestas muy grandes
3. **Formato**: Los resultados se formatean en español con separadores de miles
4. **Errores**: Si una consulta falla, se retorna un mensaje de error claro

## 🧪 Pruebas

Después de configurar, prueba cada herramienta:

1. **Consultar Ventas**: "¿Cuántas ventas hubo hoy?"
2. **Consultar Productos**: "Muéstrame el producto con código 123"
3. **Consultar Stock**: "¿Qué productos tienen stock bajo?"
4. **Sugerencias de Compras**: "¿Qué productos debería comprar?"
5. **Consultar Clientes**: "Busca el cliente Juan Pérez"
6. **Consultar Estadísticas**: "Muéstrame las estadísticas de ventas del mes"
7. **Consulta SQL**: "¿Cuántos productos tenemos en total?"

## 🔄 Actualización de Herramientas

Si necesitas agregar nuevas herramientas:

1. Define la herramienta en el AI Agent (formato JSON Schema)
2. Crea un nodo Code para la herramienta
3. Conecta el nodo Code a MySQL Execute
4. Asegúrate de que Procesar Resultados maneje el nuevo tipo

## 📚 Referencias

- [N8N AI Agent Documentation](https://docs.n8n.io/integrations/builtin/langchain-chains/n8n-nodes-langchain.agent/)
- [OpenAI Function Calling](https://platform.openai.com/docs/guides/function-calling)
- [JSON Schema](https://json-schema.org/)

