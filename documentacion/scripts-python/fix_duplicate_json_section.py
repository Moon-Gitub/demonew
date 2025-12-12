#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Script para eliminar la sección duplicada de campos JSON en el systemMessage
"""

import json
import re

WORKFLOW_FILE = "/home/cluna/Documentos/7-Moon-Desarrollos/demonew/demonew/flujos-n8n/pos-moon-asistente-sql-dinamico.json"

def remove_duplicate_json_section(system_message):
    """Elimina la sección duplicada de campos JSON"""
    
    # Patrón para encontrar la sección completa de JSON
    # Buscamos desde "📍 MANEJO DE CAMPOS JSON" hasta antes de "**SEGURIDAD:**"
    pattern = r'(\*\*📍 MANEJO DE CAMPOS JSON.*?)(\*\*VERIFICACIÓN FINAL ADICIONAL:\*\*.*?5\. ¿Estoy usando algún campo de la lista de campos JSON\?.*?CORRIGE\n\n)'
    
    matches = list(re.finditer(pattern, system_message, re.DOTALL))
    
    if len(matches) > 1:
        print(f"Encontradas {len(matches)} secciones duplicadas")
        
        # Mantener solo la primera ocurrencia
        # Eliminar todas las ocurrencias excepto la primera
        first_match = matches[0]
        first_section = first_match.group(0)
        
        # Reemplazar todas las ocurrencias con la primera
        cleaned_message = system_message
        for i, match in enumerate(matches[1:], 1):
            # Reemplazar cada duplicado con una cadena vacía
            cleaned_message = cleaned_message.replace(match.group(0), "", 1)
            print(f"✓ Eliminada sección duplicada #{i+1}")
        
        # Verificar que solo quede una
        remaining = cleaned_message.count('**📍 MANEJO DE CAMPOS JSON (CRÍTICO - LEE ESTO PRIMERO):**')
        if remaining == 1:
            print(f"✅ Limpieza completada. Quedan {remaining} sección(es)")
            return cleaned_message
        else:
            print(f"⚠️  Aún quedan {remaining} secciones. Reintentando...")
            # Método alternativo: eliminar todo después de la primera ocurrencia hasta SEGURIDAD
            parts = cleaned_message.split('**📍 MANEJO DE CAMPOS JSON (CRÍTICO - LEE ESTO PRIMERO):**', 1)
            if len(parts) == 2:
                # Tomar la primera parte
                first_part = parts[0]
                # En la segunda parte, encontrar la primera ocurrencia completa hasta SEGURIDAD
                second_part = parts[1]
                # Buscar donde termina la primera sección (antes de SEGURIDAD o antes de otra sección JSON)
                json_end_pattern = r'(.*?\*\*VERIFICACIÓN FINAL ADICIONAL:\*\*.*?5\. ¿Estoy usando algún campo de la lista de campos JSON\?.*?CORRIGE\n\n)'
                json_match = re.search(json_end_pattern, second_part, re.DOTALL)
                if json_match:
                    json_section = json_match.group(1)
                    # Encontrar donde empieza SEGURIDAD
                    seguridad_start = second_part.find('\n\n**SEGURIDAD:**')
                    if seguridad_start > 0:
                        # Tomar solo hasta SEGURIDAD
                        rest = second_part[seguridad_start:]
                        return first_part + '**📍 MANEJO DE CAMPOS JSON (CRÍTICO - LEE ESTO PRIMERO):**' + json_section + rest
    
    return system_message

def main():
    print("🔍 Verificando y limpiando duplicados en systemMessage...")
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
                
                # Verificar duplicados
                count_before = system_message.count('**📍 MANEJO DE CAMPOS JSON (CRÍTICO - LEE ESTO PRIMERO):**')
                print(f"Secciones JSON antes: {count_before}")
                
                if count_before > 1:
                    print("\n🧹 Limpiando duplicados...")
                    cleaned_message = remove_duplicate_json_section(system_message)
                    
                    count_after = cleaned_message.count('**📍 MANEJO DE CAMPOS JSON (CRÍTICO - LEE ESTO PRIMERO):**')
                    print(f"Secciones JSON después: {count_after}")
                    
                    if count_after == 1:
                        # Agregar el prefijo "=" de nuevo
                        options["systemMessage"] = "=" + cleaned_message
                        updated = True
                        print("✅ systemMessage limpiado correctamente\n")
                    else:
                        print("⚠️  No se pudo limpiar completamente. Revisar manualmente.\n")
                else:
                    print("✅ No hay duplicados\n")
                break
    
    if updated:
        # Guardar el workflow actualizado
        print("💾 Guardando workflow actualizado...")
        with open(WORKFLOW_FILE, 'w', encoding='utf-8') as f:
            json.dump(workflow, f, indent=2, ensure_ascii=False)
        print("✅ Workflow guardado exitosamente")
    else:
        print("ℹ️  No se realizaron cambios")
    
    print("=" * 70)

if __name__ == "__main__":
    main()
