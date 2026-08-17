# Solución de Errores Comunes - POS Offline

## ❌ Error: "No module named 'sqlalchemy'"

**Causa:** Las dependencias no están instaladas en el entorno virtual.

**Solución:**

```bash
cd /home/cluna/Documentos/7-Moon-Desarrollos/demonew/demonew/extras/pos-offline-moon

# Opción 1: Reinstalar el entorno virtual completo
rm -rf venv
python3 -m venv venv
venv/bin/python -m pip install -r requirements.txt

# Opción 2: Solo instalar dependencias faltantes
venv/bin/python -m pip install -r requirements.txt
```

## ❌ Error: "python: orden no encontrada"

**Causa:** El script está buscando `python` pero no está en el PATH.

**Solución:** El script `run.sh` ahora usa `venv/bin/python` directamente, así que debería funcionar. Si aún tienes problemas:

```bash
cd /home/cluna/Documentos/7-Moon-Desarrollos/demonew/demonew/extras/pos-offline-moon
venv/bin/python main.py
```

## ❌ Error: "source: not found"

**Causa:** Estás ejecutando con `sh` en lugar de `bash`.

**Solución:**

```bash
# Usa bash explícitamente
bash run.sh

# O dale permisos y ejecuta directamente
chmod +x run.sh
./run.sh
```

## ❌ Error: "externally-managed-environment"

**Causa:** Estás intentando instalar paquetes en el Python del sistema en lugar del venv.

**Solución:** Siempre usa el pip del venv:

```bash
venv/bin/python -m pip install -r requirements.txt
# NO uses: pip install (sin el venv)
```

## ✅ Verificación Rápida

Para verificar que todo está bien:

```bash
cd /home/cluna/Documentos/7-Moon-Desarrollos/demonew/demonew/extras/pos-offline-moon

# 1. Verificar que el venv existe
ls -la venv/bin/python

# 2. Verificar dependencias
venv/bin/python -c "import sqlalchemy, requests, bcrypt, PIL; print('✅ OK')"

# 3. Ejecutar
./run.sh
```

## 🔄 Reinstalación Completa

Si nada funciona, reinstala todo desde cero:

```bash
cd /home/cluna/Documentos/7-Moon-Desarrollos/demonew/demonew/extras/pos-offline-moon

# 1. Eliminar venv antiguo
rm -rf venv

# 2. Crear nuevo venv
python3 -m venv venv

# 3. Instalar dependencias
venv/bin/python -m pip install -r requirements.txt

# 4. Verificar
venv/bin/python -c "import sqlalchemy; print('✅ SQLAlchemy OK')"

# 5. Ejecutar
./run.sh
```
