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
    id_cliente = Column(Integer, default=1, nullable=True)  # ID del cliente para sincronización (nullable para compatibilidad)
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


class Cliente(Base):
    __tablename__ = 'clientes'

    id = Column(Integer, primary_key=True)
    id_servidor = Column(Integer, unique=True, nullable=False)
    nombre = Column(String, default="")
    documento = Column(String, default="")
    tipo_documento = Column(Integer, default=0)
    condicion_iva = Column(Integer, default=0)
    email = Column(String, default="")
    telefono = Column(String, default="")
    direccion = Column(String, default="")
    display = Column(String, default="")
    ultima_sincronizacion = Column(DateTime, default=datetime.now)


class MedioPago(Base):
    __tablename__ = 'medios_pago'

    id = Column(Integer, primary_key=True)
    id_servidor = Column(Integer, nullable=True)
    codigo = Column(String, unique=True, nullable=False)
    nombre = Column(String, nullable=False)
    descripcion = Column(String, default="")
    activo = Column(Integer, default=1)
    orden = Column(Integer, default=0)
    requiere_codigo = Column(Integer, default=0)
    requiere_banco = Column(Integer, default=0)
    requiere_numero = Column(Integer, default=0)
    requiere_fecha = Column(Integer, default=0)


class ListaPrecio(Base):
    __tablename__ = 'listas_precio'

    id = Column(Integer, primary_key=True)
    id_servidor = Column(Integer, nullable=True)
    codigo = Column(String, nullable=False)
    nombre = Column(String, nullable=False)
    id_empresa = Column(Integer, default=1)
    activo = Column(Integer, default=1)
    orden = Column(Integer, default=0)
    base_precio = Column(String, default="venta")
    tipo_descuento = Column(String, default="")
    valor_descuento = Column(Float, default=0)


class ProductoPrecio(Base):
    __tablename__ = 'producto_precios'

    id = Column(Integer, primary_key=True, autoincrement=True)
    codigo_lista = Column(String, nullable=False)
    id_producto = Column(Integer, nullable=False)
    precio = Column(Float, nullable=False)


class Categoria(Base):
    __tablename__ = 'categorias'

    id = Column(Integer, primary_key=True)
    id_servidor = Column(Integer, nullable=True)
    nombre = Column(String, nullable=False)


class EmpresaConfig(Base):
    __tablename__ = 'empresa_config'

    id = Column(Integer, primary_key=True)
    id_empresa = Column(Integer, unique=True, nullable=False)
    nombre = Column(String, default="")
    pto_vta = Column(Integer, default=1)
    concepto_defecto = Column(Integer, default=1)
    json_config = Column(Text, default="{}")


# Inicializar base de datos
import os
_VERBOSE_DB = os.environ.get("POS_OFFLINE_DEBUG", "").lower() in ("1", "true", "yes")

def _db_log(msg):
    if _VERBOSE_DB:
        print(msg)

_db_log(f"Inicializando base de datos en: {config.DB_PATH}")
engine = create_engine(f'sqlite:///{config.DB_PATH}', echo=False)
Base.metadata.create_all(engine)
_db_log("Tablas SQLite listas")

# Migración: agregar columna id_cliente si no existe
try:
    from sqlalchemy import inspect, text
    inspector = inspect(engine)
    columns = [col['name'] for col in inspector.get_columns('ventas')]
    
    if 'id_cliente' not in columns:
        _db_log("Agregando columna id_cliente a tabla ventas...")
        with engine.connect() as conn:
            conn.execute(text("ALTER TABLE ventas ADD COLUMN id_cliente INTEGER DEFAULT 1"))
            conn.commit()
        _db_log("Columna id_cliente agregada")
except Exception as e:
    _db_log(f"Columna id_cliente: {e}")

Session = sessionmaker(bind=engine)

def get_session():
    return Session()

def hash_password(password):
    """Hashea la contraseña usando bcrypt"""
    return bcrypt.hashpw(password.encode('utf-8'), bcrypt.gensalt()).decode('utf-8')

def verify_password(password, password_hash):
    """Verifica contraseña"""
    try:
        return bcrypt.checkpw(password.encode('utf-8'), password_hash.encode('utf-8'))
    except:
        return False
