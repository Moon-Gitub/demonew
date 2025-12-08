# 🤖 Flujo N8N - Asistente Virtual POS Moon

## 📋 Descripción

Este flujo de N8N proporciona un asistente virtual completo para el sistema POS Moon que permite:

- ✅ Consultar ventas diarias y estadísticas
- ✅ Consultar productos y stock
- ✅ Sugerencias de compras (productos con stock bajo)
- ✅ Consultar información de clientes y proveedores
- ✅ Estadísticas y reportes
- ✅ Soporte técnico mediante RAG
- ✅ Consultas en lenguaje natural a MySQL

## 🚀 Instalación

### 1. Importar el flujo en N8N

1. Abre tu instancia de N8N
2. Ve a **Workflows** → **Import from File**
3. Selecciona el archivo `pos-moon-asistente-virtual.json`
4. El flujo se importará con todos los nodos configurados

### 2. Configurar Credenciales

#### A. Credenciales de MySQL

1. En el nodo **MySQL** (o nodos que usen MySQL), configura:
   - **Host**: `{{$env.DB_HOST}}` o tu host de BD
   - **Database**: `{{$env.DB_NAME}}` o tu nombre de BD
   - **User**: `{{$env.DB_USER}}` o tu usuario
   - **Password**: `{{$env.DB_PASS}}` o tu contraseña
   - **Port**: `3306`

   **O usa variables de entorno en N8N:**
   - Ve a **Settings** → **Variables**
   - Crea las variables:
     - `DB_HOST`
     - `DB_NAME`
     - `DB_USER`
     - `DB_PASS`

#### B. Credenciales del AI Agent

1. En el nodo **AI Agent**, configura:
   - **Model**: Selecciona tu modelo (GPT-4, Claude, etc.)
   - **API Key**: Tu API key del proveedor de IA
   - **Temperature**: `0.7` (recomendado)

#### C. Configurar RAG (Opcional)

Si quieres usar RAG para soporte técnico:

1. Configura un nodo de **Vector Store** (Pinecone, Weaviate, etc.)
2. O usa **Embeddings** + **Vector Store** local
3. Actualiza el nodo **RAG Tool** con tus credenciales

### 3. Activar el Workflow

1. Activa el workflow en N8N
2. Copia la URL del webhook del nodo **Chat Trigger**
3. Configura esa URL en el módulo **Integraciones** del sistema POS

## 📊 Estructura del Flujo

```
Chat Trigger
    ↓
AI Agent (con herramientas)
    ├── Tool: Consultar Ventas
    ├── Tool: Consultar Productos
    ├── Tool: Consultar Stock
    ├── Tool: Sugerencias de Compras
    ├── Tool: Consultar Clientes
    ├── Tool: Consultar Estadísticas
    ├── Tool: Consulta SQL Personalizada
    └── Tool: RAG Soporte Técnico
    ↓
Respond to Chat
```

## 🛠️ Herramientas Disponibles

### 1. Consultar Ventas
- **Función**: `consultar_ventas`
- **Parámetros**:
  - `fecha` (opcional): Fecha específica (YYYY-MM-DD)
  - `fecha_inicio` (opcional): Fecha de inicio (YYYY-MM-DD)
  - `fecha_fin` (opcional): Fecha de fin (YYYY-MM-DD)
  - `tipo` (opcional): "diarias", "totales", "por_cliente"
- **Ejemplo**: "¿Cuántas ventas hubo hoy?"

### 2. Consultar Productos
- **Función**: `consultar_productos`
- **Parámetros**:
  - `codigo` (opcional): Código del producto
  - `descripcion` (opcional): Buscar por descripción
  - `stock_minimo` (opcional): Filtrar por stock mínimo
- **Ejemplo**: "¿Qué productos tenemos con código 123?"

### 3. Consultar Stock
- **Función**: `consultar_stock`
- **Parámetros**:
  - `tipo` (opcional): "bajo", "medio", "todos"
  - `producto_id` (opcional): ID específico del producto
- **Ejemplo**: "¿Qué productos tienen stock bajo?"

### 4. Sugerencias de Compras
- **Función**: `sugerencias_compras`
- **Parámetros**: Ninguno
- **Ejemplo**: "¿Qué productos debería comprar?"

### 5. Consultar Clientes
- **Función**: `consultar_clientes`
- **Parámetros**:
  - `nombre` (opcional): Buscar por nombre
  - `documento` (opcional): Buscar por documento
  - `id` (opcional): ID específico del cliente
- **Ejemplo**: "¿Qué información tienes del cliente Juan Pérez?"

### 6. Consultar Estadísticas
- **Función**: `consultar_estadisticas`
- **Parámetros**:
  - `tipo` (opcional): "ventas", "productos", "clientes"
  - `periodo` (opcional): "dia", "semana", "mes", "año"
- **Ejemplo**: "¿Cuáles son las estadísticas de ventas del mes?"

### 7. Consulta SQL Personalizada
- **Función**: `consulta_sql`
- **Parámetros**:
  - `query` (requerido): Consulta SQL en lenguaje natural
- **Ejemplo**: "¿Cuántos productos tenemos en total?"

### 8. RAG Soporte Técnico
- **Función**: `soporte_tecnico`
- **Parámetros**:
  - `pregunta` (requerido): Pregunta sobre soporte técnico
- **Ejemplo**: "¿Cómo configuro una nueva categoría?"

## 📝 Ejemplos de Uso

### Ventas
- "¿Cuántas ventas hubo hoy?"
- "¿Cuál fue el total de ventas del mes?"
- "Muéstrame las ventas de la última semana"
- "¿Cuál es el cliente que más compra?"

### Productos y Stock
- "¿Qué productos tenemos con stock bajo?"
- "Muéstrame todos los productos"
- "¿Cuánto stock tiene el producto con código 123?"
- "¿Qué productos debería comprar?"

### Estadísticas
- "¿Cuáles son las estadísticas de ventas?"
- "¿Cuál es el producto más vendido?"
- "Muéstrame un resumen del día"

### Consultas Personalizadas
- "¿Cuántos clientes tenemos?"
- "¿Cuál es el total de productos en stock?"
- "Muéstrame los proveedores activos"

## 🔧 Personalización

### Agregar Nuevas Herramientas

1. Agrega un nuevo nodo **Code** o **Function** en el flujo
2. Crea la función en el formato requerido
3. Agrega la herramienta al **AI Agent** en la sección **Tools**

### Modificar Consultas SQL

Las consultas SQL están en los nodos **MySQL**. Puedes modificarlas según tus necesidades.

## 📚 Documentación de Tablas

### Tabla: `ventas`
- `id`, `uuid`, `codigo`, `fecha`, `id_cliente`, `id_vendedor`
- `productos` (JSON), `total`, `neto`, `impuesto`
- `metodo_pago`, `estado`, `observaciones_vta`

### Tabla: `productos`
- `id`, `codigo`, `descripcion`, `stock`, `stock_medio`, `stock_bajo`
- `precio_compra`, `precio_venta`, `precio_venta_mayorista`
- `id_categoria`, `id_proveedor`, `ventas`

### Tabla: `clientes`
- `id`, `nombre`, `documento`, `email`, `telefono`, `direccion`
- `compras`, `ultima_compra`, `estado_cuenta`

### Tabla: `proveedores`
- `id`, `nombre`, `cuit`, `telefono`, `email`, `direccion`

### Tabla: `cajas`
- `id`, `fecha`, `monto`, `medio_pago`, `tipo`, `id_venta`

## ⚠️ Notas Importantes

1. **Seguridad**: Las consultas SQL están protegidas para solo permitir SELECT. No se permiten INSERT, UPDATE o DELETE.

2. **Performance**: Las consultas están optimizadas, pero para grandes volúmenes de datos considera agregar índices.

3. **RAG**: El RAG es opcional. Si no lo configuras, el agente seguirá funcionando sin esa herramienta.

4. **Variables de Entorno**: Es recomendable usar variables de entorno en N8N para las credenciales de BD.

## 🐛 Troubleshooting

### Error: "No se puede conectar a MySQL"
- Verifica las credenciales en el nodo MySQL
- Asegúrate de que el servidor MySQL permita conexiones remotas
- Verifica el firewall

### Error: "AI Agent no responde"
- Verifica la API key del proveedor de IA
- Revisa los límites de tu plan
- Verifica que el modelo esté disponible

### Error: "Herramienta no encontrada"
- Asegúrate de que todas las herramientas estén correctamente configuradas en el AI Agent
- Verifica que los nombres de las funciones coincidan

## 📞 Soporte

Para más información, consulta la documentación de N8N o contacta al equipo de desarrollo.

