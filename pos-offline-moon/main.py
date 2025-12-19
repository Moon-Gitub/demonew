#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
APLICACIÓN PRINCIPAL - POS OFFLINE MOON
Sistema de punto de venta offline con sincronización automática
"""

import sys
import tkinter as tk

def main():
    """Función principal"""
    print("Iniciando aplicación...")
    
    try:
        from gui import LoginWindow
        from config import config
        
        print("Módulos importados correctamente")
        
        root = tk.Tk()
        root.withdraw()  # Ocultar ventana principal inicialmente
        
        print("Ventana raíz creada")
        
        # Mostrar ventana de login
        print("Creando ventana de login...")
        login = LoginWindow(root)
        
        print("Ventana de login creada")
        
        # Asegurar que la ventana de login esté visible
        root.update()
        root.update_idletasks()
        
        print("Iniciando loop principal...")
        
        # Iniciar el loop principal
        root.mainloop()
        
    except ImportError as e:
        print(f"❌ Error de importación: {e}")
        import traceback
        traceback.print_exc()
        sys.exit(1)
    except KeyboardInterrupt:
        print("\nAplicación cerrada por el usuario")
        if 'root' in locals():
            root.destroy()
    except Exception as e:
        print(f"❌ Error al iniciar aplicación: {e}")
        import traceback
        traceback.print_exc()
        if 'root' in locals():
            root.destroy()
        sys.exit(1)

if __name__ == "__main__":
    main()
