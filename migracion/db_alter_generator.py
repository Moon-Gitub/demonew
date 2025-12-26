#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
NewMoon DB Alter Generator - Sincronizador de estructuras (GUI)

Compara dos archivos SQL (DESTINO = modelo NewMoon, ORIGEN = sistema a adaptar)
Y genera un archivo alter_table.sql con:
- CREATE TABLE para tablas que faltan en ORIGEN
- ALTER TABLE ... ADD COLUMN para columnas que faltan en ORIGEN
- ALTER TABLE ... MODIFY COLUMN para diferencias de tipo/NULL/DEFAULT/AUTO_INCREMENT
- ALTER TABLE ... ADD INDEX / ADD UNIQUE KEY para índices faltantes

Reglas:
- NUNCA hace DROP COLUMN ni DROP TABLE
- NUNCA elimina índices existentes

Uso en terminal (opcional):
    python3 db_alter_generator.py
Luego usar la GUI.
"""

import tkinter as tk
from tkinter import ttk, filedialog, messagebox, scrolledtext
import re
import os
from datetime import datetime
from typing import Dict, List, Optional


"""
PARSER SQL (estructuras de tablas)
----------------------------------
Usa un enfoque muy similar al parser del script `db_migrator.py` para asegurar
que detecte las mismas tablas/campos.
"""


class SimpleSQLParser:
    """Parser sencillo para dumps MySQL (CREATE TABLE, índices, etc.)."""

    def __init__(self) -> None:
        # {table_name: {campos, primary_key, indices, engine, charset, create_statement}}
        self.tables: Dict[str, Dict] = {}
        self.sql_content: str = ""

    def parse_file(self, path: str) -> bool:
        try:
            with open(path, "r", encoding="utf-8", errors="ignore") as f:
                self.sql_content = f.read()
            self._parse_tables()
            return True
        except Exception as e:
            messagebox.showerror("Error", f"Error leyendo/parsing SQL:\n{e}")
            return False

    def _parse_tables(self) -> None:
        self.tables = {}

        # Buscar CREATE TABLE `tabla` ( ... ) ENGINE=... DEFAULT CHARSET=...
        create_pattern = re.compile(
            r"CREATE\s+TABLE\s+`(?P<nombre>\w+)`\s*\("     # inicio
            r"(?P<cuerpo>.*?)"                             # cuerpo
            r"\)\s*ENGINE\s*=\s*(?P<engine>\w+)"           # ENGINE=InnoDB
            r"[^;]*?CHARSET\s*=\s*(?P<charset>\w+)",       # CHARSET=utf8mb3
            re.IGNORECASE | re.DOTALL,
        )

        for m in create_pattern.finditer(self.sql_content):
            nombre_tabla = m.group("nombre")
            cuerpo = m.group("cuerpo")
            engine = m.group("engine")
            charset = m.group("charset")

            campos: Dict[str, Dict] = {}
            primary_key: List[str] = []
            indices: Dict[str, Dict] = {}

            lineas = [l.strip() for l in cuerpo.split("\n") if l.strip()]
            pos = 0

            for linea in lineas:
                # Columna
                col_match = re.match(r"^`(?P<col>\w+)`\s+(?P<resto>.+?)(?:,)?$", linea)
                if col_match:
                    pos += 1
                    col = col_match.group("col")
                    resto = col_match.group("resto").rstrip(",")
                    tipo = self._extraer_tipo(resto)
                    es_null = not re.search(r"\bNOT\s+NULL\b", resto, re.IGNORECASE)
                    auto_inc = bool(re.search(r"\bAUTO_INCREMENT\b", resto, re.IGNORECASE))
                    default = self._extraer_default(resto)
                    campos[col] = {
                        "tipo": tipo,
                        "null": es_null,
                        "default": default,
                        "auto_increment": auto_inc,
                        "posicion": pos,
                        "raw": resto,
                    }
                    continue

                # PRIMARY KEY
                pk_match = re.match(
                    r"^PRIMARY\s+KEY\s*\((?P<cols>.+?)\)(?:,)?$",
                    linea,
                    re.IGNORECASE,
                )
                if pk_match:
                    cols = [c.strip().strip("`") for c in pk_match.group("cols").split(",")]
                    primary_key = cols
                    continue

                # UNIQUE KEY o KEY normal
                idx_match = re.match(
                    r"^(UNIQUE\s+KEY|KEY)\s+`(?P<nombre>\w+)`\s*\((?P<cols>.+?)\)(?:,)?$",
                    linea,
                    re.IGNORECASE,
                )
                if idx_match:
                    tipo_idx = idx_match.group(1).upper()
                    idx_nombre = idx_match.group("nombre")
                    cols = [c.strip().strip("`") for c in idx_match.group("cols").split(",")]
                    indices[idx_nombre] = {
                        "campos": cols,
                        "unique": "UNIQUE" in tipo_idx,
                    }
                    continue

            create_stmt = self._extraer_create_completo(nombre_tabla)
            self.tables[nombre_tabla] = {
                "campos": campos,
                "primary_key": primary_key,
                "indices": indices,
                "engine": engine,
                "charset": charset,
                "create_statement": create_stmt,
            }

    def _extraer_tipo(self, definicion: str) -> str:
        m = re.match(r"^(\w+(?:\([^)]+\))?)", definicion.strip(), re.IGNORECASE)
        if m:
            return m.group(1).upper()
        return "UNKNOWN"

    def _extraer_default(self, definicion: str) -> Optional[str]:
        """
        Extrae el valor DEFAULT de una definición de columna.
        Maneja correctamente valores con comillas simples, números, NULL y funciones SQL.
        """
        # Buscar DEFAULT seguido de un valor
        # Patrón mejorado: busca DEFAULT seguido de:
        # - String entre comillas simples: 'valor' o 'valor con ''comilla'''
        # - Número: 123, 45.67, -10
        # - NULL: NULL
        # - Funciones SQL: current_timestamp(), now(), etc.
        
        # Intentar primero con string entre comillas simples
        m_string = re.search(r"\bDEFAULT\s+'(.*?)'", definicion, re.IGNORECASE)
        if m_string:
            # Retornar el valor con comillas (las escaparemos después)
            return f"'{m_string.group(1)}'"
        
        # Buscar funciones SQL (sin comillas)
        m_func = re.search(r"\bDEFAULT\s+([a-z_]+\([^)]*\))", definicion, re.IGNORECASE)
        if m_func:
            return m_func.group(1)
        
        # Buscar NULL
        m_null = re.search(r"\bDEFAULT\s+NULL\b", definicion, re.IGNORECASE)
        if m_null:
            return "NULL"
        
        # Buscar números (con o sin signo, decimales)
        m_num = re.search(r"\bDEFAULT\s+([+-]?\d+(?:\.\d+)?)", definicion, re.IGNORECASE)
        if m_num:
            return m_num.group(1)
        
        # Fallback: buscar cualquier valor después de DEFAULT (sin comillas)
        m_fallback = re.search(r"\bDEFAULT\s+([^,\s]+)", definicion, re.IGNORECASE)
        if m_fallback:
            valor = m_fallback.group(1).strip()
            # Si parece un string sin comillas, agregar comillas
            if valor and not valor.upper() in ['NULL', 'CURRENT_TIMESTAMP', 'NOW()'] and not valor.replace('.', '').replace('-', '').isdigit():
                return f"'{valor}'"
            return valor
        
        return None

    def _extraer_create_completo(self, nombre_tabla: str) -> str:
        patt = re.compile(
            rf"CREATE\s+TABLE\s+`{nombre_tabla}`\s*\(.*?\)\s*ENGINE=.*?;",
            re.IGNORECASE | re.DOTALL,
        )
        m = patt.search(self.sql_content)
        if m:
            return m.group(0)
        return ""


# ----------------------------------------------------------------------
# FUNCIONES DE VALIDACIÓN Y FORMATEO
# ----------------------------------------------------------------------

def formatear_default_sql(valor_default: Optional[str]) -> str:
    """
    Formatea un valor DEFAULT para SQL, escapando comillas correctamente.
    
    Maneja:
    - NULL (sin comillas)
    - Números (sin comillas)
    - Strings (con comillas simples, escapando comillas internas)
    - Funciones SQL como current_timestamp() (sin comillas)
    
    Args:
        valor_default: Valor DEFAULT extraído del parser
        
    Returns:
        String formateado para usar en SQL, ej: "NULL", "123", "'texto'", "'texto con ''comilla'''"
    """
    if valor_default is None:
        return ""
    
    valor = valor_default.strip()
    
    # NULL (sin comillas)
    if valor.upper() == "NULL":
        return "NULL"
    
    # Funciones SQL (sin comillas)
    funciones_sql = ['CURRENT_TIMESTAMP', 'NOW()', 'CURRENT_DATE', 'CURRENT_TIME']
    if valor.upper() in funciones_sql or re.match(r'^[a-z_]+\([^)]*\)$', valor, re.IGNORECASE):
        return valor
    
    # Números (sin comillas, incluyendo decimales y negativos)
    if re.match(r'^[+-]?\d+(?:\.\d+)?$', valor):
        return valor
    
    # Strings: deben estar entre comillas simples
    # Si ya tiene comillas, extraer el contenido y re-escapar
    if valor.startswith("'") and valor.endswith("'"):
        # Extraer contenido (sin las comillas externas)
        contenido = valor[1:-1]
    else:
        # Si no tiene comillas, usar el valor tal cual
        contenido = valor
    
    # Escapar comillas simples duplicándolas (estándar SQL)
    contenido_escapado = contenido.replace("'", "''")
    
    return f"'{contenido_escapado}'"


def validar_sintaxis_sql(sql: str) -> Dict[str, any]:
    """
    Valida la sintaxis SQL básica del script generado.
    
    Returns:
        Dict con 'valido': bool, 'errores': List[str], 'advertencias': List[str]
    """
    errores = []
    advertencias = []
    
    # Contar paréntesis balanceados
    paren_abrir = sql.count('(')
    paren_cerrar = sql.count(')')
    if paren_abrir != paren_cerrar:
        errores.append(f"Paréntesis desbalanceados: {paren_abrir} abiertos, {paren_cerrar} cerrados")
    
    # Contar comillas simples (deben ser pares)
    comillas = sql.count("'")
    # Ignorar comillas en comentarios
    sql_sin_comentarios = re.sub(r'--.*', '', sql)
    comillas_sin_comentarios = sql_sin_comentarios.count("'")
    if comillas_sin_comentarios % 2 != 0:
        errores.append("Comillas simples desbalanceadas (posible comilla sin cerrar)")
    
    # Verificar que todas las líneas SQL terminen con ;
    lineas_sql = [l.strip() for l in sql.split('\n') if l.strip() and not l.strip().startswith('--')]
    lineas_sin_punto_coma = []
    for i, linea in enumerate(lineas_sql, 1):
        # Excluir SET statements que ya tienen ;
        if any(linea.upper().startswith(cmd) for cmd in ['SET ', 'CREATE ', 'ALTER ']):
            if not linea.rstrip().endswith(';'):
                lineas_sin_punto_coma.append(f"Línea {i}: {linea[:50]}...")
    
    if lineas_sin_punto_coma:
        advertencias.extend(lineas_sin_punto_coma)
    
    # Verificar tipos de datos válidos para MySQL/MariaDB
    tipos_validos = [
        'INT', 'INTEGER', 'BIGINT', 'SMALLINT', 'TINYINT', 'MEDIUMINT',
        'DECIMAL', 'NUMERIC', 'FLOAT', 'DOUBLE', 'REAL',
        'VARCHAR', 'CHAR', 'TEXT', 'TINYTEXT', 'MEDIUMTEXT', 'LONGTEXT',
        'BLOB', 'TINYBLOB', 'MEDIUMBLOB', 'LONGBLOB',
        'DATE', 'TIME', 'DATETIME', 'TIMESTAMP', 'YEAR',
        'ENUM', 'SET', 'JSON', 'BOOLEAN', 'BOOL'
    ]
    
    # Buscar tipos potencialmente inválidos en CREATE/ALTER TABLE
    patron_tipo = re.compile(r'\b([A-Z]+)\s*\(', re.IGNORECASE)
    for match in patron_tipo.finditer(sql):
        tipo = match.group(1).upper()
        # Verificar si es un tipo válido o si tiene parámetros conocidos
        if tipo not in tipos_validos and not re.match(r'^(VARCHAR|CHAR|DECIMAL|NUMERIC|FLOAT|DOUBLE|INT|BIGINT|SMALLINT|TINYINT|MEDIUMINT)', tipo):
            # Puede ser válido si tiene parámetros, solo advertencia
            advertencias.append(f"Tipo de dato potencialmente inválido: {tipo}")
    
    return {
        'valido': len(errores) == 0,
        'errores': errores,
        'advertencias': advertencias
    }


# ----------------------------------------------------------------------
# LÓGICA DE COMPARACIÓN
# ----------------------------------------------------------------------

def necesita_modificacion(campo_origen: Dict, campo_destino: Dict) -> bool:
    """Devuelve True si difieren tipo/null/default/auto_increment."""
    if campo_origen["tipo"] != campo_destino["tipo"]:
        return True
    if campo_origen["null"] != campo_destino["null"]:
        return True
    d1 = (campo_origen["default"] or "").upper()
    d2 = (campo_destino["default"] or "").upper()
    if d1 != d2:
        return True
    if campo_origen["auto_increment"] != campo_destino["auto_increment"]:
        return True
    return False


def comparar_estructuras(destino: Dict, origen: Dict) -> List[Dict]:
    """Devuelve lista de cambios (CREATE_TABLE, ADD_COLUMN, MODIFY_COLUMN, ADD_INDEX)."""
    cambios: List[Dict] = []

    for tabla_nombre, tabla_destino in destino.items():
        if tabla_nombre not in origen:
            cambios.append({
                "tipo": "CREATE_TABLE",
                "tabla": tabla_nombre,
                "definicion": tabla_destino,
            })
            continue

        tabla_origen = origen[tabla_nombre]
        campos_dest = tabla_destino["campos"]
        campos_ori = tabla_origen["campos"]

        # Campos
        for campo_nombre, campo_dest in campos_dest.items():
            if campo_nombre not in campos_ori:
                cambios.append({
                    "tipo": "ADD_COLUMN",
                    "tabla": tabla_nombre,
                    "campo": campo_nombre,
                    "definicion": campo_dest,
                })
            else:
                campo_ori = campos_ori[campo_nombre]
                if necesita_modificacion(campo_ori, campo_dest):
                    cambios.append({
                        "tipo": "MODIFY_COLUMN",
                        "tabla": tabla_nombre,
                        "campo": campo_nombre,
                        "definicion_nueva": campo_dest,
                        "origen": campo_ori,
                    })

        # Índices
        idx_dest = tabla_destino["indices"]
        idx_ori = tabla_origen["indices"]

        for idx_nombre, idx_def in idx_dest.items():
            if idx_nombre not in idx_ori:
                cambios.append({
                    "tipo": "ADD_INDEX",
                    "tabla": tabla_nombre,
                    "indice": idx_nombre,
                    "definicion": idx_def,
                })
            else:
                i_ori = idx_ori[idx_nombre]
                if i_ori["campos"] != idx_def["campos"] or i_ori["unique"] != idx_def["unique"]:
                    cambios.append({
                        "tipo": "ADD_INDEX",
                        "tabla": tabla_nombre,
                        "indice": idx_nombre,
                        "definicion": idx_def,
                    })

    return cambios


def generar_sql(cambios: List[Dict], destino: Dict, archivo_destino: str, archivo_origen: str) -> str:
    ahora = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
    lineas: List[str] = []

    lineas.append("-- ================================================================")
    lineas.append("-- SCRIPT DE SINCRONIZACIÓN DE ESTRUCTURA")
    lineas.append("-- ================================================================")
    lineas.append(f"-- Generado: {ahora}")
    lineas.append("-- ")
    lineas.append(f"-- DESTINO (modelo): {archivo_destino}")
    lineas.append(f"-- ORIGEN (a modificar): {archivo_origen}")
    lineas.append("--")
    lineas.append("-- Este script transforma la estructura del ORIGEN para que sea")
    lineas.append("-- idéntica al DESTINO, sin eliminar datos existentes.")
    lineas.append("--")
    lineas.append("-- ⚠️  IMPORTANTE: Hacer backup antes de ejecutar")
    lineas.append("-- ================================================================")
    lineas.append("")
    lineas.append("SET FOREIGN_KEY_CHECKS = 0;")
    lineas.append("SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';")
    lineas.append("")

    # Tablas nuevas
    tablas_crear = [c for c in cambios if c["tipo"] == "CREATE_TABLE"]
    if tablas_crear:
        lineas.append("-- ================================================================")
        lineas.append("-- TABLAS NUEVAS A CREAR")
        lineas.append("-- ================================================================")
        lineas.append("-- Se usa CREATE TABLE IF NOT EXISTS para evitar errores si la tabla ya existe")
        lineas.append("")
        for c in tablas_crear:
            create_stmt = c["definicion"]["create_statement"].rstrip(";")
            tabla_nombre = c["tabla"]
            
            # Agregar IF NOT EXISTS si no está presente
            if "IF NOT EXISTS" not in create_stmt.upper():
                # Reemplazar CREATE TABLE `nombre` por CREATE TABLE IF NOT EXISTS `nombre`
                create_stmt = re.sub(
                    r'CREATE\s+TABLE\s+`',
                    'CREATE TABLE IF NOT EXISTS `',
                    create_stmt,
                    flags=re.IGNORECASE
                )
                lineas.append(f"-- Creación de tabla con protección IF NOT EXISTS")
            else:
                lineas.append(f"-- Tabla {tabla_nombre} (ya incluye IF NOT EXISTS)")
            
            lineas.append(f"{create_stmt};")
            lineas.append("")
        lineas.append("")

    # Cambios por tabla
    cambios_por_tabla: Dict[str, List[Dict]] = {}
    for c in cambios:
        if c["tipo"] == "CREATE_TABLE":
            continue
        tabla = c["tabla"]
        cambios_por_tabla.setdefault(tabla, []).append(c)

    total_add = 0
    total_mod = 0
    total_idx = 0

    for tabla, lista in sorted(cambios_por_tabla.items()):
        adds = [c for c in lista if c["tipo"] == "ADD_COLUMN"]
        mods = [c for c in lista if c["tipo"] == "MODIFY_COLUMN"]
        idxs = [c for c in lista if c["tipo"] == "ADD_INDEX"]

        if not (adds or mods or idxs):
            continue

        lineas.append("-- ================================================================")
        lineas.append(f"-- TABLA: {tabla}")
        if adds:
            lineas.append(f"-- Agregar: {len(adds)} campos")
        if mods:
            lineas.append(f"-- Modificar: {len(mods)} campos")
        if idxs:
            lineas.append(f"-- Índices nuevos: {len(idxs)}")
        lineas.append("-- ================================================================")
        lineas.append("")

        # ADD COLUMN
        for c in adds:
            campo = c["campo"]
            d = c["definicion"]
            tipo = d["tipo"]
            null_str = "NULL" if d["null"] else "NOT NULL"
            default = d["default"]
            
            # Formatear DEFAULT correctamente (escapar comillas)
            if default is not None:
                default_formateado = formatear_default_sql(default)
                default_str = f" DEFAULT {default_formateado}"
            else:
                default_str = ""
            
            # Agregar comentario si se aplicó validación
            if default is not None and default_formateado != default:
                lineas.append(f"-- Valores DEFAULT correctamente escapados")
            
            lineas.append(
                f"ALTER TABLE `{tabla}` ADD COLUMN `{campo}` {tipo} {null_str}{default_str};"
            )
            total_add += 1

        if adds:
            lineas.append("")

        # MODIFY COLUMN
        for c in mods:
            campo = c["campo"]
            d = c["definicion_nueva"]
            tipo = d["tipo"]
            null_str = "NULL" if d["null"] else "NOT NULL"
            default = d["default"]
            
            # Formatear DEFAULT correctamente (escapar comillas)
            if default is not None:
                default_formateado = formatear_default_sql(default)
                default_str = f" DEFAULT {default_formateado}"
            else:
                default_str = ""
            
            ai_str = " AUTO_INCREMENT" if d["auto_increment"] else ""
            lineas.append(
                f"ALTER TABLE `{tabla}` MODIFY COLUMN `{campo}` {tipo} {null_str}{default_str}{ai_str};"
            )
            total_mod += 1

        if mods:
            lineas.append("")

        # Índices (con IF NOT EXISTS cuando sea posible)
        for c in idxs:
            idx = c["indice"]
            d = c["definicion"]
            cols = ", ".join(f"`{col}`" for col in d["campos"])
            
            # MySQL no soporta IF NOT EXISTS en ALTER TABLE ADD INDEX directamente
            # Pero podemos agregar comentario informativo
            if d["unique"]:
                lineas.append(
                    f"ALTER TABLE `{tabla}` ADD UNIQUE KEY `{idx}` ({cols});"
                )
            else:
                lineas.append(
                    f"ALTER TABLE `{tabla}` ADD INDEX `{idx}` ({cols});"
                )
            total_idx += 1

        lineas.append("")

    lineas.append("SET FOREIGN_KEY_CHECKS = 1;")
    lineas.append("")

    # Validar sintaxis SQL antes de continuar
    sql_temp = "\n".join(lineas)
    validacion = validar_sintaxis_sql(sql_temp)
    
    lineas.append("-- ================================================================")
    lineas.append("-- RESUMEN")
    lineas.append("-- ================================================================")
    lineas.append(f"-- Tablas creadas: {len(tablas_crear)}")
    lineas.append(f"-- Columnas agregadas: {total_add}")
    lineas.append(f"-- Columnas modificadas: {total_mod}")
    lineas.append(f"-- Índices agregados: {total_idx}")
    lineas.append("--")
    lineas.append("-- ⚠️ No se elimina ninguna columna ni tabla (sin DROP).")
    lineas.append("--")
    
    # Agregar resultados de validación
    if validacion['valido']:
        lineas.append("-- ✅ Validación de sintaxis SQL: PASADA")
    else:
        lineas.append("-- ❌ Validación de sintaxis SQL: FALLÓ")
        for error in validacion['errores']:
            lineas.append(f"--    ERROR: {error}")
    
    if validacion['advertencias']:
        lineas.append("--")
        lineas.append("-- ⚠️ ADVERTENCIAS:")
        for advertencia in validacion['advertencias']:
            lineas.append(f"--    {advertencia}")
    
    lineas.append("--")
    lineas.append("-- VALIDACIONES APLICADAS:")
    lineas.append("-- ✅ Valores DEFAULT con comillas correctamente escapadas")
    lineas.append("-- ✅ CREATE TABLE IF NOT EXISTS para evitar errores de tablas existentes")
    lineas.append("-- ✅ Validación de sintaxis SQL básica")
    lineas.append("-- ✅ Manejo de valores especiales (NULL, números, funciones SQL)")
    lineas.append("-- ================================================================")

    return "\n".join(lineas)


# ----------------------------------------------------------------------
# GUI
# ----------------------------------------------------------------------

class AlterGeneratorApp:
    def __init__(self, root: tk.Tk) -> None:
        self.root = root
        self.root.title("🌙 NewMoon DB Alter Generator")
        self.root.geometry("1280x800")
        self.bg = "#1a1a2e"
        self.accent_blue = "#00d9ff"
        self.accent_green = "#00ff88"
        self.text_color = "#ffffff"
        self.grid_color = "#16213e"
        self.root.configure(bg=self.bg)

        self.parser_destino = SimpleSQLParser()
        self.parser_origen = SimpleSQLParser()
        self.destino_path: Optional[str] = None
        self.origen_path: Optional[str] = None

        self.current_table: Optional[str] = None

        self._build_ui()

    def _build_ui(self) -> None:
        # Header
        header = tk.Frame(self.root, bg=self.bg, pady=10)
        header.pack(fill=tk.X)
        tk.Label(
            header,
            text="🌙 NewMoon DB Alter Generator",
            font=("Arial", 20, "bold"),
            bg=self.bg,
            fg=self.accent_blue,
        ).pack()

        # Carga de archivos
        load = tk.Frame(self.root, bg=self.bg, pady=10)
        load.pack(fill=tk.X, padx=20)

        self.btn_dest = tk.Button(
            load,
            text="📁 Cargar DESTINO (modelo)",
            command=self.load_destino,
            bg=self.accent_blue,
            fg="#000",
            font=("Arial", 10, "bold"),
            padx=20,
            pady=5,
        )
        self.btn_dest.pack(side=tk.LEFT, padx=5)

        self.lbl_dest = tk.Label(
            load,
            text="No cargado",
            bg=self.bg,
            fg=self.text_color,
            font=("Arial", 9),
        )
        self.lbl_dest.pack(side=tk.LEFT, padx=10)

        self.btn_ori = tk.Button(
            load,
            text="📁 Cargar ORIGEN",
            command=self.load_origen,
            bg=self.accent_green,
            fg="#000",
            font=("Arial", 10, "bold"),
            padx=20,
            pady=5,
        )
        self.btn_ori.pack(side=tk.LEFT, padx=5)

        self.lbl_ori = tk.Label(
            load,
            text="No cargado",
            bg=self.bg,
            fg=self.text_color,
            font=("Arial", 9),
        )
        self.lbl_ori.pack(side=tk.LEFT, padx=10)

        # Tabs de tablas
        main = tk.Frame(self.root, bg=self.bg)
        main.pack(fill=tk.BOTH, expand=True, padx=20, pady=10)

        self.tabs_frame = tk.Frame(main, bg=self.bg)
        self.tabs_frame.pack(fill=tk.X, pady=(0, 10))
        self.table_buttons: List[tk.Button] = []

        # Canvas scrollable para detalles
        canvas_frame = tk.Frame(main, bg=self.bg)
        canvas_frame.pack(fill=tk.BOTH, expand=True)

        self.canvas = tk.Canvas(canvas_frame, bg=self.bg, highlightthickness=0)
        scrollbar = ttk.Scrollbar(canvas_frame, orient="vertical", command=self.canvas.yview)
        self.scrollable = tk.Frame(self.canvas, bg=self.bg)

        self.scrollable.bind(
            "<Configure>", lambda e: self.canvas.configure(scrollregion=self.canvas.bbox("all"))
        )

        self.canvas.create_window((0, 0), window=self.scrollable, anchor="nw")
        self.canvas.configure(yscrollcommand=scrollbar.set)

        self.canvas.pack(side=tk.LEFT, fill=tk.BOTH, expand=True)
        scrollbar.pack(side=tk.RIGHT, fill=tk.Y)

        self.canvas.bind_all("<MouseWheel>", self._on_mousewheel)

        self.fields_frame = tk.Frame(self.scrollable, bg=self.bg)
        self.fields_frame.pack(fill=tk.BOTH, expand=True, padx=10, pady=10)

        # Pie con acciones
        action = tk.Frame(self.root, bg=self.bg, pady=10)
        action.pack(fill=tk.X, padx=20, pady=10)

        btn_analyze = tk.Button(
            action,
            text="🔍 Analizar",
            command=self.analyze,
            bg=self.accent_blue,
            fg="#000",
            font=("Arial", 10, "bold"),
            padx=20,
            pady=8,
        )
        btn_analyze.pack(side=tk.LEFT, padx=5)

        btn_preview = tk.Button(
            action,
            text="👁️ Vista Previa alter_table.sql",
            command=self.preview_sql,
            bg=self.accent_green,
            fg="#000",
            font=("Arial", 10, "bold"),
            padx=20,
            pady=8,
        )
        btn_preview.pack(side=tk.LEFT, padx=5)

        btn_generate = tk.Button(
            action,
            text="🚀 Generar alter_table.sql",
            command=self.generate_sql_file,
            bg="#ff6b6b",
            fg="#fff",
            font=("Arial", 10, "bold"),
            padx=20,
            pady=8,
        )
        btn_generate.pack(side=tk.LEFT, padx=5)

    def _on_mousewheel(self, event):
        self.canvas.yview_scroll(int(-1 * (event.delta / 120)), "units")

    # ------------------------------------------------------------------
    # Carga de archivos
    # ------------------------------------------------------------------

    def load_destino(self):
        path = filedialog.askopenfilename(
            title="Seleccionar SQL DESTINO (modelo)",
            filetypes=[("SQL", "*.sql"), ("Todos", "*.*")],
        )
        if not path:
            return
        if self.parser_destino.parse_file(path):
            self.destino_path = path
            n = len(self.parser_destino.tables)
            self.lbl_dest.config(
                text=f"✅ {os.path.basename(path)} ({n} tablas)", fg=self.accent_green
            )
            self._update_table_tabs()

    def load_origen(self):
        path = filedialog.askopenfilename(
            title="Seleccionar SQL ORIGEN", filetypes=[("SQL", "*.sql"), ("Todos", "*.*")]
        )
        if not path:
            return
        if self.parser_origen.parse_file(path):
            self.origen_path = path
            n = len(self.parser_origen.tables)
            self.lbl_ori.config(
                text=f"✅ {os.path.basename(path)} ({n} tablas)", fg=self.accent_green
            )
            self._update_table_tabs()

    # ------------------------------------------------------------------

    def _update_table_tabs(self):
        for b in self.table_buttons:
            b.destroy()
        self.table_buttons = []

        if not self.parser_destino.tables or not self.parser_origen.tables:
            return

        dest_tables = set(self.parser_destino.tables.keys())
        ori_tables = set(self.parser_origen.tables.keys())
        common = sorted(dest_tables & ori_tables)

        for name in common:
            btn = tk.Button(
                self.tabs_frame,
                text=name,
                command=lambda t=name: self.show_table(t),
                bg=self.grid_color,
                fg=self.text_color,
                font=("Arial", 9),
                padx=8,
                pady=4,
                relief=tk.RAISED,
            )
            btn.pack(side=tk.LEFT, padx=2)
            self.table_buttons.append(btn)

        if common:
            self.show_table(common[0])

    def show_table(self, table_name: str):
        self.current_table = table_name

        for w in self.fields_frame.winfo_children():
            w.destroy()

        if table_name not in self.parser_destino.tables:
            return

        dest = self.parser_destino.tables.get(table_name, {})
        ori = self.parser_origen.tables.get(table_name, {"campos": {}})

        campos_dest = dest.get("campos", {})
        campos_ori = ori.get("campos", {})

        # Título
        title_frame = tk.Frame(self.fields_frame, bg=self.bg)
        title_frame.pack(fill=tk.X, pady=(0, 10))
        tk.Label(
            title_frame,
            text=f"📋 Tabla: {table_name}",
            bg=self.bg,
            fg=self.accent_blue,
            font=("Arial", 12, "bold"),
        ).pack(side=tk.LEFT)
        tk.Label(
            title_frame,
            text=f" | Campos DESTINO: {len(campos_dest)} | Campos ORIGEN: {len(campos_ori)}",
            bg=self.bg,
            fg="#888",
            font=("Arial", 9),
        ).pack(side=tk.LEFT, padx=10)

        # Header
        header = tk.Frame(self.fields_frame, bg=self.grid_color, bd=1, relief=tk.RAISED)
        header.pack(fill=tk.X)
        for text, width in [
            ("Campo DESTINO", 16),
            ("Tipo D.", 12),
            ("NULL D.", 8),
            ("DEFAULT D.", 16),
            ("Campo ORIGEN", 16),
            ("Tipo O.", 12),
            ("NULL O.", 8),
            ("DEFAULT O.", 16),
            ("Estado", 14),
        ]:
            tk.Label(
                header,
                text=text,
                width=width,
                anchor="w",
                bg=self.grid_color,
                fg=self.accent_blue,
                font=("Arial", 9, "bold"),
                padx=4,
                pady=4,
            ).pack(side=tk.LEFT)

        # Filas
        all_fields = sorted(set(list(campos_dest.keys()) + list(campos_ori.keys())))

        for fname in all_fields:
            row = tk.Frame(self.fields_frame, bg=self.bg, bd=1, relief=tk.RAISED)
            row.pack(fill=tk.X, pady=1)

            d = campos_dest.get(fname)
            o = campos_ori.get(fname)

            # DESTINO
            if d:
                tk.Label(
                    row,
                    text=fname,
                    width=16,
                    anchor="w",
                    bg=self.bg,
                    fg=self.accent_green,
                    font=("Arial", 9, "bold"),
                    padx=4,
                    pady=3,
                ).pack(side=tk.LEFT)
                tk.Label(
                    row,
                    text=d["tipo"],
                    width=12,
                    anchor="w",
                    bg=self.bg,
                    fg="#ccc",
                    font=("Arial", 8),
                ).pack(side=tk.LEFT)
                tk.Label(
                    row,
                    text="NULL" if d["null"] else "NOT NULL",
                    width=8,
                    anchor="w",
                    bg=self.bg,
                    fg="#ccc",
                    font=("Arial", 8),
                ).pack(side=tk.LEFT)
                tk.Label(
                    row,
                    text=d["default"] if d["default"] is not None else "(none)",
                    width=16,
                    anchor="w",
                    bg=self.bg,
                    fg="#ccc",
                    font=("Arial", 8),
                ).pack(side=tk.LEFT)
            else:
                for width in (16, 12, 8, 16):
                    tk.Label(
                        row,
                        text="---",
                        width=width,
                        anchor="w",
                        bg=self.bg,
                        fg="#444",
                        font=("Arial", 8),
                    ).pack(side=tk.LEFT)

            # ORIGEN
            if o:
                tk.Label(
                    row,
                    text=fname,
                    width=16,
                    anchor="w",
                    bg=self.bg,
                    fg=self.accent_blue,
                    font=("Arial", 9, "bold"),
                    padx=4,
                    pady=3,
                ).pack(side=tk.LEFT)
                tk.Label(
                    row,
                    text=o["tipo"],
                    width=12,
                    anchor="w",
                    bg=self.bg,
                    fg="#ccc",
                    font=("Arial", 8),
                ).pack(side=tk.LEFT)
                tk.Label(
                    row,
                    text="NULL" if o["null"] else "NOT NULL",
                    width=8,
                    anchor="w",
                    bg=self.bg,
                    fg="#ccc",
                    font=("Arial", 8),
                ).pack(side=tk.LEFT)
                tk.Label(
                    row,
                    text=o["default"] if o["default"] is not None else "(none)",
                    width=16,
                    anchor="w",
                    bg=self.bg,
                    fg="#ccc",
                    font=("Arial", 8),
                ).pack(side=tk.LEFT)
            else:
                for width in (16, 12, 8, 16):
                    tk.Label(
                        row,
                        text="---",
                        width=width,
                        anchor="w",
                        bg=self.bg,
                        fg="#444",
                        font=("Arial", 8),
                    ).pack(side=tk.LEFT)

            # Estado
            if d and not o:
                estado = "⚠️ Falta en ORIGEN"
                color = "#ffaa00"
            elif d and o and necesita_modificacion(o, d):
                estado = "✏️ Diferente (MODIFY)"
                color = "#ffaa00"
            elif d and o:
                estado = "✅ OK"
                color = self.accent_green
            else:
                estado = "ℹ️ Solo ORIGEN"
                color = "#00aaff"

            tk.Label(
                row,
                text=estado,
                width=14,
                anchor="w",
                bg=self.bg,
                fg=color,
                font=("Arial", 9),
            ).pack(side=tk.LEFT)

    # ------------------------------------------------------------------

    def analyze(self):
        if not self.parser_destino.tables or not self.parser_origen.tables:
            messagebox.showwarning("Advertencia", "Carga primero DESTINO y ORIGEN")
            return
        cambios = comparar_estructuras(self.parser_destino.tables, self.parser_origen.tables)
        messagebox.showinfo("Análisis completado", f"Se detectaron {len(cambios)} cambios.")

    def preview_sql(self):
        if not self.parser_destino.tables or not self.parser_origen.tables:
            messagebox.showwarning("Advertencia", "Carga primero DESTINO y ORIGEN")
            return
        cambios = comparar_estructuras(self.parser_destino.tables, self.parser_origen.tables)
        sql = generar_sql(
            cambios,
            self.parser_destino.tables,
            self.destino_path or "destino.sql",
            self.origen_path or "origen.sql",
        )

        win = tk.Toplevel(self.root)
        win.title("Vista previa - alter_table.sql")
        win.geometry("1000x700")
        win.configure(bg=self.bg)

        txt = scrolledtext.ScrolledText(
            win,
            bg="#0f0f1e",
            fg=self.text_color,
            insertbackground=self.text_color,
            font=("Courier New", 10),
            wrap=tk.NONE,
        )
        txt.pack(fill=tk.BOTH, expand=True, padx=10, pady=10)
        txt.insert("1.0", sql)
        txt.config(state=tk.DISABLED)

    def generate_sql_file(self):
        if not self.parser_destino.tables or not self.parser_origen.tables:
            messagebox.showwarning("Advertencia", "Carga primero DESTINO y ORIGEN")
            return
        cambios = comparar_estructuras(self.parser_destino.tables, self.parser_origen.tables)
        sql = generar_sql(
            cambios,
            self.parser_destino.tables,
            self.destino_path or "destino.sql",
            self.origen_path or "origen.sql",
        )

        # Elegir dónde guardar el archivo
        default_name = "alter_table.sql"
        initialdir = os.getcwd()
        out_path = filedialog.asksaveasfilename(
            title="Guardar archivo alter_table.sql",
            defaultextension=".sql",
            initialfile=default_name,
            initialdir=initialdir,
            filetypes=[("SQL", "*.sql"), ("Todos los archivos", "*.*")],
        )

        if not out_path:
            # Usuario canceló
            return

        try:
            # Validar sintaxis antes de escribir
            validacion = validar_sintaxis_sql(sql)
            
            if not validacion['valido']:
                mensaje_error = "Se detectaron errores de sintaxis SQL:\n\n"
                mensaje_error += "\n".join(validacion['errores'])
                if validacion['advertencias']:
                    mensaje_error += "\n\nAdvertencias:\n"
                    mensaje_error += "\n".join(validacion['advertencias'][:5])  # Máximo 5 advertencias
                mensaje_error += "\n\n¿Deseas guardar el archivo de todas formas?"
                
                if not messagebox.askyesno("Errores de Sintaxis", mensaje_error):
                    return
            
            with open(out_path, "w", encoding="utf-8") as f:
                f.write(sql)
            
            mensaje_exito = f"✅ Generado: {out_path}\n\n"
            mensaje_exito += f"Tablas: {len([c for c in cambios if c['tipo'] == 'CREATE_TABLE'])}\n"
            mensaje_exito += f"Columnas agregadas: {sum(1 for c in cambios if c['tipo'] == 'ADD_COLUMN')}\n"
            mensaje_exito += f"Columnas modificadas: {sum(1 for c in cambios if c['tipo'] == 'MODIFY_COLUMN')}\n"
            
            if validacion['advertencias']:
                mensaje_exito += f"\n⚠️ {len(validacion['advertencias'])} advertencias (ver archivo)"
            
            messagebox.showinfo("Listo", mensaje_exito)
        except Exception as e:
            messagebox.showerror("Error", f"No se pudo escribir alter_table.sql:\n{e}")


def main():
    root = tk.Tk()
    app = AlterGeneratorApp(root)
    root.mainloop()


if __name__ == "__main__":
    main()


