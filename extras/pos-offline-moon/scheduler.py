#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""Programador: sync + auto-login a hora configurada."""

import threading
from datetime import datetime
from config import config


class MorningScheduler:
    def __init__(self, on_morning_job=None):
        self._on_morning_job = on_morning_job
        self._last_run_date = None
        self._thread = None
        self._stop = threading.Event()

    def _parse_time(self):
        try:
            parts = str(config.AUTO_LOGIN_TIME).strip().split(":")
            h = int(parts[0])
            m = int(parts[1]) if len(parts) > 1 else 0
            return h, m
        except Exception:
            return 8, 0

    def _should_run_now(self):
        if not config.AUTO_LOGIN_ENABLED:
            return False
        h, m = self._parse_time()
        now = datetime.now()
        today = now.date().isoformat()
        if self._last_run_date == today:
            return False
        if now.hour > h or (now.hour == h and now.minute >= m):
            return True
        return False

    def tick(self):
        if self._should_run_now() and self._on_morning_job:
            self._last_run_date = datetime.now().date().isoformat()
            try:
                self._on_morning_job()
            except Exception as e:
                print(f"Error job matutino: {e}")

    def _loop(self):
        while not self._stop.wait(30):
            self.tick()

    def start(self):
        if self._thread and self._thread.is_alive():
            return
        self._stop.clear()
        self._thread = threading.Thread(target=self._loop, daemon=True)
        self._thread.start()

    def stop(self):
        self._stop.set()
