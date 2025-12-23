# 📑 Índice de Archivos - Sistema Multi-Agente

## 🚀 Para Empezar Rápido

1. **Lee primero:** `RESUMEN-FINAL.md` (este te da una visión general)
2. **Importa el workflow:** `pos-moon-multi-agente.json` en n8n
3. **Sigue la instalación:** `INSTALACION.md`

## 📚 Documentación Completa

### 📖 Archivos Principales

| Archivo | Propósito | Cuándo Leerlo |
|---------|-----------|---------------|
| **RESUMEN-FINAL.md** | ✅ Visión general completa | **PRIMERO** - Para entender todo el sistema |
| **README.md** | Arquitectura y características | Para entender el diseño |
| **INSTALACION.md** | Pasos de instalación | Al instalar en n8n |
| **PASOS-CONSTRUCCION-N8N.md** | Construcción manual paso a paso | Si quieres construir desde cero |
| **GUIA-COMPLETA.md** | Guía exhaustiva con diagramas | Referencia completa |
| **COMUNICACION-ENTRE-AGENTES.md** | Cómo funciona la comunicación | Para entender el flujo de datos |

### 📝 Prompts y Configuración

| Archivo | Contenido |
|---------|-----------|
| **PROMPTS.md** | ✅ Todos los 7 prompts completos (Orquestador + 6 agentes) |
| **pos-moon-multi-agente.json** | ✅ Workflow completo listo para importar (62 nodos) |

### 🔧 Scripts y Herramientas

| Archivo | Propósito |
|---------|-----------|
| **generar_workflow_completo.py** | Script para generar/regenerar el workflow JSON |
| **generar_workflow.py** | Script base (no usar, usar el completo) |

### 📋 Notas Adicionales

| Archivo | Contenido |
|---------|-----------|
| **NOTAS-WORKFLOW.md** | Notas sobre estructura y construcción |

## 🎯 Guía Rápida por Tarea

### Quiero importar y usar el sistema:
1. Lee: `RESUMEN-FINAL.md`
2. Importa: `pos-moon-multi-agente.json` en n8n
3. Sigue: `INSTALACION.md`
4. Configura credenciales
5. ¡Prueba!

### Quiero entender cómo funciona:
1. Lee: `RESUMEN-FINAL.md`
2. Lee: `GUIA-COMPLETA.md`
3. Lee: `COMUNICACION-ENTRE-AGENTES.md`

### Quiero construir desde cero:
1. Lee: `PASOS-CONSTRUCCION-N8N.md`
2. Usa: `PROMPTS.md` para los prompts
3. Sigue paso a paso

### Quiero modificar los prompts:
1. Edita: `PROMPTS.md`
2. Copia el prompt a n8n en el nodo Agent correspondiente
3. O regenera el workflow con: `generar_workflow_completo.py`

### Quiero entender la arquitectura:
1. Lee: `README.md`
2. Lee: `GUIA-COMPLETA.md`
3. Revisa: Diagramas en `GUIA-COMPLETA.md`

### Tengo un problema:
1. Revisa: `INSTALACION.md` → Troubleshooting
2. Revisa: `COMUNICACION-ENTRE-AGENTES.md` → Verificación
3. Revisa logs en n8n

## 📦 Estructura del Workflow

### Componentes Principales:
- ✅ Orquestador completo
- ✅ 6 Sub-agentes especializados
- ✅ Todas las conexiones
- ✅ Todos los prompts
- ✅ Código JavaScript completo

### Estadísticas:
- 62 nodos totales
- 56 conexiones
- 7 prompts completos
- 5 esquemas parciales

## 🗺️ Mapa de Navegación

```
¿Qué necesitas hacer?
│
├─► Importar y usar
│   └─► RESUMEN-FINAL.md → INSTALACION.md → pos-moon-multi-agente.json
│
├─► Entender cómo funciona
│   ├─► RESUMEN-FINAL.md (overview)
│   ├─► GUIA-COMPLETA.md (detalles)
│   └─► COMUNICACION-ENTRE-AGENTES.md (flujo de datos)
│
├─► Construir manualmente
│   └─► PASOS-CONSTRUCCION-N8N.md + PROMPTS.md
│
├─► Modificar prompts
│   └─► PROMPTS.md → editar en n8n
│
├─► Resolver problemas
│   ├─► INSTALACION.md → Troubleshooting
│   └─► COMUNICACION-ENTRE-AGENTES.md → Verificación
│
└─► Entender arquitectura
    └─► README.md → GUIA-COMPLETA.md
```

## ✅ Archivos Esenciales

Si solo necesitas lo mínimo:

1. ✅ **pos-moon-multi-agente.json** - Workflow completo
2. ✅ **PROMPTS.md** - Prompts completos
3. ✅ **INSTALACION.md** - Instrucciones de setup

Con estos 3 archivos puedes usar el sistema completo.

## 📖 Lectura Recomendada

**Orden sugerido para entender todo:**

1. `RESUMEN-FINAL.md` (5 min) - Visión general
2. `README.md` (10 min) - Arquitectura
3. `GUIA-COMPLETA.md` (15 min) - Detalles completos
4. `COMUNICACION-ENTRE-AGENTES.md` (10 min) - Flujo de datos
5. `PROMPTS.md` (referencia) - Consulta según necesites

**Total: ~40 minutos para entender todo el sistema**

---

**¿Necesitas ayuda?** Empieza por `RESUMEN-FINAL.md` o `INSTALACION.md`
