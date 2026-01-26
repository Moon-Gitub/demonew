#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
SISTEMA DE SINCRONIZACIÓN
Sincroniza productos, ventas, usuarios y estado de cuenta con servidor
"""

import requests
import json
from datetime import datetime, timedelta
from database import get_session, Producto, Venta
from connection import ConnectionMonitor
from auth import AuthManager
from config import config

class SyncManager:
    def __init__(self):
        self.connection_monitor = ConnectionMonitor(callback=self.on_connection_change)
        self.syncing = False
        self.auth_manager = AuthManager()
    
    def on_connection_change(self, is_online):
        """Se ejecuta cuando cambia el estado de conexión"""
        if is_online:
            print("✅ Conexión restaurada - Iniciando sincronización...")
            self.sync_all()
    
    def sync_usuarios(self):
        """Sincroniza usuarios desde servidor"""
        return self.auth_manager.sync_usuarios()
    
    def sync_estado_cuenta(self, id_cliente_moon=None):
        """Sincroniza estado de cuenta"""
        id_cliente = id_cliente_moon or config.ID_CLIENTE_MOON
        return self.auth_manager.sync_estado_cuenta(id_cliente)
    
    def sync_productos(self):
        """Descarga productos desde el servidor"""
        try:
            # Incluir ID de cliente como parámetro para autenticación básica
            params = {'id_cliente': config.ID_CLIENTE_MOON}
            # Usar ruta directa al archivo PHP
            url = f"{config.SERVER_URL}/api/productos.php"
            response = requests.get(url, params=params, timeout=10)
            
            if response.status_code == 200:
                productos_data = response.json()
                session = get_session()
                
                for prod_data in productos_data:
                    producto = session.query(Producto).filter_by(codigo=prod_data['codigo']).first()
                    
                    if producto:
                        producto.descripcion = prod_data['descripcion']
                        producto.precio_venta = prod_data['precio_venta']
                        producto.stock = prod_data.get('stock', 0)
                        producto.ultima_actualizacion = datetime.now()
                    else:
                        producto = Producto(
                            id=prod_data['id'],
                            codigo=prod_data['codigo'],
                            descripcion=prod_data['descripcion'],
                            precio_venta=prod_data['precio_venta'],
                            precio_compra=prod_data.get('precio_compra', 0),
                            stock=prod_data.get('stock', 0),
                            categoria=prod_data.get('categoria', ''),
                            proveedor=prod_data.get('proveedor', ''),
                            iva=prod_data.get('tipo_iva', 0)
                        )
                        session.add(producto)
                
                session.commit()
                session.close()
                return True
        except Exception as e:
            print(f"Error sincronizando productos: {e}")
            return False
    
    def sync_ventas(self):
        """Sube ventas locales al servidor"""
        session = get_session()
        ventas_pendientes = session.query(Venta).filter_by(sincronizado=False).all()
        
        if not ventas_pendientes:
            session.close()
            return True
        
        try:
            # Usar ruta directa al archivo PHP
            url = f"{config.SERVER_URL}/api/ventas.php"
            
            for venta in ventas_pendientes:
                # Convertir productos al formato esperado por el API
                productos_formateados = []
                if isinstance(venta.productos, list):
                    for prod in venta.productos:
                        if isinstance(prod, dict):
                            productos_formateados.append({
                                'id': prod.get('id', prod.get('id_producto', 0)),
                                'descripcion': prod.get('descripcion', ''),
                                'cantidad': prod.get('cantidad', 1),
                                'categoria': prod.get('categoria', ''),
                                'stock': prod.get('stock', 0),
                                'precio_compra': prod.get('precio_compra', 0),
                                'precio': prod.get('precio', prod.get('precio_venta', 0)),
                                'total': prod.get('subtotal', prod.get('total', prod.get('precio', 0) * prod.get('cantidad', 1)))
                            })
                
                # Usar id_cliente si está disponible, sino usar cliente (nombre) para extraer ID
                cliente_id = getattr(venta, 'id_cliente', None) or 1
                if cliente_id == 1 and venta.cliente and venta.cliente != "Consumidor Final":
                    # Intentar extraer ID si está en formato "ID-Nombre"
                    if '-' in str(venta.cliente):
                        try:
                            cliente_id = int(str(venta.cliente).split('-')[0])
                        except:
                            cliente_id = 1
                
                # Mapear sucursal: 'Local' debe ser 'stock' para el servidor
                sucursal_servidor = venta.sucursal or 'stock'
                if sucursal_servidor == 'Local':
                    sucursal_servidor = 'stock'
                
                venta_data = {
                    'fecha': venta.fecha.isoformat(),
                    'cliente': cliente_id,  # Enviar ID del cliente
                    'productos': productos_formateados,
                    'total': float(venta.total),
                    'metodo_pago': venta.metodo_pago,
                    'sucursal': sucursal_servidor,
                    'creado_local': True
                }
                
                print(f"🔄 Sincronizando venta ID {venta.id} con {len(productos_formateados)} productos...")
                print(f"   Total: ${venta.total}, Método: {venta.metodo_pago}")
                print(f"   Productos: {json.dumps([p.get('descripcion', 'N/A') for p in productos_formateados[:3]], ensure_ascii=False)}")
                
                try:
                    response = requests.post(
                        url,
                        json=venta_data,
                        timeout=30,
                        headers={'Content-Type': 'application/json'}
                    )
                    
                    print(f"   Status: {response.status_code}")
                    
                    if response.status_code == 200:
                        resultado = response.json()
                        if resultado.get('success') or resultado.get('id'):
                            venta.id_servidor = resultado.get('id')
                            venta.sincronizado = True
                            venta.fecha_sincronizacion = datetime.now()
                            session.commit()
                            print(f"   ✅ Venta sincronizada exitosamente (ID servidor: {venta.id_servidor})")
                        else:
                            error_msg = resultado.get('error', 'Error desconocido')
                            print(f"   ❌ Error en respuesta: {error_msg}")
                            if resultado.get('respuesta_completa'):
                                print(f"   Detalles: {resultado.get('respuesta_completa')}")
                    else:
                        try:
                            error_data = response.json()
                            error_msg = error_data.get('error', response.text[:200])
                        except:
                            error_msg = response.text[:200]
                        print(f"   ❌ Error HTTP {response.status_code}: {error_msg}")
                except requests.exceptions.RequestException as e:
                    print(f"   ❌ Error de conexión: {str(e)}")
                except Exception as e:
                    print(f"   ❌ Error inesperado: {str(e)}")
                    import traceback
                    traceback.print_exc()
            
            session.close()
            return True
        except Exception as e:
            print(f"❌ Error sincronizando ventas: {e}")
            import traceback
            traceback.print_exc()
            session.close()
            return False
    
    def sync_ventas_historial(self, dias=30):
        """Descarga ventas de los últimos N días"""
        try:
            # Formatear fecha en formato MySQL (YYYY-MM-DD HH:MM:SS)
            fecha_desde = datetime.now() - timedelta(days=dias)
            fecha_desde_str = fecha_desde.strftime('%Y-%m-%d %H:%M:%S')
            
            # Usar ruta directa al archivo PHP
            url = f"{config.SERVER_URL}/api/ventas.php"
            print(f"🔍 Descargando historial de ventas desde: {fecha_desde_str} (últimos {dias} días)")
            
            response = requests.get(
                url,
                params={'desde': fecha_desde_str, 'id_cliente': config.ID_CLIENTE_MOON},
                timeout=30
            )
            
            print(f"🔍 Status code historial: {response.status_code}")
            
            if response.status_code == 200:
                data = response.json()
                if isinstance(data, list):
                    print(f"✅ Ventas recibidas del servidor: {len(data)}")
                    if len(data) > 0:
                        print(f"   Primera venta: {data[0].get('fecha', 'N/A')} - ${data[0].get('total', 0):.2f}")
                    return data
                else:
                    print(f"⚠️  Respuesta no es lista: {type(data)}, contenido: {str(data)[:200]}")
                    return []
            else:
                error_text = response.text[:500] if hasattr(response, 'text') else str(response)
                print(f"❌ Error HTTP {response.status_code}: {error_text}")
                return []
        except Exception as e:
            print(f"❌ Error descargando historial: {e}")
            import traceback
            traceback.print_exc()
            return []
    
    def sync_all(self, id_cliente_moon=None, silent=False):
        """Sincroniza todo cuando hay conexión"""
        if self.syncing:
            return
        
        self.syncing = True
        id_cliente = id_cliente_moon or config.ID_CLIENTE_MOON
        
        if not silent:
            print("🔄 Sincronizando usuarios...")
        self.sync_usuarios()
        
        if not silent:
            print("🔄 Sincronizando estado de cuenta...")
        self.sync_estado_cuenta(id_cliente)
        
        if not silent:
            print("🔄 Sincronizando productos...")
        self.sync_productos()
        
        if not silent:
            print("🔄 Sincronizando ventas pendientes...")
        self.sync_ventas()
        
        if not silent:
            print("✅ Sincronización completada")
        self.syncing = False
