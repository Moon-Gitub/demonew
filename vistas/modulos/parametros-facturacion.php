<div class="content-wrapper">

	<section class="content" style="padding-top: 0px"> 
<?php

    $tpoCte = (isset($_GET["tipoCbte"])) ? $_GET["tipoCbte"] : 1;
    $ptoVta = (isset($_GET["ptoVta"])) ? $_GET["ptoVta"] : 1;
    $emp = (isset($_GET["empresa"])) ? $_GET["empresa"] : 1;

    $respuesta = ModeloEmpresa::mdlMostrarEmpresa('empresa', 'id', $emp);

    echo "<h3>Empresa: </h3>";
    echo('<pre>');
    print_r($respuesta);
    echo('</pre>');

    echo '$_GET["empresa"]';
    echo '$_GET["tipoCbte"]';
    echo '$_GET["ptoVta"]';

    $tiposCbtes = array(
            0 => 'X',
            1 => 'Factura A',
            6 => 'Factura B', 
            11 => 'Factura C',
            //'Factura E' => 0, 
            51 => 'Factura M',
            2 => 'Nota Débito A',
            7 => 'Nota Débito B',
            12 => 'Nota Débito C',
            //'Nota Débito E' => 0, 
            52 => 'Nota Débito M',
            3 => 'Nota Crédito A',
            8 => 'Nota Crédito B',
            13 => 'Nota Crédito C',
            //'Nota Crédito E' => 0,
            53 => 'Nota Crédito M',
            4 => 'Recibo A',
            9 => 'Recibo B',
            15 => 'Recibo C',
            //'Recibo E' => 0, 
            54 => 'Recibo M',
            '' => 'no definido'
          );

    $wsaa = new WSAA($respuesta);

    if (!$wsaa) {
        exit("Error en WSAA");
    }       

    //Comparar que la fecha/hora de expiracion del ultimo TA 
    //sea mayor que ahora. Si es menor genero nuevo TA
    if (date('Y-m-d H:i:s', strtotime($wsaa->get_expiration())) < date('Y-m-d H:i:s')) {

        echo '<h3> TA vencido, generando nuevo... </h3>';

            if (!$wsaa->generar_TA()) {
                exit("Error al intentar generar Ticket de Acceso");
            }

    } else {

        echo '<h3> TA vigente </h3>';

   	}

    echo '<h3>Construyendo objeto de Facturación electrónica</h3>';

    $wsfe = new WSFE($respuesta);

    if (!$wsfe) {
        exit("Error en WSFE");
    }

    //Abrir ticket de acceso para obtener el token y el sign        
    if(!$wsfe->openTA()){
        exit("Error en OpenTA");
    }
    echo "<h3>Ticket de Acceso: </h3>";
    echo('<pre>');
    var_dump($wsfe->datosTA()); 
    echo('</pre>');

    echo "<h3>Ptos de venta: </h3>";
	$resptosventa = $wsfe->PtosVenta();          
    echo('<pre>');
    var_dump($resptosventa->FEParamGetPtosVentaResult);
    echo('</pre>');

    echo "<h3>Ultimo comprobante autorizado (Punto de venta ".$ptoVta."): </h3>";
    $resptosventa = $wsfe->UltimoAutorizado($ptoVta, $tpoCte); 
    echo('<pre>');
    echo $tiposCbtes[$tpoCte];
    var_dump($resptosventa);
    echo('</pre>');

    // $resptosventa = $wsfe->UltimoAutorizado(2, 12); //Nota Debito C
    // echo('<pre>');
    // echo 'Nota Debito C';
    // var_dump($resptosventa);
    // echo('</pre>');

    // $resptosventa = $wsfe->UltimoAutorizado(2, 13); //Nota Credito C
    // echo('<pre>');
    // echo 'Nota Credito C';
    // var_dump($resptosventa);
    // echo('</pre>');

    // $resptosventa = $wsfe->UltimoAutorizado(2, 15); //Recibo C
    // echo('<pre>');
    // echo 'Recibo C';
    // var_dump($resptosventa);
    // echo('</pre>');

    echo "<h3>Tipos de comprobantes: </h3>";
	$restipocbte = $wsfe->TiposCbte();
    echo('<pre>');
    print_r($restipocbte->FEParamGetTiposCbteResult->ResultGet->CbteTipo);
    echo('</pre>');

    echo "<h3>Tipos de concepto: </h3>";
	$restipoconcep = $wsfe->TiposConcepto();                
    echo('<pre>');
    print_r($restipoconcep->FEParamGetTiposConceptoResult->ResultGet->ConceptoTipo);
    echo('</pre>');

    echo "<h3>Tipos de documento: </h3>";
	$restipodoc = $wsfe->TiposDoc();                
    echo('<pre>');
    print_r($restipodoc->FEParamGetTiposDocResult->ResultGet->DocTipo);
    echo('</pre>');

    echo "<h3>Tipos de IVA: </h3>";
	$restipoiva = $wsfe->TiposIva();                
    echo('<pre>');
    print_r($restipoiva->FEParamGetTiposIvaResult->ResultGet->IvaTipo);
    echo('</pre>');

    echo "<h3>Tipos de moneda: </h3>";
	$restipomoneda = $wsfe->TiposMonedas();                
    echo('<pre>');
    print_r($restipomoneda->FEParamGetTiposMonedasResult->ResultGet->Moneda);
    echo('</pre>');

    echo "<h3>Cotizacion moneda (ej. dolar): </h3>";
	$rescotizacion = $wsfe->Cotizacion('DOL');                
    echo('<pre>');
    print_r($rescotizacion->FEParamGetCotizacionResult->ResultGet);
    echo('</pre>');

    echo "<h3>Tipos opcionales: </h3>";
	$resopcionales = $wsfe->Opcionales();                
    echo('<pre>');
    print_r($resopcionales->FEParamGetTiposOpcionalResult->ResultGet->OpcionalTipo);
    echo('</pre>');

    echo "<h3>Tipos tributos: </h3>";
	$restributos = $wsfe->TiposTributos();                
    echo('<pre>');
    print_r($restributos->FEParamGetTiposTributosResult->ResultGet->TributoTipo);
    echo('</pre>');
	
	echo "<h3>Cantidad de Registros: </h3>";
	$restributos = $wsfe->CantidadRegistros();                
    echo('<pre>');
    print_r($restributos->FECompTotXRequestResult);
    echo('</pre>');

	echo "<h3>Tipos de paises: </h3>";
	$restributos = $wsfe->TiposPaises();                
    echo('<pre>');
    print_r($restributos->FEParamGetTiposPaisesResult->ResultGet);
    echo('</pre>');
?>

	</section>
</div>