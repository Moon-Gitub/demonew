# Servicio de Impresión Local - POS Offline Moon

## 📋 Índice

1. [Descripción General](#descripción-general)
2. [Arquitectura](#arquitectura)
3. [Instalación y Configuración](#instalación-y-configuración)
   - [Windows](#windows)
   - [Linux](#linux)
4. [Implementación del Servicio](#implementación-del-servicio)
5. [Integración con el Sistema Web](#integración-con-el-sistema-web)
6. [Uso y Ejemplos](#uso-y-ejemplos)
7. [Solución de Problemas](#solución-de-problemas)

---

## Descripción General

Este servicio permite imprimir directamente desde el navegador web hacia una impresora instalada en la PC local, sin necesidad de usar kiosk mode o extensiones del navegador. El sistema funciona de forma transparente:

- **Si el servicio está instalado y corriendo**: Usa el servicio local para imprimir
- **Si el servicio NO está disponible**: Usa el método tradicional (`window.print()`)

### Ventajas

- ✅ No requiere configuración especial del navegador
- ✅ Funciona con cualquier navegador moderno
- ✅ Compatible con impresoras térmicas, láser, inyección de tinta
- ✅ Soporta PDFs y HTML/texto plano
- ✅ Fallback automático al método tradicional
- ✅ Fácil de instalar y configurar

---

## Arquitectura

```
┌─────────────────┐         HTTP Request          ┌──────────────────┐
│  Navegador Web  │ ────────────────────────────> │  Servicio Local  │
│  (JavaScript)   │ <──────────────────────────── │  (Python/Flask)  │
└─────────────────┘      JSON Response           └──────────────────┘
                                                          │
                                                          ▼
                                                   ┌──────────────┐
                                                   │  Impresora   │
                                                   │    Local     │
                                                   └──────────────┘
```

### Componentes

1. **Servicio Python (Flask)**: Escucha en `localhost:8888` y recibe comandos de impresión
2. **Helper JavaScript**: Detecta si el servicio está disponible y lo usa automáticamente
3. **Funciones de impresión modificadas**: Usan el helper con fallback automático

---

## Instalación y Configuración

### Windows

#### Paso 1: Requisitos Previos

1. **Python 3.7 o superior**
   - Descargar desde [python.org](https://www.python.org/downloads/)
   - ✅ **IMPORTANTE**: Marcar "Add Python to PATH" durante la instalación
   - Verificar instalación:
     ```cmd
     python --version
     ```

2. **Git (opcional, para clonar el repositorio)**
   - Descargar desde [git-scm.com](https://git-scm.com/download/win)

#### Paso 2: Descargar el Sistema

**Opción A: Desde Git**
```cmd
git clone https://github.com/Moon-Gitub/demonew.git
cd demonew/pos-offline-moon
```

**Opción B: Descargar ZIP**
1. Descargar el repositorio como ZIP
2. Extraer en una carpeta (ej: `C:\POS-Offline-Moon`)
3. Abrir PowerShell o CMD en esa carpeta

#### Paso 3: Instalación Automática

```cmd
python install.py
```

Este script:
- ✅ Verifica versión de Python
- ✅ Crea entorno virtual (`venv`)
- ✅ Instala todas las dependencias
- ✅ Crea directorios necesarios (`data/`, `logs/`, `backups/`)
- ✅ Crea archivo de configuración inicial

#### Paso 4: Configuración Inicial

```cmd
python setup.py
```

O usar el script:
```cmd
setup.bat
```

Seguir las instrucciones para:
- Configurar URL del servidor (ej: `https://newmoon.posmoon.com.ar`)
- Configurar ID Cliente Moon (ej: `14`)
- Probar conexión
- Sincronización inicial de usuarios y productos

#### Paso 5: Ejecutar el Sistema

**Opción A: Usando el script**
```cmd
run.bat
```

**Opción B: Manualmente**
```cmd
venv\Scripts\activate
python main.py
```

#### Paso 6: Configuración del Archivo `config.json`

El archivo se crea automáticamente, pero puedes editarlo manualmente:

```json
{
    "server_url": "https://newmoon.posmoon.com.ar",
    "api_base": "https://newmoon.posmoon.com.ar/api",
    "id_cliente_moon": 14,
    "sync_interval": 60,
    "connection_check_interval": 5,
    "account_check_interval": 300
}
```

**Ubicación**: `pos-offline-moon/config.json`

#### Paso 7: Instalar el Servicio de Impresión (Opcional)

Ver sección [Implementación del Servicio](#implementación-del-servicio) más abajo.

---

### Linux

#### Paso 1: Requisitos Previos

**Ubuntu/Debian:**
```bash
sudo apt-get update
sudo apt-get install python3 python3-pip python3-venv python3-tk git
```

**CentOS/RHEL:**
```bash
sudo yum install python3 python3-pip python3-tkinter git
```

**Fedora:**
```bash
sudo dnf install python3 python3-pip python3-tkinter git
```

Verificar instalación:
```bash
python3 --version
```

#### Paso 2: Descargar el Sistema

**Opción A: Desde Git**
```bash
git clone https://github.com/Moon-Gitub/demonew.git
cd demonew/pos-offline-moon
```

**Opción B: Descargar ZIP**
```bash
wget https://github.com/Moon-Gitub/demonew/archive/main.zip
unzip main.zip
cd demonew-main/pos-offline-moon
```

#### Paso 3: Instalación Automática

```bash
python3 install.py
```

O dar permisos de ejecución y usar:
```bash
chmod +x install.py
./install.py
```

Este script:
- ✅ Verifica versión de Python
- ✅ Crea entorno virtual (`venv`)
- ✅ Instala todas las dependencias
- ✅ Crea directorios necesarios
- ✅ Crea archivo de configuración inicial

#### Paso 4: Configuración Inicial

```bash
python3 setup.py
```

O usar el script:
```bash
chmod +x setup.sh
./setup.sh
```

#### Paso 5: Ejecutar el Sistema

**Opción A: Usando el script**
```bash
chmod +x run.sh
./run.sh
```

**Opción B: Manualmente**
```bash
source venv/bin/activate
python3 main.py
```

#### Paso 6: Configuración del Archivo `config.json`

```json
{
    "server_url": "https://newmoon.posmoon.com.ar",
    "api_base": "https://newmoon.posmoon.com.ar/api",
    "id_cliente_moon": 14,
    "sync_interval": 60,
    "connection_check_interval": 5,
    "account_check_interval": 300
}
```

**Ubicación**: `pos-offline-moon/config.json`

#### Paso 7: Instalar el Servicio de Impresión (Opcional)

Ver sección [Implementación del Servicio](#implementación-del-servicio) más abajo.

---

## Implementación del Servicio

### Estructura de Archivos

```
pos-offline-moon/
├── print-service/
│   ├── server.py              # Servicio Flask
│   ├── requirements.txt       # Dependencias del servicio
│   ├── config.json.example    # Ejemplo de configuración
│   ├── run.sh                 # Script de ejecución (Linux)
│   └── run.bat                # Script de ejecución (Windows)
├── vistas/
│   └── js/
│       └── print-service.js   # Helper JavaScript
└── ...
```

### 1. Crear el Servicio Python

**Archivo**: `pos-offline-moon/print-service/server.py`

```python
#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
SERVICIO DE IMPRESIÓN LOCAL
Escucha en localhost:8888 y recibe comandos de impresión desde el navegador
"""

from flask import Flask, request, jsonify
from flask_cors import CORS
import platform
import subprocess
import tempfile
import os
import json
from pathlib import Path

app = Flask(__name__)
CORS(app)  # Permitir CORS para requests desde el navegador

# Configuración
PRINT_SERVICE_PORT = 8888
DEFAULT_PRINTER = None  # None = impresora por defecto del sistema

def get_default_printer():
    """Obtiene la impresora por defecto del sistema"""
    system = platform.system()
    
    if system == "Windows":
        try:
            result = subprocess.run(
                ['powershell', '-Command', 
                 'Get-CimInstance Win32_Printer | Where-Object {$_.Default -eq $true} | Select-Object -ExpandProperty Name'],
                capture_output=True,
                text=True,
                timeout=5
            )
            return result.stdout.strip() if result.returncode == 0 else None
        except:
            return None
    
    elif system == "Linux":
        try:
            result = subprocess.run(
                ['lpstat', '-d'],
                capture_output=True,
                text=True,
                timeout=5
            )
            if result.returncode == 0:
                # Formato: "system default destination: NombreImpresora"
                output = result.stdout.strip()
                if ':' in output:
                    return output.split(':', 1)[1].strip()
            return None
        except:
            return None
    
    return None

def print_html(html_content, printer_name=None):
    """Imprime contenido HTML"""
    system = platform.system()
    printer = printer_name or DEFAULT_PRINTER or get_default_printer()
    
    try:
        # Crear archivo temporal HTML
        with tempfile.NamedTemporaryFile(mode='w', suffix='.html', delete=False, encoding='utf-8') as f:
            f.write(html_content)
            temp_file = f.name
        
        if system == "Windows":
            # Windows: usar mshta para imprimir HTML
            if printer:
                cmd = ['mshta', f'javascript:window.print();close();']
                # Abrir HTML y usar print dialog
                subprocess.Popen(['start', 'mshta', f'file:///{temp_file.replace(chr(92), "/")}'], shell=True)
            else:
                # Abrir con navegador por defecto y imprimir
                subprocess.Popen(['start', temp_file], shell=True)
        
        elif system == "Linux":
            # Linux: usar wkhtmltopdf o weasyprint para convertir a PDF y luego imprimir
            try:
                # Intentar con wkhtmltopdf
                pdf_file = temp_file.replace('.html', '.pdf')
                subprocess.run(['wkhtmltopdf', temp_file, pdf_file], check=True, timeout=30)
                if printer:
                    subprocess.run(['lp', '-d', printer, pdf_file], check=True, timeout=10)
                else:
                    subprocess.run(['lp', pdf_file], check=True, timeout=10)
                os.unlink(pdf_file)
            except:
                # Fallback: usar lpr con HTML (requiere que el sistema lo soporte)
                if printer:
                    subprocess.run(['lp', '-d', printer, temp_file], check=True, timeout=10)
                else:
                    subprocess.run(['lp', temp_file], check=True, timeout=10)
        
        # Limpiar archivo temporal después de un delay
        import threading
        def cleanup():
            import time
            time.sleep(5)  # Esperar 5 segundos antes de eliminar
            try:
                os.unlink(temp_file)
            except:
                pass
        threading.Thread(target=cleanup, daemon=True).start()
        
        return True
    
    except Exception as e:
        print(f"Error al imprimir HTML: {e}")
        return False

def print_pdf(pdf_data, printer_name=None):
    """Imprime contenido PDF (base64 o bytes)"""
    system = platform.system()
    printer = printer_name or DEFAULT_PRINTER or get_default_printer()
    
    try:
        import base64
        
        # Decodificar base64 si es necesario
        if isinstance(pdf_data, str):
            pdf_bytes = base64.b64decode(pdf_data)
        else:
            pdf_bytes = pdf_data
        
        # Crear archivo temporal PDF
        with tempfile.NamedTemporaryFile(mode='wb', suffix='.pdf', delete=False) as f:
            f.write(pdf_bytes)
            temp_file = f.name
        
        if system == "Windows":
            # Windows: usar Adobe Reader o lector PDF por defecto
            if printer:
                # Intentar imprimir directamente
                subprocess.Popen(['start', '/min', temp_file], shell=True)
            else:
                subprocess.Popen(['start', temp_file], shell=True)
        
        elif system == "Linux":
            # Linux: usar lp para imprimir PDF
            if printer:
                subprocess.run(['lp', '-d', printer, temp_file], check=True, timeout=10)
            else:
                subprocess.run(['lp', temp_file], check=True, timeout=10)
        
        # Limpiar archivo temporal
        import threading
        def cleanup():
            import time
            time.sleep(5)
            try:
                os.unlink(temp_file)
            except:
                pass
        threading.Thread(target=cleanup, daemon=True).start()
        
        return True
    
    except Exception as e:
        print(f"Error al imprimir PDF: {e}")
        return False

def print_text(text_content, printer_name=None):
    """Imprime texto plano"""
    system = platform.system()
    printer = printer_name or DEFAULT_PRINTER or get_default_printer()
    
    try:
        # Crear archivo temporal de texto
        with tempfile.NamedTemporaryFile(mode='w', suffix='.txt', delete=False, encoding='utf-8') as f:
            f.write(text_content)
            temp_file = f.name
        
        if system == "Windows":
            # Windows: usar notepad o impresora directa
            if printer:
                subprocess.run(['notepad', '/p', temp_file], check=True, timeout=10)
            else:
                subprocess.run(['notepad', '/p', temp_file], check=True, timeout=10)
        
        elif system == "Linux":
            # Linux: usar lp para imprimir texto
            if printer:
                subprocess.run(['lp', '-d', printer, temp_file], check=True, timeout=10)
            else:
                subprocess.run(['lp', temp_file], check=True, timeout=10)
        
        # Limpiar archivo temporal
        import threading
        def cleanup():
            import time
            time.sleep(2)
            try:
                os.unlink(temp_file)
            except:
                pass
        threading.Thread(target=cleanup, daemon=True).start()
        
        return True
    
    except Exception as e:
        print(f"Error al imprimir texto: {e}")
        return False

@app.route('/health', methods=['GET'])
def health():
    """Endpoint de salud del servicio"""
    return jsonify({
        'status': 'ok',
        'service': 'print-service',
        'platform': platform.system(),
        'default_printer': get_default_printer()
    })

@app.route('/print', methods=['POST'])
def print_document():
    """Endpoint principal para imprimir"""
    try:
        data = request.json
        
        if not data:
            return jsonify({'error': 'No se recibieron datos'}), 400
        
        print_type = data.get('type', 'html')  # html, pdf, text
        content = data.get('content', '')
        printer = data.get('printer', None)
        
        if not content:
            return jsonify({'error': 'No se recibió contenido para imprimir'}), 400
        
        success = False
        
        if print_type == 'html':
            success = print_html(content, printer)
        elif print_type == 'pdf':
            success = print_pdf(content, printer)
        elif print_type == 'text':
            success = print_text(content, printer)
        else:
            return jsonify({'error': f'Tipo de impresión no soportado: {print_type}'}), 400
        
        if success:
            return jsonify({
                'status': 'success',
                'message': 'Documento enviado a impresión',
                'printer': printer or get_default_printer() or 'default'
            })
        else:
            return jsonify({'error': 'Error al enviar a impresión'}), 500
    
    except Exception as e:
        return jsonify({'error': str(e)}), 500

@app.route('/printers', methods=['GET'])
def list_printers():
    """Lista las impresoras disponibles"""
    system = platform.system()
    printers = []
    
    try:
        if system == "Windows":
            result = subprocess.run(
                ['powershell', '-Command',
                 'Get-CimInstance Win32_Printer | Select-Object Name, Default | ConvertTo-Json'],
                capture_output=True,
                text=True,
                timeout=5
            )
            if result.returncode == 0:
                import json
                printer_list = json.loads(result.stdout)
                if isinstance(printer_list, dict):
                    printer_list = [printer_list]
                printers = [p.get('Name', '') for p in printer_list if p.get('Name')]
        
        elif system == "Linux":
            result = subprocess.run(
                ['lpstat', '-p'],
                capture_output=True,
                text=True,
                timeout=5
            )
            if result.returncode == 0:
                for line in result.stdout.split('\n'):
                    if line.startswith('printer '):
                        printer_name = line.split()[1]
                        printers.append(printer_name)
    
    except Exception as e:
        print(f"Error al listar impresoras: {e}")
    
    return jsonify({
        'printers': printers,
        'default': get_default_printer()
    })

if __name__ == '__main__':
    print(f"🖨️  Servicio de Impresión Local iniciado")
    print(f"📡 Escuchando en http://localhost:{PRINT_SERVICE_PORT}")
    print(f"🖨️  Impresora por defecto: {get_default_printer() or 'No detectada'}")
    print(f"\n💡 Para detener el servicio, presiona Ctrl+C\n")
    
    app.run(host='127.0.0.1', port=PRINT_SERVICE_PORT, debug=False)
```

### 2. Crear `requirements.txt` para el Servicio

**Archivo**: `pos-offline-moon/print-service/requirements.txt`

```
Flask==3.0.0
flask-cors==4.0.0
```

### 3. Crear Scripts de Ejecución

**Windows - `print-service/run.bat`:**

```batch
@echo off
REM Script para ejecutar Servicio de Impresión Local

cd /d "%~dp0"

REM Activar entorno virtual si existe
if exist "..\venv\Scripts\activate.bat" (
    call ..\venv\Scripts\activate.bat
)

REM Instalar dependencias si es necesario
pip install -q -r requirements.txt

REM Ejecutar servicio
python server.py

pause
```

**Linux - `print-service/run.sh`:**

```bash
#!/bin/bash
cd "$(dirname "$0")"

# Activar entorno virtual si existe
if [ -f "../venv/bin/activate" ]; then
    source ../venv/bin/activate
fi

# Instalar dependencias si es necesario
pip install -q -r requirements.txt

# Ejecutar servicio
python3 server.py
```

Dar permisos:
```bash
chmod +x run.sh
```

### 4. Crear Helper JavaScript

**Archivo**: `vistas/js/print-service.js`

```javascript
/**
 * SERVICIO DE IMPRESIÓN LOCAL
 * Helper para comunicarse con el servicio de impresión local
 * Si el servicio no está disponible, usa el método tradicional
 */

const PrintService = {
    SERVICE_URL: 'http://localhost:8888',
    CHECK_TIMEOUT: 1000, // 1 segundo para verificar disponibilidad
    isAvailable: null, // Cache del estado
    
    /**
     * Verifica si el servicio está disponible
     */
    async checkAvailability() {
        if (this.isAvailable !== null) {
            return this.isAvailable; // Usar cache
        }
        
        try {
            const controller = new AbortController();
            const timeoutId = setTimeout(() => controller.abort(), this.CHECK_TIMEOUT);
            
            const response = await fetch(`${this.SERVICE_URL}/health`, {
                method: 'GET',
                signal: controller.signal
            });
            
            clearTimeout(timeoutId);
            
            if (response.ok) {
                this.isAvailable = true;
                return true;
            } else {
                this.isAvailable = false;
                return false;
            }
        } catch (error) {
            this.isAvailable = false;
            return false;
        }
    },
    
    /**
     * Imprime contenido HTML
     */
    async printHTML(htmlContent, options = {}) {
        const available = await this.checkAvailability();
        
        if (available) {
            try {
                const response = await fetch(`${this.SERVICE_URL}/print`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        type: 'html',
                        content: htmlContent,
                        printer: options.printer || null
                    })
                });
                
                const result = await response.json();
                
                if (result.status === 'success') {
                    console.log('✅ Impresión enviada al servicio local');
                    return true;
                } else {
                    console.warn('⚠️ Error en servicio de impresión:', result.error);
                    // Fallback al método tradicional
                    return this.printHTMLFallback(htmlContent);
                }
            } catch (error) {
                console.warn('⚠️ Error al conectar con servicio de impresión:', error);
                // Fallback al método tradicional
                return this.printHTMLFallback(htmlContent);
            }
        } else {
            // Servicio no disponible, usar método tradicional
            return this.printHTMLFallback(htmlContent);
        }
    },
    
    /**
     * Método tradicional de impresión (fallback)
     */
    printHTMLFallback(htmlContent) {
        const mywindow = window.open('', 'PRINT', 'height=400,width=600');
        
        if (!mywindow) {
            console.error('❌ No se pudo abrir ventana de impresión');
            return false;
        }
        
        mywindow.document.write('<html><head>');
        mywindow.document.write('<style>' +
            '.tabla{' +
                'width:100%;' +
                'border-collapse:collapse;' +
                'margin:16px 0 16px 0;}' +
            '.tabla th{' +
                'border:1px solid #ddd;' +
                'padding:4px;' +
                'background-color:#d4eefd;' +
                'text-align:left;' +
                'font-size:20px;}' +
            '.tabla td{' +
                'border:1px solid #ddd;' +
                'text-align:left;' +
                'padding:6px;}' +
            '</style>');
        mywindow.document.write('</head><body style="font-family: Arial; font-size: 20px">');
        mywindow.document.write(htmlContent);
        mywindow.document.write('</body></html>');
        
        mywindow.print();
        mywindow.close();
        
        return true;
    },
    
    /**
     * Imprime contenido PDF (base64)
     */
    async printPDF(pdfBase64, options = {}) {
        const available = await this.checkAvailability();
        
        if (available) {
            try {
                const response = await fetch(`${this.SERVICE_URL}/print`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        type: 'pdf',
                        content: pdfBase64,
                        printer: options.printer || null
                    })
                });
                
                const result = await response.json();
                
                if (result.status === 'success') {
                    console.log('✅ PDF enviado al servicio local');
                    return true;
                } else {
                    console.warn('⚠️ Error en servicio de impresión:', result.error);
                    return false;
                }
            } catch (error) {
                console.warn('⚠️ Error al conectar con servicio de impresión:', error);
                return false;
            }
        } else {
            console.warn('⚠️ Servicio de impresión no disponible para PDF');
            return false;
        }
    },
    
    /**
     * Obtiene lista de impresoras disponibles
     */
    async getPrinters() {
        const available = await this.checkAvailability();
        
        if (!available) {
            return [];
        }
        
        try {
            const response = await fetch(`${this.SERVICE_URL}/printers`);
            const result = await response.json();
            return result.printers || [];
        } catch (error) {
            console.warn('⚠️ Error al obtener impresoras:', error);
            return [];
        }
    }
};

// Auto-verificar disponibilidad al cargar
PrintService.checkAvailability().then(available => {
    if (available) {
        console.log('✅ Servicio de impresión local disponible');
    } else {
        console.log('ℹ️ Servicio de impresión local no disponible, usando método tradicional');
    }
});
```

### 5. Modificar Funciones de Impresión Existentes

**Modificar `vistas/js/venta-caja.js`:**

```javascript
// Agregar al inicio del archivo (después de las otras funciones)
// Incluir el helper si no está incluido ya
// <script src="vistas/js/print-service.js"></script>

// Modificar la función impTicketCaja existente:
function impTicketCaja(el){
    // Obtener el contenido HTML del elemento
    const htmlContent = document.getElementById(el).innerHTML;
    
    // Intentar usar el servicio de impresión local
    PrintService.printHTML(htmlContent).then(success => {
        if (success) {
            console.log('✅ Impresión enviada');
        } else {
            console.log('ℹ️ Usando método tradicional');
        }
    });
    
    return true;
}
```

**Modificar función de imprimir factura:**

```javascript
// En lugar de:
// window.open("extensiones/vendor/tecnickcom/tcpdf/pdf/factura.php?codigo="+codigoVenta, "_blank");

// Usar:
$(".tablas").on("click", ".btnImprimirFacturaCaja", function(){
    var codigoVenta = $(this).attr("codigoVenta");
    
    // Intentar obtener PDF y enviarlo al servicio local
    fetch(`extensiones/vendor/tecnickcom/tcpdf/pdf/factura.php?codigo=${codigoVenta}`)
        .then(response => response.blob())
        .then(blob => {
            const reader = new FileReader();
            reader.onloadend = function() {
                const base64 = reader.result.split(',')[1];
                PrintService.printPDF(base64).then(success => {
                    if (!success) {
                        // Fallback: abrir en nueva ventana
                        window.open(`extensiones/vendor/tecnickcom/tcpdf/pdf/factura.php?codigo=${codigoVenta}`, "_blank");
                    }
                });
            };
            reader.readAsDataURL(blob);
        })
        .catch(() => {
            // Fallback: abrir en nueva ventana
            window.open(`extensiones/vendor/tecnickcom/tcpdf/pdf/factura.php?codigo=${codigoVenta}`, "_blank");
        });
});
```

---

## Uso y Ejemplos

### Ejemplo 1: Imprimir Ticket de Venta

```javascript
// Obtener contenido HTML del ticket
const ticketHTML = document.getElementById('impTicketCobroCaja').innerHTML;

// Imprimir usando el servicio
PrintService.printHTML(ticketHTML).then(success => {
    if (success) {
        console.log('Ticket enviado a impresión');
    }
});
```

### Ejemplo 2: Imprimir PDF de Factura

```javascript
// Obtener PDF como base64
fetch('extensiones/vendor/tecnickcom/tcpdf/pdf/factura.php?codigo=123')
    .then(response => response.blob())
    .then(blob => {
        const reader = new FileReader();
        reader.onloadend = function() {
            const base64 = reader.result.split(',')[1];
            PrintService.printPDF(base64);
        };
        reader.readAsDataURL(blob);
    });
```

### Ejemplo 3: Listar Impresoras Disponibles

```javascript
PrintService.getPrinters().then(printers => {
    console.log('Impresoras disponibles:', printers);
});
```

### Ejemplo 4: Imprimir en Impresora Específica

```javascript
PrintService.printHTML(htmlContent, { printer: 'HP-LaserJet-Pro' });
```

---

## Solución de Problemas

### El servicio no se inicia

**Windows:**
```cmd
# Verificar que Python esté instalado
python --version

# Verificar que el puerto 8888 no esté en uso
netstat -ano | findstr :8888

# Si está en uso, cambiar el puerto en server.py
```

**Linux:**
```bash
# Verificar que Python esté instalado
python3 --version

# Verificar que el puerto 8888 no esté en uso
sudo netstat -tulpn | grep :8888

# Instalar dependencias del sistema
sudo apt-get install cups cups-client  # Para impresión en Linux
```

### El navegador no puede conectar al servicio

1. **Verificar que el servicio esté corriendo:**
   ```bash
   # Abrir en navegador
   http://localhost:8888/health
   ```

2. **Verificar CORS:** El servicio ya incluye `flask-cors`, pero si hay problemas:
   ```python
   # En server.py, verificar que CORS esté habilitado
   CORS(app)
   ```

3. **Verificar firewall:** Asegurarse de que el firewall permita conexiones locales en el puerto 8888

### La impresión no funciona en Linux

**Instalar herramientas necesarias:**

```bash
# Para imprimir HTML
sudo apt-get install wkhtmltopdf

# Para imprimir PDF
sudo apt-get install cups cups-pdf

# Verificar impresoras
lpstat -p
```

### El servicio se cierra automáticamente

**Windows - Crear servicio como Windows Service:**

Usar `NSSM` (Non-Sucking Service Manager):
```cmd
# Descargar NSSM desde https://nssm.cc/download
nssm install PrintService "C:\ruta\al\python.exe" "C:\ruta\al\server.py"
nssm start PrintService
```

**Linux - Crear servicio systemd:**

Crear `/etc/systemd/system/print-service.service`:

```ini
[Unit]
Description=Servicio de Impresión Local POS Moon
After=network.target

[Service]
Type=simple
User=tu_usuario
WorkingDirectory=/ruta/al/pos-offline-moon/print-service
ExecStart=/ruta/al/pos-offline-moon/venv/bin/python server.py
Restart=always
RestartSec=10

[Install]
WantedBy=multi-user.target
```

Activar servicio:
```bash
sudo systemctl enable print-service
sudo systemctl start print-service
sudo systemctl status print-service
```

---

## Archivos de Configuración Resumen

### `config.json` (Sistema Principal)

```json
{
    "server_url": "https://newmoon.posmoon.com.ar",
    "api_base": "https://newmoon.posmoon.com.ar/api",
    "id_cliente_moon": 14,
    "sync_interval": 60,
    "connection_check_interval": 5,
    "account_check_interval": 300
}
```

### `print-service/config.json` (Opcional)

```json
{
    "port": 8888,
    "default_printer": null,
    "timeout": 30
}
```

---

## Notas Finales

- ✅ El servicio funciona de forma **transparente**: si está disponible lo usa, si no, usa el método tradicional
- ✅ **No requiere configuración especial** del navegador
- ✅ Compatible con **cualquier navegador moderno**
- ✅ Soporta **Windows y Linux**
- ✅ Fácil de **instalar y mantener**

---

## Próximos Pasos

1. Implementar el servicio Python (`print-service/server.py`)
2. Crear el helper JavaScript (`vistas/js/print-service.js`)
3. Modificar las funciones de impresión existentes
4. Probar en Windows y Linux
5. Documentar casos de uso específicos

---

**Última actualización**: Diciembre 2024
