#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Script de prueba para verificar que la reconstrucción de CREATE TABLE funciona correctamente
"""

import db_alter_generator

# Crear parser
parser = db_alter_generator.SimpleSQLParser()

# Datos de prueba similares a los que causan problemas
campos = {
    'id': {
        'tipo': 'INT(11)',
        'null': False,
        'default': None,
        'auto_increment': True,
        'posicion': 1
    },
    'nombre': {
        'tipo': 'VARCHAR(100)',
        'null': True,
        'default': "'test'",
        'auto_increment': False,
        'posicion': 2
    },
    'fecha': {
        'tipo': 'DATETIME',
        'null': True,
        'default': 'CURRENT_TIMESTAMP',
        'auto_increment': False,
        'posicion': 3
    }
}

primary_key = ['id']
indices = {
    'idx_nombre': {
        'campos': ['nombre'],
        'unique': False
    }
}
engine = 'InnoDB'
charset = 'utf8mb3'

# Reconstruir CREATE TABLE
result = parser._reconstruir_create_table('test_table', campos, primary_key, indices, engine, charset)

print("=" * 60)
print("SQL GENERADO:")
print("=" * 60)
print(result)
print("=" * 60)

# Validar paréntesis
paren_abrir = result.count('(')
paren_cerrar = result.count(')')
print(f"\nParéntesis abiertos: {paren_abrir}")
print(f"Paréntesis cerrados: {paren_cerrar}")
print(f"Balanceados: {'✅ SÍ' if paren_abrir == paren_cerrar else '❌ NO'}")

# Validar con la función de validación
validacion = db_alter_generator.validar_sintaxis_sql(result)
print(f"\nValidación completa:")
print(f"  Válido: {'✅ SÍ' if validacion['valido'] else '❌ NO'}")
if validacion['errores']:
    print(f"  Errores: {validacion['errores']}")
if validacion['advertencias']:
    print(f"  Advertencias: {validacion['advertencias']}")


