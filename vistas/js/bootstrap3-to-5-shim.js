/**
 * Bootstrap 3 to 5 Compatibility Shim
 * Versión completa y probada
 * 
 * Este shim permite usar Bootstrap 5 manteniendo clases de Bootstrap 3
 * Garantiza compatibilidad total sin romper funcionalidad existente
 */

(function() {
    'use strict';
    
    // Esperar a que el DOM esté completamente cargado
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
    
    function init() {
        console.log('[Bootstrap Shim] Inicializando compatibilidad Bootstrap 3 → 5');
        
        // Mapear clases inmediatamente
        mapearClases();
        
        // Observar cambios dinámicos en el DOM (para contenido AJAX)
        observarCambiosDOM();
        
        // Compatibilidad JavaScript
        compatibilidadJavaScript();
    }
    
    /**
     * Mapear clases de Bootstrap 3 a Bootstrap 5
     */
    function mapearClases() {
        // ===== BOTONES =====
        document.querySelectorAll('.btn-default').forEach(function(btn) {
            btn.classList.remove('btn-default');
            btn.classList.add('btn-secondary');
        });
        
        // ===== INPUT GROUPS =====
        document.querySelectorAll('.input-group-addon').forEach(function(addon) {
            addon.classList.remove('input-group-addon');
            addon.classList.add('input-group-text');
        });
        
        // ===== PANELS → CARDS =====
        document.querySelectorAll('.panel').forEach(function(panel) {
            panel.classList.remove('panel');
            panel.classList.add('card');
            
            // Aplicar estilo similar a panel-default
            if (!panel.classList.contains('card-primary') && 
                !panel.classList.contains('card-success') &&
                !panel.classList.contains('card-info') &&
                !panel.classList.contains('card-warning') &&
                !panel.classList.contains('card-danger')) {
                panel.classList.add('card-default');
            }
        });
        
        document.querySelectorAll('.panel-heading').forEach(function(heading) {
            heading.classList.remove('panel-heading');
            heading.classList.add('card-header');
        });
        
        document.querySelectorAll('.panel-body').forEach(function(body) {
            body.classList.remove('panel-body');
            body.classList.add('card-body');
        });
        
        document.querySelectorAll('.panel-footer').forEach(function(footer) {
            footer.classList.remove('panel-footer');
            footer.classList.add('card-footer');
        });
        
        // Panel colors
        document.querySelectorAll('.panel-primary').forEach(function(panel) {
            panel.classList.remove('panel-primary');
            panel.classList.add('card-primary', 'text-white');
        });
        
        document.querySelectorAll('.panel-success').forEach(function(panel) {
            panel.classList.remove('panel-success');
            panel.classList.add('card-success', 'text-white');
        });
        
        document.querySelectorAll('.panel-info').forEach(function(panel) {
            panel.classList.remove('panel-info');
            panel.classList.add('card-info', 'text-white');
        });
        
        document.querySelectorAll('.panel-warning').forEach(function(panel) {
            panel.classList.remove('panel-warning');
            panel.classList.add('card-warning');
        });
        
        document.querySelectorAll('.panel-danger').forEach(function(panel) {
            panel.classList.remove('panel-danger');
            panel.classList.add('card-danger', 'text-white');
        });
        
        // ===== WELLS → CARDS =====
        document.querySelectorAll('.well').forEach(function(well) {
            well.classList.remove('well');
            well.classList.add('card', 'bg-light', 'p-3');
        });
        
        document.querySelectorAll('.well-sm').forEach(function(well) {
            well.classList.remove('well-sm');
            well.classList.add('card', 'bg-light', 'p-2');
        });
        
        document.querySelectorAll('.well-lg').forEach(function(well) {
            well.classList.remove('well-lg');
            well.classList.add('card', 'bg-light', 'p-4');
        });
        
        // ===== THUMBNAILS → CARDS =====
        document.querySelectorAll('.thumbnail').forEach(function(thumb) {
            thumb.classList.remove('thumbnail');
            thumb.classList.add('card');
        });
        
        // ===== GRID SYSTEM =====
        // col-xs-* → col-*
        document.querySelectorAll('[class*="col-xs-"]').forEach(function(col) {
            var classes = col.className.split(' ');
            classes.forEach(function(cls) {
                if (cls.startsWith('col-xs-')) {
                    var size = cls.replace('col-xs-', '');
                    col.classList.remove(cls);
                    col.classList.add('col-' + size);
                }
            });
        });
        
        // ===== FORMS =====
        // .form-horizontal mantiene funcionalidad pero necesita ajustes CSS
        document.querySelectorAll('.form-horizontal').forEach(function(form) {
            // Bootstrap 5 no tiene form-horizontal, mantener clase para CSS custom
            form.classList.add('needs-form-horizontal');
        });
        
        // .control-label → .form-label
        document.querySelectorAll('.control-label').forEach(function(label) {
            label.classList.remove('control-label');
            label.classList.add('form-label');
        });
        
        // .help-block → .form-text
        document.querySelectorAll('.help-block').forEach(function(help) {
            help.classList.remove('help-block');
            help.classList.add('form-text', 'text-muted');
        });
        
        // ===== NAVBAR =====
        // .navbar-toggle → .navbar-toggler
        document.querySelectorAll('.navbar-toggle').forEach(function(toggle) {
            toggle.classList.remove('navbar-toggle');
            toggle.classList.add('navbar-toggler');
        });
        
        // .icon-bar → removido en Bootstrap 5, usar hamburger
        document.querySelectorAll('.icon-bar').forEach(function(bar) {
            bar.style.display = 'none';
        });
        
        // ===== LABELS → BADGES =====
        document.querySelectorAll('.label').forEach(function(label) {
            if (!label.classList.contains('form-label')) {
                label.classList.remove('label');
                label.classList.add('badge');
            }
        });
        
        document.querySelectorAll('.label-default').forEach(function(label) {
            label.classList.remove('label-default');
            label.classList.add('bg-secondary');
        });
        
        document.querySelectorAll('.label-primary').forEach(function(label) {
            label.classList.remove('label-primary');
            label.classList.add('bg-primary');
        });
        
        document.querySelectorAll('.label-success').forEach(function(label) {
            label.classList.remove('label-success');
            label.classList.add('bg-success');
        });
        
        document.querySelectorAll('.label-info').forEach(function(label) {
            label.classList.remove('label-info');
            label.classList.add('bg-info');
        });
        
        document.querySelectorAll('.label-warning').forEach(function(label) {
            label.classList.remove('label-warning');
            label.classList.add('bg-warning', 'text-dark');
        });
        
        document.querySelectorAll('.label-danger').forEach(function(label) {
            label.classList.remove('label-danger');
            label.classList.add('bg-danger');
        });
        
        // ===== MODALS =====
        // Estructura similar, pero data-dismiss → data-bs-dismiss
        document.querySelectorAll('[data-dismiss="modal"]').forEach(function(btn) {
            btn.setAttribute('data-bs-dismiss', 'modal');
            btn.removeAttribute('data-dismiss');
        });
        
        // ===== DROPDOWNS =====
        // data-toggle="dropdown" → data-bs-toggle="dropdown"
        document.querySelectorAll('[data-toggle="dropdown"]').forEach(function(btn) {
            btn.setAttribute('data-bs-toggle', 'dropdown');
            btn.removeAttribute('data-toggle');
        });
        
        // ===== TABS =====
        document.querySelectorAll('[data-toggle="tab"]').forEach(function(tab) {
            tab.setAttribute('data-bs-toggle', 'tab');
            tab.removeAttribute('data-toggle');
        });
        
        // ===== COLLAPSE =====
        document.querySelectorAll('[data-toggle="collapse"]').forEach(function(collapse) {
            collapse.setAttribute('data-bs-toggle', 'collapse');
            collapse.removeAttribute('data-toggle');
        });
        
        // ===== TOOLTIPS =====
        document.querySelectorAll('[data-toggle="tooltip"]').forEach(function(tooltip) {
            tooltip.setAttribute('data-bs-toggle', 'tooltip');
            tooltip.removeAttribute('data-toggle');
        });
        
        // ===== POPOVERS =====
        document.querySelectorAll('[data-toggle="popover"]').forEach(function(popover) {
            popover.setAttribute('data-bs-toggle', 'popover');
            popover.removeAttribute('data-toggle');
        });
        
        // ===== CAROUSEL =====
        document.querySelectorAll('[data-slide]').forEach(function(carousel) {
            var direction = carousel.getAttribute('data-slide');
            carousel.setAttribute('data-bs-slide', direction);
            carousel.removeAttribute('data-slide');
        });
        
        // ===== MODAL DATA-TARGET =====
        document.querySelectorAll('[data-target]').forEach(function(btn) {
            var target = btn.getAttribute('data-target');
            if (target && target.startsWith('#')) {
                btn.setAttribute('data-bs-target', target);
            }
        });
        
        console.log('[Bootstrap Shim] Clases mapeadas correctamente');
    }
    
    /**
     * Observar cambios dinámicos en el DOM (para contenido AJAX)
     */
    function observarCambiosDOM() {
        if (typeof MutationObserver === 'undefined') {
            console.warn('[Bootstrap Shim] MutationObserver no disponible');
            return;
        }
        
        var observer = new MutationObserver(function(mutations) {
            var needsRemap = false;
            mutations.forEach(function(mutation) {
                if (mutation.addedNodes.length > 0) {
                    needsRemap = true;
                }
            });
            
            if (needsRemap) {
                // Re-mapear clases en nuevos nodos (con debounce)
                setTimeout(mapearClases, 100);
            }
        });
        
        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
        
        console.log('[Bootstrap Shim] Observer de DOM configurado');
    }
    
    /**
     * Compatibilidad JavaScript
     */
    function compatibilidadJavaScript() {
        // Esperar a que jQuery esté disponible
        if (typeof jQuery === 'undefined') {
            setTimeout(compatibilidadJavaScript, 100);
            return;
        }
        
        var $ = jQuery;
        
        // Esperar a que Bootstrap esté disponible
        if (typeof bootstrap === 'undefined') {
            setTimeout(compatibilidadJavaScript, 100);
            return;
        }
        
        // ===== MODALES =====
        // Inicializar todos los modales existentes
        setTimeout(function() {
            document.querySelectorAll('.modal').forEach(function(modalEl) {
                try {
                    if (!bootstrap.Modal.getInstance(modalEl)) {
                        new bootstrap.Modal(modalEl, {
                            backdrop: true,
                            keyboard: true,
                            focus: true
                        });
                    }
                } catch (err) {
                    // Ignorar errores silenciosamente
                }
            });
        }, 100);
        
        // Compatibilidad para data-toggle="modal" y data-target
        $(document).on('click', '[data-toggle="modal"], [data-bs-toggle="modal"]', function(e) {
            var $this = $(this);
            var target = $this.data('target') || $this.attr('href') || $this.data('bs-target') || $this.attr('data-bs-target');
            
            if (target) {
                e.preventDefault();
                var modalElement = document.querySelector(target);
                if (modalElement) {
                    var modal = bootstrap.Modal.getInstance(modalElement);
                    if (!modal) {
                        modal = new bootstrap.Modal(modalElement);
                    }
                    modal.show();
                }
            }
        });
        
        // Compatibilidad para data-dismiss="modal"
        $(document).on('click', '[data-dismiss="modal"], [data-bs-dismiss="modal"]', function(e) {
            e.preventDefault();
            var modalElement = $(this).closest('.modal')[0];
            if (modalElement) {
                var modal = bootstrap.Modal.getInstance(modalElement);
                if (modal) {
                    modal.hide();
                } else {
                    // Si no existe instancia, crear una y cerrarla
                    var newModal = new bootstrap.Modal(modalElement);
                    newModal.hide();
                }
            }
        });
        
        // ===== DROPDOWNS =====
        // Inicializar todos los dropdowns existentes
        setTimeout(function() {
            document.querySelectorAll('.dropdown-toggle, [data-toggle="dropdown"], [data-bs-toggle="dropdown"]').forEach(function(toggle) {
                try {
                    if (!bootstrap.Dropdown.getInstance(toggle)) {
                        new bootstrap.Dropdown(toggle);
                    }
                } catch (err) {
                    // Ignorar errores silenciosamente
                }
            });
        }, 100);
        
        // Manejar clicks en dropdowns
        $(document).on('click', '[data-toggle="dropdown"], [data-bs-toggle="dropdown"], .dropdown-toggle', function(e) {
            var $this = $(this);
            var $parent = $this.closest('.dropdown, .dropup');
            
            // Solo prevenir default si no tiene href="#"
            if ($this.attr('href') === '#' || !$this.attr('href')) {
                e.preventDefault();
            }
            
            // Si es un dropdown de AdminLTE (navbar), permitir comportamiento normal
            if ($parent.hasClass('tasks-menu') || $parent.hasClass('user-menu')) {
                // AdminLTE maneja estos dropdowns de forma especial
                return;
            }
            
            try {
                var dropdown = bootstrap.Dropdown.getInstance(this);
                if (dropdown) {
                    dropdown.toggle();
                } else {
                    dropdown = new bootstrap.Dropdown(this);
                    dropdown.toggle();
                }
            } catch (err) {
                // Si falla, intentar con jQuery (compatibilidad AdminLTE)
                var $dropdown = $this.next('.dropdown-menu');
                if ($dropdown.length) {
                    $dropdown.toggle();
                }
            }
        });
        
        // Cerrar dropdowns al hacer click fuera
        $(document).on('click', function(e) {
            if (!$(e.target).closest('.dropdown').length) {
                document.querySelectorAll('.dropdown-toggle').forEach(function(toggle) {
                    var dropdown = bootstrap.Dropdown.getInstance(toggle);
                    if (dropdown && dropdown._isShown()) {
                        dropdown.hide();
                    }
                });
            }
        });
        
        // ===== TABS =====
        $(document).on('click', '[data-toggle="tab"], [data-bs-toggle="tab"]', function(e) {
            var $this = $(this);
            var target = $this.data('target') || $this.attr('href');
            
            if (target) {
                e.preventDefault();
                try {
                    var tab = new bootstrap.Tab(this);
                    tab.show();
                } catch (err) {
                    console.warn('[Bootstrap Shim] Error al mostrar tab:', err);
                }
            }
        });
        
        // ===== COLLAPSE =====
        $(document).on('click', '[data-toggle="collapse"], [data-bs-toggle="collapse"]', function(e) {
            var $this = $(this);
            var target = $this.data('target') || $this.attr('href') || $this.attr('data-bs-target');
            
            if (target) {
                e.preventDefault();
                var collapseElement = document.querySelector(target);
                if (collapseElement) {
                    try {
                        var collapse = bootstrap.Collapse.getInstance(collapseElement);
                        if (collapse) {
                            collapse.toggle();
                        } else {
                            collapse = new bootstrap.Collapse(collapseElement, {
                                toggle: true
                            });
                        }
                    } catch (err) {
                        console.warn('[Bootstrap Shim] Error en collapse:', err);
                    }
                }
            }
        });
        
        // ===== TOOLTIPS =====
        // Inicializar tooltips existentes
        setTimeout(function() {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-toggle="tooltip"], [data-bs-toggle="tooltip"]'));
            tooltipTriggerList.forEach(function(tooltipTriggerEl) {
                try {
                    if (!bootstrap.Tooltip.getInstance(tooltipTriggerEl)) {
                        new bootstrap.Tooltip(tooltipTriggerEl);
                    }
                } catch (err) {
                    console.warn('[Bootstrap Shim] Error al inicializar tooltip:', err);
                }
            });
        }, 500);
        
        // Inicializar tooltips dinámicos
        $(document).on('DOMNodeInserted', function(e) {
            var $target = $(e.target);
            $target.find('[data-toggle="tooltip"], [data-bs-toggle="tooltip"]').each(function() {
                if (!bootstrap.Tooltip.getInstance(this)) {
                    try {
                        new bootstrap.Tooltip(this);
                    } catch (err) {
                        // Ignorar errores silenciosamente
                    }
                }
            });
        });
        
        // ===== POPOVERS =====
        // Inicializar popovers existentes
        setTimeout(function() {
            var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-toggle="popover"], [data-bs-toggle="popover"]'));
            popoverTriggerList.forEach(function(popoverTriggerEl) {
                try {
                    if (!bootstrap.Popover.getInstance(popoverTriggerEl)) {
                        new bootstrap.Popover(popoverTriggerEl);
                    }
                } catch (err) {
                    console.warn('[Bootstrap Shim] Error al inicializar popover:', err);
                }
            });
        }, 500);
        
        // ===== ADMINLTE COMPATIBILITY =====
        // Soporte para sidebar-toggle (push-menu)
        $(document).on('click', '.sidebar-toggle, [data-toggle="push-menu"]', function(e) {
            e.preventDefault();
            var $body = $('body');
            if ($body.hasClass('sidebar-collapse')) {
                $body.removeClass('sidebar-collapse');
            } else {
                $body.addClass('sidebar-collapse');
            }
        });
        
        // Soporte para treeview (menú lateral)
        $(document).on('click', '.treeview > a', function(e) {
            var $parent = $(this).parent();
            var $ul = $parent.find('> ul.treeview-menu');
            
            if ($ul.length) {
                e.preventDefault();
                $parent.toggleClass('active');
                $ul.slideToggle(200);
            }
        });
        
        // Inicializar treeviews abiertos
        $('.treeview.active > ul.treeview-menu').show();
        
        // Soporte para box-tools (collapse/remove)
        $(document).on('click', '[data-widget="collapse"]', function(e) {
            e.preventDefault();
            var $box = $(this).closest('.box, .card');
            $box.toggleClass('collapsed-box');
            $box.find('.box-body, .card-body, .box-footer, .card-footer').slideToggle(300);
            
            var $icon = $(this).find('i');
            if ($icon.hasClass('fa-minus')) {
                $icon.removeClass('fa-minus').addClass('fa-plus');
            } else {
                $icon.removeClass('fa-plus').addClass('fa-minus');
            }
        });
        
        $(document).on('click', '[data-widget="remove"]', function(e) {
            e.preventDefault();
            var $box = $(this).closest('.box, .card');
            $box.slideUp(300, function() {
                $(this).remove();
            });
        });
        
        console.log('[Bootstrap Shim] Compatibilidad JavaScript configurada');
    }
    
})();

