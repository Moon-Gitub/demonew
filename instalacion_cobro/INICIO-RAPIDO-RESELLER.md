# ⚡ Inicio Rápido - Instalación en Reseller

Guía ultra-rápida para instalar el sistema de cobro en múltiples cuentas de hosting reseller.

---

## 🎯 PROCESO EN 5 PASOS

### PASO 1: Generar Lista de Clientes (5 minutos)

1. Sube `generar-mapeo-clientes.php` a cualquier dominio del servidor
2. Accede a: `https://tudominio.com/generar-mapeo-clientes.php`
3. Haz clic en **"📥 Descargar CSV"**
4. Abre el CSV y verifica que los usuarios sean correctos
5. Guarda como `clientes-a-instalar.csv`

**Resultado:** Archivo CSV con todos tus clientes y sus IDs.

---

### PASO 2: Elegir Método de Instalación

**Método A - cPanel Manual** ⭐ (Recomendado para 1-20 cuentas)
- ⏱️ Tiempo: 10-15 minutos por cuenta
- 🖱️ 100% visual (sin terminal)
- ✅ Más control
- ✅ Verificación inmediata
- ✅ **Guía:** [INSTALACION-CPANEL.md](INSTALACION-CPANEL.md)
- ✅ **Checklist:** [CHECKLIST-CPANEL.md](CHECKLIST-CPANEL.md)

**Método B - Script masivo** (Recomendado para 20+ cuentas)
- ⏱️ Tiempo: 2 minutos por cuenta
- 🔧 Requiere SSH/terminal
- ✅ Más rápido
- ⚠️ Requiere revisión posterior
- ✅ **Guía:** [INSTALACION-RESELLER.md](INSTALACION-RESELLER.md)

---

### PASO 3A: Instalación vía cPanel (Para 1-20 cuentas)

**Sigue la guía detallada:** [INSTALACION-CPANEL.md](INSTALACION-CPANEL.md)

**Resumen por cada cuenta:**

1. **Acceder:** WHM → List Accounts → Clic en cP (cPanel del cliente)
2. **File Manager:** Files → File Manager → public_html
3. **Subir 6 archivos:**
   - 2 controladores (sistema_cobro, mercadopago)
   - 3 modelos (sistema_cobro, mercadopago, conexion)
   - 2 vistas (cabezote-mejorado, procesar-pago)
4. **Editar cabezote-mejorado.php:**
   - Clic derecho → Edit
   - Línea 15: `$idCliente = 14;` (cambiar por ID real)
   - Save Changes
5. **Editar plantilla.php:**
   - Buscar: `include "modulos/cabezote.php";`
   - Cambiar a: `include "modulos/cabezote-mejorado.php";`
   - Save Changes
6. **Editar index.php:**
   - Verificar requires de sistema_cobro y mercadopago
   - Verificar ruta "procesar-pago"
7. **Probar:**
   - Acceder al sistema
   - Verificar ícono 🌙 y modal de pago

⏱️ **Tiempo:** 10-15 min/cuenta

**Usa el checklist:** [CHECKLIST-CPANEL.md](CHECKLIST-CPANEL.md)

---

### PASO 3B: Instalación Masiva (Para muchas cuentas)

```bash
# 1. Subir archivos al servidor principal
cd /home/tu_usuario
mkdir instalacion_cobro
# Subir todo el contenido de instalacion_cobro/

# 2. Editar script
nano script-instalacion-masiva.sh
# Cambiar RUTA_INSTALACION="/home/tu_usuario/instalacion_cobro"

# 3. Dar permisos
chmod +x script-instalacion-masiva.sh

# 4. Ejecutar
./script-instalacion-masiva.sh

# 5. Revisar resultado
```

**Resultado:** Sistema instalado en todas las cuentas automáticamente.

---

### PASO 4: Verificar Instalaciones

Por cada cuenta instalada:

```bash
https://dominio.com/testing/test-cliente-id.php
```

Debe mostrar:
- ✅ Cliente ID correcto
- ✅ Conexión a BD Moon exitosa
- ✅ Saldo correcto

---

### PASO 5: Probar Funcionamiento

1. Acceder al sistema del cliente
2. Verificar que aparece el ícono 🌙
3. Si tiene deuda, debe aparecer el modal de pago
4. Hacer una prueba de pago (con tarjeta de test)

---

## 📊 TIEMPO ESTIMADO

| Cuentas | Método Manual | Método Masivo |
|---------|---------------|---------------|
| 1-5     | 50 min        | 30 min        |
| 6-10    | 100 min       | 20 min        |
| 11-20   | 200 min       | 40 min        |
| 20+     | ---           | 60 min        |

---

## 🎯 CONFIGURACIONES ESPECÍFICAS POR CUENTA

### IMPORTANTE: ID del Cliente

Cada cuenta necesita su propio ID. Hay dos formas:

**Forma 1 - Hardcoded (Más simple):**
```php
// En cabezote-mejorado.php línea 15
$idCliente = 14; // ID específico de este cliente
```

**Forma 2 - Archivo .env (Más profesional):**
```bash
# Crear .env en cada cuenta
echo "MOON_CLIENTE_ID=14" > /home/usuario/public_html/.env
chmod 600 /home/usuario/public_html/.env
```

---

## 🗺️ MAPEO SUGERIDO

Mantén un archivo `MAPEO-CLIENTES.txt` con:

```
# Mapeo de dominios → IDs de clientes BD Moon
amarello.posmoon.com.ar = 14 (AMARELLO - Valentina Herrera)
demo.posmoon.com.ar = 7 (DEMO)
abisko.posmoon.com.ar = 2 (ABISKO)
adrimar.posmoon.com.ar = ? (Consultar en BD Moon)
anapozo.posmoon.com.ar = ? (Consultar en BD Moon)
# ...
```

---

## ⚠️ PROBLEMAS COMUNES

### Problema: "BD Moon no disponible"

**Causa:** La IP del servidor no está autorizada en el servidor de BD Moon  
**Solución:** Agregar IP `107.161.23.11` a las IPs permitidas en BD Moon

### Problema: "Cliente no encontrado"

**Causa:** ID del cliente incorrecto  
**Solución:** Verificar ID en BD Moon con `SELECT * FROM clientes WHERE dominio LIKE '%nombre%'`

### Problema: "Aparece 'al día' cuando tiene deuda"

**Causa:** El ID del cliente está mal configurado  
**Solución:** Usar `test-saldo-cliente.php` para verificar

---

## 📞 HERRAMIENTAS ÚTILES

Incluidas en `instalacion_cobro/`:

- ✅ `generar-mapeo-clientes.php` - Genera CSV automáticamente
- ✅ `script-instalacion-masiva.sh` - Instala en múltiples cuentas
- ✅ `verificador.php` - Verifica que todo funciona
- ✅ `testing/test-cliente-id.php` - Verifica ID del cliente
- ✅ `testing/test-saldo-cliente.php` - Verifica saldo y deuda

---

## 🎉 RESULTADO FINAL

Después de seguir esta guía tendrás:

✅ Sistema de cobro instalado en todas tus cuentas  
✅ Cada cuenta con su ID de cliente correcto  
✅ Modal de pago automático para clientes con deuda  
✅ Integración completa con MercadoPago  
✅ Recargos automáticos según día del mes  

---

**Tiempo total estimado:** 1-2 horas para 20+ cuentas  
**Dificultad:** Media  
**Conocimientos necesarios:** SSH básico, cPanel, PHP básico

