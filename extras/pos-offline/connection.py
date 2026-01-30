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

class ConnectionMonitor:
    def __init__(self, callback=None):
        self.is_online = False
        self.callback = callback
        self.monitoring = False
        self.check_interval = config.CONNECTION_CHECK_INTERVAL
        
    def check_connection(self):
        """Verifica si hay conexión a internet"""
        try:
            response = requests.get(f"{config.SERVER_URL}/", timeout=3)
            return response.status_code == 200
        except:
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
