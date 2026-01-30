# Wiki del Sistema POS Moon

Esta carpeta contiene la **documentación para el usuario final** del Sistema POS Moon, en formato compatible con **GitHub Wiki**.

## Contenido

- **Home.md** — Página principal e índice general.
- **_Sidebar.md** — Barra lateral de navegación (usada por GitHub Wiki si está habilitado).
- Páginas por tema: Inicio de sesión, Empresa, Productos, Movimientos, Cajas, Ventas, Clientes, Compras, Proveedores, Integraciones y cobro, Reportes, Glosario y soporte.

## Uso con GitHub Wiki

1. En el repositorio de GitHub, active **Wiki** en la pestaña del proyecto (Settings → Features → Wiki).
2. Puede copiar el contenido de esta carpeta `wiki/` al Wiki del repositorio:
   - **Opción A:** crear cada página en el Wiki manualmente y pegar el contenido de cada `.md`.
   - **Opción B:** si usa una herramienta o script para sincronizar la carpeta `wiki/` con el Wiki, configure la ruta a esta carpeta.
3. **Home.md** debe ser la página de inicio del Wiki (en GitHub Wiki la página principal se llama "Home").
4. **_Sidebar.md** define la barra lateral; GitHub Wiki usa una página llamada "_Sidebar" para mostrarla.

## Uso sin GitHub Wiki

Puede usar estos archivos como documentación en Markdown en cualquier visor (GitHub, GitLab, VS Code, etc.): abra **Home.md** como punto de entrada y siga los enlaces entre páginas. Los enlaces están escritos con la forma `[Texto](Nombre-pagina)`; en GitHub Wiki las páginas se nombran sin extensión, por eso los enlaces no llevan `.md`.

## Mantenimiento

- Actualice las páginas cuando cambie el menú, perfiles o flujos del sistema.
- Si agrega nuevas pantallas o módulos, considere añadir una sección en la página correspondiente y un enlace en Home.md y _Sidebar.md.
