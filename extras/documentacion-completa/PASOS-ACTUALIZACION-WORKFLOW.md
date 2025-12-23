# 📋 Pasos para Actualizar el Workflow de n8n con Soporte JSON

## ✅ Paso 1: Verificar que el Script se Ejecutó Correctamente

El script ya se ejecutó y actualizó el archivo. Para verificar:

```bash
cd /home/cluna/Documentos/7-Moon-Desarrollos/demonew/demonew
python3 update_json_fields_workflow.py
```

Deberías ver:
- ✓ 11 campos JSON actualizados
- ✓ dbSchema actualizado
- ✓ systemMessage actualizado

## 📥 Paso 2: Importar el Workflow Actualizado en n8n

### Opción A: Importar como Nuevo Workflow (Recomendado para probar primero)

1. Abre n8n en tu navegador
2. Ve a **Workflows** en el menú lateral
3. Haz clic en el botón **"Import from File"** o **"Import"**
4. Selecciona el archivo: `flujos-n8n/pos-moon-asistente-sql-dinamico.json`
5. El workflow se importará con el nombre: **"POS Moon - Asistente Virtual SQL Dinámico"**
6. **NO actives el workflow todavía** (primero verifica la configuración)

### Opción B: Reemplazar el Workflow Existente

1. Abre n8n y ve al workflow existente
2. Haz clic en el menú de tres puntos (⋮) en la esquina superior derecha
3. Selecciona **"Download"** para hacer un backup del workflow actual
4. Luego selecciona **"Import from File"**
5. Selecciona el archivo actualizado: `flujos-n8n/pos-moon-asistente-sql-dinamico.json`
6. Confirma que quieres reemplazar el workflow

## ⚙️ Paso 3: Verificar la Configuración del Workflow

Después de importar, verifica estos nodos:

### 3.1. Nodo "Workflow Configuration"
- Verifica que el `dbSchema` tenga las descripciones actualizadas para campos JSON
- Busca campos como `ventas.metodo_pago` y verifica que diga: "Payment method stored as JSON array..."

### 3.2. Nodo "SQL Query Generator Agent"
- Abre el nodo y ve a **Options** → **System Message**
- Verifica que contenga la sección **"📍 MANEJO DE CAMPOS JSON (CRÍTICO - LEE ESTO PRIMERO)"**
- Debe incluir ejemplos con `JSON_CONTAINS`, `JSON_EXTRACT`, etc.

### 3.3. Credenciales
- Verifica que las credenciales de **MySQL** y **OpenAI** estén configuradas correctamente
- Si faltan, configúralas desde el nodo correspondiente

## 🧪 Paso 4: Probar el Workflow

### 4.1. Activar el Workflow
1. Haz clic en el botón **"Active"** en la esquina superior derecha
2. El workflow debería activarse (el botón se pondrá verde/azul)

### 4.2. Probar Consultas con Campos JSON

Prueba estas consultas para verificar que funciona:

**Prueba 1: Ventas pagadas en efectivo**
```
¿Cuántas ventas se pagaron en efectivo?
```
**SQL esperado:**
```sql
SELECT COUNT(*) FROM ventas WHERE JSON_CONTAINS(metodo_pago, '"Efectivo"', '$[*].tipo')
```

**Prueba 2: Ventas con un producto específico**
```
¿Cuántas ventas tienen el producto con id 123?
```
**SQL esperado:**
```sql
SELECT COUNT(*) FROM ventas WHERE JSON_CONTAINS(productos, '"123"', '$[*].id_producto')
```

**Prueba 3: Ventas pagadas con tarjeta**
```
¿Cuántas ventas se pagaron con tarjeta?
```
**SQL esperado:**
```sql
SELECT COUNT(*) FROM ventas 
WHERE JSON_CONTAINS(metodo_pago, '"Tarjeta"', '$[*].tipo') 
   OR JSON_CONTAINS(metodo_pago, '"Tarjeta Débito"', '$[*].tipo') 
   OR JSON_CONTAINS(metodo_pago, '"Tarjeta Crédito"', '$[*].tipo')
```

### 4.3. Verificar que NO Genera SQL Incorrecto

**Prueba negativa:**
```
ventas pagadas en efectivo
```

**❌ NO debe generar:**
```sql
SELECT COUNT(*) FROM ventas WHERE metodo_pago = 'efectivo'
```

**✅ DEBE generar:**
```sql
SELECT COUNT(*) FROM ventas WHERE JSON_CONTAINS(metodo_pago, '"Efectivo"', '$[*].tipo')
```

## 🔧 Paso 5: Si Algo No Funciona

### Problema: El workflow no genera SQL con JSON_CONTAINS

**Solución:**
1. Verifica que el `systemMessage` tenga la sección de campos JSON
2. Re-ejecuta el script de actualización:
   ```bash
   python3 update_json_fields_workflow.py
   ```
3. Re-importa el workflow en n8n

### Problema: Error al ejecutar SQL con JSON_CONTAINS

**Solución:**
1. Verifica que tu versión de MySQL/MariaDB soporte funciones JSON (MySQL 5.7+ o MariaDB 10.2.3+)
2. Prueba la función manualmente:
   ```sql
   SELECT JSON_CONTAINS('[{"tipo":"Efectivo"}]', '"Efectivo"', '$[*].tipo');
   ```
   Debe devolver `1` (true)

### Problema: El workflow no se activa

**Solución:**
1. Verifica que todos los nodos tengan sus credenciales configuradas
2. Verifica que no haya errores de sintaxis en el JSON del workflow
3. Revisa los logs de n8n para ver errores específicos

## 📝 Resumen de Cambios Aplicados

✅ **11 campos JSON identificados y actualizados:**
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

✅ **Mejoras en systemMessage:**
- Nueva sección "MANEJO DE CAMPOS JSON"
- Ejemplos específicos de uso correcto
- Instrucciones sobre funciones JSON de MySQL
- Paso adicional en el checklist (PASO 7)

✅ **Mejoras en dbSchema:**
- Descripciones actualizadas para todos los campos JSON
- Ejemplos de uso de funciones JSON en las descripciones

## 🚀 Listo para Usar

Una vez completados estos pasos, el workflow debería generar SQL correcto para todos los campos JSON usando funciones de MySQL en lugar de comparaciones directas.
