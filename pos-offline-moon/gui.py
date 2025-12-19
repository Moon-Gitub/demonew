#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
INTERFAZ GRÁFICA MODERNA
Interfaz mejorada con tkinter para el sistema POS offline
"""

import tkinter as tk
from tkinter import ttk, messagebox
from datetime import datetime, timedelta
from database import get_session, Producto, Venta
from sync import SyncManager
from connection import ConnectionMonitor
from auth import AuthManager
from config import config

class LoginWindow:
    def __init__(self, parent):
        print("LoginWindow: Inicializando...")
        self.parent = parent
        try:
            self.auth_manager = AuthManager()
            print("LoginWindow: AuthManager creado")
            self.sync_manager = SyncManager()
            print("LoginWindow: SyncManager creado")
            self.connection_monitor = ConnectionMonitor()
            print("LoginWindow: ConnectionMonitor creado")
            self.id_cliente_moon = config.ID_CLIENTE_MOON
            print(f"LoginWindow: ID Cliente Moon = {self.id_cliente_moon}")
            
            self.setup_login_ui()
            print("LoginWindow: UI configurada")
            # Ejecutar sincronización después de que la ventana se muestre
            self.window.after(500, self.check_initial_sync)
            print("LoginWindow: Sincronización programada")
        except Exception as e:
            print(f"❌ Error en LoginWindow.__init__: {e}")
            import traceback
            traceback.print_exc()
            raise
    
    def setup_login_ui(self):
        print("setup_login_ui: Creando ventana...")
        try:
            self.window = tk.Toplevel(self.parent)
            print("setup_login_ui: Toplevel creado")
            self.window.title("POS Offline Moon - Login")
            self.window.configure(bg="#667eea")
            self.window.resizable(False, False)
            
            # Centrar ventana
            self.window.transient(self.parent)
            self.window.grab_set()
            
            print("setup_login_ui: Configurando widgets...")
            
            # Frame principal
            main_frame = tk.Frame(self.window, bg="#667eea")
            main_frame.pack(fill=tk.BOTH, expand=True, padx=40, pady=40)
            
            # Logo/Título
            title = tk.Label(
                main_frame,
                text="POS | Moon",
                font=("Arial", 28, "bold"),
                bg="#667eea",
                fg="white"
            )
            title.pack(pady=(0, 10))
            
            subtitle = tk.Label(
                main_frame,
                text="Sistema Offline",
                font=("Arial", 12),
                bg="#667eea",
                fg="white"
            )
            subtitle.pack(pady=(0, 30))
            
            # Frame de login
            login_frame = tk.Frame(main_frame, bg="white", relief=tk.RAISED, bd=2)
            login_frame.pack(fill=tk.BOTH, expand=True)
            
            tk.Label(
                login_frame,
                text="Iniciar Sesión",
                font=("Arial", 16, "bold"),
                bg="white"
            ).pack(pady=20)
            
            # Usuario
            tk.Label(login_frame, text="Usuario:", bg="white", anchor="w", font=("Arial", 10)).pack(fill=tk.X, padx=20, pady=(10, 5))
            self.usuario_entry = tk.Entry(login_frame, font=("Arial", 12), width=30, relief=tk.SOLID, bd=1)
            self.usuario_entry.pack(padx=20, pady=(0, 10))
            self.usuario_entry.focus()
            
            # Contraseña
            tk.Label(login_frame, text="Contraseña:", bg="white", anchor="w", font=("Arial", 10)).pack(fill=tk.X, padx=20, pady=(10, 5))
            self.password_entry = tk.Entry(login_frame, font=("Arial", 12), show="*", width=30, relief=tk.SOLID, bd=1)
            self.password_entry.pack(padx=20, pady=(0, 20))
            self.password_entry.bind("<Return>", lambda e: self.login())
            
            # Estado de conexión
            self.connection_status = tk.Label(
                login_frame,
                text="🟢 En línea" if self.connection_monitor.check_connection() else "🔴 Sin conexión",
                bg="white",
                fg="#666",
                font=("Arial", 9)
            )
            self.connection_status.pack(pady=5)
            
            # Botón login
            btn_login = tk.Button(
                login_frame,
                text="Ingresar",
                bg="#667eea",
                fg="white",
                font=("Arial", 12, "bold"),
                command=self.login,
                relief=tk.FLAT,
                padx=30,
                pady=10,
                cursor="hand2"
            )
            btn_login.pack(pady=20)
            
            # Botón sincronizar
            btn_sync = tk.Button(
                login_frame,
                text="🔄 Sincronizar",
                bg="#764ba2",
                fg="white",
                font=("Arial", 10),
                command=self.manual_sync,
                relief=tk.FLAT,
                padx=20,
                pady=5,
                cursor="hand2"
            )
            btn_sync.pack(pady=5)
            
            print("setup_login_ui: Widgets creados")
            
            # Forzar actualización para calcular tamaño real
            self.window.update_idletasks()
            self.window.update()
            
            # Obtener tamaño real después de que los widgets se rendericen
            width = self.window.winfo_reqwidth()
            height = self.window.winfo_reqheight()
            
            # Si el tamaño es muy pequeño, usar tamaño fijo
            if width < 400 or height < 500:
                width = 450
                height = 550
            
            # Centrar en pantalla
            screen_width = self.window.winfo_screenwidth()
            screen_height = self.window.winfo_screenheight()
            x = (screen_width // 2) - (width // 2)
            y = (screen_height // 2) - (height // 2)
            
            # Establecer tamaño y posición
            self.window.geometry(f'{width}x{height}+{x}+{y}')
            
            # Forzar que la ventana se muestre
            self.window.deiconify()
            self.window.lift()
            self.window.focus_force()
            
            # Asegurar que esté visible
            self.window.update()
            self.window.update_idletasks()
            
            print(f"setup_login_ui: Ventana mostrada - Tamaño: {width}x{height}, Posición: {x},{y}")
        except Exception as e:
            print(f"❌ Error en setup_login_ui: {e}")
            import traceback
            traceback.print_exc()
            raise
    
    def check_initial_sync(self):
        """Sincroniza al iniciar si hay conexión (en segundo plano)"""
        if self.connection_monitor.check_connection():
            # Actualizar estado mientras sincroniza
            self.connection_status.config(text="🟢 En línea - Sincronizando...")
            self.window.update()
            
            # Ejecutar sincronización en un hilo separado para no bloquear UI
            import threading
            def sync_thread():
                try:
                    print("🔄 Iniciando sincronización inicial...")
                    # Sincronizar (con prints para debug)
                    self.sync_manager.sync_all(id_cliente_moon=self.id_cliente_moon, silent=False)
                    
                    # Verificar que se guardaron usuarios
                    from database import get_session, Usuario
                    session = get_session()
                    count = session.query(Usuario).count()
                    session.close()
                    print(f"✅ Usuarios en base local después de sync: {count}")
                    
                    # Actualizar estado de conexión en la UI
                    self.window.after(0, lambda: self.connection_status.config(
                        text=f"🟢 En línea - Sincronizado ({count} usuarios)"
                    ))
                except Exception as e:
                    print(f"❌ Error en sincronización inicial: {e}")
                    import traceback
                    traceback.print_exc()
                    self.window.after(0, lambda: self.connection_status.config(
                        text="🟢 En línea - Error en sync"
                    ))
            
            thread = threading.Thread(target=sync_thread, daemon=True)
            thread.start()
        else:
            print("⚠️  Sin conexión, no se puede sincronizar")
    
    def manual_sync(self, show_message=True):
        """Sincronización manual"""
        if not self.connection_monitor.check_connection():
            if show_message:
                messagebox.showwarning("Sin conexión", "No hay conexión a internet")
            return
        
        if show_message:
            messagebox.showinfo("Sincronizando", "Sincronizando datos...")
        
        # Ejecutar sincronización en hilo separado
        import threading
        def sync_thread():
            try:
                print("🔄 Sincronización manual iniciada...")
                self.sync_manager.sync_all(id_cliente_moon=self.id_cliente_moon, silent=False)
                
                # Verificar usuarios después de sync
                from database import get_session, Usuario
                session = get_session()
                count = session.query(Usuario).count()
                usuarios = session.query(Usuario).all()
                session.close()
                
                print(f"✅ Usuarios después de sync: {count}")
                for u in usuarios:
                    print(f"  - {u.usuario} (Estado: {u.estado})")
                
                if show_message:
                    msg = f"Sincronización completada.\n{count} usuario(s) disponible(s)."
                    self.window.after(0, lambda: messagebox.showinfo("Listo", msg))
            except Exception as e:
                error_msg = f"Error en sincronización: {str(e)}"
                print(error_msg)
                import traceback
                traceback.print_exc()
                if show_message:
                    self.window.after(0, lambda: messagebox.showerror("Error", error_msg))
        
        thread = threading.Thread(target=sync_thread, daemon=True)
        thread.start()
    
    def login(self):
        """Procesa el login"""
        usuario = self.usuario_entry.get().strip()
        password = self.password_entry.get()
        
        if not usuario or not password:
            messagebox.showwarning("Campos vacíos", "Complete usuario y contraseña")
            return
        
        # Intentar login local primero
        resultado = self.auth_manager.login(usuario, password)
        
        if not resultado["success"]:
            # Si falla local, intentar sincronizar y volver a intentar
            if self.connection_monitor.check_connection():
                self.manual_sync(show_message=False)
                resultado = self.auth_manager.login(usuario, password)
            
            if not resultado["success"]:
                mensaje = resultado.get("message", "Error en login")
                if resultado.get("bloqueado"):
                    mensaje += f"\n\nSaldo pendiente: ${resultado.get('saldo', 0):.2f}"
                messagebox.showerror("Error de login", mensaje)
                return
        
        # Verificar estado de cuenta (si hay conexión o desde cache)
        if self.connection_monitor.check_connection():
            self.sync_manager.sync_estado_cuenta(self.id_cliente_moon)
        
        estado_cuenta = self.auth_manager.verificar_estado_cuenta_local(self.id_cliente_moon)
        
        if not estado_cuenta["activo"]:
            mensaje = estado_cuenta["mensaje"]
            if estado_cuenta.get("saldo"):
                mensaje += f"\n\nSaldo pendiente: ${estado_cuenta['saldo']:.2f}"
            messagebox.showerror("Acceso bloqueado", mensaje)
            return
        
        # Login exitoso - cerrar ventana de login y abrir aplicación
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
        
        # Variables
        self.productos_carrito = []
        self.total_venta = 0.0
        
        self.setup_ui()
        self.connection_monitor.start_monitoring()
        
        # Verificar estado de cuenta periódicamente
        self.check_account_status()
    
    def check_account_status(self):
        """Verifica estado de cuenta periódicamente"""
        if self.connection_monitor.check_connection():
            self.sync_manager.sync_estado_cuenta(self.id_cliente_moon)
        
        estado = self.auth_manager.verificar_estado_cuenta_local(self.id_cliente_moon)
        
        if not estado["activo"]:
            messagebox.showerror(
                "Cuenta bloqueada",
                f"{estado['mensaje']}\n\nEl sistema se cerrará."
            )
            self.root.quit()
            return
        
        # Verificar cada 5 minutos
        self.root.after(config.ACCOUNT_CHECK_INTERVAL * 1000, self.check_account_status)
    
    def setup_ui(self):
        """Configura la interfaz principal con diseño moderno"""
        self.root.title("POS Offline Moon - Sistema de Ventas")
        self.root.geometry("1400x900")
        self.root.configure(bg="#f0f2f5")
        
        # Header moderno con gradiente
        header = tk.Frame(self.root, bg="#667eea", height=70)
        header.pack(fill=tk.X)
        
        # Título en header
        title_frame = tk.Frame(header, bg="#667eea")
        title_frame.pack(side=tk.LEFT, padx=20, pady=15)
        
        tk.Label(
            title_frame,
            text="POS | Moon",
            font=("Arial", 20, "bold"),
            bg="#667eea",
            fg="white"
        ).pack(side=tk.LEFT)
        
        tk.Label(
            title_frame,
            text="Sistema Offline",
            font=("Arial", 10),
            bg="#667eea",
            fg="rgba(255,255,255,0.9)"
        ).pack(side=tk.LEFT, padx=(10, 0))
        
        # Estado de conexión y usuario
        status_frame = tk.Frame(header, bg="#667eea")
        status_frame.pack(side=tk.RIGHT, padx=20, pady=15)
        
        self.status_label = tk.Label(
            status_frame,
            text="🟢 En línea",
            bg="#667eea",
            fg="white",
            font=("Arial", 11, "bold")
        )
        self.status_label.pack(side=tk.LEFT, padx=5)
        
        user_label = tk.Label(
            status_frame,
            text=f"👤 {self.auth_manager.current_user.nombre}",
            bg="#667eea",
            fg="white",
            font=("Arial", 10)
        )
        user_label.pack(side=tk.LEFT, padx=10)
        
        btn_sync = tk.Button(
            status_frame,
            text="🔄 Sincronizar",
            bg="#764ba2",
            fg="white",
            font=("Arial", 9, "bold"),
            command=self.manual_sync,
            relief=tk.FLAT,
            padx=15,
            pady=5,
            cursor="hand2"
        )
        btn_sync.pack(side=tk.LEFT, padx=5)
        
        # Contenedor principal con layout mejorado
        main_container = tk.Frame(self.root, bg="#f0f2f5")
        main_container.pack(fill=tk.BOTH, expand=True, padx=15, pady=15)
        
        # Panel izquierdo - Carrito (más ancho)
        left_panel = tk.Frame(main_container, bg="white", relief=tk.FLAT, bd=0)
        left_panel.pack(side=tk.LEFT, fill=tk.BOTH, expand=True, padx=(0, 10))
        
        # Título del carrito con estilo
        carrito_header = tk.Frame(left_panel, bg="#667eea", height=50)
        carrito_header.pack(fill=tk.X)
        
        tk.Label(
            carrito_header,
            text="🛒 Carrito de Venta",
            font=("Arial", 16, "bold"),
            bg="#667eea",
            fg="white"
        ).pack(side=tk.LEFT, padx=15, pady=12)
        
        # Tabla de productos en carrito con mejor estilo
        carrito_frame = tk.Frame(left_panel, bg="white")
        carrito_frame.pack(fill=tk.BOTH, expand=True, padx=15, pady=15)
        
        # Estilo para el treeview
        style = ttk.Style()
        style.theme_use("clam")
        style.configure("Carrito.Treeview", 
                        background="white",
                        foreground="#2c3e50",
                        fieldbackground="white",
                        rowheight=30,
                        font=("Arial", 11))
        style.configure("Carrito.Treeview.Heading", 
                        background="#667eea",
                        foreground="white",
                        font=("Arial", 11, "bold"))
        
        self.carrito_tree = ttk.Treeview(
            carrito_frame,
            columns=("cantidad", "producto", "precio", "subtotal"),
            show="headings",
            height=18,
            style="Carrito.Treeview"
        )
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
        carrito_actions = tk.Frame(left_panel, bg="white")
        carrito_actions.pack(fill=tk.X, padx=15, pady=5)
        
        btn_limpiar = tk.Button(
            carrito_actions,
            text="🗑️ Limpiar",
            bg="#e74c3c",
            fg="white",
            font=("Arial", 10, "bold"),
            command=self.limpiar_carrito,
            relief=tk.FLAT,
            padx=20,
            pady=8,
            cursor="hand2"
        )
        btn_limpiar.pack(side=tk.LEFT, padx=5)
        
        # Total con diseño destacado
        total_frame = tk.Frame(left_panel, bg="#f8f9fa", relief=tk.RAISED, bd=1)
        total_frame.pack(fill=tk.X, padx=15, pady=10)
        
        tk.Label(
            total_frame,
            text="TOTAL A COBRAR:",
            font=("Arial", 16, "bold"),
            bg="#f8f9fa",
            fg="#2c3e50"
        ).pack(side=tk.LEFT, padx=15, pady=12)
        
        self.total_label = tk.Label(
            total_frame,
            text="$ 0.00",
            font=("Arial", 24, "bold"),
            fg="#667eea",
            bg="#f8f9fa"
        )
        self.total_label.pack(side=tk.LEFT, padx=10)
        
        # Botón cobrar destacado
        btn_cobrar = tk.Button(
            left_panel,
            text="💳 COBRAR VENTA (F7)",
            bg="#27ae60",
            fg="white",
            font=("Arial", 16, "bold"),
            command=self.cobrar_venta,
            relief=tk.FLAT,
            padx=40,
            pady=18,
            cursor="hand2"
        )
        btn_cobrar.pack(fill=tk.X, padx=15, pady=15)
        
        # Panel derecho - Productos y menú
        right_panel = tk.Frame(main_container, bg="white", relief=tk.FLAT, bd=0)
        right_panel.pack(side=tk.RIGHT, fill=tk.BOTH, padx=(10, 0))
        
        # Título del panel de productos
        productos_header = tk.Frame(right_panel, bg="#764ba2", height=50)
        productos_header.pack(fill=tk.X)
        
        tk.Label(
            productos_header,
            text="📦 Productos",
            font=("Arial", 16, "bold"),
            bg="#764ba2",
            fg="white"
        ).pack(side=tk.LEFT, padx=15, pady=12)
        
        # Búsqueda mejorada
        search_container = tk.Frame(right_panel, bg="white")
        search_container.pack(fill=tk.X, padx=15, pady=15)
        
        search_label = tk.Label(
            search_container,
            text="🔍 Buscar:",
            font=("Arial", 11, "bold"),
            bg="white",
            fg="#2c3e50"
        )
        search_label.pack(side=tk.LEFT, padx=(0, 10))
        
        self.search_var = tk.StringVar()
        self.search_var.trace("w", lambda *args: self.buscar_producto())
        
        search_entry = tk.Entry(
            search_container,
            textvariable=self.search_var,
            font=("Arial", 12),
            relief=tk.SOLID,
            bd=1,
            bg="#f8f9fa"
        )
        search_entry.pack(side=tk.LEFT, fill=tk.X, expand=True, ipady=8)
        search_entry.focus()
        
        # Botón catálogo
        btn_catalogo = tk.Button(
            search_container,
            text="📋 Ver Catálogo",
            bg="#3498db",
            fg="white",
            font=("Arial", 10, "bold"),
            command=self.mostrar_catalogo,
            relief=tk.FLAT,
            padx=15,
            pady=8,
            cursor="hand2"
        )
        btn_catalogo.pack(side=tk.LEFT, padx=(10, 0))
        
        # Lista de productos con mejor estilo
        productos_frame = tk.Frame(right_panel, bg="white")
        productos_frame.pack(fill=tk.BOTH, expand=True, padx=15, pady=(0, 15))
        
        style.configure("Productos.Treeview",
                       background="white",
                       foreground="#2c3e50",
                       fieldbackground="white",
                       rowheight=35,
                       font=("Arial", 10))
        style.configure("Productos.Treeview.Heading",
                       background="#764ba2",
                       foreground="white",
                       font=("Arial", 10, "bold"))
        
        self.productos_tree = ttk.Treeview(
            productos_frame,
            columns=("codigo", "descripcion", "precio", "stock"),
            show="headings",
            height=22,
            style="Productos.Treeview"
        )
        self.productos_tree.heading("codigo", text="Código")
        self.productos_tree.heading("descripcion", text="Descripción")
        self.productos_tree.heading("precio", text="Precio")
        self.productos_tree.heading("stock", text="Stock")
        
        self.productos_tree.column("codigo", width=100, anchor=tk.CENTER)
        self.productos_tree.column("descripcion", width=250)
        self.productos_tree.column("precio", width=120, anchor=tk.CENTER)
        self.productos_tree.column("stock", width=80, anchor=tk.CENTER)
        
        scrollbar_productos = ttk.Scrollbar(productos_frame, orient=tk.VERTICAL, command=self.productos_tree.yview)
        self.productos_tree.configure(yscrollcommand=scrollbar_productos.set)
        
        self.productos_tree.pack(side=tk.LEFT, fill=tk.BOTH, expand=True)
        scrollbar_productos.pack(side=tk.RIGHT, fill=tk.Y)
        
        self.productos_tree.bind("<Double-1>", self.agregar_al_carrito)
        self.productos_tree.bind("<Return>", self.agregar_al_carrito)
        
        # Botones de acción en panel derecho
        acciones_frame = tk.Frame(right_panel, bg="white")
        acciones_frame.pack(fill=tk.X, padx=15, pady=(0, 15))
        
        btn_ventas = tk.Button(
            acciones_frame,
            text="📊 Ventas (30 días)",
            bg="#9b59b6",
            fg="white",
            font=("Arial", 11, "bold"),
            command=self.mostrar_ventas,
            relief=tk.FLAT,
            padx=20,
            pady=10,
            cursor="hand2"
        )
        btn_ventas.pack(side=tk.LEFT, fill=tk.X, expand=True, padx=5)
        
        # Atajos de teclado
        self.root.bind('<F7>', lambda e: self.cobrar_venta())
        self.root.bind('<F5>', lambda e: self.cargar_productos())
        self.root.bind('<F1>', lambda e: self.mostrar_catalogo())
        
        # Cargar productos
        self.cargar_productos()
    
    def on_connection_change(self, is_online):
        """Actualiza UI cuando cambia conexión"""
        if is_online:
            self.status_label.config(text="🟢 En línea", fg="white")
            self.sync_manager.sync_all(id_cliente_moon=self.id_cliente_moon)
            # Verificar estado de cuenta cuando vuelve conexión
            self.sync_manager.sync_estado_cuenta(self.id_cliente_moon)
            estado = self.auth_manager.verificar_estado_cuenta_local(self.id_cliente_moon)
            if not estado["activo"]:
                messagebox.showerror("Cuenta bloqueada", estado["mensaje"])
                self.root.quit()
        else:
            self.status_label.config(text="🔴 Sin conexión - Modo offline", fg="#ffeb3b")
    
    def manual_sync(self):
        """Sincronización manual"""
        if not self.connection_monitor.check_connection():
            messagebox.showwarning("Sin conexión", "No hay conexión a internet")
            return
        
        messagebox.showinfo("Sincronizando", "Sincronizando datos...")
        self.sync_manager.sync_all(id_cliente_moon=self.id_cliente_moon)
        self.cargar_productos()
        messagebox.showinfo("Listo", "Sincronización completada")
    
    def cargar_productos(self, filtro=""):
        """Carga productos desde base local"""
        session = get_session()
        query = session.query(Producto)
        
        if filtro:
            query = query.filter(
                (Producto.descripcion.contains(filtro)) |
                (Producto.codigo.contains(filtro))
            )
        
        productos = query.all()
        
        # Limpiar tree
        for item in self.productos_tree.get_children():
            self.productos_tree.delete(item)
        
        # Agregar productos
        for prod in productos:
            self.productos_tree.insert(
                "",
                tk.END,
                values=(
                    prod.codigo,
                    prod.descripcion,
                    f"${prod.precio_venta:.2f}",
                    f"{prod.stock:.0f}" if prod.stock else "0"
                ),
                tags=(prod.id,)
            )
        
        session.close()
    
    def buscar_producto(self):
        """Filtra productos según búsqueda"""
        filtro = self.search_var.get()
        self.cargar_productos(filtro)
    
    def mostrar_catalogo(self):
        """Muestra catálogo completo de productos con precios"""
        ventana_catalogo = tk.Toplevel(self.root)
        ventana_catalogo.title("📋 Catálogo de Productos")
        ventana_catalogo.geometry("900x700")
        ventana_catalogo.configure(bg="#f0f2f5")
        
        # Header
        header_cat = tk.Frame(ventana_catalogo, bg="#667eea", height=60)
        header_cat.pack(fill=tk.X)
        
        tk.Label(
            header_cat,
            text="📋 Catálogo Completo de Productos",
            font=("Arial", 18, "bold"),
            bg="#667eea",
            fg="white"
        ).pack(pady=15)
        
        # Búsqueda en catálogo
        search_frame = tk.Frame(ventana_catalogo, bg="#f0f2f5")
        search_frame.pack(fill=tk.X, padx=20, pady=15)
        
        search_var_cat = tk.StringVar()
        search_entry_cat = tk.Entry(
            search_frame,
            textvariable=search_var_cat,
            font=("Arial", 12),
            relief=tk.SOLID,
            bd=1
        )
        search_entry_cat.pack(side=tk.LEFT, fill=tk.X, expand=True, ipady=8, padx=(0, 10))
        search_entry_cat.focus()
        
        def buscar_catalogo():
            filtro = search_var_cat.get()
            cargar_catalogo(filtro)
        
        search_entry_cat.bind("<KeyRelease>", lambda e: buscar_catalogo())
        
        btn_buscar = tk.Button(
            search_frame,
            text="🔍 Buscar",
            bg="#667eea",
            fg="white",
            font=("Arial", 10, "bold"),
            command=buscar_catalogo,
            relief=tk.FLAT,
            padx=20,
            pady=8
        )
        btn_buscar.pack(side=tk.LEFT)
        
        # Tabla de catálogo
        cat_frame = tk.Frame(ventana_catalogo, bg="white")
        cat_frame.pack(fill=tk.BOTH, expand=True, padx=20, pady=(0, 20))
        
        style = ttk.Style()
        style.configure("Catalogo.Treeview",
                       background="white",
                       rowheight=40,
                       font=("Arial", 11))
        style.configure("Catalogo.Treeview.Heading",
                       background="#667eea",
                       foreground="white",
                       font=("Arial", 11, "bold"))
        
        cat_tree = ttk.Treeview(
            cat_frame,
            columns=("codigo", "descripcion", "precio", "stock", "categoria"),
            show="headings",
            style="Catalogo.Treeview"
        )
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
        
        scrollbar_cat = ttk.Scrollbar(cat_frame, orient=tk.VERTICAL, command=cat_tree.yview)
        cat_tree.configure(yscrollcommand=scrollbar_cat.set)
        
        cat_tree.pack(side=tk.LEFT, fill=tk.BOTH, expand=True)
        scrollbar_cat.pack(side=tk.RIGHT, fill=tk.Y)
        
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
            
            # Limpiar
            for item in cat_tree.get_children():
                cat_tree.delete(item)
            
            # Agregar productos
            for prod in productos:
                cat_tree.insert(
                    "",
                    tk.END,
                    values=(
                        prod.codigo,
                        prod.descripcion,
                        f"${prod.precio_venta:.2f}",
                        f"{prod.stock:.0f}" if prod.stock else "0",
                        prod.categoria or "Sin categoría"
                    )
                )
            
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
                # Buscar si ya está en carrito
                for i, item_carrito in enumerate(self.productos_carrito):
                    if item_carrito['id'] == producto.id:
                        item_carrito['cantidad'] += 1
                        item_carrito['subtotal'] = item_carrito['cantidad'] * item_carrito['precio']
                        self.actualizar_carrito()
                        messagebox.showinfo("Producto agregado", f"Se agregó 1 unidad más de {producto.descripcion}")
                        return
                
                # Agregar nuevo
                self.productos_carrito.append({
                    'id': producto.id,
                    'codigo': producto.codigo,
                    'descripcion': producto.descripcion,
                    'precio': producto.precio_venta,
                    'cantidad': 1,
                    'subtotal': producto.precio_venta
                })
                
                self.actualizar_carrito()
                messagebox.showinfo("Producto agregado", f"{producto.descripcion} agregado al carrito")
        
        cat_tree.bind("<Double-1>", agregar_desde_catalogo)
        
        # Cargar catálogo inicial
        cargar_catalogo()
    
    def agregar_al_carrito(self, event):
        """Agrega producto seleccionado al carrito"""
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
        
        # Buscar si ya está en carrito
        for i, item_carrito in enumerate(self.productos_carrito):
            if item_carrito['id'] == producto.id:
                item_carrito['cantidad'] += 1
                item_carrito['subtotal'] = item_carrito['cantidad'] * item_carrito['precio']
                self.actualizar_carrito()
                return
        
        # Agregar nuevo
        self.productos_carrito.append({
            'id': producto.id,
            'codigo': producto.codigo,
            'descripcion': producto.descripcion,
            'precio': producto.precio_venta,
            'cantidad': 1,
            'subtotal': producto.precio_venta
        })
        
        self.actualizar_carrito()
    
    def limpiar_carrito(self):
        """Limpia el carrito de ventas"""
        if not self.productos_carrito:
            return
        
        respuesta = messagebox.askyesno(
            "Limpiar carrito",
            "¿Está seguro de que desea limpiar el carrito?"
        )
        
        if respuesta:
            self.productos_carrito = []
            self.actualizar_carrito()
    
    def actualizar_carrito(self):
        """Actualiza visualización del carrito"""
        # Limpiar
        for item in self.carrito_tree.get_children():
            self.carrito_tree.delete(item)
        
        # Agregar items
        self.total_venta = 0
        for item in self.productos_carrito:
            self.carrito_tree.insert(
                "",
                tk.END,
                values=(
                    item['cantidad'],
                    item['descripcion'],
                    f"${item['precio']:.2f}",
                    f"${item['subtotal']:.2f}"
                )
            )
            self.total_venta += item['subtotal']
        
        self.total_label.config(text=f"${self.total_venta:.2f}")
    
    def seleccionar_medio_pago(self):
        """Muestra diálogo para seleccionar medio de pago"""
        ventana_pago = tk.Toplevel(self.root)
        ventana_pago.title("Seleccionar Medio de Pago")
        ventana_pago.geometry("500x400")
        ventana_pago.configure(bg="#f0f2f5")
        ventana_pago.transient(self.root)
        ventana_pago.grab_set()
        
        # Centrar ventana
        ventana_pago.update_idletasks()
        x = (ventana_pago.winfo_screenwidth() // 2) - (500 // 2)
        y = (ventana_pago.winfo_screenheight() // 2) - (400 // 2)
        ventana_pago.geometry(f'500x400+{x}+{y}')
        
        # Header
        header_pago = tk.Frame(ventana_pago, bg="#667eea", height=60)
        header_pago.pack(fill=tk.X)
        
        tk.Label(
            header_pago,
            text="💳 Seleccionar Medio de Pago",
            font=("Arial", 16, "bold"),
            bg="#667eea",
            fg="white"
        ).pack(pady=15)
        
        # Total a cobrar
        total_frame = tk.Frame(ventana_pago, bg="#f8f9fa", relief=tk.RAISED, bd=1)
        total_frame.pack(fill=tk.X, padx=20, pady=20)
        
        tk.Label(
            total_frame,
            text="Total a cobrar:",
            font=("Arial", 12),
            bg="#f8f9fa"
        ).pack(pady=10)
        
        tk.Label(
            total_frame,
            text=f"${self.total_venta:.2f}",
            font=("Arial", 24, "bold"),
            fg="#667eea",
            bg="#f8f9fa"
        ).pack(pady=(0, 10))
        
        # Opciones de pago
        opciones_frame = tk.Frame(ventana_pago, bg="#f0f2f5")
        opciones_frame.pack(fill=tk.BOTH, expand=True, padx=20, pady=10)
        
        self.medio_pago_seleccionado = tk.StringVar(value="Efectivo")
        
        medios_pago = [
            ("💵 Efectivo", "Efectivo"),
            ("💳 Tarjeta Débito", "TD"),
            ("💳 Tarjeta Crédito", "TC"),
            ("📱 Mercado Pago", "MP"),
            ("🏦 Transferencia", "TR"),
            ("📄 Cheque", "CH"),
            ("📋 Cuenta Corriente", "CC")
        ]
        
        for texto, valor in medios_pago:
            radio = tk.Radiobutton(
                opciones_frame,
                text=texto,
                variable=self.medio_pago_seleccionado,
                value=valor,
                font=("Arial", 12),
                bg="#f0f2f5",
                activebackground="#f0f2f5",
                selectcolor="white",
                cursor="hand2",
                padx=20,
                pady=8
            )
            radio.pack(anchor=tk.W, pady=5)
        
        # Botones
        botones_frame = tk.Frame(ventana_pago, bg="#f0f2f5")
        botones_frame.pack(fill=tk.X, padx=20, pady=20)
        
        def confirmar_pago():
            self.medio_pago = self.medio_pago_seleccionado.get()
            ventana_pago.destroy()
            self.procesar_cobro()
        
        btn_confirmar = tk.Button(
            botones_frame,
            text="✅ Confirmar Pago",
            bg="#27ae60",
            fg="white",
            font=("Arial", 12, "bold"),
            command=confirmar_pago,
            relief=tk.FLAT,
            padx=30,
            pady=12,
            cursor="hand2"
        )
        btn_confirmar.pack(side=tk.LEFT, fill=tk.X, expand=True, padx=5)
        
        btn_cancelar = tk.Button(
            botones_frame,
            text="❌ Cancelar",
            bg="#e74c3c",
            fg="white",
            font=("Arial", 12, "bold"),
            command=ventana_pago.destroy,
            relief=tk.FLAT,
            padx=30,
            pady=12,
            cursor="hand2"
        )
        btn_cancelar.pack(side=tk.LEFT, fill=tk.X, expand=True, padx=5)
        
        ventana_pago.focus_force()
    
    def cobrar_venta(self):
        """Inicia el proceso de cobro"""
        if not self.productos_carrito:
            messagebox.showwarning("Carrito vacío", "Agregue productos al carrito antes de cobrar")
            return
        
        # Mostrar selección de medio de pago
        self.seleccionar_medio_pago()
    
    def procesar_cobro(self):
        """Procesa la venta después de seleccionar medio de pago"""
        try:
            # Guardar venta en base local
            session = get_session()
            venta = Venta(
                fecha=datetime.now(),
                cliente="Consumidor Final",
                productos=self.productos_carrito,
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
            
            # Intentar sincronizar si hay conexión
            if self.connection_monitor.check_connection():
                print("🔄 Intentando sincronizar venta...")
                self.sync_manager.sync_ventas()
            
            messagebox.showinfo(
                "Venta registrada",
                f"Venta guardada exitosamente.\n\nTotal: ${self.total_venta:.2f}\nMedio de pago: {self.medio_pago}\n\nLa venta se sincronizará cuando haya conexión."
            )
            
            # Limpiar carrito
            self.productos_carrito = []
            self.actualizar_carrito()
            
        except Exception as e:
            print(f"❌ Error al guardar venta: {e}")
            import traceback
            traceback.print_exc()
            messagebox.showerror("Error", f"Error al guardar la venta: {str(e)}")
    
    def mostrar_ventas(self):
        """Muestra ventas de últimos 30 días con diseño mejorado"""
        ventana_ventas = tk.Toplevel(self.root)
        ventana_ventas.title("📊 Ventas - Últimos 30 días")
        ventana_ventas.geometry("1100x700")
        ventana_ventas.configure(bg="#f0f2f5")
        
        # Header
        header_ventas = tk.Frame(ventana_ventas, bg="#667eea", height=60)
        header_ventas.pack(fill=tk.X)
        
        tk.Label(
            header_ventas,
            text="📊 Ventas - Últimos 30 días",
            font=("Arial", 18, "bold"),
            bg="#667eea",
            fg="white"
        ).pack(pady=15)
        
        # Intentar cargar desde servidor si hay conexión
        ventas_data = []
        try:
            if self.connection_monitor.check_connection():
                print("🔄 Cargando ventas desde servidor...")
                ventas_data = self.sync_manager.sync_ventas_historial(30)
                if ventas_data is None:
                    ventas_data = []
                print(f"✅ Ventas recibidas del servidor: {len(ventas_data)}")
            else:
                print("⚠️  Sin conexión, cargando desde base local...")
                # Cargar desde base local
                session = get_session()
                fecha_desde = datetime.now() - timedelta(days=30)
                ventas = session.query(Venta).filter(Venta.fecha >= fecha_desde).order_by(Venta.fecha.desc()).all()
                ventas_data = [
                    {
                        'fecha': v.fecha.isoformat(),
                        'total': v.total,
                        'cliente': v.cliente,
                        'metodo_pago': v.metodo_pago,
                        'sincronizado': '✅' if v.sincronizado else '⏳ Pendiente'
                    }
                    for v in ventas
                ]
                session.close()
                print(f"✅ Ventas cargadas desde base local: {len(ventas_data)}")
        except Exception as e:
            print(f"❌ Error al cargar ventas: {e}")
            import traceback
            traceback.print_exc()
            # Cargar desde base local como fallback
            try:
                session = get_session()
                fecha_desde = datetime.now() - timedelta(days=30)
                ventas = session.query(Venta).filter(Venta.fecha >= fecha_desde).order_by(Venta.fecha.desc()).all()
                ventas_data = [
                    {
                        'fecha': v.fecha.isoformat(),
                        'total': v.total,
                        'cliente': v.cliente,
                        'metodo_pago': v.metodo_pago,
                        'sincronizado': '✅' if v.sincronizado else '⏳ Pendiente'
                    }
                    for v in ventas
                ]
                session.close()
            except Exception as e2:
                print(f"❌ Error al cargar desde base local: {e2}")
                ventas_data = []
        
        # Resumen
        resumen_frame = tk.Frame(ventana_ventas, bg="white", relief=tk.RAISED, bd=1)
        resumen_frame.pack(fill=tk.X, padx=20, pady=15)
        
        total_ventas = sum(v.get('total', 0) for v in ventas_data)
        cantidad_ventas = len(ventas_data)
        
        tk.Label(
            resumen_frame,
            text=f"Total de ventas: {cantidad_ventas}",
            font=("Arial", 12, "bold"),
            bg="white",
            fg="#2c3e50"
        ).pack(side=tk.LEFT, padx=20, pady=15)
        
        tk.Label(
            resumen_frame,
            text=f"Total recaudado: ${total_ventas:.2f}",
            font=("Arial", 14, "bold"),
            bg="white",
            fg="#667eea"
        ).pack(side=tk.RIGHT, padx=20, pady=15)
        
        # Tabla de ventas
        tree_frame = tk.Frame(ventana_ventas, bg="white")
        tree_frame.pack(fill=tk.BOTH, expand=True, padx=20, pady=(0, 20))
        
        style = ttk.Style()
        style.configure("Ventas.Treeview",
                       background="white",
                       rowheight=35,
                       font=("Arial", 11))
        style.configure("Ventas.Treeview.Heading",
                       background="#667eea",
                       foreground="white",
                       font=("Arial", 11, "bold"))
        
        tree = ttk.Treeview(
            tree_frame,
            columns=("fecha", "cliente", "metodo_pago", "total", "estado"),
            show="headings",
            style="Ventas.Treeview"
        )
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
        
        # Agregar ventas a la tabla
        if ventas_data:
            for venta in ventas_data:
                fecha_str = venta.get('fecha', '')
                if fecha_str:
                    try:
                        if 'T' in fecha_str:
                            fecha_obj = datetime.fromisoformat(fecha_str.replace('Z', '+00:00'))
                        else:
                            fecha_obj = datetime.fromisoformat(fecha_str)
                        fecha_formateada = fecha_obj.strftime('%d/%m/%Y %H:%M')
                    except:
                        fecha_formateada = fecha_str[:19] if len(fecha_str) > 19 else fecha_str
                else:
                    fecha_formateada = "N/A"
                
                metodo_pago = venta.get('metodo_pago', 'Efectivo')
                # Mapear códigos a nombres
                metodos = {
                    'Efectivo': '💵 Efectivo',
                    'TD': '💳 Débito',
                    'TC': '💳 Crédito',
                    'MP': '📱 Mercado Pago',
                    'TR': '🏦 Transferencia',
                    'CH': '📄 Cheque',
                    'CC': '📋 Cta. Cte.'
                }
                metodo_display = metodos.get(metodo_pago, metodo_pago)
                
                tree.insert("", tk.END, values=(
                    fecha_formateada,
                    venta.get('cliente', 'Consumidor Final'),
                    metodo_display,
                    f"${venta.get('total', 0):.2f}",
                    venta.get('sincronizado', '✅')
                ))
        else:
            # Mensaje si no hay ventas
            tree.insert("", tk.END, values=(
                "No hay ventas",
                "en los últimos 30 días",
                "",
                "",
                ""
            ))
    
    def run(self):
        """Inicia la aplicación"""
        self.root.deiconify()
        self.root.mainloop()
