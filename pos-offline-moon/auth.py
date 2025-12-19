#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
SISTEMA DE AUTENTICACIÓN
Maneja login, validación de estado de cuenta y sincronización de usuarios
"""

from database import get_session, Usuario, EstadoCuenta, verify_password
from datetime import datetime
import requests
from config import config

class AuthManager:
    def __init__(self):
        self.current_user = None
        self.id_cliente_moon = config.ID_CLIENTE_MOON
    
    def login(self, usuario, password):
        """Autentica usuario localmente y valida estado de cuenta"""
        session = get_session()
        
        try:
            # Buscar usuario local
            user = session.query(Usuario).filter_by(usuario=usuario).first()
            
            if not user:
                session.close()
                return {"success": False, "message": "Usuario no encontrado"}
            
            # Verificar contraseña
            if not verify_password(password, user.password_hash):
                session.close()
                return {"success": False, "message": "Contraseña incorrecta"}
            
            # Verificar estado del usuario
            if user.estado != 1:
                session.close()
                return {"success": False, "message": "Usuario inactivo"}
            
            # Verificar estado de cuenta/pago
            estado_cuenta = session.query(EstadoCuenta).filter_by(
                id_cliente_moon=self.id_cliente_moon
            ).first()
            
            if estado_cuenta:
                # Si está bloqueado, no permitir login
                if estado_cuenta.estado_bloqueo == 1:
                    session.close()
                    return {
                        "success": False, 
                        "message": "Cuenta bloqueada por falta de pago. Por favor, realice el pago para continuar.",
                        "bloqueado": True,
                        "saldo": estado_cuenta.saldo_cuenta
                    }
                
                # Verificar si el saldo está vencido (si aplica)
                if estado_cuenta.fecha_vencimiento and estado_cuenta.fecha_vencimiento < datetime.now():
                    if estado_cuenta.saldo_cuenta > 0:
                        session.close()
                        return {
                            "success": False,
                            "message": "Cuenta vencida. Por favor, realice el pago para continuar.",
                            "bloqueado": True,
                            "saldo": estado_cuenta.saldo_cuenta
                        }
            
            # Login exitoso
            self.current_user = user
            session.close()
            return {"success": True, "user": user}
            
        except Exception as e:
            session.close()
            return {"success": False, "message": f"Error en login: {str(e)}"}
    
    def sync_usuarios(self):
        """Sincroniza usuarios desde servidor"""
        try:
            # Incluir ID de cliente como parámetro para autenticación básica
            params = {'id_cliente': config.ID_CLIENTE_MOON}
            response = requests.get(f"{config.API_BASE}/usuarios", params=params, timeout=10)
            
            if response.status_code == 200:
                usuarios_data = response.json()
                session = get_session()
                
                for user_data in usuarios_data:
                    usuario = session.query(Usuario).filter_by(
                        usuario=user_data['usuario']
                    ).first()
                    
                    if usuario:
                        # Actualizar existente
                        usuario.nombre = user_data['nombre']
                        usuario.perfil = user_data.get('perfil', 'Vendedor')
                        usuario.sucursal = user_data.get('sucursal', 'Local')
                        usuario.estado = user_data.get('estado', 1)
                        usuario.password_hash = user_data['password']  # Actualizar hash
                        usuario.ultima_sincronizacion = datetime.now()
                    else:
                        # Crear nuevo
                        usuario = Usuario(
                            id_servidor=user_data['id'],
                            usuario=user_data['usuario'],
                            password_hash=user_data['password'],
                            nombre=user_data['nombre'],
                            perfil=user_data.get('perfil', 'Vendedor'),
                            sucursal=user_data.get('sucursal', 'Local'),
                            estado=user_data.get('estado', 1)
                        )
                        session.add(usuario)
                
                session.commit()
                session.close()
                return True
        except Exception as e:
            print(f"Error sincronizando usuarios: {e}")
            return False
    
    def sync_estado_cuenta(self, id_cliente_moon):
        """Sincroniza estado de cuenta desde servidor"""
        try:
            response = requests.get(
                f"{config.API_BASE}/estado-cuenta/{id_cliente_moon}",
                timeout=10
            )
            
            if response.status_code == 200:
                estado_data = response.json()
                session = get_session()
                
                estado = session.query(EstadoCuenta).filter_by(
                    id_cliente_moon=id_cliente_moon
                ).first()
                
                if estado:
                    estado.estado_bloqueo = estado_data.get('estado_bloqueo', 0)
                    estado.saldo_cuenta = estado_data.get('saldo', 0.0)
                    if estado_data.get('ultimo_pago'):
                        estado.ultimo_pago = datetime.fromisoformat(estado_data['ultimo_pago'])
                    if estado_data.get('fecha_vencimiento'):
                        estado.fecha_vencimiento = datetime.fromisoformat(estado_data['fecha_vencimiento'])
                    estado.ultima_sincronizacion = datetime.now()
                else:
                    estado = EstadoCuenta(
                        id_cliente_moon=id_cliente_moon,
                        estado_bloqueo=estado_data.get('estado_bloqueo', 0),
                        saldo_cuenta=estado_data.get('saldo', 0.0)
                    )
                    if estado_data.get('ultimo_pago'):
                        estado.ultimo_pago = datetime.fromisoformat(estado_data['ultimo_pago'])
                    if estado_data.get('fecha_vencimiento'):
                        estado.fecha_vencimiento = datetime.fromisoformat(estado_data['fecha_vencimiento'])
                    session.add(estado)
                
                session.commit()
                session.close()
                return True
        except Exception as e:
            print(f"Error sincronizando estado de cuenta: {e}")
            return False
    
    def verificar_estado_cuenta_local(self, id_cliente_moon):
        """Verifica estado de cuenta desde base local"""
        session = get_session()
        estado = session.query(EstadoCuenta).filter_by(
            id_cliente_moon=id_cliente_moon
        ).first()
        session.close()
        
        if not estado:
            return {"activo": True, "mensaje": "Estado no verificado"}
        
        if estado.estado_bloqueo == 1:
            return {
                "activo": False,
                "bloqueado": True,
                "mensaje": "Cuenta bloqueada por falta de pago",
                "saldo": estado.saldo_cuenta
            }
        
        if estado.fecha_vencimiento and estado.fecha_vencimiento < datetime.now():
            if estado.saldo_cuenta > 0:
                return {
                    "activo": False,
                    "vencido": True,
                    "mensaje": "Cuenta vencida",
                    "saldo": estado.saldo_cuenta
                }
        
        return {"activo": True, "mensaje": "Cuenta activa"}
