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
    
    # Mostrar ventana de login
    login = LoginWindow(root)
    
    root.mainloop()

if __name__ == "__main__":
    main()
