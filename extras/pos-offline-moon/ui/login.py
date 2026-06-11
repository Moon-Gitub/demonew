#!/usr/bin/env python3
# -*- coding: utf-8 -*-

import tkinter as tk
from tkinter import messagebox
import threading
from config import config
from ui import theme


class LoginWindow:
    def __init__(self, parent, auth_manager, sync_manager, connection_monitor, on_success=None):
        self.parent = parent
        self.auth_manager = auth_manager
        self.sync_manager = sync_manager
        self.connection_monitor = connection_monitor
        self.on_success = on_success
        self.id_cliente_moon = config.ID_CLIENTE_MOON
        self.setup_login_ui()
        self.window.after(500, self.check_initial_sync)

    def setup_login_ui(self):
        self.window = tk.Toplevel(self.parent)
        self.window.title("POS Offline Moon - Login")
        self.window.geometry("450x580")
        self.window.configure(bg=theme.COLOR_PRIMARY)
        self.window.resizable(False, False)
        self.window.transient(self.parent)
        self.window.grab_set()

        main_frame = tk.Frame(self.window, bg=theme.COLOR_PRIMARY)
        main_frame.pack(fill=tk.BOTH, expand=True, padx=40, pady=40)

        tk.Label(main_frame, text="POS | Moon", font=("Arial", 28, "bold"),
                 bg=theme.COLOR_PRIMARY, fg="white").pack(pady=(0, 10))
        tk.Label(main_frame, text="Sistema Offline", font=("Arial", 12),
                 bg=theme.COLOR_PRIMARY, fg="white").pack(pady=(0, 20))

        login_frame = tk.Frame(main_frame, bg="white", relief=tk.RAISED, bd=2)
        login_frame.pack(fill=tk.BOTH, expand=True)

        tk.Label(login_frame, text="Iniciar Sesión", font=("Arial", 16, "bold"), bg="white").pack(pady=20)

        auto_txt = "Auto-login: activo" if config.AUTO_LOGIN_ENABLED else "Auto-login: desactivado"
        tk.Label(login_frame, text=auto_txt, bg="white", fg="#666", font=("Arial", 9)).pack()

        tk.Label(login_frame, text="Usuario:", bg="white", anchor="w", font=("Arial", 10)).pack(fill=tk.X, padx=20, pady=(10, 5))
        self.usuario_entry = tk.Entry(login_frame, font=("Arial", 12), width=30, relief=tk.SOLID, bd=1)
        self.usuario_entry.pack(padx=20, pady=(0, 10))
        if config.AUTO_LOGIN_USER:
            self.usuario_entry.insert(0, config.AUTO_LOGIN_USER)
        self.usuario_entry.focus()

        tk.Label(login_frame, text="Contraseña:", bg="white", anchor="w", font=("Arial", 10)).pack(fill=tk.X, padx=20, pady=(10, 5))
        self.password_entry = tk.Entry(login_frame, font=("Arial", 12), show="*", width=30, relief=tk.SOLID, bd=1)
        self.password_entry.pack(padx=20, pady=(0, 20))
        self.password_entry.bind("<Return>", lambda e: self.login())

        online = self.connection_monitor.check_connection()
        self.connection_status = tk.Label(
            login_frame,
            text="🟢 En línea" if online else "🔴 Sin conexión",
            bg="white", fg="#666", font=("Arial", 9),
        )
        self.connection_status.pack(pady=5)

        tk.Button(login_frame, text="Ingresar", bg=theme.COLOR_PRIMARY, fg="white", font=("Arial", 12, "bold"),
                  command=self.login, relief=tk.FLAT, padx=30, pady=10, cursor="hand2").pack(pady=15)
        tk.Button(login_frame, text="🔄 Sincronizar", bg=theme.COLOR_SECONDARY, fg="white", font=("Arial", 10),
                  command=self.manual_sync, relief=tk.FLAT, padx=20, pady=5, cursor="hand2").pack(pady=5)

        self.window.update_idletasks()
        w, h = max(450, self.window.winfo_reqwidth()), max(580, self.window.winfo_reqheight())
        x = (self.window.winfo_screenwidth() // 2) - (w // 2)
        y = (self.window.winfo_screenheight() // 2) - (h // 2)
        self.window.geometry(f"{w}x{h}+{x}+{y}")
        # Obligatorio si la ventana raíz está en withdraw() (si no, no se ve nada en Linux)
        self.window.deiconify()
        self.window.lift()
        self.window.attributes("-topmost", True)
        self.window.after(200, lambda: self.window.attributes("-topmost", False))
        self.window.focus_force()

    def check_initial_sync(self):
        if self.connection_monitor.check_connection():
            self.connection_status.config(text="🟢 En línea - Sincronizando...")

            def sync_thread():
                try:
                    self.sync_manager.sync_all(id_cliente_moon=self.id_cliente_moon, silent=True)
                    from database import get_session, Usuario
                    session = get_session()
                    count = session.query(Usuario).count()
                    session.close()
                    self.window.after(0, lambda: self.connection_status.config(text=f"🟢 En línea ({count} usuarios)"))
                except Exception:
                    self.window.after(0, lambda: self.connection_status.config(text="🟢 En línea - Error sync"))

            threading.Thread(target=sync_thread, daemon=True).start()

    def manual_sync(self, show_message=True):
        if not self.connection_monitor.check_connection():
            if show_message:
                messagebox.showwarning("Sin conexión", "No hay conexión a internet")
            return
        if show_message:
            messagebox.showinfo("Sincronizando", "Sincronizando datos...")

        def sync_thread():
            try:
                self.sync_manager.sync_all(id_cliente_moon=self.id_cliente_moon, silent=False)
                if show_message:
                    self.window.after(0, lambda: messagebox.showinfo("Listo", "Sincronización completada"))
            except Exception as e:
                if show_message:
                    self.window.after(0, lambda: messagebox.showerror("Error", str(e)))

        threading.Thread(target=sync_thread, daemon=True).start()

    def login(self):
        usuario = self.usuario_entry.get().strip()
        password = self.password_entry.get()
        if not usuario or not password:
            messagebox.showwarning("Campos vacíos", "Complete usuario y contraseña")
            return

        resultado = self.auth_manager.login(usuario, password)
        if not resultado["success"] and self.connection_monitor.check_connection():
            self.manual_sync(show_message=False)
            resultado = self.auth_manager.login(usuario, password)

        if not resultado["success"]:
            msg = resultado.get("message", "Error en login")
            if resultado.get("bloqueado"):
                msg += f"\n\nSaldo: ${resultado.get('saldo', 0):.2f}"
            messagebox.showerror("Error de login", msg)
            return

        if self.connection_monitor.check_connection():
            self.sync_manager.sync_estado_cuenta(self.id_cliente_moon)

        estado = self.auth_manager.verificar_estado_cuenta_local(self.id_cliente_moon)
        if not estado["activo"]:
            msg = estado["mensaje"]
            if estado.get("saldo"):
                msg += f"\n\nSaldo: ${estado['saldo']:.2f}"
            messagebox.showerror("Acceso bloqueado", msg)
            return

        guardar = messagebox.askyesno(
            "Credenciales",
            "¿Guardar usuario/contraseña para inicio automático mañana?",
        )
        if guardar:
            from config import config as cfg
            cfg.save_secrets(usuario, password)

        self.window.destroy()
        if self.on_success:
            self.on_success()
