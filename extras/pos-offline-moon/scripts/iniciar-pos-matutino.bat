@echo off
cd /d "%~dp0.."
if exist venv\Scripts\activate.bat call venv\Scripts\activate.bat
python main.py --auto-login >> logs\matutino.log 2>&1
