# 🌙 Sistema de Cobro Moon POS - Paquete de Instalación

Este paquete contiene todo lo necesario para instalar el sistema de cobro automático con MercadoPago en cualquier instalación del POS Moon.

## 📦 Contenido del paquete

```
instalacion_cobro/
├── README.md                           # Este archivo
├── INSTALACION_MANUAL.md              # Guía paso a paso manual
├── INSTALACION_AUTOMATICA.md          # Guía para usar el instalador
├── sql/
│   ├── 01_crear_tablas_mercadopago.sql      # Crear tablas en BD Moon
│   └── 02_verificar_instalacion.sql          # Verificar instalación
├── archivos/
│   ├── config.php                      # Configuración general
│   ├── .env.example                    # Ejemplo de variables de entorno
│   ├── controladores/
│   │   └── mercadopago.controlador.php  # Controlador de MercadoPago
│   ├── modelos/
│   │   └── mercadopago.modelo.php       # Modelo de MercadoPago
│   └── vistas/
│       └── modulos/
│           ├── cabezote-mejorado.php    # Cabezote con sistema de cobro
│           └── procesar-pago.php         # Procesar respuesta de MP
├── instalador/
│   └── index.php                        # Instalador automático (wizard)
├── verificador.php                      # Verificar que todo funciona
└── composer.json                        # Dependencias PHP
```

## 🚀 Métodos de Instalación

### Opción 1: Instalación Automática (Recomendado)

1. Sube la carpeta `instalacion_cobro/` al servidor
2. Accede a: `http://tudominio.com/instalacion_cobro/instalador/`
3. Sigue el wizard de instalación

Ver [INSTALACION_AUTOMATICA.md](INSTALACION_AUTOMATICA.md) para detalles.

### Opción 2: Instalación Manual

Si prefieres hacerlo manualmente o el instalador automático falla:

Ver [INSTALACION_MANUAL.md](INSTALACION_MANUAL.md) para instrucciones detalladas paso a paso.

## ⚙️ Requisitos Previos

### 1. Servidor
- ✅ PHP 7.4 o superior
- ✅ MySQL 5.7 o superior / MariaDB 10.3 o superior
- ✅ Apache/Nginx con mod_rewrite
- ✅ Composer instalado (para dependencias)

### 2. Base de Datos
- ✅ Acceso a la base de datos Moon (remota)
- ✅ Permisos para crear tablas
- ✅ Tablas existentes:
  - `clientes`
  - `clientes_cuenta_corriente`

### 3. Credenciales de MercadoPago
- ✅ Cuenta de MercadoPago (Argentina)
- ✅ Public Key y Access Token
- 📍 Obtener en: https://www.mercadopago.com.ar/developers/panel/app

### 4. Sistema POS Moon
- ✅ Versión compatible del POS Moon
- ✅ Estructura de archivos:
  ```
  /
  ├── controladores/
  ├── modelos/
  ├── vistas/
  │   └── modulos/
  ├── extensiones/
  │   └── vendor/
  └── index.php
  ```

## 📋 Checklist Pre-Instalación

Antes de comenzar, asegúrate de tener:

- [ ] Acceso FTP/SSH al servidor
- [ ] Credenciales de la base de datos Moon
- [ ] Credenciales de MercadoPago (Public Key + Access Token)
- [ ] ID del cliente en la tabla `clientes` de la BD Moon
- [ ] Backup completo del sistema (por seguridad)

## 🔍 Verificación Post-Instalación

Después de instalar, verifica:

1. **Base de Datos:**
   ```bash
   # Ejecuta en phpMyAdmin o consola MySQL:
   source sql/02_verificar_instalacion.sql
   ```

2. **Archivos:**
   ```bash
   # Accede a:
   http://tudominio.com/verificador.php
   ```

3. **Funcionalidad:**
   - Inicia sesión en el POS
   - Verifica que aparezca el ícono de la luna en la navbar
   - Haz clic y verifica que se abra el modal de cobro
   - Revisa que muestre el desglose correcto de cargos

## 🎯 Características del Sistema

### Sistema de Cobro Automático
- ✅ Modal automático según día del mes
- ✅ Desglose detallado de cargos pendientes
- ✅ Separación: Servicios Mensuales vs Otros Cargos
- ✅ Recargos selectivos (solo servicios mensuales)
- ✅ **Control por cliente de aplicación de recargos**
- ✅ Integración completa con MercadoPago
- ✅ Bloqueo del sistema después del día 26

### Recargos por Mora
| Días | Recargo | Modal | Estado |
|------|---------|-------|--------|
| 1-4  | 0%      | Puede cerrar | Normal |
| 5-9  | 0%      | Puede cerrar | Advertencia |
| 10-14| 10%     | Puede cerrar | Mora 1 |
| 15-19| 15%     | Puede cerrar | Mora 2 |
| 20-24| 20%     | Puede cerrar | Mora 3 |
| 25-26| 30%     | Puede cerrar | Mora Máxima |
| 27+  | 30%     | **NO puede cerrar** | **BLOQUEADO** |

**IMPORTANTE:**
- Los recargos se aplican **SOLO sobre servicios mensuales POS**, no sobre otros cargos como trabajos extras o renovaciones.
- Cada cliente puede ser configurado individualmente para aplicar o no recargos mediante el campo `aplicar_recargos` en la tabla `clientes`.
- Por defecto, todos los clientes tienen recargos habilitados (valor = 1).

### Control de Recargos por Cliente

El sistema permite controlar si un cliente debe tener recargos por mora o no:

```sql
-- Para EXIMIR a un cliente de recargos:
UPDATE clientes SET aplicar_recargos = 0 WHERE id = [id_del_cliente];

-- Para APLICAR recargos nuevamente:
UPDATE clientes SET aplicar_recargos = 1 WHERE id = [id_del_cliente];

-- Ver estado actual:
SELECT id, nombre, aplicar_recargos FROM clientes WHERE id = [id_del_cliente];
```

**Casos de uso:**
- Clientes VIP o con contrato especial: exentos de recargos
- Clientes en período de prueba: sin recargos
- Acuerdos comerciales especiales: sin recargos por X tiempo

## 📚 Documentación Adicional

- [INSTALACION_MANUAL.md](INSTALACION_MANUAL.md) - Guía paso a paso manual
- [INSTALACION_AUTOMATICA.md](INSTALACION_AUTOMATICA.md) - Usar el instalador automático
- [FAQ.md](FAQ.md) - Preguntas frecuentes
- [TROUBLESHOOTING.md](TROUBLESHOOTING.md) - Solución de problemas comunes

## 🆘 Soporte

Si encuentras problemas durante la instalación:

1. Revisa [TROUBLESHOOTING.md](TROUBLESHOOTING.md)
2. Verifica los logs de errores PHP
3. Consulta la documentación de MercadoPago
4. Contacta a soporte técnico

## ⚠️ Advertencias de Seguridad

1. **NUNCA** subas el archivo `.env` a Git
2. **SIEMPRE** haz backup antes de instalar
3. **USA** credenciales de TEST para pruebas
4. **CAMBIA** a credenciales de PRODUCCIÓN solo cuando esté probado
5. **PROTEGE** la carpeta `instalador/` después de instalar

## 📄 Licencia

Sistema propietario de Moon Desarrollos.

---

**Versión:** 1.0
**Fecha:** Diciembre 2025
**Desarrollado para:** Sistemas POS Moon
