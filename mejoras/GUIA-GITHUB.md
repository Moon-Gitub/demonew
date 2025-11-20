# 📘 Guía Completa para Subir a GitHub

## 🎯 Pasos para Subir tu Proyecto a GitHub

---

## 📋 Antes de Empezar

### ✅ Verificar que tienes:
- [ ] Git instalado
- [ ] Cuenta de GitHub creada
- [ ] Archivo `.gitignore` creado (ya está listo)
- [ ] Archivo `conexion.example.php` creado (ya está listo)

---

## 🚀 Paso a Paso

### **Paso 1: Instalar Git (si no lo tienes)**

```bash
# Ubuntu/Debian
sudo apt update
sudo apt install git

# Verificar instalación
git --version
```

### **Paso 2: Configurar Git (Primera vez)**

```bash
# Configurar tu nombre
git config --global user.name "Tu Nombre"

# Configurar tu email (el de GitHub)
git config --global user.email "tu-email@ejemplo.com"

# Verificar configuración
git config --list
```

### **Paso 3: Ir a tu Proyecto**

```bash
cd /home/cluna/Documentos/Moon-Desarrollos/public_html
```

### **Paso 4: Inicializar Git (si no está inicializado)**

```bash
# Verificar si ya está inicializado
ls -la | grep .git

# Si no existe .git, inicializar
git init
```

### **Paso 5: Verificar Archivos que NO se Subirán**

```bash
# Ver qué archivos están ignorados
git status --ignored

# Deberías ver:
# - modelos/conexion.php (credenciales)
# - .env (si lo usas)
# - logs/
# - vistas/img/usuarios/* (fotos subidas)
```

### **Paso 6: Agregar Archivos al Staging**

```bash
# Agregar TODOS los archivos (respetando .gitignore)
git add .

# Ver qué se va a subir
git status
```

**⚠️ VERIFICAR que NO aparezcan:**
- ❌ `modelos/conexion.php`
- ❌ `.env`
- ❌ Archivos `.log`
- ❌ Carpeta `vendor/`

**✅ SÍ deben aparecer:**
- ✅ `modelos/conexion.example.php`
- ✅ `.gitignore`
- ✅ Todos los archivos de código
- ✅ Carpeta `mejoras/`

### **Paso 7: Hacer el Primer Commit**

```bash
# Crear commit con mensaje descriptivo
git commit -m "🎉 Initial commit: Sistema ERP/POS con mejoras de MercadoPago"
```

### **Paso 8: Crear Repositorio en GitHub**

1. **Ir a GitHub**: https://github.com
2. **Click en "New repository"** (botón verde)
3. **Configurar el repositorio:**
   ```
   Repository name: erp-pos-moon
   Description: Sistema ERP/POS con integración AFIP y MercadoPago
   Visibility: 🔒 Private (RECOMENDADO)
   ❌ NO marcar "Initialize with README" (ya lo tenemos)
   ```
4. **Click en "Create repository"**

### **Paso 9: Conectar con GitHub**

GitHub te mostrará comandos. Usar estos:

```bash
# Agregar el repositorio remoto
git remote add origin https://github.com/TU_USUARIO/erp-pos-moon.git

# Verificar que se agregó
git remote -v
```

### **Paso 10: Subir a GitHub**

```bash
# Cambiar a rama main (GitHub usa "main" ahora, no "master")
git branch -M main

# Subir todo a GitHub
git push -u origin main
```

**Si te pide autenticación:**

```bash
# GitHub ya no acepta contraseñas, necesitas un Personal Access Token

# 1. Ir a GitHub → Settings → Developer settings → Personal access tokens → Tokens (classic)
# 2. Generate new token (classic)
# 3. Seleccionar scopes: repo (todo)
# 4. Copiar el token (solo se muestra una vez)
# 5. Usarlo como contraseña cuando Git lo pida
```

### **Paso 11: Verificar en GitHub**

1. Ir a: `https://github.com/TU_USUARIO/erp-pos-moon`
2. Verificar que todos los archivos están ahí
3. Verificar que NO está `conexion.php` (solo `conexion.example.php`)

---

## 🔄 Comandos para Futuros Cambios

### Subir Nuevos Cambios

```bash
# Ver qué archivos cambiaron
git status

# Ver diferencias
git diff

# Agregar cambios
git add .

# O agregar archivos específicos
git add archivo1.php archivo2.php

# Commit con mensaje descriptivo
git commit -m "feat: agregar nueva funcionalidad de reportes"

# Subir a GitHub
git push
```

### Mensajes de Commit Recomendados

```bash
# Nueva funcionalidad
git commit -m "feat: agregar módulo de reportes"

# Corrección de bug
git commit -m "fix: corregir cálculo de totales en ventas"

# Mejora
git commit -m "refactor: optimizar consultas de productos"

# Documentación
git commit -m "docs: actualizar README con instrucciones"

# Diseño
git commit -m "style: mejorar diseño del modal de cobro"

# Seguridad
git commit -m "security: proteger endpoint de pagos"
```

---

## 🌿 Trabajar con Ramas (Recomendado)

### Crear Rama para Nueva Funcionalidad

```bash
# Crear y cambiar a nueva rama
git checkout -b feature/sistema-reportes

# Hacer cambios...
git add .
git commit -m "feat: agregar sistema de reportes"

# Subir rama a GitHub
git push -u origin feature/sistema-reportes

# Volver a main
git checkout main

# Mergear cambios
git merge feature/sistema-reportes

# Subir main actualizado
git push
```

### Estructura de Ramas Recomendada

```
main (producción estable)
├── develop (desarrollo)
│   ├── feature/nueva-funcionalidad
│   ├── feature/modulo-reportes
│   └── bugfix/correccion-ventas
└── hotfix/seguridad-critica
```

---

## 🔒 Seguridad en GitHub

### ⚠️ Si Subiste Credenciales por Error

**🚨 URGENTE - Hacer esto INMEDIATAMENTE:**

```bash
# 1. Eliminar archivo del historial
git filter-branch --force --index-filter \
  "git rm --cached --ignore-unmatch modelos/conexion.php" \
  --prune-empty --tag-name-filter cat -- --all

# 2. Forzar push (elimina en GitHub)
git push origin --force --all

# 3. CAMBIAR todas las credenciales en tu servidor
# - Cambiar contraseña de MySQL
# - Cambiar tokens de MercadoPago
# - Cambiar claves de AFIP
```

### 🛡️ Proteger el Repositorio

```bash
# Si el repo es privado, configurar quién puede acceder:
# GitHub → Settings → Manage access → Invite collaborators
```

---

## 📊 Ver Estado del Repositorio

### Comandos Útiles

```bash
# Ver estado actual
git status

# Ver historial de commits
git log --oneline --graph

# Ver diferencias
git diff

# Ver archivos rastreados
git ls-files

# Ver archivos ignorados
git status --ignored

# Ver ramas
git branch -a

# Ver remotos
git remote -v
```

---

## 🔄 Sincronizar con GitHub

### Bajar Cambios

```bash
# Si trabajas en múltiples lugares
git pull origin main
```

### Resolver Conflictos

```bash
# Si hay conflicto al hacer pull
git pull origin main

# Resolver manualmente los archivos
# Luego:
git add archivo-con-conflicto.php
git commit -m "fix: resolver conflicto en merge"
git push
```

---

## 📦 Clonar el Repositorio en Otro Lugar

```bash
# En otra computadora o servidor
git clone https://github.com/TU_USUARIO/erp-pos-moon.git

cd erp-pos-moon

# Instalar dependencias
composer install

# Copiar y configurar conexión
cp modelos/conexion.example.php modelos/conexion.php
nano modelos/conexion.php

# Configurar permisos
chmod -R 755 logs storage vistas/img/usuarios vistas/img/productos
```

---

## 🎯 Configuración Avanzada

### Crear Archivo .env.example

```bash
# Si usas .env, crear ejemplo sin credenciales
cp .env .env.example

# Editar .env.example y cambiar valores reales por placeholders
nano .env.example

# Agregar .env.example a Git
git add .env.example
git commit -m "docs: agregar .env.example"
git push
```

### Ignorar Archivos Adicionales

```bash
# Editar .gitignore
nano .gitignore

# Agregar líneas adicionales
# Guardar y commit
git add .gitignore
git commit -m "chore: actualizar .gitignore"
git push
```

---

## 🆘 Solución de Problemas

### "fatal: not a git repository"
```bash
git init
```

### "fatal: remote origin already exists"
```bash
git remote remove origin
git remote add origin URL_DE_TU_REPO
```

### "Permission denied (publickey)"
```bash
# Usar HTTPS en lugar de SSH
git remote set-url origin https://github.com/USUARIO/REPO.git
```

### "Updates were rejected"
```bash
# Forzar push (solo si estás seguro)
git push -f origin main
```

### Deshacer último commit (sin subir)
```bash
git reset --soft HEAD~1
```

### Deshacer cambios en archivo
```bash
git checkout -- archivo.php
```

---

## ✅ Checklist Final

Antes de subir a GitHub, verificar:

- [ ] `.gitignore` existe y está completo
- [ ] `conexion.php` NO se va a subir (está en .gitignore)
- [ ] `conexion.example.php` SÍ se va a subir
- [ ] README.md está actualizado
- [ ] No hay credenciales hardcodeadas en el código
- [ ] Archivos de logs no se suben
- [ ] Carpeta vendor/ no se sube (se regenera con composer)
- [ ] Documentación está completa
- [ ] Repositorio es PRIVADO (si contiene lógica de negocio)

---

## 🎉 ¡Listo!

Tu proyecto ahora está en GitHub y puedes:
- ✅ Hacer backup automático
- ✅ Colaborar con otros
- ✅ Ver historial de cambios
- ✅ Trabajar con ramas
- ✅ Hacer rollback si algo sale mal
- ✅ Clonar en otros servidores

---

## 📚 Recursos Útiles

- **GitHub Docs**: https://docs.github.com
- **Git Cheat Sheet**: https://education.github.com/git-cheat-sheet-education.pdf
- **Conventional Commits**: https://www.conventionalcommits.org/

---

**¿Problemas?** Revisa esta guía o consulta la documentación oficial de Git/GitHub.

