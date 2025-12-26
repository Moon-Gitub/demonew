#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
NewMoon DB Migrator - Herramienta de migración de bases de datos MySQL
Interfaz gráfica con Tkinter para mapear y migrar datos entre bases de datos
"""

import tkinter as tk
from tkinter import ttk, filedialog, messagebox, scrolledtext
import re
import os
from datetime import datetime
from typing import Dict, List, Tuple, Optional


class SQLParser:
    """Parser para archivos SQL"""
    
    def __init__(self):
        self.tables = {}  # {table_name: {fields: [...], create_statement: str}}
        self.insert_counts = {}  # {table_name: count}
        self.sql_content = ""
    
    def parse_file(self, file_path: str) -> bool:
        """Parsea un archivo SQL"""
        try:
            with open(file_path, 'r', encoding='utf-8', errors='ignore') as f:
                self.sql_content = f.read()
            
            self._extract_tables()
            self._count_inserts()
            return True
        except Exception as e:
            messagebox.showerror("Error", f"Error parseando archivo: {e}")
            return False
    
    def _extract_tables(self):
        """Extrae todas las tablas CREATE TABLE del SQL - Parser robusto que captura TODOS los campos"""
        self.tables = {}
        # Buscar todas las ocurrencias de CREATE TABLE usando conteo de paréntesis
        for match in re.finditer(r'CREATE\s+TABLE\s+(?:IF\s+NOT\s+EXISTS\s+)?`?(\w+)`?\s*\(', self.sql_content, re.IGNORECASE):
            table_name = match.group(1)
            # Encontrar el inicio del bloque de campos (después del paréntesis)
            fields_start = match.end()
            
            # Encontrar el cierre del paréntesis correspondiente contando paréntesis
            paren_count = 1
            pos = fields_start
            while pos < len(self.sql_content) and paren_count > 0:
                if self.sql_content[pos] == '(':
                    paren_count += 1
                elif self.sql_content[pos] == ')':
                    paren_count -= 1
                pos += 1
            
            fields_text = self.sql_content[fields_start:pos-1]
            
            fields = []
            seen_fields = set()
            
            # MÉTODO SIMPLE: línea por línea, buscar `campo` y capturar hasta el final de línea
            lines = fields_text.split('\n')
            
            for line in lines:
                line_stripped = line.strip()
                
                # Saltar líneas vacías y comentarios
                if not line_stripped or line_stripped.startswith('--'):
                    continue
                
                # Buscar patrón: `nombre_campo` seguido de tipo y definición
                # Capturar TODO hasta el final de la línea (puede tener comas en decimal(10,2))
                field_match = re.match(r'`(\w+)`\s+(.+)', line_stripped, re.IGNORECASE)
                
                if field_match:
                    field_name = field_match.group(1)
                    # Capturar todo y quitar la coma final si existe
                    field_def = field_match.group(2).strip().rstrip(',')
                    
                    # Evitar duplicados
                    if field_name in seen_fields:
                        continue
                    seen_fields.add(field_name)
                    
                    # Limpiar la definición
                    field_def = ' '.join(field_def.split())
                    
                    # Ignorar si es una constraint o key
                    if any(keyword in field_def.upper() for keyword in ['PRIMARY KEY', 'FOREIGN KEY', 'UNIQUE KEY', 'INDEX', 'CONSTRAINT']):
                        continue
                    
                    # Ignorar si el nombre del campo es una palabra clave
                    if field_name.upper() in ['PRIMARY', 'KEY', 'UNIQUE', 'INDEX', 'FOREIGN', 'CONSTRAINT']:
                        continue
                    
                    # Extraer tipo de dato
                    field_type = self._parse_field_type(field_def)
                    
                    fields.append({
                        'name': field_name,
                        'type': field_type,
                        'definition': field_def
                    })
            
            if fields:
                # Guardar el CREATE statement completo
                create_statement = self.sql_content[match.start():pos]
                
                self.tables[table_name] = {
                    'fields': fields,
                    'create_statement': create_statement
                }
    
    def _parse_field_definition(self, field_str: str) -> Optional[Dict]:
        """Parsea una definición de campo completa"""
        # Buscar nombre del campo con backticks
        match = re.match(r'^`?(\w+)`?\s+(.+?)(?:,|$)', field_str.strip(), re.IGNORECASE)
        if not match:
            return None
        
        field_name = match.group(1)
        field_def = match.group(2).strip().rstrip(',')
        
        # Ignorar si es una constraint o key
        if any(keyword in field_def.upper() for keyword in ['PRIMARY KEY', 'FOREIGN KEY', 'UNIQUE KEY', 'INDEX', 'CONSTRAINT']):
            return None
        
        # Ignorar si el nombre del campo es una palabra clave
        if field_name.upper() in ['PRIMARY', 'KEY', 'UNIQUE', 'INDEX', 'FOREIGN', 'CONSTRAINT']:
            return None
        
        field_type = self._parse_field_type(field_def)
        
        return {
            'name': field_name,
            'type': field_type,
            'definition': field_def
        }
    
    def _parse_field_type(self, definition: str) -> str:
        """Extrae el tipo de dato de una definición de campo"""
        # Buscar tipo después del nombre
        match = re.search(r'(\w+(?:\([^)]+\))?)', definition, re.IGNORECASE)
        if match:
            return match.group(1).upper()
        return 'UNKNOWN'
    
    def _count_inserts(self):
        """Cuenta los INSERT statements por tabla"""
        self.insert_counts = {}
        # Buscar todos los INSERT INTO
        pattern = r'INSERT\s+INTO\s+`?(\w+)`?\s*\([^)]+\)\s*VALUES'
        matches = re.finditer(pattern, self.sql_content, re.IGNORECASE | re.DOTALL)
        
        for match in matches:
            table_name = match.group(1)
            # Contar tuplas en el VALUES
            start_pos = match.end()
            count = self._count_rows_in_insert(start_pos)
            self.insert_counts[table_name] = self.insert_counts.get(table_name, 0) + count
    
    def _count_rows_in_insert(self, start_pos: int) -> int:
        """Cuenta filas en un bloque VALUES"""
        count = 0
        paren_count = 0
        in_string = False
        string_char = None
        escape_next = False
        i = start_pos
        
        while i < len(self.sql_content):
            char = self.sql_content[i]
            
            if escape_next:
                escape_next = False
                i += 1
                continue
            
            if char == '\\':
                escape_next = True
                i += 1
                continue
            
            if char in ("'", '"') and not escape_next:
                if not in_string:
                    in_string = True
                    string_char = char
                elif char == string_char:
                    in_string = False
                    string_char = None
            elif char == '(' and not in_string:
                if paren_count == 0:
                    count += 1
                paren_count += 1
            elif char == ')' and not in_string:
                paren_count -= 1
                if paren_count < 0:
                    break
            elif char == ';' and not in_string and paren_count == 0:
                break
            
            i += 1
        
        return count
    
    def get_table_fields(self, table_name: str) -> List[Dict]:
        """Obtiene los campos de una tabla"""
        if table_name in self.tables:
            return self.tables[table_name]['fields']
        return []
    
    def get_insert_count(self, table_name: str) -> int:
        """Obtiene la cantidad de registros INSERT de una tabla"""
        return self.insert_counts.get(table_name, 0)
    
    def extract_insert_statements(self, table_name: str) -> List[str]:
        """Extrae todos los INSERT statements de una tabla del SQL"""
        inserts = []
        # Buscar todos los INSERT INTO para esta tabla
        pattern = rf'INSERT\s+INTO\s+`?{table_name}`?\s*\([^)]+\)\s*VALUES\s+(.+?);'
        matches = re.finditer(pattern, self.sql_content, re.IGNORECASE | re.DOTALL)
        
        for match in matches:
            # Extraer el INSERT completo
            start = match.start()
            # Buscar el final del INSERT (hasta el punto y coma)
            end = self.sql_content.find(';', match.end())
            if end > 0:
                insert_stmt = self.sql_content[start:end+1]
                inserts.append(insert_stmt)
        
        return inserts


class DBMigratorApp:
    """Aplicación principal de migración de bases de datos"""
    
    def __init__(self, root):
        self.root = root
        self.root.title("🌙 NewMoon DB Migrator")
        self.root.geometry("1200x800")
        self.root.configure(bg="#1a1a2e")
        
        # Colores
        self.bg_color = "#1a1a2e"
        self.accent_blue = "#00d9ff"
        self.accent_green = "#00ff88"
        self.text_color = "#ffffff"
        self.grid_color = "#16213e"
        
        # Datos
        self.parser_destino = SQLParser()
        self.parser_origen = SQLParser()
        self.mappings = {}  # {table_name: {origen_field: destino_field}}
        self.field_creations = {}  # {table_name: [{name, definition}]}
        self.current_table = None
        
        self._create_ui()
    
    def _create_ui(self):
        """Crea la interfaz de usuario"""
        # Header
        header = tk.Frame(self.root, bg=self.bg_color, pady=10)
        header.pack(fill=tk.X)
        
        title = tk.Label(header, text="🌙 NewMoon DB Migrator", 
                        font=("Arial", 20, "bold"),
                        bg=self.bg_color, fg=self.accent_blue)
        title.pack()
        
        # Botones de carga
        load_frame = tk.Frame(self.root, bg=self.bg_color, pady=10)
        load_frame.pack(fill=tk.X, padx=20)
        
        self.btn_destino = tk.Button(load_frame, text="📁 Cargar Destino", 
                                     command=self.load_destino,
                                     bg=self.accent_blue, fg="#000000",
                                     font=("Arial", 10, "bold"),
                                     padx=20, pady=5)
        self.btn_destino.pack(side=tk.LEFT, padx=5)
        
        self.label_destino = tk.Label(load_frame, text="No cargado", 
                                      bg=self.bg_color, fg=self.text_color,
                                      font=("Arial", 9))
        self.label_destino.pack(side=tk.LEFT, padx=10)
        
        self.btn_origen = tk.Button(load_frame, text="📁 Cargar Origen", 
                                    command=self.load_origen,
                                    bg=self.accent_green, fg="#000000",
                                    font=("Arial", 10, "bold"),
                                    padx=20, pady=5)
        self.btn_origen.pack(side=tk.LEFT, padx=5)
        
        self.label_origen = tk.Label(load_frame, text="No cargado", 
                                      bg=self.bg_color, fg=self.text_color,
                                      font=("Arial", 9))
        self.label_origen.pack(side=tk.LEFT, padx=10)
        
        # Frame principal con tabs
        main_frame = tk.Frame(self.root, bg=self.bg_color)
        main_frame.pack(fill=tk.BOTH, expand=True, padx=20, pady=10)
        
        # Tabs para tablas
        self.tabs_frame = tk.Frame(main_frame, bg=self.bg_color)
        self.tabs_frame.pack(fill=tk.X, pady=(0, 10))
        
        self.table_buttons = []
        
        # Canvas para scroll de campos
        canvas_frame = tk.Frame(main_frame, bg=self.bg_color)
        canvas_frame.pack(fill=tk.BOTH, expand=True)
        
        self.canvas = tk.Canvas(canvas_frame, bg=self.bg_color, highlightthickness=0)
        scrollbar = ttk.Scrollbar(canvas_frame, orient="vertical", command=self.canvas.yview)
        self.scrollable_frame = tk.Frame(self.canvas, bg=self.bg_color)
        
        self.scrollable_frame.bind(
            "<Configure>",
            lambda e: self.canvas.configure(scrollregion=self.canvas.bbox("all"))
        )
        
        self.canvas.create_window((0, 0), window=self.scrollable_frame, anchor="nw")
        self.canvas.configure(yscrollcommand=scrollbar.set)
        
        self.canvas.pack(side=tk.LEFT, fill=tk.BOTH, expand=True)
        scrollbar.pack(side=tk.RIGHT, fill=tk.Y)
        
        # Bind mousewheel
        self.canvas.bind_all("<MouseWheel>", self._on_mousewheel)
        
        # Frame de campos (se llenará dinámicamente)
        self.fields_frame = tk.Frame(self.scrollable_frame, bg=self.bg_color)
        self.fields_frame.pack(fill=tk.BOTH, expand=True, padx=10, pady=10)
        
        # Sección de campos destino sin mapear
        self.unmapped_frame = tk.Frame(main_frame, bg=self.grid_color, relief=tk.RAISED, bd=1)
        self.unmapped_frame.pack(fill=tk.X, padx=20, pady=5)
        
        self.unmapped_label = tk.Label(self.unmapped_frame, text="⚠️ Campos destino sin mapear: ", 
                                       bg=self.grid_color, fg=self.accent_blue,
                                       font=("Arial", 9, "bold"))
        self.unmapped_label.pack(side=tk.LEFT, padx=10, pady=5)
        
        # Botones de acción
        action_frame = tk.Frame(self.root, bg=self.bg_color, pady=10)
        action_frame.pack(fill=tk.X, padx=20, pady=10)
        
        btn_auto = tk.Button(action_frame, text="⚡ Auto-Mapear Tabla", 
                            command=self.auto_map,
                            bg=self.accent_blue, fg="#000000",
                            font=("Arial", 10, "bold"),
                            padx=20, pady=8)
        btn_auto.pack(side=tk.LEFT, padx=5)
        
        btn_auto_all = tk.Button(action_frame, text="🚀 Auto-Mapear TODAS", 
                                command=self.auto_map_all_tables,
                                bg="#9b59b6", fg="#ffffff",
                                font=("Arial", 10, "bold"),
                                padx=20, pady=8)
        btn_auto_all.pack(side=tk.LEFT, padx=5)
        
        btn_preview = tk.Button(action_frame, text="👁️ Vista Previa SQL", 
                               command=self.show_preview,
                               bg=self.accent_green, fg="#000000",
                               font=("Arial", 10, "bold"),
                               padx=20, pady=8)
        btn_preview.pack(side=tk.LEFT, padx=5)
        
        btn_generate = tk.Button(action_frame, text="🚀 Generar Scripts", 
                                command=self.generate_scripts,
                                bg="#ff6b6b", fg="#ffffff",
                                font=("Arial", 10, "bold"),
                                padx=20, pady=8)
        btn_generate.pack(side=tk.LEFT, padx=5)
    
    def _on_mousewheel(self, event):
        """Maneja el scroll del mouse"""
        self.canvas.yview_scroll(int(-1 * (event.delta / 120)), "units")
    
    def load_destino(self):
        """Carga el archivo SQL destino"""
        file_path = filedialog.askopenfilename(
            title="Seleccionar archivo SQL DESTINO",
            filetypes=[("SQL files", "*.sql"), ("All files", "*.*")]
        )
        
        if file_path:
            if self.parser_destino.parse_file(file_path):
                count = len(self.parser_destino.tables)
                self.label_destino.config(text=f"✅ {count} tablas", fg=self.accent_green)
                self.update_table_tabs()
    
    def load_origen(self):
        """Carga el archivo SQL origen"""
        file_path = filedialog.askopenfilename(
            title="Seleccionar archivo SQL ORIGEN",
            filetypes=[("SQL files", "*.sql"), ("All files", "*.*")]
        )
        
        if file_path:
            if self.parser_origen.parse_file(file_path):
                count = len(self.parser_origen.tables)
                self.label_origen.config(text=f"✅ {count} tablas", fg=self.accent_green)
                self.update_table_tabs()
    
    def update_table_tabs(self):
        """Actualiza los botones de tablas"""
        # Limpiar botones anteriores
        for btn in self.table_buttons:
            btn.destroy()
        self.table_buttons = []
        
        # Obtener tablas comunes
        destino_tables = set(self.parser_destino.tables.keys())
        origen_tables = set(self.parser_origen.tables.keys())
        common_tables = sorted(destino_tables & origen_tables)
        
        if not common_tables:
            return
        
        # Crear botones para cada tabla
        for table_name in common_tables:
            btn = tk.Button(self.tabs_frame, text=table_name,
                           command=lambda t=table_name: self.show_table(t),
                           bg=self.grid_color, fg=self.text_color,
                           font=("Arial", 9),
                           padx=10, pady=5, relief=tk.RAISED)
            btn.pack(side=tk.LEFT, padx=2)
            self.table_buttons.append(btn)
        
        # Mostrar primera tabla
        if common_tables:
            self.show_table(common_tables[0])
    
    def show_table(self, table_name: str):
        """Muestra los campos de una tabla para mapear - Vista comparativa completa"""
        self.current_table = table_name
        
        # Limpiar frame de campos
        for widget in self.fields_frame.winfo_children():
            widget.destroy()
        
        # Obtener campos
        origen_fields = {f['name']: f for f in self.parser_origen.get_table_fields(table_name)}
        destino_fields = {f['name']: f for f in self.parser_destino.get_table_fields(table_name)}
        
        origen_field_names = set(origen_fields.keys())
        destino_field_names = set(destino_fields.keys())
        
        # Inicializar mapeo si no existe
        if table_name not in self.mappings:
            self.mappings[table_name] = {}
        
        # Crear lista combinada de TODOS los campos (origen y destino)
        # Mostrar TODOS los campos de ambas tablas
        all_fields = sorted(origen_field_names | destino_field_names)
        
        # Header de la grilla mejorado
        header_frame = tk.Frame(self.fields_frame, bg=self.grid_color, relief=tk.RAISED, bd=2)
        header_frame.pack(fill=tk.X, pady=(0, 5))
        
        # Título de la tabla
        title_frame = tk.Frame(self.fields_frame, bg=self.bg_color)
        title_frame.pack(fill=tk.X, pady=(0, 10))
        tk.Label(title_frame, text=f"📋 Tabla: {table_name}", 
                bg=self.bg_color, fg=self.accent_blue, 
                font=("Arial", 12, "bold")).pack(side=tk.LEFT)
        record_count = self.parser_origen.get_insert_count(table_name)
        tk.Label(title_frame, text=f"({record_count} registros a migrar)", 
                bg=self.bg_color, fg="#888888", 
                font=("Arial", 10)).pack(side=tk.LEFT, padx=10)
        # Mostrar cantidad de campos encontrados
        total_campos = len(all_fields)
        campos_origen = len(origen_field_names)
        campos_destino = len(destino_field_names)
        tk.Label(title_frame, text=f"| Campos ORIGEN: {campos_origen} | Campos DESTINO: {campos_destino} | Total: {total_campos}", 
                bg=self.bg_color, fg="#00ff88", 
                font=("Arial", 9)).pack(side=tk.LEFT, padx=10)
        
        # Headers de columnas
        tk.Label(header_frame, text="Campo ORIGEN", width=18, anchor="w",
                bg=self.grid_color, fg=self.accent_blue, font=("Arial", 9, "bold"),
                padx=8, pady=8).pack(side=tk.LEFT)
        tk.Label(header_frame, text="Tipo ORIGEN", width=15, anchor="w",
                bg=self.grid_color, fg=self.accent_blue, font=("Arial", 9, "bold"),
                padx=8, pady=8).pack(side=tk.LEFT)
        tk.Label(header_frame, text="→", width=4,
                bg=self.grid_color, fg=self.text_color, font=("Arial", 11, "bold"),
                padx=5, pady=8).pack(side=tk.LEFT)
        tk.Label(header_frame, text="Campo DESTINO", width=18, anchor="w",
                bg=self.grid_color, fg=self.accent_green, font=("Arial", 9, "bold"),
                padx=8, pady=8).pack(side=tk.LEFT)
        tk.Label(header_frame, text="Tipo DESTINO", width=15, anchor="w",
                bg=self.grid_color, fg=self.accent_green, font=("Arial", 9, "bold"),
                padx=8, pady=8).pack(side=tk.LEFT)
        tk.Label(header_frame, text="Mapear a", width=18, anchor="w",
                bg=self.grid_color, fg="#ffaa00", font=("Arial", 9, "bold"),
                padx=8, pady=8).pack(side=tk.LEFT)
        tk.Label(header_frame, text="Estado", width=12, anchor="w",
                bg=self.grid_color, fg="#ffaa00", font=("Arial", 9, "bold"),
                padx=8, pady=8).pack(side=tk.LEFT)
        
        # Filas de campos - mostrar TODOS los campos de ambas tablas
        self.field_widgets = {}
        
        for field_name in all_fields:
            is_origen = field_name in origen_field_names
            is_destino = field_name in destino_field_names
            
            row_frame = tk.Frame(self.fields_frame, bg=self.bg_color, relief=tk.RAISED, bd=1)
            row_frame.pack(fill=tk.X, pady=1)
            
            # Campo ORIGEN
            if is_origen:
                origen_field = origen_fields[field_name]
                origen_type = origen_field['type']
                tk.Label(row_frame, text=field_name, width=18, anchor="w",
                        bg=self.bg_color, fg=self.accent_blue, font=("Arial", 9, "bold"),
                        padx=8, pady=6).pack(side=tk.LEFT)
                tk.Label(row_frame, text=origen_type, width=15, anchor="w",
                        bg=self.bg_color, fg="#888888", font=("Arial", 8),
                        padx=8, pady=6).pack(side=tk.LEFT)
            else:
                tk.Label(row_frame, text="---", width=18, anchor="w",
                        bg=self.bg_color, fg="#444444", font=("Arial", 9),
                        padx=8, pady=6).pack(side=tk.LEFT)
                tk.Label(row_frame, text="---", width=15, anchor="w",
                        bg=self.bg_color, fg="#444444", font=("Arial", 8),
                        padx=8, pady=6).pack(side=tk.LEFT)
            
            # Flecha
            tk.Label(row_frame, text="→", width=4,
                    bg=self.bg_color, fg=self.accent_blue, font=("Arial", 11, "bold"),
                    padx=5, pady=6).pack(side=tk.LEFT)
            
            # Campo DESTINO
            if is_destino:
                destino_field = destino_fields[field_name]
                destino_type = destino_field['type']
                tk.Label(row_frame, text=field_name, width=18, anchor="w",
                        bg=self.bg_color, fg=self.accent_green, font=("Arial", 9, "bold"),
                        padx=8, pady=6).pack(side=tk.LEFT)
                tk.Label(row_frame, text=destino_type, width=15, anchor="w",
                        bg=self.bg_color, fg="#888888", font=("Arial", 8),
                        padx=8, pady=6).pack(side=tk.LEFT)
            else:
                tk.Label(row_frame, text="---", width=18, anchor="w",
                        bg=self.bg_color, fg="#444444", font=("Arial", 9),
                        padx=8, pady=6).pack(side=tk.LEFT)
                tk.Label(row_frame, text="---", width=15, anchor="w",
                        bg=self.bg_color, fg="#444444", font=("Arial", 8),
                        padx=8, pady=6).pack(side=tk.LEFT)
            
            # Dropdown para mapear (solo si es campo origen)
            if is_origen:
                combo = ttk.Combobox(row_frame, width=16, state="readonly",
                                    font=("Arial", 9))
                combo['values'] = ['-- Ninguno --', '++ CREAR CAMPO ++'] + sorted(destino_field_names)
                combo.pack(side=tk.LEFT, padx=8, pady=6)
                
                # Estado
                status_label = tk.Label(row_frame, text="⚠️ Pendiente", width=12, anchor="w",
                                       bg=self.bg_color, fg="#ffaa00", font=("Arial", 9),
                                       padx=8, pady=6)
                status_label.pack(side=tk.LEFT)
                
                # Configurar valor actual si existe mapeo
                current_mapping = self.mappings[table_name].get(field_name)
                if current_mapping:
                    if current_mapping in destino_field_names:
                        combo.set(current_mapping)
                    elif current_mapping == "CREATE":
                        combo.set('++ CREAR CAMPO ++')
                    else:
                        combo.set('-- Ninguno --')
                else:
                    combo.set('-- Ninguno --')
                
                # Bind cambio
                combo.bind("<<ComboboxSelected>>", 
                          lambda e, f=field_name, c=combo, s=status_label: 
                          self.on_field_mapping_change(f, c, s))
                
                self.field_widgets[field_name] = {
                    'combo': combo,
                    'status': status_label,
                    'is_origen': True
                }
            else:
                # Campo solo en destino - mostrar que no necesita mapeo
                tk.Label(row_frame, text="---", width=18,
                        bg=self.bg_color, fg="#444444", font=("Arial", 9),
                        padx=8, pady=6).pack(side=tk.LEFT)
                tk.Label(row_frame, text="ℹ️ Solo destino", width=12, anchor="w",
                        bg=self.bg_color, fg="#00aaff", font=("Arial", 8),
                        padx=8, pady=6).pack(side=tk.LEFT)
        
        # Actualizar estado inicial
        self.update_mapping_status(table_name)
        self.update_unmapped_fields(table_name)
    
    def on_field_mapping_change(self, field_name: str, combo: ttk.Combobox, status_label: tk.Label):
        """Maneja el cambio de mapeo de un campo"""
        selected = combo.get()
        table_name = self.current_table
        
        if selected == '-- Ninguno --':
            self.mappings[table_name][field_name] = None
            status_label.config(text="⚪ Ignorado", fg="#888888")
        elif selected == '++ CREAR CAMPO ++':
            self.mappings[table_name][field_name] = "CREATE"
            status_label.config(text="🔵 Crear", fg=self.accent_blue)
            # Pedir definición del campo
            self.prompt_create_field(table_name, field_name)
        else:
            self.mappings[table_name][field_name] = selected
            status_label.config(text="✅ Mapeado", fg=self.accent_green)
        
        self.update_unmapped_fields(table_name)
    
    def prompt_create_field(self, table_name: str, field_name: str):
        """Solicita la definición del campo a crear"""
        dialog = tk.Toplevel(self.root)
        dialog.title("Crear Campo")
        dialog.geometry("500x200")
        dialog.configure(bg=self.bg_color)
        dialog.transient(self.root)
        dialog.grab_set()
        
        tk.Label(dialog, text=f"Definición del campo '{field_name}':", 
                bg=self.bg_color, fg=self.text_color, font=("Arial", 10, "bold"),
                pady=10).pack()
        
        tk.Label(dialog, text="Ejemplo: VARCHAR(255) NOT NULL DEFAULT ''", 
                bg=self.bg_color, fg="#888888", font=("Arial", 8)).pack()
        
        entry = tk.Entry(dialog, width=50, font=("Arial", 10))
        entry.pack(pady=10, padx=20)
        entry.focus()
        
        def save():
            definition = entry.get().strip()
            if definition:
                if table_name not in self.field_creations:
                    self.field_creations[table_name] = []
                self.field_creations[table_name].append({
                    'name': field_name,
                    'definition': definition
                })
                dialog.destroy()
        
        btn_save = tk.Button(dialog, text="Guardar", command=save,
                            bg=self.accent_green, fg="#000000",
                            font=("Arial", 10, "bold"),
                            padx=20, pady=5)
        btn_save.pack(pady=10)
        
        entry.bind("<Return>", lambda e: save())
    
    def update_mapping_status(self, table_name: str):
        """Actualiza el estado de los mapeos"""
        for field_name, widgets in self.field_widgets.items():
            mapping = self.mappings[table_name].get(field_name)
            if mapping:
                if mapping == "CREATE":
                    widgets['status'].config(text="🔵 Crear", fg=self.accent_blue)
                else:
                    widgets['status'].config(text="✅ Mapeado", fg=self.accent_green)
            else:
                widgets['status'].config(text="⚪ Ignorado", fg="#888888")
    
    def update_unmapped_fields(self, table_name: str):
        """Actualiza la lista de campos destino sin mapear"""
        destino_fields = self.parser_destino.get_table_fields(table_name)
        destino_field_names = set(f['name'] for f in destino_fields)
        
        # Campos mapeados
        mapped_destino = set()
        for origen_field, destino_field in self.mappings.get(table_name, {}).items():
            if destino_field and destino_field != "CREATE":
                mapped_destino.add(destino_field)
        
        unmapped = sorted(destino_field_names - mapped_destino)
        
        # Actualizar label
        if unmapped:
            text = "⚠️ Campos destino sin mapear: " + ", ".join(unmapped)
            self.unmapped_label.config(text=text, fg="#ffaa00")
        else:
            self.unmapped_label.config(text="✅ Todos los campos destino están mapeados", fg=self.accent_green)
    
    def auto_map(self):
        """Mapea automáticamente campos con el mismo nombre en la tabla actual"""
        if not self.current_table:
            messagebox.showwarning("Advertencia", "Selecciona una tabla primero")
            return
        
        table_name = self.current_table
        origen_fields = self.parser_origen.get_table_fields(table_name)
        destino_field_names = [f['name'] for f in self.parser_destino.get_table_fields(table_name)]
        
        if table_name not in self.mappings:
            self.mappings[table_name] = {}
        
        auto_mapped = 0
        for origen_field in origen_fields:
            field_name = origen_field['name']
            if field_name in destino_field_names and field_name not in self.mappings[table_name]:
                self.mappings[table_name][field_name] = field_name
                if field_name in self.field_widgets:
                    self.field_widgets[field_name]['combo'].set(field_name)
                    self.field_widgets[field_name]['status'].config(text="✅ Mapeado", fg=self.accent_green)
                auto_mapped += 1
        
        self.update_unmapped_fields(table_name)
        messagebox.showinfo("Auto-Mapeo", f"Se mapearon automáticamente {auto_mapped} campos en la tabla '{table_name}'")
    
    def auto_map_all_tables(self):
        """Auto-mapea TODAS las tablas automáticamente"""
        if not self.parser_origen.tables or not self.parser_destino.tables:
            messagebox.showwarning("Advertencia", "Carga primero los archivos SQL")
            return
        
        origen_tables = set(self.parser_origen.tables.keys())
        destino_tables = set(self.parser_destino.tables.keys())
        common_tables = origen_tables & destino_tables
        
        if not common_tables:
            messagebox.showwarning("Advertencia", "No hay tablas comunes entre origen y destino")
            return
        
        total_mapped = 0
        tables_mapped = 0
        
        for table_name in sorted(common_tables):
            if table_name not in self.mappings:
                self.mappings[table_name] = {}
            
            origen_fields = self.parser_origen.get_table_fields(table_name)
            destino_field_names = [f['name'] for f in self.parser_destino.get_table_fields(table_name)]
            
            table_mapped = 0
            for origen_field in origen_fields:
                field_name = origen_field['name']
                if field_name in destino_field_names and field_name not in self.mappings[table_name]:
                    self.mappings[table_name][field_name] = field_name
                    table_mapped += 1
            
            if table_mapped > 0:
                tables_mapped += 1
                total_mapped += table_mapped
        
        # Actualizar la tabla actual si está visible
        if self.current_table and self.current_table in common_tables:
            self.show_table(self.current_table)
        
        messagebox.showinfo("Auto-Mapeo Completo", 
                          f"✅ Se mapearon automáticamente {total_mapped} campos en {tables_mapped} tablas")
    
    def show_preview(self):
        """Muestra vista previa del SQL generado"""
        preview_window = tk.Toplevel(self.root)
        preview_window.title("Vista Previa SQL")
        preview_window.geometry("900x600")
        preview_window.configure(bg=self.bg_color)
        
        text_widget = scrolledtext.ScrolledText(preview_window, 
                                               bg="#0f0f1e", fg=self.text_color,
                                               font=("Courier", 10),
                                               wrap=tk.NONE)
        text_widget.pack(fill=tk.BOTH, expand=True, padx=10, pady=10)
        
        sql = self._generate_sql_preview()
        text_widget.insert("1.0", sql)
        text_widget.config(state=tk.DISABLED)
    
    def _generate_sql_preview(self) -> str:
        """Genera preview del SQL"""
        sql = "-- VISTA PREVIA DE SCRIPTS DE MIGRACIÓN\n"
        sql += f"-- Generado: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}\n\n"
        
        # Script 3: Migrar datos
        sql += "-- ============================================\n"
        sql += "-- 03_migrar_datos.sql\n"
        sql += "-- ============================================\n\n"
        
        for table_name in sorted(self.mappings.keys()):
            if not self.mappings[table_name]:
                continue
            
            destino_fields = [f['name'] for f in self.parser_destino.get_table_fields(table_name)]
            mapping = self.mappings[table_name]
            
            # Campos destino que se usarán
            used_destino_fields = []
            select_fields = []
            
            for destino_field in destino_fields:
                # Buscar si hay mapeo
                mapped = False
                for origen_field, destino_mapped in mapping.items():
                    if destino_mapped == destino_field:
                        used_destino_fields.append(destino_field)
                        select_fields.append(f"o.`{origen_field}`")
                        mapped = True
                        break
                
                if not mapped:
                    # Campo sin mapeo - usar default
                    used_destino_fields.append(destino_field)
                    select_fields.append("NULL")
            
            if used_destino_fields:
                sql += f"-- Tabla: {table_name}\n"
                sql += f"INSERT INTO `{table_name}` (`{'`, `'.join(used_destino_fields)}`)\n"
                sql += f"SELECT {', '.join(select_fields)}\n"
                sql += f"FROM `origen_db`.`{table_name}` o\n"
                sql += f"ON DUPLICATE KEY UPDATE\n"
                sql += f"    `id` = VALUES(`id`);\n\n"
        
        return sql
    
    def generate_scripts(self):
        """Genera los 4 scripts SQL"""
        if not self.mappings:
            messagebox.showwarning("Advertencia", "No hay mapeos definidos")
            return
        
        output_dir = filedialog.askdirectory(title="Seleccionar directorio para guardar scripts")
        if not output_dir:
            return
        
        timestamp = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
        
        # Script 1: Backup
        self._generate_backup_script(output_dir, timestamp)
        
        # Script 2: Alter estructura
        self._generate_alter_script(output_dir, timestamp)
        
        # Script 3: Migrar datos
        self._generate_migrate_script(output_dir, timestamp)
        
        # Script 4: Verificar
        self._generate_verify_script(output_dir, timestamp)
        
        messagebox.showinfo("Éxito", f"Scripts generados en:\n{output_dir}")
    
    def _generate_backup_script(self, output_dir: str, timestamp: str):
        """Genera script de backup"""
        script = f"""-- ============================================
-- BACKUP DE SEGURIDAD - NewMoon
-- Fecha: {timestamp}
-- ============================================
-- Ejecutar ANTES de cualquier migración

"""
        for table_name in sorted(self.mappings.keys()):
            script += f"CREATE TABLE IF NOT EXISTS `_backup_{table_name}` AS SELECT * FROM `{table_name}`;\n"
        
        script += "\n-- Backup completado\n"
        
        with open(os.path.join(output_dir, "01_backup.sql"), 'w', encoding='utf-8') as f:
            f.write(script)
    
    def _generate_alter_script(self, output_dir: str, timestamp: str):
        """Genera script de alteración de estructura"""
        script = f"""-- ============================================
-- ALTERACIONES DE ESTRUCTURA
-- Migración: Origen → NewMoon
-- Fecha: {timestamp}
-- ============================================

"""
        for table_name in sorted(self.field_creations.keys()):
            for field_info in self.field_creations[table_name]:
                field_name = field_info['name']
                field_def = field_info['definition']
                script += f"ALTER TABLE `{table_name}` ADD COLUMN IF NOT EXISTS `{field_name}` {field_def};\n"
        
        script += "\n-- Alteraciones completadas\n"
        
        with open(os.path.join(output_dir, "02_alter_estructura.sql"), 'w', encoding='utf-8') as f:
            f.write(script)
    
    def _generate_migrate_script(self, output_dir: str, timestamp: str):
        """Genera script de migración de datos con INSERTs reales del archivo origen"""
        script = f"""-- ============================================
-- MIGRACIÓN DE DATOS
-- Migración: Origen → NewMoon
-- Fecha: {timestamp}
-- ============================================
-- Este script contiene los INSERTs reales transformados según el mapeo

SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';

"""
        for table_name in sorted(self.mappings.keys()):
            if not self.mappings[table_name]:
                continue
            
            record_count = self.parser_origen.get_insert_count(table_name)
            destino_fields = [f['name'] for f in self.parser_destino.get_table_fields(table_name)]
            origen_fields = [f['name'] for f in self.parser_origen.get_table_fields(table_name)]
            mapping = self.mappings[table_name]
            
            script += f"""
-- --------------------------------------------------------
-- TABLA: {table_name}
-- Registros: {record_count}
-- --------------------------------------------------------

"""
            
            # Extraer INSERTs reales del archivo origen
            insert_statements = self.parser_origen.extract_insert_statements(table_name)
            
            if insert_statements:
                # Transformar cada INSERT según el mapeo
                for insert_stmt in insert_statements:
                    transformed_insert = self._transform_insert_statement(
                        insert_stmt, table_name, origen_fields, destino_fields, mapping
                    )
                    if transformed_insert:
                        script += transformed_insert + "\n\n"
            else:
                # Si no hay INSERTs, generar estructura vacía
                script += f"-- No se encontraron INSERTs para la tabla {table_name}\n"
                script += f"-- INSERT INTO `{table_name}` (...) VALUES (...);\n\n"
            
            # Actualizar AUTO_INCREMENT
            script += f"""-- Actualizar AUTO_INCREMENT
SELECT @max_id := IFNULL(MAX(id), 0) + 1 FROM `{table_name}`;
SET @sql = CONCAT('ALTER TABLE `{table_name}` AUTO_INCREMENT = ', @max_id);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

"""
        
        script += "SET FOREIGN_KEY_CHECKS = 1;\n"
        
        with open(os.path.join(output_dir, "03_migrar_datos.sql"), 'w', encoding='utf-8') as f:
            f.write(script)
    
    def _transform_insert_statement(self, insert_stmt: str, table_name: str, 
                                   origen_fields: List[str], destino_fields: List[str], 
                                   mapping: Dict) -> str:
        """Transforma un INSERT statement según el mapeo de campos"""
        # Extraer campos y valores del INSERT original
        # Patrón: INSERT INTO `tabla` (`campo1`, `campo2`, ...) VALUES (valor1, valor2, ...), ...
        field_match = re.search(r'INSERT\s+INTO\s+`?\w+`?\s*\(([^)]+)\)', insert_stmt, re.IGNORECASE)
        if not field_match:
            return ""
        
        # Extraer campos origen
        origen_fields_in_insert = [f.strip().strip('`') for f in field_match.group(1).split(',')]
        
        # Extraer valores (puede ser múltiples filas)
        values_match = re.search(r'VALUES\s+(.+);?$', insert_stmt, re.IGNORECASE | re.DOTALL)
        if not values_match:
            return ""
        
        values_block = values_match.group(1).strip()
        
        # Construir nuevo INSERT con campos destino
        used_destino_fields = []
        field_index_map = {}  # índice_origen -> índice_destino
        
        for destino_field in destino_fields:
            mapped = False
            for origen_field, destino_mapped in mapping.items():
                if destino_mapped == destino_field:
                    if origen_field in origen_fields_in_insert:
                        used_destino_fields.append(destino_field)
                        field_index_map[origen_fields_in_insert.index(origen_field)] = len(used_destino_fields) - 1
                        mapped = True
                        break
            
            if not mapped:
                # Campo sin mapeo - se agregará con NULL
                used_destino_fields.append(destino_field)
        
        if not used_destino_fields:
            return ""
        
        # Construir nuevo INSERT
        new_insert = f"INSERT INTO `{table_name}` (`{'`, `'.join(used_destino_fields)}`) VALUES\n"
        
        # Transformar valores según mapeo
        # Parsear filas de valores
        rows = self._parse_values_block(values_block)
        
        transformed_rows = []
        for row in rows:
            # Crear nueva fila con valores mapeados
            new_row_values = []
            for i, destino_field in enumerate(used_destino_fields):
                # Buscar si hay mapeo
                found = False
                for origen_idx, destino_idx in field_index_map.items():
                    if destino_idx == i and origen_idx < len(row):
                        new_row_values.append(row[origen_idx])
                        found = True
                        break
                
                if not found:
                    # Campo sin mapeo - usar NULL
                    new_row_values.append("NULL")
            
            transformed_rows.append("(" + ", ".join(new_row_values) + ")")
        
        new_insert += ",\n".join(transformed_rows) + ";"
        
        return new_insert
    
    def _parse_values_block(self, values_block: str) -> List[List[str]]:
        """Parsea un bloque VALUES y retorna lista de filas"""
        rows = []
        current_row = ""
        paren_count = 0
        in_string = False
        string_char = None
        escape_next = False
        
        for char in values_block:
            if escape_next:
                current_row += char
                escape_next = False
                continue
            
            if char == '\\':
                current_row += char
                escape_next = True
                continue
            
            if char in ("'", '"') and not escape_next:
                if not in_string:
                    in_string = True
                    string_char = char
                elif char == string_char:
                    in_string = False
                    string_char = None
                current_row += char
            elif char == '(' and not in_string:
                if paren_count == 0:
                    current_row = ""
                else:
                    current_row += char
                paren_count += 1
            elif char == ')' and not in_string:
                paren_count -= 1
                if paren_count == 0:
                    # Parsear valores de esta fila
                    row_values = self._parse_row_values(current_row)
                    if row_values:
                        rows.append(row_values)
                    current_row = ""
                else:
                    current_row += char
            else:
                current_row += char
        
        return rows
    
    def _parse_row_values(self, row_str: str) -> List[str]:
        """Parsea una fila de valores separados por comas"""
        values = []
        current_value = ""
        in_string = False
        string_char = None
        escape_next = False
        
        for char in row_str:
            if escape_next:
                current_value += char
                escape_next = False
                continue
            
            if char == '\\':
                current_value += char
                escape_next = True
                continue
            
            if char in ("'", '"') and not escape_next:
                if not in_string:
                    in_string = True
                    string_char = char
                elif char == string_char:
                    in_string = False
                    string_char = None
                current_value += char
            elif char == ',' and not in_string:
                values.append(current_value.strip())
                current_value = ""
            else:
                current_value += char
        
        if current_value.strip():
            values.append(current_value.strip())
        
        return values
    
    def _generate_verify_script(self, output_dir: str, timestamp: str):
        """Genera script de verificación"""
        script = f"""-- ============================================
-- VERIFICACIÓN DE MIGRACIÓN
-- Fecha: {timestamp}
-- ============================================

-- Comparar conteos
SELECT 'VERIFICACIÓN DE REGISTROS MIGRADOS' AS '';
SELECT '=================================' AS '';

"""
        for table_name in sorted(self.mappings.keys()):
            record_count = self.parser_origen.get_insert_count(table_name)
            script += f"""
SELECT 
    '{table_name}' AS tabla,
    {record_count} AS registros_origen,
    (SELECT COUNT(*) FROM `{table_name}`) AS registros_destino,
    CASE 
        WHEN {record_count} = (SELECT COUNT(*) FROM `{table_name}`)
        THEN '✅ OK'
        ELSE '⚠️ DIFERENCIA'
    END AS estado;

"""
        
        script += "\n-- Verificación completada\n"
        
        with open(os.path.join(output_dir, "04_verificar.sql"), 'w', encoding='utf-8') as f:
            f.write(script)


def main():
    """Función principal"""
    root = tk.Tk()
    app = DBMigratorApp(root)
    root.mainloop()


if __name__ == '__main__':
    main()



