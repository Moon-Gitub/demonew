# Análisis: Sistema de Intentos de Pago Mercado Pago

## Problemas Identificados

### 1. **Intentos Antiguos Nunca Se Limpian**
- Los intentos de hace un mes siguen en estado "pendiente"
- No hay lógica para marcar como "expirado" o "cancelado" los intentos antiguos
- La verificación de duplicados solo busca en los últimos 60 minutos
- Si hay un intento de hace un mes, no se detecta como duplicado

### 2. **Verificación de Duplicados Insuficiente**
- Solo verifica intentos de los últimos 60 minutos
- Si un intento tiene más de 60 minutos, se puede crear otro duplicado
- No considera intentos antiguos pendientes del mismo cliente

### 3. **Los Intentos No Se Usan Para QR Dinámico**
- Para QR dinámico (venta-caja.js) NO se crean intentos
- Solo se crea la order en Mercado Pago
- Los intentos solo se usan para el sistema de cobro (cabezote-mejorado.php)

### 4. **Falta Limpieza Automática**
- No hay función que limpie intentos expirados
- Los intentos antiguos se acumulan indefinidamente
- Esto causa confusión y posibles duplicados

## Solución Propuesta

### 1. **Limpiar Intentos Antiguos Automáticamente**
- Marcar como "expirado" los intentos pendientes con más de 24 horas
- Ejecutar limpieza automática al crear nuevos intentos
- Agregar función de limpieza manual

### 2. **Mejorar Verificación de Duplicados**
- Verificar TODOS los intentos pendientes del cliente, no solo los de 60 minutos
- Si hay un intento pendiente (aunque sea antiguo), no crear uno nuevo
- Marcar el intento antiguo como expirado antes de crear uno nuevo

### 3. **Simplificar Lógica**
- Los intentos solo son necesarios para el sistema de cobro (preferencias)
- Para QR dinámico, no crear intentos (o crear uno temporal que se limpie rápido)
- Clarificar el propósito de los intentos

## Cambios a Implementar

1. Función para limpiar intentos expirados (más de 24 horas)
2. Mejorar verificación de duplicados (considerar todos los pendientes)
3. Marcar intentos antiguos como expirados antes de crear nuevos
4. Agregar limpieza automática al registrar nuevos intentos
