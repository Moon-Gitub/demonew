const GL_DATATABLE_LENGUAJE = {

		"sProcessing":     "Procesando...",
		"sLengthMenu":     "Mostrar _MENU_ registros",
		"sZeroRecords":    "No se encontraron resultados",
		"sEmptyTable":     "Ningún dato disponible en esta tabla",
		"sInfo":           "Mostrando registros del _START_ al _END_ de un total de _TOTAL_",
		"sInfoEmpty":      "Mostrando registros del 0 al 0 de un total de 0",
		"sInfoFiltered":   "(filtrado de un total de _MAX_ registros)",
		"sInfoPostFix":    "",
		"sSearch":         "Buscar:",
		"sUrl":            "",
		"sInfoThousands":  ",",
		"sLoadingRecords": "Cargando...",
		"oPaginate": {
		"sFirst":    "Primero",
		"sLast":     "Último",
		"sNext":     "Siguiente",
		"sPrevious": "Anterior"
		},
		"oAria": {
			"sSortAscending":  ": Activar para ordenar la columna de manera ascendente",
			"sSortDescending": ": Activar para ordenar la columna de manera descendente"
		}

	}

const GL_DATATABLE_BOTONES = [ 
    {
      extend:    'excelHtml5',
      text:      '<i class="fa fa-file-excel-o"></i>',
      titleAttr: 'Exportar a Excel',
      className: 'btn btn-success'
    },
    {
      extend:    'pdfHtml5',
      text:      '<i class="fa fa-file-pdf-o"></i> ',
      titleAttr: 'Exportar a PDF',
      className: 'btn btn-danger'
    },
    {
      extend:    'print',
      text:      '<i class="fa fa-print"></i> ',
      titleAttr: 'Imprimir',
      className: 'btn btn-info'
    },
    {
      extend:    'pageLength',
      text:      '<i class="fa fa-list-alt"></i>',
      titleAttr: 'Mostrar registros',
      className: 'btn btn-primary'
    },
  ]

//FUNCIONES DEL INICIO
$(document).ready(function() {

    //plantilla
    $("#loader").fadeOut("slow");

    //crear-venta-caja
    $("#ventaCajaDetalle").focus(); 

    //compras
    listarProductosComprasValidar();

    //productos
    var msjProductos = localStorage.getItem('msjProductoCorrecto');
    if(msjProductos){
      swal({
        title: "Productos",
        text: "Acción realizada correctamente!",
        toast: true,
        timer: 2000,
        position: "top",
        type: "success",
        confirmButtonText: "¡Cerrar!"
      });

      localStorage.removeItem('msjProductoCorrecto');
    } 

    //proveedor
    var msjProveedor = localStorage.getItem('msjProveedorCorrecto');
    if(msjProveedor){
      swal({
        title: "Proveedores",
        text: "Acción realizada correctamente!",
        toast: true,
        timer: 2000,
        position: "top",
        type: "success",
        confirmButtonText: "¡Cerrar!"
      });

      localStorage.removeItem('msjProveedorCorrecto');
    }

    //ventas
    if($("#estoyEditando").val() == 0) {
      $("#nuevoMetodoPago").val("Efectivo");
    } 

    cambiarMetodoPago($("#nuevoMetodoPago")); //Esta funcion la necesito para cuando se carga el editar-venta cargue el select de metodos de pago

    var BASE_URL = window.location.href;
	//console.log(BASE_URL)
    if (BASE_URL.includes('crear-venta')) {
        $("#tokenIdTablaVentas").val(create_UUID());
    }
    
});

/*=============================================
SideBar Menu
=============================================*/
$('.sidebar-menu').tree()

/*=============================================
Data Table
=============================================*/
$(".tablas").DataTable({
	"language": GL_DATATABLE_LENGUAJE
});

/*=============================================
 //iCheck for checkbox and radio inputs
=============================================*/
$('input[type="checkbox"].minimal, input[type="radio"].minimal').iCheck({
  checkboxClass: 'icheckbox_minimal-blue',
  radioClass   : 'iradio_minimal-blue'
})

/*=============================================
 //input Mask
=============================================*/

//Datemask dd/mm/yyyy
$('#datemask').inputmask('dd/mm/yyyy', { 'placeholder': 'dd/mm/yyyy' })
//Datemask2 mm/dd/yyyy
$('#datemask2').inputmask('mm/dd/yyyy', { 'placeholder': 'mm/dd/yyyy' })
//Money Euro
$('[data-mask]').inputmask()

/*=============================================
CORRECCIÓN BOTONERAS OCULTAS BACKEND	
=============================================*/

if(window.matchMedia("(max-width:767px)").matches){
	
	$("body").removeClass('sidebar-collapse');

}else{

	$("body").addClass('sidebar-collapse');
}


const formatterPeso = new Intl.NumberFormat('es-AR', {
       style: 'currency',
       currency: 'ARS',
       minimumFractionDigits: 2
     });
     
/*=============================================
FUNCION PARA CERRAR SESION AUTOMATICAMENTE CUANDO SE ALCANZA EL TIEMPO MAXIMO DE SESION
=============================================*/

function Countdown(options) {
  var timer,
  instance = this,
  seconds = options.seconds || 10,
  updateStatus = options.onUpdateStatus || function () {},
  counterEnd = options.onCounterEnd || function () {};

  function decrementCounter() {
    updateStatus(seconds);
    if (seconds === 0) {
      counterEnd();
      instance.stop();
    }
    seconds--;
  }

  this.start = function () {
    clearInterval(timer);
    timer = 0;
    seconds = options.seconds;
    timer = setInterval(decrementCounter, 1000);
  };

  this.stop = function () {
    clearInterval(timer);
  };
}

var myCounter = new Countdown({  
seconds: $("#tiempoMaximoSesion").val(),  // number of seconds to count down
onUpdateStatus: function(sec){
	if(sec<=59){
      $("#alertaTiempoSesionRestanteLi").css('display', '');
      $("#alertaTiempoSesionRestante").text(sec);
    }
}, // callback for each second
onCounterEnd: function(){ // final action
	swal({
		  type: "info",
		  title: "Seguridad",
		  text: "Se ha alcanzado el tiempo máximo de inactividad, por seguridad se cerrará la sesión",
		  showConfirmButton: true,
		  confirmButtonText: "Cerrar", 
		  allowOutsideClick: false
		  }).then(function(result){
			if (result.value) {

				window.location = "salir";

			}
		})
} 
});


myCounter.start();

/*=============================================
Data Table ORDEN ASCENDENTE
=============================================*/
$(".tablasBotones").DataTable({

	"dom": 'Bfrtip',
    "buttons": GL_DATATABLE_BOTONES,
	"language": GL_DATATABLE_LENGUAJE

});


jQuery.ajaxSetup({
  beforeSend: function() {
     $('#loader').show();
  },
  complete: function(){
     $('#loader').hide();
  },
  success: function() {
    $('#loader').hide();
  }, 
  error: function(){
     $('#loader').hide();
  }
});

/*=============================================
FUNCION PARA CONSULTAR PRECIOS EN MODULO SIN SESION
=============================================*/
$("#precioProductoSinSesion").keyup( function (e) {

    if (e.keyCode == 13 ) {

      fnPrecioProductoSinSesion();
  } 

});

function fnPrecioProductoSinSesion() {

  var codProducto = $("#precioProductoSinSesion").val();
  
  var datos = new FormData();
  datos.append("idProductoLector", codProducto);
  
    $.ajax({

      url:"ajax/productos.ajax.php",
        method: "POST",
        data: datos,
        cache: false,
        contentType: false,
        processData: false,
        dataType:"json",
        success:function(respuesta) {

          if(respuesta ){
            console.log(respuesta)

            var precio = respuesta["precio_venta"]; 

            document.getElementById("descripcionConsultaPrecioSinSesion").innerHTML = respuesta["descripcion"];
            document.getElementById("consultaPrecioProductoSinSesion").innerHTML="$ "+precio;
            $("#consultaPrecioProductoSinSesionImagen").attr('src', respuesta["imagen"]);

            setTimeout(function(){ocultarPrecioSinSesion();},5000);
          
          } else{
            
            swal({
              title: "Productos",
              text: "No se encontró el código de producto ingresado",
              type: "error",
              toast: true,
              position: 'top',
              showConfirmButton: false,
              timer: 5000
            });

            $("#precioProductoSinSesion").val('');
        }
      
    }

  })

}

function ocultarPrecioSinSesion() {
    
    $("#precioProductoSinSesion").val('');
    $("#descripcionConsultaPrecioSinSesion").text('');
    $("#consultaPrecioProductoSinSesion").text('');   
    $("#precioProductoSinSesion").focus();
    $("#consultaPrecioProductoSinSesionImagen").attr('src', '');
}

//GENERA IDS UNICOS (ACTUALMENTE EN VENTAS, REPLICABLE A VARIAS/TODAS TABLAS)
function create_UUID(){
    var dt = new Date().getTime();
    var uuid = 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxx'.replace(/[xy]/g, function(c) {
        var r = (dt + Math.random()*16)%16 | 0;
        dt = Math.floor(dt/16);
        return (c=='x' ? r :(r&0x3|0x8)).toString(16);
    });
    return uuid;
}

//CONSULTA AJAX->AFIP PARA CONSULTAR POR UN CUIT
const consultarDatosAfip = (cuit) => {

  return new Promise(resolve => {
    var datos = new FormData();
    datos.append("idPersona", cuit);
    $.ajax({
      url:"ajax/clientes.ajax.php",
      method: "POST",
      data: datos,
      cache: false,
      contentType: false,
      processData: false,
      dataType:"json",
      success:function(respuesta){
        resolve(respuesta);
      },
      error: function(xhr, status, error) {
        console.log( xhr.responseText);
        console.log( xhr);
        console.log( error);
        console.log(status);
        swal({
          title: "Error",
          text: "Error al conectar al servicio de AFIP",
          toast: true,
          timer: 3000,
          position: 'top',
          type: "error",
          confirmButtonText: "¡Cerrar!"
        });
        return false;
      }
    })
  })
}

//OBTENGO LO INTRODUCIDO POR USUARIO PARA DETERMINAR SI ES DNI O CUIT
const cuitsAfip = async (numeroParam) => {
  var encontrado = false;
  var arrDatos = null;
  var idPersona = numeroParam;
  if(idPersona.length === 8 || idPersona.length === 7 ){ //es documento
      var cuits = obtenerCUITs(idPersona);
      //recorro todos los cuit posibles que se pueden formar con el dni ingresado y consulto en afip
      for (let index = 0; index < cuits.length; index++) {
        const respAfip = await consultarDatosAfip(cuits[index]);
        if (respAfip.hasOwnProperty('personaReturn')) {
          arrDatos = respAfip;
          encontrado = true;
          break; //ya encontré datos, salgo del for
        }
      }

  } else if (idPersona.length === 11) { //es cuit

    const respAfip = await consultarDatosAfip(idPersona);
    if (respAfip.hasOwnProperty('personaReturn')) {
      arrDatos = respAfip;
      encontrado = true;
    }
     
  } else { //Num invalido
    swal({
      title: "Error",
      text: 'El número introducido es inválido! Debe introducir DNI o CUIT/CUIL (solo números)',
      toast: true,
      timer: 3000,
      position: 'top',
      type: "error",
      confirmButtonText: "¡Cerrar!"
    });
    return false;
  }

  if(encontrado){
    return arrDatos;
  } else {
    swal({
      title: "Error",
      text: 'El número ingresado no retornó ningún resultado',
      toast: true,
      timer: 3000,
      position: 'top',
      type: "error",
      confirmButtonText: "¡Cerrar!"
    });
    return false;
  }
}

//FUNCION PARA GENERAR LAS DIFERENTES COMBINACIONES DE CUIT
function obtenerCUITs(dni) {
  if(dni.length > 8) return false;
  if(dni.length < 7) return false;
  if(dni.length == 7) dni = '0' + dni;
  var tipos = ['20','27','23','24','25','26'];
  var cuits = [];
  tipos.forEach((element) => { 
    var resultado = 0;
    var cuit_nro = dni.replace(".", "");
    cuit_nro = element + cuit_nro;
    var codes = "6789456789";
    var x = 0;
    while (x < 10) {
        var digitoValidador = parseInt(codes.substring(x, x+1));
        if (isNaN(digitoValidador)) digitoValidador = 0;
        var digito = parseInt(cuit_nro.substring(x, x+1));
        if (isNaN(digito)) digito = 0;
        var digitoValidacion = digitoValidador * digito;
        resultado += digitoValidacion;
        x++;
    }
    resultado = resultado % 11;
    resultado = resultado.toString();
    cuit_nro = cuit_nro + resultado;
    cuits.push(cuit_nro)
  });
  return cuits;
}