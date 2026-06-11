#!/usr/bin/env bash
# Ejecutar EN EL SERVIDOR después de subir los archivos nuevos.
cd "$(dirname "$0")"

echo "=== Verificación informePython ==="
echo

for f in reporte.py reporte_moon.py reporte_automatico.py; do
  if [[ ! -f "$f" ]]; then
    echo "❌ Falta $f"
    continue
  fi
  n=$(grep -c "MOON_AUTO" "$f" 2>/dev/null || echo 0)
  if [[ "$f" == "reporte_automatico.py" && "$n" -ge 1 ]]; then
    echo "✅ $f (MOON_AUTO: $n)"
  elif [[ "$f" != "reporte_automatico.py" && "$n" -ge 1 ]]; then
    echo "✅ $f (MOON_AUTO: $n)"
  else
    echo "❌ $f versión VIEJA (MOON_AUTO: $n) — reemplazar desde el ZIP del repo"
  fi
done

echo
if grep -q "while True:" reporte.py 2>/dev/null && head -15 reporte.py | grep -q "input"; then
  echo "❌ reporte.py tiene input al inicio (versión antigua)"
fi

[[ -f bases.txt ]] && echo "✅ bases.txt" || echo "⚠️ Falta bases.txt"
[[ -f bases2.txt ]] && echo "✅ bases2.txt" || echo "⚠️ Falta bases2.txt (copiá bases-2.txt → bases2.txt)"

if grep -q "reporte_util" reporte_automatico.py 2>/dev/null; then
  echo "❌ reporte_automatico.py viejo (importa reporte_util)"
else
  echo "✅ reporte_automatico.py autocontenido"
fi

echo
echo "Probar: python3 reporte_automatico.py"
