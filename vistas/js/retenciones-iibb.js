/* Retenciones IIBB */
if ($('.tablasRetencionesIibb').length) {
  $('.tablasRetencionesIibb').DataTable({
    dom: 'Bfrtip',
    buttons: GL_DATATABLE_BOTONES,
    language: GL_DATATABLE_LENGUAJE,
    order: [[0, 'asc']]
  });
}

function urlRetencionesExport(tipo) {
  var ini = $('#fechaInicialRetenciones').val() || moment().startOf('month').format('YYYY-MM-DD');
  var fin = $('#fechaFinalRetenciones').val() || moment().format('YYYY-MM-DD');
  var prov = $('#filtroProveedorRetenciones').val() || '';
  var url = 'ajax/retenciones_iibb.ajax.php?accion=exportar_' + tipo +
    '&fechaInicial=' + encodeURIComponent(ini) +
    '&fechaFinal=' + encodeURIComponent(fin);
  if (prov) {
    url += '&idProveedor=' + encodeURIComponent(prov);
  }
  return url;
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
  var ini = $('#fechaInicialRetenciones').val() || moment().startOf('month').format('YYYY-MM-DD');
  var fin = $('#fechaFinalRetenciones').val() || moment().format('YYYY-MM-DD');
  var prov = $(this).val();
  var url = 'index.php?ruta=retenciones-iibb&fechaInicial=' + encodeURIComponent(ini) + '&fechaFinal=' + encodeURIComponent(fin);
  if (prov) {
    url += '&id_proveedor=' + encodeURIComponent(prov);
  }
  window.location = url;
});

if ($('#daterangeRetenciones-btn').length) {
  var iniRet = $('#fechaInicialRetenciones').val();
  var finRet = $('#fechaFinalRetenciones').val();
  $('#daterangeRetenciones-btn').daterangepicker({
    locale: { format: 'YYYY-MM-DD', applyLabel: 'Aplicar', cancelLabel: 'Cancelar' },
    startDate: iniRet || moment().startOf('month'),
    endDate: finRet || moment()
  }, function(start, end) {
    var url = 'index.php?ruta=retenciones-iibb&fechaInicial=' + start.format('YYYY-MM-DD') + '&fechaFinal=' + end.format('YYYY-MM-DD');
    var prov = $('#filtroProveedorRetenciones').val();
    if (prov) {
      url += '&id_proveedor=' + encodeURIComponent(prov);
    }
    window.location = url;
  });
}
