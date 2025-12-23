#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Script para agregar instrucciones sobre valores reales del JSON
"""

import json

WORKFLOW_FILE = "/home/cluna/Documentos/7-Moon-Desarrollos/demonew/demonew/flujos-n8n/pos-moon-asistente-sql-dinamico.json"

def update_system_message_with_real_values(system_message):
    """Agrega sección crítica sobre valores reales del JSON"""
    
    # Sección crítica sobre valores reales
    critical_section = """
**🚨🚨🚨 CRÍTICO: REVISA LOS VALORES REALES DEL JSON 🚨🚨🚨**

**ANTES de generar SQL para campos JSON, DEBES entender que:**
- Los valores en JSON pueden variar y NO siempre son los que esperas
- NO asumas valores. DEBES considerar TODOS los formatos posibles
- Los valores pueden estar abreviados, con guiones, o en diferentes formatos

**FORMATOS REALES DE metodo_pago que encontrarás en la base de datos:**
- `[{"tipo":"Efectivo","entrega":"17569.20"}]` - Efectivo (formato completo)
- `[{"tipo":"TD-","entrega":"5106.08"}]` - Tarjeta Débito (abreviado como TD-)
- `[{"tipo":"TC-","entrega":"76865.25"}]` - Tarjeta Crédito (abreviado como TC-)
- `[{"tipo":"TR--","entrega":"2373.72"}]` - Transferencia (abreviado como TR--)
- Puede haber otros formatos o variaciones (TD, TC, TR, etc.)

**REGLA CRÍTICA - MAPEO DE CONCEPTOS A VALORES JSON:**

Cuando el usuario pregunta por un método de pago, DEBES buscar TODOS los formatos posibles:

1. **"efectivo" o "en efectivo":**
   → Busca: "Efectivo" (exacto, con mayúscula)

2. **"tarjeta débito" o "débito" o "tarjeta de débito":**
   → Busca: "TD-", "Tarjeta Débito", "Débito", "TD" (todos los formatos posibles)

3. **"tarjeta crédito" o "crédito" o "tarjeta de crédito":**
   → Busca: "TC-", "Tarjeta Crédito", "Crédito", "TC" (todos los formatos posibles)

4. **"tarjeta" (sin especificar débito o crédito):**
   → Busca: "TD-", "TC-", "Tarjeta Débito", "Tarjeta Crédito", "TD", "TC" (AMBOS tipos)

5. **"transferencia" o "transferencia bancaria":**
   → Busca: "TR--", "Transferencia", "TR", "TR-" (todos los formatos posibles)

**EJEMPLOS CORRECTOS CON MÚLTIPLES FORMATOS:**

Usuario pregunta: "ventas en efectivo" o "total de ventas en efectivo"
❌ INCORRECTO: SELECT COUNT(*) FROM ventas WHERE metodo_pago = 'efectivo'
✅ CORRECTO: SELECT COUNT(*) FROM ventas WHERE JSON_CONTAINS(metodo_pago, '"Efectivo"', '$[*].tipo')

Usuario pregunta: "ventas con tarjeta débito" o "ventas pagadas con débito"
❌ INCORRECTO: SELECT COUNT(*) FROM ventas WHERE JSON_CONTAINS(metodo_pago, '"Tarjeta Débito"', '$[*].tipo')
✅ CORRECTO: SELECT COUNT(*) FROM ventas WHERE JSON_CONTAINS(metodo_pago, '"TD-"', '$[*].tipo') OR JSON_CONTAINS(metodo_pago, '"Tarjeta Débito"', '$[*].tipo') OR JSON_CONTAINS(metodo_pago, '"Débito"', '$[*].tipo') OR JSON_CONTAINS(metodo_pago, '"TD"', '$[*].tipo')

Usuario pregunta: "ventas con tarjeta crédito" o "ventas pagadas con crédito"
❌ INCORRECTO: SELECT COUNT(*) FROM ventas WHERE JSON_CONTAINS(metodo_pago, '"Tarjeta Crédito"', '$[*].tipo')
✅ CORRECTO: SELECT COUNT(*) FROM ventas WHERE JSON_CONTAINS(metodo_pago, '"TC-"', '$[*].tipo') OR JSON_CONTAINS(metodo_pago, '"Tarjeta Crédito"', '$[*].tipo') OR JSON_CONTAINS(metodo_pago, '"Crédito"', '$[*].tipo') OR JSON_CONTAINS(metodo_pago, '"TC"', '$[*].tipo')

Usuario pregunta: "ventas con tarjeta" (sin especificar débito o crédito)
❌ INCORRECTO: SELECT COUNT(*) FROM ventas WHERE JSON_CONTAINS(metodo_pago, '"Tarjeta"', '$[*].tipo')
✅ CORRECTO: SELECT COUNT(*) FROM ventas WHERE JSON_CONTAINS(metodo_pago, '"TD-"', '$[*].tipo') OR JSON_CONTAINS(metodo_pago, '"TC-"', '$[*].tipo') OR JSON_CONTAINS(metodo_pago, '"Tarjeta Débito"', '$[*].tipo') OR JSON_CONTAINS(metodo_pago, '"Tarjeta Crédito"', '$[*].tipo') OR JSON_CONTAINS(metodo_pago, '"TD"', '$[*].tipo') OR JSON_CONTAINS(metodo_pago, '"TC"', '$[*].tipo')

Usuario pregunta: "ventas con transferencia" o "ventas pagadas con transferencia"
❌ INCORRECTO: SELECT COUNT(*) FROM ventas WHERE JSON_CONTAINS(metodo_pago, '"Transferencia"', '$[*].tipo')
✅ CORRECTO: SELECT COUNT(*) FROM ventas WHERE JSON_CONTAINS(metodo_pago, '"TR--"', '$[*].tipo') OR JSON_CONTAINS(metodo_pago, '"Transferencia"', '$[*].tipo') OR JSON_CONTAINS(metodo_pago, '"TR"', '$[*].tipo') OR JSON_CONTAINS(metodo_pago, '"TR-"', '$[*].tipo')

**PASO ADICIONAL CRÍTICO EN EL CHECKLIST:**

□ PASO 8: Si estás consultando un campo JSON (especialmente metodo_pago):
   - NO asumas un solo formato del valor
   - Considera TODOS los formatos posibles (abreviados como TD-, TC-, TR--, completos, con guiones, etc.)
   - Si el usuario dice "efectivo" → busca "Efectivo" (exacto, con mayúscula)
   - Si el usuario dice "tarjeta débito" → busca "TD-", "Tarjeta Débito", "Débito", "TD"
   - Si el usuario dice "tarjeta crédito" → busca "TC-", "Tarjeta Crédito", "Crédito", "TC"
   - Si el usuario dice "tarjeta" (sin especificar) → busca TODOS los formatos de débito Y crédito
   - Si el usuario dice "transferencia" → busca "TR--", "Transferencia", "TR", "TR-"
   - SIEMPRE usa múltiples condiciones OR para cubrir todos los formatos posibles
   - NO uses un solo JSON_CONTAINS, usa múltiples con OR

**VERIFICACIÓN ADICIONAL:**

Antes de devolver el SQL, verifica:
6. ¿Estoy consultando metodo_pago? → Si SÍ, ¿estoy buscando TODOS los formatos posibles (abreviados y completos)? → Si NO, CORRIGE

"""
    
    # Encontrar donde insertar (antes de la sección JSON)
    json_section_start = system_message.find('**📍 MANEJO DE CAMPOS JSON (CRÍTICO - LEE ESTO PRIMERO):**')
    
    if json_section_start > 0:
        # Insertar la sección crítica justo antes de la sección JSON
        before_json = system_message[:json_section_start]
        json_section = system_message[json_section_start:]
        updated_message = before_json + critical_section + json_section
        return updated_message
    else:
        # Si no encuentra la sección JSON, agregar al final antes de SEGURIDAD
        seguridad_start = system_message.find('**SEGURIDAD:**')
        if seguridad_start > 0:
            before_seguridad = system_message[:seguridad_start]
            seguridad_section = system_message[seguridad_start:]
            return before_seguridad + critical_section + seguridad_section
    
    return system_message

def main():
    print("🔄 Actualizando systemMessage con valores reales del JSON...")
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
                
                # Actualizar con la sección crítica
                updated_message = update_system_message_with_real_values(system_message)
                
                # Agregar el prefijo "=" de nuevo
                options["systemMessage"] = "=" + updated_message
                updated = True
                print("✅ systemMessage actualizado con valores reales del JSON\n")
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
    print("   ✅ Sección crítica sobre valores reales del JSON agregada")
    print("   ✅ Formatos reales documentados (Efectivo, TD-, TC-, TR--)")
    print("   ✅ Ejemplos específicos con múltiples formatos")
    print("   ✅ PASO 8 agregado al checklist")
    print("\n💡 El modelo ahora buscará TODOS los formatos posibles de métodos de pago")

if __name__ == "__main__":
    main()
