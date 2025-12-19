@echo off
REM Script para ejecutar POS Offline Moon con entorno virtual

cd /d "%~dp0"

REM Activar entorno virtual
if exist "venv\Scripts\activate.bat" (
    call venv\Scripts\activate.bat
    python main.py
) else (
    echo ❌ Entorno virtual no encontrado.
    echo 💡 Ejecuta primero: python install.py
    pause
    exit /b 1
)

pause
