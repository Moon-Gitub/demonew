# ✅ Checklist de Instalación - Sistema de Cobro

Use este checklist para cada cuenta donde instale el sistema de cobro.

---

## 📋 PRE-INSTALACIÓN

- [ ] Tengo el **ID del cliente** en la BD Moon
- [ ] Tengo acceso a la cuenta (SSH o cPanel)
- [ ] Tengo backup de la cuenta (por seguridad)
- [ ] Sé cuál es la base de datos local de la cuenta
- [ ] El cliente tiene saldo en cuenta corriente (para probar)

**ID del Cliente:** ______  
**Dominio:** ______________________  
**Usuario cPanel:** ______________  

---

## 📦 ARCHIVOS A COPIAR/VERIFICAR

### Controladores
- [ ] `controladores/sistema_cobro.controlador.php`
- [ ] `controladores/mercadopago.controlador.php`

### Modelos
- [ ] `modelos/sistema_cobro.modelo.php`
- [ ] `modelos/mercadopago.modelo.php`
- [ ] `modelos/conexion.php` (con método `conectarMoon()`)

### Vistas
- [ ] `vistas/modulos/cabezote-mejorado.php`
- [ ] `vistas/modulos/procesar-pago.php`

### Opcional
- [ ] `helpers.php` (en la raíz)
- [ ] `.env` (si usas configuración por .env)

---

## ⚙️ CONFIGURACIÓN

### En `index.php`:
- [ ] Tiene `require_once "controladores/sistema_cobro.controlador.php";`
- [ ] Tiene `require_once "modelos/sistema_cobro.modelo.php";`
- [ ] Tiene `require_once "controladores/mercadopago.controlador.php";`
- [ ] Tiene `require_once "modelos/mercadopago.modelo.php";`
- [ ] Tiene ruta "procesar-pago" configurada

### En `vistas/plantilla.php`:
- [ ] Incluye `cabezote-mejorado.php` (línea ~161)
- [ ] NO incluye el cabezote viejo

### En `vistas/modulos/cabezote-mejorado.php`:
- [ ] Línea 15 tiene el **ID correcto del cliente**
- [ ] O usa `$_ENV['MOON_CLIENTE_ID']` si hay .env

### En `modelos/conexion.php`:
- [ ] Tiene método `conectarMoon()` que conecta a BD Moon
- [ ] Credenciales de BD Moon correctas

---

## 🧪 PRUEBAS

### Test 1: Conexión a BD Moon
- [ ] `test-conexion-directa.php` muestra ✅ Conexión exitosa

### Test 2: Cliente ID Correcto
- [ ] `testing/test-cliente-id.php` muestra el ID correcto
- [ ] `testing/test-saldo-cliente.php` muestra los datos del cliente

### Test 3: Saldo y Deuda
- [ ] `testing/test-saldo-cliente.php` muestra el saldo correcto
- [ ] Si hay deuda, dice "DEBE MOSTRAR MODAL"

### Test 4: Sistema Real
- [ ] Al acceder al sistema, aparece el ícono 🌙 en navbar
- [ ] Si hay deuda, aparece el modal de pago
- [ ] El modal muestra el monto correcto
- [ ] El botón de MercadoPago funciona

---

## 🔍 VERIFICACIÓN FINAL

### Revisión Visual:
- [ ] Ícono de luna (🌙) visible en navbar superior derecha
- [ ] Al hacer clic, muestra dropdown con información
- [ ] Si hay deuda > $0, el modal se abre automáticamente
- [ ] Modal muestra:
  - [ ] Nombre del cliente correcto
  - [ ] Desglose de cargos separados (Servicios vs Otros)
  - [ ] Recargo si aplica (según día del mes)
  - [ ] Total correcto
  - [ ] Botón de MercadoPago

### Revisión Técnica:
- [ ] No hay errores en logs de PHP
- [ ] No hay errores en consola del navegador
- [ ] El botón de MP redirige correctamente
- [ ] La URL de retorno es correcta

---

## 🎯 CONFIGURACIONES ESPECÍFICAS

### ID del Cliente Configurado: ______

### Método Usado:
- [ ] Hardcoded en cabezote-mejorado.php
- [ ] Archivo .env

### Observaciones:
```
_________________________________________________________________
_________________________________________________________________
_________________________________________________________________
```

---

## ✅ INSTALACIÓN COMPLETADA

Fecha: ___/___/_____  
Instalado por: __________________  
Tiempo total: ________ minutos  

**Estado:** 
- [ ] ✅ Funcionando correctamente
- [ ] ⚠️ Funcionando con observaciones
- [ ] ❌ Requiere revisión

---

## 📞 SOPORTE POST-INSTALACIÓN

Si algo no funciona:
1. Revisar logs: `/home/usuario/logs/error_log`
2. Ejecutar tests de diagnóstico
3. Consultar INSTALACION-RESELLER.md → Problemas Comunes
4. Contactar soporte técnico

---

**Próxima revisión:** ___/___/_____  
**Notas adicionales:** 
```
_________________________________________________________________
_________________________________________________________________
```

