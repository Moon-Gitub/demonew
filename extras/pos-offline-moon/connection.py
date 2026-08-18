#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
DETECCIÓN DE CONEXIÓN A INTERNET
Monitorea el estado de conexión y notifica cambios
"""

import requests
import time
from threading import Thread, Event
from config import config

# Sesión propia: en Windows el auto-detect de proxy (WPAD) y el DNS
# pueden dejar requests.get colgado mucho más que el timeout, y la
# ventana nunca llega a dibujarse.
_http = requests.Session()
_http.trust_env = False


class ConnectionMonitor:
    def __init__(self, callback=None):
        self.is_online = False
        self.callback = callback
        self.monitoring = False
        self.check_interval = config.CONNECTION_CHECK_INTERVAL
        
    def check_connection(self):
        """Verifica si hay conexión al servidor. Nunca debe bloquear la UI."""
        try:
            response = _http.get(
                f"{config.SERVER_URL}/",
                timeout=(1, 2),
                allow_redirects=False,
            )
            return response.status_code < 500
        except Exception:
            return False
    
    def start_monitoring(self):
        """Inicia el monitoreo continuo de conexión"""
        self.monitoring = True
        thread = Thread(target=self._monitor_loop, daemon=True)
        thread.start()
    
    def _monitor_loop(self):
        """Loop de monitoreo en segundo plano"""
        while self.monitoring:
            was_online = self.is_online
            self.is_online = self.check_connection()
            
            # Si cambió el estado, notificar
            if was_online != self.is_online and self.callback:
                self.callback(self.is_online)
            
            time.sleep(self.check_interval)
    
    def stop_monitoring(self):
        self.monitoring = False
