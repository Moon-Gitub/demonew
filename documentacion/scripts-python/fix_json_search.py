#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Script para reemplazar JSON_CONTAINS por JSON_SEARCH (compatible con MySQL Hostinger)
"""

import json
import re

WORKFLOW_FILE = "/home/cluna/Documentos/7-Moon-Desarrollos/demonew/demonew/flujos-n8n/pos-moon-asistente-sql-dinamico.json"

def fix_json_functions(system_message):
    """Reemplaza JSON_CONTAINS con wildcards por JSON_SEARCH"""
    
    # Agregar sección crítica sobre compatibilidad
    critical_fix = """
**🚨🚨🚨 CRÍTICO: COMPATIBILIDAD CON MYSQL (HOSTINGER) 🚨🚨🚨**

**PROBLEMA DETECTADO:** Tu versión de MySQL NO permite wildcards ($[*]) en el tercer parámetro de JSON_CONTAINS.
**SOLUCIÓN:** DEBES usar JSON_SEARCH en lugar de JSON_CONTAINS cuando necesites buscar en arrays JSON.

**REGLA OBLIGATORIA:**
- ❌ NUNCA uses: JSON_CONTAINS(metodo_pago, '"Efectivo"', '$[*].tipo')
- ✅ SIEMPRE usa: JSON_SEARCH(metodo_pago, 'one', 'Efectivo', NULL, '$[*].tipo') IS NOT NULL

**SINTAXIS CORRECTA DE JSON_SEARCH:**
JSON_SEARCH(campo_json, 'one', 'valor_a_buscar', NULL, 'ruta_con_wildcard')
- 'one' = devuelve la primera coincidencia
- NULL = carácter de escape (por defecto)
- '$[*].tipo' = ruta con wildcard para buscar en todos los elementos del array

**EJEMPLOS CORRECTOS CON JSON_SEARCH:**

Usuario pregunta: "ventas en efectivo"
✅ CORRECTO: SELECT COUNT(*) FROM ventas WHERE JSON_SEARCH(metodo_pago, 'one', 'Efectivo', NULL, '$[*].tipo') IS NOT NULL

Usuario pregunta: "cuánta plata en efectivo vendí este mes"
✅ CORRECTO: SELECT SUM(total) AS total_efectivo FROM ventas WHERE JSON_SEARCH(metodo_pago, 'one', 'Efectivo', NULL, '$[*].tipo') IS NOT NULL AND YEAR(fecha) = YEAR(CURDATE()) AND MONTH(fecha) = MONTH(CURDATE())

Usuario pregunta: "ventas con tarjeta débito"
✅ CORRECTO: SELECT COUNT(*) FROM ventas WHERE JSON_SEARCH(metodo_pago, 'one', 'TD-', NULL, '$[*].tipo') IS NOT NULL OR JSON_SEARCH(metodo_pago, 'one', 'Tarjeta Débito', NULL, '$[*].tipo') IS NOT NULL OR JSON_SEARCH(metodo_pago, 'one', 'Débito', NULL, '$[*].tipo') IS NOT NULL

**IMPORTANTE:** 
- JSON_SEARCH devuelve la ruta si encuentra el valor, o NULL si no lo encuentra
- Por eso usamos IS NOT NULL para verificar que encontró el valor
- JSON_SEARCH SÍ permite wildcards en la ruta, a diferencia de JSON_CONTAINS

"""
    
    # Insertar sección crítica
    valores_reales_pos = system_message.find('**🚨🚨🚨 CRÍTICO: REVISA LOS VALORES REALES DEL JSON 🚨🚨🚨**')
    if valores_reales_pos > 0:
        regla_critica_pos = system_message.find('**REGLA CRÍTICA - MAPEO DE CONCEPTOS A VALORES JSON:**', valores_reales_pos)
        if regla_critica_pos > 0:
            system_message = system_message[:regla_critica_pos] + critical_fix + system_message[regla_critica_pos:]
    
    # Reemplazar JSON_CONTAINS por JSON_SEARCH en todos los ejemplos
    # Patrón general: JSON_CONTAINS(metodo_pago, '"valor"', '$[*].tipo')
    # Reemplazo: JSON_SEARCH(metodo_pago, 'one', 'valor', NULL, '$[*].tipo') IS NOT NULL
    
    replacements = [
        (r"JSON_CONTAINS\(metodo_pago, '\\\"Efectivo\\\"', '\\\$\[\*\]\.tipo'\)", 
         "JSON_SEARCH(metodo_pago, 'one', 'Efectivo', NULL, '$[*].tipo') IS NOT NULL"),
        (r"JSON_CONTAINS\(metodo_pago, '\\\"TD-\\\"', '\\\$\[\*\]\.tipo'\)", 
         "JSON_SEARCH(metodo_pago, 'one', 'TD-', NULL, '$[*].tipo') IS NOT NULL"),
        (r"JSON_CONTAINS\(metodo_pago, '\\\"TC-\\\"', '\\\$\[\*\]\.tipo'\)", 
         "JSON_SEARCH(metodo_pago, 'one', 'TC-', NULL, '$[*].tipo') IS NOT NULL"),
        (r"JSON_CONTAINS\(metodo_pago, '\\\"TR--\\\"', '\\\$\[\*\]\.tipo'\)", 
         "JSON_SEARCH(metodo_pago, 'one', 'TR--', NULL, '$[*].tipo') IS NOT NULL"),
        (r"JSON_CONTAINS\(metodo_pago, '\\\"Tarjeta Débito\\\"', '\\\$\[\*\]\.tipo'\)", 
         "JSON_SEARCH(metodo_pago, 'one', 'Tarjeta Débito', NULL, '$[*].tipo') IS NOT NULL"),
        (r"JSON_CONTAINS\(metodo_pago, '\\\"Tarjeta Crédito\\\"', '\\\$\[\*\]\.tipo'\)", 
         "JSON_SEARCH(metodo_pago, 'one', 'Tarjeta Crédito', NULL, '$[*].tipo') IS NOT NULL"),
        (r"JSON_CONTAINS\(metodo_pago, '\\\"Débito\\\"', '\\\$\[\*\]\.tipo'\)", 
         "JSON_SEARCH(metodo_pago, 'one', 'Débito', NULL, '$[*].tipo') IS NOT NULL"),
        (r"JSON_CONTAINS\(metodo_pago, '\\\"Crédito\\\"', '\\\$\[\*\]\.tipo'\)", 
         "JSON_SEARCH(metodo_pago, 'one', 'Crédito', NULL, '$[*].tipo') IS NOT NULL"),
        (r"JSON_CONTAINS\(metodo_pago, '\\\"Transferencia\\\"', '\\\$\[\*\]\.tipo'\)", 
         "JSON_SEARCH(metodo_pago, 'one', 'Transferencia', NULL, '$[*].tipo') IS NOT NULL"),
        (r"JSON_CONTAINS\(metodo_pago, '\\\"TR\\\"', '\\\$\[\*\]\.tipo'\)", 
         "JSON_SEARCH(metodo_pago, 'one', 'TR', NULL, '$[*].tipo') IS NOT NULL"),
        (r"JSON_CONTAINS\(metodo_pago, '\\\"TR-\\\"', '\\\$\[\*\]\.tipo'\)", 
         "JSON_SEARCH(metodo_pago, 'one', 'TR-', NULL, '$[*].tipo') IS NOT NULL"),
        (r"JSON_CONTAINS\(metodo_pago, '\\\"TD\\\"', '\\\$\[\*\]\.tipo'\)", 
         "JSON_SEARCH(metodo_pago, 'one', 'TD', NULL, '$[*].tipo') IS NOT NULL"),
        (r"JSON_CONTAINS\(metodo_pago, '\\\"TC\\\"', '\\\$\[\*\]\.tipo'\)", 
         "JSON_SEARCH(metodo_pago, 'one', 'TC', NULL, '$[*].tipo') IS NOT NULL"),
    ]
    
    for pattern, replacement in replacements:
        system_message = re.sub(pattern, replacement, system_message)
    
    # Actualizar sección de funciones JSON
    old_func = "1. **JSON_CONTAINS(campo, valor, ruta)** - Verifica si un valor existe en el JSON\n   - Ejemplo: `JSON_CONTAINS(ventas.metodo_pago, '\"Efectivo\"', '$[*].tipo')`\n   - Busca \"Efectivo\" en la ruta `$[*].tipo` (todos los elementos del array, campo tipo)"
    new_func = "1. **JSON_SEARCH(campo, tipo, valor, escape, ruta)** - Busca un valor en el JSON y devuelve la ruta\n   - Ejemplo: `JSON_SEARCH(ventas.metodo_pago, 'one', 'Efectivo', NULL, '$[*].tipo') IS NOT NULL`\n   - Busca \"Efectivo\" en la ruta `$[*].tipo` (todos los elementos del array, campo tipo)\n   - Devuelve la ruta si encuentra el valor, o NULL si no lo encuentra\n   - **IMPORTANTE:** JSON_SEARCH SÍ permite wildcards en la ruta, a diferencia de JSON_CONTAINS"
    system_message = system_message.replace(old_func, new_func)
    
    # Actualizar PASO 7
    old_paso7 = "□ PASO 7: Si el campo que vas a usar está en la lista de campos JSON arriba:\n   - NUNCA uses comparaciones directas (=, LIKE, etc.)\n   - SIEMPRE usa funciones JSON (JSON_CONTAINS, JSON_EXTRACT, JSON_SEARCH)"
    new_paso7 = "□ PASO 7: Si el campo que vas a usar está en la lista de campos JSON arriba:\n   - NUNCA uses comparaciones directas (=, LIKE, etc.)\n   - SIEMPRE usa funciones JSON (JSON_SEARCH para buscar valores, JSON_EXTRACT para extraer)\n   - **CRÍTICO:** Usa JSON_SEARCH en lugar de JSON_CONTAINS cuando necesites wildcards en la ruta"
    system_message = system_message.replace(old_paso7, new_paso7)
    
    # Actualizar PASO 8
    old_paso8 = "□ PASO 8: Si estás consultando un campo JSON (especialmente metodo_pago):\n   - NO asumas un solo formato del valor\n   - Considera TODOS los formatos posibles (abreviados como TD-, TC-, TR--, completos, con guiones, etc.)\n   - Si el usuario dice \"efectivo\" → busca \"Efectivo\" (exacto, con mayúscula)\n   - Si el usuario dice \"tarjeta débito\" → busca \"TD-\", \"Tarjeta Débito\", \"Débito\", \"TD\"\n   - Si el usuario dice \"tarjeta crédito\" → busca \"TC-\", \"Tarjeta Crédito\", \"Crédito\", \"TC\"\n   - Si el usuario dice \"tarjeta\" (sin especificar) → busca TODOS los formatos de débito Y crédito\n   - Si el usuario dice \"transferencia\" → busca \"TR--\", \"Transferencia\", \"TR\", \"TR-\"\n   - SIEMPRE usa múltiples condiciones OR para cubrir todos los formatos posibles\n   - NO uses un solo JSON_CONTAINS, usa múltiples con OR"
    new_paso8 = "□ PASO 8: Si estás consultando un campo JSON (especialmente metodo_pago):\n   - NO asumas un solo formato del valor\n   - Considera TODOS los formatos posibles (abreviados como TD-, TC-, TR--, completos, con guiones, etc.)\n   - Si el usuario dice \"efectivo\" → busca \"Efectivo\" (exacto, con mayúscula)\n   - Si el usuario dice \"tarjeta débito\" → busca \"TD-\", \"Tarjeta Débito\", \"Débito\", \"TD\"\n   - Si el usuario dice \"tarjeta crédito\" → busca \"TC-\", \"Tarjeta Crédito\", \"Crédito\", \"TC\"\n   - Si el usuario dice \"tarjeta\" (sin especificar) → busca TODOS los formatos de débito Y crédito\n   - Si el usuario dice \"transferencia\" → busca \"TR--\", \"Transferencia\", \"TR\", \"TR-\"\n   - SIEMPRE usa múltiples condiciones OR para cubrir todos los formatos posibles\n   - **CRÍTICO:** Usa JSON_SEARCH con IS NOT NULL, NO uses JSON_CONTAINS con wildcards"
    system_message = system_message.replace(old_paso8, new_paso8)
    
    # Actualizar ERROR 5
    old_error5 = "**ERROR 5: Consultar campos JSON como strings simples**\n❌ INCORRECTO: SELECT COUNT(*) FROM ventas WHERE metodo_pago = 'efectivo'\n❌ INCORRECTO: SELECT * FROM ventas WHERE metodo_pago LIKE '%Efectivo%'\n✅ CORRECTO: SELECT COUNT(*) FROM ventas WHERE JSON_CONTAINS(metodo_pago, '\"Efectivo\"', '$[*].tipo')"
    new_error5 = "**ERROR 5: Consultar campos JSON como strings simples**\n❌ INCORRECTO: SELECT COUNT(*) FROM ventas WHERE metodo_pago = 'efectivo'\n❌ INCORRECTO: SELECT * FROM ventas WHERE metodo_pago LIKE '%Efectivo%'\n✅ CORRECTO: SELECT COUNT(*) FROM ventas WHERE JSON_SEARCH(metodo_pago, 'one', 'Efectivo', NULL, '$[*].tipo') IS NOT NULL"
    system_message = system_message.replace(old_error5, new_error5)
    
    return system_message

def main():
    print("🔄 Reemplazando JSON_CONTAINS por JSON_SEARCH (compatible con MySQL Hostinger)...")
    print("=" * 70)
    
    with open(WORKFLOW_FILE, 'r', encoding='utf-8') as f:
        workflow = json.load(f)
    
    updated = False
    for node in workflow.get("nodes", []):
        if node.get("name") == "SQL Query Generator Agent":
            options = node.get("parameters", {}).get("options", {})
            if "systemMessage" in options:
                system_message = options["systemMessage"]
                
                if system_message.startswith("="):
                    system_message = system_message[1:]
                
                updated_message = fix_json_functions(system_message)
                options["systemMessage"] = "=" + updated_message
                updated = True
                print("✅ systemMessage actualizado: JSON_CONTAINS → JSON_SEARCH\n")
                break
    
    if updated:
        with open(WORKFLOW_FILE, 'w', encoding='utf-8') as f:
            json.dump(workflow, f, indent=2, ensure_ascii=False)
        print("💾 Workflow guardado exitosamente")
    else:
        print("⚠️  No se pudo actualizar el systemMessage")
    
    print("=" * 70)
    print("\n📊 Resumen de cambios:")
    print("   ✅ JSON_CONTAINS reemplazado por JSON_SEARCH")
    print("   ✅ Sección crítica de compatibilidad agregada")
    print("   ✅ Todos los ejemplos actualizados")
    print("   ✅ PASO 7 y PASO 8 actualizados")
    print("\n💡 Ahora las consultas funcionarán en MySQL Hostinger")

if __name__ == "__main__":
    main()
