# 🧪 Suite de Testing - Sistema de Cobro Moon POS

Esta carpeta contiene herramientas para probar el sistema de cobro en diferentes escenarios simulando distintos días del mes.

## 📁 Contenido de la carpeta

```
testing/
├── index.html                  # Menú principal de testing (COMIENZA AQUÍ)
├── simulador-base.php          # Motor de simulación
├── test-dia-3.php             # Test: Días 1-4 (Sin recargo)
├── test-dia-7.php             # Test: Días 5-9 (Período de gracia)
├── test-dia-12.php            # Test: Días 10-14 (10% recargo)
├── test-dia-17.php            # Test: Días 15-19 (15% recargo)
├── test-dia-23.php            # Test: Días 20-24 (20% recargo)
├── test-dia-26.php            # Test: Días 25-26 (30% recargo)
├── test-dia-28.php            # Test: Día 27+ (30% recargo + BLOQUEO)
├── test-dia-custom.php        # Test personalizado (cualquier día)
└── README.md                   # Este archivo
```

## 🚀 Cómo usar

### Opción 1: Interfaz web (Recomendado)

1. Abre en tu navegador:
   ```
   http://tudominio.com/testing/index.html
   ```

2. Haz clic en cualquiera de los escenarios predefinidos

3. También puedes usar el "Simulador Personalizado" para probar cualquier día específico

### Opción 2: Acceso directo a los tests

Puedes acceder directamente a cada archivo PHP:

- Día 3: `http://tudominio.com/testing/test-dia-3.php`
- Día 7: `http://tudominio.com/testing/test-dia-7.php`
- Día 12: `http://tudominio.com/testing/test-dia-12.php`
- Día 17: `http://tudominio.com/testing/test-dia-17.php`
- Día 23: `http://tudominio.com/testing/test-dia-23.php`
- Día 26: `http://tudominio.com/testing/test-dia-26.php`
- Día 28: `http://tudominio.com/testing/test-dia-28.php`
- Personalizado: `http://tudominio.com/testing/test-dia-custom.php?dia=15`

## 📊 Escenarios de prueba

| Día | Recargo | Modal | Estado |
|-----|---------|-------|--------|
| 1-4 | 0% | Puede cerrar | Normal |
| 5-9 | 0% | Puede cerrar | Advertencia |
| 10-14 | 10% | Puede cerrar | Mora 1 |
| 15-19 | 15% | Puede cerrar | Mora 2 |
| 20-24 | 20% | Puede cerrar | Mora 3 |
| 25-26 | 30% | Puede cerrar | Mora Máxima |
| 27+ | 30% | **NO puede cerrar** | **BLOQUEADO** |

## 🎯 Qué prueba cada escenario

### Sin recargo (Días 1-4)
- ✅ Cliente puede pagar sin recargos
- ✅ Modal se muestra con advertencia
- ✅ Badge verde en navbar
- ✅ Mensaje: "Recuerda abonar antes del día 5"

### Período de gracia (Días 5-9)
- ✅ Aún sin recargos
- ⚠️ Advertencias más fuertes
- ✅ Modal se muestra automáticamente
- ✅ Badge azul en navbar

### Primera mora (Días 10-14)
- ⚠️ 10% de recargo sobre servicios mensuales
- ✅ Otros cargos sin recargo
- ✅ Badge amarillo en navbar
- ✅ Mensaje de mora aplicada

### Segunda mora (Días 15-19)
- ⚠️ 15% de recargo sobre servicios mensuales
- ✅ Otros cargos sin recargo
- ⚠️ Advertencia severa
- ✅ Badge naranja en navbar

### Tercera mora (Días 20-24)
- ⚠️ 20% de recargo sobre servicios mensuales
- ✅ Otros cargos sin recargo
- ⚠️ Barra amarilla de advertencia
- ✅ Badge naranja en navbar

### Mora máxima (Días 25-26)
- 🔴 30% de recargo sobre servicios mensuales
- ⚠️ Última oportunidad antes del bloqueo
- ✅ Badge rojo en navbar

### Sistema bloqueado (Día 27+)
- 🚫 30% de recargo sobre servicios mensuales
- 🚫 Modal NO se puede cerrar
- 🚫 Sistema completamente bloqueado
- 🔴 Barra roja en navbar
- ⛔ Cliente debe pagar para continuar

## 💡 Notas importantes

### Recargos selectivos
Los recargos se aplican **ÚNICAMENTE** sobre servicios mensuales POS (descripción contiene "Servicio POS").

Otros cargos como:
- Trabajo Mejoras
- Renovación Dominio
- Instalaciones

**NO llevan recargo** independientemente del día del mes.

### Datos de ejemplo

Los tests usan datos de ejemplo:
- Cliente: ALMACEN 1933 (Julia Salcedo)
- Servicios mensuales:
  - Servicio POS octubre 2025: $7,500
  - Servicio POS noviembre 2025: $7,500
- Total servicios mensuales: $15,000

Puedes modificar estos datos editando la función `obtenerDatosEjemplo()` en `simulador-base.php`.

### Añadir otros cargos

Para probar con otros cargos (sin recargo), edita `simulador-base.php` y agrega en el array `otros_cargos`:

```php
'otros_cargos' => [
    ['descripcion' => 'Trabajo Mejoras', 'importe' => 10000.00],
    ['descripcion' => 'Renovación Dominio', 'importe' => 2400.00]
]
```

## 🔧 Personalización

### Cambiar datos de prueba

Edita `simulador-base.php` en la función `obtenerDatosEjemplo()`:

```php
function obtenerDatosEjemplo() {
    return [
        'cliente' => [
            'nombre' => 'TU CLIENTE',
            'id' => 999
        ],
        'servicios_mensuales' => [
            ['descripcion' => 'Servicio POS mes X', 'importe' => 5000.00]
        ],
        'otros_cargos' => [
            ['descripcion' => 'Trabajo Extra', 'importe' => 15000.00]
        ]
    ];
}
```

### Probar día específico

Usa el simulador custom con parámetro GET:
```
test-dia-custom.php?dia=18
```

## ⚠️ Importante

Estos tests son **simulaciones** y **NO afectan** la base de datos real. Son únicamente para visualizar cómo se comporta el sistema en diferentes días del mes.

## 📞 Soporte

Si encuentras algún problema o necesitas ayuda:
1. Verifica que todos los archivos estén en la carpeta `/testing/`
2. Asegúrate de que tu servidor tenga PHP habilitado
3. Los archivos HTML pueden abrirse directamente desde el navegador
4. Los archivos PHP necesitan un servidor web (Apache, Nginx, etc.)

---

**Creado para:** Sistema de Cobro Moon POS
**Versión:** 1.0
**Fecha:** Diciembre 2025
