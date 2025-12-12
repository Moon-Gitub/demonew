#!/usr/bin/env python3
import re

# Leer el archivo
with open('db/datos-prueba-completo.sql', 'r', encoding='utf-8') as f:
    content = f.read()

# Encontrar el INSERT de ventas
insert_match = re.search(r'INSERT INTO `ventas` \((.*?)\) VALUES', content, re.DOTALL)
if insert_match:
    columns = [col.strip().strip('`') for col in insert_match.group(1).split(',')]
    num_columns = len(columns)
    print(f'Número de columnas: {num_columns}')
    print()

# Encontrar todas las filas de valores
values_match = re.search(r'INSERT INTO `ventas`.*?VALUES\s*\n(.*?);', content, re.DOTALL)
if values_match:
    values_section = values_match.group(1)
    # Dividir por líneas que empiezan con paréntesis
    lines = []
    current_line = ''
    for line in values_section.split('\n'):
        line = line.strip()
        if not line or line.startswith('--'):
            continue
        if line.startswith('('):
            if current_line:
                lines.append(current_line)
            current_line = line
        else:
            current_line += ' ' + line
    if current_line:
        lines.append(current_line)
    
    # Analizar cada línea
    for i, line in enumerate(lines, 1):
        # Contar valores usando un método más robusto
        # Primero, quitar paréntesis externos
        if line.startswith('(') and (line.endswith('),') or line.endswith(');')):
            line = line[1:]
            if line.endswith('),'):
                line = line[:-2]
            elif line.endswith(');'):
                line = line[:-2]
        
        # Contar valores respetando JSON y strings
        values = []
        current = ''
        depth = 0
        in_string = False
        escape_next = False
        
        for char in line:
            if escape_next:
                current += char
                escape_next = False
                continue
            
            if char == '\\':
                escape_next = True
                current += char
                continue
            
            if char == "'" and not escape_next:
                in_string = not in_string
                current += char
                continue
            
            if not in_string:
                if char == '[':
                    depth += 1
                    current += char
                elif char == ']':
                    depth -= 1
                    current += char
                elif char == ',' and depth == 0:
                    values.append(current.strip())
                    current = ''
                else:
                    current += char
            else:
                current += char
        
        if current.strip():
            values.append(current.strip())
        
        num_values = len(values)
        status = '✓' if num_values == num_columns else '✗'
        row_id = values[0] if values else 'N/A'
        print(f'Fila {i} (id={row_id}): {num_values} valores {status}')
        if num_values != num_columns:
            print(f'  ESPERADO: {num_columns}, OBTENIDO: {num_values}')
            # Mostrar los valores problemáticos
            if num_values > num_columns:
                print(f'  VALORES EXTRA: {values[num_columns:]}')
            elif num_values < num_columns:
                print(f'  FALTAN: {num_columns - num_values} valores')
            print()


