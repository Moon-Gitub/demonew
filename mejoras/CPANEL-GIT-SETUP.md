# 🚀 Configurar Git en cPanel - Guía Paso a Paso

## 🎯 Objetivo

Configurar tu repositorio GitHub en cPanel para que puedas hacer deploy automático de cambios.

---

## 📋 Requisitos Previos

- [x] Repositorio en GitHub: https://github.com/claudioLuna/newposmoon
- [x] Código subido a GitHub
- [x] Acceso a cPanel
- [x] Personal Access Token de GitHub

---

## 🔧 Paso 1: Acceder a Git Version Control

1. **Iniciar sesión en cPanel**
   - URL: `https://tu-servidor:2083`
   - Usuario: `newmoon`
   - Contraseña: [tu contraseña]

2. **Navegar a Git**
   - **Files** → **Git Version Control**

---

## 🔄 Paso 2: Clonar Repositorio

### **Opción A: Repositorio Privado (Recomendado)**

1. Click en **Create** (arriba a la derecha)

2. **Configuración:**
   ```
   ✅ Clone a Repository: ACTIVADO
   
   Clone URL:
   https://claudioLuna:Mi_token@github.com/claudioLuna/newposmoon.git
   
   Repository Path:
   /home/newmoon/public_html
   
   Repository Name:
   newposmoon
   ```

3. Click **Create**

4. **Esperar** (puede tardar 1-2 minutos en clonar)

---

### **Opción B: Repositorio Público (Más Simple)**

Si haces el repo público en GitHub:

1. Click en **Create**

2. **Configuración:**
   ```
   ✅ Clone a Repository: ACTIVADO
   
   Clone URL:
   https://github.com/claudioLuna/newposmoon.git
   
   Repository Path:
   /home/newmoon/public_html
   
   Repository Name:
   newposmoon
   ```

3. Click **Create**

---

## ⚙️ Paso 3: Configurar Deployment Automático

Una vez clonado el repositorio:

1. **Verificar que `.cpanel.yml` existe**
   - Ya está en el repo (lo subimos antes)
   - cPanel lo usará automáticamente

2. **Primera vez: Configurar permisos**
   
   En **Terminal** (cPanel → Advanced → Terminal):
   ```bash
   cd /home/newmoon/public_html
   
   # Permisos de archivos
   find . -type f -exec chmod 644 {} \;
   
   # Permisos de directorios
   find . -type d -exec chmod 755 {} \;
   
   # Directorios de escritura
   chmod -R 777 logs
   chmod -R 777 vistas/img/usuarios
   chmod -R 777 vistas/img/productos
   ```

3. **Instalar dependencias de Composer**
   ```bash
   cd /home/newmoon/public_html/extensiones
   /usr/local/bin/ea-php81 /opt/cpanel/composer/bin/composer install --no-dev
   ```

---

## 🔄 Paso 4: Flujo de Trabajo Diario

### **Hacer cambios y deployar:**

#### **En tu PC (Local):**

1. **Hacer cambios en el código**
   ```bash
   cd /home/cluna/Documentos/Moon-Desarrollos/public_html
   # Editar archivos...
   ```

2. **Commit y Push**
   ```bash
   git add .
   git commit -m "feat: nueva funcionalidad X"
   git push
   ```

#### **En cPanel:**

1. **Ir a Git Version Control**

2. **Click en "Manage"** del repositorio

3. **Pestaña "Pull or Deploy"**

4. **Click "Update from Remote"**
   - Esto baja los cambios de GitHub
   - Esperar confirmación

5. **Click "Deploy HEAD Commit"**
   - Esto ejecuta `.cpanel.yml`
   - Actualiza Composer
   - Ajusta permisos
   - Esperar confirmación

6. **¡Listo!** Cambios aplicados

---

## 🎯 Comandos del .cpanel.yml

El archivo `.cpanel.yml` ejecuta automáticamente:

```yaml
deployment:
  tasks:
    # Actualizar Composer
    - cd /home/newmoon/public_html/extensiones
    - composer install --no-dev --optimize-autoloader
    
    # Ajustar permisos
    - find . -type f -exec chmod 644 {} \;
    - find . -type d -exec chmod 755 {} \;
    - chmod -R 777 logs
    - chmod -R 777 vistas/img/usuarios
    - chmod -R 777 vistas/img/productos
```

---

## 🚨 Solución de Problemas

### **Error: "could not read Username"**

**Causa:** Repositorio privado sin credenciales

**Solución:**
1. Usar token en URL (ver Opción A arriba)
2. O hacer repo público en GitHub

---

### **Error: "Directory not empty"**

**Causa:** `/home/newmoon/public_html` ya tiene archivos

**Solución:**
```bash
# Hacer backup primero
cd /home/newmoon
tar -czf backup_$(date +%Y%m%d).tar.gz public_html/

# Limpiar directorio
cd public_html
rm -rf * .[^.]*

# Dejar solo cgi-bin si existe
mkdir -p cgi-bin

# Ahora clonar desde cPanel
```

---

### **Error: "Deploy failed"**

**Verificar:**

1. **Que `.cpanel.yml` existe:**
   ```bash
   ls -la /home/newmoon/public_html/.cpanel.yml
   ```

2. **Ver logs de deployment:**
   - En cPanel → Git Version Control
   - Manage → Pull or Deploy
   - Scroll down → Ver mensajes de error

3. **Verificar permisos de Composer:**
   ```bash
   which composer
   /usr/local/bin/ea-php81 /opt/cpanel/composer/bin/composer --version
   ```

---

### **Error: "Branch is dirty"**

**Causa:** Hay cambios sin commit en el servidor

**Solución:**
```bash
cd /home/newmoon/public_html
git status
git stash  # Guardar cambios temporalmente
# O
git reset --hard  # CUIDADO: elimina cambios
```

---

## 🔒 Seguridad: Renovar Token

Si expusiste tu token accidentalmente:

1. **GitHub → Settings → Developer settings**
2. **Personal access tokens → Tokens (classic)**
3. **Delete** el token actual
4. **Generate new token**
5. **Copiar el nuevo token**
6. **En cPanel:**
   - Git Version Control → Manage
   - No puedes editar URL directamente
   - Necesitas re-crear el repositorio:
     - Remove (no borra archivos)
     - Create con nueva URL con nuevo token

---

## 📊 Ejemplo Completo

### **Escenario: Agregar nueva funcionalidad**

1. **Local:**
   ```bash
   cd /home/cluna/Documentos/Moon-Desarrollos/public_html
   
   # Crear nueva función
   nano vistas/modulos/nuevo-modulo.php
   
   # Commit
   git add vistas/modulos/nuevo-modulo.php
   git commit -m "feat: agregar módulo de reportes avanzados"
   git push
   ```

2. **cPanel:**
   - Git Version Control
   - Manage → Pull or Deploy
   - Update from Remote
   - Deploy HEAD Commit
   - ✅ Listo!

3. **Verificar:**
   ```
   https://newmoon.posmoon.com.ar/nuevo-modulo
   ```

---

## 🎯 Mejores Prácticas

### **Commits:**
- ✅ Hacer commits pequeños y frecuentes
- ✅ Mensajes descriptivos
- ✅ Probar localmente antes de push

### **Deployment:**
- ✅ Hacer backup antes de deploy grande
- ✅ Verificar logs después de deploy
- ✅ Probar funcionalidad crítica

### **Seguridad:**
- ✅ Nunca hacer commit de credenciales
- ✅ Verificar `.gitignore` está actualizado
- ✅ Renovar tokens periódicamente

---

## 📚 Referencias

- **cPanel Git Docs:** https://docs.cpanel.net/cpanel/files/git-version-control/
- **Tu Repositorio:** https://github.com/claudioLuna/newposmoon
- **Deployment Docs:** https://docs.cpanel.net/knowledge-base/web-services/guide-to-git-deployment/

---

## ✅ Checklist de Configuración

- [ ] Repositorio clonado en cPanel
- [ ] `.cpanel.yml` existe en el repo
- [ ] Primer deployment exitoso
- [ ] Composer instalado correctamente
- [ ] Permisos configurados
- [ ] Flujo de trabajo probado
- [ ] Sistema funciona en producción

---

**Última actualización:** $(date +"%d/%m/%Y")

