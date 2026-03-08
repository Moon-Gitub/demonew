/*=============================================
PUNTOS DE VENTA Y ALMACENES - Configuración visual
=============================================*/
function parsearPtosVta(val) {
  if (!val || val.trim() === '') return [];
  try {
    var arr = JSON.parse(val);
    if (Array.isArray(arr) && arr.length > 0 && arr[0].pto != null) return arr;
    if (Array.isArray(arr)) return arr;
  } catch (e) {}
  var nums = (val + '').split(/[,\s]+/).filter(Boolean).map(function(n) { return parseInt(n, 10); }).filter(function(n) { return !isNaN(n); });
  return nums.map(function(n, i) { return { pto: n, det: 'Punto ' + (i + 1) }; });
}
function parsearAlmacenes(val) {
  if (!val || val.trim() === '') return [];
  try {
    var arr = JSON.parse(val);
    if (Array.isArray(arr)) return arr;
  } catch (e) {}
  return [];
}
function actualizarPtosVtaHidden() {
  var items = [];
  $('#listaPtosVta .fila-pto').each(function() {
    var pto = $(this).find('.inp-pto').val().trim();
    var det = $(this).find('.inp-det-pto').val().trim();
    if (pto !== '') items.push({ pto: parseInt(pto, 10) || pto, det: det || 'Punto ' + pto });
  });
  $('#empPtosVta').val(JSON.stringify(items));
}
function actualizarAlmacenesHidden() {
  var items = [];
  $('#listaAlmacenes .fila-almacen').each(function() {
    var stk = $(this).find('.inp-stkProd').val().trim();
    var det = $(this).find('.inp-det-almacen').val().trim();
    if (stk !== '') items.push({ stkProd: stk, det: det || stk });
  });
  $('#empStock').val(JSON.stringify(items));
}
function renderPtosVta(items) {
  var html = '';
  (items.length ? items : [{ pto: 1, det: 'Local' }]).forEach(function(it) {
    html += '<div class="fila-pto form-inline" style="margin-bottom:8px;">' +
      '<input type="number" class="form-control input-sm inp-pto" placeholder="Nº" value="' + (it.pto || '') + '" style="width:70px;" min="1"> ' +
      '<input type="text" class="form-control input-sm inp-det-pto" placeholder="Denominación (ej. Local)" value="' + (it.det || '').replace(/"/g, '&quot;') + '" style="width:180px; margin-left:4px;"> ' +
      '<button type="button" class="btn btn-xs btn-danger btn-quitar-pto" title="Quitar"><i class="fa fa-times"></i></button>' +
      '</div>';
  });
  $('#listaPtosVta').html(html);
  actualizarPtosVtaHidden();
}
function renderAlmacenes(items) {
  var html = '';
  (items.length ? items : [{ stkProd: 'stock', det: 'Depósito' }]).forEach(function(it) {
    html += '<div class="fila-almacen form-inline" style="margin-bottom:8px;">' +
      '<input type="text" class="form-control input-sm inp-stkProd" placeholder="Columna (stock, stock1...)" value="' + (it.stkProd || '').replace(/"/g, '&quot;') + '" style="width:120px;"> ' +
      '<input type="text" class="form-control input-sm inp-det-almacen" placeholder="Denominación" value="' + (it.det || '').replace(/"/g, '&quot;') + '" style="width:180px; margin-left:4px;"> ' +
      '<button type="button" class="btn btn-xs btn-danger btn-quitar-almacen" title="Quitar"><i class="fa fa-times"></i></button>' +
      '</div>';
  });
  $('#listaAlmacenes').html(html);
  actualizarAlmacenesHidden();
}
$(document).ready(function() {
  var ptosVal = $('#empPtosVta').val();
  var almacenesVal = $('#empStock').val();
  renderPtosVta(parsearPtosVta(ptosVal));
  renderAlmacenes(parsearAlmacenes(almacenesVal));
});
$('#btnAgregarPtoVta').on('click', function() {
  $('#listaPtosVta').append(
    '<div class="fila-pto form-inline" style="margin-bottom:8px;">' +
    '<input type="number" class="form-control input-sm inp-pto" placeholder="Nº" value="" style="width:70px;" min="1"> ' +
    '<input type="text" class="form-control input-sm inp-det-pto" placeholder="Denominación" value="" style="width:180px; margin-left:4px;"> ' +
    '<button type="button" class="btn btn-xs btn-danger btn-quitar-pto" title="Quitar"><i class="fa fa-times"></i></button>' +
    '</div>'
  );
  actualizarPtosVtaHidden();
});
$('#btnAgregarAlmacen').on('click', function() {
  $('#listaAlmacenes').append(
    '<div class="fila-almacen form-inline" style="margin-bottom:8px;">' +
    '<input type="text" class="form-control input-sm inp-stkProd" placeholder="Columna (stock, stock1...)" value="" style="width:120px;"> ' +
    '<input type="text" class="form-control input-sm inp-det-almacen" placeholder="Denominación" value="" style="width:180px; margin-left:4px;"> ' +
    '<button type="button" class="btn btn-xs btn-danger btn-quitar-almacen" title="Quitar"><i class="fa fa-times"></i></button>' +
    '</div>'
  );
  actualizarAlmacenesHidden();
});
$(document).on('click', '.btn-quitar-pto', function() {
  var $lista = $('#listaPtosVta');
  if ($lista.find('.fila-pto').length > 1) $(this).closest('.fila-pto').remove();
  actualizarPtosVtaHidden();
});
$(document).on('click', '.btn-quitar-almacen', function() {
  var $lista = $('#listaAlmacenes');
  if ($lista.find('.fila-almacen').length > 1) $(this).closest('.fila-almacen').remove();
  actualizarAlmacenesHidden();
});
$(document).on('input change', '#listaPtosVta .inp-pto, #listaPtosVta .inp-det-pto', actualizarPtosVtaHidden);
$(document).on('input change', '#listaAlmacenes .inp-stkProd, #listaAlmacenes .inp-det-almacen', actualizarAlmacenesHidden);
$('form').on('submit', function() {
  actualizarPtosVtaHidden();
  actualizarAlmacenesHidden();
});

/*=============================================
LISTAR TODOS LOS COMPROBANTES SELECCIONADOS
=============================================*/

$(".chkTiposCbtes").change(function(){

	var listaComprobantes = [];

	$("#empTipoCbtes").val('');

	//var numItems = $('.chkTiposCbtes').length;

	$('.chkTiposCbtes').each(function(){
		if($(this).is(':checked')){
			listaComprobantes.push({ "codigo" : $(this).val(), 
				"descripcion" : $(this).attr('cbteDesc')
			})					
		}
	})

	$("#empTipoCbtes").val(JSON.stringify(listaComprobantes));
})

/*=============================================
SUBIENDO EL CERTIFICADO
=============================================*/
// $(".nuevoCertificado").change(function(){

//  	var certificado = this.files[0];

//  	console.log(certificado);
// 	/*=============================================
//   	VALIDAMOS EL FORMATO DE LA IMAGEN SEA JPG O PNG
//   	=============================================*/
// 	if(certificado["size"] > 2000000){

//   		$(".nuevoCert").val("");

//   		 swal({
// 		      title: "Error al subir el certificado",
// 		      text: "¡El archivo no debe pesar más de 2MB!",
// 		      type: "error",
// 		      confirmButtonText: "¡Cerrar!"
// 		    });

//   	}else{

 //   		var datosCerti = new FileReader;
 //   		datosCerti.readAsDataURL(certificado);

 //   		$(datosCerti).on("load", function(event){

 //   			var rutaCerti = event.target.result;

 //   			$(".previsualizarCert").attr("src", rutaCerti);


 //   			console.log(rutaCerti);
 //   		})

 // })

/*=============================================
EDITAR EMPRESA
=============================================*/

// $(".tablaEmpresa tbody").on("click", "button.btnEditarProducto", function(){

// 	var idProducto = $(this).attr("idProducto");

// 	var datos = new FormData();
//     datos.append("idProducto", idProducto);

//      $.ajax({

//       url:"ajax/productos.ajax.php",
//       method: "POST",
//       data: datos,
//       cache: false,
//       contentType: false,
//       processData: false,
//       dataType:"json",
//       success:function(respuesta){

//           var datosCategoria = new FormData();
//           datosCategoria.append("idCategoria",respuesta["id_categoria"]);

//            $.ajax({

//               url:"ajax/categorias.ajax.php",
//               method: "POST",
//               data: datosCategoria,
//               cache: false,
//               contentType: false,
//               processData: false,
//               dataType:"json",
//               success:function(respuesta){

//                   $("#editarCategoria").val(respuesta["id"]);
//                   $("#editarCategoria").html(respuesta["categoria"]);

//               }

//           })

//            $("#editarCodigo").val(respuesta["codigo"]);

//            $("#editarDescripcion").val(respuesta["descripcion"]);

//            $("#editarStock").val(respuesta["stock"]);

//            $("#editarPrecioCompra").val(respuesta["precio_compra"]);

//            $("#editarPrecioVenta").val(respuesta["precio_venta"]);

//            if(respuesta["imagen"] != ""){

//            	$("#imagenActual").val(respuesta["imagen"]);

//            	$(".previsualizar").attr("src",  respuesta["imagen"]);

//            }

//       }

//   })

// })
