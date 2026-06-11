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
import session_store

class AuthManager:
    def __init__(self):
        self.current_user = None
        self.id_cliente_moon = config.ID_CLIENTE_MOON
    
    def try_auto_login(self, sync_first=True):
        """Login sin UI usando secrets.env o sesión válida del día."""
        if not config.AUTO_LOGIN_ENABLED:
            return {"success": False, "message": "Auto-login deshabilitado"}

        sess = session_store.load_session()
        if sess and sess.get("usuario"):
            session = get_session()
            try:
                user = session.query(Usuario).filter_by(usuario=sess["usuario"]).first()
                if user and user.estado == 1:
                    chk = self._check_cuenta(session)
                    if not chk["success"]:
                        return chk
                    self.current_user = user
                    return {"success": True, "user": user, "via": "session"}
            finally:
                session.close()

        if not config.has_auto_credentials():
            return {"success": False, "message": "Sin credenciales en secrets.env"}

        if sync_first:
            try:
                self.sync_usuarios()
            except Exception:
                pass

        result = self.login(config.AUTO_LOGIN_USER, config.AUTO_LOGIN_PASSWORD)
        if result.get("success"):
            result["via"] = "secrets"
        return result

    def _check_cuenta(self, session):
        estado_cuenta = session.query(EstadoCuenta).filter_by(
            id_cliente_moon=self.id_cliente_moon
        ).first()
        if estado_cuenta and estado_cuenta.estado_bloqueo == 1:
            return {
                "success": False,
                "message": "Cuenta bloqueada por falta de pago.",
                "bloqueado": True,
            }
        if estado_cuenta and estado_cuenta.fecha_vencimiento and estado_cuenta.fecha_vencimiento < datetime.now():
            if estado_cuenta.saldo_cuenta > 0:
                return {"success": False, "message": "Cuenta vencida.", "bloqueado": True}
        return {"success": True}

    def login(self, usuario, password):
        """Autentica usuario localmente y valida estado de cuenta"""
        session = get_session()
        
        try:
            # Buscar usuario local
            print(f"🔍 Buscando usuario: {usuario}")
            user = session.query(Usuario).filter_by(usuario=usuario).first()
            
            if not user:
                # Listar todos los usuarios disponibles para debug
                todos_usuarios = session.query(Usuario).all()
                print(f"🔍 Usuarios en base local: {len(todos_usuarios)}")
                for u in todos_usuarios:
                    print(f"  - {u.usuario} (ID: {u.id}, Estado: {u.estado})")
                
                session.close()
                return {"success": False, "message": "Usuario no encontrado. Verifica que la sincronización se haya completado."}
            
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
            session_store.save_session(
                user.id, user.usuario, user.perfil or "", user.sucursal or ""
            )
            session_store._set("ultimo_login_ok", datetime.now().isoformat())
            session.close()
            return {"success": True, "user": user}
            
        except Exception as e:
            session.close()
            return {"success": False, "message": f"Error en login: {str(e)}"}
    
    def sync_usuarios(self):
        """Sincroniza usuarios desde servidor"""
        try:
            # Incluir ID de cliente como parámetro para autenticación básica
            from api_client import offline_params
            params = offline_params()
            # Usar ruta directa al archivo PHP
            url = f"{config.SERVER_URL}/api/usuarios.php"
            print(f"🔍 Sincronizando usuarios desde: {url}")
            print(f"🔍 Parámetros: {params}")
            
            response = requests.get(url, params=params, timeout=10)
            
            print(f"🔍 Status code: {response.status_code}")
            
            if response.status_code == 200:
                usuarios_data = response.json()
                print(f"🔍 Usuarios recibidos: {len(usuarios_data) if isinstance(usuarios_data, list) else 'No es lista'}")
                
                if not isinstance(usuarios_data, list):
                    print(f"❌ Error: respuesta no es una lista: {type(usuarios_data)}")
                    print(f"❌ Contenido: {usuarios_data}")
                    return False
                
                if len(usuarios_data) == 0:
                    print("⚠️  No se recibieron usuarios del servidor")
                    return False
                
                session = get_session()
                print(f"🔍 Sesión de base de datos obtenida")
                
                usuarios_guardados = 0
                usuarios_actualizados = 0
                
                try:
                    for user_data in usuarios_data:
                        print(f"🔍 Procesando usuario: {user_data.get('usuario', 'N/A')}")
                        print(f"🔍 Datos recibidos: {user_data}")
                        
                        usuario = session.query(Usuario).filter_by(
                            usuario=user_data['usuario']
                        ).first()
                        
                        if usuario:
                            # Actualizar existente
                            print(f"🔍 Usuario existente encontrado, actualizando...")
                            usuario.nombre = user_data['nombre']
                            usuario.perfil = user_data.get('perfil', 'Vendedor')
                            usuario.sucursal = user_data.get('sucursal', 'Local')
                            usuario.estado = user_data.get('estado', 1)
                            usuario.password_hash = user_data['password']  # Actualizar hash
                            usuario.ultima_sincronizacion = datetime.now()
                            usuarios_actualizados += 1
                            print(f"✅ Usuario actualizado: {user_data['usuario']}")
                        else:
                            # Crear nuevo
                            print(f"🔍 Creando nuevo usuario...")
                            usuario = Usuario(
                                id_servidor=user_data.get('id'),
                                usuario=user_data['usuario'],
                                password_hash=user_data['password'],
                                nombre=user_data['nombre'],
                                perfil=user_data.get('perfil', 'Vendedor'),
                                sucursal=user_data.get('sucursal', 'Local'),
                                estado=user_data.get('estado', 1)
                            )
                            session.add(usuario)
                            usuarios_guardados += 1
                            print(f"✅ Usuario agregado a sesión: {user_data['usuario']}")
                    
                    print(f"🔍 Haciendo commit a la base de datos...")
                    session.commit()
                    print(f"✅ Commit exitoso")
                    
                    # Verificar que se guardaron
                    session2 = get_session()
                    count = session2.query(Usuario).count()
                    session2.close()
                    print(f"✅ Verificación: {count} usuarios en base de datos")
                    
                except Exception as e:
                    print(f"❌ Error al procesar usuarios: {e}")
                    import traceback
                    traceback.print_exc()
                    session.rollback()
                    raise
                finally:
                    session.close()
                    print(f"🔍 Sesión cerrada")
                
                print(f"✅ Sincronización completada: {usuarios_guardados} nuevos, {usuarios_actualizados} actualizados")
                return True
            else:
                print(f"❌ Error HTTP {response.status_code}: {response.text}")
                return False
        except Exception as e:
            print(f"❌ Error sincronizando usuarios: {e}")
            import traceback
            traceback.print_exc()
            return False
    
    def sync_estado_cuenta(self, id_cliente_moon):
        """Sincroniza estado de cuenta desde servidor"""
        try:
            # Usar ruta directa al archivo PHP
            url = f"{config.SERVER_URL}/api/estado-cuenta.php"
            params = {'id': id_cliente_moon}
            response = requests.get(url, params=params, timeout=10)
            
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
