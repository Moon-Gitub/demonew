/**
 * Script para verificar que Bootstrap 5 está cargado
 * 
 * INSTRUCCIONES:
 * 1. Abre el sistema en el navegador
 * 2. Presiona F12 (consola)
 * 3. Pega este código completo y presiona Enter
 * 4. Verás un reporte completo del estado
 */

(function() {
    console.log("═══════════════════════════════════════════════════════════");
    console.log("🔍 VERIFICACIÓN BOOTSTRAP 5");
    console.log("═══════════════════════════════════════════════════════════");
    console.log("");
    
    // 1. Verificar Bootstrap 5
    console.log("1. VERIFICANDO BOOTSTRAP 5:");
    if (typeof bootstrap !== 'undefined') {
        console.log("   ✅ Bootstrap 5 está cargado");
        try {
            var version = bootstrap.Tooltip.VERSION || bootstrap.Modal.VERSION || "5.x";
            console.log("   📦 Versión: " + version);
        } catch(e) {
            console.log("   📦 Versión: 5.x (detectada)");
        }
    } else {
        console.log("   ❌ Bootstrap 5 NO está cargado");
        console.log("   ⚠️  Puede que esté usando Bootstrap 3");
    }
    console.log("");
    
    // 2. Verificar jQuery
    console.log("2. VERIFICANDO JQUERY:");
    if (typeof jQuery !== 'undefined') {
        console.log("   ✅ jQuery está cargado");
        console.log("   📦 Versión: " + jQuery.fn.jquery);
    } else {
        console.log("   ❌ jQuery NO está cargado");
    }
    console.log("");
    
    // 3. Verificar Shim
    console.log("3. VERIFICANDO SHIM DE COMPATIBILIDAD:");
    var shimScript = document.querySelector('script[src*="bootstrap3-to-5-shim"]');
    if (shimScript) {
        console.log("   ✅ Shim encontrado en HTML");
        console.log("   📁 Ruta: " + shimScript.src);
    } else {
        console.log("   ❌ Shim NO encontrado en HTML");
    }
    console.log("");
    
    // 4. Verificar CSS de compatibilidad
    console.log("4. VERIFICANDO CSS DE COMPATIBILIDAD:");
    var compatCSS = document.querySelector('link[href*="bootstrap-compat"]');
    if (compatCSS) {
        console.log("   ✅ CSS de compatibilidad encontrado");
        console.log("   📁 Ruta: " + compatCSS.href);
    } else {
        console.log("   ❌ CSS de compatibilidad NO encontrado");
    }
    console.log("");
    
    // 5. Verificar Bootstrap CSS (CDN)
    console.log("5. VERIFICANDO BOOTSTRAP CSS (CDN):");
    var bootstrapCSS = document.querySelector('link[href*="bootstrap@5"]');
    if (bootstrapCSS) {
        console.log("   ✅ Bootstrap 5 CSS encontrado");
        console.log("   📁 URL: " + bootstrapCSS.href);
        var versionMatch = bootstrapCSS.href.match(/bootstrap@([\d.]+)/);
        if (versionMatch) {
            console.log("   📦 Versión detectada: " + versionMatch[1]);
        }
    } else {
        console.log("   ❌ Bootstrap 5 CSS NO encontrado");
        var bootstrap3CSS = document.querySelector('link[href*="bootstrap"]');
        if (bootstrap3CSS) {
            console.log("   ⚠️  Encontrado otro Bootstrap: " + bootstrap3CSS.href);
        }
    }
    console.log("");
    
    // 6. Verificar Bootstrap JS (CDN)
    console.log("6. VERIFICANDO BOOTSTRAP JS (CDN):");
    var bootstrapJS = document.querySelector('script[src*="bootstrap@5"]');
    if (bootstrapJS) {
        console.log("   ✅ Bootstrap 5 JS encontrado");
        console.log("   📁 URL: " + bootstrapJS.src);
    } else {
        console.log("   ❌ Bootstrap 5 JS NO encontrado");
        var bootstrap3JS = document.querySelector('script[src*="bootstrap"]');
        if (bootstrap3JS) {
            console.log("   ⚠️  Encontrado otro Bootstrap: " + bootstrap3JS.src);
        }
    }
    console.log("");
    
    // 7. Verificar mensajes del shim en consola
    console.log("7. VERIFICANDO MENSAJES DEL SHIM:");
    console.log("   (Revisa arriba en la consola si ves mensajes como:)");
    console.log("   [Bootstrap Shim] Inicializando compatibilidad...");
    console.log("");
    
    // 8. Probar funcionalidad
    console.log("8. PROBANDO FUNCIONALIDAD:");
    if (typeof bootstrap !== 'undefined') {
        try {
            // Intentar crear un tooltip de prueba
            var testElement = document.createElement('div');
            testElement.setAttribute('data-bs-toggle', 'tooltip');
            testElement.setAttribute('title', 'Test');
            var tooltip = new bootstrap.Tooltip(testElement);
            console.log("   ✅ Tooltip funciona (Bootstrap 5 activo)");
            tooltip.dispose();
        } catch(e) {
            console.log("   ⚠️  Error al probar tooltip: " + e.message);
        }
    } else {
        console.log("   ❌ No se puede probar (Bootstrap 5 no cargado)");
    }
    console.log("");
    
    // 9. Resumen
    console.log("═══════════════════════════════════════════════════════════");
    console.log("📊 RESUMEN:");
    console.log("═══════════════════════════════════════════════════════════");
    
    var allOK = true;
    if (typeof bootstrap === 'undefined') {
        console.log("❌ Bootstrap 5 NO está cargado");
        allOK = false;
    }
    if (!shimScript) {
        console.log("❌ Shim NO está cargado");
        allOK = false;
    }
    if (!bootstrapCSS || !bootstrapCSS.href.includes('bootstrap@5')) {
        console.log("❌ Bootstrap 5 CSS NO está cargado");
        allOK = false;
    }
    if (!bootstrapJS || !bootstrapJS.src.includes('bootstrap@5')) {
        console.log("❌ Bootstrap 5 JS NO está cargado");
        allOK = false;
    }
    
    if (allOK) {
        console.log("");
        console.log("✅ ¡TODO CORRECTO! Bootstrap 5 está activo y funcionando");
        console.log("");
        console.log("💡 Nota: La apariencia se ve igual porque el shim");
        console.log("   mantiene compatibilidad visual con Bootstrap 3.");
        console.log("   La mejora está en la seguridad, no en lo visual.");
    } else {
        console.log("");
        console.log("⚠️  ALGUNOS COMPONENTES NO ESTÁN CARGADOS");
        console.log("");
        console.log("Posibles causas:");
        console.log("1. Caché del navegador - Presiona Ctrl+Shift+R");
        console.log("2. Estás en la rama incorrecta");
        console.log("3. Archivos no se subieron al servidor");
    }
    
    console.log("═══════════════════════════════════════════════════════════");
})();

