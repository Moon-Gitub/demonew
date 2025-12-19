#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
GESTIÓN DE BASE DE DATOS LOCAL
SQLite para almacenar productos, ventas, usuarios y estado de cuenta
"""

from sqlalchemy import create_engine, Column, Integer, String, Float, DateTime, Boolean, JSON, Text
from sqlalchemy.ext.declarative import declarative_base
from sqlalchemy.orm import sessionmaker
from datetime import datetime
from config import config
import bcrypt

Base = declarative_base()

class Usuario(Base):
    __tablename__ = 'usuarios'
    
    id = Column(Integer, primary_key=True)
    id_servidor = Column(Integer, nullable=True)
    usuario = Column(String, unique=True, nullable=False)
    password_hash = Column(String, nullable=False)
    nombre = Column(String, nullable=False)
    perfil = Column(String, default="Vendedor")
    sucursal = Column(String, default="Local")
    estado = Column(Integer, default=1)  # 1=activo, 0=inactivo
    ultima_sincronizacion = Column(DateTime, default=datetime.now)

class EstadoCuenta(Base):
    __tablename__ = 'estado_cuenta'
    
    id = Column(Integer, primary_key=True)
    id_cliente_moon = Column(Integer, nullable=False)
    estado_bloqueo = Column(Integer, default=0)  # 0=activo, 1=bloqueado
    saldo_cuenta = Column(Float, default=0.0)
    ultimo_pago = Column(DateTime, nullable=True)
    fecha_vencimiento = Column(DateTime, nullable=True)
    ultima_sincronizacion = Column(DateTime, default=datetime.now)

class Producto(Base):
    __tablename__ = 'productos'
    
    id = Column(Integer, primary_key=True)
    codigo = Column(String, unique=True, nullable=False)
    descripcion = Column(String, nullable=False)
    precio_venta = Column(Float, nullable=False)
    precio_compra = Column(Float)
    stock = Column(Float, default=0)
    categoria = Column(String)
    proveedor = Column(String)
    iva = Column(Float, default=0)
    sincronizado = Column(Boolean, default=True)
    ultima_actualizacion = Column(DateTime, default=datetime.now)

class Venta(Base):
    __tablename__ = 'ventas'
    
    id = Column(Integer, primary_key=True, autoincrement=True)
    id_servidor = Column(Integer, nullable=True)
    fecha = Column(DateTime, default=datetime.now, nullable=False)
    cliente = Column(String, default="Consumidor Final")
    productos = Column(JSON, nullable=False)
    total = Column(Float, nullable=False)
    metodo_pago = Column(String, default="Efectivo")
    sucursal = Column(String, default="Local")
    sincronizado = Column(Boolean, default=False)
    fecha_sincronizacion = Column(DateTime, nullable=True)
    creado_local = Column(Boolean, default=True)

class Configuracion(Base):
    __tablename__ = 'configuracion'
    
    clave = Column(String, primary_key=True)
    valor = Column(Text)

# Inicializar base de datos
print(f"🔍 Inicializando base de datos en: {config.DB_PATH}")
engine = create_engine(f'sqlite:///{config.DB_PATH}', echo=False)

# Crear todas las tablas
print(f"🔍 Creando tablas...")
Base.metadata.create_all(engine)
print(f"✅ Tablas creadas/verificadas")

Session = sessionmaker(bind=engine)

def get_session():
    session = Session()
    print(f"🔍 Nueva sesión creada")
    return session

def hash_password(password):
    """Hashea la contraseña usando bcrypt"""
    return bcrypt.hashpw(password.encode('utf-8'), bcrypt.gensalt()).decode('utf-8')

def verify_password(password, password_hash):
    """Verifica contraseña"""
    try:
        return bcrypt.checkpw(password.encode('utf-8'), password_hash.encode('utf-8'))
    except:
        return False
