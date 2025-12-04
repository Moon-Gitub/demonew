# 📑 Índice de Instalación - Sistema de Cobro Moon POS

Carpeta completa para instalar el sistema de cobro en uno o múltiples sistemas.

---

## 📚 DOCUMENTACIÓN

### 🏁 Guías de Inicio
- **[README.md](README.md)** - Visión general del paquete
- **[INDICE.md](INDICE.md)** - Este archivo (índice completo)

### ⭐ Guía Recomendada para Reseller
- **[INSTALACION-CPANEL.md](INSTALACION-CPANEL.md)** ⭐ **EMPIEZA AQUÍ**
  - 100% vía cPanel (sin terminal)
  - Paso a paso con imágenes descriptivas
  - 10-15 min por cuenta
  - Ya probado y funcionando

- **[CHECKLIST-CPANEL.md](CHECKLIST-CPANEL.md)** - Checklist visual para seguimiento

### 📖 Guías Complementarias
- **[INICIO-RAPIDO-RESELLER.md](INICIO-RAPIDO-RESELLER.md)** - Resumen ejecutivo
- **[INSTALACION-RESELLER.md](INSTALACION-RESELLER.md)** - Instalación masiva (con script bash)
- **[INSTALACION_MANUAL.md](INSTALACION_MANUAL.md)** - Instalación técnica (con terminal)
- **[CHECKLIST-INSTALACION.md](CHECKLIST-INSTALACION.md)** - Checklist general

---

## 🛠️ HERRAMIENTAS

### 🗺️ Generación de Mapeo
- **[generar-mapeo-clientes.php](generar-mapeo-clientes.php)** - Genera CSV con todos los clientes desde BD Moon
- **[clientes-a-instalar.csv.example](clientes-a-instalar.csv.example)** - Ejemplo de archivo CSV

### 🚀 Scripts de Instalación
- **[script-instalacion-masiva.sh](script-instalacion-masiva.sh)** - Instala en múltiples cuentas automáticamente

### ✅ Verificación
- **[verificador.php](verificador.php)** - Verifica que todo esté instalado correctamente

---

## 📦 ARCHIVOS DE INSTALACIÓN

### Carpeta `archivos/`

Contiene todos los archivos necesarios para copiar al sistema del cliente:

```
archivos/
├── controladores/
│   └── mercadopago.controlador.php
├── modelos/
│   └── mercadopago.modelo.php
└── vistas/modulos/
    ├── cabezote-mejorado.php
    └── procesar-pago.php
```

**NOTA:** Los archivos `sistema_cobro.controlador.php` y `sistema_cobro.modelo.php` 
ya están en la carpeta `cobro/` del repositorio principal.

---

## 🗄️ SCRIPTS SQL

### Carpeta `sql/`

- **[01_crear_tablas_mercadopago.sql](sql/01_crear_tablas_mercadopago.sql)** - Crear tablas en BD Moon
- **[02_verificar_instalacion.sql](sql/02_verificar_instalacion.sql)** - Verificar instalación
- **[03_agregar_control_recargos.sql](sql/03_agregar_control_recargos.sql)** - Agregar control de recargos

**⚠️ IMPORTANTE:** Los scripts SQL se ejecutan UNA SOLA VEZ en la BD Moon (remota),
NO en cada cuenta del reseller.

---

## 🎯 FLUJO RECOMENDADO

### Para Instalación en Reseller (Múltiples Cuentas):

```
1. Leer: INICIO-RAPIDO-RESELLER.md
   ↓
2. Generar mapeo con: generar-mapeo-clientes.php
   ↓
3. Revisar/editar: clientes-a-instalar.csv
   ↓
4. Decidir: ¿Manual o Masiva?
   ↓
5a. Si Manual: Seguir INSTALACION-RESELLER.md → Instalación Manual
   ↓
5b. Si Masiva: Ejecutar script-instalacion-masiva.sh
   ↓
6. Verificar cada cuenta con verificador.php
   ↓
7. Probar en cada sistema
```

### Para Instalación Individual:

```
1. Leer: INSTALACION_MANUAL.md
   ↓
2. Seguir pasos 1-13
   ↓
3. Verificar con verificador.php
   ↓
4. Probar funcionamiento
```

---

## 📊 COMPARACIÓN DE MÉTODOS

| Aspecto | Manual Individual | Manual Reseller | Script Masivo |
|---------|-------------------|-----------------|---------------|
| **Cuentas** | 1 | 1-10 | 10+ |
| **Tiempo/cuenta** | 15 min | 10 min | 2 min |
| **Control** | ⭐⭐⭐⭐⭐ | ⭐⭐⭐⭐ | ⭐⭐⭐ |
| **Velocidad** | ⭐⭐ | ⭐⭐⭐ | ⭐⭐⭐⭐⭐ |
| **Complejidad** | Baja | Media | Media-Alta |

---

## ⚙️ REQUISITOS TÉCNICOS

### En el Servidor Reseller:
- ✅ Acceso SSH (para script masivo)
- ✅ Acceso WHM (para instalación manual)
- ✅ PHP 7.4+ en todas las cuentas
- ✅ Composer instalado en cada cuenta (o vendor compartido)

### En la BD Moon:
- ✅ Tablas de MercadoPago creadas (ejecutar SQL una sola vez)
- ✅ IPs del servidor reseller autorizadas
- ✅ Tabla `clientes` con todos los clientes registrados
- ✅ Columnas `estado_bloqueo` y `aplicar_recargos` agregadas

---

## 🆘 SOPORTE RÁPIDO

### Error: "BD Moon no disponible"
```bash
# Verificar IP autorizada
mysql -h 107.161.23.11 -u cobrosposmooncom_dbuser -p
# Si falla, la IP no está autorizada
```

### Error: "Cliente no encontrado"
```sql
-- Verificar que el cliente existe
SELECT id, nombre, dominio FROM clientes WHERE id = 14;
```

### Sistema dice "al día" cuando tiene deuda
```bash
# Usar herramienta de debug
https://dominio.com/testing/test-saldo-cliente.php
```

---

## 📞 CONTACTO Y DOCUMENTACIÓN ADICIONAL

- 📖 Documentación completa en `README.md`
- 🔧 Troubleshooting en `INSTALACION-RESELLER.md`
- 💬 Soporte: Moon Desarrollos

---

**Creado para:** Instalación en hosting reseller WHM/cPanel  
**Versión:** 2.0  
**Fecha:** Diciembre 2025  
**Autor:** Moon Desarrollos

