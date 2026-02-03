# Notas de versión – POS Moon

## Versión 1.0

Resumen de mejoras y funcionalidades desde el inicio del proyecto.

---

### Login y seguridad

- **Login en un solo intento:** Se llama a `session_write_close()` antes del redirect tras un login correcto, para que la sesión se guarde antes de que el navegador siga a "inicio" y no sea necesario ingresar dos veces.
- **Protección contra fuerza bruta:** Límite de intentos fallidos por usuario (bloqueo temporal). Registro de intentos en sesión y mensajes de intentos restantes / tiempo de bloqueo.
- **Validación de usuario:** Regex para usuario (`/^[a-zA-Z0-9]+$/`). Uso correcto de `$raiz` dentro del método estático `ctrIngresoUsuario()` para el `require` de `permisos_rol.modelo.php`.
- **Contraseñas:** Compatibilidad con formato antiguo (crypt) y nuevo (password_hash). Migración automática al nuevo formato y rehash cuando corresponde.
- **Token CSRF:** Generación y uso de token CSRF en sesión para protección de formularios.
- **Pantalla de login:** Configuración dinámica desde base de datos (fondo, logo, color de botón, fuente, etc.) por empresa.

---

### Permisos por rol

- **Tablas `pantallas` y `permisos_rol`:** ABM de pantallas del sistema y asignación por rol (qué pantallas ve cada perfil).
- **Menú según permisos:** El menú solo muestra las pantallas permitidas para el rol del usuario logueado.
- **Pantallas por defecto:** Carga inicial de todas las pantallas (inicio, empresa, usuarios, productos, ventas, cajas, clientes, compras, proveedores, reportes, configuración, etc.) con agrupación y orden.
- **Roles iniciales:** Administrador (todas las pantallas) y Vendedor (conjunto reducido: inicio, productos, impresión precios, caja cajero, ventas, crear venta, clientes, chat).
- **Scripts SQL:** Creación y datos iniciales con ids explícitos; script de reset (`fix-permisos-rol-plan-b.sql`) sin depender de DROP/ADD de FK.

---

### Listas de precio

- **Tabla `listas_precio`:** ABM de listas por empresa (código, nombre, base de precio, tipo y valor de descuento, orden, activo).
- **Base de precio:** Uso de `precio_venta` o `precio_compra` del producto.
- **Descuento:** Ninguno o porcentaje sobre la base.
- **Datos iniciales:** Precio Público, Precio Costo, Trabajadores Valle Grande, Empleados (con ids 1–4).
- **Asignación por usuario:** Cada usuario puede tener listas de precio permitidas; el sistema aplica la configuración correspondiente en ventas y presupuestos.

---

### Medios de pago

- **Tabla `medios_pago`:** ABM de medios (código, nombre, descripción, flags: requiere_codigo, requiere_banco, requiere_numero, requiere_fecha, orden).
- **Datos iniciales:** Efectivo, Tarjeta Débito, Tarjeta Crédito, Cheque, Transferencia, Cuenta Corriente (ids 1–6).
- **Uso en ventas, presupuestos, cuenta corriente clientes/proveedores:** Los medios se leen desde la BD.

---

### Formatos de balanza

- **Tabla `balanzas_formatos`:** Configuración por empresa de códigos de balanza (prefijo, posiciones de producto y cantidad/peso, factor divisor, cantidad fija, etc.).
- **Modos:** Peso (substring y divisor), unidad (cantidad fija), ninguno.
- **Datos iniciales:** Ejemplos para prefijos 20000, 20 y 21 (ids 1–3).

---

### Sistema de cobro / Mercado Pago

- **Conexión a BD Moon:** El cabezote mejorado consulta la base central de Moon (clientes, cuenta corriente, saldos) para mostrar el widget de cobro.
- **Credenciales Mercado Pago:** Obtención desde `.env` o configuración por empresa (public_key, access_token).
- **Widget de pago:** Botón "Pagar con Mercado Pago" y flujo de pago; redirección a procesar-pago.
- **Webhook:** `webhook-mercadopago.php` para notificaciones de pago.
- **Fallback:** Si la conexión a BD Moon o las credenciales fallan, se muestra el cabezote normal y se registra "Sistema de cobro no disponible" en log.
- **Diagnóstico:** Script `extras/tests/diagnostico-sistema-cobro.php` para verificar credenciales, conexión Moon y tablas de pagos.

---

### Base de datos

- **Scripts unificados:** `db/reset-todas-tablas-con-datos.sql` crea/vacía e inserta con ids explícitos: `balanzas_formatos`, `listas_precio`, `medios_pago`, `pantallas`, `permisos_rol`. Sin DROP/ADD de FK para evitar errores por FK inexistente.
- **Scripts por módulo:** `crear-tabla-listas-precio.sql`, `crear-tabla-balanzas-formatos.sql`, `Nuevas/crear-tabla-medios-pago.sql`, `crear-tablas-permisos-rol.sql`, `tablas-con-datos-listas-medios-pantallas-permisos.sql`.
- **Corrección de FK y duplicados:** `fix-permisos-rol-fk.sql` y `fix-permisos-rol-plan-b.sql` para corregir errores 150/1062 en `permisos_rol` y `pantallas`.

---

### Despliegue y actualización en el VPS

- **Script `actualizar-desde-github.sh`:** Actualización desde GitHub por cuenta (HTTPS, rama main). Por defecto hace `git fetch`, `git reset --hard origin/main` y `git clean -fd` para dejar el código igual al repo. Opción `--merge` para intentar conservar cambios locales.
- **Documentación:** Pasos para configurar Git en cada cuenta (credential.helper store, usuario GitHub), proteger carpeta `facturacion/` (no pisar certificados/config), y comandos manuales (pull, reset, clean con exclusiones).
- **Clone desde cero:** Pasos para clonar el repo en un hosting nuevo, crear `.env`, cargar BD, copiar certificados/logo y permisos básicos.

---

### Otros

- **Variables de entorno:** Uso de `.env` (Dotenv) para configuración local (BD, BD Moon, Mercado Pago, etc.); `helpers.php` y `env()`.
- **Rutas y front controller:** Acceso por `index.php?ruta=...` y reescritura con `.htaccess` para URLs amigables.
- **Compatibilidad:** Múltiples empresas, múltiples usuarios por empresa, perfiles y permisos por pantalla.

---

*Documento generado como resumen de mejoras del sistema POS Moon – Versión 1.0.*
