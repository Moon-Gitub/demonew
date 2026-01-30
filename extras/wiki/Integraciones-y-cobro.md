# Integraciones y cobro

En esta sección se describe la **integración con Mercado Pago**, el **modal de cobro** que aparece en el cabezote, la **gestión de integraciones** y el **Asistente Virtual**. Es información útil tanto para el usuario final como para entender el comportamiento del sistema de cobro.

---

## Cabezote y sistema de cobro

El sistema usa un **cabezote** (barra superior) que puede incluir o no el **sistema de cobro** con Mercado Pago:

- **Cabezote principal (activo):** `cabezote-mejorado.php` — incluye el sistema de cobro con Mercado Pago, modal de pago e información de cuenta.
- **Cabezote de respaldo:** si el sistema de cobro falla (por ejemplo, base de datos o servicio externo no disponible), el sistema carga automáticamente un cabezote básico sin cobro para que pueda seguir usando el resto del sistema.

El usuario no elige manualmente qué cabezote ver; el sistema lo decide según si el módulo de cobro está operativo.

---

## Configuración de Mercado Pago

Para que el **cobro con Mercado Pago** funcione, el Administrador debe configurar las credenciales de Mercado Pago en el sistema:

- **Dónde:** normalmente en **Empresa → Datos Empresa** o en **Integraciones → Gestionar Integraciones**, en la sección “Configuración de Mercado Pago” o similar.
- **Qué se configura:** Access Token (o credenciales que indique la documentación de Mercado Pago) y, si aplica, identificador de preferencia o webhook.
- **Quién lo hace:** solo el Administrador.

Sin esta configuración, el botón “Pagar con Mercado Pago” no podrá generar enlaces de pago correctos.

---

## Modal de cobro (recordatorio de pago)

En el cabezote puede aparecer un **modal de cobro** que recuerda al usuario (empresa/cliente del sistema) que debe abonar el servicio mensual. Ese modal está vinculado al **sistema de cobro** de Moon (abono del software), no a las ventas del negocio.

### Comportamiento general

- **Aparición:** el modal puede mostrarse automáticamente al cargar la página, según el día del mes y el estado de la cuenta (al día, con recargo, vencida).
- **Límite de apariciones:** para no molestar, el modal se muestra como máximo **3 veces por sesión de navegador** (por login). Si cierra el navegador y vuelve a entrar, se considera una nueva sesión.
- **Cierre:** según el estado de la cuenta, el modal puede ser cerrable (solo recordatorio) o **fijo** (no se puede cerrar hasta regularizar).

### Cuándo el modal es “normal” (se puede cerrar)

- Días **5 a 9:** recordatorio de abono mensual.
- Días **10 a 21:** recordatorio con 10 % de interés.
- Días **21 a 26:** advertencia con 15 % de interés y días restantes.
- Días **26 a 28:** el modal sigue siendo cerrable pero puede mostrarse una **advertencia en rojo** indicando que el vencimiento está muy cerca.

### Cuándo el modal es “fijo” (no se puede cerrar)

- **Día del mes mayor a 28:** el sistema considera la cuenta vencida y el modal se muestra **fijo**: no tiene botón de cerrar, no se cierra con ESC ni haciendo clic fuera. El objetivo es que se regularice el pago antes de seguir usando el sistema con normalidad.

### Cómo se registra el pago del abono

El modal indica claramente que **solo se computa el pago cuando el usuario hace clic en el botón “Pagar con Mercado Pago”** y completa el pago dentro de la plataforma de Mercado Pago. **No** se consideran válidos para el abono:

- Transferencias bancarias hechas por su cuenta sin usar el botón.
- Pagos en efectivo u otros medios que no pasen por el flujo de Mercado Pago iniciado desde el modal.

Una vez que hace clic en el botón, puede elegir dentro de Mercado Pago el método que prefiera (tarjeta, transferencia desde Mercado Pago, efectivo en puntos de pago, etc.); lo importante es que el pago se inicie desde ese botón para que quede registrado en el sistema de cobro.

### Resumen para el usuario final

- Si ve el modal de cobro: es un recordatorio o aviso del abono del sistema.
- Para pagar correctamente: use **siempre** el botón **“Pagar con Mercado Pago”** y complete el pago en la pantalla que se abre.
- Si el modal no se cierra (día > 28): debe regularizar el pago para poder cerrar el modal y usar el sistema con normalidad.
- El modal aparece como máximo 3 veces por sesión para no interrumpir demasiado.

---

## Gestionar Integraciones

**Ruta:** Integraciones → Gestionar Integraciones  
**Perfil:** solo Administrador

En esta pantalla se **configuran las integraciones** del sistema con servicios externos, por ejemplo:

- **Mercado Pago:** credenciales, webhook (si aplica).
- Otras APIs o servicios que el sistema soporte (envío de facturas, notificaciones, etc.).

Cada integración suele tener sus propios campos (tokens, URLs, claves). La documentación técnica o el soporte indicarán qué completar en cada caso.

---

## Asistente Virtual (Chat)

**Ruta:** Integraciones → Asistente Virtual (o menú principal → Asistente Virtual)  
**Perfil:** Administrador y Vendedor

El **Asistente Virtual** es un chat integrado en el sistema que permite:

- Hacer preguntas sobre el uso del sistema.
- Obtener ayuda para tareas habituales (cómo crear una venta, cómo cargar un cliente, etc.).
- Resolver dudas rápidas sin salir del POS.

El asistente puede estar conectado a un servicio externo (bot, IA, soporte) según la configuración del proyecto. No reemplaza al soporte humano en casos complejos, pero sirve como primera ayuda para el usuario final.

---

## Resumen

| Tema | Descripción |
|------|-------------|
| Cabezote | Barra superior; si el módulo de cobro funciona, incluye Mercado Pago y modal; si falla, se usa cabezote básico. |
| Configuración Mercado Pago | Credenciales en Empresa o Integraciones; necesario para que el botón de pago funcione. |
| Modal de cobro | Recordatorio de abono del sistema; máximo 3 veces por sesión; fijo si día > 28; solo el botón “Pagar con Mercado Pago” computa el pago. |
| Gestionar Integraciones | Configurar Mercado Pago y otras integraciones (solo Admin). |
| Asistente Virtual | Chat de ayuda dentro del sistema. |

Para **crear ventas** y cobrar a clientes en el negocio, ver [Ventas](Ventas) y [Cajas](Cajas). Para **medios de pago** en ventas y caja, ver [Empresa – Cargar Medios de Pago](Empresa#cargar-medios-de-pago).
