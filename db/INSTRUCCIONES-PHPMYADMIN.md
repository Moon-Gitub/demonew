# 📋 INSTRUCCIONES: Ejecutar SQL en phpMyAdmin

## 🎯 IMPORTANTE: Este SQL va en la BD MOON (NO en la local)

---

## 📝 PASOS DETALLADOS

### 1. Abrir phpMyAdmin

```
URL: http://107.161.23.241/phpmyadmin
O la URL que uses para acceder a phpMyAdmin
```

### 2. Iniciar sesión

```
Servidor: 107.161.23.241
Usuario: moondesa_moon
Contraseña: F!b+hn#i3Vk-
```

### 3. Seleccionar la base de datos

- En el panel izquierdo, hacer clic en: **`moondesa_moon`**
- Debe aparecer resaltada en azul

### 4. Ir a la pestaña SQL

- En el menú superior, hacer clic en la pestaña **`SQL`**

### 5. Copiar el SQL

- Abrir el archivo: **`db/EJECUTAR-EN-MOONDESA_MOON.sql`**
- Seleccionar TODO el contenido (Ctrl+A)
- Copiar (Ctrl+C)

### 6. Pegar en phpMyAdmin

- Hacer clic en el área de texto grande
- Pegar el SQL (Ctrl+V)

### 7. Ejecutar

- Hacer clic en el botón **"Continuar"** o **"Go"** (abajo a la derecha)

### 8. Verificar resultado

Deberías ver un mensaje verde que dice:
```
✅ 3 tablas creadas
✅ 2 vistas creadas
✅ Query ejecutado exitosamente
```

---

## 🔍 VERIFICACIÓN

### Ver las tablas creadas:

En la pestaña **"Estructura"** de la BD `moondesa_moon`, deberías ver:

```
mercadopago_intentos
mercadopago_pagos
mercadopago_webhooks
```

### Ver las vistas creadas:

En la misma lista, con icono diferente:

```
v_mercadopago_resumen
v_mercadopago_pendientes
```

---

## 🚨 SI ALGO SALE MAL

### Error: "Table already exists"

**No es un error**, significa que las tablas ya fueron creadas antes.

El script usa `CREATE TABLE IF NOT EXISTS`, así que es seguro ejecutarlo varias veces.

### Error: "Access denied"

Verifica que estés usando:
- Servidor: **107.161.23.241**
- Usuario: **moondesa_moon**
- Contraseña correcta

### Error: "Database not found"

Asegúrate de seleccionar la base de datos **`moondesa_moon`** antes de ejecutar el SQL.

---

## ✅ CHECKLIST RÁPIDO

- [ ] Abrir phpMyAdmin
- [ ] Conectar a 107.161.23.241
- [ ] Seleccionar BD: moondesa_moon
- [ ] Ir a pestaña SQL
- [ ] Copiar archivo: db/EJECUTAR-EN-MOONDESA_MOON.sql
- [ ] Pegar en phpMyAdmin
- [ ] Hacer clic en "Continuar"
- [ ] Verificar mensaje de éxito ✅
- [ ] Verificar que aparecen las 3 tablas
- [ ] Verificar que aparecen las 2 vistas

---

## 📸 CAPTURAS DE REFERENCIA

### Paso 1: Seleccionar BD
```
[Panel izquierdo]
→ moondesa_moon  ← Hacer clic aquí
```

### Paso 2: Pestaña SQL
```
[Menú superior]
Estructura | SQL | Buscar | ...
           ↑
    Hacer clic aquí
```

### Paso 3: Área de texto
```
┌────────────────────────────────┐
│ -- Pegar el SQL aquí           │
│                                │
│                                │
└────────────────────────────────┘
        [Continuar]  ← Clic aquí
```

---

## 🎉 LISTO

Una vez que veas el mensaje de éxito, las tablas están creadas y el sistema de MercadoPago puede empezar a funcionar.

**Próximo paso:** Configurar el webhook en el panel de MercadoPago.

---

**Archivo SQL:** `db/EJECUTAR-EN-MOONDESA_MOON.sql`
**Fecha:** 20 Noviembre 2025
