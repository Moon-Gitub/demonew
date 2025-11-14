<body style="background: -webkit-gradient(linear, 0% 0%, 0% 100%, from(rgb(82 101 141)), to(rgb(255 255 255)));height: auto;min-height: 100%;">
    <br>
    <span style="display: block; text-align: center;" >
      <img src="vistas/img/plantilla/back3.png" >
    </span>

    <style>
    img {
        max-width: 100%;
        height: auto;
    }
    h1 {
      font-size: 250px;
    }
    h2 {
      font-size: 60px;
    }
    </style>

    <section class="content-header">
        <div class="form-group">
            <center>
                <div class="input-group">
                    <input type="hidden" autofocus class="form-control input-lg" id="precioProductoSinSesion" value="<?php echo $_GET["idProducto"]?>" style="text-align:center;">
                    <center><h3 id="descripcionConsultaPrecioSinSesion"></h3></center>
                    <hr>
                    <center><h2 id="consultaPrecioProductoSinSesion"></h2></center>  
                    <center><img src="" id="consultaPrecioProductoSinSesionImagen"></center>
                    <hr>          
                </div>
            </center>
        </div>      
    </section>

    <script>
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
                        setTimeout(function(){
                            precioProductoSinSesionQr();
                        },4000);
                    } else{
                        swal({
                        title: "Productos",
                        text: "No se encontró el código de producto ingresado",
                        type: "error",
                        toast: true,
                        position: 'top',
                        showConfirmButton: false,
                        timer: 4000
                        });
                        $("#precioProductoSinSesionQr").val('');
                    }
                }
            })
        }
        
        function precioProductoSinSesionQr() {
            $("#precioProductoSinSesion").val('');
            $("#descripcionConsultaPrecioSinSesion").text('Muchas Gracias');
            $("#consultaPrecioProductoSinSesion").text('');   
            $("#precioProductoSinSesion").focus();
            $("#consultaPrecioProductoSinSesionImagen").attr('src', '');
            setTimeout(function(){
                window.close();
            }, 2000);
        }
        
        window.onload=fnPrecioProductoSinSesion();
    </script>
</body>