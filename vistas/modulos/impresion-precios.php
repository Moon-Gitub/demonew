
<div class="content-wrapper">

  <section class="content-header">
    
    <h1>
      
      Impresión precios productos
    
    </h1>

    <ol class="breadcrumb">
      
      <li><a href="inicio"><i class="fa fa-dashboard"></i> Inicio</a></li>
      
      <li class="active">Impresión precios productos</li>
    
    </ol>

  </section>

  <section class="content">

    <div class="box">

    <div class="box-header with-border">
	   <input type="hidden" id="idSucursal" name="idSucursal" value="<?php echo $_SESSION["sucursal"]; ?>" required>
              
        <div class="row">
          <div class="col-md-6">
		  <button class="btn btn-primary" title="Impresion Normal" id="btnImprimirPreciosComunProductos">
              <i class="fa fa-newspaper-o"></i>
            </button>
			<button class="btn btn-success" title="Impresion Oferta" id="btnImprimirPreciosSuperProductos">
					  <i class="fa fa-file-pdf-o"></i>
			</button>
			<button class="btn btn-warning" title="Imprimir Codigo Qr" id="btnImprimirCodigosQr">
				<i class="fa fa-qrcode"></i>
			</button>
			<button class="btn btn-danger" title="Imprimir Codigo Barra" id="btnImprimirCodigosBarra">
					  <i class="fa fa-barcode"></i>
			</button>
		
			 <input type="hidden" id="arrayProductosImpresion" name="arrayProductosImpresion"/>
		  </div>
		  <div class="col-md-3">
          </div>
          <div class="col-md-3">
		  <div class="panel panel-default" id="precioPlace" style="display:none;">
		  <div class="panel-heading">
            <center><h4 id="contador"></h4>
			<button class="btn btn-primary" onclick="verProductosImpresion()"  id="detallePlace" style="display:none;" data-toggle="modal" data-target="#modalAgregarMarca" id="verSeleccion">
              <i class="fa fa-file-pdf-o"> Ver Seleccion</i>
            </button>
			
			</center>
          </div>
		  </div>
		 
		  </div>
        </div>
		
	   </div>

      <div class="box-body">
        
        <table class="table table-bordered" id="tablaImpresionProductosImpresion" name="tablaImpresionProductosImpresion"> 
        <thead>
         
         <tr>
           <th><center>Id</center></th>
           <th><center>Codigo</center></th>
           <th><center>Descripcion</center></th>
           <th><center>Precio Venta</center></th>
		   <th><center>Quitar</center></th>
           <th><center>Agregar</center></th>

         </tr> 

        </thead>

        </table>

      </div>

    </div>

  </section>

</div>

<style>
.uniqueClassName {
    text-align: center;
}
</style>

<!--=====================================
MODAL AGREGAR MARCA
======================================-->

<div id="modalAgregarMarca" class="modal fade" role="dialog">
  
  <div class="modal-dialog">
    <div class="modal-content">
      <form role="form" method="post">
        <!--=====================================
        CABEZA DEL MODAL
        ======================================-->
        <div class="modal-header" style="background:#3c8dbc; color:white">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Productos Seleccionados</h4>
        </div>
        <!--=====================================
        CUERPO DEL MODAL
        ======================================-->
        <div class="modal-body">
          <div class="box-body">
			<table class="table table-bordered table-striped dt-responsive" id="tablaProductosImprimir" width="100%">
          <thead>
		<tr>
           <th><center>Código</center></th>
		   <th><center>Descripción</center></th>
		    <th><center>Precio</center></th>
        </tr> 

        </thead>      

       </table>
  
          </div>

        </div>

        <!--=====================================
        PIE DEL MODAL
        ======================================-->

        <div class="modal-footer">

          <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Salir</button>


        </div>

      </form>

    </div>

  </div>

</div>