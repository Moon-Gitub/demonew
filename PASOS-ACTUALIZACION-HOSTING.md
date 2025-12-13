# Pasos para Clonar y Actualizar el Repositorio en Diferentes Cuentas de Hosting

Este documento describe el proceso paso a paso para clonar y actualizar el repositorio desde GitHub en diferentes cuentas de hosting usando la terminal.

---

## 📋 Índice

1. [Requisitos Previos](#requisitos-previos)
2. [Primera Vez: Clonar el Repositorio](#primera-vez-clonar-el-repositorio)
3. [🔄 Clonar y Reemplazar Todo el Contenido](#clonar-y-reemplazar-todo-el-contenido) ⭐ **RECOMENDADO**
4. [Actualizar Cambios Existentes](#actualizar-cambios-existentes)
5. [Configuración de SSH (Opcional pero Recomendado)](#configuración-de-ssh-opcional-pero-recomendado)
6. [Comandos Rápidos de Referencia](#comandos-rápidos-de-referencia)
7. [Solución de Problemas Comunes](#solución-de-problemas-comunes)

---

## 🔧 Requisitos Previos

Antes de comenzar, asegúrate de tener:

- ✅ Acceso SSH a la cuenta de hosting
- ✅ Git instalado en el servidor (verificar con `git --version`)
- ✅ Credenciales de GitHub (usuario y token de acceso personal o SSH)
- ✅ Ruta donde se aloja la aplicación en el servidor

### Verificar Git en el Servidor

```bash
git --version
```

Si no está instalado, instálalo según tu sistema:
- **Ubuntu/Debian**: `sudo apt-get install git`
- **CentOS/RHEL**: `sudo yum install git`

---

## 🚀 Primera Vez: Clonar el Repositorio

### ⚠️ IMPORTANTE: Antes de Continuar

**Si ya tienes archivos en `public_html` y quieres reemplazarlos completamente con el contenido de GitHub**, sigue la sección **[Clonar y Reemplazar Todo el Contenido](#clonar-y-reemplazar-todo-el-contenido)** más abajo.

Si es la primera vez o quieres mantener los archivos existentes, continúa con los pasos siguientes.

---

### Opción 1: Clonar en Directorio Vacío o Nuevo

#### Paso 1: Conectarse al Servidor

```bash
ssh usuario@servidor.com
# Ejemplo: ssh usuario@hostinger.com
```

#### Paso 2: Navegar al Directorio de la Aplicación

```bash
# Ir al directorio donde está la aplicación (o donde quieres clonarla)
cd /home/usuario/public_html
# O según la estructura de tu hosting:
# cd /home/usuario/domains/tudominio.com/public_html
# cd /var/www/html
```

#### Paso 3: Clonar el Repositorio

**Opción A: Usando HTTPS (Requiere credenciales)**

```bash
git clone https://github.com/Moon-Gitub/demonew.git .
# El punto (.) al final clona directamente en el directorio actual
```

Si el directorio ya tiene archivos, usa un nombre de carpeta:

```bash
git clone https://github.com/Moon-Gitub/demonew.git demonew
cd demonew
```

**Opción B: Usando SSH (Recomendado - más seguro)**

```bash
git clone git@github.com:Moon-Gitub/demonew.git .
```

---

## 🔄 Clonar y Reemplazar Todo el Contenido

### ⚠️ ADVERTENCIA CRÍTICA

**Este proceso eliminará TODOS los archivos existentes en `public_html` y los reemplazará con el contenido de GitHub.**

**Antes de continuar:**
- ✅ Haz backup de tu base de datos
- ✅ Haz backup de archivos importantes (`.env`, configuraciones personalizadas, imágenes subidas, etc.)
- ✅ Guarda cualquier archivo que hayas modificado manualmente
- ✅ Verifica que tienes acceso a GitHub

### Paso 1: Conectarse al Servidor

```bash
ssh usuario@servidor.com
# Ejemplo: ssh usuario@hostinger.com
```

### Paso 2: Navegar a public_html

```bash
cd /home/usuario/public_html
# O según tu hosting:
# cd /home/usuario/domains/tudominio.com/public_html
```

### Paso 3: Hacer Backup de Archivos Importantes (Opcional pero Recomendado)

```bash
# Crear carpeta de backup
mkdir -p ~/backup_public_html_$(date +%Y%m%d_%H%M%S)

# Copiar archivos importantes antes de reemplazar
# Ejemplo: copiar .env si existe
if [ -f .env ]; then
    cp .env ~/backup_public_html_$(date +%Y%m%d_%H%M%S)/
    echo "✓ Backup de .env creado"
fi

# Copiar carpeta de uploads si existe
if [ -d "vistas/img/usuarios" ]; then
    cp -r vistas/img/usuarios ~/backup_public_html_$(date +%Y%m%d_%H%M%S)/
    echo "✓ Backup de imágenes creado"
fi

# Ver qué archivos/carpetas importantes tienes
ls -la
```

### Paso 4: Eliminar Todo el Contenido Actual

```bash
# Ver qué hay actualmente (para referencia)
ls -la

# Eliminar TODO excepto archivos ocultos importantes
# Opción 1: Eliminar todo excepto .htaccess y .env (si existen)
find . -maxdepth 1 ! -name '.' ! -name '..' ! -name '.htaccess' ! -name '.env' ! -name '.git' -exec rm -rf {} +

# Opción 2: Eliminar ABSOLUTAMENTE TODO (más agresivo)
# ⚠️ CUIDADO: Esto elimina incluso .htaccess y .env
# rm -rf * .[^.]*
```

**Recomendación**: Usa la Opción 1 para preservar `.htaccess` y `.env` si existen.

### Paso 5: Clonar el Repositorio desde GitHub

**Opción A: Usando HTTPS**

```bash
git clone https://github.com/Moon-Gitub/demonew.git .
```

**Opción B: Usando SSH**

```bash
git clone git@github.com:Moon-Gitub/demonew.git .
```

### Paso 6: Verificar que se Clonó Correctamente

```bash
# Verificar que los archivos están ahí
ls -la

# Verificar que es un repositorio Git
git status

# Ver la rama actual
git branch
```

### Paso 7: Restaurar Archivos de Configuración (Si hiciste backup)

```bash
# Si guardaste .env, restaurarlo
if [ -f ~/backup_public_html_*/.env ]; then
    cp ~/backup_public_html_*/.env .env
    echo "✓ .env restaurado"
fi

# Si guardaste imágenes, restaurarlas
if [ -d ~/backup_public_html_*/usuarios ]; then
    cp -r ~/backup_public_html_*/usuarios vistas/img/
    echo "✓ Imágenes restauradas"
fi
```

### Paso 8: Configurar Permisos (Si es necesario)

```bash
# Dar permisos adecuados
chmod -R 755 .
chmod -R 777 vistas/img/usuarios  # Si existe
chmod 644 .env  # Si existe
```

### Paso 9: Verificar que Todo Funciona

```bash
# Ver el último commit
git log -1

# Verificar estado
git status
```

---

### Script Completo: Clonar y Reemplazar Todo (Todo en Uno)

Puedes crear un script para automatizar todo el proceso:

```bash
nano ~/clonar-y-reemplazar.sh
```

**Contenido del script:**

```bash
#!/bin/bash

# Colores
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

# Ruta del proyecto
PROJECT_PATH="/home/usuario/public_html"
REPO_URL="https://github.com/Moon-Gitub/demonew.git"
# O usar SSH: REPO_URL="git@github.com:Moon-Gitub/demonew.git"

echo -e "${YELLOW}════════════════════════════════════════${NC}"
echo -e "${YELLOW}  CLONAR Y REEMPLAZAR CONTENIDO${NC}"
echo -e "${YELLOW}════════════════════════════════════════${NC}"
echo ""

# Confirmación
echo -e "${RED}⚠️  ADVERTENCIA: Esto eliminará TODO en $PROJECT_PATH${NC}"
read -p "¿Estás seguro? Escribe 'SI' para continuar: " confirmacion

if [ "$confirmacion" != "SI" ]; then
    echo -e "${RED}Operación cancelada${NC}"
    exit 1
fi

# Ir al directorio
cd $PROJECT_PATH || exit 1

# Crear backup de archivos importantes
echo -e "${YELLOW}Creando backup de archivos importantes...${NC}"
BACKUP_DIR=~/backup_public_html_$(date +%Y%m%d_%H%M%S)
mkdir -p $BACKUP_DIR

if [ -f .env ]; then
    cp .env $BACKUP_DIR/
    echo -e "${GREEN}✓ Backup de .env creado${NC}"
fi

if [ -d "vistas/img/usuarios" ]; then
    cp -r vistas/img/usuarios $BACKUP_DIR/ 2>/dev/null
    echo -e "${GREEN}✓ Backup de imágenes creado${NC}"
fi

# Eliminar contenido existente (excepto .htaccess, .env, .git)
echo -e "${YELLOW}Eliminando contenido existente...${NC}"
find . -maxdepth 1 ! -name '.' ! -name '..' ! -name '.htaccess' ! -name '.env' ! -name '.git' -exec rm -rf {} + 2>/dev/null
echo -e "${GREEN}✓ Contenido eliminado${NC}"

# Clonar repositorio
echo -e "${YELLOW}Clonando repositorio desde GitHub...${NC}"
if git clone $REPO_URL .; then
    echo -e "${GREEN}✓ Repositorio clonado exitosamente${NC}"
else
    echo -e "${RED}✗ Error al clonar el repositorio${NC}"
    exit 1
fi

# Restaurar archivos importantes
echo -e "${YELLOW}Restaurando archivos de configuración...${NC}"
if [ -f $BACKUP_DIR/.env ]; then
    cp $BACKUP_DIR/.env .env
    echo -e "${GREEN}✓ .env restaurado${NC}"
fi

if [ -d "$BACKUP_DIR/usuarios" ]; then
    mkdir -p vistas/img/
    cp -r $BACKUP_DIR/usuarios vistas/img/
    echo -e "${GREEN}✓ Imágenes restauradas${NC}"
fi

# Configurar permisos
echo -e "${YELLOW}Configurando permisos...${NC}"
chmod -R 755 . 2>/dev/null
if [ -d "vistas/img/usuarios" ]; then
    chmod -R 777 vistas/img/usuarios
fi
if [ -f .env ]; then
    chmod 644 .env
fi
echo -e "${GREEN}✓ Permisos configurados${NC}"

# Verificar
echo ""
echo -e "${GREEN}════════════════════════════════════════${NC}"
echo -e "${GREEN}  ✓ PROCESO COMPLETADO${NC}"
echo -e "${GREEN}════════════════════════════════════════${NC}"
echo ""
echo "Último commit:"
git log -1 --oneline
echo ""
echo "Backup guardado en: $BACKUP_DIR"
echo ""
```

**Hacer ejecutable:**

```bash
chmod +x ~/clonar-y-reemplazar.sh
```

**Usar el script:**

```bash
~/clonar-y-reemplazar.sh
```

### Paso 4: Configurar Git (Solo primera vez)

```bash
git config user.name "Tu Nombre"
git config user.email "tu-email@ejemplo.com"
```

### Paso 5: Verificar la Rama

```bash
git branch
# Deberías ver: * main
```

Si estás en otra rama, cambiar a main:

```bash
git checkout main
```

---

## 🔄 Actualizar Cambios Existentes

Si ya tienes el repositorio clonado y solo necesitas actualizar los cambios:

### Paso 1: Conectarse al Servidor

```bash
ssh usuario@servidor.com
```

### Paso 2: Ir al Directorio del Proyecto

```bash
cd /home/usuario/public_html
# O la ruta donde está tu proyecto
```

### Paso 3: Verificar Estado Actual

```bash
git status
```

Esto mostrará:
- Si hay cambios locales sin commitear
- Si estás sincronizado con el remoto
- En qué rama estás

### Paso 4: Guardar Cambios Locales (Si los hay)

**⚠️ IMPORTANTE**: Si tienes cambios locales que quieres conservar:

```bash
# Opción 1: Crear un commit con tus cambios locales
git add .
git commit -m "Cambios locales antes de actualizar"

# Opción 2: Guardar cambios en un stash (temporal)
git stash save "Cambios locales temporales"
```

**Si NO tienes cambios importantes y quieres descartarlos:**

```bash
git reset --hard
git clean -fd
```

### Paso 5: Obtener los Últimos Cambios

```bash
# Obtener información del remoto
git fetch origin

# Ver qué cambios hay
git log HEAD..origin/main
```

### Paso 6: Actualizar el Código

```bash
# Actualizar a la última versión de main
git pull origin main
```

Si hay conflictos, Git te lo indicará. Resuélvelos manualmente.

### Paso 7: Verificar la Actualización

```bash
# Ver el último commit
git log -1

# Ver el estado
git status
```

---

## 🔐 Configuración de SSH (Opcional pero Recomendado)

Usar SSH evita tener que ingresar credenciales cada vez.

### Paso 1: Generar Clave SSH (En tu Computadora Local)

```bash
ssh-keygen -t ed25519 -C "tu-email@ejemplo.com"
# Presiona Enter para usar la ubicación predeterminada
# Ingresa una contraseña (opcional pero recomendado)
```

### Paso 2: Copiar la Clave Pública

```bash
cat ~/.ssh/id_ed25519.pub
# Copia todo el contenido que aparece
```

### Paso 3: Agregar la Clave a GitHub

1. Ve a GitHub → Settings → SSH and GPG keys
2. Click en "New SSH key"
3. Pega la clave pública
4. Guarda

### Paso 4: Probar la Conexión

```bash
ssh -T git@github.com
# Deberías ver: Hi Moon-Gitub! You've successfully authenticated...
```

### Paso 5: Cambiar el Remoto a SSH (Si clonaste con HTTPS)

```bash
# Ver el remoto actual
git remote -v

# Cambiar a SSH
git remote set-url origin git@github.com:Moon-Gitub/demonew.git

# Verificar
git remote -v
```

---

## ⚡ Comandos Rápidos de Referencia

### Actualización Rápida (Todo en uno)

```bash
cd /ruta/al/proyecto && git fetch origin && git pull origin main
```

### Ver Últimos Cambios Sin Aplicar

```bash
git fetch origin
git log HEAD..origin/main --oneline
```

### Ver Diferencias

```bash
git diff origin/main
```

### Cambiar a un Commit Específico

```bash
# Ver commits
git log --oneline

# Cambiar a un commit específico
git checkout <hash-del-commit>
# Ejemplo: git checkout f59a4a1
```

### Volver a la Última Versión

```bash
git checkout main
git pull origin main
```

### Limpiar Archivos No Rastreados

```bash
git clean -fd
```

---

## 🔍 Solución de Problemas Comunes

### Problema 1: "Permission denied (publickey)"

**Solución**: Configura SSH o usa HTTPS con token de acceso personal.

```bash
# Verificar si tienes clave SSH
ls -la ~/.ssh

# Si no existe, generar una (ver sección SSH arriba)
```

### Problema 2: "Your local changes would be overwritten"

**Solución**: Guarda o descarta tus cambios locales.

```bash
# Opción 1: Guardar cambios
git stash
git pull origin main
git stash pop

# Opción 2: Descartar cambios (¡CUIDADO!)
git reset --hard
git pull origin main
```

### Problema 3: "Merge conflict"

**Solución**: Resuelve los conflictos manualmente.

```bash
# Ver archivos en conflicto
git status

# Abre los archivos y busca las marcas <<<<<<< ======= >>>>>>>
# Edita manualmente y luego:
git add .
git commit -m "Resuelto conflicto de merge"
```

### Problema 4: "Repository not found"

**Solución**: Verifica que tienes acceso al repositorio y la URL es correcta.

```bash
# Verificar remoto
git remote -v

# Si es incorrecto, cambiarlo
git remote set-url origin https://github.com/Moon-Gitub/demonew.git
```

### Problema 5: "Authentication failed"

**Solución**: Usa un token de acceso personal en lugar de contraseña.

1. GitHub → Settings → Developer settings → Personal access tokens
2. Genera un nuevo token con permisos de `repo`
3. Usa el token como contraseña cuando Git lo pida

### Problema 6: Git no está instalado en el servidor

**Solución**: Instalar Git según el sistema operativo.

```bash
# Ubuntu/Debian
sudo apt-get update
sudo apt-get install git

# CentOS/RHEL
sudo yum install git

# Verificar instalación
git --version
```

---

## 📝 Script de Actualización Automática

Puedes crear un script para automatizar el proceso:

### Crear el Script

```bash
nano ~/actualizar-proyecto.sh
```

### Contenido del Script

```bash
#!/bin/bash

# Colores para output
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Ruta del proyecto
PROJECT_PATH="/home/usuario/public_html"

echo -e "${YELLOW}Iniciando actualización del proyecto...${NC}"

# Ir al directorio del proyecto
cd $PROJECT_PATH

# Verificar que es un repositorio Git
if [ ! -d ".git" ]; then
    echo -e "${RED}Error: No es un repositorio Git${NC}"
    exit 1
fi

# Guardar cambios locales si existen
if ! git diff-index --quiet HEAD --; then
    echo -e "${YELLOW}Hay cambios locales. Guardando en stash...${NC}"
    git stash save "Cambios locales antes de actualizar - $(date)"
fi

# Obtener últimos cambios
echo -e "${YELLOW}Obteniendo últimos cambios de GitHub...${NC}"
git fetch origin

# Verificar si hay cambios
if [ $(git rev-list HEAD..origin/main --count) -eq 0 ]; then
    echo -e "${GREEN}Ya estás en la última versión${NC}"
    exit 0
fi

# Mostrar commits nuevos
echo -e "${YELLOW}Nuevos commits:${NC}"
git log HEAD..origin/main --oneline

# Actualizar
echo -e "${YELLOW}Actualizando código...${NC}"
if git pull origin main; then
    echo -e "${GREEN}✓ Actualización completada exitosamente${NC}"
    echo -e "${GREEN}Último commit: $(git log -1 --oneline)${NC}"
else
    echo -e "${RED}✗ Error al actualizar. Revisa los conflictos.${NC}"
    exit 1
fi
```

### Hacer el Script Ejecutable

```bash
chmod +x ~/actualizar-proyecto.sh
```

### Usar el Script

```bash
~/actualizar-proyecto.sh
```

---

## 🎯 Checklist de Actualización

Antes de actualizar en producción, verifica:

- [ ] Hacer backup de la base de datos
- [ ] Hacer backup de archivos importantes (`.env`, configuraciones)
- [ ] Verificar que no hay cambios locales importantes
- [ ] Revisar los commits nuevos: `git log HEAD..origin/main`
- [ ] Actualizar en un entorno de prueba primero (si es posible)
- [ ] Verificar permisos de archivos después de actualizar
- [ ] Limpiar caché si es necesario
- [ ] Probar funcionalidades críticas después de actualizar

---

## 📞 Comandos Útiles Adicionales

### Ver Historial de Commits

```bash
git log --oneline -10  # Últimos 10 commits
git log --graph --oneline --all  # Ver todas las ramas
```

### Ver Cambios en un Archivo Específico

```bash
git diff HEAD~1 HEAD -- ruta/al/archivo.php
```

### Ver Quién Hizo Cambios

```bash
git blame ruta/al/archivo.php
```

### Crear una Rama para Pruebas

```bash
git checkout -b testing
git pull origin main
# Hacer pruebas aquí
git checkout main
```

### Comparar Versiones

```bash
# Comparar con la versión remota
git diff main origin/main

# Comparar con un commit específico
git diff f59a4a1 HEAD
```

---

## 🔒 Seguridad

### Buenas Prácticas

1. **Nunca commitees archivos sensibles** (`.env`, passwords, etc.)
2. **Usa `.gitignore`** para excluir archivos sensibles
3. **Usa SSH** en lugar de HTTPS cuando sea posible
4. **Usa tokens de acceso personal** con permisos mínimos necesarios
5. **Haz backups** antes de actualizar en producción

### Verificar Archivos Sensibles

```bash
# Ver qué archivos están siendo rastreados
git ls-files

# Si encuentras archivos sensibles, agregarlos a .gitignore
echo "archivo-sensible.txt" >> .gitignore
git rm --cached archivo-sensible.txt
git commit -m "Remover archivo sensible"
```

---

## 📚 Recursos Adicionales

- [Documentación oficial de Git](https://git-scm.com/doc)
- [GitHub Docs](https://docs.github.com/)
- [Git Cheat Sheet](https://education.github.com/git-cheat-sheet-education.pdf)

---

## ✅ Resumen Rápido

### Opción 1: Clonar y Reemplazar Todo (Pisando contenido existente) ⭐

```bash
# 1. Conectarse al servidor
ssh usuario@servidor.com

# 2. Ir a public_html
cd /home/usuario/public_html

# 3. Hacer backup de archivos importantes (opcional)
mkdir -p ~/backup && cp .env vistas/img/usuarios ~/backup/ 2>/dev/null

# 4. Eliminar todo excepto .htaccess y .env
find . -maxdepth 1 ! -name '.' ! -name '..' ! -name '.htaccess' ! -name '.env' ! -name '.git' -exec rm -rf {} +

# 5. Clonar desde GitHub
git clone https://github.com/Moon-Gitub/demonew.git .

# 6. Restaurar archivos importantes si hiciste backup
cp ~/backup/.env .env 2>/dev/null
```

### Opción 2: Primera vez (directorio vacío)

```bash
cd /ruta/del/proyecto
git clone https://github.com/Moon-Gitub/demonew.git .
git config user.name "Tu Nombre"
git config user.email "tu-email@ejemplo.com"
```

### Opción 3: Actualizar cambios existentes

```bash
cd /ruta/del/proyecto
git fetch origin
git pull origin main
```

### Verificar estado:

```bash
git status
git log -1
```

---

**Última actualización**: 2024
**Repositorio**: https://github.com/Moon-Gitub/demonew
