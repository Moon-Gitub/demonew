#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Script para agregar ejemplos específicos de SUM con filtros de fecha y método de pago
"""

import json
import re

WORKFLOW_FILE = "/home/cluna/Documentos/7-Moon-Desarrollos/demonew/demonew/flujos-n8n/pos-moon-asistente-sql-dinamico.json"

def add_sum_examples(system_message):
    """Agrega ejemplos específicos de SUM con filtros de fecha"""
    
    # Ejemplos adicionales para SUM con fecha
    sum_examples = """
**EJEMPLOS ESPECÍFICOS DE SUM CON FILTROS DE FECHA Y MÉTODO DE PAGO:**

Usuario pregunta: "cuánta plata en efectivo vendí este mes" o "total vendido en efectivo este mes"
❌ INCORRECTO: SELECT SUM(total) FROM ventas WHERE metodo_pago = 'efectivo' AND MONTH(fecha) = MONTH(NOW())
✅ CORRECTO: SELECT SUM(total) AS total_efectivo FROM ventas WHERE JSON_CONTAINS(metodo_pago, '"Efectivo"', '$[*].tipo') AND YEAR(fecha) = YEAR(CURDATE()) AND MONTH(fecha) = MONTH(CURDATE())

Usuario pregunta: "cuánto vendí con tarjeta este mes" o "total vendido con tarjeta este mes"
❌ INCORRECTO: SELECT SUM(total) FROM ventas WHERE metodo_pago LIKE '%tarjeta%' AND MONTH(fecha) = MONTH(NOW())
✅ CORRECTO: SELECT SUM(total) AS total_tarjeta FROM ventas WHERE (JSON_CONTAINS(metodo_pago, '"TD-"', '$[*].tipo') OR JSON_CONTAINS(metodo_pago, '"TC-"', '$[*].tipo') OR JSON_CONTAINS(metodo_pago, '"Tarjeta Débito"', '$[*].tipo') OR JSON_CONTAINS(metodo_pago, '"Tarjeta Crédito"', '$[*].tipo')) AND YEAR(fecha) = YEAR(CURDATE()) AND MONTH(fecha) = MONTH(CURDATE())

Usuario pregunta: "cuánto vendí en efectivo en diciembre" o "total efectivo diciembre 2024"
❌ INCORRECTO: SELECT SUM(total) FROM ventas WHERE metodo_pago = 'efectivo' AND fecha LIKE '%2024-12%'
✅ CORRECTO: SELECT SUM(total) AS total_efectivo FROM ventas WHERE JSON_CONTAINS(metodo_pago, '"Efectivo"', '$[*].tipo') AND YEAR(fecha) = 2024 AND MONTH(fecha) = 12

Usuario pregunta: "ventas en efectivo del año" o "total efectivo este año"
❌ INCORRECTO: SELECT SUM(total) FROM ventas WHERE metodo_pago = 'efectivo' AND YEAR(fecha) = YEAR(NOW())
✅ CORRECTO: SELECT SUM(total) AS total_efectivo FROM ventas WHERE JSON_CONTAINS(metodo_pago, '"Efectivo"', '$[*].tipo') AND YEAR(fecha) = YEAR(CURDATE())

**IMPORTANTE PARA SUMAS:**
- Usa SUM(total) para sumar el campo total de ventas
- Para filtrar por mes actual: YEAR(fecha) = YEAR(CURDATE()) AND MONTH(fecha) = MONTH(CURDATE())
- Para filtrar por año actual: YEAR(fecha) = YEAR(CURDATE())
- Para filtrar por mes/año específico: YEAR(fecha) = 2024 AND MONTH(fecha) = 12
- SIEMPRE combina el filtro JSON del método de pago con el filtro de fecha usando AND

"""
    
    # Buscar donde insertar (después de los ejemplos de COUNT)
    # Buscar la sección de ejemplos de metodo_pago
    pattern = r'(Usuario pregunta: "ventas con transferencia".*?OR JSON_CONTAINS\(metodo_pago, \'"TR-"\'[^)]+\)\n)'
    match = re.search(pattern, system_message, re.DOTALL)
    
    if match:
        # Insertar después de los ejemplos de COUNT
        insert_pos = match.end()
        updated_message = system_message[:insert_pos] + sum_examples + system_message[insert_pos:]
        return updated_message
    else:
        # Si no encuentra, buscar la sección PASO 8
        paso8_pattern = r'(□ PASO 8:.*?NO uses un solo JSON_CONTAINS, usa múltiples con OR\n)'
        match = re.search(paso8_pattern, system_message, re.DOTALL)
        if match:
            insert_pos = match.end()
            updated_message = system_message[:insert_pos] + sum_examples + system_message[insert_pos:]
            return updated_message
    
    # Si no encuentra ningún patrón, agregar antes de la sección "📍 MANEJO DE CAMPOS JSON"
    json_section = system_message.find('**📍 MANEJO DE CAMPOS JSON (CRÍTICO - LEE ESTO PRIMERO):**')
    if json_section > 0:
        updated_message = system_message[:json_section] + sum_examples + system_message[json_section:]
        return updated_message
    
    return system_message

def main():
    print("🔄 Agregando ejemplos de SUM con filtros de fecha...")
    print("=" * 70)
    
    # Leer el workflow
    with open(WORKFLOW_FILE, 'r', encoding='utf-8') as f:
        workflow = json.load(f)
    
    # Buscar el nodo "SQL Query Generator Agent"
    updated = False
    for node in workflow.get("nodes", []):
        if node.get("name") == "SQL Query Generator Agent":
            options = node.get("parameters", {}).get("options", {})
            if "systemMessage" in options:
                system_message = options["systemMessage"]
                
                # Remover el prefijo "=" si existe
                if system_message.startswith("="):
                    system_message = system_message[1:]
                
                # Verificar si ya tiene los ejemplos
                if "cuánta plata en efectivo vendí este mes" in system_message:
                    print("⚠️  Los ejemplos de SUM ya están presentes")
                    return
                
                # Actualizar con ejemplos de SUM
                updated_message = add_sum_examples(system_message)
                
                # Agregar el prefijo "=" de nuevo
                options["systemMessage"] = "=" + updated_message
                updated = True
                print("✅ Ejemplos de SUM agregados\n")
                break
    
    if updated:
        # Guardar el workflow actualizado
        print("💾 Guardando workflow actualizado...")
        with open(WORKFLOW_FILE, 'w', encoding='utf-8') as f:
            json.dump(workflow, f, indent=2, ensure_ascii=False)
        print("✅ Workflow guardado exitosamente")
    else:
        print("⚠️  No se pudo actualizar el systemMessage")
    
    print("=" * 70)
    print("\n📊 Resumen de mejoras:")
    print("   ✅ Ejemplos de SUM con filtros de fecha agregados")
    print("   ✅ Ejemplos específicos para 'este mes', 'este año', 'mes específico'")
    print("   ✅ Combinación correcta de JSON_CONTAINS con filtros de fecha")
    print("\n💡 El modelo ahora puede generar consultas de SUM correctamente")

if __name__ == "__main__":
    main()
