# Scripts Python

Esta carpeta contiene todos los scripts Python del proyecto POS Moon.

## Scripts de Workflows n8n

### Scripts de Generación de Workflows

- **generar_workflow_completo.py**: Genera el workflow JSON completo del sistema multi-agente con todos los nodos, conexiones y prompts
- **generar_workflow.py**: Genera workflows básicos para n8n
- **generar_orquestador_corregido.py**: Genera el workflow del orquestador principal corregido
- **generar_orquestador_toolworkflow.py**: Genera el workflow del orquestador usando toolWorkflow
- **generar_subagentes_corregidos.py**: Genera los workflows de los sub-agentes especializados corregidos
- **generar_nueva_estructura.py**: Genera una nueva estructura de workflows

## Scripts de Mantenimiento y Corrección

### Scripts de Corrección de JSON

- **fix_json_search.py**: Corrige problemas de búsqueda en archivos JSON de workflows
- **fix_duplicate_json_section.py**: Elimina secciones duplicadas en archivos JSON
- **update_json_fields_workflow.py**: Actualiza campos específicos en workflows JSON
- **update_json_real_values.py**: Actualiza valores reales en archivos JSON de workflows

### Scripts de Análisis

- **add_sum_examples.py**: Agrega ejemplos de consultas SUM a los prompts
- **check_ventas.py**: Verifica y analiza datos de ventas en la base de datos

## Uso

Todos los scripts están diseñados para trabajar con los workflows de n8n ubicados en `flujos-n8n/`. 

**Nota**: Algunos scripts pueden requerir ajustes de rutas si se ejecutan desde esta nueva ubicación.

## Requisitos

- Python 3.x
- Acceso a los archivos JSON de workflows en `../flujos-n8n/`
