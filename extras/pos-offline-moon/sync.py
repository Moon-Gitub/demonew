#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
SISTEMA DE SINCRONIZACIÓN
Sincroniza productos, ventas, usuarios y estado de cuenta con servidor
"""

import requests
import json
from datetime import datetime, timedelta
from database import (
    get_session, Producto, Venta, Cliente, MedioPago, ListaPrecio,
    Categoria, EmpresaConfig,
)
from connection import ConnectionMonitor
from auth import AuthManager
from config import config
from api_client import api_get, offline_params

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
    
    def sync_empresa(self):
        try:
            r = api_get("empresa-offline.php", timeout=15)
            if r.status_code != 200:
                return False
            data = r.json()
            session = get_session()
            emp = data.get("empresa") or {}
            row = session.query(EmpresaConfig).filter_by(id_empresa=config.ID_EMPRESA).first()
            import json as _json
            payload = _json.dumps(data, ensure_ascii=False)
            if row:
                row.nombre = emp.get("nombre", "")
                row.pto_vta = emp.get("pto_vta", 1)
                row.concepto_defecto = emp.get("concepto_defecto", 1)
                row.json_config = payload
            else:
                session.add(EmpresaConfig(
                    id_empresa=config.ID_EMPRESA,
                    nombre=emp.get("nombre", ""),
                    pto_vta=emp.get("pto_vta", 1),
                    concepto_defecto=emp.get("concepto_defecto", 1),
                    json_config=payload,
                ))
            for lista in data.get("listas", []):
                lp = session.query(ListaPrecio).filter_by(
                    codigo=lista["codigo"], id_empresa=config.ID_EMPRESA
                ).first()
                if lp:
                    lp.nombre = lista.get("nombre", lp.nombre)
                    lp.base_precio = lista.get("base_precio", "precio_venta")
                    lp.tipo_descuento = lista.get("tipo_descuento", "")
                    lp.valor_descuento = lista.get("valor_descuento", 0)
                else:
                    session.add(ListaPrecio(
                        id_servidor=lista.get("id"),
                        codigo=lista["codigo"],
                        nombre=lista.get("nombre", lista["codigo"]),
                        id_empresa=config.ID_EMPRESA,
                        base_precio=lista.get("base_precio", "precio_venta"),
                        tipo_descuento=lista.get("tipo_descuento", ""),
                        valor_descuento=lista.get("valor_descuento", 0),
                        orden=lista.get("orden", 0),
                    ))
            session.commit()
            session.close()
            return True
        except Exception as e:
            print(f"Error sync empresa: {e}")
            return False

    def sync_medios_pago(self):
        try:
            r = api_get("medios-pago.php", timeout=15)
            if r.status_code != 200:
                return False
            medios = r.json()
            session = get_session()
            for m in medios:
                row = session.query(MedioPago).filter_by(codigo=m["codigo"]).first()
                if row:
                    row.nombre = m.get("nombre", row.nombre)
                    row.activo = m.get("activo", 1)
                    row.orden = m.get("orden", 0)
                else:
                    session.add(MedioPago(
                        id_servidor=m.get("id"),
                        codigo=m["codigo"],
                        nombre=m.get("nombre", m["codigo"]),
                        descripcion=m.get("descripcion", ""),
                        activo=m.get("activo", 1),
                        orden=m.get("orden", 0),
                        requiere_codigo=m.get("requiere_codigo", 0),
                        requiere_banco=m.get("requiere_banco", 0),
                        requiere_numero=m.get("requiere_numero", 0),
                        requiere_fecha=m.get("requiere_fecha", 0),
                    ))
            session.commit()
            session.close()
            return True
        except Exception as e:
            print(f"Error sync medios: {e}")
            return False

    def sync_clientes(self):
        try:
            url = f"{config.SERVER_URL}/api/clientes.php"
            response = requests.get(url, params=offline_params(), timeout=60)
            if response.status_code != 200:
                return False
            clientes = response.json()
            session = get_session()
            for c in clientes:
                cid = int(c["id"])
                row = session.query(Cliente).filter_by(id_servidor=cid).first()
                display = c.get("display", f"{cid}-{c.get('nombre', '')}")
                if row:
                    row.nombre = c.get("nombre", "")
                    row.documento = c.get("documento", "")
                    row.display = display
                else:
                    session.add(Cliente(
                        id_servidor=cid,
                        nombre=c.get("nombre", ""),
                        documento=c.get("documento", ""),
                        tipo_documento=c.get("tipo_documento", 0),
                        condicion_iva=c.get("condicion_iva", 0),
                        email=c.get("email", ""),
                        telefono=c.get("telefono", ""),
                        direccion=c.get("direccion", ""),
                        display=display,
                    ))
            session.commit()
            session.close()
            return True
        except Exception as e:
            print(f"Error sync clientes: {e}")
            return False

    def sync_catalogo(self):
        """Catálogo unificado: productos + categorías + listas."""
        try:
            r = api_get(
                "catalogo-offline.php",
                timeout=120,
            )
            if r.status_code != 200:
                return self.sync_productos()
            data = r.json()
            session = get_session()
            for prod_data in data.get("productos", []):
                producto = session.query(Producto).filter_by(codigo=prod_data["codigo"]).first()
                if producto:
                    producto.descripcion = prod_data["descripcion"]
                    producto.precio_venta = prod_data["precio_venta"]
                    producto.precio_compra = prod_data.get("precio_compra", 0)
                    producto.stock = prod_data.get("stock", 0)
                    producto.iva = prod_data.get("tipo_iva", 21)
                    producto.ultima_actualizacion = datetime.now()
                else:
                    session.add(Producto(
                        id=prod_data["id"],
                        codigo=prod_data["codigo"],
                        descripcion=prod_data["descripcion"],
                        precio_venta=prod_data["precio_venta"],
                        precio_compra=prod_data.get("precio_compra", 0),
                        stock=prod_data.get("stock", 0),
                        categoria=str(prod_data.get("categoria", "")),
                        iva=prod_data.get("tipo_iva", 21),
                    ))
            for cat in data.get("categorias", []):
                nombre = cat.get("nombre", "")
                if not nombre:
                    continue
                row = session.query(Categoria).filter_by(nombre=nombre).first()
                if not row:
                    session.add(Categoria(id_servidor=cat.get("id"), nombre=nombre))
            for lista in data.get("listas", []):
                lp = session.query(ListaPrecio).filter_by(
                    codigo=lista["codigo"], id_empresa=config.ID_EMPRESA
                ).first()
                if lp:
                    lp.nombre = lista.get("nombre", lp.nombre)
                    lp.base_precio = lista.get("base_precio", "precio_venta")
                    lp.tipo_descuento = lista.get("tipo_descuento", "")
                    lp.valor_descuento = lista.get("valor_descuento", 0)
                else:
                    session.add(ListaPrecio(
                        id_servidor=lista.get("id"),
                        codigo=lista["codigo"],
                        nombre=lista.get("nombre", lista["codigo"]),
                        id_empresa=config.ID_EMPRESA,
                        base_precio=lista.get("base_precio", "precio_venta"),
                        tipo_descuento=lista.get("tipo_descuento", ""),
                        valor_descuento=lista.get("valor_descuento", 0),
                    ))
            session.commit()
            session.close()
            session_store_set = datetime.now().isoformat()
            from database import Configuracion
            s2 = get_session()
            row = s2.query(Configuracion).filter_by(clave="ultima_sincronizacion_catalogo").first()
            if row:
                row.valor = session_store_set
            else:
                s2.add(Configuracion(clave="ultima_sincronizacion_catalogo", valor=session_store_set))
            s2.commit()
            s2.close()
            return True
        except Exception as e:
            print(f"Error sync catalogo: {e}")
            return self.sync_productos()

    def sync_productos(self):
        """Descarga productos desde el servidor"""
        try:
            params = offline_params()
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
                
                # Calcular impuesto_detalle correctamente basado en productos
                # Mapeo de tipos de IVA a IDs y porcentajes
                iva_map = {
                    0: {"id": 3, "descripcion": "IVA 0%", "porcentaje": 0.0},
                    2: {"id": 9, "descripcion": "IVA 2,5%", "porcentaje": 0.025},
                    5: {"id": 8, "descripcion": "IVA 5%", "porcentaje": 0.05},
                    10: {"id": 4, "descripcion": "IVA 10,5%", "porcentaje": 0.105},
                    21: {"id": 5, "descripcion": "IVA 21%", "porcentaje": 0.21},
                    27: {"id": 6, "descripcion": "IVA 27%", "porcentaje": 0.27}
                }
                
                # Agrupar productos por tipo de IVA y calcular bases imponibles
                bases_por_iva = {}  # {tipo_iva: {"base": 0, "iva": 0}}
                
                for prod in productos_formateados:
                    # Obtener tipo de IVA del producto (buscar en BD local si es necesario)
                    tipo_iva = prod.get('tipo_iva', 21)  # Por defecto 21% si no se especifica
                    
                    # Si no viene en el producto, intentar obtenerlo de la BD local
                    if 'tipo_iva' not in prod or prod.get('tipo_iva') is None:
                        try:
                            from database import get_session, Producto
                            session_temp = get_session()
                            producto_bd = session_temp.query(Producto).filter_by(id=prod.get('id', 0)).first()
                            if producto_bd and producto_bd.iva is not None:
                                tipo_iva = int(producto_bd.iva)
                            session_temp.close()
                        except:
                            tipo_iva = 21  # Por defecto
                    
                    # Obtener subtotal del producto
                    subtotal = float(prod.get('total', prod.get('subtotal', 0)))
                    
                    # Calcular base imponible según tipo de IVA
                    if tipo_iva in iva_map:
                        porcentaje = iva_map[tipo_iva]["porcentaje"]
                        if porcentaje > 0:
                            # Base imponible = subtotal / (1 + porcentaje)
                            base_imponible = subtotal / (1 + porcentaje)
                            iva_calculado = subtotal - base_imponible
                        else:
                            # IVA 0%
                            base_imponible = subtotal
                            iva_calculado = 0.0
                        
                        # Acumular por tipo de IVA
                        if tipo_iva not in bases_por_iva:
                            bases_por_iva[tipo_iva] = {"base": 0.0, "iva": 0.0}
                        bases_por_iva[tipo_iva]["base"] += base_imponible
                        bases_por_iva[tipo_iva]["iva"] += iva_calculado
                    else:
                        # Si el tipo de IVA no está en el mapa, usar IVA 21% por defecto
                        base_imponible = subtotal / 1.21
                        iva_calculado = subtotal - base_imponible
                        if 21 not in bases_por_iva:
                            bases_por_iva[21] = {"base": 0.0, "iva": 0.0}
                        bases_por_iva[21]["base"] += base_imponible
                        bases_por_iva[21]["iva"] += iva_calculado
                
                # Construir impuesto_detalle en el formato correcto
                impuesto_detalle = []
                for tipo_iva, valores in bases_por_iva.items():
                    if tipo_iva in iva_map:
                        base_redondeada = round(valores["base"], 2)
                        iva_redondeado = round(valores["iva"], 2)
                        # Solo agregar si hay valores mayores a 0
                        if base_redondeada > 0 or iva_redondeado > 0:
                            impuesto_detalle.append({
                                "id": iva_map[tipo_iva]["id"],
                                "descripcion": iva_map[tipo_iva]["descripcion"],
                                "baseImponible": str(base_redondeada),
                                "iva": str(iva_redondeado)
                            })
                
                # Si no hay impuestos calculados, usar IVA 0% con el total
                if not impuesto_detalle:
                    impuesto_detalle.append({
                        "id": 3,
                        "descripcion": "IVA 0%",
                        "baseImponible": str(round(float(venta.total), 2)),
                        "iva": "0"
                    })
                
                # Log para depuración
                print(f"   📊 impuesto_detalle calculado: {json.dumps(impuesto_detalle)}")
                
                # Convertir impuesto_detalle a JSON string
                impuesto_detalle_json = json.dumps(impuesto_detalle, ensure_ascii=False)
                
                venta_data = {
                    'fecha': venta.fecha.isoformat(),
                    'cliente': cliente_id,  # Enviar ID del cliente
                    'productos': productos_formateados,
                    'total': float(venta.total),
                    'metodo_pago': venta.metodo_pago,
                    'sucursal': sucursal_servidor,
                    'id_empresa': config.ID_EMPRESA,
                    'impuesto_detalle': impuesto_detalle_json,  # Enviar como JSON string
                    'creado_local': True
                }
                
                print(f"   📤 Enviando impuesto_detalle: {impuesto_detalle_json[:200]}...")
                
                print(f"🔄 Sincronizando venta ID {venta.id} con {len(productos_formateados)} productos...")
                print(f"   Total: ${venta.total}, Método: {venta.metodo_pago}")
                print(f"   Productos: {json.dumps([p.get('descripcion', 'N/A') for p in productos_formateados[:3]], ensure_ascii=False)}")
                print(f"   ✅ impuesto_detalle incluido en venta_data: {'impuesto_detalle' in venta_data}")
                if 'impuesto_detalle' in venta_data:
                    print(f"   📋 impuesto_detalle valor: {venta_data['impuesto_detalle'][:200]}...")
                
                try:
                    # Log del JSON que se va a enviar
                    json_to_send = json.dumps(venta_data, ensure_ascii=False)
                    print(f"   📤 JSON completo a enviar (primeros 500 chars): {json_to_send[:500]}...")
                    
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
            print("🔄 Sincronizando empresa y medios...")
        self.sync_empresa()
        self.sync_medios_pago()

        if not silent:
            print("🔄 Sincronizando clientes...")
        self.sync_clientes()

        if not silent:
            print("🔄 Sincronizando catálogo...")
        self.sync_catalogo()
        
        if not silent:
            print("🔄 Sincronizando ventas pendientes...")
        self.sync_ventas()
        
        if not silent:
            print("✅ Sincronización completada")
        self.syncing = False
