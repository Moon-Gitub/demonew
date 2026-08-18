#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""Pantalla principal de caja (paridad con crear-venta-caja)."""

import tkinter as tk
from tkinter import ttk, messagebox, simpledialog
from datetime import datetime, timedelta
from database import get_session, Producto, Venta, Cliente, MedioPago, ListaPrecio
from config import config
from lista_precio import precio_con_lista
from scale_parser import parse_scale_barcode
import online_actions
from ui import responsive


class POSApp:
    def __init__(self, root, auth_manager, sync_manager, connection_monitor, id_cliente_moon):
        self.root = root
        self.auth_manager = auth_manager
        self.sync_manager = sync_manager
        self.connection_monitor = connection_monitor
        self.id_cliente_moon = id_cliente_moon
        self.productos_carrito = []
        self.total_venta = 0.0
        self.clientes_disponibles = [{'id': 1, 'display': '1-Consumidor Final', 'nombre': 'Consumidor Final'}]
        self.lista_precio_codigo = tk.StringVar()
        self.listas_config = {}
        self._listas_db = []
        self._listas_combo_values = ["Precio venta (defecto)"]
        self._cargar_maestros_locales()
        
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
        # Con una medida fija de 1600x1000, en las notebooks de 1366x768 quedaban
        # fuera de pantalla la columna de pago y el botón de cobrar.
        responsive.ajustar_ventana(self.root, 1600, 1000, min_ancho=900, min_alto=560)
        self.esc = responsive.escala(self.root)
        esc = self.esc
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
        menu_ayuda.add_command(label="Atajos: F1=Nueva venta, F5=Recargar, F7=Cobrar, F9=Catálogo", command=lambda: None)
        menu_ventas.add_command(label="Facturar (requiere conexión)", command=self.accion_facturar_online)
        menu_ventas.add_command(label="Mercado Pago", command=online_actions.abrir_mercado_pago)
        
        # Header con gradiente (similar a crear-venta-caja online)
        header = tk.Frame(self.root, bg="#667eea")
        header.pack(fill=tk.X, side=tk.TOP)
        
        tk.Label(header, text="POS | Moon - Crear Venta", font=esc.fuente(18, True),
                bg="#667eea", fg="white").pack(side=tk.LEFT, padx=esc.px(20), pady=esc.px(15))
        
        sucursal_usuario = getattr(self.auth_manager.current_user, 'sucursal', None) or "Local"
        tk.Label(header, text=f"Sucursal: {sucursal_usuario}", bg="#667eea",
                fg="white", font=esc.fuente(10)).pack(side=tk.LEFT, padx=esc.px(10), pady=esc.px(15))
        
        self.status_label = tk.Label(header, text="🟢 En línea", bg="#667eea", fg="white",
                                     font=esc.fuente(11, True))
        self.status_label.pack(side=tk.RIGHT, padx=esc.px(10), pady=esc.px(15))
        
        tk.Label(header, text=f"👤 {self.auth_manager.current_user.nombre}", bg="#667eea",
                fg="white", font=esc.fuente(10)).pack(side=tk.RIGHT, padx=esc.px(10), pady=esc.px(15))
        
        # Contenedor principal - 3 columnas
        main_container = tk.Frame(self.root, bg="#ecf0f5")
        main_container.pack(fill=tk.BOTH, expand=True, padx=esc.px(10), pady=esc.px(10))
        
        # Las tres columnas se ubican antes de llenarlas: las laterales reservan
        # su ancho y la central se queda con lo que sobre. Si se dejaba para el
        # final, en pantallas angostas la columna de pago desaparecía.
        left_col = tk.Frame(main_container, bg="white", relief=tk.RAISED, bd=1, width=esc.px(400))
        left_col.pack(side=tk.LEFT, fill=tk.Y, padx=(0, 5))
        left_col.pack_propagate(False)
        
        right_col, right_body = responsive.columna_scrollable(
            main_container, bg="white", ancho=esc.px(330)
        )
        right_col.pack(side=tk.RIGHT, fill=tk.Y, padx=(5, 0))
        
        center_col = tk.Frame(main_container, bg="white", relief=tk.RAISED, bd=1)
        center_col.pack(side=tk.LEFT, fill=tk.BOTH, expand=True, padx=5)
        
        # COLUMNA IZQUIERDA: Búsqueda y lista de productos
        tk.Label(left_col, text="🔍 BUSCAR PRODUCTO", font=esc.fuente(14, True),
                bg="white", fg="#2c3e50").pack(pady=esc.px(15))
        
        search_frame = tk.Frame(left_col, bg="white")
        search_frame.pack(fill=tk.X, padx=esc.px(15), pady=esc.px(10))
        
        self.search_var = tk.StringVar()
        search_entry = tk.Entry(search_frame, textvariable=self.search_var, font=esc.fuente(13),
                               relief=tk.SOLID, bd=2, bg="#f8f9fa")
        search_entry.pack(fill=tk.X, ipady=esc.px(10))
        search_entry.focus()
        self.search_var.trace("w", lambda *args: self.buscar_producto())
        
        tk.Button(search_frame, text="📋 Ver Catálogo Completo", bg="#3498db", fg="white",
                 font=esc.fuente(10, True), command=self.mostrar_catalogo, relief=tk.FLAT,
                 padx=esc.px(15), pady=esc.px(8), cursor="hand2").pack(fill=tk.X, pady=(esc.px(10), 0))
        
        tk.Label(left_col, text="LISTA DE PRODUCTOS", font=esc.fuente(12, True),
                bg="white", fg="#7f8c8d").pack(pady=(esc.px(15), esc.px(5)))
        
        # Botón Agregar producto: se ubica antes que la lista porque pack recorta
        # lo último que se agrega cuando falta lugar, y este botón no puede faltar.
        btn_agregar = tk.Button(left_col, text="➕ Agregar Producto Seleccionado (Enter)", bg="#27ae60", fg="white",
                               font=esc.fuente(10, True), command=self.agregar_producto_seleccionado,
                               relief=tk.FLAT, padx=esc.px(15), pady=esc.px(8), cursor="hand2")
        btn_agregar.pack(side=tk.BOTTOM, fill=tk.X, padx=esc.px(15), pady=(esc.px(5), esc.px(15)))
        
        productos_frame = tk.Frame(left_col, bg="white")
        productos_frame.pack(fill=tk.BOTH, expand=True, padx=esc.px(15), pady=(0, esc.px(5)))
        
        # Treeview de productos
        style = ttk.Style()
        style.theme_use("clam")
        style.configure("Treeview", rowheight=esc.px(24, minimo=17), font=esc.fuente(9))
        style.configure("Treeview.Heading", font=esc.fuente(9, True))
        
        # La altura va en filas y fija el mínimo que pide la tabla: con 22 el
        # bloque no entraba en pantallas bajas. Con fill/expand igual se estira.
        self.productos_tree = ttk.Treeview(productos_frame, columns=("codigo", "descripcion", "precio", "stock"),
                                          show="headings", height=6)
        self.productos_tree.heading("codigo", text="Código")
        self.productos_tree.heading("descripcion", text="Descripción")
        self.productos_tree.heading("precio", text="Precio")
        self.productos_tree.heading("stock", text="Stock")
        
        self.productos_tree.column("codigo", width=esc.px(80), minwidth=35, anchor=tk.CENTER, stretch=False)
        self.productos_tree.column("descripcion", width=esc.px(170), minwidth=35, stretch=False)
        self.productos_tree.column("precio", width=esc.px(85), minwidth=35, anchor=tk.CENTER, stretch=False)
        self.productos_tree.column("stock", width=esc.px(60), minwidth=35, anchor=tk.CENTER, stretch=False)
        responsive.columnas_proporcionales(self.productos_tree, (0.20, 0.45, 0.22, 0.13))
        
        scrollbar_prod = ttk.Scrollbar(productos_frame, orient=tk.VERTICAL, command=self.productos_tree.yview)
        self.productos_tree.configure(yscrollcommand=scrollbar_prod.set)
        
        self.productos_tree.pack(side=tk.LEFT, fill=tk.BOTH, expand=True)
        scrollbar_prod.pack(side=tk.RIGHT, fill=tk.Y)
        
        # Eventos para productos
        self.productos_tree.bind("<Double-1>", self.agregar_al_carrito)
        self.productos_tree.bind("<Return>", self.agregar_al_carrito)
        self.productos_tree.bind("<space>", self.agregar_al_carrito)
        self.productos_tree.bind("<Up>", self.navegar_productos)
        self.productos_tree.bind("<Down>", self.navegar_productos)
        
        # Enfocar en la lista de productos al cargar
        self.productos_tree.focus_set()
        
        # COLUMNA CENTRAL: Cliente y Carrito de venta
        # Lista de precio
        lista_frame = tk.Frame(center_col, bg="#f8f9fa", relief=tk.RAISED, bd=1)
        lista_frame.pack(fill=tk.X, padx=esc.px(15), pady=(esc.px(15), esc.px(5)))
        tk.Label(lista_frame, text="📋 LISTA DE PRECIO", font=esc.fuente(11, True),
                bg="#f8f9fa", fg="#2c3e50").pack(side=tk.LEFT, padx=esc.px(10), pady=esc.px(8))
        self.lista_combo = ttk.Combobox(lista_frame, textvariable=self.lista_precio_codigo,
                                        state="readonly", width=24, font=esc.fuente(10))
        self.lista_combo.pack(side=tk.LEFT, fill=tk.X, expand=True, padx=esc.px(10), pady=esc.px(8))
        self.lista_combo.bind("<<ComboboxSelected>>", lambda e: self._aplicar_lista_carrito())
        if hasattr(self, '_listas_combo_values'):
            self.lista_combo['values'] = self._listas_combo_values

        # Sección de cliente
        cliente_frame = tk.Frame(center_col, bg="#f8f9fa", relief=tk.RAISED, bd=1)
        cliente_frame.pack(fill=tk.X, padx=esc.px(15), pady=(esc.px(5), esc.px(10)))
        
        tk.Label(cliente_frame, text="👤 CLIENTE", font=esc.fuente(12, True),
                bg="#f8f9fa", fg="#2c3e50").pack(side=tk.LEFT, padx=esc.px(10), pady=esc.px(10))
        
        self.cliente_seleccionado = tk.StringVar(value="1-Consumidor Final")
        self.cliente_id = 1  # ID por defecto
        
        cliente_entry_frame = tk.Frame(cliente_frame, bg="#f8f9fa")
        cliente_entry_frame.pack(side=tk.LEFT, fill=tk.X, expand=True, padx=esc.px(10), pady=esc.px(10))
        
        self.cliente_entry = tk.Entry(cliente_entry_frame, textvariable=self.cliente_seleccionado,
                                     font=esc.fuente(11), relief=tk.SOLID, bd=1, state="readonly")
        self.cliente_entry.pack(side=tk.LEFT, fill=tk.X, expand=True, ipady=esc.px(5))
        
        tk.Button(cliente_entry_frame, text="🔍 Buscar", bg="#3498db", fg="white",
                 font=esc.fuente(9, True), command=self.buscar_cliente, relief=tk.FLAT,
                 padx=esc.px(15), pady=esc.px(5), cursor="hand2").pack(side=tk.LEFT, padx=(esc.px(5), 0))
        
        tk.Label(center_col, text="🛒 CARRITO DE VENTA", font=esc.fuente(16, True),
                bg="#667eea", fg="white").pack(fill=tk.X, pady=0, ipady=esc.px(15))
        
        # El pie de la columna (cobrar, total y botones del carrito) se ubica
        # antes que la tabla: así el carrito cede alto y nada queda cortado.
        btn_cobrar = tk.Button(center_col, text="💳 COBRAR VENTA (F7)", bg="#27ae60", fg="white",
                              font=esc.fuente(18, True), command=self.cobrar_venta, relief=tk.FLAT,
                              padx=esc.px(40), pady=esc.px(18), cursor="hand2")
        btn_cobrar.pack(side=tk.BOTTOM, fill=tk.X, padx=esc.px(15), pady=(esc.px(5), esc.px(15)))
        
        # Total destacado
        total_frame = tk.Frame(center_col, bg="#34495e", relief=tk.RAISED, bd=2)
        total_frame.pack(side=tk.BOTTOM, fill=tk.X, padx=esc.px(15), pady=esc.px(8))
        
        tk.Label(total_frame, text="TOTAL A COBRAR:", font=esc.fuente(16, True),
                bg="#34495e", fg="white").pack(side=tk.LEFT, padx=esc.px(20), pady=esc.px(12))
        
        self.total_label = tk.Label(total_frame, text="$ 0.00", font=esc.fuente(28, True),
                                    fg="#2ecc71", bg="#34495e")
        self.total_label.pack(side=tk.RIGHT, padx=esc.px(20), pady=esc.px(12))
        
        # Botones de acción del carrito
        carrito_actions = tk.Frame(center_col, bg="white")
        carrito_actions.pack(side=tk.BOTTOM, fill=tk.X, padx=esc.px(15), pady=esc.px(5))
        
        # width=1 hace que cada botón pida lo mínimo y que pack les reparta el
        # ancho en partes iguales; si no, el último se recorta al no haber lugar.
        for texto, color, accion in (
            ("➕ Aumentar", "#27ae60", self.aumentar_cantidad),
            ("➖ Disminuir", "#f39c12", self.disminuir_cantidad),
            ("🗑️ Eliminar", "#e74c3c", self.eliminar_item),
            ("🗑️ Limpiar", "#c0392b", self.limpiar_carrito),
        ):
            tk.Button(carrito_actions, text=texto, bg=color, fg="white", width=1,
                     font=esc.fuente(10, True), command=accion, relief=tk.FLAT,
                     pady=esc.px(8), cursor="hand2").pack(side=tk.LEFT, padx=esc.px(3), fill=tk.X, expand=True)
        
        # Tabla del carrito
        carrito_frame = tk.Frame(center_col, bg="white")
        carrito_frame.pack(fill=tk.BOTH, expand=True, padx=esc.px(15), pady=esc.px(10))
        
        self.carrito_tree = ttk.Treeview(carrito_frame, columns=("cantidad", "producto", "precio", "subtotal"),
                                        show="headings", height=5)
        self.carrito_tree.heading("cantidad", text="Cant.")
        self.carrito_tree.heading("producto", text="Producto")
        self.carrito_tree.heading("precio", text="P. Unit.")
        self.carrito_tree.heading("subtotal", text="Subtotal")
        
        self.carrito_tree.column("cantidad", width=esc.px(70), minwidth=35, anchor=tk.CENTER, stretch=False)
        self.carrito_tree.column("producto", width=esc.px(280), minwidth=35, stretch=False)
        self.carrito_tree.column("precio", width=esc.px(110), minwidth=35, anchor=tk.CENTER, stretch=False)
        self.carrito_tree.column("subtotal", width=esc.px(110), minwidth=35, anchor=tk.CENTER, stretch=False)
        responsive.columnas_proporcionales(self.carrito_tree, (0.13, 0.50, 0.185, 0.185))
        
        scrollbar_carrito = ttk.Scrollbar(carrito_frame, orient=tk.VERTICAL, command=self.carrito_tree.yview)
        self.carrito_tree.configure(yscrollcommand=scrollbar_carrito.set)
        
        self.carrito_tree.pack(side=tk.LEFT, fill=tk.BOTH, expand=True)
        scrollbar_carrito.pack(side=tk.RIGHT, fill=tk.Y)
        
        # COLUMNA DERECHA: Acciones, método de pago y resumen
        # Sección: Método de Pago (PRIMERO, más visible)
        pago_header = tk.Frame(right_body, bg="#667eea")
        pago_header.pack(fill=tk.X, pady=(0, 0))
        
        titulo_pago = tk.Label(pago_header, text="💳 MÉTODO DE PAGO", font=esc.fuente(12, True),
                bg="#667eea", fg="white")
        titulo_pago.pack(pady=esc.px(12))
        
        self.pago_frame = tk.Frame(right_body, bg="white", relief=tk.RAISED, bd=1)
        self.pago_frame.pack(fill=tk.X, padx=esc.px(12), pady=esc.px(12))
        pago_frame = self.pago_frame
        
        self.medio_pago_seleccionado = tk.StringVar(value="EF")
        self.medio_pago = "EF"
        self._medios_pago_widgets = []
        self._build_medios_pago(pago_frame)
        
        # Sección: Acciones Rápidas
        titulo_acciones = tk.Label(right_body, text="⚡ ACCIONES RÁPIDAS", font=esc.fuente(12, True),
                bg="#764ba2", fg="white")
        titulo_acciones.pack(fill=tk.X, pady=(esc.px(10), 0), ipady=esc.px(12))
        self._titulos_derecha = (titulo_pago, titulo_acciones)
        
        actions_frame = tk.Frame(right_body, bg="white")
        actions_frame.pack(fill=tk.X, padx=esc.px(12), pady=esc.px(12))
        
        tk.Button(actions_frame, text="📊 Ver Ventas (30 días)", bg="#9b59b6", fg="white",
                 font=esc.fuente(11, True), command=self.mostrar_ventas, relief=tk.FLAT,
                 pady=esc.px(10), cursor="hand2").pack(fill=tk.X, pady=esc.px(4))
        
        tk.Button(actions_frame, text="🔄 Sincronizar", bg="#3498db", fg="white",
                 font=esc.fuente(11, True), command=self.manual_sync, relief=tk.FLAT,
                 pady=esc.px(10), cursor="hand2").pack(fill=tk.X, pady=esc.px(4))
        
        tk.Button(actions_frame, text="📋 Catálogo Completo", bg="#16a085", fg="white",
                 font=esc.fuente(11, True), command=self.mostrar_catalogo, relief=tk.FLAT,
                 pady=esc.px(10), cursor="hand2").pack(fill=tk.X, pady=esc.px(4))
        
        # Resumen de sesión
        resumen_frame = tk.Frame(right_body, bg="#ecf0f5", relief=tk.RAISED, bd=1)
        resumen_frame.pack(fill=tk.X, padx=esc.px(12), pady=esc.px(12))
        
        tk.Label(resumen_frame, text="📈 RESUMEN", font=esc.fuente(12, True),
                bg="#ecf0f5", fg="#2c3e50").pack(pady=esc.px(8))
        
        self.resumen_label = tk.Label(resumen_frame, text="Productos: 0\nTotal: $0.00",
                                     font=esc.fuente(11), bg="#ecf0f5", fg="#7f8c8d", justify=tk.LEFT)
        self.resumen_label.pack(pady=(0, esc.px(10)), padx=esc.px(10))
        
        # Las columnas laterales se reparten según el ancho real de la ventana,
        # así el carrito no queda aplastado en pantallas angostas ni desperdicia
        # lugar en las grandes, y acompaña si el usuario redimensiona.
        def ajustar_columnas(_evento=None):
            ancho = self.root.winfo_width()
            if ancho <= 1:
                return
            izquierda = max(230, min(esc.px(400), int(ancho * 0.26)))
            derecha = max(195, min(esc.px(330), int(ancho * 0.22)))
            if (izquierda, derecha) != getattr(self, "_anchos_columnas", None):
                self._anchos_columnas = (izquierda, derecha)
                left_col.configure(width=izquierda)
                right_col.configure(width=derecha)
                # Los títulos cortan en dos líneas antes que recortarse.
                for titulo in getattr(self, "_titulos_derecha", ()):
                    titulo.configure(wraplength=max(120, derecha - 16))

        self.root.bind("<Configure>", ajustar_columnas)
        self.root.after(60, ajustar_columnas)
        
        # Atajos de teclado globales
        self.root.bind('<F1>', lambda e: self.nueva_venta())
        self.root.bind('<F7>', lambda e: self.cobrar_venta())
        self.root.bind('<F5>', lambda e: self.cargar_productos())
        self.root.bind('<F9>', lambda e: self.mostrar_catalogo())
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
        # Cargar clientes después de crear la UI (con delay para no bloquear)
        self.root.after(500, self.cargar_clientes)
    
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
        self._cargar_maestros_locales()
        if hasattr(self, 'lista_combo'):
            self.lista_combo['values'] = self._listas_combo_values
        if hasattr(self, 'pago_frame'):
            self._build_medios_pago(self.pago_frame)
        self.cargar_productos()
        messagebox.showinfo("Listo", "Sincronización completada")
    
    def cargar_clientes(self):
        """Cargar clientes: local SQLite y sincronizar si hay red."""
        session = get_session()
        local = session.query(Cliente).limit(5000).all()
        session.close()
        if local:
            self.clientes_disponibles = [
                {
                    'id': c.id_servidor,
                    'nombre': c.nombre,
                    'documento': c.documento,
                    'display': c.display or f"{c.id_servidor}-{c.nombre}",
                }
                for c in local
            ]
        try:
            if self.connection_monitor.check_connection():
                self.sync_manager.sync_clientes()
                session = get_session()
                local = session.query(Cliente).limit(5000).all()
                session.close()
                if local:
                    self.clientes_disponibles = [
                        {
                            'id': c.id_servidor,
                            'nombre': c.nombre,
                            'documento': c.documento,
                            'display': c.display or f"{c.id_servidor}-{c.nombre}",
                        }
                        for c in local
                    ]
                    return
            if self.connection_monitor.check_connection():
                import requests
                # Intentar primero con el endpoint API
                url = f"{config.SERVER_URL}/api/clientes.php"
                params = {'id_cliente': self.id_cliente_moon}
                print(f"🔍 Cargando clientes desde: {url}")
                response = requests.get(url, params=params, timeout=10)
                
                print(f"🔍 Status code clientes: {response.status_code}")
                
                if response.status_code == 200:
                    try:
                        clientes_data = response.json()
                        if isinstance(clientes_data, list) and len(clientes_data) > 0:
                            self.clientes_disponibles = clientes_data
                            print(f"✅ Cargados {len(self.clientes_disponibles)} clientes")
                            return
                    except:
                        pass
                
                # Si falla, intentar con ajax/clientes.ajax.php usando GET con id_cliente
                print("⚠️ Endpoint API no disponible, intentando con ajax...")
                url_ajax = f"{config.SERVER_URL}/ajax/clientes.ajax.php"
                params_ajax = {'id_cliente': self.id_cliente_moon, 'listarClientes': '1'}
                print(f"🔍 Intentando ajax con: {url_ajax}?id_cliente={self.id_cliente_moon}&listarClientes=1")
                response_ajax = requests.get(url_ajax, params=params_ajax, timeout=10)
                
                print(f"🔍 Status code ajax: {response_ajax.status_code}")
                
                if response_ajax.status_code == 200:
                    try:
                        clientes_data = response_ajax.json()
                        print(f"🔍 Respuesta ajax recibida: {len(clientes_data) if isinstance(clientes_data, list) else 'no es lista'} elementos")
                        if isinstance(clientes_data, list) and len(clientes_data) > 0:
                            # Convertir formato del ajax al formato esperado
                            self.clientes_disponibles = []
                            for cliente in clientes_data:
                                self.clientes_disponibles.append({
                                    'id': int(cliente.get('id', 1)),
                                    'nombre': cliente.get('nombre', 'Sin nombre'),
                                    'documento': str(cliente.get('documento', '')),
                                    'telefono': str(cliente.get('telefono', '')),
                                    'display': f"{cliente.get('id', 1)}-{cliente.get('nombre', 'Sin nombre')}"
                                })
                            print(f"✅ Cargados {len(self.clientes_disponibles)} clientes desde ajax")
                            return
                        else:
                            print(f"⚠️ Respuesta ajax vacía o inválida: {clientes_data[:200] if not isinstance(clientes_data, list) else 'lista vacía'}")
                    except Exception as e:
                        print(f"⚠️ Error parseando respuesta ajax: {e}")
                        print(f"🔍 Respuesta raw: {response_ajax.text[:500]}")
                else:
                    print(f"⚠️ Error HTTP ajax {response_ajax.status_code}: {response_ajax.text[:200]}")
                
                print("⚠️ No se pudieron cargar clientes, usando cliente por defecto")
                self.clientes_disponibles = [{'id': 1, 'nombre': 'Consumidor Final', 'display': '1-Consumidor Final'}]
            else:
                print("⚠️ Sin conexión, usando cliente por defecto")
                self.clientes_disponibles = [{'id': 1, 'nombre': 'Consumidor Final', 'display': '1-Consumidor Final'}]
        except Exception as e:
            print(f"❌ Error cargando clientes: {e}")
            import traceback
            traceback.print_exc()
            self.clientes_disponibles = [{'id': 1, 'nombre': 'Consumidor Final', 'display': '1-Consumidor Final'}]
    
    def buscar_cliente(self):
        """Abrir ventana para buscar y seleccionar cliente"""
        # Recargar clientes antes de abrir la ventana
        self.cargar_clientes()
        
        ventana_cliente = tk.Toplevel(self.root)
        ventana_cliente.title("🔍 Buscar Cliente")
        ventana_cliente.configure(bg="#ecf0f5")
        ventana_cliente.transient(self.root)
        ventana_cliente.grab_set()
        
        responsive.ajustar_ventana(ventana_cliente, 700, 600, min_ancho=520, min_alto=420)
        
        header = tk.Frame(ventana_cliente, bg="#667eea", height=50)
        header.pack(fill=tk.X)
        tk.Label(header, text="🔍 Buscar Cliente", font=("Arial", 16, "bold"),
                bg="#667eea", fg="white").pack(pady=12)
        
        # Botón recargar clientes
        btn_recargar = tk.Button(header, text="🔄 Recargar", bg="#764ba2", fg="white",
                                font=("Arial", 9, "bold"), command=lambda: self.recargar_clientes_en_ventana(ventana_cliente, cliente_tree),
                                relief=tk.FLAT, padx=10, pady=5, cursor="hand2")
        btn_recargar.pack(side=tk.RIGHT, padx=10, pady=10)
        
        search_frame = tk.Frame(ventana_cliente, bg="#ecf0f5")
        search_frame.pack(fill=tk.X, padx=20, pady=15)
        
        search_var_cliente = tk.StringVar()
        search_entry = tk.Entry(search_frame, textvariable=search_var_cliente, font=("Arial", 12),
                               relief=tk.SOLID, bd=1)
        search_entry.pack(side=tk.LEFT, fill=tk.X, expand=True, ipady=8, padx=(0, 10))
        search_entry.focus()
        
        tree_frame = tk.Frame(ventana_cliente, bg="white")
        tree_frame.pack(fill=tk.BOTH, expand=True, padx=20, pady=(0, 20))
        
        cliente_tree = ttk.Treeview(tree_frame, columns=("id", "nombre", "documento", "telefono"), show="headings")
        cliente_tree.heading("id", text="ID")
        cliente_tree.heading("nombre", text="Nombre")
        cliente_tree.heading("documento", text="Documento")
        cliente_tree.heading("telefono", text="Teléfono")
        
        cliente_tree.column("id", width=60, anchor=tk.CENTER)
        cliente_tree.column("nombre", width=300)
        cliente_tree.column("documento", width=150, anchor=tk.CENTER)
        cliente_tree.column("telefono", width=150)
        
        scrollbar = ttk.Scrollbar(tree_frame, orient=tk.VERTICAL, command=cliente_tree.yview)
        cliente_tree.configure(yscrollcommand=scrollbar.set)
        
        cliente_tree.pack(side=tk.LEFT, fill=tk.BOTH, expand=True)
        scrollbar.pack(side=tk.RIGHT, fill=tk.Y)
        
        def actualizar_lista():
            """Actualizar la lista de clientes según el filtro"""
            filtro = search_var_cliente.get().lower()
            for item in cliente_tree.get_children():
                cliente_tree.delete(item)
            
            if not self.clientes_disponibles or len(self.clientes_disponibles) == 0:
                cliente_tree.insert("", tk.END, values=("", "No hay clientes disponibles", "", ""))
                return
            
            for cliente in self.clientes_disponibles:
                nombre = cliente.get('nombre', 'Sin nombre')
                display = cliente.get('display', f"{cliente.get('id', '')}-{nombre}")
                documento = str(cliente.get('documento', ''))
                
                # Si no hay filtro o el filtro coincide
                if not filtro or filtro in display.lower() or filtro in nombre.lower() or filtro in documento.lower():
                    cliente_tree.insert("", tk.END, values=(
                        cliente.get('id', ''),
                        nombre,
                        documento,
                        cliente.get('telefono', '')
                    ))
        
        def buscar_cliente():
            actualizar_lista()
        
        search_entry.bind("<KeyRelease>", lambda e: buscar_cliente())
        tk.Button(search_frame, text="🔍 Buscar", bg="#667eea", fg="white",
                 font=("Arial", 10, "bold"), command=buscar_cliente, relief=tk.FLAT, padx=20, pady=8).pack(side=tk.LEFT)
        
        # Cargar todos los clientes inicialmente
        if not hasattr(self, 'clientes_disponibles') or not self.clientes_disponibles:
            self.clientes_disponibles = [{'id': 1, 'nombre': 'Consumidor Final', 'display': '1-Consumidor Final'}]
        
        actualizar_lista()
        
        def seleccionar_cliente(event=None):
            selection = cliente_tree.selection()
            if not selection:
                messagebox.showwarning("Seleccionar", "Seleccione un cliente de la lista")
                return
            
            item = cliente_tree.item(selection[0])
            cliente_id_str = item['values'][0]
            
            if not cliente_id_str or cliente_id_str == "":
                return
            
            try:
                cliente_id = int(cliente_id_str)
                cliente_nombre = item['values'][1]
                
                self.cliente_id = cliente_id
                self.cliente_seleccionado.set(f"{cliente_id}-{cliente_nombre}")
                ventana_cliente.destroy()
                messagebox.showinfo("Cliente seleccionado", f"Cliente seleccionado: {cliente_nombre}")
            except (ValueError, IndexError):
                messagebox.showerror("Error", "Error al seleccionar el cliente")
        
        cliente_tree.bind("<Double-1>", seleccionar_cliente)
        cliente_tree.bind("<Return>", seleccionar_cliente)
        
        # Botón seleccionar
        btn_frame = tk.Frame(ventana_cliente, bg="#ecf0f5")
        btn_frame.pack(fill=tk.X, padx=20, pady=10)
        
        tk.Button(btn_frame, text="✅ Seleccionar Cliente", bg="#27ae60", fg="white",
                 font=("Arial", 12, "bold"), command=seleccionar_cliente, relief=tk.FLAT,
                 padx=30, pady=10, cursor="hand2").pack(side=tk.LEFT, fill=tk.X, expand=True, padx=5)
        
        tk.Button(btn_frame, text="❌ Cancelar", bg="#e74c3c", fg="white",
                 font=("Arial", 12, "bold"), command=ventana_cliente.destroy, relief=tk.FLAT,
                 padx=30, pady=10, cursor="hand2").pack(side=tk.LEFT, fill=tk.X, expand=True, padx=5)
        
        ventana_cliente.focus_force()
    
    def recargar_clientes_en_ventana(self, ventana, tree):
        """Recargar clientes y actualizar el árbol"""
        # Mostrar mensaje de carga
        for item in tree.get_children():
            tree.delete(item)
        tree.insert("", tk.END, values=("", "🔄 Cargando clientes...", "", ""))
        ventana.update()
        
        self.cargar_clientes()
        
        # Limpiar y recargar
        for item in tree.get_children():
            tree.delete(item)
        
        if not self.clientes_disponibles or len(self.clientes_disponibles) == 0:
            tree.insert("", tk.END, values=("", "⚠️ No hay clientes disponibles", "", ""))
        else:
            for cliente in self.clientes_disponibles:
                tree.insert("", tk.END, values=(
                    cliente.get('id', ''),
                    cliente.get('nombre', 'Sin nombre'),
                    cliente.get('documento', ''),
                    cliente.get('telefono', '')
                ))
    
    def _cargar_maestros_locales(self):
        session = get_session()
        try:
            self.clientes_disponibles = [
                {
                    'id': c.id_servidor,
                    'nombre': c.nombre,
                    'documento': c.documento,
                    'display': c.display or f"{c.id_servidor}-{c.nombre}",
                }
                for c in session.query(Cliente).limit(5000).all()
            ]
            if not self.clientes_disponibles:
                self.clientes_disponibles = [{'id': 1, 'display': '1-Consumidor Final', 'nombre': 'Consumidor Final'}]
            listas = session.query(ListaPrecio).filter_by(id_empresa=config.ID_EMPRESA, activo=1).order_by(ListaPrecio.orden).all()
            self._listas_db = listas
            self._listas_combo_values = ["Precio venta (defecto)"] + [f"{l.codigo} - {l.nombre}" for l in listas]
            self.listas_config = {
                l.codigo: {
                    'base_precio': l.base_precio,
                    'tipo_descuento': l.tipo_descuento,
                    'valor_descuento': l.valor_descuento,
                }
                for l in listas
            }
        finally:
            session.close()

    def _build_medios_pago(self, parent):
        for w in self._medios_pago_widgets:
            w.destroy()
        self._medios_pago_widgets = []
        session = get_session()
        medios = session.query(MedioPago).filter_by(activo=1).order_by(MedioPago.orden).all()
        session.close()
        esc = getattr(self, "esc", None) or responsive.Escala(1.0)
        if not medios:
            medios_data = [("EF", "Efectivo"), ("TD", "Tarjeta Débito"), ("TC", "Tarjeta Crédito")]
            for cod, nom in medios_data:
                rb = tk.Radiobutton(
                    parent, text=nom, variable=self.medio_pago_seleccionado, value=cod,
                    font=esc.fuente(11), bg="white", padx=esc.px(12), pady=esc.px(5), cursor="hand2",
                    anchor=tk.W, justify=tk.LEFT,
                    command=lambda v=cod: setattr(self, 'medio_pago', v),
                )
                rb.pack(fill=tk.X, pady=1)
                self._medios_pago_widgets.append(rb)
            return
        first = medios[0].codigo
        self.medio_pago_seleccionado.set(first)
        self.medio_pago = first
        for m in medios:
            label = f"{m.nombre} ({m.codigo})"
            rb = tk.Radiobutton(
                parent, text=label, variable=self.medio_pago_seleccionado, value=m.codigo,
                font=esc.fuente(11), bg="white", padx=esc.px(12), pady=esc.px(5), cursor="hand2",
                anchor=tk.W, justify=tk.LEFT,
                command=lambda v=m.codigo: setattr(self, 'medio_pago', v),
            )
            rb.pack(fill=tk.X, pady=1)
            self._medios_pago_widgets.append(rb)

    def _precio_producto(self, producto):
        cod = self.lista_precio_codigo.get()
        if not cod and self._listas_db:
            cod = self._listas_db[0].codigo
        cfg = self.listas_config.get(cod) if cod else None
        prod = {
            'precio_venta': producto.precio_venta,
            'precio_compra': producto.precio_compra or 0,
        }
        return precio_con_lista(prod, cfg)

    def _aplicar_lista_carrito(self):
        session = get_session()
        for item in self.productos_carrito:
            p = session.query(Producto).filter_by(id=item['id']).first()
            if p:
                item['precio'] = self._precio_producto(p)
                item['subtotal'] = item['precio'] * item['cantidad']
        session.close()
        self.actualizar_carrito()

    def nueva_venta(self):
        if self.productos_carrito:
            if not messagebox.askyesno("Nueva venta", "¿Limpiar carrito actual?"):
                return
        self.limpiar_carrito()

    def accion_facturar_online(self):
        if not self.connection_monitor.check_connection():
            messagebox.showwarning("Sin conexión", "La facturación AFIP requiere internet.")
            return
        online_actions.facturar_venta_servidor(0)

    def _descontar_stock_local(self, productos_json):
        session = get_session()
        try:
            for prod in productos_json:
                pid = prod.get('id') or prod.get('id_producto')
                cant = float(prod.get('cantidad', 1))
                p = session.query(Producto).filter_by(id=pid).first()
                if p and p.stock is not None:
                    p.stock = max(0, float(p.stock) - cant)
            session.commit()
        finally:
            session.close()

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
        filtro = self.search_var.get().strip()
        scale = parse_scale_barcode(filtro) if filtro else None
        if scale:
            session = get_session()
            producto = session.query(Producto).filter(
                (Producto.codigo == scale['codigo']) | (Producto.codigo.contains(scale['codigo']))
            ).first()
            session.close()
            if producto:
                precio = self._precio_producto(producto)
                cant = scale['cantidad']
                if producto.stock and producto.stock < cant:
                    messagebox.showwarning("Stock", f"Stock insuficiente ({producto.stock})")
                    return
                self.productos_carrito.append({
                    'id': producto.id, 'codigo': producto.codigo,
                    'descripcion': producto.descripcion, 'precio': precio,
                    'cantidad': cant, 'subtotal': precio * cant,
                    'categoria': producto.categoria or '', 'stock': producto.stock or 0,
                    'precio_compra': producto.precio_compra or 0,
                })
                self.actualizar_carrito()
                self.search_var.set("")
                return
        self.cargar_productos(filtro)
    
    def mostrar_catalogo(self):
        """Ventana de catálogo completo"""
        cat_window = tk.Toplevel(self.root)
        cat_window.title("📋 Catálogo de Productos")
        cat_window.configure(bg="#ecf0f5")
        responsive.ajustar_ventana(cat_window, 1000, 700, min_ancho=640, min_alto=440)
        
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

        precio = self._precio_producto(producto)
        nueva_cant = 1
        for i, item_carrito in enumerate(self.productos_carrito):
            if item_carrito['id'] == producto.id:
                nueva_cant = item_carrito['cantidad'] + 1
                if producto.stock and producto.stock < nueva_cant:
                    messagebox.showwarning("Stock", f"Stock insuficiente ({producto.stock})")
                    return
                item_carrito['cantidad'] = nueva_cant
                item_carrito['precio'] = precio
                item_carrito['subtotal'] = nueva_cant * precio
                self.actualizar_carrito()
                self.productos_tree.focus_set()
                return

        if producto.stock is not None and producto.stock < 1:
            messagebox.showwarning("Stock", "Sin stock disponible")
            return

        self.productos_carrito.append({
            'id': producto.id, 'codigo': producto.codigo, 'descripcion': producto.descripcion,
            'precio': precio, 'cantidad': 1, 'subtotal': precio,
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
            if hasattr(self, 'clientes_disponibles') and self.clientes_disponibles:
                for cliente in self.clientes_disponibles:
                    if cliente.get('id') == self.cliente_id:
                        cliente_nombre = cliente.get('nombre', cliente.get('display', 'Consumidor Final').split('-', 1)[-1] if '-' in cliente.get('display', '') else 'Consumidor Final')
                        break
            
            session = get_session()
            venta = Venta(
                fecha=datetime.now(),
                cliente=cliente_nombre,
                id_cliente=getattr(self, 'cliente_id', 1),
                productos=productos_json,
                total=self.total_venta,
                metodo_pago=self.medio_pago,
                sincronizado=False,
                creado_local=True
            )
            session.add(venta)
            session.commit()
            venta_id = venta.id
            self._descontar_stock_local(productos_json)
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
            if sincronizado:
                online_actions.imprimir_ticket_local(venta_id)

            self.productos_carrito = []
            self.cargar_productos()
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
        ventana.configure(bg="#ecf0f5")
        responsive.ajustar_ventana(ventana, 1200, 700, min_ancho=700, min_alto=440)
        
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
        """Muestra la caja; el mainloop lo maneja main.py."""
        self.root.deiconify()
        self.root.lift()
        self.root.focus_force()
