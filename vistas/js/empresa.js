

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
