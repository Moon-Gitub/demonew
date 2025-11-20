# 📘 Comandos Git - Referencia Rápida

## ✅ Tu Repositorio GitHub

**URL:** https://github.com/claudioLuna/newposmoon

---

## 🚀 Comandos Básicos

### **Ver estado del repositorio**
```bash
cd /home/cluna/Documentos/Moon-Desarrollos/public_html
git status
```

### **Agregar cambios**
```bash
# Agregar todos los archivos modificados
git add .

# O agregar archivos específicos
git add archivo1.php archivo2.php
```

### **Hacer commit**
```bash
git commit -m "descripción de los cambios"
```

### **Subir cambios a GitHub**
```bash
git push
```

### **Bajar cambios desde GitHub**
```bash
git pull
```

---

## 📝 Flujo de Trabajo Normal

### **1. Hacer cambios en tu código**
```bash
# Editar archivos...
nano vistas/modulos/cabezote-mejorado.php
```

### **2. Ver qué cambió**
```bash
git status
git diff
```

### **3. Agregar cambios**
```bash
git add .
```

### **4. Hacer commit**
```bash
git commit -m "feat: agregar nueva funcionalidad X"
```

### **5. Subir a GitHub**
```bash
git push
```

---

## 🔄 Escenarios Comunes

### **Deshacer cambios no guardados**
```bash
# Deshacer cambios en un archivo
git checkout -- archivo.php

# Deshacer todos los cambios
git checkout -- .
```

### **Deshacer último commit (sin borrar cambios)**
```bash
git reset --soft HEAD~1
```

### **Ver historial de commits**
```bash
git log --oneline --graph

# O más detallado
git log
```

### **Ver diferencias**
```bash
# Ver cambios no guardados
git diff

# Ver cambios en staging
git diff --staged
```

---

## 🌿 Trabajar con Ramas

### **Crear nueva rama**
```bash
git checkout -b feature/nueva-funcionalidad
```

### **Cambiar de rama**
```bash
git checkout main
git checkout feature/nueva-funcionalidad
```

### **Ver ramas**
```bash
git branch -a
```

### **Mergear rama a main**
```bash
git checkout main
git merge feature/nueva-funcionalidad
git push
```

### **Eliminar rama**
```bash
# Local
git branch -d feature/nueva-funcionalidad

# Remota
git push origin --delete feature/nueva-funcionalidad
```

---

## 📦 Comandos de Sincronización

### **Clonar repositorio en otra computadora**
```bash
git clone https://github.com/claudioLuna/newposmoon.git
cd newposmoon
```

### **Actualizar desde GitHub (pull)**
```bash
git pull origin main
```

### **Forzar push (⚠️ usar con cuidado)**
```bash
git push -f origin main
```

---

## 🔍 Comandos de Información

### **Ver configuración**
```bash
git config --list
```

### **Ver remote**
```bash
git remote -v
```

### **Ver archivos rastreados**
```bash
git ls-files
```

### **Ver archivos ignorados**
```bash
git status --ignored
```

---

## 🛠️ Configuración Adicional

### **Configurar nombre y email**
```bash
git config --global user.name "Tu Nombre"
git config --global user.email "tu-email@ejemplo.com"
```

### **Configurar editor**
```bash
git config --global core.editor nano
```

### **Ver log bonito**
```bash
git log --oneline --graph --decorate --all
```

---

## 📋 Mensajes de Commit Recomendados

Usa prefijos para organizar tus commits:

- `feat:` nueva funcionalidad
- `fix:` corrección de bug
- `refactor:` refactorización de código
- `style:` cambios de estilo (CSS, formato)
- `docs:` documentación
- `test:` agregar tests
- `chore:` tareas de mantenimiento
- `security:` mejoras de seguridad

**Ejemplos:**
```bash
git commit -m "feat: agregar sistema de reportes avanzado"
git commit -m "fix: corregir cálculo de totales en ventas"
git commit -m "refactor: mejorar estructura del módulo de productos"
git commit -m "docs: actualizar README con instrucciones de instalación"
git commit -m "security: proteger endpoints contra SQL injection"
```

---

## 🚨 Solución de Problemas

### **Error: "fatal: not a git repository"**
```bash
cd /home/cluna/Documentos/Moon-Desarrollos/public_html
```

### **Error: "Your branch is behind"**
```bash
git pull origin main
```

### **Error: conflicto en merge**
```bash
# 1. Ver archivos con conflicto
git status

# 2. Editar manualmente los archivos
nano archivo-con-conflicto.php

# 3. Buscar y resolver marcas de conflicto:
#    <<<<<<< HEAD
#    tu código
#    =======
#    código del remote
#    >>>>>>> branch

# 4. Agregar y commit
git add archivo-con-conflicto.php
git commit -m "fix: resolver conflicto en merge"
git push
```

### **Olvidé agregar algo al último commit**
```bash
# Agregar archivos
git add archivo-olvidado.php

# Enmendar commit anterior
git commit --amend --no-edit

# Forzar push
git push -f
```

---

## 📁 Archivos Importantes

### **`.gitignore`** 
Archivos que NO se suben a GitHub:
- `modelos/conexion.php` (credenciales)
- `.env` (variables de entorno)
- `logs/` (archivos de log)
- `vendor/` (dependencias de Composer)

### **`modelos/conexion.example.php`**
Plantilla SIN credenciales para compartir

### **`README.md`**
Documentación principal del proyecto

---

## 🔗 Enlaces Útiles

- **Tu Repositorio:** https://github.com/claudioLuna/newposmoon
- **GitHub Docs:** https://docs.github.com
- **Git Cheat Sheet:** https://education.github.com/git-cheat-sheet-education.pdf

---

## 💡 Consejos

1. **Haz commits frecuentes** con mensajes descriptivos
2. **Haz pull antes de push** para evitar conflictos
3. **Usa ramas** para nuevas funcionalidades
4. **Nunca subas credenciales** (están en `.gitignore`)
5. **Revisa con `git status`** antes de commit
6. **Usa `git diff`** para ver exactamente qué cambió

---

**Última actualización:** $(date +"%d/%m/%Y")

