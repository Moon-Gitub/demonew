# 📋 Instrucciones para Actualizar el Workflow en Hostinger

## ✅ Estado Actual del Workflow

El workflow está **completamente actualizado** con:
- ✅ 14 campos JSON identificados y documentados
- ✅ Sección completa de manejo de campos JSON en systemMessage
- ✅ Referencia al dbSchema configurada
- ✅ Sin duplicados
- ✅ Ejemplos específicos para consultas JSON

## 📥 Pasos para Importar en n8n (Hostinger)

### Paso 1: Acceder a n8n
1. Accede a tu panel de Hostinger
2. Abre n8n (puede estar en un subdominio o puerto específico)
3. Inicia sesión con tus credenciales

### Paso 2: Importar el Workflow

**Opción A: Importar como Nuevo (Recomendado)**
1. En n8n, ve a **Workflows** en el menú lateral
2. Haz clic en **"Import from File"** o el botón **"+"** → **"Import"**
3. Selecciona el archivo: `flujos-n8n/pos-moon-asistente-sql-dinamico.json`
4. El workflow se importará con el nombre: **"POS Moon - Asistente Virtual SQL Dinámico"**

**Opción B: Reemplazar Existente**
1. Abre el workflow existente en n8n
2. Menú de tres puntos (⋮) → **"Download"** (para hacer backup)
3. Luego **"Import from File"**
4. Selecciona el archivo actualizado
5. Confirma el reemplazo

### Paso 3: Verificar y Corregir la Referencia al Nodo

**⚠️ IMPORTANTE:** Si ves el error "Referenced node doesn't exist":

1. Abre el nodo **"SQL Query Generator Agent"**
2. Ve a la pestaña **"Parameters"** → **"Options"** → **"System Message"**
3. Busca la línea que dice:
   ```
   **Esquema de base de datos (OBLIGATORIO - ÚSALO SIEMPRE):**
   {{ $("Workflow Configuration").first().json.dbSchema }}
   ```

4. **Si el error persiste**, cambia la referencia a una de estas opciones:

   **Opción 1 (Recomendada):** Usar el nodo de entrada directamente
   ```
   {{ $input.first().json.dbSchema }}
   ```

   **Opción 2:** Usar el ID del nodo (más confiable)
   - Primero, abre el nodo "Workflow Configuration"
   - Copia su ID (está en la URL o en los metadatos)
   - Reemplaza con: `{{ $("575d32ad-8ac4-490b-ab19-178468dce4c1").first().json.dbSchema }}`
   - (El ID puede variar, úsalo del nodo real en tu n8n)

   **Opción 3:** Verificar el nombre exacto del nodo
   - Asegúrate de que el nodo se llame exactamente **"Workflow Configuration"** (sin espacios extra, sin números)
   - Si tiene otro nombre, cambia la referencia para que coincida

### Paso 4: Verificar Credenciales

1. **MySQL:**
   - Abre el nodo **"Execute SQL Query"**
   - Verifica que las credenciales de MySQL estén configuradas
   - Debe apuntar a tu base de datos en Hostinger

2. **OpenAI:**
   - Abre el nodo **"OpenAI Chat Model"**
   - Verifica que las credenciales de OpenAI estén configuradas
   - Debe tener tu API key de OpenAI

### Paso 5: Activar el Workflow

1. Haz clic en el botón **"Active"** en la esquina superior derecha
2. El workflow debería activarse (el botón se pondrá verde/azul)
3. Si hay errores, revisa los logs en la pestaña **"Logs"**

### Paso 6: Probar el Workflow

Prueba estas consultas para verificar que funciona:

**Prueba 1:**
```
¿Cuántas ventas se pagaron en efectivo?
```
**SQL esperado:**
```sql
SELECT COUNT(*) FROM ventas 
WHERE JSON_CONTAINS(metodo_pago, '"Efectivo"', '$[*].tipo')
```

**Prueba 2:**
```
¿Cuántas ventas tienen el producto con id 123?
```
**SQL esperado:**
```sql
SELECT COUNT(*) FROM ventas 
WHERE JSON_CONTAINS(productos, '"123"', '$[*].id_producto')
```

## 🔧 Solución de Problemas Comunes en Hostinger

### Error: "Referenced node doesn't exist"

**Causa:** n8n no puede encontrar el nodo "Workflow Configuration"

**Soluciones:**
1. Verifica que el nodo se llame exactamente "Workflow Configuration"
2. Cambia la referencia a `{{ $input.first().json.dbSchema }}`
3. Verifica que el nodo esté conectado antes del "SQL Query Generator Agent"

### Error: "Cannot read property 'dbSchema' of undefined"

**Causa:** El nodo anterior no está pasando el campo dbSchema

**Solución:**
1. Abre el nodo "Workflow Configuration"
2. Verifica que tenga un assignment llamado "dbSchema"
3. Verifica que el tipo sea "object" y tenga el JSON del esquema

### Error al ejecutar SQL con JSON_CONTAINS

**Causa:** Tu versión de MySQL/MariaDB puede no soportar funciones JSON

**Solución:**
1. Verifica la versión de MySQL:
   ```sql
   SELECT VERSION();
   ```
2. Necesitas MySQL 5.7+ o MariaDB 10.2.3+ para funciones JSON
3. Si tu versión es menor, considera actualizar o usar otra solución

## 📊 Resumen de Campos JSON Configurados

El workflow está configurado para manejar estos campos JSON:

- `ventas.productos`
- `ventas.metodo_pago`
- `ventas.impuesto_detalle`
- `ventas.pedido_afip`
- `ventas.respuesta_afip`
- `presupuestos.productos`
- `presupuestos.metodo_pago`
- `presupuestos.impuesto_detalle`
- `compras.productos`
- `pedidos.productos`
- `clientes_cuenta_corriente.metodo_pago`

## ✅ Checklist Final

Antes de considerar el workflow listo, verifica:

- [ ] Workflow importado sin errores
- [ ] Referencia al nodo funciona (sin errores en rojo)
- [ ] Credenciales MySQL configuradas
- [ ] Credenciales OpenAI configuradas
- [ ] Workflow activado
- [ ] Prueba de consulta simple funciona
- [ ] Prueba de consulta con campo JSON funciona

## 🚀 Listo para Usar

Una vez completados estos pasos, el workflow debería funcionar correctamente en tu entorno de Hostinger y generar SQL correcto para todos los campos JSON.
