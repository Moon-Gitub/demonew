#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
INTERFAZ GRÁFICA MODERNA Y FUNCIONAL
Sistema POS offline con diseño inspirado en crear-venta-caja
"""

import tkinter as tk
from tkinter import ttk, messagebox, simpledialog
from datetime import datetime, timedelta
from database import get_session, Producto, Venta
from sync import SyncManager
from connection import ConnectionMonitor
from auth import AuthManager
from config import config

class LoginWindow:
    def __init__(self, parent):
        self.parent = parent
        self.auth_manager = AuthManager()
        self.sync_manager = SyncManager()
        self.connection_monitor = ConnectionMonitor()
        self.id_cliente_moon = config.ID_CLIENTE_MOON
        
        self.setup_login_ui()
        self.window.after(500, self.check_initial_sync)
    
    def setup_login_ui(self):
        self.window = tk.Toplevel(self.parent)
        self.window.title("POS Offline Moon - Login")
        self.window.geometry("450x550")
        self.window.configure(bg="#667eea")
        self.window.resizable(False, False)
        self.window.transient(self.parent)
        self.window.grab_set()
        
        main_frame = tk.Frame(self.window, bg="#667eea")
        main_frame.pack(fill=tk.BOTH, expand=True, padx=40, pady=40)
        
        tk.Label(main_frame, text="POS | Moon", font=("Arial", 28, "bold"), bg="#667eea", fg="white").pack(pady=(0, 10))
        tk.Label(main_frame, text="Sistema Offline", font=("Arial", 12), bg="#667eea", fg="white").pack(pady=(0, 30))
        
        login_frame = tk.Frame(main_frame, bg="white", relief=tk.RAISED, bd=2)
        login_frame.pack(fill=tk.BOTH, expand=True)
        
        tk.Label(login_frame, text="Iniciar Sesión", font=("Arial", 16, "bold"), bg="white").pack(pady=20)
        
        tk.Label(login_frame, text="Usuario:", bg="white", anchor="w", font=("Arial", 10)).pack(fill=tk.X, padx=20, pady=(10, 5))
        self.usuario_entry = tk.Entry(login_frame, font=("Arial", 12), width=30, relief=tk.SOLID, bd=1)
        self.usuario_entry.pack(padx=20, pady=(0, 10))
        self.usuario_entry.focus()
        
        tk.Label(login_frame, text="Contraseña:", bg="white", anchor="w", font=("Arial", 10)).pack(fill=tk.X, padx=20, pady=(10, 5))
        self.password_entry = tk.Entry(login_frame, font=("Arial", 12), show="*", width=30, relief=tk.SOLID, bd=1)
        self.password_entry.pack(padx=20, pady=(0, 20))
        self.password_entry.bind("<Return>", lambda e: self.login())
        
        self.connection_status = tk.Label(
            login_frame,
            text="🟢 En línea" if self.connection_monitor.check_connection() else "🔴 Sin conexión",
            bg="white", fg="#666", font=("Arial", 9)
        )
        self.connection_status.pack(pady=5)
        
        tk.Button(login_frame, text="Ingresar", bg="#667eea", fg="white", font=("Arial", 12, "bold"),
                 command=self.login, relief=tk.FLAT, padx=30, pady=10, cursor="hand2").pack(pady=20)
        
        tk.Button(login_frame, text="🔄 Sincronizar", bg="#764ba2", fg="white", font=("Arial", 10),
                 command=self.manual_sync, relief=tk.FLAT, padx=20, pady=5, cursor="hand2").pack(pady=5)
        
        self.window.update_idletasks()
        width = max(450, self.window.winfo_reqwidth())
        height = max(550, self.window.winfo_reqheight())
        x = (self.window.winfo_screenwidth() // 2) - (width // 2)
        y = (self.window.winfo_screenheight() // 2) - (height // 2)
        self.window.geometry(f'{width}x{height}+{x}+{y}')
        self.window.deiconify()
        self.window.lift()
        self.window.focus_force()
    
    def check_initial_sync(self):
        if self.connection_monitor.check_connection():
            self.connection_status.config(text="🟢 En línea - Sincronizando...")
            import threading
            def sync_thread():
                try:
                    self.sync_manager.sync_all(id_cliente_moon=self.id_cliente_moon, silent=True)
                    from database import get_session, Usuario
                    session = get_session()
                    count = session.query(Usuario).count()
                    session.close()
                    self.window.after(0, lambda: self.connection_status.config(text=f"🟢 En línea ({count} usuarios)"))
                except:
                    self.window.after(0, lambda: self.connection_status.config(text="🟢 En línea - Error"))
            threading.Thread(target=sync_thread, daemon=True).start()
    
    def manual_sync(self, show_message=True):
        if not self.connection_monitor.check_connection():
            if show_message:
                messagebox.showwarning("Sin conexión", "No hay conexión a internet")
            return
        if show_message:
            messagebox.showinfo("Sincronizando", "Sincronizando datos...")
        import threading
        def sync_thread():
            try:
                self.sync_manager.sync_all(id_cliente_moon=self.id_cliente_moon, silent=False)
                if show_message:
                    self.window.after(0, lambda: messagebox.showinfo("Listo", "Sincronización completada"))
            except Exception as e:
                if show_message:
                    self.window.after(0, lambda: messagebox.showerror("Error", f"Error: {str(e)}"))
        threading.Thread(target=sync_thread, daemon=True).start()
    
    def login(self):
        usuario = self.usuario_entry.get().strip()
        password = self.password_entry.get()
        
        if not usuario or not password:
            messagebox.showwarning("Campos vacíos", "Complete usuario y contraseña")
            return
        
        resultado = self.auth_manager.login(usuario, password)
        
        if not resultado["success"]:
            if self.connection_monitor.check_connection():
                self.manual_sync(show_message=False)
                resultado = self.auth_manager.login(usuario, password)
            
            if not resultado["success"]:
                mensaje = resultado.get("message", "Error en login")
                if resultado.get("bloqueado"):
                    mensaje += f"\n\nSaldo pendiente: ${resultado.get('saldo', 0):.2f}"
                messagebox.showerror("Error de login", mensaje)
                return
        
        if self.connection_monitor.check_connection():
            self.sync_manager.sync_estado_cuenta(self.id_cliente_moon)
        
        estado_cuenta = self.auth_manager.verificar_estado_cuenta_local(self.id_cliente_moon)
        
        if not estado_cuenta["activo"]:
            mensaje = estado_cuenta["mensaje"]
            if estado_cuenta.get("saldo"):
                mensaje += f"\n\nSaldo pendiente: ${estado_cuenta['saldo']:.2f}"
            messagebox.showerror("Acceso bloqueado", mensaje)
            return
        
        self.window.destroy()
        app = POSApp(self.parent, self.auth_manager, self.sync_manager, self.connection_monitor, self.id_cliente_moon)
        app.run()

class POSApp:
    def __init__(self, root, auth_manager, sync_manager, connection_monitor, id_cliente_moon):
        self.root = root
        self.auth_manager = auth_manager
        self.sync_manager = sync_manager
        self.connection_monitor = connection_monitor
        self.id_cliente_moon = id_cliente_moon
        self.productos_carrito = []
        self.total_venta = 0.0
        
        self.setup_ui()
        self.connection_monitor.start_monitoring()
        self.check_account_status()
    
    def check_account_status(self):
        if self.connection_monitor.check_connection():
            self.sync_manager.sync_estado_cuenta(self.id_cliente_moon)
        estado = self.auth_manager.verificar_estado_cuenta_local(self.id_cliente_moon)
        if not estado["activo"]:
            messagebox.showerror("Cuenta bloqueada", f"{estado['mensaje']}\n\nEl sistema se cerrará.")
            self.root.quit()
            return
        self.root.after(config.ACCOUNT_CHECK_INTERVAL * 1000, self.check_account_status)
    
    def setup_ui(self):
        """Interfaz principal funcional y visualmente atractiva"""
        self.root.title("POS Offline Moon")
        self.root.geometry("1600x1000")
        self.root.configure(bg="#ecf0f5")
        
        # Menú superior
        menu_bar = tk.Menu(self.root)
        self.root.config(menu=menu_bar)
        
        menu_archivo = tk.Menu(menu_bar, tearoff=0)
        menu_bar.add_cascade(label="Archivo", menu=menu_archivo)
        menu_archivo.add_command(label="Sincronizar", command=self.manual_sync)
        menu_archivo.add_separator()
        menu_archivo.add_command(label="Salir", command=self.root.quit)
        
        menu_productos = tk.Menu(menu_bar, tearoff=0)
        menu_bar.add_cascade(label="Productos", menu=menu_productos)
        menu_productos.add_command(label="Ver Catálogo", command=self.mostrar_catalogo)
        menu_productos.add_command(label="Recargar", command=lambda: self.cargar_productos())
        
        menu_ventas = tk.Menu(menu_bar, tearoff=0)
        menu_bar.add_cascade(label="Ventas", menu=menu_ventas)
        menu_ventas.add_command(label="Ver Ventas (30 días)", command=self.mostrar_ventas)
        
        menu_ayuda = tk.Menu(menu_bar, tearoff=0)
        menu_bar.add_cascade(label="Ayuda", menu=menu_ayuda)
        menu_ayuda.add_command(label="Atajos: F7=Cobrar, F5=Recargar, F1=Catálogo", command=lambda: None)
        
        # Header
        header = tk.Frame(self.root, bg="#2c3e50", height=60)
        header.pack(fill=tk.X)
        
        tk.Label(header, text="POS | Moon - Sistema Offline", font=("Arial", 18, "bold"),
                bg="#2c3e50", fg="white").pack(side=tk.LEFT, padx=20, pady=15)
        
        self.status_label = tk.Label(header, text="🟢 En línea", bg="#2c3e50", fg="white",
                                     font=("Arial", 11, "bold"))
        self.status_label.pack(side=tk.RIGHT, padx=10, pady=15)
        
        tk.Label(header, text=f"👤 {self.auth_manager.current_user.nombre}", bg="#2c3e50",
                fg="white", font=("Arial", 10)).pack(side=tk.RIGHT, padx=10, pady=15)
        
        # Contenedor principal - 3 columnas
        main_container = tk.Frame(self.root, bg="#ecf0f5")
        main_container.pack(fill=tk.BOTH, expand=True, padx=10, pady=10)
        
        # COLUMNA IZQUIERDA: Búsqueda y lista de productos
        left_col = tk.Frame(main_container, bg="white", relief=tk.RAISED, bd=1)
        left_col.pack(side=tk.LEFT, fill=tk.BOTH, expand=False, padx=(0, 5))
        left_col.config(width=400)
        
        tk.Label(left_col, text="🔍 BUSCAR PRODUCTO", font=("Arial", 14, "bold"),
                bg="white", fg="#2c3e50").pack(pady=15)
        
        search_frame = tk.Frame(left_col, bg="white")
        search_frame.pack(fill=tk.X, padx=15, pady=10)
        
        self.search_var = tk.StringVar()
        search_entry = tk.Entry(search_frame, textvariable=self.search_var, font=("Arial", 13),
                               relief=tk.SOLID, bd=2, bg="#f8f9fa")
        search_entry.pack(fill=tk.X, ipady=10)
        search_entry.focus()
        self.search_var.trace("w", lambda *args: self.buscar_producto())
        
        tk.Button(search_frame, text="📋 Ver Catálogo Completo", bg="#3498db", fg="white",
                 font=("Arial", 10, "bold"), command=self.mostrar_catalogo, relief=tk.FLAT,
                 padx=15, pady=8, cursor="hand2").pack(fill=tk.X, pady=(10, 0))
        
        tk.Label(left_col, text="LISTA DE PRODUCTOS", font=("Arial", 12, "bold"),
                bg="white", fg="#7f8c8d").pack(pady=(15, 5))
        
        productos_frame = tk.Frame(left_col, bg="white")
        productos_frame.pack(fill=tk.BOTH, expand=True, padx=15, pady=(0, 10))
        
        # Treeview de productos
        style = ttk.Style()
        style.theme_use("clam")
        
        self.productos_tree = ttk.Treeview(productos_frame, columns=("codigo", "descripcion", "precio", "stock"),
                                          show="headings", height=22)
        self.productos_tree.heading("codigo", text="Código")
        self.productos_tree.heading("descripcion", text="Descripción")
        self.productos_tree.heading("precio", text="Precio")
        self.productos_tree.heading("stock", text="Stock")
        
        self.productos_tree.column("codigo", width=90, anchor=tk.CENTER)
        self.productos_tree.column("descripcion", width=200)
        self.productos_tree.column("precio", width=90, anchor=tk.CENTER)
        self.productos_tree.column("stock", width=70, anchor=tk.CENTER)
        
        scrollbar_prod = ttk.Scrollbar(productos_frame, orient=tk.VERTICAL, command=self.productos_tree.yview)
        self.productos_tree.configure(yscrollcommand=scrollbar_prod.set)
        
        self.productos_tree.pack(side=tk.LEFT, fill=tk.BOTH, expand=True)
        scrollbar_prod.pack(side=tk.RIGHT, fill=tk.Y)
        
        # Botón Agregar producto
        btn_agregar = tk.Button(left_col, text="➕ Agregar Producto Seleccionado (Enter)", bg="#27ae60", fg="white",
                               font=("Arial", 10, "bold"), command=self.agregar_producto_seleccionado,
                               relief=tk.FLAT, padx=15, pady=8, cursor="hand2")
        btn_agregar.pack(fill=tk.X, padx=15, pady=(0, 15))
        
        # Eventos para productos
        self.productos_tree.bind("<Double-1>", self.agregar_al_carrito)
        self.productos_tree.bind("<Return>", self.agregar_al_carrito)
        self.productos_tree.bind("<space>", self.agregar_al_carrito)
        self.productos_tree.bind("<Up>", self.navegar_productos)
        self.productos_tree.bind("<Down>", self.navegar_productos)
        
        # Enfocar en la lista de productos al cargar
        self.productos_tree.focus_set()
        
        # COLUMNA CENTRAL: Cliente y Carrito de venta
        center_col = tk.Frame(main_container, bg="white", relief=tk.RAISED, bd=1)
        center_col.pack(side=tk.LEFT, fill=tk.BOTH, expand=True, padx=5)
        
        # Sección de cliente
        cliente_frame = tk.Frame(center_col, bg="#f8f9fa", relief=tk.RAISED, bd=1)
        cliente_frame.pack(fill=tk.X, padx=15, pady=(15, 10))
        
        tk.Label(cliente_frame, text="👤 CLIENTE", font=("Arial", 12, "bold"),
                bg="#f8f9fa", fg="#2c3e50").pack(side=tk.LEFT, padx=10, pady=10)
        
        self.cliente_seleccionado = tk.StringVar(value="1-Consumidor Final")
        self.cliente_id = 1  # ID por defecto
        
        cliente_entry_frame = tk.Frame(cliente_frame, bg="#f8f9fa")
        cliente_entry_frame.pack(side=tk.LEFT, fill=tk.X, expand=True, padx=10, pady=10)
        
        self.cliente_entry = tk.Entry(cliente_entry_frame, textvariable=self.cliente_seleccionado,
                                     font=("Arial", 11), relief=tk.SOLID, bd=1, state="readonly")
        self.cliente_entry.pack(side=tk.LEFT, fill=tk.X, expand=True, ipady=5)
        
        tk.Button(cliente_entry_frame, text="🔍 Buscar", bg="#3498db", fg="white",
                 font=("Arial", 9, "bold"), command=self.buscar_cliente, relief=tk.FLAT,
                 padx=15, pady=5, cursor="hand2").pack(side=tk.LEFT, padx=(5, 0))
        
        tk.Label(center_col, text="🛒 CARRITO DE VENTA", font=("Arial", 16, "bold"),
                bg="#667eea", fg="white").pack(fill=tk.X, pady=0, ipady=15)
        
        # Tabla del carrito
        carrito_frame = tk.Frame(center_col, bg="white")
        carrito_frame.pack(fill=tk.BOTH, expand=True, padx=15, pady=15)
        
        self.carrito_tree = ttk.Treeview(carrito_frame, columns=("cantidad", "producto", "precio", "subtotal"),
                                        show="headings", height=20)
        self.carrito_tree.heading("cantidad", text="Cant.")
        self.carrito_tree.heading("producto", text="Producto")
        self.carrito_tree.heading("precio", text="P. Unit.")
        self.carrito_tree.heading("subtotal", text="Subtotal")
        
        self.carrito_tree.column("cantidad", width=80, anchor=tk.CENTER)
        self.carrito_tree.column("producto", width=350)
        self.carrito_tree.column("precio", width=120, anchor=tk.CENTER)
        self.carrito_tree.column("subtotal", width=120, anchor=tk.CENTER)
        
        scrollbar_carrito = ttk.Scrollbar(carrito_frame, orient=tk.VERTICAL, command=self.carrito_tree.yview)
        self.carrito_tree.configure(yscrollcommand=scrollbar_carrito.set)
        
        self.carrito_tree.pack(side=tk.LEFT, fill=tk.BOTH, expand=True)
        scrollbar_carrito.pack(side=tk.RIGHT, fill=tk.Y)
        
        # Botones de acción del carrito
        carrito_actions = tk.Frame(center_col, bg="white")
        carrito_actions.pack(fill=tk.X, padx=15, pady=5)
        
        tk.Button(carrito_actions, text="➕ Aumentar", bg="#27ae60", fg="white",
                 font=("Arial", 10, "bold"), command=self.aumentar_cantidad, relief=tk.FLAT,
                 padx=15, pady=8, cursor="hand2").pack(side=tk.LEFT, padx=5, fill=tk.X, expand=True)
        
        tk.Button(carrito_actions, text="➖ Disminuir", bg="#f39c12", fg="white",
                 font=("Arial", 10, "bold"), command=self.disminuir_cantidad, relief=tk.FLAT,
                 padx=15, pady=8, cursor="hand2").pack(side=tk.LEFT, padx=5, fill=tk.X, expand=True)
        
        tk.Button(carrito_actions, text="🗑️ Eliminar", bg="#e74c3c", fg="white",
                 font=("Arial", 10, "bold"), command=self.eliminar_item, relief=tk.FLAT,
                 padx=15, pady=8, cursor="hand2").pack(side=tk.LEFT, padx=5, fill=tk.X, expand=True)
        
        tk.Button(carrito_actions, text="🗑️ Limpiar Todo", bg="#c0392b", fg="white",
                 font=("Arial", 10, "bold"), command=self.limpiar_carrito, relief=tk.FLAT,
                 padx=15, pady=8, cursor="hand2").pack(side=tk.LEFT, padx=5, fill=tk.X, expand=True)
        
        # Total destacado
        total_frame = tk.Frame(center_col, bg="#34495e", relief=tk.RAISED, bd=2)
        total_frame.pack(fill=tk.X, padx=15, pady=10)
        
        tk.Label(total_frame, text="TOTAL A COBRAR:", font=("Arial", 16, "bold"),
                bg="#34495e", fg="white").pack(side=tk.LEFT, padx=20, pady=15)
        
        self.total_label = tk.Label(total_frame, text="$ 0.00", font=("Arial", 28, "bold"),
                                    fg="#2ecc71", bg="#34495e")
        self.total_label.pack(side=tk.RIGHT, padx=20, pady=15)
        
        # Botón cobrar grande
        btn_cobrar = tk.Button(center_col, text="💳 COBRAR VENTA (F7)", bg="#27ae60", fg="white",
                              font=("Arial", 18, "bold"), command=self.cobrar_venta, relief=tk.FLAT,
                              padx=40, pady=20, cursor="hand2")
        btn_cobrar.pack(fill=tk.X, padx=15, pady=15)
        
        # COLUMNA DERECHA: Acciones, método de pago y resumen
        right_col = tk.Frame(main_container, bg="white", relief=tk.RAISED, bd=1)
        right_col.pack(side=tk.LEFT, fill=tk.BOTH, expand=False, padx=(5, 0))
        right_col.config(width=320)
        
        # Sección: Método de Pago (PRIMERO, más visible)
        pago_header = tk.Frame(right_col, bg="#667eea", height=50)
        pago_header.pack(fill=tk.X, pady=(0, 0))
        
        tk.Label(pago_header, text="💳 MÉTODO DE PAGO", font=("Arial", 14, "bold"),
                bg="#667eea", fg="white").pack(pady=12)
        
        pago_frame = tk.Frame(right_col, bg="white", relief=tk.RAISED, bd=1)
        pago_frame.pack(fill=tk.X, padx=15, pady=15)
        
        self.medio_pago_seleccionado = tk.StringVar(value="Efectivo")
        self.medio_pago = "Efectivo"  # Valor inicial
        
        medios = [
            ("💵 Efectivo", "Efectivo"),
            ("💳 Tarjeta Débito", "TD"),
            ("💳 Tarjeta Crédito", "TC"),
            ("📱 Mercado Pago", "MP"),
            ("🏦 Transferencia", "TR"),
            ("📄 Cheque", "CH"),
            ("📋 Cuenta Corriente", "CC")
        ]
        
        for texto, valor in medios:
            radio = tk.Radiobutton(pago_frame, text=texto, variable=self.medio_pago_seleccionado,
                                  value=valor, font=("Arial", 11), bg="white", padx=15, pady=6,
                                  cursor="hand2", selectcolor="#667eea",
                                  command=lambda v=valor: setattr(self, 'medio_pago', v))
            radio.pack(anchor=tk.W, pady=2)
        
        # Sección: Acciones Rápidas
        tk.Label(right_col, text="⚡ ACCIONES RÁPIDAS", font=("Arial", 14, "bold"),
                bg="#764ba2", fg="white").pack(fill=tk.X, pady=(10, 0), ipady=15)
        
        actions_frame = tk.Frame(right_col, bg="white")
        actions_frame.pack(fill=tk.X, padx=15, pady=15)
        
        tk.Button(actions_frame, text="📊 Ver Ventas\n(Últimos 30 días)", bg="#9b59b6", fg="white",
                 font=("Arial", 11, "bold"), command=self.mostrar_ventas, relief=tk.FLAT,
                 padx=15, pady=12, cursor="hand2", width=25).pack(fill=tk.X, pady=5)
        
        tk.Button(actions_frame, text="🔄 Sincronizar", bg="#3498db", fg="white",
                 font=("Arial", 11, "bold"), command=self.manual_sync, relief=tk.FLAT,
                 padx=15, pady=12, cursor="hand2", width=25).pack(fill=tk.X, pady=5)
        
        tk.Button(actions_frame, text="📋 Catálogo\nCompleto", bg="#16a085", fg="white",
                 font=("Arial", 11, "bold"), command=self.mostrar_catalogo, relief=tk.FLAT,
                 padx=15, pady=12, cursor="hand2", width=25).pack(fill=tk.X, pady=5)
        
        # Resumen de sesión
        resumen_frame = tk.Frame(right_col, bg="#ecf0f5", relief=tk.RAISED, bd=1)
        resumen_frame.pack(fill=tk.X, padx=15, pady=15)
        
        tk.Label(resumen_frame, text="📈 RESUMEN", font=("Arial", 12, "bold"),
                bg="#ecf0f5", fg="#2c3e50").pack(pady=10)
        
        self.resumen_label = tk.Label(resumen_frame, text="Productos: 0\nTotal: $0.00",
                                     font=("Arial", 11), bg="#ecf0f5", fg="#7f8c8d", justify=tk.LEFT)
        self.resumen_label.pack(pady=(0, 10), padx=10)
        
        # Atajos de teclado globales
        self.root.bind('<F7>', lambda e: self.cobrar_venta())
        self.root.bind('<F5>', lambda e: self.cargar_productos())
        self.root.bind('<F1>', lambda e: self.mostrar_catalogo())
        self.root.bind('<Delete>', lambda e: self.eliminar_item())
        self.root.bind('<plus>', lambda e: self.aumentar_cantidad())
        self.root.bind('<minus>', lambda e: self.disminuir_cantidad())
        
        # Navegación por teclado
        search_entry.bind('<Tab>', lambda e: self.focus_next_widget(e))
        search_entry.bind('<Return>', lambda e: self.agregar_producto_seleccionado())
        
        # Enfocar en búsqueda al iniciar
        search_entry.focus_set()
        
        self.cargar_productos()
    
    def on_connection_change(self, is_online):
        if is_online:
            self.status_label.config(text="🟢 En línea", fg="white")
            self.sync_manager.sync_all(id_cliente_moon=self.id_cliente_moon)
            self.sync_manager.sync_estado_cuenta(self.id_cliente_moon)
            estado = self.auth_manager.verificar_estado_cuenta_local(self.id_cliente_moon)
            if not estado["activo"]:
                messagebox.showerror("Cuenta bloqueada", estado["mensaje"])
                self.root.quit()
        else:
            self.status_label.config(text="🔴 Sin conexión", fg="#ffeb3b")
    
    def manual_sync(self):
        if not self.connection_monitor.check_connection():
            messagebox.showwarning("Sin conexión", "No hay conexión a internet")
            return
        messagebox.showinfo("Sincronizando", "Sincronizando datos...")
        self.sync_manager.sync_all(id_cliente_moon=self.id_cliente_moon)
        self.cargar_productos()
        messagebox.showinfo("Listo", "Sincronización completada")
    
    def cargar_clientes(self):
        """Cargar lista de clientes desde el servidor"""
        try:
            if self.connection_monitor.check_connection():
                import requests
                url = f"{config.SERVER_URL}/api/clientes.php"
                params = {'id_cliente': self.id_cliente_moon}
                response = requests.get(url, params=params, timeout=10)
                
                if response.status_code == 200:
                    self.clientes_disponibles = response.json()
                else:
                    self.clientes_disponibles = [{'id': 1, 'display': '1-Consumidor Final'}]
            else:
                self.clientes_disponibles = [{'id': 1, 'display': '1-Consumidor Final'}]
        except:
            self.clientes_disponibles = [{'id': 1, 'display': '1-Consumidor Final'}]
    
    def buscar_cliente(self):
        """Abrir ventana para buscar y seleccionar cliente"""
        ventana_cliente = tk.Toplevel(self.root)
        ventana_cliente.title("🔍 Buscar Cliente")
        ventana_cliente.geometry("600x500")
        ventana_cliente.configure(bg="#ecf0f5")
        ventana_cliente.transient(self.root)
        
        ventana_cliente.update_idletasks()
        x = (ventana_cliente.winfo_screenwidth() // 2) - (600 // 2)
        y = (ventana_cliente.winfo_screenheight() // 2) - (500 // 2)
        ventana_cliente.geometry(f'600x500+{x}+{y}')
        
        header = tk.Frame(ventana_cliente, bg="#667eea", height=50)
        header.pack(fill=tk.X)
        tk.Label(header, text="🔍 Buscar Cliente", font=("Arial", 16, "bold"),
                bg="#667eea", fg="white").pack(pady=12)
        
        search_frame = tk.Frame(ventana_cliente, bg="#ecf0f5")
        search_frame.pack(fill=tk.X, padx=20, pady=15)
        
        search_var_cliente = tk.StringVar()
        search_entry = tk.Entry(search_frame, textvariable=search_var_cliente, font=("Arial", 12),
                               relief=tk.SOLID, bd=1)
        search_entry.pack(side=tk.LEFT, fill=tk.X, expand=True, ipady=8, padx=(0, 10))
        search_entry.focus()
        
        def buscar_cliente():
            filtro = search_var_cliente.get().lower()
            for item in cliente_tree.get_children():
                cliente_tree.delete(item)
            
            for cliente in self.clientes_disponibles:
                display = cliente.get('display', f"{cliente.get('id', '')}-{cliente.get('nombre', '')}")
                if filtro in display.lower() or filtro in cliente.get('documento', '').lower():
                    cliente_tree.insert("", tk.END, values=(
                        cliente.get('id', ''),
                        cliente.get('nombre', 'Sin nombre'),
                        cliente.get('documento', ''),
                        cliente.get('telefono', '')
                    ))
        
        search_entry.bind("<KeyRelease>", lambda e: buscar_cliente())
        tk.Button(search_frame, text="🔍 Buscar", bg="#667eea", fg="white",
                 font=("Arial", 10, "bold"), command=buscar_cliente, relief=tk.FLAT, padx=20, pady=8).pack(side=tk.LEFT)
        
        tree_frame = tk.Frame(ventana_cliente, bg="white")
        tree_frame.pack(fill=tk.BOTH, expand=True, padx=20, pady=(0, 20))
        
        cliente_tree = ttk.Treeview(tree_frame, columns=("id", "nombre", "documento", "telefono"), show="headings")
        cliente_tree.heading("id", text="ID")
        cliente_tree.heading("nombre", text="Nombre")
        cliente_tree.heading("documento", text="Documento")
        cliente_tree.heading("telefono", text="Teléfono")
        
        cliente_tree.column("id", width=60, anchor=tk.CENTER)
        cliente_tree.column("nombre", width=250)
        cliente_tree.column("documento", width=120, anchor=tk.CENTER)
        cliente_tree.column("telefono", width=120)
        
        scrollbar = ttk.Scrollbar(tree_frame, orient=tk.VERTICAL, command=cliente_tree.yview)
        cliente_tree.configure(yscrollcommand=scrollbar.set)
        
        cliente_tree.pack(side=tk.LEFT, fill=tk.BOTH, expand=True)
        scrollbar.pack(side=tk.RIGHT, fill=tk.Y)
        
        # Cargar todos los clientes inicialmente
        for cliente in self.clientes_disponibles:
            cliente_tree.insert("", tk.END, values=(
                cliente.get('id', ''),
                cliente.get('nombre', 'Sin nombre'),
                cliente.get('documento', ''),
                cliente.get('telefono', '')
            ))
        
        def seleccionar_cliente(event):
            selection = cliente_tree.selection()
            if not selection:
                return
            
            item = cliente_tree.item(selection[0])
            cliente_id = int(item['values'][0])
            cliente_nombre = item['values'][1]
            
            self.cliente_id = cliente_id
            self.cliente_seleccionado.set(f"{cliente_id}-{cliente_nombre}")
            ventana_cliente.destroy()
        
        cliente_tree.bind("<Double-1>", seleccionar_cliente)
        cliente_tree.bind("<Return>", seleccionar_cliente)
    
    def cargar_productos(self, filtro=""):
        session = get_session()
        query = session.query(Producto)
        
        if filtro:
            query = query.filter(
                (Producto.descripcion.contains(filtro)) |
                (Producto.codigo.contains(filtro))
            )
        
        productos = query.all()
        
        for item in self.productos_tree.get_children():
            self.productos_tree.delete(item)
        
        for prod in productos:
            self.productos_tree.insert("", tk.END, values=(
                prod.codigo, prod.descripcion, f"${prod.precio_venta:.2f}",
                f"{prod.stock:.0f}" if prod.stock else "0"
            ), tags=(prod.id,))
        
        session.close()
        self.actualizar_resumen()
    
    def buscar_producto(self):
        filtro = self.search_var.get()
        self.cargar_productos(filtro)
    
    def mostrar_catalogo(self):
        """Ventana de catálogo completo"""
        cat_window = tk.Toplevel(self.root)
        cat_window.title("📋 Catálogo de Productos")
        cat_window.geometry("1000x700")
        cat_window.configure(bg="#ecf0f5")
        
        header = tk.Frame(cat_window, bg="#667eea", height=60)
        header.pack(fill=tk.X)
        tk.Label(header, text="📋 Catálogo Completo de Productos", font=("Arial", 18, "bold"),
                bg="#667eea", fg="white").pack(pady=15)
        
        search_frame = tk.Frame(cat_window, bg="#ecf0f5")
        search_frame.pack(fill=tk.X, padx=20, pady=15)
        
        search_var_cat = tk.StringVar()
        search_entry = tk.Entry(search_frame, textvariable=search_var_cat, font=("Arial", 12),
                               relief=tk.SOLID, bd=1)
        search_entry.pack(side=tk.LEFT, fill=tk.X, expand=True, ipady=8, padx=(0, 10))
        search_entry.focus()
        
        def buscar():
            filtro = search_var_cat.get()
            cargar_catalogo(filtro)
        
        search_entry.bind("<KeyRelease>", lambda e: buscar())
        tk.Button(search_frame, text="🔍 Buscar", bg="#667eea", fg="white",
                 font=("Arial", 10, "bold"), command=buscar, relief=tk.FLAT, padx=20, pady=8).pack(side=tk.LEFT)
        
        cat_frame = tk.Frame(cat_window, bg="white")
        cat_frame.pack(fill=tk.BOTH, expand=True, padx=20, pady=(0, 20))
        
        cat_tree = ttk.Treeview(cat_frame, columns=("codigo", "descripcion", "precio", "stock", "categoria"),
                               show="headings")
        cat_tree.heading("codigo", text="Código")
        cat_tree.heading("descripcion", text="Descripción")
        cat_tree.heading("precio", text="Precio Venta")
        cat_tree.heading("stock", text="Stock")
        cat_tree.heading("categoria", text="Categoría")
        
        cat_tree.column("codigo", width=120, anchor=tk.CENTER)
        cat_tree.column("descripcion", width=350)
        cat_tree.column("precio", width=150, anchor=tk.CENTER)
        cat_tree.column("stock", width=100, anchor=tk.CENTER)
        cat_tree.column("categoria", width=150)
        
        scrollbar = ttk.Scrollbar(cat_frame, orient=tk.VERTICAL, command=cat_tree.yview)
        cat_tree.configure(yscrollcommand=scrollbar.set)
        
        cat_tree.pack(side=tk.LEFT, fill=tk.BOTH, expand=True)
        scrollbar.pack(side=tk.RIGHT, fill=tk.Y)
        
        def cargar_catalogo(filtro=""):
            session = get_session()
            query = session.query(Producto)
            if filtro:
                query = query.filter(
                    (Producto.descripcion.contains(filtro)) |
                    (Producto.codigo.contains(filtro)) |
                    (Producto.categoria.contains(filtro) if Producto.categoria else False)
                )
            productos = query.all()
            
            for item in cat_tree.get_children():
                cat_tree.delete(item)
            
            for prod in productos:
                cat_tree.insert("", tk.END, values=(
                    prod.codigo, prod.descripcion, f"${prod.precio_venta:.2f}",
                    f"{prod.stock:.0f}" if prod.stock else "0", prod.categoria or "Sin categoría"
                ))
            
            session.close()
        
        def agregar_desde_catalogo(event):
            selection = cat_tree.selection()
            if not selection:
                return
            item = cat_tree.item(selection[0])
            codigo = item['values'][0]
            
            session = get_session()
            producto = session.query(Producto).filter_by(codigo=codigo).first()
            session.close()
            
            if producto:
                for i, item_carrito in enumerate(self.productos_carrito):
                    if item_carrito['id'] == producto.id:
                        item_carrito['cantidad'] += 1
                        item_carrito['subtotal'] = item_carrito['cantidad'] * item_carrito['precio']
                        self.actualizar_carrito()
                        messagebox.showinfo("Producto agregado", f"Se agregó 1 unidad más de {producto.descripcion}")
                        return
                
                self.productos_carrito.append({
                    'id': producto.id, 'codigo': producto.codigo, 'descripcion': producto.descripcion,
                    'precio': producto.precio_venta, 'cantidad': 1, 'subtotal': producto.precio_venta
                })
                self.actualizar_carrito()
                messagebox.showinfo("Producto agregado", f"{producto.descripcion} agregado al carrito")
        
        cat_tree.bind("<Double-1>", agregar_desde_catalogo)
        cargar_catalogo()
    
    def agregar_producto_seleccionado(self):
        """Agregar producto seleccionado al carrito (puede llamarse desde botón o teclado)"""
        selection = self.productos_tree.selection()
        if not selection:
            # Si no hay selección, seleccionar el primer item
            items = self.productos_tree.get_children()
            if items:
                self.productos_tree.selection_set(items[0])
                self.productos_tree.focus(items[0])
                selection = self.productos_tree.selection()
            else:
                messagebox.showinfo("Sin productos", "No hay productos para agregar")
                return
        
        self.agregar_al_carrito(None)
    
    def agregar_al_carrito(self, event):
        """Agregar producto al carrito desde evento"""
        selection = self.productos_tree.selection()
        if not selection:
            return
        
        item = self.productos_tree.item(selection[0])
        codigo = item['values'][0]
        
        session = get_session()
        producto = session.query(Producto).filter_by(codigo=codigo).first()
        session.close()
        
        if not producto:
            return
        
        for i, item_carrito in enumerate(self.productos_carrito):
            if item_carrito['id'] == producto.id:
                item_carrito['cantidad'] += 1
                item_carrito['subtotal'] = item_carrito['cantidad'] * item_carrito['precio']
                self.actualizar_carrito()
                # Volver a enfocar en la lista de productos
                self.productos_tree.focus_set()
                return
        
        self.productos_carrito.append({
            'id': producto.id, 'codigo': producto.codigo, 'descripcion': producto.descripcion,
            'precio': producto.precio_venta, 'cantidad': 1, 'subtotal': producto.precio_venta,
            'categoria': producto.categoria or '', 'stock': producto.stock or 0,
            'precio_compra': producto.precio_compra or 0
        })
        self.actualizar_carrito()
        # Volver a enfocar en la lista de productos
        self.productos_tree.focus_set()
    
    def navegar_productos(self, event):
        """Navegar por productos con flechas"""
        selection = self.productos_tree.selection()
        items = self.productos_tree.get_children()
        
        if not items:
            return
        
        if not selection:
            self.productos_tree.selection_set(items[0])
            self.productos_tree.focus(items[0])
            return
        
        current_index = items.index(selection[0])
        
        if event.keysym == 'Up' and current_index > 0:
            next_index = current_index - 1
        elif event.keysym == 'Down' and current_index < len(items) - 1:
            next_index = current_index + 1
        else:
            return
        
        self.productos_tree.selection_set(items[next_index])
        self.productos_tree.focus(items[next_index])
        self.productos_tree.see(items[next_index])
    
    def focus_next_widget(self, event):
        """Navegar entre widgets con Tab"""
        widget = event.widget
        if isinstance(widget, tk.Entry):
            if widget == self.search_var:
                # Desde búsqueda, ir a lista de productos
                self.productos_tree.focus_set()
                items = self.productos_tree.get_children()
                if items:
                    self.productos_tree.selection_set(items[0])
                    self.productos_tree.focus(items[0])
                return "break"
    
    def aumentar_cantidad(self):
        selection = self.carrito_tree.selection()
        if not selection:
            messagebox.showwarning("Seleccionar", "Seleccione un producto del carrito")
            return
        
        item = self.carrito_tree.item(selection[0])
        producto_desc = item['values'][1]
        
        for item_carrito in self.productos_carrito:
            if item_carrito['descripcion'] == producto_desc:
                item_carrito['cantidad'] += 1
                item_carrito['subtotal'] = item_carrito['cantidad'] * item_carrito['precio']
                self.actualizar_carrito()
                return
    
    def disminuir_cantidad(self):
        selection = self.carrito_tree.selection()
        if not selection:
            messagebox.showwarning("Seleccionar", "Seleccione un producto del carrito")
            return
        
        item = self.carrito_tree.item(selection[0])
        producto_desc = item['values'][1]
        
        for item_carrito in self.productos_carrito:
            if item_carrito['descripcion'] == producto_desc:
                if item_carrito['cantidad'] > 1:
                    item_carrito['cantidad'] -= 1
                    item_carrito['subtotal'] = item_carrito['cantidad'] * item_carrito['precio']
                    self.actualizar_carrito()
                else:
                    messagebox.showinfo("Cantidad mínima", "La cantidad mínima es 1. Use Eliminar para quitar el producto.")
                return
    
    def eliminar_item(self):
        selection = self.carrito_tree.selection()
        if not selection:
            messagebox.showwarning("Seleccionar", "Seleccione un producto del carrito")
            return
        
        item = self.carrito_tree.item(selection[0])
        producto_desc = item['values'][1]
        
        self.productos_carrito = [p for p in self.productos_carrito if p['descripcion'] != producto_desc]
        self.actualizar_carrito()
    
    def limpiar_carrito(self):
        if not self.productos_carrito:
            return
        if messagebox.askyesno("Limpiar carrito", "¿Está seguro de limpiar todo el carrito?"):
            self.productos_carrito = []
            self.actualizar_carrito()
    
    def actualizar_carrito(self):
        for item in self.carrito_tree.get_children():
            self.carrito_tree.delete(item)
        
        self.total_venta = 0
        for item in self.productos_carrito:
            self.carrito_tree.insert("", tk.END, values=(
                item['cantidad'], item['descripcion'], f"${item['precio']:.2f}", f"${item['subtotal']:.2f}"
            ))
            self.total_venta += item['subtotal']
        
        self.total_label.config(text=f"${self.total_venta:.2f}")
        self.actualizar_resumen()
    
    def actualizar_resumen(self):
        cantidad_productos = sum(item['cantidad'] for item in self.productos_carrito)
        self.resumen_label.config(text=f"Productos: {cantidad_productos}\nTotal: ${self.total_venta:.2f}")
    
    def cobrar_venta(self):
        if not self.productos_carrito:
            messagebox.showwarning("Carrito vacío", "Agregue productos al carrito antes de cobrar")
            return
        
        # Usar el método de pago seleccionado directamente (sin modal)
        self.medio_pago = self.medio_pago_seleccionado.get()
        self.procesar_cobro()
    
    def procesar_cobro(self):
        try:
            # Convertir productos al formato correcto para la base de datos
            productos_json = []
            for item in self.productos_carrito:
                productos_json.append({
                    'id': item.get('id', 0),
                    'id_producto': item.get('id', 0),
                    'codigo': item.get('codigo', ''),
                    'descripcion': item.get('descripcion', ''),
                    'cantidad': item.get('cantidad', 1),
                    'precio': item.get('precio', 0),
                    'precio_venta': item.get('precio', 0),
                    'precio_compra': item.get('precio_compra', 0),
                    'subtotal': item.get('subtotal', 0),
                    'total': item.get('subtotal', 0),
                    'categoria': item.get('categoria', ''),
                    'stock': item.get('stock', 0)
                })
            
            # Obtener nombre del cliente seleccionado
            cliente_nombre = "Consumidor Final"
            for cliente in self.clientes_disponibles:
                if cliente.get('id') == self.cliente_id:
                    cliente_nombre = cliente.get('nombre', 'Consumidor Final')
                    break
            
            session = get_session()
            venta = Venta(
                fecha=datetime.now(),
                cliente=cliente_nombre,
                id_cliente=self.cliente_id,
                productos=productos_json,
                total=self.total_venta,
                metodo_pago=self.medio_pago,
                sincronizado=False,
                creado_local=True
            )
            session.add(venta)
            session.commit()
            venta_id = venta.id
            session.close()
            
            print(f"✅ Venta guardada localmente con ID: {venta_id}")
            print(f"   Productos: {len(productos_json)}, Total: ${self.total_venta:.2f}")
            
            sincronizado = False
            if self.connection_monitor.check_connection():
                print("🔄 Sincronizando venta...")
                sincronizado = self.sync_manager.sync_ventas()
                if sincronizado:
                    print("✅ Venta sincronizada exitosamente")
                else:
                    print("⚠️ Error al sincronizar, se intentará más tarde")
            
            mensaje = f"Venta guardada exitosamente.\n\nTotal: ${self.total_venta:.2f}\n"
            mensaje += f"Medio de pago: {self.medio_pago}\n"
            if sincronizado:
                mensaje += "\n✅ Venta sincronizada con el servidor."
            else:
                mensaje += "\n⏳ La venta se sincronizará cuando haya conexión."
            
            messagebox.showinfo("Venta registrada", mensaje)
            
            self.productos_carrito = []
            self.actualizar_carrito()
            # Volver a enfocar en búsqueda
            for widget in self.root.winfo_children():
                if isinstance(widget, tk.Frame):
                    for child in widget.winfo_children():
                        if isinstance(child, tk.Frame):
                            for entry in child.winfo_children():
                                if isinstance(entry, tk.Entry):
                                    entry.focus_set()
                                    break
            
        except Exception as e:
            print(f"❌ Error al guardar venta: {e}")
            import traceback
            traceback.print_exc()
            messagebox.showerror("Error", f"Error al guardar la venta: {str(e)}")
    
    def mostrar_ventas(self):
        """Muestra ventas de últimos 30 días"""
        ventana = tk.Toplevel(self.root)
        ventana.title("📊 Ventas - Últimos 30 días")
        ventana.geometry("1200x700")
        ventana.configure(bg="#ecf0f5")
        
        header = tk.Frame(ventana, bg="#667eea", height=60)
        header.pack(fill=tk.X)
        tk.Label(header, text="📊 Ventas - Últimos 30 días", font=("Arial", 18, "bold"),
                bg="#667eea", fg="white").pack(pady=15)
        
        ventas_data = []
        try:
            # Intentar obtener ventas del servidor si hay conexión
            if self.connection_monitor.check_connection():
                print("🔄 Obteniendo ventas del servidor...")
                ventas_servidor = self.sync_manager.sync_ventas_historial(30)
                if ventas_servidor and isinstance(ventas_servidor, list):
                    ventas_data = ventas_servidor
                    print(f"✅ Obtenidas {len(ventas_data)} ventas del servidor")
                else:
                    print("⚠️ No se obtuvieron ventas del servidor, usando locales")
                    ventas_data = []
            
            # Si no hay datos del servidor o no hay conexión, usar ventas locales
            if not ventas_data:
                print("🔄 Obteniendo ventas locales...")
                session = get_session()
                fecha_desde = datetime.now() - timedelta(days=30)
                ventas = session.query(Venta).filter(Venta.fecha >= fecha_desde).order_by(Venta.fecha.desc()).all()
                ventas_data = [{
                    'fecha': v.fecha.isoformat() if hasattr(v.fecha, 'isoformat') else str(v.fecha),
                    'total': float(v.total),
                    'cliente': v.cliente or 'Consumidor Final',
                    'metodo_pago': v.metodo_pago or 'Efectivo',
                    'sincronizado': '✅' if v.sincronizado else '⏳ Pendiente'
                } for v in ventas]
                session.close()
                print(f"✅ Obtenidas {len(ventas_data)} ventas locales")
        except Exception as e:
            print(f"❌ Error obteniendo ventas: {e}")
            import traceback
            traceback.print_exc()
            try:
                # Fallback a ventas locales
                session = get_session()
                fecha_desde = datetime.now() - timedelta(days=30)
                ventas = session.query(Venta).filter(Venta.fecha >= fecha_desde).order_by(Venta.fecha.desc()).all()
                ventas_data = [{
                    'fecha': v.fecha.isoformat() if hasattr(v.fecha, 'isoformat') else str(v.fecha),
                    'total': float(v.total),
                    'cliente': v.cliente or 'Consumidor Final',
                    'metodo_pago': v.metodo_pago or 'Efectivo',
                    'sincronizado': '✅' if v.sincronizado else '⏳ Pendiente'
                } for v in ventas]
                session.close()
            except Exception as e2:
                print(f"❌ Error en fallback: {e2}")
                ventas_data = []
        
        resumen_frame = tk.Frame(ventana, bg="white", relief=tk.RAISED, bd=1)
        resumen_frame.pack(fill=tk.X, padx=20, pady=15)
        
        total_ventas = sum(v.get('total', 0) for v in ventas_data)
        cantidad = len(ventas_data)
        
        tk.Label(resumen_frame, text=f"Total de ventas: {cantidad}", font=("Arial", 12, "bold"),
                bg="white", fg="#2c3e50").pack(side=tk.LEFT, padx=20, pady=15)
        tk.Label(resumen_frame, text=f"Total recaudado: ${total_ventas:.2f}", font=("Arial", 14, "bold"),
                bg="white", fg="#667eea").pack(side=tk.RIGHT, padx=20, pady=15)
        
        tree_frame = tk.Frame(ventana, bg="white")
        tree_frame.pack(fill=tk.BOTH, expand=True, padx=20, pady=(0, 20))
        
        tree = ttk.Treeview(tree_frame, columns=("fecha", "cliente", "metodo_pago", "total", "estado"), show="headings")
        tree.heading("fecha", text="Fecha y Hora")
        tree.heading("cliente", text="Cliente")
        tree.heading("metodo_pago", text="Medio de Pago")
        tree.heading("total", text="Total")
        tree.heading("estado", text="Estado")
        
        tree.column("fecha", width=180)
        tree.column("cliente", width=250)
        tree.column("metodo_pago", width=150, anchor=tk.CENTER)
        tree.column("total", width=150, anchor=tk.CENTER)
        tree.column("estado", width=120, anchor=tk.CENTER)
        
        scrollbar = ttk.Scrollbar(tree_frame, orient=tk.VERTICAL, command=tree.yview)
        tree.configure(yscrollcommand=scrollbar.set)
        
        tree.pack(side=tk.LEFT, fill=tk.BOTH, expand=True)
        scrollbar.pack(side=tk.RIGHT, fill=tk.Y)
        
        if ventas_data:
            metodos = {'Efectivo': '💵 Efectivo', 'TD': '💳 Débito', 'TC': '💳 Crédito',
                      'MP': '📱 Mercado Pago', 'TR': '🏦 Transferencia', 'CH': '📄 Cheque',
                      'CC': '📋 Cta. Cte.'}
            
            for venta in ventas_data:
                fecha_str = venta.get('fecha', '')
                try:
                    if 'T' in fecha_str:
                        fecha_obj = datetime.fromisoformat(fecha_str.replace('Z', '+00:00'))
                    else:
                        fecha_obj = datetime.fromisoformat(fecha_str)
                    fecha_formateada = fecha_obj.strftime('%d/%m/%Y %H:%M')
                except:
                    fecha_formateada = fecha_str[:19] if len(fecha_str) > 19 else fecha_str
                
                metodo = venta.get('metodo_pago', 'Efectivo')
                metodo_display = metodos.get(metodo, metodo)
                
                tree.insert("", tk.END, values=(
                    fecha_formateada, venta.get('cliente', 'Consumidor Final'),
                    metodo_display, f"${venta.get('total', 0):.2f}",
                    venta.get('sincronizado', '✅')
                ))
        else:
            tree.insert("", tk.END, values=("No hay ventas", "en los últimos 30 días", "", "", ""))
    
    def run(self):
        self.root.deiconify()
        self.root.mainloop()
