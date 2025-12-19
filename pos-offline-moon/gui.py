#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
INTERFAZ GRÁFICA
Interfaz moderna con tkinter para el sistema POS offline
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
        self.parent = parent
        self.auth_manager = AuthManager()
        self.sync_manager = SyncManager()
        self.connection_monitor = ConnectionMonitor()
        self.id_cliente_moon = config.ID_CLIENTE_MOON
        
        self.setup_login_ui()
        # Ejecutar sincronización después de que la ventana se muestre
        self.window.after(500, self.check_initial_sync)
    
    def setup_login_ui(self):
        self.window = tk.Toplevel(self.parent)
        self.window.title("POS Offline Moon - Login")
        self.window.geometry("450x550")
        self.window.configure(bg="#667eea")
        self.window.resizable(False, False)
        
        # Centrar ventana
        self.window.transient(self.parent)
        self.window.grab_set()
        
        # Forzar que la ventana se muestre
        self.window.deiconify()
        self.window.lift()
        self.window.focus_force()
        
        # Centrar en pantalla
        self.window.update_idletasks()
        width = self.window.winfo_width()
        height = self.window.winfo_height()
        x = (self.window.winfo_screenwidth() // 2) - (width // 2)
        y = (self.window.winfo_screenheight() // 2) - (height // 2)
        self.window.geometry(f'{width}x{height}+{x}+{y}')
        
        # Asegurar que esté visible
        self.window.update()
        
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
                    # Sincronizar en modo silencioso (sin prints)
                    self.sync_manager.sync_all(id_cliente_moon=self.id_cliente_moon, silent=True)
                    
                    # Actualizar estado de conexión en la UI
                    self.window.after(0, lambda: self.connection_status.config(
                        text="🟢 En línea - Sincronizado"
                    ))
                except Exception as e:
                    print(f"Error en sincronización inicial: {e}")
                    self.window.after(0, lambda: self.connection_status.config(
                        text="🟢 En línea - Error en sync"
                    ))
            
            thread = threading.Thread(target=sync_thread, daemon=True)
            thread.start()
    
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
                self.sync_manager.sync_all(id_cliente_moon=self.id_cliente_moon)
                if show_message:
                    self.window.after(0, lambda: messagebox.showinfo("Listo", "Sincronización completada"))
            except Exception as e:
                error_msg = f"Error en sincronización: {str(e)}"
                print(error_msg)
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
        self.root.title("POS Offline Moon")
        self.root.geometry("1200x800")
        self.root.configure(bg="#f5f5f5")
        
        # Header con estado de conexión y usuario
        header = tk.Frame(self.root, bg="#667eea", height=60)
        header.pack(fill=tk.X)
        
        self.status_label = tk.Label(
            header,
            text="🟢 En línea",
            bg="#667eea",
            fg="white",
            font=("Arial", 12, "bold")
        )
        self.status_label.pack(side=tk.LEFT, padx=20, pady=15)
        
        # Usuario actual
        user_label = tk.Label(
            header,
            text=f"👤 {self.auth_manager.current_user.nombre}",
            bg="#667eea",
            fg="white",
            font=("Arial", 10)
        )
        user_label.pack(side=tk.LEFT, padx=10)
        
        btn_sync = tk.Button(
            header,
            text="🔄 Sincronizar",
            bg="#764ba2",
            fg="white",
            font=("Arial", 10, "bold"),
            command=self.manual_sync,
            relief=tk.FLAT,
            padx=20,
            cursor="hand2"
        )
        btn_sync.pack(side=tk.RIGHT, padx=20, pady=10)
        
        # Contenedor principal
        main_container = tk.Frame(self.root, bg="#f5f5f5")
        main_container.pack(fill=tk.BOTH, expand=True, padx=10, pady=10)
        
        # Panel izquierdo - Carrito
        left_panel = tk.Frame(main_container, bg="white", relief=tk.RAISED, bd=2)
        left_panel.pack(side=tk.LEFT, fill=tk.BOTH, expand=True, padx=(0, 5))
        
        tk.Label(
            left_panel,
            text="Carrito de Venta",
            font=("Arial", 16, "bold"),
            bg="white"
        ).pack(pady=10)
        
        # Tabla de productos en carrito
        carrito_frame = tk.Frame(left_panel, bg="white")
        carrito_frame.pack(fill=tk.BOTH, expand=True, padx=10, pady=10)
        
        self.carrito_tree = ttk.Treeview(
            carrito_frame,
            columns=("cantidad", "producto", "precio", "subtotal"),
            show="headings",
            height=15
        )
        self.carrito_tree.heading("cantidad", text="Cant.")
        self.carrito_tree.heading("producto", text="Producto")
        self.carrito_tree.heading("precio", text="P. Unit.")
        self.carrito_tree.heading("subtotal", text="Subtotal")
        
        self.carrito_tree.column("cantidad", width=80, anchor=tk.CENTER)
        self.carrito_tree.column("producto", width=300)
        self.carrito_tree.column("precio", width=100, anchor=tk.CENTER)
        self.carrito_tree.column("subtotal", width=100, anchor=tk.CENTER)
        
        scrollbar_carrito = ttk.Scrollbar(carrito_frame, orient=tk.VERTICAL, command=self.carrito_tree.yview)
        self.carrito_tree.configure(yscrollcommand=scrollbar_carrito.set)
        
        self.carrito_tree.pack(side=tk.LEFT, fill=tk.BOTH, expand=True)
        scrollbar_carrito.pack(side=tk.RIGHT, fill=tk.Y)
        
        # Total
        total_frame = tk.Frame(left_panel, bg="white")
        total_frame.pack(fill=tk.X, padx=10, pady=10)
        
        tk.Label(
            total_frame,
            text="TOTAL:",
            font=("Arial", 18, "bold"),
            bg="white"
        ).pack(side=tk.LEFT)
        
        self.total_label = tk.Label(
            total_frame,
            text="$ 0.00",
            font=("Arial", 18, "bold"),
            fg="#667eea",
            bg="white"
        )
        self.total_label.pack(side=tk.LEFT, padx=10)
        
        btn_cobrar = tk.Button(
            left_panel,
            text="💳 COBRAR (F7)",
            bg="#667eea",
            fg="white",
            font=("Arial", 14, "bold"),
            command=self.cobrar_venta,
            relief=tk.FLAT,
            padx=30,
            pady=15,
            cursor="hand2"
        )
        btn_cobrar.pack(pady=10)
        
        # Panel derecho - Búsqueda de productos
        right_panel = tk.Frame(main_container, bg="#f8f9fa", relief=tk.RAISED, bd=2)
        right_panel.pack(side=tk.RIGHT, fill=tk.BOTH, padx=(5, 0))
        
        tk.Label(
            right_panel,
            text="Buscar Producto",
            font=("Arial", 14, "bold"),
            bg="#f8f9fa"
        ).pack(pady=10)
        
        # Búsqueda
        search_frame = tk.Frame(right_panel, bg="#f8f9fa")
        search_frame.pack(fill=tk.X, padx=10, pady=5)
        
        self.search_var = tk.StringVar()
        self.search_var.trace("w", lambda *args: self.buscar_producto())
        
        search_entry = tk.Entry(
            search_frame,
            textvariable=self.search_var,
            font=("Arial", 12),
            width=30,
            relief=tk.SOLID,
            bd=1
        )
        search_entry.pack(pady=5)
        search_entry.focus()
        
        # Lista de productos
        productos_frame = tk.Frame(right_panel, bg="#f8f9fa")
        productos_frame.pack(fill=tk.BOTH, expand=True, padx=10, pady=10)
        
        self.productos_tree = ttk.Treeview(
            productos_frame,
            columns=("codigo", "descripcion", "precio", "stock"),
            show="headings",
            height=20
        )
        self.productos_tree.heading("codigo", text="Código")
        self.productos_tree.heading("descripcion", text="Descripción")
        self.productos_tree.heading("precio", text="Precio")
        self.productos_tree.heading("stock", text="Stock")
        
        self.productos_tree.column("codigo", width=100)
        self.productos_tree.column("descripcion", width=200)
        self.productos_tree.column("precio", width=100, anchor=tk.CENTER)
        self.productos_tree.column("stock", width=80, anchor=tk.CENTER)
        
        scrollbar_productos = ttk.Scrollbar(productos_frame, orient=tk.VERTICAL, command=self.productos_tree.yview)
        self.productos_tree.configure(yscrollcommand=scrollbar_productos.set)
        
        self.productos_tree.pack(side=tk.LEFT, fill=tk.BOTH, expand=True)
        scrollbar_productos.pack(side=tk.RIGHT, fill=tk.Y)
        
        self.productos_tree.bind("<Double-1>", self.agregar_al_carrito)
        
        # Botón ver ventas
        btn_ventas = tk.Button(
            right_panel,
            text="📊 Ver Ventas (Últimos 30 días)",
            bg="#764ba2",
            fg="white",
            font=("Arial", 10, "bold"),
            command=self.mostrar_ventas,
            relief=tk.FLAT,
            padx=20,
            pady=10,
            cursor="hand2"
        )
        btn_ventas.pack(pady=10)
        
        # Atajos de teclado
        self.root.bind('<F7>', lambda e: self.cobrar_venta())
        
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
    
    def cobrar_venta(self):
        """Procesa la venta"""
        if not self.productos_carrito:
            messagebox.showwarning("Carrito vacío", "Agregue productos al carrito")
            return
        
        # Guardar venta en base local
        session = get_session()
        venta = Venta(
            fecha=datetime.now(),
            cliente="Consumidor Final",
            productos=self.productos_carrito,
            total=self.total_venta,
            metodo_pago="Efectivo",
            sincronizado=False,
            creado_local=True
        )
        session.add(venta)
        session.commit()
        session.close()
        
        # Intentar sincronizar si hay conexión
        if self.connection_monitor.check_connection():
            self.sync_manager.sync_ventas()
        
        messagebox.showinfo("Venta registrada", f"Venta guardada. Total: ${self.total_venta:.2f}")
        
        # Limpiar carrito
        self.productos_carrito = []
        self.actualizar_carrito()
    
    def mostrar_ventas(self):
        """Muestra ventas de últimos 30 días"""
        ventana_ventas = tk.Toplevel(self.root)
        ventana_ventas.title("Ventas - Últimos 30 días")
        ventana_ventas.geometry("1000x600")
        
        # Intentar cargar desde servidor si hay conexión
        ventas_data = []
        if self.connection_monitor.check_connection():
            ventas_data = self.sync_manager.sync_ventas_historial(30)
        else:
            # Cargar desde base local
            session = get_session()
            fecha_desde = datetime.now() - timedelta(days=30)
            ventas = session.query(Venta).filter(Venta.fecha >= fecha_desde).all()
            ventas_data = [
                {
                    'fecha': v.fecha.isoformat(),
                    'total': v.total,
                    'cliente': v.cliente,
                    'sincronizado': '✅' if v.sincronizado else '⏳'
                }
                for v in ventas
            ]
            session.close()
        
        # Tabla de ventas
        tree_frame = tk.Frame(ventana_ventas)
        tree_frame.pack(fill=tk.BOTH, expand=True, padx=10, pady=10)
        
        tree = ttk.Treeview(
            tree_frame,
            columns=("fecha", "cliente", "total", "estado"),
            show="headings"
        )
        tree.heading("fecha", text="Fecha")
        tree.heading("cliente", text="Cliente")
        tree.heading("total", text="Total")
        tree.heading("estado", text="Estado")
        
        tree.column("fecha", width=200)
        tree.column("cliente", width=300)
        tree.column("total", width=150, anchor=tk.CENTER)
        tree.column("estado", width=100, anchor=tk.CENTER)
        
        scrollbar = ttk.Scrollbar(tree_frame, orient=tk.VERTICAL, command=tree.yview)
        tree.configure(yscrollcommand=scrollbar.set)
        
        tree.pack(side=tk.LEFT, fill=tk.BOTH, expand=True)
        scrollbar.pack(side=tk.RIGHT, fill=tk.Y)
        
        for venta in ventas_data:
            tree.insert("", tk.END, values=(
                venta['fecha'][:19],
                venta['cliente'],
                f"${venta['total']:.2f}",
                venta.get('sincronizado', '✅')
            ))
    
    def run(self):
        """Inicia la aplicación"""
        self.root.deiconify()
        self.root.mainloop()
