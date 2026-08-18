#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""Cliente HTTP común para APIs offline."""

import requests
from config import config

_http = requests.Session()
_http.trust_env = False


def offline_params(extra=None):
    p = {"id_cliente": config.ID_CLIENTE_MOON}
    if config.POS_API_KEY:
        p["api_key"] = config.POS_API_KEY
    if extra:
        p.update(extra)
    return p


def api_get(path, timeout=30, extra=None):
    url = f"{config.SERVER_URL}{path}" if path.startswith("/") else f"{config.API_BASE}/{path.lstrip('/')}"
    ex = {"id_empresa": config.ID_EMPRESA}
    if extra:
        ex.update(extra)
    params = offline_params(ex)
    return _http.get(url, params=params, timeout=timeout, headers={"X-Requested-With": "XMLHttpRequest"})
