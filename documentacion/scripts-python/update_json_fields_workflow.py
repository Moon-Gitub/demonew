#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Script para actualizar el workflow de n8n con soporte completo para campos JSON
"""

import json
import re

# Archivo del workflow
WORKFLOW_FILE = "/home/cluna/Documentos/7-Moon-Desarrollos/demonew/demonew/flujos-n8n/pos-moon-asistente-sql-dinamico.json"

# Mapeo de campos JSON por tabla
JSON_FIELDS = {
    "ventas": {
        "productos": "Products stored as JSON array. Use MySQL JSON functions (JSON_EXTRACT, JSON_CONTAINS, JSON_SEARCH) to query nested data. Example: JSON_CONTAINS(productos, '\"123\"', '$[*].id_producto')",
        "metodo_pago": "Payment method stored as JSON array. Format: [{\"tipo\":\"Efectivo\",\"entrega\":\"17569.20\"}]. Use MySQL JSON functions to query. Example: JSON_CONTAINS(metodo_pago, '\"Efectivo\"', '$[*].tipo')",
        "impuesto_detalle": "Tax details stored as JSON. Use MySQL JSON functions (JSON_EXTRACT, JSON_CONTAINS) to query nested data",
        "pedido_afip": "AFIP request data stored as JSON. Use MySQL JSON functions to query nested data",
        "respuesta_afip": "AFIP response data stored as JSON. Use MySQL JSON functions to query nested data"
    },
    "presupuestos": {
        "productos": "Products stored as JSON array. Use MySQL JSON functions (JSON_EXTRACT, JSON_CONTAINS, JSON_SEARCH) to query nested data. Example: JSON_CONTAINS(productos, '\"123\"', '$[*].id_producto')",
        "metodo_pago": "Payment method stored as JSON array. Format: [{\"tipo\":\"Efectivo\",\"entrega\":\"17569.20\"}]. Use MySQL JSON functions to query. Example: JSON_CONTAINS(metodo_pago, '\"Efectivo\"', '$[*].tipo')",
        "impuesto_detalle": "Tax details stored as JSON. Use MySQL JSON functions (JSON_EXTRACT, JSON_CONTAINS) to query nested data"
    },
    "compras": {
        "productos": "Products stored as JSON array. Use MySQL JSON functions (JSON_EXTRACT, JSON_CONTAINS, JSON_SEARCH) to query nested data. Example: JSON_CONTAINS(productos, '\"123\"', '$[*].id_producto')"
    },
    "pedidos": {
        "productos": "Products stored as JSON array. Use MySQL JSON functions (JSON_EXTRACT, JSON_CONTAINS, JSON_SEARCH) to query nested data. Example: JSON_CONTAINS(productos, '\"123\"', '$[*].id_producto')"
    },
    "clientes_cuenta_corriente": {
        "metodo_pago": "Payment method stored as JSON (can be NULL). Use MySQL JSON functions to query if present. Example: JSON_CONTAINS(metodo_pago, '\"Efectivo\"', '$[*].tipo')"
    }
}

def update_db_schema(db_schema_str):
    """Actualiza las descripciones de campos JSON en el dbSchema"""
    schema = json.loads(db_schema_str)
    
    for table in schema.get("tables", []):
        table_name = table.get("name", "")
        if table_name in JSON_FIELDS:
            for column in table.get("columns", []):
                col_name = column.get("name", "")
                if col_name in JSON_FIELDS[table_name]:
                    # Actualizar la descripción
                    column["description"] = JSON_FIELDS[table_name][col_name]
                    print(f"✓ Actualizado: {table_name}.{col_name}")
    
    return json.dumps(schema, indent=2, ensure_ascii=False)

def update_system_message(system_message):
    """Agrega la sección de campos JSON al systemMessage"""
    
    json_section = """
**📍 MANEJO DE CAMPOS JSON (CRÍTICO - LEE ESTO PRIMERO):**

Algunos campos almacenan datos como JSON strings (NO como strings simples). DEBES usar funciones JSON de MySQL para consultarlos.

**CAMPOS JSON IDENTIFICADOS:**
- `ventas.productos` - Array JSON de productos
- `ventas.metodo_pago` - Array JSON: [{"tipo":"Efectivo","entrega":"17569.20"}]
- `ventas.impuesto_detalle` - Objeto JSON con detalles de impuestos
- `ventas.pedido_afip` - JSON con datos de pedido AFIP
- `ventas.respuesta_afip` - JSON con respuesta AFIP
- `presupuestos.productos` - Array JSON de productos
- `presupuestos.metodo_pago` - Array JSON: [{"tipo":"Efectivo","entrega":"17569.20"}]
- `presupuestos.impuesto_detalle` - Objeto JSON con detalles de impuestos
- `compras.productos` - Array JSON de productos
- `pedidos.productos` - Array JSON de productos
- `clientes_cuenta_corriente.metodo_pago` - JSON (puede ser NULL)

**❌ ERRORES CRÍTICOS QUE DEBES EVITAR:**

**ERROR 5: Consultar campos JSON como strings simples**
❌ INCORRECTO: SELECT COUNT(*) FROM ventas WHERE metodo_pago = 'efectivo'
❌ INCORRECTO: SELECT * FROM ventas WHERE metodo_pago LIKE '%Efectivo%'
✅ CORRECTO: SELECT COUNT(*) FROM ventas WHERE JSON_CONTAINS(metodo_pago, '"Efectivo"', '$[*].tipo')

**ERROR 6: No usar funciones JSON para campos JSON**
❌ INCORRECTO: SELECT * FROM ventas WHERE productos = '123'
✅ CORRECTO: SELECT * FROM ventas WHERE JSON_CONTAINS(productos, '"123"', '$[*].id_producto')

**FUNCIONES JSON DE MYSQL QUE DEBES USAR:**

1. **JSON_CONTAINS(campo, valor, ruta)** - Verifica si un valor existe en el JSON
   - Ejemplo: `JSON_CONTAINS(ventas.metodo_pago, '"Efectivo"', '$[*].tipo')`
   - Busca "Efectivo" en la ruta `$[*].tipo` (todos los elementos del array, campo tipo)

2. **JSON_EXTRACT(campo, ruta)** - Extrae un valor del JSON
   - Ejemplo: `JSON_EXTRACT(ventas.metodo_pago, '$[0].entrega')`
   - Extrae el campo "entrega" del primer elemento del array

3. **JSON_SEARCH(campo, tipo, valor, ruta)** - Busca un valor y devuelve la ruta
   - Ejemplo: `JSON_SEARCH(ventas.productos, 'one', '123', NULL, '$[*].id_producto')`

**EJEMPLOS ESPECÍFICOS CORRECTOS:**

Usuario pregunta: "ventas pagadas en efectivo"
❌ NUNCA hagas: SELECT COUNT(*) FROM ventas WHERE metodo_pago = 'efectivo'
✅ SIEMPRE haz: SELECT COUNT(*) FROM ventas WHERE JSON_CONTAINS(metodo_pago, '"Efectivo"', '$[*].tipo')

Usuario pregunta: "ventas con producto id 123"
❌ NUNCA hagas: SELECT * FROM ventas WHERE productos LIKE '%123%'
✅ SIEMPRE haz: SELECT * FROM ventas WHERE JSON_CONTAINS(productos, '"123"', '$[*].id_producto')

Usuario pregunta: "ventas pagadas con tarjeta"
❌ NUNCA hagas: SELECT * FROM ventas WHERE metodo_pago = 'tarjeta'
✅ SIEMPRE haz: SELECT * FROM ventas WHERE JSON_CONTAINS(metodo_pago, '"Tarjeta"', '$[*].tipo') OR JSON_CONTAINS(metodo_pago, '"Tarjeta Débito"', '$[*].tipo') OR JSON_CONTAINS(metodo_pago, '"Tarjeta Crédito"', '$[*].tipo')

**PASO ADICIONAL EN EL CHECKLIST:**

□ PASO 7: Si el campo que vas a usar está en la lista de campos JSON arriba:
   - NUNCA uses comparaciones directas (=, LIKE, etc.)
   - SIEMPRE usa funciones JSON (JSON_CONTAINS, JSON_EXTRACT, JSON_SEARCH)
   - Verifica la estructura del JSON en la descripción del campo
   - Si buscas un valor en un array, usa la ruta '$[*].campo'
   - Si buscas un valor en un objeto, usa la ruta '$.campo'

**VERIFICACIÓN FINAL ADICIONAL:**

Antes de devolver el SQL, verifica:
5. ¿Estoy usando algún campo de la lista de campos JSON? → Si SÍ, ¿estoy usando funciones JSON? → Si NO, CORRIGE

"""
    
    # Insertar la sección antes de "**SEGURIDAD:**"
    if "**SEGURIDAD:**" in system_message:
        system_message = system_message.replace("**SEGURIDAD:**", json_section + "\n**SEGURIDAD:**")
    else:
        # Si no encuentra SEGURIDAD, agregar al final antes del último recordatorio
        system_message = system_message.rstrip() + "\n\n" + json_section
    
    return system_message

def main():
    print("🔄 Actualizando workflow de n8n con soporte para campos JSON...")
    print("=" * 70)
    
    # Leer el workflow
    with open(WORKFLOW_FILE, 'r', encoding='utf-8') as f:
        workflow = json.load(f)
    
    # Buscar el nodo "Workflow Configuration"
    for node in workflow.get("nodes", []):
        if node.get("name") == "Workflow Configuration":
            assignments = node.get("parameters", {}).get("assignments", {}).get("assignments", [])
            for assignment in assignments:
                if assignment.get("name") == "dbSchema":
                    print("\n📝 Actualizando dbSchema...")
                    old_value = assignment.get("value", "")
                    new_value = update_db_schema(old_value)
                    assignment["value"] = new_value
                    print(f"✓ dbSchema actualizado\n")
                    break
    
    # Buscar el nodo "SQL Query Generator Agent"
    for node in workflow.get("nodes", []):
        if node.get("name") == "SQL Query Generator Agent":
            options = node.get("parameters", {}).get("options", {})
            if "systemMessage" in options:
                print("📝 Actualizando systemMessage...")
                old_message = options["systemMessage"]
                # Remover el prefijo "=" si existe
                if old_message.startswith("="):
                    old_message = old_message[1:]
                new_message = update_system_message(old_message)
                # Agregar el prefijo "=" de nuevo
                options["systemMessage"] = "=" + new_message
                print(f"✓ systemMessage actualizado\n")
                break
    
    # Guardar el workflow actualizado
    print("💾 Guardando workflow actualizado...")
    with open(WORKFLOW_FILE, 'w', encoding='utf-8') as f:
        json.dump(workflow, f, indent=2, ensure_ascii=False)
    
    print("=" * 70)
    print("✅ ¡Workflow actualizado exitosamente!")
    print(f"\n📊 Campos JSON actualizados:")
    total = sum(len(fields) for fields in JSON_FIELDS.values())
    print(f"   - {len(JSON_FIELDS)} tablas con campos JSON")
    print(f"   - {total} campos JSON en total")
    print(f"\n📁 Archivo: {WORKFLOW_FILE}")

if __name__ == "__main__":
    main()
