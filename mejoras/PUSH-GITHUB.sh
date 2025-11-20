#!/bin/bash

# ============================================
# Script para hacer push a GitHub
# ============================================

echo "🚀 Subiendo código a GitHub..."
echo ""
echo "⚠️  Cuando te pida credenciales:"
echo "   Username: claudioLuna"
echo "   Password: [PEGAR TU PERSONAL ACCESS TOKEN]"
echo ""

cd /home/cluna/Documentos/Moon-Desarrollos/public_html

git push -u origin main

echo ""
echo "✅ ¡Listo! Código subido a GitHub"

