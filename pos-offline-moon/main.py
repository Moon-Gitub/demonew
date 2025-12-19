#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
APLICACIÓN PRINCIPAL - POS OFFLINE MOON
Sistema de punto de venta offline con sincronización automática
"""

import tkinter as tk
from gui import LoginWindow
from config import config

def main():
    """Función principal"""
    root = tk.Tk()
    root.withdraw()  # Ocultar ventana principal inicialmente
    
    try:
        # Mostrar ventana de login
        login = LoginWindow(root)
        
        # Asegurar que la ventana de login esté visible
        root.update()
        
        # Iniciar el loop principal
        root.mainloop()
    except KeyboardInterrupt:
        print("\nAplicación cerrada por el usuario")
        root.destroy()
    except Exception as e:
        print(f"Error al iniciar aplicación: {e}")
        import traceback
        traceback.print_exc()
        root.destroy()

if __name__ == "__main__":
    main()
