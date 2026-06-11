/* Retenciones IIBB */
$(function() {

  function fechasRetenciones() {
    var ini = $('#fechaInicialRetenciones').val() || moment().startOf('month').format('YYYY-MM-DD');
    var fin = $('#fechaFinalRetenciones').val() || moment().format('YYYY-MM-DD');
    return { ini: ini, fin: fin };
  }

  function urlFiltroRetenciones(ini, fin) {
    var url = 'retenciones-iibb?fechaInicial=' + encodeURIComponent(ini) +
      '&fechaFinal=' + encodeURIComponent(fin);
    var prov = $('#filtroProveedorRetenciones').val();
    if (prov) {
      url += '&id_proveedor=' + encodeURIComponent(prov);
    }
    return url;
  }

  function urlRetencionesExport(tipo) {
    var f = fechasRetenciones();
    var base = window.location.pathname.replace(/\/[^/]*$/, '/');
    if (base.slice(-1) !== '/') {
      base += '/';
    }
    var url = base + 'ajax/retenciones_iibb.ajax.php?accion=exportar_' + tipo +
      '&fechaInicial=' + encodeURIComponent(f.ini) +
      '&fechaFinal=' + encodeURIComponent(f.fin);
    var prov = $('#filtroProveedorRetenciones').val() || '';
    if (prov) {
      url += '&idProveedor=' + encodeURIComponent(prov);
    }
    return url;
  }

  function aplicarRangoRetenciones(start, end) {
    var ini = start.format('YYYY-MM-DD');
    var fin = end.format('YYYY-MM-DD');
    $('#fechaInicialRetenciones').val(ini);
    $('#fechaFinalRetenciones').val(fin);
    $('#daterangeRetenciones-btn span').html(
      '<i class="fa fa-calendar"></i> ' + ini + ' - ' + fin
    );
    window.location = urlFiltroRetenciones(ini, fin);
  }

  if ($('#daterangeRetenciones-btn').length) {
    var f = fechasRetenciones();
    $('#daterangeRetenciones-btn').daterangepicker({
      ranges: {
        'Hoy': [moment(), moment()],
        'Ayer': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
        'Últimos 7 días': [moment().subtract(6, 'days'), moment()],
        'Últimos 30 días': [moment().subtract(29, 'days'), moment()],
        'Este mes': [moment().startOf('month'), moment().endOf('month')],
        'Último mes': [
          moment().subtract(1, 'month').startOf('month'),
          moment().subtract(1, 'month').endOf('month')
        ]
      },
      locale: {
        format: 'YYYY-MM-DD',
        applyLabel: 'Aplicar',
        cancelLabel: 'Cancelar',
        customRangeLabel: 'Personalizado',
        daysOfWeek: ['Do', 'Lu', 'Ma', 'Mi', 'Ju', 'Vi', 'Sa'],
        monthNames: [
          'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
          'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
        ],
        firstDay: 1
      },
      startDate: moment(f.ini, 'YYYY-MM-DD'),
      endDate: moment(f.fin, 'YYYY-MM-DD')
    }, aplicarRangoRetenciones);
  }

  if ($('.tablasRetencionesIibb').length) {
    $('.tablasRetencionesIibb').DataTable({
      dom: 'Bfrtip',
      buttons: GL_DATATABLE_BOTONES,
      language: $.extend({}, GL_DATATABLE_LENGUAJE, {
        sEmptyTable: 'No hay retenciones en el período seleccionado.'
      }),
      order: [[0, 'asc']]
    });
  }

  $('#btnExportarTxtRetenciones').on('click', function(e) {
    e.preventDefault();
    window.location = urlRetencionesExport('txt');
  });

  $('#btnExportarZipRetenciones').on('click', function(e) {
    e.preventDefault();
    window.location = urlRetencionesExport('zip');
  });

  $('#filtroProveedorRetenciones').on('change', function() {
    var f = fechasRetenciones();
    window.location = urlFiltroRetenciones(f.ini, f.fin);
  });

});
